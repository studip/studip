<?php

/**
 * Class AdminCourseFilter
 *
 * The main class to filter all courses for admins. It's a singleton class, so you
 * better call it with AdminCourseFilter::get(). The whole class is created to
 * provide a nice hook for plugins to add special filters into the admin-area of
 * Stud.IP.
 *
 * To add a filter with a plugin, listen to the notification "AdminCourseFilterWillQuery"
 * like this:
 *
 *     NotificationCenter::addObserver($this, "addMyFilter", "AdminCourseFilterWillQuery");
 *
 * Where $this is an object and "addMyFilter" a method. Such a method might look like this:
 *
 *     public function addLectureshipFilter($event, $filter)
 *     {
 *         if ($GLOBALS['user']->cfg->getValue("LECTURESHIP_FILTER")) {
 *             $filter->settings['query']['joins']['lehrauftrag'] = array(
 *                 'join' => "INNER JOIN",
 *                 'on' => "seminare.Seminar_id = lehrauftrag.seminar_id"
 *             );
 *         }
 *     }
 *
 * Within this method you alter the public $filter->settings array, because this array
 * describes entirely the big query for the admin-search. In our example above
 * we simple add an INNER JOIN to filter for the course having an entry in
 * the lehrauftrag table.
 *
 * Description of this array is as follows:
 *
 * $filter->settings['query']            : The main sql query as a prepared statement.
 * $filter->settings['query']['select']  : An assoc array. $filter->settings['query']['select']['Number_of_teachers'] = "COUNT(DISTINCT dozenten.user_id)"
 *                                         will select the result of COUNT as the variable Number_of_teachers.
 * $filter->settings['query']['joins']   : Example $filter->settings['query']['joins']['dozenten'] = array(
 *                                          'join' => "INNER JOIN", //default value, else use "LEFT JOIN"
 *                                          'table' => "seminar_user", //can me omitted if you don't want to use a table-alias
 *                                          'on' => "dozenten.Seminar_id = seminare.Seminar_id AND dozenten.status = 'dozent'"
 *                                          )
 *                                         if 'table' differs from the index, the index will be the alias of the table.
 *                                         So normally you don't need to name a table if you don't want it to be aliased.
 * $filter->settings['query']['where']   : You might want to use the method $filter->where($sql, $parameter) instead.
 * $filter->settings['query']['orderby'] : You might want to use $filter->orderBy($attribute, $flag = "ASC") instead.
 * $filter->settings['parameter']        : An assoc array of parameter that will be passed to
 *                                         the prepared statement.
 *
 */
class AdminCourseFilter
{
    static protected $instance = null;
    public $max_show_courses = 500;
    public $settings = [];

