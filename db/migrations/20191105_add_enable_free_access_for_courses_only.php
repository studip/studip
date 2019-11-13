<?php


class AddEnableFreeAccessForCoursesOnly extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "UPDATE `config` SET `type` = 'string',
            `description` = '1: courses and institutes with public access are visible without login. courses_only: only courses with public access are visible without login. 0: disable this feature.'
            WHERE `field` = 'ENABLE_FREE_ACCESS'"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "UPDATE `config` SET `type` = 'boolean',
            `description` = 'If true, courses with public access are available'
            WHERE `field` = 'ENABLE_FREE_ACCESS'"
        );
    }


    public function description()
    {
        return 'Adds the "courses_only" option for ENABLE_FREE_ACCESS in the configuration.';
    }
}
