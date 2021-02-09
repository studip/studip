<?php
# Lifter010: TODO
/**
* Helper functions for deputy handling
*
*
* @author       Thomas Hackl <thomas.hackl@uni-passau.de>
* @access       public
* @modulegroup  library
* @module       deputies_functions.inc
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// deputies_functions.inc.php
// helper functions for deputy handling
// Copyright (c) 2010 Thomas Hackl <thomas.hackl@uni-passau.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+



/**
 * Adds a person as deputy of a course or another person.
 *
 * @param string $user_id person to add as deputy
 * @param string $range_id ID of a course or a person
 * @return int Number of affected rows in the database (hopefully 1).
 */
function addDeputy($user_id, $range_id) {
    if (Deputy::exists([$range_id, $user_id])) {
        return true;
    }

    $d = new Deputy();
    $d->user_id  = $user_id;
    $d->range_id = $range_id;

    // Check if the given range_id is a course and has a parent.
    if ($course = Course::find($range_id)) {
        if ($course->parent_course && !Deputy::exists([$course->parent_course, $user_id])) {
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
function isDeputy($user_id, $range_id, $check_edit_about=false) {
    $d = Deputy::find([$range_id, $user_id]);
    if (!$d) {
        return false;
    }

    return $check_edit_about
         ? $d->edit_about == 1
         : true;
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
function getMyDeputySeminarsQuery($type, $sem_number_sql, $sem_number_end_sql, $add_fields, $add_query, $studygroups = false) {
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
                $where .= " AND seminare.status != 99";
            }
            if($studygroups) {
                $where .= " AND seminare.status = 99";
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