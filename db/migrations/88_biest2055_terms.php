<?php

class Biest2055Terms extends Migration
{
    function description ()
    {
        return 'add TERMS_ACCEPTED user config';
    }

    function up ()
    {
        $sql = "INSERT INTO `user_config` ".
            "(`userconfig_id`, `parent_id`, `user_id`, `field`, `value`, `mkdate`, `chdate`, `comment`) ".
            "SELECT MD5(CONCAT(user_id,'TERMS_ACCEPTED')), NULL, user_id, 'TERMS_ACCEPTED', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),'' FROM auth_user_md5";
        DBManager::get()->exec($sql);
    }

    function down ()
    {
        $sql = 'DELETE FROM `user_config` WHERE `field` = "TERMS_ACCEPTED"';
        DBManager::get()->exec($sql);
    }
}