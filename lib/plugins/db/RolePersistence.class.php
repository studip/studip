<?php
/**
 * RolePersistence.class.php
 *
 * Funktionen für das Rollenmanagement
 *
 * @author      Dennis Reil <dennis.reil@offis.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     pluginengine
 * @subpackage  db
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 */
class RolePersistence
{
    const ROLES_CACHE_KEY = 'plugins/rolepersistence/roles';
    const ROLES_PLUGINS_CACHE_KEY = 'plugins/rolepersistence/roles_plugins/';

    private static $user_roles = [];

    /**
     * Returns all available roles.
     *
     * @return array Roles
     */
    public static function getAllRoles()
    {
        // read cache
        $roles = self::readCache(null);

        // cache miss, retrieve from database
        if (!$roles) {
            $query = "SELECT `roleid`, `rolename`, `system` = 'y' AS `is_system`
                      FROM `roles`
                      ORDER BY `rolename`";
            $statement = DBManager::get()->query($query);
            $statement->setFetchMode(PDO::FETCH_ASSOC);

            $roles = [];
            foreach ($statement as $row) {
                extract($row);

                $roles[$roleid] = new Role($roleid, $rolename, $is_system);
            }

            // write to cache
            self::writeCache(null, $roles);
        }

        return $roles;
    }

