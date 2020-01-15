<?php

// set include path
$inc_path = ini_get('include_path');
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../..';
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../../config';
ini_set('include_path', $inc_path);

define("TEST_FIXTURES_PATH", dirname(dirname(__FILE__)) . "/fixtures/");

require 'lib/classes/StudipAutoloader.php';
require 'lib/functions.php';

$STUDIP_BASE_PATH = realpath(dirname(__FILE__) . '/../..');

StudipAutoloader::register();
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'models');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'resources');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'resources');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'exceptions');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'exceptions' . DIRECTORY_SEPARATOR . 'resources');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'migrations');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'resources');

// load config-variables
StudipFileloader::load(
    'config_defaults.inc.php config_local.inc.php',
    $GLOBALS,
    compact('STUDIP_BASE_PATH', 'ABSOLUTE_URI_STUDIP', 'ASSETS_URL', 'CANONICAL_RELATIVE_PATH_STUDIP'),
    true
);

require_once 'vendor/flexi/lib/flexi.php';
$GLOBALS['template_factory'] = new Flexi_TemplateFactory(dirname(dirname(__DIR__)) . '/templates');

// create "fake" cache class
if (!class_exists('StudipArrayCache')) {
    class StudipArrayCache implements StudipCache {
        public $data = [];

        function expire($key)
        {
            unset($this->data);
        }

        function flush()
        {
            $this->data = [];
        }

        function read($key)
        {
            return $this->data[$key];
        }

        function write($name, $content, $expire = 43200)
        {
            return ($this->data[$name] = $content);
        }
    }
}

// SimpleORMapFake
if (!class_exists('StudipTestHelper')) {
    class StudipTestHelper
    {
        static function set_up_tables($tables)
        {
            // first step, set fake cache
            $testconfig = new Config(['cache_class' => 'StudipArrayCache']);
            Config::set($testconfig);
            StudipCacheFactory::setConfig($testconfig);

            $GLOBALS['CACHING_ENABLE'] = true;

            $cache = StudipCacheFactory::getCache(false);

            // second step, expire table scheme
            SimpleORMap::expireTableScheme();

            $schemes = [];

            foreach ($tables as $db_table) {
                include TEST_FIXTURES_PATH."simpleormap/$db_table.php";
                $db_fields = $pk = [];
                foreach ($result as $rs) {
                    $db_fields[mb_strtolower($rs['name'])] = [
                        'name'    => $rs['name'],
                        'null'    => $rs['null'],
                        'default' => $rs['default'],
                        'type'    => $rs['type'],
                        'extra'   => $rs['extra']
                    ];
                    if ($rs['key'] == 'PRI'){
                        $pk[] = mb_strtolower($rs['name']);
                    }
                }
                $schemes[$db_table]['db_fields'] = $db_fields;
                $schemes[$db_table]['pk'] = $pk;
            }

            $cache->write('DB_TABLE_SCHEMES', serialize($schemes));
        }

        static function tear_down_tables()
        {
            SimpleORMap::expireTableScheme();
            Config::set(null);

            StudipCacheFactory::setConfig(null);
            $GLOBALS['CACHING_ENABLE'] = false;
        }
    }
}
