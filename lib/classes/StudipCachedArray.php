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
class StudipCachedArray implements ArrayAccess, Countable
{
    const ENCODE_JSON = 0;
    const ENCODE_SERIALIZE = 1;

    protected $key;
    protected $cache;
    protected $partitions;
    protected $partition_by;
    protected $encoding;

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
    public function __construct($key, $partition_by = 1, $encoding = self::ENCODE_JSON, StudipCache $cache = null)
    {
        if (!is_callable($partition_by) && !is_int($partition_by)) {
            throw new Exception('Parameter $partition_by may only be a number or a callable if set');
        }
        if (ctype_digit($partition_by) && $partition_by <= 1) {
            throw new Exception('Parameter $partition_by must be positive and not zero');
        }

        $this->key          = $key;
        $this->partition_by = $partition_by;
        $this->encoding     = $encoding;
        $this->setCache($cache ?? StudipCacheFactory::getCache());
    }

    /**
     * Sets the cache for this array and resets internal states.
     *
     * @param StudipCache $cache
     */
    public function setCache(StudipCache $cache)
    {
        $this->cache = $cache;
        $this->reset();
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
        $data = &$this->loadData($offset);

        if (array_key_exists($offset, $data)) {
            unset($data[$offset]);
            $this->storeData($offset);
        }
    }

    /**
     * Counts all values in chache.
     * @return int
     */
    public function count()
    {
        return array_sum($this->partitions);
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
     * Returns all items in cache as an array.
     * @return array
     */
    public function getArrayCopy()
    {
        $result = [];
        foreach (array_keys($this->partitions) as $partition) {
            foreach ($this->loadData($partition) as $key => $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Loads all partitions of this cache and resets the data array
     * @return array
     */
    public function reset()
    {
        $this->data = [];
        $this->partitions = [];

        $cached = $this->cache->read($this->key);
        if ($cached) {
            $this->partitions = $this->decode($cached);
        }
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
                $this->data[$partition] = $this->decode($cached);
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
            if (!array_key_exists($partition, $this->partitions) || count($this->data[$partition]) > 0) {
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
                    $this->cache->write($key, $this->encode($data));
                }
            }
        }

        if (count($this->partitions) === 0) {
            $this->cache->expire($this->key);
        } else {
            $this->cache->write($this->key, $this->encode($this->partitions));
        }
    }

    /**
     * Encodes the given data based on the set encoding mechanism.
     * @param  mixed $data
     * @return string
     */
    protected function encode($data)
    {
        if ($this->encoding === self::ENCODE_JSON) {
            return json_encode($data);
        }

        if ($this->encoding === self::ENCODE_SERIALIZE) {
            return serialize($data);
        }

        throw new Exception('Unknown encoding type');
    }

    /**
     * Decodes the given data based on the set encoding mechanism.
     * @param  string $data
     * @return mixed
     */
    protected function decode($data)
    {
        if ($this->encoding === self::ENCODE_JSON) {
            return json_decode($data, true);
        }

        if ($this->encoding === self::ENCODE_SERIALIZE) {
            return unserialize($data);
        }

        throw new Exception('Unknown decoding type');
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
