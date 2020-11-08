<?php
class ConfigI18n extends Migration
{
    public function description()
    {
        return 'Changes config table to allow i18n types';
    }

    public function up()
    {
        $query = "ALTER TABLE `config`
                  CHANGE COLUMN `type` `type` ENUM('boolean','integer','string','array', 'i18n') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'string'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `i18n`
                  FROM `config`
                  JOIN `i18n`
                    ON `i18n`.`object_id` = CAST(MD5(`config`.`field`) AS CHAR CHARACTER SET latin1)
                        AND `i18n`.`table` = 'config'
                        AND `i18n`.`field` = 'value'
                  WHERE `config`.`type` = 'i18n'";
        DBManager::get()->exec($query);

        $query = "UPDATE `config`
                  SET `type` = 'string'
                  WHERE `type` = 'i18n'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `config`
                  CHANGE COLUMN `type` `type` ENUM('boolean','integer','string','array') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'string'";
        DBManager::get()->exec($query);
    }
}
