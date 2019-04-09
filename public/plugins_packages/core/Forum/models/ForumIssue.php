<?php
/**
 * ForumIssue.php - Manage issues linked to postings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

NotificationCenter::addObserver('ForumIssue', 'unlinkIssue', 'ForumBeforeDelete');

class ForumIssue
{
    /**
     * Get the id of the topic linked to the issue denoted by the passed id.
     *
     * @param string $issue_id
     * @return string  the id of the linked topic
     */
    static function getThreadIdForIssue($issue_id)
    {
        $stmt = DBManager::get()->prepare("SELECT topic_id FROM forum_entries_issues
            WHERE issue_id = ?");
        $stmt->execute([$issue_id]);

        return ($stmt->fetchColumn());
    }


    /**
     * Get the id of the issue linked to the topic denoted by the passed id.
     * 
     * @param string $topic_id
     * @return string  the id of the linked topic
     */
    static function getIssueIdForThread($topic_id)
    {
        $stmt = DBManager::get()->prepare("SELECT issue_id FROM forum_entries_issues
            WHERE topic_id = ?");
        $stmt->execute([$topic_id]);
        
        return ($stmt->fetchColumn());
    }


    /**
     * Create/Update the linked posting for the passed issue_id
     *
     * @param string $seminar_id
     * @param string $issue_id    issue id to link to
     * @param string $title       (new) title of the posting
     * @param string $content     (new) content of the posting
     */
    static function setThreadForIssue($seminar_id, $issue_id, $title, $content)
    {
        if ($topic_id = self::getThreadIdForIssue($issue_id)) {   // update
            ForumEntry::update($topic_id, $title ?: _('Ohne Titel'), $content);

        } else {                                                  // create
            // make sure the forum is set up properly
            ForumEntry::checkRootEntry($seminar_id);

            $topic_id = md5(uniqid(rand()));

            ForumEntry::insert([
                'topic_id'    => $topic_id,
                'seminar_id'  => $seminar_id,
                'user_id'     => $GLOBALS['user']->id,
                'name'        => $title ?: _('Ohne Titel'),
                'content'     => $content,
                'author'      => get_fullname($GLOBALS['user']->id),
                'author_host' => ($GLOBALS['user']->id == 'nobody') ? getenv('REMOTE_ADDR') : ''
            ], $seminar_id);

            $stmt = DBManager::get()->prepare("INSERT INTO forum_entries_issues
                (issue_id, topic_id) VALUES (?, ?)");
            $stmt->execute([$issue_id, $topic_id]);
        }
    }

    /**
     * Remove the link for the posting denoted by the passed topic_id
     *
     * @param object $notification
     * @param string $topic_id
     */
    static function unlinkIssue($notification, $topic_id)
    {
        $stmt = DBManager::get()->prepare("DELETE FROM forum_entries_issues
            WHERE topic_id = ?");
        $stmt->execute([$topic_id]);
    }
}
