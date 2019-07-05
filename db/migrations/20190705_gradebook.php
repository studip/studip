<?php
/**
 * Gradebook API for Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class Gradebook extends Migration
{
    public function description()
    {
        return 'initial database setup for Gradebook API';
    }

    public function up()
    {
        $db = DBManager::get();

        $sql =
             "CREATE TABLE `grading_definitions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `course_id` char(32) COLLATE latin1_bin NOT NULL,
              `item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `tool` varchar(64) COLLATE latin1_bin NOT NULL,
              `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `position` int(11) NOT NULL DEFAULT '0',
              `weight` float UNSIGNED NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `course_id` (`course_id`),
              KEY `tool` (`tool`)
              ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        $db->exec($sql);

        $sql =
             'CREATE TABLE `grading_instances` (
              `definition_id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` char(32) COLLATE latin1_bin NOT NULL,
              `rawgrade` decimal(6,5) UNSIGNED NOT NULL,
              `feedback` varchar(255) COLLATE utf8mb4_unicode_ci,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`definition_id`,`user_id`)
              ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC';
        $db->exec($sql);

        // install as core plugin
        $sql = "INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
                VALUES ('GradebookModule', 'Gradebook', 'StandardPlugin,SystemPlugin', 'yes', 1)";
        $db->exec($sql);

        $sql = "INSERT INTO roles_plugins (roleid, pluginid) SELECT roleid, ? FROM roles WHERE `system` = 'y'";
        $db->execute($sql, [$db->lastInsertId()]);
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DELETE plugins, roles_plugins FROM plugins LEFT JOIN roles_plugins USING(pluginid)
                   WHERE pluginclassname = 'GradebookModule'");

        $db->exec('DROP TABLE grading_definitions, grading_instances');

        SimpleORMap::expireTableScheme();
    }
}
