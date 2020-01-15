<?php

/**
 * ResourcePermission.class.php - model class for resource permissions.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 *
 * @property string id database column
 * @property string user_id database column
 * @property string resource_id database column
 * @property string perms database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property User user belongs_to User
 * @property Resource resource belongs_to Resource
 */


/**
 * Description of the resources permission system:
 *  - admin: An admin may do everything in the resource management:
 *    edit resource bookings and resources.
 *  - tutor: A tutor may edit all resource bookings.
 *  - autor: An autor may edit his own resource bookings only.
 *  - user: A user may read internal comments on resource bookings.
 */
class ResourcePermission extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_permissions';

        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_func' => 'find'
        ];

        $config['belongs_to']['resource'] = [
            'class_name' => 'Resource',
            'foreign_key' => 'resource_id',
            'assoc_func' => 'find'
        ];

        $config['registered_callbacks']['before_store'][] = 'cbLogChanges';
        $config['registered_callbacks']['before_delete'][] = 'cbLogDeletion';

        parent::configure($config);
    }

    /**
     * @inheritDoc
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $user = User::find($storage->user_id);
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
            _('Berechtigungen an Ressourcen'),
            self::$config['db_table'],
            $rows,
            $user
        );
    }


    /**
     * This is a callback method to create an entry in the Stud.IP log
     * when a ResourcePermission object is stored.
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
                        _('Globale Berechtigungen für %1$s (Rechtestufe %2$s) hinzugefügt.'),
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
                        _('%1$s: Hinzufügen von Berechtigungen für %2$s (Rechtestufe %3$s).'),
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
                            _('Globale Berechtigungen für %1$s von %2$s auf %3$s geändert.'),
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
                            _('%1$s: Änderung der Berechtigungen für %2$s von %3$s auf %4$s.'),
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
     * when a ResourcePermission object is deleted.
     */
    public function cbLogDeletion()
    {
        if ($this->resource_id == 'global') {
            StudipLog::log(
                'RES_PERM_CHANGE',
                $this->resource_id,
                $this->user_id,
                sprintf(
                    _('Globale Berechtigungen für %1$s (Rechtestufe %2$s) gelöscht.'),
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
                    _('%1$s: Löschen der Berechtigungen für %2$s (Rechtestufe %3$s).'),
                    $this->resource->getFullName(),
                    $this->user->username,
                    $this->perms
                )
            );
        }
    }
}
