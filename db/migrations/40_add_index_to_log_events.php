<?php

class AddIndexToLogEvents extends Migration {
    function description() {
        return "adds an index to the log_events table";
    }

    function up() {
         DBManager::get()->query("ALTER TABLE `log_events` ADD INDEX `action_id` ( `action_id` )");
    }

    function down() {
         DBManager::get()->query("ALTER TABLE `log_events` DROP INDEX `action_id`");
    }
}
