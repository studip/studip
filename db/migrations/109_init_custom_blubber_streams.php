<?php

class InitCustomBlubberStreams extends Migration {

    function description() {
        return 'Introduces custom blubber streams and makes hashtags more reliable';
    }

    function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `blubber_tags` (
                `topic_id` varchar(32) NOT NULL,
                `tag` varchar(128) NOT NULL,
                PRIMARY KEY `unique_tags` (`topic_id`,`tag`),
                KEY `tag` (`tag`)
            ) ENGINE=MyISAM
        ");
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `blubber_streams` (
                `stream_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `name` varchar(32) NOT NULL,
                `sort` enum('activity','age') NOT NULL DEFAULT 'age',
                `defaultstream` tinyint(2) NOT NULL DEFAULT '0',
                `pool_courses` text,
                `pool_groups` text,
                `pool_hashtags` text,
                `filter_type` text,
                `filter_courses` text,
                `filter_groups` text,
                `filter_users` text,
                `filter_hashtags` text,
                `filter_nohashtags` text,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`stream_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=MyISAM
        ");

        DBManager::get()->exec("ALTER TABLE `blubber` DROP INDEX  `root_id` ,
            ADD INDEX  `root_id` (  `root_id` ,  `mkdate` )");

        DBManager::get()->exec("ALTER TABLE  `blubber` DROP INDEX  `Seminar_id` ,
            ADD INDEX  `Seminar_id` (  `Seminar_id` ,  `context_type` )");

        //noch Hashtags/Tags in eigene Tabelle packen:
        $statement = DBManager::get()->prepare(
            "SELECT blubber.* " .
            "FROM blubber " .
            "WHERE LOCATE('#', blubber.description) > 0 " .
        "");
        $statement->execute();
        $hashtag_regexp = "(?:^|\s)#([\w\d_\.\-\?!\+=%]*[\w\d])";
        $insert_statement = DBManager::get()->prepare(
            "INSERT IGNORE INTO blubber_tags " .
            "SET topic_id = :topic_id, " .
                "tag = :tag " .
        "");

        while($blubber = $statement->fetch(PDO::FETCH_ASSOC)) {
            preg_match_all("/".$hashtag_regexp."/", $blubber['description'], $matches);
            foreach ($matches as $match) {
                $match = trim($match[0]);
                $tag = $match[0] === "#" ? substr($match, 1) : $match;
                if ($tag) {
                    $insert_statement->execute([
                        'topic_id' => $blubber['root_id'],
                        'tag' => strtolower($tag)
                    ]);
                }
            }
        }
    }

    function down() {

    }

}
