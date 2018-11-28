#!/usr/bin/env php
<?php
/**
 * This script removes all members from a course that should not have been
 * members in the first place.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @see    https://develop.studip.de/trac/ticket/7783
 */

require_once __DIR__ . '/studip_cli_env.inc.php';
require_once __DIR__ . '/../config/config_local.inc.php';
require_once __DIR__ . '/../app/models/members.php';

function output($what) {
    if (StudipVersion::olderThan(4)) {
        $what = studip_utf8encode($what);
    }

    fwrite(STDOUT, $what);
}

$opts    = getopt('d', ['dry-run']);
$dry_run = isset($opts['d']) || isset($opts['dry-run']);

// Reduce arguments by options (this is far from perfect)
$args = $_SERVER['argv'];
$arg_stop = array_search('--', $args);
if ($arg_stop !== false) {
    $args = array_slice($args, $arg_stop + 1);
} elseif (count($opts)) {
    $args = array_slice($args, 1 + count($opts));
} else {
    $args = array_slice($args, 1);
}

if (count($args) < 1) {
    output("Fix for Biest 7783 - Use {$argv[0]} [--dry-run/-d] <semester_id,current,next>\n");
    exit(0);
}

$semester_ids = explode(',', implode(',', array_map('trim', $args)));
foreach ($semester_ids as $index => $semester_id) {
    if ($semester_id === 'current') {
        $semester_id = Semester::findCurrent()->id;
    } elseif ($semester_id === 'next') {
        $semester_id = Semester::findNext()->id;
    } elseif (Semester::find($semester_id) === null) {
        output("Semester id {$semester_id} is invalid\n");
        exit(0);
    }

    $semester_ids[$index] = $semester_id;
}

$query = "SELECT DISTINCT
              cs.`set_id`, s.`seminar_id`
          FROM `semester_data` AS sd
          JOIN `seminare` AS s
            ON (s.`start_time` <= sd.`beginn`
                AND (
                    sd.`beginn` <= s.`start_time` + s.`duration_time`
                    OR s.`duration_time` = -1
                )
            )
          JOIN `seminar_courseset` AS scs USING (`seminar_id`)
          JOIN `coursesets` AS cs USING (`set_id`)
          JOIN `auth_user_md5` USING (`user_id`)
          JOIN `courseset_rule` AS csr USING (`set_id`)
          JOIN `admission_condition` AS ac USING (`rule_id`)
          JOIN `userfilter` AS uf USING (`filter_id`)
          JOIN `userfilter_fields` AS uff USING (`filter_id`)
          WHERE `semester_id` IN (:semester_ids)
            AND `algorithm_run` = 0
            AND uff.`type` = 'SemesterOfStudyCondition'
            AND uff.`value` > 1
          ORDER BY cs.`name` ASC, s.`name`";
$statement = DBManager::get()->prepare($query);
$statement->bindValue(':semester_ids', $semester_ids);
$statement->execute();
$sets = $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

foreach ($sets as $set_id => $course_ids) {
    $courseset = new CourseSet($set_id);
    $remove = [];
    foreach ($course_ids as $course_id) {
        $course = Course::find($course_id);
        $members = new MembersModel($course_id, $course->getFullname());
        $applicants = $members->getAdmissionMembers();
        foreach (['awaiting', 'claiming'] as $status) {
            foreach ($applicants[$status] as $applicant) {
                $errors = $courseset->checkAdmission($applicant->user_id, $course_id);
                if (count($errors) === 0) {
                    continue;
                }

                if (!isset($remove[$course_id])) {
                    $remove[$course_id] = [
                        'course'  => $course,
                        'members' => $members,
                        'status'  => [],
                    ];
                }
                if (!isset($remove[$course_id]['status'][$status])) {
                    $remove[$course_id]['status'][$status] = [];
                }

                $remove[$course_id]['status'][$status][] = User::find($applicant['user_id']);
            }
        }
    }

    if ($remove) {
        $owner = User::find($courseset->getUserId())->getFullname();
        output("= Anmeldeset {$courseset->getName()} ({$owner}):\n");

        foreach ($remove as $row) {
            output("  - Veranstaltung {$row['course']->getFullname()}:\n");
            foreach ($row['status'] as $status => $users) {
                $user_ids = array_map(function (User $user) {
                    return $user->id;
                }, $users);

                if ($dry_run) {
                    foreach ($users as $user) {
                        output("    - Nutzer {$user->getFullname()}\n");
                    }
                } else {
                    $result = $row['members']->cancelAdmissionSubscription($user_ids, $status);
                    foreach ($result as $row) {
                        output("    - Nutzer {$row}\n");
                    }
                }

            }
        }
    }
}
