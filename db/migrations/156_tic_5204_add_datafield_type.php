<?php
class Tic5204AddDatafieldType extends Migration
{
    public function description()
    {
        return 'adds datafield type selectboxmultiple';
    }

    public function up()
    {
        $db = DbManager::get();
        $db->exec("ALTER TABLE `datafields` CHANGE `type` `type` ENUM('bool','textline','textarea','selectbox','date','time','email','phone','radio','combo','link','selectboxmultiple') NOT NULL DEFAULT 'textline'");
        $db->exec("ALTER TABLE `datafields` ADD `is_userfilter` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `is_required`");
    }
}