    /**
     * returns an AdminCourseFilter singleton object
     * @return AdminCourseFilter or derived-class object
     */
    static public function get($reset_settings = false)
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class($reset_settings);
        }
        return self::$instance;
    }

    /**
     * Constructor of the singleton-object. The settings might come from the session
     * if $reset_settings is false.
     * @param bool $reset_settings : should the session settings of the singleton be reset?
     */
    public function __construct($reset_settings = false)
    {
        $this->initSettings();

        if ($reset_settings) {
            $this->resetSettings();
        } else {
            $this->restoreSettings();
        }
    }

    /**
     * store settings in session
     */
    public function storeSettings()
    {
        $_SESSION['AdminCourseFilter_settings'] = $this->settings;
    }

    /**
     * restore settings from session
     */
    public function restoreSettings()
    {
        if ($_SESSION['AdminCourseFilter_settings']) {
            $this->settings = $_SESSION['AdminCourseFilter_settings'];
        }
    }

    /**
     * reset settings
     */
    public function resetSettings()
    {
        $this->initSettings();
        unset($_SESSION['AdminCourseFilter_settings']);
    }

    /**
     * initialize settings
     */
    public function initSettings()
    {
        $this->settings = [];

        $this->settings['query']['select'] = [
            'Institut' => "Institute.Name",
            'teilnehmer' => "(SELECT COUNT(seminar_id)
                          FROM seminar_user
                          WHERE seminar_id = seminare.Seminar_id AND status != 'dozent' AND status != 'tutor')",
            'prelim' => "(SELECT COUNT(seminar_id)
                          FROM admission_seminar_user
                          WHERE seminar_id = seminare.Seminar_id AND status = 'accepted')",
            'waiting' => "(SELECT COUNT(seminar_id)
                          FROM admission_seminar_user
                          WHERE seminar_id = seminare.Seminar_id AND status = 'awaiting')",
            'requests' => "(SELECT COUNT(request_id)
                          FROM resources_requests
                          WHERE seminar_id = seminare.Seminar_id)",
            'course_set' => "(SELECT set_id FROM seminar_courseset WHERE seminar_id = seminare.Seminar_id LIMIT 1)"
        ];
        $this->settings['query']['joins'] = [
            'Institute' => [
                'join' => "INNER JOIN",
                'on' => "seminare.Institut_id = Institute.Institut_id"
            ],
            'sem_types' => [
                'join' => "LEFT JOIN",
                'on' => "sem_types.id = seminare.status"
            ],
            'sem_classes' => [
                'join' => "LEFT JOIN",
                'on' => "sem_classes.id = sem_types.class"
            ]
        ];
        $this->settings['query']['where'] = [];
        $this->settings['query']['orderby'] = Config::get()->IMPORTANT_SEMNUMBER ? "seminare.veranstaltungsnummer, seminare.name" : "seminare.name";
    }

    /**
     * Adds a filter for all courses of the given semester.
     * @param string $semester_id : ID of the given semester.
     * @return $this
     * @throws Exception if semester_id does not exist
     */
    public function filterBySemester($semester_id)
    {
        $semester = Semester::find($semester_id);
        if (!$semester) {
            throw new Exception("Das ausgewählte Semester scheint nicht zu existieren.");
        }
        $this->settings['query']['where']['semester'] = "(seminare.start_time <= :semester_beginn AND ((:semester_beginn <= seminare.start_time + seminare.duration_time) OR (seminare.duration_time = -1)))";
        $this->settings['parameter']['semester_beginn'] = $semester['beginn'];
        return $this;
    }

    /**
     * Adds a filter for a sem_type or many sem_types if the parameter is an array.
     * @param array|integer $type : id or ids of sem_types
     * @return $this
     */
    public function filterByType($type)
    {
        if (is_array($type)) {
            $this->settings['query']['where']['status'] = "seminare.status IN (:types)";
            $this->settings['parameter']['types'] = $type;
        } else {
            $this->settings['query']['where']['status'] = "seminare.status = :type";
            $this->settings['parameter']['type'] = (int) $type;
        }
        return $this;
    }

    /**
     * Adds a filter for an institut_id or many institut_ids if the parameter is an array.
     * @param array|integer $institut_ids : id or ids of institutes
     * @return $this
     */
    public function filterByInstitute($institut_ids)
    {
        if (is_array($institut_ids)) {
            $this->settings['query']['where']['institute'] = "seminare.Institut_id IN (:institut_ids)";
            $this->settings['parameter']['institut_ids'] = $institut_ids;
        } else {
            $this->settings['query']['where']['status'] = "seminare.Institut_id = :institut_id";
            $this->settings['parameter']['institut_id'] = (string) $institut_ids;
        }
        return $this;
    }

    public function filterByDozent($user_ids)
    {
        $this->settings['query']['joins']['dozenten'] = [
            'join' => "INNER JOIN",
            'table' => "seminar_user",
            'on' => "dozenten.Seminar_id = seminare.Seminar_id AND dozenten.status = 'dozent'"
        ];
        if (is_array($user_ids)) {
            $this->settings['query']['where']['dozenten'] = "dozenten.user_id IN (:dozenten_ids)";
            $this->settings['parameter']['dozenten_ids'] = $user_ids;
        } else {
            $this->settings['query']['where']['dozenten'] = "dozenten.user_id = :dozenten_id";
            $this->settings['parameter']['dozenten_id'] = (string) $user_ids;
        }
        return $this;
    }

    /**
     * Adds a filter for a textstring, that can be the coursenumber, the name of the course
     * or the last name of one of the dozenten.
     * @param string $text : the searchstring
     * @return $this
     */
    public function filterBySearchstring($text)
    {
        $this->settings['query']['joins']['dozenten'] = [
            'join' => "INNER JOIN",
            'table' => "seminar_user",
            'on' => "dozenten.Seminar_id = seminare.Seminar_id AND dozenten.status = 'dozent'"
        ];
        $this->settings['query']['joins']['dozentendata'] = [
            'join' => "INNER JOIN",
            'table' => "auth_user_md5",
            'on' => "dozenten.user_id = dozentendata.user_id"
        ];
        $this->settings['query']['where']['search'] = "(CONCAT_WS(' ', seminare.VeranstaltungsNummer, seminare.name, seminare.Untertitel, dozentendata.Nachname) LIKE :search
            OR CONCAT(dozentendata.Nachname, ', ', dozentendata.Vorname) LIKE :search
            OR CONCAT_WS(' ', dozentendata.Vorname, dozentendata.Nachname) LIKE :search
            OR dozentendata.Vorname LIKE :search
            OR dozentendata.Nachname LIKE :search
        )";
        $this->settings['parameter']['search'] = "%".$text."%";

        return $this;
    }

    /**
     * @param string $attribute : column, name of the column, yb whcih we should order the results
     * @param string $flag : "ASC" or "DESC for ascending order or descending order,
     * @return $this
     * @throws Exception if $flag does not exist
     */
    public function orderBy($attribute, $flag = 'ASC')
    {
        $flag = mb_strtoupper($flag);
        if (!in_array($flag, words('ASC DESC'))) {
            throw new Exception("Sortierreihenfolge undefiniert.");
        }
        if (in_array($attribute, words('VeranstaltungsNummer Name status teilnehmer waiting prelim requests completion start_time'))) {
            $this->settings['query']['orderby'] = $attribute . ' ' . $flag;
        }
        return $this;
    }

    /**
     * Adds a where filter.
     * @param string $where : any where condition like "sem_classes.overview = 'CoreOverview'"
     * @param array $parameter : an array of parameter that appear in the $where query.
     * @param null|string $id : an id of the where-query. Use this to possibly
     *                          avoid double where conditions or allow deleting the condition
     *                          by plugins if necessary. Can be omitted.
     * @return $this
     */
    public function where($where, $parameter = [], $id = null)
    {
        if (!$id) {
            $id = md5($where);
        }
        $this->settings['query']['where'][$id] = $where;
        $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $parameter);
        return $this;
    }

    /**
     * Returns the data of the resultset of the AdminCourseFilter.
     * Also saves the settings in the session.
     * Note that a notification AdminCourseFilterWillQuery will be posted, before the result is computed.
     * Plugins may register at this event to fully alter this AdminCourseFilter-object and so the resultset.
     * @return array : associative array with seminar_ids as keys and seminar-data-arrays as values.
     */
    public function getCourses($grouped = true)
    {
        NotificationCenter::postNotification("AdminCourseFilterWillQuery", $this);
        if (empty($this->settings['query']['where'])) {
            return [];
        }
        $statement = DBManager::get()->prepare($this->createQuery());
        $statement->execute($this->settings['parameter']);
        $_SESSION['AdminCourseFilter_settings'] = $this->settings;
        return $statement->fetchAll($grouped ? (PDO::FETCH_GROUP | PDO::FETCH_ASSOC) : PDO::FETCH_ASSOC);
    }

    /**
     * @return number of courses that this filter would return
     */
    public function countCourses()
    {
        NotificationCenter::postNotification("AdminCourseFilterWillQuery", $this);
        if (empty($this->settings['query']['where'])) {
            return 0;
        }
        return DBManager::get()->fetchColumn($this->createQuery(true), $this->settings['parameter']);
    }

    /**
     * Returns the data of the resultset of the AdminCourseFilter.
     *
     * Note that a notification AdminCourseFilterWillQuery will be posted, before the result is computed.
     * Plugins may register at this event to fully alter this AdminCourseFilter-object and so the resultset.
     * @return array : associative array with seminar_ids as keys and seminar-data-arrays as values.
     */
    public function getCoursesForAdminWidget()
    {
        $count_courses = $this->countCourses();
        if ($count_courses && $count_courses <= $this->max_show_courses) {
            $settings = $this->settings;
            $this->settings['query']['select'] = [];
            $this->settings['query']['orderby'] = Config::get()->IMPORTANT_SEMNUMBER ? 'seminare.veranstaltungsnummer, seminare.name' : 'seminare.name';
            $ret = $this->getCourses(false);
            $this->settings = $settings;
            return $ret;
        }
        return [];
    }

    /**
     * Creates the sql-query from the $this->settings['query']
     * @only_count : boolean
     * @return string : the big query
     */
    public function createQuery($only_count = false)
    {
        if ($only_count) {
            $select_query = "COUNT(DISTINCT seminare.Seminar_id) ";
        } else {
            $select_query = "seminare.* ";
            foreach ((array) $this->settings['query']['select'] as $alias => $select) {
                $select_query .= ", ".$select." AS ".$alias." ";
            }
        }

        $join_query = "";
        foreach ((array) $this->settings['query']['joins'] as $alias => $joininfo) {
            $table = isset($joininfo['table']) ? $joininfo['table']." AS ".$alias : $alias;
            $on = isset($joininfo['on']) ? " ON (".$joininfo['on'].")" : "";
            $join_query .= " ".(isset($joininfo['join']) ? $joininfo['join'] : "INNER JOIN")." ".$table.$on." ";
        }

        $where_query = "";
        if (count($this->settings['query']['where']) > 0) {
            $where_query .= implode(" AND ", $this->settings['query']['where']);
        }

        $query = "
            SELECT ".$select_query."
            FROM seminare
                ".$join_query."
            ".($where_query ? "WHERE ".$where_query : "");
        if (!$only_count) {
            $query .= " GROUP BY seminare.Seminar_id ORDER BY ".$this->settings['query']['orderby'].($this->settings['query']['orderby'] !== "seminare.name" ? ", seminare.name" : "");
        }

        return $query;
    }

}
