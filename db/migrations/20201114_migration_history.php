<?php
class MigrationHistory extends Migration
{
    public function description()
    {
        return 'Extends migration table to allow a history of who did what when';
    }

    public function up()
    {
        $query = "ALTER TABLE `schema_versions`
                  ADD COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `schema_versions`
                  DROP COLUMN `user_id`,
                  DROP COLUMN `mkdate`";
        DBManager::get()->exec($query);
    }
}
