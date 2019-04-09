<?php
/*
 *  Copyright (c) 2013  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once __DIR__ . '/StreamAvatar.class.php';

/**
 * A class to fetch blubber-postings from the database and return an array of
 * BlubberPosting-objects.
 *
 * Such a stream is defined by some pool-variables and some filter-variables.
 * The pool at first defines the set of overall blubber-postings that should
 * be taken and the filter filters this set by it's own parameters.
 *
 * For example a profile-stream of a user takes all blubber from the user in the
 * pool and the filter is defined in a way to reduce this set to all public postings.
 *
 */
class BlubberStream extends SimpleORMap {

    public $filter_threads = [];
    public $max_age = null;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'blubber_streams';

        $config['registered_callbacks']['before_store'][] = 'serializeData';
        $config['registered_callbacks']['after_store'][] = 'unserializeData';
        $config['registered_callbacks']['after_initialize'][] = 'unserializeData';

        $config['default_values']['user_id'] = $GLOBALS['user']->id;

        parent::configure($config);
    }

    static public function create($pool = [], $filter = []) {
        $stream = new BlubberStream();
        foreach ($pool as $key => $pool_variable) {
            $stream['pool_'.$key] = $pool_variable;
        }
        foreach ($filter as $key => $filter_variable) {
            $stream['filter_'.$key] = $filter_variable;
        }
        return $stream;
    }

    /**
     * find all customized streams of that user
     * @param string/null $user_id : current user if null
     * @return array of BlubberStream
     */
    static public function findMine($user_id = null) {
        $user_id OR $user_id = $GLOBALS['user']->id;
        return self::findBySQL(
            "user_id = ? ORDER BY name ASC ",
            [$user_id]
        );
    }

    /**
     * Creates the global-blubberstream and returns it.
     * @return BlubberStream
     */
    static public function getGlobalStream($user_id = null) {
        $user_id OR $user_id = $GLOBALS['user']->id;
        $stream = new BlubberStream();
        $stream['pool_courses'] = [];
        $stream['pool_groups'] = [];
        $stream['sort'] = "age";
        $stream->max_age = strtotime('-1 year');
        $stream->user_id = $user_id;
        return $stream;
    }

    /**
     * Creates a course-stream and returns it.
     * @param string $course_id : a Seminar_id of a course
     * @return BlubberStream
     */
    static public function getCourseStream($course_id) {
        $stream = new BlubberStream();
        $stream['pool_courses'] = [$course_id];
        $stream['sort'] = "activity";
        return $stream;
    }

    /**
     * Creates a personal profile-stream of a user and returns it.
     * @param string $user_id : user_id of the wanted profile-stream
     * @return BlubberStream
     */
    static public function getProfileStream($user_id) {
        $stream = new BlubberStream();
        $stream['filter_users'] = [$user_id];
        $stream['filter_type'] = ["public"];
        $stream['sort'] = "age";
        return $stream;
    }

    /**
     * Creates a stream for given thread or all given threads (as an array)
     * @param string/array $topic_id : id(s) of thread
     * @return \BlubberStream
     */
    static public function getThreadStream($topic_id) {
        $stream = new BlubberStream();
        $stream->filter_threads = is_array($topic_id) ? $topic_id : [$topic_id];
        return $stream;
    }

    /**
     * Serializes $this->data so it is saves as string in the database.
     * @return boolean
     */
    protected function serializeData()
    {
        $this->content['pool_courses'] = implode(",", (array) $this->content['pool_courses']);
        $this->content['pool_groups'] = implode(",", (array) $this->content['pool_groups']);
        $this->content['pool_hashtags'] = implode(" ", (array) $this->content['pool_hashtags']);
        $this->content['filter_type'] = implode(",", (array) $this->content['filter_type']);
        $this->content['filter_courses'] = implode(",", (array) $this->content['filter_courses']);
        $this->content['filter_groups'] = implode(",", (array) $this->content['filter_groups']);
        $this->content['filter_users'] = implode(",", (array) $this->content['filter_users']);
        $this->content['filter_hashtags'] = implode(" ", (array) $this->content['filter_hashtags']);
        $this->content['filter_nohashtags'] = implode(" ", (array) $this->content['filter_nohashtags']);
        return true;
    }

    /**
     * Unserializes $this->data so it can be used as an array or something else.
     * @return boolean : true
     */
    protected function unserializeData()
    {
        $this->content['pool_courses'] = $this->content['pool_courses']
                ? explode(",", $this->content['pool_courses'])
                : [];
        $this->content['pool_groups'] = $this->content['pool_groups']
                ? explode(",", $this->content['pool_groups'])
                : [];
        $this->content['pool_hashtags'] = $this->content['pool_hashtags']
                ? explode(" ", $this->content['pool_hashtags'])
                : [];

        $this->content['filter_type'] = $this->content['filter_type']
                ? explode(",", $this->content['filter_type'])
                : [];
        $this->content['filter_courses'] = $this->content['filter_courses']
                ? explode(",", $this->content['filter_courses'])
                : [];
        $this->content['filter_groups'] = $this->content['filter_groups']
                ? explode(",", $this->content['filter_groups'])
                : [];
        $this->content['filter_users'] = $this->content['filter_users']
                ? explode(",", $this->content['filter_users'])
                : [];
        $this->content['filter_hashtags'] = $this->content['filter_hashtags']
                ? explode(" ", $this->content['filter_hashtags'])
                : [];
        $this->content['filter_nohashtags'] = $this->content['filter_nohashtags']
                ? explode(" ", $this->content['filter_nohashtags'])
                : [];
        return true;
    }

    /**
     * Returns an array of BlubberPosting objects that are the threads of the
     * stream.
     * @param integer $offset : start with the $offset-th thread in stream
     * @param integer $limit : return only $limit threads, null means unlimited
     * @param integer $stream_time : defines the state of the stream, because the
     *   postings in a stream and their order changes from time to time, we need
     *   the stream-time to have a precise offset-value and get exactly those
     *   threads we want to display.
     * @return array of \BlubberPosting
     */
    public function fetchThreads($offset = 0, $limit = null, $stream_time = null) {
        list($sql, $parameters) = $this->getThreadsSql($offset, $limit, $stream_time);
        $statement = DBManager::get()->prepare($sql);
        $statement->execute($parameters);
        $posting_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $postings = [];
        foreach ($posting_data as $data) {
            $posting = new BlubberPosting($data['topic_id']);
            $postings[] = $posting;
        }
        return $postings;
    }

    /**
     * Returns the number of threads in this stream.
     * @return integer
     */
    public function fetchNumberOfThreads() {
        list($sql, $parameters) = $this->getThreadsSql();
        $statement = DBManager::get()->prepare(
            "SELECT COUNT(*) " .
            "FROM (".$sql.") AS threads " .
        "");
        $statement->execute($parameters);
        return $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetches all new postings (threads and comments) that appear in the stream
     * and are newer than $since (as a unix timestamp).
     * @param integer $since : unix-timestamp
     * @return array of \BlubberPosting
     */
    public function fetchNewPostings($since, $from) {
        list($sql, $parameters) = $this->getNewPostingsSql($since, $from);
        $statement = DBManager::get()->prepare($sql);
        $statement->execute($parameters);
        $posting_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $postings = [];
        foreach ($posting_data as $data) {
            $posting = new BlubberPosting();
            $posting->setData($data);
            $posting->setNew(false);
            $postings[] = $posting;
        }
        return $postings;
    }

    /**
     * Fetches all hashtags that appear in this stream ordered by appearances
     * (starting with the most frequented). You can also define a date, so you
     * get only hashtags from threads newer than that date. And you can define
     * a limit.
     * @param integer|null $since : unix-timestamp for a date so you get only
     *   hashtags from threads that are newer than that date. Or null if there is
     *   no restriction in time.
     * @param integer|null $limit : limit the amount of results or null for
     *   infinite results
     * @return array of strings
     */
    public function fetchTags($since = null, $limit = null) {
        list($sql, $parameters) = $this->getThreadsSql();
        $sql = strtr($sql, ['FROM blubber ' => 'FROM blubber INNER JOIN blubber_tags bt1 USING(topic_id) ']);
        $statement = DBManager::get()->prepare(
            "SELECT blubber_tags.tag, SUM(1) AS counter " .
            "FROM (".$sql.") AS threads " .
                "INNER JOIN blubber_tags ON (blubber_tags.topic_id = threads.topic_id) " .
            (($since !== null) ? "WHERE threads.chdate >= :tags_since " : "") .
            "GROUP BY blubber_tags.tag " .
            "ORDER BY SUM(1) DESC " .
        "");
        if ($since !== null) {
            $parameters['tags_since'] = $since;
        }
        $statement->execute($parameters);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns sql and parameter for a PDO-statement that fetches all threads of
     * this stream.
     * @param integer $offset : $offset of the stream
     * @param integer $limit : how many threads should be returned (null means unlimited)
     * @param integer stream_time : unix-timestamp that defines the state of the
     *   stream. Ignore all threads that are newer than $stream_time and use the
     *   old order of the stream from the time $stream_time.
     * @return array : array(string $sql, array $parameter)
     */
    protected function getThreadsSql($offset = 0, $limit = null, $stream_time = null) {
        list($pool_sql, $filter_sql, $parameters) = $this->getSqlParts();
        if ($stream_time !== null) {
            $filter_sql[] = "blubber.mkdate <= :stream_time";
            $filter_sql[] = "(blubber_reshares.chdate <= :stream_time OR blubber_reshares.chdate IS NULL)";
            $parameters['stream_time'] = $stream_time;
        }
        $about_tags = ((count($this['pool_hashtags']) > 0) or (count($this['filter_hashtags']) > 0));

        $sql =
            "SELECT blubber.topic_id, blubber.root_id, blubber.mkdate, blubber.chdate " .
            "FROM blubber " .
                "INNER JOIN blubber AS comment ON (comment.root_id = blubber.topic_id) " .
                "LEFT JOIN blubber_mentions ON (blubber_mentions.topic_id = blubber.topic_id) " .
                ($about_tags ? "LEFT JOIN blubber_tags ON (blubber_tags.topic_id = blubber.topic_id) " : "") .
                "LEFT JOIN blubber_reshares ON (blubber_reshares.topic_id = blubber.topic_id) " .
            "WHERE " .
                //pool
                (count($pool_sql) ? "(1 != 1 OR ".implode(" OR ", $pool_sql).") " : "") .
                (count($pool_sql) && count($filter_sql) ? " AND " : "") .
                //filter
                (count($filter_sql) ? implode(" AND ", $filter_sql)." " : "") .
            "GROUP BY blubber.topic_id " .
            "ORDER BY " . ($this['sort'] === "activity"
                            ? "IF(MAX(blubber_reshares.chdate) IS NULL OR MAX(comment.mkdate) > MAX(blubber_reshares.chdate), MAX(comment.mkdate), MAX(blubber_reshares.chdate)) DESC"
                            : "blubber.mkdate DESC"
                          )." " .
            (($offset or $limit) ? "LIMIT ".(int) $offset.", ".(int) $limit." " : " ") .
        "";
        return [$sql, $parameters];
    }

    /**
     * Returns an array with sql and parameter for a PDO-statement that fetches
     * all blubber-postings of this stream newer than $since.
     * @param integer $since : unix-timestamp
     * @param integer $from : unix-timestamp
     * @return array : array(string $sql, array $parameter)
     */
    protected function getNewPostingsSql($since, $from) {
        list($pool_sql, $filter_sql, $parameters) = $this->getSqlParts();
        $filter_sql[] = "comment.chdate BETWEEN :since AND :from ";
        $parameters['since'] = $since;
        $parameters['from'] = $from;

        $sql =
            "SELECT comment.* " .
            "FROM blubber " .
                "INNER JOIN blubber AS comment ON (comment.root_id = blubber.topic_id) " .
                "LEFT JOIN blubber_mentions ON (blubber_mentions.topic_id = blubber.topic_id) " .
                "LEFT JOIN blubber_tags ON (blubber_tags.topic_id = blubber.topic_id) " .
                "LEFT JOIN blubber_reshares ON (blubber_reshares.topic_id = blubber.topic_id) " .
            "WHERE " .
                //pool
                (count($pool_sql) ? "(1 != 1 OR ".implode(" OR ", $pool_sql).") " : "") .
                (count($pool_sql) && count($filter_sql) ? " AND " : "") .
                //filter
                (count($filter_sql) ? implode(" AND ", $filter_sql)." " : "") .
            "ORDER BY comment.chdate " .
        "";
        return [$sql, $parameters];
    }

    /**
     * Returns parts for an sql-statement in shape of arrays that define the
     * pool-sql, the filter-sql and the parameter of the stream.
     * @return array(array $pool_sql, array $filter_sql, array $parameter)
     */
    protected function getSqlParts() {
        $pool_sql = [];
        $filter_sql = [];
        $parameters = [];

        if (count($this['pool_courses'])) {
            $pool_courses = $this->getCourses($this['pool_courses']);
            if (count($pool_courses)) {
                $pool_sql[] = "(blubber.Seminar_id IN (:pool_courses) AND blubber.context_type = 'course')";
                $parameters['pool_courses'] = $pool_courses;
            }
        }
        if (count($this['pool_groups']) > 0) {
            $pool_users = $this->getUsersByGroups($this['pool_groups']);
            $pool_users[] = $this->user_id;
            if (count($pool_users)) {
                $pool_sql[] = "blubber.user_id IN (:pool_users)";
                $pool_sql[] = "blubber_reshares.user_id IN (:pool_users)";
                $parameters['pool_users'] = $pool_users;
            }
        }
        if (count($this['pool_hashtags']) > 0) {
            $pool_sql[] = "(blubber_tags.tag IN (:pool_hashtags))";
            $parameters['pool_hashtags'] = $this['pool_hashtags'];
        }

        // Rights to see the blubber-postings:
        $parameters['seminar_ids'] = $this->getMyCourses();
        if ($GLOBALS['perm']->have_perm("admin", $this->user_id)) {
            if ((count($this['pool_courses']) && ($this['pool_courses'][0] !== "all")) || count($this['filter_courses'])) {
                $filter_sql[] =
                    "(blubber.context_type = 'public' " .
                        "OR (blubber.context_type = 'private' AND blubber_mentions.user_id = :me) " .
                        "OR (blubber.context_type = 'course') " .
                    ")";
            } else {
                $filter_sql[] =
                    "(blubber.context_type = 'public' " .
                        "OR (blubber.context_type = 'private' AND blubber_mentions.user_id = :me) " .
                    ")";
            }
        } else {
            $filter_sql[] =
                "(blubber.context_type = 'public' " .
                    "OR (blubber.context_type = 'private' AND blubber_mentions.user_id = :me) " .
                    (count($parameters['seminar_ids']) ? "OR (blubber.context_type = 'course' AND blubber.Seminar_id IN (:seminar_ids)) " : "") .
                ")";
        }
        $parameters['me'] = $this->user_id;

        if (count($this['filter_type']) > 0) {
            $filter_sql[] = "blubber.context_type IN (:filter_type)";
            $parameters['filter_type'] = $this['filter_type'];
        }
        if (count($this['filter_courses']) > 0) {
            $filter_courses = $this->getCourses($this['filter_courses']);
            if (count($filter_courses)) {
                $filter_sql[] = "(blubber.Seminar_id IN (:filter_courses) AND blubber.context_type = 'course')";
                $parameters['filter_courses'] = $filter_courses;
            } else {
                $filter_sql[] = "1 != 1";
            }
        }
        if (count($this['filter_groups']) > 0 or count($this['filter_users']) > 0) {
            $filter_users = [];
            if (count($this['filter_groups']) > 0) {
                $filter_users = $this->getUsersByGroups($this['filter_groups']);
                $filter_users[] = $this->user_id;
            }
            if (count($this['filter_users']) > 0) {
                $filter_users = $filter_users + $this['filter_users'];
                $filter_users = array_unique($filter_users);
            }
            $filter_sql[] = "(blubber.user_id IN (:filter_users) OR blubber_reshares.user_id IN (:filter_users))";

            $parameters['filter_users'] = $filter_users;
        }
        if (count($this['filter_hashtags']) > 0) {
            $filter_sql[] = "(blubber_tags.tag IN (:filter_hashtags))";
            $parameters['filter_hashtags'] = $this['filter_hashtags'];
        }
        if (count($this['filter_nohashtags']) > 0) {
            $filter_sql[] = "( 0 = (SELECT COUNT(*) FROM blubber_tags AS tags WHERE tags.topic_id = blubber.topic_id AND tag IN (:filter_nohashtags) ) )";
            $parameters['filter_nohashtags'] = $this['filter_nohashtags'];
        }
        if (count($this->filter_threads) > 0) {
            $filter_sql[] = "blubber.topic_id IN (:filter_threads) ";
            $parameters['filter_threads'] = $this->filter_threads;
        }
        if ($this->max_age) {
            $filter_sql[] = "blubber.mkdate > :max_age ";
            $parameters['max_age'] = $this->max_age;
        }
        return [$pool_sql, $filter_sql, $parameters];
    }

    /**
     * Returns an array of users defined by the groups selected in the stream.
     * @return array : array of user_ids
     */
    protected function getUsersByGroups($groups) {
        if ($groups[0] === "all") {
            $statement = DBManager::get()->prepare(
                "SELECT DISTINCT user_id, '0' AS external_contact " .
                    "FROM contact " .
                    "WHERE owner_id = :me " .
                "UNION SELECT DISTINCT external_contact_id AS user_id, '1' AS external_contact " .
                    "FROM blubber_follower " .
                    "WHERE left_follows_right = '1' " .
            "");
            $statement->execute(['me' => $this->user_id]);
            return $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        } elseif(count($groups)) {
            $statement = DBManager::get()->prepare(
                "SELECT DISTINCT statusgruppe_user.user_id " .
                "FROM statusgruppe_user " .
                    "INNER JOIN statusgruppen ON (statusgruppen.statusgruppe_id = statusgruppe_user.statusgruppe_id) " .
                "WHERE statusgruppen.range_id = :me " .
                    "AND statusgruppen.statusgruppe_id IN (:groups) " .
            "");
            $statement->execute([
                'me' => $this->user_id,
                'groups' => $groups
            ]);
            return $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        }
    }

    /**
     * Returns the Seminar_ids selected in this stream but only those I am
     * member of and in which blubber is an active plugin.
     * Returns the given array if user is admin/root (or an empty array if
     * given array contains "all"). This is due to performance problems.
     * @param array $courses : array of Seminar_ids or the string "all"
     * @return array of Seminar_ids
     */
    protected function getCourses($courses) {
        $mycourses = $this->getMyCourses();
        if ($courses[0] === "all") {
            return $mycourses;
        } else {
            if ($GLOBALS['perm']->have_perm("admin", $this->user_id)) {
                return $courses;
            } else {
                return array_intersect($courses, $mycourses);
            }
        }
    }

    /**
     * Returns all Seminar_ids to courses I am member of and in which blubber
     * is an active plugin.
     * @return array of string : array of Seminar_ids
     */
    protected function getMyCourses() {
        if ($GLOBALS['perm']->have_perm("admin", $this->user_id)) {
            return [];
        }
        $mandatory_classes = [];
        $standard_classes = [];
        $forbidden_classes = [];
        $mandatory_types = [];
        $standard_types = [];
        $forbidden_types = [];
        foreach (SemClass::getClasses() as $key => $class) {
            $blubber_setting = $class->getModuleMetadata("Blubber");
            if ($class->isModuleMandatory("Blubber")) {
                $mandatory_classes[] = $key;
            }
            if ($class->isSlotModule("Blubber") or ($blubber_setting['activated'] && !$blubber_setting['sticky'])) {
                $standard_classes[] = $key;
            }
            if (!$class->isModuleAllowed("Blubber")) {
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
        $is_deputy = get_config('DEPUTIES_ENABLE') && DBManager::get()->fetchColumn("SELECT 1 FROM deputies WHERE user_id=? LIMIT 1", [$this->user_id]);
        $blubber_plugin_info = PluginManager::getInstance()->getPluginInfo('Blubber');
        $statement = DBManager::get()->prepare("
            SELECT seminare.Seminar_id
            FROM seminar_user
                INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
            WHERE seminar_user.user_id = :me
                AND seminare.status IN (:mandatory_types)
                AND seminare.status NOT IN (:forbidden_types)

            UNION DISTINCT SELECT seminare.Seminar_id
            FROM seminar_user
                INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
                INNER JOIN plugins_activated ON (pluginid = :blubber_plugin_id
                    AND plugins_activated.range_type = 'sem' AND plugins_activated.range_id = seminare.Seminar_id)
            WHERE seminar_user.user_id = :me
                AND plugins_activated.state = 1
                AND seminare.status NOT IN (:forbidden_types)

            UNION DISTINCT SELECT seminare.Seminar_id
            FROM seminar_user
                INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
                LEFT JOIN plugins_activated ON (pluginid = :blubber_plugin_id
                    AND plugins_activated.range_type = 'sem' AND plugins_activated.range_id = seminare.Seminar_id)
            WHERE seminar_user.user_id = :me
                AND plugins_activated.state IS NULL
                AND seminare.status NOT IN (:forbidden_types)
        ");
        $parameter = ['me' => $this->user_id];
        $parameter['mandatory_types'] = count($mandatory_types) ? $mandatory_types : null;
        $parameter['standard_types'] = count($standard_types) ? $standard_types : null;
        $parameter['forbidden_types'] = count($forbidden_types) ? $forbidden_types : [-1];
        $parameter['blubber_plugin_id'] = $blubber_plugin_info['id'];
        $statement->execute($parameter);
        $my_courses = $statement->fetchFirst();
        if ($is_deputy) {
            $statement = DBManager::get()->prepare(
                "SELECT deputies.range_id " .
                "FROM deputies " .
                    "INNER JOIN seminare ON (seminare.Seminar_id = deputies.range_id) " .
                    "LEFT JOIN plugins_activated ON (pluginid = :blubber_plugin_id AND plugins_activated.range_type = 'sem' AND plugins_activated.range_id = seminare.Seminar_id) " .
                "WHERE deputies.user_id = :me " .
                    "AND (" .
                        "seminare.status IN (:mandatory_types) " .
                        "OR (plugins_activated.state = 1) " .
                        "OR (seminare.status IN (:standard_types) AND plugins_activated.state != 0) " .
                    ") " .
                    "AND seminare.status NOT IN (:forbidden_types) " .
            "");
            $statement->execute($parameter);
            $my_courses = array_merge($my_courses, $statement->fetchFirst());
        }
        return $my_courses;
    }

}
