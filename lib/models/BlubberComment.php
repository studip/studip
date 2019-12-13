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

        $config['registered_callbacks']['before_create'][] = 'transformMentions';
        $config['registered_callbacks']['after_create'][] = 'cbCreateNotifications';

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
        $output['avatar']        = Avatar::getAvatar($this['user_id'])->getURL(Avatar::MEDIUM);
        $output['user_name']     = $this->user ? $this->user->getFullName() : _('unbekannt');
        $output['user_username'] = $this->user ? $this->user['username'] : '';
        $output['class']         = $this['user_id'] === $GLOBALS['user']->id ? 'mine' : 'theirs';
        $output['html']          = formatReady($this['content']) . $this->getOpenGraphURLs()->render();
        $output['writable']      = $this->isWritable();
        $output['chdate']        = (int) $output['chdate'];
        $output['mkdate']        = (int) $output['mkdate'];
        return $output;
    }

    public function isWritable()
    {
        return $GLOBALS['user']->id === $this['user_id']
            || $GLOBALS['perm']->have_perm('root')
            || ($this->thread['context_type'] === 'course' && $this->thread->isWritable());
    }

    public function getOpenGraphURLs()
    {
        return OpenGraph::extract($this['content']);
    }

    public function cbCreateNotifications()
    {
        $this->thread->notifyUsersForNewComment($this);
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
        $this['content'] = transformBeforeSave($this['content']);
    }
}
