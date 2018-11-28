<?php

/**
 * Migration for Ticket #7059
 *
 * @author  <mlunzena@uos.de>
 */
class AddEtaskTables extends Migration
{

    public function description()
    {
        return 'Adds the eAufgaben tables.';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_tasks` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(64) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `task` TEXT NOT NULL,
            `user_id` CHAR(32) NOT NULL,
            `mkdate` INT(11) NOT NULL,
            `chdate` INT(11) NOT NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_task_tags` (
            `task_id` INT(11) NOT NULL,
            `user_id` CHAR(32) NOT NULL,
            `tag` VARCHAR(64) NOT NULL,
            PRIMARY KEY (`task_id`, `user_id`, `tag`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_tests` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `user_id` CHAR(32) NOT NULL,
            `mkdate` INT(11) NOT NULL,
            `chdate` INT(11) NOT NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_test_tags` (
            `test_id` INT(11) NOT NULL,
            `user_id` CHAR(32) NOT NULL,
            `tag` VARCHAR(64) NOT NULL,
            PRIMARY KEY (`test_id`, `user_id`, `tag`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_test_tasks` (
            `test_id` INT(11) NOT NULL,
            `task_id` INT(11) NOT NULL,
            `position` INT(11) NOT NULL,
            `points` FLOAT NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`test_id`, `task_id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_assignments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `test_id` INT(11) NOT NULL,
            `range_type` ENUM('course', 'global', 'group', 'institute', 'user') NULL,
            `range_id` CHAR(32) NULL,
            `type` VARCHAR(64) NOT NULL,
            `start` INT(11) NULL,
            `end` INT(11) NULL,
            `active` TINYINT(1) NOT NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_assignment_ranges` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `assignment_id` INT(11) NOT NULL,
            `range_type` ENUM('course', 'global', 'group', 'institute', 'user') NOT NULL,
            `range_id` CHAR(32) NOT NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `assignment_id` (`assignment_id`,`range_type`,`range_id`)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_assignment_attempts` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `assignment_id` INT(11) NOT NULL,
            `user_id` CHAR(32) NOT NULL,
            `start` INT(11) NULL,
            `end` INT(11) NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        $db->exec("
        CREATE TABLE IF NOT EXISTS `etask_responses` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `assignment_id` INT(11) NOT NULL,
            `task_id` INT(11) NOT NULL,
            `user_id` CHAR(32) NOT NULL,
            `response` TEXT NOT NULL,
            `state` TINYINT(1) NULL,
            `points` FLOAT NULL,
            `feedback` TEXT NULL,
            `grader_id` CHAR(32) NULL,
            `mkdate` INT(11) NOT NULL,
            `chdate` INT(11) NOT NULL,
            `options` TEXT NOT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");
    }

    public function down()
    {
        foreach ([
            'etask_tasks', 'etask_task_tags',
            'etask_tests', 'etask_test_tags',
            'etask_test_tasks', 'etask_assignments',
            'etask_assignment_attempts', 'etask_responses'
        ] as $table) {
            # $db->exec('DROP TABLE IF EXISTS `' . $table . '`');
        }
    }
}
