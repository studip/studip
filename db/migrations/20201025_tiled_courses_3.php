<?php
class TiledCourses3 extends Migration
{
    public function description()
    {
        return 'Splits config setting for tiles/tables view into responsive/non responsive mode';
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'MY_COURSES_TILED_DISPLAY_RESPONSIVE', '1', 'boolean', 'user',
                    'MeineVeranstaltungen', 'Hat die Kachelansicht unter \"Meine Veranstaltungen\" aktiviert (responsiv)',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'MY_COURSES_TILED_DISPLAY_RESPONSIVE'";
        DBManager::get()->exec($query);
    }
}
