<?php
class AdjustI18nTables extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `i18n`
                  CHANGE COLUMN `table` `table` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `field` `field` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `lang` `lang` VARCHAR(5) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `i18n`
                  CHANGE COLUMN `table` `table` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  CHANGE COLUMN `field` `field` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  CHANGE COLUMN `lang` `lang` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);
    }
}
