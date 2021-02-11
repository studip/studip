<?php
/**
 * Generic cache trait for search module
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
trait GlobalSearchCacheTrait
{
    protected static $cache = [];

    /**
     * Convenience method for getting and setting value at once.
     * @param  string  $index     Index to look up/set
     * @param  Closure $generator Generator for the value
     * @return mixed value
     */
    protected static function fromCache($index, Closure $generator)
    {
        if (static::hasCachedItem($index)) {
            return static::getCachedItem($index);
        }

        return static::setCachedItem($index, $generator());
    }

    /**
     * Returns whether the cache has the item
     * @param  string  $index Index to look up
     * @return boolean
     */
    protected static function hasCachedItem($index)
    {
        return array_key_exists($index, static::$cache);
    }

    /**
     * Returns the cached item
     * @param  string  $index Index to look up
     * @return mixed value of item or null if not found
     */
    protected static function getCachedItem($index)
    {
        return static::hasCachedItem($index) ? static::$cache[$index] : null;
    }

    /**
     * Stored an item in cache
     * @param  string  $index Index to store
     * @param  string  $value Value to store
     * @return mixed value
     */
    protected static function setCachedItem($index, $value)
    {
        return static::$cache[$index] = $value;
    }

    /**
     * Clears the cache.
     */
    public static function clearCache()
    {
        static::$cache = [];
    }
}
