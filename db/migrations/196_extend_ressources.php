<?php
class ExtendRessources extends Migration
{
    public function description()
    {
        return 'Adds additional functions to the ressource administration';
    }

    public function up()
    {
        DBManager::get()->execute("ALTER TABLE `resources_objects` ADD `requestable` TINYINT( 4 ) NOT NULL DEFAULT '1' AFTER `multiple_assign`");
        DBManager::get()->execute("ALTER TABLE `resources_categories_properties` ADD `protected` TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER `requestable`");
        DBManager::get()->execute("ALTER TABLE `resources_properties` ADD `info_label` TINYINT( 4 ) NOT NULL DEFAULT '0'");
    }

    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `resources_objects` DROP `requestable`");
        DBManager::get()->execute("ALTER TABLE `resources_categories_properties` DROP `protected`");
        DBManager::get()->execute("ALTER TABLE `resources_properties` DROP `info_label`");
    }
}
