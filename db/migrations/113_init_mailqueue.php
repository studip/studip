<?php
class InitMailqueue extends Migration
{
    function description()
    {
        return 'Creates configs and cronjobs for the mailqueue. You still need to enable it.';
    }

    function up()
    {
        //Add table
        $query = "CREATE TABLE IF NOT EXISTS `mail_queue_entries` (
            `mail_queue_id` varchar(32) NOT NULL,
            `mail` text NOT NULL,
            `message_id` varchar(32) DEFAULT NULL,
            `user_id` varchar(32) DEFAULT NULL,
            `tries` int(11) NOT NULL,
            `last_try` int(11) NOT NULL DEFAULT '0',
            `mkdate` bigint(20) NOT NULL,
            `chdate` bigint(20) NOT NULL,
            PRIMARY KEY (`mail_queue_id`),
            KEY `message_id` (`message_id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=MyISAM";
        $statement = DBManager::get()->prepare($query);
        $statement->execute();

        // Add config entries
        $query = "INSERT IGNORE INTO `config`
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`,
                     `mkdate`, `chdate`, `description`)
                  VALUES (MD5(:field), :field, :value, 1, :type, 'global', 'global',
                          UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':field' => 'MAILQUEUE_ENABLE',
            ':value' => "0",
            ':type'  => 'boolean',
            ':description' => 'Aktiviert bzw. deaktiviert die Mailqueue',
        ]);

        // Add default cron tasks and schedules
        $new_job = [
            'filename'    => 'lib/cronjobs/send_mail_queue.class.php',
            'class'       => 'SendMailQueueJob',
            'priority'    => 'normal'
        ];

        $query = "INSERT IGNORE INTO `cronjobs_tasks`
                    (`task_id`, `filename`, `class`, `active`)
                  VALUES (:task_id, :filename, :class, 1)";
        $task_statement = DBManager::get()->prepare($query);

        $query = "INSERT IGNORE INTO `cronjobs_schedules`
                    (`schedule_id`, `task_id`, `parameters`, `priority`,
                     `type`, `minute`, `hour`, `mkdate`, `chdate`,
                     `last_result`)
                  VALUES (:schedule_id, :task_id, '[]', :priority, 'periodic',
                          :minute, :hour, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                          NULL)";
        $schedule_statement = DBManager::get()->prepare($query);

        
        $task_id = md5(uniqid('task', true));

        $task_statement->execute([
            ':task_id'  => $task_id,
            ':filename' => $new_job['filename'],
            ':class'    => $new_job['class'],
        ]);

        $schedule_id = md5(uniqid('schedule', true));
        $schedule_statement->execute([
            ':schedule_id' => $schedule_id,
            ':task_id'     => $task_id,
            ':priority'    => $new_job['priority'],
            ':hour'        => $new_job['hour'],
            ':minute'      => $new_job['minute'],
        ]);
    }

    function down()
    {
        DBManager::get()->query("DROP TABLE IF EXISTS `cronjobs_tasks`, `cronjobs_schedules`, `cronjobs_logs`");

        DBManager::get()->query("DELETE FROM config WHERE field IN ('CRONJOB_ENABLE', 'CRONJOBS_ESCALATION')");
    }
}
