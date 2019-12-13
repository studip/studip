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
                $query = "INSERT IGNORE INTO blubber_mentions
                          SET user_id = :user_id,
                              thread_id = :thread_id,
                              external_contact = 0,
                              mkdate = UNIX_TIMESTAMP()";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    'user_id'   => $user->getId(),
                    'thread_id' => $thread->getId()
                ]);
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

    public static function findMyGlobalThreads($limit = 51, $since = null, $olderthan = null)
    {
        $condition = "LEFT JOIN blubber_comments
                        ON blubber_comments.thread_id = blubber_threads.thread_id
                      WHERE (blubber_threads.content IS NULL OR blubber_threads.content = '')
                        AND blubber_comments.comment_id IS NULL
                        AND (display_class IS NULL OR display_class = 'BlubberThread')
                        AND UNIX_TIMESTAMP() - blubber_threads.mkdate > 60 * 60";
        self::deleteBySQL($condition);

        $query = SQLQuery::table('blubber_threads')
            ->join('blubber_comments', 'blubber_comments', 'blubber_threads.thread_id = blubber_comments.thread_id', 'LEFT JOIN')
            ->join('my_comments', 'blubber_comments', 'blubber_threads.thread_id = my_comments.thread_id', 'LEFT JOIN')
            ->join('blubber_mentions', 'blubber_mentions', 'blubber_mentions.thread_id = blubber_threads.thread_id', 'LEFT JOIN');

        if (!$GLOBALS['perm']->have_perm('admin')) {
            //user, autor, tutor, dozent
            $query->where('mycourses', implode(' OR ', [
                "(blubber_threads.context_type = 'public' AND (my_comments.user_id = :user_id OR blubber_threads.user_id = :user_id))",
                "(blubber_threads.context_type = 'course' AND blubber_threads.context_id IN (:seminar_ids))",
                "(blubber_threads.context_type = 'institute' AND blubber_threads.context_id IN (:institut_ids))",
                "(blubber_threads.context_type = 'private' AND blubber_mentions.user_id = :user_id AND blubber_mentions.external_contact = 0)",
            ]), [
                'seminar_ids'  => self::getMyBlubberCourses(),
                'institut_ids' => self::getMyBlubberInstitutes(),
            ]);
        } elseif (!$GLOBALS['perm']->have_perm('root')) {
            //admin
            $query->where('mycourses', implode(' OR ', [
                "(blubber_threads.context_type = 'public' AND (my_comments.user_id = :user_id OR blubber_threads.user_id = :user_id))",
                "(blubber_threads.context_type = 'institute' AND blubber_threads.context_id IN (:institut_ids))",
                "(blubber_threads.context_type = 'private' AND blubber_mentions.user_id = :user_id AND blubber_mentions.external_contact = 0)",
            ]), ['institut_ids' => self::getMyBlubberInstitutes()]);
        } else {
            //root
            $query->where(implode(' OR ', [
                "((blubber_threads.context_type = 'public' OR blubber_threads.context_type IN ('course', 'institute')) AND (my_comments.user_id = :user_id OR blubber_threads.user_id = :user_id))",
                "(blubber_threads.context_type = 'private' AND blubber_mentions.user_id = :user_id AND blubber_mentions.external_contact = '0')",
            ]));
        }
        if ($since !== null) {
            $query->where('since', 'blubber_comments.mkdate >= :since OR blubber_threads.mkdate >= :since', compact('since'));
        }
        $query->where("blubber_threads.visible_in_stream = 1");
        $query->where("(blubber_comments.mkdate IS NULL OR blubber_comments.mkdate > UNIX_TIMESTAMP() - 86400 * 365)");
        $query->parameter('user_id', $GLOBALS['user']->id);
        $query->groupBy('blubber_threads.thread_id');
        if ($olderthan !== null) {
            $query->having('olderthan', "IFNULL(MAX(blubber_comments.mkdate), blubber_threads.mkdate) <= :olderthan", [
                'olderthan' => $olderthan
            ]);
        }
        $query->orderBy("IFNULL(MAX(blubber_comments.mkdate), blubber_threads.mkdate) DESC");
        $query->limit($limit);

        $threads = $query->fetchAll(static::class);
        $upgraded_threads = array_map(function ($thread) {
            return self::upgradeThread($thread);
        }, $threads);

        if (!$olderthan) {
            $thread = new self();
            $thread->setId('global');
            if (!$since || $thread->getLatestActivity() >= $since) {
                array_unshift($upgraded_threads, $thread);
            }
        }

        $upgraded_threads = array_filter($upgraded_threads, function ($t) {
            return $t->isVisibleInStream() && $t->isReadable();
        });

        return $upgraded_threads;
    }

    public static function findBySeminar($seminar_id)
    {
        return self::findBySQL("context_id = ? AND context_type = 'course'", [$seminar_id]);
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
        if ($this->getId() === 'global') {
            return _('Globale Blubber');
        }

        if ($this['context_type'] === 'public') {
            return sprintf(_('Blubber von %s'), $this->user ? $this->user->getFullName() : _('unbekannt'));
        }

        if ($this['context_type'] === "private") {
            $query = "SELECT IFNULL(blubber_external_contact.name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)) AS name
                      FROM blubber_mentions
                      LEFT JOIN auth_user_md5
                        ON blubber_mentions.user_id = auth_user_md5.user_id
                           AND blubber_mentions.external_contact = 0
                      LEFT JOIN blubber_external_contact
                        ON blubber_external_contact.external_contact_id = blubber_mentions.user_id
                           AND blubber_mentions.external_contact = 1
                      WHERE blubber_mentions.thread_id = :thread_id
                        AND blubber_mentions.user_id != :me
                      ORDER BY name";
            $names = DBManager::get()->fetchFirst($query, [
                'thread_id' => $this->getId(),
                'me'        => $GLOBALS['user']->id,
            ]);

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
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$course->status]['class']];
            $icons = [];
            $admin = null;
            $modulemanager = new Modules();
            foreach (SemClass::getSlots() as $slot) {
                if ($sem_class->isModuleAllowed($sem_class->getSlotModule($slot))
                        && $modulemanager->checkLocal($slot, $this['context_id'], 'sem')) {
                    $last_visit = object_get_visit($this['context_id'], $slot);
                    $module = $sem_class->getModule($slot, $this['context_id']);
                    if ($module) {
                        $nav = $module->getIconNavigation($this['context_id'], $last_visit, $GLOBALS['user']->id);
                        if ($nav) {
                            $icons[] = $nav;
                        }
                    }
                }
            }
            foreach (PluginManager::getInstance()->getPlugins('StandardPlugin', $this['context_id']) as $plugin) {
                if (!$sem_class->isSlotModule(get_class($plugin))) {
                    $last_visit = object_get_visit($this['context_id'], get_class($plugin));
                    $nav = $plugin->getIconNavigation($this['context_id'], $last_visit, $GLOBALS['user']->id);
                    if ($nav) {
                        $icons[] = $nav;
                    }
                }
            }

            $nextdate = CourseDate::findOneBySQL("range_id = ? AND `date` >= UNIX_TIMESTAMP() ORDER BY `date` ASC", [$this['context_id']]);

            $template = $GLOBALS['template_factory']->open('blubber/course_context');
            $template->thread = $this;
            $template->course = $course;
            $template->icons = $icons;
            $template->nextdate = $nextdate;
            return $template;
        }

        if ($this['context_type'] === 'private') {
            $query = "SELECT *
                      FROM blubber_mentions
                      LEFT JOIN auth_user_md5
                        ON blubber_mentions.user_id = auth_user_md5.user_id
                           AND blubber_mentions.external_contact = 0
                      LEFT JOIN blubber_external_contact
                        ON blubber_mentions.user_id = blubber_external_contact.external_contact_id
                           AND blubber_mentions.external_contact = 1
                      WHERE thread_id = ?
                      ORDER BY IFNULL(blubber_external_contact.name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname))";
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

    public function getOpenGraphURLs()
    {
        return OpenGraph::extract($this['content']);
    }

    public function getLatestActivity()
    {
        if ($this->getId() === 'global') {
            $newest_thread = self::findOneBySQL("visible_in_stream = 1 AND context_type = 'public' ORDER BY mkdate DESC");
            return $newest_thread ? $newest_thread['mkdate'] : null;
        }

        $newest_comment = BlubberComment::findOneBySQL("thread_id = ? ORDER BY mkdate DESC", [$this->getId()]);
        return $newest_comment ? $newest_comment['mkdate'] : $this['mkdate'];
    }

    public function getURL()
    {
        return URLHelper::getURL('dispatch.php/blubber/index/' . $this->getId());
    }

    public function notifyUsersForNewComment($comment)
    {
        $user_ids = [];
        if ($this['context_type'] === 'public') {
            $query = "SELECT DISTINCT user_id
                      FROM blubber_comments
                      WHERE thread_id = :thread_id
                        AND external_contact = 0
                        AND user_id != :me";
            $user_ids = DBManager::get()->fetchFirst($query, [
                'thread_id' => $this->getId(),
                'me'        => $GLOBALS['user']->id,
            ]);
            if (!$this['external_contact'] && $this['user_id'] !== $GLOBALS['user']->id && !in_array($this['user_id'], $user_ids)) {
                $user_ids[] = $this['user_id'];
            }
        } elseif ($this['context_type'] === 'private') {
            $query = "SELECT user_id
                      FROM blubber_mentions
                      WHERE thread_id = :thread_id
                        AND external_contact = 0
                        AND user_id != :me
            ";
            $user_ids = DBManager::get()->fetchFirst($query, [
                'thread_id' => $this->getId(),
                'me'        => $GLOBALS['user']->id,
            ]);
        } elseif ($this['context_type'] === 'course') {
            $query = "SELECT user_id
                      FROM seminar_user
                      WHERE Seminar_id = :context_id
                        AND user_id != :me";
            $user_ids = DBManager::get()->fetchFirst($query, [
                'context_id' => $this['context_id'],
                'me'         => $GLOBALS['user']->id,
            ]);
        } elseif ($this['context_type'] === 'institute') {
            $query = "SELECT user_id
                      FROM user_inst
                      WHERE Institut_id = :context_id
                        AND user_id != :me";
            $user_ids = DBManager::get()->fetchFirst($query, [
                'context_id' => $this['context_id'],
                'me'         => $GLOBALS['user']->id,
            ]);
        }
        PersonalNotifications::add(
            $user_ids,
            $this->getURL(),
            sprintf(_('%s hat einen Kommentar geschrieben.'), get_fullname()),
            'blubberthread_' . $this->getId(),
            Icon::create('blubber'),
            true
        );
    }

    public function isVisibleInStream()
    {
        return $this['visible_in_stream'];
    }

    public function isWritable()
    {
        if ($this['context_type'] === 'course' || $this['context_type'] === 'institute') {
            return $GLOBALS['perm']->have_studip_perm('tutor', $this['context_id']);
        } else {
            return $GLOBALS['perm']->have_perm('root') || $this['user_id'] === $GLOBALS['user']->id;
        }
    }

    public function isReadable()
    {
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
                'me'        => $GLOBALS['user']->id,
                'thread_id' => $this->getId()
            ]);
        }

        if (in_array($this['context_type'], ['course', 'institute'])) {
            return $GLOBALS['perm']->have_studip_perm('user', $this['context_id']);
        }

        return false;
    }

    public function isCommentable()
    {
        return $this->isReadable() && $this['commentable'];
    }

    public function getAvatar()
    {
        if ($this->getId() === 'global') {
            return Icon::create('blubber')->asImagePath();
        }

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

    public function getJSONData($limit_comments = 50, $around_comment_id = null)
    {
        $output = [
            'thread_posting' => $this->toRawArray(),
            'context_info'   => '',
            'comments'       => [],
            'more_up'        => 0,
            'more_down'      => 0
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

        $query = "SELECT blubber_comments.*
                  FROM blubber_comments
                  WHERE blubber_comments.thread_id = :thread_id
                  ORDER BY mkdate DESC
                  LIMIT :limit";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'thread_id' => $this->getId(),
            'limit'     => $limit_comments + 1,
        ]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > $limit_comments) {
            $output['more_up'] = 1;
        }

        foreach ($result as $data) {
            $comment = BlubberComment::buildExisting($data);
            $output['comments'][] = $comment->getJSONData();
        }

        return $output;
    }

    /**
     * Returns all Seminar_ids to courses I am member of and in which blubber
     * is an active plugin.
     * @return array of string : array of Seminar_ids
     */
    protected static function getMyBlubberCourses()
    {
        if ($GLOBALS['perm']->have_perm('admin', $GLOBALS['user']->id)) {
            return [];
        }

        $mandatory_classes = [];
        $standard_classes = [];
        $forbidden_classes = [];
        $mandatory_types = [];
        $standard_types = [];
        $forbidden_types = [];

        foreach (SemClass::getClasses() as $key => $class) {
            $blubber_setting = $class->getModuleMetadata('Blubber');
            if ($class->isModuleMandatory('Blubber')) {
                $mandatory_classes[] = $key;
            }
            if ($class->isSlotModule('Blubber') || ($blubber_setting['activated'] && !$blubber_setting['sticky'])) {
                $standard_classes[] = $key;
            }
            if (!$class->isModuleAllowed('Blubber')) {
                $forbidden_classes[] = $key;
            }
        }

        foreach (SemType::getTypes() as $key => $type) {
            if (in_array($type['class'], $mandatory_classes)) {
                $mandatory_types[] = $key;
            }
            if (in_array($type['class'], $standard_classes)) {
                $standard_types[] = $key;
            }
            if (in_array($type['class'], $forbidden_classes)) {
                $forbidden_types[] = $key;
            }
        }

        $is_deputy = Config::get()->DEPUTIES_ENABLE && Deputy::countByUser_id($GLOBALS['user']->id) > 0;
        $blubber_plugin_info = PluginManager::getInstance()->getPluginInfo('Blubber');

        $parameters = [
            'me'                => $GLOBALS['user']->id,
            'mandatory_types'   => $mandatory_types ?: null,
            'standard_types'    => $standard_types ?: null,
            'forbidden_types'   => $forbidden_types ?: [-1],
            'blubber_plugin_id' => $blubber_plugin_info['id'],
        ];

        $query = "SELECT seminare.Seminar_id
                  FROM seminar_user
                  INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
                  WHERE seminar_user.user_id = :me
                    AND seminare.status IN (:mandatory_types)
                    AND seminare.status NOT IN (:forbidden_types)

                  UNION DISTINCT

                  SELECT seminare.Seminar_id
                  FROM seminar_user
                  INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
                  INNER JOIN plugins_activated
                    ON pluginid = :blubber_plugin_id
                       AND plugins_activated.range_type = 'sem'
                       AND plugins_activated.range_id = seminare.Seminar_id
                  WHERE seminar_user.user_id = :me
                    AND plugins_activated.state = 1
                    AND seminare.status NOT IN (:forbidden_types)

                  UNION DISTINCT

                  SELECT seminare.Seminar_id
                  FROM seminar_user
                  INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
                  LEFT JOIN plugins_activated
                    ON pluginid = :blubber_plugin_id
                       AND plugins_activated.range_type = 'sem'
                       AND plugins_activated.range_id = seminare.Seminar_id
                  WHERE seminar_user.user_id = :me
                    AND plugins_activated.state IS NULL
                    AND seminare.status NOT IN (:forbidden_types)";
        $my_courses = DBManager::get()->fetchFirst($query, $parameters);

        if ($is_deputy) {
            $query = "SELECT deputies.r019ange_id
                      FROM deputies
                      INNER JOIN seminare ON (seminare.Seminar_id = deputies.range_id)
                      LEFT JOIN plugins_activated
                        ON pluginid = :blubber_plugin_id AND plugins_activated.range_type = 'sem'
                           AND plugins_activated.range_id = seminare.Seminar_id
                      WHERE deputies.user_id = :me
                        AND (
                            seminare.status IN (:mandatory_types)
                            OR plugins_activated.state = 1
                            OR (
                                seminare.status IN (:standard_types)
                                AND plugins_activated.state != 0
                            )
                        )
                        AND seminare.status NOT IN (:forbidden_types)";
            $my_courses = array_merge(
                $my_courses,
                DBManager::get()->fechFirst($query, $parameters)
            );
        }
        return $my_courses;
    }

    protected static function getMyBlubberInstitutes()
    {
        if ($GLOBALS['perm']->have_perm('root', $GLOBALS['user']->id)) {
            return [];
        }

        $query = "SELECT Institut_id
                  FROM user_inst
                  WHERE user_id = ?";
        return DBManager::get()->fetchFirst($query, [$GLOBALS['user']->id]);
    }
}
