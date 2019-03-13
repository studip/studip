<?
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * RolePersistence.class.php
 *
 * PHP version 5
 *
 * @author      Dennis Reil <dennis.reil@offis.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     pluginengine
 * @subpackage  db
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 */

/**
 * role id unknown
 */
define("UNKNOWN_ROLE_ID",-1);

/**
 * Funktionen für das Rollenmanagement
 * TODO: (mriehe) this is a static class, change the public function in static public functions
 *
 */
class RolePersistence
{

    const ROLES_CACHE_KEY = 'plugins/rolepersistence/roles';
    const ROLES_PLUGINS_CACHE_KEY = 'plugins/rolepersistence/roles_plugins/';

    private static $user_roles = array();

    /**
     * Enter description here...
     *
     * @return array Roles
     */
    public static function getAllRoles()
    {
        $cache = StudipCacheFactory::getCache();

        // read cache (unserializing a cache miss - FALSE - does not matter)
        $roles = unserialize($cache->read(self::ROLES_CACHE_KEY));

        // cache miss, retrieve from database
        if (!$roles) {
            $roles = array();
            $stmt = DBManager::get()->query("SELECT * FROM roles ORDER BY rolename");
            foreach ($stmt as $row) {
                $role = new Role();
                $role->setRoleid($row["roleid"]);
                $role->setRolename($row["rolename"]);
                $role->setSystemtype($row["system"] == 'y');
                $roles[$row["roleid"]] = $role;
            }

            // write to cache
            $cache->write(self::ROLES_CACHE_KEY, serialize($roles));
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
        StudipCacheFactory::getCache()->expire(self::ROLES_CACHE_KEY);
        self::$user_roles = array();

        $db = DBManager::get();

        // role is not in database
        if ($role->getRoleid() == UNKNOWN_ROLE_ID) {
            $stmt = $db->prepare("INSERT INTO roles (roleid, rolename) ".
                                 "values (0, ?)");
            $stmt->execute(array($role->getRolename()));
            $roleid = $db->lastInsertId();
            NotificationCenter::postNotification('RoleDidCreate', $roleid, $role->getRolename()); 
        }
        // role is already in database
        else {
            $roleid = $role->getRoleid();
            $stmt = $db->prepare("UPDATE roles SET rolename=? WHERE roleid=?");
            $stmt->execute(array($role->getRolename(), $roleid));
            NotificationCenter::postNotification('RoleDidUpdate', $roleid, $role->getRolename()); 

        }
        return $roleid;
    }

    /**
     * Delete role if not a permanent role. System roles cannot be deleted.
     *
     * @param unknown_type $role
     */
    public static function deleteRole($role)
    {
        $cache = StudipCacheFactory::getCache();
        $id = $role->getRoleid();
        $name = $role->getRolename();
        // sweep roles cache
        $cache->expire(self::ROLES_CACHE_KEY);
        self::$user_roles = array();

        $db = DBManager::get();
        $stmt = $db->prepare("DELETE FROM roles WHERE roleid=? AND system='n'");
        $stmt->execute(array($id));
        if ($stmt->rowCount())
        {
            $stmt = $db->prepare("SELECT pluginid FROM roles_plugins WHERE roleid=?");
            $stmt->execute(array($id));

            foreach ($stmt as $row) {
                $pluginid = $row['pluginid'];
                $cache->expire(self::ROLES_PLUGINS_CACHE_KEY . $pluginid);
            }

            $stmt = $db->prepare("DELETE FROM roles_user WHERE roleid=?");
            $stmt->execute(array($id));
            $stmt = $db->prepare("DELETE FROM roles_plugins WHERE roleid=?");
            $stmt->execute(array($id));
            $stmt = $db->prepare("DELETE FROM roles_studipperms WHERE roleid=?");
            $stmt->execute(array($id));
        }
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
        if ($role->getRoleid() <> UNKNOWN_ROLE_ID) {
            $roleid = self::saveRole($role);
        } else {
            $roleid = $role->getRoleid();
        }
        $stmt = DBManager::get()->prepare("REPLACE INTO roles_user ".
          "(roleid, userid, institut_id) VALUES (?, ?, ?)");
        $stmt->execute([$roleid, $user->id, $institut_id]);
        NotificationCenter::postNotification(
            'RoleAssignmentDidCreate',
            $roleid,
            $user->id,
            $institut_id
        ); 
        self::$user_roles = array();
    }

    /**
     * Gets all assigned roles from the database for a user
     *
     * @param int $userid
     * @param boolean $implicit
     * @return array
     */
    public static function getAssignedRoles($userid, $implicit = false)
    {
        $key = $userid . (int)$implicit;
        if (!array_key_exists($key, self::$user_roles)) {
            if ($implicit && is_object($GLOBALS['perm'])) {
                $global_perm = $GLOBALS['perm']->get_perm($userid);

                $stmt = DBManager::get()->prepare("SELECT DISTINCT r.roleid FROM roles_user r "
                      . "WHERE r.userid=? "
                      . "UNION "
                      . "SELECT rp.roleid FROM roles_studipperms rp WHERE rp.permname = ?");
                $stmt->execute(array($userid, $global_perm));
            } else {
                $stmt = DBManager::get()->prepare("SELECT DISTINCT r.roleid FROM roles_user r WHERE r.userid=?");
                $stmt->execute(array($userid));
            }
            self::$user_roles[$key] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return array_intersect_key(self::getAllRoles(), array_flip(self::$user_roles[$key]));
    }

    public static function getAssignedRoleInstitutes($user_id, $role_id)
    {
        return DBManager::get()->fetchFirst("SELECT institut_id FROM roles_user WHERE userid=? AND roleid=?", array($user_id, $role_id));
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
        if ($institut_id) {
            $faculty_id = Institute::find($institut_id)->fakultaets_id;
        }
        $assigned = DBManager::get()->fetchColumn("SELECT r.roleid FROM roles_user AS u 
                                               LEFT JOIN roles AS r ON r.roleid=u.roleid 
                                               WHERE u.userid=? AND r.rolename=? AND u.institut_id IN (?)", array($userid,$assignedrole,array((string)$institut_id,(string)$faculty_id)));
        return $assigned != '';
    }

    /**
     * Deletes a role assignment from the database
     *
     * @param User $user
     * @param Role $role
     */
    public static function deleteRoleAssignment(User $user,$role, $institut_id = null)
    {
        if ($institut_id === null) {
            $stmt = DBManager::get()->prepare("DELETE FROM roles_user WHERE roleid=? AND userid=?");
            $stmt->execute(array($role->getRoleid(),$user->id));
        } else {
            $stmt = DBManager::get()->prepare("DELETE FROM roles_user WHERE roleid=? AND userid=? AND institut_id=?");
            $stmt->execute(array($role->getRoleid(),$user->id,$institut_id));
        }
        NotificationCenter::postNotification(
            'RoleAssignmentDidDelete',
            $role->getRoleid(),
            $user->id,
            $institut_id
        ); 

        self::$user_roles = array();
    }

    /**
     * Get's all Role-Assignments for a certain user.
     * If no user is set, all role assignments are returned.
     *
     * @param User $user
     * @return array with roleids and the assigned userids
     */
    public static function getAllRoleAssignments($user = null)
    {
        if ($user == null) {
            $query = "SELECT role_id, user_id FROM roles_user";
            $result = DBManager::get()->query($query);
        } else {
            $query = "SELECT role_id, user_id FROM roles_user WHERE userid = ?";
            $result = DBManager::get()->prepare($query);
            $result->execute([$user->id]);
        }

        return $statement->fetchPairs();
    }

    /**
     * Enter description here...
     *
     * @param int $pluginid
     * @param array $roleids
     */
    public static function assignPluginRoles($pluginid,$roleids)
    {
        StudipCacheFactory::getCache()->expire(self::ROLES_PLUGINS_CACHE_KEY . (int) $pluginid);

        $stmt = DBManager::get()->prepare("REPLACE INTO roles_plugins (roleid, pluginid) VALUES (?, ?)");
        foreach ($roleids as $roleid) {
            $stmt->execute(array($roleid, $pluginid));
            NotificationCenter::postNotification('PluginRoleAssignmentDidCreate', $roleid, $pluginid); 

        }
    }

    /**
     * Enter description here...
     *
     * @param int $pluginid
     * @param array $roleids
     */
    public static function deleteAssignedPluginRoles($pluginid,$roleids)
    {
        StudipCacheFactory::getCache()->expire(self::ROLES_PLUGINS_CACHE_KEY . (int) $pluginid);

        $stmt = DBManager::get()->prepare("DELETE FROM roles_plugins WHERE roleid=? AND pluginid=?");
        foreach ($roleids as $roleid) {
            $stmt->execute(array($roleid, $pluginid));
            NotificationCenter::postNotification('PluginRoleAssignmentDidDelete', $roleid, $pluginid); 

        }
    }

    /**
     * Enter description here...
     *
     * @param int $pluginid
     * @return array
     */
    public static function getAssignedPluginRoles($pluginid=-1)
    {
        $cache = StudipCacheFactory::getCache();

        // read plugin roles from cache (unserialize does not matter on cache
        $key = self::ROLES_PLUGINS_CACHE_KEY . (int) $pluginid;
        $result = unserialize($cache->read($key));

        // cache miss, retrieve roles from database
        if (!$result) {

            $result = array();
            $roles = self::getAllRoles();

            $stmt = DBManager::get()->prepare("SELECT * FROM roles_plugins WHERE pluginid=?");
            $stmt->execute(array($pluginid));

            while ($row = $stmt->fetch()) {
                if (isset($roles[$row["roleid"]])) {
                    $result[] = $roles[$row["roleid"]];
                }
            }

            // write to cache
            $cache->write($key, serialize($result));
        }
        return $result;
    }
}
