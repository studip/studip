<?php
/**
 * Proxies a StudipCache and stores the expire operation in the database.
 * These operations are lateron applied to the cache they should have
 * been applied to in the beginning.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.3
 */
class StudipCacheProxy implements StudipCache
{
    protected $actual_cache;
    protected $proxy_these;

    /**
     * @param StudipCache $cache       The actual cache object
     * @param mixed       $proxy_these List of operations to proxy (should be
     *                                 an array but a space seperated string
     *                                 is also valid)
     */
    public function __construct(StudipCache $cache, $proxy_these = ['expire'])
    {
        if (!is_array($proxy_these)) {
            $proxy_these = words($proxy_these);
        }

        $this->actual_cache = $cache;
        $this->proxy_these  = is_array($proxy_these)
                            ? $proxy_these
                            : words($proxy_these);
    }

    /**
     * Expires just a single key.
     *
     * @param string $key The item's key
     */
    public function expire($key)
    {
        if (in_array('expire', $this->proxy_these)) {
            try {
                $operation = new StudipCacheOperation([$key, 'expire']);
                $operation->parameters = serialize([]);
                $operation->store();
            } catch (Exception $e) {
            }
        }

        return $this->actual_cache->expire($key);
    }

    /**
     * Expire all items from the cache.
     */
    public function flush()
    {
        if (in_array('flush', $this->proxy_these)) {
            try {
                $operation = new StudipCacheOperation(['', 'flush']);
                $operation->parameters = serialize([]);
                $operation->store();
            } catch (Exception $e) {
            }
        }

        return $this->actual_cache->flush();
    }

    /**
     * Reads just a single key from the cache.
     *
     * @param  string $key The item's key
     * @return mixed The corresponding value
     */
    public function read($key)
    {
        return $this->actual_cache->read($key);
    }

    /**
     * Store data at the server.
     *
     * @param string $key     The item's key
     * @param string $content The item's conten
     * @param int    $expires The item's expiry time in seconds, defaults to 12h
     * @return bool  Returns TRUE on success or FALSE on failure
     */
    public function write($key, $content, $expires = self::DEFAULT_EXPIRATION)
    {
        if (in_array('write', $this->proxy_these)) {
            try {
                $operation = new StudipCacheOperation([$key, 'write']);
                $operation->parameters = serialize([$content, $expires]);
                $operation->store();
            } catch (Exception $e) {
            }
        }

        return $this->actual_cache->write($key, $content, $expires);
    }
}
