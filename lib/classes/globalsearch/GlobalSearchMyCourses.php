<?php
/**
 * GlobalSearchModule for my courses
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchMyCourses extends GlobalSearchModule
{
    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Meine Veranstaltungen');
    }

    /**
     * Returns the filters that are displayed in the sidebar of the global search.
     *
     * @return array Filters for this class.
     */
    public static function getFilters()
    {
        return ['semester', 'institute', 'seminar_type'];
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @param $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        if (!$search) {
            return null;
        }

        // generate SQL for the given sidebar filter (semester, institute, seminar_type)
        if ($filter['category'] === self::class || $filter['category'] == 'show_all_categories') {
            if ($filter['semester']) {
                $semester_condition = " AND (`courses`.start_time <= " .DBManager::get()->quote($filter['semester']).
                            " AND (`courses`.start_time + `courses`.duration_time >= " .DBManager::get()->quote($filter['semester']).
                            " OR `courses`.duration_time = -1)) ";
            }
            if ($filter['institute']) {
                $institutes = self::getInstituteIdsForSQL($filter['institute']);
                $institute_condition = " AND `courses`.`Institut_id` IN (" .DBManager::get()->quote($institutes). ") ";
            }
            if ($filter['seminar_type']) {
                $seminar_types = self::getSeminarTypesForSQL($filter['seminar_type']);
                $seminar_type_condition = " AND `courses`.`status` IN (" .DBManager::get()->quote($seminar_types). ") ";
            }
        }

        $search = str_replace(" ", "% ", $search);
        $query = DBManager::get()->quote("%{$search}%");
        $user_id = DBManager::get()->quote($GLOBALS['user']->id);
        $sql = "SELECT SQL_CALC_FOUND_ROWS courses.* FROM `seminare` AS  courses
                JOIN `seminar_user` USING (`Seminar_id`)
                JOIN `sem_types` ON (courses.`status` = `sem_types`.`id`)
                WHERE `user_id` = {$user_id}
                  AND (courses.`Name` LIKE {$query}
                    OR courses.`VeranstaltungsNummer` LIKE {$query}
                    OR CONCAT_WS(' ', `sem_types`.`name`, courses.`Name`) LIKE {$query}
                  )
                  {$institute_condition}
                  {$seminar_type_condition}
                  {$semester_condition}
                ORDER BY `start_time` DESC
                LIMIT " . $limit;
        return $sql;
    }

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
     * @param $id
     * @param $search
     * @return mixed
     */
    public static function filter($course_id, $search)
    {
        $course = Course::buildExisting($course_id);
        $seminar = new Seminar($course);
        $turnus_string = $seminar->getDatesExport([
            'short'  => true,
            'shrink' => true,
        ]);
        //Shorten, if string too long (add link for details.php)
        if (mb_strlen($turnus_string) > 70) {
            $turnus_string = htmlReady(mb_substr($turnus_string, 0, mb_strpos(mb_substr($turnus_string, 70, mb_strlen($turnus_string)), ',') + 71));
            $turnus_string .= ' ... <a href="' . URLHelper::getURL('dispatch.php/course/details/index/' . $course->id) . '">(' . _('mehr') . ')</a>';
        } else {
            $turnus_string = htmlReady($turnus_string);
        }
        $lecturers = $course->getMembersWithStatus('dozent');
        $semester = $course->start_semester;

        // If you are not root, perhaps not all available subcourses are visible.
        $visibleChildren = $course->children;
        if (!$GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)) {
            $visibleChildren = $visibleChildren->filter(function($c) {
                return $c->visible;
            });
        }
        $result_children = [];
        foreach($visibleChildren as $child) {
            $result_children[] = self::filter($child, $search);
        }

        $result = [
            'id'            => $course->id,
            'number'        => self::mark($course->veranstaltungsnummer, $search),
            'name'          => self::mark($course->getFullname(), $search),
            'url'           => URLHelper::getURL('dispatch.php/course/details/index/' . $course->id),
            'date'          => $semester->token ?: $semester->name,
            'dates'         => $turnus_string,
            'has_children'  => count($course->children) > 0,
            'children'      => $result_children,
            'additional'    => implode(', ',
                array_filter(
                    array_map(
                        function ($lecturer, $index) use ($search, $course) {
                            if ($index < 3) {
                                return '<a href="' . URLHelper::getURL('dispatch.php/profile', ['username' => $lecturer->username]) . '">' . self::mark($lecturer->getUserFullname(), $search) . '</a>';
                            } else if ($index == 3) {
                                return '<a href="' . URLHelper::getURL('dispatch.php/course/details/index/' . $course->id) . '">... (' . _('mehr') . ') </a>';
                            }
                        },
                        $lecturers,
                        array_keys($lecturers)
                    )
                )
            ),
            'expand'     => self::getSearchURL($search),
        ];
        if ($course->getSemClass()->offsetGet('studygroup_mode')) {
            $avatar = StudygroupAvatar::getAvatar($course->id);
        } else {
            $avatar = CourseAvatar::getAvatar($course->id);
        }
        $result['img'] = $avatar->getUrl(Avatar::MEDIUM);
        return $result;
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * @param string $searchterm what to search for?
     * @return URL to the full search, containing the searchterm and the category
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class
        ]);
    }

}
