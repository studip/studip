<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class WikiProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        // Check visibility of wiki page
        $page = \WikiPage::findLatestPage($activity->context_id, $activity->object_id);
        if ($page && !$page->isVisibleTo($GLOBALS['user'])) {
            return false;
        }

        $activity->content = \htmlReady($activity->content);

        if ($activity->context === 'course') {
            $url = \URLHelper::getURL('wiki.php', ['cid' => $activity->context_id, 'keyword' => $activity->object_id]);
            $route = \URLHelper::getURL("api.php/course/{$activity->context_id}/wiki/{$activity->object_id}", null, true);

            $activity->object_url = [
                $url => _('Zum Wiki der Veranstaltung'),
            ];

            $activity->object_route = $route;

        } elseif ($activity->context === 'institute') {
            $url = \URLHelper::getURL('wiki.php', ['cid' => $activity->context_id, 'keyword' => $activity->object_id]);
            $route= null;

            $activity->object_url = [
                $url => _('Zum Wiki der Einrichtung')
            ];

            $activity->object_route = $route;
        }

        return true;
    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param \WikiPage  $info information which a relevant for the activity
     */
    public static function postActivity($event, $info)
    {
        $range_id = $info['range_id'];
        $keyword = $info['keyword'];

        $type = get_object_type($range_id);
        if ($type === 'sem') {
            $course = \Course::find($range_id);
        } else {
            $course = \Institute::find($range_id);
        }

        $user_id = $GLOBALS['user']->id;
        $mkdate = time();

        if ($event === 'WikiPageDidCreate') {
            $verb = 'created';
            if ($type === 'sem') {
                $summary = _('Die Wiki-Seite %s wurde von %s in der Veranstaltung "%s" angelegt.');
            } else {
                $summary = _('Die Wiki-Seite %s wurde von %s in der Einrichtung "%s" angelegt.');
            }
        } elseif ($event === 'WikiPageDidUpdate') {
            $verb = 'edited';
            if ($type === 'sem') {
                $summary = _('Die Wiki-Seite %s wurde von %s in der Veranstaltung "%s" aktualisiert.');
            } else {
                $summary = _('Die Wiki-Seite %s wurde von %s in der Einrichtung "%s" aktualisiert.');
            }
        } elseif ($event === 'WikiPageDidDelete') {
            $verb = 'voided';
            if ($type === 'sem') {
                $summary = _('Die Wiki-Seite %s wurde von %s in der Veranstaltung "%s" gelöscht.');
            } else {
                $summary = _('Die Wiki-Seite %s wurde von %s in der Einrichtung "%s" gelöscht.');
            }
        }

        $summary = sprintf($summary, $keyword, get_fullname($user_id), $course->name);

        $activity = Activity::create([
            'provider'     => __CLASS__,
            'context'      => $type === 'sem' ? 'course' : 'institute',
            'context_id'   => $range_id,
            'content'      => $summary,
            'actor_type'   => 'user',   // who initiated the activity?
            'actor_id'     => $user_id, // id of initiator
            'verb'         => $verb,    // the activity type
            'object_id'    => $keyword, // the id of the referenced object
            'object_type'  => 'wiki',   // type of activity object
            'mkdate'       =>  $mkdate,
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('eine Wiki-Seite');
    }
}
