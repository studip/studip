<?php

/**
 * Room.class.php - model class for a resource which is a room
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
 * @property string id room-ID (equal to Resource.resource_id)
 * @property Building belongs_to building
 * @property string room_type resource property
 * @property int seats resource property
 * @property bool booking_plan_is_public resource property
 *
 * Other properties are inherited from the parent class (Resource).
 */


/**
 * The Room class is a derived class of the Resource class.
 * It containts specialisations for room resources.
 */
class Room extends Resource
{
    protected static $required_properties = [
        'room_type',
        'seats',
        'booking_plan_is_public'
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

        $config['additional_fields']['building']['get']   = 'findBuilding';
        $config['registered_callbacks']['before_store'][] = 'cbValidate';

        parent::configure($config);
    }


    public static function getTranslatedClassName($item_count = 1)
    {
        return ngettext(
            'Raum',
            'Räume',
            $item_count
        );
    }


    public static function countAll()
    {
        return self::countBySql(
            "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE rc.class_name IN ( :room_class_names )",
            [
                'room_class_names' => RoomManager::getAllRoomClassNames()
            ]
        );
    }


    public static function findAll()
    {
        return self::findBySql(
            "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE rc.class_name IN ( :room_class_names )
            ORDER BY sort_position DESC, name ASC, mkdate ASC",
            [
                'room_class_names' => RoomManager::getAllRoomClassNames()
            ]
        );
    }


    public static function findByNameOrBuilding($room, $building)
    {
        $params = [];

        $sql = "INNER JOIN resource_categories rc
                ON resources.category_id = rc.id";
        if ($building) {
            $sql .= " INNER JOIN resources pr
                    ON resources.parent_id = pr.id";
        }
        $sql .= " WHERE rc.class_name IN ( :room_class_names )";
        $params['room_class_names'] = RoomManager::getAllRoomClassNames();
        if ($room) {
            $sql                 .= " AND resources.name LIKE CONCAT('%', :room_name, '%')";
            $params['room_name'] = $room;
        }
        if ($building) {
            $sql                     .= " AND pr.name LIKE CONCAT('%', :building_name, '%')";
            $params['building_name'] = $building;
        }
        $sql .= " ORDER BY sort_position DESC, name ASC, mkdate ASC";

        return self::findBySql($sql, $params);
    }


    public static function getRequiredProperties()
    {
        return self::$required_properties;
    }


    /**
     * Finds rooms by a building specified by its ID.
     *
     * @param string $building_id The ID of the building.
     *
     * @return array An array with rooms or an empty array.
     */
    public static function findByBuilding(string $building_id)
    {
        $building = Building::find($building_id);
        if (!$building) {
            return [];
        }

        //Return all found Room objects below the building:
        return $building->findChildrenByClassName('Room', 0, true);
    }


