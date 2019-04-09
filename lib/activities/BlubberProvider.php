<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */


namespace Studip\Activity;

require_once 'public/plugins_packages/core/Blubber/models/BlubberPosting.class.php';

class BlubberProvider implements ActivityProvider
{
    // This stores all already handled blubber items
    // (for this script execution)
    private static $handled = [];

    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $blubb = \BlubberPosting::find($activity->object_id);

        $activity->content = formatReady($blubb->description);

        if ($blubb->context_type == 'course') {
            $params = ['cid' => $blubb->seminar_id];
        } else {
            $params = ['username' => get_username($blubb->seminar_id)];
        }
        $url = \PluginEngine::getURL(
            'Blubber',
            $params,
            'streams/thread/' . $blubb->root_id . '#posting_' . $activity->object_id,
            true
        );

        $route = \URLHelper::getURL('api.php/blubber/posting/' . $activity->object_id, NULL, true);

        $activity->object_url = [
            $url => _('Zum Blubberstream')
        ];

        $activity->object_route = $route;

        return true;
    }

    /**
     * creates an activity for a given context and blubb
     *
     * @param String $context
     * @param String $context_id
     * @param String  $blubb
     * @param bool   $is_new
     */
    private static function doPostActivity($context, $context_id, $blubb, $is_new)
    {
        $verb = $is_new ? 'created' : 'edited';

        $activity = Activity::create(
            [
                'provider'     => __CLASS__,
                'context'      => $context,
                'context_id'   => $context_id,
                'title'        => '',
                'content'      => NULL,
                'actor_type'   => 'user',             // who initiated the activity?
                'actor_id'     => $blubb['user_id'],  // id of initiator
                'verb'         => $verb,              // the activity type
                'object_id'    => $blubb['topic_id'], // the id of the referenced object
                'object_type'  => 'blubber',          // type of activity object
                'mkdate'       => $blubb['chdate']
            ]
        );

    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notication for an activity
     * @param String  $blubb
     * @param bool   $is_new
     */
    public static function postActivity($event, $blubb, $is_new)
    {
        // Check if this blubb was already handled
        if (in_array($blubb->id, self::$handled)) {
            return;
        }

        switch($blubb['context_type']) {
            case 'private':
                foreach ($blubb->getRelatedUsers() as $context_id) {
                    self::doPostActivity('user', $context_id, $blubb, $is_new);
                }
            break;

            case 'course':
                self::doPostActivity('course', $blubb['Seminar_id'], $blubb, $is_new);
            break;

            case 'public':
                self::doPostActivity('system', 'system', $blubb, $is_new);
            break;
        }

        // Stored id as handled
        self::$handled[] = $blubb->id;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('eine Blubbernachricht');
    }

}
