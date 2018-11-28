<?php

## Copyright (c) 2011 Elmar Ludwig, University of Osnabrueck
##
## PHPLIB Data Storage Container using Stud.IP cache
## for use with Stud.IP and cache only!

class CT_Cache {
    const CACHE_KEY_PREFIX = 'session_data';
    const SESSION_LIFETIME = 7200;

    private $cache;

    function ac_start() {
        $this->cache = StudipCacheFactory::getCache();
    }

    function ac_get_lock() {
    }

    function ac_release_lock() {
    }

    function ac_newid($str, $name = null) {
        return $this->ac_get_value($str) === false ? $str : false;
    }

    function ac_store($id, $name = null, $str) {
        $cache_key = self::CACHE_KEY_PREFIX . '/' . $id;
        return $this->cache->write($cache_key, $str, self::SESSION_LIFETIME);
    }

    function ac_delete($id, $name = null) {
        $cache_key = self::CACHE_KEY_PREFIX . '/' . $id;
        $this->cache->expire($cache_key);
    }

    function ac_gc($gc_time, $name = null) {
    }

    function ac_halt($s) {
        echo "<b>$s</b>";
        exit;
    }

    function ac_get_value($id, $name = null) {
        $cache_key = self::CACHE_KEY_PREFIX . '/' . $id;
        return $this->cache->read($cache_key);
    }

    function ac_get_changed($id, $name = null) {
    }

    function ac_set_changed($id, $name = null, $timestamp) {
    }
}
?>
