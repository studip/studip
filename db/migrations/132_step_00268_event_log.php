<?php
class Step00268EventLog extends Migration {

    /**
     * short description of this migration
     */
    function description() {
        return 'Extends event logging for using by arbitrary objects.';
    }

    /**
     * perform this migration
     */
    function up() {
        DBManager::get()->exec("ALTER TABLE `log_actions` ADD `filename`
            VARCHAR( 255 ) NULL DEFAULT NULL ,
            ADD `class` VARCHAR( 255 ) NULL DEFAULT NULL");
        DBManager::get()->exec("ALTER TABLE `log_actions` ADD `type`
            ENUM( 'core', 'plugin', 'file' ) NULL DEFAULT NULL");
    }

    /**
     * revert this migration
     */
    function down() {
        DBManager::get()->exec("ALTER TABLE `log_actions`
            DROP `filename`, DROP `class`, DROP `type`");
    }

}