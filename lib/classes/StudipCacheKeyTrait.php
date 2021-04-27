<?php
/**
 * Trait for unique cache hashes per key for each system based on db configuration which should
 * be sufficient to eliminate cache mishaps.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     GPL2 or any later version
 * @package     studip
 * @subpackage  cache
 * @since       Stud.IP 5.0
 */
trait StudipCacheKeyTrait
{
    protected $cache_prefix = null;

    /**
     * Returns a prefix cache key based on db configuration.
     *
     * @param  string $offset
     * @return string
     */
    protected function getCacheKey($offset)
    {
        if ($this->cache_prefix === null) {
            $this->cache_prefix = md5("{$GLOBALS['DB_STUDIP_HOST']}|{$GLOBALS['DB_STUDIP_DATABASE']}");
        }
        return "{$this->cache_prefix}/{$offset}";
    }
}
