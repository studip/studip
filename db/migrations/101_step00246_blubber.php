<?php

class Step00246Blubber extends Migration
{


    function description()
    {
        return 'install Blubber as core-plugin';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber` (
                `topic_id` varchar(32) NOT NULL DEFAULT '',
                `parent_id` varchar(32) NOT NULL DEFAULT '',
                `root_id` varchar(32) NOT NULL DEFAULT '',
                `context_type` enum('public','private','course') NOT NULL DEFAULT 'public',
                `name` varchar(255) DEFAULT NULL,
                `description` text,
                `mkdate` int(20) NOT NULL DEFAULT '0',
                `chdate` int(20) NOT NULL DEFAULT '0',
                `author_host` varchar(255) DEFAULT NULL,
                `Seminar_id` varchar(32) NOT NULL DEFAULT '',
                `user_id` varchar(32) NOT NULL DEFAULT '',
                `external_contact` tinyint(4) NOT NULL DEFAULT '0',
                PRIMARY KEY (`topic_id`),
                KEY `root_id` (`root_id`),
                KEY `Seminar_id` (`Seminar_id`),
                KEY `parent_id` (`parent_id`),
                KEY `chdate` (`chdate`),
                KEY `mkdate` (`mkdate`),
                KEY `user_id` (`user_id`,`Seminar_id`)
            ) ENGINE=MyISAM;
        ");
        //Spezialevents, bisher nur für Löschen von Beiträgen verwendet
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_events_queue` (
                `event_type` varchar(32) NOT NULL,
                `item_id` varchar(32) NOT NULL,
                `mkdate` int(11) NOT NULL,
                PRIMARY KEY (`event_type`,`item_id`,`mkdate`),
                KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM
        ");
        //Blubberautoren, die nicht in Stud.IP angemeldet sind wie anonyme
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_external_contact` (
                `external_contact_id` varchar(32) NOT NULL,
                `mail_identifier` varchar(256) DEFAULT NULL,
                `contact_type` varchar(16) NOT NULL DEFAULT 'anonymous',
                `name` varchar(256) NOT NULL,
                `data` text,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`external_contact_id`),
                KEY `mail_identifier` (`mail_identifier`),
                KEY `contact_type` (`contact_type`)
            ) ENGINE=MyISAM
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_follower` (
                `studip_user_id` varchar(32) NOT NULL,
                `external_contact_id` varchar(32) NOT NULL,
                `left_follows_right` tinyint(1) NOT NULL,
                KEY `studip_user_id` (`studip_user_id`),
                KEY `external_contact_id` (`external_contact_id`)
            ) ENGINE=MyISAM
        ");
        //Rechte für private Blubber
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_mentions` (
                `topic_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `external_contact` tinyint(4) NOT NULL DEFAULT '0',
                `mkdate` int(11) NOT NULL,
                UNIQUE KEY `unique_users_per_topic` (`topic_id`,`user_id`,`external_contact`),
                KEY `topic_id` (`topic_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=MyISAM
        ");

        $old_blubber = $db->query(
            "SELECT * FROM plugins WHERE pluginclassname = 'Blubber' " .
        "")->fetch(PDO::FETCH_ASSOC);

        if ($old_blubber) {
            //Umschreiben des Ortes von Blubber
            $db->exec("
                UPDATE plugins SET pluginpath = 'core/Blubber' WHERE pluginclassname = 'Blubber'
            ");
            if ($old_blubber['pluginpath'] !== "core/Blubber") {
                @rmdirr($GLOBALS['PLUGINS_PATH']."/".$old_blubber['pluginpath']);
            }
            $db->exec("
                INSERT IGNORE INTO blubber (`topic_id`,`parent_id`,`root_id`,`context_type`,`name`,`description`,`mkdate`,`chdate`,`author_host`,`Seminar_id`,`user_id`,`external_contact`)
                    SELECT `topic_id`,`parent_id`,`root_id`,'course',`name`,`description`,`mkdate`,`chdate`,`author_host`,`Seminar_id`,`user_id`,0
                    FROM px_topics
            ");
        } else {
            //Installieren des Plugins
            $db->exec("
                INSERT INTO plugins
                SET pluginclassname = 'Blubber',
                    pluginpath = 'core/Blubber',
                    pluginname = 'Blubber',
                    plugintype = 'StandardPlugin,SystemPlugin',
                    enabled = 'yes',
                    navigationpos = '1'
            ");
            $plugin_id = $db->lastInsertId();
            $db->exec("
                INSERT IGNORE INTO roles_plugins (roleid, pluginid)
                    SELECT roleid, ".$db->quote($plugin_id)." FROM roles WHERE `system` = 'y'
            ");
        }
    }

    function down()
    {
    }
}
