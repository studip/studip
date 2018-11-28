<?php

class Step216AutomatisiertesEintragen extends Migration
{
    function description ()
    {
        return 'add new root view: automatic user insert';
    }

    function up ()
    {
        // create new auto_insert_sem table
        // seminar_id : Seminar_id
        // status : Stud.IP user status (root,admin,dozent etc.)
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `auto_insert_sem` (
                `seminar_id` char(32) NOT NULL,
                `status` enum('autor','tutor','dozent') NOT NULL DEFAULT 'autor',
                PRIMARY KEY  (`seminar_id`,`status`)
                ) ENGINE=MyISAM;
        ");

        DBManager::get()->exec("
           CREATE TABLE IF NOT EXISTS `auto_insert_user` (
                `seminar_id` char(32) NOT NULL,
                `user_id` char(32) NOT NULL,
                `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY  (`seminar_id`,`user_id`)
                ) ENGINE=MyISAM;
        ");

        $options[] =
            array(
            'name'        => 'AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM',
            'type'        => 'string',
            'value'       => 'tutor',
            'section'     => 'global',
            'description' => 'Ab welchem Status soll in Veranstaltungen mit automatisch eingetragenen Nutzern der Teilnehmerreiter zu sehen sein?'
            );

        $stmt = DBManager::get()->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }

        if (is_array($GLOBALS['AUTO_INSERT_SEM'])) {
            $stmt = DBManager::get()->prepare("
            INSERT INTO `auto_insert_sem` (
            `seminar_id` , `status` ) VALUES
            (:seminar_id, 'autor') ,
            (:seminar_id, 'tutor') ,
            (:seminar_id, 'dozent')
            ");
            foreach ($GLOBALS['AUTO_INSERT_SEM'] as $seminar_id) {
                $stmt->execute(array('seminar_id' => $seminar_id));
            }
        }
    }

    function down ()
    {
        DBManager::get()->exec("DROP TABLE auto_insert_sem");
        DBManager::get()->exec("DROP TABLE auto_insert_user");
        DBManager::get()->exec("DELETE FROM config WHERE field = 'AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM'");
    }
}
