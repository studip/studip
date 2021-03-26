<?php

/**
 * room_group.php - contains Resources_RoomGroupController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2019
 * @category    Stud.IP
 * @since       4.5
 */


class Resources_RoomGroupController extends AuthenticatedController
{

    /**
     * @param array $room_ids
     * Loads the rooms and permissions and sets the $rooms, $common_permissions
     * and $partial_permissiosn attributes.
     */
    protected function loadRoomsAndPermissions(array $room_ids = [])
    {
        if (!count($room_ids)) {
            return null;
        }

        $this->rooms = Room::findBySql(
            "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE
            resources.id IN ( :room_ids )
            AND rc.class_name IN ( :room_class_names )
            ORDER BY resources.name ASC",
            [
                'room_ids' => $this->room_ids,
                'room_class_names' => RoomManager::getAllRoomClassNames()
            ]
        );

        //Load permissions for the specified rooms:

        $all_permissions = ResourcePermission::findBySql(
            'resource_id IN ( :room_ids )',
            [
                'room_ids' => $room_ids
            ]
        );

        //Now we sort the permissions to get all permissions which are
        //set for all the rooms and those who are set only for a part
        //of the rooms:

        //First we sort all permissions by the user (first level),
        //permission level (second level) and the permission object (third level).
        //First and second level use the user_id or the permission level resp.
        //as index.
        $user_permissions = [];
        if(!empty($all_permissions)) {
            foreach ($all_permissions as $permission) {
                if (!is_array($user_permissions[$permission->user_id])) {
                    $user_permissions[$permission->user_id] = [];
                }
        
                if (!is_array($user_permissions[$permission->user_id][$permission->perms])) {
                    $user_permissions[$permission->user_id][$permission->perms] = [];
                }
        
                $user_permissions[$permission->user_id][$permission->perms][] = $permission;
            }
        }

        //After that step we can check if the number of permissions for one user
        //and one permission level corresponds with the amount of rooms.
        //If so, we have found a permission which exists for all the rooms
        //in the room group.

        $this->common_permissions = [];
        $this->partial_permissions = [];
        if(!empty($user_permissions)) {
            foreach ($user_permissions as $user_id => $perm_level_data) {
                foreach ($perm_level_data as $level => $permissions) {
                    //If $permissions is an array with the same length as
                    //$this->rooms we have found a permission which is common
                    //for all the rooms:
                    if (count($permissions) == count($this->rooms)) {
                        $this->common_permissions[] = $permissions[0];
                    } else {
                        $this->partial_permissions = array_merge(
                            $this->partial_permissions,
                            $permissions
                        );
                    }
                }
            }
        }
    }


    public function permissions_action($selected_clipboard_id = null)
    {
        PageLayout::setTitle(
            _('Berechtigungen setzen')
        );

        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            if (Request::isDialog()) {
                PageLayout::postError(
                    _('Sie benötigen globale Admin-Rechte in der Raumverwaltung, um diese Aktion aufrufen zu können!')
                );
                return;
            } else {
                throw new AccessDeniedException(
                    _('Sie benötigen globale Admin-Rechte in der Raumverwaltung, um diese Aktion aufrufen zu können!')
                );
            }
        }

        if (!$selected_clipboard_id) {
            //Check if a clipboard is selected:
            $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        } else {
            $_SESSION['selected_clipboard_id'] = $selected_clipboard_id;
        }

        $clipboard = Clipboard::find($selected_clipboard_id);
        if (!$clipboard) {
            PageLayout::postError(
                _('Die gewählte Raumgruppe wurde nicht gefunden!')
            );
            return;
        }

