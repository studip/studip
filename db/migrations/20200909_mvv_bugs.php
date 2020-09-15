<?php
class MvvBugs extends Migration
{
    public function description()
    {
        return 'This migration fixes several bugs related to mvv';
    }

    public function up()
    {
        // Create table mvv_extern_contacts
        $query = "CREATE TABLE IF NOT EXISTS `mvv_extern_contacts` (
                    `extern_contact_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `vorname` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `homepage` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `mail` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `tel` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `mkdate` INT(11) NOT NULL,
                    `chdate` INT(11) NOT NULL,
                    PRIMARY KEY (`extern_contact_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        // Allow decimal credit point specifications
        $query = "ALTER TABLE `mvv_modul`
                  MODIFY COLUMN `kp` DOUBLE(5,2) DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil`
                  MODIFY COLUMN `kp` DOUBLE(5,2) DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilabschnitt`
                  MODIFY COLUMN `kp` DOUBLE(5,2) DEFAULT NULL";
        DBManager::get()->exec($query);

        // Adjust config sections for mvv
        $query = "UPDATE `config`
                  SET `section` = 'mvv'
                  WHERE `field` LIKE 'MVV%'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        // Remove table mvv_extern_contacts
        $query = "DROP TABLE IF EXISTS `mvv_extern_contacts`";
        DBManager::get()->exec($query);

        // Disallow decimal credit point specifications
        $query = "ALTER TABLE `mvv_modul`
                  MODIFY COLUMN `kp` INT(11) DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil`
                  MODIFY COLUMN `kp` INT(11) DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilabschnitt`
                  MODIFY COLUMN `kp` INT(11) DEFAULT NULL";
        DBManager::get()->exec($query);
    }
}
