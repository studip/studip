<?php

class Tic1091Chat extends Migration
{
    function description ()
    {
        return 'remove user config of chat client';
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM `user_config` WHERE `field` = 'CHAT_USE_AJAX_CLIENT'");
    }

    function down ()
    {
    }
}