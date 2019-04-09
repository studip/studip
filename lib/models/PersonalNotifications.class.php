<?php
/**
 * This is an easy to use class to display personal notifications to the user,
 * that are shown in real-time. Users can deactivate the feature of personal
 * notifications, but by default it is turned on.
 *
 * For example if user A subscribed to the changes of a special wiki-page, and
 * another user B changed this wiki-page, you can inform user A about the change
 * by the following one-liner:
 *
 * PersonalNotifications::add(
 *      $user_A_user_id, //id of user A or array of ´multiple user_ids
 *      $url_of_wiki_page, //when user A clicks this URL he/she should jump directly to the changed wiki-page
 *      "User B changed wiki-page xyz", //a small text that describes the notification
 *      "wiki_page_1234", //an (optional) html-id of the content of the wiki page. If the user is looking at the content already, the notification will disappear automatically
 *      Icon::create('wiki', 'clickable'), //an (optional) icon that is displayed next to the notification-text
 * );
 *
 * Appearing to the user, deleting by the user and so on of the notification is
 * handled by this class automatically.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 *
 * @property string personal_notification_id database column
 * @property string id alias column for personal_notification_id
 * @property string url database column
 * @property string text database column
 * @property string avatar database column
 * @property string html_id database column
 * @property string mkdate database column
 */
class PersonalNotifications extends SimpleORMap
{
    const GC_MAX_DAYS = 30; // Garbage collector removes notifications after 30 days
    const CACHE_DURATION = 86400; // 24 * 60 * 60 = 1 day

    protected static function configure($config = [])
    {
        $config['db_table'] = 'personal_notifications';
        $config['additional_fields']['more_unseen'] = true;

        $config['registered_callbacks']['after_store'][] = 'cbExpireCache';
        $config['registered_callbacks']['before_delete'][] = 'cbExpireCache';

        parent::configure($config);
    }

    protected $unseen = null;

    protected function cbExpireCache($notification)
    {
        $query = "SELECT user_id
                  FROM personal_notifications_user
                  WHERE personal_notification_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $notification->id);
        $statement->execute();

