<?php
# Lifter010: TODO
/**
 * media_proxy.php - media proxy cache model
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


/**
 * Special Exception class for proxy errors. The exception message
 * must be HTTP/1.1 response status line.
 */
class MediaProxyException extends Exception {
}

/**
 * Model class for the Stud.IP media proxy.
 */
class MediaProxy
{
    const GC_PROBABILITY = 2;

    private $cache_path;
    private $cache_lifetime;
    private $cache_maxlength;

    /**
     * Initalize a new MediaProxy instance.
     */
    public function __construct()
    {
        $config = Config::GetInstance();
        $this->cache_path = $config->getValue('MEDIA_CACHE_PATH');
        $this->cache_lifetime = $config->getValue('MEDIA_CACHE_LIFETIME');
        $this->cache_maxlength = $config->getValue('MEDIA_CACHE_MAX_LENGTH');

        if (mt_rand(0, 99) < self::GC_PROBABILITY) {
            $this->garbageCollect();
        }
    }

    /**
     * Retrieve meta data about a (possibly) cached media resource.
     *
     * @return array    meta data of resource or NULL (not cached)
     */
    public function getMetaData($url)
    {
        $id = md5($url);

        $query = "SELECT id, type, UNIX_TIMESTAMP(chdate) AS chdate,
                         UNIX_TIMESTAMP(expires) AS expires
                  FROM media_cache
                  WHERE id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);

        if ($row = $statement->fetch()) {
            if ($row['expires'] > time()) {
                return $row;
            } else {
                $this->removeCacheEntries([$id]);
            }
        }

        return NULL;
    }

    /**
     * Read URL and send data to the browser (similar to readfile()).
     * Will cache the sent data if possible. An optional timestamp can
     * be specified if the browser supplied an If-Modified-Since header.
     *
     * @param string $url             URL to send
     * @param int    $modified_since  test if resource is modified
     */
    public function readURL($url, $modified_since = NULL)
    {
        $metadata = $this->getMetaData($url);
        $cachefile = $this->getCacheFile(md5($url));

        if (!$metadata) {
            return $this->cacheURL($url);
        }

        if (isset($modified_since) && $metadata['chdate'] <= $modified_since) {
            throw new MediaProxyException('HTTP/1.0 304 Not Modified');
        }

        $type = $metadata['type'];
        $chdate = $metadata['chdate'];
        $expires = $metadata['expires'];

        if (file_exists($cachefile)) {
            $this->sendHeaders($type, filesize($cachefile), $chdate, $expires);
            readfile($cachefile);
        } else {
            $this->sendHeaders($type, NULL, $chdate, $expires);
            $this->sendData($url, true);
        }
    }

    /**
     * Send the appropriate HTTP response headers to the client.
     */
    private function sendHeaders($type, $length, $chdate, $expires)
    {
        if (isset($length)) {
            header("Content-Length: $length");
        }

        header("Content-Type: $type");
        header("Last-Modified: " . gmdate(DATE_RFC1123, $chdate));
        header("Expires: " . gmdate(DATE_RFC1123, $expires));
        header('Pragma: public');
    }

    /**
     * Send the data from the given URL to the client.
     *
     * @param string $url       URL to send
     * @param bool   $cache     should data be cached?
     */
    private function sendData($url, $cache)
    {
        $handle = fopen($url, 'rb');
        $length = 0;
        $data = '';

        if ($handle === false) {
            throw new MediaProxyException('HTTP/1.1 404 Not Found');
        }

        while (!feof($handle)) {
            $buffer = fread($handle, 65536);
            $length += mb_strlen($buffer);

            if ($cache) {
                if ($length <= $this->cache_maxlength) {
                    $data .= $buffer;
                } else {
                    $cache = false;
                }
            }

            echo $buffer;
        }

        fclose($handle);

        if ($cache) {
            file_put_contents($this->getCacheFile(md5($url)), $data);
        }
    }

    /**
     * Read URL, try to cache the data and send it to the browser.
     *
     * @param string $url             URL to send
     */
    private function cacheURL($url)
    {
        $response = FileManager::fetchURLMetadata($url);

        foreach ($response as $key => $value) {
            $response[mb_strtolower($key)] = $value;
        }
        if ($response['response_code'] != 200) {
            throw new MediaProxyException($response['response']);
        } else if (!isset($response['content-type'])
            || !in_array(array_shift(explode('/', $response['content-type'])), words('image audio video'))
            || mb_stripos($response['content-type'], 'svg') !== false) {
            throw new MediaProxyException('HTTP/1.1 415 Unsupported Media Type');
        }

        $type = $response['content-type'];
        $length = $response['content-length'];
        $chdate = $response['last-modified'];
        $expires = $response['expires'];

        $chdate = isset($chdate) ? strtotime($chdate) : time();
        $expires = isset($expires) ? strtotime($expires) : time() + $this->cache_lifetime;

        $this->sendHeaders($type, $length, $chdate, $expires);
        $this->sendData($url, $length <= $this->cache_maxlength);
        $this->addCacheEntry(md5($url), $type, $chdate, $expires);
    }

    /**
     * Remove old files from the media cache.
     */
    public function garbageCollect()
    {
        $db = DBManager::get();
        $config = Config::GetInstance();
        $limit = (int)$config->getValue('MEDIA_CACHE_MAX_FILES');

        $result = $db->query("SELECT id FROM media_cache ORDER BY expires DESC LIMIT $limit, 1000");

        if ($ids = $result->fetchAll(PDO::FETCH_COLUMN)) {
            $this->removeCacheEntries($ids);
        }
    }

    /**
     * Get the file system path for a cached resource.
     */
    private function getCacheFile($id)
    {
        return $this->cache_path . '/' . $id;
    }

    /**
     * Add a cached resource to the database table.
     */
    private function addCacheEntry($id, $type, $chdate, $expires)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT INTO media_cache (id, type, chdate, expires) VALUES (?,?,?,?)');
        $stmt->execute([$id, $type, strftime('%F %T', $chdate), strftime('%F %T', $expires)]);
    }

    /**
     * Remove cached resources from the database table.
     */
    private function removeCacheEntries(array $ids)
    {
        $db = DBManager::get();

        $stmt = $db->prepare("DELETE FROM media_cache WHERE id IN (?)");
        $stmt->execute([$ids ?: '']);

        foreach ($ids as $id) {
            @unlink($this->getCacheFile($id));
        }
    }
}
