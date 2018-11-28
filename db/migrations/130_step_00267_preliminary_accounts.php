<?
Class Step00267PreliminaryAccounts extends Migration {

    function description()
    {
        return 'switches all entries with auth_plugin=null to "standard"';
    }


    function up()
    {
        DBManager::get()->exec("UPDATE auth_user_md5 SET auth_plugin='standard' WHERE auth_plugin IS NULL");
        DBManager::get()->exec("ALTER TABLE `auth_user_md5` CHANGE `auth_plugin` `auth_plugin` VARCHAR(64) NULL DEFAULT 'standard'");
    }
}
