#!/usr/bin/env php
<?php
/**
 * cleanup_admission_rules.php
 *
 * deletes entries in %admissions tables
 * which were orphaned by BIEST #6617
 *
 * @author    André Noack <noack@data-quest.de>
 * @license   GPL2 or any later version
 * @copyright Stud.IP Core Group
 */
require_once 'studip_cli_env.inc.php';
require_once 'lib/classes/admission/CourseSet.class.php';

$sql = "SELECT * FROM
(
SELECT rule_id,'ConditionalAdmission' as class FROM `conditionaladmissions`
UNION
SELECT rule_id,'CourseMemberAdmission' as class FROM `coursememberadmissions`
UNION
SELECT rule_id,'LimitedAdmission' as class FROM limitedadmissions
UNION
SELECT rule_id,'LockedAdmission' as class FROM lockedadmissions
UNION
SELECT rule_id,'ParticipantRestrictedAdmission' as class FROM participantrestrictedadmissions
UNION
SELECT rule_id,'PasswordAdmission' as class FROM passwordadmissions
UNION
SELECT rule_id,'TimedAdmission' as class FROM timedadmissions
) a
LEFT JOIN courseset_rule USING(rule_id) WHERE set_id IS NULL";

$foo = new CourseSet();
$c1 = $c2 = 0;
DBManager::get()
->fetchAll($sql, null, function ($data) use (&$c1,&$c2) {
        $c1++;
        if (class_exists($data['class'])) {
            $rule = new $data['class']($data['rule_id']);
            if ($rule->getId() === $data['rule_id']) {
                echo 'deleting: ' . $rule->getName() . ' with id: ' . $rule->getId() . chr(10);
                $c2++;
                $rule->delete();
            }
        }
}
);
printf("found: %s deleted: %s \n", $c1,$c2);
