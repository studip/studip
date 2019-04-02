<?php
class Step00223LockRules extends Migration
{
    public function description()
    {
        return 'Step00223: extend db table lock_rules';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `lock_rules` CHANGE `permission` `permission` ENUM( 'autor', 'tutor', 'dozent', 'admin', 'root' ) NOT NULL DEFAULT 'dozent'");
        $db->exec("ALTER TABLE `lock_rules` ADD `object_type` ENUM( 'sem', 'inst', 'user' ) NOT NULL DEFAULT 'sem'");
        $db->exec("ALTER TABLE `lock_rules` ADD `user_id` VARCHAR( 32 ) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE `user_info` ADD `lock_rule` VARCHAR( 32 ) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE `Institute` ADD `lock_rule` VARCHAR( 32 ) NOT NULL DEFAULT ''");
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `lock_rules` CHANGE `permission` `permission` ENUM( 'tutor', 'dozent', 'admin', 'root' ) NOT NULL DEFAULT 'dozent'");
        $db->exec("ALTER TABLE `lock_rules` DROP `object_type`");
        $db->exec("ALTER TABLE `lock_rules` DROP `user_id`");
        $db->exec("ALTER TABLE `user_info` DROP `lock_rule`");
        $db->exec("ALTER TABLE `Institute` DROP `lock_rule`");
    }
}
