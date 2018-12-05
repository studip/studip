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
        $modules = array();

        // get standard-plugins (suitable for seminars)
        $plugin_manager = PluginManager::getInstance();
        $plugins        = $plugin_manager->getPlugins('StandardPlugin');     // get all globally enabled plugins
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
        $modules = array();

        // get core modules
        $admin_modules = new AdminModules();

        foreach ($admin_modules->registered_modules as $key => $data) {
            if ($admin_modules->isEnableable($key, '', 'sem')) $modules[$key] = $data['name'];
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
        $enabled = array();

        $plugin_manager = PluginManager::getInstance();
        $plugins        = $plugin_manager->getPlugins('StandardPlugin');     // get all globally enabled plugins
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
        $institutes = array();

        // Prepare institutes statement
        $query               = "SELECT Institut_id, Name
                  FROM Institute
                  WHERE fakultaets_id = ? AND fakultaets_id != Institut_id
                  ORDER BY Name";
        $institute_statement = DBManager::get()->prepare($query);

        // get faculties
        $stmt = DBManager::get()->query("SELECT Name, Institut_id, 1 AS is_fak,'admin' AS inst_perms "
                                        . "FROM Institute WHERE Institut_id = fakultaets_id ORDER BY Name");
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $institutes[$data['Institut_id']] = array(
                'name'   => $data['Name'],
                'childs' => array()
            );
            // institutes for faculties
            $institute_statement->execute(array($data['Institut_id']));
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
     *
     * @return void
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
        $statement->execute(array(
            get_userid($username),
            $sem_id
        ));
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
        $statement->execute(array(
            $perm,
            $sem_id,
            get_userid($username),
        ));
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
     *  param string search a filter term
     *
     * @return string count
     */
    public static function countGroups($search = null, $closedGroups = null)
    {
        $status = studygroup_sem_types();

        $query = "SELECT COUNT(*)
                  FROM seminare
                  WHERE status IN (?)";
        if (!$GLOBALS['perm']->have_perm('root')) {
            if (!$closedGroups) {
                $query .= " AND admission_prelim = 0 ";
            }
            $query .= "AND visible = 1";
        }

        $parameters = array($status);

        if (isset($search)) {
            $query .= " AND Name LIKE CONCAT('%', ?, '%')";
            $parameters[] = $search;
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        return $statement->fetchColumn();
    }

    /**
     * get all studygroups in a paged manner wrt a stort criteria and a search term
     *
     * @param string sort criteria
     * @param int lower bound of the resultset
     * @param int elements per page, if null get the global configuration value
     * @param string search term
     *
     * @return array studygroups
     */
    public static function getAllGroups($sort = '', $lower_bound = 1, $elements_per_page = NULL, $search = null, $closedGroups = null)
    {
        if (is_null($elements_per_page)) {
            $elements_per_page = get_config('ENTRIES_PER_PAGE');
        }

        $status = studygroup_sem_types();

        $sql = "SELECT *
                FROM seminare AS s
                WHERE status IN (?)";
        if (!$GLOBALS['perm']->have_perm('root')) {
            $sql .= "AND visible = 1";
        }

        $parameters[] = [$status];

        if (isset($search)) {
            $sql .= " AND Name LIKE CONCAT('%', ?, '%')";
            $parameters[] = $search;
            if (!$closedGroups) {
                $sql .= " AND admission_prelim = 0 ";
            }
        }
        $sort_order = (mb_substr($sort, mb_strlen($sort) - 3, 3) == 'asc') ? 'asc' : 'desc';

        // add here the sortings
        if ($sort == 'name_asc') {
            $sql .= " ORDER BY Name ASC";
        } else if ($sort == 'name_desc') {
            $sql .= " ORDER BY Name DESC";
        } else if ($sort == 'founded_asc') {
            $sql .= " ORDER BY mkdate ASC";
        } else if ($sort == 'founded_desc') {
            $sql .= " ORDER BY mkdate DESC";
        } else if ($sort == 'member_asc' || $sort == 'member_desc') {
            $sql = "SELECT s.*, (SELECT COUNT(*) FROM seminar_user AS su WHERE s.Seminar_id = su.Seminar_id) AS countsems
                    FROM seminare AS s
                    WHERE s.status IN (?)";
            if (!$GLOBALS['perm']->have_perm('root')) {
                $sql .= "AND s.visible = 1";
            }
            $parameters = array($status);

            if (!empty($search)) {
                $sql .= " AND s.Name LIKE CONCAT('%', ?, '%')";
                $parameters[] = $search;
            }

            $sql .= " ORDER BY countsems " . $sort_order;
        } else if ($sort == 'founder_asc' || $sort == 'founder_desc') {
            $sql = "SELECT s.*
                    FROM seminare AS s
                    LEFT JOIN seminar_user AS su ON (s.Seminar_id = su.Seminar_id AND su.status = 'dozent')
                    LEFT JOIN auth_user_md5 AS aum ON (su.user_id = aum.user_id)
                    WHERE s.status IN (?)";
            if (!$GLOBALS['perm']->have_perm('root')) {
                $sql .= "AND s.visible = 1";
            }
            $parameters = array($status);

            if (!empty($search)) {
                $sql .= " AND s.Name LIKE CONCAT('%', ?, '%')";
                $parameters[] = $search;
            }
            $sql .= " GROUP BY s.Seminar_id";
            $sql .= " ORDER BY aum.Nachname " . $sort_order;

        } else if ($sort == 'ismember_asc' || $sort == 'ismember_desc') {
            $sql = "SELECT s.*,
                          (SELECT su.user_id FROM seminar_user AS su WHERE su.user_id = ? AND su.Seminar_id = s.Seminar_id ) AS ismember
                    FROM seminare AS s
                    WHERE s.status IN (?)";
            if (!$GLOBALS['perm']->have_perm('root')) {
                $sql .= "AND s.visible = 1";
            }
            $parameters = array(
                $GLOBALS['user']->id,
                $status,
            );
            if (!empty($search)) {
                $sql .= " AND s.Name LIKE CONCAT('%', ?, '%')";
                $parameters[] = $search;
            }
            $sql .= " ORDER BY `ismember`" . $sort_order;
        } else if ($sort == 'access_asc') {
            $sql .= " ORDER BY admission_prelim ASC";
        } else if ($sort == 'access_desc') {
            $sql .= " ORDER BY admission_prelim DESC";
        }

        $sql .= ', name ASC LIMIT ' . (int)$lower_bound . ',' . (int)$elements_per_page;

        $statement = DBManager::get()->prepare($sql);
        $statement->execute($parameters);
        $groups = $statement->fetchAll();

        return $groups;
    }

    /**
     * returns the count of members for a given studygroup
     *
     * @param string id of a studygroup
     *
     * @return int count
     */
    public static function countMembers($semid)
    {
        $sql = "SELECT COUNT(user_id) FROM `seminar_user` WHERE Seminar_id = ?";

        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($semid));
        $count = $stmt->fetchColumn();

        return (int)$count;
    }

    /**
     * get founder for a given studgroup
     *
     * @param string id of a studygroup
     *
     * @return array founder
     *
     */
    public static function getFounder($semid)
    {
        foreach (CourseMember::findByCourseAndStatus($semid, 'dozent') as $user) {
            $founder[] = array(
                'user_id'  => $user->user_id,
                'fullname' => $user->getUserFullname(),
                'uname'    => $user->username
            );
        }
        return (array)$founder;
    }

    /**
     * checks whether a user is a member of a studygroup
     *
     * @param string id of a user
     * @param string id of a studygroup
     *
     * @return boolean membership
     */
    public static function isMember($userid, $semid)
    {
        $sql = "SELECT 1 FROM `seminar_user` WHERE Seminar_id = ? AND user_id = ?";

        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($semid, $userid));

        return (bool)$stmt->fetchColumn();
    }

    /**
     * adds a founder to a given studygroup
     *
     * @param string username
     * @param string id of a studygroup
     *
     * @return void
     */
    public static function addFounder($username, $sem_id)
    {
        $stmt = DBManager::get()->prepare("INSERT IGNORE INTO seminar_user "
                                          . "(Seminar_id, user_id, status) VALUES (?, ?, 'dozent')");
        $stmt->execute(array($sem_id, get_userid($username)));
    }

    /**
     * removes a founder from a given studygroup
     *
     * @param string username
     * @param string id of a studygroup
     *
     * @return void
     */
    public static function removeFounder($username, $sem_id)
    {
        $stmt = DBManager::get()->prepare("DELETE FROM seminar_user "
                                          . "WHERE Seminar_id = ? AND user_id = ?");
        $stmt->execute(array($sem_id, get_userid($username)));
    }

    /**
     * get founders of a given studygroup
     *
     * @param string id of a studygroup
     *
     * @return array founders
     */
    public static function getFounders($sem_id)
    {
        $query = "SELECT username, perms, " . $GLOBALS['_fullname_sql']['full_rev'] . " as fullname FROM seminar_user "
                 . "LEFT JOIN auth_user_md5 USING (user_id) "
                 . "LEFT JOIN user_info USING (user_id) "
                 . "WHERE Seminar_id = ? AND status = 'dozent'";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array($sem_id));

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
    public static function getMembers($sem_id, $lower_bound = 1, $elements_per_page = NULL)
    {
        if (is_null($elements_per_page)) {
            $elements_per_page = get_config('ENTRIES_PER_PAGE');
        }

        $query = "SELECT user_id ,username ,perms, seminar_user.status, " . $GLOBALS['_fullname_sql']['full_rev']
                 . " as fullname, seminar_user.mkdate FROM seminar_user "
                 . "LEFT JOIN auth_user_md5 USING (user_id) "
                 . "LEFT JOIN user_info USING (user_id) "
                 . "WHERE Seminar_id = ? "
                 . "ORDER BY seminar_user.mkdate ASC, seminar_user.status ASC";

        if ($elements_per_page != 'all') {
            $query .= " LIMIT " . $lower_bound . "," . $elements_per_page;
        }

        return DBManager::get()->fetchGrouped($query, array($sem_id));
    }

    /**
     * invites a member to a given studygroup.
     *
     * @param string user id
     * @param string id of a studygroup
     *
     * @return void
     */
    public static function inviteMember($user_id, $sem_id)
    {
        $stmt = DBManager::get()->prepare("REPLACE INTO studygroup_invitations "
                                          . "(sem_id, user_id, mkdate) VALUES (?, ?, ?)");
        $stmt->execute(array($sem_id, $user_id, time()));
    }

    /**
     * cancels invitation.
     *
     * @param string username
     * @param string id of a studygroup
     *
     * @return void
     */
    public static function cancelInvitation($username, $sem_id)
    {
        $query     = "DELETE FROM studygroup_invitations
                  WHERE sem_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            $sem_id,
            get_userid($username)
        ));
    }

    /**
     * returns invited member of a given studygroup.
     *
     * @param string id of a studygroup
     *
     * @return array invited members
     */
    public static function getInvitations($sem_id)
    {
        $query = "SELECT username,user_id, " . $GLOBALS['_fullname_sql']['full_rev']
                 . " as fullname, studygroup_invitations.mkdate FROM studygroup_invitations "
                 . "LEFT JOIN auth_user_md5 USING (user_id) "
                 . "LEFT JOIN user_info USING (user_id) "
                 . "WHERE studygroup_invitations.sem_id = ? "
                 . "ORDER BY studygroup_invitations.mkdate ASC";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array($sem_id));

        return $stmt->fetchAll();
    }

    /**
     * checks if a user is already invited.
     *
     * @param string user id
     * @param string id of a studygroup
     *
     * @return array invited members
     */
    public static function isInvited($user_id, $sem_id)
    {
        $query = "SELECT count(*) as count FROM studygroup_invitations "
                 . "WHERE user_id = ? AND sem_id = ?";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array($user_id, $sem_id));
        $result = $stmt->fetch();

        return ($result['count'] != 0) ? true : false;
    }

    /**
     * callback function - used to compare sequences of studygroup statuses
     *
     * @param array status a
     * @param array status b
     *
     * return int ordering
     */
    public static function compare_status($a, $b)
    {
        if ($a['status'] == $b['status']) return strnatcmp($a['fullname'], $b['fullname']);
        elseif ($a['status'] == 'dozent') {
            if ($b['status'] == 'tutor') return -1;
            elseif ($b['status'] == 'autor') return -1;
        } elseif ($a['status'] == 'tutor') {
            if ($b['status'] == 'dozent') return +1;
            elseif ($b['status'] == 'autor') return -1;
        } elseif ($a['status'] == 'autor') {
            if ($b['status'] == 'tutor') return +1;
            elseif ($b['status'] == 'dozent') return +1;
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
        $sql  = "SELECT *
                FROM seminare
                WHERE Seminar_id = ? AND status IN (?)";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array(
            $sem_id,
            studygroup_sem_types()
        ));

        return $stmt->fetch();
    }

    /**
     * If a new user applies, an application note to all moderators and founders
     * of a studygroup will be automatically sent while calling this function.
     * The note contains the user's name and a direct link to the member page of the studygroup.
     *
     * @param string $sem_id id of a seminar / studygroup
     * @param strimg $user_id id of the applicant
     *
     * @return int                 number of recipients
     */
    public static function applicationNotice($sem_id, $user_id)
    {
        $sem        = new Seminar($sem_id);
        $dozenten   = $sem->getMembers();
        $tutors     = $sem->getMembers('tutor');
        $recipients = array();
        $msging     = new Messaging();

        foreach (array_merge($dozenten, $tutors) as $uid => $user) {
            $recipients[] = $user['username'];
        }

        if (mb_strlen($sem->getName()) > 32) //cut subject if to long
            $subject = sprintf(_("[Studiengruppe: %s...]"), mb_substr($sem->getName(), 0, 30));
        else
            $subject = sprintf(_("[Studiengruppe: %s]"), $sem->getName());

        if (StudygroupModel::isInvited($user_id, $sem_id)) {
            $subject .= " " . _("Einladung akzeptiert");
            $message = sprintf(_("%s hat die Einladung zur Studiengruppe %s akzeptiert. Klicken Sie auf den untenstehenden Link, um direkt zur Studiengruppe zu gelangen.\n\n [Direkt zur Studiengruppe]%s"),
                get_fullname($user_id), $sem->getName(), URLHelper::getlink($GLOBALS['ABSOLUTE_URI_STUDIP'] . "dispatch.php/course/studygroup/members/" . $sem->id, array('cid' => $sem->id)));
        } else {
            $subject .= " " . _("Neuer Mitgliedsantrag");
            $message = sprintf(_("%s möchte der Studiengruppe %s beitreten. Klicken Sie auf den untenstehenden Link, um direkt zur Studiengruppe zu gelangen.\n\n [Direkt zur Studiengruppe]%s"),
                get_fullname($user_id), $sem->getName(), URLHelper::getlink($GLOBALS['ABSOLUTE_URI_STUDIP'] . "dispatch.php/course/studygroup/members/" . $sem->id, array('cid' => $sem->id)));
        }

        return $msging->insert_message($message, $recipients, '', '', '', '1', '', $subject);
    }
}
