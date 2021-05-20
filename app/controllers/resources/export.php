<?php

/**
 * requests.php - contains Resources_ExportController
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
 * Resources_ExportController contains functionality for data export
 * for resources and especially rooms, buildings and locations.
 */
class Resources_ExportController extends AuthenticatedController
{
    protected function returnCsvData($data = [], $file_name = '')
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException(
                'Resources_ExportController::returnCsvData requires an array as parameter!'
            );
        }
        $this->render_csv(
            $data,
            $file_name
        );
    }

    protected function returnHtmlData($data = '')
    {
        $this->set_content_type('text/html');
        $this->render_text($data);
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->export_type  = Request::get('export_type', 'default');
        $this->export_param = Request::get('export_param', '');

        if (Navigation::hasItem('/resources/export')) {
            Navigation::activateItem('/resources/export');
        }
    }

    public function select_booking_sources_action($range_with_id = null)
    {
        if (Navigation::hasItem('/resources/export/select_booking_sources')) {
            Navigation::activateItem('/resources/export/select_booking_sources');
        }
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(_('Quellen für den Export von Buchungen auswählen'));

        $this->select_rooms = Request::get('select_rooms');

        if ($this->select_rooms) {
            $this->available_rooms = RoomManager::getUserRooms(
                User::findCurrent()
            );
            if (!$this->available_rooms) {
                PageLayout::postInfo(
                    _('Es sind keine Quellen für den Export von Buchungen vorhanden!')
                );
            }
        } else {
            $this->available_clipboards = Clipboard::getClipboardsForUser(
                $GLOBALS['user']->id,
                ['Room']
            );

            if(!$this->available_clipboards) {
                PageLayout::postInfo(
                    _('Sie müssen zunächst Raumgruppen erstellen'),
                    [
                        sprintf(
                            _('Klicken %shier%s, um ein Raumgruppen anzulegen.'),
                            '<a href="' . URLHelper::getLink('dispatch.php/room_management/overview/rooms') . '">',
                            '</a>')
                    ]
                );
            }
        }


        $this->begin = new DateTime();
        $this->begin->setDate(
            intval(date('Y')),
            intval(date('m')),
            1
        );
        $this->begin->setTime(0, 0, 0);
        $this->end = clone $this->begin;
        $this->end = $this->end->add(
            new DateInterval('P1M')
        )->sub(
            new DateInterval('P1D')
        );
        $this->end->setTime(23, 59, 59);

        $this->range_type = null;
        $this->range_id   = null;

        if (!$range_with_id) {
            //Check if range-ID and range_type are provided in an URL parameter:
            $this->range_type = Request::get('range_type');
            $this->range_id   = Request::get('range_id');
        }

        //Build sidebar

        $sidebar = Sidebar::get();

        $views = new ViewsWidget();
        $views->addLink(
            _('Raumgruppen auswählen'),
            $this->select_booking_sourcesURL()
        )->setActive(!$this->select_rooms);
        $views->addLink(
            _('Räume auswählen'),
            $this->select_booking_sourcesURL(
                [
                    'select_rooms' => '1'
                ]
            )
        )->setActive($this->select_rooms);
        $sidebar->addWidget($views);
    }


    public function resource_bookings_action($resource_id = null)
    {
        PageLayout::setTitle(
            _('Buchungen exportieren')
        );

        if (!$resource_id) {
            PageLayout::postError(
                _('Es wurde keine Ressource ausgewählt!')
            );
            return;
        }

        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Die gewählte Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->resource->getDerivedClassInstance();

        PageLayout::setTitle(
            sprintf(
                '%s: Buchungen exportieren',
                $this->resource->getFullName()
            )
        );

        $this->current_user = User::findCurrent();

        if (!$this->resource->userHasPermission($this->current_user, 'user')) {
            throw new AccessDeniedException();
        }

        $this->begin = new DateTime();
        $this->end   = clone $this->begin;

        $begin_timestamp = Request::get('begin');
        $end_timestamp   = Request::get('end');
        $week_timestamp  = Request::get('timestamp');
        if ($week_timestamp) {
            //Calculate start and end of week:
            $this->begin->setTimestamp($week_timestamp);
            if ($this->begin->format('N') > 1) {
                $this->begin->sub(
                    new DateInterval('P' . ($this->begin->format('N') - 1) . 'D')
                );
            }
            $this->begin->setTime(0, 0);
            $this->end = clone $this->begin;
            $this->end = $this->end->add(
                new DateInterval('P7D')
            )->sub(
                new DateInterval('PT1S')
            );
        } elseif ($begin_timestamp && $end_timestamp) {
            $this->begin->setTimestamp($begin_timestamp);
            $this->end->setTimestamp($end_timestamp);
        } else {
            $this->begin->setDate(
                intval(date('Y')),
                intval(date('m')),
                1
            );
            $this->begin->setTime(0, 0, 0);
            $this->end = clone $this->begin;
            $this->end = $this->end->add(
                new DateInterval('P1M')
            )->sub(
                new DateInterval('P1D')
            );
            $this->end->setTime(23, 59, 59);
        }
    }


    public function bookings_action()
    {
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
        }

        //Get the IDs of all selected clipboards and rooms:
        $this->selected_clipboard_ids = Request::getArray('selected_clipboards');
        $this->selected_room_ids      = Request::getArray('selected_rooms');
        $this->selected_resource_ids  = Request::getArray('selected_resources');

        //Get begin and end date:
        $this->begin = Request::getDateTime(
            'begin_date',
            'd.m.Y',
            'begin_time',
            'H:i'
        );
        $this->end   = Request::getDateTime(
            'end_date',
            'd.m.Y',
            'end_time',
            'H:i'
        );

        if ($this->begin >= $this->end) {
            PageLayout::postError(
                _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
            );
            return;
        }

        //Resolve the IDs to clipboards (or rooms) and build a list of rooms.

        $this->selected_clipboards = Clipboard::findMany(
            $this->selected_clipboard_ids
        );
        $room_sql                  = "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE
            rc.class_name IN ( :room_class_names )
            AND resources.id IN ( :room_ids )
            ORDER BY resources.name ASC";
        $this->selected_rooms      = Room::findBySql(
            $room_sql,
            [
                'room_ids' => $this->selected_room_ids,
                'room_class_names' => RoomManager::getAllRoomClassNames()
            ]
        );
        $this->selected_resources  = Resource::findMany(
            $this->selected_resource_ids
        );

        $resources = [];
        foreach ($this->selected_clipboards as $clipboard) {
            $clipboard_room_ids = $clipboard->getAllRangeIds('Room');
            $clipboard_rooms    = Room::findBySql(
                $room_sql,
                [
                    'room_ids' => $clipboard_room_ids,
                    'room_class_names' => RoomManager::getAllRoomClassNames()
                ]
            );
            $resources          = array_merge($resources, $clipboard_rooms);
        }

        $resources = array_merge($resources, $this->selected_rooms, $this->selected_resources);

        //Collect all bookings in the selected time range and export them.

        $booking_data = [
            [
                _('Beginn'),
                _('Ende'),
                _('Rüstzeit'),
                _('Raumname'),
                _('Buchungstyp'),
                _('Beschreibung'),
                _('Buchende Person'),
                _('Belegende Person(en)'),
                _('Interner Kommentar')
            ]
        ];

        foreach ($resources as $resource) {
            //Retrieve the bookings in the specified time range:
            $bookings = ResourceBooking::findByResourceAndTimeRanges(
                $resource,
                [
                    [
                        'begin' => $this->begin->getTimestamp(),
                        'end'   => $this->end->getTimestamp()
                    ]
                ],
                [0, 1, 2, 3]
            );

            //Prepare data for export:

            foreach ($bookings as $booking) {
                $booking_data[] = [
                    date('d.m.Y H:i', $booking->begin),
                    date('d.m.Y H:i', $booking->end),
                    sprintf(
                        _('%u min.'),
                        intval($booking->preparation_time / 60)
                    ),
                    $booking->resource->name,
                    (
                    $booking->booking_type == '0'
                        ? _('Buchung')
                        : (
                    $booking->booking_type == '1'
                        ? _('Reservierung')
                        : (
                    $booking->booking_type == '2'
                        ? _('Sperrbuchung')
                        : _('sonstiges')
                    )
                    )
                    ),
                    $booking->description,
                    $booking->booking_user ? $booking->booking_user->getFullName() : '',
                    implode(', ', $booking->getAssignedUsers()),
                    $booking->internal_comment
                ];
            }
        }

        $this->returnCsvData(
            $booking_data,
            sprintf(
                '%1$s_%2$s - %3$s-%4$s.csv',
                date('Ymd', time()),
                _('Buchungsliste'),
                $this->begin->format('Ymd_Hi'),
                $this->end->format('Ymd_Hi')
            )
        );
    }


    public function booking_plan_action($resource_id = null)
    {
        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Die angegebene Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->resource->getDerivedClassInstance();

        $user_has_user_permissions = $this->resource->userHasPermission(
            User::findCurrent(),
            'user'
        );
        if (!$user_has_user_permissions) {
            throw new AccessDeniedException();
        }
        if ($this->resource instanceof Room) {
            if (!$this->resource->booking_plan_is_public
                && !$user_has_user_permissions) {
                throw new AccessDeniedException();
            }
        }

        $week_timestamp = Request::get('timestamp', time());

        $this->date = new DateTime();
        $this->date->setTimestamp($week_timestamp);

        $week_begin = clone $this->date;
        $week_begin->sub(
            new DateInterval('P' . abs($this->date->format('N') - 1) . 'D')
        );
        $week_begin->setTime(0, 0, 0);

        $week_end = clone $week_begin;
        $week_end->add(new DateInterval('P7D'))->sub(new DateInterval('PT1S'));

        if ($this->export_type == 'csv') {
            $csv_data = [];

            $csv_data[] = [
                _('Beginn'),
                _('Ende'),
                _('Rüstzeit (Minuten)'),
                (
                $this->resource instanceof Room
                    ? _('Raum / Gebäude')
                    : _('Ressource')
                ),
                _('Buchungstyp'),
                _('Beschreibung'),
                _('Buchende Person'),
                _('Belegende Person(en)'),
                _('Interner Kommentar')
            ];

            //Get booking intervals:
            if ($this->resource instanceof Room) {
                $intervals = RoomManager::getBookingIntervalsForRoom(
                    $this->resource,
                    $week_begin,
                    $week_end,
                    [0, 2],
                    [0, 2]
                );
            } else {
                $intervals = ResourceManager::getBookingIntervalsForResource(
                    $this->resource,
                    $week_begin,
                    $week_end,
                    [0, 2]
                );
            }

            foreach ($intervals as $interval) {
                $resource_name = '';
                if ($interval->resource) {
                    $interval_resource = $interval->resource->getDerivedClassInstance();
                    $resource_name     = $interval_resource->getFullName();
                }

                $booking_type_string = '';
                if ($interval->booking->booking_type == '0') {
                    $booking_type_string = _('Buchung');
                } elseif ($interval->booking->booking_type == '1') {
                    $booking_type_string = _('Reservierung');
                } elseif ($interval->booking->booking_type == '2') {
                    $booking_type_string = _('Sperrbuchung');
                }

                $csv_data[] = [
                    date('d.m.Y H:i', $interval->begin),
                    date('d.m.Y H:i', $interval->end),
                    round($interval->booking->preparation_time / 60),
                    $resource_name,
                    $booking_type_string,
                    $interval->booking->description,
                    (
                    $interval->booking->booking_user instanceof User
                        ? $interval->booking->booking_user->getFullName()
                        : ''
                    ),
                    $interval->booking->getAssignedUserName(),
                    $interval->booking->internal_comment
                ];
            }

            $file_name = sprintf(
                _('Belegungsplan %1$s, KW %2$d.csv'),
                $this->resource->getFullName(),
                $this->date->format('W')
            );
            $this->returnCsvData($csv_data, $file_name);
        } else {
            $this->render_nothing();
        }
    }
}
