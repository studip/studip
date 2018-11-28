<?
class Step00157RoleManagment extends Migration
{
    function description ()
    {
        return 'rename de_studip_core_RoleManagementPlugin to RoleManagementPlugin';
    }

    function up ()
    {
        DBManager::get()->exec("UPDATE plugins SET pluginclassname = 'RoleManagementPlugin' WHERE pluginclassname = 'de_studip_core_RoleManagementPlugin'");
        DBManager::get()->exec("DELETE FROM plugins WHERE pluginclassname = 'de_studip_core_UserManagementPlugin'");
        DBManager::get()->exec("DELETE FROM plugins_activated WHERE pluginid = 2");
        DBManager::get()->exec("DELETE FROM roles_plugins WHERE pluginid = 2");
    }

    function down ()
    {
        DBManager::get()->exec("UPDATE plugins SET pluginclassname = 'de_studip_core_RoleManagementPlugin' WHERE pluginclassname = 'RoleManagementPlugin'");
    }
}
?>
