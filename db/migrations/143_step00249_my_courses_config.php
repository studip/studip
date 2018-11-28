<?php

class Step00249MyCoursesConfig extends Migration
{
    function description()
    {
        return 'adds config option for new my courses';
    }

    function up()
    {
        DBManager::get()->exec("
            INSERT IGNORE INTO config
            (config_id, field, value, is_default, `type`, `range`, section, mkdate, chdate, description, comment)
            VALUES
            (md5('MY_COURSES_ENABLE_STUDYGROUPS'), 'MY_COURSES_ENABLE_STUDYGROUPS', 0, 0, 'boolean', 'global', 'MeineVeranstaltungen',
             UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), 'Sollen Studiengruppen in einem eigenen Bereich angezeigt werden (Neues Navigationelement in Meine Veranstaltungen)?.', '')
            ");

        DBManager::get()->exec("
            INSERT IGNORE INTO config
            (config_id, field, value, is_default, `type`, `range`, section, mkdate, chdate, description, comment)
            VALUES
            (md5('MY_COURSES_ENABLE_ALL_SEMESTERS'), 'MY_COURSES_ENABLE_ALL_SEMESTERS', 0, 0, 'boolean', 'global', 'MeineVeranstaltungen',
             UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), 'Ermöglicht die Anzeige von allen Semestern unter meine Veranstaltungen.', '')
            ");

        DBManager::get()->exec("UPDATE config SET value = 'sem_number' WHERE field = 'MY_COURSES_FORCE_GROUPING'");
    }

    function down()
    {
    }
}
