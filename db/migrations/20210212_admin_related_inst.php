<?php

class AdminRelatedInst extends Migration
{
    public function description()
    {
        return 'Add config option to enable admin access to courses of related institutes';
    }

    public function up()
    {
        $query = "INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'ALLOW_ADMIN_RELATED_INST',
            ':description' => 'Admins beteiligter Einrichtungen haben die gleiche Rechte an Veranstaltungen wie die Heimateinrichtung',
            ':section'     => 'global',
            ':range'       => 'global',
            ':type'        => 'boolean',
            ':value'       => '0'
        ]);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'ALLOW_ADMIN_RELATED_INST'";
        DBManager::get()->exec($query);
    }
}
