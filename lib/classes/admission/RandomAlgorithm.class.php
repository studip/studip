<?php

/**
 * RandomAlgorithm.class.php - Standard seat distribution algorithm
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class RandomAlgorithm extends AdmissionAlgorithm
{

    /**
     * Runs the algorithm, thus distributing course seats.
     *
     * @param CourseSet $courseSet The course set containing the courses
     * that seats shall be distributed for.
     * @see CourseSet
     */
    public function run($courseSet)
    {
        if ($courseSet->hasAdmissionRule('LimitedAdmission')) {
            return $this->distributeByPriorities($courseSet);
        } else {
            return $this->distributeByCourses($courseSet);
        }
    }

    /**
     * Distribute seats for several courses in a course set.
     * No priorities are given.
     *
     * @param CourseSet $courseSet The course set containing the courses
     * that seats shall be distributed for.
     * @see CourseSet
     */
    private function distributeByCourses($courseSet)
    {
        Log::DEBUG('start seat distribution for course set: ' . $courseSet->getId());
        $groups_quota = [];
        $conditional_rule = array_pop(
            array_filter($courseSet->getAdmissionRules(), function ($r) {
                return $r instanceof ConditionalAdmission
                    && count($r->getConditionGroups());
            })
        );
        $conditiongroups = $conditional_rule ? $conditional_rule->getConditionGroups() : [];
        if (count($conditiongroups)) {
            foreach (array_keys($conditiongroups) as $group_id) {
                $groups_quota[$group_id] = $conditional_rule->getQuota($group_id);
            }
        } else {
            $groups_quota[] = 100;
        }
        foreach ($courseSet->getCourses() as $course_id) {
            $waiting_users = [];
            $course = Course::find($course_id);
            $free_seats_course = $course->getFreeSeats();
            foreach ($groups_quota as $group_id => $quota) {
                $claiming_users = AdmissionPriority::getPrioritiesByCourse($courseSet->getId(), $course->id);
                if (isset($conditiongroups[$group_id])) {
                    Log::DEBUG(sprintf('found conditiongroup %s with quota %s', $group_id, $quota));
                    foreach(array_keys($claiming_users) as $user_id) {
                        $condition_ok = true;
                        foreach ($conditiongroups[$group_id] as $condition) {
                            if ($condition->isFulfilled($user_id)) {
                                $condition_ok = true;
                                break;
                            }
                            $condition_ok = false;
                        }
                        if (!$condition_ok) {
                            unset($claiming_users[$user_id]);
                        }
                    }
                }
                $factored_users = $courseSet->getUserFactorList();
                //apply bonus/malus to users, exclude participants
                foreach(array_keys($claiming_users) as $user_id) {
                    if (!$course->getParticipantStatus($user_id)) {
                        $claiming_users[$user_id] = 1;
                        if (isset($factored_users[$user_id])) {
                            $claiming_users[$user_id] =
                                $factored_users[$user_id] == PHP_INT_MAX ?
                                    PHP_INT_MAX :
                                    $claiming_users[$user_id] * $factored_users[$user_id];
                        }
                        Log::DEBUG(sprintf('user %s gets factor %s', $user_id, $claiming_users[$user_id]));
                    } else {
                        unset($claiming_users[$user_id]);
                        Log::DEBUG(sprintf('user %s is already %s, ignoring', $user_id, $course->getParticipantStatus($user_id)));
                    }
                }
                $free_seats = round($free_seats_course * $quota / 100, 0, PHP_ROUND_HALF_DOWN);
                Log::DEBUG(sprintf('distribute %s seats on %s claiming in course %s %s', $free_seats, count($claiming_users), $course->id, ($group_id ? 'conditiongroup ' . $group_id . ' quota ' . $quota : '')));
                $claiming_users = $this->rollTheDice($claiming_users);
                Log::DEBUG('the die is cast: ' . print_r($claiming_users,1));
                $chosen_ones = array_slice(array_keys($claiming_users),0 , $free_seats);
                Log::DEBUG('chosen ones: ' . print_r($chosen_ones,1));
                $this->addUsersToCourse($chosen_ones, $course);
                foreach ($chosen_ones as $one) unset($waiting_users[$one]);
                if ($free_seats < count($claiming_users)) {
                    $remaining_ones = array_slice(array_keys($claiming_users), $free_seats);
                    foreach ($remaining_ones as $one) {
                        $waiting_users[$one] = $claiming_users[$one];
                    }
                }
            }
            if (count($waiting_users)) {
                $waiting_users = $this->rollTheDice($waiting_users);
                if (!$course->admission_disable_waitlist) {
                    $free_seats_waitlist = $course->admission_waitlist_max ?: count($waiting_users);
                    $waiting_list_ones = array_slice(array_keys($waiting_users), 0, $free_seats_waitlist);
                    Log::DEBUG('waiting list ones: ' . print_r($waiting_list_ones, 1));
                    $this->addUsersToWaitlist($waiting_list_ones, $course);
                } else {
                    $free_seats_waitlist = 0;
                }
                if ($free_seats_waitlist < count($waiting_users)) {
                    $remaining_ones = array_slice(array_keys($waiting_users),$free_seats_waitlist);
                    Log::DEBUG('remaining ones: ' . print_r($remaining_ones, 1));
                    $this->notifyRemainingUsers($remaining_ones, $course);
                }
            }
        }
    }

    /**
     * Distribute seats for several courses in a course set using the given
     * user priorities.
     *
     * @param CourseSet $courseSet The course set containing the courses
     * that seats shall be distributed for.
     * @see CourseSet
     */
    private function distributeByPriorities($courseSet)
    {
        Log::DEBUG('start seat distribution for course set: ' . $courseSet->getId());
        $limited_admission = $courseSet->getAdmissionRule('LimitedAdmission');
        //all users with their priorities
        $claiming_users = AdmissionPriority::getPriorities($courseSet->getId());

        //all users which have bonus/malus
        $factored_users = $courseSet->getUserFactorList();

        //all users with their max number of courses
        $max_seats_users = array_combine(array_keys($claiming_users),
                                         array_map(function($u) use ($limited_admission) {return $limited_admission->getMaxNumberForUser($u);},
                                                   array_keys($claiming_users)
                                                   )
                                         );
        //unlucky users get a bonus for the next round
        $bonus_users = [];

        //users / courses für later waitlist distribution
        $waiting_users = [];

        //number of already distributed seats for users
        $distributed_users = [];

        $prio_mapper = function ($users, $course_id) use ($claiming_users) {
            $mapper = function ($u) use ($course_id) {
                return isset($u[$course_id]) ? $u[$course_id] : null;
            };
            return array_filter(array_map($mapper, array_intersect_key($claiming_users, array_flip($users))));
        };

        $groups_quota = [];
        $conditional_rule = array_pop(
            array_filter($courseSet->getAdmissionRules(), function ($r) {
                return $r instanceof ConditionalAdmission
                    && count($r->getConditionGroups());
            })
        );
        $conditiongroups = $conditional_rule ? $conditional_rule->getConditionGroups() : [];
        if (count($conditiongroups)) {
            foreach (array_keys($conditiongroups) as $group_id) {
                $groups_quota[$group_id] = $conditional_rule->getQuota($group_id);
            }
        } else {
            $groups_quota[] = 100;
        }

        //sort courses by highest count of prio 1 applicants
        $stats = AdmissionPriority::getPrioritiesStats($courseSet->getId());
        $courses = array_map(function ($a) {return $a['h'];},$stats);
        arsort($courses, SORT_NUMERIC);
        $max_prio = AdmissionPriority::getPrioritiesMax($courseSet->getId());
        //count already manually distributed places
        $distributed_users = $this->countParticipatingUsers(array_keys($courses), array_keys($claiming_users));
        Log::DEBUG('already distributed users: ' . print_r($distributed_users,1));
        //walk through all prios with all courses
        foreach(range(1, $max_prio) as $current_prio) {
            foreach (array_keys($courses) as $course_id) {
                $course = Course::find($course_id);
                $free_seats_course = $course->getFreeSeats();
                foreach ($groups_quota as $group_id => $quota) {
                    $current_claiming = [];
                    //find users with current prio for this course, if they still need a place
                    foreach ($claiming_users as $user_id => $prio_courses) {
                        $condition_ok = true;
                        if (isset($conditiongroups[$group_id])) {
                            Log::DEBUG(sprintf('found conditiongroup %s with quota %s', $group_id, $quota));
                            foreach ($conditiongroups[$group_id] as $condition) {
                                if ($condition->isFulfilled($user_id)) {
                                    $condition_ok = true;
                                    break;
                                }
                                $condition_ok = false;
                            }
                        }

                        if ($condition_ok && $prio_courses[$course_id] == $current_prio
                            && $distributed_users[$user_id] < $max_seats_users[$user_id]) {
                            //exclude participants
                            if (!$course->getParticipantStatus($user_id)) {
                                $current_claiming[$user_id] = 1;
                                if (isset($factored_users[$user_id])) {
                                    $current_claiming[$user_id] =
                                        $factored_users[$user_id] == PHP_INT_MAX ?
                                            PHP_INT_MAX :
                                            $current_claiming[$user_id] * $factored_users[$user_id];
                                }
                            } else {
                                Log::DEBUG(sprintf('user %s is already %s in course %s, ignoring', $user_id, $course->getParticipantStatus($user_id), $course->id));
                            }
                        }
                    }
                    //give maximum bonus to users which were unlucky before
                    foreach (array_keys($current_claiming) as $user_id) {
                        if ($bonus_users[$user_id] > 0) {
                            $current_claiming[$user_id] *= $bonus_users[$user_id] * count($current_claiming) + 1;
                        }
                    }
                    $free_seats = round($free_seats_course * $quota / 100, 0, PHP_ROUND_HALF_DOWN);
                    Log::DEBUG(sprintf('distribute %s seats on %s claiming with prio %s in course %s %s', $free_seats, count($current_claiming),$current_prio, $course->id, ($group_id ? 'conditiongroup ' . $group_id . ' quota ' . $quota : '')));
                    Log::DEBUG('users to distribute: ' . print_r($current_claiming,1));
                    $current_claiming = $this->rollTheDice($current_claiming);
                    Log::DEBUG('the die is cast: ' . print_r($current_claiming,1));
                    $chosen_ones = array_slice(array_keys($current_claiming),0 , $free_seats);
                    Log::DEBUG('chosen ones: ' . print_r($chosen_ones,1));
                    $this->addUsersToCourse($chosen_ones, $course, $prio_mapper($chosen_ones, $course->id));
                    foreach ($chosen_ones as $one) {
                        $distributed_users[$one]++;
                        $bonus_users[$one]--;
                    }
                    if ($free_seats < count($current_claiming)) {
                        $remaining_ones = array_slice(array_keys($current_claiming), $free_seats);
                        foreach ($remaining_ones as $one) {
                            $bonus_users[$one]++;
                            $waiting_users[$current_prio][$course_id][] = $one;
                        }
                    }
                }
            }
        }
        //distribute to waitlists if applicable
        Log::DEBUG('waiting list: ' . print_r($waiting_users, 1));
        foreach ($waiting_users as $current_prio => $current_prio_waiting_courses) {
            foreach ($current_prio_waiting_courses as $course_id => $users) {
                $users = array_filter(array_unique($users), function($user_id) use ($distributed_users, $max_seats_users) {
                    return $distributed_users[$user_id] < $max_seats_users[$user_id];});
                    $course = Course::find($course_id);
                    Log::DEBUG(sprintf('distribute waitlist of %s with prio %s in course %s', count($users), $current_prio, $course->id));
                    if (!$course->admission_disable_waitlist) {
                        if ($course->admission_waitlist_max) {
                            $free_seats_waitlist = $course->admission_waitlist_max - $course->getNumWaiting();
                            $free_seats_waitlist = $free_seats_waitlist < 0 ? 0 : $free_seats_waitlist;
                        } else {
                            $free_seats_waitlist = count($users);
                        }
                        $waiting_list_ones = array_slice($users, 0, $free_seats_waitlist);
                        Log::DEBUG('waiting list ones: ' . print_r($waiting_list_ones, 1));
                        $this->addUsersToWaitlist($waiting_list_ones, $course, $prio_mapper($waiting_list_ones, $course->id));
                        foreach ($waiting_list_ones as $one) {
                            $distributed_users[$one]++;
                        }
                    } else {
                        $free_seats_waitlist = 0;
                    }
                    if ($free_seats_waitlist < count($users)) {
                        $remaining_ones = array_slice($users, $free_seats_waitlist);
                        Log::DEBUG('remaining ones: ' . print_r($remaining_ones, 1));
                        $this->notifyRemainingUsers($remaining_ones, $course, $prio_mapper($remaining_ones, $course->id));
                    }
            }
        }
    }

    /**
     * Notify users about the fact that they couldn't get a seat and the
     * waiting list is disabled in a course.
     *
     * @param Array  $user_list Users to be notified
     * @param Course $course    The course without waiting list
     * @param int    $prio      User's priority for the given course.
     */
    public function notifyRemainingUsers($user_list, $course, $prio = null)
    {
        foreach ($user_list as $chosen_one) {
            setTempLanguage($chosen_one);
            $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $course->name);
            $message_body = sprintf(_('Sie haben leider bei der Platzverteilung der Veranstaltung **%s** __keinen__ Platz erhalten. Für diese Veranstaltung wurde keine Warteliste vorgesehen.'),
                                       $course->name);
            if ($prio) {
                $message_body .= "\n" . sprintf(_("Sie hatten für diese Veranstaltung die Priorität %s gewählt."), $prio[$chosen_one]);
            }
            messaging::sendSystemMessage($chosen_one, $message_title, $message_body);
            restoreLanguage();
        }
    }

    /**
     * Notify users that they couldn't get a seat but are now on the waiting
     * list for a given course.
     *
     * @param Array  $user_list Users to be notified
     * @param Course $course    The course without waiting list
     * @param int    $prio      User's priority for the given course.
     */
    private function addUsersToWaitlist($user_list, $course, $prio = null)
    {
        $maxpos = $course->admission_applicants->findBy('status', 'awaiting')->orderBy('position desc')->val('position');
        foreach ($user_list as $chosen_one) {
            if ($course->getParticipantStatus($chosen_one)) {
                continue;
            }
            $maxpos++;
            $new_admission_member = new AdmissionApplication();
            $new_admission_member->user_id = $chosen_one;
            $new_admission_member->position = $maxpos;
            $new_admission_member->status = 'awaiting';
            try {
                $course->admission_applicants[] = $new_admission_member;
            } catch (InvalidArgumentException $e) {
                Log::DEBUG($e->getMessage());
                continue;
            }
            if ($new_admission_member->store()) {
                setTempLanguage($chosen_one);
                $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $course->name);
                $message_body = sprintf(_('Sie haben leider bei der Platzverteilung der Veranstaltung **%s** __keinen__ Platz erhalten. Sie wurden jedoch auf Position %s auf die Warteliste gesetzt. Das System wird Sie automatisch eintragen und benachrichtigen, sobald ein Platz für Sie frei wird.'),
                                           $course->name,
                                           $maxpos);
                if ($prio) {
                    $message_body .= "\n" . sprintf(_("Sie hatten für diese Veranstaltung die Priorität %s gewählt."), $prio[$chosen_one]);
                }
                messaging::sendSystemMessage($chosen_one, $message_title, $message_body);
                restoreLanguage();
                StudipLog::log('SEM_USER_ADD', $course->id, $chosen_one, 'awaiting', 'Auf Warteliste gelost, Position: ' . $maxpos);
            }
        }
    }

    /**
     * Add the lucky ones who got a seat to the given course.
     *
     * @param Array  $user_list users to add as members
     * @param Course $course    course to add users to
     * @param int    $prio      user's priority for the given course
     */
    private function addUsersToCourse($user_list, $course, $prio = null)
    {
        $seminar = new Seminar($course);
        foreach ($user_list as $chosen_one) {
            setTempLanguage($chosen_one);
            $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $seminar->getName());
            if ($seminar->admission_prelim) {
                if ($seminar->addPreliminaryMember($chosen_one)) {
                    $message_body = sprintf (_('Sie haben bei der Platzvergabe der Veranstaltung **%s** einen vorläufigen Platz erhalten. Die endgültige Zulassung zu der Veranstaltung ist noch von weiteren Bedingungen abhängig, die Sie bitte der Veranstaltungsbeschreibung entnehmen.'),
                            $seminar->getName());
                }
            } else {
                if ($seminar->addMember($chosen_one, 'autor')) {
                    $message_body = sprintf (_("Sie haben bei der Platzvergabe der Veranstaltung **%s** einen Platz erhalten. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen. Damit sind Sie auch für die Präsenzveranstaltung zugelassen."),
                            $seminar->getName());
                }
            }
            if ($prio) {
                $message_body .= "\n" . sprintf(_("Sie hatten für diese Veranstaltung die Priorität %s gewählt."), $prio[$chosen_one]);
            }
            messaging::sendSystemMessage($chosen_one, $message_title, $message_body);
            restoreLanguage();
        }
    }

    /**
     * Caedite eos. Novit enim Dominus qui sunt eius.
     *
     * @param array $user_list
     */
    private function rollTheDice($user_list)
    {
        $max = count($user_list);
        foreach($user_list as $user_id => $factor) {
            $user_list[$user_id] = $factor * mt_rand(1, $max);
        }
        arsort($user_list, SORT_NUMERIC);
        return $user_list;
    }

    /**
     * How many users have gotten a seat in distribution?
     *
     * @return Number of users who where lucky enough to be course members now.
     */
    public function countParticipatingUsers($course_ids, $user_ids)
    {
        $distributed_users = [];
        $sum = function($r) use (&$distributed_users) {
            $distributed_users[$r['user_id']] += $r['c'];
        };
        $db = DbManager::get();
        $db->fetchAll("SELECT user_id, COUNT(*) as c FROM seminar_user
            WHERE seminar_id IN(?) AND user_id IN(?) AND status IN (?) GROUP BY user_id",
            [$course_ids, $user_ids, ['user', 'autor']], $sum);
        $db->fetchAll("SELECT user_id, COUNT(*) as c FROM admission_seminar_user
            WHERE seminar_id IN(?) AND user_id IN(?) GROUP BY user_id", [$course_ids, $user_ids], $sum);
        return $distributed_users;
    }

}
