<?php

class Tic5961AddConfigDefaultSem extends Migration
{
    function description()
    {
        return 'adds config option for new my courses';
    }

    function up()
    {
        DBManager::get()->execute("
                INSERT IGNORE INTO `config`
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ", [
            'name'        => 'MY_COURSES_DEFAULT_CYCLE',
            'value'       => 'last',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'MeineVeranstaltungen',
            'description' => 'Standardeinstellung für den Semester-Filter, falls noch keine Auswahl getätigt wurde. (all, future, current, last)',
            ]
        );
    }

    function down()
    {
        DBManager::get()->exec("DELETE FROM config WHERE `field` = 'MY_COURSES_DEFAULT_CYCLE'");
    }
}
