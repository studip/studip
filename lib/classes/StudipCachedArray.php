<?php
/**
 * This class represents an array in cache and removes the neccessity to
 * encode/decode and store the data after every change.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */
class StudipCachedArray implements ArrayAccess
{
    protected $key;
    protected $cache;
    protected $data = [];

    /**
     * Constructs the cached array
     *
     * @param string $key Cache key where the array is/should be stored
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->cache = StudipCacheFactory::getCache();

        $this->loadData();
    }

    /**
     * Determines whether an offset exists in the array.
     *
     * @param string $offset Offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Returns the value at given offset or null if it doesn't exist.
     *
     * @param string $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Sets the value for a given offset.
     *
     * @param string $offset Offset
     * @param mixed  $value  Value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        $this->storeData();
    }

    /**
     * Unsets the value at a given offset
     *
     * @param string $offset Offset
     */
    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
            $this->storeData();
        }
    }

    /**
     * Clears all data.
     */
    public function clear()
    {
        $this->data = [];
        $this->storeData();
    }

    /**
     * Loads the data from cache.
     */
    protected function loadData()
    {
        $cached = $this->cache->read($this->key);
        if ($cached) {
            $this->data = json_decode($cached, true);
        }
    }

    /**
     * Stores the data back to the cache.
     */
    protected function storeData()
    {
        $this->cache->write($this->key, json_encode($this->data));
    }
}
