<?php

/**
 * room_planning.php - contains Resources_RoomPlanningController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2018
 * @category    Stud.IP
 * @since       TODO
 */


/**
 * Resources_RoomPlanningController contains actions related to
 * room planing tasks.
 */
class Resources_RoomPlanningController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        $anonymous_actions = ['booking_plan', 'anonymous_booking_plan_data'];
        if (in_array($action, $anonymous_actions)) {
            $this->allow_nobody = true;
        }
        parent::before_filter($action, $args);
    }


    public function overbooked_rooms_action()
    {
        $current_user = User::findCurrent();
        if (!($current_user instanceof User)) {
            throw new AccessDeniedException();
        }
        if (!ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            throw new AccessDeniedException();
        }

        $current_semester = Semester::findCurrent();

        if (!$current_semester) {
            PageLayout::postError(
                _('Es gibt kein aktuelles Semester in dieser Stud.IP-Installation!')
            );
        }
        $begin = DateTime::createFromFormat('Y-m-d H:i:s', '2017-06-12 0:00:00');
        //$begin->setTimestamp($current_semester->beginn);
        $end = DateTime::createFromFormat('Y-m-d H:i:s', '2017-06-18 23:59:59');
        //$end->setTimestamp($current_semester->ende);

        $this->overbooked_bookings = RoomManager::findOverbookedRoomBookings($begin, $end);

        $this->courses          = [];
        $this->overbooked_rooms = [];
        foreach ($this->overbooked_bookings as $booking) {
            $room   = Room::find($booking->resource_id);
            $course = Course::find($booking->range_id);
            if (!$room || !$course) {
                //Either the resource is not a room resource
                //or the course does not exist (anymore).
                continue;
            }

            $this->overbooked_rooms[$room->id] = $room;
            //There may be more than one course for a room:
            if (!is_array($this->courses[$room->id])) {
                $this->courses[$room->id] = [];
            }
            $this->courses[$room->id][$course->id] = [
                'name'         => $course->getFullName(),
                'participants' => CourseMember::countBySeminar_id($course->id)
            ];
        }
    }


    public function booking_plan_action($resource_id = null)
    {
        if (Navigation::hasItem('/resources/planning/booking_plan')) {
            Navigation::activateItem('/resources/planning/booking_plan');
        }

        PageLayout::setTitle(_('Belegungsplan'));

        $current_user = User::findCurrent();

        $new_resource_id = Request::get('new_resource_id');
        if ($new_resource_id) {
            $this->redirect(
                'resources/room_planning/booking_plan/' . $new_resource_id
            );
        }

        if (!$resource_id) {
            $resource_id = Request::get('resource_id');
        }
        $this->rooms = null;
        if ($current_user instanceof User) {
            $this->rooms = RoomManager::getUserRooms($current_user);
        }
        if (!$resource_id) {
            if ($current_user instanceof User) {
                //Get a list of all available rooms and let the user select
                //one of those.
                $this->selection_link_template =
                    'resources/room_planning/booking_plan/%s';
                return;
            } else {
                throw new AccessDeniedException();
            }
        }

        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Der gewählte Raum wurde nicht gefunden!')
            );
            return;
        }

        URLHelper::addLinkParam('resource_id', $this->resource->id);
        $this->resource = $this->resource->getDerivedClassInstance();

        PageLayout::setTitle(
            sprintf(
                _('Belegungsplan: %s'),
                $this->resource->getFullName()
            )
        );

        if ($this->resource->requestable) {
            $this->display_all_requests = Request::get('display_all_requests');
        } else {
            $this->display_all_requests = false;
        }

        //The booking plan is visible when the user-ID is not 'nobody'
        //and the user has at least 'user' permissions on the resource.
        //The booking plan is also visible when the resource is a room
        //and its booking plan is publicly available.
        $plan_is_visible      = false;
        $this->anonymous_view = true;
        $this->booking_types  = [0, 1, 2];
        if ($current_user instanceof User) {
            if ($this->display_all_requests) {
                $plan_is_visible = $this->resource->userHasPermission(
                    $current_user,
                    'autor'
                );
            } else {
                $plan_is_visible = $this->resource->bookingPlanVisibleForUser($current_user);
            }
            $this->anonymous_view = false;
            if ($this->resource->userHasPermission($current_user, 'admin')) {
                $this->booking_types[] = 3;
            }
        }
        //If the plan visibility cannot be determined by the user,
        //we can still check if the plan is visible to the public:
        if (!$plan_is_visible && ($this->resource instanceof Room)) {
            $plan_is_visible = $this->resource->booking_plan_is_public;
        }
        if (!$plan_is_visible) {
            throw new AccessDeniedException(
                _('Der Belegungsplan ist für Sie nicht zugänglich!')
            );
        }

        $this->user_has_request_permissions = false;
        $this->user_has_booking_permissions = false;
        if ($current_user instanceof User) {
            $this->user_has_request_permissions = $this->resource->userHasRequestRights($current_user);
            $this->user_has_booking_permissions = $this->resource->userHasBookingRights(
                $current_user
            );
        }

        if (!$this->user_has_booking_permissions && $this->display_all_requests) {
            throw new AccessDeniedException(
                _('Sie sind nicht dazu berechtigt, alle Anfragen im Belegungsplan zu sehen!')
            );
        }

        $week_timestamp = Request::get('timestamp');
        $default_date   = Request::get('defaultDate');
        $this->date     = new DateTime();
        if ($week_timestamp) {
            $this->date->setTimestamp($week_timestamp);
        } elseif ($default_date) {
            $this->date     = Request::getDateTime('defaultDate');
            $week_timestamp = $this->date->getTimestamp();
        } else {
            $week_timestamp = $this->date->getTimestamp();
        }

        //Build sidebar:
        $sidebar = Sidebar::get();

        if ($this->rooms) {
            $room_select = new SelectWidget(
                _('Anderen Raum wählen'),
                '',
                'new_resource_id'
            );
            $options     = [];
            foreach ($this->rooms as $room) {
                $options[$room->id] = $room->name;
            }
            $room_select->setOptions($options, $this->resource->id);
            $sidebar->addWidget($room_select);
        }

        $views = new ViewsWidget();
        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            if ($this->resource->userHasPermission($current_user)) {
                $views->addLink(
                    _('Standard Zeitfenster'),
                    URLHelper::getURL(
                        '',
                        [
                            'defaultDate' => Request::get('defaultDate', date('Y-m-d'))
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-std_view']
                )->setActive(!Request::get('allday'));

                $views->addLink(
                    _('Ganztägiges Zeitfenster'),
                    URLHelper::getURL(
                        '',
                        [
                            'allday'      => true,
                            'defaultDate' => Request::get('defaultDate', date('Y-m-d'))
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-allday_view']
                )->setActive(Request::get('allday'));
            }
        }
        $sidebar->addWidget($views);

        if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS && $this->user_has_booking_permissions) {
            $options = new OptionsWidget();
            if ($this->resource->requestable) {
                $options->addCheckbox(
                    _('Alle Anfragen anzeigen'),
                    $this->display_all_requests ? 'checked' : '',
                    $this->url_for(
                        'resources/room_planning/booking_plan/' . $this->resource->id,
                        [
                            'display_all_requests' => '1',
                            'allday'               => Request::get('allday', false)
                        ]
                    ),
                    $this->url_for(
                        'resources/room_planning/booking_plan/' . $this->resource->id,
                        [
                            'allday' => Request::get('allday', false)
                        ]
                    ),
                    []
                );
            }
            $sidebar->addWidget($options);
        }

        $dpicker = new SidebarWidget();
        $dpicker->setTitle('Datum');
        $picker_html = $this->get_template_factory()->render(
            'resources/room_planning/_sidebar_date_selection.php'
        );
        $dpicker->addElement(new WidgetElement($picker_html));
        $sidebar->addWidget($dpicker);

        $actions = new ActionsWidget();
        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            if ($this->user_has_booking_permissions) {
                $actions->addLink(
                    _('Neue Buchung'),
                    URLHelper::getURL('dispatch.php/resources/booking/add/' . $this->resource->id),
                    Icon::create('add')
                )->asDialog();
            } elseif ($this->resource->requestable && $this->user_has_request_permissions
                      && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
                $actions->addLink(
                    _('Neue Anfrage'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_request/add/'
                        . $this->resource->id
                    ),
                    Icon::create('add')
                )->asDialog("size=auto");
            }
            if ($this->resource->userHasPermission($current_user)) {
                $actions->addLink(
                    _('Belegungsplan drucken'),
                    'javascript:void(window.print());',
                    Icon::create('print')
                );
                if ($this->resource instanceof Room) {
                    $actions->addLink(
                        _('Individuelle Druckansicht'),
                        URLHelper::getURL(
                            'dispatch.php/resources/print/individual_booking_plan/'
                            . $this->resource->id,
                            [
                                'timestamp' => $week_timestamp
                            ]
                        ),
                        Icon::create('print')
                    );
                }
                $actions->addLink(
                    _('Buchungen exportieren'),
                    URLHelper::getURL(
                        'dispatch.php/resources/export/resource_bookings/' . $this->resource->id,
                        [
                            'timestamp' => $week_timestamp
                        ]
                    ),
                    Icon::create('file-excel'),
                    [
                        'data-dialog' => 'size=auto',
                        'id'          => 'export-resource-bookings-action'
                    ]
                );

                if ($this->resource instanceof Room) {
                    $actions->addLink(
                        _('Raumeigenschaften anzeigen'),
                        URLHelper::getURL(
                            'dispatch.php/resources/room/index/' . $this->resource->id
                        ),
                        Icon::create('link-intern'),
                        ['data-dialog' => 'size=auto']
                    );
                }
            }
        }
        if ($this->resource instanceof Room && $this->resource->booking_plan_is_public) {
            $actions->addLink(
                _('QR-Code anzeigen'),
                $this->resource->getActionURL('booking_plan'),
                Icon::create('download'),
                [
                    'data-qr-code'       => '',
                    'data-qr-code-print' => '1',
                    'data-qr-title'      => _('Aktueller Belegungsplan')
                ]
            );
        }

        if ($current_user instanceof User) {
            //No check necessary here: This part of the controller is only called
            //when a room has been selected before.
            if (($this->resource instanceof Room) && RoomManager::userHasRooms($current_user)) {
                $actions->addLink(
                    _('Anderen Raum wählen'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_planning/booking_plan',
                        [],
                        true
                    ),
                    Icon::create('refresh')
                );
            }
        }
        $sidebar->addWidget($actions);

        $course_booking_colour = ColourValue::find('Resources.BookingPlan.CourseBooking.Bg');
        $booking_colour        = ColourValue::find('Resources.BookingPlan.Booking.Bg');
        $lock_colour           = ColourValue::find('Resources.BookingPlan.Lock.Bg');
        $preparation_colour    = ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $reservation_colour    = ColourValue::find('Resources.BookingPlan.Reservation.Bg');
        $request_colour        = ColourValue::find('Resources.BookingPlan.Request.Bg');

        if ($this->resource instanceof Room) {
            $this->table_keys = [
                [
                    'colour' => (string)$booking_colour,
                    'text'   => _('Manuelle Buchung')
                ],
                [
                    'colour' => (string)$course_booking_colour,
                    'text'   => _('Veranstaltungsbezogene Buchung')
                ],
                [
                    'colour' => (string)$lock_colour,
                    'text'   => _('Sperrbuchung')
                ],
                [
                    'colour' => (string)$preparation_colour,
                    'text'   => _('Rüstzeit')
                ],
                [
                    'colour' => (string)$reservation_colour,
                    'text'   => _('Reservierung')
                ]
            ];
            if (!$this->anonymous_view) {
                if ($current_user instanceof User) {
                    if ($this->resource->userHasPermission($current_user, 'admin')) {
                        $planned_booking_colour = ColourValue::find('Resources.BookingPlan.PlannedBooking.Bg');
                        $this->table_keys[]     = [
                            'colour' => (string)$planned_booking_colour,
                            'text'   => _('Geplante Buchung')
                        ];
                    }
                }
                $this->table_keys[] = [
                    'colour' => (string)$request_colour,
                    'text'   => (
                    $this->display_all_requests
                        ? _('Anfrage')
                        : _('Eigene Anfrage')
                    )
                ];
            }
        } else {
            $this->table_keys = [];
        }

        $this->fullcalendar_studip_urls = [];
        if ($this->user_has_booking_permissions) {
            $this->fullcalendar_studip_urls['add'] = $this->url_for(
                'resources/booking/add/' . $this->resource->id
            );
        } elseif ($this->resource->requestable && !$this->anonymous_view
                  && $this->user_has_request_permissions && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            $this->fullcalendar_studip_urls['add'] = $this->url_for(
                'resources/room_request/add/' . $this->resource->id
            );
        }
    }


    public function semester_plan_action($resource_id = null)
    {
        $current_user = User::findCurrent();
        if (!($current_user instanceof User)) {
            throw new AccessDeniedException();
        }
        $this->rooms = RoomManager::getUserRooms($current_user);

        if (Navigation::hasItem('/resources/planning/semester_plan')) {
            Navigation::activateItem('/resources/planning/semester_plan');
        }

        $new_resource_id = Request::get('new_resource_id');
        if ($new_resource_id) {
            $this->redirect(
                'resources/room_planning/semester_plan/' . $new_resource_id
            );
        }

        if (!$resource_id) {
            $resource_id = Request::get('resource_id');
        }
        if (!$resource_id) {
            //Get a list of all available rooms and let the user select
            //one of those.

            $this->selection_link_template =
                'resources/room_planning/semester_plan/%s';
            return;
        }

        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Die angegebene Ressource wurde nicht gefunden!')
            );
            return;
        }
        URLHelper::addLinkParam('resource_id', $this->resource->id);
        $this->resource = $this->resource->getDerivedClassInstance();

        PageLayout::setTitle(
            sprintf(
                _('Semester-Belegungsplan: %s'),
                $this->resource->getFullName()
            )
        );
        $this->current_semester_id = Request::get('semester_id');
        if ($this->current_semester_id) {
            URLHelper::addLinkParam('semester_id', $this->semester_id);
        } else {
            $this->current_semester_id = Semester::findCurrent()->id;
        }
        if (Request::isDialog()) {
            $this->dialog_semesters = array_reverse(Semester::getAll());
            $this->plan_link        = URLHelper::getLink(
                'dispatch.php/resources/room_planning/semester_plan/' . $this->resource->id,
                [
                    'allday' => Request::get('allday', false)
                ]
            );
        }
        URLHelper::addLinkParam('semester_timerange', Request::get("semester_timerange"));
        URLHelper::addLinkParam('allday', Request::get('allday'));

        if ($this->resource->requestable) {
            $this->display_all_requests = Request::get('display_all_requests');
            URLHelper::addLinkParam('display_all_requests', Request::get('display_all_requests'));

        } else {
            $this->display_all_requests = false;
        }


        //The booking plan is visible when the user-ID is not 'nobody'
        //and the user has at least 'user' permissions on the resource.
        //The booking plan is also visible when the resource is a room
        //and its booking plan is publicly available.
        $plan_is_visible =
            $this->resource->bookingPlanVisibleForUser($current_user);

        if (!$plan_is_visible) {
            throw new AccessDeniedException(
                _('Der Belegungsplan ist für Sie nicht zugänglich!')
            );
        }


        $this->user_has_booking_permissions = $this->resource->userHasBookingRights(
            $current_user
        );
        $this->booking_types                = [0, 1, 2];
        if ($this->resource->userHasPermission($current_user, 'admin')) {
            $this->booking_types[] = 3;
        }
        if ($this->user_has_booking_permissions) {
            URLHelper::addLinkParam('display_single_bookings', Request::get("display_single_bookings"));
            $this->display_single_bookings = Request::get("display_single_bookings");
        }

        if (!$this->user_has_booking_permissions && $this->display_all_requests) {
            throw new AccessDeniedException(
                _('Sie sind nicht dazu berechtigt, alle Anfragen im Belegungsplan zu sehen!')
            );
        }

        $this->fullcalendar_studip_urls = [];
        if ($this->user_has_booking_permissions) {
            $this->fullcalendar_studip_urls['add'] = $this->url_for(
                'resources/booking/add/' . $this->resource->id
            );
        }
        //Build sidebar:
        $sidebar = Sidebar::get();

        $this->semester = Semester::findCurrent();
        //For the semester selector:
        if (Request::submitted('semester_id')) {
            $this->semester = Semester::find(Request::get('semester_id'));
            if (!$this->semester) {
                PageLayout::postError(
                    _('Das ausgewählte Semester wurde nicht in der Datenbank gefunden!')
                );
                return;
            }
        }

        $this->fullcalendar_studip_urls = [];
        if ($this->user_has_booking_permissions) {
            $this->fullcalendar_studip_urls['add'] = $this->url_for(
                'resources/booking/add/' . $this->resource->id, ['semester_id' => $this->semester->id]
            );
        }

        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            if ($this->rooms) {
                $room_select = new SelectWidget(
                    _('Anderen Raum wählen'),
                    '',
                    'new_resource_id'
                );
                $options     = [];
                foreach ($this->rooms as $room) {
                    $options[$room->id] = $room->name;
                }
                $room_select->setOptions($options, $this->resource->id);
                $sidebar->addWidget($room_select);
            }
            if ($this->resource->userHasPermission($current_user, 'user')) {
                $views = new ViewsWidget();
                $views->setTitle(_('Zeitfenster'));
                $views->addLink(
                    _('Standard Zeitfenster'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_planning/semester_plan/' . $this->resource->id,
                        [
                            'allday' => null
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-std_view']
                )->setActive(!Request::get('allday'));

                $views->addLink(
                    _('Ganztägiges Zeitfenster'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_planning/semester_plan/' . $this->resource->id,
                        [
                            'allday' => true,
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-allday_view']
                )->setActive(Request::get('allday'));
                $sidebar->addWidget($views);

                $views2 = new ViewsWidget();
                $views2->setTitle(_('Semesterzeitraum'));
                $views2->addLink(
                    _('Vorlesungszeit'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_planning/semester_plan/' . $this->resource->id,
                        [
                            'allday'             => Request::get('allday'),
                            'defaultDate'        => Request::get('defaultDate', date('Y-m-d')),
                            'semester_id'        => $this->semester->id,
                            'semester_timerange' => 'vorles'
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-vorles_view']
                )->setActive(Request::get('semester_timerange') != 'fullsem');
                $views2->addLink(
                    _('gesamtes Semester'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_planning/semester_plan/' . $this->resource->id,
                        [
                            'allday'             => Request::get('allday'),
                            'defaultDate'        => Request::get('defaultDate', date('Y-m-d')),
                            'semester_id'        => $this->semester->id,
                            'semester_timerange' => 'fullsem'
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-fullsem_view']
                )->setActive(Request::get('semester_timerange') == 'fullsem');

                $sidebar->addWidget($views2);
            }
        }
        if ($this->user_has_booking_permissions) {
            $options = new OptionsWidget();
            $options->addCheckbox(
                _('zukünftige Einzeltermine einblenden'),
                $this->display_single_bookings ? 'checked' : '',
                $this->url_for(
                    'resources/room_planning/semester_plan/' . $this->resource->id,
                    [
                        'display_single_bookings' => '1',

                    ]
                ),
                $this->url_for(
                    'resources/room_planning/semester_plan/' . $this->resource->id,
                    [
                        'display_single_bookings' => null,
                    ]
                ),
                []
            );
            $sidebar->addWidget($options);
        }

        $semester_selector = new SemesterSelectorWidget(
            URLHelper::getURL(
                'dispatch.php/resources/room_planning/semester_plan/' . $this->resource->id,
                [
                    'allday' => Request::get('allday', false)
                ]
            )
        );
        $sidebar->addWidget($semester_selector);

        $sidebar = Sidebar::get();

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Drucken'),
            'javascript:void(window.print());',
            Icon::create('print')
        );
        //No check necessary here: This part of the controller is only called
        //when a room has been selected before.
        $actions->addLink(
            _('Anderen Raum wählen'),
            URLHelper::getURL(
                'dispatch.php/resources/room_planning/semester_plan',
                [],
                true
            ),
            Icon::create('refresh')
        );
        $sidebar->addWidget($actions);

        $booking_colour                        = ColourValue::find('Resources.BookingPlan.Booking.Bg');
        $course_booking_colour                 = ColourValue::find('Resources.BookingPlan.CourseBooking.Bg');
        $lock_colour                           = ColourValue::find('Resources.BookingPlan.Lock.Bg');
        $preparation_colour                    = ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $reservation_colour                    = ColourValue::find('Resources.BookingPlan.Reservation.Bg');
        $request_colour                        = ColourValue::find('Resources.BookingPlan.Request.Bg');
        $this->table_keys                      = [
            [
                'colour' => (string)$booking_colour,
                'text'   => _('Manuelle Buchung')
            ],
            [
                'colour' => (string)$course_booking_colour,
                'text'   => _('Veranstaltungsbezogene Buchung')
            ],
            [
                'colour' => (string)$lock_colour,
                'text'   => _('Sperrbuchung')
            ],
            [
                'colour' => (string)$preparation_colour,
                'text'   => _('Rüstzeit')
            ],
            [
                'colour' => (string)$reservation_colour,
                'text'   => _('Reservierung')
            ],
        ];
        if ($this->resource->userHasPermission($current_user, 'admin')) {
            $planned_booking_colour = ColourValue::find('Resources.BookingPlan.PlannedBooking.Bg');
            $this->table_keys[]     = [
                'colour' => (string)$planned_booking_colour,
                'text'   => _('Geplante Buchung')
            ];
        }

        if ($this->resource->requestable) {
            if ($this->user_has_booking_permissions && $this->display_all_requests) {
                $this->table_keys[] =
                    [
                        'colour' => (string)$request_colour,
                        'text'   => _('Anfrage für regelmäßige Termine')
                    ];
            }
            if (!$this->user_has_booking_permissions) {
                $this->table_keys[] =
                    [
                        'colour' => (string)$request_colour,
                        'text'   =>
                            _('Eigene Anfrage für regelmäßige Termine')
                    ];
            }
        }
    }
}
