<?php

/**
 * Abstract class GlobalSearchModule
 *
 * Module for global search extensions, e.g. forum, files or users
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
abstract class GlobalSearchModule
{
    /**
     * Employ generic cache methods. Be aware that this cache is shared among
     * the search modules so use indices properly:
     *
     * - user/:id for users
     * - range/:id for courses and institutes
     * - folder/id for folders
     *
     * Please add to this list if you add a new module that introduces a new
     * type.
     */
    use GlobalSearchCacheTrait;

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    abstract public static function getName();

    /**
     * Has to return a SQL Query that discovers all objects. All retrieved data is passed row by row to getGlobalSearchFilter.
     *
     * @param $search the input query string
     * @param $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return String SQL Query to discover elements for the search
     */
    abstract public static function getSQL($search, $filter, $limit);

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param $data One row returned from getSQL SQL Query
     * @param $search The searchstring (Use for markup e.g. self::mark)
     * @return mixed Information Array
     */
    abstract public static function filter($data, $search);

    /**
     * Returns the filters that are displayed in the sidebar of the global search.
     *
     * @return array Filters for this class.
     */
    public static function getFilters()
    {
        return '';
    }

    /**
     * Returns the URL that can be called for a full search, containing
     * the specified category and the searchterm.
     *
     * Overwrite this method in your subclass to provide the category
     * specific search.
     *
     * @param string $searchterm what to search for?
     * @return URL to the full search, containing the searchterm and the category
     */
    public static function getSearchURL($searchterm)
    {
        return '';
    }

    /**
     * Function to mark a querystring in a resultstring
     *
     * @param $string
     * @param $query
     * @param bool|true $filename
     * @return mixed
     */
    public static function mark($string, $query, $longtext = false, $filename = true)
    {
        // Secure
        $string = strip_tags($string);

        // Maximum length for an unshortened string.
        $maxlength = 100;

        if (mb_strpos($query, '/') !== false) {
            $args = explode('/', $query);
            if ($filename) {
                return self::mark($string, trim($args[1]));
            }
            return self::mark($string, trim($args[0]));
        }

        $query = trim($query);

        // Replace direct string
        $quoted = preg_quote($query, '/');
        $result = preg_replace("/{$quoted}/Si", "<mark>$0</mark>", $string, -1, $found);

        if ($found) {
            // Check for overlength
            if ($longtext && mb_strlen($result) > $maxlength) {
                $start = max([0, mb_stripos($result, '<mark>') - 20]);
                return '[...]' . mb_substr($result, $start, $maxlength) . '[...]';
            }

            return $result;
        }

        // Replace camelcase
        $i = 1;
        $replacement = "${$i}";
        foreach (preg_split('//u', mb_strtoupper($query), -1, PREG_SPLIT_NO_EMPTY) as $letter) {
            $quoted = preg_quote($letter, '/');
            $queryletter[] = "({$quoted})";
            $replacement .= '<mark>$' . ++$i . '</mark>$' . ++$i;
        }

        $pattern = '/([\w\W]*)' . implode('([\w\W]*)', $queryletter) . '/S';
        $result = preg_replace($pattern, $replacement, $string, -1, $found);

        if ($found) {
            // Check for overlength
            if ($longtext && mb_strlen($result) > $maxlength) {
                $start = max([0, mb_stripos($result, '<mark>') - 20]);
                $space = mb_stripos($result, ' ', $start);
                $start = $space < $start + 20 ? $space : $start;
                return '[...]' . mb_substr($result, $start, $maxlength) . '[...]';
            }

            return $result;
        }

        // Check for overlength
        if ($longtext && mb_strlen($result) > $maxlength) {
            return '[...]' . mb_substr($string, 0, $maxlength) . '[...]';
        }

        if (mb_strlen($string) > $maxlength) {
            return mb_substr($string, 0, $maxlength) . '[...]';
        }

        return $string;
    }

    /**
    * Get the selected institute with sub-institutes as an array of IDs
    * or a single institute as a string to use in the SQL query.
    *
    * @param $institute_id ID of the given institute or faculty
    * @return mixed: a single institute as string if selected
    *                or an array of institute IDs if a faculty was selected
    */
    public static function getInstituteIdsForSQL($institute_id)
    {
        $institutes = Institute::findByFaculty($institute_id);
        if ($institutes) {
            $institute_ids = array_column($institutes, 'Institut_id');
            $institute_ids[] = $institute_id;
            return $institute_ids;
        } else {
            return $institute_id;
        }
    }

    /**
     * Get the selected seminar class with sub-types as an array
     * or a single seminar type as a string to use in an SQL query.
     *
     * @param $sem_class a single sem_type ID or a sem_class containing multiple sem_types
     * @return mixed: seminar class/types formatted for an SQL query
     */
    public static function getSeminarTypesForSQL($sem_class)
    {
        $classes = SemClass::getClasses();
        if ($pos = strpos($sem_class, '_')) {
            // return just the sem_types.id (which is equal to seminare.status)
            return substr($sem_class, $pos + 1);
        } else {
            $type_ids = [];
            // return an array containing all sem_types belonging to the chosen sem_class
            $class = $classes[$sem_class];
            foreach ($class->getSemTypes() as $types_id => $types) {
                array_push($type_ids, $types['id']);
            }
            return $type_ids;
        }
    }

    /**
     * Get the current semester considering the given
     * SEMESTER_TIME_SWITCH in the CONFIG
     * (n weeks before the next semester)
     *
     * @return int The start time of the current semester.
     */
    public static function getCurrentSemester()
    {
        $sem_time_switch = Config::get()->SEMESTER_TIME_SWITCH;
        $current_semester = Semester::findByTimestamp(time() + $sem_time_switch * 7 * 24 * 3600);

        return $current_semester['beginn'];
    }

    /**
     * Returns a list of all active search modules
     * @return array search_class => data
     */
    public static function getActiveSearchModules()
    {
        $modules = Config::get()->GLOBALSEARCH_MODULES;

        // TODO: Throw this away and activate the php7 code as soon as possible
        foreach ($modules as $module => $data) {
            if ($module === 'GlobalSearchModules' && !MVV::isVisibleSearch()) {
                unset($modules[$module]);
                continue;
            }

            if (in_array($module, ['GlobalSearchResources', 'GlobalSearchRoomAssignments'])
                && !Config::get()->RESOURCES_ENABLE)
            {
                unset($modules[$module]);
                continue;
            }

            if (!$data['active'] || !class_exists($module, true)) {
                unset($modules[$module]);
                continue;
            }
        }

        // PHP7
        // $modules = array_filter($modules, function ($data, $module) {
        //     if ($module === 'GlobalSearchModules' && !MVV::isVisibleSearch()) {
        //         return false;
        //     }
        //
        //     if (in_array($module, ['GlobalSearchResources', 'GlobalSearchRoomAssignments'])
        //         && !Config::get()->RESOURCES_ENABLE)
        //     {
        //         return false;
        //     }
        //
        //     return $data['active'] && class_exists($module, true);
        // }, ARRAY_FILTER_USE_BOTH);

        return array_keys($modules);
    }
}
