<?php

/**
 * Building.class.php - model class for a resource which is a building
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.1
 *
 * @property string id building-ID (equal to Resource.resource_id)
 * @property Location Location
 * Other properties are inherited from the parent class (Resource).
 */


/**
 * The building class is a derived class from the Resource class
 * which includes specialisations for Building resource types.
 */
class Building extends Resource
{
    protected static $required_properties = [
        'address',
        'number',
        'geo_coordinates'
    ];
    
    protected static function configure($config = [])
    {
        if (!is_array($config['additional_fields'])) {
            $config['additional_fields'] = [];
        }
        foreach (self::$required_properties as $property) {
            $config['additional_fields'][$property] = [
                'get' => 'getProperty',
                'set' => 'setProperty'
            ];
        }
        
        $config['additional_fields']['location']['get'] = 'findLocation';
        $config['additional_fields']['rooms']['get']    = 'findRooms';
        
        $config['additional_fields']['facility_manager'] = [
            'get' => 'getPropertyRelatedObject',
            'set' => 'setPropertyRelatedObject'
        ];
        
        $config['registered_callbacks']['before_store'][] = 'cbValidate';
        parent::configure($config);
    }
    
    /**
     * Returns all buildings which are stored in the database.
     *
     * @return Building[] An array of Building objects.
     */
    public static function findAll()
    {
        return self::findBySql(
            "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE rc.class_name = 'Building'
            ORDER BY sort_position DESC, name ASC, mkdate ASC"
        );
    }
    
    public static function getTranslatedClassName($item_count = 1)
    {
        return ngettext(
            'Gebäude',
            'Gebäude',
            $item_count
        );
    }
    
    public static function getRequiredProperties()
    {
        return self::$required_properties;
    }
    
    /**
     * Finds buildings by a location specified by its ID.
     *
     * @param string $location_id The ID of the location.
     *
     * @return Building[] An array with buildings or an empty array.
     */
    public static function findByLocation($location_id = null)
    {
        if (!$location_id) {
            return [];
        }
        
        $location = Building::find($location_id);
        if (!$location) {
            return [];
        }
        
        //Return all found Building objects below the location:
        return $location->findChildrenByClassName('Building', 0, true);
    }
    
    
    /**
     * Returns the part of the URL for getLink and getURL which will be
     * placed inside the calls to URLHelper::getLink and URLHelper::getURL
     * in these methods.
     *
     * @param string $action The action for the building.
     * @param string $id The ID of the building.
     *
     * @return string The URL path for the specified action.
     * @throws InvalidArgumentException If $building_id is empty.
     *
     */
    protected static function buildPathForAction($action = 'show', $id = null)
    {
        if (!$id) {
            throw new InvalidArgumentException(
                _('Zur Erstellung der URL fehlt eine Gebäude-ID!')
            );
        }
        
        //There are some actions which can be handled by the general
        //resource controller:
        if (in_array($action, ['files', 'request', 'lock'])) {
            return parent::buildPathForAction($action, $id);
        }
        
        switch ($action) {
            case 'show':
                return 'dispatch.php/resources/building/index/' . $id;
            case 'add':
                return 'dispatch.php/resources/building/add';
            case 'edit':
                return 'dispatch.php/resources/building/edit/' . $id;
            case 'delete':
                return 'dispatch.php/resources/building/delete/' . $id;
            default:
                return parent::buildPathForAction($action, $id);
        }
    }
    
    /**
     * Returns the appropriate link for the building action that shall be
     * executed on a building.
     *
     * @param string $action The action which shall be executed.
     *     For buildings the actions 'show', 'booking_plan', 'add', 'edit'
     *     and 'delete' are defined.
     * @param string $id The ID of the building on which the specified
     *     action shall be executed.
     * @param $link_parameters @TODO
     *
     * @return string The Link for the building action.
     */
    public static function getLinkForAction(
        $action = 'show',
        $id = null,
        $link_parameters = []
    )
    {
        return URLHelper::getLink(
            self::buildPathForAction($action, $id),
            $link_parameters
        );
    }
    
