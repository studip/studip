<?php

/**
 * RoomManager.class.php
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
 */

/**
 * The RoomManager class contains methods that simplify the handling of rooms
 * which are one type of resources.
 */
class RoomManager
{
    public static function findRoomsByRequest(ResourceRequest $request, $excluded_room_ids = [])
    {
        $all_relevant_properties = $request->getPropertyData();
        $all_relevant_properties['room_category_id'] = $request->category_id;
        return self::findRooms(
            '',
            '',
            '',
            $all_relevant_properties,
            $request->getTimeIntervals(true),
            'name ASC, mkdate ASC',
            true,
            $excluded_room_ids
        );
    }


    /**
     * Helper method that creates the identical SQL query for the
     * countUserRooms and findUserRooms methods.
     */
    protected static function getUserRoomsSqlData(
        User $user,
        $level = 'user',
        $permanent_only = false,
        $time = null,
        $sql_conditions = '',
        $sql_condition_parameters = [],
        $sql_order_by = 'ORDER BY resources.sort_position DESC, resources.name ASC'
    )
    {
        $used_time = time();
        if ($time instanceof DateTime) {
            $used_time = $time->getTimestamp();
        } elseif ($time) {
            $used_time = $time;
        }

        $user_is_admin = (
            ResourceManager::userHasGlobalPermission(
                $user,
                'admin'
            ) ||
            $GLOBALS['perm']->have_perm('root')
        );

        $query = '';
        $data = [];
        if ($user_is_admin) {
            $query = "resources.category_id IN (:room_categories)
                ";
            if ($sql_conditions) {
                $query .= 'AND ' . $sql_conditions;
                if (count($sql_condition_parameters)) {
                    $data = $sql_condition_parameters;
                }
            }
        } else {
            $perms = ResourceManager::getHigherPermissionLevels($level);
            array_push($perms, $level);

            $query = "resources.category_id IN (:room_categories)
                AND
                resources.id IN (
                    SELECT resource_id FROM resource_permissions
                    WHERE user_id = :user_id
                    AND perms IN ( :perms ) ";
            $data = [
                'user_id' => $user->id,
                'perms' => $perms
            ];
            if (!$permanent_only) {
                $query .= " UNION
                    SELECT resource_id FROM resource_temporary_permissions
                    WHERE user_id = :user_id
                    AND perms IN ( :perms )
                    AND (begin <= :time)
                    AND (end >= :time) ";
                $data['time'] = $used_time;
            }
            $query .= ") ";

            if ($sql_conditions) {
                $query .= 'AND ' . $sql_conditions;
                if (count($sql_condition_parameters)) {
                    $data = array_merge($data, $sql_condition_parameters);
                }
            }
        }
        $data['room_categories'] = SimpleCollection::createFromArray(ResourceCategory::findAll())
            ->findBy('class_name', self::getAllRoomClassNames())
            ->pluck('id');
        $query .= " GROUP BY resources.id " . $sql_order_by;

        return [
            'query' => $query,
            'data' => $data
        ];
    }


    /**
     * Checks whether the specified user has at least one permanent or
     * temporary permission on at least one room. Root users and global resource
     * admins always have permanent permissions.
     *
     * @param User $user The user whose permissions shall be checkec.
     *
     * @param string $level The minimum permission level the user must have
     *     on a room so that it will be accepted for the check.
     *     Defaults to the "user" permission level.
     *
     * @param bool $permanent_only Whether to count only rooms with permanent
     *     permissions (true) or rooms with permanent and temporary
     *     permissions (false). Defaults to false.
     *
     * @param string $sql_conditions An optional SQL condition that will be
     *     placed in the WHERE block of the SQL query to filter
     *     the result set.
     *
     * @param array $sql_condition_parameters An optional associative array
     *     for the sql condition parameter containing variable parameters
     *     that shall be used in the SQL query.
     *
     * @param DateTime|int|null $time The timestamp for the check on
     *     temporary permissions. If this parameter is not set
     *     the current timestamp will be used.
     *
     * @returns bool True, when the user has permissions for at least one room,
     *     false otherwise.
     */
    public static function userHasRooms(
        User $user,
        $level = 'user',
        $permanent_only = false,
        $time = null,
        $sql_conditions = '',
        $sql_condition_parameters = []
    )
    {
        if ($GLOBALS['perm']->have_perm('root', $user->id)) {
            return true;
        }
        $sql = self::getUserRoomsSqlData($user, $level, $permanent_only, $time, $sql_conditions, $sql_condition_parameters, 'ORDER BY NULL');

        $db = DBManager::get();
        $exists_query = 'SELECT 1 FROM resources WHERE ' . $sql['query'] . ' LIMIT 1';
        $stmt = $db->prepare($exists_query);
        $stmt->execute($sql['data']);
        return $stmt->fetchColumn();
    }


    /**
     * Counts all rooms for which the specified user has permanent or
     * temporary permissions. Root users and global resource admins
     * get the amount of rooms in the Stud.IP system as result.
     *
     * @param User $user The user whose rooms shall be retrieved.
     *
     * @param string $level The minimum permission level the user must have
     *     on a room so that it will be included in the result set.
     *     Defaults to the "user" permission level.
     *
     * @param bool $permanent_only Whether to count only rooms with permanent
     *     permissions (true) or rooms with permanent and temporary
     *     permissions (false). Defaults to false.
     *
     * @param DateTime|int|null $time The timestamp for the check on
     *     temporary permissions. If this parameter is not set
     *     the current timestamp will be used.
     *
     * @param string $sql_conditions An optional SQL condition that will be
     *     placed in the WHERE block of the SQL query to filter
     *     the result set.
     *
     * @param array $sql_condition_parameters An optional associative array
     *     for the sql condition parameter containing variable parameters
     *     that shall be used in the SQL query.
     */
    public static function countUserRooms(
        User $user,
        $level = 'user',
        $permanent_only = false,
        $time = null,
        $sql_conditions = '',
        $sql_condition_parameters = []
    )
    {
        if (ResourceManager::userHasGlobalPermission($user, $level)) {
            //Count all rooms.
            $room_class_names = self::getAllRoomClassNames();
            return Room::countBySql(
                "INNER JOIN resource_categories
                ON resources.category_id = resource_categories.id
                WHERE
                resource_categories.class_name IN ( :room_class_names )",
                [
                    'room_class_names' => $room_class_names
                ]
            );
        }
        $sql = self::getUserRoomsSqlData($user, $level, $permanent_only, $time, $sql_conditions, $sql_condition_parameters);
        return Room::countBySql($sql['query'], $sql['data']);
    }


    /**
     * Retrieves all rooms for which the specified user has permanent or
     * temporary permissions. Root users and global resource admins
     * get a list of all rooms stored in the Stud.IP system.
     *
     * @param User $user The user whose rooms shall be retrieved.
     *
     * @param bool $permanent_only Whether to retrieve only rooms with permanent
     *     permissions (true) or rooms with permanent and temporary
     *     permissions (false). Defaults to false.
     *
     * @param string $level The minimum permission level the user must have
     *     on a room so that it will be included in the result set.
     *
     * @param string $sql_conditions An optional SQL condition that will be
     *     placed in the WHERE block of the SQL query to filter
     *     the result set.
     *
     * @param array $sql_condition_parameters An optional associative array
     *     for the sql condition parameter containing variable parameters
     *     that shall be used in the SQL query.
     *
     * @param DateTime|int|null $time The timestamp for the check on
     *     temporary permissions. If this parameter is not set
     *     the current timestamp will be used.
     * @return Room[]
     */
    public static function getUserRooms(
        User $user,
        $level = 'user',
        $permanent_only = false,
        $time = null,
        $sql_conditions = '',
        $sql_condition_parameters = []
    )
    {
        if (ResourceManager::userHasGlobalPermission($user, $level)) {
            //Return all rooms:
            $room_class_names = self::getAllRoomClassNames();
            return Room::findBySql(
                "INNER JOIN resource_categories
                ON resources.category_id = resource_categories.id
                WHERE
                resource_categories.class_name IN ( :room_class_names )
                GROUP BY resources.id
                ORDER BY resources.sort_position DESC, resources.name ASC",
                [
                    'room_class_names' => $room_class_names
                ]
            );
        }
        $sql = self::getUserRoomsSqlData($user, $level, $permanent_only, $time, $sql_conditions, $sql_condition_parameters);
        return Room::findBySql($sql['query'], $sql['data']);
    }


    /**
     * @param bool $notify_teachers True, if the teachers of the course where
     *     the room request belongs to shall be notified, false otherwise.
     *     This parameter is only useful for room requests made for courses
     *     and their dates.
     */
    public static function createRoomBookingsByRequest(
        Room $room,
        RoomRequest $room_request,
        User $user,
        $notify_teachers = true
    )
    {
        $time_intervals = $room_request->getTimeIntervals();

        $bookings = [];

        //Create a ResourceBooking for each time interval:
        foreach ($time_intervals as $interval) {
            $booking = new ResourceBooking();
            $booking->resource_id = $room->id;
            $booking->begin = $interval['begin'];
            $booking->end = $interval['end'];
            $booking->range_id = $user->id;
            $booking->preparation_time = $room_request->preparation_time;
            if ($booking->store()) {
                $bookings[] = $booking;
            }
        }

        //Notify the creator of the room request:
        if ($room_request->user) {
            //Send the message in the room request creator's preferred language:
            setTempLanguage($room_request->user->id);
            Message::send(
                '____%system%____',
                [$room_request->user->username],
                sprintf(
                    _('Ihre Raumanfrage für den Raum %1$s wurde von %2$s aufgelöst'),
                    $room->name,
                    $user->getFullName()
                ),
                sprintf(
                    _('Ihre Raumanfrage für den Raum %1$s wurde von %2$s aufgelöst.'),
                    $room->name,
                    $user->getFullName()
                )
            );
            restoreLanguage();
        }

        if ($notify_teachers) {
            //Notify the teachers of the course, but check first if the room
            //request belongs to a course:

            if ($room_request->course) {
                $lecturers = CourseMember::findByCourseAndStatus(
                    $room_request->course_id,
                    'dozent'
                );

                foreach ($lecturers as $lecturer) {
                    //Send the message in the lecturer's preferred language:
                    setTempLanguage($lecturer->user->id);
                    $message_body = sprintf(
                        _('Für die Veranstaltung %1$s wurden Raumbuchungen für die folgenden Termine erstellt:'),
                        $room_request->course->name
                    ) . "\n\n";

                    foreach ($time_intervals as $time_interval) {
                        $message_body .= '- '
                                       . sprintf(
                                           _('Am %1$s von %2$s Uhr bis %3$s Uhr'),
                                           date('d.m.Y', $time_interval['begin']),
                                           date('H:i', $time_interval['begin']),
                                           date('H:i', $time_interval['end'])
                                       )
                                       . "\n";
                    }

                    Message::send(
                        '____%system%____',
                        $lecturer->user->username,
                        sprintf(
                            _('Auflösung einer Raumanfrage in Veranstaltung %s'),
                            $room_request->course->name
                        ),
                        $message_body
                    );
                    restoreLanguage();
                }
            }
        }

        $room_request->closed = 2;
        $room_request->store();

        return $bookings;
    }


    public static function getBookingIntervalsForRoom(
        Room $room,
        DateTime $begin,
        DateTime $end,
        $booking_types = [0, 1, 2, 3],
        $building_booking_types = [0, 1, 2, 3],
        $exclude_canceled_intervals = true
    )
    {
        $intervals = [];

        if (!$booking_types && !$building_booking_types) {
            //No types specified => nothing to do.
            return $intervals;
        }

        if ($begin >= $end) {
            //Invalid time range specified => nothing to do either.
            return $intervals;
        }

        //Build the SQL query and the SQL parameters:
        $sql = "INNER JOIN resource_bookings
                ON resource_booking_intervals.booking_id = resource_bookings.id
                WHERE
                (
                    (
                        resource_booking_intervals.begin >= :begin
                        AND
                        resource_booking_intervals.begin <= :end
                    )
                    OR
                    (
                        resource_booking_intervals.end >= :begin
                        AND
                        resource_booking_intervals.end <= :end
                    )
                ) ";
        $sql_array = [
            'begin' => $begin->getTimestamp(),
            'end' => $end->getTimestamp(),
        ];

        if ($exclude_canceled_intervals) {
            $sql .= "AND resource_booking_intervals.takes_place = '1' ";
        }

        if ($booking_types) {
            $sql .= "AND (
                resource_booking_intervals.resource_id = :room_id
                AND
                resource_bookings.booking_type IN ( :booking_types )
            ) ";
            $sql_array['room_id'] = $room->id;
            $sql_array['booking_types'] = $booking_types;
        }
        if ($building_booking_types) {
            $sql .= "OR (
                resource_booking_intervals.resource_id = :building_id
                AND
                resource_bookings.booking_type IN ( :building_booking_types )
            ) ";
            $sql_array['building_id'] = $room->building->id;
            $sql_array['building_booking_types'] = $building_booking_types;
        }

