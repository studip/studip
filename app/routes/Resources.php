<?php
namespace RESTAPI\Routes;

/**
 * This file contains the REST class for the
 * room and resource management system.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class Resources extends \RESTAPI\RouteMap
{

    //Resource routes:


    /**
     * Get a resource object.
     * @param derived_class: If the URL parameter derived_class is set
     *     the resource object is converted to an instance of the
     *     class that does correct handling of the resource object.
     *
     * @get /resources/resource/:resource_id
     */
    public function getResource($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        if (\Request::submitted('derived_classes')) {
            $resource = $resource->getDerivedClassInstance();
        }

        $result = $resource->toRawArray();

        $result['full_name'] = $resource->getFullName();
        $result['has_children'] = $resource->children ? true : false;

        return $result;
    }


    /**
     * Modifies a resource object.
     *
     * @put /resources/resource/:resource_id
     */
    public function editResource($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'autor')) {
            $this->halt(403);
            return;
        }

        $name = $this->data['name'];
        $description = $this->data['description'];
        $parent_id = $this->data['parent_id'];
        $properties = $this->data['properties'];

        if ($name) {
            $resource->name = $name;
        }
        if ($description) {
            $resource->description = $description;
        }
        if ($parent_id) {
            if (!Resource::exists($parent_id)) {
                $this->halt(
                    400,
                    'No resource exists with the ID \'' . $parent_id . '\'!'
                );
            }
            $resource->parent_id = $parent_id;
        }
        if ($properties) {
            foreach ($properties as $name => $value) {
                try {
                    $resource->setProperty($name, $value, $GLOBALS['user']->id);
                } catch (\AccessDeniedException $e) {
                    $this->halt(
                        403,
                        $e->getMessage()
                    );
                } catch (\Exception $e) {
                    $this->halt(
                        500,
                        $e->getMessage()
                    );
                }
            }
        }

        if ($resource->isDirty()) {
            if ($resource->store()) {
                return $resource->toRawArray();
            } else {
                $this->halt(
                    500,
                    'Error while saving the resource object!'
                );
            }
        }
        return $resource->toRawArray();
    }


    /**
     * Deletes a resource object.
     *
     * @delete /resources/resource/:resource_id
     */
    public function deleteResource($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'admin')) {
            $this->halt(403);
            return;
        }

        if (\Request::submitted('derived_classes')) {
            $resource = $resource->getDerivedClassInstance();
        }

        if ($resource->delete()) {
            return 'OK';
        } else {
            $this->halt(
                500,
                'Error while deleting the resource object!'
            );
        }
    }


    /**
     * Returns the child resources of a resource object, if they exist.
     *
     * @get /resources/resource/:resource_id/children
     */
    public function getResourceChildren($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        $use_derived_classes = (bool) \Request::submitted('derived_classes');

        $result = [];

        $children = \Resource::findBySql(
            'parent_id = :resource_id
    ORDER BY name ASC',
            [
                'resource_id' => $resource->id
            ]
        );
        if ($children) {
            foreach ($children as $child) {
                if ($use_derived_classes) {
                    $child = $child->getDerivedClassInstance();
                }
                $result[] = $child->toRawArray();
            }
        }
        return $result;
    }


    /**
     * Returns the parent resource of a resource object, if it exists.
     *
     * @get /resources/resource/:resource_id/parent
     */
    public function getResourceParent($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        if (!$resource->parent) {
            $this->notFound('This resource has no parent!');
        }

        $use_derived_classes = (bool) \Request::submitted('derived_classes');

        if ($use_derived_classes) {
            $parent = $resource->parent->getDerivedClassInstance();
            return $parent->toRawArray();
        }

        return $resource->parent->toRawArray();
    }


    /**
     * Get all property objects of a resource.
     *
     * @get /resources/resource/:resource_id/properties
     */
    public function getResourceProperties($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        $result = [];
        $properties = \ResourceProperty::findBySql(
            'INNER JOIN resource_property_definitions rpd
            ON resource_properties.property_id = rpd.property_id
            WHERE
            resource_properties.resource_id = :resource_id
            ORDER BY rpd.name ASC',
            [
                'resource_id' => $resource->id
            ]
        );

        if ($properties) {
            foreach ($properties as $property) {
                $data = $property->toRawArray();
                $data['name'] = $property->definition->name;
                $data['type'] = $property->definition->type;
                if ($data['type'] == 'position') {
                    //position properties also get the map-URL:
                    $data['map_url'] = \ResourceManager::getMapUrlForResourcePosition(
                        $property
                    );
                }
                $result[] = $data;
            }
        }

        return $result;
    }


    /**
     * Returns the booking plan of a resource for a week specified
     * by the parameters begin and end.
     *
     * @param begin: The begin timestamp of the time range for the booking plan.
     * @param end: The end timestamp of the time range for the booking plan.
     *
     * @allow_nobody
     *
     * @get /resources/resource/:resource_id/booking_plan
     */
    public function getResourceBookingPlan($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        $current_user = \User::findCurrent();
        $nobody_access = true;

        if ($current_user instanceof \User) {
            $nobody_access = false;
            if (!$resource->bookingPlanVisibleForUser($current_user)) {
                throw new \AccessDeniedException();
            }
        } elseif ($resource instanceof \Room) {
            if (!$resource->booking_plan_is_public) {
                throw new \AccessDeniedException();
            }
        }
        $user_is_resource_user = false;
        if ($current_user instanceof \User) {
            $user_is_resource_user = $resource->userHasPermission(
                $current_user,
                'user'
            );
        }

        $display_requests = false;
        if ($current_user instanceof \User) {
            $display_requests = \Request::get('display_requests');
        }
        $display_all_requests = \Request::get('display_all_requests');

        if ($display_all_requests && !$user_is_resource_user) {
            //The user is not allowed to see all requests.
            throw new \AccessDeniedException();
        }

        $begin_date = \Request::get('start');
        $end_date = \Request::get('end');
        if (!$begin_date || !$end_date) {
            //No time range specified.
            $this->halt(400, 'The parameters "start" and "end" are missing!');
            return;
        }

        //Try the ISO format first: YYYY-MM-DDTHH:MM:SS±ZZ:ZZ
        $begin = \DateTime::createFromFormat(\DateTime::RFC3339, $begin_date);
        $end = \DateTime::createFromFormat(\DateTime::RFC3339, $end_date);

        if (!($begin instanceof \DateTime) || !($end instanceof \DateTime)) {
            $begin = new \DateTime();
            $end = new \DateTime();
            //Assume the local timezone and use the Y-m-d format:
            $date_regex = '/[0-9]{4}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/';
            if (preg_match($date_regex, $begin_date)) {
                //$begin is specified in the date formay YYYY-MM-DD:
                $begin_str = explode('-', $begin_date);
                $begin->setDate(
                    intval($begin_str[0]),
                    intval($begin_str[1]),
                    intval($begin_str[2])
                );
                $begin->setTime(0,0,0);
            } else {
                $begin->setTimestamp($begin_date);
            }
            //Now we do the same for $end_timestamp:
            if (preg_match($date_regex, $end_date)) {
                //$begin is specified in the date formay YYYY-MM-DD:
                $end_str = explode('-', $end_date);
                $end->setDate(
                    intval($end_str[0]),
                    intval($end_str[1]),
                    intval($end_str[2])
                );
                $end->setTime(23,59,59);
            } else {
                $end->setTimestamp($end_date);
            }
        }

        //Get parameters:
        $booking_types = [];
        if (!$nobody_access) {
            $booking_types = \Request::getArray('booking_types');
        }

        $begin_timestamp = $begin->getTimestamp();
        $end_timestamp = $end->getTimestamp();

        //Get the event data sources:
        $bookings = \ResourceBooking::findByResourceAndTimeRanges(
            $resource,
            [
                [
                    'begin' => $begin_timestamp,
                    'end' => $end_timestamp
                ]
            ],
            $booking_types
        );
        $requests = [];
        if ($display_all_requests) {
            $requests = \ResourceRequest::findByResourceAndTimeRanges(
                $resource,
                [
                    [
                        'begin' => $begin_timestamp,
                        'end' => $end_timestamp
                    ]
                ],
                0
            );
        } elseif ($display_requests) {
            //Get the users own request only:
            $requests = \ResourceRequest::findByResourceAndTimeRanges(
                $resource,
                [
                    [
                        'begin' => $begin_timestamp,
                        'end' => $end_timestamp
                    ]
                ],
                0,
                [],
                'user_id = :user_id',
                ['user_id' => $current_user->id]
            );
        }

        $objects = array_merge($bookings, $requests);
        $event_data = \Studip\Fullcalendar::createData($objects, $begin_timestamp, $end_timestamp);

        if ($nobody_access) {
            //For nobody users, the code stops here since
            //nobody users are not allowed to include additional objects.
            return $event_data;
        }

        //Check if there are additional objects to be displayed:
        $additional_objects = \Request::getArray('additional_objects');
        $additional_object_colours = \Request::getArray('additional_object_colours');
        if ($additional_objects) {
            foreach ($additional_objects as $object_class => $object_ids) {
                if (!is_a($object_class, '\SimpleORMap', true)) {
                    continue;
                }
                if (!is_a($object_class, '\Studip\Calendar\EventSource', true)) {
                    continue;
                }

                $special_colours = [];
                if ($additional_object_colours[$object_class]) {
                    $special_colours = $additional_object_colours[$object_class];
                }

                $additional_objects = $object_class::findMany($object_ids);
                foreach ($additional_objects as $additional_object) {
                    $event_data = $additional_object->getFilteredEventData(
                        $current_user->id,
                        null,
                        null,
                        $begin,
                        $end
                    );

                    if ($special_colours) {
                        foreach ($event_data as $data) {
                            $data->text_colour = $special_colours['fg'];
                            $data->background_colour = $special_colours['bg'];
                            $data->editable = false;
                            $event_data[] = $data->toFullcalendarEvent();
                        }
                    }
                }
            }
        }
        return $event_data;
    }


    /**
     * Returns the booking plan of a resource for a selected semester.
     *
     * @param semester_id: The ID of the semester. Defaults to the current
     *     semester, if not set.
     *
     * @get /resources/resource/:resource_id/semester_plan
     */
    public function getResourceSemesterBookingPlan($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        $current_user = \User::findCurrent();

        if (!$resource->bookingPlanVisibleForUser($current_user)) {
            throw new \AccessDeniedException();
        }

        $user_is_resource_user = $resource->userHasPermission(
            $current_user,
            'user'
        );

        $display_requests = \Request::get('display_requests');
        $display_all_requests = \Request::get('display_all_requests');

        $begin = new \DateTime();
        $end = new \DateTime();

        $semester_id = \Request::get('semester_id');
        $semester = null;

        if ($semester_id) {
            $semester = \Semester::find($semester_id);
            if (!$semester) {
                $this->halt(404, 'Specified semester not found!');
            }
        } else {
            $semester = \Semester::findCurrent();
            if (!$semester) {
                $this->halt(500, 'Current semester not available!');
            }
        }

        if (\Request::get('semester_timerange') != 'fullsem') {
            $begin->setTimestamp($semester->vorles_beginn);
            $end->setTimestamp($semester->vorles_ende);
        } else {
            $begin->setTimestamp($semester->beginn);
            $end->setTimestamp($semester->ende);
        }

        //Get parameters:
        $booking_types = \Request::getArray('booking_types');

        $begin_timestamp = $begin->getTimestamp();
        $end_timestamp = $end->getTimestamp();

        //Get the event data sources:
        $bookings = \ResourceBooking::findByResourceAndTimeRanges(
            $resource,
            [
                [
                    'begin' => $begin_timestamp,
                    'end' => $end_timestamp
                ]
            ],
            $booking_types
        );

        $requests = [];
        if ($display_all_requests || $display_requests) {
            $requests_sql = "INNER JOIN seminar_cycle_dates scd
                USING (metadate_id)
                WHERE
                resource_id = :resource_id
                AND
                closed = '0' ";
            $requests_sql_params = [
                'begin' => $begin_timestamp,
                'end' => $end_timestamp,
                'resource_id' => $resource->id
            ];
            if (!$display_all_requests) {
                $requests_sql .= "AND user_id = :user_id ";
                $requests_sql_params['user_id'] = $current_user->id;
            }

            $requests = \ResourceRequest::findBySql(
                $requests_sql,
                $requests_sql_params
            );
        }

        $merged_objects = [];
        $metadates = [];

        foreach ($bookings as $booking) {
            $booking->resource = $resource;
            $irrelevant_booking = false;
            if ($booking->getRepetitionType() != 'weekly') {
                if (!\Request::get('display_single_bookings')) {
                    $irrelevant_booking = true;
                } else if ($booking->end < strtotime('today')) {
                    $irrelevant_booking = true;
                }
            }
            if ($booking->getAssignedUserType() === 'course' && in_array($booking->assigned_course_date->metadate_id, $metadates)) {
                $irrelevant_booking = true;
            };
            if (!$irrelevant_booking) {
                //It is an booking with repetitions that has to be included
                //in the semester plan.
                if (in_array($booking->getRepetitionType(), ['single','weekly'])) {
                    $event_list = $booking->convertToEventData([\ResourceBookingInterval::build(['interval_id' => md5(uniqid()), 'begin' => $booking->begin - $booking->preparation_time, 'end' => $booking->end])], $current_user);
                } else {
                    $event_list = $booking->getFilteredEventData(null,null,null,strtotime('today'), $end_timestamp);
                }
                foreach ($event_list as $event_data) {
                    if ($booking->getAssignedUserType() === 'course' && $booking->assigned_course_date->metadate_id) {
                        $index = sprintf(
                            '%1$s_%2$s_%3$s',
                            $booking->assigned_course_date->metadate_id,
                            $event_data->begin->format('NHis'),
                            $event_data->end->format('NHis')
                        );
                        $metadates[] = $booking->assigned_course_date->metadate_id;
                    } else {
                        $index = sprintf(
                            '%1$s_%2$s_%3$s',
                            $booking->id,
                            $event_data->begin->format('NHis'),
                            $event_data->end->format('NHis')
                        );
                    }

                    //Strip some data that cannot be used effectively in here:
                    $event_data->api_urls = [];
                    $event_data->editable = false;

                    $merged_objects[$index] = $event_data;
                }
            }
        }

        foreach ($requests as $request) {
            if ($request->cycle instanceof \SeminarCycleDate) {
                $cycle_dates = $request->cycle->getAllDates();
                foreach ($cycle_dates as $cycle_date) {
                    $relevant_request = $semester->beginn <= $cycle_date->date
                                     && $semester->ende >= $cycle_date->date;
                    if ($relevant_request) {
                        //We have found a date for the current semester
                        //that makes the request relevant.
                        break;
                    }
                }
                if (!$relevant_request) {
                    continue;
                }
                $event_data_list = $request->getFilteredEventData(
                    $current_user->id
                );

                foreach ($event_data_list as $event_data) {
                    $index = sprintf(
                        '%1$s_%2$s_%3$s',
                        $request->metadate_id,
                        $event_data->begin->format('NHis'),
                        $event_data->end->format('NHis')
                    );

                    //Strip some data that cannot be used effectively in here:
                    $event_data->view_urls = [];
                    $event_data->api_urls = [];

                    $merged_objects[$index] = $event_data;
                }
            }
        }

        //Convert the merged events to Fullcalendar events:
        $data = [];
        foreach ($merged_objects as $obj) {
            $data[] = $obj->toFullCalendarEvent();
        }

        return $data;
    }


    /**
     * Gets request of a resource. At your option the requests can be
     * limited to a specific time range, specified by the parameters
     * begin and end. Furthermore the requests can be filtered by user-ID.
     *
     * @param begin: A timestamp specifying the begin of the time range.
     * @param end: A timestamp specifying the end of the time range.
     * @param user_id: This parameter limits the result set to requests
     *     of the user specified by the user-ID provided in this parameter.
     *
     * @get /resources/resource/:resource_id/requests
     */
    public function getResourceRequests($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        $begin = $this->data['begin'];
        $end = $this->data['end'];
        $user_id = $this->data['user_id'];

        $sql = 'resource_id = :resource_id ';
        $sql_array = [
            'resource_id' => $resource->id
        ];

        if ($begin and $end) {
            $sql .= 'AND ((begin >= :begin AND begin <= :end)
                    OR
                    (end >= :begin AND end <= :end)) ';
            $sql_array['begin'] = $begin;
            $sql_array['end'] = $end;
        }

        if ($user_id) {
            $sql .= 'AND user_id = :user_id ';
            $sql_array['user_id'] = $user_id;
        }

        $sql .= 'ORDER BY mkdate ASC';

        $requests = \ResourceRequest::findBySql($sql, $sql_array);

        $result = [];
        foreach ($requests as $request) {
            $result[] = $request->toRawArray();
        }

        return $result;
    }


    /**
     *
     * @param begin: A timestamp specifying the begin of the time range.
     * @param end: A timestamp specifying the end of the time range.
     * @param user_id: This parameter limits the result set to bookings
     *     of the user specified by the user-ID provided in this parameter.
     * @param types: Limits the result to booking types specified in this
     *     parameter. The allowed types are comma separated like this: "1,2,3".
     *     The defined types are:
     *     0 = normal booking, 1 = reservation, 2 = lock.
     *
     * @get /resources/resource/:resource_id/bookings
     */
    public function getResourceBookings($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        $begin = \Request::get('begin');
        $end = \Request::get('end');
        $user_id = \Request::get('user_id');
        $types = [];
        $types_str = \Request::get('types');
        if ($types_str) {
            $types = explode(',', $types_str);
        }

        $sql = 'resource_id = :resource_id ';
        $sql_array = [
            'resource_id' => $resource->id
        ];

        if ($begin and $end) {
            $sql .= 'AND ((begin >= :begin AND begin <= :end)
                    OR
                    (end >= :begin AND end <= :end)) ';
            $sql_array['begin'] = $begin;
            $sql_array['end'] = $end;
        }

        if ($user_id) {
            $sql .= 'AND user_id = :user_id ';
            $sql_array['user_id'] = $user_id;
        }
        if ($types) {
            $sql .= 'AND booking_type IN ( :types ) ';
            $sql_array['types'] = $types;
        }

        $sql .= 'ORDER BY mkdate ASC';

        $bookings = \ResourceBooking::findBySql($sql, $sql_array);

        $result = [];
        if ($bookings) {
            foreach  ($bookings as $booking) {
                $result[] = $booking->toRawArray();
            }
        }

        return $result;
    }


    /**
     * Creates a booking/reservation/lock for a resource.
     *
     * @param begin: The begin timestamp for the booking.
     * @param end: The end timestamp for the booking.
     * @param preparation_time: The amount of seconds for preparation time
     *     before the begin timestamp.
     * @param internal_comment: A comment that is only visible for some
     *     parts of the staff.
     * @param booking_type: The booking type:
     *     0 = normal booking
     *     1 = reservation
     *     2 = lock
     *
     * @post /resources/resource/:resource_id/assign
     */
    public function createResourceBooking($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'user')) {
            throw new \AccessDeniedException();
        }

        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $preparation_time = \Request::int('preparation_time');
        $internal_comment = \Request::get('internal_comment');
        $booking_type = \Request::int('booking_type');

        $begin = new \DateTime();
        $begin->setTimestamp($begin_str);
        $end = new \DateTime();
        $end->setTimestamp($end_str);

        try {
            $booking = $resource->createSimpleBooking(
                \User::findCurrent(),
                $begin,
                $end,
                $preparation_time,
                $internal_comment,
                $booking_type
            );
            return $booking;
        } catch (\Exception $e) {
            $this->halt(
                400,
                $e->getMessage()
            );
        }
    }


    /**
     * Creates a resource request.
     *
     * @post /resources/resource/:resource_id/request_simple
     */
    public function createSimpleResourceRequest($resource_id)
    {
        $resource = \Resource::find($resource_id);
        if (!$resource) {
            $this->notFound('Resource object not found!');
        }

        $resource = $resource->getDerivedClassInstance();

        $user = \User::findCurrent();
        if (!$resource->userHasPermission($user, 'user')) {
            throw new \AccessDeniedException();
        }

        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $comment = \Request::get('comment');

        $begin = new \DateTime();
        $begin->setTimestamp($begin_str);
        $end = new \DateTime();
        $end->setTimestamp($end_str);

        try {
            $request = $resource->createSimpleRequest(
                $user,
                $begin,
                $end,
                $comment
            );
            return $request;
        } catch (\Exception $e) {
            $this->halt(
                400,
                $e->getMessage()
            );
        }
    }


    /**
     * Change the status of a resource booking interval:
     * @post /resources/booking_interval/:interval_id/toggle_takes_place
     */
    public function toggleResourceBookingIntervalTakesPlaceField($interval_id)
    {
        $interval = \ResourceBookingInterval::find($interval_id);
        if (!$interval) {
            $this->notFound('ResourceBookingInterval object not found!');
        }

        //Get the resource and check the permissions of the user:
        $resource = $interval->resource;
        if (!$resource) {
            $this->halt(500, 'ResourceBookingInterval not linked with a resource!');
        }

        $resource = $resource->getDerivedClassInstance();

        if (!$resource->userHasPermission(\User::findCurrent(), 'autor')) {
            $this->halt(403, 'You do not have sufficient permissions to modify the interval!');
        }

        //Switch the takes_place field:
        $interval->takes_place = $interval->takes_place ? '0' : '1';

        if ($interval->store()) {
            return [
                'takes_place' => $interval->takes_place
            ];
        } else {
            $this->halt(500, 'Error while storing the interval!');
        }
    }
}
