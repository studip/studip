<?php
class RemoveNewWidgetSystem extends Migration
{
    public function description()
    {
        return 'Remove fragments of orphaned widget system';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->exec('DROP TABLE widgets');
        $db->exec('DROP TABLE widget_containers');
        $db->exec('DROP TABLE widget_elements');
    }


    public function down()
    {
        $db = DBManager::get();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `widget_containers` (
                `container_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                `range_type` ENUM('course','institute','user','plugin','other') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'course',
                `scope` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'default',
                `parent_id` INT(11) UNSIGNED NULL DEFAULT NULL,
                `mkdate` INT(11) UNSIGNED NOT NULL,
                `chdate` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`container_id`),
                UNIQUE KEY `range` (`range_id`, `range_type`, `scope`),
                KEY `parent_id` (`parent_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `widget_elements` (
                `element_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `container_id` INT(11) UNSIGNED NOT NULL,
                `widget_id` INT(11) UNSIGNED NOT NULL,
                `x` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `y` TINYINT(3) UNSIGNED NOT NULL,
                `width` TINYINT(1) UNSIGNED NOT NULL,
                `height` TINYINT(1) UNSIGNED NOT NULL,
                `locked` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `removable` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `options` VARCHAR(8192) NOT NULL DEFAULT '[]',
                `mkdate` INT(11) UNSIGNED NOT NULL,
                `chdate` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`element_id`),
                KEY `container_id` (`container_id`),
                KEY `widget_id` (`widget_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `widgets` (
                `widget_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `class` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `filename` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                `enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `mkdate` INT(11) UNSIGNED NOT NULL,
                `chdate` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`widget_id`),
                UNIQUE KEY `class` (`class`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
    }
}
