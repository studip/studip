<?php

class GenderIso5218 extends Migration
{
    function description()
    {
        return 'make gender representation match ISO 5218';
    }

    function up()
    {
        $db = DBManager::get();

        $db->exec("UPDATE user_info SET geschlecht = 2 WHERE geschlecht = 1");
    }

    function down()
    {
        $db = DBManager::get();

        $db->exec("UPDATE user_info SET geschlecht = 0 WHERE geschlecht = 1");
        $db->exec("UPDATE user_info SET geschlecht = 1 WHERE geschlecht = 2");
    }
}
