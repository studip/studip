<?php
/**
 * Migration for proxied cache operations.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.3
 */
class AddCacheOperationsTable extends Migration
{
    public function description()
    {
        return 'Creates the database table for proxied cache operations';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `cache_operations` (
                      `cache_key` VARCHAR(256) NOT NULL DEFAULT '',
                      `operation` CHAR(6) NOT NULL DEFAULT '',
                      `parameters` TEXT NOT NULL,
                      `mkdate` INT(11) UNSIGNED NOT NULL,
                      `chdate` INT(11) UNSIGNED NOT NULL,
                      PRIMARY KEY (`cache_key`(200), `operation`)
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DROP TABLE `cache_operations`";
        DBManager::get()->exec($query);
    }
}
