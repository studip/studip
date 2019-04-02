<?php
class ContactRework extends Migration
{
    public function description()
    {
        return 'Make the usage of contacts more simple';
    }

    public function up()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS contact_userinfo");
        DBManager::get()->exec("CREATE TABLE `contact_new` (
          `owner_id` varchar(32) NOT NULL DEFAULT '',
          `user_id` varchar(32) NOT NULL DEFAULT '',
          `calpermission` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
          PRIMARY KEY (`owner_id`,`user_id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=MyISAM");
        DBManager::get()->exec("INSERT INTO `contact_new` SELECT `owner_id`,`user_id`, MAX(`calpermission`) FROM `contact` GROUP BY `owner_id`,`user_id`");
        DBManager::get()->exec("DROP TABLE `contact`");
        DBManager::get()->exec("RENAME TABLE `contact_new` TO `contact`");

        Config::get()->delete("FOAF_ENABLE");
        Config::get()->delete("FOAF_SHOW_IDENTITY");
    }

    public function down()
    {
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `contact_userinfo` (
            `userinfo_id` varchar(32) NOT NULL DEFAULT '',
            `contact_id` varchar(32) NOT NULL DEFAULT '',
            `name` varchar(255) NOT NULL DEFAULT '',
            `content` text NOT NULL,
            `priority` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`userinfo_id`),
            KEY `contact_id` (`contact_id`),
            KEY `priority` (`priority`)
        ) ENGINE=MyISAM;");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `contact` (
              `contact_id` varchar(32) NOT NULL DEFAULT '',
              `owner_id` varchar(32) NOT NULL DEFAULT '',
              `user_id` varchar(32) NOT NULL DEFAULT '',
              `buddy` tinyint(4) NOT NULL DEFAULT '1',
              `calpermission` tinyint(2) unsigned NOT NULL DEFAULT '1'
            ) ENGINE=MyISAM;");
        DBManager::get()->exec("ALTER TABLE contact ADD COLUMN buddy tinyint(4) NOT NULL DEFAULT '1'");
    }
}
