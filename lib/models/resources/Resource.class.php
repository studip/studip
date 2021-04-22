<?php

/**
 * Resource.class.php - model class for a resource
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
 * @property string resource_id database column
 * @property string id alias column for resource_id
 * @property string parent_id database column
 * @property string category_id database column
 * @property string level database column
 * @property string name database column
 * @property string description database column
 * @property string requestable database column
 * @property string sort_position database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection category belongs_to ResourceCategory
 * @property SimpleORMapCollection properties has_many ResourceProperty
 * @property SimpleORMapCollection permissions has_many ResourcePermission
 * @property SimpleORMapCollection bookings has_many ResourceBooking
 * @property SimpleORMapCollection children has_many Resource
 * @property FolderType folder
 */


/**
 * The Resource class is the base class of the new
 * Room and Resource management system in Stud.IP.
 * It provides core functionality for handling general resources
 * and can be derived for handling special resources.
 */
class Resource extends SimpleORMap implements StudipItem
{
    /**
     * This is a cache for permissions that users have on resources.
     * It is meant to reduce the database requests in cases where the
     * same permission is queried a lot of times.
     */
    protected static $permission_cache;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'resources';

        $config['belongs_to']['category'] = [
            'class_name'  => 'ResourceCategory',
            'foreign_key' => 'category_id',
            'assoc_func'  => 'find'
        ];

