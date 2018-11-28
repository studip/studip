<?
class AddAllowSelfassignInstitute extends Migration
{
    function description ()
    {
        return 'add config option for separated en-/disabling studycourse and institute self-assignment';
    }

    function up ()
    {
        $db = DBManager::get();

        $name = 'ALLOW_SELFASSIGN_INSTITUTE';
        $description = 'Wenn eingeschaltet, dürfen Studenten sich selbst Einrichtungen an denen sie studieren zuordnen.';
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

        $db->exec("DELETE FROM config WHERE field = 'ALLOW_SELFASSIGN_INSTITUTE'");
    }
}
?>
