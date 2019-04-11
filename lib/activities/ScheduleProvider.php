<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class ScheduleProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $activity->content = htmlReady($activity->content);

        $url = \URLHelper::getUrl("dispatch.php/course/dates?cid={$activity->context_id}");
        $route = \URLHelper::getURL('api.php/course/' . $activity->context_id . '/events', NULL, true);

        $activity->object_url = [
            $url => _('Zum Ablaufplan der Veranstaltung')
        ];

        $activity->object_route = $route;

        return true;
    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param Array  $sem Seminar-class for the notification
     */
    public static function postActivity($event, $sem)
    {
        $range_id = $sem->getId();

        $type = get_object_type($range_id);
        if ($type == 'sem') {
            $course = \Course::find($range_id);
        }

        $user_id = $GLOBALS['user']->id;
        $mkdate = time();

        if ($event == 'CourseDidChangeSchedule') {
            $verb = 'edited';
            $summary = _('Der Ablaufplan wurde in der Veranstaltung "%s" von %s aktualisiert.');
            $summary = sprintf($summary, $course->name, get_fullname($user_id));
        }

        $activity = Activity::create(
            [
                'provider'     => __CLASS__,
                'context'      => ($type == 'sem') ? 'course' : 'institute',
                'context_id'   => $range_id,
                'content'      => $summary,
                'actor_type'   => 'user',     // who initiated the activity?
                'actor_id'     => $user_id,   // id of initiator
                'verb'         => $verb,      // the activity type
                'object_id'    => $range_id,  // the id of the referenced object
                'object_type'  => 'schedule', // type of activity object
                'mkdate'       =>  $mkdate
            ]
        );

    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('einen Eintrag im Ablaufplan');
    }

}