        $config['has_many']['properties'] = [
            'class_name'        => 'ResourceProperty',
            'assoc_foreign_key' => 'resource_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];

        $config['has_many']['permissions'] = [
            'class_name'        => 'ResourcePermission',
            'assoc_foreign_key' => 'resource_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];

        $config['has_many']['requests'] = [
            'class_name'        => 'ResourceRequest',
            'assoc_foreign_key' => 'resource_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];

        $config['has_many']['bookings'] = [
            'class_name'        => 'ResourceBooking',
            'assoc_foreign_key' => 'resource_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];

        $config['has_many']['children'] = [
            'class_name' => 'Resource',
            'assoc_func' => 'findChildren',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];

        $config['belongs_to']['parent'] = [
            'class_name'  => 'Resource',
            'foreign_key' => 'parent_id'
        ];

        $config['i18n_fields']['description'] = true;

        $config['additional_fields']['class_name']        = ['category', 'class_name'];
        $config['registered_callbacks']['before_store'][] = 'cbValidate';

        parent::configure($config);
    }

    /**
     * Returns the children of a resource.
     * The children are converted to an instance of the derived class,
     * if they are not instances of the default Resource class.
     */
    public static function findChildren($resource_id)
    {
        $children = self::findBySql(
            'parent_id = :parent_id ORDER BY name ASC',
            ['parent_id' => $resource_id]
        );

        if (!$children) {
            return [];
        }

        foreach ($children as &$child) {
            $child = $child->getDerivedClassInstance();
        }
        return $children;
    }

    /**
     * Returns a translation of the resource class name.
     * The translated name can be singular or plural, depending
     * on the value of the parameter $item_count.
     *
     * @param int $item_count The amount of items the translation shall be
     *     made for. This is only used to determine, if a singular or a
     *     plural form shall be returned.
     *
     * @return string The translated form of the class name, either in
     *     singular or plural.
     *
     */
    public static function getTranslatedClassName($item_count = 1)
    {
        return ngettext(
            'Ressource',
            'Ressourcen',
            $item_count
        );
    }

    /**
     * Retrieves all resources which don't have a parent resource assigned.
     * Such resources are called root resources since they are roots of
     * a resource hierarchy (or a resource tree).
     *
     * @return Resource[] An array of Resource objects
     *     which are root resources.
     */
    public static function getRootResources()
    {
        return self::findBySql("parent_id = '' ORDER BY name");
    }

    /**
     * A method for overloaded classes so that they can define properties
     * that are required for that resource class.
     *
     * @return string[] An array with the names of the required properties.
     *     Example: The properties with the names "foo", "bar" and "baz"
     *     are required properties. The array would have the following content:
     *     [
     *         'foo',
     *         'bar',
     *         'baz'
     *     ]
     */
    public static function getRequiredProperties()
    {
        return [];
    }


    /**
     * Returns the part of the URL for getLink and getURL which will be
     * placed inside the calls to URLHelper::getLink and URLHelper::getURL
     * in these methods.
     *
     * @param string $action The action for the resource.
     * @param string $id The ID of the resource.
     *
     * @return string The URL path for the specified action.
     * @throws InvalidArgumentException If $resource_id is empty.
     *
     */
    protected static function buildPathForAction($action = 'show', $id = null)
    {
        $actions_without_id = ['add'];
        if (!$id && !in_array($action, $actions_without_id)) {
            throw new InvalidArgumentException(
                _('Zur Erstellung der URL fehlt eine Ressourcen-ID!')
            );
        }

        switch ($action) {
            case 'show':
                return 'dispatch.php/resources/resource/index/' . $id;
            case 'add':
                return 'dispatch.php/resources/resource/add';
            case 'edit':
                return 'dispatch.php/resources/resource/edit/' . $id;
            case 'files':
                return 'dispatch.php/resources/resource/files/' . $id . '/';
            case 'permissions':
                return 'dispatch.php/resources/resource/permissions/' . $id;
            case 'temporary_permissions':
                return 'dispatch.php/resources/resource/temporary_permissions/' . $id;
            case 'booking_plan':
                return 'dispatch.php/resources/room_planning/booking_plan/' . $id;
            case 'request_plan':
                return 'dispatch.php/resources/room_planning/request_plan/' . $id;
            case 'semester_plan':
                return 'dispatch.php/resources/room_planning/semester_plan/' . $id;
            case 'assign-undecided':
                return 'dispatch.php/resources/booking/add/' . $id;
            case 'assign':
                return 'dispatch.php/resources/booking/add/' . $id . '/0';
            case 'reserve':
                return 'dispatch.php/resources/booking/add/' . $id . '/1';
            case 'lock':
                return 'dispatch.php/resources/booking/add/' . $id . '/2';
            case 'delete_bookings':
                return 'dispatch.php/resources/resource/delete_bookings/' . $id;
            case 'export_bookings':
                return 'dispatch.php/resources/export/resource_bookings/' . $id;
            case 'delete':
                return 'dispatch.php/resources/resource/delete/' . $id;
            default:
                return 'dispatch.php/resources/resource/show/' . $id;
        }
    }

    /**
     * Returns the appropriate link for the resource action that shall be
     * executed on a resource.
     *
     * @param string $action The action which shall be executed.
     *     For default Resources the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param string $id The ID of the resource on which the specified
     *     action shall be executed.
     * @param array $link_parameters Optional parameters for the link.
     *
     * @return string The Link for the resource action.
     * @throws InvalidArgumentException If $resource_id is empty.
     *
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
     * Returns the appropriate URL for the resource action that shall be
     * executed on a resource.
     *
     * @param string $action The action which shall be executed.
     *     For default Resources the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param string $id The ID of the resource on which the specified
     *     action shall be executed.
     * @param array $url_parameters Optional parameters for the URL.
     *
     * @return string The URL for the resource action.
     * @throws InvalidArgumentException If $resource_id is empty.
     *
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

    /**
     * The SORM store method is overloaded to assure that the right level
     * attribute is stored.
     */
    public function store()
    {
        //Set the level attribute according to the parent's
        //level attribute. If no parents are defined
        //set the level to zero.
        if ($this->parent_id && $this->parent) {
            $this->level = $this->parent->level + 1;
        } else {
            $this->level = 0;
        }

        //Store the folder, if it hasn't been stored before:

        $folder = $this->getFolder();
        if ($folder) {
            $folder->store();
        }

        return parent::store();
    }

    public function delete()
    {
        //Delete the folder:

        $folder = $this->getFolder(false);
        if ($folder) {
            $folder->delete();
        }

        return parent::delete();
    }

    public function cbValidate()
    {
        if (!$this->category_id) {
            throw new InvalidResourceException(
                sprintf(
                    _('Die Ressource %s ist keiner Ressourcenkategorie zugeordnet!'),
                    $this->name
                )
            );
        }
        return true;
    }

    public function __toString()
    {
        return $this->getFullName();
    }


    /**
     * Retrieves the folder for this resource.
     *
     * @param bool $create_if_missing Whether to create a folder (true) or
     *     not (false) in case no folder exists for this resource.
     *     Defaults to true.
     *
     * @returns ResourceFolder|null Either a ResourceFolder instance or null
     *     in case no such instance can be retrieved or created.
     */
    public function getFolder($create_if_missing = true)
    {
        $folder = Folder::findOneByRange_id($this->id);

        if ($folder) {
            $folder = $folder->getTypedFolder();

            if ($folder instanceof ResourceFolder) {
                //Only return ResourceFolder instances.
                return $folder;
            }
        } elseif ($create_if_missing) {
            $folder = $this->createFolder();
            if ($folder instanceof ResourceFolder) {
                return $folder;
            }
        }
        //In all other cases return null:
        return null;
    }

    public function setFolder(ResourceFolder $folder)
    {
        if ($this->isNew()) {
            $this->store();
        }

        $folder->range_id   = $this->id;
        $folder->range_type = 'Resource';

        return $folder->store();
    }

    public function createFolder()
    {
        if ($this->isNew()) {
            $this->id = $this->getNewId();
        }

        $folder = Folder::createTopFolder(
            $this->id,
            'Resource',
            'ResourceFolder'
        );

        if ($folder) {
            $folder = $folder->getTypedFolder();
            if ($folder) {
                $folder->store();
                return $folder;
            }
        }

        return null;
    }

    /**
     * Returns a list of property names that are required
     * for the resource class.
     *
     * @return string[] An array with the property names.
     */
    public function getRequiredPropertyNames()
    {
        return [];
    }


    /**
     * This is a simplified version of the createBooking method.
     * @param User $user
     * @param DateTime $begin
     * @param DateTime $end
     * @param int $preparation_time
     * @param string $description
     * @param string $internal_comment
     * @param int $booking_type
     * @return ResourceBooking
     * @see Resource::createBooking
     */
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
        return $this->createBooking(
            $user,
            $user->id,
            [
                [
                    'begin' => $begin,
                    'end'   => $end
                ]
            ],
            null,
            0,
            null,
            $preparation_time,
            $description,
            $internal_comment,
            $booking_type
        );
    }

    /**
     * Creates bookings from a request.
     * @param User $user
     * @param ResourceRequest $request The request from which
     *     a resource booking shall be built.
     * @param int $preparation_time
     * @param string $description
     * @param string $internal_comment
     * @param int $booking_type
     * @param bool $prepend_preparation_time . If this is set to true,
     *     the preparation time will end before the start of the
     *     requested time. If $prepend_preparation_time is set to false
     *     (the default) the preparation time starts with the start of the
     *     requested time.
     * @param bool $notify_lecturers
     * @return ResourceBooking[] A list of resource bookings
     *     matching the request.
     * @throws ResourceRequestException if the request could not be marked
     *     as resolved.
     *
     * @throws ResourceUnavailableException if the resource cannot be assigned
     *     in at least one of the time ranges specified by the resource request.
     */
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
        $course_dates = $request->getAffectedDates();

        $bookings = [];
        if ($course_dates) {
            foreach ($course_dates as $course_date) {
                $booking = $this->createBooking(
                    $user,
                    $course_date->id,
                    [
                        [
                            'begin' => (
                            $prepend_preparation_time
                                ? $course_date->date - $preparation_time
                                : $course_date->date
                            ),
                            'end'   => $course_date->end_time
                        ]
                    ],
                    null,
                    0,
                    $course_date->end_time,
                    $preparation_time,
                    $description,
                    $internal_comment,
                    $booking_type
                );

                if ($booking instanceof ResourceBooking) {
                    $bookings[] = $booking;
                }
            }
        } elseif (count($request->appointments)) {
            //It is a request for multiple single dates.
            //Such requests are resolved into multiple bookings.
            foreach ($request->appointments as $appointment) {
                $begin = (
                $prepend_preparation_time
                    ? $appointment->appointment->date - $preparation_time
                    : $appointment->appointment->date
                );
                $end   = $appointment->appointment->end_time;

                $booking = $this->createBooking(
                    $user,
                    $appointment->appointment_id,
                    [
                        [
                            'begin' => $begin,
                            'end'   => $end
                        ]
                    ],
                    null,
                    0,
                    $end,
                    $preparation_time,
                    $description,
                    $internal_comment,
                    $booking_type
                );

                if ($booking instanceof ResourceBooking) {
                    $bookings[] = $booking;
                }
            }
        } else {
            //No date objects for the request.
            //It is a simple request:
            $booking = $this->createBooking(
                $user,
                $request->user->id,
                [
                    [
                        'begin' => (
                        $prepend_preparation_time
                            ? $request->begin - $preparation_time
                            : $request->begin
                        ),
                        'end'   => $request->end
                    ]
                ],
                null,
                0,
                $request->end,
                $preparation_time,
                $description,
                $internal_comment,
                $booking_type
            );

            if ($booking instanceof ResourceBooking) {
                $bookings[] = $booking;
            }
        }

        if (!$request->closeRequest($notify_lecturers)) {
            throw new ResourceRequestException(
                _('Die Anfrage konnte nicht als bearbeitet markiert werden!')
            );
        }

        return $bookings;
    }

    /**
     * A factory method for creating a ResourceBooking object
     * for this resource.
     *
     * @param User $user The user who wishes to create a resource booking.
     * @param string $range_id The ID of the user (or the Stud.IP object)
     *     which owns the ResourceBooking.
     * @param array[][] $time_ranges The time ranges for the booking.
     *     At least one time range has to be specified using unix timestamps
     *     or DateTime objects.
     *     This array has the following structure:
     *     [
     *         [
     *             'begin' => The begin timestamp or DateTime object.
     *             'end' => The end timestamp or DateTime object.
     *         ]
     *     ]
     * @param DateInterval|null $repetition_interval The repetition interval
     *     for the new booking. This must be a DateInterval object if
     *     repetitions shall be stored.
     *     Otherwise this parameter must be set to null.
     * @param int $repeat_amount The amount of repetitions.
     *     This parameter is only regarded if $repetition_interval contains
     *     a DateInterval object.
     *     In case repetitions are specified by their end date set this
     *     parameter to 0.
     * @param DateTime|string|null $repetition_end_date The end date of the
     *     repetition. This can either be an unix timestamp or a DateTime object
     *     and will only be regarded if $repetition_interval contains a
     *     DateInterval object.
     *     In case repetitions are specified by their amount set this
     *     parameter to null.
     * @param int $repetition_amount
     * @param int $preparation_time The preparation time which is needed before
     *     the real start time. This will be substracted
     *     from the begin timestamp and stored in an extra column of the
     *     resource_bookings table.
     * @param string $description An optional description for the booking.
     *     This fields was previously known as "user_free_name".
     * @param string $internal_comment An optional comment for the
     *     booking which is intended to be used internally
     *     in the room and resource administration staff.
     * @param int $booking_type The booking type.
     *     0 = normal booking
     *     1 = reservation
     *     2 = lock booking
     * @param bool $force_booking If this parameter is set to true,
     *     overlapping bookings are removed before storing this booking.
     *
     * @return ResourceBooking object.
     * @throws InvalidArgumentException If no time ranges are specified
     *     or if there is an error regarding the time ranges.
     * @throws ResourceBookingRangeException If $range_id is not set.
     * @throws ResourceBookingOverlapException If the booking overlaps
     *     with another booking or a resource lock.
     * @throws ResourcePermissionException If the specified user does not
     *     have sufficient permissions to create a resource booking.
     * @throws ResourceBookingException If the repetition interval
     *     is invalid or if the resource booking cannot be stored.
     *
     */
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
        if (!is_array($time_ranges)) {
            throw new InvalidArgumentException(
                _('Es wurden keine Zeitbereiche für die Buchung angegeben!')
            );
        }

        $booking_begin = null;
        $booking_end   = null;

        //Check if each entry of the $time_intervals array is in the right
        //format and if it contains either timestamps or DateTime objects.
        //After that the time ranges are checked for validity (begin > end)
        //and if there are locks or bookings in one of the time ranges.
        //Furthermore all reservations that are affected by this booking
        //are collected so that the persons who made the reservations
        //can be informed about the new booking.
        $affected_reservations = [];
        foreach ($time_ranges as $index => $time_range) {
            $begin = $time_range['begin'];
            $end   = $time_range['end'];

            if ($begin === null || $end === null) {
                throw new InvalidArgumentException(
                    _('Mindestens eines der Zeitintervalls ist im falschen Format!')
                );
            }

            if (!($begin instanceof DateTime)) {
                $b = new DateTime();
                $b->setTimestamp($begin);
                $begin = $b;
            }
            if (!($end instanceof DateTime)) {
                $e = new DateTime();
                $e->setTimestamp($end);
                $end = $e;
            }

            $real_begin = clone $begin;
            if ($preparation_time > 0) {
                $real_begin = $real_begin->sub(
                    new DateInterval('PT' . $preparation_time . 'S')
                );
            }

            if ($real_begin > $end) {
                throw new InvalidArgumentException(
                    _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
                );
            }

            $duration     = $end->getTimestamp() - $begin->getTimestamp();
            $min_duration = Config::get()->RESOURCES_MIN_BOOKING_TIME;
            if ($duration < ($min_duration * 60)) {
                throw new InvalidArgumentException(
                    sprintf(
                        _('Die minimale Buchungsdauer von %1$d Minuten wurde unterschritten!'),
                        $min_duration
                    )
                );
            }

            if ($index == array_keys($time_ranges)[0]) {
                $booking_begin = clone $begin;
                $booking_end   = clone $end;
            }

            if ($repetition_interval instanceof DateInterval) {
                //We must calculate the end of the repetition interval
                //by using $repetition_amount or $repetition_end_date.
                $repetition_end = null;
                if ($repetition_end_date instanceof DateTime) {
                    $repetition_end = $repetition_end_date;
                } elseif ($repetition_end_date) {
                    //convert $repetition_end_date to a DateTime object:
                    $red = new DateTime();
                    $red->setTimestamp($repetition_end_date);
                    $repetition_end = $red;
                } else {
                    //$repetition_end_date is not set: Use $repetition_amount.
                    //Add the repetition interval $repetition_amount times
                    //to the $real_begin DateTime object to get the end date
                    //of the repetition:
                    $repetition = clone $real_begin;
                    for ($i = 0; $i < $repetition_amount; $i++) {
                        $repetition = $repetition->add($repetition_interval);
                    }
                    $repetition_end = $repetition;
                }

                $current_date = clone $real_begin;

                //Check for each repetition if the resource is available
                //or locked:
                while ($current_date <= $repetition_end) {
                    $current_begin = clone $current_date;
                    $current_end   = clone $current_date;
                    $current_end->setTime(
                        intval($end->format('H')),
                        intval($end->format('i')),
                        intval($end->format('s'))
                    );

                    if ($current_begin < $current_end) {
                        $affected_reservations = array_merge(
                            ResourceBooking::findByResourceAndTimeRanges(
                                $this,
                                [
                                    [
                                        'begin' => $current_begin->getTimestamp(),
                                        'end'   => $current_end->getTimestamp(),
                                    ]
                                ],
                                [1, 3]
                            ),
                            $affected_reservations
                        );
                    }

                    $current_date = $current_date->add($repetition_interval);
                }
            } else {
                $affected_reservations = array_merge(
                    ResourceBooking::findByResourceAndTimeRanges(
                        $this,
                        [
                            [
                                'begin' => $real_begin->getTimestamp(),
                                'end'   => $end->getTimestamp(),
                            ]
                        ],
                        [1, 3]
                    ),
                    $affected_reservations
                );
            }
        }

        $booking                  = new ResourceBooking();
        $booking->resource_id     = $this->id;
        $booking->booking_user_id = $user->id;
        $booking->range_id        = $range_id;
        $booking->description     = $description;
        $booking->begin           = $booking_begin->getTimestamp();
        $booking->end             = $booking_end->getTimestamp();

        if ($repetition_interval instanceof DateInterval) {
            if ($repetition_end_date) {
                if ($repetition_end_date instanceof DateTime) {
                    $booking->repeat_end = $repetition_end_date->getTimestamp();
                } else {
                    $booking->repeat_end = $repetition_end_date;
                }
            } elseif ($repetition_amount) {
                $booking->repeat_quantity = $repetition_amount;
            }

            $booking->repetition_interval = $repetition_interval->format('P%YY%MM%DD');
        }

        if ($preparation_time) {
            $booking->preparation_time = $preparation_time;
        } else {
            $booking->preparation_time = '0';
        }
        $booking->internal_comment = $internal_comment;
        $booking->booking_type     = (int)$booking_type;

        //We can finally store the new booking.

        try {
            $booking->store($force_booking);
        } catch (ResourceBookingOverlapException $e) {
            if ($begin->format('Ymd') == $end->format('Ymd')) {
                throw new ResourceBookingException(
                    sprintf(
                        _('%1$s: Die Buchung vom %2$s bis %3$s konnte wegen Überlappungen nicht gespeichert werden: %4$s'),
                        $this->getFullName(),
                        $begin->format('d.m.Y H:i'),
                        $end->format('H:i'),
                        $e->getMessage()
                    )
                );
            } else {
                throw new ResourceBookingException(
                    sprintf(
                        _('%1$s: Die Buchung vom %2$s bis %3$s konnte wegen Überlappungen nicht gespeichert werden: %4$s'),
                        $this->getFullName(),
                        $begin->format('d.m.Y H:i'),
                        $end->format('d.m.Y H:i'),
                        $e->getMessage()
                    )
                );
            }
        } catch (Exception $e) {
            if ($begin->format('Ymd') == $end->format('Ymd')) {
                throw new ResourceBookingException(
                    sprintf(
                        _('%1$s: Die Buchung vom %2$s bis %3$s konnte aus folgendem Grund nicht gespeichert werden: %4$s'),
                        $this->getFullName(),
                        $begin->format('d.m.Y H:i'),
                        $end->format('H:i'),
                        $e->getMessage()
                    )
                );
            } else {
                throw new ResourceBookingException(
                    sprintf(
                        _('%1$s: Die Buchung vom %2$s bis %3$s konnte aus folgendem Grund nicht gespeichert werden: %4$s'),
                        $this->getFullName(),
                        $begin->format('d.m.Y H:i'),
                        $end->format('d.m.Y H:i'),
                        $e->getMessage()
                    )
                );
            }
        }

        return $booking;
    }

