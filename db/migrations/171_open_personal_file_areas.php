<?php

/**
 * open_personal_file_areas.php
 *
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */

class OpenPersonalFileAreas extends Migration
{
    public function description()
    {
        return 'Create config entries for open personal file areas';
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config`
                  (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`,
                   `mkdate`, `chdate`, `description`)
                  VALUES (:id, :field, :value, 1, :type, 'global', 'files',
                          UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':id' => md5(uniqid('PERSONALDOCUMENT_OPEN_ACCESS')),
            ':field' => 'PERSONALDOCUMENT_OPEN_ACCESS',
            ':value' => (int) false,
            ':type' => 'boolean',
            ':description' => 'Schaltet die persönlichen Dateibereiche zur Einsicht für alle Nutzer frei',
        ]);

        $statement->execute([
            ':id' => md5(uniqid('PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED')),
            ':field' => 'PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED',
            ':value' => (int) true,
            ':type' => 'boolean',
            ':description' => 'Erlaubt Root-Accounts das Verändern von persönlichen Dateibereiche im Namen fremder Nutzer',
        ]);
    }

    public function down()
    {
        DBManager::get()->query("DELETE FROM config WHERE field IN ('PERSONALDOCUMENT_OPEN_ACCESS', 'PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED')");
    }
 }
