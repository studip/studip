<?php

class AddContentWidget extends Migration
{
    public function description()
    {
        return "Adds ContentsWidget";
    }

    public function up()
    {
        $db = DBManager::get();
        $classname = 'ContentsWidget';
        // get highest position
        $navpos = (int)$db->fetchColumn("SELECT navigationpos FROM plugins ORDER BY navigationpos DESC") + 1;

        // insert plugin into db
        $db->execute("INSERT INTO plugins
            (pluginclassname, pluginpath, pluginname, plugintype, enabled, navigationpos)
            VALUES (?, ?, ?, 'PortalPlugin', 'yes', ?)",
            [$classname, 'core/' . $classname, $classname, $navpos]);

        // get id of newly created plugin (we purposely do not use PDO::lastInserId())
        $plugin_id = $db->fetchColumn("SELECT pluginid FROM plugins WHERE pluginclassname = ?", [$classname]);

        // set all default roles for the plugin
        $stmt = $db->prepare("INSERT INTO roles_plugins
            (roleid, pluginid) VALUES (?, ?)");
        foreach (range(1, 6) as $role_id) {
            $stmt->execute([$role_id, $plugin_id]);
        }
    }

    public function down()
    {
        $db = DBManager::get();
        $classname = 'ContentsWidget';
        // get id of widget
        $widget_id = $db->fetchColumn("SELECT pluginid FROM plugins WHERE pluginclassname = ?", [$classname]);
        $db->execute("DELETE FROM plugins WHERE pluginid = ?", [$widget_id]);
        $db->execute("DELETE FROM widget_default WHERE pluginid = ?", [$widget_id]);
        $db->execute("DELETE FROM widget_user WHERE pluginid = ?", [$widget_id]);
        $db->execute("DELETE FROM roles_plugins WHERE pluginid = ?", [$widget_id]);
    }
}

