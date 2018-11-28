<?php
class Tic4454RoomrequestOptions extends Migration
{
    function description()
    {
        return 'create table resources_requests_user_status, change table resources_requests';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE  `resources_requests` ADD  `last_modified_by` VARCHAR( 32 ) NOT NULL DEFAULT  '' AFTER  `user_id`");
        $db->exec("ALTER TABLE  `resources_requests` ADD  `reply_recipients` ENUM(  'requester',  'lecturer' ) NOT NULL DEFAULT  'requester' AFTER `reply_comment`");
        $db->exec("CREATE TABLE IF NOT EXISTS `resources_requests_user_status` (
                  `request_id` char(32) NOT NULL DEFAULT '',
                  `user_id` char(32) NOT NULL DEFAULT '',
                  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`request_id`,`user_id`)
                ) ENGINE=MyISAM");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `resources_requests` DROP `last_modified_by`");
        DBManager::get()->exec("ALTER TABLE `resources_requests` DROP `reply_recipients`");
        DBManager::get()->exec("DROP TABLE IF EXISTS `resources_requests_user_status`");
    }
}
