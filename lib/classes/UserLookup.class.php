<?php
# Lifter010: TODO
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.1
 */

/**
 * UserLookup.class.php
 * provides an easy way to look up user ids by certain filter criteria
 *
 * Example of use:
 * @code
 *   # Create a new UserLookup object
 *   $user_lookup = new UserLookup;
 *
 *   # Filter all users in their first to sixth fachsemester
 *   $user_lookup->setFilter('fachsemester', range(1, 6));
 *
 *   # Filter all users that have an 'autor' or 'tutor' permission
 *   $user_lookup->setFilter('status', array('autor', 'tutor'));
 *
 *   # Get a list of all matching user ids (sorted by the user's names)
 *   $user_ids = $user_lookup->execute(UserLookup::FLAG_SORT_NAME);
 *
 *   # Get another list of all matching user ids but this time we want
 *   # the complete unordered dataset
 *   $user_ids = $user_lookup->execute(UserLookup::FLAG_RETURN_FULL_INFO);
 * @endcode
 */

final class UserLookup
{
    // At the moment, the cache is only used for the GetValuesForType method
    const USE_CACHE = false;
    const CACHE_DURATION = 3600; // 1 hour

    const FLAG_SORT_NAME = 1;
    const FLAG_RETURN_FULL_INFO = 2;

    /**
     * Predefined array of filter criteria
     *
     * @var array
     */
    protected static $types = array(
        'abschluss' => array(
            'filter' => 'UserLookup::abschlussFilter',
            'values' => 'UserLookup::abschlussValues',
        ),
        'fach' => array(
            'filter' => 'UserLookup::fachFilter',
            'values' => 'UserLookup::fachValues',
        ),
        'fachsemester' => array(
            'filter' => 'UserLookup::fachsemesterFilter',
            'values' => 'UserLookup::fachsemesterValues',
        ),
        'institut' => array(
            'filter' => 'UserLookup::institutFilter',
            'values' => 'UserLookup::institutValues',
        ),
        'status' => array(
            'filter' => 'UserLookup::statusFilter',
            'values' => 'UserLookup::statusValues',
        ),
        'domain'=>array(
            'filter'    =>'UserLookup::domainFilter',
            'values'    =>'UserLookup::domainValues'
        )
    );