    /**
     * Returns the appropriate URL for the building action that shall be
     * executed on a building.
     *
     * @param string $action The action which shall be executed.
     *     For buildings the actions 'show', 'booking_plan', 'add', 'edit'
     *     and 'delete' are defined.
     * @param string $id The ID of the building on which the specified
     *     action shall be executed.
     * @param $url_parameters @TODO
     * @return string The URL for the building action.
     */
    public static function getURLForAction(
        $action = 'show',
        $id = null,
        $url_parameters = []
    )
    {
        return URLHelper::getURL(
            self::buildPathForAction($action, $id),
            $url_parameters
        );
    }
    
    public function getRequiredPropertyNames()
    {
        return self::$required_properties;
    }
    
    public function __toString()
    {
        return $this->getFullName();
    }
    
    public function cbValidate()
    {
        if (!$this->findParentByClassName('Location')) {
            //Buildings must have a location as parent!
            throw new InvalidResourceException(
                sprintf(
                    _('Das Gebäude %1$s ist keinem Standort zugeordnet!'),
                    $this->name
                )
            );
        }
        
        if (!is_a($this->category->class_name, get_class($this), true)) {
            //Only resources with the Building category can be handled
            //with this class!
            throw new InvalidResourceException(
                sprintf(
                    _('Das Gebäude %1$s ist der falschen Ressourcen-Klasse zugeordnet!'),
                    $this->name
                )
            );
        }
        
        return true;
    }
    
    //property and shortcut methods:
    
    /**
     * Returns the full (localised) name of the building.
     *
     * @return string The full name of the building.
     */
    public function getFullName()
    {
        return sprintf(
            _('Gebäude %s'),
            $this->name
        );
    }
    
    /**
     * Returns the path for the building's image.
     * If the building has no image the path for a general
     * building icon will be returned.
     *
     * @return string The image path to the building's image.
     */
    public function getDefaultPictureUrl()
    {
        return $this->getIcon()->asImagePath();
    }
    
    public function getIcon($role = Icon::ROLE_INFO)
    {
        return Icon::create('home', $role);
    }
    
    public function checkHierarchy()
    {
        //We must check if this building has buildings as children
        //or rooms or buildings as parents. In any of those cases the hierarchy
        //is invalid!
        
        $children = $this->findChildrenByClassName('Building');
        if (count($children) > 0) {
            //At least one child anywhere below this building
            //resource is a building, too.
            return false;
        }
        
        $parents = ResourceManager::getHierarchy($this);
        //We do not need to check this element:
        array_shift($parents);
        foreach ($parents as $parent) {
            $parent = $parent->getDerivedClassInstance();
            if (($parent instanceof Building) || ($parent instanceof Room)) {
                //Hierarchy error
                return false;
            }
        }
        
        //If code execution reaches this point then
        //the hierarchy around this building is valid.
        return true;
    }
    
    /**
     * Returns the link for an action for this building.
     * This is the non-static variant of Building::getLinkForAction.
     *
     * @param string $action The action which shall be executed.
     *     For buildings the actions 'show', 'booking_plan', 'add', 'edit'
     *     and 'delete' are defined.
     * @param array $link_parameters Optional parameters for the link.
     *
     * @return string The Link for the building action.
     */
    public function getActionLink($action = 'show', $link_parameters = [])
    {
        return self::getLinkForAction(
            $action,
            $this->id,
            $link_parameters
        );
    }
    
    /**
     * Returns the URL for an action for this building.
     * This is the non-static variant of Building::getURLForAction.
     *
     * @param string $action The action which shall be executed.
     *     For buildings the actions 'show', 'booking_plan', 'add', 'edit'
     *     and 'delete' are defined.
     * @param array $link_parameters Optional parameters for the URL.
     *
     * @return string The URL for the building action.
     */
    public function getActionURL($action = 'show', $url_parameters = [])
    {
        return self::getURLForAction(
            $action,
            $this->id,
            $url_parameters
        );
    }
    