    /**
     * Returns rooms that match the criteria of the room request.
     *
     * @param RoomRequest $request A RoomRequest object.
     * @param User $user The user who wishes to search for rooms matching
     *     the room request.
     * @param int $offset An offset for the result set.
     * @param int $limit A limit for the result set.
     * @param Room[] $searchable_rooms An (optional) array of rooms
     *     which will limit the search to the rooms in the array.
     * @param Array $properties An array providing request properties
     *     and their values in case the request doesn't have (the desired)
     *     properties set.
     *
     * @return Room[] An array of room resources.
     */
    public static function findByRoomRequestAndProperties(
        RoomRequest $request,
        User $user,
        $offset = 0,
        $limit = 0,
        $searchable_rooms = [],
        $properties = []
    )
    {
        //We have to check first if the user is permitted to search:
        //The user must have at least 'tutor' status in the
        //room and resource management:

        if (!ResourceManager::userHasGlobalPermission($user, 'tutor')) {
            throw new AccessDeniedException(
                _('Ihre Berechtigungen sind unzureichend, um Räume anhand einer Raumanfrage zu suchen!')
            );
        }

        if (!$properties) {
            $properties = [];
            foreach ($request->properties as $property) {
                $properties[$property->name] = $property->state;
            }
        }

        //Build the SQL query, based on the room request's properties
        //and the specified parameters:

        $sql = "INNER JOIN resource_properties rp
            ON resources.id = rp.resource_id
            INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE
            resources.requestable = '1'
            AND
            rc.class_name IN ( :room_class_names ) ";
        $sql_array = [
            'room_class_names' => RoomManager::getAllRoomClassNames()
        ];
        if (count($properties)) {
            $sql       .= "AND ( ";

            $property_c = 1;
            foreach ($properties as $name => $state) {
                if (!$state) {
                    continue;
                }
                $definition = ResourcePropertyDefinition::findOneBySql(
                    "INNER JOIN resource_category_properties rcp
                    WHERE rcp.category_id = :category_id
                    AND resource_property_definitions.name = :name",
                    [
                        'category_id' => $request->category_id,
                        'name'        => $name
                    ]
                );
                if (!$definition) {
                    //Such a property doesn't exist for the specified category.
                    continue;
                }

                if ($property_c > 1) {
                    $sql .= ' OR ';
                }
                $sql .= "(rp.property_id = :property_id$property_c ";

                //By looking at the definition we can determine if we have to do
                //a range search or a search for an exact match.
                if ($definition->range_search || $name == 'seats') {
                    $sql .= "AND rp.state >= :property_state$property_c";
                } else {
                    $sql .= "AND rp.state = :property_state$property_c";
                }

                $sql                                    .= ")";
                $sql_array["property_id$property_c"]    = $definition->id;
                $sql_array["property_state$property_c"] = $state;

                $property_c++;
            }

            $sql .= ") ";
        }

        if (!empty($searchable_rooms)) {
            $room_ids = [];
            foreach ($searchable_rooms as $room) {
                if ($room instanceof Room) {
                    $room_ids[] = $room->id;
                }
            }
            if ($room_ids) {
                $sql                   .= "AND resources.id IN ( :room_ids ) ";
                $sql_array['room_ids'] = $room_ids;
            } else {
                //We can't look for non-existing rooms:
                return [];
            }
        }

        $sql .= "GROUP BY resource_id ORDER BY resources.name ASC ";

        $offset = intval($offset);
        if ($offset > 0) {
            $sql                 .= "OFFSET :offset ";
            $sql_array['offset'] = $offset;
        }

        $limit = intval($limit);
        if ($limit > 0) {
            $sql                .= "LIMIT :limit ";
            $sql_array['limit'] = $limit;
        }

        return Room::findBySql($sql, $sql_array);
    }


    /**
     * Determins if the specified room is a room part
     * and then returns all other room parts.
     *
     * @param Room $room The room part whose other room parts shall be found.
     *
     * @return Room[] An array of room objects or an empty array
     *     if no other room parts can be found.
     */
    public static function findOtherRoomParts(Room $room)
    {
        $other_room_parts = [];

        $separable_room = SeparableRoom::findByRoomPart($room);
        if ($separable_room instanceof SeparableRoom) {
            $other_room_parts = $separable_room->findOtherRoomParts(
                [$room]
            );
        }

        return $other_room_parts;
    }


    /**
     * Helper method to return the SQL code for publicBookingPlansExists,
     * countByPublicBookingPlans and getByPublicBookingPlans.
     */
    protected static function getPublicBookingPlansSql()
    {
        return "INNER JOIN resource_categories AS rc
            ON resources.category_id = rc.id
        INNER JOIN resource_properties AS rp
            ON resources.id = rp.resource_id
        INNER JOIN resource_property_definitions AS rpd
            USING (property_id)
        WHERE rc.class_name IN ( :room_class_names )
            AND rpd.name = 'booking_plan_is_public'
            AND rp.state = '1'
        ORDER BY resources.name ASC, resources.mkdate ASC";
    }


    /**
     * Checks wheter rooms with public booking plans exist.
     *
     * @return bool True, if at least one room has a public booking plan,
     *     false otherwise.
     */
    public static function publicBookingPlansExists()
    {
        return Room::countBySql(
            self::getPublicBookingPlansSql(),
            ['room_class_names' => RoomManager::getAllRoomClassNames()]
        ) > 0;
    }


    /**
     * Retrieves all rooms that have a public booking plan,
     * ordered by name and creation date.
     *
     * @return array A list of rooms with public booking plans.
     */
    public static function findByPublicBookingPlans()
    {
        return self::findBySql(
            self::getPublicBookingPlansSql(),
            ['room_class_names' => RoomManager::getAllRoomClassNames()]
        );
    }


