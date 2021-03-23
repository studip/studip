<?php
/**
* garbage_collector.class.php
*
* @author André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access public
* @since  2.4
*/
require_once 'lib/classes/CronJob.class.php';

class GarbageCollectorJob extends CronJob
{

    public static function getName()
    {
        return _('Datenbank bereinigen');
    }

    public static function getDescription()
    {
        return _('Entfernt endgültig gelöschte Nachrichten, nicht zugehörige Dateianhänge, abgelaufene Ankündigungen, '
               . 'alte Aktivitäten, veraltete Plugin-Assets sowie veraltete OAuth-Servernonces und abgelaufene '
               . 'Terminblöcke');
    }

    public static function getParameters()
    {
        return [
            'verbose' => [
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden (sind später im Log des Cronjobs sichtbar)'),
            ],
            'news_deletion_days' => [
                'type'        => 'integer',
                'default'     => 365,
                'status'      => 'optional',
                'description' => _('(Ankündigungen): Nach wie vielen Tagen sollen die abgelaufenen '
                                 .'Ankündigungen gelöscht werden (0 für Zeitpunkt des Ablaufdatums, Default: 365 Tage)?'),
            ],
            'message_deletion_days' => [
                'type'        => 'integer',
                'default'     => 30,
                'status'      => 'optional',
                'description' => _('(Systemnachrichten): Nach wie vielen Tagen sollen die '
                                 .'Systemnachrichten gelöscht werden (0 für sofort, Default: 30 Tage)?'),
            ],
        ];
    }

    public function execute($last_result, $parameters = [])
    {
        $db = DBManager::get();

        if ($parameters['verbose']) {
            $message_count_before = DBManager::get()->fetchColumn("SELECT COUNT(*) FROM `message`");
        }

        // delete outdated news
        $news_deletion_days = $parameters['news_deletion_days'] * 86400;
        $deleted_news = StudipNews::DoGarbageCollect($news_deletion_days);

        // delete messages
        $query = "DELETE `message`, `message_user`, `message_tags`
                  FROM `message`
                  RIGHT JOIN (
                      SELECT `message_id`
                      FROM `message_user`
                      GROUP BY `message_id`
                      HAVING COUNT(`message_id`) = SUM(`deleted`)
                  ) AS tmp USING (`message_id`)
                  LEFT JOIN `message_user` USING (`message_id`)
                  LEFT JOIN `message_tags` USING (`message_id`)";
        DBManager::get()->execute($query);

        // delete system messages
        $query = "DELETE `message`, `message_user`, `message_tags`
                  FROM `message`
                  LEFT JOIN `message_user` USING (`message_id`)
                  LEFT JOIN `message_tags` USING (`message_id`)
                  WHERE `autor_id` = '____%system%____'
                    AND DATE(FROM_UNIXTIME(`message`.`mkdate`)) + INTERVAL :days DAY < DATE(NOW())";
        DBManager::get()->execute($query, [
            ':days' => $parameters['message_deletion_days'],
        ]);

        // Remove outdated opengraph urls
        $query = "DELETE FROM `opengraphdata`
                  WHERE `last_update` < UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK)";
        DBManager::get()->exec($query);

        //delete old attachments of non-sent and deleted messages:
        //A folder is old and not attached to a message when it has the
        //range type 'message', belongs to the folder type 'MessageFolder',
        //is older than 2 hours and has a range-ID that doesn't exist
        //in the "message" table.
        $unsent_attachment_folders = Folder::deleteBySql(
            "folder_type = 'MessageFolder'
            AND
            range_type = 'message'
            AND
            chdate < UNIX_TIMESTAMP(DATE_ADD(NOW(),INTERVAL -2 HOUR))
            AND
            range_id NOT IN (
                SELECT message_id FROM message
            )",
            [
                'user_id' => $GLOBALS['user']->id
            ]
        );

