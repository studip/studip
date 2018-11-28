<?php
class Tic4450ArchivProtectedFiles extends Migration
{
    function description()
    {
        return 'ALTER TABLE `archiv` ADD `archiv_protected_file_id`';
    }

    function up()
    {
        $query = "ALTER TABLE `archiv` ADD `archiv_protected_file_id` VARCHAR(32) NOT NULL DEFAULT '' AFTER `archiv_file_id`";

        DBManager::get()->exec($query);
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `archiv` DROP `archiv_protected_file_id`");
    }
}
