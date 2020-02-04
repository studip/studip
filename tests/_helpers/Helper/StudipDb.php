<?php

namespace Helper;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;

require_once 'StudipPDO.php';

class StudipDb extends \Codeception\Module
{
    /**
     * @api
     *
     * @var
     */
    public $dbh;

    /**
     * @var array
     */
    protected $config = [
    ];

    /**
     * @var array
     */
    protected $requiredFields = ['dsn', 'user', 'password'];

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initialize()
    {
        $this->checkConfig('dsn', 'DB_STUDIP_HOST');
        $this->checkConfig('dsn', 'DB_STUDIP_DATABASE');
        $this->checkConfig('user', 'DB_STUDIP_USER');
        $this->checkConfig('password', 'DB_STUDIP_PASSWORD');

        $this->connect();
    }

    private function checkConfig($configKey, $name)
    {
        if (strstr($this->config[$configKey], '%'.$name.'%')) {
            throw new ModuleConfigException(__CLASS__, 'You have to provide a '.$name.' either as ENV variable or in config_local.inc.php!');
        }
    }

    private function connect()
    {
        try {
            $this->dbh = null;
            $this->dbh = new \StudipPDO($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage().' while creating PDO connection');
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _before(\Codeception\TestInterface $test)
    {
        $this->dbh->beginTransaction();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _after(\Codeception\TestInterface $test)
    {
        $this->dbh->rollBack();
    }
}