        if ($clipboard->user_id != $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(
            $clipboard->name . ': ' . _('Berechtigungen setzen')
        );

        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        if ($selected_clipboard_item_ids) {
            $this->room_ids = $clipboard->getSomeRangeIds(
                'Room',
                $selected_clipboard_item_ids
            );
            if (!$this->room_ids) {
                PageLayout::postError(
                    _('Es wurden keine Räume ausgewählt!')
                );
                return;
            }
        } else {
            $this->room_ids = $clipboard->getAllRangeIds('Room');
            if (!$this->room_ids) {
                PageLayout::postInfo(
                    _('Die Raumgruppe enthält keine Räume!')
                );
                return;
            }
        }

        $this->rooms = Room::findMany($this->room_ids);

        $this->show_form = true;
        $this->clipboard = $clipboard;
        if (Request::submitted('bulk_delete')) {
            CSRFProtection::verifyUnsafeRequest();
            $selected_user_ids = Request::getArray('selected_permissions');

            $count = ResourcePermission::deleteBySql(
                'resource_id IN ( :room_ids ) AND user_id IN ( :user_ids )',
                [
                    'room_ids' => $this->room_ids,
                    'user_ids' => $selected_user_ids
                ]
            );
            if ($count > 0) {
                PageLayout::postSuccess(
                    _('Die gemeinsamen Berechtigungen wurden gelöscht!')
                );
            } else {
                PageLayout::postWarning(
                    _('Die gemeinsamen Berechtigungen konnten nicht gelöscht werden!')
                );
            }
        } elseif (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->loadRoomsAndPermissions($this->room_ids);

            //Filter out the entries without a user-ID
            //from the new common permissions:
            $new_common_permissions = Request::getArray('permissions');
            $new_common_permissions['user_id'] = array_filter(
                $new_common_permissions['user_id']
            );

            $old_common_permissions = $this->common_permissions;

            //An array for the permission objects that were either created
            //or updated:
            $handled_common_permissions = [];

            $errors = [];
            if ($new_common_permissions['user_id']) {
                foreach ($new_common_permissions['user_id'] as $key => $user_id) {
                    if (!$user_id) {
                        //An array entry without a user-ID
                        //must not be processed!
                        continue;
                    }
                    $user = User::find($user_id);
                    $permission = $new_common_permissions['level'][$key];
                    if (!in_array($permission, ['user', 'autor', 'tutor', 'admin'])) {
                        PageLayout::postError(
                            sprintf(
                                _('Es wurde eine ungültige Berechtigungsstufe angegeben (%s).'),
                                htmlReady($permission)
                            )
                        );
                        return;
                    }

                    //Loop through all rooms and set the user's permission
                    //according to the permission level.

                    foreach ($this->rooms as $room) {
                        $permission_object = ResourcePermission::findOneBySql(
                            'resource_id = :room_id
                            AND
                            user_id = :user_id',
                            [
                                'room_id' => $room->id,
                                'user_id' => $user_id
                            ]
                        );

                        if (!$permission_object) {
                            //Create a new permission object:
                            $permission_object = new ResourcePermission();
                            $permission_object->resource_id = $room->id;
                            $permission_object->user_id = $user_id;
                        }

                        $permission_object->perms = $permission;

                        if ($permission_object->isDirty()) {
                            if (!$permission_object->store()) {
                                $errors[] = sprintf(
                                    _('Die Berechtigungen von %1$s am Raum %2$s konnten nicht gespeichert werden!'),
                                    htmlReady($user->getFullName()),
                                    htmlReady($this->resource->name)
                                );
                            } else {
                                $handled_common_permissions[] = $permission_object->id;
                            }
                        } else {
                            $handled_common_permissions[] = $permission_object->id;
                        }
                    }
                }
            } else {
                //No new common permissions exist. This means that all common
                //permissions can be erased for all rooms:
                foreach ($old_common_permissions as $p) {
                    ResourcePermission::deleteBySql(
                        'resource_id IN ( :room_ids ) AND user_id = :user_id',
                        [
                            'room_ids' => $this->room_ids,
                            'user_id' => $p->user_id
                        ]
                    );
                }
            }

            if ($handled_common_permissions) {
                foreach ($this->common_permissions as $p) {
                    if (!in_array($p->id, $handled_common_permissions)) {
                        //The ID of the common permission object is not in the
                        //array of handled permissions. This means that the
                        //permission has been revoked for all rooms:
                        ResourcePermission::deleteBySql(
                            'resource_id IN ( :room_ids ) AND user_id = :user_id',
                            [
                                'room_ids' => $this->room_ids,
                                'user_id' => $p->user_id
                            ]
                        );
                    }
                }
            }

            if ($errors) {
                PageLayout::postError(
                    _('Die folgenden Fehler traten auf:'),
                    $errors
                );
            } else {
                PageLayout::postSuccess(
                    _('Die Berechtigungen wurden gespeichert!')
                );
            }
        }

        $this->user_search = QuickSearch::get(
            'user_id',
            new StandardSearch('user_id')
        );

        $this->user_search->fireJSFunctionOnSelect(
            "function (user_id) {
                 var table = jQuery('#RoomGroupCommonPermissionTable')[0];
                 STUDIP.Resources.addUserToPermissionList(user_id, table);
            }"
        );

        $this->loadRoomsAndPermissions($this->room_ids);

        //We must build the permission_room_list:
        $this->permission_room_list = [];
        foreach ($this->partial_permissions as $permission) {
            $room = $permission->resource->getDerivedClassInstance();
            $this->permission_room_list[$permission->id] = $room->getFullName();
        }
    }
}
