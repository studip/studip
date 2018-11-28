<?php

class Step209Teilnehmericon extends Migration
{
    function description ()
    {
        return 'add a new icon to the seminar overview - participants';
    }

    function up ()
    {
        // expand the enum in object_user_visits
        DBManager::get()->exec("ALTER TABLE `object_user_visits`
            CHANGE `type` `type`
            ENUM( 'vote', 'documents', 'forum', 'literature', 'schedule', 'scm',
                'sem', 'wiki', 'news', 'eval', 'inst', 'ilias_connect',
                'elearning_interface', 'participants' ) NOT NULL DEFAULT 'vote'");

        // copy timestamps from sem to participants to reduce red participant-icons
        DBManager::get()->exec("INSERT INTO object_user_visits
            (object_id, user_id, type, visitdate, last_visitdate)
            (SELECT object_id, ouv.user_id, 'participants' as type, visitdate, last_visitdate
            FROM object_user_visits AS ouv
            LEFT JOIN seminar_user AS su ON (su.user_id = ouv.user_id AND su.Seminar_id = ouv.object_id)
            WHERE type='sem' AND status IN ('tutor', 'dozent'))");
    }

    function down ()
    {
        DBManager::get()->exec("DELETE FROM object_user_visits WHERE type = 'participants'");
        DBManager::get()->exec("ALTER TABLE `object_user_visits`
            CHANGE `type` `type`
            ENUM( 'vote', 'documents', 'forum', 'literature', 'schedule', 'scm',
                'sem', 'wiki', 'news', 'eval', 'inst', 'ilias_connect',
                'elearning_interface') NOT NULL DEFAULT 'vote'");
    }
}
