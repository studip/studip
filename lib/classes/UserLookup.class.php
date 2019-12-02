<?php
/**
 * UserLookup.class.php
 *
 * provides an easy way to look up user ids by certain filter criteria
 *
 * Example of use:
 * @code
 *   # Create a new UserLookup object
 *    $user_lookup = new UserLookup;
 *
 *   # Filter all users in their first to sixth fachsemester
 *   $user_lookup->setFilter('fachsemester', range(1, 6));
 *
 *   # Filter all users that have an 'autor' or 'tutor' permission
 *   $user_lookup->setFilter('status', ['autor', 'tutor']);
 *
 *   # Get a list of all matching user ids (sorted by the user's names)
 *   $user_ids = $user_lookup->execute(UserLookup::FLAG_SORT_NAME);
 *
 *   # Get another list of all matching user ids but this time we want
 *   # the complete unordered dataset
 *   $user_ids = $user_lookup->execute(UserLookup::FLAG_RETURN_FULL_INFO);
 * @endcode
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.1
 */
class UserLookup
{
    // At the moment, the cache is only used for the GetValuesForType method
    const USE_CACHE = false;
    const CACHE_DURATION = 3600; // 1 hour

    const FLAG_SORT_NAME = 1;
    const FLAG_RETURN_FULL_INFO = 2;

    // Special constant to use for a combined study group filter
    const USE_COMBINED_STUDYGROUP_FILTER = 'use-combined-studygroup-filter';

    /**
     * Predefined array of filter criteria
     *
     * @var array
     */
    protected static $types = [
        'abschluss' => [
            'filter' => self::USE_COMBINED_STUDYGROUP_FILTER,
            'values' => 'UserLookup::abschlussValues',
        ],
        'fach' => [
            'filter' => self::USE_COMBINED_STUDYGROUP_FILTER,
            'values' => 'UserLookup::fachValues',
        ],
        'fachsemester' => [
            'filter' => self::USE_COMBINED_STUDYGROUP_FILTER,
            'values' => 'UserLookup::fachsemesterValues',
        ],
        'institut' => [
            'filter' => 'UserLookup::institutFilter',
            'values' => 'UserLookup::institutValues',
        ],
        'status' => [
            'filter' => 'UserLookup::statusFilter',
            'values' => 'UserLookup::statusValues',
        ],
        'domain' => [
            'filter'    =>'UserLookup::domainFilter',
            'values'    =>'UserLookup::domainValues'
        ],
    ];

    /**
     * Contains the resulting filter set
     * @var array
     */
    private $filters = [];

    /**
     * Adds another type filter to the set of current filters.
     *
     * Multiple filters for the same filter type result in an AND filter
     * within this type while multiple filters across filter types result
     * in an OR filter across these types.
     *
     * @param  string $type   Type of filter to add
     * @param  string $value  Value to filter against
     * @return UserLookup     Returns itself to allow chaining
     */
    public function setFilter($type, $value)
    {
        if (!array_key_exists($type, self::$types)) {
            throw new Exception('[UserLookup] Cannot set filter for unknown type "' . $type . '"');
        }

        if (!isset($this->filters[$type])) {
            $this->filters[$type] = [];
        }

        $this->filters[$type] = array_merge($this->filters[$type], (array) $value);

        return $this;
    }

    /**
     * Executes the actual lookup by executing all individual filter types
     * and returning the intersection of all according result sets.
     *
     * Possible flags:
     *  - FLAG_SORT_NAME         Sorts the user ids in the result by the
     *                           actual user names
     *  - FLAG_RETURN_FULL_INFO  Returns rudimental user info instead of just
     *                           the ids (as an array with the user id as key
     *                           and an array containting the info as value)
     *
     * @param  int $flags Optional set of flags as seen above
     * @return array      Either a simple list of user ids or an associative
     *                    array of user ids and user info if FLAG_RETURN_FULL_INFO
     *                    is set
     */
    public function execute($flags = null)
    {
        if (count($this->filters) === 0) {
            throw new Exception('[UserLookup] Cannot execute empty filter set');
        }

        $result = null;
        foreach ($this->getFilters() as $filter) {
            $temp_result = call_user_func($filter['callable'], $filter['needles']);

            if ($result === null) {
                $result = $temp_result;
            } else {
                $result = array_intersect($result, $temp_result);
            }
        }

        if (($flags & self::FLAG_SORT_NAME) && !($flags & self::FLAG_RETURN_FULL_INFO)) {
            $query = "SELECT user_id
                      FROM auth_user_md5
                      WHERE user_id IN (?)
                      ORDER BY Nachname ASC, Vorname ASC";
            $result = DBManager::get()->fetchFirst($query, [$result ?: '']);
        }

        if (!empty($result) && ($flags & self::FLAG_RETURN_FULL_INFO)) {
            $query = "SELECT `user_id`, `username`, `Vorname`, `Nachname`, `Email`, `perms`
                      FROM `auth_user_md5`
                      WHERE `user_id` IN (?)";
            if ($flags & self::FLAG_SORT_NAME) {
                $query .= " ORDER BY Nachname ASC, Vorname ASC";
            }

            $result = DBManager::get()->fetchGrouped($query, [$result ?: '']);
        }

        return $result;
    }

