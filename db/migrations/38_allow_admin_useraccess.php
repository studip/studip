<?
class AllowAdminUseraccess extends Migration
{
    function description ()
    {
        return 'add config option for administrators in order to allows editing of sensible user data like passwords';
    }

    function up ()
    {
        $db = DBManager::get();

        $name = 'ALLOW_ADMIN_USERACCESS';
        $description = 'Wenn eingeschaltet, dürfen Administratoren sensible persönliche Angaben wie z.B. Passwörter ändern.';
        $time = time();

        $db->exec("
            INSERT INTO config
                (config_id, field, value, is_default, type, mkdate, chdate, description)
            VALUES
                (MD5('$name'), '$name', '1', 1, 'boolean', $time, $time, '$description')
        ");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM config WHERE field = 'ALLOW_ADMIN_USERACCESS'");
    }
}
?>
