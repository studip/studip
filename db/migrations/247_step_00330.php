<?php
/**
 * @author Timo Hartge <hartge@data-quest.de>
 */
class StEP00330 extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `semester_data`
                    ADD `visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1' AFTER `vorles_ende`";
        DBManager::get()->exec();

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `semester_data` DROP `visible`");

        SimpleORMap::expireTableScheme();
    }
}
