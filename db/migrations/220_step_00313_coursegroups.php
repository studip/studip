<?php
class StEP00313Coursegroups extends Migration
{
    public function up()
    {
        DBManager::get()->exec(
            "ALTER TABLE `sem_classes` ADD `is_group` TINYINT(1) NOT NULL DEFAULT '0' AFTER `show_raumzeit`");
        DBManager::get()->exec(
            "ALTER TABLE `seminare` ADD `parent_course` VARCHAR(32) NULL DEFAULT NULL AFTER `public_topics`");
        DBManager::get()->exec(
            "ALTER TABLE `seminare` ADD INDEX(`parent_course`)");
        StudipLog::registerAction('SEM_ADD_TO_GROUP', 'Veranstaltung zu Gruppe hinzufügen',
            '%user ordnet Veranstaltung %sem(%affected) der Gruppe %sem(%coaffected) zu.', null);
        StudipLog::registerAction('SEM_DEL_FROM_GROUP', 'Veranstaltung aus Gruppe entfernen',
            '%user entfernt Veranstaltung %sem(%affected) aus der Gruppe %sem(%coaffected).', null);
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `sem_classes` DROP `is_group`");
        DBManager::get()->exec("ALTER TABLE `seminare` DROP `parent_course`");

        StudipLog::unregisterAction('SEM_ADD_TO_GROUP');
        StudipLog::unregisterAction('SEM_DEL_FROM_GROUP');
    }
}
