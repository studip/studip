<?php

/**
 * ResourceTemporaryPermission.class.php
 * Contains the ResourceTemporaryPermission class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 */


/**
 * The ResourceTemporaryPermission class represents temporary permissions
 * granted to a user for a resource.
 *
 * @property string id database column
 * @property string resource_id database column
 * @property string user_id database column
 * @property string begin database column
 * @property string end database column
 * @property string perms database column: The permission level granted
 *     in the specified time range.
 * @property string mkdate database column
 * @property string chdate database column
 * @property Resource resource belongs_to Resource
 */
class ResourceTemporaryPermission extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_temporary_permissions';
        
        $config['belongs_to']['resource'] = [
            'class_name'  => 'Resource',
            'foreign_key' => 'resource_id',
            'assoc_func'  => 'find'
        ];
        
        $config['belongs_to']['user'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
            'assoc_func'  => 'find'
        ];
        
        $config['registered_callbacks']['before_store'][]  = 'cbLogChanges';
        $config['registered_callbacks']['before_delete'][] = 'cbLogDeletion';
        
        parent::configure($config);
    }
    
    /**
     * @inheritDoc
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $user        = User::find($storage->user_id);
        $permissions = self::findBySql(
            'user_id = :user_id ORDER BY mkdate',
            [
                'user_id' => $storage->user_id
            ]
        );
        
        $rows = [];
        foreach ($permissions as $permission) {
            $rows[] = $permission->toRawArray();
        }
        
        $storage->addTabularData(
            _('Temporäre Berechtigungen an Ressourcen'),
            self::$config['db_table'],
            $rows,
            $user
        );
    }
    
    /**
     * Returns the current permission a user has for a resource.
     *
     * @param User $user The user whose permission shall be retrieved.
     * @param string $resource_id The resource where the user's permission
     *     shall be retrieved.
     *
     * @return string The permission level as string or an empty string, if no
     *    temporary permission exists for the specified user on the
     *    specified resource.
     */
    public static function getCurrentTemporaryPermissions(User $user, string $resource_id)
    {
        $perm = self::findOneBySql(
            'resource_id = :resource_id
            AND
            user_id = :user_id',
            [
                'resource_id' => $resource_id,
                'user_id'     => $user->id
            ]
        );
        if ($perm) {
            return $perm->perm;
        }
        return '';
    }
    
    
    public static function userHasPermissionInTimeRange(
        User $user,
        string $resource_id,
        DateTime $begin,
        DateTime $end
    )
    {
        //Query explaination: We want exactly one permission object
        //for the specified user and the resource.
        //The permission must exist during the whole specified time range
        //and therefore the begin and end of the permission must either
        //meet the time range exactly or it must start earlier and end
        //later than the time range. The permission level is checked afterwards
        //if a permission object can be found.
        $perm = self::findOneBySql(
            'user_id = :user_id
            AND
            resource_id = :resource_id
            AND
            (begin <= :begin AND end >= :end)',
            [
                'user_id'     => $user->id,
                'resource_id' => $resource_id,
                'begin'       => $begin->getTimestamp(),
                'end'         => $end->getTimestamp()
            ]
        );
        
        if (!$perm) {
            //If no permission object can be found the user obviously
            //doesn't have the requested permissions.
            return false;
        }
        
        return ResourceManager::comparePermissionLevels($perm->perm, $perm) >= 0;
    }
    
    
    /**
     * This is a callback method to create an entry in the Stud.IP log
     * when a ResourceTemporaryPermission object is stored.
     */
    public function cbLogChanges()
    {
        if ($this->isNew()) {
            //Insert
            if ($this->resource_id == 'global') {
                //Global permissions
                StudipLog::log(
                    'RES_PERM_CHANGE',
                    $this->resource_id,
                    $this->user_id,
                    sprintf(
                        _('Globale temporäre Berechtigungen für %1$s (Rechtestufe %2$s) hinzugefügt.'),
                        $this->user->username,
                        $this->perms
                    )
                );
                
            } elseif ($this->resource_id) {
                //Resource-specific permissions
                StudipLog::log(
                    'RES_PERM_CHANGE',
                    $this->resource_id,
                    $this->user_id,
                    sprintf(
                        _('%1$s: Hinzufügen von temporären Berechtigungen für %2$s (Rechtestufe %3$s).'),
                        $this->resource->getDerivedClassInstance()->getFullName(),
                        $this->user->username,
                        $this->perms
                    )
                );
            } else {
                throw new ResourcePermissionException(
                    _('Berechtigungen müssen mit bestimmten Ressourcen verknüpft sein, bevor sie gespeichert werden!')
                );
            }
        } else {
            //Update?
            if ($this->content_db['perms'] != $this->perms) {
                //Update!
                if ($this->resource_id == 'global') {
                    StudipLog::log(
                        'RES_PERM_CHANGE',
                        $this->resource_id,
                        $this->user_id,
                        sprintf(
                            _('Globale temporäre Berechtigungen für %1$s von %2$s auf %3$s geändert.'),
                            $this->user->username,
                            $this->content_db['perms'],
                            $this->perms
                        )
                    );
                } else {
                    StudipLog::log(
                        'RES_PERM_CHANGE',
                        $this->resource_id,
                        $this->user_id,
                        sprintf(
                            _('%1$s: Änderung der temporären Berechtigungen für %2$s von %3$s auf %4$s.'),
                            $this->resource->getFullName(),
                            $this->user->username,
                            $this->content_db['perms'],
                            $this->perms
                        )
                    );
                }
            }
        }
    }
    
    /**
     * This is a callback method to create an entry in the Stud.IP log
     * when a ResourceTemporaryPermission object is deleted.
     */
    public function cbLogDeletion()
    {
        if ($this->resource_id == 'global') {
            StudipLog::log(
                'RES_PERM_CHANGE',
                $this->resource_id,
                $this->user_id,
                sprintf(
                    _('Globale temporäre Berechtigungen für %1$s (Rechtestufe %2$s) gelöscht.'),
                    $this->user->username,
                    $this->perms
                )
            );
        } else {
            StudipLog::log(
                'RES_PERM_CHANGE',
                $this->resource_id,
                $this->user_id,
                sprintf(
                    _('%1$s: Löschen der temporären Berechtigungen für %2$s (Rechtestufe %3$s).'),
                    $this->resource->getFullName(),
                    $this->user->username,
                    $this->perms
                )
            );
        }
    }
}
