<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class LiteratureProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public static function getActivityDetails($activity)
    {
        $activity->content = \htmlReady($activity->content);

        if ($activity->context == "course") {

            $url = \URLHelper::getUrl("dispatch.php/literature/edit_list?cid={$activity->context_id}&view=literatur_sem&open_item={$activity->object_id}#anchor");
            $route = null;

            $activity->object_url = [
                $url => _('Zur Literaturliste in der Veranstaltung')
            ];

            $activity->object_route = $route;

        } elseif ($activity->context == "institute") {
            $url = \URLHelper::getUrl("dispatch.php/literature/edit_list?cid={$activity->context_id}&view=literatur_sem&open_item={$activity->object_id}#anchor");
            $route= null;

            $activity->object_url = [
                $url => _('Zur Literaturliste in der Einrichtung')
            ];

            $activity->object_route = $route;
        }

        return true;
    }


    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param Array  $info information which a relevant for the activity
     */
    public static function postActivity($event, $info)
    {
        $range_id = $info['range_id'];
        $list_id  = $info['list_id'];
        $name     = $info['name'];
        $type     = get_object_type($range_id);
        $user_id  = $GLOBALS['user']->id;
        $mkdate   = time();

        if ($type == 'sem') {
            $course = \Course::find($range_id);
        } else {
            $course = \Institute::find($range_id);
        }

        if ($event == 'LitListDidUpdate') {
            $verb = 'edited';
            if ($type == 'sem') {
                $summary = _('Die Literaturliste "%s" wurde von %s in der Veranstaltung "%s" geändert.');
            } else {
                $summary = _('Die Literaturliste "%s" wurde von %s in der Einrichtung "%s" geändert.');
            }
        } elseif ($event == 'LitListDidCreate') {
            $verb = 'created';
            if ($type == 'sem') {
                $summary = _('Die Literaturliste "%s" wurde von %s in der Veranstaltung "%s" erstellt.');
            } else {
                $summary = _('Die Literaturliste "%s" wurde von %s in der Einrichtung "%s" erstellt.');
            }
        } elseif ($event == 'LitListDidDelete') {
            $verb = 'voided';
            if ($type == 'sem') {
                $summary = _('Die Literaturliste "%s" wurde von %s in der Veranstaltung "%s" entfernt.');
            } else {
                $summary = _('Die Literaturliste "%s" wurde von %s in der Einrichtung "%s" entfernt.');
            }
        } elseif ($event == 'LitListElementDidUpdate') {
            $verb = 'edited';
            if ($type == 'sem') {
                $summary = _('Es wurde der Eintrag "%s" von %s in einer Literaturliste in der Veranstaltung "%s" geändert.');
            } else {
                $summary = _('Es wurde der Eintrag "%s" von %s in einer Literaturliste in der Einrichtung "%s" geändert.');
            }
        } elseif ($event == 'LitListElementDidInsert') {
            $verb = 'created';
            if ($type == 'sem') {
                $summary = _('Es wurde von %s%s ein Eintrag zu einer Literaturliste in der Veranstaltung "%s" hinzugefügt.');
            } else {
                $summary = _('Es wurde von %s%s ein Eintrag zu einer Literaturliste in der Einrichtung "%s" hinzugefügt.');
            }
        } elseif ($event == 'LitListElementDidDelete') {
            $verb = 'voided';
            if ($type == 'sem') {
                $summary = _('Es wurde aus der Literaturliste "%s" von %s in der Veranstaltung "%s" ein Eintrag entfernt.');
            } else {
                $summary = _('Es wurde aus der Literaturliste "%s" von %s in der Einrichtung "%s" ein Eintrag entfernt.');
            }
        }

        $summary = sprintf($summary, $name, get_fullname($user_id), $course->name);

        if (isset($verb)) {
            $activity = Activity::create(
                [
                    'provider'     => __CLASS__,
                    'context'      => ($type == 'sem') ? 'course' : 'institute',
                    'context_id'   => $range_id,
                    'content'      => $summary,
                    'actor_type'   => 'user',           // who initiated the activity?
                    'actor_id'     => $user_id,         // id of initiator
                    'verb'         => $verb,            // the activity type
                    'object_id'    => $list_id,         // the id of the referenced object
                    'object_type'  => 'literaturelist', // type of activity object
                    'mkdate'       =>  $mkdate
                ]
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('einen Literatureintrag');
    }

}
