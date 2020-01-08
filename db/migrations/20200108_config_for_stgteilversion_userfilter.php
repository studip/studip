<?php
class ConfigForStgteilversionUserfilter extends Migration
{
    public function description()
    {
        return 'Adds config entry for Studiengangteil-Version userfilter display';
    }

    public function up()
    {
        $query = "INSERT INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'DISPLAY_STGTEILVERSION_USERFILTER', '0', 'boolean', 'global',
                    'coursesets', 'Steuert die Anzeige des Studiengangteil-Version Filters beim Erstellen von bedingten Anmelderegeln.',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'DISPLAY_STGTEILVERSION_USERFILTER'";
        DBManager::get()->exec($query);
    }
}
