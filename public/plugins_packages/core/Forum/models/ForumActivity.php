<?php
/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class ForumActivity
{
    /**
     * Post activity for new forum post
     *
     * @param string $event
     * @param string $topic_id
     * @param array $post
     */
    public static function newEntry($event, $topic_id, $post)
    {
        $verb = $post['depth'] == 3 ? 'answered' : 'created';

        if ($verb == 'created') {
            if ($post['depth'] == 1) {
                $summary = _('%s hat im Forum der Veranstaltung "%s" einen Bereich erstellt.');
            } else {
                $summary = _('%s hat im Forum der Veranstaltung "%s" ein Thema erstellt.');
            }
        } else {
            $summary = _('%s hat im Forum der Veranstaltung "%s" auf ein Thema geantwortet.');
        }

        self::post($post, $verb, $summary);
    }

    /**
     * Post activity for updating a forum post
     * @param string $event
     * @param string $topic_id
     * @param string $post
     */
    public static function updateEntry($event, $topic_id, $post)
    {
        $summary = _('%s hat im Forum der Veranstaltung "%s" einen Beitrag editiert.');

        if ($post['user_id'] == $GLOBALS['user']->id) {
            $content = sprintf(
                _('%s hat seinen eigenen Beitrag vom %s editiert.'),
                self::getPostUsername($post),
                date('d.m.y, H:i', $post['mkdate'])
            );
        } else {
            $content = sprintf(
                _('%s hat den Beitrag von %s vom %s editiert.'),
                get_fullname($GLOBALS['user']->id),
                self::getPostUsername($post),
                date('d.m.y, H:i', $post['mkdate'])
            );
        }

        self::post($post, 'edited', $summary, $content);
    }

    /**
     * Post activity for deleting a forum post
     * $param string $event
     * @param string $topic_id
     * @param string $post
     */
    public static function deleteEntry($event, $topic_id, $post)
    {
        // Remove all previous activities for the post
        Studip\Activity\Activity::deleteBySQL(
            "provider = ? AND object_type = 'forum' AND object_id = ?",
            [Studip\Activity\ForumProvider::class, $topic_id]
        );

        $summary = _('%s hat im Forum der Veranstaltung "%s" einen Beitrag gelöscht.');

        if ($post['user_id'] == $GLOBALS['user']->id) {
            $content = sprintf(
                _('%s hat seinen Beitrag vom %s gelöscht.'),
                self::getPostUsername($post),
                date('d.m.y, H:i', $post['mkdate'])
            );
        } else {
            $content = sprintf(
                _('%s hat den Beitrag von %s vom %s gelöscht.'),
                get_fullname($GLOBALS['user']->id),
                self::getPostUsername($post),
                date('d.m.y, H:i', $post['mkdate'])
            );
        }

        self::post($post, 'deleted', $summary, $content);
    }

    private static function post($post, $verb, $summary, $content = null)
    {
        // skip system-created entries like "Allgemeine Diskussionen"
        if (!$post['user_id']) {
            return;
        }

        $range_id = $post['seminar_id'];
        $type     = get_object_type($range_id);

        $obj = get_object_name($range_id, $type);

        $data = [
            'provider'     => 'Studip\Activity\ForumProvider',
            'context'      => $type === 'sem' ? 'course' : 'institute',
            'context_id'   => $post['seminar_id'],
            'content'      => null,
            'actor_type'   => 'user',             // who initiated the activity?
            'actor_id'     => $post['user_id'],   // id of initiator
            'verb'         => $verb,              // the activity type
            'object_id'    => $post['topic_id'],  // the id of the referenced object
            'object_type'  => 'forum',            // type of activity object
            'mkdate'       => $post['mkdate'] ?: time()
        ];

        if ($post['anonymous']) {
            $data['actor_type'] = 'anonymous';
            $data['actor_id']   = '';
        }

        $activity = Studip\Activity\Activity::create($data);
    }

    /**
     * Returns the poster's name for a forum post.
     *
     * @param array $post
     * @return string
     */
    private static function getPostUsername($post)
    {
        if ($post['anonymous']) {
            return _('Anonym');
        }

        return get_fullname($post['user_id']);
    }
}