        $sql .= "ORDER BY begin ASC, end ASC";

        $intervals = ResourceBookingInterval::findBySql($sql, $sql_array);
        return $intervals;
    }


    /**
     * Finds rooms by name, time range and which (at your option)
     * meet the requirements specified by other properties.
     * If a property isn't specified it won't be searched for.
     *
     * @param string $room_name The name of the room.
     * @param string $location_id The ID of the location where the room shall
     *     lie in.
     * @param string $building_id
     * @param string[] $properties An array of properties
     *     and their desired states.
     *     This array has the following structure:
     *     $properties['property_name'] = 'property_state';
     *     The array keys specify the property names, the entries of the array
     *     specify the desired state.
     *     Only the seats property may be a single value or an array with
     *     two entries. In the first case the room must have
     *     at least $seats seats. In the latter case the room must have between
     *     $seats[0] and $seats[1] seats.
     *     There are special pseudo properties that are handled by this array:
     *     - room_category_id: The id of a resource category
     *       that has 'Room' as class name.
     *
     * @param array $time_ranges Optional time ranges where the rooms have
     *     to be available. The format of the array is the following:
     *     [
     *         [
     *             'begin' => (begin timestamp or DateTime object),
     *             'end' => (end timestamp or DateTime object)
     *         ]
     *     ]
     * @param string $order_by An optional SQL snippet specifying the order of
     *     the results. Defaults to null (no sorting).
     * @param bool $only_requestable_rooms Whether the search shall be limited
     *     to requestable rooms only (true) or not (false).
     * @param array $excluded_room_ids
     * @return array
     */
    public static function findRooms(
        $room_name = '',
        $location_id = null,
        $building_id = null,
        $properties = [],
        $time_ranges = [],
        $order_by = null,
        $only_requestable_rooms = true,
        $excluded_room_ids = []
    )
    {
        $sql = "INNER JOIN resource_categories rc
                ON resources.category_id = rc.id
                WHERE rc.class_name IN ( :room_class_names ) ";

        $sql_array = [
            'room_class_names' => self::getAllRoomClassNames()
        ];

        if ($only_requestable_rooms) {
            $sql .= "AND resources.requestable > '0' ";
        }

        if ($room_name) {
            $sql .= "AND resources.name LIKE CONCAT('%', :room_name, '%') ";
            $sql_array['room_name'] = $room_name;
        }

        if ($properties['room_category_id']) {
            $sql .= "AND rc.id = :room_category_id ";
            $sql_array['room_category_id'] = $properties['room_category_id'];
        }
        if ($excluded_room_ids && is_array($excluded_room_ids)) {
            $sql .= " AND resources.id NOT IN ( :room_ids ) ";
            $sql_array['room_ids'] = $excluded_room_ids;
        }

        $sql .= " GROUP BY resources.name ";

        if ($order_by) {
            $sql .= "ORDER BY " . $order_by . ' ';
        }

        $rooms = Room::findBySql($sql, $sql_array);

        //We must filter the rooms after we retrieved them since
        //room bookings can have repetitions that we have to check.

        $filtered_rooms = [];
        if ($location_id || $building_id) {
            foreach ($rooms as $room) {
                //First check if the room lies in the building
                //before checking its availability:

                $building = $room->findParentByClassName('Building');

                if (!$building) {
                    //An invalid room: we don't want that either!
                    continue;
                }

                if ($building_id) {
                    if (is_array($building_id)) {
                        if (in_array($building->id, $building_id)) {
                            //We have found a room inside the building we are
                            //looking for.
                            $filtered_rooms[] = $room;
                            continue;
                        }
                    }

                    if ($building->id == $building_id) {
                        //We have found a room inside the building we are
                        //looking for.
                        $filtered_rooms[] = $room;
                        continue;
                    }
                }

                //If code execution reaches this point, we haven't found
                //a room yet, because no building has been specified.
                //We have to look for locations instead, if one is given.
                if ($location_id) {
                    $location = $building->findParentByClassName('Location');

                    if ($location_id && ($location->id == $location_id)) {
                        //The room lies in the location we're searching for.
                        $filtered_rooms[] = $room;
                    }
                }
            }
        } else {
            //There is no selection for a specific building or location.
            $filtered_rooms = $rooms;
        }

        $result = [];
        if (is_array($time_ranges)) {
            //We must check if the room is available:
            foreach ($filtered_rooms as $room) {
                $room_is_available = true;
                foreach ($time_ranges as $time_range) {
                    if (!$time_range['begin'] || !$time_range['end']) {
                        //Invalid format.
                        continue;
                    }
                    $begin = null;
                    if ($time_range['begin'] instanceof DateTime) {
                        $begin = $time_range['begin'];
                    } else {
                        $begin = new DateTime();
                        $begin->setTimestamp($time_range['begin']);
                    }
                    $end = null;
                    if ($time_range['end'] instanceof DateTime) {
                        $end = $time_range['end'];
                    } else {
                        $end = new DateTime();
                        $end->setTimestamp($time_range['end']);
                    }
                    if (!$room->isAvailable($begin, $end)) {
                        $room_is_available = false;
                        continue 2;
                    }
                }
                if ($room_is_available) {
                    $result[] = $room;
                }
            }
        } else {
            //No time frame has been specified.
            //All found rooms can be transferred to the result set.
            $result = $filtered_rooms;
        }

        //Filter out the special property room_category_id:
        unset($properties['room_category_id']);

        //Now we filter each room for the properties, if any:
        if (is_array($properties) && count($properties)) {
            $old_result = $result;
            $result = [];
            $required_property_c = count($properties);
            foreach ($old_result as $room) {
                $room_property_match = 0;
                foreach ($properties as $name => $state) {
                    $room_prop_state = $room->getProperty($name);
                    if ($room_prop_state === null) {
                        //No such property.
                        continue;
                    }
                    if (is_array($state)) {
                        //A range is specified. We must check if the
                        //value of the property lies in the range.
                        //Furthermore we must check if only minimum or maximum are
                        //set or if both are set. Depending on that condition,
                        //the conditions are different.
                        if ($state[0] and $state[1]) {
                            //Minimum and maximum are specified:
                            if (($room_prop_state >= $state[0]) && $room_prop_state <= $state[1]) {
                                $room_property_match++;
                            }
                        } elseif ($state[0]) {
                            //Only a minimum is given:
                            if ($room_prop_state >= $state[0]) {
                                $room_property_match++;
                            }
                        } elseif ($state[1]) {
                            //Only a maximum is given:
                            if ($room_prop_state <= $state[1]) {
                                $room_property_match++;
                            }
                        }
                    } elseif ($state) {
                        if ($room_prop_state == $state) {
                            $room_property_match++;
                        }
                    }
                }
                if ($room_property_match == $required_property_c) {
                    $result[] = $room;
                }
            }
        }

        //sort $result array by building name and then by room name:
        usort($result, function ($a, $b) {
            if ($a->building->name > $b->building->name) {
                return 1;
            } elseif ($a->building->name < $b->building->name) {
                return -1;
            } else {
                //building names are identical: compare room names:
                if ($a->name > $b->name) {
                    return 1;
                } elseif ($a->name < $b->name) {
                    return -1;
                } else {
                    //building names and room names are identical:
                    //order by mkdate
                    if ($a->mkdate > $b->mkdate) {
                        return 1;
                    } elseif ($a->mkdate < $b->mkdate) {
                        return -1;
                    } else {
                        //Even the mkdate is equal?
                        //Well then there is nothing else we can do here...
                        return 0;
                    }
                }
            }
        });

        return $result;
    }

    /**
     * Searches for room bookings where the room is overbooked
     * in the specified time range.
     * A room is overbooked if a resource booking exists for that room
     * which is associated with a course and the amount of course participants
     * is higher than the amount of seats in the room.
     *
     * @param DateTime $begin The begin of the time range.
     * @param DateTime $end The end of the time range.
     *
     * @returns SimpleORMapCollection A collection of overbooked room bookings
     *     as ResourceBooking objects.
     */
    public static function findOverbookedRoomBookings(DateTime $begin, DateTime $end)
    {
        //First we must check if $begin and $end specify a valid time range:
        if ($end <= $begin) {
            throw new InvalidArgumentException(
                _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
            );
        }

        //Now we query all room resources together with courses and
        //resource bookings:

        $found_bookings = ResourceBooking::findBySql(
            "INNER JOIN resources
                ON resource_bookings.resource_id = resources.id
                INNER JOIN resource_categories
                ON resources.category_id = resource_categories.id
                INNER JOIN resource_properties
                ON resources.id = resource_properties.resource_id
                INNER JOIN resource_property_definitions rpd
                ON resource_properties.property_id = rpd.property_id
                WHERE TRUE"
            /*
               INNER JOIN seminare
               ON resource_bookings.range_id = seminare.seminar_id
               WHERE
               TRUE"
               /*
               (resource_categories.class_name = 'Room' OR TRUE)
               AND
               (
               (
               resource_bookings.begin >= :begin
               AND
               resource_bookings.repeat_end <= :end
               )
               OR
               (
               resource_bookings.begin >= :begin
               AND
               resource_bookings.end <= :end
               )
               OR TRUE
               )
               AND
               (rpd.name = 'seats' OR TRUE)
               AND
               (resource_properties.state < (
               SELECT COUNT(user_id) FROM seminar_user
               WHERE seminar_user.seminar_id = seminare.seminar_id
               ) OR TRUE) ORDER BY name ASC",
               [
               'begin' => $begin->getTimestamp(),
               'end' => $end->getTimestamp()
               ]
             */
        );

        return $found_bookings;
        //SimpleORMapCollection::createFromArray($found_bookings);
    }


    /**
     * Retrieves all properties that are bound to room resource categories
     * or room resources directly.
     *
     * @param bool $only_searchable Whether only searchable properties
     *     shall be returned (true) or all properties shall be returned (false).
     *     Defaults to false.
     *
     * @param string[] $excluded_properties An array containing the names
     *     of the properties that shall be excluded from the result set.
     *
     * @returns ResourcePropertyDefinition[] An array of
     *     ResourcePropertyDefinition objects.
     */
    public static function getAllRoomPropertyDefinitions(
        $only_searchable = false,
        $excluded_properties = []
    )
    {
        if ($excluded_properties) {
            if (!is_array($excluded_properties)) {
                $excluded_properties = [$excluded_properties];
            }
        }

        $sql = "INNER JOIN resource_category_properties rcp
            USING (property_id)
            INNER JOIN resource_categories rc
            ON rcp.category_id = rc.id
            WHERE
            rc.class_name IN ( :room_class_names ) ";
        $sql_array = [
            'room_class_names' => self::getAllRoomClassNames()
        ];

        if ($only_searchable) {
            $sql .= "AND resource_property_definitions.searchable = '1' ";
        }

        if ($excluded_properties) {
            $sql .= "AND resource_property_definitions.name NOT IN (
                :excluded_properties
                ) ";
            $sql_array['excluded_properties'] = $excluded_properties;
        }

        $sql .= "GROUP BY
            resource_property_definitions.property_id
            ORDER BY
            resource_property_definitions.name ASC,
            resource_property_definitions.mkdate ASC;";

        return ResourcePropertyDefinition::findBySql(
            $sql, $sql_array
        );
    }


    /**
     * Returns the names of all hierarchy elements from the location to the
     * specified room.
     *
     * @param Room $room The room to start with.
     *
     * @param bool $all_items Whether to include all resource item names
     *     while traversing the tree upwards (true) or to only include the
     *     names of the room, the building and the location (false).
     *
     * @returns string[] An array with the names of the hierarchy elements,
     *     starting with the location name.
     */
    public static function getHierarchyNames(Room $room, $all_items = false)
    {
        $names = [$room->name];

        if ($all_items) {
            return ResourceManager::getHierarchyNames($room);
        } else {
            $building = $room->building;
            if ($building instanceof Building) {
                $names[] = $building->name;

                $location = $building->location;
                if ($location instanceof Location) {
                    $names[] = $location->name;
                }
            }
        }
        return array_reverse($names);
    }


    /**
     * Groups rooms by their location and building.
     *
     * @param Room[] $rooms An array of rooms that shall be grouped.
     *
     * @returns Array[][][] A three dimensional array with the whole
     *     hierarchy for the specified rooms.
     *     The array structure is as follows:
     *     [
     *         [
     *             'location' => Location object
     *             'buildings' => [
     *                 'building' => Building object
     *                 'rooms' => Array of room objects.
     *             ]
     *         ]
     *     ]
     */
    public static function groupRooms($rooms = [])
    {
        $grouped_rooms = [];
        foreach ($rooms as $room) {
            if (!$room instanceof Room) {
                continue;
            }
            $building = $room->building;
            if (!($building instanceof Building)) {
                //Invalid room
                continue;
            }
            $location = $building->location;
            if (!($location instanceof Location)) {
                //Invalid room
                continue;
            }
            if (!is_array($grouped_rooms[$location->id])) {
                $grouped_rooms[$location->id] = [
                    'location' => $location,
                    'buildings' => []
                ];
            }
            if (!is_array($grouped_rooms[$location->id]['buildings'][$building->id])) {
                $grouped_rooms[$location->id]['buildings'][$building->id] = [
                    'building' => $building,
                    'rooms' => []
                ];
            }
            $grouped_rooms[$location->id]['buildings'][$building->id]['rooms'][] = $room;
        }

        return $grouped_rooms;
    }


    /**
     * @returns string[] A list containing class names of all classes that are
     *     derived from the Room class and the Room class itself.
     */
    public static function getAllRoomClassNames()
    {
        $resource_class_names = ResourceManager::getAllResourceClassNames();
        $room_class_names = [];
        foreach($resource_class_names as $class_name) {
            if (is_a($class_name, 'Room', true)) {
                $room_class_names[] = $class_name;
            }
        }
        return $room_class_names;
    }


    /**
     * This method creates the SQL data for the methods countRequestableRooms
     * and findRequestableRooms.
     *
     * @param array time_ranges[][] The time ranges in which a requestable room
     *     must be available to be included in the result set. This is a
     *     two-dimensional array where the second dimension is an associative
     *     array with two indexes: "begin" and "end". These can either be
     *     timestamps or DateTime objects representing the begin and end
     *     of the time range, respectively.
     *
     * @returns array An associative array with two indexes:
     *     - sql: The SQL query as string.
     *     - sql_params: An associative array with all parameters for the query.
     */
    protected static function getRequestableRoomsSqlData(array $time_ranges = [])
    {
        $sql = "INNER JOIN `resource_categories` rc
                ON `resources`.`category_id` = rc.id ";
        if ($time_ranges) {
            $sql .= "INNER JOIN `resource_booking_intervals` rbi
                     ON `resources`.`id` = rbi.`resource_id` ";
        }
        $sql .= "WHERE `rc`.`class_name` IN ( :room_class_names )
                 AND `resources`.`requestable` > '0' ";

        $sql_params = [
            'room_class_names' => self::getAllRoomClassNames()
        ];

        if (!$time_ranges) {
            $sql .= 'ORDER BY `resources`.`name` ASC';
            return [
                'sql' => $sql,
                'sql_params' => $sql_params
            ];
        }

        $sql .= "AND (";

        $i = 1;
        foreach ($time_ranges as $time_range) {
            if (!is_array($time_range)) {
                continue;
            }
            if ($time_range['begin'] >= $time_range['end']) {
                continue;
            }

            $begin = $time_range['begin'];
            if ($time_range['begin'] instanceof DateTime) {
                $begin = $time_range['begin']->getTimestamp();
            }
            $end = $time_range['end'];
            if ($time_range['end'] instanceof DateTime) {
                $end = $time_range['end']->getTimestamp();
            }

            if ($i > 1) {
                $sql .= 'AND ';
            }
            $sql .= "(rbi.`begin` NOT BETWEEN :begin$i AND :end$i
                     AND rbi.`end` NOT BETWEEN :begin$i AND :end$i)";
            $sql_params["begin$i"] = $begin;
            $sql_params["end$i"] = $end;

            $i++;
        }
        $sql .= ') GROUP BY `resources`.`id` ORDER BY `resources`.`name` ASC';

        return [
            'sql' => $sql,
            'sql_params' => $sql_params
        ];
    }


    public static function countRequestableRooms(array $time_ranges = [])
    {
        $sql_data = self::getRequestableRoomsSqlData($time_ranges);
        return Room::countBySql($sql_data['sql'], $sql_data['sql_params']);
    }


    public static function findRequestableRooms(array $time_ranges = [])
    {
        $sql_data = self::getRequestableRoomsSqlData($time_ranges);
        return Room::findBySql($sql_data['sql'], $sql_data['sql_params']);
    }
}
