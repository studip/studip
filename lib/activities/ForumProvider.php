<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later3
 */


namespace Studip\Activity;

class ForumProvider implements ActivityProvider
{
    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $post = \ForumEntry::getEntry($activity->object_id);

        $activity->content = formatReady($post['content']);

        $url = \PluginEngine::getURL('CoreForum', [], 'index/index/' . $post['topic_id']
                    .'?cid='. $post['seminar_id'] .'&highlight_topic='. $post['topic_id']
                    .'#'. $post['topic_id']);

        $route = \URLHelper::getURL('api.php/forum_entry/' . $post['topic_id'], NULL, true);

        $activity->object_url = [
            $url => _('Zum Forum der Veranstaltung')
        ];

        $activity->object_route = $route;

        return true;
    }

    /**
     *  {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('einen Forenbeitrag');
    }

}
