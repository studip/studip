<?php
/**
 * filename - Short description for file
 *
 * Long description for file (if any)...
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

class ForumPerm {

    /**
     * Check, if the a user has the passed permission in a seminar.
     * Possible permissions are:
     *   edit_category     - Editing the name of a category<br>
     *   add_category      - Adding a new category<br>
     *   remove_category   - Removing an existing category<br>
     *   sort_category     - Sorting categories<br>
     *   edit_area         - Editing an area (title + content)<br>
     *   add_area          - Adding a new area<br>
     *   remove_area       - Removing an area and all belonging threads<br>
     *   sort_area         - Sorting of areas in categories and between categories<br>
     *   search            - Searching in postings<br>
     *   edit_entry        - Editing of foreign threads/postings<br>
     *   add_entry         - Creating a new thread/posting<br>
     *   remove_entry      - Removing of foreign threads/postings<br>
     *   fav_entry         - Marking a Posting as "favorite"<br>
     *   like_entry        - Liking a posting<br>
     *   move_thread       - Moving a thrad between ares<br>
     *   close_thread      - Close or open a thread<br>
     *   make_sticky       - Make a thread sticky<br>
     *   abo               - Signing up for mail-notifications for new entries<br>
     *   forward_entry     - Forwarding an existing entry as a message<br>
     *   pdfexport         - Exporting parts of the forum as PDF<br>
     *   admin             - Allowed to mass-administrate the forum<br>
     *   view              - Allowed to view the forum at all<br>
     *   edit_closed       - Editing entries in a closed thread
     *
     * @param string $perm        one of the modular permissions
     * @param string $seminar_id  the seminar to check for
     * @param string $user_id     the user to check for
     * @return boolean  true, if the user has the perms, false otherwise
     */
    public static function has($perm, $seminar_id, $user_id = null)
    {
        static $permissions = [];

        // if no user-id is passed, use the current user (for your convenience)
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }

        // get the status for the user in the passed seminar
        if (!$permissions[$seminar_id][$user_id]) {
            $permissions[$seminar_id][$user_id] = $GLOBALS['perm']->get_studip_perm($seminar_id, $user_id);
        }

        $status = $permissions[$seminar_id][$user_id];

        // take care of the not logged in user
        if ($user_id == 'nobody' || $status == false) {
            // which status has nobody - read only or read/write?
            if (get_object_type($seminar_id) == 'sem') {
                $sem = Seminar::getInstance($seminar_id);

                if ($sem->write_level == 0) {
                    $status = 'nobody_write';
                } else if ($sem->read_level == 0) {
                    $status = 'nobody_read';
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        // root and admins have all possible perms
        if (in_array($status, words('root admin')) !== false) {
            return true;
        }

        // eCULT Notlösung
        if ($status == 'tutor' && $seminar_id == '30e0b89dcc9173d5fccf9f22b13b87bd') {
            $status = 'autor';
        }

        // check the status and the passed permission
        if (($status == 'dozent' || $status == 'tutor') && in_array($perm,
            words('edit_category add_category remove_category sort_category '
            . 'edit_area add_area remove_area sort_area '
            . 'search edit_entry add_entry remove_entry fav_entry like_entry move_thread '
            . 'make_sticky close_thread abo forward_entry pdfexport view edit_closed')
        ) !== false) {
            return true;
        } else if ($status == 'autor' && in_array($perm, words('search add_entry fav_entry like_entry forward_entry abo pdfexport view')) !== false) {
            return true;
        } else if ($status == 'user' && in_array($perm, words('search forward_entry pdfexport view')) !== false) {
            return true;
        } else if ($status == 'nobody_write' && in_array($perm, words('search add_entry pdfexport view')) !== false) {
            return true;
        } else if ($status == 'nobody_read' && in_array($perm, words('search pdfexport view')) !== false) {
            return true;
        }

        // user has no permission
        return false;
    }

    /**
     * If the user has not the passed perm in a seminar, an AccessDeniedException
     * is thrown.
     * An optional topic_id can be passed which is checked against the passed
     * seminar if the topic_id belongs to that seminar
     *
     * @param string $perm        for the list of possible perms and their function see @ForumPerm::hasPerm()
     * @param string $seminar_id  the seminar to check for
     * @param string $topic_id    if passed, this topic_id is checked if it belongs to the passed seminar
     *
     * @throws AccessDeniedException
     */
    public static function check($perm, $seminar_id, $topic_id = null)
    {
        if (!self::has($perm, $seminar_id)) {
            throw new AccessDeniedException(sprintf(
                _("Sie haben keine Berechtigung für diese Aktion! Benötigte Berechtigung: %s"),
                $perm)
            );
        }

        // check the topic id (if any)
        if ($topic_id) {
            self::checkTopicId($seminar_id, $topic_id);
        }
    }

    /**
     * Check if the current user is allowed to edit the topic
     *  denoted by the passed id
     *
     * @staticvar array $perms
     *
     * @param string $topic_id the id for the topic to check for
     *
     * @return bool true if the user has the necessary perms, false otherwise
     */
    public static function hasEditPerms($topic_id)
    {
        static $perms = [];

        if (!$perms[$topic_id]) {
            // find out if the posting is the last in the thread
            $constraints = ForumEntry::getConstraints($topic_id);

            $stmt = DBManager::get()->prepare("SELECT user_id, seminar_id
                FROM forum_entries WHERE topic_id = ?");
            $stmt->execute([$topic_id]);

            $data = $stmt->fetch();

            $closed = ForumEntry::isClosed($topic_id);

            $perms[$topic_id] = (($GLOBALS['user']->id == $data['user_id'] && $GLOBALS['user']->id != 'nobody') ||
                ForumPerm::has('edit_entry', $constraints['seminar_id']))
                    && (!$closed || $closed && ForumPerm::has('edit_closed', $constraints['seminar_id']));
        }

        return $perms[$topic_id];
    }

    /**
     * check if the passed category_id belongs to the passed seminar_id.
     * Throws an AccessDenied denied exception if this is not the case
     *
     * @param type $seminar_id   id of the seminar, the category should belong to
     * @param type $category_id  the id of the category to check
     */
    public static function checkCategoryId($seminar_id, $category_id)
    {
        $data = ForumCat::get($category_id);

        if ($data['seminar_id'] != $seminar_id) {
            throw new AccessDeniedException(sprintf(
                _('Forum: Sie haben keine Berechtigung auf die Kategorie mit der ID %s zuzugreifen!'),
                $category_id
            ));
        }
    }

    /**
     * check if the passed topic_id belongs to the passed seminar_id.
     * Throws an AccessDenied denied exception if this is not the case
     *
     * @param type $seminar_id  id of the seminar, the category should belong to
     * @param type $topic_id    the id of the topic to check
     */
    public static function checkTopicId($seminar_id, $topic_id)
    {
        $data = ForumEntry::getConstraints($topic_id);

        if ($data['seminar_id'] != $seminar_id) {
            throw new AccessDeniedException(sprintf(
                _('Forum: Sie haben keine Berechtigung auf den Eintrag mit der ID %s zuzugreifen!'),
                $topic_id
            ));
        }
    }
}
