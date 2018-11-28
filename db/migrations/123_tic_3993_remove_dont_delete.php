<?php
# refers to https://develop.studip.de/trac/ticket/3993
class Tic3993RemoveDontDelete extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'remove unused feature to protect messages from deletion';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();

        try {
            $db->exec("ALTER TABLE `message` DROP COLUMN `dont_delete`");
        } catch (Exception $e) { }
    }


    /**
     * revert this migration
     */
    function down()
    {
        DBManager::get()->exec('ALTER TABLE `message` ADD `dont_delete` tinyint(1) NOT NULL DEFAULT \'0\' AFTER `snd_rec`');
    }
}
