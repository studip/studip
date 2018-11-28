<?
class LogActionsExpires extends Migration
{
    function description ()
    {
        return '"expires" column of log_actions should not be NULL';
    }

    function up ()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE log_actions CHANGE expires
                      expires int(20) NOT NULL default 0");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE log_actions CHANGE expires
                      expires int(20) default NULL");
    }
}
?>
