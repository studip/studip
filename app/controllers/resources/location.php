<?php

/**
 * location.php - contains Resources_LocationController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2020
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_LocationController contains actions for Location resources.
 */
class Resources_LocationController extends AuthenticatedController
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

    public function index_action($location_id = null)
    {
        $current_user = User::findCurrent();
        $sufficient_permissions = ResourceManager::userHasGlobalPermission(
            $current_user,
            'user'
        ) || RoomManager::countUserrooms($current_user);
        if (!$sufficient_permissions) {
            throw new AccessDeniedException();
        }

        if (!$location_id) {
            $locations = Location::findAll();
            if($locations){
                $location_id = $locations[0]->id;
            } else {
                return;
            }
        }
        $this->location = Location::find($location_id);
        $this->geo_coordinates_object = $this->location->getPropertyObject(
            'geo_coordinates'
        );

        PageLayout::setTitle(
            $this->location->getFullName() . ' - ' . _('Informationen')
        );

        $this->other_properties = [];
        //Filter out all properties that are in the required_properties array
        //of the Room class.
        $required_properties = Location::getRequiredProperties();
        foreach ($this->location->properties as $property) {
            if (!in_array($property->name, $required_properties)) {
                if ($property->state) {
                    $this->other_properties[] = $property->name;
                }
            }
        }

        if (!Request::isDialog()) {
            //We must add add a sidebar and activate the navigation item
            //for the room management overview.

            if (Navigation::hasItem('/room_management/overview/index')) {
                Navigation::activateItem('/room_management/overview/index');
            }

            $user = User::findCurrent();
            $current_user_is_resource_admin = $this->location->userHasPermission(
                $user,
                'admin'
            );
            $sidebar = Sidebar::get();
            $actions = new ActionsWidget();
            $actions_available = false;
            if ($current_user_is_resource_admin) {
                $actions->addLink(
                    _('Standort bearbeiten'),
                    $this->location->getActionURL('edit'),
                    Icon::create('edit'),
                    [
                        'data-dialog' => '1'
                    ]
                );
                $actions->addLink(
                    _('Rechte bearbeiten'),
                    $this->location->getActionURL('permissions'),
                    Icon::create('roles'),
                    [
                        'data-dialog' => '1'
                    ]
                );
            }
            if ($this->geo_coordinates_object instanceof ResourceProperty) {
                $actions_available = true;
                $actions->addLink(
                    _('Lageplan anzeigen'),
                    ResourceManager::getMapUrlForResourcePosition($this->geo_coordinates_object),
                    Icon::create('globe'),
                    ['target' => '_blank']
                );
            }
            if ($actions_available) {
                $sidebar->addWidget($actions);
            }

            $tree_widget = new ResourceTreeWidget(Location::findAll(), null, null);
            $tree_widget->setCurrentResource($this->location);

            $sidebar->addWidget($tree_widget);
        }
    }


    public function select_category_action()
    {
        PageLayout::setTitle(_('Standort hinzufügen'));
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        
        $this->next_action = Request::get('next_action');
        $this->categories = ResourceCategory::findByClass_name('Location');

        if (!$this->categories) {
            PageLayout::postError(
                _('Es sind keine Gebäudekategorien eingerichtet!')
            );
        }
    }


    protected function addEditHandler($mode = 'edit', $location_id = null)
    {
        $this->show_form = false;
        $this->location = null;
        $this->mode = $mode;

        if ($mode == 'add') {
            PageLayout::setTitle(_('Standort hinzufügen'));
        } elseif (($mode == 'edit') || ($mode == 'delete')) {
            $this->location = Location::find($location_id);
            if (!$this->location) {
                PageLayout::postError(
                    _('Der angegebene Standort wurde nicht gefunden!')
                );
                return;
            }
            if ($mode == 'edit') {
                PageLayout::setTitle(sprintf(_('%s: Bearbeiten'), $this->location->getFullName()));
            } elseif ($mode == 'delete') {
                PageLayout::setTitle(sprintf(_('%s: Löschen'), $this->location->getFullName()));
            }
        }

        if ($mode != 'delete') {
            $this->category_id = Request::get('category_id');
            $this->name = '';
            $this->description = '';
            $this->latitude = '0.0';
            $this->longitude = '0.0';
            $this->altitude = '0.0';
            $this->sort_position = '';

            if ($mode == 'edit') {
                //Set data from the location object:
                $this->name = $this->location->name;
                $this->description = $this->location->description;
                $position_data = ResourceManager::getPositionArray(
                    $this->location->getPropertyObject('geo_coordinates')
                );
                $this->latitude = $position_data[0];
                $this->longitude = $position_data[1];
                $this->altitude = $position_data[2];
                $this->sort_position = $this->location->sort_position;
            }
        }

        $this->property_data = [];
        if ($mode == 'edit') {
            $this->grouped_defined_properties =
                $this->location->category->getGroupedPropertyDefinitions(
                    $this->location->getRequiredPropertyNames()
                );
            foreach ($this->grouped_defined_properties as $properties) {
                foreach ($properties as $property) {
                    $this->property_data[$property->id] =
                        $this->location->getProperty($property->name);
                }
            }
        }

        $this->show_form = true;

        if (Request::submitted('save')) {

            CSRFProtection::verifyUnsafeRequest();

            if ($mode == 'delete') {
                if ($this->location->delete()) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Der Standort wurde gelöscht!')
                    );
                } else {
                    PageLayout::postError(
                        _('Fehler beim Löschen des Standortes!')
                    );
                }
                return;
            }

            //Process submitted form:
            $this->category_id = Request::get('category_id');
            $this->name = Request::get('name');
            $this->description = Request::get('description');
            $this->latitude = Request::float('geo_coordinates_latitude');
            $this->longitude = Request::float('geo_coordinates_longitude');
            $this->altitude = Request::float('geo_coordinates_altitude');
            $this->osm_link = Request::get('geo_coordinates_osm_link');
            $this->property_data = Request::getArray('properties');

            if ($GLOBALS['perm']->have_perm('root')) {
                $this->sort_position = Request::get('sort_position');
            }

            //validation:
            if (!$this->name) {
                PageLayout::postError(
                    _('Der Name des Standortes ist leer!')
                );
                return;
            }
            if ($mode == 'add') {
                $category_exists = ResourceCategory::countBySql(
                    "id = :category_id
                    AND class_name = 'Location'",
                    [
                        'category_id' => $this->category_id
                    ]
                ) > 0;
                if (!$category_exists) {
                    PageLayout::postError(
                        _('Die gewählte Standortkategorie wurde nicht gefunden!')
                    );
                    return;
                }
            }

            //data conversion:

            $position_string = '';
            if ($this->latitude >= 0.0) {
                $position_string .= '+';
            }
            $position_string .= number_format($this->latitude, 7);

            if ($this->longitude >= 0.0) {
                $position_string .= '+';
            }
            $position_string .= number_format($this->longitude, 7);

            if ($this->altitude >= 0.0) {
                $position_string .= '+';
            }
            $position_string .= number_format($this->altitude, 7) . 'CRSWGS_84/';

            //store data:
            if ($mode == 'add') {
                $this->location = new Location();
                $this->location->category_id = $this->category_id;
            }

            $this->location->name = $this->name;
            $this->location->description = $this->description;
            if ($GLOBALS['perm']->have_perm('root')) {
                $this->location->sort_position = $this->sort_position;
            }
            
            if ($this->location->isDirty()) {
                $successfully_stored = $this->location->store();
            } else {
                $successfully_stored = true;
            }

            if ($GLOBALS['perm']->have_perm('root')) {
                $this->location->sort_position = $this->sort_position;
            }

            $user = User::findCurrent();

            $unchanged_properties = [];
            if ($this->location->isPropertyEditable('geo_coordinates', $user)) {
                $this->location->geo_coordinates = $position_string;
            } elseif ($this->location->geo_coordinates != $position_string) {
                $unchanged_properties[] = $this->location->getPropertyObject('geo_coordinates');
            }
            
            $failed_properties = $this->location->setPropertiesById(
                $this->property_data,
                $user
            );

            $unchanged_properties = array_merge(
                $unchanged_properties,
                array_keys($failed_properties)
            );

            if ($successfully_stored) {
                $this->show_form = false;
                if (!$unchanged_properties) {
                    PageLayout::postSuccess(
                        _('Der Standort wurde gespeichert!')
                    );
                } else {
                    PageLayout::postWarning(
                        _('Der Standort wurde gespeichert, aber dessen folgende Eigenschaften wurden nicht gesetzt:'),
                        $unchanged_properties
                    );
                }
            } else {
                PageLayout::postError(
                    _('Fehler beim Speichern des Standortes!')
                );
            }
        }
    }


    public function add_action()
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $this->addEditHandler('add');
    }

    public function edit_action($location_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'autor')) {
            throw new AccessDeniedException();
        }

        $this->addEditHandler('edit', $location_id);
    }


    public function delete_action($location_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $this->addEditHandler('delete', $location_id);
    }
}