        $user_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($user_ids as $user_id) {
            PersonalNotifications::expireCache($user_id);
        }
    }

    /**
     * Garbage collector the personal notifications.
     * Removes all notifications older than 30 days.
     */
    public static function doGarbageCollect()
    {
        $sql = "DELETE personal_notifications, personal_notifications_user
                FROM personal_notifications
                LEFT JOIN personal_notifications_user USING(personal_notification_id)
                WHERE mkdate < ?";
        $st = DBManager::get()->prepare($sql);
        $st->execute([time() - self::GC_MAX_DAYS * 24 * 60 * 60]);
    }

    /**
     * Central function to add a personal notification to the user. This could be
     * anything that needs to catch the attention of the user. The notification
     * will be displayed in realtime to the user and he/she can get to the url.
     * @param array|string $user_ids : array of user_ids or a single md5-user_id
     * @param string $url : URL of the point of interest of the notification
     * @param string $text : a displayed text that describes the notification
     * @param null|string $html_id : id in the html-document. If user reaches
     *   this html-element the notification will be marked as read, so the user
     *   does not need to handle the information twice. Optional. Default: null
     * @param Icon|string $avatar : either an Icon or a URL of an
     *   image for the notification. Best size: 40px x 40px
     * @return boolean : true on success
     */
    public static function add($user_ids, $url, $text, $html_id = null, $avatar = null, $dialog = false)
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        if (!count($user_ids)) {
            return false;
        }
        $notification = new PersonalNotifications();
        $notification['html_id'] = $html_id;
        $notification['url']     = $url;
        $notification['text']    = $text;
        $notification['avatar']  = $avatar instanceof Icon ? $avatar->asImagePath(40) : $avatar;
        $notification['dialog']  = $dialog ? 1 : 0;
        $notification->store();

        foreach ($user_ids as $user_id) {
            self::expireCache($user_id);

            if (self::isActivated($user_id)) {
                $assignment = new PersonalNotificationsUser();
                $assignment['personal_notification_id'] = $notification->id;
                $assignment['user_id'] = $user_id;
                $assignment['seen'] = 0;
                $assignment->store();
            }
        }
        return true;
    }

    /**
     * Returns all notifications fitting to the parameters.
     * @param boolean $only_unread : true for getting only unread notifications, false for all.
     * @param null|string $user_id : ID of special user the notification should belong to or (default:) null for current user
     * @return array of \PersonalNotifications in ascending order of mkdate
     */
    public static function getMyNotifications($only_unread = true, $user_id = null, $limit = 15)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }

        $cached = self::getCache($user_id);
        if ($cached === false) {
            $query = "SELECT pn.*, COUNT(DISTINCT personal_notification_id) - 1 AS unseen
                      FROM personal_notifications AS pn
                      INNER JOIN personal_notifications_user AS u USING (personal_notification_id)
                      WHERE u.user_id = :user_id
                        AND u.seen = IFNULL(:only_unread, u.seen)
                      GROUP BY pn.url
                      ORDER BY mkdate ASC
                      LIMIT :limit";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':only_unread', $only_unread ? '0' : null);
            $statement->bindValue(':limit', (int)$limit, StudipPDO::PARAM_COLUMN);
            $statement->execute();

            $db_data = $statement->fetchAll(PDO::FETCH_ASSOC);

            self::setCache($user_id, $db_data);
        } else {
            $db_data = $cached;
        }

        $notifications = [];
        foreach ($db_data as $data) {
            $notification = new PersonalNotifications();
            $notification->setData($data);
            $notification->more_unseen = $data['unseen'];
            $notifications[] = $notification;
        }
        return $notifications;
    }

    /**
     * Mark a notification as read by the user. It won't appear anymore in the
     * notification-list on top of its site.
     * @param string $notification_id : ID of the notification
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     * @return boolean : true on success, false if it failed.
     */
    public static function markAsRead($notification_id, $user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        self::expireCache($user_id);

        $pn = new PersonalNotifications($notification_id);
        $notification_users = PersonalNotificationsUser::findBySQL("INNER JOIN personal_notifications USING (personal_notification_id) WHERE user_id = :user_id AND seen = '0' AND personal_notifications.url = :url ", [
            'user_id' => $user_id,
            'url' => $pn['url']
        ]);
        foreach ($notification_users as $notification_user) {
            $notification_user['seen'] = 1;
            $notification_user->store();
        }
    }

    /**
     * Marks all notifications as read by the user. It won't appear anymore in
     * the notification-list on top of its site.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     * @return boolean : true on success, false if it failed.
     */
    public static function markAllAsRead($user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        self::expireCache($user_id);

        $notification_users = PersonalNotificationsUser::findBySQL("user_id = :user_id AND seen = '0'", [
            'user_id' => $user_id
        ]);
        foreach ($notification_users as $notification_user) {
            $notification_user['seen'] = 1;
            $notification_user->store();
        }
        return true;
    }

    /**
     * Mark a notification as read for the user by the given HTML-ID. It won't appear anymore in the
     * notification-list on top of its site.
     * @param string $html_id : HTML ID attribute of the notification
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     * @return boolean : true on success, false if it failed.
     */
    public static function markAsReadByHTML($html_id, $user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        self::expireCache($user_id);

        $notification_users = PersonalNotificationsUser::findBySQL("INNER JOIN personal_notifications USING (personal_notification_id) WHERE user_id = :user_id AND seen = '0' AND personal_notifications.html_id = :html_id ", [
            'user_id' => $user_id,
            'html_id' => $html_id
        ]);
        foreach ($notification_users as $notification_user) {
            $notification_user['seen'] = 1;
            $notification_user->store();
        }
    }

    /**
     * Returns the cache hash to use for a specific user.
     *
     * @param String $user_id Id of the user
     * @return String Cache hash to use for the user
     */
    protected static function getCacheHash($user_id)
    {
        return '/personal-notifications/' . $user_id;
    }

    /**
     * Returns the cached values for a specific user.
     *
     * @param String $user_id Id of the user
     * @return mixed Array of item data (may be empty) or false if no data is cached
     */
    protected static function getCache($user_id)
    {
        $cache  = StudipCacheFactory::getCache();
        $hash   = self::getCacheHash($user_id);
        $cached = $cache->read($hash);

        if ($cached === false) {
            return false;
        }

        return unserialize($cached);
    }

    /**
     * Stored the provided item data in cache for a specific user.
     *
     * @param String $user_id Id of the user
     * @param Array  $items   Raw db data of the items
     */
    protected static function setCache($user_id, $items)
    {
        $cache = StudipCacheFactory::getCache();
        $hash  = self::getCacheHash($user_id);
        $cache->write($hash, serialize($items), self::CACHE_DURATION);
    }

    /**
     * Removes the cached entries for a specific user.
     *
     * @param String $user_id Id of the user
     */
    protected static function expireCache($user_id)
    {
        $cache = StudipCacheFactory::getCache();
        $hash  = self::getCacheHash($user_id);
        $cache->expire($hash);
    }

    /**
     * Activates personal notifications for a given user.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     */
    public static function activate($user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_DEACTIVATED", "0");
    }

    /**
     * Deactivates personal notifications for a given user.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     */
    public static function deactivate($user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_DEACTIVATED", "1");
    }

    /**
     * Activates audio plopp for new personal notifications for a given user.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     */
    public static function activateAudioFeedback($user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_AUDIO_DEACTIVATED", "0");
    }

    /**
     * Deactivates audio plopp for new personal notifications for a given user.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     */
    public static function deactivateAudioFeedback($user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_AUDIO_DEACTIVATED", "1");
    }

    /**
     * Checks if personal notifications are activated for the whole Stud.IP. This
     * could be false for performance issues.
     * @return boolean : true if activated else false
     */
    public static function isGloballyActivated()
    {
        $config = Config::GetInstance();
        return !empty($config['PERSONAL_NOTIFICATIONS_ACTIVATED']);
    }

    /**
     * Checks if a given user should see the personal notification. Either the
     * Stud.IP or the user could deactivate personal notification. If neither is
     * the case, this function returns true.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     * @return boolean : true if activated else false
     */
    public static function isActivated($user_id = null)
    {
        if (!PersonalNotifications::isGloballyActivated()) {
            return false;
        }

        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        return UserConfig::get($user_id)->getValue("PERSONAL_NOTIFICATIONS_DEACTIVATED") ? false : true;
    }

    /**
     * Checks if a given user should hear audio plopp for new personal notification.
     * Either the Stud.IP or the user could deactivate personal notification or
     * audio feedback. If neither is the case, this function returns true.
     * @param string|null $user_id : ID of special user the notification should belong to or (default:) null for current user
     * @return boolean : true if activated else false
     */
    public static function isAudioActivated($user_id = null)
    {
        if (!PersonalNotifications::isGloballyActivated()) {
            return false;
        }
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        return UserConfig::get($user_id)->getValue("PERSONAL_NOTIFICATIONS_AUDIO_DEACTIVATED") ? false : true;
    }

    /**
     * Returns HTML-represantation of the notification which is a list-element.
     * @return string : html-output;
     */
    public function getLiElement()
    {
        return $GLOBALS['template_factory']
                ->open('personal_notifications/notification.php')
                ->render(['notification' => $this]);
    }

    /**
     * Sets the value of the "more unseen" notifications (notification with same url but a different id).
     *
     * @param int $unseen Number of more unseen notifications
     */
    public function setmore_unseen($unseen)
    {
        $this->unseen = (int)$unseen;
    }

    /**
     * Returns (or retrieves) the number of "more unseen" notifications.
     *
     * @return int Number of "more unseen" notifications
     */
    public function getmore_unseen()
    {
        if ($this->unseen === null) {
            $query = "SELECT COUNT(*)
                      FROM personal_notifications AS pn
                      INNER JOIN personal_notifications_user AS u USING (personal_notification_id)
                      WHERE pn.personal_notification_id != :pn_id
                        AND u.user_id = :user_id
                        AND u.seen = '0'
                        AND pn.url = :url";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                ':pn_id'   => $this->id,
                ':user_id' => $GLOBALS['user']->id,
                ':url'     => $this->url,
            ]);
            $this->unseen = 0 + $statement->fetchColumn();
        }
        return $this->unseen;
    }

}
