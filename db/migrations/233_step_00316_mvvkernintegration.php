<?php

class Step00316Mvvkernintegration extends Migration
{

    public function description()
    {
        return 'StEP00316 - MVV: Vollständige Kernintegration';
    }

    public function up() {

        $db = DBManager::get();
        $db->exec("UPDATE `log_actions` SET `class` = 'MVV', `type` = 'core' WHERE `class` LIKE 'MVVPlugin' AND `type` = 'plugin'");
        
        // remove dependencies on old core plugin
        $db->execute("DELETE FROM `plugin_assets` "
                . "WHERE `plugin_id` IN ("
                . "   SELECT `pluginid` "
                . "   FROM `plugins` "
                . "   WHERE `pluginpath` = 'core/Modulverwaltung')");
        
        $db->execute("DELETE FROM `roles_plugins` "
                . "WHERE `pluginid` IN ("
                . "   SELECT `pluginid` "
                . "   FROM `plugins` "
                . "   WHERE `pluginpath` = 'core/Modulverwaltung')");
        
        $db->execute("DELETE FROM `plugins` "
                . "WHERE `pluginpath` = 'core/Modulverwaltung'");
    }

    public function down() {
    }

}
