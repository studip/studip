<?php
class AddSeminareSemesterCourseIndex extends Migration
{
    public function description()
    {
        return 'Creates a better performing connection between courses and semesters.';
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `semester_courses`
            ADD KEY `course_id` (`course_id`)
        ");
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `semester_courses`
            DROP KEY `course_id`
        ");
    }
}
