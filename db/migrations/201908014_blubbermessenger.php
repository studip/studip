<?php

class Blubbermessenger extends Migration
{
    public function description()
    {
        return 'Changes the blubber-tool to make it even better and more messenger-like.';
    }

    public function up()
    {
        $query = "CREATE TABLE `blubber_threads` (
                    `thread_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `context_type` ENUM('public','private','course','institute') COLLATE latin1_bin NOT NULL DEFAULT 'public',
                    `context_id` VARCHAR(32) COLLATE latin1_bin NOT NULL DEFAULT '',
                    `user_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `external_contact` TINYINT(1) NOT NULL DEFAULT 0,
                    `content` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `display_class` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `visible_in_stream` TINYINT(1) NOT NULL DEFAULT 1,
                    `commentable` TINYINT(1) NOT NULL DEFAULT 1,
                    `metadata` TEXT COLLATE utf8mb4_unicode_ci NULL,
                    `chdate` INT(11) DEFAULT NULL,
                    `mkdate` INT(11) DEFAULT NULL,
                    PRIMARY KEY (`thread_id`),
                    KEY `context_type` (`context_type`),
                    KEY `context_id` (`context_id`),
                    KEY `user_id` (`user_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "INSERT INTO `blubber_threads` (
                    `thread_id`, `context_type`, `context_id`, `user_id`, `external_contact`, `content`, `display_class`, `visible_in_stream`, `chdate`, `mkdate`
                  )
                  SELECT `topic_id`, `context_type`, `Seminar_id`, `user_id`, `external_contact`, `description`, NULL, '1', `chdate`, `mkdate`
                  FROM blubber
                  WHERE parent_id = '0'";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE `blubber_comments` (
                    `comment_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `thread_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `user_id` CHAR(32) COLLATE latin1_bin NOT NULL DEFAULT '',
                    `external_contact` TINYINT(1) NOT NULL DEFAULT 0,
                    `content` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `network` VARCHAR(64) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
                    `chdate` INT(11) DEFAULT NULL,
                    `mkdate` INT(11) DEFAULT NULL,
                    PRIMARY KEY (`comment_id`),
                    KEY `thread_id` (`thread_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "INSERT INTO `blubber_comments` (
                    `comment_id`, `thread_id`, `user_id`, `external_contact`, `content`, `chdate`, `mkdate`
                  )
                  SELECT `topic_id`, `root_id`, `user_id`, `external_contact`, `description`, `chdate`, `mkdate`
                  FROM blubber
                  WHERE parent_id != '0'";
        DBManager::get()->exec($query);

        DBManager::get()->exec("DROP TABLE `blubber`");

        $query = "ALTER TABLE blubber_mentions
                  CHANGE `topic_id` `thread_id` CHAR(32) COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        DBManager::get()->exec("DROP TABLE blubber_reshares");
        DBManager::get()->exec("DROP TABLE blubber_streams");

        // Create config entries
        $query = "INSERT INTO `config`
                  SET `field` = :field,
                      `value` = :value,
                      `type` = :type,
                      `range` = :range,
                      `section` = :section,
                      `mkdate` = UNIX_TIMESTAMP(),
                      `chdate` = UNIX_TIMESTAMP(),
                      `description` = :description";
        $config_statement = DBManager::get()->prepare($query);

        $config_statement->execute([
            ':field'       => 'BLUBBER_GLOBAL_MESSENGER_ACTIVATE',
            ':value'       => '1',
            ':type'        => 'boolean',
            ':range'       => 'global',
            ':section'     => 'global',
            ':description' => 'Ist Blubber unter Community global aktiv? Blubber in Veranstaltungen wird über das Plugin Blubber aktiviert oder deaktiviert.',
        ]);
        $config_statement->execute([
            ':field'       => 'BLUBBER_DEFAULT_THREAD',
            ':value'       => '1',
            ':type'        => 'string',
            ':range'       => 'user',
            ':section'     => '',
            ':description' => 'Dieses ist bei dem globalen Blubber-Messenger der vorausgewählte Blubber.',
        ]);

        // activate routes:
        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/app/routes/Blubber.php';
        RESTAPI\ConsumerPermissions::get()->activateRouteMap(new RESTAPI\Routes\Blubber());

        // Blubber to be the primary messenger in courses
        $query = "SELECT pluginid
                  FROM plugins
                  WHERE pluginclassname = 'CoreForum'";
        $forum_id = DBManager::get()->fetchColumn($query);

        $query = "SELECT id, modules
                  FROM sem_classes
                  WHERE forum = 'CoreForum'";
        $select_sem_class = DBManager::get()->query($query);
        $sem_classes = $select_sem_class->fetchAll(PDO::FETCH_ASSOC);

        $query = "INSERT IGNORE INTO plugins_activated (pluginid, range_type, range_id, state)
                  SELECT :forum_id, 'sem', seminare.Seminar_id, '1'
                  FROM seminare
                  JOIN sem_types ON seminare.status = sem_types.id
                  JOIN sem_classes ON sem_types.class = sem_classes.id
                  JOIN forum_entries ON forum_entries.seminar_id = seminare.Seminar_id
                  WHERE sem_classes.id = :sem_class
                  GROUP BY seminare.Seminar_id
                  HAVING COUNT(*) > 1";
        $activate_forum_for_courses = DBManager::get()->prepare($query);

        $query = "UPDATE sem_classes
                  SET modules = :modules,
                      forum = 'Blubber'
                  WHERE id = :id";
        $update = DBManager::get()->prepare($query);

        foreach ($sem_classes as $sem_class) {
            $modules = json_decode($sem_class['modules'], true);
            $forum_was_activated = $modules['CoreForum']['activated'];

            $modules['CoreForum']['activated'] = 0;
            $modules['Blubber']['activated'] = 1;

            $update->execute([
                'id'      => $sem_class['id'],
                'modules' => json_encode($modules),
            ]);
            if ($forum_was_activated) {
                // activate old forum in old courses that have more than one posting:
                $activate_forum_for_courses->execute([
                    'forum_id'  => $forum_id,
                    'sem_class' => $sem_class['id'],
                ]);
            }
        }

        // delete old blubber-stream avatars
        $blubberstreams_folder = "{$GLOBALS['DYNAMIC_CONTENT_PATH']}/blubberstream";
        foreach (glob("{$blubberstreams_folder}/*") as $file) {
            @unlink($blubberstreams_folder . "/" . $file);
        }
        @rmdir($blubberstreams_folder);

        DBManager::get()->exec("
            DELETE FROM activities
            WHERE object_type = 'blubber'
        ");
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE `blubber_comments`, `blubber_threads`");
    }
}
