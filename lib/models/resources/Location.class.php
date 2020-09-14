<?php

/**
 * Location.class.php - model class for a resource which is a location
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
 * @property string id location-ID (equal to Resource.resource_id)
 * Other properties are inherited from the parent class (Resource).
 */
class Location extends Resource
{
    protected static $required_properties = [
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
        
        $config['additional_fields']['buildings']['get'] = 'findBuildings';
        
        $config['additional_fields']['director'] = [
            'get' => 'getPropertyRelatedObject',
            'set' => 'setPropertyRelatedObject'
        ];
        
        $config['registered_callbacks']['before_store'][] = 'cbValidate';
        parent::configure($config);
    }
    
    public static function getTranslatedClassName($item_count = 1)
    {
        return ngettext(
            'Standort',
            'Standorte',
            $item_count
        );
    }
    
    /**
     * Returns all locations which are stored in the database.
     *
     * @return Location[] An array of Location objects.
     */
    public static function findAll()
    {
        return self::findBySql(
            "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE rc.class_name = 'Location'
            ORDER BY sort_position DESC, name ASC, mkdate ASC"
        );
    }
    
    /**
     * Returns the part of the URL for getLink and getURL which will be
     * placed inside the calls to URLHelper::getLink and URLHelper::getURL
     * in these methods.
     */
    protected static function buildPathForAction($action = 'show', $id = null)
    {
        if (!$id) {
            throw new InvalidArgumentException(
                _('Zuer Erstellung der URL fehlt eine Standort-ID!')
            );
        }
        
        switch ($action) {
            case 'show':
                return 'dispatch.php/resources/location/index/' . $id;
            case 'add':
                return 'dispatch.php/resources/location/add';
            case 'edit':
                return 'dispatch.php/resources/location/edit/' . $id;
            case 'delete':
                return 'dispatch.php/resources/location/delete/' . $id;
            default:
                //There are some actions which can be handled by the general
                //resource controller:
                return parent::buildPathForAction($action, $id);
        }
    }
    
    /**
     * Returns the appropriate link for the location action that shall be
     * executed on a location.
     *
     * @param string $action The action which shall be executed.
     *     For locations the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param string $id The ID of the location on which the specified
     *     action shall be executed.
     * @param array $link_parameters Optional parameters for the link.
     *
     * @return string The Link for the location action.
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
     * Returns the appropriate URL for the location action that shall be
     * executed on a location.
     *
     * @param string $action The action which shall be executed.
     *     For locations the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param string $id The ID of the location on which the specified
     *     action shall be executed.
     * @param array $url_parameters Optional parameters for the URL.
     *
     * @return string The URL for the location action.
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
        if ($this->parent_id) {
            //Locations must not have parents! They are a top layer resource!
            throw new InvalidResourceException(
                sprintf(
                    _('Der Standort %1$s darf keiner anderen Ressource untergeordnet sein!'),
                    $this->name
                )
            );
        }
        
        if (!is_a($this->category->class_name, get_class($this), true)) {
            //Only resources with the Location category can be handled
            //with this class!
            throw new InvalidResourceException(
                sprintf(
                    _('Der Standort %1$s ist der falschen Ressourcen-Klasse zugeordnet!'),
                    $this->name
                )
            );
        }
    }
    
    /**
     * Returns the full (localised) name of the location.
     *
     * @return string The full name of the location.
     */
    public function getFullName()
    {
        return sprintf(
            _('Standort %s'),
            $this->name
        );
    }
    
    public function getDefaultPictureUrl()
    {
        return $this->getIcon()->asImagePath();
    }
    
    public function getIcon($role = Icon::ROLE_INFO)
    {
        return Icon::create('place', $role);
    }
    
    public function checkHierarchy()
    {
        //We must check if this location has locations as children
        //or rooms, buildings or locations as parents.
        //In any of those cases the hierarchy is invalid!
        
        $children = $this->findChildrenByClassName('Location');
        if (count($children) > 0) {
            //At least one child anywhere below this location
            //resource is a location, too.
            return false;
        }
        
        $parents = ResourceManager::getHierarchy($this);
        //We do not need to check this element:
        array_shift($parents);
        foreach ($parents as $parent) {
            $parent = $parent->getDerivedClassInstance();
            if (($parent instanceof Location) || ($parent instanceof Building)
                || ($parent instanceof Room)) {
                //Hierarchy error
                return false;
            }
        }
        
        //If code execution reaches this point then
        //the hierarchy around this location is valid.
        return true;
    }
    
    /**
     * Returns the link for an action for this building.
     * This is the non-static variant of Building::getLinkForAction.
     *
     * @param string $action The action which shall be executed.
     *     For locations the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
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
     * Returns the URL for an action for this location.
     * This is the non-static variant of Location::getURLForAction.
     *
     * @param string $action The action which shall be executed.
     *     For locations the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param array $url_parameters Optional parameters for the URL.
     * @return string
     */
    public function getActionURL($action = 'show', $url_parameters = [])
    {
        return self::getURLForAction(
            $action,
            $this->id,
            $url_parameters
        );
    }
    
    // Relation methods:
    
    /**
     * Retrieves the buildings which are associated to this location
     * by looking up the child resources of this location.
     *
     * @return Building[] An array with Building objects or an empty array
     *     if no buildings can be found.
     */
    public function findBuildings()
    {
        $buildings = parent::findChildrenByClassName('Building');
        
        $result = [];
        foreach ($buildings as $building) {
            $result[] = Building::toObject($building);
        }
        return $result;
    }
    
    /**
     * Adds a child resource to this location. The child resource
     * must not be a resource of the Location class.
     *
     * @param Resource $resource The resource which shall be added as child.
     *
     * @return True, if the resource could be added as child, false otherwise.
     * @throws InvalidResourceException If the specified resource belongs to
     *     the Location resource class.
     *
     */
    public function addChild(Resource $resource)
    {
        if ($resource->class_name == $this->class_name) {
            throw new InvalidResourceException(
                _('Ein Standort darf keinem anderen Standort in der Hierarchie untergeordnet werden!')
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
