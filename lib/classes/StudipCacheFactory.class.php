<?php
# Lifter007: TODO

/**
 * This factory retrieves the instance of StudipCache configured for use in
 * this Stud.IP installation.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marco Diedrich (mdiedric@uos)
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright 2007 (c) Authors
 * @since     1.6
 * @license   GPL2 or any later version
 */

class StudipCacheFactory
{
    /**
     * the default cache class
     *
     * @var string
     */
    const DEFAULT_CACHE_CLASS = 'StudipDbCache';

    /**
     * singleton instance
     *
     * @var StudipCache
     */
    private static $cache;


    /**
     * config instance
     *
     * @var Config
     */
    private static $config = null;


    /**
     * Returns the currently used config instance
     *
     * @return Config        an instance of class Config used by this factory to
     *                       determine the class of the actual implementation of
     *                       the StudipCache interface; if no config was set, it
     *                       returns the instance returned by Config#getInstance
     * @see Config
     */
    public static function getConfig()
    {
        return is_null(self::$config) ? Config::getInstance() : self::$config;
    }


    /**
     * @param    Config       an instance of class Config which will be used to
     *                        determine the class of the implementation of interface
     *                        StudipCache
     *
     * @return void
     */
    public static function setConfig($config)
    {
        self::$config = $config;
        self::$cache = NULL;
    }


    /**
     * Configure the file, class and arguments used for instantiation of the
     * StudipCache instance. After sending this method, the previously used cache
     * instance is voided and a new instance will be created on demand.
     *
     * @param    string             the absolute path to the implementing class
     * @param    string             the name of the class
     * @param    array              an array of custom arguments
     *
     * @return void
     */
    public static function configure($file, $class, $arguments)
    {
        # TODO encoding for strings... but probably the caller should care..
        $arguments = json_encode($arguments);

        // strip leading STUDIP_BASE_PATH from file path
        $file = studip_relative_path($file);

        self::unconfigure();

        $cfg = self::getConfig();

        $cfg->create('cache_class', [
            'comment' => 'Pfad der Datei, die die StudipCache-Klasse enthält',
            'value'   => $class]);
        $cfg->create('cache_class_file', [
            'comment' => 'Klassenname des zu verwendenden StudipCaches',
            'value'   => $file]);
        $cfg->create('cache_init_args', [
            'comment' => 'JSON-kodiertes Array von Argumenten für die Instanziierung der StudipCache-Klasse',
            'value'   => $arguments]);

        $cfg->store('cache_class', $class);
        $cfg->store('cache_class_file', $file);
        $cfg->store('cache_init_args', $arguments);
    }


    /**
     * Resets the configuration and voids the cache instance.
     *
     * @return void
     */
    public static function unconfigure()
    {
        $cfg = self::getConfig();

        $cfg->delete('cache_class');
        $cfg->delete('cache_class_file');
        $cfg->delete('cache_init_args');

        self::$cache = NULL;
    }


    /**
     * Returns a cache instance.
     *
     * @param bool $apply_proxied_operations Whether or not to apply any
     *                                       proxied (disable this in tests!)
     * @return StudipCache the cache instance
     */
    public static function getCache($apply_proxied_operations = true)
    {
        if (is_null(self::$cache)) {
            $proxied = false;

            if (!$GLOBALS['CACHING_ENABLE']) {
                self::$cache = new StudipNullCache();

                // Proxy cache operations if CACHING_ENABLE is different from the globally set
                // caching value. This should only be the case in cli mode.
                if (isset($GLOBALS['GLOBAL_CACHING_ENABLE']) && $GLOBALS['GLOBAL_CACHING_ENABLE']) {
                    $proxied = true;
                }
            } else {
                try {
                    $class = self::loadCacheClass();
                    $args = self::retrieveConstructorArguments();
                    self::$cache = self::instantiateCache($class, $args);
                } catch (Exception $e) {
                    error_log(__METHOD__ . ': ' . $e->getMessage());
                    PageLayout::addBodyElements(MessageBox::error(__METHOD__ . ': ' . $e->getMessage()));
                    $class = self::DEFAULT_CACHE_CLASS;
                    self::$cache = new $class();
                }
            }

            // If proxy should be used, inject it. Otherwise apply pending
            // operations, if any.
            if ($proxied) {
                self::$cache = new StudipCacheProxy(self::$cache);
            } elseif ($GLOBALS['CACHING_ENABLE'] && $apply_proxied_operations) {
                // Even if the above condition will try to eliminate most
                // failures, the following operation still needs to be wrapped
                // in a try/catch block. Otherwise there are no means to
                // execute migration 166 which creates the neccessary tables
                // for said operation.
                try {
                    StudipCacheOperation::apply(self::$cache);
                } catch (Exception $e) {
                }
            }
        }

        return self::$cache;
    }


    /**
     * Load configured cache class and return its name.
     *
     * @return string  the name of the configured cache class
     */
    public static function loadCacheClass()
    {
        $cfg = self::getConfig();
        $cache_class_file = $cfg->getValue('cache_class_file');
        $cache_class      = $cfg->getValue('cache_class');

        # default class
        if (is_null($cache_class)) {
            $version = new DBSchemaVersion();
            if (!$version->contains(224)) {
                // db cache is not yet available, use StudipNullCache
                return 'StudipNullCache';
            }

            return self::DEFAULT_CACHE_CLASS;
        }

        # already loaded
        if (class_exists($cache_class)) {
            return $cache_class;
        }

        $loaded = @include $cache_class_file;
        if ($loaded === FALSE || !class_exists($cache_class)) {
            # TODO (mlunzena) a more specific exception would be welcome here
            throw new Exception("Could not find class: '$cache_class'");
        }

        return $cache_class;
    }

    /**
     * Return an array of arguments required for instantiation of the cache
     * class.
     *
     * @return array  the array of arguments
     */
    public static function retrieveConstructorArguments()
    {
        $cfg_args = self::getConfig()->getValue('cache_init_args');
        return isset($cfg_args) ? json_decode($cfg_args, TRUE) : [];
    }

    /**
     * Return an instance of a given class using some arguments
     *
     * @param  string  the name of the class
     * @param  array   an array of arguments to be used by the constructor
     *
     * @return StudipCache  an instance of the specified class
     */
    public static function instantiateCache($class, $arguments)
    {
        $reflection_class = new ReflectionClass($class);
        return sizeof($arguments)
               ? $reflection_class->newInstanceArgs($arguments)
               : $reflection_class->newInstance();
    }
}
