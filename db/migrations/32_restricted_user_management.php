<?
class RestrictedUserManagement extends Migration
{
    function description ()
    {
        return 'add config option for restricting user management to root';
    }

    function up ()
    {
        $db = DBManager::get();

        $name = 'RESTRICTED_USER_MANAGEMENT';
        $description = 'Schränkt Zugriff auf die globale Nutzerverwaltung auf root ein';
        $time = time();

        $db->exec("
            INSERT INTO config
                (config_id, field, value, is_default, type, mkdate, chdate, description)
            VALUES
                (MD5('$name'), '$name', '0', 1, 'boolean', $time, $time, '$description')
        ");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM config WHERE field = 'RESTRICTED_USER_MANAGEMENT'");
    }
}
?>
