<?php
class Step00161PluginAdmin extends Migration
{
    function description()
    {
        return 'remove plugin admin and role admin plugin';
    }

    function up()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM plugins WHERE pluginid IN (1, 3)");
        $db->exec("DELETE FROM plugins_activated WHERE pluginid IN (1, 3)");
        $db->exec("DELETE FROM roles_plugins WHERE pluginid IN (1, 3)");

        $db->exec("ALTER TABLE plugins DROP plugindesc");
    }

    function down()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE plugins ADD plugindesc varchar(45) NOT NULL default '' AFTER pluginname");

        $db->exec("INSERT INTO plugins
                    (pluginid, pluginclassname, pluginpath, pluginname, plugintype, enabled, navigationpos, dependentonid)
                   VALUES
                    (1, 'PluginAdministrationPlugin', 'core', 'Plugin-Administration', 'AdministrationPlugin', 'yes', 0, NULL),
                    (3, 'RoleManagementPlugin', 'core', 'RollenManagement', 'AdministrationPlugin', 'yes', 1, 1)");

        $db->exec("INSERT INTO roles_plugins (roleid, pluginid) VALUES (1, 1), (1, 3)");
    }
}
?>
