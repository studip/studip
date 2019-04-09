<?php
/**
 * ForumFavorite.php - Add and remove favorite postings
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

class ForumFavorite {
    
    /**
     * Set the topic denoted by the passed id as favorite for the 
     * currently logged in user
     * 
     * @param string $topic_id
     */
    static function set($topic_id) {
        $stmt = DBManager::get()->prepare("REPLACE INTO
            forum_favorites (topic_id, user_id)
            VALUES (?, ?)");
        $stmt->execute([$topic_id, $GLOBALS['user']->id]);
    }

    /**
     * Remove the topic denoted by the passed id as favorite for the 
     * currently logged in user
     * 
     * @param string $topic_id
     */
    static function remove($topic_id) {
        $stmt = DBManager::get()->prepare("DELETE FROM forum_favorites
            WHERE topic_id = ? AND user_id = ?");
        $stmt->execute([$topic_id, $GLOBALS['user']->id]);        
    }
}