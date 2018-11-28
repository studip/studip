<?
Class Step00269PluginRoles extends Migration {

    function description()
    {
        return 'adds column institut_id to table roles_user';
    }


    function up()
    {
        DBManager::get()->exec("ALTER TABLE `roles_user` ADD `institut_id` CHAR(32) NOT NULL DEFAULT ''");
        DBManager::get()->exec("ALTER TABLE `roles_user` DROP PRIMARY KEY");
        DBManager::get()->exec("ALTER TABLE `roles_user` ADD PRIMARY KEY( `roleid`, `userid`, `institut_id`)");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `roles_user` DROP `institut_id`");
    }
}
