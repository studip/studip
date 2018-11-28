<?
Class Tic4072NewPasswordHashing extends Migration {

    function description()
    {
        return 'hashes all existing passwords with new algo';
    }


    function up()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `auth_user_md5` CHANGE `password` `password` VARBINARY( 64 ) NOT NULL DEFAULT ''");
        $hasher = UserManagement::getPwdHasher();
        $pwd_up = $db->prepare("UPDATE auth_user_md5 SET password=? WHERE user_id=?");
        foreach($db->query("SELECT user_id,password FROM auth_user_md5 WHERE auth_plugin='standard' AND password <> ''") as $row) {
            $new_pwd = $hasher->HashPassword($row['password']);
            $pwd_up->execute(array($new_pwd, $row['user_id']));
        }
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `auth_user_md5` CHANGE `password` `password` VARCHAR( 32 ) NOT NULL DEFAULT ''");
    }
}
