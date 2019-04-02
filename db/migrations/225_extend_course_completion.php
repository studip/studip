<?php
class ExtendCourseCompletion extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `seminare`
                  CHANGE COLUMN `is_complete` `completion` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        $query = "UPDATE `seminare`
                  SET `completion` = `completion` + 1
                  WHERE `completion` > 0";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "UPDATE `seminare`
                  SET `completion` = `completion` - 1
                  WHERE `completion` > 0";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminare`
                  CHANGE COLUMN `completion` `is_complete` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);
    }
}
