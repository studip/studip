<?php

class DropCitationStyle extends Migration
{
    public function description()
    {
        return 'Drop unused LIBRARY_CITATION_STYLE settings';
    }

    public function up()
    {
        // remove config entries
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN ('COURSE_LIBRARY_CITATION_STYLE', 'USER_LIBRARY_CITATION_STYLE')";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        // create config entries
        $query = "INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'COURSE_LIBRARY_CITATION_STYLE',
            ':description' => 'Der standardmäßig genutzte Zitationsstil innerhalb der Veranstaltung.',
            ':section'     => 'Library',
            ':range'       => 'course',
            ':type'        => 'string',
            ':value'       => ''
        ]);
        $statement->execute([
            ':name'        => 'USER_LIBRARY_CITATION_STYLE',
            ':description' => 'Der präferierte Zitationsstil einer Person.',
            ':section'     => 'Library',
            ':range'       => 'user',
            ':type'        => 'string',
            ':value'       => ''
        ]);
    }
}
