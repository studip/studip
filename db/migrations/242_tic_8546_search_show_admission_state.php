<?php
class Tic8546SearchShowAdmissionState extends Migration
{

    function description()
    {
        return 'Adds configuration options to show admission state of courses '
            . 'in course search to global config';
    }

    function up()
    {

        $stmt = DBManager::get()->prepare("INSERT INTO `config`
            (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
            VALUES
            (:name, :value, :type, 'global', 'coursesearch', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :desc)
            ON DUPLICATE KEY UPDATE `chdate`=VALUES(`chdate`)");

        $stmt->execute([
                'name' => 'COURSE_SEARCH_SHOW_ADMISSION_STATE',
                'value' => false,
                'type' => 'boolean',
                'desc' => 'Anzeige des Zugangsstatus in der Veranstaltungssuche als Icon.'
            ]);

    }

    function down()
    {
        DBManager::get()->exec("DELETE FROM `config` WHERE `field` =
            'COURSE_SEARCH_SHOW_ADMISSION_STATE'"
        );
    }

}