    /**
     * Inserts the role into the database or does an update, if it's already there
     *
     * @param Role $role
     * @return the role id
     */
    public static function saveRole($role)
    {
        // sweep roles cache, see #getAllRoles
        self::expireCache(null);
        self::$user_roles = [];

        // role is not in database
        $query = "INSERT INTO `roles` (`roleid`, `rolename`, `system`)
                  VALUES (?, ?, 'n')
                  ON DUPLICATE KEY UPDATE `rolename` = VALUES(`rolename`)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$role->getRoleId(), $role->getRolename()]);

        if ($role->getRoleid() === Role::UNKNOWN_ROLE_ID) {
            $role_id = DBManager::get()->lastInsertId();
            $role->setRoleid($role_id);

            $event = 'RoleDidCreate';
        } else {
            $event = 'RoleDidUpdate';
        }

        NotificationCenter::postNotification(
            $event,
            $role->getRoleid(),
            $role->getRolename()
        );

        return $role->getRoleid();
    }

    /**
     * Delete role if not a permanent role. System roles cannot be deleted.
     *
     * @param Role $role
     */
    public static function deleteRole($role)
    {
        $id = $role->getRoleid();
        $name = $role->getRolename();

        // sweep roles cache
        self::expireCache(null);
        self::$user_roles = [];

        $query = "SELECT `pluginid` FROM `roles_plugins` WHERE `roleid` = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        $statement->setFetchMode(PDO::FETCH_COLUMN, 0);

        foreach ($statement as $plugin_id) {
            self::expireCache($plugin_id);
        }

        DBManager::get()->execute(
            "DELETE `roles`, `roles_user`, `roles_plugins`, `roles_studipperms`
             FROM `roles`
             LEFT JOIN `roles_user` USING (`roleid`)
             LEFT JOIN `roles_plugins` USING (`roleid`)
             LEFT JOIN `roles_studipperms` USING (`roleid`)
             WHERE `roleid` = ? AND `system` = 'n'",
            [$id]
        );

        NotificationCenter::postNotification('RoleDidDelete', $id, $name);
    }

    /**
     * Saves a role assignment to the database
     *
     * @param User $user
     * @param Role $role
     * @param string $institut_id
     */
    public static function assignRole(User $user, $role, $institut_id = '')
    {
        // role is not in database
        // save it to the database first
        if ($role->getRoleid() !== Role::UNKNOWN_ROLE_ID) {
            $roleid = self::saveRole($role);
        } else {
            $roleid = $role->getRoleid();
        }

        $query = "REPLACE INTO `roles_user` (`roleid`, `userid`, `institut_id`)
                  VALUES (?, ?, ?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$roleid, $user->id, $institut_id]);

        NotificationCenter::postNotification(
            'RoleAssignmentDidCreate',
            $roleid,
            $user->id,
            $institut_id
        );

        self::$user_roles = [];
    }

    /**
     * Gets all assigned roles from the database for a user
     *
     * @param int $userid
     * @param boolean $implicit
     * @return array
     */
    public static function getAssignedRoles($user_id, $implicit = false)
    {
        $key = $user_id . '/' . (int) $implicit;
        if (!array_key_exists($key, self::$user_roles)) {
            if ($implicit && is_object($GLOBALS['perm'])) {
                $global_perm = $GLOBALS['perm']->get_perm($user_id);

                $query = "SELECT DISTINCT `roleid`
                          FROM `roles_user`
                          WHERE `userid` = ?

                          UNION

                          SELECT `roleid`
                          FROM `roles_studipperms`
                          WHERE `permname` = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$user_id, $global_perm]);
            } else {
                $query = "SELECT DISTINCT `roleid`
                          FROM `roles_user`
                          WHERE `userid` = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$user_id]);
            }
            self::$user_roles[$key] = $statement->fetchAll(PDO::FETCH_COLUMN);
        }
        return array_intersect_key(self::getAllRoles(), array_flip(self::$user_roles[$key]));
    }

    /**
     * Returns institutes for which the given user has the given role.
     * @param  string $user_id User id
     * @param  int    $role_id Role id
     * @return array of institute ids
     */
    public static function getAssignedRoleInstitutes($user_id, $role_id)
    {
        return DBManager::get()->fetchFirst(
            "SELECT `institut_id` FROM `roles_user` WHERE `userid` = ? AND `roleid` = ?",
            [$user_id, $role_id]
        );
    }

    /**
     * Checks a role assignment for an user
     * optionally check for institute
     *
     * @param string $userid
     * @param string $assignedrole
     * @param string $institut_id
     * @return boolean
     */
    public static function isAssignedRole($userid, $assignedrole, $institut_id = '')
    {
        $faculty_id = $institut_id
                    ? Institute::find($institut_id)->fakultaets_id
                    : null;

        $query = "SELECT u.`userid` IS NOT NULL OR rsp.`roleid` IS NOT NULL
                  FROM `roles` AS r
                  -- Explicit assignment
                  LEFT JOIN `roles_user` AS u
                    ON r.`roleid` = u.`roleid`
                      AND u.`userid` = :user_id
                      AND u.`institut_id` IN (:institutes)
                  -- Implicit assignment
                  JOIN `auth_user_md5` AS a ON a.`user_id` = :user_id
                  LEFT JOIN `roles_studipperms` AS rsp
                    ON r.`roleid` = rsp.`roleid` AND a.`perms` = rsp.`permname`
                  WHERE r.`rolename` = :rolename";
        return (bool) DBManager::get()->fetchColumn($query, [
            ':user_id'    => $userid,
            ':rolename'   => $assignedrole,
            ':institutes' => [(string) $institut_id, (string) $faculty_id],
        ]);
    }

    /**
     * Deletes a role assignment from the database
     *
     * @param User   $user
     * @param Role   $role
     * @param String $institut_id
     */
    public static function deleteRoleAssignment(User $user, $role, $institut_id = null)
    {
        $query = "DELETE FROM `roles_user`
                  WHERE `roleid` = ?
                    AND `userid` = ?
                    AND `institut_id` = IFNULL(?, `institut_id`)";
        DBManager::get()->execute(
            $query,
            [$role->getRoleid(), $user->id, $institut_id]
        );

        NotificationCenter::postNotification(
            'RoleAssignmentDidDelete',
            $role->getRoleid(),
            $user->id,
            $institut_id
        );

        self::$user_roles = [];
    }

    /**
     * Get's all Role-Assignments for a certain user.
     * If no user is set, all role assignments are returned.
     *
     * @param User $user
     * @return array with roleids and the assigned userids
     * @deprecated seems to be unused (and was corrupt for some versions)
     */
    public static function getAllRoleAssignments($user = null)
    {
        $query = "SELECT `roleid`, `userid`
                  FROM `roles_user`
                  WHERE `userid` = IFNULL(?, `userid`)";
        return DBManager::get()->fetchPairs($query, [$user]);
    }

    /**
     * Enter description here...
     *
     * @param int $pluginid
     * @param array $roleids
     */
    public static function assignPluginRoles($plugin_id, $role_ids)
    {
        $plugin_id = (int) $plugin_id;

        self::expireCache($plugin_id);

        $query = "REPLACE INTO `roles_plugins` (`roleid`, `pluginid`)
                  VALUES (:role_id, :plugin_id)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);

        foreach ($role_ids as $role_id) {
            $statement->bindValue(':role_id', $role_id);
            $statement->execute();

            NotificationCenter::postNotification(
                'PluginRoleAssignmentDidCreate',
                $role_id,
                $plugin_id
            );

        }
    }

    /**
     * Removes the given roles' assignments from the given plugin.
     *
     * @param int $pluginid
     * @param array $roleids
     */
    public static function deleteAssignedPluginRoles($plugin_id, $role_ids)
    {
        $plugin_id = (int) $plugin_id;

        self::expireCache($plugin_id);

        $query = "DELETE FROM `roles_plugins`
                  WHERE `pluginid` = :plugin_id
                    AND `roleid` = :role_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);

        foreach ($role_ids as $role_id) {
            $statement->bindValue(':role_id', $role_id);
            $statement->execute();

            NotificationCenter::postNotification(
                'PluginRoleAssignmentDidDelete',
                $role_id,
                $plugin_id
            );

        }
    }

    /**
     * Return all roles assigned to a plugin.
     *
     * @param int $pluginid
     * @return array
     */
    public static function getAssignedPluginRoles($plugin_id)
    {
        $plugin_id = (int) $plugin_id;

        // read plugin roles from cache
        $result = self::readCache($plugin_id);

        // cache miss, retrieve roles from database
        if (!$result) {
            $result = [];
            $roles = self::getAllRoles();

            $query = "SELECT `roleid` FROM `roles_plugins` WHERE `pluginid` = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$plugin_id]);
            $statement->setFetchMode(PDO::FETCH_COLUMN, 0);

            foreach ($statement as $role_id) {
                if (isset($roles[$role_id])) {
                    $result[] = $roles[$role_id];
                }
            }

            // write to cache
            self::writeCache($plugin_id, $result);
        }

        return $result;
    }

    /**
     * Returns statistic values for each role:
     *
     * - number of explicitely assigned users
     * - number of implicitely assigned users
     * - number of assigned plugins
     *
     * @return array
     */
    public static function getStatistics()
    {
        // Get basic statistics
        $query = "SELECT r.`roleid`,
                         COUNT(DISTINCT ru.`userid`) AS explicit,
                         COUNT(DISTINCT rp.`pluginid`) AS plugins
                  FROM roles AS r
                  -- Explicit assignment
                  LEFT JOIN `roles_user` AS ru
                    ON r.`roleid` = ru.`roleid` AND ru.`userid` IN (SELECT `user_id` FROM `auth_user_md5`)
                  -- Plugins
                  LEFT JOIN `roles_plugins` AS rp
                    ON r.`roleid` = rp.`roleid` AND rp.`pluginid` IN (SELECT `pluginid` FROM `plugins`)
                  GROUP BY r.`roleid`";
        $result = DBManager::get()->fetchGrouped($query);

        // Fetch implicit assignments in a second query due to performance
        // reasons
        foreach (self::countImplicitUsers(array_keys($result)) as $id => $count) {
            $result[$id]['implicit'] = $count;
        }

        return $result;
    }

    /**
     * Counts the implicitely assigned users for a role.
     * @param  mixed $role_id Role id or array of role ids
     * @return mixed number of implictit for the role (if one role id is given)
     *               or associative array [role id => number of implicit users]
     *               when given a list of role ids
     */
    public static function countImplicitUsers($role_id)
    {
        // Ensure that the result array has an entry for every role id
        $result = array_fill_keys((array) $role_id, 0);

        $query = "SELECT rsp.`roleid`, COUNT(*) AS implicit
                  FROM `roles_studipperms` AS rsp
                  JOIN `auth_user_md5` AS a ON rsp.`permname` = a.`perms`
                  LEFT JOIN `roles_user` AS ru
                    ON a.`user_id` = ru.`userid` AND rsp.`roleid` = ru.`roleid`
                  WHERE rsp.`roleid` IN (?)
                    AND ru.`userid` IS NULL
                  GROUP BY rsp.`roleid`";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$role_id]);
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        foreach ($statement as $row) {
            $result[$row['roleid']] = (int) $row['implicit'];
        }

        return is_array($role_id)
             ? $result
             : $result[$role_id];
    }

    // Cache operations
    private static $cache = null;

    private static function getCache()
    {
        if (self::$cache === null) {
            self::$cache = StudipCacheFactory::getCache();
        }
        return self::$cache;
    }

    private static function getCacheKey($key = null)
    {
        return $key === null
             ? self::ROLES_CACHE_KEY
             : self::ROLES_PLUGINS_CACHE_KEY . $key;
    }

    private static function readCache($index = null)
    {
        $key = self::getCacheKey($index);
        $cached = self::getCache()->read($key);
        return $cached ? unserialize($cached) : false;
    }

    private static function writeCache($index = null, $value)
    {
        $key = self::getCacheKey($index);
        self::getCache()->write($key, serialize($value));
    }

    private static function expireCache($index = null)
    {
        $key = self::getCacheKey($index);
        self::getCache()->expire($key);
    }
}
