<?
class AuthUserMd5Perms extends Migration
{
    function description ()
    {
        return 'add index for column perms in auth_user_md5';
    }

    function up ()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE auth_user_md5 ADD KEY perms (perms)");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE auth_user_md5 DROP KEY perms");
    }
}
?>
