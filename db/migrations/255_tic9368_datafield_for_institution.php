<?php
class Tic9368DatafieldForInstitution extends Migration
{
    public function description()
    {
        return 'add column for institution_id to datafield-table';
    }

    public function up()
    {
        $query = "ALTER TABLE `datafields`
            ADD COLUMN `institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `view_perms`;";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `datafields` DROP COLUMN `institut_id`";
        DBManager::get()->exec($query);
    }
}