    /**
     * Retrieves all existing room types from the database.
     * Only room types which have at least one room object with that
     * type in the database are considered here.
     *
     * @return array An array consisting of all room types which
     *     exist in the database.
     */
    public static function getAllRoomTypes()
    {
        $db      = DBManager::get();
        $stmt = $db->prepare(
            "SELECT DISTINCT state FROM resource_properties
            INNER JOIN resource_property_definitions rpd
            USING (property_id)
            INNER JOIN resource_category_properties rcp
            USING (property_id)
            INNER JOIN resource_categories rc
            ON rcp.category_id = rc.id
            WHERE
            state != ''
            AND
            rc.class_name IN ( :room_class_names )
            AND
            rpd.name = 'room_type'
            ORDER BY state ASC"
        );
        $stmt->execute(
            ['room_class_names' => RoomManager::getAllRoomClassNames()]
        );
        return $stmt->fetchAll(
            PDO::FETCH_COLUMN,
            0
        );
    }

    /**
     * Returns the part of the URL for getLink and getURL which will be
     * placed inside the calls to URLHelper::getLink and URLHelper::getURL
     * in these methods.
     *
     * @param string $action The action for the room.
     * @param string $id The ID of the room.
     *
     * @return string The URL path for the specified action.
     * @throws InvalidArgumentException If $room_id is empty.
     *
     */
    protected static function buildPathForAction($action = 'show', $id = null)
    {
        if (!$id) {
            throw new InvalidArgumentException(
                _('Zur Erstellung der URL fehlt eine Raum-ID!')
            );
        }

        switch ($action) {
            case 'show':
                return 'dispatch.php/resources/room/index/' . $id;
            case 'add':
                return 'dispatch.php/resources/room/add';
            case 'edit':
                return 'dispatch.php/resources/room/edit/' . $id;
            case 'delete':
                return 'dispatch.php/resources/room/delete/' . $id;
            case 'request':
                return 'dispatch.php/resources/room_request/add/' . $id;
            case 'request_list':
                return 'dispatch.php/resources/room_request/overview?room_id=' . $id;
            case 'booking_plan':
                return 'dispatch.php/resources/room_planning/booking_plan/' . $id;
            default:
                //There are some actions which can be handled by the general
                //resource controller:
                return parent::buildPathForAction($action, $id);
        }
    }


    /**
     * Returns the appropriate link for the room action that shall be
     * executed on a room.
     *
     * @param string $action The action which shall be executed.
     *     For rooms the actions 'show', 'booking_plan', 'add', 'edit' and 'delete'
     *     are defined.
     * @param string $id The ID of the room on which the specified
     *     action shall be executed.
     * @param array $link_parameters Optional parameters for the link.
     *
     * @return string The Link for the room action.
     * @throws InvalidArgumentException If $room_id is empty.
     *
     */
    public static function getLinkForAction($action = 'show', $id = null, $link_parameters = [])
    {
        return URLHelper::getLink(
            self::buildPathForAction($action, $id),
            $link_parameters
        );
    }


    /**
     * Returns the appropriate URL for the room action that shall be
     * executed on a room.
     *
     * @param string $action The action which shall be executed.
     *     For rooms the actions 'show', 'booking_plan', 'add', 'edit' and 'delete'
     *     are defined.
     * @param string $id The ID of the room on which the specified
     *     action shall be executed.
     * @param array $url_parameters Optional parameters for the URL.
     *
     * @return string The URL for the room action.
     * @throws InvalidArgumentException If $room_id is empty.
     *
     */
    public static function getURLForAction($action = 'show', $id = null, $url_parameters = [])
    {
        return URLHelper::getURL(
            self::buildPathForAction($action, $id),
            $url_parameters
        );
    }


    public function cbValidate()
    {
        $building = $this->findParentByClassName('Building');
        if (!$building) {
            //Rooms must have parents! They have to be placed below buildings!
            throw new InvalidResourceException(
                sprintf(
                    _('Der Raum %1$s ist keinem Gebäude zugeordnet!'),
                    $this->name
                )
            );
        }

        if (!is_a($this->category->class_name, get_class($this), true)) {
            //Only resources with the Building category can be handled
            //with this class!
            throw new InvalidResourceException(
                sprintf(
                    _('Der Raum %1$s ist der falschen Ressourcen-Klasse zugeordnet!'),
                    $this->name
                )
            );
        }
        return true;
    }


