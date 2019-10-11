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
        return _('Entfernt endgültig gelöschte Nachrichten, nicht zugehörige Dateianhänge, abgelaufene Ankündigungen, alte Aktivitäten, veraltete Plugin-Assets sowie veraltete OAuth-Servernonces');
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

    public function setUp()
    {

    }

    public function execute($last_result, $parameters = [])
    {
        $db = DBManager::get();

        // delete outdated news
        $news_deletion_days = 0;
        if($parameters['news_deletion_days'] > 0) {
            $news_deletion_days =  (int) $parameters['news_deletion_days'] * 86400;
        }
        $deleted_news = StudipNews::DoGarbageCollect($news_deletion_days);

        // delete messages
        $to_delete = $db->query("SELECT message_id, count( message_id ) AS gesamt, count(IF (deleted = 0, NULL, 1) ) AS geloescht
                FROM message_user GROUP BY message_id HAVING gesamt = geloescht")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (count($to_delete)) {
            $db->exec("DELETE FROM message_user WHERE message_id IN (" . $db->quote($to_delete) . ")");
            $db->exec("DELETE FROM message WHERE message_id IN (" . $db->quote($to_delete) . ")");
        }

        // Remove outdated opengraph urls
        $query = "DELETE FROM `opengraphdata`
                  WHERE `last_update` < UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK)";
        DBManager::get()->exec($query);

        // delete system messages
        $message_deletion_days = 0;
        if($parameters['message_deletion_days'] > 0){
            $message_deletion_days =  (int) $parameters['message_deletion_days'] * 86400;
        }
        $query = "SELECT message_id FROM message
                 WHERE autor_id = '____%system%____' 
                 AND UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(mkdate))) + ? < UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(UNIX_TIMESTAMP())))";

        $stm = $db->prepare($query);
        $stm->execute([$message_deletion_days]);
        $to_delete_system = $stm->fetchAll(PDO::FETCH_COLUMN);  

        if (count($to_delete_system) > 0) {
            $db->exec("DELETE FROM message_user WHERE message_id IN(" . $db->quote($to_delete_system) . ")");
            $db->exec("DELETE FROM message WHERE message_id IN(" . $db->quote($to_delete_system) . ")");
        }

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


        if ($parameters['verbose']) {
            printf(_("Gelöschte Ankündigungen: %u") . "\n", (int)$deleted_news);
            printf(_("Gelöschte Nachrichten: %u") . "\n", (count($to_delete) + count($to_delete_system)));
            printf(_("Gelöschte Dateianhänge: %u") . "\n", count($unsent_attachment_folders));
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
    }
}
