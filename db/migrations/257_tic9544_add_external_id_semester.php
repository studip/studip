<?php

class Tic9544AddExternalIdSemester extends Migration
{
    public function description()
    {
        return 'add column for external id to semester data table';
    }

    public function up()
    {
        $query = "ALTER TABLE `semester_data`
            ADD `external_id` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `visible`";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `semester_data` DROP `external_id`";
        DBManager::get()->exec($query);
    }
}