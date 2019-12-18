<?php

namespace Helper;

use Codeception\Module as CodeceptionModule;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\TestInterface;

require_once 'StudipPDO.php';

class StudipDb extends CodeceptionModule
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
    protected $sql = [];

    /**
     * @var array
     */
    protected $config = [
        'populate' => true,
        'dump' => null,
    ];

    /**
     * @var string
     */
    public $sqlToRun;

    /**
     * @var array
     */
    protected $requiredFields = ['dsn', 'user', 'password'];

    public function _initialize()
    {
        if ($this->config['dump'] && $this->config['populate']) {
            $this->readSql();
        }

        $this->connect();

        // starting with loading dump
        if ($this->config['populate']) {
            $this->loadDump();
        }
    }

    private function readSql()
    {
        if (!file_exists(Configuration::projectDir().$this->config['dump'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nFile with dump doesn't exist.\n"
                .'Please, check path for sql file: '
                .$this->config['dump']
            );
        }

        $sql = file_get_contents(Configuration::projectDir().$this->config['dump']);

        // remove C-style comments (except MySQL directives)
        $sql = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $sql);

        if (!empty($sql)) {
            $this->sql = preg_split('/\r\n|\n|\r/', $sql, -1, PREG_SPLIT_NO_EMPTY);
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

    public function _before(TestInterface $test)
    {
        $this->dbh->beginTransaction();
        parent::_before($test);
    }

    public function _after(TestInterface $test)
    {
        $this->dbh->rollBack();
        parent::_after($test);
    }

    protected function loadDump()
    {
        if (!$this->sql) {
            return;
        }
        try {
            $this->loadSql($this->sql);
        } catch (\PDOException $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage()."\nSQL query being executed: ".$this->sqlToRun
            );
        }
    }

    protected function loadSql($sql)
    {
        $query = '';
        $delimiter = ';';
        $delimiterLength = 1;

        foreach ($sql as $sqlLine) {
            if (preg_match('/DELIMITER ([\;\$\|\\\\]+)/i', $sqlLine, $match)) {
                $delimiter = $match[1];
                $delimiterLength = strlen($delimiter);
                continue;
            }

            $parsed = $this->sqlLine($sqlLine);
            if ($parsed) {
                continue;
            }

            $query .= "\n".rtrim($sqlLine);

            if (substr($query, -1 * $delimiterLength, $delimiterLength) == $delimiter) {
                $this->sqlToRun = substr($query, 0, -1 * $delimiterLength);
                $this->sqlQuery($this->sqlToRun);
                $query = '';
            }
        }
    }

    protected function sqlLine($sql)
    {
        $sql = trim($sql);

        return
            $sql === ''
            || $sql === ';'
            || preg_match('~^((--.*?)|(#))~s', $sql);
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec($query);
    }
}
