<?php

class Step00234HomepagePluginActivation extends Migration
{

    function description()
    {
        return 'configuration entry for default homepage plugin activation';
    }

    function up()
    {
        $db = DBManager::get();
        /*
         * Insert new global configuration entry: are installed homepage plugins
         * activated or deactivated per default? Predefined default is 
         * "activated" here.
         */
        $query = $db->prepare("INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES (MD5(?), '', ?, ?, '1', ?, 'global', 'privacy', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ?, '', '')");

        $query->execute(['HOMEPAGEPLUGIN_DEFAULT_ACTIVATION', 'HOMEPAGEPLUGIN_DEFAULT_ACTIVATION', 1, 'boolean', 'Sollen neu installierte Homepageplugins automatisch für Benutzer aktiviert sein?']);

    }

    function down()
    {
        $db = DBManager::get();
        /*
         * Removes global configuration value for default homepage plugin
         * activation status.
         */
        $query = $db->prepare("DELETE FROM `config` WHERE `field` = ?");

        $query->execute(['HOMEPAGEPLUGIN_DEFAULT_ACTIVATION']);

    }
}
?>
