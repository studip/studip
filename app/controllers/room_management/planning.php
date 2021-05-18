<?php

/**
 * planning.php - contains RoomManagement_PlanningController
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
 * RoomManagement_PlanningController contains room planning functionality.
 */
class RoomManagement_PlanningController extends AuthenticatedController
{
    public function index_action($selected_clipboard_id = null)
    {
        PageLayout::setTitle(
            _('Raumgruppen-Belegungsplan')
        );

        if (Navigation::hasItem('/resources/planning/index')) {
            Navigation::activateItem('/resources/planning/index');
        }

        if ($selected_clipboard_id) {
            $_SESSION['selected_clipboard_id'] = $selected_clipboard_id;
            $this->redirect(
                $this->url_for('room_management/planning/index', $_GET)
            );
        }

        $this->display_all_requests = Request::get('display_all_requests');

        //Build sidebar:
        $sidebar = Sidebar::get();

        $views = new ViewsWidget();
        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            $views->addLink(
                _('Standard Zeitfenster'),
                URLHelper::getURL(
                    'dispatch.php/room_management/planning/index',
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
                    'dispatch.php/room_management/planning/index',
                    [
                        'allday'      => true,
                        'defaultDate' => Request::get('defaultDate', date('Y-m-d'))
                    ]
                ),
                null,
                ['class' => 'booking-plan-allday_view']
            )->setActive(Request::get('allday'));
        }
        $sidebar->addWidget($views);

        $dpicker = new SidebarWidget();
        $dpicker->setTitle('Datum');
        $picker_html = $this->get_template_factory()->render(
            'resources/room_planning/_sidebar_date_selection.php'
        );
        $dpicker->addElement(new WidgetElement($picker_html));
        $sidebar->addWidget($dpicker);

        //Add clipboard widget:
        $clipboard_widget = new RoomClipboardWidget();
        $clipboard_widget->setReadonly(true);
        $clipboard_widget->setApplyButtonTitle(
            _('Anzeigen')
        );
        $sidebar->addWidget($clipboard_widget);

        //Check if a clipboard is selected:
        $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        if (!$selected_clipboard_id) {
            $clipboards = Clipboard::getClipboardsForUser($GLOBALS['user']->id);
            if (!empty($clipboards)) {
                $selected_clipboard_id = $clipboards[0]->id;
            }
        }
        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        $rooms = [];
        if ($selected_clipboard_id) {
            $clipboard = Clipboard::find($selected_clipboard_id);
            $this->clipboard = $clipboard;
            if ($clipboard) {
                PageLayout::setTitle(
                    $clipboard->name . ': ' . _('Raumgruppen-Belegungsplan')
                );
                if ($selected_clipboard_item_ids) {
                    //The array of current clipboard item IDs is not empty.
                    //This means that at least one but not necessarily all
                    //clipboard items are selected.
                    $room_ids = $clipboard->getSomeRangeIds(
                        'Room',
                        $selected_clipboard_item_ids
                    );
                } else {
                    //Use all items from the clipboard:
                    $room_ids = $clipboard->getAllRangeIds('Room');
                }

                $rooms = Room::findMany($room_ids);
            } else {
                $this->no_clipboard = true;
                return;
            }
        }

        if (!$rooms) {
            //No rooms could be found.
            $this->no_rooms = true;
            return;
        }

        //Generate the resources array for the fullcalendar scheduler plugin:
        $this->scheduler_resources = [];
        foreach ($room_ids as $room_id) {
            $room = Room::find($room_id);
            $this->scheduler_resources[] = [
                'id'          => $room->id,
                'parent_name' => $room->building->name,
                'title'       => $room->name
            ];
        }

        $current_user = User::findCurrent();

        $room_c = count($rooms);
        $requestable_rooms_c = 0;
        $request_rights_c = 0;
        $booking_rights_c = 0;
        $admin_rights_c = 0;
        $this->booking_types = [0, 1, 2];

        foreach ($rooms as $room) {
            if ($room->userHasRequestRights($current_user)) {
                $request_rights_c++;
            }
            if ($room->userHasBookingRights($current_user)) {
                $booking_rights_c++;
            }
            if ($room->userHasPermission($current_user, 'admin')) {
                $admin_rights_c++;
            }
            if ($room->requestable) {
                $requestable_rooms_c++;
            }

            //Check the permissions for the room:
            //The booking plan must be visible for the user.
            $sufficient_permissions =
                $room->bookingPlanVisibleForUser($current_user);
            if (!$sufficient_permissions) {
                throw new AccessDeniedException(
                    sprintf(
                        _('Der Belegungsplan des Raumes %s ist für Sie nicht zugänglich!'),
                        $room->name
                    )
                );
            }
        }

        $this->all_rooms_booking_rights = ($room_c == $booking_rights_c);
        $all_rooms_admin = ($room_c == $admin_rights_c);
        if ($all_rooms_admin) {
            //Display planned bookings, too:
            $this->booking_types[] = 3;
        }
        if (!$this->all_rooms_booking_rights && $this->display_all_requests) {
            throw new AccessDeniedException(
                _('Sie sind nicht dazu berechtigt, alle Anfragen im Belegungsplan zu sehen!')
            );
        }

