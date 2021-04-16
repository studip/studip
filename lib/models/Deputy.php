<?php
/**
 * Deputy.class.php
 * model class for table deputies
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string range_id database column
 * @property string user_id database column
 * @property string gruppe database column
 * @property string notification database column
 * @property string edit_about database column
 * @property User deputy belongs_to User
 * @property Course course belongs_to Course
 * @property User boss has_one User
 */
class Deputy extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'deputies';

        $config['belongs_to']['deputy'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'        => 'Course',
            'foreign_key'       => 'range_id',
            'assoc_foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['boss'] = [
            'class_name'        => 'User',
            'foreign_key'       => 'range_id',
            'assoc_foreign_key' => 'user_id'
        ];

        $config['additional_fields']['vorname']       = ['deputy', 'vorname'];
        $config['additional_fields']['nachname']      = ['deputy', 'nachname'];
        $config['additional_fields']['username']      = ['deputy', 'username'];
        $config['additional_fields']['perms']         = ['deputy', 'perms'];
        $config['additional_fields']['boss_vorname']  = ['boss', 'vorname'];
        $config['additional_fields']['boss_nachname'] = ['boss', 'nachname'];
        $config['additional_fields']['boss_username'] = ['boss', 'username'];
        $config['additional_fields']['course_name']   = ['course', 'name'];
        $config['additional_fields']['course_number'] = ['course', 'veranstaltungsnummer'];

        parent::configure($config);
    }

    /**
     * Gets the full deputy name (in fact just redirecting to User class)
     * @see User::getFullname
     * @param string $format one of full,full_rev,no_title,no_title_rev,no_title_short,no_title_motto,full_rev_username
     * @return string The deputy's full name, like "John Doe" or "Doe, John"
     */
    public function getDeputyFullname($format = 'full')
    {
        return $this->deputy->getFullname($format);
    }

    /**
     * Gets the full boss name (in fact just redirecting to User class)
     * @see User::getFullname
     * @param string $format one of full,full_rev,no_title,no_title_rev,no_title_short,no_title_motto,full_rev_username
     * @return string The bosses full name, like "John Doe" or "Doe, John"
     */
    public function getBossFullname($format = 'full')
    {
        if ($this->boss) {
            return $this->boss->getFullname($format);
        } else {
            return null;
        }
    }

    /**
     * Gets the full course name (in fact just redirecting to Course class)
     * @see Course::getFullname
     * @param string $format one of default, type-name, number-type-name, number-name,
     *                       number-name-semester, sem-duration-name
     * @return string The courses' full name, like "1234 Lecture: Databases"
     */
    public function getCourseFullname($format = 'default')
    {
        if ($this->course) {
            return $this->course->getFullname($format);
        } else {
            return null;
        }
    }
    /**
     * Adds a person as deputy of a course or another person.
     *
     * @param string $user_id person to add as deputy
     * @param string $range_id ID of a course or a person
     * @return int Number of affected rows in the database (hopefully 1).
     */
    public static function addDeputy($user_id, $range_id) {
        if (self::exists([$range_id, $user_id])) {
            return true;
        }

        $d = new Deputy();
        $d->user_id  = $user_id;
        $d->range_id = $range_id;

        // Check if the given range_id is a course and has a parent.
        if ($course = Course::find($range_id)) {
            if ($course->parent_course && !self::exists([$course->parent_course, $user_id])) {
                $p = new Deputy();
                $p->user_id  = $user_id;
                $p->range_id = $course->parent_course;
                $p->store();
            }
        }

        return $d->store();
    }

    /**
     * Checks whether the given person is a deputy in the given context
     * (course or person).
     *
     * @param string $user_id person ID to check
     * @param string $range_id course or person ID
     * @param boolean $check_edit_about check if the given person may edit
     * the other person's profile
     * @return boolean Is the given person deputy in the given context?
     */
    public static function isDeputy(string $user_id, string $range_id, bool $check_edit_about=false) {
        $d = self::find([$range_id, $user_id]);
        if (!$d) {
            return false;
        }

        return $check_edit_about
            ? $d->edit_about == 1
            : true;
    }

    /**
     * Checks if persons may be assigned as default deputy of other persons.
     *
     * @return boolean activation status of the default deputy functionality.
     */
    public static function isActivated()
    {
        return Config::get()->DEPUTIES_ENABLE && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE;
    }

    /**
     * Checks if default deputies may get the rights to edit their bosses profile
     * page.
     *
     * @return boolean activation status of the deputy boss profile page editing functionality.
     */
    public static function isEditActivated() {
        return self::isActivated() && Config::get()->DEPUTIES_EDIT_ABOUT_ENABLE;
    }

    /**
     * Shows which permission level a person must have in order to be available
     * as deputy.
     *
     * @param boolean $min_perm_only whether to give only the minimum permission
     * or a set of all valid permissions available for being deputy
     * @return mixed The minimum permission needed for being deputy or a set of
     * all permissions between minimum and "admin"
     */
    public static function getValidPerms($min_perm_only = false)
    {
        return $min_perm_only ? 'tutor' : ['tutor', 'dozent'];
    }

    /**
     * Fetches all deputies of the given course or person.
     *
     * @param string|null $range_id ID of a course or person
     * @return SimpleCollection An array containing all deputies.
     */
    public static function findDeputies(string $range_id = null)
    {
        if(is_null($range_id)) {
            $range_id = $GLOBALS['user']->id;
        }
        return SimpleCollection::createFromArray(
            self::findBySQL('range_id = ?', [$range_id])
        )->orderBy('boss_nachname ASC, boss_vorname ASC');
    }

    /**
     * Fetches all persons of which the given person is default deputy.
     *
     * @param string|null $user_id the user to check
     * @return SimpleCollection An array of the given person's bosses.
     */
    public static function findDeputyBosses(string $user_id = null)
    {
        if(is_null($user_id)) {
            $user_id = $GLOBALS['user']->id;
        }

        return SimpleCollection::createFromArray(
            self::findBySQL('JOIN auth_user_md5 ON (deputies.range_id = auth_user_md5.user_id)
                WHERE deputies.user_id = ?',
                [$user_id]
            )
        )->orderBy('boss_nachname ASC, boss_vorname ASC');
    }

    /**
     * Fetch all courses of which the given person is default deputy.
     * @param string|null $user_id
     * @return SimpleCollection
     */
    public static function findDeputyCourses(string $user_id = null)
    {
        if(is_null($user_id)) {
            $user_id = $GLOBALS['user']->id;
        }
        return SimpleCollection::createFromArray(
            self::findBySQL('JOIN seminare ON range_id = seminar_id WHERE user_id = ?', [$user_id])
        );
    }

    /**
     * Database query for retrieving all courses where the current user is deputy
     * in.
     *
     * @param string $type are we in the "My courses" list (='my_courses') or
     * in grouping or notification view ('gruppe', 'notification') or outside
     * Stud.IP in the notification cronjob (='notification_cli')?
     * @param string $sem_number_sql SQL for specifying the semester for a course
     * @param string $sem_number_end_sql SQL for specifying the last semester
     * a course is in
     * @param string $add_fields optionally necessary fields from database
     * @param string $add_query additional joins
     * @return string The SQL query for getting all courses where the current
     * user is deputy in
     */
    public static function getMySeminarsQuery($type, $sem_number_sql, $sem_number_end_sql, $add_fields, $add_query, $studygroups = false) {
        global $user;
        switch ($type) {
            // My courses list
            case 'meine_sem':
                $threshold = object_get_visit_threshold();
                $fields = [
                    "seminare.VeranstaltungsNummer AS sem_nr",
                    "CONCAT(seminare.Name, ' ["._("Vertretung")."]') AS Name",
                    "seminare.Seminar_id",
                    "seminare.status as sem_status",
                    "'dozent'",
                    "deputies.gruppe",
                    "seminare.chdate",
                    "seminare.visible",
                    "admission_binding",
                    "modules",
                    "IFNULL(visitdate, $threshold) as visitdate",
                    "admission_prelim",
                    "$sem_number_sql as sem_number",
                    "$sem_number_end_sql as sem_number_end"
                ];
                $joins = [
                    "JOIN seminare ON (deputies.range_id=seminare.Seminar_id)",
                    "LEFT JOIN object_user_visits ouv ON (ouv.object_id=deputies.range_id AND ouv.user_id='$user->id' AND ouv.type='sem')"
                ];
                $where = " WHERE deputies.user_id = '$user->id'";
                if (Config::get()->MY_COURSES_ENABLE_STUDYGROUPS && !$studygroups) {
                    $studygroup_types = DBManager::get()->quote(studygroup_sem_types());
                    $where .= " AND seminare.status NOT IN ({$studygroup_types})";
                }
                if ($studygroups) {
                    $studygroup_types = DBManager::get()->quote(studygroup_sem_types());
                    $where .= " AND seminare.status IN ({$studygroup_types})";
                }
                break;
            // Grouping and notification settings for my courses
            case 'gruppe':
            case 'notification':
                $fields = [
                    "seminare.VeranstaltungsNummer AS sem_nr",
                    "CONCAT(seminare.Name, ' ["._("Vertretung")."]') AS Name",
                    "seminare.Seminar_id",
                    "seminare.status as sem_status",
                    "deputies.gruppe",
                    "seminare.visible",
                    "$sem_number_sql as sem_number",
                    "$sem_number_end_sql as sem_number_end"
                ];
                $joins = [
                    "JOIN seminare ON (deputies.range_id=seminare.Seminar_id)"
                ];
                $where = " WHERE deputies.user_id = '$user->id'";
                break;
            // Notification mail sending from client script
            case 'notification_cli':
                $fields = [
                    "aum.user_id",
                    "aum.username",
                    $GLOBALS['_fullname_sql']['full']." AS fullname",
                    "aum.Email"
                ];
                $joins = [
                    "INNER JOIN auth_user_md5 aum ON (deputies.user_id=aum.user_id)",
                    "LEFT JOIN user_info ui ON (ui.user_id=deputies.user_id)"
                ];
                $where = " WHERE deputies.notification != 0";
                break;
        }
        $query = "SELECT ".implode(", ", $fields)." ".$add_fields.
            " FROM deputies ".
            implode(" ", $joins).
            $add_query.
            $where;
        return $query;
    }
}
