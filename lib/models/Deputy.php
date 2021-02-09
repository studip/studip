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
            self::findBySQL('user_id = ?', [$user_id])
        )->orderBy('boss_nachname ASC, boss_vorname ASC');
    }
}
