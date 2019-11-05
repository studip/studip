<?php


class AddEnableFreeAccessForCoursesOnly extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT INTO config
            (`field`, `value`, `type`, `range`,
            `section`, `mkdate`, `chdate`,
            `description`)
            VALUES
            ('ENABLE_FREE_ACCESS_FOR_COURSES_ONLY', '0', 'boolean', 'global',
            'global', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Whether the public access shall be limited to courses (true) or unlimited (false).')"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "DELETE FROM config
            WHERE field = 'ENABLE_FREE_ACCESS_FOR_COURSES_ONLY'"
        );
    }


    public function description()
    {
        return 'Adds the ENABLE_FREE_ACCESS_FOR_COURSES_ONLY parameter into the configuration.';
    }
}
