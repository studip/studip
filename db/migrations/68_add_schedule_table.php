<?php

class AddScheduleTable extends Migration
{
    function description ()
    {
        return 'add schedule db-table for new schedule';
    }

    function up ()
    {
        // create new multi-purpose schedule table
        DBManager::get()->exec("
            CREATE TABLE `schedule` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `start` smallint(6) NOT NULL COMMENT 'start hour and minutes',
                `end` smallint(6) NOT NULL COMMENT 'end hour and minutes',
                `day` tinyint(4) NOT NULL COMMENT 'day of week, 0-6',
                `title` varchar(255) NOT NULL,
                `content` varchar(255) NOT NULL,
                `color` varchar(7) NOT NULL COMMENT 'color, rgb in hex',
                `user_id` varchar(32) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`)
                ) ENGINE=MyISAM;
        ");

        DBManager::get()->exec("
            CREATE TABLE `schedule_seminare` (
                `user_id` VARCHAR( 32 ) NOT NULL ,
                `seminar_id` VARCHAR( 32 ) NOT NULL ,
                `metadate_id` VARCHAR( 32 ) NOT NULL ,
                `visible` BOOLEAN NOT NULL DEFAULT '1' ,
                `color` VARCHAR( 7 ) NULL COMMENT 'color, rgb in hex',
                PRIMARY KEY ( `user_id` , `seminar_id`, `metadate_id` )
            ) ENGINE=MyISAM;
        ");

        // move old "virtual" entries to new table
        $db = DBManager::get()->query("SELECT sus.*, metadata_dates FROM seminar_user_schedule as sus
            LEFT JOIN seminare as s ON (s.Seminar_id = sus.range_id)
            WHERE s.Seminar_id IS NOT NULL");

        $stmt = DBManager::get()->prepare("INSERT IGNORE INTO schedule_seminare
            (user_id, seminar_id, metadate_id) VALUES(?, ?, ?)");

        while ($data = $db->fetch()) {
            $md = @unserialize($data['metadata_dates']);
            if (is_array($md['turnus_data'])) {
                foreach ($md['turnus_data'] as $cycle) {
                    $stmt->execute(array($data['user_id'], $data['range_id'], $cycle['metadate_id']));
                }
            }
        }

    }

    function down ()
    {
        DBManager::get()->query("DROP TABLE schedule");
        DBManager::get()->query("DROP TABLE schedule_seminare");
    }
}
