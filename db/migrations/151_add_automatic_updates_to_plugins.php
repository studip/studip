<?php
/**
 * Adds two columns into plugins to enable automatic updates
 */
class AddAutomaticUpdatesToPlugins extends Migration
{
    function description()
    {
        return 'Plugins can now be updated automatically via github or other repositories';
    }

    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `plugins` ADD `automatic_update_url` VARCHAR( 256 ) NULL DEFAULT NULL AFTER `dependentonid` ,
            ADD `automatic_update_secret` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `automatic_update_url`
        ");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `plugins` DROP COLUMN `automatic_update_secret` ");
        DBManager::get()->exec("ALTER TABLE `plugins` DROP COLUMN `automatic_update_url` ");
    }
}
