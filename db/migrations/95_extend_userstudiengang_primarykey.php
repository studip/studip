<?php
# refers to https://develop.studip.de/trac/ticket/2568
class ExtendUserstudiengangPrimarykey extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'extends the primary key of table "user_studiengang" to include column "abschluss_id"';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $query = "ALTER TABLE `user_studiengang`
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`user_id`, `studiengang_id`, `abschluss_id`)";
        DBManager::get()->exec($query);
    }

    /**
     * revert this migration
     */
    function down()
    {
        $query = "ALTER TABLE `user_studiengang`
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`user_id`, `studiengang_id`)";
        DBManager::get()->exec($query);
    }
}
