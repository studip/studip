<?php
namespace RESTAPI\Routes;

/**
 * This file contains API routes related to ResourcePermission
 * and ResourceTemporaryPermission objects.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class ResourcePermissions extends \RESTAPI\RouteMap
{

    //Methods for permanent permissions:


    /**
     * Get the permission levels of users for the specified resource.
     *
     * @param levels: Limit the result set to the specified permission levels.
     *     Allowed permission levels: user, autor, tutor, admin.
     *     The permission levels have to be comma separated like in the
     *     following example: "autor,tutor,admin".
     *
     * @get /resources/permissions/:resource_id
     */
    public function getResourcePermissions($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $levels_str = \Request::get('levels');
        $levels = [];
        if ($levels_str) {
            $levels = explode(',', $levels_str);
        }

        $sql = 'resource_id = :resource_id ';
        $sql_array = [
            'resource_id' => $resource->id
        ];

        if ($levels) {
            $sql .= 'AND perms IN ( :levels ) ';
            $sql_array['levels'] = $levels;
        }

        $permissions = \ResourcePermission::findBySql($sql, $sql_array);

        $result = [];
        if ($permissions) {
            foreach ($permissions as $permission) {
                $result[] = $permission->toRawArray();
            }
        }

        return $result;
    }


    /**
     * Returns the permissions a specific user has on a specified resource.
     *
     * @get /resources/permissions/:resource_id/:user:_id
     */
    public function getPermission($resource_id, $user_id)
    {
        if ($resource_id !== 'global') {
            if (!\Resource::exists($resource_id)) {
                $this->halt(
                    404,
                    'Resource not found!'
                );
                return;
            }
        }

        $user = \User::find($user_id);
        if (!$user) {
            $this->halt(
                400,
                'No user was provided!'
            );
            return;
        }

        $current_user = \User::findCurrent();

        if (!\ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            if ($resource_id != 'global') {
                $resource = \Resource::find($resource_id);
                $resource = $resource->getDerivedClassInstance();
                if (!$resource->userHasPermission($current_user, 'admin')) {
                    $this->halt(403);
                    return;
                }
            } else {
                //$resource_id == 'global': One must be admin
                //to perform this action!
                $this->halt(403);
                return;
            }
        }

        $permission = \ResourcePermission::findOneBySql(
            "resource_id = :resource_id AND user_id = :user_id",
            [
                'resource_id' => $resource_id,
                'user_id' => $user->id
            ]
        );

        if ($permission) {
            return $permission->toRawArray();
        } else {
            //The user already had no global permissions!
            return NULL;
        }
    }


    /**
     * @post /resources/permissions/:resource_id/:user_id
     */
    public function setPermission($resource_id, $user_id)
    {
        if ($resource_id !== 'global') {
            if (!\Resource::exists($resource_id)) {
                $this->halt(
                    404,
                    'Resource not found!'
                );
                return;
            }
        }

        $user = \User::find($user_id);
        if (!$user) {
            $this->halt(
                400,
                'No user was provided!'
            );
            return;
        }

        $current_user = \User::findCurrent();

        if (!\ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            if ($resource_id != 'global') {
                $resource = \Resource::find($resource_id);
                $resource = $resource->getDerivedClassInstance();
                if (!$resource->userHasPermission($current_user, 'admin')) {
                    $this->halt(403);
                    return;
                }
            } else {
                //$resource_id == 'global': One must be admin
                //to perform this action!
                $this->halt(403);
                return;
            }
        }

        //Verify permission level:
        $perms = \Request::get('perms');

        if (!in_array($perms, ['user', 'autor', 'tutor', 'admin'])) {
            $this->halt(
                400,
                'Invalid permission level specified!'
            );
            return;
        }

        //Check if permissions are already present for the user.
        //If not, create a new permission object.
        $permission = \ResourcePermission::findOneBySql(
            "resource_id = :resource_id AND user_id = :user_id",
            [
                'resource_id' => $resource_id,
                'user_id' => $user->id
            ]
        );

        if (!$permission) {
            $permission = new \ResourcePermission();
            $permission->resource_id = $resource_id;
            $permission->user_id = $user->id;
        }

        $permission->perms = $perms;

        if ($permission->isDirty()) {
            if ($permission->store()) {
                return $permission->toRawArray();
            } else {
                $this->halt(
                    500,
                    'Error while saving permissions!'
                );
            }
        }

        return $permission->toRawArray();
    }


    /**
     * @delete /resources/permissions/:resource_id/:user_id
     */
    public function deletePermission($resource_id, $user_id)
    {
        if ($resource_id !== 'global') {
            if (!\Resource::exists($resource_id)) {
                $this->halt(
                    404,
                    'Resource not found!'
                );
                return;
            }
        }

        $user = \User::find($user_id);
        if (!$user) {
            $this->halt(
                400,
                'No user was provided!'
            );
            return;
        }

        $current_user = \User::findCurrent();

        if (!\ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            if ($resource_id != 'global') {
                $resource = \Resource::find($resource_id);
                $resource = $resource->getDerivedClassInstance();
                if (!$resource->userHasPermission($current_user, 'admin')) {
                    $this->halt(403);
                    return;
                }
            } else {
                //$resource_id == 'global': One must be admin
                //to perform this action!
                $this->halt(403);
                return;
            }
        }

        $permission = \ResourcePermission::findOneBySql(
            "resource_id = :resource_id AND user_id = :user_id",
            [
                'resource_id' => $resource_id,
                'user_id' => $user->id
            ]
        );

        if (!$permission) {
            //The user already had no global permissions!
            return 'OK';
        }

        if ($permission->delete()) {
            return 'OK';
        } else {
            $this->halt(
                500,
                'Error while deleting global permissions!'
            );
        }
    }


    //Methods for temporary permissions:


    /**
     * Get the temporary permission levels of users for the specified resource.
     * The begin and end parameters are mandatory to determine a time range
     * to collect the temporary permissions in that range.
     *
     * @param begin: The begin timestamp of the time range.
     * @param end: The end timestamp of the time range.
     * @param levels: Limit the result set to the specified temporary permission
     *     levels. Allowed permission levels: user, autor, tutor, admin.
     *     The permission levels have to be comma separated like in the
     *     following example: "autor,tutor,admin".
     *
     * @get /resources/temporary_permissions/:resource_id
     */
    public function getTemporaryResourcePermissions($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $begin = \Request::get('begin');
        $end = \Request::get('end');
        $levels_str = \Request::get('levels');
        $levels = [];
        if ($levels_str) {
            $levels = explode(',', $levels_str);
        }

        if (!$begin or !$end) {
            //Use the current day:
            $begin = strtotime('today 0:00:00');
            $end = strtotime('today 23:59:59');
        }

        $sql = 'resource_id = :resource_id
               AND
               ((begin >= :begin AND begin <= :end)
               OR
               (end >= :begin AND end <= :end))
               OR
               (begin < :begin AND end > :end)';
        $sql_array = [
            'resource_id' => $resource->id,
            'begin' => $begin,
            'end' => $end
        ];

        if ($levels) {
            $sql .= 'AND perms IN ( :levels ) ';
            $sql_array['levels'] = $levels;
        }

        $permissions = \ResourceTemporaryPermission::findBySql(
            $sql,
            $sql_array
        );

        $result = [];
        if ($permissions) {
            foreach ($permissions as $permission) {
                $result[] = $permission->toRawArray();
            }
        }

        return $result;
    }


    /**
     * Returns the permissions a specific user has on a specified resource.
     *
     * @get /resources/temporary_permissions/:resource_id/:user:_id
     */
    public function getTemporaryPermission($resource_id, $user_id)
    {
        if ($resource_id !== 'global') {
            if (!\Resource::exists($resource_id)) {
                $this->halt(
                    404,
                    'Resource not found!'
                );
                return;
            }
        }

        $user = \User::find($user_id);
        if (!$user) {
            $this->halt(
                400,
                'No user was provided!'
            );
            return;
        }

        $current_user = \User::findCurrent();

        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $begin = null;
        $end = null;
        $with_time_range = false;
        if ($begin_str and $end_str) {
            $with_time_range = true;
            $begin = new \DateTime();
            $begin->setTimestamp($begin_str);
            $end = new \DateTime();
            $end->setTimestamp($end_str);
        }

        if (!\ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            if ($resource_id != 'global') {
                $resource = \Resource::find($resource_id);
                $resource = $resource->getDerivedClassInstance();
                if (!$resource->userHasPermission($current_user, 'admin')) {
                    $this->halt(403);
                    return;
                }
            } else {
                //$resource_id == 'global': One must be admin
                //to perform this action!
                $this->halt(403);
                return;
            }
        }

        $permissions = null;
        if ($with_time_range) {
            $permissions = \ResourceTemporaryPermission::findBySql(
                "resource_id = :resource_id AND user_id = :user_id
                AND (
                    (begin >= :begin AND begin <= :end)
                    OR
                    (end >= :begin AND end <= :end)
                )",
                [
                    'resource_id' => $resource_id,
                    'user_id' => $user->id,
                    'begin' => $begin->getTimestamp(),
                    'end' => $end->getTimestamp()
                ]
            );
        } else {
            $permissions = \ResourceTemporaryPermission::findBySql(
                "resource_id = :resource_id AND user_id = :user_id",
                [
                    'resource_id' => $resource_id,
                    'user_id' => $user->id
                ]
            );
        }

        if ($permissions) {
            $result = [];
            foreach ($permissions as $permission) {
                $result[] = $permission->toRawArray();
            }
            return $result;
        } else {
            //The user already had no global permissions!
            return NULL;
        }
    }


    /**
     * Sets temporary permissions for a user.
     *
     * @param begin The begin timestamp for the temporary permisssion.
     * @param end The end timestamp for the temporary permission.
     * @param perms The permission level for the temporary permission.
     *
     * @post /resources/temporary_permissions/:resource_id/:user_id
     */
    public function setTemporaryPermission($resource_id, $user_id)
    {
        if (!\Resource::exists($resource_id)) {
            $this->halt(
                404,
                'Resource not found!'
            );
            return;
        }

        $user = \User::find($user_id);
        if (!$user) {
            $this->halt(
                400,
                'No user was provided!'
            );
            return;
        }

        $current_user = \User::findCurrent();

        if (!\ResourceManager::userHasGlobalPermission($current_user, 'admin')
            and !$resource->userHasPermission($current_user, 'admin')) {
            $this->halt(403);
            return;
        }

        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $begin = null;
        $end = null;
        if (!$begin_str or !$end_str) {
            $this->halt(
                400,
                'No time range specified for temporary permission!'
            );
            return;
        }

        $begin = new \DateTime();
        $begin->setTimestamp($begin_str);
        $end = new \DateTime();
        $end->setTimestamp($end_str);

        //Verify permission level:
        $perms = \Request::get('perms');

        if (!in_array($perms, ['user', 'autor', 'tutor', 'admin'])) {
            $this->halt(
                400,
                'Invalid permission level specified!'
            );
            return;
        }

        //Check if permissions are already present for the user.
        //If not, create a new permission object.
        $permission = \ResourceTemporaryPermission::findOneBySql(
            "resource_id = :resource_id AND user_id = :user_id
            AND begin = :begin AND end = :end",
            [
                'resource_id' => $resource_id,
                'user_id' => $user->id,
                'begin' => $begin->getTimestamp(),
                'end' => $end->getTimestamp()
            ]
        );

        if (!$permission) {
            $permission = new \ResourceTemporaryPermission();
            $permission->resource_id = $resource_id;
            $permission->user_id = $user->id;
            $permission->begin = $begin->getTimestamp();
            $permission->end = $end->getTimestamp();
        }

        $permission->perms = $perms;

        if ($permission->isDirty()) {
            if ($permission->store()) {
                return $permission->toRawArray();
            } else {
                $this->halt(
                    500,
                    'Error while saving permissions!'
                );
            }
        }

        return $permission->toRawArray();
    }


    /**
     * Deletes all temporary permissions of a user.
     * If a time interval is given all permissions inside the interval
     * are deleted.
     *
     * @delete /resources/temporary_permissions/:resource_id/:user_id
     */
    public function deleteTemporaryPermission($resource_id, $user_id)
    {
        if ($resource_id !== 'global') {
            if (!\Resource::exists($resource_id)) {
                $this->halt(
                    404,
                    'Resource not found!'
                );
                return;
            }
        }

        $user = \User::find($user_id);
        if (!$user) {
            $this->halt(
                400,
                'No user was provided!'
            );
            return;
        }

        $current_user = \User::findCurrent();

        if (!\ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            if ($resource_id != 'global') {
                $resource = \Resource::find($resource_id);
                $resource = $resource->getDerivedClassInstance();
                if (!$resource->userHasPermission($current_user, 'admin')) {
                    $this->halt(403);
                    return;
                }
            } else {
                //$resource_id == 'global': One must be admin
                //to perform this action!
                $this->halt(403);
                return;
            }
        }

        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $begin = null;
        $end = null;
        $with_time_range = false;
        if ($begin_str and $end_str) {
            $with_time_range = true;
            $begin = new \DateTime();
            $begin->setTimestamp($begin_str);
            $end = new \DateTime();
            $end->setTimestamp($end_str);
        }

        if ($with_time_range) {
            \ResourceTemporaryPermission::deleteBySql(
                "resource_id = :resource_id AND user_id = :user_id
                AND (
                    (begin >= :begin AND end <= :end)
                )",
                [
                    'resource_id' => $resource_id,
                    'user_id' => $user->id,
                    'begin' => $begin->getTimestamp(),
                    'end' => $end->getTimestamp()
                ]
            );
        } else {
            \ResourceTemporaryPermission::deleteBySql(
                "resource_id = :resource_id AND user_id = :user_id",
                [
                    'resource_id' => $resource_id,
                    'user_id' => $user->id
                ]
            );
        }

        return 'OK';
    }
}
