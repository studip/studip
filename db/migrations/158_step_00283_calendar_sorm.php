<?php
class Step00283CalendarSorm extends Migration
{
    public function description()
    {
        return 'Normalise representation of calendar events to get real group events and use SORM.';
    }

    public function up()
    {
        DBManager::get()->exec(
                "CREATE TABLE IF NOT EXISTS `event_data` (
                `event_id` varchar(32) NOT NULL,
                `autor_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) DEFAULT NULL,
                `uid` varchar(255) NOT NULL,
                `start` int(10) unsigned NOT NULL DEFAULT '0',
                `end` int(10) unsigned NOT NULL DEFAULT '0',
                `summary` varchar(255) NOT NULL DEFAULT '',
                `description` text,
                `class` enum('PUBLIC','PRIVATE','CONFIDENTIAL') NOT NULL DEFAULT 'PRIVATE',
                `categories` tinytext,
                `category_intern` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `location` tinytext,
                `ts` int(10) unsigned NOT NULL DEFAULT '0',
                `linterval` smallint(5) unsigned DEFAULT NULL,
                `sinterval` smallint(5) unsigned DEFAULT NULL,
                `wdays` varchar(7) DEFAULT NULL,
                `month` tinyint(3) unsigned DEFAULT NULL,
                `day` tinyint(3) unsigned DEFAULT NULL,
                `rtype` enum('SINGLE','DAILY','WEEKLY','MONTHLY','YEARLY') NOT NULL DEFAULT 'SINGLE',
                `duration` smallint(5) unsigned NOT NULL DEFAULT '0',
                `count` smallint(5) DEFAULT '0',
                `expire` int(10) unsigned NOT NULL DEFAULT '0',
                `exceptions` text,
                `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
                `chdate` int(10) unsigned NOT NULL DEFAULT '0',
                `importdate` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`event_id`),
                UNIQUE KEY `uid` (`uid`),
                KEY `autor_id` (`autor_id`)
                ) ENGINE=MyISAM");
        DBManager::get()->execute(
                "CREATE TABLE IF NOT EXISTS `calendar_event` (
                `range_id` varchar(32) NOT NULL,
                `event_id` varchar(32) NOT NULL,
                `group_status` tinyint(4) unsigned NOT NULL DEFAULT '0',
                `chdate` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`range_id`, `event_id`)
                ) ENGINE=MYISAM");
        DBManager::get()->execute(
                "INSERT IGNORE INTO `event_data`
                (`event_id`, `autor_id`, `editor_id`, `uid`, `start`, `end`,
                `summary`, `description`, `class`, `categories`, `category_intern`,
                `priority`, `location`, `ts`, `linterval`, `sinterval`, `wdays`,
                `month`, `day`, `rtype`, `duration`, `count`, `expire`, `exceptions`,
                `mkdate`, `chdate`, `importdate`)
                SELECT `event_id`, `autor_id`, NULLIF(`editor_id`, ''),
                `uid`, `start`, `end`,
                `summary`, `description`, `class`, `categories`, `category_intern`,
                `priority`, `location`, `ts`, `linterval`, `sinterval`, `wdays`,
                `month`, `day`, `rtype`, `duration`, `count`, `expire`, `exceptions`,
                `mkdate`, `chdate`, `importdate`
                FROM calendar_events WHERE 1 GROUP BY `event_id`");
        DBManager::get()->execute(
                "INSERT IGNORE INTO `calendar_event`
                (`range_id`, `event_id`, `chdate`)
                SELECT `range_id`, `event_id`, `chdate`
                FROM `calendar_events` WHERE 1");
        DBManager::get()->exec('DROP TABLE IF EXISTS calendar_events');

        DBManager::get()->exec('ALTER TABLE contact CHANGE '
                . "`calpermission` `calpermission` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
        DBManager::get()->exec("UPDATE IGNORE contact SET calpermission = '0' "
                . "WHERE calpermission = '1'");
    }

    public function down()
    {
        DBManager::get()->execute(
                "CREATE TABLE IF NOT EXISTS `calendar_events` (
                `event_id` varchar(32) NOT NULL DEFAULT '',
                `range_id` varchar(32) NOT NULL DEFAULT '',
                `autor_id` varchar(32) NOT NULL DEFAULT '',
                `editor_id` varchar(32) NOT NULL,
                `uid` varchar(255) NOT NULL DEFAULT '',
                `start` int(10) unsigned NOT NULL DEFAULT '0',
                `end` int(10) unsigned NOT NULL DEFAULT '0',
                `summary` varchar(255) NOT NULL DEFAULT '',
                `description` text,
                `class` enum('PUBLIC','PRIVATE','CONFIDENTIAL') NOT NULL DEFAULT 'PRIVATE',
                `categories` tinytext,
                `category_intern` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `location` tinytext,
                `ts` int(10) unsigned NOT NULL DEFAULT '0',
                `linterval` smallint(5) unsigned DEFAULT NULL,
                `sinterval` smallint(5) unsigned DEFAULT NULL,
                `wdays` varchar(7) DEFAULT NULL,
                `month` tinyint(3) unsigned DEFAULT NULL,
                `day` tinyint(3) unsigned DEFAULT NULL,
                `rtype` enum('SINGLE','DAILY','WEEKLY','MONTHLY','YEARLY') NOT NULL DEFAULT 'SINGLE',
                `duration` smallint(5) unsigned NOT NULL DEFAULT '0',
                `count` smallint(5) DEFAULT '0',
                `expire` int(10) unsigned NOT NULL DEFAULT '0',
                `exceptions` text,
                `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
                `chdate` int(10) unsigned NOT NULL DEFAULT '0',
                `importdate` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`event_id`),
                UNIQUE KEY `uid_range` (`uid`,`range_id`),
                KEY `range_id` (`range_id`),
                KEY `autor_id` (`autor_id`)
              ) ENGINE=MyISAM;");
        DBManager::get()->execute(
                "INSERT INTO `calendar_events`
                (`event_id`, `range_id`, `autor_id`, `editor_id`, `uid`, `start`, `end`,
                `summary`, `description`, `class`, `categories`, `category_intern`,
                `priority`, `location`, `ts`, `linterval`, `sinterval`, `wdays`,
                `month`, `day`, `rtype`, `duration`, `count`, `expire`, `exceptions`,
                `mkdate`, `chdate`, `importdate`)
                SELECT e.`event_id`, ce.`range_id`, e.`autor_id`, IFNULL(e.`editor_id`, ''),
                e.`uid`, e.`start`, e.`end`,
                e.`summary`, e.`description`, e.`class`, e.`categories`, e.`category_intern`,
                e.`priority`, e.`location`, e.`ts`, e.`linterval`, e.`sinterval`, e.`wdays`,
                e.`month`, e.`day`, e.`rtype`, e.`duration`, e.`count`, e.`expire`, e.`exceptions`,
                e.`mkdate`, e.`chdate`, e.`importdate`
                FROM `calendar_event` INNER JOIN `event` USING(`event_id`)
                WHERE 1");
        DBManager::get()->exec('DROP TABLE IF EXISTS calendar_event');
        DBManager::get()->exec('DROP TABLE IF EXISTS event_data');

        DBManager::get()->exec('ALTER TABLE contact CHANGE '
                . "`calpermission` `calpermission` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1'");
        DBManager::get()->exec("UPDATE IGNORE contact SET calpermission = '1' "
                . "WHERE calpermission = '0'");
    }
}
