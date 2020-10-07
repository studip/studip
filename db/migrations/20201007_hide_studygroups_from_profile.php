<?php
class HideStudygroupsFromProfile extends Migration
{
    public function description()
    {
        return 'Adds config settings that hides studygroups from profiles';
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'HIDE_STUDYGROUPS_FROM_PROFILE', '0', 'boolean', 'global',
                    'studygroups', 'Sollen Studiengruppen bei der Anzeige der Veranstaltungen auf dem Profil versteckt werden?',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'HIDE_STUDYGROUPS_FROM_PROFILE'";
        DBManager::get()->exec($query);
    }
}