    protected function getFilters()
    {
        $filters = [];
        $study_course_filter = [];
        foreach ($this->filters as $type => $values) {
            if (self::$types[$type]['filter'] === self::USE_COMBINED_STUDYGROUP_FILTER) {
                $study_course_filter[$type] = $values;
            } else {
                $filters[] = [
                    'callable' => self::$types[$type]['filter'],
                    'needles'  => $values,
                ];
            }
        }

        if ($study_course_filter) {
            $filters[] = [
                'callable' => [$this, 'combinedStudyCourseFilter'],
                'needles'  => $study_course_filter,
            ];
        }

        return $filters;
    }

    /**
     * Clears all defined filters.
     *
     * @return UserLookup Returns itself to allow chaining
     */
    public function clearFilters()
    {
        $this->filters = [];
        $this->study_course_filter = [];
        return $this;
    }

    /**
     * Adds or updates a filter criterion the global set of criteria.
     *
     * @param string   $name            Name of the criterion type
     * @param callback $values_callback Callback for the type's values
     * @param callback $filter_callback Actual filter callback for a defined
     *                                  set of needles
     */
    public static function addType($name, $values_callback, $filter_callback)
    {
        if (!is_callable($values_callback)) {
            throw new Exception('[UserLookup] Values callback for type "' . $name . '" is not callable');
        }
        if (!is_callable($filter_callback)) {
            throw new Exception('[UserLookup] Filter callback for type "' . $name . '" is not callable');
        }

        self::$types[$name] = [
            'filter' => $filter_callback,
            'values' => $values_callback,
        ];
    }

    /**
     * Returns all valid values for a certain criterion type.
     *
     * @param  string $type Name of the criterion type
     * @return array  Associative array containing the values as keys and
     *                descriptive names as values
     */
    public static function getValuesForType($type)
    {
        if (!array_key_exists($type, self::$types)) {
            throw new Exception('[UserLookup] Unknown type "' . $type . '"');
        }

        if (self::USE_CACHE) {
            $cache = StudipCacheFactory::getCache();
            $cache_key = "UserLookup/{$type}/values";
            $cached_values = $cache->read($cache_key);
            if ($cached_values) {
                return unserialize($cached_values);
            }
        }

        $values = call_user_func(self::$types[$type]['values']);

        if (self::USE_CACHE) {
            $cache->write($cache_key, serialize($values), self::CACHE_DURATION);
        }

        return $values;
    }

    /**
     * Return all user with matching studiengang_id in $needles
     * @param  array $needles List of studiengang ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function fachFilter($needles)
    {
        $query = "SELECT user_id FROM user_studiengang WHERE fach_id IN (?)";
        return DBManager::get()->fetchFirst($query, [$needles ?: '']);
    }

    /**
     * Return all studycourses
     * @return array Associative array of studiengang ids and studiengang names
     */
    protected static function fachValues()
    {
        $query = "SELECT `fach_id`, `name`
                  FROM `fach`
                  ORDER BY `name` ASC";
        return DBManager::get()->fetchPairs($query);
    }

    /**
     * Return all user with matching abschluss_id in $needles
     * @param  array $needles List of abschluss ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function abschlussFilter($needles)
    {
        $query = "SELECT user_id FROM user_studiengang WHERE abschluss_id IN (?)";
        return DBManager::get()->fetchFirst($query, [$needles ?: '']);
    }

    /**
     * Return all studydegrees
     * @return array Associative array of abschluss ids and abschluss names
     */
    protected static function abschlussValues()
    {
        $query = "SELECT `abschluss_id`, `name`
                  FROM `abschluss`
                  ORDER BY `name` ASC";
        return DBManager::get()->fetchPairs($query);
    }

