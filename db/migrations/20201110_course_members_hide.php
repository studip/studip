<?php
class CourseMembersHide extends Migration
{
    public function description()
    {
        return 'Add config option to hide course members page';
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'COURSE_MEMBERS_HIDE',
            ':description' => 'Über diese Option können Sie die Teilnehmendenliste für Studierende der Veranstaltung unsichtbar machen',
            ':range'       => 'course',
            ':type'        => 'boolean',
            ':value'       => '0'
        ]);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'COURSE_MEMBERS_HIDE'";
        DBManager::get()->exec($query);
    }
}
