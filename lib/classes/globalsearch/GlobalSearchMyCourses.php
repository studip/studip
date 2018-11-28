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
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search)
    {
        if (!$search) {
            return null;
        }
        $search = str_replace(" ", "% ", $search);
        $query = DBManager::get()->quote("%{$search}%");
        $user_id = DBManager::get()->quote($GLOBALS['user']->id);
        $sql = "SELECT courses.* FROM `seminare` AS  courses
                JOIN `seminar_user` USING (`Seminar_id`)
                JOIN `sem_types` ON (courses.`status` = `sem_types`.`id`)
                WHERE `user_id` = $user_id
                  AND (courses.`Name` LIKE $query
                        OR courses.`VeranstaltungsNummer` LIKE $query
                        OR CONCAT_WS(' ', `sem_types`.`name`, courses.`Name`) LIKE $query
                      )
                ORDER BY `start_time` DESC
                LIMIT " . (4 * Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE);
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
        $result = [
            'id'     => $course->id,
            'name'   => self::mark($course->getFullname(), $search),
            'url'    => URLHelper::getURL('dispatch.php/course/overview/', ['cid' => $course->id]),
            'date'   => $course->start_semester->name,
            'expand' => self::getSearchURL($search),
        ];
        $avatar = CourseAvatar::getAvatar($course->id);
        $result['img'] = $avatar->getUrl(Avatar::MEDIUM);
        return $result;
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * This could become obsolete when we have a real global search page.
     *
     * @param string $searchterm what to search for?
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/courses', [
            'reset_all'                                   => 1,
            'search_sem_qs_choose'                        => 'title_lecturer_number',
            'search_sem_sem'                              => 'all',
            'search_sem_quick_search_parameter'           => $searchterm,
            'search_sem_1508068a50572e5faff81c27f7b3a72f' => 1 // Why the hell is that needed?
        ]);
    }

}
