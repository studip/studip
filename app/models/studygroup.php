<?php
# Lifter010: TODO
/*
 * studygroup.php - Contains the StudygroupModel class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     André Klaßen <andre.klassen@elan-ev.de>
 * @copyright  2009 ELAN e.V.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category   Stud.IP
 *
 */

require_once 'lib/messaging.inc.php';

class StudygroupModel
{
    /**
     * retrieves all installed plugins
     *
     * @return array modules a set of all plugins
     */
    public static function getInstalledPlugins()
    {
        $modules = [];

        // get all globally enabled plugins
        $plugins = PluginManager::getInstance()->getPlugins('StandardPlugin');
        foreach ($plugins as $plugin) {
            $modules[get_class($plugin)] = $plugin->getPluginName();
        }

        return $modules;
    }

    /**
     * retrieves all modules
     *
     * @return array modules
     */
    public static function getInstalledModules()
    {
        $modules = [];

        // get core modules
        $admin_modules = new AdminModules();

        foreach ($admin_modules->registered_modules as $key => $data) {
            if ($admin_modules->isEnableable($key, '', 'sem')) {
                $modules[$key] = $data['name'];
            }
        }

        return $modules;
    }

    /**
     * gets enabled plugins for a given studygroup
     *
     * @param string id of a studygroup
     *
     * @return array enabled plugins
     */
    public static function getEnabledPlugins($id)
    {
        $enabled = [];

        // get all globally enabled plugins
        $plugins = PluginManager::getInstance()->getPlugins('StandardPlugin');
        foreach ($plugins as $plugin) {
            $enabled[get_class($plugin)] = $plugin->isActivated($id);
        }

        return $enabled;
    }

    /**
     * retrieves all institues suitbable for an admin wrt global studygroup settings
     *
     * @return array institutes
     */
    public static function getInstitutes()
    {
        $institutes = [];

        // Prepare institutes statement
        $query = "SELECT Institut_id, Name
                  FROM Institute
                  WHERE fakultaets_id = ? AND fakultaets_id != Institut_id
                  ORDER BY Name";
        $institute_statement = DBManager::get()->prepare($query);

        // get faculties
        $query = "SELECT Name, Institut_id, 1 AS is_fak, 'admin' AS inst_perms
                  FROM Institute
                  WHERE Institut_id = fakultaets_id
                  ORDER BY Name";
        $stmt = DBManager::get()->query($query);
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $institutes[$data['Institut_id']] = [
                'name'   => $data['Name'],
                'childs' => [],
            ];

            // institutes for faculties
            $institute_statement->execute([$data['Institut_id']]);
            while ($data2 = $institute_statement->fetch(PDO::FETCH_ASSOC)) {
                $institutes[$data['Institut_id']]['childs'][$data2['Institut_id']] = $data2['Name'];
            }
            $institute_statement->closeCursor();
        }