    /**
     * Contains the resulting filter set
     * @var array
     */
    private $filters = array();

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
            throw new Exception('[UserLookup] Cannot set filter for unknown type "'.$type.'"');
        }

        if (!isset($this->filters[$type])) {
            $this->filters[$type] = array();
        }

        $this->filters[$type] = array_merge($this->filters[$type], (array)$value);

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
        foreach ($this->filters as $type => $values) {
            $temp_result = call_user_func(self::$types[$type]['filter'], $values);

            if ($result === null) {
                $result = $temp_result;
            } else {
                $result = array_intersect($result, $temp_result);
            }
        }

        if (($flags & self::FLAG_SORT_NAME) and !($flags & self::FLAG_RETURN_FULL_INFO)) {
            $temp_result = self::arrayQuery("SELECT user_id FROM auth_user_md5 WHERE user_id IN (??) ORDER BY Nachname ASC, Vorname ASC", $result);
            $result = $temp_result->fetchAll(PDO::FETCH_COLUMN);
        }

        if (!empty($result) and ($flags & self::FLAG_RETURN_FULL_INFO)) {
            $query = "SELECT user_id, username, Vorname, Nachname, Email, perms FROM auth_user_md5 WHERE user_id IN (??)";
            if ($flags & self::FLAG_SORT_NAME) {
                $query .= " ORDER BY Nachname ASC, Vorname ASC";
            }

            $temp_result = self::arrayQuery($query, $result);
            $result = $temp_result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            $result = array_map('reset', $result);
        }

        return $result;
    }

    /**
     * Clears all defined filters.
     *
     * @return UserLookup Returns itself to allow chaining
     */
    public function clearFilters()
    {
        $this->filters = array();
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
            throw new Exception('[UserLookup] Values callback for type "'.$name.'" is not callable');
        }
        if (!is_callable($filter_callback)) {
            throw new Exception('[UserLookup] Filter callback for type "'.$name.'" is not callable');
        }

        self::$types[$name] = array(
            'filter' => $filter_callback,
            'values' => $values_callback,
        );
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
            throw new Exception('[UserLookup] Unknown type "'.$type.'"');
        }

        if (self::USE_CACHE) {
            $cache = StudipCacheFactory::getCache();
            $cache_key = 'UserLookup/'.$type.'/values';
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
     * Convenience method to query over an array via ".. WHERE x IN (<array>)".
     * Any ?? is substituted by the array of values.
     *
     * @param  string $query  The query to execute
     * @param  array  $values Array containing the values to search for
     * @return PDOStatement   Result of the query against the db
     */
    protected static function arrayQuery($query, $values)
    {
        $values = array_unique($values);
        $values = array_map(array(DBManager::get(), 'quote'), $values);
        $query = str_replace('??', implode(',', $values), $query);

        return DBManager::get()->query($query);
    }

    /**
     * Return all user with matching studiengang_id in $needles
     * @param  array $needles List of studiengang ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function fachFilter($needles)
    {
        $db_result = self::arrayQuery("SELECT user_id FROM user_studiengang WHERE fach_id IN (??)", $needles);
        return $db_result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Return all studycourses
     * @return array Associative array of studiengang ids and studiengang names
     */
    protected static function fachValues()
    {
        $db_result = DBManager::get()->query("SELECT fach_id, name FROM fach ORDER BY name ASC");
        $result = $db_result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
        return array_map('reset', $result);
    }

    /**
     * Return all user with matching abschluss_id in $needles
     * @param  array $needles List of abschluss ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function abschlussFilter($needles)
    {
        $result = self::arrayQuery("SELECT user_id FROM user_studiengang WHERE abschluss_id IN (??)", $needles);
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Return all studydegrees
     * @return array Associative array of abschluss ids and abschluss names
     */
    protected static function abschlussValues()
    {
        $db_result = DBManager::get()->query("SELECT abschluss_id, name FROM abschluss ORDER BY name ASC");
        $result = $db_result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
        return array_map('reset', $result);
    }

    /**
     * Return all users with a matching fachsemester given in $needles
     * @param  array $needles List of fachsemesters to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function fachsemesterFilter($needles)
    {
        $result = self::arrayQuery("SELECT user_id FROM user_studiengang WHERE semester IN (??)", $needles);
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Create a array with all possible values for studysemesters
     * @return array Associative array of fachsemesters and fachsemesters
     *               (pretty dull, i know)
     */
    protected static function fachsemesterValues()
    {
        $db_result = DBManager::get()->query("SELECT MAX(semester) FROM user_studiengang");
        $max = $db_result->fetchColumn();
        $values = range(1, $max);
        return array_combine($values, $values);
    }

    /**
     * Return all users with a matching institut_id given in $needles
     * @param  array $needles List of institut ids to filter against
     * @return array List of user ids matching the given filter
     */
    protected static function institutFilter($needles)
    {
        $result = self::arrayQuery("SELECT user_id FROM user_inst WHERE Institut_id IN (??)", $needles);
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Return all faculty's and instituts
     * @return array Associative array of institut ids and institut data
     *               (Be aware that this array is multidimensional)
     */
    protected static function institutValues()
    {
        $db_result = DBManager::get()->query("SELECT fakultaets_id, Institut_id, Name, fakultaets_id = Institut_id AS is_fakultaet FROM Institute ORDER BY Institut_id = fakultaets_id DESC, Name ASC")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $result = array();
        foreach ($db_result as $fakultaets_id => $items) {
            foreach ($items as $item) {
                if (!isset($result[$fakultaets_id])) {
                    $result[$fakultaets_id] = array(
                        'name'   => $item['Name'],
                        'values' => array(),
                    );
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
        $result = self::arrayQuery("SELECT user_id FROM auth_user_md5 WHERE perms IN (??)", $needles);
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    protected static function domainFilter($needles){
   $result = self::arrayQuery("SELECT user_id FROM user_userdomains WHERE userdomain_id IN (??)", $needles);
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }
    protected static function domainValues(){
     
           $domains = array();
        $domains ['keine']=_('Ohne Domain');
        foreach(UserDomain::getUserDomains() as $domain){
            $domains[$domain->getId()] = $domain->getName();
        }
       
        return $domains;
    }

    /**
     * Return all valid statusses
     * @return array Associative array of status name and description
     */
    protected static function statusValues()
    {
        return array(
            'autor'  => _('Autor'),
            'tutor'  => _('Tutor'),
            'dozent' => _('Dozent'),
            'admin'  => _('Admin'),
            'root'   => _('Root'),
        );
    }
}
