<?php

/**
 * CoursesetModel - Database stuff for course set management
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class CoursesetModel
{
    /**
     * Fetches courses at the given institutes.
     * @param Array  $instituteIds IDs of institutes to check
     * @param String $coursesetId Get also courses assigned to the given courseset
     * @param Array  $selectedCourses Courses that have already been selected manually
     * @param String $semester_id Get only courses belonging to the given semester
     * @param mixed  $filter Fetch only courses fulfilling a search string or of a certain lecturer?
     *
     * @return Array Found courses.
     */
    public function getInstCourses($instituteIds, $coursesetId = '', $selectedCourses = [], $semester_id = null, $filter = false)
    {
        // Get semester dates for course sorting.
        $currentSemester = $semester_id ? Semester::find($semester_id) : Semester::findCurrent();

        $db = DBManager::get();
        $courses = [];

        // Exclude studygroup and grouping parent sem types from search.
        $excludeTypes = array_merge(
            studygroup_sem_types() ?: [''],
            SemType::getGroupingSemTypes() ?: ['']
        );

        if ($filter === true) {
            $query = "SELECT su.`Seminar_id`
                      FROM `seminar_user` su
                      INNER JOIN `seminare` s USING (`Seminar_id`)
                      WHERE s.status NOT IN(?)
                        AND s.`start_time` <= ?
                        AND (? <= (s.`start_time` + s.`duration_time`) OR s.`duration_time` = -1)
                        AND su.`user_id` = ?";
            $parameters = [$excludeTypes, $currentSemester->beginn, $currentSemester->beginn, $GLOBALS['user']->id];

            if (Config::get()->DEPUTIES_ENABLE) {
                $query .= " UNION ";
                $query .= "SELECT s.`Seminar_id`
                           FROM `seminare` s
                           INNER JOIN `deputies` d ON (s.`Seminar_id`=d.`range_id`)
                           WHERE s.`start_time` <= ?
                             AND (? <= (s.`start_time` + s.`duration_time`) OR s.`duration_time` = -1)
                             AND d.`user_id` = ?";
                $parameters = array_merge(
                    $parameters,
                    [$currentSemester->beginn, $currentSemester->beginn, $GLOBALS['user']->id]
                );
            }
            $courses = $db->fetchFirst($query, $parameters);
        } elseif (mb_strlen($filter) > 1) {
            $query = "SELECT DISTINCT s.seminar_id
                      FROM seminare s
                      INNER JOIN seminar_user su
                         ON s.seminar_id = su.seminar_id AND su.status = 'dozent'
                      INNER JOIN auth_user_md5 aum USING (user_id)
                      WHERE s.status NOT IN (:exclude_types)
                        AND s.start_time <= :sembegin
                        AND (:sembegin <= (s.start_time + s.duration_time) OR s.duration_time = -1)
                        AND s.Institut_id IN (:institutes)
                        AND (
                            s.name LIKE :filter
                            OR s.Veranstaltungsnummer LIKE :filter
                            OR Nachname LIKE :filter
                        )";
            $courses = $db->fetchFirst($query, [
                'exclude_types' => $excludeTypes,
                'sembegin'      => $currentSemester->beginn,
                'institutes'    => $instituteIds,
                'filter'        => '%' . $filter .'%',
            ]);
        }
        //filter courses from other sets out
        if (count($courses)) {
            $query = "SELECT DISTINCT seminar_id
                      FROM seminar_courseset
                      LEFT JOIN courseset_rule USING (set_id)
                      WHERE type NOT IN ('LockedAdmission','PasswordAdmission')
                        AND seminar_id IN (?)";
            $found = DBManager::get()->fetchFirst($query, [$courses]);
            $courses = array_diff($courses, $found);
        }

        if ($coursesetId) {
            $query = "SELECT seminar_id FROM seminar_courseset sc WHERE set_id = ?";
            $courses = array_merge(
                $courses,
                $db->fetchFirst($query, [$coursesetId])
            );
        }

        if ($selectedCourses) {
            $courses = array_merge($courses, $selectedCourses);
        }
        $data = [];
        $callable = function ($course) use (&$data, $coursesetId) {
            $data[$course->id] = [
                'seminar_id'           => $course->Seminar_id,
                'VeranstaltungsNummer' => $course->VeranstaltungsNummer,
                'Name'                 => $course->Name . ($course->duration_time == -1 ? ' ' . _('(unbegrenzt)') : ''),
                'admission_turnout'    => $course->admission_turnout,
                'visible'              => $course->visible,
            ];

            $query = "SELECT type
                      FROM seminar_courseset
                      INNER JOIN courseset_rule USING (set_id)
                      WHERE type IN ('LockedAdmission','PasswordAdmission')
                        AND seminar_id = ?";
            if ($coursesetId) {
                $query .= "AND set_id <> ?";
            }

            $data[$course->id]['admission_type'] = DBManager::get()->fetchColumn(
                $query,
                $coursesetId ? [$course->id, $coursesetId] : [$course->id]
            );

        };
        Course::findEachMany($callable, array_unique($courses),"ORDER BY start_time DESC, VeranstaltungsNummer ASC, Name ASC");

        return $data;
    }

    /**
     * Fetch institutes for course sets.
     *
     * @param Array $filter filter settings, e.g. a special subset of allowed
     * admission rules
     *
     * @return Array Found institutes.
     */
    public static function getInstitutes($filter = [])
    {
        global $perm, $user;

        $parameters = [1];
        $query = "SELECT COUNT(DISTINCT ci.set_id)
                  FROM courseset_institute ci
                  LEFT JOIN coursesets c ON c.set_id = ci.set_id
                  LEFT JOIN courseset_rule cr ON c.set_id = cr.set_id
                  LEFT JOIN seminar_courseset sc ON c.set_id = sc.set_id
                  LEFT JOIN seminare s ON s.seminar_id = sc.seminar_id
                  WHERE ci.institute_id = ?";
        if ($filter['course_set_name']) {
            $query .= " AND c.name LIKE ?";
            $parameters[] = $filter['course_set_name'] . '%';
        }
        if (is_array($filter['rule_types']) && count($filter['rule_types'])) {
            $query .= " AND cr.type IN (?)";
            $parameters[] = $filter['rule_types'];
        }
        if ($filter['semester_id']) {
            $query .= " AND s.start_time = ?";
            $parameters[] = Semester::find($filter['semester_id'])->beginn;
        }
        $cs_count_statement = DBManager::get()->prepare($query);
        $query = str_replace('ci.institute_id', '1', $query);
        $cs_count_all_statement = DBManager::get()->prepare($query);

        if ($perm->have_perm('root')) {
            $cs_count_all_statement->execute($parameters);
            $num_sets = $cs_count_all_statement->fetchColumn();

            $my_inst['all'] = [
                'name'     => _('alle'),
                'is_fak'   => true,
                'num_sets' => $num_sets
            ];
            $top_insts = Institute::findBySQL('Institut_id = fakultaets_id ORDER BY Name');
        } else {
            $top_insts = Institute::findMany(User::find($user->id)->institute_memberships->findBy('inst_perms', words('admin dozent'))->pluck('institut_id'),'ORDER BY institut_id=fakultaets_id,name');
        }
        foreach ($top_insts as $inst) {
            $my_inst[$inst->id] = $inst->toArray('name is_fak');
            $parameters[0] = $inst->id;
            $cs_count_statement->execute($parameters);
            $my_inst[$inst->id]['num_sets'] = $cs_count_statement->fetchColumn();
            if ($inst->is_fak && ($perm->have_perm('root') || $inst->members->findBy('user_id', $user->id)->val('inst_perms') == 'admin')) {
                $alle = $inst->sub_institutes;
                if (count($alle)) {
                    $my_inst[$inst->id . '_all'] = [
                        'name'    => sprintf(_('[Alle unter %s]'), $inst->name),
                        'is_fak'  => 'all'
                    ];

                    $num_inst = 0;
                    $num_sets_alle = $my_inst[$inst->id]['num_sets'];

                    foreach ($alle as $institute) {
                       $num_inst += 1;
                       $my_inst[$institute->id] = $institute->toArray('name is_fak');
                       $parameters[0] = $institute->id;
                       $cs_count_statement->execute($parameters);
                       $my_inst[$institute->id]['num_sets'] = $cs_count_statement->fetchColumn();
                       $num_sets_alle += $my_inst[$institute->id]['num_sets'];
                    }
                    $my_inst[$inst->id . '_all']['num_inst'] = $num_inst;
                    $my_inst[$inst->id . '_all']['num_sets']  = $num_sets_alle;
                }
            }
        }
        return $my_inst;
    }
}
