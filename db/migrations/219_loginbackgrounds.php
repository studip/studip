<?php

class Loginbackgrounds extends Migration
{
    public function description()
    {
        return 'Add database table for management of login background pictures';
    }

    public function up()
    {
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `loginbackgrounds` (
                       `background_id` INT NOT NULL AUTO_INCREMENT,
                       `filename` VARCHAR(255) NOT NULL,
                       `mobile` TINYINT(1) NOT NULL DEFAULT 1,
                       `desktop` TINYINT(1) NOT NULL DEFAULT 1,
                       `in_release` TINYINT(1) NOT NULL DEFAULT 0,
                       PRIMARY KEY (`background_id`)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        DBManager::get()->exec(
            "INSERT INTO `loginbackgrounds` SET `filename` = 'Login-Hintergrund.jpg', ".
            "`mobile` = 0, `desktop` = 1, `in_release` = 1");

        DBManager::get()->exec(
            "INSERT INTO `loginbackgrounds` SET `filename` = 'Login-Hintergrund-mobil.jpg', ".
            "`mobile` = 1, `desktop` = 0, `in_release` = 1");

        mkdir($GLOBALS['STUDIP_BASE_PATH'] . '/public/pictures/loginbackgrounds');
    }

    public function down()
    {
        DBManager::get()->exec('DROP TABLE IF EXISTS `loginbackgrounds`');
    }
}
