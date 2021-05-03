<?php
/**
 * BlubberComment
 * Model class for BlubberComments
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 *
 * @property BlubberThread thread related thread
 */

class BlubberComment extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures this model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'blubber_comments';

        $config['belongs_to']['thread'] = [
            'class_name'  => BlubberThread::class,
            'foreign_key' => 'thread_id'
        ];
        $config['belongs_to']['user'] = [
            'class_name'        => User::class,
            'foreign_key'       => 'user_id',
            'assoc_foreign_key' => 'user_id',
        ];
        $config['belongs_to']['external_user'] = [
            'class_name'        => ExternalUser::class,
            'foreign_key'       => 'user_id',
            'assoc_foreign_key' => 'external_contact_id',
        ];

        $config['registered_callbacks']['before_create'][] = 'transformMentions';
        $config['registered_callbacks']['before_create'][] = 'cbAddFollowing';
        $config['registered_callbacks']['after_create'][] = 'cbCreateNotifications';
        $config['registered_callbacks']['before_delete'][] = 'cbCreateDeleteEvent';

        parent::configure($config);
    }

    /**
     * Export available blubber comments of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ? AND external_contact = '0'", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Blubber-Kommentare'), 'blubbercomments', $field_data);
            }
        }
    }

    public function getJSONdata()
    {
        $output = $this->toRawArray();

        if ($this['external_contact']) {
            $output['user_id']       = $this->user_id;
            $output['avatar']        = $this->external_user
                ? $this->external_user['avatar_url']
                : "";
            $output['user_name']     = $this->external_user
                ? $this->external_user['name']
                : _('unbekannt');
            $output['user_username'] = '';
        } else {
            $output['user_id']       = $this->user_id;
            $output['avatar']        = Avatar::getAvatar($this['user_id'])->getURL(Avatar::MEDIUM);
            $output['user_name']     = $this->user ? $this->user->getFullName() : _('unbekannt');
            $output['user_username'] = $this->user ? $this->user['username'] : '';
        }
        $output['class']         = $this['user_id'] === $GLOBALS['user']->id ? 'mine' : 'theirs';
        $output['html']          = blubberReady($this['content']) . $this->getOpenGraphURLs()->render();
        $output['writable']      = $this->isWritable();
        $output['chdate']        = (int) $output['chdate'];
        $output['mkdate']        = (int) $output['mkdate'];
        return $output;
    }

    /**
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function isWritable(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        return $user_id === $this['user_id']
            || $GLOBALS['perm']->have_perm('root', $user_id)
            || ($this->thread['context_type'] === 'course' && $this->thread->isWritable($user_id));
    }

    public function getOpenGraphURLs()
    {
        return OpenGraph::extract($this['content']);
    }

    public function transformMentions()
    {
        BlubberThread::$mention_thread_id = $this->thread_id;
        StudipTransformFormat::addStudipMarkup(
            'mention1',
            '(?:^|\W)(@\"[^\n\"]*\")',
            '',
            'BlubberThread::mention'
        );
        StudipTransformFormat::addStudipMarkup(
            'mention2',
            '(?:^|\W)(@[^\s]*[\d\w_]+)',
            '',
            'BlubberThread::mention'
        );
        $this['content'] = \Studip\Markup::purifyHtml($this['content']);
        $this['content'] = transformBeforeSave($this['content']);
    }

    /**
     * Adds a following on a first comment to the global stream if it is
     * configured to be opt-in.
     */
    public function cbAddFollowing()
    {
        // Everything is opt-out, no need to add a following
        if (Config::get()->BLUBBER_GLOBAL_THREAD_OPTOUT) {
            return;
        }

        // Only global thread is opt-out
        if ($this->thread->id !== 'global') {
            return;
        }

        // Check whether user has commented before and thus already had
        // a following inserted
        $query = "SELECT 1
                  FROM `blubber_comments`
                  WHERE `thread_id` = :thread_id
                    AND `user_id` = :user_id";
        $has_commented = DBManager::get()->fetchColumn($query, [
            ':thread_id' => $this->thread_id,
            ':user_id'   => $GLOBALS['user']->id,
        ]);

        if ($has_commented) {
            return;
        }

        // Add following
        $this->thread->addFollowingByUser();
    }

    public function cbCreateNotifications()
    {
        $this->thread->notifyUsersForNewComment($this);
    }

    public function cbCreateDeleteEvent()
    {
        $statement = DBManager::get()->prepare("
            INSERT IGNORE INTO blubber_events_queue
            SET event_type = 'delete',
                item_id = ?,
                mkdate = UNIX_TIMESTAMP()
        ");
        $statement->execute([$this->getId()]);
        $statement = DBManager::get()->prepare("
            DELETE FROM blubber_events_queue
            WHERE mkdate <= UNIX_TIMESTAMP() - 60 * 15
        ");
        $statement->execute();
    }
}
