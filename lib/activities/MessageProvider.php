<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class MessageProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the acitivty to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $message = \Message::find($activity->object_id);

        if (!$message
            || !$activity->getContextObject()
            || !$message->permissionToRead($activity->getContextObject()->getObserver()->id))
        {
            return false;
        }

        $activity->content = formatReady($message->message);

        $url = \URLHelper::getUrl("dispatch.php/messages/read/{$message->id}", ['cid' => null]);

        $route = \URLHelper::getURL('api.php/message/' . $message->id, NULL, true);

        $activity->object_url = [
            $url => _('Zur Nachricht')
        ];

        $activity->object_route = $route;

        return true;
    }


    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param Array  $info information which a relevant for the activity
     */
    public static function postActivity($event, $message_id, $data)
    {
        foreach ($data['rec_id'] as $rec_id) {

            // activity for receipent
            $activity = Activity::create(
                [
                    'provider'     => __CLASS__,
                    'context'      => 'user',
                    'context_id'   => $rec_id,
                    'content'      => NULL,
                    'actor_type'   => 'user',           // who initiated the activity?
                    'actor_id'     => $data['user_id'], // id of initiator
                    'verb'         => 'sent',           // the activity type
                    'object_id'    => $message_id,      // the id of the referenced object
                    'object_type'  => 'message',        // type of activity object
                    'mkdate'       => time()
                ]
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('eine Nachricht');
    }

}
