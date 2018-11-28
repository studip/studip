<?php

class Tic5170CleanUp extends Migration {

    function description()
    {
        return 'cleans up a bit.';
    }

    function up()
    {
        $db = DbManager::get();
        $db->exec("ALTER TABLE `message` DROP `chat_id`, DROP `readed`");
        $db->exec("ALTER TABLE `message_user` DROP `confirmed_read`");
        try {
            $db->exec("ALTER TABLE `message_user` DROP COLUMN `dont_delete`");
        } catch (Exception $e) { }
        $db->exec("ALTER TABLE `user_info` DROP `guestbook`");
        $db->exec("DROP TABLE object_rate");
        $db->exec("DROP TABLE object_user");
        $db->exec("DROP TABLE px_topics");
        $db->exec("DROP TABLE rss_feeds");
        $db->exec("DELETE FROM user_visibility_settings WHERE plugin IS NOT NULL");
        $db->exec("DELETE FROM user_visibility_settings WHERE identifier = 'plugins'");
        SimpleORMap::expireTableScheme();
    }

    function down()
    {

    }

}

