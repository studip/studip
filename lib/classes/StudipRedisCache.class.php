<?php
/**
 * Cache implementation using redis.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     GPL2 or any later version
 * @package     studip
 * @subpackage  cache
 * @since       Stud.IP 5.0
 */
class StudipRedisCache implements StudipSystemCache
{
    use StudipCacheKeyTrait;

    private $redis;

    /**
     * @return string A translateable display name for this cache class.
     */
    public static function getDisplayName(): string
    {
        return _('Redis');
    }

    /**
     * Construct a cache instance.
     *
     * @param string $hostname Hostname of redis server
     * @param int    $port     Port of redis server
     */
    public function __construct($hostname, $port)
    {
        if (!extension_loaded('redis')) {
            throw new Exception('Redis extension missing.');
        }

        $this->redis = new Redis();
        $status = $this->redis->connect($hostname, $port, 1);

        if (!$status) {
            throw new Exception('Could not add cache.');
        }
    }

    /**
     * Returns the instance of the redis server connection.
     *
     * @return Redis instance
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * Expire item from the cache.
     *
     * Example:
     *
     *   # expires foo
     *   $cache->expire('foo');
     *
     * @param   string $arg a single key.
     */
    public function expire($arg)
    {
        $key = $this->getCacheKey($arg);
        $this->redis->unlink($key);
    }

    /**
     * Retrieve item from the server.
     *
     * Example:
     *
     *   # reads foo
     *   $foo = $cache->reads('foo');
     *
     * @param  string $arg a single key
     * @return mixed  the previously stored data if an item with such a key
     *                exists on the server or FALSE on failure.
     */
    public function read($arg)
    {
        $key = $this->getCacheKey($arg);
        return $this->redis->get($key);
    }

    /**
     * Store data at the server.
     *
     * @param string   the item's key.
     * @param string   the item's content.
     * @param int      the item's expiry time in seconds. Defaults to 12h.
     * @return mixed  returns TRUE on success or FALSE on failure.
     */
    public function write($name, $content, $expire = self::DEFAULT_EXPIRATION)
    {
        $key = $this->getCacheKey($name);
        return $this->redis->setEx($key, $expire, $content);
    }

    /**
     * Expire all items from the cache.
     */
    public function flush()
    {
        $pattern = $this->getCacheKey('*');
        foreach ($this->redis->keys($pattern) as $key) {
            $this->redis->unlink($key);
        }
    }

    /**
     * @param string $method Method to call
     * @param array $args Arguments to pass
     * @return false|mixed
     */
    public function __call($method, $args)
    {
        if (is_callable([$this->redis, $method])) {
            return call_user_func_array([$this->redis, $method], $args);
        }
        throw new BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Return statistics.
     *
     * @StudipCache::getStats()
     *
     * @return array|array[]
     */
    public function getStats(): array
    {
        $stats = $this->redis->info();
        $stats['size'] = count($this->redis->keys($this->getCacheKey('*')));
        return ["{$this->redis->getHost()}:{$this->redis->getPort()}" => $stats];
    }

    /*
     * Return the Vue component name and props that handle configuration.
     *
     * @see StudipCache::getConfig()
     *
     * @return array
     */
    public static function getConfig(): array
    {
        $currentCache = Config::get()->SYSTEMCACHE;

        // Set default config for this cache
        $currentConfig = [
            'hostname' => '',
            'port' => null
        ];

        // If this cache is set as system cache, use config from global settings.
        if ($currentCache['type'] == __CLASS__) {
            $currentConfig = $currentCache['config'];
            $currentConfig['port'] = $currentConfig['port'] ? (int) $currentConfig['port'] : null;
        }

        return [
            'component' => 'RedisCacheConfig',
            'props' => $currentConfig
        ];
    }
}