    /**
     * This method creates a simple request for this resource.
     * A simple request is not bound to a date, metadate
     * or course object and its time ranges. Instead the time
     * range is specified directly.
     * Note that simple resource requests do not support recurrence.
     *
     * @param User $user The user who wishes to create a simple request.
     * @param DateTime $begin The begin timestamp of the request.
     * @param DateTime $end The end timestamp of the request.
     * @param string $comment A comment for the resource request.
     * @param int $preparation_time The requested preparation time before
     *     the begin of the requested time range. This parameter must be
     *     specified in seconds. Only positive values are accepted.
     *
     * @return ResourceRequest A resource request object.
     * @throws AccessDeniedException If the user is not permitted
     *     to request this resource.
     * @throws InvalidArgumentException If the the timestamps provided by
     *     $begin and $end are invalid or if $begin is greater than or equal
     *     to $end which results in an invalid time range.
     * @throws ResourceUnavailableException If the resource is not available
     *     in the selected time range.
     * @throws ResourceRequestException If the resource request
     *     cannot be stored.
     *
     */
    public function createSimpleRequest(
        User $user,
        DateTime $begin,
        DateTime $end,
        $comment = '',
        $preparation_time = 0
    )
    {
        //All users are permitted to create a request,
        //if the resource is requestable.

        if (!$this->requestable) {
            throw new InvalidArgumentException(
                _('Diese Ressource kann nicht angefragt werden!')
            );
        }

        if ($begin > $end) {
            throw new InvalidArgumentException(
                _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
            );
        } elseif ($begin == $end) {
            throw new InvalidArgumentException(
                _('Startzeitpunkt und Endzeitpunkt sind identisch!')
            );
        }

        if (!$this->isAvailable($begin, $end)) {
            throw new ResourceUnavailableException(
                sprintf(
                    _('Die Ressource %1$s ist im Zeitraum von %2$s bis %3$s nicht verfügbar!'),
                    $this->name,
                    $begin->format('d.m.Y H:i'),
                    $end->format('d.m.Y H:i')
                )
            );
        }

        $request              = new ResourceRequest();
        $request->resource_id = $this->id;
        $request->category_id = $this->category_id;
        $request->user_id     = $user->id;

        $request->begin            = $begin->getTimestamp();
        $request->end              = $end->getTimestamp();
        $request->preparation_time = (
        $preparation_time > 0
            ? $preparation_time
            : 0
        );

        $request->closed  = '0';
        $request->comment = $comment;

        if (!$request->store()) {
            throw new ResourceRequestException(
                sprintf(
                    _('Die Anfrage zur Ressource %s konnte nicht gespeichert werden!'),
                    $this->name
                )
            );
        }

        return $request;
    }


    /**
     * This method creates a resource request for this resource.
     *
     * @param User $user The user who wishes to create a request.
     * @param string|array $date_range_ids One or more IDs of Stud.IP objects
     *     which can provide at least one time range.
     *     Objects which fulfill this requirement are
     *     course dates (CourseDate objects),
     *     cycle dates (SeminarCycleDate objects)
     *     and courses (Course objects).
     *     If only one ID is provided it can be passed as string.
     *     If multiple IDs are provided they have to be passed as array.
     * @param string $comment A comment for the resource request.
     * @param mixed[] $properties The wishable properties
     *     for the resource request. The format of the array is as follows:
     *     [
     *         'property name' => 'property state'
     *     ]
     * @param int $preparation_time The requested preparation time before
     *     the begin of the requested time range. This parameter must be
     *     specified in seconds. Only positive values are accepted.
     *
     * @return ResourceRequest A resource request object.
     * @throws InvalidArgumentException If $date_range_id is not set.
     *     or no object which can provide at least one time range
     *     can be found with the specified ID.
     * @throws ResourceNoTimeRangeException If no time range can be found
     *     by looking at the object, specified by its ID in $date_range_id.
     * @throws ResourceUnavailableException If the resource is not available
     *     in the selected time range.
     * @throws ResourceRequestException If the resource request
     *     cannot be stored.
     *
     */
    public function createRequest(
        User $user,
        $date_range_ids = null,
        $comment = '',
        $properties = [],
        $preparation_time = 0
    )
    {
        if (!$date_range_ids) {
            throw new InvalidArgumentException(
                _('Es wurde keine ID eines Objektes angegeben, welches Zeiträume für eine Ressourcenanfrage liefern kann!')
            );
        }

        if (!$this->requestable) {
            throw new InvalidArgumentException(
                _('Diese Ressource kann nicht angefragt werden!')
            );
        }

        //We must get the date ranges by looking at $date_range_id
        //and the object which lies behind that ID.

        if (!is_array($date_range_ids)) {
            $date_range_ids = [$date_range_ids];
        }

        $time_ranges = [];
        foreach ($date_range_ids as $date_range_id) {
            $time_ranges = array_merge(
                $time_ranges,
                ResourceManager::getTimeRangesFromRangeId(
                    $date_range_id
                )
            );
        }

        if (!$time_ranges) {
            //We couldn't find any time range.
            throw new ResourceNoTimeRangeException(
                sprintf(
                    _('Es konnte kein Zeitbereich für die Anfrage der Ressource %s gefunden werden.'),
                    $this->name
                )
            );
        }

        //Default resource request handling:
        //Check if the resource is available in all requested time ranges.

        foreach ($time_ranges as $time_range) {
            if (!$this->isAvailable($time_range[0], $time_range[1])) {
                throw new ResourceUnavailableException(
                    sprintf(
                        _('Die Ressource %1$s ist im Zeitraum vom %2$s bis %3$s nicht verfügbar!'),
                        $this->name,
                        $time_range[0]->format('d.m.Y H:i'),
                        $time_range[1]->format('d.m.Y H:i')
                    )
                );
            }
        }

        //We must check, if all the properties exist:
        if ($properties and is_array($properties)) {
            foreach ($properties as $property_name => $property_state) {
                $property_object = ResourcePropertyDefinition::findByName(
                    $property_name
                );

                if (!$property_object) {
                    throw new ResourcePropertyException(
                        sprintf(
                            _('Die Ressourceneigenschaft %s ist nicht definiert!'),
                            $property_name
                        )
                    );
                } elseif (count($property_object) > 1) {
                    throw new ResourcePropertyException(
                        sprintf(
                            _('Es gibt mehrere Ressourceneigenschaften mit dem Namen %s!'),
                            $property_name
                        )
                    );
                }

                //$property_object is an array of ResourcePropertyDefinition objects:
                $property_data[] = [
                    'object' => $property_object[0],
                    'state'  => $property_state
                ];
            }
        }

        $request                   = new ResourceRequest();
        $request->resource_id      = $this->id;
        $request->category_id      = $this->category_id;
        $request->user_id          = $user->id;
        $request->comment          = $comment;
        $request->preparation_time = (
        $preparation_time > 0
            ? $preparation_time
            : 0
        );
        $request->closed           = '0';

        //Resolve the date range ID and set the
        //appropriate field in the request object:
        if (count($date_range_ids) <= 1) {
            $course_date = CourseDate::find($date_range_ids[0]);
            if ($course_date) {
                $request->termin_id = $course_date->id;
            } else {
                $cycle_date = SeminarCycleDate::find($date_range_ids[0]);
                if ($cycle_date) {
                    $request->metadate_id = $cycle_date->id;
                } else {
                    $course = Course::find($date_range_ids[0]);
                    if ($course) {
                        $request->course_id = $course->id;
                    }
                }
            }

            if (!$request->store()) {
                throw new ResourceRequestException(
                    sprintf(
                        _('Die Anfrage zur Ressource %s konnte nicht gespeichert werden!'),
                        $this->name
                    )
                );
            }
        } else {
            if (!$request->store()) {
                throw new ResourceRequestException(
                    sprintf(
                        _('Die Anfrage zur Ressource %s konnte nicht gespeichert werden!'),
                        $this->name
                    )
                );
            }

            //More than one entry:
            //We must use ResourceBookingAppointment objects.
            foreach ($date_range_ids as $date_range_id) {
                $appointment_id = null;
                $course_date    = CourseDate::find($date_range_id);
                if ($course_date) {
                    $appointment_id = $course_date->id;
                } else {
                    $cycle_date = SeminarCycleDate::find($date_range_id);
                    if ($cycle_date) {
                        $appointment_id = $cycle_date->id;
                    } else {
                        $course = Course::find($date_range_id);
                        if ($course) {
                            $appointment_id = $course->id;
                        }
                    }
                }

                if ($appointment_id) {
                    $rra                 = new ResourceRequestAppointment();
                    $rra->request_id     = $request->id;
                    $rra->appointment_id = $appointment_id;
                    if (!$rra->store()) {
                        throw new ResourceRequestException(
                            _('Die Terminzuordnungen zur Anfrage konnten nicht gespeichert werden!')
                        );
                    }
                }
            }
        }

        //The request has been created: Now we need to link the properties:
        if(!empty($property_data)) {
            foreach ($property_data as $property) {
                $rrp              = new ResourceRequestProperty();
                $rrp->request_id  = $request->id;
                $rrp->property_id = $property['object']->id;
                $rrp->state       = intval($property['state']);
                if (!$rrp->store()) {
                    throw new InvalidResourceRequestException(
                        sprintf(
                            _('%1$s: Die Eigenschaft %2$s zur Anfrage konnte nicht gespeichert werden!'),
                            $this->getFullName(),
                            $property['object']->name
                        )
                    );
                }
            }
        }
        return $request;
    }

