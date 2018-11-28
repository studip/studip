<?php
/**
 * Migration that registers the cronjob for TIC 5661 that removes
 * expired entries from the table "object_user_visits".
 *
 * Note: This migration only registers the task but does not set up
 * any schedule, thus leaving it optional.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.3
 */

class Tic5661SetupCronjob extends Migration
{
    public function description()
    {
        return 'Registers the cronjob for TIC 5661 that removes expired '
             . 'entries from the table "object_user_visits".';
    }

    public function up()
    {
        CronjobScheduler::registerTask($this->getFilename(), false);
    }

    public function down()
    {
        $task_id = CronjobTask::findOneByFilename($this->getFilename())->task_id;
        CronjobScheduler::unregisterTask($task_id);
    }

    private function getFilename()
    {
        return 'lib/cronjobs/clean_object_user_visits.php';
    }
}
