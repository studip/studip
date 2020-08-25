<?php

/**
 * room.php - contains Resources_RoomController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2018
 * @category    Stud.IP
 * @since       TODO
 */


/**
 * Resources_RoomController contains actions for Room resources.
 */
class Resources_RoomController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!Request::isDialog()) {
            if (Navigation::hasItem('/resources/structure')) {
                Navigation::activateItem('/resources/structure');
            }
        }
    }

    public function index_action($room_id = null)
    {
        $this->room = Room::find($room_id);

        if (!$this->room) {
            PageLayout::postError(
                _('Der angegebene Raum wurde nicht gefunden!')
            );
            return;
        }

        PageLayout::setTitle(
            $this->room->getFullName() . ' - ' . _('Informationen')
        );


        //We must add add a sidebar and activate the navigation item
        //for the room management overview.

        if (Navigation::hasItem('/room_management/overview/index')) {
            Navigation::activateItem('/room_management/overview/index');
        }

        $user                                 = User::findCurrent();
        $current_user_is_resource_admin       = $this->room->userHasPermission(
            $user,
            'admin'
        );
        $current_user_is_resource_tutor       = $this->room->userHasPermission(
            $user,
            'tutor'
        );
        $this->current_user_is_resource_autor = $this->room->userHasPermission(
            $user,
            'autor'
        );
        $current_user_is_resource_user        = $this->room->userHasPermission(
            $user,
            'user'
        );

        $sidebar           = Sidebar::get();
        $actions           = new ActionsWidget();
        $actions_available = false;

        if ($current_user_is_resource_user) {
            if ($this->current_user_is_resource_autor) {
                $actions_available = true;
                $actions->addLink(
                    _('Wochenbelegung'),
                    $this->room->getActionURL('booking_plan'),
                    Icon::create('timetable'),
                    [
                        'target' => '_blank'
                    ]
                );
                $actions->addLink(
                    _('Semesterbelegung'),
                    $this->room->getActionURL('semester_plan'),
                    Icon::create('timetable'),
                    [
                        'target' => '_blank'
                    ]
                );
            } elseif ($this->room->bookingPlanVisibleForUser($user)) {
                $actions_available = true;
                $actions->addLink(
                    _('Belegungsplan'),
                    $this->room->getActionURL('booking_plan'),
                    Icon::create('timetable'),
                    [
                        'data-dialog' => 'size=big'
                    ]
                );
                $actions->addLink(
                    _('Semesterbelegung'),
                    $this->room->getActionURL('semester_plan'),
                    Icon::create('timetable'),
                    [
                        'data-dialog' => 'size=big'
                    ]
                );
            }
            if ($current_user_is_resource_admin) {
                $actions_available = true;
                $actions->addLink(
                    _('Raum bearbeiten'),
                    $this->room->getActionURL('edit'),
                    Icon::create('edit'),
                    [
                        'data-dialog' => 'size=auto'
                    ]
                );
                $actions->addLink(
                    _('Rechte bearbeiten'),
                    $this->room->getActionURL('permissions'),
                    Icon::create('roles'),
                    [
                        'data-dialog' => 'size=auto'
                    ]
                );
            }
            if ($current_user_is_resource_tutor && $this->room->requestable) {
                $actions_available = true;
                $actions->addLink(
                    _('Raumanfragen anzeigen'),
                    $this->room->getActionURL('request_list'),
                    Icon::create('room-request'),
                    [
                        'target' => '_blank'
                    ]
                );
            }
        }
        if (!$this->current_user_is_resource_autor && $this->room->requestable) {
            $actions_available = true;
            $actions->addLink(
                _('Raum anfragen'),
                $this->room->getActionURL('request'),
                Icon::create('room-request'),
                [
                    'data-dialog' => 'size=auto'
                ]
            );
        }
        if ($actions_available) {
            $sidebar->addWidget($actions);
        }

        $tree_widget = new ResourceTreeWidget(Location::findAll(), null, null);
        $tree_widget->setCurrentResource($this->room);
        $sidebar->addWidget($tree_widget);

        $this->grouped_properties = $this->room->getGroupedProperties(
            $this->room->getRequiredPropertyNames()
        );
    }


    public function select_category_action()
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Raum hinzufügen'));
        $this->next_action = Request::get('next_action');
        $room_class_names = RoomManager::getAllRoomClassNames();
        $this->categories = ResourceCategory::findBySql(
            'class_name IN ( :class_names ) ORDER BY name ASC',
            [
                'class_names' => $room_class_names
            ]
        );

        if (!$this->categories) {
            PageLayout::postError(
                _('Es sind keine Raumkategorien eingerichtet!')
            );
        }
    }


    protected function addEditDeleteHandler($mode = 'edit', $room_id = null)
    {
        $user            = User::findCurrent();
        $this->mode      = $mode;
        $this->show_form = false;
        if ($mode == 'add') {
            PageLayout::setTitle(_('Raum hinzufügen'));
        } elseif ($mode == 'edit' || $mode == 'delete') {
            $this->room = Room::find($room_id);
            if (!$this->room) {
                PageLayout::postError(
                    _('Der angegebene Raum wurde nicht gefunden!')
                );
                return;
            }

            if (($mode == 'edit' && !$this->room->userHasPermission($user, 'admin'))
                || ($mode == 'delete' && !$this->room->userHasPermission($user, 'admin'))) {
                throw new AccessDeniedException();
            }
            if ($mode == 'edit') {
                PageLayout::setTitle(sprintf(_('%s: Bearbeiten'), $this->room->getFullName()));
            } elseif ($mode == 'delete') {
                PageLayout::setTitle(sprintf(_('%s: Löschen'), $this->room->getFullName()));
            }
        }

        if ($mode == 'add' && !ResourceManager::userHasGlobalPermission($user, 'admin')) {
            throw new AccessDeniedException();
        }

        if ($mode == 'add' || $mode == 'edit') {
            //get the list of buildings to set a parent for the room:
            $buildings = Building::findAll();

            //We must convert the buildings to a hierarchy since rooms can be
            //placed multiple layers below a building:
            $this->building_hierarchies = [];
            foreach ($buildings as $building) {
                //Build the complete hierarchy from the root resource to
                //the building:
                $hierarchy = ResourceManager::getHierarchyNames($building);

                array_reverse($hierarchy);

                $this->building_hierarchies[$building->id] = '/' . implode('/', $hierarchy);
            }

            //In add-mode the category must be set before calling this method.
            if ($mode == 'edit') {
                $this->category = $this->room->category;
            }

            if (!($this->category instanceof ResourceCategory)) {
                PageLayout::postError(
                    _('Die gewählte Raumkategorie wurde nicht gefunden!')
                );
                return;
            }

            //Get all properties of the room:
            $this->grouped_defined_properties = [];
            $this->property_data              = [];
            if ($mode == 'edit') {
                $this->grouped_defined_properties =
                    $this->room->category->getGroupedPropertyDefinitions(
                        ['seats', 'room_type', 'booking_plan_is_public']
                    );
                foreach ($this->grouped_defined_properties as $group_properties) {
                    foreach ($group_properties as $property) {
                        $this->property_data[$property->id] =
                            $this->room->getProperty($property->name);
                    }
                }
            } elseif ($mode == 'add') {
                $this->grouped_defined_properties =
                    $this->category->getGroupedPropertyDefinitions(
                        ['seats', 'room_type', 'booking_plan_is_public']
                    );
            }
        }

        $this->show_form = true;
        if (Request::submitted('confirmed')) {

            CSRFProtection::verifyUnsafeRequest();

            if ($mode == 'add' || $mode == 'edit') {
                //Process submitted form:
                $this->parent_id = Request::get('parent_id');
                if ($mode == 'add') {
                    $this->category_id = Request::get('category_id');
                }
                $this->name                   = Request::get('name');
                $this->description            = Request::get('description');
                $this->requestable            = Request::int('requestable');
                $this->room_type              = Request::get('room_type');
                $this->seats                  = Request::int('seats');
                $this->booking_plan_is_public = Request::get('booking_plan_is_public');
                $this->sort_position          = Request::get('sort_position');
                $this->property_data          = Request::getArray('properties');

                //validation:

                $parent = Resource::find($this->parent_id);
                $parent = $parent->getDerivedClassInstance();
                if (!($parent instanceof Building)) {
                    //We must search the hierarchy until we find a building:
                    $building = $parent->findByClassName('Building');
                    if (!$building) {
                        PageLayout::postError(
                            _('Der Raum wurde keinem Gebäude zugeordnet!')
                        );
                    }
                    return;
                }

                if ($mode == 'add') {
                    //Check if the user has admin permissions on the parent resource.
                    //These are required so that a child resource can be created:
                    if (!$parent->userMayCreateChild($user)) {
                        throw new AccessDeniedException(
                            _('Unzureichende Berechtigungen zum Anlegen eines Raumes in der gewählten Hierarchie!')
                        );
                    }

                    $category_class = ResourceCategory::getClassNameById(
                        $this->category_id
                    );

                    if ($category_class != 'Room' && !is_subclass_of($category_class, 'Room')) {
                        PageLayout::postError(
                            _('Die gewählte Kategorie ist für Räume nicht geeignet!')
                        );
                        return;
                    }
                }

                if (!$this->name) {
                    PageLayout::postError(
                        _('Der Name des Raumes ist leer!')
                    );
                    return;
                }

                if ($this->seats < 0) {
                    PageLayout::postError(
                        _('Die Anzahl der Sitzplätze darf nicht negativ sein!')
                    );
                    return;
                }

                //data conversion:

                //(nothing to do here at the moment)

                //store data:

                if ($mode == 'add') {
                    $this->room = new Room();
                }

                //store the room object:
                $this->room->parent_id = $this->parent_id;
                if ($mode == 'add') {
                    $this->room->category_id = $this->category_id;
                }
                $this->room->name        = $this->name;
                $this->room->description = $this->description;
                $this->room->requestable = strval($this->requestable);
                if ($GLOBALS['perm']->have_perm('root')) {
                    $this->room->sort_position = $this->sort_position;
                }
                if ($this->room->isDirty()) {
                    $successfully_stored = $this->room->store();
                } else {
                    $successfully_stored = true;
                }

                $user = User::findCurrent();

                //Now we can store the room's properties, if the permissions
                //are high enough:
                $unchanged_properties = [];

                //Special treatment for special properties:
                if ($this->room->isPropertyEditable('room_type', $user)) {
                    $this->room->room_type = $this->room_type;
                } elseif ($this->room->room_type != $this->room_type) {
                    $unchanged_properties[] = $this->room->getPropertyObject('room_type');
                }
                if ($this->room->isPropertyEditable('seats', $user)) {
                    $this->room->seats = $this->seats;
                } elseif ($this->room->seats != $this->seats) {
                    $unchanged_properties[] = $this->room->getPropertyObject('seats');
                }
                if ($this->room->isPropertyEditable('booking_plan_is_public', $user)) {
                    $this->room->booking_plan_is_public = (bool)$this->booking_plan_is_public;
                } elseif ($this->room->booking_plan_is_public != $this->booking_plan_is_public) {
                    $unchanged_properties[] = $this->room->getPropertyObject(
                        'booking_plan_is_public'
                    );
                }

                //Non-special treatment for ordinary properties:
                $failed_properties = $this->room->setPropertiesById(
                    $this->property_data,
                    $user
                );

                $failed_property_objects = ResourcePropertyDefinition::findMany(
                    array_keys($failed_properties)
                );

                $unchanged_properties = array_merge(
                    $unchanged_properties,
                    $failed_property_objects
                );

                if ($successfully_stored && !$unchanged_properties) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Der Raum wurde gespeichert!')
                    );
                } elseif ($successfully_stored) {
                    $this->show_form = false;
                    if (count($unchanged_properties) == 1) {
                        PageLayout::postWarning(
                            sprintf(
                                _('Der Raum wurde gespeichert, aber die Eigenschaft %s konnte wegen fehlender Berechtigungen nicht gespeichert werden!'),
                                htmlReady($unchanged_properties[0]->display_name)
                            )
                        );
                    } else {
                        PageLayout::postWarning(
                            _('Der Raum wurde gespeichert, aber die folgenden Eigenschaften konnten wegen fehlender Berechtigungen nicht gespeichert werden:'),
                            array_map('htmlReady', SimpleCollection::createFromArray($unchanged_properties)->pluck('display_name'))
                        );
                    }
                } else {
                    PageLayout::postError(
                        _('Fehler beim Speichern des Raumes!')
                    );
                }
            } elseif ($mode == 'delete') {
                if ($this->room->delete()) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Der Raum wurde gelöscht!')
                    );
                } else {
                    PageLayout::postError(
                        _('Fehler beim Löschen des Raumes!')
                    );
                }
            }
        } else {
            //For add mode $this->category_id is set directly in add_action!
            if ($mode == 'edit' || $mode == 'delete') {
                //Show form with current data:
                $this->parent_id              = $this->room->parent_id;
                $this->category_id            = $this->room->category_id;
                $this->name                   = $this->room->name;
                $this->description            = $this->room->description;
                $this->requestable            = '1';
                $this->room_type              = $this->room->room_type;
                $this->seats                  = $this->room->seats;
                $this->booking_plan_is_public = (bool)$this->room->booking_plan_is_public;
                $this->sort_position          = $this->room->sort_position;
            }
        }
    }


    public function add_action()
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(_('Raum hinzufügen'));

        $this->category_id = Request::get('category_id');
        $this->category    = ResourceCategory::find($this->category_id);
        if (!$this->category) {
            //If no category_id is set we must redirect to the
            //select_category action:
            $this->redirect(
                URLHelper::getURL(
                    'dispatch.php/resources/room/select_category'
                )
            );
        } else {
            $category_class = $this->category->class_name;
            if ($category_class != 'Room' && !is_subclass_of($category_class, 'Room')) {
                PageLayout::postError(
                    _('Die gewählte Kategorie ist für Räume nicht geeignet!')
                );
                return;
            }
            $this->addEditDeleteHandler('add');
        }
    }


    public function edit_action($room_id = null)
    {
        PageLayout::setTitle(_('Raum bearbeiten'));
        $this->addEditDeleteHandler('edit', $room_id);
    }


    public function delete_action($room_id = null)
    {
        PageLayout::setTitle(_('Raum löschen'));
        $this->addEditDeleteHandler('delete', $room_id);
    }


    /**
     * This action handles booking a room from a resource request.
     * Contrary to the resources/resource/assign action
     * direct booking is not possible here.
     */
    public function assign_action($room_id = null)
    {
        $this->show_form = false;
        PageLayout::setTitle(_('Raum zuweisen'));

        $this->room = Room::find($room_id);
        if (!$this->room) {
            PageLayout::postError(
                _('Der angegebene Raum wurde nicht gefunden!')
            );
            return;
        }

        if (!$this->room->userHasPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $this->room_request = RoomRequest::find(
            Request::get('request_id')
        );
        if (!$this->room_request) {
            PageLayout::postError(
                _('Die angegebene Raumanfrage wurde nicht gefunden!')
            );
            return;
        }

        $this->show_form = true;

        if (Request::submitted('confirmed')) {
            //Create a room booking with data from the selected room request.

            $this->notify_teachers = Request::get('notify_teachers', '0');

            $bookings = RoomManager::createRoomBookingsByRequest(
                $this->room,
                $this->room_request,
                User::findCurrent(),
                (bool)$this->notify_teachers
            );

            if ($bookings) {
                $booked_time_interval_strings = [];
                foreach ($bookings as $booking) {
                    $time_intervals = $booking->getTimeIntervals();
                    foreach ($time_intervals as $time_interval) {
                        $booked_time_interval_strings[] = sprintf(
                            _('Am %1$s zwischen %2$s Uhr und %3$s Uhr'),
                            date('d.m.Y', $time_interval['begin']),
                            date('H:i', $time_interval['begin']),
                            date('H:i', $time_interval['end'])
                        );
                    }
                }

                $this->show_form = false;
                PageLayout::postSuccess(
                    sprintf(
                        _('Der Raum %1$s wurde zu folgenden Zeiten gebucht:'),
                        htmlReady($this->room->name)
                    ),
                    $booked_time_interval_strings
                );
            } else {
                PageLayout::postError(
                    sprintf(
                        _('Fehler beim Buchen des Raumes %1$s!'),
                        htmlReady($this->room->name)
                    )
                );
            }
        } else {
            $this->notify_teachers = '0';
        }
    }
}
