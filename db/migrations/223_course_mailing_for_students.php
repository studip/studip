<?php
class CourseMailingForStudents extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `seminare`
                    ADD COLUMN `student_mailing` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        DBManager::get()->exec("ALTER TABLE `session_data` CHANGE COLUMN `val` `val` mediumblob NOT NULL"); //see #9106
    }

    public function down()
    {
        $query = "ALTER TABLE `seminare`
                    DROP COLUMN `student_mailing`";
        DBManager::get()->exec($query);

}
