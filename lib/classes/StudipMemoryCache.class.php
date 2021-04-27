<?php
/**
 * The php memory implementation of the StudipCache interface.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */
class StudipMemoryCache implements StudipCache
{
    protected $memory_cache = [];

    /**
     * Expires just a single key.
     *
     * @param  string  the key
     */
    public function expire($key)
    {
        unset($this->memory_cache[$key]);
    }

    /**
     * Expire all items from the cache.
     */
    public function flush()
    {
        $this->memory_cache = [];
    }

    /**
     * Reads just a single key from the cache.
     *
     * @param  string  the key
     *
     * @return mixed   the corresponding value
     */
    public function read($key)
    {
        if (!isset($this->memory_cache[$key])) {
            return false;
        }
        if ($this->memory_cache[$key]['expires'] < time()) {
            $this->expire($key);
            return false;
        }
        return $this->memory_cache[$key]['data'];
    }

    /**
     * Store data at the server.
     *
     * @param string   the item's key.
     * @param mixed    the item's content (will be serialized if necessary).
     * @param int      the item's expiry time in seconds. Defaults to 12h.
     *
     * @returns mixed  returns TRUE on success or FALSE on failure.
     *
     */
    public function write($name, $content, $expire = self::DEFAULT_EXPIRATION)
    {
        $this->memory_cache[$name] = [
            'expires' => time() + $expire,
            'data'    => $content,
        ];
    }
}
