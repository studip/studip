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
     * @param  String $courseSetId
     * @return A 2-dimensional array containing all priorities.
     */
    public static function getPriorities($courseSetId)
    {
        $query = "SELECT p.`user_id`, p.`seminar_id`, p.`priority`
                  FROM `priorities` AS p
                  JOIN `seminare` AS s ON (p.`seminar_id` = s.`Seminar_id`)
                  WHERE p.`set_id` = ?";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$courseSetId]);

        $priorities = [];
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
     * @param  String $courseSetId
     * @param  String $courseId
     * @return An array containing all priorities.
     */
    public static function getPrioritiesByCourse($courseSetId, $courseId)
    {
        $query = "SELECT p.`user_id`, p.`priority`
                  FROM `priorities` AS p
                  JOIN `seminare` AS s ON (p.`seminar_id` = s.`Seminar_id`)
                  WHERE p.`set_id` = ? AND p.`seminar_id` = ?";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$courseSetId, $courseId]);

        $priorities = [];
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
     * @param  String $courseSetId
     * @param  String $userId
     * @return An array containing all priorities.
     */
    public static function getPrioritiesByUser($courseSetId, $userId)
    {
        $query = "SELECT p.`seminar_id`, p.`priority`
                  FROM `priorities` AS p
                  JOIN `seminare` AS s ON (p.`seminar_id` = s.`Seminar_id`)
                  WHERE p.`set_id` = ? AND p.`user_id` = ?
                  ORDER BY p.`priority`";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$courseSetId, $userId]);

        $priorities = [];
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $priorities[$current['seminar_id']] = $current['priority'];
        }
        return $priorities;
    }

    /**
     * The given user sets a course in the given course set to priority x.
     *
     * @param  String $courseSetId
     * @param  String $userId
     * @param  String $courseId
     * @param  int    $priority
     * @return int Number of affected rows, if any.
     */
    public static function setPriority($courseSetId, $userId, $courseId, $priority)
    {
        $query = "INSERT INTO `priorities` (
                    `user_id`, `set_id`, `seminar_id`, `priority`, `mkdate`, `chdate`
                  )
                  SELECT ?, ?, `seminare`.`seminar_id`, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  FROM `seminare` INNER JOIN `seminar_courseset` USING(`seminar_id`)
                  WHERE `seminare`.`seminar_id` = ? AND `set_id` = ?
                    ON DUPLICATE KEY
                      UPDATE `priority` = VALUES(`priority`),
                             `chdate` = VALUES(`chdate`)";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([(string)$userId, (string)$courseSetId, (int)$priority, (string)$courseId, (string)$courseSetId]);

        $ok = $stmt->rowCount();
        if ($ok) {
            StudipLog::log(
                'SEM_USER_ADD', $courseId, $userId,
                'Anmeldung zur Platzvergabe',
                sprintf('Prio: %s Anmeldeset: %s', $priority, $courseSetId)
            );
            NotificationCenter::postNotification(
                'UserAdmissionPriorityDidCreate', $courseId, $userId
            );
        }
        return $ok;
    }

    /**
     * unset priority for given user,set and course
     * reorder remaining priorities
     *
     * @param  String $courseSetId
     * @param  String $userId
     * @param  String $courseId
     * @return int Number of affected rows, if any.
     */
    public static function unsetPriority($courseSetId, $userId, $courseId)
    {
        $query = "DELETE FROM `priorities`
                  WHERE `user_id` = ? AND `seminar_id` = ? AND `set_id` = ?
                  LIMIT 1";
        $deleted = DBManager::get()->execute($query, [$userId, $courseId, $courseSetId]);
        if (!$deleted) {
            return 0;
        }

        $priovar = md5($courseSetId . $userId);
        DBManager::get()->exec("SET @{$priovar} := 0");

        $query = "UPDATE `priorities`
                  SET `priority` = (@{$priovar} := @{$priovar} + 1)
                  WHERE `user_id` = ? AND `set_id` = ?
                  ORDER BY `priority`";
        DBManager::get()->execute($query, [$userId, $courseSetId]);

        StudipLog::log(
            'SEM_USER_DEL', $courseId, $userId,
            'Anmeldung zur Platzvergabe zurückgezogen',
            sprintf('Anmeldeset: %s', $courseSetId)
        );
        NotificationCenter::postNotification(
            'UserAdmissionPriorityDidDelete', $courseId, $userId
        );

        return $deleted;
    }

    /**
     * delete all priorities for one set
     *
     * @param  String $courseSetId
     * @return int Number of affected rows, if any.
     */
    public static function unsetAllPriorities($courseSetId)
    {
        $query = "DELETE FROM `priorities` WHERE `set_id` = ?";
        return DBManager::get()->execute($query, [$courseSetId]);
    }

    /**
     * delete all priorities for one set and one user
     *
     * @param  String $courseSetId
     * @param  String $userId
     * @return int Number of affected rows, if any.
     */
    public static function unsetAllPrioritiesForUser($courseSetId, $userId)
    {
        $query = "DELETE FROM `priorities`
                  WHERE `user_id` = ? AND `set_id` = ?";
        return DBManager::get()->execute($query, [$userId, $courseSetId]);
    }

    /**
     * returns statistics of priority selection for a set
     *
     * @param  String $courseSetId
     * @return array stats grouped by course id
     */
    public static function getPrioritiesStats($courseSetId)
    {
        $query = "SELECT p.`seminar_id`,
                         COUNT(*) AS c,
                         AVG(p.`priority`) AS a,
                         COUNT(IF(p.`priority` = 1, 1, NULL)) AS h
                  FROM `priorities` AS p
                  JOIN `seminare` AS s ON (p.`seminar_id` = s.`Seminar_id`)
                  WHERE p.`set_id` = ?
                  GROUP BY p.`seminar_id`";
        return DBManager::get()->fetchGrouped($query, [$courseSetId]);
    }

    /**
     * returns number of users with priorities for a set
     *
     * @param  String $courseSetId
     * @return integer
     */
    public static function getPrioritiesCount($courseSetId)
    {
        $query = "SELECT COUNT(DISTINCT `user_id`)
                  FROM `priorities`
                  WHERE `set_id` = ?";
        return (int) DBManager::get()->fetchColumn($query, [$courseSetId]);
    }

    /**
     * return max chosen priority in set
     *
     * @param  String $courseSetId
     * @return integer
     */
    public static function getPrioritiesMax($courseSetId)
    {
        $query = "SELECT MAX(`priority`) FROM `priorities` WHERE `set_id` = ?";
        return (int) DBManager::get()->fetchColumn($query, [$courseSetId]);
    }

    /**
     * delete all priorities for one course
     *
     * @param  String $course_id
     * @return int Number of affected rows, if any.
     */
    public static function unsetAllPrioritiesForCourse($course_id)
    {
        $query = "DELETE FROM `priorities` WHERE `seminar_id` = ?";
        return DBManager::get()->execute($query, [$course_id]);
    }
}
