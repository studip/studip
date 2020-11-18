<?php
class IntroduceRangeConfig extends Migration
{
    public function description()
    {
        return 'Adds new config types "range" and "institute"';
    }

    public function up()
    {
        $query = "ALTER TABLE `config`
                  CHANGE COLUMN `range` `range` ENUM('global', 'range', 'user', 'course', 'institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'global'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $quey = "DELETE `config`, `config_values`
                 FROM `config`
                 LEFT JOIN `config_values` USING(`field`)
                 WHERE `range` IN ('range', 'institute')";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `config`
                  CHANGE COLUMN `range` `range` ENUM('global', 'user', 'course') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'global'";
        DBManager::get()->exec($query);
    }
}