        //delete old attachments of non-stored and deleted mvv objects:
        //A folder is old and not attached to a mvv object when it has a mvv
        //range type, belongs to the folder type 'MVVFolder',
        //is older than 2 hours and has a range-ID that doesn't exist.
        $unsent_mvv_folders = Folder::deleteBySql(
            "LEFT JOIN `file_refs` ON (`file_refs`.`folder_id` = `folders`.`id`)
            LEFT JOIN `mvv_files_filerefs` ON (`file_refs`.`id` = `mvv_files_filerefs`.`fileref_id`)
            LEFT JOIN `mvv_files_ranges` USING (`mvvfile_id`)
            WHERE `folders`.`folder_type` = 'MVVFolder'
            AND `folders`.`chdate` < UNIX_TIMESTAMP(DATE_ADD(NOW(),INTERVAL -2 HOUR))
            AND ((`mvv_files_ranges`.`range_type` = 'Studiengang' AND `mvv_files_ranges`.`range_id` NOT IN ( SELECT `studiengang_id` FROM `mvv_studiengang`))
            OR (`mvv_files_ranges`.`range_type` = 'AbschlussKategorie' AND `mvv_files_ranges`.`range_id` NOT IN ( SELECT `kategorie_id` FROM `mvv_abschl_kategorie`))
            OR (`mvv_files_ranges`.`range_type` = 'StgteilVersion' AND `mvv_files_ranges`.`range_id` NOT IN ( SELECT `version_id` FROM `mvv_stgteilversion`)))",
            [
                'user_id' => $GLOBALS['user']->id
            ]
        );
        if ($unsent_mvv_folders) {
            $db->exec("DELETE FROM mvv_files_filerefs WHERE fileref_id NOT IN (SELECT id FROM file_refs)");
            $db->exec("DELETE FROM mvv_files WHERE mvvfile_id NOT IN (SELECT mvvfile_id FROM mvv_files_filerefs)");
            $db->exec("DELETE FROM mvv_files_ranges WHERE mvvfile_id NOT IN (SELECT mvvfile_id FROM mvv_files)");
        }

        if ($parameters['verbose']) {
            $message_count_after = DBManager::get()->fetchColumn("SELECT COUNT(*) FROM `message`");

            printf(_("Gelöschte Ankündigungen: %u") . "\n", (int)$deleted_news);
            printf(_("Gelöschte Nachrichten: %u") . "\n", $message_count_before - $message_count_after);
            printf(_("Gelöschte Dateianhänge: %u") . "\n", $unsent_attachment_folders);
            printf(_("Gelöschte MVV-Dateien: %u") . "\n", $unsent_mvv_folders);
        }

        Token::deleteBySQL('expiration < UNIX_TIMESTAMP()');
        PersonalNotifications::doGarbageCollect();

        Studip\Activity\Activity::doGarbageCollect();

        // clean db cache
        $cache = new StudipDbCache();
        $cache->purge();

        // Remove old plugin assets
        PluginAsset::deleteBySQL('chdate < ?', [time() - PluginAsset::CACHE_DURATION]);

        // Remove expired oauth server nonces
        $query = "DELETE FROM `oauth_server_nonce`
                  WHERE `osn_timestamp` < UNIX_TIMESTAMP(NOW() - INTERVAL 6 HOUR)";
        $removed = DBManager::get()->exec($query);

        if ($removed > 0 && $parameters['verbose']) {
            printf(_('Gelöschte Server-Nonces: %u') . "\n", (int)$removed);
        }

        // Remove expired consultation slots
        $condition = "LEFT JOIN `consultation_slots` USING (`block_id`)
                      JOIN `config_values`
                        ON `config_values`.`range_id` = `consultation_blocks`.`range_id`
                           AND `field` = 'CONSULTATION_GARBAGE_COLLECT'
                           AND `value` = '1'
                      GROUP BY `block_id`
                      HAVING COUNT(`slot_id`) = SUM(`end_time` < UNIX_TIMESTAMP())";
        $removed = ConsultationBlock::deleteBySQL($condition);
        if ($removed > 0 && $parameters['verbose']) {
            printf(_('Gelöschte Terminblöcke: %u') . "\n", $removed);
        }
    }
}
