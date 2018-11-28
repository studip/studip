<?php

class ScoreConfigOption extends Migration
{
    function description()
    {
        return 'adds config option for stud.ip score, ranking and kings';
    }

    function up()
    {
        DBManager::get()->exec("
            INSERT INTO config
            (config_id, field, value, is_default, `type`, `range`, section, mkdate, chdate, description, comment)
            VALUES
            ('e6b6b8be6caf8abf0904c29e30e9b129', 'SCORE_ENABLE', '1', 1, 'boolean', 'global', 'modules', 
             UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), 'Schaltet ein oder aus, ob die Rangliste und die Score-Funktion global verfügbar sind.', '')
        ");
    }

}
