<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class DocumentsProvider implements ActivityProvider
{

    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $activity->content = \htmlReady($activity->content);

        $document = \FileRef::find($activity->object_id);

        // check, if current observer has access to document
        if (!$document || !$activity->getContextObject() || !$document->folder->getTypedFolder()->isFileDownloadable($document, $activity->getContextObject()->getObserver()->id)) {
            return false;
        }

        if ($activity->context == "course") {
            $url = \URLHelper::getUrl("dispatch.php/course/files/flat?cid={$activity->context_id}");
            $route = \URLHelper::getURL('api.php/file/' . $activity->object_id, NULL, true);

            $activity->object_url = [
                $url => _('Zum Dateibereich der Veranstaltung')
            ];
        } elseif ($activity->context == "institute") {
            $url = \URLHelper::getUrl("dispatch.php/institute/files/flat?cid={$activity->context_id}");
            $route= null;

            $activity->object_url = [
                $url => _('Zum Dateibereich der Einrichtung')
            ];
        }

        $activity->object_route = $route;

        return true;
    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param \FileRef  $document information which a relevant for the activity
     */
    public function postActivity($event, $file_ref)
    {


        $user_id = $file_ref->user_id;
        $file_name = $file_ref->name;
        $course_id = $file_ref->folder->range_id;
        $file_id = $file_ref->id;

        $type     = $file_ref->folder->range_type;
        if ($type == 'course') {
            $course = \Course::find($course_id);
        } elseif ($type == 'institute') {
            $course = \Institute::find($course_id);
        }

        if (!isset($course)) {
            return;
        }

        if (in_array($event, ['FileRefDidCreate'])) {
            $verb = 'created';
            if ($type == 'course') {
                $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" hochgeladen.');
            } else {
                $summary = _('Die Datei %s wurde von %s in der Einrichtung "%s" hochgeladen.');
            }
            $summary = sprintf($summary,$file_name, get_fullname($user_id) ,$course->name);
            $mkdate = $file_ref->mkdate;
        } elseif (in_array($event, ['FileRefDidUpdate'])) {
            $verb = 'edited';
            if ($type == 'course') {
                $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" aktualisiert.');
            } else {
                $summary = _('Die Datei %s wurde von %s in der Einrichtung "%s" aktualisiert.');
            }
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $file_ref->chdate;
        } elseif (in_array($event, ['FileRefDidDelete'])) {
            $verb = 'voided';
            if ($type == 'course') {
                $summary = _('Die Datei %s wurde von %s in der Veranstaltung "%s" gelöscht.');
            } else {
                $summary = _('Die Datei %s wurde von %s in der Einrichtung "%s" gelöscht.');
            }
            $summary = sprintf($summary,$file_name, get_fullname($user_id), $course->name);
            $mkdate = $file_ref->chdate;
        } else {
            return;
        }

        if (isset($verb)) {
            $activity = Activity::create(
                [
                    'provider'     => __CLASS__,
                    'context'      => $type,
                    'context_id'   => $course_id,
                    'content'      => $summary,
                    'actor_type'   => 'user',      // who initiated the activity?
                    'actor_id'     => $user_id,    // id of initiator
                    'verb'         => $verb,       // the activity type
                    'object_id'    => $file_id,    // the id of the referenced object
                    'object_type'  => 'documents', // type of activity object
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
        return _('eine Datei');
    }
}
