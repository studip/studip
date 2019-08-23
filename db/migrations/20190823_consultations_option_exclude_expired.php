<?php
class ConsultationsOptionExcludeExpired extends Migration
{
    public function description()
    {
        return 'Adds user config option that allows to show/hide expired blocks';
    }

    public function up()
    {
        $query = "INSERT INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'CONSULTATION_EXCLUDE_EXPIRED', '1', 'boolean', 'user',
                    'global', 'Sprechstunden: Sollen abgelaufene Blöcke ausgeblendet werden',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'CONSULTATION_EXCLUDE_EXPIRED'";
        DBManager::get()->exec($query);
    }
}
