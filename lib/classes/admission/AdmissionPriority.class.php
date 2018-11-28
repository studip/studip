<?php

/**
 * AdmissionPriority.class.php
 *
 * This class represents priorities a user has given to a set of courses.
 * No instance is needed, all methods are designed to be called statically.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class AdmissionPriority
{

    /**
     * Get all priorities for the given course set.
     * The priorities are stored in a 2-dimensional array in the form
     * priority[user_id][course_id] = x.
     *
     * @param  String courseSetId
     * @return A 2-dimensional array containing all priorities.
     */
    public static function getPriorities($courseSetId)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `priorities`
             WHERE `set_id`=?");
        $stmt->execute(array($courseSetId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['user_id']][$current['seminar_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * Get all priorities for the given course in the given course set.
     * The priorities are stored in an array in the form
     * priority[user_id] = x.
     *
     * @param  String courseSetId
     * @param  String courseId
     * @return An array containing all priorities.
     */
    public static function getPrioritiesByCourse($courseSetId, $courseId)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `priorities`
             WHERE `set_id`=? AND `seminar_id`=?");
        $stmt->execute(array($courseSetId, $courseId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['user_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * Get all priorities the given user has set in the given course set.
     * The priorities are stored in an array in the form
     * priority[course_id] = x.
     *
     * @param  String courseSetId
     * @param  String userId
     * @return An array containing all priorities.
     */
    public static function getPrioritiesByUser($courseSetId, $userId)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `priorities`
             WHERE `set_id`=? AND `user_id`=? ORDER BY priority");
        $stmt->execute(array($courseSetId, $userId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['seminar_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * The given user sets a course in the given course set to priority x.
     *
     * @param  String courseSetId
     * @param  String userId
     * @param  String courseId
     * @param  int priority
     * @return int Number of affected rows, if any.
     */
    public static function setPriority($courseSetId, $userId, $courseId, $priority)
    {
        $priorities = array();
        $stmt = DBManager::get()->prepare(
            "INSERT INTO `priorities` (`user_id`, `set_id`, `seminar_id`,
                    `priority`, `mkdate`, `chdate`)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE `priority`=VALUES(`priority`),
                    `chdate`=VALUES(`chdate`)");
        $stmt->execute(array($userId, $courseSetId, $courseId,
            $priority, time(), time()));
        $ok = $stmt->rowCount();
        if ($ok) {
            StudipLog::log('SEM_USER_ADD', $courseId,
            $userId, 'Anmeldung zur Platzvergabe', sprintf('Prio: %s Anmeldeset: %s', $priority, $courseSetId));
            NotificationCenter::postNotification('UserAdmissionPriorityDidCreate', $courseId, $userId); 
        }
        return $ok;
    }

    /**
     * unset priority for given user,set and course
     * reorder remaining priorities
     *
     * @param  String courseSetId
     * @param  String userId
     * @param  String courseId
     * @return int Number of affected rows, if any.
     */
    public static function unsetPriority($courseSetId, $userId, $courseId)
    {
        $db = DBManager::get();
        $deleted = $db->execute("DELETE FROM priorities WHERE user_id=? AND seminar_id=? AND set_id=? LIMIT 1",
                 array($userId, $courseId, $courseSetId));
        if ($deleted) {
            $priovar = md5($courseSetId . $userId);
            $db->exec("SET @$priovar:=0");
            $db->execute("UPDATE priorities SET priority = @$priovar:=@$priovar+1 WHERE user_id=? AND set_id=? ORDER BY priority", array($userId, $courseSetId));
            StudipLog::log('SEM_USER_DEL', $courseId,
            $userId, 'Anmeldung zur Platzvergabe zurückgezogen', sprintf('Anmeldeset: %s', $courseSetId));
            NotificationCenter::postNotification('UserAdmissionPriorityDidDelete', $courseId, $userId); 
        }
        return $deleted;
    }

    /**
     * delete all priorities for one set
     *
     * @param  String courseSetId
     * @return int Number of affected rows, if any.
     */
    public static function unsetAllPriorities($courseSetId)
    {
        return DBManager::get()
        ->execute("DELETE FROM priorities WHERE set_id=?",
                array($courseSetId));
    }

    /**
     * delete all priorities for one set and one user
     *
     * @param  String courseSetId
     * @param  String userId
     * @return int Number of affected rows, if any.
     */
    public static function unsetAllPrioritiesForUser($courseSetId, $userId)
    {
        return DBManager::get()
        ->execute("DELETE FROM priorities WHERE user_id=? AND set_id=?",
                array($userId, $courseSetId));
    }

    /**
     * returns statistics of priority selection for a set
     *
     * @param  String courseSetId
     * @return array stats grouped by course id
     */
    public static function getPrioritiesStats($courseSetId)
    {
        return DBManager::get()
                ->fetchGrouped("SELECT seminar_id, COUNT(*) as c, AVG(priority) as a, COUNT(IF(priority=1,1,NULL)) as h FROM priorities WHERE set_id = ? GROUP BY seminar_id",
                 array($courseSetId));
    }

    /**
     * returns number of users with priorities for a set
     *
     * @param  String courseSetId
     * @return integer
     */
    public static function getPrioritiesCount($courseSetId)
    {
        return DBManager::get()
                ->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM priorities WHERE set_id = ?",
                 array($courseSetId));
    }

    /**
     * return max chosen priority in set
     *
     * @param  String courseSetId
     * @return integer
     */
    public static function getPrioritiesMax($courseSetId)
    {
        return DBManager::get()
        ->fetchColumn("SELECT MAX(priority) FROM priorities WHERE set_id = ?",
                array($courseSetId));
    }

    /**
     * delete all priorities for one course
     *
     * @param  String course Id
     * @return int Number of affected rows, if any.
     */
    public static function unsetAllPrioritiesForCourse($course_id)
    {
        return DBManager::get()
        ->execute("DELETE FROM priorities WHERE seminar_id=?",
                array($course_id));
    }

} /* end of class AdmissionPriority */

