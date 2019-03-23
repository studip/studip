<?php
class LogEventAutoincrement extends Migration
{
    public function description()
    {
        return 'Converts log_events.event_id to auto_increment';
    }

    public function up()
    {
        $query = "ALTER TABLE `log_events` DROP COLUMN `event_id`";
        DBManager::get()->execute($query);

        $query = "ALTER TABLE `log_events`
                    ADD COLUMN `event_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST";
        DBManager::get()->execute($query);

        $query = "ALTER TABLE `log_events`
                    MODIFY COLUMN `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->execute($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `log_events` ADD COLUMN `new_id` VARCHAR(32) FIRST";
        DBManager::get()->execute($query);

        $query = "UPDATE `log_events` SET `new_id` = MD5(`event_id`)";
        DBManager::get()->execute($query);

        $query = "ALTER TABLE `log_events`
                    DROP COLUMN `event_id`,
                    CHANGE COLUMN `new_id` `event_id` VARCHAR(32) PRIMARY KEY";
        DBManager::get()->execute($query);
    }
}