    /**
     * Retrieves the rooms which reside inside this building by looking up
     * the child resources of this building.
     *
     * @return Room[] An array with Room objects or an empty array
     *     if no rooms can be found.
     */
    public function findRooms()
    {
        $rooms = parent::findChildrenByClassName('Room', 0, true);
        
        $result = [];
        foreach ($rooms as $room) {
            if ($room instanceof Room) {
                $result[] = $room;
            }
        }
        return $result;
    }
    
    /**
     * Retrieves the location where this building is assigned to by looking up
     * the parent resources of this building.
     *
     * @return Location|null A Location object if it can be found,
     *     null otherwise.
     */
    public function findLocation()
    {
        $location = parent::findParentByClassName('Location');
        if ($location instanceof Resource) {
            return $location->getDerivedClassInstance();
        }
        return null;
    }
    
    /**
     * Adds a child resource to this building. The child resource
     * must not be a resource of the class Building or Location.
     *
     * @param Resource $resource The resource which shall be added as child.
     *
     * @return True, if the resource could be added as child, false otherwise.
     * @throws InvalidResourceException If the specified resource belongs to
     *     the resource classes Building or Location.
     *
     */
    public function addChild(Resource $resource)
    {
        if ($resource->class_name == $this->class_name) {
            throw new InvalidResourceException(
                _('Ein Gebäude darf keinem anderen Gebäude in der Hierarchie untergeordnet werden!')
            );
        } elseif ($resource->class_name == 'Location') {
            throw new InvalidResourceException(
                _('Ein Standort darf keinem Gebäude in der Hierarchie untergeordnet werden!')
            );
        }
        return parent::addChild($resource);
    }
    
    public function createSimpleBooking(
        User $user,
        DateTime $begin,
        DateTime $end,
        $preparation_time = 0,
        $description = '',
        $internal_comment = '',
        $booking_type = 0
    )
    {
        return null;
    }
    
    public function createBookingFromRequest(
        User $user,
        ResourceRequest $request,
        $preparation_time = 0,
        $description = '',
        $internal_comment = '',
        $booking_type = 0,
        $prepend_preparation_time = false,
        $notify_lecturers = false
    )
    {
        return null;
    }
    
    
    public function createBooking(
        User $user,
        $range_id = null,
        $time_ranges = [],
        $repetition_interval = null,
        $repetition_amount = 0,
        $repetition_end_date = null,
        $preparation_time = 0,
        $description = '',
        $internal_comment = '',
        $booking_type = 0,
        $force_booking = false
    )
    {
        return null;
    }
    
    public function createSimpleRequest(
        User $user,
        DateTime $begin,
        DateTime $end,
        $comment = '',
        $preparation_time = 0
    )
    {
        return null;
    }
    
    public function createRequest(
        User $user,
        $date_range_ids = null,
        $comment = '',
        $properties = [],
        $preparation_time = 0
    )
    {
        return null;
    }
    
    public function createLock(
        User $user,
        DateTime $begin,
        DateTime $end,
        $internal_comment = ''
    )
    {
        return null;
    }
    
    public function isAssigned(
        DateTime $begin,
        DateTime $end,
        $excluded_booking_ids = []
    )
    {
        return false;
    }
    
    public function isReserved(
        DateTime $begin,
        DateTime $end,
        $excluded_reservation_ids = []
    )
    {
        return false;
    }
    
    public function isLocked(
        DateTime $begin,
        DateTime $end,
        $excluded_lock_ids = []
    )
    {
        return true;
    }
    
    public function isAvailable(
        DateTime $begin,
        DateTime $end,
        $excluded_booking_ids = []
    )
    {
        return false;
    }
}
