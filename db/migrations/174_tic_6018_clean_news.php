<?php

class Tic6018CleanNews extends Migration
{
    function description()
    {
        return 'adds config option to cleaner news display';
    }

    function up()
    {
            DBManager::get()->execute("
                INSERT IGNORE INTO `config`
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ", [
                    'name'        => 'NEWS_DISPLAY',
                    'value'       => '2',
                    'type'        => 'integer',
                    'range'       => 'global',
                    'section'     => 'view',
                    'description' => 'Legt fest, wie sich News für Anwender präsentieren. (2 zeigt sowohl Autor als auch Zugriffszahlen an. 1 zeigt nur den Autor an. 0 blendet beides für Benutzer aus.',
                ]
            );
    }

    function down()
    {
        DBManager::get()->exec("DELETE FROM config WHERE `field` = 'NEWS_DISPLAY'");
    }
}
