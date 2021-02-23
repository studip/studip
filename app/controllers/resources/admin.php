<?php

/**
 * admin.php - contains Resources_AdminController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2018
 * @category    Stud.IP
 * @since       TODO
 */


/**
 * Resources_AdminController contains actions
 * for the global resource administration.
 */
class Resources_AdminController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->sidebar = Sidebar::get();
        $this->current_user = User::findCurrent();
        if (!ResourceManager::userHasGlobalPermission($this->current_user, 'admin')
            && !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
    }

    public function permissions_action($resource_id = null)
    {
        if (Navigation::hasItem('/resources/admin/permissions')) {
            Navigation::activateItem('/resources/admin/permissions');
        }

        //The relayed controller needs a resource object or the flag
        //that global permissions shall be edited.
        //Furthermore it needs the attribute resource_id.
        if ($resource_id == 'global') {
            $this->edit_global_permissions = true;
        } else {
            $this->resource = Resource::find($resource_id);
            if (!$this->resource) {
                PageLayout::postError(
                    _('Die angegebene Ressource wurde nicht gefunden!')
                );
                return;
            }
            if (!$this->resource->userHasPermission($this->current_user, 'admin')
                && !$GLOBALS['perm']->have_perm('root')) {
                throw new AccessDeniedException();
            }
        }
        $this->resource_id = $resource_id;
        $response          = $this->relay('resources/resource/permissions/' . $resource_id);

        //We must replace all paths directing to the original controller
        //with the path to this controller so that the click on the "save"
        //button in the relayed controller links to this controller.
        $this->other_controllers_html = str_replace(
            'dispatch.php/resources/resource/permissions',
            'dispatch.php/resources/admin/permissions',
            $response->body
        );
    }


    public function global_locks_action()
    {
        if (Navigation::hasItem('/resources/admin/global_locks')) {
            Navigation::activateItem('/resources/admin/global_locks');
        }

        PageLayout::setTitle(
            _('Globale Sperren verwalten')
        );

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Sperrung hinzufügen'),
            $this->url_for('resources/global_locks/add'),
            Icon::create('add'),
            [
                'data-dialog' => 'size=auto'
            ]
        );
        $this->sidebar->addWidget($actions);

        $now = time();

        $this->current_lock = GlobalResourceLock::findOneBySql(
            'begin <= :now AND end >= :now',
            [
                'now' => $now
            ]
        );

        $this->future_locks = GlobalResourceLock::findBySql(
            '(begin > :now) OR (end > :now)
            ORDER BY begin ASC, end ASC',
            [
                'now' => $now
            ]
        );
    }


    public function user_permissions_action()
    {
        if (Navigation::hasItem('/resources/admin/user_permissions')) {
            Navigation::activateItem('/resources/admin/user_permissions');
        }

        PageLayout::setTitle(
            _('Berechtigungs-Übersicht')
        );

        $user_search = new SearchWidget(
            $this->url_for('resources/admin/user_permissions')
        );
        $user_search->addNeedle(
            _('Suche nach einer Person'),
            'user_id',
            _('Name oder Nutzername der Person'),
            new StandardSearch('user_id'),
            'function(){jQuery(this).closest("form").submit();}'
        );

        $this->sidebar->addWidget($user_search);

        $user_id    = Request::get('user_id');
        $this->user = User::find($user_id);
        if ($this->user) {
            PageLayout::setTitle(
                sprintf(
                    _('Berechtigungen für %s'),
                    $this->user->getFullName()
                ) . sprintf(
                    ' (%1$s) (%2$s)',
                    $this->user->username,
                    $this->user->perms
                )
            );
            if (GlobalResourceLock::currentlyLocked()) {
                $this->current_global_lock = true;
            }

            //get the permissions of that user:

            $this->global_permission = ResourcePermission::findOneBySql(
                "user_id = :user_id AND resource_id = 'global'",
                [
                    'user_id' => $this->user->id
                ]
            );

            $this->permissions = ResourcePermission::findBySql(
                'INNER JOIN resources
                ON resource_permissions.resource_id = resources.id
                WHERE
                user_id = :user_id
                ORDER BY resources.name ASC, resource_permissions.mkdate ASC',
                [
                    'user_id' => $this->user->id
                ]
            );

            $this->temporary_permissions = ResourceTemporaryPermission::findBySql(
                'INNER JOIN resources
                ON resource_temporary_permissions.resource_id = resources.id
                WHERE
                user_id = :user_id
                ORDER BY resources.name ASC, resource_temporary_permissions.mkdate ASC',
                [
                    'user_id' => $this->user->id
                ]
            );

            //Get last activity:
            $this->now = new DateTime();

            $this->last_activity = ResourceManager::getUserInactivityInterval(
                $this->user,
                $this->now
            );

            if ($this->last_activity instanceof DateInterval) {
                //Calculate the date of the last inactivity:
                $last_activity_date = $this->now->sub($this->last_activity);
                $this->last_activity_date = $last_activity_date->format('d.m.Y H:i');
            }
        }
    }

    public function booking_log_action($user_id = null, $resource_id = null)
    {
        $this->show_all_records = Request::get('show_all_records');

        $this->user = User::find($user_id);
        if (!$this->user) {
            PageLayout::postError(
                _('Die angegebene Person wurde nicht gefunden!')
            );
            return;
        }

        $this->resource = null;
        if ($resource_id) {
            $this->resource = Resource::find($resource_id);
            if (!$this->resource) {
                PageLayout::postError(
                    _('Die angegebene Ressource wurde nicht gefunden!')
                );
                return;
            }

            $this->resource = $this->resource->getDerivedClassInstance();
        }

        //Get bookings:

        $this->bookings = null;
        if ($this->resource) {
            $this->bookings = ResourceBooking::findBySql(
                'range_id = :user_id AND resource_id = :resource_id '
                . (!$this->show_all_records
                    ? 'AND begin > (UNIX_TIMESTAMP() - 86400) '
                    : ''
                ) . 'ORDER BY begin ASC, end ASC',
                [
                    'user_id'     => $this->user->id,
                    'resource_id' => $this->resource->id
                ]
            );
        } else {
            $this->bookings = ResourceBooking::findBySql(
                'range_id = :user_id '
                . (!$this->show_all_records
                    ? 'AND begin > (UNIX_TIMESTAMP() - 86400) '
                    : ''
                ) . 'ORDER BY begin ASC, end ASC',
                [
                    'user_id' => $this->user->id
                ]
            );
        }
    }

    public function categories_action()
    {
        if (!ResourceManager::userHasGlobalPermission($this->current_user, 'admin')) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(
            _('Kategorien verwalten')
        );

        if (Navigation::hasItem('/resources/admin/categories')) {
            Navigation::activateItem('/resources/admin/categories');
        }

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neue Kategorie'),
            URLHelper::getURL(
                'dispatch.php/resources/category/add'
            ),
            Icon::create('add'),
            ['data-dialog' => 'size=auto;reload-on-close']
        );

        $this->sidebar->addWidget($actions);

        $this->categories = ResourceCategory::findAll();

        if (!$this->categories) {
            PageLayout::postInfo(
                _('Es wurden keine Kategorien gefunden!')
            );
        }
    }

    public function properties_action()
    {
        if (Navigation::hasItem('/resources/admin/properties')) {
            Navigation::activateItem('/resources/admin/properties');
        }

        PageLayout::setTitle(
            _('Eigenschaften verwalten')
        );

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Eigenschaft hinzufügen'),
            $this->url_for('resources/property/add'),
            Icon::create('add'),
            [
                'data-dialog' => 'size=auto'
            ]
        );

        $this->sidebar->addWidget($actions);


        //Get all properties:
        $this->properties = ResourcePropertyDefinition::findBySql(
            'TRUE
            GROUP BY property_id
            ORDER BY name ASC, type ASC, mkdate ASC'
        );

        //Get the categories where the properties are used.
        $this->categories = [];
        if (is_array($this->properties)) {
            $db = DBManager::get();

            $stmt = $db->prepare(
                "SELECT DISTINCT rc.name AS name
                FROM resource_category_properties rcp
                INNER JOIN resource_categories rc
                ON rcp.category_id = rc.id
                WHERE
                rcp.property_id = :property_id
                ORDER BY name ASC"
            );

            foreach ($this->properties as $property) {
                $stmt->execute(
                    [
                        'property_id' => $property->id
                    ]
                );

                $this->categories[$property->id] = $stmt->fetchAll(
                    PDO::FETCH_COLUMN,
                    0
                );
            }
        }
    }


    public function property_groups_action()
    {
        if (Navigation::hasItem('/resources/admin/property_groups')) {
            Navigation::activateItem('/resources/admin/property_groups');
        }

        PageLayout::setTitle(_('Eigenschaftsgruppen verwalten'));

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            //Fields from the first table:
            $this->selected_groups           = Request::getArray('selected_groups');
            $this->selected_group_properties = Request::getArray('selected_group_properties');
            //Fields from the second table:
            $this->new_group_name      = Request::get('new_group_name');
            $this->selected_properties = Request::getArray('selected_properties');
            $this->property_move       = Request::getArray('property_move');
            $this->group_position      = Request::getArray('group_position');
            $this->edited_group_names  = Request::getArray('edited_group_names');
            $this->property_position   = Request::getArray('property_position');

            $property_object_cache = [];
            $group_object_cache    = [];

            //Process fields from the first table:
            if ($this->selected_group_properties) {
                foreach ($this->selected_group_properties as $group_properties) {
                    foreach ($group_properties as $property_id) {
                        $property = ResourcePropertyDefinition::find($property_id);
                        if (!$property) {
                            //Invalid / non-existant property.
                            continue;
                        }
                        $property_object_cache[$property->id] = $property;
                        $property->property_group_id          = '';
                        $property->property_group_pos         = '0';
                    }
                }
            }
            if ($this->selected_groups) {
                ResourcePropertyGroup::deleteBySql(
                    'id IN ( :group_ids )',
                    [
                        'group_ids' => $this->selected_groups
                    ]
                );
            }

            //Process fields from the second table:
            if ($this->new_group_name) {
                $group           = new ResourcePropertyGroup();
                $group->name     = $this->new_group_name;
                $group->position = '0';
                if ($group->store()) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Die neue Eigenschaftsgruppe mit dem Namen %s wurde angelegt!'),
                            htmlReady($group->name)
                        )
                    );
                    $this->new_group_name = '';
                } else {
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Anlegen der Eigenschaftsgruppe %s!'),
                            htmlReady($group->name)
                        )
                    );
                }

                if ($this->selected_properties) {
                    foreach ($this->selected_properties as $property_id) {
                        $property = ResourcePropertyDefinition::find($property_id);
                        if (!$property) {
                            //Invalid / non-existing property.
                            continue;
                        }

                        $property_object_cache[$property->id] = $property;

                        $property->property_group_id = $group->id;
                        if ($property->property_group_pos == '') {
                            $property->property_group_pos = '0';
                        }
                    }
                }
            }

            if ($this->property_move) {
                //At least one property is selected for moving into another
                //property group.
                foreach ($this->property_move as $property_id => $group_id) {
                    $property = $property_object_cache[$property_id];
                    if (!$property) {
                        $property = ResourcePropertyDefinition::find($property_id);
                    }
                    if (!$property) {
                        continue;
                    }
                    $property_object_cache[$property->id] = $property;

                    if ($group_id) {
                        $group = $group_object_cache[$group_id];
                        if (!$group) {
                            $group = ResourcePropertyGroup::find($group_id);
                        }
                        if (!$group) {
                            continue;
                        }
                        $group_object_cache[$group->id] = $group;

                        $property->property_group_id = $group->id;
                        if ($property->property_group_pos == '') {
                            $property->property_group_pos = '0';
                        }
                    }
                }
            }

            if ($this->group_position) {
                foreach ($this->group_position as $group_id => $position) {
                    $group = $group_object_cache[$group_id];
                    if (!$group) {
                        $group = ResourcePropertyGroup::find($group_id);
                    }
                    if (!$group) {
                        //Invalid / non-existing group.
                        continue;
                    }
                    $group_object_cache[$group_id] = $group;

                    $group->position = $position;
                }
            }

            if ($this->edited_group_names) {
                foreach ($this->edited_group_names as $group_id => $new_name) {
                    $group = $group_object_cache[$group_id];
                    if (!$group) {
                        $group = ResourcePropertyGroup::find($group_id);
                    }
                    if (!$group) {
                        //Invalid / non-existing group.
                        continue;
                    }
                    $group_object_cache[$group_id] = $group;
                    if ($group->name != $new_name) {
                        $group->name = $new_name;
                    }
                }
            }

            if ($this->property_position) {
                foreach ($this->property_position as $property_id => $position) {
                    $property = $property_object_cache[$property_id];
                    if (!$property) {
                        $property = ResourcePropertyDefinition::find($property_id);
                    }
                    if (!$property) {
                        continue;
                    }
                    $property_object_cache[$property->id] = $property;

                    //Make sure the position is a number:
                    $position = intval($position);

                    $property->property_group_pos = $position;
                }
            }

            foreach ($group_object_cache as $group) {
                if ($group->isDirty()) {
                    $group->store();
                }
            }

            foreach ($property_object_cache as $property) {
                if ($property->isDirty()) {
                    $property->store();
                }
            }
        }

        $this->property_groups = ResourcePropertyGroup::findBySql(
            'TRUE ORDER BY position, name ASC'
        );

        $this->ungrouped_properties = ResourcePropertyDefinition::findBySql(
            "property_group_id IS NULL OR property_group_id = ''
            ORDER BY name ASC, type ASC"
        );
    }


    protected function deleteSeparableRoomsById($separable_room_ids = [])
    {
        if (!is_array($separable_room_ids)) {
            return;
        }

        if (count($separable_room_ids) == 1) {
            //Only one separable room to delete.
            $separable_room = SeparableRoom::find($separable_room_ids[0]);

            if ($separable_room) {
                if ($separable_room->delete()) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Der teilbare Raum %s wurde gelöscht!'),
                            htmlReady($separable_room->name)
                        )
                    );
                } else {
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Löschen des teilbaren Raumes %s!'),
                            htmlReady($separable_room->name)
                        )
                    );
                }
            } else {
                PageLayout::postError(
                    _('Der gewählte teilbare Raum wurde nicht gefunden!')
                );
            }
        } else {
            //More than one separable room to delete.

            $errors          = [];
            $rooms_not_found = 0;
            foreach ($separable_room_ids as $separable_room_id) {
                $separable_room = SeparableRoom::find($separable_room_id);

                if ($separable_room) {
                    if (!$separable_room->delete()) {
                        $errors[] = sprintf(
                            _('Fehler beim Löschen des teilbaren Raumes %s!'),
                            htmlReady($separable_room->name)
                        );
                    }
                } else {
                    $rooms_not_found++;
                }
            }

            if ($rooms_not_found > 0) {
                //Add an error message on top of the other error messages.
                array_unshift(
                    $errors,
                    sprintf(
                        _('%d teilbare Räume wurden nicht gefunden!'),
                        $rooms_not_found
                    )
                );
            }

            if ($errors) {
                PageLayout::postError(
                    ngettext(
                        'Der folgende Fehler trat beim Löschen mehrerer teilbarer Räume auf:',
                        'Die folgenden Fehler traten beim Löschen mehrerer teilbarer Räume auf:',
                        count($errors)
                    ),
                    $errors
                );
            }
        }
    }


    protected function deleteSeparableRoomPartsById($room_part_ids = [])
    {
        if (!is_array($room_part_ids)) {
            return;
        }

        if (count($room_part_ids) == 1) {
            $separable_room_part = SeparableRoomPart::find(
                explode('_', $room_part_ids[0])
            );
            if ($separable_room_part) {
                $room           = $separable_room_part->room;
                $separable_room = $separable_room_part->separable_room;
                if ($separable_room_part->delete()) {
                    //Check if the separable room as any parts left:
                    if ($separable_room) {
                        if (count($separable_room->parts) == 0) {
                            $separable_room->delete();
                        }
                    }
                    PageLayout::postSuccess(
                        sprintf(
                            _('Der Raum %1$s wurde aus dem teilbaren Raum %2$s gelöscht!'),
                            ($room ? htmlReady($room->name) : _('unbekannt')),
                            ($separable_room ? htmlReady($separable_room->name) : _('unbekannt'))
                        )
                    );
                } else {
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Löschen des Raumes %1$s aus dem teilbaren Raum %2$s!'),
                            ($room ? htmlReady($room->name) : _('unbekannt')),
                            ($separable_room ? htmlReady($separable_room->name) : _('unbekannt'))
                        )
                    );
                }
            } else {
                PageLayout::postError(
                    _('Der gewählte Raumteil wurde nicht gefunden!')
                );
            }
        } else {
            $errors          = [];
            $parts_not_found = 0;
            foreach ($room_part_ids as $room_part_id) {
                $separable_room_part = SeparableRoomPart::find(
                    explode('_', $room_part_id)
                );

                if ($separable_room_part) {
                    $room           = $separable_room_part->room;
                    $separable_room = $separable_room_part->separable_room;
                    if ($separable_room_part->delete()) {
                        //Check if the separable room as any parts left.
                        if ($separable_room) {
                            if (count($separable_room->parts) == 0) {
                                //There are no parts left in the separable room
                                //so that it can be deleted, too.
                                $separable_room->delete();
                            }
                        }
                        PageLayout::postSuccess(
                            sprintf(
                                _('Der Raum %1$s wurde aus dem teilbaren Raum %2$s gelöscht!'),
                                ($room ? htmlReady($room->name) : _('unbekannt')),
                                ($separable_room ? htmlReady($separable_room->name) : _('unbekannt'))
                            )
                        );
                    } else {
                        PageLayout::postError(
                            sprintf(
                                _('Fehler beim Löschen des Raumes %1$s aus dem teilbaren Raum %2$s!'),
                                ($room ? htmlReady($room->name) : _('unbekannt')),
                                ($separable_room ? htmlReady($separable_room->name) : _('unbekannt'))
                            )
                        );
                    }
                } else {
                    $parts_not_found++;
                }
            }

            if ($parts_not_found > 0) {
                //Add an error message on top of the other error messages.
                array_unshift(
                    $errors,
                    sprintf(
                        _('%d Raumteile wurden nicht gefunden!'),
                        $parts_not_found
                    )
                );
            }

            if ($errors) {
                PageLayout::postError(
                    ngettext(
                        'Der folgende Fehler trat beim Löschen mehrerer Raumteile auf:',
                        'Die folgenden Fehler traten beim Löschen mehrerer Raumeteile auf:',
                        count($errors)
                    ),
                    $errors
                );
            }
        }
    }


    public function separable_rooms_action()
    {
        if (Navigation::hasItem('/resources/admin/separable_rooms')) {
            Navigation::activateItem('/resources/admin/separable_rooms');
        }

        PageLayout::setTitle(
            _('Teilbare Räume verwalten')
        );

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
        }

        $db = DBManager::get();

        $this->buildings   = [];
        $this->building    = null;
        $this->building_id = Request::get('building_id');

        if ($this->building_id) {
            $this->building = Building::find($this->building_id);
        } else {
            $this->buildings = Building::findAll();
        }

        if (Request::submitted('create_separable_room')) {
            $selected_single_room_ids  = Request::getArray('selected_single_rooms');
            $this->separable_room_name = Request::get('separable_room_name');

            $resources = Resource::findMany($selected_single_room_ids);

            //Check if all IDs represent rooms:
            $all_rooms = [];
            foreach ($resources as $resource) {
                $resource = $resource->getDerivedClassInstance();
                if ($resource instanceof Room) {
                    $all_rooms[] = $resource;
                }
            }

            if (count($all_rooms) != count($resources)) {
                PageLayout::postError(
                    sprintf(
                        _('Teilbare Räume dürfen nur aus Raum-Objekten bestehen! %d ausgewählte Objekte sind keine Räume!'),
                        count($resources) - count($all_rooms)
                    )
                );
                return;
            }

            //Check if the rooms are already part of other separable rooms:

            $separable_room_part_ids_stmt = $db->prepare(
                'SELECT room_id FROM separable_room_parts
                INNER JOIN separable_rooms
                ON separable_room_parts.separable_room_id = separable_rooms.id
                WHERE separable_rooms.building_id = :building_id'
            );

            $separable_room_part_ids_stmt->execute(
                [
                    'building_id' => $this->building_id,
                ]
            );

            $separable_room_part_ids = $separable_room_part_ids_stmt->fetchAll(
                PDO::FETCH_COLUMN,
                0
            );

            $rooms = [];
            if ($separable_room_part_ids) {
                //There are other separable rooms.
                foreach ($all_rooms as $room) {
                    if (!in_array($room->id, $separable_room_part_ids)) {
                        //The room is not part of another separable room.
                        //We can add it to the final list of rooms
                        //that will be included in the new separable room.
                        $rooms[] = $room;
                    }
                }
            } else {
                //No separable rooms exist: All rooms can be added to
                //a new separable room.
                $rooms = $all_rooms;
            }

            //Now we create a separable room:
            try {
                $separable_room = SeparableRoom::createFromRooms(
                    $this->building_id,
                    $rooms,
                    $this->separable_room_name
                );
                //Reset the separable room name:
                $this->separable_room_name = '';
            } catch (SeparableRoomException $e) {
                //Show a warning for the exception when not all
                //rooms could be added to the separable room:
                PageLayout::postWarning(
                    _('Der teilbare Raum konnte nicht korrekt gespeichert werden!'),
                    [$e->getMessage()]
                );
                return;
            } catch (Exception $e) {
                PageLayout::postError(
                    $e->getMessage()
                );
                return;
            }
        }

        if (Request::submitted('add_room_part')) {
            $selected_single_room_ids = Request::getArray('selected_single_rooms');

            $resources = Resource::findMany($selected_single_room_ids);

            //Check if all IDs represent rooms:
            $all_rooms = [];
            foreach ($resources as $resource) {
                $resource = $resource->getDerivedClassInstance();
                if ($resource instanceof Room) {
                    $all_rooms[] = $resource;
                }
            }

            if (count($all_rooms) != count($resources)) {
                PageLayout::postError(
                    sprintf(
                        _('Teilbare Räume dürfen nur aus Raum-Objekten bestehen! %d ausgewählte Objekte sind keine Räume!'),
                        count($resources) - count($all_rooms)
                    )
                );
                return;
            }

            //Check if the rooms are already part of other separable rooms:
            $separable_room_part_ids_stmt = $db->prepare(
                'SELECT room_id FROM separable_room_parts
                INNER JOIN separable_rooms
                ON separable_room_parts.separable_room_id = separable_rooms.id
                WHERE separable_rooms.building_id = :building_id'
            );

            $separable_room_part_ids_stmt->execute(
                [
                    'building_id' => $this->building_id,
                ]
            );

            $separable_room_part_ids = $separable_room_part_ids_stmt->fetchAll(
                PDO::FETCH_COLUMN,
                0
            );

            $rooms = [];
            if ($separable_room_part_ids) {
                //There are other separable rooms.
                foreach ($all_rooms as $room) {
                    if (!in_array($room->id, $separable_room_part_ids)) {
                        //The room is not part of another separable room.
                        //We can add it to the final list of rooms
                        //that will be included in the new separable room.
                        $rooms[] = $room;
                    }
                }
            }

            //Get the selected separable room:
            $separable_room_id = Request::get('separable_room_id');
            if ($separable_room_id) {
                $separable_room = SeparableRoom::find($separable_room_id);
                if ($separable_room) {
                    foreach ($rooms as $room) {
                        $separable_room_part = SeparableRoomPart::findOneBySql(
                            'separable_room_id = :separable_room_id
                            AND room_id = :room_id',
                            [
                                'separable_room_id' => $separable_room_id,
                                'room_id'           => $room->id
                            ]
                        );
                        if ($separable_room_part) {
                            PageLayout::postInfo(
                                sprintf(
                                    _('Der Raum %1$s ist bereits Teil des teilbaren Raumes %2$s!'),
                                    htmlReady($room->name),
                                    htmlReady($separable_room->name)
                                )
                            );
                        } else {
                            $separable_room_part                    = new SeparableRoomPart();
                            $separable_room_part->separable_room_id = $separable_room_id;
                            $separable_room_part->room_id           = $room->id;
                            if (!$separable_room_part->store()) {
                                PageLayout::postError(
                                    sprintf(
                                        _('Fehler beim Zuordnen des Raumteiles %1$s zum teilbaren Raum %2$s!'),
                                        htmlReady($room->name),
                                        htmlReady($separable_room->name)
                                    )
                                );
                            }
                        }
                    }
                } else {
                    PageLayout::postError(
                        _('Der gewählte teilbare Raum wurde nicht gefunden!')
                    );
                }
            } else {
                PageLayout::postError(
                    _('Es wurde kein teilbarer Raum ausgewählt!')
                );
            }
        }

        if (Request::submitted('delete_separable_room')) {
            $delete_separable_room_array = Request::getArray('delete_separable_room');
            $separable_room_id           = array_keys($delete_separable_room_array)[0];
            $this->deleteSeparableRoomsById([$separable_room_id]);
        }

        if (Request::submitted('bulk_delete_separable_rooms')) {
            $separable_room_ids = Request::getArray('selected_separable_rooms');
            $this->deleteSeparableRoomsById($separable_room_ids);
        }

        if (Request::submitted('delete_room_part')) {
            $delete_room_part_array = Request::getArray('delete_room_part');
            $room_part_id           = array_keys($delete_room_part_array)[0];
            $this->deleteSeparableRoomPartsById([$room_part_id]);
        }

        if (Request::submitted('bulk_delete_room_parts')) {
            $room_part_ids = Request::getArray('selected_room_parts');
            $this->deleteSeparableRoomPartsById($room_part_ids);
        }

        //The following code is responsible for displaying rooms
        //and separable rooms:
        if ($this->building_id) {
            //Load all rooms for that building:
            $this->rooms = Room::findByBuilding($this->building_id);

            $this->separable_rooms = SeparableRoom::findBySql(
                'building_id = :building_id',
                [
                    'building_id' => $this->building_id
                ]
            );

            $separable_room_part_ids_stmt = $db->prepare(
                'SELECT room_id FROM separable_room_parts
                INNER JOIN separable_rooms
                ON separable_room_parts.separable_room_id = separable_rooms.id
                WHERE separable_rooms.building_id = :building_id'
            );

            $separable_room_part_ids_stmt->execute(
                [
                    'building_id' => $this->building_id,
                ]
            );

            $separable_room_part_ids = $separable_room_part_ids_stmt->fetchAll(
                PDO::FETCH_COLUMN,
                0
            );

            if ($separable_room_part_ids) {
                $rooms              = $this->building->rooms;
                $this->single_rooms = [];
                foreach ($rooms as $room) {
                    if (!in_array($room->id, $separable_room_part_ids)) {
                        $this->single_rooms[] = $room;
                    }
                }
            } else {
                $this->single_rooms = $this->building->rooms;
            }
        }
    }


    public function configuration_action()
    {
        if (Navigation::hasItem('/resources/admin/configuration')) {
            Navigation::activateItem('/resources/admin/configuration');
        }

        PageLayout::setTitle(
            _('Konfigurationsoptionen')
        );

        $this->config = Config::get();

        $this->resources_booking_plan_start_hour =
            $this->config->RESOURCES_BOOKING_PLAN_START_HOUR;
        $this->resources_booking_plan_end_hour   =
            $this->config->RESOURCES_BOOKING_PLAN_END_HOUR;

        if (Request::submitted('save')) {

            //Get colors:
            $colours = Request::getArray('colours');

            //Validate:
            CSRFProtection::verifyUnsafeRequest();

            $this->resources_booking_plan_start_hour = Request::get('resources_booking_plan_start_hour');
            $this->resources_booking_plan_end_hour   = Request::get('resources_booking_plan_end_hour');
            //Adjust format of booking plan start and end hours:
            //Add another zero in front of the string, if the hour contains
            //of just one digit.
            if (preg_match('/^[0-9]\:/', $this->resources_booking_plan_start_hour)) {
                $this->resources_booking_plan_start_hour = '0'
                    . $this->resources_booking_plan_start_hour;
            }
            if (preg_match('/^[0-9]\:/', $this->resources_booking_plan_end_hour)) {
                $this->resources_booking_plan_end_hour = '0'
                    . $this->resources_booking_plan_end_hour;
            }

            //Check format of booking plan start and end hours:
            $hour_regex = '/^(([01][0-9])|(2[0-3]))\:[0-5][0-9]$/';
            if (!preg_match($hour_regex, $this->resources_booking_plan_start_hour)) {
                PageLayout::postError(
                    _('Die Startuhrzeit für den Belegungsplan ist im falschen Format!')
                );
                return;
            }
            if (!preg_match($hour_regex, $this->resources_booking_plan_end_hour)) {
                PageLayout::postError(
                    _('Die Enduhrzeit für den Belegungsplan ist im falschen Format!')
                );
                return;
            }
            //Check the time value:
            $begin = strtotime($this->resources_booking_plan_start_hour);
            $end   = strtotime($this->resources_booking_plan_end_hour);
            if ($begin >= $end) {
                PageLayout::postError(
                    _('Die Startuhrzeit für den Belegungsplan darf nicht hinter der Enduhrzeit liegen!')
                );
                return;
            }


            //Store colors:

            foreach ($colours as $colour_id => $value) {
                //Validate value:
                if (!preg_match('/#([0-9A-Fa-f]{2}){3}/', $value)) {
                    PageLayout::postError(
                        sprintf(
                            _('Der Farbwert für %s ist ungültig!'),
                            htmlReady($colour_id)
                        )
                    );
                    return;
                }

                //We don't want to create new colors here so we only
                //modify colors that exist.

                $colour = ColourValue::find($colour_id);
                if ($colour) {
                    //Strip the first character from $value since we don't need
                    //the '#'-character in the database. Furthermore we add 'ff'
                    //to the end of the string to make the color intransparent.
                    $colour->value = mb_strtolower(substr($value, 1)) . 'ff';

                    if ($colour->isDirty()) {
                        if (!$colour->store()) {
                            PageLayout::postError(
                                sprintf(
                                    _('Fehler beim Speichern des Farbwertes für %s!'),
                                    htmlReady($colour_id)
                                )
                            );
                            return;
                        }
                    }
                }
            }

            //Store config values:
            $this->config->store(
                'RESOURCES_ENABLE',
                (bool)Request::get('resources_enable')
            );
            $this->config->store(
                'RESOURCES_ALLOW_VIEW_RESOURCE_OCCUPATION',
                (bool)Request::get('resources_allow_view_resource_occupation')
            );

            $this->config->store(
                'RESOURCES_ALLOW_ROOM_PROPERTY_REQUESTS',
                (bool)Request::get('resources_allow_room_property_requests')
            );

            $this->config->store(
                'RESOURCES_ALLOW_ROOM_REQUESTS',
                (bool)Request::get('resources_allow_room_requests')
            );

            $this->config->store(
                'RESOURCES_DIRECT_ROOM_REQUESTS_ONLY',
                (bool)Request::get('resources_direct_room_requests_only')
            );

            $this->config->store(
                'RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE',
                Request::int('resources_allow_single_assign_percentage')
            );

            $this->config->store(
                'RESOURCES_ALLOW_SINGLE_DATE_GROUPING',
                Request::int('resources_allow_single_date_grouping')
            );

            $this->config->store(
                'RESOURCES_MAP_SERVICE_URL',
                Request::get('resources_map_service_url')
            );

            $this->config->store(
                'RESOURCES_MAX_PREPARATION_TIME',
                Request::get('resources_max_preparation_time')
            );
            $this->config->store(
                'RESOURCES_MIN_BOOKING_TIME',
                Request::get('resources_min_booking_time')
            );
            $this->config->store(
                'RESOURCES_DISPLAY_CURRENT_REQUESTS_IN_OVERVIEW',
                Request::get('resources_display_current_requests_in_overview')
            );

            $this->config->store(
                'RESOURCES_BOOKING_PLAN_START_HOUR',
                $this->resources_booking_plan_start_hour
            );
            $this->config->store(
                'RESOURCES_BOOKING_PLAN_END_HOUR',
                $this->resources_booking_plan_end_hour
            );

            PageLayout::postSuccess(
                _('Die Konfigurationsoptionen wurden gespeichert!')
            );
        }

        $this->colours = ColourValue::findBySql(
            "colour_id LIKE 'Resources%'
           ORDER BY colour_id ASC"
        );
    }
}
