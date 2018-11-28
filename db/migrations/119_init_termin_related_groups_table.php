<?php

class InitTerminRelatedGroupsTable extends Migration {

    function description() {
        return 'Thread can now be closed.';
    }

    function up() {
        DBManager::get()->exec(
            "CREATE TABLE IF NOT EXISTS `termin_related_groups` (
                `termin_id` VARCHAR(32) NOT NULL ,
                `statusgruppe_id` VARCHAR(45) NOT NULL ,
                UNIQUE KEY `unique` (`termin_id`,`statusgruppe_id`),
                INDEX `termin_id` (`termin_id` ASC) ,
                INDEX `statusgruppe_id` (`statusgruppe_id` ASC) 
            ) ENGINE=MyISAM;");
    }

    function down() {
        DBManager::get()->exec("DROP TABLE `termin_related_groups`;");
    }

}

