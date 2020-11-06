<?php
/**
 * Calendar.class.php - Holds some additional functions and constants
 * related to the personal calendar.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.2
 */
class Calendar
{
    /**
     * The (positive) end of unix epche
     */
    const CALENDAR_END = 0x7FFFFFFF;

    /**
     * The user is the owner of the calendar.
     */
    const PERMISSION_OWN = 16;

    /**
     * The user has administrative access to the calendar.
     * Means, he is not the owner but have the same rights.
     * Not in use at the moment.
     */
    const PERMISSION_ADMIN = 8;

    /**
     * The user can add new events and edit existing events in the calendar.
     * If the owner of the calendar has created an confidential event, the only
     * information the user get is the start and end time. The event is shown as
     * busy time in the views for him.
     * If the user adds a confidential event, only he and the owner has full
     * access to it. The event is shown as busy time to all other users.
     */
    const PERMISSION_WRITABLE = 4;

    /**
     * The user can read all information of all events, except events marked as
     * confidential. These events are shown as busy times in the views.
     * The user can not add new events nor edit existing events.
     */
    const PERMISSION_READABLE = 2;

    /**
     * The user is not allowed to get any information about the calendar.
     * The user has no access to the calendar but he see public events on the
     * profile of the owner.
     */
    const PERMISSION_FORBIDDEN = 1;

    /**
     * The calendar is related to one user. He is the owner of the calendar.
     */
    const RANGE_USER = 1;

    /**
     * The calendar is related to a group of users
     * ("contact group" or Statusgruppe).
     * Not used at the moment.
     * The implemeted group functionality shows all personal calendars of the
     * members of a contact group. It is not a shared calendar where all members
     * have access to.
     */
    const RANGE_GROUP = 2;

    /**
     * The calendar is a module of a course or studygroup. All members with
     * status author, tutor or dozent have write access (PERMISSION_WRITABLE).
     * Users with local status user has only read access (PERMISSION_READABLE).
     */
    const RANGE_SEM = 3;

    /**
     * The calendar is a module of an institute or faculty. All members with
     * status author, tutor or dozent have write access (PERMISSION_WRITABLE).
     * Users with local status user has only read access (PERMISSION_READABLE).
     */
    const RANGE_INST = 4;

    /**
     * Retrieves all contact groups (statusgruppen) owned by the given user
     * where at least one member has granted access to his calender for the user.
     *
     * @param string $user_id User id of the owner.
     * @return type
     */
    public static function getGroups($user_id)
    {
        $groups = [];
        $calendar_owners = CalendarUser::getOwners($user_id)->pluck('owner_id');
        $sg_groups = SimpleORMapCollection::createFromArray(
                Statusgruppen::findByRange_id($user_id))
                ->orderBy('position')
                ->pluck('statusgruppe_id');
        if (sizeof($calendar_owners)) {
            $sg_users = StatusgruppeUser::findBySQL(
                    'statusgruppe_id IN(?) AND user_id IN(?)',
                    [$sg_groups, $calendar_owners]);
            foreach ($sg_users as $sg_user) {
                $groups[$sg_user->group->id] = $sg_user->group;
            }
        }
        return $groups;
    }

    public static function GetInstituteActivatedCalendar($user_id)
    {
        $stmt = DBManager::get()->prepare("SELECT ui.Institut_id, Name, modules "
                . "FROM user_inst ui LEFT JOIN Institute i USING(Institut_id) "
                . "WHERE user_id = ? AND inst_perms IN ('admin','dozent','tutor','autor') "
                . "ORDER BY Name ASC");
        $modules = new Modules();
        $stmt->execute([$user_id]);
        $active_calendar = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($modules->isBit($row['modules'],
                    $modules->registered_modules['calendar']['id'])) {
                $active_calendar[$row['Institut_id']] = $row['Name'];
            }
        }
        return $active_calendar;
    }

    /**
     *
     * @param type $user_id
     * @return type
     */
    public static function GetCoursesActivatedCalendar($user_id)
    {
        $courses_user = SimpleCollection::createFromArray(
                CourseMember::findByUser($user_id));
        $modules = new Modules();
        $courses = $courses_user->filter(function ($c) use ($modules) {
            if ($modules->isBit($c->course->modules,
                    $modules->registered_modules['calendar']['id'])) {
                return $c;
            }
        });
        return $courses->pluck('course');
    }

    public static function GetLecturers()
    {
        $stmt = DBManager::get()->prepare("SELECT aum.username, "
                . "CONCAT(aum.Nachname,', ',aum.vorname) as fullname, "
                . "aum.user_id FROM auth_user_md5 aum WHERE perms = 'dozent' "
                . "ORDER BY fullname");
        $stmt->execute();
        $lecturers = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['user_id'] != $GLOBALS['user']->id) {
                $lecturers[] = ['name' => $row['fullname'],
                    'username' => $row['username'], 'id' => $row['user_id']];
            }
        }
        return $lecturers;
    }

    /**
     * Returns an array of default user settings for the calendar or a specific
     * value if the index is given.
     *
     * @param string $index Index of setting to get.
     * @return string|array Array of settings or one setting
     */
    public static function getDefaultUserSettings($index = null)
    {
        $default = [
            'view'              => 'week',
            'start'             => '9',
            'end'               => '20',
            'step_day'          => '900',
            'step_week'         => '1800',
            'type_week'         => 'LONG',
            'step_week_group'   => '3600',
            'step_day_group'    => '3600',
            'show_declined'     => '0'
        ];
        return (is_null($index) ? $default : $default[$index]);
    }
}