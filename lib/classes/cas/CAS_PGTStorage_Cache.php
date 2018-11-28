<?php
/**
 * CAS_PGTStorage_Cache.php - PGTStorage backend using StudipCache
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

require_once 'vendor/phpCAS/CAS.php';

class CAS_PGTStorage_Cache extends CAS_PGTStorage_AbstractStorage
{
    /**
     * This method returns an informational string giving the type of storage
     * used by the object (used for debugging purposes).
     *
     * @return an informational string.
     */
    public function getStorageType()
    {
        return 'cache';
    }

    /**
     * This method returns an informational string giving informations on the
     * parameters of the storage.(used for debugging purposes).
     *
     * @return an informational string.
     */
    public function getStorageInfo()
    {
        return 'cache_class_file=' . Config::get()->cache_class_file;
    }

    /**
     * This method stores a PGT and its corresponding PGT Iou in the cache.
     *
     * @param string $pgt     the PGT
     * @param string $pgt_iou the PGT iou
     */
    public function write($pgt, $pgt_iou)
    {
        $cache = StudipCacheFactory::getCache();
        $cache_key = 'pgtiou/' . $pgt_iou;
        return $cache->write($cache_key, $pgt);
    }

    /**
     * This method reads a PGT corresponding to a PGT Iou and deletes the
     * corresponding cache entry.
     *
     * @param string $pgt_iou the PGT iou
     *
     * @return the corresponding PGT, or FALSE on error
     */
    public function read($pgt_iou)
    {
        $cache = StudipCacheFactory::getCache();
        $cache_key = 'pgtiou/' . $pgt_iou;
        $pgt = $cache->read($cache_key);
        $cache->expire($cache_key);
        return $pgt;
    }
}
