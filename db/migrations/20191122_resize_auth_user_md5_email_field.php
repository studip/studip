<?php


class ResizeAuthUserMd5EmailField extends Migration
{
    public function up()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE `auth_user_md5`
            CHANGE COLUMN `Email` `Email` VARCHAR(256) NULL DEFAULT NULL"
        );
    }


    public function down()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE `auth_user_md5`
            CHANGE COLUMN `Email` `Email` VARCHAR(256) NULL DEFAULT NULL"
        );
    }


    public function description()
    {
        return 'Resizes the Email field of the auth_user_md5 table to mail addresses with up to 256 characters, as specified in RFC3696.';
    }
}
