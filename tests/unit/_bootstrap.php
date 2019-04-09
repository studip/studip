<?php

// Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

// set error reporting
error_reporting(E_ALL & ~E_NOTICE);
if (version_compare(phpversion(), '5.4', '>=')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}

// set include path
$inc_path = ini_get('include_path');
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../..';
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../../config';
ini_set('include_path', $inc_path);

// load varstream for easier filesystem testing
require_once 'varstream.php';

define("TEST_FIXTURES_PATH", dirname(dirname(__FILE__)) . "/fixtures/");

require 'lib/classes/StudipAutoloader.php';
require 'lib/functions.php';

$STUDIP_BASE_PATH = realpath(dirname(__FILE__) . '/../..');

StudipAutoloader::register();
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'activities', 'Studip\\Activity');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'models');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'exceptions');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'sidebar');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'helpbar');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'engine');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'core');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'db');

// load config-variables
StudipFileloader::load('config/config_local.inc.php', $GLOBALS);

require_once 'vendor/yaml/lib/sfYamlParser.php';
$yaml = new \sfYamlParser();
$config = $yaml->parse(file_get_contents(dirname(__FILE__) .'/../unit.suite.yml'));

// connect to database if configured
if (isset($config['modules']['config']['Db'])) {
    DBManager::getInstance()->setConnection('studip',
        $config['modules']['config']['Db']['dsn'],
        $config['modules']['config']['Db']['user'],
        $config['modules']['config']['Db']['password']);
} else {
    //DBManager::getInstance()->setConnection('studip', 'sqlite://'. $GLOBALS ,'', '');
}

// minimal support for running with PHPUnit 6.x
if (!class_exists('PHPUnit_Framework_TestCase')) {
    abstract class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase
    {
        public function getMock($classname)
        {
            return $this->createMock($classname);
        }

        public function setExpectedException($exception)
        {
            return $this->expectException($exception);
        }
    }
}

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