    public function getRequiredPropertyNames()
    {
        return self::$required_properties;
    }


    public function __toString()
    {
        return $this->getFullName();
    }


    /**
     * This method calls Resource::createRequest and transforms the
     * resulting ResourceRequest object into a RoomRequest object.
     *
     * @return RoomRequest A room request object.
     * @see Resource::createRequest for paramter descriptions
     *     and thrown exceptions.
     * @inheritDoc
     */
    public function createRequest(
        User $user,
        $date_range_id = null,
        $comment = '',
        $properties = [],
        $preparation_time = 0
    )
    {
        $request = parent::createRequest(
            $user,
            $date_range_id,
            $comment,
            $properties,
            $preparation_time
        );

        return RoomRequest::build($request, false);
    }


    /**
     * Adds a child resource to this room. The child resource
     * must not be a resource of the class Room, Building or Location.
     *
     * @param Resource $resource The resource which shall be added as child.
     *
     * @return True, if the resource could be added as child, false otherwise.
     * @throws InvalidResourceException If the specified resource belongs to
     *     the resource classes Room, Building or Location.
     *
     */
    public function addChild(Resource $resource)
    {
        if ($resource->class_name == $this->class_name) {
            throw new InvalidResourceException(
                _('Ein Raum darf keinem anderen Raum in der Hierarchie untergeordnet werden!')
            );
        } elseif ($resource->class_name == 'Location') {
            throw new InvalidResourceException(
                _('Ein Standort darf keinem Raum in der Hierarchie untergeordnet werden!')
            );
        } elseif ($resource->class_name == 'Building') {
            throw new InvalidResourceException(
                _('Ein Gebäude darf keinem Raum in der Hierarchie untergeordnet werden!')
            );
        }
        return parent::addChild($resource);
    }


    /**
     * Returns the full name of this room.
     *
     * @return string The full name of this room.
     */
    public function getFullName()
    {
        return sprintf(
            _('Raum %s'),
            $this->name
        );
    }

    public function getDefaultPictureUrl()
    {
        return $this->getIcon()->asImagePath();
    }

    public function getIcon($role = Icon::ROLE_INFO)
    {
        return Icon::create('room', $role);
    }


    public function checkHierarchy()
    {
        //We must check if this room has rooms as children
        //or as parents. In any of those cases the hierarchy
        //is invalid!

        $children = $this->findChildrenByClassName('Room');
        if (count($children) > 0) {
            //At least one child anywhere below this room
            //resource is a room, too.
            return false;
        }

        $parents = ResourceManager::getHierarchy($this);
        //We do not need to check this element:
        array_shift($parents);
        foreach ($parents as $parent) {
            $parent = $parent->getDerivedClassInstance();
            if ($parent instanceof Room) {
                //Hierarchy error
                return false;
            }
        }

        //If code execution reaches this point then
        //the hierarchy around this room is valid.
        return true;
    }


    /**
     * @see Resource::bookingPlanVisibleForUser
     * @inheritDoc
     */
    public function bookingPlanVisibleForUser(User $user, $time_range = [])
    {
        return parent::bookingPlanVisibleForUser($user, $time_range)
            || $this->booking_plan_is_public;
    }


    /**
     * Returns the link for an action for this room.
     * This is the non-static variant of Room::getLinkForAction.
     *
     * @param string $action The action which shall be executed.
     *     For rooms the actions 'show', 'booking_plan', 'add', 'edit' and 'delete'
     *     are defined.
     * @param array $link_parameters Optional parameters for the link.
     * @return string @TODO
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
     * Returns the URL for an action for this room.
     * This is the non-static variant of Room::getURLForAction.
     *
     * @param string $action The action which shall be executed.
     *     For rooms the actions 'show', 'booking_plan', 'add', 'edit' and 'delete'
     *     are defined.
     * @param array $url_parameters Optional parameters for the URL.
     * @return string @TODO
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
     * Retrieves the building where this room resides in by looking up
     * the parent resources of this Room.
     *
     * @return Building|null A Building object if it can be found,
     *     null otherwise.
     */
    public function findBuilding()
    {
        $building = self::findParentByClassName('Building');
        if ($building instanceof Resource) {
            return $building->getDerivedClassInstance();
        }
        return null;
    }
}