        if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS && $this->all_rooms_booking_rights) {
            $options = new OptionsWidget();
            $options->addCheckbox(
                _('Alle Anfragen anzeigen'),
                $this->display_all_requests ? 'checked' : '',
                $this->url_for(
                    'room_management/planning/index/' . $_SESSION['selected_clipboard_id'],
                    [
                        'display_all_requests' => '1'
                    ]
                ),
                $this->url_for(
                    'room_management/planning/index/' . $_SESSION['selected_clipboard_id']
                ),
                []
            );
            $sidebar->insertWidget($options, 'roomclipboard');
        }

        $this->fullcalendar_studip_urls = [];
        if ($this->all_rooms_booking_rights) {
            $this->fullcalendar_studip_urls['add'] = URLHelper::getURL(
                'dispatch.php/resources/booking/add'
            );
        }

        $booking_colour = ColourValue::find('Resources.BookingPlan.Booking.Bg');
        $course_booking_colour = ColourValue::find('Resources.BookingPlan.CourseBooking.Bg');
        $lock_colour = ColourValue::find('Resources.BookingPlan.Lock.Bg');
        $preparation_colour = ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $reservation_colour = ColourValue::find('Resources.BookingPlan.Reservation.Bg');
        $request_colour = ColourValue::find('Resources.BookingPlan.Request.Bg');
        $this->table_keys = [
            [
                'colour' => $booking_colour->__toString(),
                'text'   => _('Manuelle Buchung')
            ],
            [
                'colour' => $course_booking_colour->__toString(),
                'text'   => _('Veranstaltungsbezogene Buchung')
            ],
            [
                'colour' => $lock_colour->__toString(),
                'text'   => _('Sperrbuchung')
            ],
            [
                'colour' => $preparation_colour->__toString(),
                'text'   => _('Rüstzeit')
            ],
            [
                'colour' => $reservation_colour->__toString(),
                'text'   => _('Reservierung')
            ],
        ];
        if ($all_rooms_admin) {
            $planned_booking_colour = ColourValue::find('Resources.BookingPlan.PlannedBooking.Bg');
            $this->table_keys[] = [
                'colour' => $planned_booking_colour->__toString(),
                'text'   => _('Geplante Buchung')
            ];
        }
        if ($this->display_all_requests) {
            $this->table_keys[] = [
                'colour' => $request_colour->__toString(),
                'text'   => _('Anfrage')
            ];
        }
    }

    public function semester_plan_action($selected_clipboard_id = null)
    {
        PageLayout::setTitle(
            _('Raumgruppen-Semester-Belegungsplan')
        );

        if (Navigation::hasItem('/resources/planning/semestergroup_plan')) {
            Navigation::activateItem('/resources/planning/semestergroup_plan');
        }

        if ($selected_clipboard_id) {
            $_SESSION['selected_clipboard_id'] = $selected_clipboard_id;
            $this->redirect(
                $this->url_for('room_management/planning/semester_plan', $_GET)
            );
        }

        $this->display_all_requests = Request::get('display_all_requests');

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

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Buchungen kopieren'),
            $this->url_for('room_management/planning/copy_bookings'),
            Icon::create('assessment+add'),
            ['data-dialog' => 'size=auto']
        );
        $sidebar->addWidget($actions);


        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            $views = new ViewsWidget();
            $views->setTitle(_('Zeitfenster'));
            $views->addLink(
                _('Standard Zeitfenster'),
                URLHelper::getURL(
                    'dispatch.php/room_management/planning/semester_plan',
                    [
                        'defaultDate' => Request::get('defaultDate', date('Y-m-d')),
                        'semester_id' => $this->semester->id,
                        'semester_timerange' => Request::get('semester_timerange', 'vorles')
                    ]
                ),
                null,
                ['class' => 'booking-plan-std_view']
            )->setActive(!Request::get('allday'));

            $views->addLink(
                _('Ganztägiges Zeitfenster'),
                URLHelper::getURL(
                    'dispatch.php/room_management/planning/semester_plan',
                    [
                        'allday' => true,
                        'defaultDate' => Request::get('defaultDate', date('Y-m-d')),
                        'semester_id' => $this->semester->id,
                        'semester_timerange' => Request::get('semester_timerange', 'vorles')
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
                    'dispatch.php/room_management/planning/semester_plan',
                    [
                        'allday' => Request::get('allday'),
                        'defaultDate' => Request::get('defaultDate', date('Y-m-d')),
                        'semester_id' => $this->semester->id,
                        'semester_timerange' => 'vorles'
                    ]
                ),
                null,
                ['class' => 'booking-plan-vorles_view']
            )->setActive(Request::get('semester_timerange') != 'fullsem');
            $views2->addLink(
                _('gesamtes Semester'),
                URLHelper::getURL(
                    'dispatch.php/room_management/planning/semester_plan',
                    [
                        'allday' => Request::get('allday'),
                        'defaultDate' => Request::get('defaultDate', date('Y-m-d')),
                        'semester_id' => $this->semester->id,
                        'semester_timerange' => 'fullsem'
                    ]
                ),
                null,
                ['class' => 'booking-plan-fullsem_view']
            )->setActive(Request::get('semester_timerange') == 'fullsem');
            $sidebar->addWidget($views2);
        }
        $semester_selector = new SemesterSelectorWidget(
            URLHelper::getURL(
                'dispatch.php/room_management/planning/semester_plan/' . $this->resource->id,
                [
                    'allday' => Request::get('allday', false)
                ]
            )
        );
        $sidebar->addWidget($semester_selector);

        //Add clipboard widget:
        $clipboard_widget = new RoomClipboardWidget();
        $clipboard_widget->setReadonly(true);
        $clipboard_widget->setApplyButtonTitle(
            _('Anzeigen')
        );
        $sidebar->addWidget($clipboard_widget);

        //Check if a clipboard is selected:
        $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        $rooms = [];
        if ($selected_clipboard_id) {
            $clipboard = Clipboard::find($selected_clipboard_id);
            $this->clipboard = $clipboard;
            if ($clipboard) {
                PageLayout::setTitle(
                    $clipboard->name . ': ' . _('Raumgruppen-Semester-Belegungsplan')
                );
                if ($selected_clipboard_item_ids) {
                    //The array of current clipboard item IDs is not empty.
                    //This means that at least one but not necessarily all
                    //clipboard items are selected.
                    $room_ids = $clipboard->getSomeRangeIds(
                        'Room',
                        $selected_clipboard_item_ids
                    );
                } else {
                    //Use all items from the clipboard:
                    $room_ids = $clipboard->getAllRangeIds('Room');
                }

                $rooms = Room::findMany($room_ids);
            } else {
                $this->no_clipboard = true;
                return;
            }
        }

        if (!$rooms) {
            //No rooms could be found.
            $this->no_rooms = true;
            return;
        }

        //Generate the resources array for the fullcalendar scheduler plugin:
        $this->scheduler_resources = [];
        foreach ($room_ids as $room_id) {
            $room = Room::find($room_id);
            $this->scheduler_resources[] = [
                'id' => $room->id,
                'parent_name' => $room->building->name,
                'title' => $room->name
            ];
        }

        $current_user = User::findCurrent();

        $room_c = count($rooms);
        $requestable_rooms_c = 0;
        $booking_rights_c = 0;
        $admin_rights_c = 0;
        $this->booking_types = [0, 1, 2];

        foreach ($rooms as $room) {
            if ($room->userHasBookingRights($current_user)) {
                $booking_rights_c++;
            }
            if ($room->userHasPermission($current_user, 'admin')) {
                $admin_rights_c++;
            }
            if ($room->requestable) {
                $requestable_rooms_c++;
            }

            //Check the permissions for the room:
            $sufficient_permissions =
                $room->userHasPermission($current_user, 'user')
                || $room->booking_plan_is_public;
            if (!$sufficient_permissions) {
                throw new AccessDeniedException();
            }
        }

        $all_rooms_requestable = ($room_c == $requestable_rooms_c);
        $all_rooms_booking_rights = ($room_c == $booking_rights_c);
        $all_rooms_admin = ($room_c == $admin_rights_c);
        if ($all_rooms_admin) {
            //Display planned bookings, too:
            $this->booking_types[] = 3;
        }

        if (!$all_rooms_booking_rights && $this->display_all_requests) {
            throw new AccessDeniedException(
                _('Sie sind nicht dazu berechtigt, alle Anfragen im Belegungsplan zu sehen!')
            );
        }

        if ($all_rooms_booking_rights) {
            $options = new OptionsWidget();
            $options->addCheckbox(
                _('Alle Anfragen anzeigen'),
                $this->display_all_requests ? 'checked' : '',
                $this->url_for(
                    'room_management/planning/semester_plan/' . $_SESSION['selected_clipboard_id'],
                    [
                        'display_all_requests' => '1',
                        'semester_id' => Request::option('semester_id')
                    ]
                ),
                $this->url_for(
                    'room_management/planning/semester_plan/' . $_SESSION['selected_clipboard_id'],
                    [
                        'semester_id' => Request::option('semester_id')
                    ]
                ),
                []
            );
            $sidebar->insertWidget($options, 'roomclipboard');
        }

        $booking_colour = ColourValue::find('Resources.BookingPlan.Booking.Bg');
        $simple_booking_exception_colour = ColourValue::find('Resources.BookingPlan.SimpleBookingWithExceptions.Bg');
        $course_booking_colour = ColourValue::find('Resources.BookingPlan.CourseBooking.Bg');
        $course_booking_with_exceptions_colour = ColourValue::find('Resources.BookingPlan.CourseBookingWithExceptions.Bg');
        $lock_colour = ColourValue::find('Resources.BookingPlan.Lock.Bg');
        $preparation_colour = ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $reservation_colour = ColourValue::find('Resources.BookingPlan.Reservation.Bg');
        $request_colour = ColourValue::find('Resources.BookingPlan.Request.Bg');
        $this->table_keys = [
            [
                'colour' => $booking_colour->__toString(),
                'text'   => _('Manuelle Buchung')
            ],
            [
                'colour' => $course_booking_colour->__toString(),
                'text'   => _('Veranstaltungsbezogene Buchung')
            ],
            [
                'colour' => $lock_colour->__toString(),
                'text'   => _('Sperrbuchung')
            ],
            [
                'colour' => $preparation_colour->__toString(),
                'text'   => _('Rüstzeit')
            ],
            [
                'colour' => $reservation_colour->__toString(),
                'text'   => _('Reservierung')
            ],
        ];
        if ($all_rooms_admin) {
            $planned_booking_colour = ColourValue::find('Resources.BookingPlan.PlannedBooking.Bg');
            $this->table_keys[] = [
                'colour' => $planned_booking_colour->__toString(),
                'text'   => _('Geplante Buchung')
            ];
        }
        if ($this->display_all_requests) {
            $this->table_keys[] = [
                'colour' => $request_colour->__toString(),
                'text'   => _('Anfrage')
            ];
        }

    }

    public function copy_bookings_action($clipboard_id = null)
    {
        PageLayout::setTitle(
            _('Buchungen kopieren')
        );

        if (Navigation::hasItem('/resources/planning/copy_bookings')) {
            Navigation::activateItem('/resources/planning/copy_bookings');
        }

        //Check if the clipboard is selected:
        $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        $user = User::findCurrent();

        $this->clipboard = null;
        if ($selected_clipboard_id) {
            $this->clipboard = Clipboard::find($selected_clipboard_id);
        } else {
            $this->clipboard = Clipboard::find($clipboard_id);
            if (!$clipboard_id) {
                PageLayout::postError(
                    _('Es wurde keine Raumgruppe ausgewählt!')
                );
                return;
            }
        }
        if (!$this->clipboard) {
            PageLayout::postError(
                _('Die gewählte Raumgruppe wurde nicht gefunden!')
            );
            return;
        }
        if ($this->clipboard->user_id != $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(
            $this->clipboard->name . ': ' . _('Buchungen kopieren')
        );

        //Step 1: Room selection
        $this->step = 1;

        //Get all Room items from the clipboard where the user has at least
        //user permissions:
        $all_room_ids = $this->clipboard->getAllRangeIds('Room');
        $unfiltered_rooms = Room::findMany($all_room_ids);
        $this->rooms = [];
        $this->available_room_ids = [];
        foreach ($unfiltered_rooms as $room) {
            if ($room->userHasPermission($user, 'autor')) {
                $this->rooms[] = $room;
                $this->available_room_ids[] = $room->id;
            }
        }

        $this->selected_room_ids = [];
        if ($selected_clipboard_id == $this->clipboard->id) {
            $this->selected_room_ids = $selected_clipboard_item_ids;
        }

        //Get all available semesters:
        $this->available_semesters = Semester::getAll();
        $this->sem_week_selected = false;
        $this->selected_sem_week = 1;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('select_rooms') || Request::submitted('step1')) {
                $this->step = 2;
            } elseif (Request::submitted('test_copy') || Request::submitted('step2')
                      || Request::submitted('download_booking_list')) {
                $this->step = 3;
            } elseif (Request::submitted('copy')) {
                $this->step = 4;
            }
        }

        if ($this->step >= 2) {
            //Step 2: Select and verify bookings and semester
            $this->source_semester_id = Request::get('source_semester_id');
            $this->sem_week_selected = Request::get('sem_week_selected');
            $this->selected_sem_week = Request::get('selected_sem_week');
            $this->selected_room_ids = Request::getArray('selected_room_ids');
            if (!$this->source_semester_id) {
                PageLayout::postError(
                    _('Es wurde kein Semester ausgewählt!')
                );
                $this->step = 1;
                return;
            }
            $this->source_semester = Semester::find($this->source_semester_id);
            if (!$this->source_semester) {
                PageLayout::postError(
                    _('Das gewählte Semester wurde nicht gefunden!')
                );
                $this->step = 1;
                return;
            }
            if ($this->sem_week_selected) {
                $last_sem_week_number = $this->source_semester->getSemWeekNumber(
                    $this->source_semester->vorles_ende
                );
                if (($this->selected_sem_week < 1) || ($this->selected_sem_week > $last_sem_week_number)) {
                    PageLayout::postError(
                        _('Die gewählte Semesterwoche liegt außerhalb des gewählten Semesters!')
                    );
                    $this->step = 1;
                    return;
                }
            }
            if (!$this->selected_room_ids) {
                PageLayout::postError(
                    _('Es wurden keine Räume ausgewählt!')
                );
                $this->step = 1;
                return;
            }

            foreach ($this->selected_room_ids as $room_id) {
                if (!in_array($room_id, $all_room_ids)) {
                    PageLayout::postError(
                        _('Es wurde ein Raum ausgewählt, der nicht Teil der Raumgruppe ist!')
                    );
                    $this->step = 1;
                    return;
                }
                if (!in_array($room_id, $this->available_room_ids)) {
                    PageLayout::postError(
                        _('Es wurde ein Raum ausgewählt, an dem die Berechtigungen zum Kopieren von Buchungen nicht ausreichend sind!')
                    );
                    $this->step = 1;
                    return;
                }
            }

            if (Request::submitted('step1')) {
                $this->step = 1;
                return;
            }

            $this->selected_rooms = Room::findMany($this->selected_room_ids);

            $this->available_target_semesters = Semester::findBySql(
                'beginn > :source_semester_end ORDER BY beginn ASC',
                ['source_semester_end' => $this->source_semester->ende]
            );
            if (!$this->available_target_semesters) {
                PageLayout::postError(
                    _('Es sind keine Semester vorhanden, die nach dem ausgewählten Semester starten!')
                );
                $this->step = 1;
                return;
            }

            $unfiltered_bookings = [];
            foreach ($this->selected_rooms as $room) {
                $room_bookings = [];
                if ($this->sem_week_selected) {
                    $selected_week_begin = $this->source_semester->vorles_beginn;
                    if ($this->selected_sem_week > 1) {
                        $selected_week_begin = strtotime(
                            sprintf('+%d weeks', $this->selected_sem_week),
                            $this->source_semester->vorles_beginn
                        );
                    }
                    $room_bookings = ResourceBooking::findByResourceAndTimeRanges(
                        $room,
                        [
                            [
                                'begin' => $selected_week_begin,
                                'end' => $this->source_semester->ende
                            ]
                        ]
                    );
                } else {
                    $room_bookings = ResourceBooking::findByResourceAndTimeRanges(
                        $room,
                        [
                            [
                                'begin' => $this->source_semester->beginn,
                                'end' => $this->source_semester->ende
                            ]
                        ]
                    );
                }
                if ($room_bookings) {
                    $unfiltered_bookings = array_merge(
                        $unfiltered_bookings,
                        $room_bookings
                    );
                }
            }
            $this->bookings = [];
            $this->available_booking_ids = [];
            $this->booking_time_ranges = [];
            foreach ($unfiltered_bookings as $booking) {
                if (!$booking->repetition_interval || !$booking->isSimpleBooking()) {
                    //We only regard simple bookings with repetitions here.
                    continue;
                }
                $this->bookings[] = $booking;
                $this->available_booking_ids[] = $booking->id;
                $this->booking_time_ranges[$booking->id] =
                    $booking->getTimeIntervalStrings();
            }

            if (!$this->available_booking_ids) {
                PageLayout::postError(
                    sprintf(
                        _('Die gewählten Räume haben im Semester %s keine einfachen Buchungen mit Wiederholungen!'),
                        htmlReady($this->source_semester->name)
                    )
                );
                $this->step = 1;
                return;
            }
        }
        if ($this->step >= 3) {
            //Step 3: Test copying into the target semester
            $this->show_copy_button = false;
            $this->target_semester_id = Request::get('target_semester_id');
            $this->selected_booking_ids = Request::getArray('selected_booking_ids');
            if (!$this->target_semester_id) {
                PageLayout::postError(
                    _('Es wurde kein Zielsemester ausgewählt!')
                );
                $this->step = 2;
                return;
            }
            $this->target_semester = Semester::find($this->target_semester_id);
            if (!$this->target_semester) {
                PageLayout::postError(
                    _('Das gewählte Zielsemester wurde nicht gefunden!')
                );
                $this->step = 2;
                return;
            }

            if (!$this->selected_booking_ids) {
                PageLayout::postError(
                    _('Es wurden keine Buchungen ausgewählt!')
                );
                $this->step = 2;
                return;
            }

            foreach ($this->selected_booking_ids as $booking_id) {
                if (!in_array($booking_id, $this->available_booking_ids)) {
                    PageLayout::postError(
                        _('Es wurde eine Buchung ausgewählt, die nicht Teil der Raumgruppe ist!')
                    );
                    $this->step = 2;
                    return;
                }
            }

            if (Request::submitted('step2')) {
                $this->step = 2;
                return;
            }

            //Retrieve booking objects:
            $this->selected_bookings = ResourceBooking::findMany($this->selected_booking_ids);

            if (!$this->selected_bookings) {
                PageLayout::postError(
                    _('Die gewählten Buchungen wurden nicht in der Datenbank gefunden!')
                );
                $this->step = 2;
                return;
            }

            //$booking_copy_data is an associative array where the items have
            //the following strucutre:
            //[
            //    'sem_week' => The week number of the target semester.
            //    'begin' => The timestamp of the begin of the copied booking.
            //    'end' => The timestamp of the end of the copied booking.
            //    'available' => Whether the resource is available
            //        on the specified time range (true) or not (false).
            //]
            $this->booking_copy_data = [];

            //Loop over each booking and do the following:
            //1. Calculate the week number and the week day of the booking
            //   in the semester, unless the week number has been explicitly
            //   specified in step 1.
            //2. Calculate the date for the copy of the booking and store it
            //   in an array.
            //3. Check if the resource of the booking is available on the
            //   calculcated date in the time range of the original booking.
            //4. Add the availability information to the array
            //   with the booking copies.
            //5. Count the number of bookings and how many of them can be
            //   copied in the target semester. If more that 50% of bookings
            //   cannot be copied, do not show the copy action and instead
            //   provide a download button to download the list of bookings.

            $available_booking_c = 0;
            foreach ($this->selected_bookings as $booking) {
                $begin_sem_week_number = 0;
                if ($this->sem_week_selected) {
                    $begin_sem_week_number = $this->selected_sem_week +
                                             $this->source_semester->getSemWeekNumber($booking->begin) - 1;
                } else {
                    $begin_sem_week_number = $this->source_semester->getSemWeekNumber($booking->begin);
                }
                if (!$begin_sem_week_number) {
                    PageLayout::postError(
                        sprintf(
                            _('Eine Buchung (%1$s) liegt außerhalb des Semesters %2$s!'),
                            htmlReady($booking->__toString()),
                            htmlReady($this->source_semester->name)
                        )
                    );
                    $this->step = 2;
                    return;
                }
                $begin_week_day = date('N', $booking->begin);
                $begin_time = explode(':', date('H:i:s', $booking->begin));
                $end_time = explode(':', date('H:i:s', $booking->end));

                //Calculate the duration (begin-end-difference):
                $booking_begin = new DateTime();
                $booking_begin->setTimestamp($booking->begin);
                $booking_end = new DateTime();
                $booking_end->setTimestamp($booking->end);

                $duration = $booking_begin->diff($booking_end);
                $booking_repeat_end = new DateTime();
                $booking_repeat_end->setTimestamp($booking->repeat_end);
                $repeat_duration = $booking_end->diff($booking_repeat_end);

                //Calculate the new begin date:
                $target_sem_week_begin = new DateTime();
                $target_sem_week_begin->setTimestamp($this->target_semester->beginn);
                $target_sem_week_begin = $target_sem_week_begin->add(
                    new DateInterval('P' . ($begin_sem_week_number - 1) . 'W')
                );
                $target_begin = clone $target_sem_week_begin;
                $begin_week_day_diff = $begin_week_day - $target_sem_week_begin->format('N');
                if ($begin_week_day_diff < 0) {
                    $target_begin = $target_begin->sub(
                        new DateInterval('P' . abs($begin_week_day_diff) . 'D')
                    );
                } elseif ($begin_week_day_diff > 0) {
                    $target_begin = $target_begin->add(
                        new DateInterval('P' . $begin_week_day_diff . 'D')
                    );
                }
                $target_begin->setTime(
                    intval($begin_time[0]),
                    intval($begin_time[1]),
                    intval($begin_time[2])
                );

                //Calculcate the new end date using the duration:
                $target_end = clone $target_begin;
                $target_end = $target_end->add($duration);

                //Calculcate the new repeat end using the repeat duration
                //or the end of the semester, if repeat_end of the original
                //booking is the same timestamp as the course end of the
                //source semester.
                $target_repeat_end = clone $target_end;
                if ($booking->repeat_end >= $this->source_semester->vorles_ende) {
                    $target_repeat_end->setTimestamp(
                        $this->target_semester->vorles_ende
                    );
                } else {
                    $target_repeat_end = $target_repeat_end->add(
                        $repeat_duration
                    );
                    if ($target_repeat_end >= $this->target_semester->vorles_ende) {
                        $target_repeat_end->setTimestamp(
                            $this->target_semester->vorles_ende
                        );
                    }
                }

                $copy_data = [
                    'sem_week_number' => $begin_sem_week_number,
                    'copy' => null,
                    'available' => null,
                    'original' => $booking,
                    'time_intervals' => []
                ];

                $copy = new ResourceBooking();
                $copy->resource_id = $booking->resource_id;
                $copy->range_id = $booking->range_id;
                $copy->booking_user_id = $GLOBALS['user']->id;
                $copy->description = $booking->description;
                $copy->begin = $target_begin->getTimestamp() +
                               $booking->preparation_time;
                $copy->end = $target_end->getTimestamp();
                $copy->preparation_time = $booking->preparation_time;
                $copy->booking_type = $booking->booking_type;
                $copy->repeat_end = $target_repeat_end->getTimestamp();
                $copy->repeat_quantity = $booking->repeat_quantity;
                $copy->repetition_interval = $booking->repetition_interval;
                $copy->internal_comment = $booking->internal_comment;
                if ($this->step == 3) {
                    //We only need to call validate when we are really
                    //trying to check if the booking can be made.
                    //After step 3, we don't need to call validate manually
                    //since it is automatically called before storing.
                    //Furthermore, the availability flag isn't important
                    //anymore after step 3.
                    $time_intervals = $copy->calculateTimeIntervals();
                    if (!$time_intervals) {
                        //The copied booking will have no time intervals.
                        //So we can skip to the next one.
                        continue;
                    }
                    $copy_data['time_intervals'] = $copy->calculateTimeIntervals();
                    try {
                        $copy->validate();
                        $copy_data['available'] = true;
                        $available_booking_c++;
                    } catch (Exception $e) {
                        $copy_data['available'] = false;
                    }
                }
                $copy_data['copy'] = $copy;
                $this->booking_copy_data[$booking->id] = $copy_data;
            }
            if (Request::submitted('download_booking_list')) {
                $csv_data = [
                    [
                        _('Buchungsnummer'),
                        _('Buchungszeitraum'),
                        _('Raum'),
                        _('Verfügbar')
                    ]
                ];
                $booking_c = 1;
                foreach ($this->booking_copy_data as $data) {
                    foreach ($data['time_intervals'] as $interval) {
                        $time_range = sprintf(
                            '%1$s - %2$s',
                            date('d.m.Y H:i', $interval['begin']),
                            date('d.m.Y H:i', $interval['end'])
                        );
                        $csv_data[] = [
                            $booking_c,
                            $time_range,
                            $data['original']->resource->name,
                            $data['available'] ? _('ja') : _('nein')
                        ];
                    }
                    $booking_c++;
                }
                $filename = sprintf(
                    _('Zu kopierende Buchungen am %s') . '.csv',
                    date('d.m.Y')
                );
                $this->render_csv($csv_data, $filename);
                return;
            } elseif ($this->step < 4) {
                $booking_c = count($this->selected_bookings);
                if ($booking_c) {
                    if (($available_booking_c / $booking_c) < 0.5) {
                        PageLayout::postInfo(
                            _('Weniger als die Hälfte der Buchungen können in das Zielsemester kopiert werden!')
                        );
                        return;
                    } else {
                        $this->show_copy_button = true;
                    }
                }
            }
        }
        if ($this->step >= 4) {
            $errors = [];
            $count = 0;
            //Step 4: Copy the bookings
            foreach ($this->booking_copy_data as $copy_data) {
                try {
                    $copy_data['copy']->store();
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
                $count++;
            }

            if (!$errors) {
                PageLayout::postSuccess(
                    _('Alle Buchungen wurden kopiert!')
                );
            } else {
                if (count($errors) < $count) {
                    PageLayout::postWarning(
                        _('Es konnten nicht alle Buchungen kopiert werden!'),
                        $errors
                    );
                } else {
                    PageLayout::postError(
                        _('Keine der ausgewählten Buchungen konnte kopiert werden!'),
                        $errors
                    );
                }
            }
        }
    }

    public function booking_comments_action($selected_clipboard_id = null)
    {
        PageLayout::setTitle(_('Buchungen mit Kommentaren'));

        if (Navigation::hasItem('/resources/planning/booking_comments')) {
            Navigation::activateItem('/resources/planning/booking_comments');
        }

        if ($selected_clipboard_id) {
            $_SESSION['selected_clipboard_id'] = $selected_clipboard_id;
            $this->redirect(
                $this->url_for('room_management/planning/booking_comments', $_GET)
            );
        }

        //Get the selected date or use the current date, if none specified:
        $this->date = Request::getDateTime('date', 'd.m.Y', null, null, new DateTime());
        if ($this->date === false) {
            //Format parsing error. Try the YYYY-mm-dd format:
            $this->date = Request::getDateTime('date', 'Y-m-d', null, null, new DateTime());
            if ($this->date === false) {
                //Fallback to the current date:
                $this->date = new DateTime();
            }
        }

        //Build sidebar:
        $sidebar = Sidebar::get();

        //Add the date selection widget:
        $date_search = new SearchWidget(
            $this->url_for('room_management/planning/booking_comments')
        );
        $date_search->setTitle(_('Datum'));
        $date_search->setMethod('get');
        $date_search->addNeedle(
            _('Datum'),
            'date',
            'DD.MM.YYYY',
            null,
            null,
            $this->date->format('d.m.Y'),
            ['class' => 'with-datepicker']
        );
        $sidebar->addWidget($date_search);

        //Add clipboard widget:
        $clipboard_widget = new RoomClipboardWidget();
        $clipboard_widget->setReadonly(true);
        $clipboard_widget->setApplyButtonTitle(
            _('Anzeigen')
        );
        $sidebar->addWidget($clipboard_widget);

        $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        if (!$selected_clipboard_id) {
            $clipboards = Clipboard::getClipboardsForUser($GLOBALS['user']->id);
            if (!empty($clipboards)) {
                $selected_clipboard_id = $clipboards[0]->id;
            }
        }
        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        $this->current_user = User::findCurrent();
        $this->room_ids = [];
        if ($selected_clipboard_id) {
            $clipboard = Clipboard::find($selected_clipboard_id);
            $this->clipboard = $clipboard;
            if ($clipboard) {
                PageLayout::setTitle(
                    $clipboard->name . ': ' . _('Buchungen mit Kommentaren')
                );
                if ($selected_clipboard_item_ids) {
                    //The array of current clipboard item IDs is not empty.
                    //This means that at least one but not necessarily all
                    //clipboard items are retrieved, checked for user
                    //resource permissions and then added, if the user's
                    //permissions are high enough.
                    $room_ids = $clipboard->getSomeRangeIds(
                        'Room',
                        $selected_clipboard_item_ids
                    );
                } else {
                    //Use all items from the clipboard:
                    $room_ids = $clipboard->getAllRangeIds('Room');
                }

                $rooms = Resource::findMany($room_ids);
                foreach ($rooms as $room) {
                    $room = $room->getDerivedClassInstance();
                    if ($room instanceof Room) {
                        if ($room->userHasPermission($this->current_user, 'user')) {
                            $this->room_ids[] = $room->id;
                        }
                    }
                }
            }
        }

        //Add the actions widget:

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Export für Word'),
            $this->url_for(
                'room_management/planning/booking_comments',
                [
                    'export' => 'html',
                    'date' => $this->date->format('d.m.Y')
                ]
            ),
            Icon::create('file-text')
        );
        $actions->addLink(
            _('Export als CSV'),
            $this->url_for(
                'room_management/planning/booking_comments',
                [
                    'export' => 'csv',
                    'date' => $this->date->format('d.m.Y')
                ]
            ),
            Icon::create('file-excel')
        );
        $sidebar->addWidget($actions);

        //Calculate week begin and end:
        $week_end = new DateTime();
        $week_end->setTimestamp(strtotime('next sunday', $this->date->getTimestamp()));
        $week_end->setTime(23,59,59);
        $week_begin = clone $week_end;
        $week_begin = $week_begin->sub(new DateInterval('P1W'))->add(new DateInterval('PT1S'));

        //Get bookings:

        $booking_intervals = ResourceBookingInterval::findBySql(
            "INNER JOIN resource_bookings rb
            ON resource_booking_intervals.booking_id = rb.id
            WHERE rb.internal_comment <> ''
            AND rb.resource_id IN ( :room_ids )
            AND (
                resource_booking_intervals.begin BETWEEN :begin AND :end
                OR
                resource_booking_intervals.end BETWEEN :begin AND :end
            )
            ORDER BY resource_booking_intervals.begin ASC, resource_booking_intervals.end ASC",
            [
                'room_ids' => $this->room_ids,
                'begin' => $week_begin->getTimestamp(),
                'end' => $week_end->getTimestamp()
            ]
        );

        //Array structure:
        //Layer 1: keys: resource-IDs, content: Array
        //Layer 2: keys: 0 = name (resource name), 1-7: weekdays: Array
        //Layer 3 (weekdays): Array
        //Layer 4 (weekdays): 0 = time, 1 = comment
        $this->data = [
        ];

        foreach ($booking_intervals as $interval) {
            $l1_index = $interval->booking->resource_id; //Layer 1 index
            if (!is_array($this->data[$l1_index])) {
                $this->data[$l1_index] = [
                    $interval->booking->resource->name,
                    [],
                    [],
                    [],
                    [],
                    [],
                    [],
                    []
                ];
            }

            $booking_text_items = [];
            if ($interval->booking->description) {
                $booking_text_items[] = $interval->booking->description;
            }
            if ($interval->booking->assigned_user instanceof User) {
                $booking_text_items[] =
                    $interval->booking->assigned_user->getFullName();
            }
            if ($interval->booking->internal_comment) {
                $booking_text_items[] = $interval->booking->internal_comment;
            }
            $booking_text_string = implode('; ', $booking_text_items);

            $interval_begin = new DateTime();
            $interval_begin->setTimestamp($interval->begin);
            $interval_end = new DateTime();
            $interval_end->setTimestamp($interval->end);
            $begin_weekday = date('N', $interval->begin);
            $end_weekday = date('N', $interval->end);

            if ($interval_begin->format('Ymd') != $interval_end->format('Ymd')) {
                //The interval is spread over several days.
                //It must be displayd on each of them.
                $current_day = clone $interval_begin;
                if ($interval_begin < $week_begin) {
                    $current_day->setTime(0,0,0);
                    //The interval starts before the current week.
                    //We have to move the current day to the begin
                    //of the week to calculate the begin time.
                    while ($current_day < $week_begin) {
                        $current_day = $current_day->add(
                            new DateInterval('P1D')
                        );
                    }
                }

                //At this point we have reached the begin of the selected week.
                //Add an entry to the data array:
                $l2_index = intval($current_day->format('N'));
                if (!is_array($this->data[$l1_index][$l2_index])) {
                    $this->data[$l1_index][$l2_index] = [];
                }

                if ($current_day->format('Ymd') == $interval_end->format('Ymd')) {
                    //The interval ends on the first day of the week.
                    $time_string = sprintf(
                        _('%1$s - %2$s Uhr'),
                        $current_day->format('H:i'),
                        $interval_end->format('H:i')
                    );
                    $this->data[$l1_index][$l2_index][] = [
                        $time_string,
                        $booking_text_string
                    ];
                    //There is nothing else to do for this interval.
                } else {
                    //The interval ends on another day of the week.
                    $time_string = sprintf(
                        _('%1$s - %2$s Uhr'),
                        $current_day->format('H:i'),
                        '23:59'
                    );

                    $this->data[$l1_index][$l2_index][] = [
                        $time_string,
                        $booking_text_string
                    ];

                    $current_day = $current_day->add(new DateInterval('P1D'));

                    //Now we loop over each day until we have reached the end day.
                    //We compare date strings, because the time may differ
                    //between $current_day and $interval_end.
                    while ($current_day->format('Ymd') < $interval_end->format('Ymd')) {
                        if ($current_day > $week_end) {
                            //out of range
                            break;
                        }
                        if ($current_day >= $week_begin) {
                            $l2_index = intval($current_day->format('N'));
                            if (!is_array($this->data[$l1_index][$l2_index])) {
                                $this->data[$l1_index][$l2_index] = [];
                            }
                            $time_string = sprintf(
                                _('%1$s - %2$s Uhr'),
                                '0:00',
                                '23:59'
                            );
                            $this->data[$l1_index][$l2_index][] = [
                                $time_string,
                                $booking_text_string
                            ];
                        }
                        $current_day = $current_day->add(
                            new DateInterval('P1D')
                        );
                    }
                    if ($current_day->format('Ymd') <= $week_end->format('Ymd')) {
                        //We have reached the last day of the interval,
                        //which lies inside the week.
                        $time_string = sprintf(
                            _('%1$s - %2$s Uhr'),
                            '0:00',
                            $interval_end->format('H:i')
                        );
                        $l2_index = intval($interval_end->format('N'));
                        if (!is_array($this->data[$l1_index][$l2_index])) {
                            $this->data[$l1_index][$l2_index] = [];
                        }
                        $this->data[$l1_index][$l2_index][] = [
                            $time_string,
                            $booking_text_string
                        ];
                    }
                }
            } else {
                $l2_index = intval($begin_weekday); //Layer 2 index
                if (!is_array($this->data[$l1_index][$l2_index])) {
                    $this->data[$l1_index][$l2_index] = [];
                }
                $time_string = sprintf(
                    _('%1$s - %2$s Uhr'),
                    date('H:i', $interval->booking->begin),
                    date('H:i', $interval->booking->end)
                );
                $this->data[$l1_index][$l2_index][] = [
                    $time_string,
                    $booking_text_string
                ];
            }
        }

        //Sort the data array by the room name:
        usort($this->data, function ($a, $b)
            {
                if ($a[0] == $b[0]) {
                    return 0;
                }
                return ($a[0] < $b[0]) ? -1 : 1;
            }
        );

        $export = Request::get('export');
        if ($export == 'html') {
            //Load the export template:
            $factory = new Flexi_TemplateFactory(
                $GLOBALS['STUDIP_BASE_PATH'] . '/app/views/room_management/planning/'
            );

            $template = $factory->open('booking_comments_html_export_frame.php');

            $template->set_attribute(
                'data',
                $this->data
            );
            $template->set_attribute('date', $this->date);

            $html = $template->render();

            $file_name = sprintf(
                _('Buchungen, KW %d.doc'),
                $this->date->format('W')
            );

            $this->set_content_type('application/msword; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $file_name . '"');
            $this->render_text($html);
        } elseif ($export == 'csv') {
            $csv_data = [
                [
                    sprintf(
                        _('%d. Kalenderwoche'),
                        $this->date->format('W')
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Montag'),
                        date(
                            'd.m.Y',
                            strtotime('this week monday', $this->date->getTimestamp())
                        )
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Dienstag'),
                        date(
                            'd.m.Y',
                            strtotime('this week tuesday', $this->date->getTimestamp())
                        )
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Mittwoch'),
                        date(
                            'd.m.Y',
                            strtotime('this week wednesday', $this->date->getTimestamp())
                        )
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Donnerstag'),
                        date(
                            'd.m.Y',
                            strtotime('this week thursday', $this->date->getTimestamp())
                        )
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Freitag'),
                        date(
                            'd.m.Y',
                            strtotime('this week friday', $this->date->getTimestamp())
                        )
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Samstag'),
                        date(
                            'd.m.Y',
                            strtotime('this week saturday', $this->date->getTimestamp())
                        )
                    ),
                    sprintf(
                        '%1$s' . "\n" . '%2$s',
                        _('Sonntag'),
                        date(
                            'd.m.Y',
                            strtotime('this week sunday', $this->date->getTimestamp())
                        )
                    )
                ]
            ];

            foreach ($this->data as $row) {
                $csv_row = [];
                foreach ($row as $i => $cell) {
                    if ($i == 0) {
                        $csv_row[0] = $cell;
                    } else {
                        $csv_row[$i] = '';
                        if ($cell) {
                            $items = [];
                            foreach ($cell as $day_item) {
                                $items[] = $day_item[0] . ': ' . $day_item[1];
                            }
                            $csv_row[$i] = implode("\n\n", $items);
                        }
                    }
                }
                $csv_data[] = $csv_row;
            }

            $this->set_content_type('text/csv');
            $this->render_text(array_to_csv($csv_data));
        }
    }
}