        return $institutes;
    }

    /**
     * allows an user to access a "closed" studygroup
     *
     * @param string username
     * @param string id of a studygroup
     */
    public static function accept_user($username, $sem_id)
    {
        $query = "SELECT user_id
                  FROM admission_seminar_user AS asu
                  JOIN auth_user_md5 AS au USING (user_id)
                  WHERE au.username = ? AND asu.seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$username, $sem_id]);
        if ($accept_user_id = $statement->fetchColumn()) {
            $query = "INSERT INTO seminar_user
                        (user_id, seminar_id, status, position, gruppe,
                         notification, mkdate, comment, visible)
                      VALUES (?, ?, 'autor', 0, 8, 0, UNIX_TIMESTAMP(), '', 'yes')";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$accept_user_id, $sem_id]);

            $query = "DELETE FROM admission_seminar_user
                      WHERE user_id = ? AND seminar_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$accept_user_id, $sem_id]);

            // Post equivalent notifications to a regular course
            $seminar = Seminar::getInstance($sem_id);
            NotificationCenter::postNotification(
                'CourseDidGetMember', $seminar, $accept_user_id
            );
            NotificationCenter::postNotification(
                'CourseDidChangeMember', $seminar, $accept_user_id
            );
            NotificationCenter::postNotification(
                'UserDidEnterCourse', $sem_id, $accept_user_id
            );
        }
    }

    /**
     * denies access to a "closed" studygroup for an user
     *
     * @param string username
     * @param string id of a studygroup
     *
     * @return void
     */
    public static function deny_user($username, $sem_id)
    {
        $query = "DELETE FROM admission_seminar_user
                  WHERE user_id = ? AND seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            get_userid($username),
            $sem_id
        ]);
    }

    /**
     * promotes an user in a studygroup wrt to a given perm
     *
     * @param string username
     * @param string id of a studygroup
     * @param string perm
     *
     * @return void
     */
    public static function promote_user($username, $sem_id, $perm)
    {
        $query = "UPDATE seminar_user
                  SET status = ?
                  WHERE Seminar_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $perm,
            $sem_id,
            get_userid($username),
        ]);
    }

    /**
     * removes a user of a studygroup
     *
     * @param string username
     * @param string id of a studygroup
     *
     * @return void
     */
    public static function remove_user($username, $sem_id)
    {
        $user_id = get_userid($username);

        $query = "DELETE FROM seminar_user
                  WHERE Seminar_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$sem_id, $user_id]);

        // Post equivalent notifications to a regular course
        $seminar = Seminar::getInstance($sem_id);
        NotificationCenter::postNotification(
            'CourseDidChangeMember', $seminar, $user_id
        );
        NotificationCenter::postNotification(
            'UserDidLeaveCourse', $sem_id, $user_id
        );
    }

    /**
     * retrieves the count of all studygroups
     *
     * @param string $search        Search term
     * @param mixed  $closed_groups Display closed groups
     * @return int count
     */
    public static function countGroups($search = null, $closed_groups = null)
    {
        $status = studygroup_sem_types();

        $query = "SELECT COUNT(*)
                  FROM seminare
                  WHERE status IN (?)";
        if (!$GLOBALS['perm']->have_perm('root')) {
            $query .= "AND visible = 1";
        }

        $parameters = [$status];

        if (isset($search)) {
            $query .= " AND Name LIKE CONCAT('%', ?, '%')";
            $parameters[] = $search;
        }
        if (isset($closed_groups) && !$closed_groups) {
            $query .= " AND admission_prelim = 0 ";
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    /**
     * get all studygroups in a paged manner wrt a stort criteria and a search term
     *
     * @param string $sort              Sort criteria
     * @param int    $lower_bound       Lower bound of the resultset
     * @param int    $elements_per_page Elements per page, if null get the global configuration value
     * @param string $search            Search term
     * @param mixed  $closed_groups     Display closed groups
     * @return array studygroups
     */
    public static function getAllGroups($sort = '', $lower_bound = 1, $elements_per_page = null, $search = null, $closed_groups = null)
    {
        if (!$elements_per_page) {
            $elements_per_page = Config::get()->ENTRIES_PER_PAGE;
        }

        $sql = "SELECT *
                FROM seminare AS s";
        $sql_additional = '';
        $conditions = [];
        $parameters = [];

        $conditions[] = 's.status IN (?)';
        $parameters[] = studygroup_sem_types();

        if (!$GLOBALS['perm']->have_perm('root')) {
            $conditions[] = 's.visible = 1';
        }

        if (isset($search)) {
            $conditions[] = "Name LIKE CONCAT('%', ?, '%')";
            $parameters[] = $search;
        }
        if (isset($closed_groups) && !$closed_groups) {
            $conditions[] = 'admission_prelim = 0';
        }

        list($sort_by, $sort_order) = explode('_', $sort);
        $sort_order = $sort_order === 'asc' ? 'ASC' : 'DESC';

        // add here the sortings
        if ($sort_by === 'name') {
            $sort_by = 'Name';
        } elseif ($sort_by === 'founded') {
            $sort_by = 'mkdate';
        } elseif ($sort_by === 'member') {
            $sort_by = 'members';

            $sql = "SELECT s.*, COUNT(su.user_id) AS members
                    FROM seminare AS s
                    LEFT JOIN seminar_user AS su USING (Seminar_id)";

            $sql_additional = 'GROUP BY s.Seminar_id';
        } elseif ($sort_by === 'founder') {
            $sort_by = "GROUP_CONCAT(aum.Nachname ORDER BY su.status, su.position, aum.Nachname, aum.Vorname SEPARATOR ',')";

            $sql = "SELECT s.*
                    FROM seminare AS s
                    LEFT JOIN seminar_user AS su ON (s.Seminar_id = su.Seminar_id AND su.status = 'dozent')
                    LEFT JOIN auth_user_md5 AS aum ON (su.user_id = aum.user_id)";

            $sql_additional = 'GROUP BY s.Seminar_id';
        } elseif ($sort_by === 'ismember') {
            $sort_by = 'is_member';

            $sql = "SELECT s.*, COUNT(su.user_id) AS is_member
                    FROM seminare AS s
                    LEFT JOIN seminar_user AS su ON s.Seminar_id = su.Seminar_id AND su.user_id = ?";
            array_unshift($parameters, $GLOBALS['user']->id);

            $sql_additional = 'GROUP BY s.Seminar_id';
        } elseif ($sort_by == 'access') {
            $sort_by = 'admission_prelim';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ' . $sql_additional;
        $sql .= " ORDER BY {$sort_by} {$sort_order}";
        $sql .= ", name {$sort_order} LIMIT " . (int) $lower_bound . ',' . (int) $elements_per_page;

        $statement = DBManager::get()->prepare($sql);
        $statement->execute($parameters);
        $groups = $statement->fetchAll();

        return $groups;
    }

    /**
     * returns the count of members for a given studygroup
     *
     * @param string id of a studygroup
     * @return int count
     */
    public static function countMembers($semid)
    {
        $sql = "SELECT COUNT(`user_id`)
                FROM `seminar_user`
                WHERE `Seminar_id` = ?";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute([$semid]);
        $count = $stmt->fetchColumn();

        return (int) $count;
    }

    /**
     * get founder for a given studgroup
     *
     * @param string id of a studygroup
     * @return array founder
     */
    public static function getFounder($semid)
    {
        $founder = [];
        foreach (CourseMember::findByCourseAndStatus($semid, 'dozent') as $user) {
            $founder[] = [
                'user_id'  => $user->user_id,
                'fullname' => $user->getUserFullname(),
                'uname'    => $user->username,
            ];
        }
        return $founder;
    }

    /**
     * checks whether a user is a member of a studygroup
     *
     * @param string id of a user
     * @param string id of a studygroup
     * @return boolean membership
     */
    public static function isMember($userid, $semid)
    {
        $sql = "SELECT 1
                FROM `seminar_user`
                WHERE `Seminar_id` = ? AND `user_id` = ?";

        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute([$semid, $userid]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * adds a founder to a given studygroup
     *
     * @param string username
     * @param string id of a studygroup
     */
    public static function addFounder($username, $sem_id)
    {
        $query = "INSERT IGNORE INTO seminar_user (Seminar_id, user_id, status)
                  VALUES (?, ?, 'dozent')";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$sem_id, get_userid($username)]);
    }

    /**
     * removes a founder from a given studygroup
     *
     * @param string username
     * @param string id of a studygroup
     */
    public static function removeFounder($username, $sem_id)
    {
        $query = "DELETE FROM seminar_user
                  WHERE Seminar_id = ? AND user_id = ?";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$sem_id, get_userid($username)]);
    }

    /**
     * get founders of a given studygroup
     *
     * @param string id of a studygroup
     * @return array founders
     */
    public static function getFounders($sem_id)
    {
        $query = "SELECT username, perms, {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
                  FROM seminar_user
                  LEFT JOIN auth_user_md5 USING (user_id)
                  LEFT JOIN user_info USING (user_id)
                  WHERE Seminar_id = ? AND status = 'dozent'";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$sem_id]);

        return $stmt->fetchAll();
    }

    /**
     * retrieves all members of a given studygroup in a paged manner
     *
     * @param string id of a studygroup
     * @param int lower bound of the resultset
     * @param int elements per page, if null get the global configuration value
     *
     * @return array members
     */
    public static function getMembers($sem_id, $lower_bound = 1, $elements_per_page = null)
    {
        if (!$elements_per_page) {
            $elements_per_page = Config::get()->ENTRIES_PER_PAGE;
        }

        $query = "SELECT user_id ,username ,perms, seminar_user.status,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname,
                         seminar_user.mkdate
                  FROM seminar_user
                  LEFT JOIN auth_user_md5 USING (user_id)
                  LEFT JOIN user_info USING (user_id)
                  WHERE Seminar_id = ?
                  ORDER BY seminar_user.mkdate ASC, seminar_user.status ASC";

        if ($elements_per_page !== 'all') {
            $query .= " LIMIT {$lower_bound}, {$elements_per_page}";
        }

        return DBManager::get()->fetchGrouped($query, [$sem_id]);
    }

    /**
     * invites a member to a given studygroup.
     *
     * @param string user id
     * @param string id of a studygroup
     */
    public static function inviteMember($user_id, $sem_id)
    {
        $query = "REPLACE INTO studygroup_invitations (sem_id, user_id, mkdate)
                  VALUES (?, ?, UNIX_TIMESTAMP())";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$sem_id, $user_id]);
    }

    /**
     * cancels invitation.
     *
     * @param string username
     * @param string id of a studygroup
     */
    public static function cancelInvitation($username, $sem_id)
    {
        $query = "DELETE FROM studygroup_invitations
                  WHERE sem_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $sem_id,
            get_userid($username)
        ]);
    }

    /**
     * returns invited member of a given studygroup.
     *
     * @param string id of a studygroup
     * @return array invited members
     */
    public static function getInvitations($sem_id)
    {
        $query = "SELECT username, user_id,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname,
                         studygroup_invitations.mkdate
                  FROM studygroup_invitations
                  LEFT JOIN auth_user_md5 USING (user_id)
                  LEFT JOIN user_info USING (user_id)
                  WHERE studygroup_invitations.sem_id = ?
                  ORDER BY studygroup_invitations.mkdate ASC";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$sem_id]);

        return $stmt->fetchAll();
    }

    /**
     * checks if a user is already invited.
     *
     * @param string user id
     * @param string id of a studygroup
     * @return array invited members
     */
    public static function isInvited($user_id, $sem_id)
    {
        $query = "SELECT 1
                  FROM studygroup_invitations
                  WHERE user_id = ? AND sem_id = ?";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$user_id, $sem_id]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * callback function - used to compare sequences of studygroup statuses
     *
     * @param array status a
     * @param array status b
     * @return int ordering
     */
    public static function compare_status($a, $b)
    {
        if ($a['status'] === $b['status']) {
            return strnatcmp($a['fullname'], $b['fullname']);
        }

        if ($a['status'] === 'dozent') {
            if ($b['status'] === 'tutor') {
                return -1;
            } elseif ($b['status'] === 'autor') {
                return -1;
            }
        } elseif ($a['status'] === 'tutor') {
            if ($b['status'] === 'dozent') {
                return 1;
            } elseif ($b['status'] === 'autor') {
                return -1;
            }
        } elseif ($a['status'] === 'autor') {
            if ($b['status'] === 'tutor') {
                return 1;
            } elseif ($b['status'] === 'dozent') {
                return 1;
            }
        }
    }

    /**
     * Checks for a given seminar_id whether a course is a studygroup
     *
     * @param   string id of a seminar
     *
     * @return  array studygroup
     */
    public static function isStudygroup($sem_id)
    {
        $sql = "SELECT *
                FROM seminare
                WHERE Seminar_id = ? AND status IN (?)";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute([
            $sem_id,
            studygroup_sem_types()
        ]);

        return $stmt->fetch();
    }

    /**
     * If a new user applies, an application note to all moderators and founders
     * of a studygroup will be automatically sent while calling this function.
     * The note contains the user's name and a direct link to the member page of the studygroup.
     *
     * @param string $sem_id id of a seminar / studygroup
     * @param string $user_id id of the applicant
     * @return int number of recipients
     */
    public static function applicationNotice($sem_id, $user_id)
    {
        $sem        = new Seminar($sem_id);
        $dozenten   = $sem->getMembers();
        $tutors     = $sem->getMembers('tutor');
        $recipients = [];
        $msging     = new Messaging();

        foreach (array_merge($dozenten, $tutors) as $uid => $user) {
            $recipients[] = $user['username'];
        }

        if (mb_strlen($sem->getName()) > 32) //cut subject if to long
            $subject = sprintf(_('[Studiengruppe: %s...]'), mb_substr($sem->getName(), 0, 30));
        else
            $subject = sprintf(_('[Studiengruppe: %s]'), $sem->getName());

        if (StudygroupModel::isInvited($user_id, $sem_id)) {
            $subject .= ' ' . _('Einladung akzeptiert');
            $message = sprintf(
                _("%s hat die Einladung zur Studiengruppe %s akzeptiert. Klicken Sie auf den untenstehenden Link, um direkt zur Studiengruppe zu gelangen.\n\n [Direkt zur Studiengruppe]%s"),
                get_fullname($user_id),
                $sem->getName(),
                URLHelper::getlink(
                    "{$GLOBALS['ABSOLUTE_URI_STUDIP']}dispatch.php/course/studygroup/members/?cid={$sem->id}",
                    ['cid' => $sem->id]
                )
            );
        } else {
            $subject .= ' ' . _('Neuer Mitgliedsantrag');
            $message = sprintf(
                _("%s möchte der Studiengruppe %s beitreten. Klicken Sie auf den untenstehenden Link, um direkt zur Studiengruppe zu gelangen.\n\n [Direkt zur Studiengruppe]%s"),
                get_fullname($user_id),
                $sem->getName(),
                URLHelper::getlink(
                    "{$GLOBALS['ABSOLUTE_URI_STUDIP']}dispatch.php/course/studygroup/members/?cid={$sem->id}",
                    ['cid' => $sem->id]
                )
            );
        }

        return $msging->insert_message($message, $recipients, '', '', '', '1', '', $subject);
    }
}
