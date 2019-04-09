<?php
class SetupCronjobs extends Migration
{
    function description()
    {
        return 'Creates cronjob tables in database and according config entries';
    }

    function up()
    {
        DBManager::get()->query("CREATE TABLE IF NOT EXISTS `cronjobs_tasks` (
            `task_id` CHAR(32) NOT NULL DEFAULT '',
            `filename` VARCHAR(255) NOT NULL,
            `class` VARCHAR(255) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 0,
            `execution_count` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            `assigned_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`task_id`)
        ) ENGINE=MyISAM");

        DBManager::get()->query("CREATE TABLE IF NOT EXISTS `cronjobs_schedules` (
            `schedule_id` CHAR(32) NOT NULL DEFAULT '',
            `task_id` CHAR(32) NOT NULL DEFAULT '',
            `active` TINYINT(1) NOT NULL DEFAULT 0,
            `title` VARCHAR(255) NULL DEFAULT NULL,
            `description` VARCHAR(4096) DEFAULT NULL,
            `parameters` TEXT,
            `priority` ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
            `type` ENUM('periodic','once') NOT NULL DEFAULT 'periodic',
            `minute` TINYINT(2) DEFAULT NULL,
            `hour` TINYINT(2) DEFAULT NULL,
            `day` TINYINT(2) DEFAULT NULL,
            `month` TINYINT(2) DEFAULT NULL,
            `day_of_week` TINYINT(1) UNSIGNED DEFAULT NULL,
            `next_execution` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `last_execution` INT(11) UNSIGNED DEFAULT NULL,
            `last_result` TEXT,
            `execution_count` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            `mkdate` INT(11) UNSIGNED NOT NULL,
            `chdate` INT(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`schedule_id`),
            KEY `task_id` (`task_id`)
        ) ENGINE=MyISAM");

        DBManager::get()->query("CREATE TABLE IF NOT EXISTS `cronjobs_logs` (
            `log_id` CHAR(32) NOT NULL DEFAULT '',
            `schedule_id` CHAR(32) NOT NULL DEFAULT '',
            `scheduled` INT(11) UNSIGNED NOT NULL,
            `executed` INT(11) UNSIGNED NOT NULL,
            `exception` TEXT DEFAULT NULL,
            `output` TEXT,
            `duration` FLOAT NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `schedule_id` (`schedule_id`)
        ) ENGINE=MyISAM");

        // Add config entries
        $query = "INSERT IGNORE INTO `config`
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`,
                     `mkdate`, `chdate`, `description`)
                  VALUES (MD5(:field), :field, :value, 1, :type, 'global', 'global',
                          UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':field' => 'CRONJOBS_ENABLE',
            ':value' => (int)false,
            ':type'  => 'boolean',
            ':description' => 'Schaltet die Cronjobs an',
        ]);

        $statement->execute([
            ':field' => 'CRONJOBS_ESCALATION',
            ':value' => 300,
            ':type'  => 'integer',
            ':description' => 'Gibt an, nach wievielen Sekunden ein Cronjob als steckengeblieben angesehen wird',
        ]);

        // Add default cron tasks and schedules
        $default_data = [
            [
                'filename'    => 'lib/cronjobs/cleanup_log.class.php',
                'class'       => 'CleanupLogJob',
                'priority'    => 'normal',
                'hour'        => 2,
                'minute'      => 13,
            ],
            [
                'filename'    => 'lib/cronjobs/purge_cache.class.php',
                'class'       => 'PurgeCacheJob',
                'priority'    => 'low',
                'hour'        => null,
                'minute'      => -30,
            ],
            [
                'filename'    => 'lib/cronjobs/send_mail_notifications.class.php',
                'class'       => 'SendMailNotificationsJob',
                'priority'    => 'high',
                'hour'        => 1,
                'minute'      => 7,
            ],
            [
                'filename'    => 'lib/cronjobs/check_admission.class.php',
                'class'       => 'CheckAdmissionJob',
                'priority'    => 'normal',
                'hour'        => null,
                'minute'      => -30,
            ],
            [
                'filename'    => 'lib/cronjobs/garbage_collector.class.php',
                'class'       => 'GarbageCollectorJob',
                'priority'    => 'normal',
                'hour'        => 2,
                'minute'      => 33,
            ],
            [
                'filename'    => 'lib/cronjobs/session_gc.class.php',
                'class'       => 'SessionGcJob',
                'priority'    => 'normal',
                'hour'        => 3,
                'minute'      => 13,
            ],
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

        foreach ($default_data as $row) {
            $task_id = md5(uniqid('task', true));

            $task_statement->execute([
                ':task_id'  => $task_id,
                ':filename' => $row['filename'],
                ':class'    => $row['class'],
            ]);

            $schedule_id = md5(uniqid('schedule', true));
            $schedule_statement->execute([
                ':schedule_id' => $schedule_id,
                ':task_id'     => $task_id,
                ':priority'    => $row['priority'],
                ':hour'        => $row['hour'],
                ':minute'      => $row['minute'],
            ]);
        }
    }

    function down()
    {
        DBManager::get()->query("DROP TABLE IF EXISTS `cronjobs_tasks`, `cronjobs_schedules`, `cronjobs_logs`");

        DBManager::get()->query("DELETE FROM config WHERE field IN ('CRONJOB_ENABLE', 'CRONJOBS_ESCALATION')");
    }
}
