<?php
/**
 * This class represents an array in cache and removes the neccessity to
 * encode/decode and store the data after every change.
 *
 * Caching is handled by splitting the data into partitions based on the key.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 *
 * @todo Automagic partition size?
 */
class StudipCachedArray implements ArrayAccess
{
    protected $key;
    protected $cache;
    protected $partitions;
    protected $partition_by;

    protected $data = [];

    /**
     * Constructs the cached array
     *
     * @param string $key          Cache key where the array is/should be stored
     * @param mixed  $partition_by Defines the partitioning, this may either be
     *                             an int which will be length of the substring
     *                             of the given chache offset or a callable which
     *                             will return the partition key.
     */
    public function __construct($key, $partition_by = 1)
    {
        if (!is_callable($partition_by) && !is_int($partition_by)) {
            throw new Exception('Parameter $partition_by may only be a number or a callable if set');
        }
        if (ctype_digit($partition_by) && $partition_by <= 1) {
            throw new Exception('Parameter $partition_by must be positive and not zero');
        }

        $this->key          = $key;
        $this->partition_by = $partition_by;

        $this->cache = StudipCacheFactory::getCache();

        $cached = $this->cache->read($key);
        if ($cached) {
            $this->partitions = json_decode($cached, true);
        } else {
            $this->partitions = [];
        }
    }

    /**
     * Determines whether an offset exists in the array.
     *
     * @param string $offset Offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists(
            $offset,
            $this->loadData($offset)
        );
    }

    /**
     * Returns the value at given offset or null if it doesn't exist.
     *
     * @param string $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->loadData($offset)[$offset] ?? null;
    }

    /**
     * Sets the value for a given offset.
     *
     * @param string $offset Offset
     * @param mixed  $value  Value
     */
    public function offsetSet($offset, $value)
    {
        $this->loadData($offset)[$offset] = $value;

        $this->storeData($offset);
    }

    /**
     * Unsets the value at a given offset
     *
     * @param string $offset Offset
     */
    public function offsetUnset($offset)
    {
        $data = $this->loadData($offset);

        if (array_key_exists($offset, $data)) {
            unset($data[$offset]);
            $this->storeData($offset);
        }
    }

    /**
     * Clears all data.
     */
    public function clear()
    {
        foreach (array_keys($this->partitions) as $partition) {
            $this->cache->expire($this->getPartitionKey($partition));
        }

        $this->partitions = [];
        $this->data = [];

        $this->storeData();
    }

    /**
     * Loads the data from cache.
     */
    protected function &loadData($offset)
    {
        $partition = $this->getPartition($offset);
        if (!isset($this->data[$partition])) {
            $cached = $this->cache->read($this->getPartitionKey($offset));
            if ($cached) {
                $this->data[$partition] = json_decode($cached, true);
            } else {
                $this->data[$partition] = [];
            }
        }

        return $this->data[$partition];
    }

    /**
     * Stores the data back to the cache.
     */
    protected function storeData($offset = null)
    {
        $partition = false;

        if ($offset !== null) {
            $partition = $this->getPartition($offset);
            if (!array_key_exists($partition, $this->partitions) && count($this->data[$partition]) > 0) {
                $this->partitions[$partition] = count($this->data[$partition]);
            } elseif (array_key_exists($partition, $this->partitions) && count($this->data[$partition]) === 0) {
                unset($this->partitions[$partition]);
            }
        }

        foreach ($this->data as $p => $data) {
            if ($partition === false || $p == $partition) {
                $key = $this->getPartitionKey($p);

                if (count($data) === 0) {
                    $this->cache->expire($key);
                } else {
                    $this->cache->write($key, json_encode($data));
                }
            }
        }

        if (count($this->partitions) === 0) {
            $this->cache->expire($this->key);
        } else {
            $this->cache->write($this->key, json_encode($this->partitions));
        }
    }

    /**
     * Extracts the partition from the key.
     * @param  string $offset
     * @return string partition
     */
    protected function getPartition($offset)
    {
        if (is_callable($this->partition_by)) {
            return call_user_func($this->partition_by, $offset);
        }
        return mb_substr($offset, 0, $this->partition_by);
    }

    /**
     * Returns the partition key for storage.
     *
     * @param  string $offset
     * @return string
     */
    protected function getPartitionKey($offset)
    {
        return rtrim($this->key, '/') . '/' . $this->getPartition($offset);
    }
}
