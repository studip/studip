<?php

/**
 * building.php - contains Resources_BuildingController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017
 * @category    Stud.IP
 * @since       4.1
 */


/**
 * Resources_BuildingController contains actions for Building resources.
 */
class Resources_BuildingController extends AuthenticatedController
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
    
    public function index_action($building_id)
    {
        $this->building = Building::find($building_id);
        $building_details = [];
    
        if($this->building->number) {
            $building_details[_('Gebäudenummer')] = $this->building->number;
        }
        if($this->building->address) {
            $building_details[_('Adresse')] = $this->building->address;
        }
        
        $this->building_details = $building_details;
        $this->geo_coordinates_object = $this->building->getPropertyObject(
            'geo_coordinates'
        );
        
        PageLayout::setTitle(
            $this->building->getFullName() . ' - ' . _('Informationen')
        );
        if (Navigation::hasItem('/room_management/overview/index')) {
            Navigation::activateItem('/room_management/overview/index');
        }
        
        $user = User::findCurrent();
        
        $current_user_is_resource_admin = $this->building->userHasPermission(
            $user,
            'admin'
        );
        
        $sidebar           = Sidebar::get();
        $actions           = new ActionsWidget();
        $actions_available = false;
        if ($current_user_is_resource_admin) {
            $actions_available = true;
            $actions->addLink(
                _('Gebäude bearbeiten'),
                $this->building->getActionURL('edit'),
                Icon::create('edit'),
                [
                    'data-dialog' => '1'
                ]
            );
            $actions->addLink(
                _('Rechte bearbeiten'),
                $this->building->getActionURL('permissions'),
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
        $tree_widget->setCurrentResource($this->building);
        
        $sidebar->addWidget($tree_widget);
    }
    
    public function select_category_action()
    {
        PageLayout::setTitle(_('Gebäude hinzufügen'));
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        
        $this->next_action = Request::get('next_action');
        $this->categories  = ResourceCategory::findByClass_name('Building');
        
        if (!$this->categories) {
            PageLayout::postError(
                _('Es sind keine Gebäudekategorien eingerichtet!')
            );
        }
    }
    
    protected function addEditHandler($mode = 'edit', $building_id = null)
    {
        $this->mode      = $mode;
        $this->show_form = false;
        if ($mode == 'edit') {
            $this->building = Building::find($building_id);
            if (!$this->building) {
                PageLayout::postError(
                    _('Das angegebene Gebäude wurde nicht gefunden!')
                );
                return;
            }
        }
        
        //Get all properties of the building:
        $this->property_data              = [];
        $this->grouped_defined_properties = [];
        if ($mode == 'add') {
            $this->category_id = Request::get('category_id');
            if (!$this->category_id) {
                PageLayout::postInfo(
                    _('Bitte wählen Sie eine Gebäudekategorie aus!')
                );
                return;
            }
            $this->category = ResourceCategory::find($this->category_id);
            if (!$this->category) {
                PageLayout::postError(
                    _('Die gewählte Gebäudekategorie wurde nicht gefunden!')
                );
                return;
            }
            
            $this->grouped_defined_properties = $this->category->getGroupedPropertyDefinitions(
                ['geo_coordinates', 'number', 'address']
            );
        } elseif ($mode == 'edit') {
            $this->grouped_defined_properties =
                $this->building->category->getGroupedPropertyDefinitions(
                    $this->building->getRequiredPropertyNames()
                );
            PageLayout::setTitle(sprintf(_('%s: Bearbeiten'), $this->building->getFullName()));
        }
        
        if (($mode == 'add') || ($mode == 'edit')) {
            $this->possible_parents = [];
            $locations              = Location::findAll();
            foreach ($locations as $location) {
                $this->possible_parents[] = $location;
                
                //Get all ResourceLabel resources from the location
                //and add them as possible parents:
                $labels = $location->findChildrenByClassName('ResourceLabel', 3);
                
                foreach ($labels as $label) {
                    $this->possible_parents[] = $label;
                }
            }
        }
        
        foreach ($this->grouped_defined_properties as $properties) {
            foreach ($properties as $property) {
                if ($mode == 'add') {
                    $this->property_data[$property->id] = '';
                } elseif ($mode == 'edit') {
                    $this->property_data[$property->id] =
                        $this->building->getProperty($property->name);
                }
            }
        }
        
        $this->show_form = true;
        if (Request::submitted('confirmed')) {
            CSRFProtection::verifyUnsafeRequest();
            //Process submitted form:
            $this->name          = Request::get('name');
            $this->description   = Request::get('description');
            $this->number        = Request::get('number');
            $this->address       = Request::get('address');
            $this->parent_id     = Request::get('parent_id');
            $this->latitude      = Request::float('geo_coordinates_latitude');
            $this->longitude     = Request::float('geo_coordinates_longitude');
            $this->altitude      = Request::float('geo_coordinates_altitude');
            $this->osm_link      = Request::get('geo_coordinates_osm_link');
            $this->sort_position = Request::get('sort_position');
            
            $this->property_data = Request::getArray('properties');
            
            //validation:
            if (!$this->name) {
                PageLayout::postError(
                    _('Der Name des Gebäudes ist leer!')
                );
                return;
            }
            if (!$this->parent_id) {
                PageLayout::postError(
                    _('Es wurde keine Hierarchieebene ausgewählt!')
                );
                return;
            }
            if (!Resource::exists($this->parent_id)) {
                PageLayout::postError(
                    _('Die gewählte Hierarchieebene wurde nicht gefunden!')
                );
                return;
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
                $this->building              = new Building();
                $this->building->category_id = $this->category_id;
            }
            
            $this->building->name        = $this->name;
            $this->building->description = $this->description;
            $this->building->parent_id   = $this->parent_id;
            
            if ($this->building->isDirty()) {
                $successfully_stored = $this->building->store();
            } else {
                $successfully_stored = true;
            }
            
            if ($GLOBALS['perm']->have_perm('root')) {
                $this->building->sort_position = $this->sort_position;
            }
            
            //Store properties:
            
            //Special treatment for the geo_coordinates property:
            
            $failed_properties = 0;
            try {
                $this->building->geo_coordinates = $position_string;
            } catch (Exception $e) {
                $failed_properties++;
            }
            
            //Special treatment for the number property:
            try {
                $this->building->number = $this->number;
            } catch (Exception $e) {
                $failed_properties++;
            }
            
            //Special treatment for the address property:
            try {
                $this->building->address = $this->address;
            } catch (Exception $e) {
                $failed_properties++;
            }
            
            //Store all non-special properties:
            
            $user              = User::findCurrent();
            $failed_properties += count(
                $this->building->setPropertiesById(
                    $this->property_data,
                    $user
                )
            );
            
            //Display result messages:
            
            if ($successfully_stored && !$failed_properties) {
                $this->show_form = false;
                PageLayout::postSuccess(
                    _('Das Gebäude wurde gespeichert!')
                );
            } elseif ($successfully_stored) {
                $this->show_form = false;
                PageLayout::postWarning(
                    sprintf(
                        ngettext(
                            'Das Gebäude wurde gespeichert, aber eine Eigenschaft konnte nicht gespeichert werden!',
                            'Das Gebäude wurde gespeichert, aber %d Eigenschaften konnten nicht gespeichert werden!',
                            $failed_properties
                        ),
                        $failed_properties
                    )
                );
            } else {
                PageLayout::postError(
                    _('Fehler beim Speichern des Gebäudes!')
                );
            }
        } else {
            if($this->building) {
                //Show form with current data:
                $this->name        = $this->building->name;
                $this->description = $this->building->description;
                $this->number      = $this->building->number;
                $this->address     = $this->building->address;
                $this->parent_id   = $this->building->parent_id;
                $geo_coordinates = $this->building->getPropertyObject('geo_coordinates');
                if ($mode == 'edit' && $geo_coordinates) {
                    $position_data   = ResourceManager::getPositionArray(
                        $geo_coordinates
                    );
                    $this->latitude  = $position_data[0];
                    $this->longitude = $position_data[1];
                    $this->altitude  = $position_data[2];
                }
                $this->sort_position = $this->building->sort_position;
            }
        }
    }
    
    
    public function add_action()
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        
        PageLayout::setTitle(_('Gebäude hinzufügen'));
        
        $this->category_id = Request::get('category_id');
        $this->category    = ResourceCategory::find($this->category_id);
        if (!$this->category) {
            //If no category_id is set we must redirect to the
            //select_category action:
            $this->redirect(
                URLHelper::getURL(
                    'dispatch.php/resources/building/select_category'
                )
            );
        } else {
            $category_class = $this->category->class_name;
            if (($category_class != 'Building') && !is_subclass_of($category_class, 'Building')) {
                PageLayout::postError(
                    _('Die gewählte Kategorie ist für Gebäude nicht geeignet!')
                );
                return;
            }
            $this->addEditHandler('add');
        }
    }
    
    
    public function edit_action($building_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        
        $this->addEditHandler('edit', $building_id);
    }
    
    
    public function delete_action($building_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        
        $this->show_form = false;
        
        $this->building = Building::find($building_id);
        if (!$this->building) {
            PageLayout::postError(
                _('Das angegebene Gebäude wurde nicht gefunden!')
            );
            return;
        }
        PageLayout::setTitle(sprintf(_('%s: Löschen'), $this->building->getFullName()));
        
        //geo_coordinates_object is needed in the index view that is loaded
        //from the view for this action.
        $this->geo_coordinates_object = $this->building->getPropertyObject(
            'geo_coordinates'
        );
        $this->required_properties    = Building::getRequiredProperties(
            ['geo_coordinates']
        );
        
        $this->show_form = true;
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($this->building->delete()) {
                $this->show_form = false;
                PageLayout::postSuccess(_('Das Gebäude wurde gelöscht!'));
            } else {
                PageLayout::postError(_('Fehler beim Löschen des Gebaüdes!'));
            }
        }
    }
    
    
    public function lock_action($building_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
        
        $this->show_form = false;
        $this->building  = Room::find($building_id);
        if (!$this->building) {
            PageLayout::postError(
                _('Das angegebene Gebäude wurde nicht gefunden!')
            );
            return;
        }
        
        PageLayout::setTitle(
            sprintf(
                _('Gebäude %s sperren'),
                $this->building->name
            )
        );
        
        if (Request::submitted('confirmed')) {
            $begin_date = Request::get('begin_date');
            $begin_time = Request::get('begin_time');
            $end_date   = Request::get('end_date');
            $end_time   = Request::get('end_time');
            
            //$begin and $end are in the format Y-m-d H:i
            $begin_date  = explode('.', $begin_date);
            $begin_time  = explode(':', $begin_time);
            $this->begin = new DateTime();
            $this->begin->setDate(
                intval($begin_date[2]),
                intval($begin_date[1]),
                intval($begin_date[0])
            );
            $this->begin->setTime(
                intval($begin_time[0]),
                intval($begin_time[1]),
                0
            );
            
            $end_date  = explode('.', $end_date);
            $end_time  = explode(':', $end_time);
            $this->end = new DateTime();
            $this->end->setDate(
                intval($end_date[2]),
                intval($end_date[1]),
                intval($end_date[0])
            );
            $this->end->setTime(
                intval($end_time[0]),
                intval($end_time[1]),
                0
            );
            
            try {
                $lock = ResourceManager::lockResource(
                    $this->building,
                    $this->begin,
                    $this->end
                );
            } catch (ResourceUnlockableException $e) {
                PageLayout::postError(
                    _('Das Gebäude konnte nicht gesperrt werden!'),
                    [$e->getMessage()]
                );
                $this->show_form = true;
                return;
            } catch (ResourceUnavailableException $e) {
                PageLayout::postError(
                    _('Das Gebäude konnte nicht gesperrt werden!'),
                    [$e->getMessage()]
                );
                $this->show_form = true;
                return;
            }
            $this->show_form = false;
            PageLayout::postSuccess(
                sprintf(
                    _('Das Gebäude wurde im Zeitraum von %1$s bis %2$s gesperrt!'),
                    $this->begin->format('d.m.Y H:i'),
                    $this->end->format('d.m.Y H:i')
                )
            );
        } else {
            //Set default form data:
            $this->begin = new DateTime();
            $this->end   = new DateTime();
            $this->end->add(new DateInterval('P1D'));
            $this->show_form = true;
        }
    }
}