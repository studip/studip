<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @see https://develop.studip.de/trac/ticket/10882
 */
class AddIndexForPluginsActivated extends Migration
{
    public function description()
    {
        return 'Adds missing index over `range_id` and `range_type` for table `plugins_activated`';
    }

    public function up()
    {
        $query = "ALTER TABLE `plugins_activated`
                  ADD INDEX `range` (`range_id`, `range_type`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `plugins_activated`
                  DROP INDEX `range`";
        DBManager::get()->exec($query);
    }
}
