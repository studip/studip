<?
class LockRuleAdminPerm extends Migration
{
    function description ()
    {
        return 'add config options for restricting aux rules and lock rules';
    }

    function up ()
    {
        $db = DBManager::get();

        $name = 'AUX_RULE_ADMIN_PERM';
        $description = 'mit welchem Status dürfen Zusatzangaben definiert werden (admin, root)';
        $time = time();

        $db->exec("
            INSERT INTO config
                (config_id, field, value, is_default, type, mkdate, chdate, description)
            VALUES
                (MD5('$name'), '$name', 'admin', 1, 'string', $time, $time, '$description')
        ");

        $name = 'LOCK_RULE_ADMIN_PERM';
        $description = 'mit welchem Status dürfen Sperrebenen angepasst werden (admin, root)';

        $db->exec("
            INSERT INTO config
                (config_id, field, value, is_default, type, mkdate, chdate, description)
            VALUES
                (MD5('$name'), '$name', 'admin', 1, 'string', $time, $time, '$description')
        ");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM config WHERE field = 'AUX_RULE_ADMIN_PERM'");
        $db->exec("DELETE FROM config WHERE field = 'LOCK_RULE_ADMIN_PERM'");
    }
}
?>
