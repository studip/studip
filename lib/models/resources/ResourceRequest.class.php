<?php

/**
 * ResourceRequest.class.php - Contains a model class for resource requests.
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
 * ResourceRequest is a model class for resource requests.
 *
 * @property string id database column
 * @property string resource_id database column
 * @property string category_id database column
 * @property string course_id database column
 * @property string termin_id database column
 * @property string metadate_id database column
 * @property string begin database column
 * @property string end database column
 * @property string preparation_time databasse column
 * @property string marked database column
 *     There are four marking states:
 *     0 - not marked
 *     1 - red state
 *     2 - yellow state
 *     3 - green state
 * @property string user_id database column
 * @property string last_modified_by database column
 * @property string comment database column
 * @property string reply_comment database column
 * @property string reply_recipients database column:
 *     enum('requester', 'lecturer')
 * @property string closed database column, possible states are:
 *     0 - room-request is open
 *     1 - room-request has been processed, but no confirmation has been sent
 *     2 - room-request has been processed and a confirmation has been sent
 *     3 - room-request has been declined
 *
 * @property string mkdate database column
 * @property string chdate database column
 * @property Resource resource belongs_to Resource
 * @property User requester belongs_to User
 * @property User last_modifier belongs_to User
 *
 *
 * The attributes begin and end are only used in simple resource requests.
 * The "traditional" resource requests use either course_id, metadate_id
 * or termin_id to store the time ranges connected to the request.
 */
class ResourceRequest extends SimpleORMap implements PrivacyObject, Studip\Calendar\EventSource
{
    /**
     * The amount of defined marking states.
     */
    const MARKING_STATES = 4;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_requests';

        $config['belongs_to']['resource'] = [
            'class_name'  => 'Resource',
            'foreign_key' => 'resource_id',
            'assoc_func'  => 'find'
        ];

        $config['belongs_to']['category'] = [
            'class_name'  => 'ResourceCategory',
            'foreign_key' => 'category_id',
            'assoc_func'  => 'find'
        ];

        $config['belongs_to']['user'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
            'assoc_func'  => 'find'
        ];

        $config['belongs_to']['last_modifier'] = [
            'class_name'  => 'User',
            'foreign_key' => 'last_modified_by',
            'assoc_func'  => 'find'
        ];

        $config['belongs_to']['course'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'course_id',
            'assoc_func'  => 'find'
        ];

        $config['belongs_to']['cycle'] = [
            'class_name'  => 'SeminarCycleDate',
            'foreign_key' => 'metadate_id'
        ];

        $config['belongs_to']['date'] = [
            'class_name'  => 'CourseDate',
            'foreign_key' => 'termin_id'
        ];

        $config['has_many']['properties'] = [
            'class_name'        => 'ResourceRequestProperty',
            'foreign_key'       => 'id',
            'assoc_foreign_key' => 'request_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete'
        ];

        $config['has_many']['appointments'] = [
            'class_name'        => 'ResourceRequestAppointment',
            'foreign_key'       => 'id',
            'assoc_foreign_key' => 'request_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete'
        ];

        //In regard to TIC 6460:
        //As long as TIC 6460 is not implemented, we must add the validate
        //method as a callback before storing the object.
        if (!method_exists('SimpleORMap', 'validate')) {
            $config['registered_callbacks']['before_store'][] = 'validate';
        }
        $config['registered_callbacks']['after_create'][] = 'cbLogNewRequest';
        $config['registered_callbacks']['after_store'][] = 'cbAfterStore';


