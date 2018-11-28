<?php

class Step00159DataFieldEntry extends Migration
{
    function description()
    {
        return 'alter table datafields';
    }

    function up()
    {
        DBManager::get()->exec("ALTER TABLE `datafields` MODIFY COLUMN `type` ENUM('bool','textline','textarea','selectbox','date','time','email','phone','radio','combo','link') NOT NULL DEFAULT 'textline'");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `datafields` MODIFY COLUMN `type` ENUM('bool','textline','textarea','selectbox','date','time','email','url','phone','radio','combo') NOT NULL DEFAULT 'textline'");
    }
}
?>
