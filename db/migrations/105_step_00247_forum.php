<?php

class Step00247Forum extends Migration
{
    function description()
    {
        return 'Add forum as core-plugin';
    }

    function up()
    {
        // check if the plugin has been installed previously and is in a know db-state
        $forumpp_version = DBManager::get()->query("SELECT version FROM schema_version
            WHERE domain = 'ForumPP'")->fetchColumn();

        if ($forumpp_version !== false && $forumpp_version != 6) {  // version 6 is the DB-Version of the latest ForumPP-Plugin
            throw new Exception(_('Sie verwenden das ForumPP-Plugin in einer alten Version. '
                . 'Bitte aktualisieren Sie es zuerst auf die neueste Version, sonst kann '
                . 'Die Stud.IP-Migration nicht ausgeführt werden'));

        } else if ($forumpp_version == 6) { // prepare the tables for the rest of the migration
            // rename the forum-tables
            DBManager::get()->exec("RENAME TABLE
                forumpp_abo_users           TO forum_abo_users,
                forumpp_categories          TO forum_categories,
                forumpp_categories_entries  TO forum_categories_entries,
                forumpp_entries             TO forum_entries,
                forumpp_favorites           TO forum_favorites,
                forumpp_likes               TO forum_likes,
                forumpp_visits              TO forum_visits");

        } else {  // create the necessary tables for the forum from scratch
            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_categories` (
                    `category_id` varchar(32) NOT NULL,
                    `seminar_id` varchar(32) NOT NULL,
                    `entry_name` varchar(255) NOT NULL,
                    `pos` INT NOT NULL DEFAULT '0',
                    PRIMARY KEY ( `category_id` )
                ) ENGINE=MyISAM
            ");

            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_categories_entries` (
                    `category_id` varchar(32) NOT NULL,
                    `topic_id` varchar(32) NOT NULL,
                    `pos` INT NOT NULL DEFAULT '0',
                    PRIMARY KEY ( `category_id` , `topic_id` )
                ) ENGINE=MyISAM
            ");


            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_entries` (
                    `topic_id` varchar(32) NOT NULL,
                    `seminar_id` varchar(32) NOT NULL,
                    `user_id` varchar(32) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `content` text NOT NULL,
                    `area` TINYINT NOT NULL DEFAULT '0',
                    `mkdate` int(20) NOT NULL,
                    `chdate` int(20) NOT NULL,
                    `author` varchar(255) NOT NULL,
                    `author_host` varchar(255) NOT NULL,
                    `lft` int(11) NOT NULL,
                    `rgt` int(11) NOT NULL,
                    `depth` int(11) NOT NULL,
                    `anonymous` tinyint(4) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`topic_id`)
                ) ENGINE=MyISAM
            ");

            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_likes` (
                  `topic_id` varchar(32) NOT NULL,
                  `user_id` varchar(32) NOT NULL,
                  PRIMARY KEY (`topic_id`,`user_id`)
                ) ENGINE=MyISAM
            ");

            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_visits` (
                    user_id varchar(32) NOT NULL,
                    seminar_id varchar(32) NOT NULL,
                    visitdate int(11) NOT NULL,
                    last_visitdate int(11) NOT NULL,
                    PRIMARY KEY ( `user_id` , `seminar_id` )
                ) ENGINE=MyISAM
            ");

            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_favorites` (
                    user_id varchar(32) NOT NULL,
                    topic_id varchar(32) NOT NULL,
                    PRIMARY KEY ( `user_id` , `topic_id` )
                ) ENGINE=MyISAM
            ");

            DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `forum_abo_users` (
                    `topic_id` varchar(32) NOT NULL,
                    `user_id` varchar(32) NOT NULL,
                    PRIMARY KEY (`topic_id`,`user_id`)
                ) ENGINE=MyISAM
            ");
        }

        // add new table for the issue-connection
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `forum_entries_issues` (
            `topic_id` varchar(32) NOT NULL,
            `issue_id` varchar(32) NOT NULL,
            PRIMARY KEY (`topic_id`,`issue_id`)
        ) ENGINE=MyISAM");

        // add some highly needed indices
        DBManager::get()->exec("ALTER TABLE `forum_entries` ADD INDEX (  `seminar_id` ,  `lft` )");
        DBManager::get()->exec("ALTER TABLE `forum_entries` ADD INDEX (  `seminar_id` ,  `rgt` )");
        DBManager::get()->exec("ALTER TABLE `forum_entries` ADD INDEX (  `user_id` )");
        DBManager::get()->exec("ALTER TABLE `forum_categories` ADD INDEX (  `seminar_id` )");

        // get highest position
        $navpos = DBManager::get()->query("SELECT navigationpos FROM plugins
            ORDER BY navigationpos DESC")->fetchColumn() + 1;

        // insert plugin into db
        $stmt = DBManager::get()->prepare("INSERT INTO plugins
            (pluginclassname, pluginpath, pluginname, plugintype, enabled, navigationpos)
            VALUES ('CoreForum', 'core/Forum', 'Forum', 'ForumModule,StandardPlugin,StudipModule', 'yes', ?)");
        $stmt->execute([$navpos]);

        // get id of newly created plugin (we purposely do not use PDO::lastInserId())
        $plugin_id = DBManager::get()->query("SELECT pluginid FROM plugins
            WHERE pluginpath = 'core/Forum'")->fetchColumn();

        // set all default roles for the plugin (including nobody)
        $stmt = DBManager::get()->prepare("INSERT INTO roles_plugins
            (roleid, pluginid) VALUES (?, ?)");
        foreach (range(1,7) as $role_id) {
            $stmt->execute([$role_id, $plugin_id]);
        }


        // remove old ForumPP-plugin
        $old_forum = DBManager::get()->query("SELECT * FROM plugins
            WHERE pluginclassname = 'ForumPP'")->fetch(PDO::FETCH_ASSOC);

        if ($old_forum) {
            DBManager::get()->exec("DELETE FROM plugins
                WHERE pluginclassname = 'ForumPP'");
            DBManager::get()->exec("DELETE FROM plugins_activated
                WHERE pluginid = " . $old_forum['pluginid']);
            DBManager::get()->exec("DELETE FROM plugins_default_activations
                WHERE pluginid = " . $old_forum['pluginid']);
            DBManager::get()->exec("DELETE FROM roles_plugins
                WHERE pluginid = " . $old_forum['pluginid']);
            DBManager::get()->exec("DELETE FROM schema_version
                WHERE domain = 'ForumPP'");
        }

        // remove user-settings for the old forum
        DBManager::get()->exec("DELETE FROM user_config WHERE `field` = 'FORUM_SETTINGS'");
    }

    function down()
    {
    }
}
