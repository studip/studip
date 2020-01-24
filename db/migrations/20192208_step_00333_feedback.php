<?php

/**
 * Feedback Elements for Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Nils Gehrke <nils.gehrke@uni-goettingen.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

class Step00333Feedback extends Migration
{
    public function description()
    {
        return 'initial database setup for feedback elements';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE IF NOT EXISTS `feedback` (
            `id` int NOT NULL AUTO_INCREMENT,
            `user_id` varchar(32) NOT NULL,
            `range_id` varchar(32) NOT NULL,
            `range_type` varchar(32) NOT NULL,
            `course_id` varchar(32) NOT NULL,
            `question` text NOT NULL,
            `description` text NOT NULL,
            `mode` int NOT NULL,
            `results_visible` tinyint NOT NULL,
            `commentable` tinyint NOT NULL,
            `mkdate` int NOT NULL,
            `chdate` int NOT NULL,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            INDEX `idx_range` (`range_id`,`range_type`),
            KEY `course_id` (`course_id`)
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS `feedback_entries` (
            `id` int NOT NULL AUTO_INCREMENT,
            `feedback_id` int NOT NULL,
            `user_id` varchar(32) NOT NULL,
            `comment` text NOT NULL,
            `rating` int NOT NULL,
            `mkdate` text NOT NULL,
            `chdate` text NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`),
            KEY `user_id` (`user_id`)
        )");

        // install as core plugin
        $db->exec(
            "INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
            VALUES ('FeedbackModule', 'Feedback','StandardPlugin,SystemPlugin','yes', 1)"
        );

        $db->execute(
            "INSERT INTO roles_plugins (roleid, pluginid) 
            SELECT roleid, ? 
            FROM roles 
            WHERE `system` = 'y'",
            [$db->lastInsertId()]
        );

        // install config settings
        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
      
      $stmt->execute([
            'name'        => 'FEEDBACK_ADMIN_PERM',
            'description' => 'Voreinstellung für Berechtigungslevel, um Einstellung zu Feedback-Elementen zu verwalten',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'tutor'
        ]);
      
      $stmt->execute([
            'name'        => 'FEEDBACK_CREATE_PERM',
            'description' => 'Voreinstellung für Berechtigungslevel, um Feedback-Elemente anzulegen.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'tutor'
        ]);

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec('DROP TABLE feedback_entries');
        $db->exec('DROP TABLE feedback');

        $db->exec(
            "DELETE plugins, roles_plugins 
            FROM plugins LEFT JOIN roles_plugins USING(pluginid)
            WHERE pluginclassname = 'FeedbackModule'"
        );

        SimpleORMap::expireTableScheme();
    }
}
