<?php
# refers to https://develop.studip.de/trac/ticket/2568
class Step237DatafieldsMandatory extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds  column is_required, description to the table datafields';
    }

    /**
     * perform this migration
     */
    function up()
    {
        DBManager::get()->exec("ALTER TABLE `datafields` ADD `is_required` TINYINT NOT NULL DEFAULT '0', ADD `description` TEXT NOT NULL DEFAULT ''");
    }

    /**
     * revert this migration
     */
    function down()
    {
        DBManager::get()->exec("ALTER TABLE `datafields`   DROP `is_required`,   DROP `description`;");
    }
}
