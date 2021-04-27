<?php
/**
 * An interface which has to be implemented by instances returned from
 * StudipCacheFactory#getCache
 *
 * @package    studip
 * @subpackage lib
 *
 * @author     Marco Diedrich (mdiedric@uos)
 * @author     Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright  (c) Authors
 * @since      1.6
 * @license    GPL2 or any later version
 */

interface StudipCache
{
    const DEFAULT_EXPIRATION = 12 * 60 * 60; // 12 hours

    /**
     * Expire item from the cache.
     *
     * Example:
     *
     *   # expires foo
     *   $cache->expire('foo');
     *
     * @param string $arg a single key
     */
    public function expire($arg);

    /**1
     * Expire all items from the cache.
     */
    public function flush();

    /**
     * Retrieve item from the server.
     *
     * Example:
     *
     *   # reads foo
     *   $foo = $cache->reads('foo');
     *
     * @param string $arg a single key
     *
     * @return mixed    the previously stored data if an item with such a key
     *                  exists on the server or FALSE on failure.
     */
    public function read($arg);

    /**
     * Store data at the server.
     *
     * @param string $name     the item's key.
     * @param mixed  $content  the item's content (will be serialized if necessary).
     * @param int    $expired  the item's expiry time in seconds. Optional, defaults to 12h.
     *
     * @return bool     returns TRUE on success or FALSE on failure.
     */
    public function write($name, $content, $expires = self::DEFAULT_EXPIRATION);
}
