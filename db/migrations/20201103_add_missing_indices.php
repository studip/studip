<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @see https://develop.studip.de/trac/ticket/10882
 */
class AddMissingIndices extends Migration
{
    public function description()
    {
        return 'Adds missing indices for tables `activities` and `plugins_activated`';
    }

    public function up()
    {
        $query = "ALTER TABLE `activities`
                  ADD INDEX `object_id` (`object_id`(32))";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `plugins_activated`
                  ADD INDEX `range` (`range_id`, `range_type`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `activities`
                  DROP INDEX `object_id`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `plugins_activated`
                  DROP INDEX `range`";
        DBManager::get()->exec($query);
    }
}
