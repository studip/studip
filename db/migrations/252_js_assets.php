<?php
class JsAssets extends Migration
{
    public function description()
    {
        return 'Alter table "plugin_assets" to allow js assets';
    }

    public function up()
    {
        $query = "ALTER TABLE `plugin_assets`
                    MODIFY COLUMN `type` ENUM('css', 'js') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->execute($query);
    }

    public function down()
    {
        $query = "DELETE FROM `plugin_assets`
                  WHERE `type` = 'js'";
        DBManager::get()->execute($query);

        $query = "ALTER TABLE `plugin_assets`
                    MODIFY COLUMN `type` ENUM('css') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->execute($query);
    }
}
