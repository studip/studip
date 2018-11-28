<?php

class Step00266ForcedLockRules extends Migration {

    function description() {
        return 'AuxLockRules can now be chosen to be forced before doing something in a seminar';
    }

    function up() {
        DBManager::get()->exec("ALTER TABLE `seminare` ADD `aux_lock_rule_forced` TINYINT NOT NULL DEFAULT '0' AFTER `aux_lock_rule`;");
         
    }

    function down() {
        DBManager::get()->exec("ALTER TABLE `seminare` DROP `aux_lock_rule_forced`;");
    }

}

?>
