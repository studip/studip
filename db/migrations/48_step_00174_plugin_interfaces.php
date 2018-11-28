<?
class Step00174PluginInterfaces extends Migration
{
    function description ()
    {
        return 'update database schema for plugin interfaces';
    }

    function up ()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE plugins CHANGE plugintype
                      plugintype TEXT NOT NULL default ''");

        $db->exec("UPDATE plugins SET plugintype =
                      CONCAT(plugintype, 'Plugin')");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("UPDATE plugins SET plugintype =
                      TRIM(TRAILING 'Plugin' FROM plugintype)");

        $db->exec("ALTER TABLE plugins CHANGE plugintype
                      plugintype varchar(255) NOT NULL default 'Standard'");
    }
}
?>