    /**
     * Creates a lock booking for this resource.
     *
     * @param User $user The user who wishes to create a lock booking.
     * @param DateTime $begin The begin of the lock time range.
     * @param DateTime $end The end of the lock time range.
     * @param string $internal_comment An optional comment for the
     *     lock booking which is intended to be used internally
     *     in the room and resource administration staff.
     *
     * @return ResourceBooking A ResourceBooking object.
     * @throws ResourceUnavailableException If a lock booking already
     *     exists in the specified time range.
     *
     * @throws AccessDeniedException If the user does not have sufficient
     *     permissions to lock this resource.
     */
    public function createLock(
        User $user,
        DateTime $begin,
        DateTime $end,
        $internal_comment = ''
    )
    {
        if (!$this->userHasPermission($user, 'admin')) {
            throw new AccessDeniedException(
                sprintf(
                    _('%s: Unzureichende Berechtigungen zum Erstellen einer Sperrbuchung!'),
                    $this->getFullName()
                )
            );
        }

        if ($this->isLocked($begin, $end)) {
            throw new ResourceUnavailableException(
                sprintf(
                    _('%1$s: Im Zeitbereich von %2$s bis %3$s gibt es bereits Sperrbuchungen!'),
                    $this->getFullName(),
                    $begin->format('d.m.Y H:i'),
                    $end->format('d.m.Y H:i')
                )
            );
        }

        $lock                   = new ResourceBooking();
        $lock->booking_type     = '2';
        $lock->range_id         = $user->id;
        $lock->resource_id      = $this->id;
        $lock->begin            = $begin->getTimestamp();
        $lock->end              = $end->getTimestamp();
        $lock->internal_comment = $internal_comment;

        if (!$lock->store()) {
            throw new ResourceBookingException(
                sprintf(
                    _('%1$s: Fehler beim Speichern der Sperrbuchung für den Zeitbereich von %2$s bis %3$s!'),
                    $begin->format('d.m.Y H:i'),
                    $end->format('d.m.Y H:i')
                )
            );
        }

        return $lock;
    }

    /**
     * Retrieves the properties grouped by their property groups
     * and in the order specified in that group.
     *
     * @param string[] excluded_properties An array with the names
     *     of the properties that shall be excluded from the result set.
     *
     * @return array An array with the group names as keys and the properties
     *     in the second array dimension. The structure of the array
     *     is as follows:
     *     [
     *          group1 name => [
     *              property1,
     *              property2,
     *              ...
     *          ],
     *          group2 name => [
     *              ...
     *          ]
     *     ]
     */
    public function getGroupedProperties($excluded_properties = [])
    {
        if (is_array($excluded_properties) && count($excluded_properties)) {
            $properties = ResourceProperty::findBySql(
                "INNER JOIN resource_property_definitions rpd
                USING (property_id)
                LEFT JOIN resource_property_groups rpg
                ON rpd.property_group_id = rpg.id
                WHERE
                resource_properties.resource_id = :resource_id
                AND
                rpd.name NOT IN ( :excluded_properties )
                ORDER BY
                rpg.position ASC, rpg.name ASC,
                rpd.property_group_pos ASC, rpd.name ASC",
                [
                    'resource_id'         => $this->id,
                    'excluded_properties' => $excluded_properties
                ]
            );
        } else {
            $properties = ResourceProperty::findBySql(
                "INNER JOIN resource_property_definitions rpd
                USING (property_id)
                LEFT JOIN resource_property_groups rpg
                ON rpd.property_group_id = rpg.id
                WHERE
                resource_properties.resource_id = :resource_id
                ORDER BY
                rpg.position ASC, rpg.name ASC,
                rpd.property_group_pos ASC, rpd.name ASC",
                [
                    'resource_id' => $this->id
                ]
            );
        }

        if (!$properties) {
            return [];
        }

        $property_groups = [];
        foreach ($properties as $property) {
            if (!$property->state) {
                continue;
            }
            $group_name = '';
            if ($property->definition->group->name) {
                $group_name = $property->definition->group->name;
            }
            if (!is_array($property_groups[$group_name])) {
                $property_groups[$group_name] = [];
            }
            $property_groups[$group_name][] = $property;
        }

        return $property_groups;
    }


