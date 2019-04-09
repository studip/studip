<?php
/**
 * StudipCache implementation using database table
 *
 * @package     studip
 * @subpackage  cache
 *
 * @author    Elmar Ludwig <elmar.ludwig@uos.de>
 */
class StudipDbCache implements StudipCache
{
    /**
     * Expire item from the cache.
     *
     * @param string $arg a single key
     */
    public function expire($arg)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('DELETE FROM cache WHERE cache_key = ?');
        $stmt->execute([$arg]);
    }

    /**
     * Expire all items from the cache.
     */
    public function flush()
    {
        $db = DBManager::get();

        $db->exec('TRUNCATE TABLE cache');
    }

    /**
     * Delete all expired items from the cache.
     */
    public function purge()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('DELETE FROM cache WHERE expires < ?');
        $stmt->execute([time()]);
    }

    /**
     * Retrieve item from the server.
     *
     * @param string $arg a single key
     *
     * @return mixed    the previously stored data if an item with such a key
     *                  exists on the server or FALSE on failure.
     */
    public function read($arg)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT content FROM cache WHERE cache_key = ? AND expires > ?');
        $stmt->execute([$arg, time()]);
        $result = $stmt->fetchColumn();

        return $result !== false ? unserialize($result) : false;
    }

    /**
     * Store data at the server.
     *
     * @param string $name     the item's key.
     * @param mixed  $content  the item's content (will be serialized if necessary).
     * @param int    $expired  the item's expiry time in seconds. Optional, defaults to 12h.
     *
     * @return bool     returns TRUE on success or FALSE on failure.
     */
    public function write($name, $content, $expires = self::DEFAULT_EXPIRATION)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('REPLACE INTO cache VALUES(?, ?, ?)');
        $stmt->execute([$name, serialize($content), time() + $expires]);
    }
}
