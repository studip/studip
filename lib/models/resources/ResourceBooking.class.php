<?php

/**
 * ResourceBooking.class.php - model class for resource bookings
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
 */


/**
 * The ResourceBooking class is responsible for storing
 * bookings of resources in a specified time range
 * or a time interval.
 *
 * @property string id database column
 * @property string resource_id database column
 * @property string range_id database column
 *     The user, course etc. where the booking (booking)
 *     is associated with.
 * @property string booking_user_id database column
 *     The user who created the booking (booking).
 * @property string description database column
 * @property string begin database column
 * @property string end database column
 * @property string booking_type database column: The booking type.
 *     The following types are defined:
 *     0 = normal booking
 *     1 = reservation
 *     2 = lock
 *     3 = planned booking (reservation from external tools)
 *
 * @property string repeat_end database column
 * @property string repeat_quantity database column
 * @property string repetition_interval database column
 *     The repetition_interval column contains a date interval string in a
 *     format that is accepted by the DateInterval class constructor.
 *     Examples for values of the repetition_interval column:
 *     - For an one month interval, the value is "P1M".
 *     - For an interval of two days, the value is "P2D".
 *     See the DateInterval documentation for more examples:
 *     https://secure.php.net/manual/en/class.dateinterval.php
 *
 * @property string internal_comment database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property Resource resource belongs_to Resource
 * @property User assigned_user belongs_to User
 * @property CourseDate assigned_course_date belongs_to CourseDate
 */
class ResourceBooking extends SimpleORMap implements PrivacyObject, Studip\Calendar\EventSource
{
    private $assigned_user_type;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_bookings';

        $config['belongs_to']['resource'] = [
            'class_name' => 'Resource',
            'foreign_key' => 'resource_id',
            'assoc_func' => 'find'
        ];
        $config['belongs_to']['assigned_user'] = [
            'class_name' => 'User',
            'foreign_key' => 'range_id',
            'assoc_func' => 'find'
        ];
        $config['belongs_to']['assigned_course_date'] = [
            'class_name' => 'CourseDate',
            'foreign_key' => 'range_id',
            'assoc_func' => 'find'
        ];
        $config['has_many']['time_intervals'] = [
            'class_name' => 'ResourceBookingInterval',
            'assoc_foreign_key' => 'booking_id',
            'on_delete' => 'delete'
        ];
        $config['belongs_to']['booking_user'] = [
            'class_name' => 'User',
            'foreign_key' => 'booking_user_id',
            'assoc_func' => 'find'
        ];

        $config['additional_fields']['course_id'] = ['assigned_course_date', 'range_id'];
        $config['additional_fields']['room_name'] = ['resource', 'name'];

        $config['registered_callbacks']['after_store'][] = 'updateIntervals';
        $config['registered_callbacks']['after_store'][] = 'createStoreLogEntry';
        $config['registered_callbacks']['after_delete'][] = 'sendDeleteNotification';
        $config['registered_callbacks']['after_delete'][] = 'createDeleteLogEntry';

        //In regard to TIC 6460:
        //As long as TIC 6460 is not implemented, we must add the validate
        //method as a callback before storing the object.
        if (!method_exists('SimpleORMap', 'validate')) {
            $config['registered_callbacks']['before_store'][] = 'validate';
        }