    /**
     * Determines wheter this resource has a property
     * with the specified name.
     *
     * @param string $name The name of the resource property.
     *
     * @return bool True, if this resource has a property with
     *     the specified name, false otherwise.
     */
    public function propertyExists($name = '')
    {
        if (!$name) {
            return false;
        }

        $db = DBManager::get();

        $exists_stmt = $db->prepare(
            "SELECT TRUE FROM resource_properties
            INNER JOIN resource_property_definitions rpd
            ON resource_properties.property_id = rpd.property_id
            WHERE resource_properties.resource_id = :resource_id
                AND rpd.name = :name");

        $exists_stmt->execute(
            [
                'resource_id' => $this->id,
                'name'        => $name
            ]
        );

        $exists = $exists_stmt->fetchColumn(0);

        return (bool)$exists;
    }

    /**
     * Retrieves a ResourceProperty object for a property of this resource
     * which has the specified name. If the property has not been set for this
     * resource, but is defined for this resource's category, a new
     * ResourceProperty object will be created, stored and returned.
     *
     * @param string $name The name of the resource property.
     *
     * @return ResourceProperty|null Either a ResourceProperty object for
     *     the resource property matching the specified name or null,
     *     if no resource property with the specified name can be found.
     * @throws InvalidResourceCategoryException If this resource category
     * doesn't match the category of the resource object.
     *
     */
    public function getPropertyObject(string $name)
    {
        if (!$this->propertyExists($name)) {
            //A property with the name $name does not exist for this
            //resource object. If it is a defined property
            //we can still try to create it:

            if ($this->category->hasProperty($name)) {
                $property = $this->category->createDefinedResourceProperty(
                    $this,
                    $name
                );

                $property->store();
                return $property;
            } else {
                return null;
            }
        }

        return ResourceProperty::findOneBySql(
            "INNER JOIN resource_property_definitions rpd
                ON resource_properties.property_id = rpd.property_id
            WHERE resource_properties.resource_id = :resource_id
                AND rpd.name = :name",
            [
                'resource_id' => $this->id,
                'name'        => $name
            ]
        );
    }

    /**
     * Returns all info-label properties
     *
     * @return SimpleCollection
     */
    public function getInfolabelPrperties()
    {
        return SimpleCollection::createFromArray(
            ResourceProperty::findBySQL('INNER JOIN `resource_property_definitions` USING (`property_id`) 
                WHERE `info_label` = 1 AND `state` != "" AND `resource_id` = ?', [$this->id]
            )
        );
    }

    /**
     * Returns the state of the property specified by $name.
     * If the property has not been set for this resource, but is defined
     * for this resource's category, a new ResourceProperty object
     * will be created, stored and its state will be returned.
     *
     * @param string $name The name of the resource property.
     *
     * @return string|null The state of the specified property or null
     *     if the propery can't be found.
     */
    public function getProperty(string $name)
    {
        if (!$this->propertyExists($name)) {
            //A property with the name $name does not exist for this
            //resource object. If it is a defined property
            //we can still try to create it:

            if ($this->category->hasProperty($name)) {
                $property = $this->category->createDefinedResourceProperty(
                    $this,
                    $name,
                    ''
                );

                $property->store();
                return $property->state;
            } else {
                return null;
            }
        }

        $db = DBManager::get();

        $value_stmt = $db->prepare(
            "SELECT resource_properties.state FROM resource_properties
            INNER JOIN resource_property_definitions rpd
                ON resource_properties.property_id = rpd.property_id
            WHERE resource_properties.resource_id = :resource_id
                AND rpd.name = :name");

        $value_stmt->execute(
            [
                'resource_id' => $this->id,
                'name'        => $name
            ]
        );

        $value = $value_stmt->fetchColumn(0);

        if (!$value) {
            return null;
        }

        return $value;
    }

    /**
     * Retrieves an object by the state of a property of this resource,
     * specified by the property's name.
     * This method is useful for properties of type user, institute
     * or fileref. Those properties store IDs of User, Institute
     * or FileRef objects. Therefore the IDs can be resolved directly
     * to get the corresponding User, Institute or FileRef object directly.
     *
     * @param string $name The name of the resource property.
     *
     * @return SimpleORMap|null A SimpleORMap-based object or null,
     *     if no such object can be retrieved from the property's state.
     */
    public function getPropertyRelatedObject(string $name)
    {
        //Get the property state first:
        $property = $this->getPropertyObject($name);

        //Now we return the object which is referenced by the property's state:

        if ($property) {
            switch ($property->definition->type) {
                case 'user':
                    return User::find($property->state);
                case 'institute':
                    return Institute::find($property->state);
                case 'fileref' :
                    return FileRef::find($property->state);
                default:
                    //For all other property types where we cannot create an object
                    //we return the raw state value:
                    return $property->state;
            }
        }
        return null;
    }

    /**
     * Sets a specified property of this resource to the specified state.
     * If the property has not been set for this resource, but is defined
     * for this resource's category, a new ResourceProperty object
     * will be created, stored and its state will be returned.
     *
     * @param string $name The name of the resource property.
     * @param mixed $state The state of the resource property.
     * @param User|null $user The user who wishes to set the property.
     *
     * @return True, if the property state could be set, false otherwise.
     */
    public function setProperty(string $name, $state = '', $user = null)
    {
        if (!($user instanceof User)) {
            $user = User::findCurrent();
            if (!$user) {
                //We cannot continue without a user object!
                return false;
            }
        }

        //Get the minimum permission level required for modifying the property:

        if (!$this->userHasPermission($user, 'admin')) {
            throw new AccessDeniedException(
                sprintf(
                    _('Unzureichende Berechtigungen zum Ändern der Ressource %s!'),
                    $this->name
                )
            );
        }
        if (!$this->category->userHasPropertyWritePermissions($name, $user, $this)) {
            throw new AccessDeniedException(
                sprintf(
                    _('Unzureichende Berechtigungen zum Ändern der Eigenschaft %s!'),
                    $name
                )
            );
        }

        if (!$this->propertyExists($name)) {
            //A property with the name $name does not exist for this
            //resource object. If it is a defined property
            //we can still try to create it:

            if ($this->category->hasProperty($name)) {
                $property = $this->category->createDefinedResourceProperty(
                    $this,
                    $name,
                    $state
                );
                return $property->store();
            } else {
                return false;
            }
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
     * Sets the properties (specified by their names) to the specified values.
     *
     * @param array $properties The properties array in the format "key-value".
     *     The array keys must contain the property name while the
     *     items of the array contain the values.
     *     Example:
     *     ['bar' => 'foo']: Sets the value 'foo' for the property
     *     with the name 'bar'.
     *
     * @param User|null $user The user who wishes to set the properties.
     *     If this is left empty, the current user will be used.
     *
     * @return array If properties cannot be set, their names (as key) and the
     *     error messages (if any) are returned.
     *     The array has the following structure:
     *     [
     *         (property name) => (error message or empty string)
     *     ]
     */
    public function setPropertiesByName(array $properties, User $user)
    {
        $failed_properties = [];

        if (!($user instanceof User)) {
            $user = User::findCurrent();
            if (!$user) {
                //No property can be set.
                foreach ($properties as $name => $state) {
                    $failed_properties[$name] = '';
                }
                return $failed_properties;
            }
        }

        foreach ($properties as $name => $state) {
            try {
                $this->setProperty($name, $state, $user);
            } catch (Exception $e) {
                $this->failed_properties[$name] = $e->getMessage();
            }
        }

        return $failed_properties;
    }

    /**
     * Sets the properties (specified by their IDs) to the specified values.
     *
     * @param array $properties The properties array in the format "key-value".
     *     The array keys must contain the property-ID while the
     *     items of the array contain the values.
     *     Example:
     *     ['1' => 'foo']: Sets the value 'foo' for the property
     *     with the ID '1'.
     *
     * @param User|null $user The user who wishes to set the properties.
     *     If this is left empty, the current user will be used.
     *
     * @return array If properties cannot be set, their ids (as key) and the
     *     error messages (if any) are returned.
     *     The array has the following structure:
     *     [
     *         (property-ID) => (error message or empty string)
     *     ]
     */
    public function setPropertiesById(array $properties, User $user = null)
    {
        $failed_properties = [];

        if (!($user instanceof User)) {
            $user = User::findCurrent();
            if (!$user) {
                //No property can be set.
                foreach ($properties as $id => $state) {
                    $failed_properties[$id] = '';
                }
                return $failed_properties;
            }
        }

        foreach ($properties as $id => $state) {
            $property = ResourcePropertyDefinition::find($id);
            if (!$property) {
                //Invalid property:
                $this->failed_properties[$id] =
                    _('Die Eigenschaft wurde nicht gefunden!');
                continue;
            }
            try {
                $this->setProperty($property->name, $state, $user);
            } catch (Exception $e) {
                $failed_properties[$id] = $e->getMessage();
            }
        }

        return $failed_properties;
    }

    /**
     * Determines if the specified user has sufficient permissions to edit
     * the property specified by its name.
     *
     * @param string $name The name of the resource property.
     * @param user $user The user whose edit permissions shall be checked.
     *
     * @return bool True, if the user has edit permissions for the property,
     *     false otherwise.
     */
    public function isPropertyEditable(string $name, User $user)
    {
        return $this->category->userHasPropertyWritePermissions($name, $user, $this);
    }

    /**
     * Sets the state of a property by its definition_id rather than its name.
     *
     * @param string $property_definition_id The definition-ID of the property.
     * @param string $state The state of the property.
     *
     * @return bool True, if the property state can be stored, false otherwise.
     * @throws ResourcePropertyStateException If the provided state is invalid
     *     for the specified resource property.
     *
     */
    public function setPropertyByDefinitionId($property_definition_id = null, $state = null)
    {
        if (!$property_definition_id and !$state) {
            return false;
        }

        //Get property definition:
        $definition = ResourcePropertyDefinition::find($property_definition_id);
        if (!$definition) {
            return false;
        }

        //Check if the state matches the property definition's rules:
        $definition->validateState($state);

        //Check if the property for this resource already exists.
        //If so, update it. Otherwise create it.

        $property = ResourceProperty::findOneBySql(
            '(property_id = :property_id) AND (resource_id = :resource_id)',
            [
                'property_id' => $definition->id,
                'resource_id' => $this->id
            ]
        );

        if (!$property) {
            $property              = new ResourceProperty();
            $property->property_id = $definition->id;
            $property->resource_id = $this->id;
        }

        $property->state = $state;
        return $property->store();
    }

    /**
     * Sets the property state by specifying an SimpleORMap object.
     * This method is meant for resource properties of type user,
     * institute or fileref.
     *
     * @param string $name The name of the resource property.
     * @param SimpleORMap $object The object for the resource property.
     *
     * @return bool True, if the property has been saved, false otherwise.
     */
    public function setPropertyRelatedObject(string $name, SimpleORMap $object)
    {
        //Get the property state first:
        $property = $this->getPropertyObject($name);

        if (!$property) {
            return false;
        }

        //Now we return the object which is referenced by the property's state:

        switch ($property->definition->type) {
            case 'user':
                if (!($object instanceof User)) {
                    throw new ResourcePropertyException(
                        _("Eine Ressourceneigenschaft vom Typ 'user' benötigt ein Nutzer-Objekt zur Wertzuweisung!")
                    );
                }
            break;
            case 'institute':
                if (!($object instanceof Institute)) {
                    throw new ResourcePropertyException(
                        _("Eine Ressourceneigenschaft vom Typ 'institute' benötigt ein Institut-Objekt zur Wertzuweisung!")
                    );
                }
            break;
            case 'fileref':
                if (!($object instanceof FileRef)) {
                    throw new ResourcePropertyException(
                        _("Eine Ressourceneigenschaft vom Typ 'fileref' benötigt ein FileRef-Objekt zur Wertzuweisung!")
                    );
                }
            break;
            default:
            break;
        }

        //When no exception is thrown above we can set the object's ID
        //as the property's state:
        $property->state = $object->id;

        return $property->store();
    }

    /**
     * Deletes a property for a resource.
     *
     * @param string $name The name of the property to be deleted.
     *
     * @param User $user The user who wishes to delete the property.
     * @return number
     */
    public function deleteProperty(string $name, User $user)
    {
        //Get the user object and the minimum permission level
        //required for modifying the property:

        if (!$this->userHasPermission($user, 'admin')) {
            throw new AccessDeniedException(
                sprintf(
                    _('Unzureichende Berechtigungen zum Ändern der Ressource %s!'),
                    $this->name
                )
            );
        }
        if (!$this->category->userHasPropertyWritePermissions($name, $user)) {
            throw new AccessDeniedException(
                sprintf(
                    _('Unzureichende Berechtigungen zum Löschen der Eigenschaft %s!'),
                    $name
                )
            );
        }

        return ResourceProperty::deleteBySql(
            "INNER JOIN resource_property_definitions rpd
            ON resource_properties.property_id = rpd.property_id
            WHERE
            rpd.name = :name AND resource_properties.resource_id = :resource_id",
            [
                'name'        => $name,
                'resource_id' => $this->id
            ]
        );
    }

    /**
     * Returns the path for the resource's image.
     * If the resource has no image the path for a general
     * resource icon will be returned.
     *
     * Classes derived from the Resource class should only re-implement
     * this method if they have an alternative storage method for
     * resource pictures than the Stud.IP file system.
     *
     * @return string The URL to the resource picture.
     */
    public function getPictureUrl()
    {
        return '';
    }

    /**
     * Returns the default picture for the resource class.
     *
     * Classes derived from Resource should re-implement this method
     * if they want to get a different default picture than the resource icon.
     * The call to getPictureUrl will call the getDefaultPictureUrl method
     * from the derived class.
     *
     * @return string The URL to the picture.
     */
    public function getDefaultPictureUrl()
    {
        return $this->getIcon()->asImagePath();
    }

    /**
     * Returns the Icon for the resource class.
     *
     * Classes derived from Resource should re-implement this method
     * if they want to get a different icon than the resource icon.
     * @param string $role
     * @return Icon The icon for the resource.
     */
    public function getIcon($role = Icon::ROLE_INFO)
    {
        return Icon::create('resources', $role);
    }

    /**
     * Returns all properties in a two-dimensional array with the following
     * property data inside of the second dimension:
     * [
     *     'name' => (the property's name)
     *     'display_name' => (the display name of the property)
     *     'type' => (the property's type)
     *     'state' => (the property's state)
     *     'requestable' => (if the property is requestable or not (true or false))
     * ]
     *
     * @param bool $only_requestable_properties If only requestable properties
     *     shall be returned set this to true. If all properties shall be
     *     returned, set this to false.
     *
     * @return array[] A two-dimensional array containing property data.
     */
    public function getPropertyArray($only_requestable_properties = false)
    {
        $property_array = [];

        if ($this->properties) {
            foreach ($this->properties as $property) {
                if ($only_requestable_properties) {
                    $category_property = ResourceCategoryProperty::findByNameAndCategoryId(
                        $property->name,
                        $this->category_id
                    );

                    if ($category_property) {
                        if ($category_property->requestable) {
                            $property_array[] = [
                                'name'         => $property->name,
                                'display_name' => $property->display_name,
                                'type'         => $property->type,
                                'state'        => $property->state,
                                'requestable'  => $property->isRequestable()
                            ];
                        }
                    }
                } else {
                    $property_array[] = [
                        'name'         => $property->name,
                        'display_name' => $property->display_name,
                        'type'         => $property->type,
                        'state'        => $property->state,
                        'requestable'  => $property->isRequestable()
                    ];
                }
            }
        }
        return $property_array;
    }

    /**
     * Shortcut method for ResourceBooking::countByResourceAndTimeRanges.
     * Determines whether normal resource bookings exist
     * in the specified time range.
     *
     * @param DateTime $begin Time range start timestamp.
     *
     * @param DateTime $end Time range end timestamp.
     *
     * @param array $excluded_booking_ids The IDs of bookings that shall
     *     be excluded from the determination of the "assigned" status.
     *
     * @return bool True, if the resource is assigned in the specified
     *     time range, false otherwise.
     */
    public function isAssigned(
        DateTime $begin,
        DateTime $end,
        $excluded_booking_ids = []
    )
    {
        return ResourceBooking::countByResourceAndTimeRanges(
                $this,
                [
                    [
                        'begin' => $begin->getTimestamp(),
                        'end'   => $end->getTimestamp()
                    ]
                ],
                [0],
                $excluded_booking_ids
            ) > 0;
    }

    /**
     * Shortcut method for ResourceBooking::countByResourceAndTimeRanges.
     * Determines whether resource reservations exist
     * in the specified time range.
     *
     * @param DateTime $begin Time range start timestamp.
     *
     * @param DateTime $end Time range end timestamp.
     *
     * @param array $excluded_reservation_ids The IDs of reservation bookings that shall
     *     be excluded from the determination of the "reserved" status.
     *
     * @return bool True, if the resource is reserved in the specified
     *     time range, false otherwise.
     */
    public function isReserved(
        DateTime $begin,
        DateTime $end,
        $excluded_reservation_ids = []
    )
    {
        //One second is added to the begin timestamp to avoid
        //getting "false" overlaps where another booking ends on exactly
        //the begin timestamp.
        return ResourceBooking::countByResourceAndTimeRanges(
                $this,
                [
                    [
                        'begin' => $begin->getTimestamp(),
                        'end'   => $end->getTimestamp()
                    ]
                ],
                [1, 3],
                $excluded_reservation_ids
            ) > 0;
    }

    /**
     * Shortcut method for ResourceBooking::countByResourceAndTimeRanges.
     * Determines whether resource locks exist
     * in the specified time range.
     *
     * @param DateTime $begin Time range start timestamp.
     *
     * @param DateTime $end Time range end timestamp.
     *
     * @param array $excluded_lock_ids The IDs of lock bookings that shall
     *     be excluded from the determination of the "locked" status.
     *
     * @return bool True, if the resource is locked in the specified
     *     time range, false otherwise.
     */
    public function isLocked(
        DateTime $begin,
        DateTime $end,
        $excluded_lock_ids = []
    )
    {
        //One second is added to the begin timestamp to avoid
        //getting "false" overlaps where another booking ends on exactly
        //the begin timestamp.
        return ResourceBooking::countByResourceAndTimeRanges(
                $this,
                [
                    [
                        'begin' => $begin->getTimestamp(),
                        'end'   => $end->getTimestamp()
                    ]
                ],
                [2],
                $excluded_lock_ids
            ) > 0;
    }

    /**
     * Determines, if the resource is available (not assigned or locked)
     * in a specified time range.
     *
     * @param DateTime $begin Time range start timestamp.
     * @param DateTime $end Time range end timestamp.
     *
     * @param array $excluded_booking_ids The IDs of available bookings that shall
     *     be excluded from the determination of the "available" status.
     *
     * @return bool True, if the resource is available in the specified
     *     time range, false otherwise.
     */
    public function isAvailable(
        DateTime $begin,
        DateTime $end,
        $excluded_booking_ids = []
    )
    {
        return ResourceBooking::countByResourceAndTimeRanges(
                $this,
                [
                    [
                        'begin' => $begin->getTimestamp(),
                        'end'   => $end->getTimestamp()
                    ]
                ],
                [0, 2],
                $excluded_booking_ids
            ) == 0;
    }

    /**
     * Determines, if the resource is available (not assigned or locked)
     * in the time ranges specified by a resource request.
     *
     * @param ResourceRequest $request A resource request object.
     *
     * @return bool True, if the resource is available in the
     *     time ranges of the resource request, false otherwise.
     */
    public function isAvailableForRequest(ResourceRequest $request)
    {
        $time_intervals = $request->getTimeIntervals(true);
        if (!$time_intervals) {
            //Without a single time interval we cannot check
            //if the resource is available.
            return false;
        }
        foreach ($time_intervals as $time_interval) {
            $begin = new DateTime();
            $end   = new DateTime();
            $begin->setTimestamp($time_interval['begin']);
            $end->setTimestamp($time_interval['end']);

            if (!$this->isAvailable($begin, $end)) {
                //The resource is not available in the time interval.
                //We can stop here and return false.
                return false;
            }

            //If code execution reaches this point the resource is
            //available in all time intervals of the resource request:
            return true;
        }
    }

    /**
     * Returns the full (localised) name of the resource.
     *
     * @return string The full name of the resource.
     */
    public function getFullName()
    {
        return sprintf(
            _('Ressource %s'),
            $this->name
        );
    }

    /**
     * Sets the permission for one user for this resource.
     *
     * @param User $user The user whose permission shall be set.
     * @param string $perm The permission level for the specified user.
     *     The levels 'user', 'autor', 'tutor' and 'admin' are allowed.
     *
     * @return bool True, if the permission has been stored successfully,
     *     false otherwise.
     */
    public function setUserPermission(User $user, $perm = 'autor')
    {
        if (!in_array($perm, ['user', 'autor', 'tutor', 'admin'])) {
            return false;
        }

        $perm_object = ResourcePermission::findOneBySql(
            '(user_id = :user_id) AND (resource_id = :resource_id)',
            [
                'user_id'     => $user->id,
                'resource_id' => $this->id
            ]
        );

        if (!$perm_object) {
            $perm_object              = new ResourcePermission();
            $perm_object->user_id     = $user->id;
            $perm_object->resource_id = $this->id;
        }

        $perm_object->perms = $perm;
        $stored = (bool)$perm_object->store();
        if ($stored) {
            if (!is_array(self::$permission_cache[$this->id])) {
                self::$permission_cache[$this->id] = [];
            }
            //Update the permission cache.
            self::$permission_cache[$this->id][$user->id] = $perm;
        }
        return $stored;
    }

    /**
     * Deletes the permission a specified user has on this resource.
     *
     * @param User $user The user whose permission shall be deleted.
     *
     * @return bool True
     */
    public function deleteUserPermission(User $user)
    {
        $deleted = ResourcePermission::deleteBySql(
            '(user_id = :user_id) AND (resource_id = :resource_id)',
            [
                'user_id'     => $user->id,
                'resource_id' => $this->id
            ]
        );

        if ($deleted && is_array(self::$permission_cache[$this->id])) {
            //Update the permission cache.
            self::$permission_cache[$this->id][$user->id] = null;
        }

        return true;
    }

    /**
     * Deletes all permissions of all users for this resource.
     *
     * @return bool True
     */
    public function deleteAllPermissions()
    {
        ResourcePermission::deleteBySql(
            'resource_id = :resource_id',
            [
                'resource_id' => $this->id
            ]
        );

        //Update the permission cache:
        self::$permission_cache[$this->id] = [];

        return true;
    }

    /**
     * Retrieves the permission level a specified user
     * has on this resource.
     *
     * Setting the optional $time_range parameter will also enable checks for
     * temporary global permissions.
     *
     * @param User $user The user whose permission shall be retrieved.
     *
     * @param array $time_range (DateTime) This is an optional parameter that can
     *     be used to pass two DateTime objects to this method. The first object
     *     will be treated as the begin timestamp and the second one as the
     *     end timestamp.
     *
     * @param bool $permanent_only Whether to retrieve only permanent permissions
     *     (true) or permanent and temporary permissions (false).
     *     Defaults to false.
     *
     * @return string The permission level, expressed as string.
     *     The level can be 'user', 'autor', 'tutor' or 'admin'.
     */
    public function getUserPermission(User $user, $time_range = [], $permanent_only = false)
    {
        if ($user->perms == 'root') {
            //root users are automatically resource admins:
            return 'admin';
        }

        //Check for a temporary permission first:

        $perm_string = '';
        $temp_perm   = null;

        if (!$permanent_only) {
            $begin = time();
            $end   = $begin;

            //If $time range is set and contains two DateTime objects
            //we can include that in the search for temporary permissions.
            if ($time_range) {
                if ($time_range[0] instanceof DateTime) {
                    $begin = $time_range[0]->getTimestamp();
                } else {
                    $begin = $time_range[0];
                }
                if ($time_range[1] instanceof DateTime) {
                    $end = $time_range[1]->getTimestamp();
                } else {
                    $end = $time_range[1];
                }
            }

            $temp_perm = ResourceTemporaryPermission::findOneBySql(
                '(resource_id = :resource_id) AND (user_id = :user_id)
                AND (begin <= :begin) AND (end >= :end)',
                [
                    'resource_id' => $this->id,
                    'user_id'     => $user->id,
                    'begin'       => $begin,
                    'end'         => $end
                ]
            );
        }

        if ($temp_perm) {
            $perm_string = $temp_perm->perms;
        } else {
            //No temporary permission exist or has been retrieved.
            //Check for a "normal" permission.
            $cached_perms = self::$permission_cache[$this->id][$user->id];
            if ($cached_perms === null) {
                //The permission of the specified user is not in the
                //permission cache. Load it from the database and store
                //it in the permission cache before returning it.
                $perms = ResourcePermission::findOneBySql(
                    '(resource_id = :resource_id) AND (user_id = :user_id)',
                    [
                        'resource_id' => $this->id,
                        'user_id'     => $user->id
                    ]
                );
                if ($perms) {
                    if (!is_array(self::$permission_cache[$this->id])) {
                        self::$permission_cache[$this->id] = [];
                    }
                    self::$permission_cache[$this->id][$user->id] = $perms->perms;
                    $perm_string                                  = $perms->perms;
                }
            } else {
                $perm_string = $cached_perms;
            }
        }

        //Now we must check for global resource locks:

        if (GlobalResourceLock::currentlyLocked()) {
            //The resource management system is currently locked.
            //We must either return 'admin' for users with that
            //permission level or 'user' for all other permission
            //levels.
            if ($perm_string == 'admin') {
                return 'admin';
            } elseif ($perm_string) {
                //A permission level exists for the user.
                //The user gets "user" permissions in case
                //a global lock is active.
                return 'user';
            } else {
                //No permission level exists for the user.
                return '';
            }
        }

        //No global resource lock exists. We must return
        //the permission string if it is set:
        if ($perm_string) {
            return $perm_string;
        }

        //A user which doesn't have special permissions for this resource
        //can have global resource permissions:
        $global_perm = ResourceManager::getGlobalResourcePermission($user);
        if ($global_perm) {
            //Set the permission cache:
            if (!is_array(self::$permission_cache[$this->id])) {
                self::$permission_cache[$this->id] = [];
            }
            self::$permission_cache[$this->id][$user->id] = $global_perm;
        }
        return $global_perm;
    }

    /**
     * Determines if a user has the specified permission.
     *
     * @param User $user The user whose permissions shall be checked on this
     *     resource object.
     * @param string $permission The permission level.
     * @param $time_range @TODO
     *
     * @return bool True, if the specified user has the specified permission,
     *     false otherwise.
     */
    public function userHasPermission(
        User $user,
        string $permission = 'user',
        array $time_range = []
    )
    {
        if (!in_array($permission, ['user', 'autor', 'tutor', 'admin'])) {
            return false;
        }

        if ($user->perms == 'root') {
            //root users have all permissions for the resource.
            return true;
        }

        $perm_level = $this->getUserPermission($user, $time_range);

        if ($permission === 'user') {
            //No check for global resource locks here:
            //If only user permissions are requested we can safely grant them
            //since 'user' users may only perform reading actions but
            //no writing actions.
            if (in_array($perm_level, ['user', 'autor', 'tutor', 'admin'])) {
                return true;
            } else {
                return false;
            }
        } elseif ($permission === 'autor') {
            if (GlobalResourceLock::currentlyLocked()) {
                //A global resource lock means no writing actions are permitted.
                return false;
            }
            if (in_array($perm_level, ['autor', 'tutor', 'admin'])) {
                return true;
            } else {
                return false;
            }
        } elseif ($permission === 'tutor') {
            if (GlobalResourceLock::currentlyLocked()) {
                //A global resource lock means no writing actions are permitted.
                return false;
            }
            if (in_array($perm_level, ['tutor', 'admin'])) {
                return true;
            } else {
                return false;
            }
        } elseif ($permission === 'admin') {
            //No check for global resource locks here:
            //Admins may always do write actions in the resource management.
            if ($perm_level == 'admin') {
                return true;
            } else {
                return false;
            }
        }
        //Code execution should be finished at this point.
        //If this point is reached the user has no permissions for the
        //resource management system at all.
        return false;
    }

    /**
     * Determines whether the user may create a child resource
     * on this resource.
     *
     * @param User $user The user whose permission to create a child
     *     resource shall be checked.
     *
     * @return bool True, if the user may create a child resource
     *     on this resource, false otherwise.
     */
    public function userMayCreateChild(User $user)
    {
        return $this->userHasPermission($user, 'admin');
    }

    /**
     * Checks if the specified user has sufficient permissions to make resource
     * requests, according to the setting RESOURCES_MIN_REQUEST_PERMISSION.
     * This permission check is only relevant for creating requests that are not
     * bound to a course.
     *
     * @param User $user The user whose request permissions shall be checked.
     *
     * @return bool True, if the user has request permissions, false otherwise.
     */
    public function userHasRequestRights(User $user)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            return false;
        }
        $min_perm = Config::get()->RESOURCES_MIN_REQUEST_PERMISSION;
        if (!in_array($min_perm, ['', 'user', 'autor', 'tutor', 'admin'])) {
            //Invalid permission level!
            return false;
        }
        if (!$min_perm) {
            //No minimum permission set: Every logged-in user
            //can create requests.
            return true;
        }
        return $this->userHasPermission($user, $min_perm);
    }

    /**
     * Determines whether the user may book the resource or not.
     * An optional time range can be set to check  the user's
     * temporary permissions on another date than the current date.
     *
     * @param User $user The user whose booking permissions shall be checked.
     *
     * @param int|string|DateTime $begin The begin timestamp of the
     *     optional time range.
     *
     * @param int|string|DateTime $end The end timestamp of the
     *     optional time range.
     *
     * @return bool True, if the user may book the resource, false otherwise.
     */
    public function userHasBookingRights(
        User $user,
        $begin = null,
        $end = null
    )
    {
        if (!$begin) {
            $begin = time();
        }
        if (!$end) {
            $end = $begin;
        }

        //Check the permissions on this resource and the global permissions:
        return $this->userHasPermission(
            $user,
            'autor',
            [
                $begin,
                $end
            ]
        );
    }

    /**
     * Determines if the booking plan of the resource is visible for a
     * specified user.
     *
     * @param User $user The user whose permission to view the booking plan
     *     shall be determined.
     *
     * @param DateTime[] $time_range An optional time range for the
     *     permission check.
     * @return bool True, if the user can see the resource booking plan,
     *     false otherwise.
     * @see Resource::getUserPermission
     *
     */
    public function bookingPlanVisibleForUser(User $user, $time_range = [])
    {
        return $this->userHasPermission($user, 'user', $time_range)
            || $GLOBALS['perm']->have_perm('root', $user->id);
    }

    /**
     * Retrieves a parent resource object that matches the specified
     * class name. The search stops when either a parent resource
     * with the class name is found or when the root resource object
     * is reached.
     *
     * @param string $class_name The class name of the parent.
     *
     * @return Resource|null Either a resource object or null
     *     in case a matching parent resource cannot be found.
     */
    public function findParentByClassName($class_name = 'Resource')
    {
        $resource_ids = [$this->id];
        $resource     = $this->parent;

        while ($resource) {
            //We should check for circular hierarchies first
            //to avoid an endless while loop:
            if (in_array($resource->id, $resource_ids)) {
                //We have a circular hierarchy: this resource is
                //the parent of itself which is an invalid state!
                throw new InvalidResourceException(
                    sprintf(
                        _('Zirkuläre Hierarchie: Die Ressource %1$s ist ein Elternknoten von sich selbst!'),
                        $resource->name
                    )
                );
            }
            if (is_a($resource->class_name, $class_name, true)) {
                //We have found a parent node which has the
                //specified class name: return that parent.
                return $resource;
            }
            //The current parent was not the one we were looking for.
            //Therefore we must go one layer up in the resource
            //hierarchy and continue search:
            $resource_ids[] = $resource->id;
            $resource       = $resource->parent;
        }
        //The search was not successful:
        //We have reached the root resource (whose parent_id field
        //is set to an equivalend of NULL) and we haven't found a
        //resource matching the specified class name.
        return null;
    }

    /**
     * This method searches the hierarchy below this resource
     * to find resources matching the specified class name.
     * Via the optional parameter $depth the search can be limited
     * to a specific amount of layers.
     *
     * @param string $class_name The name of the resource class
     *     where resources shall be found to.
     * @param int $depth The (optional) maximum depth below this resource
     *     which shall be searched.
     * @param bool $convert_objects True, if objects shall be converted to
     *     $class_name (default), false otherwise.
     * @param bool $order_by_name Order the children by name.
     *     Defaults to true.
     *
     * @return Resource[] An array of resource objects or an empty array
     *     if no matching resources can be found.
     */
    public function findChildrenByClassName(
        $class_name = 'Resource',
        $depth = 0,
        $convert_objects = true,
        $order_by_name = true
    )
    {
        $result = [];
        if ($this->children) {
            //this resource has children: iterate over them and
            //check if they match the search criteria.
            foreach ($this->children as $child) {
                if (is_a($child->class_name, $class_name, true)) {
                    if ($convert_objects) {
                        $result[] = $child->getDerivedClassInstance();
                    } else {
                        $result[] = $child;
                    }
                }
                if (($depth > 1) || ($depth == 0)) {
                    //Search the child and lower depth by one when calling this
                    //method on the child.
                    $result = array_merge(
                        $result,
                        $child->findChildrenByClassName(
                            $class_name,
                            (($depth > 1) ? $depth - 1 : 0),
                            $convert_objects
                        )
                    );
                }
            }
            if ($order_by_name) {
                usort(
                    $result,
                    function ($a, $b) {
                        if ($a->name == $b->name) {
                            return 0;
                        } elseif ($a->name < $b->name) {
                            return -1;
                        } else {
                            return 1;
                        }
                    }
                );
            }
        }
        return $result;
    }

    /**
     * Adds a resource as child resource to this resource.
     *
     * @param Resource $resource The child resource.
     *
     * @return bool True on success, false on failure.
     */
    public function addChild(Resource $resource)
    {
        $old_parent    = $resource->parent;
        $old_parent_id = $resource->parent_id;

        $resource->parent    = $this;
        $resource->parent_id = $this->id;

        if (!$resource->checkHierarchy()) {
            //We must revert the parent fields since $resource
            //may be used in other code pieces afterwards.
            $resource->parent    = $old_parent;
            $resource->parent_id = $old_parent_id;
            throw new InvalidArgumentException(
                sprintf(
                    _('Die Ressource %1$s (Typ %2$s) kann nicht unterhalb der Ressource %3$s (Typ %4$s) platziert werden!'),
                    $resource->name,
                    $resource->class_name,
                    $this->name,
                    $this->class_name
                )
            );
        }
        if ($resource->isDirty()) {
            //Only store the resource object if setting the parent_id field
            //did change it:
            return $resource->store();
        }
        //The resource object hasn't changed by setting the parent_id field:
        //We can return true.
        return true;
    }

    /**
     * Get all resource requests for the resource in a given timeframe.
     *
     * @param DateTime $begin Begin of timeframe.
     * @param DateTime $end End of timeframe.
     *
     * @return ResourceRequest[] An array of ResourceRequest objects.
     */
    public function getOpenResourceRequests(DateTime $begin, DateTime $end)
    {
        //We must get all requests that either have a start and end date
        //set or that have a start date, repeate end, repeat interval and
        //repeat quantity set.

        return ResourceRequest::findByResourceAndTimeRanges(
            $this,
            [
                [
                    'begin' => $begin->getTimestamp(),
                    'end'   => $end->getTimestamp()
                ]
            ],
            0
        );
    }

    /**
     * Get all resource bookings for the resource in a given timeframe.
     *
     * @param DateTime $begin Begin of timeframe.
     * @param DateTime $end End of timeframe.
     *
     * @return ResourceBooking[] An array of ResourceBooking objects.
     */
    public function getResourceBookings(DateTime $begin, DateTime $end)
    {
        return ResourceBooking::findByResourceAndTimeRanges(
            $this,
            [
                [
                    'begin' => $begin->getTimestamp(),
                    'end' => $end->getTimestamp()
                ]
            ],
            [0]
        );
    }


    /**
     * Get all resource locks for the resource in a given timeframe.
     *
     * @param DateTime $begin Begin of timeframe.
     * @param DateTime $end End of timeframe.
     *
     * @return ResourceBooking[] An array of ResourceBooking objects.
     */
    public function getResourceLocks(DateTime $begin, DateTime $end)
    {
        return ResourceBooking::findByResourceAndTimeRanges(
            $this,
            [
                [
                    $begin->getTimestamp(),
                    $end->getTimestamp()
                ]
            ],
            [2]
        );
    }


    /**
     * Determines if files are attached to this resource.
     * If a folder exists for this resource its files are counted.
     * Depending on whether the folder has files in it or not
     * this method returns true or false.
     *
     * @return bool True, if there are files attached to this resource,
     *     false otherwise.
     */
    public function hasFiles()
    {
        $folder = Folder::findOneBySql(
            'range_id = :range_id',
            [
                'range_id' => $this->id
            ]
        );

        if (!$folder) {
            return false;
        }

        //Since files from resources shall always be stored in the
        //Stud.IP file system we can skip the conversion from Folder
        //to FolderType and count the FileRef-objects for this resource
        //directly in the database. Since resource folders do not
        //have subfolders we will count any file of the resource:
        return FileRef::countBySql(
                'folder_id = :folder_id',
                [
                    'folder_id' => $folder->id
                ]
            ) > 0;
    }

    /**
     * Converts a Resource object to an object of a specialised resource class.
     *
     * @return Resource|other An object of a specialised resource class
     *     or a Resource object, if the resource is a standard resource
     *     with the class_name 'Resource' in its resource category.
     *     If the derived resource class is not available, an instance of
     *     BrokenResource is returned.
     */
    public function getDerivedClassInstance()
    {
        $class_name = $this->class_name;

        if ($class_name == 'Resource') {
            //It is a standard resource which is managed by this class.
            return $this;
        }

        if (is_subclass_of($class_name, 'Resource')) {
            $converted_resource = $class_name::buildExisting(
                $this->toRawArray()
            );
            return $converted_resource;
        } else {
            //$class_name does not contain the name of a subclass
            //of Resource. That's an error!
            $broken_resource = BrokenResource::buildExisting(
                $this->toRawArray()
            );
            return $broken_resource;
        }
    }

    /**
     * Checks if the place in the resource hierarchy (resource tree)
     * is correct for this resource.
     * This method has no function in this class but can be filled
     * with logic in one of the classes derived from Resource.
     *
     * @return bool True, if this resource is correctly placed,
     *     false otherwise.
     * @throws NoResourceClassException
     *     if the class name of this resource is not a derived class
     *     of the Resource class.
     *
     */
    public function checkHierarchy()
    {
        if ($this->class_name == 'Resource') {
            //Objects of the Resource class are always in the right
            //place of the resource hierarchy.
            return true;
        }

        //The object does not use the Resource class name and uses
        //a derived class instead. We must check the hierarchy
        //using the checkHierarchy method of the derived class.

        $converted_resource = $this->getDerivedClassInstance();
        return $converted_resource->checkHierarchy();
    }

    /**
     * Returns the link for an action for this resource.
     * This is the non-static variant of Resource::getLinkForAction.
     *
     * @param string $action The action which shall be executed.
     *     For default Resources the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param array $link_parameters Optional parameters for the link.
     * @return string @TODO
     */
    public function getActionLink($action = 'show', $link_parameters = [])
    {
        //We must check the class name and call the appropriate
        //getLinkForAction method for derived classes:

        $class_name = $this->class_name;
        if (is_subclass_of($class_name, 'Resource')) {
            return $class_name::getLinkForAction(
                $action,
                $this->id,
                $link_parameters
            );
        } else {
            return self::getLinkForAction(
                $action,
                $this->id,
                $link_parameters
            );
        }
    }

    /**
     * Returns the URL for an action for this resource.
     * This is the non-static variant of Resource::getURLForAction.
     *
     * @param string $action The action which shall be executed.
     *     For default Resources the actions 'show', 'add', 'edit' and 'delete'
     *     are defined.
     * @param array $url_parameters Optional parameters for the URL.
     * @return string @TODO
     */
    public function getActionURL($action = 'show', $url_parameters = [])
    {
        //We must check the class name and call the appropriate
        //getURLForAction method for derived classes:

        $class_name = $this->class_name;
        if (is_subclass_of($class_name, 'Resource')) {
            return $class_name::getURLForAction(
                $action,
                $this->id,
                $url_parameters
            );
        } else {
            return self::getURLForAction(
                $action,
                $this->id,
                $url_parameters
            );
        }
    }

    public function getItemName($long_format = true)
    {
        if ($long_format) {
            //In some cases the general Resource class may be used
            //when the resource objects are in fact instances
            //of derived classes. To make sure that the correct prefix
            //is always displayed, we retrieve the derived class first
            //before returning the name:
            $derived_class = $this->getDerivedClassInstance();
            return $derived_class->getFullName();
        } else {
            return $this->name;
        }
    }

    public function getItemURL()
    {
        return $this->getActionURL('show');
    }

    public function getItemAvatarURL()
    {
        return Icon::create('resources', Icon::ROLE_INFO)->asImagePath();
    }


    public function getLink() : StudipLink
    {
        return new StudipLink($this->getActionURL(), $this->name, Icon::create('resources'));
    }
}
