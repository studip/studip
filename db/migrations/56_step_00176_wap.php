<?php
class Step00176Wap extends Migration
{
    function description()
    {
        return 'remove sessions table for wap';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS wap_sessions");
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("CREATE TABLE wap_sessions ( "
                 ."`user_id` char(32) NOT NULL default '', "
                 ."`session_id` char(32) NOT NULL default '', "
                 ."`creation_time` datetime default NULL, "
                 ."PRIMARY KEY (`session_id`)) ENGINE=MyISAM");
    }
}