        parent::configure($config);
    }


    public function createStoreLogEntry()
    {
        if ($this->isSimpleBooking()) {
            StudipLog::log(
                'RES_ASSIGN_SINGLE',
                $this->resource_id,
                null,
                $this->__toString(),
                null,
                $GLOBALS['user']->id
            );
        } else {
            StudipLog::log(
                'RES_ASSIGN_SEM',
                $this->resource_id,
                $this->range_id,
                $this->__toString(),
                null,
                $GLOBALS['user']->id
            );
        }
    }


    public function createDeleteLogEntry()
    {
        if ($this->isSimpleBooking()) {
            StudipLog::log(
                'RES_ASSIGN_DEL_SINGLE',
                $this->resource_id,
                null,
                $this->__toString(),
                null,
                $GLOBALS['user']->id
            );
        } else {
            StudipLog::log(
                'RES_ASSIGN_DEL_SEM',
                $this->resource_id,
                $this->range_id,
                $this->__toString(),
                null,
                $GLOBALS['user']->id
            );
        }
    }


    /**
     * Internal method that generated the SQL query used in
     * findByResourceAndTimeRanges and countByResourceAndTimeRanges.
     *
     * @see findByResourceAndTimeRanges
     * @inheritDoc
     */
    protected static function buildResourceAndTimeRangesSqlQuery(
        Resource $resource,
        $time_ranges = [],
        $booking_types = [],
        $excluded_booking_ids = []
    )
    {
        if (!is_array($time_ranges)) {
            throw new InvalidArgumentException(
                _('Es wurde keine Liste mit Zeiträumen angegeben!')
            );
        }

        //Check the array:
        foreach ($time_ranges as $time_range) {
            if ($time_range['begin'] > $time_range['end']) {
                throw new InvalidArgumentException(
                    _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
                );
            }

            if ($time_range['begin'] == $time_range['end']) {
                throw new InvalidArgumentException(
                    _('Startzeitpunkt und Endzeitpunkt dürfen nicht identisch sein!')
                );
            }
        }

        $sql_params = [
            'resource_id' => $resource->id
        ];

        //First we build the SQL snippet for the case that the $booking_type
        //variable is set to something different than null.
        $booking_type_sql = '';
        if (is_array($booking_types) && count($booking_types)) {
            $booking_type_sql = ' AND booking_type IN ( :booking_types )';
            $sql_params['booking_types'] = $booking_types;
        }

        //Then we build the snippet for excluded booking IDs, if specified.
        $excluded_booking_ids_sql = '';
        if (is_array($excluded_booking_ids) && count($excluded_booking_ids)) {
            $excluded_booking_ids_sql = ' AND resource_booking_intervals.booking_id NOT IN ( :excluded_ids ) ';
            $sql_params['excluded_ids'] = $excluded_booking_ids;
        }

        //Now we build the SQL snippet for the time intervals.
        //These are repeated four times in the query below.
        //First we use one template ($time_sql_template) and
        //replace NUMBER with our counting variable $i.
        //BEGIN and END are replaced below since the columns for
        //BEGIN and END are different in the four cases where we
        //repeat the SQL snippet for the time intervals.
        $time_sql_template = '
            (resource_booking_intervals.begin < :endNUMBER AND :beginNUMBER < resource_booking_intervals.end)
            ';

        $time_sql = '';
        if ($time_ranges) {
            $time_sql = ' AND (';

            $i = 1;
            foreach ($time_ranges as $time_range) {
                if ($i > 1) {
                    $time_sql .= ' OR ';
                }
                $time_sql .= str_replace(
                    'NUMBER',
                    $i,
                    $time_sql_template
                );

                if ($time_range['begin'] instanceof DateTime) {
                    $sql_params[('begin' . $i)] = $time_range['begin']->getTimestamp();
                } else {
                    $sql_params[('begin' . $i)] = $time_range['begin'];
                }
                if ($time_range['end'] instanceof DateTime) {
                    $sql_params[('end' . $i)] = $time_range['end']->getTimestamp();
                } else {
                    $sql_params[('end' . $i)] = $time_range['end'];
                }

                $i++;
            }

            $time_sql .= ') ';
        }

        //Check if the booking has a start and end timestamp set
        //or if it has repetitions that have a matching timestamp.
        //This is done in the rest of the SQL query:

        $whole_sql = "resource_bookings.id IN (
                    SELECT resource_booking_intervals.booking_id FROM resource_booking_intervals WHERE
                    resource_booking_intervals.resource_id = :resource_id
                    AND resource_booking_intervals.takes_place = 1"
                    . $excluded_booking_ids_sql
                    . $time_sql
                    . " GROUP BY resource_booking_intervals.booking_id ORDER BY NULL)
                    $booking_type_sql
";

        return [
            'sql' => $whole_sql,
            'params' => $sql_params
        ];
    }


    /**
     * Retrieves all resource booking for the given resource and
     * time range. By default, all booking are returned.
     * To get only bookings of a certain type
     * set the $booking_type parameter.
     *
     * @param Resource $resource The resource whose requests shall be retrieved.
     * @param array $time_ranges An array with time ranges as DateTime objects.
     *     The array has the following structure:
     *     [
     *         [
     *             'begin' => begin timestamp,
     *             'end' => end timestamp
     *         ],
     *         ...
     *     ]
     * @param array $booking_types An optional specification for the
     *     booking_type column in the database. More than one booking
     *     type can be specified.
     *     By default this is set to an empty array which means
     *     that resource booking are not filtered by the type column.
     *     The allowed resource booking types are specified in the
     *     class documentation.
     *
     * @param array $excluded_booking_ids An array of strings representing
     *     resource booking IDs. IDs specified in this array are excluded
     *     from the search.
     * @return ResourceBooking[] An array of ResourceRequest objects.
     *     If no requests can be found, the array is empty.
     *
     * @throws InvalidArgumentException, if the time ranges are either not in an
     *     array matching the format description from above or if one of the
     *     following conditions is met in one of the time ranges:
     *     - begin > end
     *     - begin == end
     */
    public static function findByResourceAndTimeRanges(
        Resource $resource,
        $time_ranges = [],
        $booking_types = [],
        $excluded_booking_ids = []
    )
    {
        //Build the SQL query and the parameter array.

        $sql_data = self::buildResourceAndTimeRangesSqlQuery(
            $resource,
            $time_ranges,
            $booking_types,
            $excluded_booking_ids
        );

        //Call findBySql:
        return self::findBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * Counts all resource bookings for the specified resource and
     * time range. By default, all bookings are counted.
     * To count only bookings of a certain type
     * set the $booking_type parameter.
     *
     * @see findByResourceAndTimeRanges
     * @inheritDoc
     */
    public static function countByResourceAndTimeRanges(
        Resource $resource,
        $time_ranges = [],
        $booking_types = [],
        $excluded_booking_ids = []
    )
    {
        //Build the SQL query and the parameter array.

        $sql_data = self::buildResourceAndTimeRangesSqlQuery(
            $resource,
            $time_ranges,
            $booking_types,
            $excluded_booking_ids
        );

        //Call countBySql:
        return self::countBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * Deletes all resource bookings for the specified resource and
     * time range. By default, all bookings are counted.
     * To count only bookings of a certain type
     * set the $booking_type parameter.
     *
     * @see findByResourceAndTimeRanges
     * @inheritDoc
     */
    public static function deleteByResourceAndTimeRanges(
        Resource $resource,
        $time_ranges = [],
        $booking_types = [],
        $excluded_booking_ids = []
    )
    {
        //Build the SQL query and the parameter array.
        $sql_data = self::buildResourceAndTimeRangesSqlQuery(
            $resource,
            $time_ranges,
            $booking_types,
            $excluded_booking_ids
        );

        //Call deleteBySql:
        return self::deleteBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * The SimpleORMap::store method is overloaded to allow forced booking
     * of resource bookings, meaning that all other bookings of the
     * resource of a booking are deleted when they overlap with this booking.
     *
     * @param bool $force_booking Whether booking shall be forced (true)
     *     or not (false). Defaults to false.
     * @return bool
     */
    public function store($force_booking = false)
    {
        if ($force_booking == true) {
            $this->deleteOverlappingBookings();
        }
        $this->deleteOverlappingReservations();

        if (parent::store()) {
            //Check if the booking is bound to a course date.
            //If this is the case, check for existing bookings
            //and delete them, so that there is only one booking
            //for a course date:
            $course_date_exists = CourseDate::exists($this->range_id);
            if ($course_date_exists) {
                self::deleteBySql(
                    'range_id = :range_id AND id <> :this_id',
                    [
                        'this_id' => $this->id,
                        'range_id' => $this->range_id
                    ]
                );
            }
            return true;
        }
        return false;
    }


    /**
     * This validation method is called before storing an object.
     */
    public function validate()
    {
        if ((!$this->resource_id) || !($this->resource instanceof Resource)) {
            throw new InvalidArgumentException(
                _('Es wurde keine Ressource zur Buchung angegeben!')
            );
        }

        if (!$this->range_id && !$this->description) {
            throw new ResourceBookingRangeException(
                _('Es muss eine Person oder ein Text zur Buchung eingegeben werden!')
            );
        }

        if ((!$this->booking_user_id) || !($this->booking_user instanceof User)) {
            /*throw new InvalidArgumentException(
                _('Die buchende Person wurde nicht gesetzt!')
            );*/
            $this->booking_user = User::findCurrent();
        }

        if ($this->begin >= $this->end) {
            throw new InvalidArgumentException(
                _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
            );
        }

        if ($this->repetition_interval) {
            if (!($this->repeat_quantity || $this->repeat_end)) {
                throw new InvalidArgumentException(
                    _('Es wurde ein Wiederholungsintervall ohne Begrenzung angegeben!')
                );
            }
            if ((!$this->repeat_quantity) && ($real_begin > $this->repeat_end)) {
                throw new InvalidArgumentException(
                    _('Der Startzeitpunkt darf nicht hinter dem Ende der Wiederholungen liegen!')
                );
            }
        }

        //Check if the user has booking rights on the resource.
        //The user must have either permanent permissions or they have to
        //have booking rights by a temporary permission in this moment
        //(the moment this booking is saved).
        $derived_resource = $this->resource->getDerivedClassInstance();
        $user_has_booking_rights = $derived_resource->userHasBookingRights(
            $this->booking_user
        );
        if (!$user_has_booking_rights) {
            throw new ResourcePermissionException(
                sprintf(
                    _('Unzureichende Berechtigungen zum Buchen der Ressource %s!'),
                    $this->resource->name
                )
            );
        }

        $time_intervals = $this->calculateTimeIntervals(true);
        $time_interval_overlaps = [];
        foreach ($time_intervals as $time_interval) {
            $is_locked = $derived_resource->isLocked(
                $time_interval['begin'],
                $time_interval['end'],
                ($this->isNew() ? [] : [$this->id])
            );
            if ($is_locked) {
                if ($time_interval['begin']->format('Ymd') == $time_interval['end']->format('Ymd')) {
                    $time_interval_overlaps[] = sprintf(
                        _('Gesperrt im Bereich vom %1$s bis %2$s'),
                        $time_interval['begin']->format('d.m.Y H:i'),
                        $time_interval['end']->format('H:i')
                    );
                } else {
                    $time_interval_overlaps[] = sprintf(
                        _('Gesperrt im Bereich vom %1$s bis zum %2$s'),
                        $time_interval['begin']->format('d.m.Y H:i'),
                        $time_interval['end']->format('d.m.Y H:i')
                    );
                }
            } else {
                $is_assigned = $derived_resource->isAssigned(
                    $time_interval['begin'],
                    $time_interval['end'],
                    ($this->isNew() ? [] : [$this->id])
                );
                if ($is_assigned) {
                    if ($time_interval['begin']->format('Ymd') == $time_interval['end']->format('Ymd')) {
                        $time_interval_overlaps[] = sprintf(
                            _('Gebucht im Bereich vom %1$s bis %2$s'),
                            $time_interval['begin']->format('d.m.Y H:i'),
                            $time_interval['end']->format('H:i')
                        );
                    } else {
                        $time_interval_overlaps[] = sprintf(
                            _('Gebucht im Bereich vom %1$s bis zum %2$s'),
                            $time_interval['begin']->format('d.m.Y H:i'),
                            $time_interval['end']->format('d.m.Y H:i')
                        );
                    }
                }
            }
        }
        if ($time_interval_overlaps) {
            throw new ResourceBookingOverlapException(
                implode(', ', $time_interval_overlaps)
            );
        }

        NotificationCenter::postNotification('ResourceBookingWillValidate', $this);
    }


    /**
     * This method updates the intervals of this resource booking
     * which are stored in the resource_booking_intervals table.
     */
    public function updateIntervals($keep_exceptions = true)
    {
        if ($keep_exceptions) {
            //Delete all intervals with takes_place > 0
            //and update the time intervals of the exceptions:
            ResourceBookingInterval::deleteBySql(
                "booking_id = :booking_id
                AND
                takes_place > '0'",
                [
                    'booking_id' => $this->id
                ]
            );

            //Get all interval exceptions:
            $exceptions = ResourceBookingInterval::findBySql(
                "booking_id = :booking_id
                AND
                takes_place < '1'",
                [
                    'booking_id' => $this->id
                ]
            );

            if ($exceptions) {
                //Now we must compare the time intervals of the booking
                //with the time intervals of the exceptions.
                //If there is only a difference in hours we update
                //the exceptions. Otherwise we delete the exceptions.

                $repetition_interval = $this->getRepetitionInterval();
                if (!$repetition_interval) {
                    //No repetition interval also means nothing left to do.
                    return;
                }

                $repetition_begin = new DateTime();
                $repetition_begin->setTimestamp($this->begin - $this->preparation_time);
                $date_end = new DateTime();
                $date_end->setTimestamp($this->end);
                $repetition_end = new DateTime();
                $repetition_end->setTimestamp($this->repeat_end);
                $repetition_interval = $this->getRepetitionInterval();

                $duration = $repetition_begin->diff($date_end);

                //Loop over all exceptions and check if they belong to
                //one of the repetions:

                $obsolete_exception_ids = [];
                foreach ($exceptions as $exception) {
                    $exception_begin = new DateTime();
                    $exception_begin->setTimestamp($exception->begin);
                    $exception_end = new DateTime();
                    $exception_end->setTimestamp($exception->end);
                    $exc_begin_str = $exception_begin->format('Y-m-d');
                    $exc_end_str = $exception_end->format('Y-m-d');

                    $exception_obsolete = true;

                    $current_repetition = clone $repetition_begin;
                    while ($current_repetition < $repetition_end) {
                        $current_end = clone $current_repetition;
                        $current_end->add($duration);

                        $current_begin_str = $current_repetition->format('Y-m-d');
                        $current_end_str = $current_end->format('Y-m-d');

                        if ($current_begin_str == $exc_begin_str && $current_end_str == $exc_end_str) {
                            //We found one exception which needs to be updated.
                            $exception_obsolete = false;
                            $exception->begin = $current_repetition->getTimestamp();
                            $exception->end = $current_end->getTimestamp();
                            if ($exception->isDirty()) {
                                $exception->store();
                            }
                            //No need to loop the rest of the repetitions:
                            break;
                        }

                        $current_repetition->add($repetition_interval);
                    }

                    if ($exception_obsolete) {
                        $obsolete_exception_ids[] = $exception->id;
                    }
                }

                if ($obsolete_exception_ids) {
                    ResourceBookingInterval::deleteBySql(
                        'interval_id IN ( :ids )',
                        [
                            'ids' => $obsolete_exception_ids
                        ]
                    );
                }
            }
        } else {
            //We delete all existing intervals for this booking
            //and re-create them.
            ResourceBookingInterval::deleteBySql(
                'booking_id = :booking_id',
                [
                    'booking_id' => $this->id
                ]
            );
        }

        ResourceBookingInterval::createFromBooking($this);
    }


    /**
     * Deletes the ResourceBooking object if there are no
     * ResourceBookingInterval objects attachted to it.
     *
     * @return null|bool If the ResourceBooking object still has
     *     ResourceBookingInterval objects attachted to it,
     *     null is returned. Otherwise the return value of the
     *     SimpleORMap::delete method for the ResourceBooking object
     *     is returned.
     */
    public function deleteIfNoInterval()
    {
        $intervals = ResourceBookingInterval::countBySql(
            'booking_id = :id',
            [
                'id' => $this->id
            ]
        );
        if ($intervals == 0) {
            return $this->delete();
        }

        return null;
    }


    /**
     * Deletes all bookings in the time ranges of this resource booking.
     * Such bookings would prevent saving this booking.
     *
     * @return int The amount of deleted bookings.
     */
    public function deleteOverlappingBookings()
    {
        $real_begin = new DateTime();
        $real_begin->setTimestamp($this->begin - $this->preparation_time);
        $end = new DateTime();
        $end->setTimestamp($this->end);
        $repetition_end = new DateTime();
        $repetition_end->setTimestamp($this->repeat_end);

        $deleted_c = 0;
        if ($this->repetition_interval) {
            $repetition_interval = $this->getRepetitionInterval();

            if (!($this->repeat_quantity || $this->repeat_end)) {
                throw new InvalidArgumentException(
                    _('Es wurde ein Wiederholungsintervall ohne Begrenzung angegeben!')
                );
            }
            if ((!$this->repeat_quantity) && ($real_begin > $this->repeat_end)) {
                throw new InvalidArgumentException(
                    _('Der Startzeitpunkt darf nicht hinter dem Ende der Wiederholungen liegen!')
                );
            }

            //Look in each repetition for overlapping bookings and delete them.
            $current_date = clone $real_begin;
            while ($current_date <= $repetition_end) {
                $current_begin = clone $current_date;
                $current_end = clone $current_date;
                $current_end->setTime(
                    intval($end->format('H')),
                    intval($end->format('i')),
                    intval($end->format('s'))
                );
                $derived_resource = $this->resource->getDerivedClassInstance();
                if ($derived_resource->userHasPermission($this->booking_user, 'tutor', [$current_begin, $current_end])) {
                    //Sufficient permissions to delete bookings
                    //in the time frame.
                    $delete_sql = '(begin BETWEEN :begin AND :end
                        OR
                        end BETWEEN :begin AND :end)
                        AND
                        resource_id = :resource_id ';
                    $sql_params = [
                        'begin' => $current_begin->getTimestamp(),
                        'end' => $current_end->getTimestamp(),
                        'resource_id' => $this->resource->id
                    ];
                    if (!$this->isNew()) {
                        $delete_sql .= 'booking_id <> :booking_id';
                        $sql_params['booking_id'] = $this->id;
                    }
                    $intervals = ResourceBookingInterval::findBySQL(
                        $delete_sql,
                        $sql_params
                    );

                    $affected_bookings = [];
                    foreach ($intervals as $interval) {
                        if ($interval->booking instanceof ResourceBooking) {
                            $affected_bookings[$interval->booking_id] = $interval->booking;
                        }
                        $deleted_c += $interval->delete();
                    }

                    foreach ($affected_bookings as $booking) {
                        $booking->deleteIfNoInterval();
                    }
                }
                $current_date = $current_date->add($repetition_interval);
            }
        } else {
            $derived_resource = $this->resource->getDerivedClassInstance();
            if ($derived_resource->userHasPermission($this->booking_user, 'autor', [$real_begin, $end])) {
                $delete_sql = '(begin BETWEEN :begin AND :end
                    OR
                    end BETWEEN :begin AND :end)
                    AND
                    resource_id = :resource_id ';
                $sql_params = [
                    'begin' => $real_begin->getTimestamp(),
                    'end' => $end->getTimestamp(),
                    'resource_id' => $this->resource->id
                ];
                if (!$this->isNew()) {
                    $delete_sql .= 'booking_id <> :booking_id';
                    $sql_params['booking_id'] = $this->id;
                }
                $intervals = ResourceBookingInterval::findBySQL(
                    $delete_sql,
                    $sql_params
                );
                $affected_bookings = [];
                foreach ($intervals as $interval) {
                    if ($interval->booking instanceof ResourceBooking) {
                        $affected_bookings[$interval->booking_id] = $interval->booking;
                    }
                    $deleted_c += $interval->delete();
                }

                foreach ($affected_bookings as $booking) {
                    $booking->deleteIfNoInterval();
                }
            }
        }

        return $deleted_c;
    }


    /**
     * Deletes all reservations in the time ranges of this resource booking.
     *
     * @return int The amount of deleted reservations.
     */
    public function deleteOverlappingReservations()
    {
        //Notify all persons who made reservations or who are assigned
        //to the reservation about the new booking which overwrites
        //their reservation:
        $booking_resource = Resource::find($this->resource_id);
        $booking_user = User::find($this->booking_user_id);

        $real_begin = new DateTime();
        $real_begin->setTimestamp($this->begin - $this->preparation_time);
        $end = new DateTime();
        $end->setTimestamp($this->end);

        $deleted_c = 0;

        $template_factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
        );

        $affected_reservations =  ResourceBooking::findByResourceAndTimeRanges(
            $booking_resource,
            [
                [
                    'begin' => $real_begin->getTimestamp(),
                    'end' => $end->getTimestamp(),
                ]
            ],
            [1, 3],
            [$this->id]
        );
        foreach ($affected_reservations as $reservation) {
            if ($reservation->id == $this->id) {
                continue;
            }
            if ($reservation->assigned_user && (
                $reservation->range_id != $reservation->booking_user_id
            )) {
                //Inform the person who is assigned to the reservation:
                setTempLanguage($reservation->assigned_user->id);
                $lang_path = getUserLanguagePath($reservation->assigned_user->id);

                $template = $template_factory->open(
                    $lang_path . '/LC_MAILS/overbooked_reservation.php'
                );
                $template->set_attribute('resource', $booking_resource);
                $template->set_attribute('reservation', $reservation);
                $template->set_attribute('booking_user', $booking_user);
                $mail_text = $template->render();

                Message::send(
                    '____%system%____',
                    [$reservation->assigned_user->username],
                    _('Reservierung überbucht'),
                    $mail_text
                );

                restoreLanguage();
            }
            if ($reservation->booking_user) {
                //Inform the person who made the reservation:
                setTempLanguage($reservation->booking_user->id);
                $lang_path = getUserLanguagePath($reservation->booking_user->id);

                $template = $template_factory->open(
                    $lang_path . '/LC_MAILS/overbooked_reservation.php'
                );
                $template->set_attribute('resource', $booking_resource);
                $template->set_attribute('reservation', $reservation);
                $template->set_attribute('booking_user', $booking_user);
                $mail_text = $template->render();

                Message::send(
                    '____%system%____',
                    [$reservation->booking_user->username],
                    _('Reservierung überbucht'),
                    $mail_text
                );

                restoreLanguage();
            }

            //Delete the reservation:
            $deleted_c += $reservation->delete();
        }
        return $deleted_c;
    }


    /**
     * Determines whether the resource booking ends on the same timestamp
     * like the lecture time of one of the defined semesters.
     *
     * @return True, if the resource booking ends with a semester,
     *     false otherwise.
     */
    public function endsWithSemester()
    {
        return Semester::countBySql(
            '(beginn <= :begin) AND (ende >= :begin)
             AND vorles_ende = :repeat_end',
            [
                'begin' => $this->begin,
                'repeat_end' => $this->repeat_end
            ]
        ) > 0;
    }


    /**
     * Check if the specified user is the owner of the booking.
     *
     * @param User $user The user whose ownership shall be tested.
     *
     * @return bool True, if the specified user is the owner of the booking,
     *     false otherwise.
     */
    public function userIsOwner(User $user)
    {
        return $this->booking_user_id == $user->id;
    }


    /**
     * Determines wheter the booking is read only for a specified user.
     *
     * @param User $user The user whose permissions shall be checked.
     *
     * @return bool True, if the specified user may only perform reading
     *     actions on the booking, false otherwise.
     */
    public function isReadOnlyForUser(User $user)
    {
        if ($this->isSimpleBooking()) {
            //In case it is a simple booking, one has to be
            //either resource tutor or the owner of the request.
            if ($this->userIsOwner($user)) {
                return false;
            }
            //Still no answer? Check, if the user is resource tutor.
            $derived_resource = $this->resource->getDerivedClassInstance();
            return !$derived_resource->userHasPermission($user, 'tutor');
        }
        //Non-simple bookings (course bookings etc.) are always read-only.
        return true;
    }


    /**
     * Determines whether this resource booking has a repetition in the
     * specified time range.
     * @param DateTime $begin
     * @param DateTime $end
     * @return bool True, if the booking has repetitions in the timeframe
     * specified by $begin and $end, false otherwise.
     */
    public function isRepetitionInTimeframe(DateTime $begin, DateTime $end)
    {
        return ResourceBookingInterval::countBySql(
            "booking_id = :booking_id
            AND (
                (begin BETWEEN :begin AND :end)
                OR
                (end BETWEEN :begin AND :end)
                OR
                (begin < :begin AND end > :end)
            )
            AND NOT (
                (begin BETWEEN :booking_begin AND :booking_end)
                OR
                (end BETWEEN :booking_begin AND :booking_end)
                OR
                (begin < :booking_begin AND end > :booking_end)
            )",
            [
                'booking_id' => $this->id,
                'begin' => $begin->getTimestamp(),
                'end' => $end->getTimestamp(),
                'booking_begin' => $this->begin,
                'booking_end' => $this->end
            ]
        ) > 0;
    }


    /**
     * Returns the DateInterval object according to the set repetition
     * interval of this resource booking object.
     *
     * @return DateInterval|null A DateInterval object or null,
     *     if this booking has no repetition interval.
     */
    public function getRepetitionInterval()
    {
        $repetition_interval = null;
        if ($this->repetition_interval) {
            try {
                if ($this->repetition_interval instanceof DateInterval) {
                    $repetition_interval = $this->repetition_interval;
                } else {
                    $repetition_interval = new DateInterval($this->repetition_interval);
                }
            } catch (Exception $e) {
                //Invalid repetition interval string.
                throw new InvalidArgumentException(
                    sprintf(
                        _('Das Wiederholungsintervall ist in einem ungültigen Format (%s)!'),
                        $this->repetition_interval
                    )
                );
            }
        }
        return $repetition_interval;
    }


    public function getRepeatModeString()
    {
        $interval = $this->getRepetitionInterval();

        if ($interval->m) {
            switch ($interval->m) {
                case 1:  return _('jeden Monat');
                case 2:  return _('jeden zweiten Monat');
                case 3:  return _('jeden dritten Monat');
                case 4:  return _('jeden vierten Monat');
                case 5:  return _('jeden fünften Monat');
                case 6:  return _('jeden sechsten Monat');
                case 7:  return _('jeden siebten Monat');
                case 8:  return _('jeden achten Monat');
                case 9:  return _('jeden neunten Monat');
                case 10: return _('jeden zehnten Monat');
                case 11: return _('jeden elften Monat');
            }
        } elseif (($interval->d % 7) == 0) {
            $week = round($interval->d / 7);
            switch ($week) {
                case 1: return _('jede Woche');
                case 2: return _('jede zweite Woche');
                case 3: return _('jede dritte Woche');
            }
        } elseif ($interval->d) {
            if ($interval->d > 1) {
                return sprintf(
                    _('jeden %d. Tag'),
                    $interval->d
                );
            } elseif ($interval->d == 1) {
                return _('jeden Tag');
            } else {
                return _('ungültiges Zeitintervall');
            }
        }
    }

    /**
     * Determines if the resource booking overlaps with another
     * resource booking.
     *
     * @return bool True, if there are other bookings which overlap
     *     with this one, false otherwise.
     */
    public function hasOverlappingBookings()
    {
        //Get all intervals of this booking, loop over them
        //and check if there are other intervals in the same
        //timeframe for the same resource.

        $intervals = ResourceBookingInterval::findBySql(
            'booking_id = :booking_id',
            [
                'booking_id' => $this->id
            ]
        );

        if ($intervals) {
            foreach ($intervals as $interval) {
                $count = ResourceBookingInterval::countBySql(
                    'booking_id <> :booking_id
                    AND
                    resource_id = :resource_id
                    AND takes_place = 1
                    AND
                    (
                        begin < :end AND :begin < end
                    )',
                    [
                        'booking_id' => $this->id,
                        'resource_id' => $this->resource_id,
                        'begin' => $interval->begin,
                        'end' => $interval->end
                    ]
                );

                if ($count) {
                    //We have found an interval which overlaps
                    //with an interval of this booking.
                    return true;
                }
            }
        }

        //If this booking has no intervals it means it doesn't have
        //any time intervals at all. Therefore there can't be any
        //overlapping bookings.
        return false;
    }

    /**
     * Gets the bookings that overlap with this booking.
     *
     * @return array Array of ResourceBooking objects
     */
    public function getOverlappingBookings()
    {
        //Get all intervals of this booking, loop over them
        //and check if there are other intervals in the same
        //timeframe for the same resource. If so, collect the
        //booking-IDs and get all the bookings.

        $intervals = ResourceBookingInterval::findBybooking_id($this->id);

        if ($intervals) {
            $db = DBManager::get();

            $get_booking_id_stmt = $db->prepare(
                'SELECT DISTINCT booking_id
                FROM resource_booking_intervals
                WHERE
                booking_id <> :booking_id
                AND
                resource_id = :resource_id
                AND takes_place = 1
                AND
                (
                    begin < :end AND :begin < end
                )'
            );
            $overlapping_booking_ids = [];
            foreach ($intervals as $interval) {
                $get_booking_id_stmt->execute(
                    [
                        'booking_id' => $this->id,
                        'resource_id' => $this->resource_id,
                        'begin' => $interval->begin,
                        'end' => $interval->end
                    ]
                );

                $overlapping_booking_ids = array_merge(
                    $overlapping_booking_ids,
                    $get_booking_id_stmt->fetchAll(
                        PDO::FETCH_COLUMN,
                        0
                    )
                );
            }

            if ($overlapping_booking_ids) {
                $overlapping_booking_ids = array_unique(
                    $overlapping_booking_ids
                );

                return ResourceBooking::findMany(
                    $overlapping_booking_ids
                );
            }
        }

        //If this booking has no intervals it means it doesn't have
        //any time intervals at all. Therefore there can't be any
        //overlapping bookings.
        return false;
    }


    /**
     * Calculates all time intervals as begin and end timestamps
     * by looking at the repetition settings, if any.
     *
     * @param bool $as_datetime Whether to return the timestamps
     *     as DateTime objects (true) or not (false). Defaults to false.
     *
     * @return array A two-dimensional array with each time interval
     *     for this booking. The array has the following structure:
     *     [
     *         [
     *             'begin' => Begin timestamp.
     *             'end' => End timestamp.
     *         ]
     *     ]
     */
    public function calculateTimeIntervals($as_datetime = false)
    {
        $interval_data = [];
        $booking_begin = new DateTime();
        $booking_begin->setTimestamp($this->begin);
        if ($this->preparation_time) {
            $booking_begin->setTimestamp(
                $this->begin - $this->preparation_time
            );
        }
        $booking_end = new DateTime();
        $booking_end->setTimestamp($this->end);

        //use begin and end to create the first interval:
        $interval_data[] = [
            'begin' => (
                $as_datetime
                ? clone $booking_begin
                : $booking_begin->getTimestamp()
            ),
            'end' => (
                $as_datetime
                ? clone $booking_end
                : $booking_end->getTimestamp()
            )
        ];

        if (($this->repeat_quantity > 0) || $this->repeat_end) {
            //Repetition: we must check which repetition interval has been
            //selected and then create entries for each repetition.
            //Repetition starts with the begin date and ends with the
            //"repeat_end" date.
            $repetition_end = new DateTime();
            $repetition_end->setTimestamp($this->repeat_end);
            //The DateInterval constructor will throw an exception,
            //if it cannot parse the string stored in $this->repetition_interval.
            $repetition_interval = $this->getRepetitionInterval();

            if ($repetition_interval instanceof DateInterval) {
                $duration = $booking_begin->diff($booking_end);

                //Check if end is later than begin to avoid
                //infinite loops.
                if ($repetition_end > $booking_begin) {
                    $current_begin = clone $booking_begin;
                    $current_begin->add($repetition_interval);
                    while ($current_begin < $repetition_end) {
                        $current_end = clone $current_begin;
                        $current_end->add($duration);
                        $interval_data[] = [
                            'begin' => (
                                $as_datetime
                                ? clone $current_begin
                                : $current_begin->getTimestamp()
                            ),
                            'end' => (
                                $as_datetime
                                ? clone $current_end
                                : $current_end->getTimestamp()
                            )
                        ];
                        $current_begin->add($repetition_interval);
                    }
                } else {
                    //end timestamp is before begin timestamp:
                    //return an empty array
                    return [];
                }
            }
            //Everything went fine.
        }
        return $interval_data;
    }


    /**
     * Retrieves all time intervals for this resource booking.
     *
     * @return ResourceBookingInterval[] An array of
     *     ResourceBookingInterval objects.
     */
    public function getTimeIntervals($with_exceptions = true)
    {
        if ($with_exceptions) {
            return ResourceBookingInterval::findBySQL(
                "booking_id = :booking_id
                ORDER BY begin ASC, end ASC",
                [
                    'booking_id' => $this->id
                ]
            );
        } else {
            return ResourceBookingInterval::findBySQL(
                "booking_id = :booking_id AND takes_place = '1'
                ORDER BY begin ASC, end ASC",
                [
                    'booking_id' => $this->id
                ]
            );
        }
    }


    /**
     * Retrieves all time intervals for this resource booking
     * in a specified time range.
     *
     * @param DateTime $begin The begin of the time range.
     * @param DateTime $end The end of the time range.
     *
     * @return ResourceBookingInterval[] An array of
     *     ResourceBookingInterval objects.
     */
    public function getTimeIntervalsInTimeRange(DateTime $begin, DateTime $end)
    {
        if ($begin > $end) {
            //We don't serve querys with invalid time ranges here.
            return [];
        }

        return ResourceBookingInterval::findBySql(
            'booking_id = :booking_id
            AND
            (
                (begin >= :begin AND begin <= :end)
                OR
                (end >= :begin AND end <= :end)
            )
            ORDER BY begin ASC, end ASC',
            [
                'booking_id' => $this->id,
                'begin' => $begin->getTimestamp(),
                'end' => $end->getTimestamp()
            ]
        );
    }


    /**
     * Returns all allocating users: Users who are associated with this booking
     * through a request or a course date for which this booking has been made
     * (all allocating users). If no user could be determined but the booking
     * is bound to a course, the course name is returned instead.
     *
     * @deprecated
     * @param bool $only_names Whether only the names of these users shall be
     *     returned (true) or user objects shall be returned (false).
     *
     * @return string[]|User[] Depending on the value of the $only_names
     *     parameter a string array or an user object array is returned.
     *     In case no user can be found, the array is empty.
     *     In case the parameter $only_names is set to true, the result
     *     may also contain a course name, if no users can be found but the
     *     booking is bound to a course.
     */
    public function getAssignedUsers($only_names = true)
    {
        //The only two cases which shall be handled are that
        //a booking is assigned to a course or an user.
        if ($this->getAssignedUserType() === 'course') {
            //Return all persons associated with that course date.
            if (count($this->assigned_course_date->dozenten)) {
                if ($only_names) {
                    $lecturers = [];
                    foreach ($this->assigned_course_date->dozenten as $lecturer) {
                        $lecturers[] = $lecturer->getFullName();
                    }
                    return $lecturers;
                } else {
                    return $this->assigned_course_date->dozenten;
                }
            } else {
                $lecturers = User::findBySql(
                    "INNER JOIN seminar_user
                    USING (user_id)
                    WHERE
                    seminar_user.seminar_id = :course_id
                    AND
                    seminar_user.status = 'dozent'
                    ORDER BY nachname ASC, vorname ASC",
                    [
                        'course_id' => $this->assigned_course_date->range_id
                    ]
                );
                if (!$lecturers) {
                    return [];
                }
                if ($only_names) {
                    $names = [];
                    foreach ($lecturers as $lecturer) {
                        $names[] = $lecturer->getFullName();
                    }
                    return $names;
                } else {
                    return $lecturers;
                }
            }
        } elseif ($this->getAssignedUserType() === 'user') {
            if ($only_names) {
                return [$this->assigned_user->getFullName()];
            }
            return [$this->assigned_user];
        }

        return [];
    }

    public function getAssignedUser()
    {
        if ($this->getAssignedUserType() === 'course') {
            return $this->assigned_course_date->course;
        }
        if ($this->getAssignedUserType() === 'user') {
            return $this->assigned_user;
        }
    }

    public function getAssignedUserType()
    {
        if (isset($this->assigned_user_type)) {
            return $this->assigned_user_type;
        }
        if ($this->assigned_course_date instanceof CourseDate) {
            return $this->assigned_user_type = 'course';
        }
        if ($this->assigned_user instanceof User) {
            return $this->assigned_user_type = 'user';
        }
        return $this->assigned_user_type = 'none';
    }

    public function getAssignedUserName()
    {
        if ($this->getAssignedUserType() === 'course') {
            $name = $this->assigned_course_date->course->getFullname();
            $name .= ' (' . implode(',', $this->assigned_course_date->course->getMembersWithStatus('dozent', true)->limit(3)->getValue('nachname')) . ')';
        } elseif ($this->getAssignedUserType() === 'user') {
            if (get_visibility_by_id($this->assigned_user->id) ||
                ($this->assigned_user->id == $GLOBALS['user']->id)
            ) {
                $name = $this->assigned_user->getFullname();
                if ($this->description) {
                    $name .= " \n" . $this->description;
                }
            } else {
                //Check if the current user has at least user permissions
                //on the resource. In that case, even invisible assigned
                //users become visible.
                $resource = $this->resource->getDerivedClassInstance();
                $current_user = User::findCurrent();
                if (($resource instanceof Resource) && ($current_user instanceof User)) {
                    if ($resource->userHasPermission($current_user, 'user')) {
                        $name = $this->assigned_user->getFullname();
                        if ($this->description) {
                            $name .= " \n" . $this->description;
                        }
                    }
                }
            }
        } else {
            $name = $this->description;
        }
        return $name;
    }


    /**
     * Determines whether the booking is a simple booking
     * that is not bound to course dates or similar Stud.IP objects.
     */
    public function isSimpleBooking()
    {
        return !($this->getAssignedUserType() === 'course');
    }


    /**
     * Determins whether the booking has exceptions in repetitions or not.
     * When the booking is bound to a course via a CourseDate instance,
     * the exceptions are looked up using the corresponding metadate (if any).
     * If a metadate exists to the course date and it has cancelled dates
     * (metadate has CourseExDate instances assigned) then the booking has
     * exceptions.
     * If the booking is a simple booking, the exception status is determined
     * by checking if the booking has ResourceBookingInterval instances
     * that don't take place assigned to it. This is only checked,
     * if a repetition interval is set for the booking so that such instances
     * can exist.
     *
     * @return bool True, if the booking has exceptions in the repetition,
     *     false otherwise.
     */
    public function hasExceptions()
    {
        if ($this->isSimpleBooking()) {
            //This is a simple booking: Check the repetition interval:
            if ($this->repetition_interval) {
                //A repetition interval exists: Check if booking intervals
                //exist that don't take place:
                return ResourceBookingInterval::countBySql(
                    "booking_id = :booking_id
                    AND takes_place = '0'",
                    [
                        'booking_id' => $this->id
                    ]
                ) > 0;
            } else {
                return false;
            }
        } else {
            //Check if the course booking has exceptions (via metadate):
            $metadate = $this->assigned_course_date->cycle;
            if ($metadate instanceof SeminarCycleDate) {
                //Check if the metadate has exceptions:
                return CourseExDate::countBySql(
                    'metadate_id = :metadate_id',
                    [
                        'metadate_id' => $metadate->id
                    ]
                ) > 0;
            } else {
                //No metadate is assigned to the course booking.
                return false;
            }
        }
    }


    public function __toString()
    {
        return date('d.m.Y H:i', $this->begin)
             . ' - '
             . date('d.m.Y H:i', $this->end);
    }


    public function getTimeIntervalStrings()
    {
        $time_intervals = ResourceBookingInterval::findBySQL(
            'booking_id = :booking_id ORDER BY begin ASC, end ASC',
            [
                'booking_id' => $this->id
            ]
        );

        $strings = [];
        foreach ($time_intervals as $interval) {
            if (date('Ymd', $interval->begin) == date('Ymd', $interval->end)) {
                $strings[] = sprintf(
                    '%1$s %2$s - %3$s',
                    date('d.m.Y', $interval->begin),
                    date('H:i', $interval->begin),
                    date('H:i', $interval->end)
                );
            } else {
                $strings[] = sprintf(
                    '%1$s - %2$s',
                    date('d.m.Y H:i', $interval->begin),
                    date('d.m.Y H:i', $interval->end)
                );
            }
        }
        return $strings;
    }


    /**
     * @inheritDoc
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $user = User::find($storage->user_id);
        $bookings = self::findBySql(
            'user_id = :user_id ORDER BY mkdate',
            [
                'user_id' => $storage->user_id
            ]
        );

        $rows = [];
        foreach ($bookings as $booking) {
            $rows[] = $booking->toRawArray();
        }
        $storage->addTabularData(
            _('Buchungen von Ressourcen'),
            'resource_bookings',
            $rows,
            $user
        );
    }


    public function convertToEventData(array $time_intervals, $user)
    {
        $booking_plan_booking_bg =
            \ColourValue::find('Resources.BookingPlan.Booking.Bg');
        $booking_plan_booking_fg =
            \ColourValue::find('Resources.BookingPlan.Booking.Fg');
        $booking_plan_simple_booking_with_exceptions_bg =
            ColourValue::find('Resources.BookingPlan.SimpleBookingWithExceptions.Bg');
        $booking_plan_simple_booking_with_exceptions_fg =
            ColourValue::find('Resources.BookingPlan.SimpleBookingWithExceptions.Fg');
        $booking_plan_reservation_bg = \ColourValue::find('Resources.BookingPlan.Reservation.Bg');
        $booking_plan_reservation_fg = \ColourValue::find('Resources.BookingPlan.Reservation.Fg');
        $booking_plan_lock_bg = \ColourValue::find('Resources.BookingPlan.Lock.Bg');
        $booking_plan_lock_fg = \ColourValue::find('Resources.BookingPlan.Lock.Fg');
        $booking_plan_planned_booking_bg = \ColourValue::find('Resources.BookingPlan.PlannedBooking.Bg');
        $booking_plan_planned_booking_fg = \ColourValue::find('Resources.BookingPlan.PlannedBooking.Fg');
        $booking_plan_preparation_bg = \ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $booking_plan_preparation_fg = \ColourValue::find('Resources.BookingPlan.PreparationTime.Fg');
        $booking_plan_course_booking_bg = \ColourValue::find('Resources.BookingPlan.CourseBooking.Bg');
        $booking_plan_course_booking_fg = \ColourValue::find('Resources.BookingPlan.CourseBooking.Fg');

        $colour = $booking_plan_booking_bg->__toString();
        $text_colour = $booking_plan_booking_fg->__toString();
        $event_classes = [];

        if ($this->booking_type == '0') {
            $event_classes[] = 'resource-booking';
            //Check if the booking is a course booking:
            if ($this->getAssignedUserType() === 'course') {
                //It is a course date.
                $event_classes[] = 'for-course';
                $colour = $booking_plan_course_booking_bg->__toString();
                $text_colour = $booking_plan_course_booking_fg->__toString();
            }
        } elseif ($this->booking_type == '1') {
            $event_classes[] = 'resource-reservation';
            $colour = $booking_plan_reservation_bg->__toString();
            $text_colour = $booking_plan_reservation_fg->__toString();
        } elseif ($this->booking_type == '2') {
            $event_classes[] = 'resource-lock';
            $colour = $booking_plan_lock_bg->__toString();
            $text_colour = $booking_plan_lock_fg->__toString();
        } elseif ($this->booking_type == '3') {
            $event_classes[] = 'resource-planned-booking';
            $colour = $booking_plan_planned_booking_bg->__toString();
            $text_colour = $booking_plan_planned_booking_fg->__toString();
        }

        $booking_is_editable = false;
        if ($user instanceof User) {
            $booking_is_editable = !$this->isReadOnlyForUser($user);
        }

        $booking_api_urls = [];
        $booking_view_urls = [
            'show' => \URLHelper::getURL(
                'dispatch.php/resources/booking/index/'
                . $this->id
            ),
        ];
        if ($booking_is_editable) {
            $booking_view_urls['edit'] = \URLHelper::getURL(
                'dispatch.php/resources/booking/edit/'
                . $this->id
            );
        }
        $events = [];

        foreach ($time_intervals as $interval) {
            $real_begin = $interval['begin'];
            if ($this->preparation_time) {
                $real_begin += $this->preparation_time;
                $begin = new DateTime();
                $begin->setTimestamp($interval['begin']);
                $end = new DateTime();
                $end->setTimestamp($real_begin);
                $events[] = new Studip\Calendar\EventData(
                    $begin,
                    $end,
                    _('Rüstzeit'),
                    ['preparation-time'],
                    $booking_plan_preparation_fg->__toString(),
                    $booking_plan_preparation_bg->__toString(),
                    $booking_is_editable,
                    'ResourceBookingInterval',
                    $interval->id,
                    'ResourceBooking',
                    $this->id,
                    'Resource',
                    $this->resource_id,
                    $booking_view_urls
                );
            }
            $prefix = '';
            $icon = '';

            if ($user instanceof User) {
                $derived_resource = $this->resource->getDerivedClassInstance();
                if ($derived_resource->userHasPermission($user, 'user') && $this->internal_comment) {
                    $icon = 'chat2';
                }
            }

            if (!$this->isSimpleBooking()) {
                if ($this->assigned_course_date->metadate_id) {
                    $icon = 'refresh';
                }
            } elseif ($this->repeat_end > $this->end) {
                $icon = 'refresh';
            }

            $event_title = $prefix . $this->getAssignedUserName();

            $interval_api_urls = $booking_api_urls;
            if ($booking_is_editable) {
                //A note on the move URL:
                //When used in a room group booking plan, the interval_id
                //URL parameter is subject to modification in JavaScript.
                //(lib/resources.js, method dropEventInRoomGroupBookingPlan)
                $interval_api_urls = [
                    'resize' => \URLHelper::getURL(
                        'api.php/resources/booking/'
                      . $this->id . '/move',
                        [
                            'quiet' => '1',
                            'interval_id' => $interval->id
                        ]
                    ),
                    'move' => \URLHelper::getURL(
                        'api.php/resources/booking/'
                      . $this->id . '/move',
                        [
                            'quiet' => '1',
                            'interval_id' => $interval->id
                        ]
                    )
                ];
            }
            $begin = new DateTime();
            $begin->setTimestamp($real_begin);
            $end = new DateTime();
            $end->setTimestamp($interval['end']);
            $events[] = new Studip\Calendar\EventData(
                $begin,
                $end,
                $event_title,
                $event_classes,
                $text_colour,
                $colour,
                $booking_is_editable,
                'ResourceBookingInterval',
                $interval->id,
                'ResourceBooking',
                $this->id,
                'Resource',
                $this->resource_id,
                $booking_view_urls,
                $interval_api_urls,
                $icon
            );
        }

        return $events;
    }


    public function getAllEventData()
    {
        return $this->convertToEventData(
            $this->getTimeIntervals(false),
            User::findCurrent()
        );
    }


    public function getEventDataForTimeRange(DateTime $begin, DateTime $end)
    {

        return $this->getFilteredEventData(null, null, null, $begin, $end);
    }


    public function getFilteredEventData(
        $user_id = null,
        $range_id = null,
        $range_type = null,
        $begin = null,
        $end = null
    )
    {
        $sql = "booking_id = :booking_id AND takes_place = 1 ";
        $sql_array = [
            'booking_id' => $this->id
        ];

        if ($begin && $end) {
            if ($begin instanceof DateTime) {
                $begin = $begin->getTimestamp();
            }
            if ($end instanceof DateTime) {
                $end = $end->getTimestamp();
            }
            $sql .= "AND begin <= :end AND :begin <= end";
            $sql_array['begin'] = $begin;
            $sql_array['end'] = $end;
        }
        $time_intervals = ResourceBookingInterval::findBySQL(
            $sql,
            $sql_array
        );

        $user = null;
        if ($user_id) {
            $user = User::find($user_id);
        } else {
            $user = User::findCurrent();
        }

        return $this->convertToEventData($time_intervals, $user);
    }

    /**
     * @return string
     */
    public function getRepetitionType()
    {
        if ($this->getAssignedUserType() === 'course') {
            if ($this->assigned_course_date->metadate_id) {
                return 'weekly';
            } else {
                return 'single';
            }
        } else {
            $intervall = $this->getRepetitionInterval();
            if (!$intervall) {
                return 'single';
            }
            if ($intervall->m) {
                return 'monthly';
            }
            if ($intervall->d % 7 === 0) {
                return 'weekly';
            }
            if ($intervall->d) {
                return 'daily';
            }
        }
    }


    /**
     * Sends a notification that the booking has been deleted
     * to the user that created the booking.
     */
    public function sendDeleteNotification()
    {
        if ($this->booking_type != '0') {
            //We only handle real bookings in this method.
            return;
        }

        if ($this->end < time()) {
            //Bookings that lie in the past can be deleted without
            //sending notifications.
            return;
        }

        $booking_resource = Resource::find($this->resource_id);
        $booking_user = User::find($this->booking_user_id);
        if (!$booking_resource || !$booking_user) {
            //Nothing we can do here.
            return;
        }

        $template_factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
        );
        setTempLanguage($booking_user->id);
        $lang_path = getUserLanguagePath($booking_user->id);

        $template = $template_factory->open(
            $lang_path . '/LC_MAILS/delete_booking_notification.php'
        );
        $template->set_attribute('resource', $booking_resource->getDerivedClassInstance());
        $template->set_attribute('begin', $this->begin);
        $template->set_attribute('end', $this->end);
        $template->set_attribute('deleting_user', User::findCurrent());

        $mail_text = $template->render();

        Message::send(
            '____%system%____',
            [$booking_user->username],
            _('Ihre Buchung wurde gelöscht'),
            $mail_text
        );

        restoreLanguage();
    }
}
