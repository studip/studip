<?php
/**
 * Migration for TIC #5145
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 *
 * @see https://develop.studip.de/trac/ticket/5415
 */
class Tic5415PluginAssets extends Migration
{
    function description()
    {
        return 'Creates the database tables that store the plugin assets information';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `plugin_assets` (
                      `asset_id` char(32) NOT NULL DEFAULT '',
                      `plugin_id` int(10) unsigned NOT NULL,
                      `type` enum('css') NOT NULL DEFAULT 'css',
                      `filename` varchar(255) NOT NULL DEFAULT '',
                      `storagename` varchar(255) NOT NULL DEFAULT '',
                      `size` int(11) unsigned DEFAULT NULL,
                      `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
                      `chdate` int(11) unsigned NOT NULL DEFAULT '0',
                      PRIMARY KEY (`asset_id`)
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE `plugin_assets`");
    }
}
