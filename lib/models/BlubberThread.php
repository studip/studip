<?php
/**
 * BlubberThread
 * Model class for BlubberThreads
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

class BlubberThread extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures this model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'blubber_threads';

        $config['has_many']['comments'] = [
            'class_name' => BlubberComment::class,
            'on_store'   => 'store',
            'on_delete'  => 'delete',
            'order_by'   => 'ORDER BY mkdate ASC'
        ];
        $config['has_many']['mentions'] = [
            'class_name' => BlubberMention::class,
            'on_store'   => 'store',
            'on_delete'  => 'delete',
        ];
        $config['belongs_to']['user'] = [
            'class_name'        => User::class,
            'foreign_key'       => 'user_id',
            'assoc_foreign_key' => 'user_id',
        ];

        $config['serialized_fields']['metadata'] = 'JSONArrayObject';

        parent::configure($config);
    }

    public static $mention_thread_id = null;
    protected $last_visit = null;

    /**
     * Pre-Markup rule. Recognizes mentions in blubber as @username or @"Firstname lastname"
     * and turns them into usual studip-links. The mentioned person is notified by
     * sending a message to him/her as a side-effect.
     * @param StudipTransformFormat $markup
     * @param array $matches
     * @return string
     */
    public static function mention($markup, $matches)
    {
        $mention = $matches[1];
        $thread = self::find(self::$mention_thread_id);
        $username = stripslashes(mb_substr($mention, 1));
        if ($username[0] !== '"') {
            $user = User::findByUsername($username);
        } else {
            $name = mb_substr($username, 1, -1); // Strip quotes
            $user = User::findOneBySQL("CONCAT(Vorname, ' ', Nachname) = ?", [$name]);
        }
        if ($user
            && !$thread->isNew()
            && $user->getId()
            && $user->getId() !== $GLOBALS['user']->id
        ) {
            if ($thread['context_type'] === 'private') {
                $mention = new BlubberMention();
                $mention['thread_id'] = $thread->getId();
                $mention['user_id'] = $user->getId();
                $mention->store();
            } elseif ($thread['context_type'] === 'public') {
                PersonalNotifications::add(
                    $user->getId(),
                    $thread->getURL(),
                    sprintf(_('%s hat Sie in einem Blubber erwähnt.'), get_fullname()),
                    'blubberthread_' . $thread->getId(),
                    Icon::create('blubber'),
                    true
                );
            }
            $oldbase = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $url = URLHelper::getLink('dispatch.php/profile', ['username' => $user->username]);
            URLHelper::setBaseURL($oldbase);

            return str_replace(
                $matches[1],
                '[' . $user->getFullName() . ']' . $url . ' ',
                $matches[0]
            );
        }

        return $markup->quote($matches[0]);
    }

    public static function findBySQL($sql, $params = [])
    {
        return parent::findAndMapBySQL(function ($thread) {
            return self::upgradeThread($thread);
        }, $sql, $params);
    }

    public static function find($id)
    {
        return self::upgradeThread(parent::find($id));
    }

    /**
     * Checks if a BlubberThread has a display_class and returns an instance of
     * display_class with the same data. Otherwise returns BlubberThread.
     * @param BlubberThread|boolean $thread : instance of BlubberThread or false
     * @return BlubberThread|boolean
     */
    public static function upgradeThread($thread)
    {
        if ($thread
            && $thread['display_class']
            && $thread['display_class'] !== 'BlubberThread'
            && is_subclass_of($thread['display_class'], 'BlubberThread')
        ) {
            $class = $thread['display_class'];
            $display_thread = $class::buildExisting($thread->toRawArray());
            return $display_thread;
        }

        return $thread;
    }

    /**
     * @param string $limit      optional; limits the number of results
     * @param string $since      optional; selects threads after this date (exclusive)
     * @param string $olderthan  optional; selects threads before this date (exclusive)
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     * @param string $search     optional; filters the threads by a search string
     *
     * @return array  an array of the user's global BlubberThreads
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function findMyGlobalThreads($limit = 51, $since = null, $olderthan = null, string $user_id = null, $search = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;

        $condition = "LEFT JOIN blubber_comments
                        ON blubber_comments.thread_id = blubber_threads.thread_id
                      WHERE (blubber_threads.content IS NULL OR blubber_threads.content = '')
                        AND blubber_comments.comment_id IS NULL
                        AND (display_class IS NULL OR display_class = 'BlubberThread')
                        AND UNIX_TIMESTAMP() - blubber_threads.mkdate > 60 * 60";
        self::deleteBySQL($condition);



        $query = SQLQuery::table('blubber_threads')
            ->join('my_comments', 'blubber_comments', 'blubber_threads.thread_id = my_comments.thread_id', 'LEFT JOIN')
            ->join('blubber_mentions', 'blubber_mentions', 'blubber_mentions.thread_id = blubber_threads.thread_id', 'LEFT JOIN');

        if (!$GLOBALS['perm']->have_perm('admin', $user_id)) {
            //user, autor, tutor, dozent
            $query->where('mycourses', implode(' OR ', [
                "(blubber_threads.context_type = 'public' AND (my_comments.user_id = :user_id OR blubber_threads.user_id = :user_id OR blubber_threads.thread_id = 'global'))",
                "(blubber_threads.context_type = 'course' AND blubber_threads.context_id IN (:seminar_ids))",
                "(blubber_threads.context_type = 'institute' AND blubber_threads.context_id IN (:institut_ids))",
                "(blubber_threads.context_type = 'private' AND blubber_mentions.user_id = :user_id AND blubber_mentions.external_contact = 0)",
            ]), [
                'seminar_ids'  => self::getMyBlubberCourses($user_id),
                'institut_ids' => self::getMyBlubberInstitutes($user_id),
            ]);
        } elseif (!$GLOBALS['perm']->have_perm('root', $user_id)) {
            //admin
            $query->where('mycourses', implode(' OR ', [
                "(blubber_threads.context_type = 'public' AND (my_comments.user_id = :user_id OR blubber_threads.user_id = :user_id OR blubber_threads.thread_id = 'global'))",
                "(blubber_threads.context_type = 'institute' AND blubber_threads.context_id IN (:institut_ids))",
                "(blubber_threads.context_type = 'private' AND blubber_mentions.user_id = :user_id AND blubber_mentions.external_contact = 0)",
            ]), ['institut_ids' => self::getMyBlubberInstitutes($user_id)]);
        } else {
            //root
            $query->where(implode(' OR ', [
                "((blubber_threads.context_type = 'public' OR blubber_threads.context_type IN ('course', 'institute')) AND (my_comments.user_id = :user_id OR blubber_threads.user_id = :user_id OR blubber_threads.thread_id = 'global'))",
                "(blubber_threads.context_type = 'private' AND blubber_mentions.user_id = :user_id AND blubber_mentions.external_contact = '0')",
            ]));
        }
        $query->where("blubber_threads.visible_in_stream = 1");
        $query->parameter('user_id', $user_id);
        $query->groupBy('blubber_threads.thread_id');

        $thread_ids = $query->fetchAll("thread_id");

        $threads = [];

        foreach ($threads as $thread) {
            if ($since) {
                $active_time = $thread->getLatestActivity();
                $since = max($since, $active_time);
            }
            if ($olderthan) {
                $active_time = $thread->getLatestActivity();
                $olderthan = min($olderthan, $active_time);
            }
        }

        do {
            list($newthreads, $filtered, $new_since, $new_olderthan) = self::getOrderedThreads(
                $thread_ids,
                $limit - count($threads),
                $since,
                $olderthan,
                $user_id,
                $search
            );

            if ($since) {
                $since = max($since, $new_since);
            }
            if ($olderthan) {
                $olderthan = min($olderthan, $new_olderthan);
            } else {
                $olderthan = $new_olderthan;
            }
            $threads = array_merge($threads, $newthreads);
        } while ($filtered && $limit);

        return $threads;
    }

    /**
     * This method is used to get the ordered (upgraded) threads. Because a thread is also able to
     * manage its own visibility and not only pure SQL, we need to execute
     * @param $thread_ids
     * @param string $limit      optional; limits the number of results
     * @param string $since      optional; selects threads after this date (exclusive)
     * @param string $olderthan  optional; selects threads before this date (exclusive)
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     * @param string $search     optional; filters the threads by a search string
     * @return array
     */
    protected static function getOrderedThreads($thread_ids, $limit = 51, $since = null, $olderthan = null, string $user_id = null, $search = null)
    {
        $query = SQLQuery::table('blubber_threads')->join(
            'blubber_comments',
            'blubber_comments', 'blubber_threads.thread_id = blubber_comments.thread_id',
            'LEFT JOIN'
        );

        $query->where(
            "filter_thread_ids",
            "blubber_threads.thread_id IN (:thread_ids)",
            ['thread_ids' => $thread_ids]
        );
        if ($search !== null) {
            $query->where(
                "search",
                "(blubber_threads.content LIKE :search OR blubber_comments.content LIKE :search)",
                ['search' => '%' . $search . '%']
            );
        }
        if ($since !== null) {
            $query->where(
                'since',
                '(blubber_comments.mkdate > :since OR blubber_threads.mkdate > :since)',
                compact('since')
            );
        }
        $query->groupBy('blubber_threads.thread_id');
        if ($olderthan !== null) {
            $query->having(
                'olderthan',
                "IFNULL(MAX(blubber_comments.mkdate), blubber_threads.mkdate) < :olderthan",
                ['olderthan' => $olderthan]
            );
        }
        $query->orderBy("IFNULL(MAX(blubber_comments.mkdate), blubber_threads.mkdate) DESC");
        $query->limit($limit);

        $threads = $query->fetchAll(static::class);

        $upgraded_threads = array_map(function ($thread) {
            return self::upgradeThread($thread);
        }, $threads);

        $since = 0;
        $olderthan = time();
        foreach ($upgraded_threads as $thread) {
            $active_time = $thread->getLatestActivity();
            $since = max($since, $active_time);
            $olderthan = min($olderthan, $active_time);
        }

        $old_count = count($upgraded_threads);

        $upgraded_threads = array_filter($upgraded_threads, function ($thread) use ($user_id) {
            return $thread->isVisibleInStream() && $thread->isReadable($user_id);
        });

        return [$upgraded_threads, $old_count !== count($upgraded_threads), $since, $olderthan];
    }

    /**
     * @param string $institut_id  the ID of an institute
     * @param string $only_in_stream  optional; filter threads by `visible_in_stream`
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     */
    public static function findByInstitut($institut_id, $only_in_stream = false, string $user_id = null)
    {
        return self::findByContext($institut_id, $only_in_stream, 'institute', $user_id);
    }

    /**
     * @param string $seminar_id  the ID of a course
     * @param string $only_in_stream  optional; filter threads by `visible_in_stream`
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     */
    public static function findBySeminar($seminar_id, $only_in_stream = false, string $user_id = null)
    {
        return self::findByContext($seminar_id, $only_in_stream, 'course', $user_id);
    }

    /**
     * @param string $seminar_id  the ID of a course
     * @param string $only_in_stream  optional; filter threads by `visible_in_stream`
     * @param string $context_type  optional; filter threads by `context_type`
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     */
    public static function findByContext($context_id, $only_in_stream = false, $context_type = 'course', string $user_id = null)
    {
        if (!BlubberThread::findOneBySQL("context_type = :type AND context_id = :context_id AND visible_in_stream = '1' AND content IS NULL AND display_class IS NULL", ['context_id' => $context_id, 'type' => $context_type])) {
            //create the default-thread for this context
            $coursethread = new BlubberThread();
            $coursethread['user_id'] = $user_id ?? $GLOBALS['user']->id;
            $coursethread['external_contact'] = 0;
            $coursethread['context_type'] = $context_type;
            $coursethread['context_id'] = $context_id;
            $coursethread['visible_in_stream'] = 1;
            $coursethread['commentable'] = 1;
            $coursethread->store();
        }
        $query = SQLQuery::table('blubber_threads')
            ->join('blubber_comments', 'blubber_comments', 'blubber_threads.thread_id = blubber_comments.thread_id', 'LEFT JOIN');
        if ($only_in_stream) {
            $query->where("blubber_threads.visible_in_stream = 1");
        }
        $query->where("context", "blubber_threads.context_type = :context_type AND blubber_threads.context_id = :context_id", [
            'context_id' => $context_id,
            'context_type' => $context_type
        ]);
        $query->groupBy('blubber_threads.thread_id');
        $query->orderBy("IFNULL(MAX(blubber_comments.mkdate), blubber_threads.mkdate) DESC");

        $threads = $query->fetchAll(static::class);

        $threads = array_map(function ($thread) {
            return self::upgradeThread($thread);
        }, $threads);
        $threads = array_filter($threads, function ($t) use ($user_id){
            return $t->isVisibleInStream() && $t->isReadable($user_id);
        });
        return $threads;
    }

    /**
     * Export available blubber threads of a given user into a storage object
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
                $storage->addTabularData(_('Blubber-Threads'), 'blubberthreads', $field_data);
            }
        }
    }

    public function getName()
    {
        if ($this['context_type'] === 'public') {
            return sprintf(_('Blubber von %s'), $this->user ? $this->user->getFullName() : _('unbekannt'));
        }

        if ($this['context_type'] === 'private') {
            $query = "SELECT IFNULL(external_users.name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)) AS name
                      FROM blubber_mentions
                      LEFT JOIN auth_user_md5
                        ON blubber_mentions.user_id = auth_user_md5.user_id
                           AND blubber_mentions.external_contact = 0
                      LEFT JOIN external_users
                        ON external_users.external_contact_id = blubber_mentions.user_id
                           AND blubber_mentions.external_contact = 1
                      WHERE blubber_mentions.thread_id = :thread_id
                        AND blubber_mentions.user_id != :me
                      ORDER BY name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                'thread_id' => $this->getId(),
                'me'        => $GLOBALS['user']->id,
            ]);
            $names = $statement->fetchFirst();
            $names = array_map(function ($name) {
                return $name ?? _('unbekannt');
            }, $names);

            $names[] = _('ich');
            $names = implode(', ', $names);
            return mb_substr($names, 0, 60);
        }

        if($this['context_type'] === 'course') {
            if ($this['content']) {
                return mb_substr((string) Course::find($this['context_id'])->name . ': ' . $this['content'], 0, 50) . ' ...';
            } else {
                return (string) Course::find($this['context_id'])->name;
            }
        }

        if ($this['context_type'] === 'institute') {
            if ($this['content']) {
                return mb_substr((string) Institute::find($this['context_id'])->name . ': ' . $this['content'], 0, 50) . ' ...';
            } else {
                return (string) Institute::find($this['context_id'])->name;
            }
        }

        return _('Ein mysteröser Blubber');
    }

    public function getContentTemplate()
    {
        $template = $GLOBALS['template_factory']->open('blubber/thread_content');
        $template->thread = $this;
        return $template;
    }

    /**
     * Returns a template (or null) to display this in the context container
     */
    public function getContextTemplate()
    {
        if ($this['context_type'] === 'course') {
            $course = Course::find($this['context_id']);
            $icons = [];
            $schedule_active = false;
            foreach ($course->tools as $tool) {
                if ($module = $tool->getStudipModule()) {
                    $last_visit = object_get_visit($this['context_id'], $module->getPluginId());
                    $nav = $module->getIconNavigation($this['context_id'], $last_visit, $GLOBALS['user']->id);
                    if ($nav) {
                        $icons[] = $nav;
                    }
                    if ($module instanceof CoreSchedule) {
                        $schedule_active = true;
                    }
                }
            }

            $nextdate = false;
            if ($schedule_active) {
                $nextdate = CourseDate::findOneBySQL("range_id = ? AND `date` >= UNIX_TIMESTAMP() ORDER BY `date` ASC", [$this['context_id']]);
            }

            $teachers       = CourseMember::findBySQL("Seminar_id = ? AND status = 'dozent' ORDER BY position ASC", [$this['context_id']]);
            $tutors         = CourseMember::findBySQL("Seminar_id = ? AND status = 'tutor' ORDER BY position ASC", [$this['context_id']]);
            $students_count = CourseMember::countBySQL("Seminar_id = ? AND status IN ('autor', 'user') ORDER BY position ASC", [$this['context_id']]);

            $template = $GLOBALS['template_factory']->open('blubber/course_context');
            $template->thread         = $this;
            $template->course         = $course;
            $template->icons          = $icons;
            $template->nextdate       = $nextdate;
            $template->teachers       = $teachers;
            $template->tutors         = $tutors;
            $template->students_count = $students_count;
            $template->hashtags       = $this->getHashtags();
            $template->unfollowed     = !$this->isFollowedByUser();
            return $template;
        }

        if ($this['context_type'] === 'private') {
            $query = "SELECT *
                      FROM blubber_mentions
                      LEFT JOIN auth_user_md5
                        ON blubber_mentions.user_id = auth_user_md5.user_id
                           AND blubber_mentions.external_contact = 0
                      LEFT JOIN external_users
                        ON blubber_mentions.user_id = external_users.external_contact_id
                           AND blubber_mentions.external_contact = 1
                      WHERE thread_id = ?
                      ORDER BY IFNULL(external_users.name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname))";
            $mentions = DBManager::get()->prepare($query);
            $mentions->execute([$this->getId()]);

            $template = $GLOBALS['template_factory']->open('blubber/private_context');
            $template->thread = $this;
            $template->mentions = $mentions->fetchAll(PDO::FETCH_ASSOC);
            return $template;
        }

        if ($this['context_type'] === 'public') {
            $template = $GLOBALS['template_factory']->open('blubber/public_context');
            $template->thread = $this;
            return $template;
        }

        if ($this['context_type'] === 'institute') {
            $template = $GLOBALS['template_factory']->open('blubber/institute_context');
            $template->thread = $this;
            $template->institute = Institute::find($this['context_id']);
            return $template;
        }
    }

    /**
     * Lets a user follow a thread
     *
     * @param string|null $user_id Id of the user (optional, defaults to current user
     */
    public function addFollowingByUser($user_id = null)
    {
        $query = "DELETE FROM `blubber_threads_followstates`
                  WHERE `thread_id` = :thread_id
                    AND `user_id` = :user_id";
        DBManager::get()->execute($query, [
            ':thread_id' => $this->id,
            ':user_id'   => $user_id ?? $GLOBALS['user']->id,
        ]);
    }

    /**
     * Lets a user unfollow a thread
     *
     * @param string|null $user_id Id of the user (optional, defaults to current user
     */
    public function removeFollowingByUser($user_id = null)
    {
        $query = "REPLACE INTO `blubber_threads_followstates`
                  VALUES (:thread_id, :user_id, 'unfollowed', UNIX_TIMESTAMP())";
        DBManager::get()->execute($query, [
            ':thread_id' => $this->id,
            ':user_id'   => $user_id ?? $GLOBALS['user']->id,
        ]);
    }

    /**
     * Returns whether a user follows a thread.
     *
     * @param string|null $user_id Id of the user (optional, defaults to current user
     * @return bool
     */
    public function isFollowedByUser($user_id = null)
    {
        $query = "SELECT 1
                  FROM `blubber_threads_followstates`
                  WHERE `thread_id` = :thread_id
                    AND `user_id` = :user_id
                    AND `state` = 'unfollowed'";
        $unfollowed = (bool) DBManager::get()->fetchColumn($query, [
            ':thread_id' => $this->id,
            ':user_id'   => $user_id ?? $GLOBALS['user']->id,
        ]);

        return !$unfollowed;
    }

    public function getOpenGraphURLs()
    {
        return OpenGraph::extract($this['content']);
    }

    public function getLatestActivity()
    {
        $newest_comment = BlubberComment::findOneBySQL("thread_id = ? ORDER BY mkdate DESC", [$this->getId()]);
        return $newest_comment ? $newest_comment['mkdate'] : $this['mkdate'];
    }

    public function getURL()
    {
        if (($this['context_type'] === "course") || ($this['context_type'] === "institute")) {
            return URLHelper::getURL('plugins.php/blubber/messenger/course/' . $this->getId(), ['cid' => $this['context_id']]);
        }
        return URLHelper::getURL('dispatch.php/blubber/index/' . $this->getId());
    }

    /**
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getLastVisit(string $user_id = null)
    {
        return UserConfig::get($user_id ?? $GLOBALS['user']->id)->getValue("BLUBBERTHREAD_VISITED_".$this->getId());
    }

    public function notifyUsersForNewComment($comment)
    {
        $data = $this->getNotificationUsersQueryAndParameters();

        if ($data === false) {
            return;
        }

        $query = "SELECT user_id, `preferred_language` AS language
                  FROM `user_info`
                  WHERE `user_id` IN (
                      {$data['query']}
                  )";

        $statement = DBManager::get()->prepare($query);
        foreach ($data['parameters'] as $key => $value) {
            $statement->bindValue($key, $value);
        }
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $notifications = [];
        foreach ($statement as $row) {
            $user_id  = $row['user_id'];
            $language = $row['language'] ?? Config::get()->DEFAULT_LANGUAGE;

            if (!isset($notifications[$language])) {
                setTempLanguage(false, $language);

                $notifications[$language] = PersonalNotifications::create([
                    'url'     => $this->getURL(),
                    'text'    => sprintf(_('%s hat eine Nachricht geschrieben.'), get_fullname()),
                    'avatar'  => Icon::create('blubber')->asImagePath(),
                    'dialog'  => true,
                    'html_id' => "blubberthread_{$this->id}",
                ]);

                restoreLanguage();
            }

            $notifications[$language]->link($user_id);
        }
    }

    /**
     * Returns an array that includes the query and parameters to retrieve the
     * user ids of all users that should be notified by a new post in this
     * thread.
     *
     * The array needs to have the following structure:
     *
     * [
     *     'query' => ...,
     *     'parameters' => ...
     * ]
     *
     * @return array|false
     */
    protected function getNotificationUsersQueryAndParameters()
    {
        // Default set of parameters
        $parameters = [
            ':thread_id' => $this->id,
            ':user_id'   =>  $GLOBALS['user']->id,
        ];

        // Public context: Notify all users that participated
        if ($this->context_type === 'public') {
            $query = "SELECT DISTINCT `user_id`
                      FROM `blubber_comments`
                      WHERE `thread_id` = :thread_id
                          AND `external_contact` = 0
                          AND `user_id` != :user_id";

            if (!$this->external_contact && $this->user_id !== $GLOBALS['user']->id) {
                $query .= " UNION SELECT '{$this->user_id}' AS `user_id`";
            }

            return compact('query', 'parameters');
        }

        // Private context: Notify all mentioned users
        if ($this->context_type === 'private') {
            $query = "SELECT user_id
                      FROM blubber_mentions
                      WHERE thread_id = :thread_id
                        AND external_contact = 0
                        AND user_id != :user_id";

            return compact('query', 'parameters');
        }

        // Course context: Notify all members of the course except the ones that
        // turned the notifications off
        if ($this->context_type === 'course') {
            $query = "SELECT seminar_user.user_id
                      FROM seminar_user
                      LEFT JOIN blubber_threads_followstates ON (
                          seminar_user.user_id = blubber_threads_followstates.user_id
                          AND blubber_threads_followstates.thread_id = :thread_id
                          AND blubber_threads_followstates.state = 'unfollowed'
                      )
                      WHERE seminar_user.Seminar_id = :context_id
                          AND seminar_user.user_id != :user_id
                          AND blubber_threads_followstates.user_id IS NULL";

            $parameters[':context_id'] = $this->context_id;

            return compact('query', 'parameters');
        }

        // Institute context: Notify all members of the institute
        if ($this->context_type === 'institute') {
            $query = "SELECT user_id
                      FROM user_inst
                      WHERE Institut_id = :context_id
                          AND user_id != :user_id";

            unset($parameters[':thread_id']);
            $parameters[':context_id'] = $this->context_id;

            return compact('query', 'parameters');
        }

        return false;
    }

    public function isVisibleInStream()
    {
        return $this['visible_in_stream'];
    }

    /**
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function isWritable(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        if ($this['context_type'] === 'course' || $this['context_type'] === 'institute') {
            return $GLOBALS['perm']->have_studip_perm('tutor', $this['context_id'], $user_id);
        } else {
            return $GLOBALS['perm']->have_perm('root', $user_id) || $this['user_id'] === $user_id;
        }
    }

    /**
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function isReadable(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        if ($this['context_type'] === 'public') {
            return true;
        }

        if ($this['context_type'] === 'private') {
            $query = "SELECT 1
                      FROM blubber_mentions
                      WHERE thread_id = :thread_id
                        AND user_id = :me
                        AND external_contact = 0";
            return (bool) DBManager::get()->fetchColumn($query, [
                'me'        => $user_id,
                'thread_id' => $this->getId()
            ]);
        }

        if (in_array($this['context_type'], ['course', 'institute'])) {
            return $GLOBALS['perm']->have_studip_perm('user', $this['context_id'], $user_id);
        }

        return false;
    }

    /**
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     */
    public function isCommentable(string $user_id = null)
    {
        return $this->isReadable($user_id) && $this['commentable'];
    }

    public function getAvatar()
    {
        if ($this['context_type'] === 'course') {
            return CourseAvatar::getAvatar($this['context_id'])->getURL(Avatar::MEDIUM);
        }

        if ($this['context_type'] === 'institute') {
            return InstituteAvatar::getAvatar($this['context_id'])->getURL(Avatar::MEDIUM);
        }

        if ($this['context_type'] === 'private') {
            $query = "SELECT user_id, external_contact
                      FROM blubber_mentions
                      WHERE thread_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->getId()]);
            $mentions = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($mentions) === 1) {
                return Avatar::getAvatar($mentions[0]['user_id'])->getURL(Avatar::MEDIUM);
            }

            if (count($mentions) === 2 && $mentions[0]['user_id'] === $GLOBALS['user']->id && !$mentions[0]['external_contact']) {
                return Avatar::getAvatar($mentions[1]['user_id'])->getURL(Avatar::MEDIUM);
            }

            if (count($mentions) === 2 && $mentions[1]['user_id'] === $GLOBALS['user']->id && !$mentions[1]['external_contact']) {
                return Avatar::getAvatar($mentions[0]['user_id'])->getURL(Avatar::MEDIUM);
            }

            return Icon::create('group3')->asImagePath();
        }

        if ($this['context_type'] === 'public') {
            return Icon::create('globe')->asImagePath();
        }

        return CourseAvatar::getNobody()->getURL(Avatar::MEDIUM);
    }

    public function getJSONData($limit_comments = 50, $user_id = null, $search = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        $output = [
            'thread_posting'  => $this->toRawArray(),
            'context_info'    => '',
            'comments'        => [],
            'more_up'         => 0,
            'more_down'       => 0,
            'unseen_comments' => BlubberComment::countBySQL("thread_id = ? AND mkdate >= ? AND user_id != ?", [
                $this->getId(),
                $this->getLastVisit() ?: object_get_visit_threshold(),
                $user_id
            ]),
            'notifications' => $this->id === 'global' || ($this->context_type === 'course' && !$GLOBALS['perm']->have_perm('admin')),
            'followed' => $this->isFollowedByUser(),
        ];
        $context_info = $this->getContextTemplate();
        if ($context_info) {
            $output['context_info'] = $context_info->render();
        }
        $output['thread_posting']['name'] = $this->getName();
        $output['thread_posting']['user_name'] = $this->user ? $this->user->getFullName() : _("unbekannt");
        $output['thread_posting']['user_username'] = $this->user ? $this->user['username'] : "";
        $output['thread_posting']['avatar'] = Avatar::getAvatar($this['user_id'])->getURL(Avatar::MEDIUM);
        $output['thread_posting']['html'] = $this->getContentTemplate()->render();
        $output['thread_posting']['writable'] = $this->isWritable() ? 1 : 0;
        $output['thread_posting']['chdate'] = (int) $output['thread_posting']['chdate'];
        $output['thread_posting']['mkdate'] = (int) $output['thread_posting']['mkdate'];

        if ($search) {
            $query = "SELECT blubber_comments.*
                      FROM blubber_comments
                      WHERE blubber_comments.thread_id = :thread_id
                          AND content LIKE :search
                      ORDER BY mkdate DESC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                'thread_id' => $this->getId(),
                'search'    => '%' . $search . '%'
            ]);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT blubber_comments.*
                      FROM blubber_comments
                      WHERE blubber_comments.thread_id = :thread_id
                      ORDER BY mkdate DESC
                      LIMIT :limit";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                'thread_id' => $this->getId(),
                'limit' => $limit_comments + 1,
            ]);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > $limit_comments) {
                $output['more_up'] = 1;
            }
        }

        foreach ($result as $data) {
            $comment = BlubberComment::buildExisting($data);
            $output['comments'][] = $comment->getJSONData();
        }

        return $output;
    }

    /**
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function markAsRead(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;

        $statement = DBManager::get()->prepare("
            UPDATE personal_notifications_user
                INNER JOIN personal_notifications USING (personal_notification_id)
            SET personal_notifications_user.seen = '1'
            WHERE personal_notifications_user.user_id = :user_id
                AND personal_notifications.html_id = :html_id
        ");
        $statement->execute([
            'user_id' => $user_id,
            'html_id' => "blubberthread_".$this->getId()
        ]);
        $this->last_visit[$user_id] = !$this->last_visit[$user_id]
            ? object_get_visit($this->getId(), "blubberthread", "last", "", $user_id)
            : $this->last_visit[$user_id];
        UserConfig::get($user_id)->store("BLUBBERTHREAD_VISITED_".$this->getId(), time());
    }

    public function getHashtags($since = null)
    {
        $query = "
            SELECT *
            FROM blubber_comments
            WHERE thread_id = ".DBManager::get()->quote($this->getId())."
                AND content REGEXP '(^|[[:blank:]]|[[:cntrl:]])#[[:graph:]]' > 0
        ";
        if ($since) {
            $get_hashtags = DBManager::get()->query($query ."
                    AND mkdate > ".DBManager::get()->quote($since)."
            ");
        } else {
            $get_hashtags = DBManager::get()->query($query);
        }
        $hashtags = [];
        foreach ($get_hashtags->fetchAll(PDO::FETCH_ASSOC) as $comment_data) {
            $matched = preg_match_all(
                '/'. BlubberFormat::REGEXP_HASHTAG . '/uS',
                $comment_data['content'],
                $matches
            );

            if ($matched === 0) {
                continue;
            }

            foreach ($matches[1] as $tag) {
                $hashtags[mb_strtolower($tag)] += 1;
            }
        }
        asort($hashtags);
        return array_reverse($hashtags);
    }

    /**
     * Returns all Seminar_ids to courses I am member of and in which blubber
     * is an active plugin.
     *
     * @param string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return array of string : array of Seminar_ids
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getMyBlubberCourses(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        if ($GLOBALS['perm']->have_perm('admin', $user_id)) {
            return [];
        }

        $is_deputy = Config::get()->DEPUTIES_ENABLE && Deputy::countByUser_id($user_id) > 0;
        $blubber_plugin_info = PluginManager::getInstance()->getPluginInfo('Blubber');

        $parameters = [
            'me'                => $user_id,
            'blubber_plugin_id' => $blubber_plugin_info['id'],
        ];

        $query = "SELECT seminar_user.Seminar_id
                  FROM seminar_user
                  INNER JOIN tools_activated
                    ON plugin_id = :blubber_plugin_id
                       AND tools_activated.range_id = seminar_user.Seminar_id
                  WHERE seminar_user.user_id = :me";

        $my_courses = DBManager::get()->fetchFirst($query, $parameters);

        if ($is_deputy) {
            $query = "SELECT deputies.range_id
                      FROM deputies
                      INNER JOIN tools_activated
                    ON plugin_id = :blubber_plugin_id
                       AND tools_activated.range_id = deputies.range_id
                  WHERE deputies.user_id = :me";
            $my_courses = array_merge(
                $my_courses,
                DBManager::get()->fetchFirst($query, $parameters)
            );
        }
        return $my_courses;
    }

    /**
     * @param ?string $user_id  optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getMyBlubberInstitutes(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        if ($GLOBALS['perm']->have_perm('root', $user_id)) {
            return [];
        }

        $query = "SELECT Institut_id
                  FROM user_inst
                  WHERE user_id = ?";
        $institut_ids = DBManager::get()->fetchFirst($query, [$user_id]);
        $blubberplugin = PluginManager::getInstance()->getPlugin("Blubber");
        if (!$blubberplugin) {
            return [];
        }

        foreach ($institut_ids as $index => $institut_id) {
            if (!PluginManager::getInstance()->isPluginActivated($blubberplugin->getPluginId(), $institut_id)) {
                unset($institut_ids[$index]);
            }
        }
        return $institut_ids;
    }
}
