<?php

class TerminRelatedPersons extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds the table termin_related_persons to DB';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `termin_related_persons` ( " .
                "`range_id` varchar(32) NOT NULL, " .
                "`user_id` varchar(32) NOT NULL, " .
                "PRIMARY KEY (`range_id`,`user_id`) " .
            ") ENGINE=MyISAM"
        );
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE `termin_related_persons` ");
    }
}
