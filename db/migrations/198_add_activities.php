<?php

require_once 'lib/bootstrap-api.php';

class AddActivities extends Migration
{

    public function description()
    {
        return 'add table for activities';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE `activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `object_id` varchar(255) NOT NULL,
            `context` enum('system','course','institute','user') NOT NULL,
            `context_id` varchar(32) NOT NULL,
            `provider` varchar(255) NOT NULL,
            `actor_type` varchar(255) NOT NULL,
            `actor_id` varchar(255) NOT NULL,
            `verb` enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','sent','voided') NOT NULL DEFAULT 'experienced',
            `content` text NULL,
            `object_type` varchar(255) NOT NULL,
            `mkdate` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `context_id` (`context_id`),
            KEY `mkdate` (`mkdate`)
        ) ENGINE = InnoDB ROW_FORMAT=DYNAMIC");


        //CHECK IF API IS ENABLED
        if (!Config::get()->API_ENABLED) {
            $db->exec("UPDATE `config` SET `value` = '1' WHERE `field` = 'API_ENABLED'");
        }
        //SET PERMISSION FOR ROUTE
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->set('/user/:user_id/activitystream','get', true, true);
        $permissions->store();

        //CHECK IF OLD ACTIVITY-FEED-PLUGIN IS ACTIVE
        $old_id = $db->query("SELECT pluginid FROM plugins
            WHERE pluginclassname = 'ActivityFeed'
            AND pluginpath NOT LIKE '%core%'")->fetchColumn();

        if ($old_id !== false) {
            $stmt = $db->prepare("DELETE FROM plugins WHERE pluginid = ?");
            $stmt->execute([$old_id]);

            $stmt = $db->prepare("DELETE FROM plugins_activated WHERE pluginid = ?");
            $stmt->execute([$old_id]);

            $stmt = $db->prepare("DELETE FROM plugins_default_activations WHERE pluginid = ?");
            $stmt->execute([$old_id]);

            $stmt = $db->prepare("DELETE FROM roles_plugins WHERE pluginid = ?");
            $stmt->execute([$old_id]);
        }

        // Activate Widget
        $classname = "ActivityFeed";
        $navpos = $db->query("SELECT navigationpos FROM plugins
            ORDER BY navigationpos DESC")->fetchColumn() + 1;

        // insert plugin into db
        $stmt = $db->prepare("INSERT INTO plugins
            (pluginclassname, pluginpath, pluginname, plugintype, enabled, navigationpos)
            VALUES (?, ?, ?, 'PortalPlugin', 'yes', ?)");
        $stmt->execute([$classname, 'core/'.$classname, $classname, $navpos]);

        // get id of newly created plugin (
        $plugin_id = $db->query("SELECT pluginid FROM plugins
            WHERE pluginclassname = '$classname'")->fetchColumn();

        // set all default roles for the plugin
        $stmt = $db->prepare("INSERT INTO roles_plugins
            (roleid, pluginid) VALUES (?, ?)");
        foreach (range(1, 6) as $role_id) {
            $stmt->execute([$role_id, $plugin_id]);
        }
    }

    public function down()
    {
        //DEACTIVATE ROUTE
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->set('/user/:user_id/activitystream','get', false, true);
        $permissions->store();

        $db = DBManager::get();

        //REMOVE WIDGET
        $widget_id = $db->query("SELECT pluginid FROM plugins
            WHERE pluginclassname = 'ActivityFeed'")->fetchColumn();

        $stmt = $db->prepare("DELETE FROM plugins WHERE pluginid = ?");
        $stmt->execute([$widget_id]);

        $stmt = $db->prepare("DELETE FROM widget_default WHERE pluginid = ?");
        $stmt->execute([$widget_id]);

        $stmt = $db->prepare("DELETE FROM widget_user WHERE pluginid = ?");
        $stmt->execute([$widget_id]);

        $stmt = $db->prepare("DELETE FROM roles_plugins WHERE pluginid = ?");
        $stmt->execute([$widget_id]);

        $db->exec("DROP TABLE `activities`");

    }
}
