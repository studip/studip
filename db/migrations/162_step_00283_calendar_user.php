<?php
class Step00283CalendarUser extends Migration {

    function description() {
        return 'New table to manage access to user calendars. It replaces the former access management in contacts and contact groups.';
    }

    function up() {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `calendar_user` (
            `owner_id` varchar(32) NOT NULL,
            `user_id` varchar(32) NOT NULL,
            `permission` int(2) NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,
            PRIMARY KEY (`owner_id`,`user_id`)
        ) ENGINE=MyISAM");
    }

    function down() {
        DBManager::get()->execute('DROP TABLE IF EXISTS calendar_user');
    }

}
