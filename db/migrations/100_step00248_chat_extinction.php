<?php

class Step00248ChatExtinction extends Migration
{


    function description()
    {
        return 'wipe out chat';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS chat_data");
        $db->exec("ALTER TABLE user_visibility DROP COLUMN chat");
        $db->exec("DELETE FROM config WHERE field = 'CHAT_USE_AJAX_CLIENT'");
        $db->exec("DELETE FROM config WHERE field = 'CHAT_ENABLE'");
        $db->exec("DELETE FROM config WHERE field = 'CHAT_VISIBILITY_DEFAULT'");
        $db->exec("DELETE FROM user_config WHERE field = 'CHAT_USE_AJAX_CLIENT'");
    }

    function down()
    {
    }
}
