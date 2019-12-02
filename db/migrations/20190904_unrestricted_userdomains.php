<?php
class UnrestrictedUserdomains extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `userdomains`
                  ADD COLUMN `restricted_access` TINYINT(1) NOT NULL DEFAULT 1,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `userdomains`
                  DROP COLUMN `restricted_access`,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);
    }
}
