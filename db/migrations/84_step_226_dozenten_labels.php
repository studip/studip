<?php

class Step226DozentenLabels extends Migration
{
    function description ()
    {
        return 'adds a label-field to seminar_user and a new config for label-proposals';
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE seminar_user " .
            "ADD COLUMN label VARCHAR(128) NOT NULL DEFAULT ''" .
        "");

        $name = "PROPOSED_TEACHER_LABELS";
        $time = time();
        $description = "Write a list of comma separated possible labels for teachers and tutor here.";
        $db->exec(
            "INSERT IGNORE INTO config " .
                "(config_id, field, value, is_default, type, mkdate, chdate, description, section) " .
            "VALUES " .
                "(MD5(".$db->quote($name)."), ".$db->quote($name).", '', 1, 'string', $time, $time, ".$db->quote($description).", 'global') " .
        "");
    }

    function down ()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE seminar_user " .
            "DROP COLUMN label " .
        "");
        $db->exec(
            "DELETE FROM config " .
            "WHERE field = 'PROPOSED_TEACHER_LABELS' " .
        "");
    }
}
