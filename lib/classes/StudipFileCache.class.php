<?php
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipFileCache.class.php
//
//
//
// Copyright (c) 2007 André Noack <noack@data-quest.de>
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+

/**
 * StudipCache implementation using files
 *
 * @package     studip
 * @subpackage  cache
 *
 * @author    André Noack <noack@data-quest.de>
 * @version   2
 */
class StudipFileCache implements StudipSystemCache
{
    use StudipCacheKeyTrait;

    /**
     * full path to cache directory
     *
     * @var string
     */
    private $dir;

    /**
     * @return string A translateable display name for this cache class.
     */
    public static function getDisplayName(): string
    {
        return _('Dateisystem');
    }

    /**
     * without the 'dir' argument the cache path is taken from
     * $CACHING_FILECACHE_PATH or is set to
     * $TMP_PATH/studip_cache
     *
     * @param string the path to use
     * @return void
     * @throws exception if the directory does not exist or could not be
     *         created
     */
    public function __construct($path = '')
    {
        $this->dir = $path
                  ?: (Config::get()->SYSTEMCACHE['type'] == 'StudipFileCache' ?
                        Config::get()->SYSTEMCACHE['config']['path'] : '')
                  ?: $GLOBALS['CACHING_FILECACHE_PATH']
                  ?: ($GLOBALS['TMP_PATH'] . '/' . 'studip_cache');
        $this->dir = rtrim($this->dir, '\\/') . '/';

        if (!is_dir($this->dir) && !@mkdir($this->dir, 0700)) {
            throw new Exception('Could not create directory: ' . $this->dir);
        }

        if (!is_writable($this->dir)) {
            throw new Exception('Can not write to directory: ' . $this->dir);
        }
    }

    /**
     * get path to cache directory
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->dir;
    }

    /**
     * expire cache item
     *
     * @see StudipCache::expire()
     * @param string $arg
     * @return void
     */
    public function expire($arg)
    {
        $key = $this->getCacheKey($arg);

        if ($file = $this->getPathAndFile($key)){
            @unlink($file);
        }
    }

    /**
     * Expire all items from the cache.
     */
    public function flush()
    {
        rmdirr($this->dir);
    }

    /**
     * retrieve cache item from filesystem
     * tests first if item is expired
     *
     * @see StudipCache::read()
     * @param string $arg a cache key
     * @return string|bool
     */
    public function read($arg)
    {
        $key = $this->getCacheKey($arg);

        if ($file = $this->check($key)){
            $f = @fopen($file, 'rb');
            if ($f) {
                @flock($f, LOCK_SH);
                $result = stream_get_contents($f);
                @fclose($f);
            }
            return unserialize($result);
        }
        return false;
    }

    /**
     * store data as cache item in filesystem
     *
     * @see StudipCache::write()
     * @param string $arg a cache key
     * @param mixed $content data to store
     * @param int $expire expiry time in seconds, default 12h
     * @return int|bool the number of bytes that were written to the file,
     *         or false on failure
     */
    public function write($arg, $content, $expire = self::DEFAULT_EXPIRATION)
    {
        $key = $this->getCacheKey($arg);

        $this->expire($key);
        $file = $this->getPathAndFile($key, $expire);
        return @file_put_contents($file, serialize($content), LOCK_EX);
    }

    /**
     * checks if specified cache item is expired
     * if expired the cache file is deleted
     *
     * @param string $key a cache key to check
     * @return string|bool the path to the cache file or false if expired
     */
    private function check($key)
    {
        if ($file = $this->getPathAndFile($key)){
            list($id, $expire) = explode('-', basename($file));
            if (time() < $expire) {
                return $file;
            } else {
                @unlink($file);
            }
        }
        return false;
    }

    /**
     * get the full path to a cache file
     *
     * the cache files are organized in sub-folders named by
     * the first two characters of the hashed cache key.
     * the filename is constructed from the hashed cache key
     * and the timestamp of expiration
     *
     * @param string $key a cache key
     * @param int $expire expiry time in seconds
     * @return string|bool full path to cache item or false on failure
     */
    private function getPathAndFile($key, $expire = null)
    {
        $id = hash('md5', $key);
        $path = $this->dir . mb_substr($id, 0, 2);
        if (!is_dir($path) && !@mkdir($path, 0700)) {
            throw new Exception('Could not create directory: ' . $path);
        }
        if (!is_null($expire)){
            return $path . '/' . $id . '-' . (time() + $expire);
        } else {
            $files = @glob("{$path}/{$id}*");
            if (count($files) > 0) {
                return $files[0];
            }
        }
        return false;
    }

    /**
     * purges expired entries from the cache directory
     *
     * @param bool echo messages if set to false
     * @return int the number of deleted files
     */
    public function purge($be_quiet = true)
    {
        $now = time();
        $deleted = 0;
        foreach (@glob($this->dir . '*', GLOB_ONLYDIR) as $current_dir){
            foreach (@glob("{$current_dir}/*") as $file){
                list($id, $expire) = explode('-', basename($file));
                if ($expire < $now) {
                    if (@unlink($file)) {
                        ++$deleted;
                        if (!$be_quiet) {
                            echo "File: {$file} deleted.\n";
                        }
                    }
                } else if (!$be_quiet){
                    echo "File: {$file} expires on " . strftime('%x %X', $expire) . "\n";
                }
            }
        }
        return $deleted;
    }

    /**
     * Return statistics.
     *
     * @StudipSystemCache::getStats()
     *
     * @return array|array[]
     */
    public function getStats(): array
    {
        return [
            __CLASS__ => [
                'name' => _('Anzahl Einträge'),
                'value' => DBManager::get()->fetchColumn("SELECT COUNT(*) FROM `cache`")
            ]
        ];
    }

    /**
     * Return the Vue component name and props that handle configuration.
     *
     * @see StudipSystemCache::getConfig()
     *
     * @return array
     */
    public static function getConfig(): array
    {
        $currentCache = Config::get()->SYSTEMCACHE;

        // Set default config for this cache
        $currentConfig = [
            'path' => $GLOBALS['TMP_PATH'] . '/studip_cache'
        ];

        // If this cache is set as system cache, use config from global settings.
        if ($currentCache['type'] == __CLASS__) {
            $currentConfig = $currentCache['config'];
        }

        return [
            'component' => 'FileCacheConfig',
            'props' => $currentConfig
        ];
    }

}
