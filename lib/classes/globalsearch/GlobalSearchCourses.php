<?php
/**
 * Global search module for courses
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchCourses extends GlobalSearchModule implements GlobalSearchFulltext
{
    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Veranstaltungen');
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
        $search = str_replace(' ', '% ', $search);
        $query = DBManager::get()->quote("%{$search}%");

        $language = DBManager::get()->quote($_SESSION['_language']);

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $visibility = "courses.`visible` = 1 AND ";
            $seminaruser = " AND NOT EXISTS (
                SELECT 1 FROM `seminar_user`
                WHERE `seminar_id` = `courses`.`Seminar_id`
                    AND `user_id` = " . DBManager::get()->quote($GLOBALS['user']->id) . "
            ) ";
        }

        // generate SQL for the given sidebar filter (semester, institute, seminar_type)
        if ($filter['category'] === self::class || $filter['category'] === 'show_all_categories') {
            if ($filter['semester']) {
                $semester_condition = " AND (`courses`.start_time <= " . DBManager::get()->quote($filter['semester']) .
                            " AND (`courses`.start_time + `courses`.duration_time >= " . DBManager::get()->quote($filter['semester']).
                            " OR `courses`.duration_time = -1)) ";
            }
            if ($filter['institute']) {
                $institutes = self::getInstituteIdsForSQL($filter['institute']);
                $institute_condition = " AND `courses`.`Institut_id` IN (" . DBManager::get()->quote($institutes) . ") ";
            }
            if ($filter['seminar_type']) {
                $seminar_types = self::getSeminarTypesForSQL($filter['seminar_type']);
                $seminar_type_condition = " AND `courses`.`status` IN (" . DBManager::get()->quote($seminar_types) . ") ";
            }
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS courses.`Seminar_id`, courses.`start_time`,
                       IFNULL(`i18n`.`value`, courses.`Name`) AS `Name`,
                       courses.`VeranstaltungsNummer`, courses.`status`
                FROM `seminare` AS courses
                LEFT JOIN `i18n`
                  ON `i18n`.`object_id` = courses.`Seminar_id`
                      AND `i18n`.`table` = 'seminare'
                      AND `i18n`.`field` = 'name'
                      AND `lang` = {$language}
                JOIN `sem_types` ON (courses.`status` = `sem_types`.`id`)
                JOIN `seminar_user` u ON (u.`Seminar_id` = courses.`Seminar_id` AND u.`status` = 'dozent')
                JOIN `auth_user_md5` a ON (a.`user_id` = u.`user_id`)
                WHERE {$visibility}
                    (
                        IFNULL(`i18n`.`value`, courses.`Name`) LIKE {$query}
                        OR courses.`VeranstaltungsNummer` LIKE {$query}
                        OR CONCAT_WS(' ', `sem_types`.`name`, IFNULL(`i18n`.`value`, courses.`Name`), `sem_types`.`name`) LIKE {$query}
                        OR CONCAT_WS(', ', a.`Nachname`, a.`Vorname`) LIKE {$query}
                        OR CONCAT_WS(' ', a.`Nachname`, a.`Vorname`, a.`Nachname`) LIKE {$query}
                    )
                {$seminaruser}
                {$institute_condition}
                {$seminar_type_condition}
                {$semester_condition}
                GROUP BY courses.Seminar_id
                ORDER BY ABS(start_time - UNIX_TIMESTAMP()) ASC";

        if (Config::get()->IMPORTANT_SEMNUMBER) {
            $sql .= ", courses.`VeranstaltungsNummer`";
        }

        $sql .= ", IFNULL(`i18n`.`value`, courses.`Name`)";
        $sql .= " LIMIT " . $limit;

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
    public static function filter($data, $search)
    {
        $course = Course::buildExisting($data);
        $seminar = new Seminar($course);
        $turnus_string = $seminar->getDatesExport([
            'short'  => true,
            'shrink' => true,
        ]);
        //Shorten, if string too long (add link for details.php)
        if (mb_strlen($turnus_string) > 70) {
            $turnus_string = htmlReady(mb_substr($turnus_string, 0, mb_strpos(mb_substr($turnus_string, 70, mb_strlen($turnus_string)), ',') + 71));
            $turnus_string .= ' ... <a href="' . URLHelper::getURL("dispatch.php/course/details/index/{$course->id}") . '">(' . _('mehr') . ')</a>';
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
     * Enables fulltext (MATCH AGAINST) search by creating the corresponding indices.
     */
    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE `seminare` ADD FULLTEXT INDEX globalsearch (`VeranstaltungsNummer`, `Name`)");
        DBManager::get()->exec("ALTER TABLE `sem_types` ADD FULLTEXT INDEX globalsearch (`Name`)");
    }

    /**
     * Disables fulltext (MATCH AGAINST) search by removing the corresponding indices.
     */
    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON `seminare`");
        DBManager::get()->exec("DROP INDEX globalsearch ON `sem_types`");
    }

    /**
     * Executes a fulltext (MATCH AGAINST) search in database for the given search term.
     *
     * @param string $search the term to search for.
     * @return string SQL query.
     */
    public static function getFulltextSearch($search)
    {
        if (!$search) {
            return null;
        }

        $query = DBManager::get()->quote(preg_replace("/(\w+)[*]*\s?/", "+$1* ", $search));

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $visibility = "courses.visible = 1 AND ";
            $seminaruser = " AND NOT EXISTS (
                SELECT 1 FROM seminar_user
                WHERE seminar_id = courses.Seminar_id
                    AND user_id = ".DBManager::get()->quote($GLOBALS['user']->id).") ";
        }

        $semtype = DBManager::get()->query(
            "SELECT `id`, `name` FROM `sem_types` WHERE MATCH (`name`) AGAINST ($query IN BOOLEAN MODE)");
        while ($type = $semtype->fetch(PDO::FETCH_ASSOC)) {

            $semtypes[] = $type['id'];
            // Get up some order criteria with the semtypes
            // Remove semtypes form query
                $replace = "/".$type['name'][0].chunk_split(mb_substr($type['name'], 1), 1, '?')."\*\s?/i";
                $query = preg_replace($replace, "", $query);
        }

        if (isset($semtypes)) {
            $semstatus = "`status` IN (".join(",",$semtypes) .") DESC, ";
        }

        $sql = "SELECT courses.`Seminar_id`, courses.`start_time`, courses.`Name`,
                    courses.`VeranstaltungsNummer`, courses.`status`
                FROM `seminare` AS courses
                WHERE MATCH(`VeranstaltungsNummer`, `Name`) AGAINST ($query IN BOOLEAN MODE)
                ORDER BY $semstatus ABS(`start_time` - UNIX_TIMESTAMP()) ASC,
                     MATCH(`VeranstaltungsNummer`, `Name`) AGAINST ($query IN BOOLEAN MODE) DESC
                LIMIT " . (4 * Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE);
        return $sql;
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
