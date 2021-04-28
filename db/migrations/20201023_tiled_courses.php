<?php
class TiledCourses extends Migration
{
    public function description()
    {
        return 'Creates the config entries for tiled display on my courses';
    }

    public function up()
    {
        // Add Config
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'MY_COURSES_ALLOW_TILED_DISPLAY', '0', 'boolean', 'global',
                    'MeineVeranstaltungen', 'Soll die Kachelansicht unter \"Meine Veranstaltungen\" aktiviert werden?',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        // Add UserConfig
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'MY_COURSES_TILED_DISPLAY', '0', 'boolean', 'user',
                    'MeineVeranstaltungen', 'Hat die Kachelansicht unter \"Meine Veranstaltungen\" aktiviert',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  ), (
                      'MY_COURSES_SHOW_NEW_ICONS_ONLY', '0', 'boolean', 'user',
                      'MeineVeranstaltungen', 'Nur Icons für neue Inhalte sollen angezeigt werden',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        // Remove Config and UserConfig
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'MY_COURSES_ALLOW_TILED_DISPLAY',
                      'MY_COURSES_TILED_DISPLAY',
                      'MY_COURSES_SHOW_NEW_ICONS_ONLY'
                  )";
        DBManager::get()->exec($query);
    }
}