    /**
     * Return all users with a matching fachsemester given in $needles
     * @param  array $needles List of fachsemesters to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function fachsemesterFilter($needles)
    {
        $query = "SELECT user_id FROM user_studiengang WHERE semester IN (?)";
        return DBManager::get()->fetchFirst($query, [$needles ?: '']);
    }

    /**
     * Create a array with all possible values for studysemesters
     * @return array Associative array of fachsemesters and fachsemesters
     *               (pretty dull, i know)
     */
    protected static function fachsemesterValues()
    {
        $query = "SELECT MAX(`semester`) FROM `user_studiengang`";
        $max = DBManager::get()->fetchColumn($query);
        $values = range(1, $max);
        return array_combine($values, $values);
    }

    /**
     * Returns all user with a matching set of values in user study course
     * table.
     * @return array List of user ids matching the given filter
     */
    private static function combinedStudyCourseFilter(array $needles)
    {
        $type_column_mapping = [
            'abschluss'    => 'abschluss_id',
            'fach'         => 'fach_id',
            'fachsemester' => 'semester',
        ];

        $conditions = [];
        $parameters = [];
        foreach ($needles as $type => $needles) {
            $column = $type_column_mapping[$type];

            $conditions[] = "`{$column}` IN (:{$column})";
            $parameters[":{$column}"] = $needles;
        }

        $query = "SELECT `user_id`
                  FROM `user_studiengang`
                  WHERE " . implode(' AND ', $conditions);

        return DBManager::get()->fetchFirst($query, $parameters);
    }

    /**
     * Return all users with a matching institut_id given in $needles
     * @param  array $needles List of institut ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function institutFilter($needles)
    {
        if (!$needles) {
            return [];
        }

        $query = "SELECT `user_id`
                  FROM `user_inst`
                  WHERE `Institut_id` IN (?)";
        return DBManager::get()->fetchFirst($query, [$needles]);
    }

    /**
     * Return all faculty's and instituts
     * @return array Associative array of institut ids and institut data
     *               (Be aware that this array is multidimensional)
     */
    protected static function institutValues()
    {
        $query = "SELECT `fakultaets_id`, `Institut_id`, `Name`,
                         `fakultaets_id` = `Institut_id` AS is_fakultaet
                  FROM `Institute`
                  ORDER BY `Institut_id` = `fakultaets_id` DESC, `Name` ASC";
        $db_result = DBManager::get()->query($query)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $result = [];
        foreach ($db_result as $fakultaets_id => $items) {
            foreach ($items as $item) {
                if (!isset($result[$fakultaets_id])) {
                    $result[$fakultaets_id] = [
                        'name'   => $item['Name'],
                        'values' => [],
                    ];
                } else {
                    $result[$fakultaets_id]['values'][$item['Institut_id']] = $item['Name'];
                }
            }
        }
        return $result;
    }

    /**
     * Return all users with a matching status given in $needles
     * @param  array $needles List of statusses to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function statusFilter($needles)
    {
        if (!$needles) {
            return [];
        }

        $query = "SELECT `user_id`
                  FROM `auth_user_md5`
                  WHERE `perms` IN (?)";
        return DBManager::get()->fetchFirst($query, [$needles]);
    }

    /**
     * Return all valid statusses
     * @return array Associative array of status name and description
     */
    protected static function statusValues()
    {
        return [
            'autor'  => _('Autor'),
            'tutor'  => _('Tutor'),
            'dozent' => _('Dozent'),
            'admin'  => _('Admin'),
            'root'   => _('Root'),
        ];
    }

    /**
     * Return all users with a matching domain given in $needles
     * @param  array $needles List of domain ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function domainFilter($needles)
    {
        if (!$needles) {
            return [];
        }

        $query = "SELECT `user_id`
                 FROM `user_userdomains`
                 WHERE `userdomain_id` IN (?)";
        return DBManager::get()->fetchFirst($query, [$needles]);
    }

    /**
     * Return all valid domains
     * @return array Associative array of domain id and name
     */
    protected static function domainValues()
    {
        $domains = [];
        $domains['keine'] = _('Ohne Domain');
        foreach (UserDomain::getUserDomains() as $domain) {
            $domains[$domain->getId()] = $domain->getName();
        }

        return $domains;
    }
}