        parent::configure($config);
    }

    /**
     * @inheritDoc
     */
    public static function exportUserdata(StoredUserData $storage)
    {
        $user = User::find($storage->user_id);

        $requests = self::findBySql(
            'user_id = :user_id ORDER BY mkdate',
            [
                'user_id' => $storage->user_id
            ]
        );

        $request_rows = [];
        foreach ($requests as $request) {
            $request_rows[] = $request->toRawArray();
        }
        $storage->addTabularData(
            _('Ressourcenanfragen'),
            'resource_requests',
            $request_rows,
            $user
        );
    }

    /**
     * Retrieves all resource requests from the database.
     *
     * @return ResourceRequest[] An array of ResourceRequests objects
     *     or an empty array, if no resource requests are stored
     *     in the database.
     */
    public static function findAll()
    {
        return self::findBySql('TRUE ORDER BY mkdate ASC');
    }

    /**
     * Retrieves all open resource requests from the database.
     *
     * @return ResourceRequest[] An array of ResourceRequests objects
     *     or an empty array, if no open resource requests are stored
     *     in the database.
     */
    public static function findOpen()
    {
        return self::findBySql("closed = '0' ORDER BY mkdate ASC");
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
        $closed_status = null,
        $excluded_request_ids = [],
        $additional_conditions = '',
        $additional_parameters = []
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

        //First we build the SQL snippet for the case that the $closed_status
        //variable is set to something different than null.
        $closed_status_sql = '';
        if ($closed_status !== null) {
            $closed_status_sql    = ' AND (resource_requests.closed = :status) ';
            $sql_params['status'] = strval($closed_status);
        }

        //Then we build the snipped for excluded request IDs, if specified.
        $excluded_request_ids_sql = '';
        if (is_array($excluded_request_ids) && count($excluded_request_ids)) {
            $excluded_request_ids_sql   = ' AND resource_requests.id NOT IN ( :excluded_ids ) ';
            $sql_params['excluded_ids'] = $excluded_request_ids;
        }

        //Now we build the SQL snippet for the time intervals.
        //These are repeated four times in the query below.
        //First we use one template ($time_sql_template) and
        //replace NUMBER with our counting variable $i.
        //BEGIN and END are replaced below since the columns for
        //BEGIN and END are different in the four cases where we
        //repeat the SQL snippet for the time intervals.
        $time_sql_template = '(
            BEGIN BETWEEN :beginNUMBER AND :endNUMBER
            OR
            END BETWEEN :beginNUMBER AND :endNUMBER
            OR
            BEGIN <= :beginNUMBER AND END >= :endNUMBER
        ) ';

        $time_sql = '';
        if ($time_ranges) {
            $time_sql = 'AND (';

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

                $sql_params[('begin' . $i)] = $time_range['begin'];
                $sql_params[('end' . $i)]   = $time_range['end'];

                $i++;
            }

            $time_sql .= ') ';
        }

        //Check if the request has a start and end timestamp set or if it belongs
        //to a date, a metadate or a course.
        //This is done in the rest of the SQL query:

        $whole_sql = 'resource_requests.id IN (
                SELECT id FROM resource_requests
                WHERE
                resource_id = :resource_id
        '
            . str_replace(
                ['BEGIN', 'END'],
                ['(CAST(begin AS SIGNED) - preparation_time)', 'end'],
                $time_sql
            )
            . (
            $closed_status_sql
                ? $closed_status_sql
                : ''
            ) . '
                UNION
                SELECT id FROM resource_requests
                INNER JOIN termine USING (termin_id)
                WHERE
                resource_id = :resource_id
                   '
            . str_replace(
                ['BEGIN', 'END'],
                [
                    '(termine.date - resource_requests.preparation_time)',
                    'termine.end_time'
                ],
                $time_sql
            )
            . (
            $closed_status_sql
                ? $closed_status_sql
                : ''
            ) . '
                UNION
                SELECT id FROM resource_requests
                INNER JOIN termine USING (metadate_id)
                WHERE
                resource_id = :resource_id
                   '
            . str_replace(
                ['BEGIN', 'END'],
                [
                    '(termine.date - resource_requests.preparation_time)',
                    'termine.end_time'
                ],
                $time_sql
            )
            . (
            $closed_status_sql
                ? $closed_status_sql
                : ''
            ) . '
            UNION
            SELECT id FROM resource_requests
            INNER JOIN termine
            ON resource_requests.course_id = termine.range_id
            WHERE
            resource_id = :resource_id
                   '
            . str_replace(
                ['BEGIN', 'END'],
                [
                    '(termine.date - resource_requests.preparation_time)',
                    'termine.end_time'
                ],
                $time_sql
            )
            . (
            $closed_status_sql
                ? $closed_status_sql
                : ''
            ) . '
            GROUP BY id
        ) '
            . $excluded_request_ids_sql;

        if ($additional_conditions) {
            $whole_sql .= ' AND ' . $additional_conditions;
            if ($additional_parameters) {
                $sql_params = array_merge($sql_params, $additional_parameters);
            }
        }
        $whole_sql .= ' ORDER BY mkdate ASC';

        return [
            'sql'    => $whole_sql,
            'params' => $sql_params
        ];
    }

    /**
     * Retrieves all resource requests for the given resource and
     * time range. By default, all requests are returned.
     * To get only open or closed requests set the $closed_status parameter.
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
     * @param mixed $closed_status An optional status for the closed column in the
     *     database. By default this is set to null which means that
     *     resource requests are not filtered by the status column field.
     *     A value of 0 means only open requests are retrived.
     *     A value of 1 means only closed requests are retrieved.
     *
     * @param array $excluded_request_ids An array of strings representing
     *     resource request IDs. IDs specified in this array are excluded from
     *     the search.
     * @return ResourceRequest[] An array of ResourceRequest objects.
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
        $closed_status = null,
        $excluded_request_ids = [],
        $additional_conditions = '',
        $additional_parameters = []
    )
    {
        //Build the SQL query and the parameter array.

        $sql_data = self::buildResourceAndTimeRangesSqlQuery(
            $resource,
            $time_ranges,
            $closed_status,
            $excluded_request_ids,
            $additional_conditions,
            $additional_parameters
        );

        //Call findBySql:
        return self::findBySql($sql_data['sql'], $sql_data['params']);
    }

    public static function countByResourceAndTimeRanges(
        Resource $resource,
        $time_ranges = [],
        $closed_status = null,
        $excluded_request_ids = [],
        $additional_conditions = '',
        $additional_parameters = []
    )
    {
        $sql_data = self::buildResourceAndTimeRangesSqlQuery(
            $resource,
            $time_ranges,
            $closed_status,
            $excluded_request_ids,
            $additional_conditions,
            $additional_parameters
        );

        return self::countBySql($sql_data['sql'], $sql_data['params']);
    }

    public static function findByCourse($course_id)
    {
        return self::findOneBySql(
            "termin_id = '' AND metadate_id = '' AND course_id = :course_id",
            [
                'course_id' => $course_id
            ]
        );
    }

    public static function findByDate($date_id)
    {
        return self::findOneBySql(
            'termin_id = :date_id',
            [
                'date_id' => $date_id
            ]
        );
    }

    public static function findByMetadate($metadate_id)
    {
        return self::findOneBySql(
            'metadate_id = :metadate_id',
            [
                'metadate_id' => $metadate_id
            ]
        );
    }

    public static function existsByCourse($course_id, $request_is_open = false)
    {
        $sql = '';
        if ($request_is_open) {
            $sql .= "closed = '0' AND ";
        }

        $request = self::findOneBySql(
            $sql . "termin_id = '' AND metadate_id = '' AND course_id = :course_id",
            [
                'course_id' => $course_id
            ]
        );

        if ($request) {
            return $request->id;
        } else {
            return false;
        }
    }

    public static function existsByDate($date_id, $request_is_open = false)
    {
        $sql = '';
        if ($request_is_open) {
            $sql .= "closed = '0' AND ";
        }

        $request = self::findOneBySql(
            $sql . "termin_id = :date_id",
            [
                'date_id' => $date_id
            ]
        );

        if ($request) {
            return $request->id;
        } else {
            return false;
        }
    }

    public static function existsByMetadate($metadate_id, $request_is_open = false)
    {
        $sql = '';
        if ($request_is_open) {
            $sql .= "closed = '0' AND ";
        }

        $request = self::findOneBySql(
            $sql . "metadate_id = :metadate_id",
            [
                'metadate_id' => $metadate_id
            ]
        );

        if ($request) {
            return $request->id;
        } else {
            return false;
        }
    }

    /**
     * A callback method that creates a Stud.IP log entry
     * when a new request has been made.
     */
    public function cbLogNewRequest()
    {
        $this->sendNewRequestMail();
        StudipLog::log('RES_REQUEST_NEW', $this->id, $this->resource_id);
    }

    /**
     * A callback method that send a mail
     * when a new request has been udpated.
     */
    public function cbAfterStore()
    {
        if($this->closed == '3') {
            $this->sendRequestDeniedMail();
        }
    }

    /**
     * This validation method is called before storing an object.
     */
    public function validate()
    {
        if (!$this->resource_id && !$this->category_id) {
            throw new Exception(
                _('Eine Anfrage muss einer konkreten Ressource oder deren Kategorie zugewiesen sein!')
            );
        }
    }

    public function getDerivedClassInstance()
    {
        if (!$this->resource) {
            //We cannot determine a derived class.
            return $this;
        }
        $class_name = $this->resource->class_name;

        if ($class_name == 'Resource') {
            //This is already the correct class.
            return $this;
        }

        if (is_subclass_of($class_name, 'Resource')) {
            //Now we append 'Request' to the class name:
            $class_name         = $class_name . 'Request';
            $converted_resource = $class_name::buildExisting(
                $this->toRawArray()
            );
            return $converted_resource;
        } else {
            //$class_name does not contain the name of a subclass
            //of Resource. That's an error!
            throw new NoResourceClassException(
                sprintf(
                    _('Die Klasse %1$s ist keine Spezialisierung der Ressourcen-Kernklasse!'),
                    $class_name
                )
            );
        }
    }

    /**
     * Sets the range fields (termin_id, metadate_id, course_id)
     * or the ResourceRequestAppointment objects related to this request
     * according to the range type and its range-IDs specified as parameters
     * for this method. The ResourceRequest object is not stored after
     * setting the fields / related objects.
     *
     * @param string $range_type The range type for this request. One of
     *     the following: 'date', 'cycle', 'course' or 'date-multiple'.
     *
     * @param array $range_ids An array of range-IDs to be set for the
     *     specified range type. This is mostly an array of size one
     *     since the fields termin_id, metadate_id and course_id only
     *     accept one ID. The range type 'date-multiple' accepts multiple
     *     IDs.
     *
     * @return void No return value.
     */
    public function setRangeFields($range_type = '', $range_ids = [])
    {
        if ($range_type == 'date') {
            $this->termin_id   = $range_ids[0];
            $this->metadate_id = '';
        } elseif ($range_type == 'cycle') {
            $this->termin_id   = '';
            $this->metadate_id = $range_ids[0];
        } elseif ($range_type == 'date-multiple') {
            $this->termin_id   = '';
            $this->metadate_id = '';
            $appointments      = [];
            foreach ($range_ids as $range_id) {
                $app                 = new ResourceRequestAppointment();
                $app->appointment_id = $range_id;
                $appointments[]      = $app;
            }
            $this->appointments = $appointments;
        } elseif ($range_type == 'course') {
            $this->termin_id   = '';
            $this->metadate_id = '';
            $this->course_id   = $range_ids[0];
        }
    }

    /**
     * Closes the requests and sends out notification mails.
     * If the request is closed and a resource has been booked,
     * it can be passed as parameter to be included in the notification mails.
     *
     * @param bool $notify_lecturers Whether to notify lecturers of a course
     *     (true) or not (false). Defaults to false. Note that this parameter
     *     is only useful in case the request is bound to a course, either
     *     directly or via a course date or a course cycle date.
     *
     * @param ResourceBooking $bookings The resource bookings that have been
     *     created from this request.
     * @return bool @TODO
     */
    public function closeRequest($notify_lecturers = false, $bookings = [])
    {
        if ($this->closed >= 2) {
            //The request has already been closed.
            return true;
        }

        $this->closed = 1;
        if ($this->isDirty()) {
            $this->store();
        }

        //Now we send the confirmation mail to the requester:
        $this->sendCloseRequestMailToRequester($bookings);

        if ($notify_lecturers) {
            $this->sendCloseRequestMailToLecturers($bookings);
        }

        //Sending successful: The request is closed.
        $this->closed = 2;
        if ($this->isDirty()) {
            return $this->store();
        }
        return true;
    }

    /**
     * Returns the resource requests whose time ranges overlap
     * with those of this resource request.
     *
     * @return ResourceRequest[] An array of ResourceRequest objects.
     */
    public function getOverlappingRequests()
    {
        if ($this->resource) {
            return self::findByResourceAndTimeRanges(
                $this->resource,
                $this->getTimeIntervals(true),
                0,
                [$this->id]
            );
        }
        return [];
    }

    /**
     * Counts the resource requests whose time ranges overlap
     * with those of this resource request.
     *
     * @return int The amount of overlapping resource requests.
     */
    public function countOverlappingRequests()
    {
        if ($this->resource) {
            return self::countByResourceAndTimeRanges(
                $this->resource,
                $this->getTimeIntervals(true),
                0,
                [$this->id]
            );
        }
        return 0;
    }

    /**
     * Returns the resource bookings whose time ranges overlap
     * with those of this resource request.
     *
     * @return ResourceBooking[] An array of ResourceBooking objects.
     */
    public function getOverlappingBookings()
    {
        if ($this->resource) {
            return ResourceBooking::findByResourceAndTimeRanges(
                $this->resource,
                $this->getTimeIntervals(true),
                [0, 2]
            );
        }
        return [];
    }

    /**
     * Counts the resource bookings whose time ranges overlap
     * with those of this resource request.
     *
     * @return int The amount of overlapping resource bookings.
     */
    public function countOverlappingBookings()
    {
        if ($this->resource) {
            return ResourceBooking::countByResourceAndTimeRanges(
                $this->resource,
                $this->getTimeIntervals(true),
                [0, 2]
            );
        }
        return 0;
    }

    /**
     * Returns the repetion interval if regular appointments are used
     * for this request.
     *
     * @return DateInterval|null In case regular appointments are used
     *     for this request a DateInterval is returned.
     *     Otherwise null is returned.
     */
    public function getRepetitionInterval()
    {
        if ($this->metadate_id) {
            //It is a set of regular appointments.
            //We just have to compute the time difference between the first
            //two appointments to get the interval.

            $first_date  = $this->cycle->dates[0];
            $second_date = $this->cycle->dates[1];

            if (!$first_date || !$second_date) {
                //Either only one date is in the set of regular appointments
                //or there is a database error. We cannot continue.
                return null;
            }

            $first_datetime = new DateTime();
            $first_datetime->setTimestamp($first_date->date);
            $second_datetime = new DateTime();
            $second_datetime->setTimestamp($second_date->date);

            return $first_datetime->diff($second_datetime);
        }

        return null;
    }

    public function getStartDate()
    {
        $start_date = new DateTime();
        if (count($this->appointments)) {
            $start_date->setTimestamp($this->appointments[0]->appointment->date);
            return $start_date;
        } elseif ($this->termin_id) {
            $start_date->setTimestamp($this->date->date);
            return $start_date;
        } elseif ($this->metadate_id) {
            $start_date->setTimestamp($this->cycle->dates[0]->date);
            return $start_date;
        } elseif ($this->course_id) {
            $start_date = new DateTime();
            $start_date->setTimestamp($this->course->dates[0]->date);
            return $start_date;
        } elseif ($this->begin) {
            $start_date->setTimestamp($this->begin);
            return $start_date;
        }
        return null;
    }

    public function getEndDate()
    {
        $end_date = new DateTime();
        if (count($this->appointments)) {
            $end_date->setTimestamp($this->appointments->last()->appointment->end_time);
            return $end_date;
        } elseif ($this->termin_id) {
            $end_date->setTimestamp($this->date->end_time);
            return $end_date;
        } elseif ($this->metadate_id) {
            $end_date->setTimestamp($this->cycle->dates->last()->end_time);
            return $end_date;
        } elseif ($this->course_id) {
            $end_date = new DateTime();
            $end_date->setTimestamp($this->course->dates->last()->end_time);
            return $end_date;
        } elseif ($this->end) {
            $end_date->setTimestamp($this->end);
            return $end_date;
        }
        return null;
    }

    public function getStartSemester()
    {
        $start_date = $this->getStartDate();
        if ($start_date instanceof DateTime) {
            return Semester::findByTimestamp($start_date->getTimestamp());
        }
        return null;
    }

    public function getEndSemester()
    {
        $end_date = $this->getEndDate();
        if ($end_date instanceof DateTime) {
            return Semester::findByTimestamp($end_date->getTimestamp());
        }
        return null;
    }

    public function getRepetitionEndDate()
    {
        $repetition_interval = $this->getRepetitionInterval();

        if (!$repetition_interval) {
            //There is no repetition.
            return null;
        }

        return $this->getEndDate();
    }

    /**
     * Retrieves the time intervals by looking at metadate objects
     * and other time interval sources and returns them grouped by metadate.
     * @param bool $with_preparation_time @TODO
     * @return mixed[][][] A three-dimensional array with
     *     the following structure:
     *     - The first dimension has the metadate-id as index. For single dates
     *       an empty string is used as index.
     *     - The second dimension contains two elements:
     *       - 'metadate' => The metadate object. This is only set, if the
     *                       request is for a metadate.
     *       - 'intervals' => The time intervals.
     *     - The third dimension contains a time interval
     *       in the following format:
     *       [
     *           'begin' => The begin timestamp
     *           'end' => The end timestamp
     *           'range' => The name of the range class that provides the range_id.
     *               This is usually the name of the SORM class.
     *           'range_id' => The ID of the single date or ResourceRequestAppointment.
     *       ]
     */
    public function getGroupedTimeIntervals($with_preparation_time = false)
    {
        if (count($this->appointments)) {
            $time_intervals = [
                '' => [
                    'metadate'  => null,
                    'intervals' => []
                ]
            ];
            foreach ($this->appointments as $appointment) {
                if ($with_preparation_time) {
                    $interval = [
                        'begin' => $appointment->appointment->date - $this->preparation_time,
                        'end'   => $appointment->appointment->end_time
                    ];
                } else {
                    $interval = [
                        'begin' => $appointment->appointment->date,
                        'end'   => $appointment->appointment->end_time
                    ];
                }
                $interval['range']                 = 'CourseDate';
                $interval['range_id']              = $appointment->appointment_id;
                $time_intervals['']['intervals'][] = $interval;
            }
            return $time_intervals;
        } elseif ($this->termin_id) {
            if ($with_preparation_time) {
                $interval = [
                    'begin' => $this->date->date - $this->preparation_time,
                    'end'   => $this->date->end_time
                ];
            } else {
                $interval = [
                    'begin' => $this->date->date,
                    'end'   => $this->date->end_time
                ];
            }
            $interval['range']    = 'CourseDate';
            $interval['range_id'] = $this->termin_id;
            return [
                '' => [
                    'metadate'  => null,
                    'intervals' => [$interval]
                ]
            ];
        } elseif ($this->metadate_id) {
            $time_intervals = [
                $this->metadate_id => [
                    'metadate'  => $this->cycle,
                    'intervals' => []
                ]
            ];
            foreach ($this->cycle->dates as $date) {
                if ($with_preparation_time) {
                    $interval = [
                        'begin' => $date->date - $this->preparation_time,
                        'end'   => $date->end_time
                    ];
                } else {
                    $interval = [
                        'begin' => $date->date,
                        'end'   => $date->end_time
                    ];
                }
                $interval['range']                                 = 'CourseDate';
                $interval['range_id']                              = $date->id;
                $time_intervals[$this->metadate_id]['intervals'][] = $interval;
            }
            return $time_intervals;
        } elseif ($this->course_id) {
            $time_intervals = [];
            if ($this->course->cycles) {
                foreach ($this->course->cycles as $cycle) {
                    $time_intervals[$cycle->id] = [
                        'metadate'  => $cycle,
                        'intervals' => []
                    ];
                    if ($cycle->dates) {
                        foreach ($cycle->dates as $date) {
                            if ($with_preparation_time) {
                                $interval = [
                                    'begin' => $date->date - $this->preparation_time,
                                    'end'   => $date->end_time
                                ];
                            } else {
                                $interval = [
                                    'begin' => $date->date,
                                    'end'   => $date->end_time
                                ];
                            }
                            $interval['range']                         = 'CourseDate';
                            $interval['range_id']                      = $date->id;
                            $time_intervals[$cycle->id]['intervals'][] = $interval;
                        }
                    }
                }
            }
            if ($this->course->dates) {
                $time_intervals[''] = [
                    'metadate'  => null,
                    'intervals' => []
                ];
                foreach ($this->course->dates as $date) {
                    if ($date->cycle instanceof SeminarCycleDate) {
                        //Metadates are already handled above.
                        continue;
                    }
                    if ($with_preparation_time) {
                        $interval = [
                            'begin' => $date->date - $this->preparation_time,
                            'end'   => $date->end_time
                        ];
                    } else {
                        $interval = [
                            'begin' => $date->date,
                            'end'   => $date->end_time
                        ];
                    }
                    $interval['range']                 = 'CourseDate';
                    $interval['range_id']              = $date->id;
                    $time_intervals['']['intervals'][] = $interval;
                }
            }
            return $time_intervals;
        } elseif ($this->begin && $this->end) {
            if ($with_preparation_time) {
                $interval = [
                    'begin' => $this->begin - $this->preparation_time,
                    'end'   => $this->end
                ];
            } else {
                $interval = [
                    'begin' => $this->begin,
                    'end'   => $this->end
                ];
            }
            $interval['range']    = 'User';
            $interval['range_id'] = $this->user_id;
            return [
                '' => [
                    'metadate'  => null,
                    'intervals' => [$interval]
                ]
            ];
        } else {
            return [];
        }
    }

    /**
     * Retrieves the time intervals for this request.
     *
     * @param bool $with_preparation_time Whether the preparation time
     *     of the request shall be prepended to the begin timestamp (true)
     *     or whether it should not be included at all (false).
     *     Defaults to false.
     *
     * @param bool $with_range Whether to include data of the Stud.IP range
     *     and its corresponding ID to the request (true) or not (false).
     *     Defaults to false.
     *
     * @return string[][] A two-dimensional array of unix timestamps.
     *     The first dimension contains one entry for each date,
     *     the second dimension contains the start and end timestamp
     *     for the date.
     *     The second dimension uses the array keys 'begin' and 'end'
     *     for start and end date.
     *     If the @with_range parameter is set to true, the second array
     *     dimension also contains the key 'range' for specifying the
     *     range type and 'range_id' for specifying the ID of the
     *     range object.
     *     The range can be "CourseDate", "ResourceRequestAppointment"
     *     or "User". The last two can only be present for simple requests
     *     that are not bound to a course. The range "CourseDate"
     *     can only occur on course-bound requests.
     */
    public function getTimeIntervals($with_preparation_time = false, $with_range = false)
    {
        if (count($this->appointments)) {
            $time_intervals = [];
            foreach ($this->appointments as $appointment) {
                if ($with_preparation_time) {
                    $interval = [
                        'begin' => $appointment->appointment->date - $this->preparation_time,
                        'end'   => $appointment->appointment->end_time
                    ];
                } else {
                    $interval = [
                        'begin' => $appointment->appointment->date,
                        'end'   => $appointment->appointment->end_time
                    ];
                }
                if ($with_range) {
                    $interval['range']    = 'ResourceRequestAppointment';
                    $interval['range_id'] = $appointment->appointment_id;
                }
                $time_intervals[] = $interval;
            }
            return $time_intervals;
        } elseif ($this->termin_id) {
            if ($with_preparation_time) {
                $interval = [
                    'begin' => $this->date->date - $this->preparation_time,
                    'end'   => $this->date->end_time
                ];
            } else {
                $interval = [
                    'begin' => $this->date->date,
                    'end'   => $this->date->end_time
                ];
            }
            if ($with_range) {
                $interval['range']    = 'CourseDate';
                $interval['range_id'] = $this->termin_id;
            }
            return [$interval];
        } elseif ($this->metadate_id) {
            $time_intervals = [];
            foreach ($this->cycle->dates as $date) {
                if ($with_preparation_time) {
                    $interval = [
                        'begin' => $date->date - $this->preparation_time,
                        'end'   => $date->end_time
                    ];
                } else {
                    $interval = [
                        'begin' => $date->date,
                        'end'   => $date->end_time
                    ];
                }
                if ($with_range) {
                    $interval['range']    = 'CourseDate';
                    $interval['range_id'] = $date->id;
                }
                $time_intervals[] = $interval;
            }
            return $time_intervals;
        } elseif ($this->course_id) {
            $time_intervals = [];
            if ($this->course->dates) {
                foreach ($this->course->dates as $date) {
                    if ($with_preparation_time) {
                        $interval = [
                            'begin' => $date->date - $this->preparation_time,
                            'end'   => $date->end_time
                        ];
                    } else {
                        $interval = [
                            'begin' => $date->date,
                            'end'   => $date->end_time
                        ];
                    }
                    if ($with_range) {
                        $interval['range']    = 'CourseDate';
                        $interval['range_id'] = $date->id;
                    }
                    $time_intervals[] = $interval;
                }
            }
            return $time_intervals;
        } elseif ($this->begin && $this->end) {
            if ($with_preparation_time) {
                $interval = [
                    'begin' => $this->begin - $this->preparation_time,
                    'end'   => $this->end
                ];
            } else {
                $interval = [
                    'begin' => $this->begin,
                    'end'   => $this->end
                ];
            }
            if ($with_range) {
                $interval['range']    = 'User';
                $interval['range_id'] = $this->user_id;
            }
            return [$interval];
        } else {
            return [];
        }
    }


    /**
     * Returns a string representation of the time intervals for this request.
     */
    public function getTimeIntervalStrings()
    {
        $strings   = [];
        $intervals = $this->getTimeIntervals();
        foreach ($intervals as $interval) {
            $same_day = (date('Ymd', $interval['begin'])
                == date('Ymd', $interval['end'])
            );
            if ($same_day) {
                $strings[] = strftime('%a %x %R', $interval['begin']) . ' - ' . strftime('%R', $interval['end']);
            } else {
                $strings[] = strftime('%a %x %R', $interval['begin']) . ' - ' . strftime('%a %x %R', $interval['end']);
            }
        }
        return $strings;
    }


    /**
     * Filters the time intervals for this request
     * by a specified time range.
     *
     * @see ResourceRequest::getTimeIntervals for the return format.
     */
    public function getTimeIntervalsInTimeRange(DateTime $begin, DateTime $end)
    {
        $all_time_intervals = $this->getTimeIntervals();

        $included_intervals = [];
        foreach ($all_time_intervals as $interval) {
            $interval_in_range = (
                (
                    $interval['begin'] >= $begin->getTimestamp()
                    and
                    $interval['begin'] <= $end->getTimestamp()
                )
                or
                (
                    $interval['end'] >= $begin->getTimestamp()
                    and
                    $interval['end'] <= $end->getTimestamp()
                )
            );
            if ($interval_in_range) {
                $included_intervals[] = $interval;
            }
        }

        return $included_intervals;
    }


    /**
     * Returns a string representation of the ResourceRequest's type.
     */
    public function getType()
    {
        if (count($this->appointments)) {
            return 'appointments';
        } elseif ($this->termin_id) {
            return 'date';
        } elseif ($this->metadate_id) {
            return 'cycle';
        } elseif ($this->course_id) {
            return 'course';
        }
        return null;
    }

    /**
     * Returns a string representation of the status of the ResourceRequest.
     */
    public function getStatus()
    {
        switch ($this->closed) {
            case '0':
                return 'open';
            case '1':
                return 'pending';
            case '2':
                return 'closed';
            case '3':
                return 'declined';
            default:
                return '';
        }
    }


    /**
     * Returns a textual representation of the status of the ResourceRequest.
     */
    public function getStatusText()
    {
        if ($this->isNew()) {
            return _('Diese Anfrage wurde noch nicht gespeichert.');
        }
        if ($this->closed == 0) {
            return _('Die Anfrage wurde noch nicht bearbeitet.');
        } else if ($this->closed == 3) {
            return _('Die Anfrage wurde bearbeitet und abgelehnt.');
        } else {
            return _('Die Anfrage wurde bearbeitet.');
        }

        return _('unbekannt');
    }


    /**
     * Returns a textual representation of the dates for which the request
     * has been created.
     *
     * @param bool $as_array True, if an array with a string for each date
     *     (single or cycle date) shall be returned, false otherwise.
     *
     * @returns string|array Depending on the parameter $as_array, the text
     *     is returned as one string or as an array of strings for each date
     *     (single or cycle date).
     */
    public function getDateString($as_array = false)
    {
        $strings = [];
        if (count($this->appointments)) {
            $parts  = [];
            foreach ($this->appointments as $rra) {
                if ($rra->appointment) {
                    $parts[] = $rra->appointment->getFullname('include-room');
                }
            }
            $strings[] .= implode('; ', $parts);
        } elseif ($this->termin_id) {
            if ($this->date) {
                $strings[] = $this->date->getFullname('include-room');
            }
        } elseif ($this->metadate_id) {
            if ($this->cycle) {
                $this->cycle->dates->map(function($date) use(&$strings){
                    $strings[] = $date->getFullname('include-room');
                });
            }
        } elseif ($this->course_id) {
            $course = new Seminar($this->course_id);
            if ($course) {
                $strings[] = $course->getDatesExport(
                    [
                        'short'     => true,
                        'shrink'    => true,
                        'show_room' => true
                    ]
                );
            }
        } elseif ($this->begin && $this->end) {
            $begin_date = date('Ymd', $this->begin);
            $end_date   = date('Ymd', $this->end);
            if($this->resource) {
                $resource_name = htmlReady($this->resource->getFullName());
            }
            if ($begin_date == $end_date) {
                $strings[] = strftime('%a., %x, %R', $this->begin) . ' - '
                           . strftime('%R', $this->end) . ' ' . $resource_name;
            } else {
                //Begin and end are on differnt dates
                $strings[] = strftime('%a., %x, %R', $this->begin) . ' - '
                    . strftime('%a., %x, %R', $this->end) . ' ' . $resource_name;
            }
        }

        if ($as_array) {
            return $strings;
        } else {
            return implode(';', $strings);
        }
    }


    /**
     * Returns a human-readable string describing the type of the request.
     *
     * @param bool $short If this parameter is set to true, only the
     *     type of the request is returned without any information about the
     *     appointments. Otherwise, appointment information like the
     *     date or the repetition are appended. Defaults to false.
     * @return string
     */
    public function getTypeString($short = false)
    {
        if (count($this->appointments)) {
            if ($short) {
                return _('Einzeltermine');
            } else {
                return sprintf(_('Einzeltermine (%sx)'), count($this->appointments));
            }
        } elseif ($this->date) {
            if ($short) {
                return _('Einzeltermin');
            } else {
                return sprintf(_('Einzeltermin (%s)'), $this->date->getFullname());
            }
        } elseif ($this->cycle) {
            if ($short) {
                return _('Regelmäßige Termine');
            } else {
                return sprintf(
                    _('Regelmäßige Termine (%s)'),
                    $this->cycle->toString('short')
                );
            }
        } elseif ($this->course) {
            if ($short) {
                return _('Alle Termine der Veranstaltung');
            } else {
                return sprintf(
                    _('Alle Termine der Veranstaltung (%sx)'),
                    count($this->course->dates)
                );
            }
        } else {
            return _('Einfache Anfrage');
        }
    }


    /**
     * Returns an array of date objects which are affected
     * by this ResourceRequest.
     */
    public function getAffectedDates()
    {
        $dates = [];
        switch ($this->getType()) {
            case 'date':
                $dates[] = $this->date;
                break;
            case 'cycle':
                $dates = $this->cycle->dates->getArrayCopy();
                break;
            case 'course':
                $dates = $this->course->dates->getArrayCopy();
                break;
        }
        return $dates;
    }


    /**
     * @param arrya $excluded_property_names
     * Returns all resource property definitions for all properties
     * which can be applied for this ResourceRequest by looking at the
     * Resource category. If no resource category ID is set for the request
     * an empty array is returned.
     */
    public function getAvailableProperties($excluded_property_names = [])
    {
        if (!$this->category_id) {
            //Without a category-ID we cannot find any property!
            return [];
        }
        if (count($excluded_property_names)) {
            return ResourcePropertyDefinition::findBySql(
                "INNER JOIN resource_category_properties
                USING (property_id)
                WHERE requestable = '1' AND category_id = :category_id
                AND name NOT IN ( :excluded_property_names )",
                [
                    'category_id'             => $this->category_id,
                    'excluded_property_names' => $excluded_property_names
                ]
            );
        } else {
            return ResourcePropertyDefinition::findBySql(
                "INNER JOIN resource_category_properties
                USING (property_id)
                WHERE requestable = '1' AND category_id = :category_id",
                [
                    'category_id' => $this->category_id
                ]
            );
        }
    }


    /**
     * Returns a "compressed" array of resource request properties.
     * @param array $excluded_property_names
     * @return array An associative array where the keys represent the
     *     property names and the values represent the property states.
     *     Note that the value can be an array in case of range properties.
     */
    public function getPropertyData($excluded_property_names = [])
    {
        $data = [];
        foreach ($this->properties as $property) {
            if ($property->definition->range_search) {
                //Assume that a minimum value is requested:
                $data[$property->name] = [$property->state];
            } else {
                $data[$property->name] = $property->state;
            }
        }
        return $data;
    }

    /**
     * @param $name
     * @return bool
     */
    public function propertyExists($name)
    {
        $db = DBManager::get();

        $exists_stmt = $db->prepare(
            "SELECT TRUE FROM resource_request_properties
            INNER JOIN resource_property_definitions rpd
                ON resource_request_properties.property_id = rpd.property_id
            WHERE resource_request_properties.request_id = :request_id
                AND rpd.name = :name");

        $exists_stmt->execute(
            [
                'request_id' => $this->id,
                'name'       => $name
            ]
        );

        $exists = $exists_stmt->fetchColumn(0);

        return (bool)$exists;
    }


    /**
     * @param $name
     * Returns the state of the property specified by $name.
     */
    public function getProperty($name)
    {
        if (!$this->propertyExists($name)) {
            //A property with the name $name does not exist for this
            //resource request object.
            //In that case we can only return null, since resource requests
            //store only those properties which are requested:

            return null;
        }

        $db = DBManager::get();

        $value_stmt = $db->prepare(
            "SELECT resource_request_properties.state FROM resource_request_properties
            INNER JOIN resource_property_definitions rpd
                ON resource_request_properties.property_id = rpd.property_id
            WHERE resource_request_properties.request_id = :request_id
                AND rpd.name = :name");

        $value_stmt->execute(
            [
                'request_id' => $this->id,
                'name'       => $name
            ]
        );

        $value = $value_stmt->fetchColumn(0);

        if (!$value) {
            return null;
        }

        return $value;
    }


    /**
     * @param $name
     * @return ResourceRequestProperty
     * @throws InvalidResourceCategoryException If this resource category
     *     doesn't match the category of the resource request object.
     * @throws ResourcePropertyException If the name of the
     *     resource request property is not defined for this resource category.
     */
    public function getPropertyObject($name)
    {
        if (!$this->propertyExists($name)) {
            //A property with the name $name does not exist for this
            //resource object. If it is a mandatory property
            //we can still try to create it:

            $property = $this->category->createDefinedResourceRequestProperty(
                $this,
                $name
            );

            $property->store();
            return $property;
        }

        return ResourceRequestProperty::findOneBySql(
            "INNER JOIN resource_property_definitions rpd
                ON resource_request_properties.property_id = rpd.property_id
            WHERE resource_request_properties.request_id = :request_id
                AND rpd.name = :name",
            [
                'request_id' => $this->id,
                'name'       => $name
            ]
        );
    }


    /**
     * @param string $name
     * @param string $state
     * @return True, if the property state could be set, false otherwise.
     */
    public function setProperty($name, $state = '')
    {
        if (!$this->propertyExists($name)) {
            //A property with the name $name does not exist for this
            //resource object. If it is a mandatory property
            //we can still try to create it:

            if ($this->category) {
                $property = $this->category->createDefinedResourceRequestProperty(
                    $this,
                    $name,
                    $state
                );
                return $property->store();
            }
            return false;
        }

        $property = $this->getPropertyObject($name);

        if ($property) {
            $property->state = $state;
            if ($property->isDirty()) {
                return $property->store();
            }
            return true;
        }
    }


    /**
     * Sets or unsets the properties for this resource request.
     *
     * @param array $property_list The properties which shall be set
     *     or unset. The array has the following structure:
     *     [
     *         property_name => property_value
     *     ]
     *
     * @param bool $accept_null_values True, if a value of null
     *     shall be used when setting the property.
     *     If $accept_null_values is set to false all properties
     *     with a value equal to null will be deleted.
     *
     * @return null
     */
    public function updateProperties($property_list = [], $accept_null_values = false)
    {
        //Delete all properties first then re-create them
        //from the $property_list array:
        $this->properties->delete();
        if (is_array($property_list)) {
            foreach ($property_list as $name => $state) {
                if ($state or $accept_null_values) {
                    //State is set or null values are allowed:
                    //create/update the property
                    $this->setProperty($name, $state);
                }
            }
        }
        $this->resetRelation('properties');
    }


    public function deletePropertyIfExists($name = '')
    {
        if (!$this->propertyExists($name)) {
            return true;
        } else {
            $property = $this->getPropertyObject($name);
            return $property->delete();
        }
    }


    public function getRangeName()
    {
        if ($this->getRangeType() === 'course') {
            $name = $this->getRangeObject()->getFullname();
            $name .= ' (' . implode(',', $this->getRangeObject()->getMembersWithStatus('dozent', true)->limit(3)->getValue('nachname')) . ')';
        } else {
            $range_object = $this->getRangeObject();
            if ($range_object instanceof User) {
                if (get_visibility_by_id($range_object->id)) {
                    $name = $range_object->getFullName();
                } else if ($this->user_id == $GLOBALS['user']->id) {
                    $name = $range_object->getFullName();
                } else {
                    $current_user = User::findCurrent();
                    if ($current_user instanceof User) {
                        //If the current user has at least autor permissions
                        //(which are required to see all requests), they can
                        //see the name of the requester.
                        if ($this->resource_id && ($this->resource instanceof Resource)
                            && $this->resource->userHasPermission($current_user, 'autor')) {
                            $name = $range_object->getFullname();
                        } else if (ResourceManager::userHasGlobalPermission($current_user, 'autor')) {
                            $name = $range_object->getFullname();
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                }
            } else {
                $name = $range_object->getFullname();
            }
            if ($this->comment) {
                $name .= " \n" . $this->comment;
            }
        }
        return $name;
    }


    public function isSimpleRequest()
    {
        return !$this->course_id && !$this->metadate_id && !$this->termin_id;
    }


    public function getRangeId()
    {
        //Check if the request belongs to a course:
        if ($this->termin_id) {
            return $this->date->range_id;
        } elseif ($this->metadate_id) {
            return $this->cycle->seminar_id;
        } elseif ($this->course_id) {
            return $this->course_id;
        }

        //The request does not belong to a course and therefore
        //belongs to a user:
        return $this->user_id;
    }


    public function getRangeType()
    {
        if ($this->course_id || $this->termin_id || $this->metadate_id) {
            return 'course';
        }
        return 'user';
    }


    public function getRangeObject()
    {
        if ($this->course_id) {
            return $this->course;
        }
        if ($this->termin_id) {
            return $this->date->course;
        }
        if ($this->metadate_id) {
            return $this->cycle->course;
        }
        return $this->user;
    }


    /**
     * This method sends a notification mail to all room administrators
     * that informs them of this new request.
     */
    public function sendNewRequestMail()
    {
        //First we must get all users who have admin permissions in the
        //resource management system. Depending wheter a resource_id is set
        //for this resource request either all admins of a resource or
        //all admins of the resource management system must be informed.

        $now         = time();
        if ($this->resource_id) {
            //The resource-ID is set for this request:
            //Get all admins of the resource and the resource management system.
            $admin_users = User::findBySql(
                "user_id IN (
                    SELECT user_id FROM resource_permissions
                    WHERE (
                        resource_id = :resource_id
                        OR resource_id = 'global'
                    )
                    AND perms = 'admin'
                    UNION
                    SELECT user_id FROM resource_temporary_permissions
                    WHERE resource_id = :resource_id
                    AND perms = 'admin'
                    AND begin <= :now AND end >= :now
                )",
                [
                    'resource_id' => $this->resource_id,
                    'now'         => $now
                ]
            );
        } else {
            //Get all admins of the resource management system.
            $admin_users = User::findBySql(
                "user_id IN (
                    SELECT user_id FROM resource_permissions
                    WHERE resource_id = 'global'
                    AND perms = 'admin'
                    UNION
                    SELECT user_id FROM resource_temporary_permissions
                    WHERE resource_id = 'global'
                    AND perms = 'admin'
                    AND begin <= :now AND end >= :now
                    GROUP BY user_id
                )",
                [
                    'now' => $now
                ]
            );
        }

        if (!$admin_users) {
            return;
        }

        $factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
        );

        foreach ($admin_users as $user) {
            $user_lang_path = getUserLanguagePath($user->id);

            $template = $factory->open(
                $user_lang_path . '/LC_MAILS/new_resource_request.php'
            );
            $template->set_attribute('request', $this);

            if ($this->resource instanceof Resource) {
                $resource = $this->resource->getDerivedClassInstance();
                if ($resource instanceof Room) {
                    $template->set_attribute('requested_room', $resource->name);
                } else {
                    $template->set_attribute('requested_resource', $resource->name);
                }
            }

            $mail_text = $template->render();

            setLocaleEnv($user->preferred_language);

            if ($this->resource) {
                $resource = $this->resource->getDerivedClassInstance();
                $template->set_attribute('derived_resource', $resource);
                $mail_title = sprintf(
                    _('%1$s: Neue Anfrage in der Raumverwaltung'),
                    $resource->getFullName()
                );
            } else {
                $mail_title = sprintf(
                    _('Neue Anfrage in der Raumverwaltung')
                );
            }

            Message::send(
                '____%system%____',
                $user->username,
                $mail_title,
                $mail_text
            );

            restoreLanguage();
        }
    }


    /**
     * @param array $bookings
     * This method sends a mail to inform the requester that
     * the request has been closed.
     */
    public function sendCloseRequestMailToRequester($bookings = [])
    {
        $factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
        );

        $requester_lang      = $this->user->preferred_language;
        $requester_lang_path = getUserLanguagePath($this->user->id);
        setLocaleEnv($requester_lang);

        $template = $factory->open(
            $requester_lang_path . '/LC_MAILS/close_resource_request.php'
        );
        $template->set_attribute('request', $this);
        if ($this->course) {
            $lecturers      = CourseMember::findByCourseAndStatus(
                $this->course->id,
                'dozent'
            );
            $lecturer_names = [];
            foreach ($lecturers as $lecturer) {
                if ($lecturer->user instanceof User) {
                    $lecturer_names[] = $lecturer->user->getFullName();
                }
            }

            $lecturer_names = implode(', ', $lecturer_names);
            $template->set_attribute('lecturer_names', $lecturer_names);
        }
        if (is_array($bookings)) {
            $booked_rooms          = [];
            $booked_time_intervals = [];
            $metadates             = [];
            $single_dates          = [];
            foreach ($bookings as $booking) {
                if (!($booking instanceof ResourceBooking)) {
                    continue;
                }
                $booked_rooms[] = $booking->resource->name;
                if ($booking->assigned_course_date instanceof CourseDate) {
                    $single_date = $booking->assigned_course_date;
                    $metadate    = $single_date->cycle;
                    if ($metadate instanceof SeminarCycleDate) {
                        $metadates[$metadate->id] = $metadate;
                    } else {
                        $single_dates[$single_date->id] = $single_date;
                    }
                } else {
                    $time_intervals = $booking->getTimeIntervals();
                    foreach ($time_intervals as $time_interval) {
                        $booked_time_intervals[] = $time_interval->__toString();
                    }
                }
            }
            $booked_rooms = array_unique($booked_rooms);
            sort($booked_rooms);
            $template->set_attribute('booked_rooms', implode(', ', $booked_rooms));
            $template->set_attribute('metadates', $metadates);
            $template->set_attribute('single_dates', $single_dates);
            $template->set_attribute('booked_time_intervals', $booked_time_intervals);
        }

        $mail_title = _('Ihre Anfrage wurde bearbeitet!');
        $mail_text  = $template->render();

        Message::send(
            '____%system%____',
            $this->user->username,
            $mail_title,
            $mail_text
        );

        restoreLanguage();
    }


    /**
     * @param array $bookings
     * This method sends mails to the lecurers of the course (if any)
     * where this request has been assigned to. The sent mail informs them
     * about the closing of the request.
     */
    public function sendCloseRequestMailToLecturers($bookings = [])
    {
        //Notify each lecturer of the course:
        if ($this->course) {
            $lecturers = CourseMember::findByCourseAndStatus(
                $this->course->id,
                'dozent'
            );

            if ($lecturers) {
                $factory = new Flexi_TemplateFactory(
                    $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
                );

                $lecturer_names = [];
                foreach ($lecturers as $lecturer) {
                    if ($lecturer->user instanceof User) {
                        $lecturer_names[] = $lecturer->user->getFullName();
                    }
                }
                $lecturer_names = implode(', ', $lecturer_names);

                $booked_rooms          = [];
                $booked_time_intervals = [];
                $metadates             = [];
                $single_dates          = [];
                if (is_array($bookings)) {
                    $booked_rooms = [];
                    foreach ($bookings as $booking) {
                        if (!($booking instanceof ResourceBooking)) {
                            continue;
                        }
                        $booked_rooms[] = $booking->resource->name;
                        if ($booking->assigned_course_date instanceof CourseDate) {
                            $single_date = $booking->assigned_course_date;
                            $metadate    = $single_date->cycle;
                            if ($metadate instanceof SeminarCycleDate) {
                                $metadates[$metadate->id] = $metadate;
                            } else {
                                $single_dates[$single_date->id] = $single_date;
                            }
                        } else {
                            $time_intervals = $booking->getTimeIntervals();
                            foreach ($time_intervals as $time_interval) {
                                $booked_time_intervals[] = $time_interval->__toString();
                            }
                        }
                    }
                }
                $booked_rooms = array_unique($booked_rooms);
                sort($booked_rooms);
                $booked_rooms = implode(', ', $booked_rooms);

                foreach ($lecturers as $lecturer) {
                    $lec_lang      = $lecturer->user->preferred_language;
                    $lec_lang_path = getUserLanguagePath($lecturer->user->id);

                    setLocaleEnv($lec_lang);

                    $template = $factory->open(
                        $lec_lang_path . '/LC_MAILS/close_resource_request.php'
                    );
                    $template->set_attribute('request', $this);
                    $template->set_attribute('lecturer_names', $lecturer_names);
                    $template->set_attribute('booked_rooms', $booked_rooms);
                    $template->set_attribute('metadates', $metadates);
                    $template->set_attribute('single_dates', $single_dates);
                    $template->set_attribute('booked_time_intervals', $booked_time_intervals);

                    $mail_title = _('Bearbeitung einer Anfrage!');
                    $mail_text  = $template->render();

                    Message::send(
                        '____%system%____',
                        $lecturer->user->username,
                        $mail_title,
                        $mail_text
                    );

                    restoreLanguage();
                }
            }
        }
    }


    /**
     * This method sends a mail to inform the requester
     * about the denial of the request.
     */
    public function sendRequestDeniedMail()
    {
        //Get the user who made the request:
        $user = $this->user;
        if (!($user instanceof User)) {
            //No mail to send.
            return;
        }

        //Load the mail template:
        $factory        = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
        );
        $user_lang_path = getUserLanguagePath($user->id);
        $template       = $factory->open(
            $user_lang_path . '/LC_MAILS/request_denied_mail.inc.php'
        );

        $range_object = $this->getRangeObject();
        $mail_title = _('Raumanfrage wurde abgelehnt');
        if($range_object instanceof Course) {
            $mail_title .= ': ' . $range_object->getFullname();
        }
        $mail_text  = $template->render(
            [
                'request' => $this,
                'range_object' => $range_object
            ]
        );

        //Send the mail:
        Message::send(
            '____%system%____',
            $user->username,
            $mail_title,
            $mail_text
        );
    }


    public function isReadOnlyForUser(User $user)
    {
        $resource = $this->resource;
        if (!$resource) {
            //We cannot continue with the permission check.
            return false;
        }
        $resource = $resource->getDerivedClassInstance();

        return !$resource->userHasPermission($user, 'autor')
            && ($this->user_id != $user->id);
    }

    protected function convertToEventData(array $time_intervals, User $user)
    {
        $booking_plan_request_bg     =
            ColourValue::find('Resources.BookingPlan.Request.Bg');
        $booking_plan_request_fg     =
            ColourValue::find('Resources.BookingPlan.Request.Fg');
        $booking_plan_preparation_bg =
            ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $booking_plan_preparation_fg =
            ColourValue::find('Resources.BookingPlan.PreparationTime.Fg');

        $user_is_resource_autor = false;
        if ($this->resource_id && ($this->resource instanceof Resource)) {
            $user_is_resource_autor = $this->resource->userHasPermission(
                $user,
                'autor'
            );
        }
        $request_is_editable =
            $user_is_resource_autor || ($user->id == $this->user_id);

        $request_api_urls  = [];
        $request_view_urls = [];

        if ($request_is_editable) {
            $request_api_urls = [
                'resize' => URLHelper::getURL(
                    'api.php/resources/request/'
                    . $this->id . '/move',
                    [
                        'quiet' => '1'
                    ]
                ),
                'move'   => URLHelper::getURL(
                    'api.php/resources/request/'
                    . $this->id . '/move',
                    [
                        'quiet' => '1'
                    ]
                )
            ];

            $request_view_urls = [
                'edit' => URLHelper::getURL(
                    'dispatch.php/resources/room_request/edit/'
                    . $this->id
                )
            ];
            if ($this->resource_id && ($this->resource instanceof Resource)) {
                if ($this->resource->userHasBookingRights($user)) {
                    $request_view_urls['edit'] = URLHelper::getURL(
                        'dispatch.php/resources/room_request/resolve/'
                        . $this->id
                    );
                }
            }
        }

        $events = [];

        foreach ($time_intervals as $interval) {
            $real_begin = $interval['begin'];
            if ($this->preparation_time) {
                $real_begin += $this->preparation_time;
                $begin      = new DateTime();
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
                    $request_is_editable,
                    '',
                    '',
                    'ResourceRequest',
                    $this->id,
                    'Resource',
                    $this->resource_id,
                    $request_view_urls,
                    $request_api_urls
                );
            }

            $begin = new DateTime();
            $begin->setTimestamp($real_begin);
            $end = new DateTime();
            $end->setTimestamp($interval['end']);

            $events[] = new Studip\Calendar\EventData(
                $begin,
                $end,
                $this->getRangeName(),
                ['resource-request'],
                $booking_plan_request_fg->__toString(),
                $booking_plan_request_bg->__toString(),
                $request_is_editable,
                'ResourceRequest',
                $this->id,
                'Resource',
                $this->resource_id,
                'Resource',
                $this->resource_id,
                $request_view_urls,
                $request_api_urls
            );
        }

        return $events;
    }


    public function getAllEventData()
    {
        return $this->convertToEventData(
            $this->getTimeIntervals(true),
            User::findCurrent()
        );
    }


    public function getEventDataForTimeRange(DateTime $begin, DateTime $end)
    {
        $intervals      = $this->getTimeIntervals(true);
        $time_intervals = [];

        $begin_timestamp = $begin->getTimestamp();
        $end_timestamp   = $end->getTimestamp();

        foreach ($intervals as $interval) {
            if ((($interval['begin'] >= $begin_timestamp)
                    && ($interval['begin'] <= $end_timestamp)) ||
                (($interval['end'] >= $begin_timestamp)
                    && ($interval['end'] <= $end_timestamp)) ||
                (($interval['begin'] < $begin_timestamp)
                    && ($interval['end'] > $end_timestamp))
            ) {
                $time_intervals[] = $interval;
            }
        }

        return $this->convertToEventData($time_intervals, User::findCurrent());
    }


    public function getFilteredEventData(
        $user_id = null,
        $range_id = null,
        $range_type = null,
        $begin = null,
        $end = null
    )
    {
        $intervals      = $this->getTimeIntervals(true);
        $time_intervals = [];

        if ($begin && $end) {
            $begin_timestamp = $begin;
            $end_timestamp   = $end;
            if ($begin instanceof DateTime) {
                $begin_timestamp = $begin->getTimestamp();
            }
            if ($end instanceof DateTime) {
                $end_timestamp = $end->getTimestamp();
            }

            foreach ($intervals as $interval) {
                if ((($interval['begin'] >= $begin_timestamp)
                        && ($interval['begin'] <= $end_timestamp)) ||
                    (($interval['end'] >= $begin_timestamp)
                        && ($interval['end'] <= $end_timestamp)) ||
                    (($interval['begin'] < $begin_timestamp)
                        && ($interval['end'] > $end_timestamp))
                ) {
                    $time_intervals[] = $interval;
                }
            }
        } else {
            $time_intervals = $intervals;
        }

        $user = null;
        if ($user_id) {
            $user = User::find($user_id);
        } else {
            $user = User::findCurrent();
        }

        return $this->convertToEventData($time_intervals, $user);
    }

    public function getPriority()
    {
        $first = $this->getTimeIntervals()[0];
        $diff  = round(($first['begin'] - time()) / 86400);
        return $diff;
    }
}