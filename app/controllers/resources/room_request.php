<?php

/**
 * request.php - contains Resources_RequestController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2019
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_RequestController contains resource request functionality.
 */
class Resources_RoomRequestController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->current_user = User::findCurrent();

        if (in_array($action, ['overview', 'planning', 'export_list', 'resolve', 'decline'])) {
            $this->current_user = User::findCurrent();
            $user_is_global_resource_autor = ResourceManager::userHasGlobalPermission($this->current_user, 'autor');
            if (!RoomManager::userHasRooms($this->current_user, 'autor', true) && !$user_is_global_resource_autor) {
                throw new AccessDeniedException(_('Ihre Berechtigungen an Räumen reichen nicht aus, um die Anfrageliste anzeigen zu können!'));
            }

            $this->available_rooms = RoomManager::getUserRooms($this->current_user, 'autor', true);
            $this->selected_room_ids = [];

            $this->filter =& $_SESSION[__CLASS__]['filter'];

            if (in_array($action, ['resolve', 'decline'])) {
                $this->filter['get_only_request_ids'] = true;
                $this->filter['filter_request_id'] = $args[0];
            } else {
                $this->filter['get_only_request_ids'] = false;
            }

            if ($action === 'planning') {
                $this->filter['request_periods'] ='periodic';
            }

            if (Request::get('reset_filter')) {
                $this->filter = [
                    'marked'       => -1,
                    'own_requests' => 1
                ];
            } else {
                if (Request::option('institut_id')) {
                    $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', Request::option('institut_id'));
                }
                if (Request::option('semester_id')) {
                    $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_CYCLE', Request::option('semester_id'));
                }
                if (!Semester::find($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE)) {
                    $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE = Semester::findCurrent()->id;
                }
                $this->filter['marked'] = -1;
                $this->filter['own_requests'] = 1;
                if (Request::submitted('marked')) {
                    $this->filter['marked'] = Request::get('marked');
                }
                if (Request::submitted('request_periods')) {
                    $request_periods = Request::get('request_periods');
                    if (in_array($request_periods, ['aperiodic', 'periodic'])) {
                        $this->filter['request_periods'] = $request_periods;
                    } else {
                        $this->filter['request_periods'] = null;
                    }
                }
                if (Request::submitted('toggle_specific_requests')) {
                    $this->filter['specific_requests'] = $this->filter['specific_requests'] ? 0 : 1;
                }
                if (Request::submitted('toggle_own_requests')) {
                    $this->filter['own_requests'] = $this->filter['own_requests'] ? 0 : 1;
                }
                if (Request::submitted('course_type')) {
                    $this->filter['course_type'] = Request::option('course_type');
                    if ($this->filter['course_type'] == 'all') {
                        unset($this->filter['course_type']);
                    }
                }
                if (Request::submitted('group')) {
                    $this->filter['group'] = Request::option('group');
                    if ($this->filter['group'] == 'all') {
                        unset($this->filter['group']);
                    }
                }
                if (Request::submitted('room_id')) {
                    $this->filter['room_id'] = Request::get('room_id');
                    if ($this->filter['room_id'] == 'all') {
                        unset($this->filter['room_id']);
                    }
                }
                if (Request::submitted('dow')) {
                    $this->filter['dow'] = Request::option('dow');
                    if ($this->filter['dow'] == 'all') {
                        unset($this->filter['dow']);
                    }
                }
            }

            $this->selected_room_ids = [];
            if ($this->filter['room_id']) {
                $room = Resource::find($this->filter['room_id']);
                if (!($room instanceof Resource)) {
                    PageLayout::postError(
                        _('Der gewählte Raum wurde nicht gefunden!')
                    );
                    return;
                }
                $room = $room->getDerivedClassInstance();
                if (!($room instanceof Room)) {
                    PageLayout::postError(
                        _('Es wurde kein Raum ausgewählt!')
                    );
                    return;
                }
                if (!$room->userHasPermission($this->current_user, 'autor', [])) {
                    PageLayout::postError(
                        sprintf(
                            _('Die Berechtigungen für den Raum %s sind nicht ausreichend, um die Anfrageliste anzeigen zu können!'),
                            htmlReady($room->name)
                        )
                    );
                    return;
                }
                $this->selected_room_ids = [$room->id];
            } elseif ($this->filter['group']) {
                //Filter rooms by the selected room group:
                $clipboard = Clipboard::find($this->filter['group']);
                if (!($clipboard instanceof Clipboard)) {
                    PageLayout::postError(
                        _('Die gewählte Raumgruppe wurde nicht gefunden!')
                    );
                    return;
                }
                $room_ids = $clipboard->getAllRangeIds('Room');
                if (!$room_ids) {
                    PageLayout::postError(
                        _('Die gewählte Raumgruppe enthält keine Räume!')
                    );
                    return;
                }
                $rooms = Resource::findMany($room_ids);
                foreach ($rooms as $room) {
                    $room = $room->getDerivedClassInstance();
                    if ($room instanceof Room) {
                        $this->selected_room_ids[] = $room->id;
                    }
                }
            } else {
                //No filter for a room or a room group set:
                //Display requests of all available rooms:
                foreach ($this->available_rooms as $room) {
                    $this->selected_room_ids[] = $room->id;
                }
            }

            //At this point, only the "marked" filter is definetly set.
            //If more filters are set, we must show the reset button.
            $this->show_filter_reset_button = count($this->filter) > 2;

            //The following filters are special:
            $this->filter['semester'] = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
            $this->filter['institute'] = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;
            $this->entries_per_page = Config::get()->ENTRIES_PER_PAGE;
        }
    }

    /**
     * @return array
     */
    protected function getFilteredRoomRequests()
    {
        $sql = "resource_requests.closed < '1' AND (resource_id IN ( :room_ids) ";
        $sql_params = [
            'room_ids' => $this->selected_room_ids
        ];
        if (!$this->filter['specific_requests']) {
            $sql .= "OR resource_id IS NULL or resource_id = ''";
        }
        $sql .= ') ';
        if (!$this->filter['own_requests']) {
            $sql .= " AND resource_requests.user_id <> :current_user_id ";
            $sql_params['current_user_id'] = User::findCurrent()->id;
        }

        if ($this->filter['request_periods'] == 'periodic') {
            $sql .= " AND resource_requests.termin_id = '' ";
        }
        if ($this->filter['request_periods'] == 'aperiodic') {
            $sql .= " AND resource_requests.termin_id <> '' ";
        }


        $institute_id = $this->filter['institute'];
        if ($institute_id) {
            $common_seminar_sql = 'resource_requests.course_id IN (
                SELECT seminar_id FROM seminare
                WHERE %1$s
                )';
            $include_children = false;
            if (preg_match('/_withinst$/', $institute_id)) {
                $include_children = true;
                $institute_id = explode('_', $institute_id)[0];
            }
            $institute = Institute::find($institute_id);
            if ($institute instanceof Institute) {
                $institute_ids = [$institute->id];
                if ($institute->isFaculty() && $include_children) {
                    //Get the requests from courses from the faculty
                    //and its institutes.
                    foreach ($institute->sub_institutes as $sub_inst) {
                        $institute_ids[] = $sub_inst->id;
                    }
                }

                if ($sql) {
                    $sql .= ' AND ';
                }
                $sql .= sprintf(
                    $common_seminar_sql,
                    'seminare.institut_id IN ( :institute_ids )'
                );
                $sql_params['institute_ids'] = $institute_ids;
            }
        }

        if ($this->filter['marked'] > -1 && $this->filter['marked'] < ResourceRequest::MARKING_STATES) {
            if ($sql) {
                $sql .= ' AND ';
            }
            if ($this->filter['marked'] == 0) {
                $sql .= "resource_requests.marked = '0' ";
            } else {
                $sql .= "(resource_requests.marked > '0' && resource_requests.marked < :max_marked) ";
                $sql_params['max_marked'] = ResourceRequest::MARKING_STATES;
            }
        }

        $semester_id = $this->filter['semester'];
        if ($semester_id) {
            $semester = Semester::find($semester_id);
            if ($semester instanceof Semester) {
                if ($sql) {
                    $sql .= ' AND ';
                }
                $sql .= "(
                (resource_requests.termin_id <> '' AND EXISTS (SELECT * FROM termine WHERE termine.termin_id=resource_requests.termin_id AND termine.date BETWEEN :semester_begin AND :semester_end))
                    OR
                    (resource_requests.metadate_id <> '' AND EXISTS (SELECT * FROM termine WHERE termine.metadate_id=resource_requests.metadate_id AND termine.date BETWEEN :semester_begin AND :semester_end))
                    OR
                    (resource_requests.termin_id = '' AND resource_requests.metadate_id = '' AND EXISTS (SELECT * FROM termine WHERE termine.range_id=resource_requests.course_id AND termine.date BETWEEN :semester_begin AND :semester_end))
                     ";

                if (!$this->filter['request_periods']) {
                    $sql .= ' OR (
                            ((CAST(resource_requests.begin AS SIGNED) - resource_requests.preparation_time)
                             BETWEEN :semester_begin AND :semester_end)
                            OR
                            (resource_requests.end BETWEEN :semester_begin AND :semester_end)
                        )';
                }
                $sql .= ') ';
                $sql_params['semester_begin'] = $semester->beginn;
                $sql_params['semester_end'] = $semester->ende;
            }
        }
        if (!empty($this->filter['course_type'])) {
            $course_type = explode('_', $this->filter['course_type']);
            if (empty($course_type[1])) {
                $course_types = array_keys(SemClass::getClasses()[$course_type[0]]->getSemTypes());
            } else {
                $course_types = [$course_type[1]];
            }
            $sql .= " AND EXISTS (SELECT * FROM seminare
                    WHERE resource_requests.course_id=seminare.seminar_id
                    AND seminare.status IN(:course_types)) ";
            $sql_params[':course_types'] = $course_types;
        }

        if (!$sql) {
            //No filtering done
            $sql = 'TRUE ';
        }

        $sql .= " GROUP BY resource_requests.id ORDER BY mkdate ASC";

        $requests = RoomRequest::findBySql($sql, $sql_params);
        $result = [];
        if (!empty($this->filter['dow'])) {
            $week_days = [$this->filter['dow']];
            foreach ($requests as $request) {
                $time_intervals = $request->getTimeIntervals(true);

                //Check for each time interval if it lies in at least one
                //of the specified week days.
                foreach ($time_intervals as $time_interval) {
                    $interval_weekdays = []; //The weekdays of the time interval.
                    $weekday_begin = date('N', $time_interval['begin']);
                    $weekday_end = date('N', $time_interval['end']);
                    $interval_weekdays[] = $weekday_begin;
                    if (($weekday_end - $weekday_begin) != 0) {
                        $interval_weekdays[] = $weekday_end;
                        $current_day = $weekday_begin;
                        //In case the end lies on a day after the begin
                        //or even in another week, we must loop until
                        //we reached the weekday of the end timestamp.
                        while ($current_day != $weekday_end) {
                            $interval_weekdays[] = $current_day;
                            if ($current_day == 7) {
                                $current_day = 1;
                            } else {
                                $current_day++;
                            }
                        }
                    }
                    array_unique($interval_weekdays);
                    //We have all relevant weekdays and can now check
                    //if the time interval lies in one of the relevant weekdays:
                    foreach ($interval_weekdays as $iwd) {
                        if (in_array($iwd, $week_days)) {
                            //Add the request to the result set. By using the
                            //request-ID as key, we can make sure one request
                            //is only added once to the result set.
                            $result[$request->id] = $request;
                            //Continue to the next request:
                            continue 2;
                        }
                    }
                }
            }
            if ($this->filter['get_only_request_ids']) {
                return array_keys($result);
            }
        } else {
            $result = $requests;
            if ($this->filter['get_only_request_ids']) {
                return SimpleCollection::createFromArray($requests)->pluck('id');
            }
        }
        return $result;
    }


    protected function getRoomAvailability(Room $room, $time_intervals = [])
    {
        $availability = [];
        foreach ($time_intervals as $interval) {
            $begin = new DateTime();
            $end = new DateTime();
            $begin->setTimestamp($interval['begin']);
            $end->setTimestamp($interval['end']);
            $availability[] = $room->isAvailable($begin, $end);
        }
        return $availability;
    }


    /**
     * Shows all requests. By default, only open requests are shown.
     */
    public function overview_action(int $page = 0)
    {
        if (Navigation::hasItem('/resources/planning/requests_overview')) {
            Navigation::activateItem('/resources/planning/requests_overview');
        }

        PageLayout::setTitle(_('Anfragenliste'));

        $sidebar = Sidebar::get();

        if ($this->show_filter_reset_button) {
            $filter_reset_widget = new ActionsWidget();
            $filter_reset_widget->addLink(
                _('Filter zurücksetzen'),
                $this->overviewURL(['reset_filter' => '1']),
                Icon::create('filter+decline')
            );
            $sidebar->addWidget($filter_reset_widget);
        }

        $institute_selector = new InstituteSelectWidget(
            '',
            'institut_id',
            'get'
        );
        $institute_selector->includeAllOption();
        $institute_selector->setSelectedElementIds($this->filter['institute']);
        $sidebar->addWidget($institute_selector);

        $semester_selector = new SemesterSelectorWidget(
            '',
            'semester_id',
            'get'
        );
        $semester_selector->setSelection($this->filter['semester']);
        $sidebar->addWidget($semester_selector);

        $list = new SelectWidget(
            _('Veranstaltungstypfilter'),
            $this->overviewURL(),
            'course_type'
        );
        $list->addElement(
            new SelectElement(
                'all',
                _('Alle'),
                empty($this->filter['course_type'])
            ),
            'course-type-all'
        );

        foreach (SemClass::getClasses() as $class_id => $class) {
            if ($class['studygroup_mode']) {
                continue;
            }

            $element = new SelectElement(
                $class_id,
                $class['name'],
                $this->filter['course_type'] === (string)$class_id
            );
            $list->addElement(
                $element->setAsHeader(),
                'course-type-' . $class_id
            );

            foreach ($class->getSemTypes() as $id => $result) {
                $element = new SelectElement(
                    $class_id . '_' . $id,
                    $result['name'],
                    $this->filter['course_type'] === $class_id . '_' . $id
                );
                $list->addElement(
                    $element->setIndentLevel(1),
                    'course-type-' . $class_id . '_' . $id
                );
            }
        }
        $sidebar->addWidget($list, 'filter-course-type');

        $widget = new SelectWidget(
            _('Raumgruppen'),
            $this->overviewURL(),
            'group'
        );
        $widget->addElement(
            new SelectElement(
                '',
                _('Alle'),
                empty($this->filter['group'])
            ),
            'clip-all'
        );
        foreach (Clipboard::getClipboardsForUser($GLOBALS['user']->id, ['Room']) as $clip) {
            $widget->addElement(
                new SelectElement(
                    $clip->id,
                    $clip->name,
                    $this->filter['group'] == $clip->id
                ),
                'clip-' . $clip->id
            );
        }
        $sidebar->addWidget($widget);

        $widget = new SelectWidget(
            _('Räume'),
            $this->overviewURL(),
            'room_id'
        );
        $widget->addElement(
            new SelectElement(
                '',
                _('bitte wählen'),
                !$this->filter['room_id']
            )
        );
        foreach ($this->available_rooms as $room) {
            $widget->addElement(
                new SelectElement(
                    $room->id,
                    $room->name,
                    $room->id == $this->filter['room_id']
                )
            );
        }
        $sidebar->addWidget($widget);

        $widget = new OptionsWidget(_('Filter'));
        $widget->addRadioButton(
            _('Alle Anfragen'),
            $this->overviewURL($this->filter['marked'] != '-1' ? ['marked' => '-1'] : []),
            $this->filter['marked'] == -1
        );
        $widget->addRadioButton(
            _('Nur markierte Anfragen'),
            $this->overviewURL($this->filter['marked'] != '1' ? ['marked' => '1'] : []),
            $this->filter['marked'] == 1
        );
        $widget->addRadioButton(
            _('Nur unmarkierte Anfragen'),
            $this->overviewURL($this->filter['marked'] != '0' ? ['marked' => '0'] : []),
            $this->filter['marked'] == 0
        );
        $widget->addElement(new WidgetElement('<br>'));

        $widget->addRadioButton(
            _('Alle Termine'),
            $this->overviewURL(['request_periods' => '0']),
            !$this->filter['request_periods']
        );
        $widget->addRadioButton(
            _('Nur regelmäßige Termine'),
            $this->overviewURL(['request_periods' => 'periodic']),
            $this->filter['request_periods'] == 'periodic'
        );
        $widget->addRadioButton(
            _('Nur unregelmäßige Termine'),
            $this->overviewURL(['request_periods' => 'aperiodic']),
            $this->filter['request_periods'] == 'aperiodic'
        );
        $widget->addElement(new WidgetElement('<br>'));
        $widget->addCheckbox(
            _('Nur mit Raumangabe'),
            $this->filter['specific_requests'],
            $this->overviewURL(['toggle_specific_requests' => 1])
        );
        $widget->addCheckbox(
            _('Eigene Anfragen anzeigen'),
            $this->filter['own_requests'],
            $this->overviewURL(['toggle_own_requests' => 1])
        );
        $sidebar->addWidget($widget);

        $dow_selector = new SelectWidget(
            _('Wochentag'),
            $this->overviewURL(),
            'dow');
        $dow_selector->addElement(
            new SelectElement(
                'all', _('Alle'), empty($this->filter['dow'])
            ),
            'dow-all'
        );
        foreach (range(1, 7) as $day) {
            $dow_selector->addElement(new SelectElement(
                $day, strftime('%A', strtotime('this monday +' . ($day - 1) . ' day')), $this->filter['dow'] == $day
            ), 'dow-' . $day);
        }
        $sidebar->addWidget($dow_selector, 'filter-dow');

        $relevant_export_url_params = [
            'institut_id'               => 'institute',
            'semester_id'               => 'semester',
            'course_type'               => 'course_type',
            'group'                     => 'group',
            'room_id'                   => null,
            'marked'                    => 'marked',
            'request_periods'           => 'request_periods',
            'toggle_specific_requests'  => 'specific_requests',
            'dow'                       => 'dow'
        ];
        $export_url_params = [];
        foreach ($relevant_export_url_params as $param => $filter_name) {
            if (Request::submitted($param)) {
                $export_url_params[$param] = Request::get($param);
            } elseif (isset($this->filter[$filter_name])) {
                $export_url_params[$param] = $this->filter[$filter_name];
            }
        }
        $export = new ExportWidget();
        $export->addLink(
            _('Gefilterte Anfragen'),
            $this->export_listURL($export_url_params),
            Icon::create('file-excel')
        );
        $export->addLink(
            _('Alle Anfragen'),
            $this->export_listURL(),
            Icon::create('file-excel')
        );
        $sidebar->addWidget($export);

        $requests = $this->getFilteredRoomRequests();
        $this->count_requests = count($requests);
        $requests = array_slice(
            $requests,
            $this->entries_per_page * ($page - 1),
            $this->entries_per_page
        );

        $this->pagination = Pagination::create(
            $this->count_requests,
            $page,
            $this->entries_per_page
        );
        $this->requests = $requests;
    }

    public function index_action($request_id = null)
    {
        $this->request = ResourceRequest::find($request_id);
        if (!$this->request) {
            PageLayout::postError(
                _('Die angegebene Anfrage wurde nicht gefunden!')
            );
            return;
        }
        $this->request = $this->request->getDerivedClassInstance();
        $this->resource = $this->request->resource;
        if (!$this->resource) {
            PageLayout::postError(
                _('Die angegebene Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->resource->getDerivedClassInstance();

        PageLayout::setTitle(
            sprintf(
                _('%s: Details zur Anfrage'),
                $this->resource->getFullName()
            )
        );
    }

    /**
     * This action handles resource requests that are not bound
     * to a course or another Stud.IP object.
     */
    public function add_action($resource_id = null)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }
        $this->resource = null;
        $this->request = null;
        $this->show_form = false;
        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Die angegebene Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->resource->getDerivedClassInstance();
        PageLayout::setTitle(
            sprintf(
                _('%s: Neue Anfrage erstellen'),
                $this->resource->getFullName()
            )
        );
        $current_user = User::findCurrent();
        if (!$this->resource->userHasRequestRights($current_user)) {
            throw new AccessDeniedException();
        }
        $this->form_action_link = $this->link_for('resources/room_request/add/' . $this->resource->id);
        if (!($this->resource instanceof Room)) {
            PageLayout::postError(
                _('Die angegebene Ressource ist kein Raum!')
            );
            return;
        }
        if (!$this->resource->requestable) {
            PageLayout::postError(
                _('Die angegebene Ressource kann nicht angefragt werden!')
            );
            return;
        }


        //Check if the resource is a room and if the room is part of a
        //separable room.
        if ($this->resource instanceof Room) {
            $separable_room = SeparableRoom::findByRoomPart($this->resource);
            if ($separable_room instanceof SeparableRoom) {
                //We must display a warning. But first we check,
                //if we can get the other room parts so that
                //we can make a more informative message.

                $other_room_parts = $separable_room->findOtherRoomParts(
                    [$this->resource]
                );

                if ($other_room_parts) {

                    $other_room_links = [];
                    foreach ($other_room_parts as $room_part) {
                        $other_room_links[] = sprintf(
                            '<a target="_blank" href="%1$s">%2$s</a>',
                            $room_part->getActionLink('show'),
                            htmlReady($room_part->name)
                        );
                    }
                    PageLayout::postInfo(
                        sprintf(
                            _('Der Raum %1$s ist ein Teilraum des Raumes %2$s. Weitere Teilräume sind:'),
                            htmlReady($this->resource->name),
                            htmlReady($separable_room->name)
                        ),
                        $other_room_links
                    );
                } else {
                    PageLayout::postInfo(
                        sprintf(
                            _('Der Raum %1$s ist ein Teilraum des Raumes %2$s.'),
                            htmlReady($this->resource->name),
                            htmlReady($separable_room->name)
                        )
                    );
                }
            }
        }

        $begin = new DateTime();
        $end = new DateTime();

        //Check if a begin and end timestamp are specified:
        if (!Request::submitted('save') && Request::submitted('begin')
            && Request::submitted('end')) {
            $begin->setTimestamp(Request::get('begin'));
            $end->setTimestamp(Request::get('end'));
        } else {
            //Assume a date is requested for tomorrow.
            //Round the current time to hours and substract
            //two hours from $begin.
            $begin->add(new DateInterval('P1D'));
            $begin->setTime(
                $begin->format('H'),
                0
            );
            $end = clone($begin);
            $begin->sub(new DateInterval('PT2H'));
        }

        $config = Config::get();
        $this->begin_date_str = $begin->format('d.m.Y');
        $this->begin_time_str = $begin->format('H:i');
        $this->end_date_str = $end->format('d.m.Y');
        $this->end_time_str = $end->format('H:i');
        $this->preparation_time = 0;
        $this->max_preparation_time = $config->RESOURCES_MAX_PREPARATION_TIME;
        $this->comment = '';

        $this->show_form = true;

        if (Request::submitted('save')) {
            //Get the requested time range and check if the resource
            //is available in that time range. If so, create a new
            //resource request (or a room request if the resource
            //is a room).

            $this->begin_date_str = Request::get('begin_date');
            $this->begin_time_str = Request::get('begin_time');
            $this->end_date_str = Request::get('end_date');
            $this->end_time_str = Request::get('end_time');
            $this->preparation_time = Request::get('preparation_time');

            if (!$this->begin_date_str) {
                PageLayout::postError(
                    _('Es wurde kein Startdatum angegeben!')
                );
                return;
            }
            if (!$this->begin_time_str) {
                PageLayout::postError(
                    _('Es wurde kein Startzeitpunkt angegeben!')
                );
                return;
            }
            if (!$this->end_date_str) {
                PageLayout::postError(
                    _('Es wurde kein Enddatum angegeben!')
                );
                return;
            }
            if (!$this->end_time_str) {
                PageLayout::postError(
                    _('Es wurde kein Endzeitpunkt angegeben!')
                );
                return;
            }
            if ($this->preparation_time > $this->max_preparation_time) {
                PageLayout::postError(
                    sprintf(
                        _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                        $this->max_preparation_time
                    )
                );
            }

            //Comment is optional.
            $this->comment = Request::get('comment');

            //Convert the date and time strings to DateTime objects:

            $begin_date_arr = explode('.', $this->begin_date_str);
            $begin_time_arr = explode(':', $this->begin_time_str);
            $end_date_arr = explode('.', $this->end_date_str);
            $end_time_arr = explode(':', $this->end_time_str);

            $new_begin = new DateTime();
            $new_begin->setDate(
                $begin_date_arr[2],
                $begin_date_arr[1],
                $begin_date_arr[0]
            );
            $new_begin->setTime(
                $begin_time_arr[0],
                $begin_time_arr[1],
                $begin_time_arr[2]
            );
            $new_end = new DateTime();
            $new_end->setDate(
                $end_date_arr[2],
                $end_date_arr[1],
                $end_date_arr[0]
            );
            $new_end->setTime(
                $end_time_arr[0],
                $end_time_arr[1],
                $end_time_arr[2]
            );

            try {
                //All checks are done in Resource::createSimpleRequest.
                $request = $this->resource->createSimpleRequest(
                    User::findCurrent(),
                    $new_begin,
                    $new_end,
                    $this->comment,
                    $this->preparation_time * 60
                );

                if ($request) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Die Anfrage wurde gespeichert.')
                    );
                }
            } catch (Exception $e) {
                PageLayout::postError($e->getMessage());
            }
            //All other exceptions are not caught since they are not thrown
            //in Resource::createSimpleRequest.
        }
    }


    public function edit_action($request_id = null)
    {
        $this->resource = null;
        $this->request = null;
        $this->show_form = false;

        $this->request = ResourceRequest::find($request_id);
        if (!$this->request) {
            PageLayout::postError(
                _('Die angegebene Anfrage wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->request->resource;
        if (!$this->resource) {
            PageLayout::postError(
                _('Die angegebene Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->resource->getDerivedClassInstance();

        PageLayout::setTitle(
            sprintf(
                _('%s: Neue Anfrage erstellen'),
                $this->resource->getFullName()
            )
        );
        $this->form_action_link = $this->link_for(
            'resources/room_request/edit/' . $this->request->id
        );

        if (!($this->resource instanceof Room)) {
            PageLayout::postError(
                _('Die angegebene Ressource ist kein Raum!')
            );
            return;
        }

        $current_user = User::findCurrent();

        //Since all Stud.IP users are allowed to create requests,
        //there is no restriction for creating requests.
        $user_may_edit_request = $this->resource->userHasPermission(
                $current_user,
                'autor'
            ) || $this->request->user_id == $current_user->id;

        if (!$user_may_edit_request) {
            throw new AccessDeniedException();
        }

        //Check if the resource is a room and if the room is part of a
        //separable room.
        if ($this->resource instanceof Room) {
            $separable_room = SeparableRoom::findByRoomPart($this->resource);
            if ($separable_room instanceof SeparableRoom) {
                //We must display a warning. But first we check,
                //if we can get the other room parts so that
                //we can make a more informative message.

                $other_room_parts = $separable_room->findOtherRoomParts(
                    [$this->resource]
                );

                if ($other_room_parts) {

                    $other_room_links = [];
                    foreach ($other_room_parts as $room_part) {
                        $other_room_links[] = sprintf(
                            '<a target="_blank" href="%1$s">%2$s</a>',
                            $room_part->getActionLink('show'),
                            htmlReady($room_part->name)
                        );
                    }
                    PageLayout::postInfo(
                        sprintf(
                            _('Der Raum %1$s ist ein Teilraum des Raumes %2$s. Weitere Teilräume sind:'),
                            htmlReady($this->resource->name),
                            htmlReady($separable_room->name)
                        ),
                        $other_room_links
                    );
                } else {
                    PageLayout::postInfo(
                        sprintf(
                            _('Der Raum %1$s ist ein Teilraum des Raumes %2$s.'),
                            htmlReady($this->resource->name),
                            htmlReady($separable_room->name)
                        )
                    );
                }
            }
        }

        $begin = new DateTime();
        $end = new DateTime();

        //Get begin and end date from the request:
        $begin->setTimestamp($this->request->begin);
        $end->setTimestamp($this->request->end);

        $config = Config::get();
        $this->begin_date_str = $begin->format('d.m.Y');
        $this->begin_time_str = $begin->format('H:i');
        $this->end_date_str = $end->format('d.m.Y');
        $this->end_time_str = $end->format('H:i');
        $this->preparation_time = 0;
        $this->max_preparation_time = $config->RESOURCES_MAX_PREPARATION_TIME;
        $this->comment = '';
        $this->comment = $this->request->comment;
        $this->preparation_time = intval($this->request->preparation_time / 60);

        $this->show_form = true;

        if (Request::submitted('save')) {
            //Get the requested time range and check if the resource
            //is available in that time range. If so, create a new
            //resource request (or a room request if the resource
            //is a room).

            $this->begin_date_str = Request::get('begin_date');
            $this->begin_time_str = Request::get('begin_time');
            $this->end_date_str = Request::get('end_date');
            $this->end_time_str = Request::get('end_time');
            $this->preparation_time = Request::get('preparation_time');

            if (!$this->begin_date_str) {
                PageLayout::postError(
                    _('Es wurde kein Startdatum angegeben!')
                );
                return;
            }
            if (!$this->begin_time_str) {
                PageLayout::postError(
                    _('Es wurde kein Startzeitpunkt angegeben!')
                );
                return;
            }
            if (!$this->end_date_str) {
                PageLayout::postError(
                    _('Es wurde kein Enddatum angegeben!')
                );
                return;
            }
            if (!$this->end_time_str) {
                PageLayout::postError(
                    _('Es wurde kein Endzeitpunkt angegeben!')
                );
                return;
            }
            if ($this->preparation_time > $this->max_preparation_time) {
                PageLayout::postError(
                    sprintf(
                        _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                        $this->max_preparation_time
                    )
                );
            }

            //Comment is optional.
            $this->comment = Request::get('comment');

            //Convert the date and time strings to DateTime objects:

            $begin_date_arr = explode('.', $this->begin_date_str);
            $begin_time_arr = explode(':', $this->begin_time_str);
            $end_date_arr = explode('.', $this->end_date_str);
            $end_time_arr = explode(':', $this->end_time_str);

            $new_begin = new DateTime();
            $new_begin->setDate(
                $begin_date_arr[2],
                $begin_date_arr[1],
                $begin_date_arr[0]
            );
            $new_begin->setTime(
                $begin_time_arr[0],
                $begin_time_arr[1],
                $begin_time_arr[2]
            );
            $new_end = new DateTime();
            $new_end->setDate(
                $end_date_arr[2],
                $end_date_arr[1],
                $end_date_arr[0]
            );
            $new_end->setTime(
                $end_time_arr[0],
                $end_time_arr[1],
                $end_time_arr[2]
            );

            $this->request->begin = $new_begin->getTimestamp();
            $this->request->end = $new_end->getTimestamp();
            $this->request->comment = $this->comment;
            $this->request->preparation_time = $this->preparation_time * 60;

            if ($this->request->isDirty()) {
                $successfully_stored = $this->request->store();
            } else {
                $successfully_stored = true;
            }
            if ($successfully_stored) {
                $this->show_form = false;
                PageLayout::postSuccess(
                    _('Die Anfrage wurde gespeichert.')
                );
            } else {
                PageLayout::postError(
                    _('Die Anfrage konnte nicht gespeichert werden.')
                );
            }
        }
    }


    public function delete_action($request_id = null)
    {
        $this->request = ResourceRequest::find($request_id);
        if (!$this->request) {
            PageLayout::postError(
                _('Die angegebene Anfrage wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->request->resource;

        PageLayout::setTitle(
            sprintf(
                _('%s: Anfrage löschen'),
                $this->resource->getFullName()
            )
        );

        $user_may_delete_request = ResourceManager::userHasGlobalPermission(
                $this->current_user,
                'autor'
            ) || $this->request->user_id == $this->current_user->id;

        if (!$user_may_delete_request) {
            throw new AccessDeniedException();
        }

        $this->show_form = true;

        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->request->delete()) {
                $this->show_form = false;
                PageLayout::postSuccess(
                    _('Die Anfrage wurde gelöscht!')
                );
            } else {
                PageLayout::postError(
                    _('Fehler beim Löschen der Anfrage!')
                );
            }
        }
    }


    /**
     * This action displays information about a request
     * that are relevant before resolving it.
     * The view of this action redirects to resources/booking/add
     * when one wishes to book a room.
     */
    public function resolve_action($request_id = null)
    {
        $this->show_info = false;
        $this->config = Config::get();
        $this->request = ResourceRequest::find($request_id);
        if (!$this->request) {
            PageLayout::postError(
                _('Die angegebene Anfrage wurde nicht gefunden!')
            );
            return;
        }
        $user_has_permission = false;
        $this->user_is_global_autor = ResourceManager::userHasGlobalPermission(
            $this->current_user,
            'autor'
        );

        if (!Request::submitted('single-request')) {
            $request_ids = $this->getFilteredRoomRequests();
            if ($request_ids && count($request_ids) > 1) {
                $this->setRequestForPagination($request_ids);
            }
        }
        //$this->current_user is set in the before_filter.

        $this->request_resource = null;
        if ($this->request->resource instanceof Resource) {
            $this->request_resource = $this->request->resource->getDerivedClassInstance();
        }

        if ($this->request_resource instanceof Resource) {
            //The user must have permanent autor permissions
            //for the selected room:
            $user_has_permission = $this->request_resource->userHasPermission(
                $this->current_user,
                'autor',
                [],
                true
            );
            PageLayout::setTitle(
                sprintf(
                    _('%s: Anfrage auflösen'),
                    $this->request_resource->getFullName()
                )
            );
        } else {
            PageLayout::setTitle(
                _('Anfrage auflösen')
            );
        }
        if (!$user_has_permission && !$this->user_is_global_autor) {
            throw new AccessDeniedException();
        }

        $this->show_info = true;

        if ($this->request->closed > 0) {
            PageLayout::postInfo(
                _('Die Anfrage wurde bereits aufgelöst!')
            );
            $this->show_form = false;
            return;
        }

        $this->notification_settings = 'creator';
        if ($this->request->reply_recipients == 'lecturer') {
            $this->notification_settings = 'creator_and_lecturers';
        }
        $this->reply_comment = $this->request->reply_comment;

        $this->expand_metadates = (Request::submitted('expand_metadates') || Request::option('force_expand_metadates')) && !Request::submitted('fold_metadates');
        $this->show_expand_metadates_button = false;

        $this->request_time_intervals = [];

        if ($this->expand_metadates) {
            //Get all single dates directly, ordered by date.
            $this->request_time_intervals = [
                '' => [
                    'metadate'  => null,
                    'intervals' => $this->request->getTimeIntervals(true, true)
                ]
            ];
        } else {
            //Get dates grouped by metadates.
            $this->request_time_intervals = $this->request->getGroupedTimeIntervals(true);
        }
        $this->request_semester_string = '';
        $request_start_semester = $this->request->getStartSemester();
        $request_end_semester = $this->request->getEndSemester();
        if ($request_start_semester->id != $request_end_semester->id && $request_end_semester->id) {
            $this->request_semester_string = sprintf(
                '%1$s - %2$s',
                $request_start_semester->name,
                $request_end_semester->name
            );
        } else {
            $this->request_semester_string = $request_start_semester->name;
        }

        $this->metadate_available = [];
        $this->room_availability = [];
        $this->room_underload = [];
        $this->requested_room_fully_available = true;
        $this->room_availability_share = [];
        $this->selected_rooms = [];

        //Load all previously selected rooms where at least one date has been
        //assigned to to the list of alternative rooms and place them
        //at the top of the list.
        if (Request::isPost()) {
            $this->selected_rooms = Request::getArray('selected_rooms');
        }

        $selected_room = $this->request_resource;
        $this->visible_dates = 0;
        if ($selected_room instanceof Room) {
            $this->metadate_available[$selected_room->id] = [];
            $this->room_availability[$selected_room->id] = [];
            $this->room_availability_share[$selected_room->id] = 1.0;
            foreach ($this->request_time_intervals as $metadate_id => $data) {
                if ($data['metadate'] instanceof SeminarCycleDate && !$this->expand_metadates) {
                    //There is at least one metadate in the grouped set
                    //of time intervals. The expand button must be shown.
                    $this->show_expand_metadates_button = true;
                    $this->visible_dates++;
                    $metadate_availability = $this->getRoomAvailability(
                        $selected_room,
                        $data['intervals']
                    );
                    $metadate_available = true;
                    foreach ($metadate_availability as $available) {
                        if (!$available) {
                            $metadate_available = false;
                            break;
                        }
                    }
                    if ($metadate_available && !$this->selected_rooms) {
                        $this->selected_rooms['SeminarCycleDate_' . $metadate_id] = $selected_room->id;
                    }
                    $this->room_availability[$selected_room->id][$metadate_id] = [$metadate_available];
                } else {
                    $this->visible_dates += count($data['intervals']);
                    $metadate_availability = [];
                    foreach ($data['intervals'] as $interval) {
                        $interval_available = $this->getRoomAvailability(
                            $selected_room,
                            [$interval]
                        );
                        if ($interval_available[0]) {
                            $range_index = $interval['range'] . '_' . $interval['range_id'];
                            $this->selected_rooms[$range_index] = $selected_room->id;
                        }
                        $metadate_availability[] = $interval_available[0];
                    }
                    $this->room_availability[$selected_room->id][$metadate_id] = $metadate_availability;
                }
            }
            if ($this->request->getProperty('seats') > 0) {
                $this->room_underload[$selected_room->id] =
                    round(((int)$selected_room->seats / (int)$this->request->getProperty('seats')) * 100);
            }
            $amount_of_dates = count(reset($this->room_availability));
            foreach ($this->room_availability[$selected_room->id] as $metadate_id => $metadate_availability) {
                $this->metadate_available[$selected_room->id][$metadate_id] = true;
                $unavailable_c = 0;
                foreach ($metadate_availability as $available) {
                    if (!$available) {
                        $this->requested_room_fully_available = false;
                        $this->metadate_available[$selected_room->id][$metadate_id] = false;
                        $unavailable_c++;
                    }
                }
                $this->room_availability_share[$selected_room->id] =
                    ($amount_of_dates - $unavailable_c) / $amount_of_dates;
            }
        } else {
            //If no room is selected, it cannot be declared fully available.
            $this->requested_room_fully_available = false;
            $this->room_availability_share[$selected_room->id] = 0.0;
            foreach ($this->request_time_intervals as $data) {
                if ($data['metadate'] instanceof SeminarCycleDate && !$this->expand_metadates) {
                    $this->show_expand_metadates_button = true;
                    $this->visible_dates++;
                    break;
                } else {
                    $this->visible_dates += count($data['intervals']);
                }
            }
        }

        //Load the room groups of the current user:

        $this->clipboards = Clipboard::getClipboardsForUser($this->current_user->id, ['Room']);

        $this->selected_clipboard_id = Request::get('selected_clipboard_id');
        if (!$this->selected_clipboard_id) {
            if (count($this->clipboards) > 0) {
                $this->selected_clipboard_id = $this->clipboards[0]->id;
            }
        }

        $this->alternatives_selection = 'room_search';
        if (Request::submitted('select_alternatives')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->selected_rooms = Request::getArray('selected_rooms');
            $this->alternatives_selection = Request::get('alternatives_selection');
        }

        $this->show_form = true;
        $room_search_type = new RoomSearch();
        $room_search_type->setAcceptedPermissionLevels(['autor', 'tutor', 'admin']);
        $room_search_type->setAdditionalDisplayProperties(['seats']);
        $this->room_search = new QuickSearch('searched_room_id', $room_search_type);
        $this->alternative_rooms = [];

        $previously_selected_room_ids = [];
        if ($this->selected_rooms) {
            $previously_selected_room_ids = array_unique($this->selected_rooms);
            if ($this->request->resource instanceof Resource) {
                $previously_selected_rooms = Resource::findBySql(
                    'id IN ( :room_ids ) AND id <> :request_room_id',
                    [
                        'room_ids'        => $previously_selected_room_ids,
                        'request_room_id' => $this->request->resource_id
                    ]
                );
            } else {
                $previously_selected_rooms = Resource::findMany($previously_selected_room_ids);
            }
            foreach ($previously_selected_rooms as $room) {
                $room = $room->getDerivedClassInstance();
                if ($room instanceof Room) {
                    if ($room->userHasPermission($this->current_user, 'autor', [])) {
                        $this->alternative_rooms[] = $room;
                    }
                }
            }
        }

        if (!($selected_room instanceof Room) && !Request::submitted('alternatives_selection')) {
            if (!$this->config->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY) {
                //Use the property search as default alternative selection.
                $this->alternatives_selection = 'request';
            } else {
                //Use the room search instead.
                $this->alternatives_selection = 'room_search';
            }
        }

        if (!$this->requested_room_fully_available || Request::submitted('select_alternatives')) {
            if ($this->alternatives_selection == 'clipboard') {
                $room_group_rooms = [];
                //Get the selected clipboard:
                $clipboard = Clipboard::find($this->selected_clipboard_id);
                if ($clipboard instanceof Clipboard) {
                    //Get the rooms of the selected clipboard
                    $room_ids = $clipboard->getAllRangeIds('Room');

                    //Remove the requested room's ID from the result set
                    //to avoid duplicate rows:
                    $requested_room_index = array_search($this->request->resource_id, $room_ids);
                    if ($requested_room_index !== false) {
                        unset($room_ids[$requested_room_index]);
                    }
                    foreach ($previously_selected_room_ids as $psr_id) {
                        $psr_index = array_search($psr_id, $room_ids);
                        if ($psr_index !== false) {
                            unset($room_ids[$psr_index]);
                        }
                    }
                    $resources = Resource::findMany($room_ids);
                    foreach ($resources as $resource) {
                        //We must filter each room so that only rooms with
                        //permanent autor permissions are included
                        //in the list of alternative rooms:
                        $resource = $resource->getDerivedClassInstance();
                        if ($resource instanceof Room) {
                            if ($resource->userHasPermission($this->current_user, 'autor', [])) {
                                $room_group_rooms[] = $resource;
                            }
                        }
                    }
                }
                $this->alternative_rooms = array_merge(
                    $this->alternative_rooms,
                    $room_group_rooms
                );
            } elseif ($this->alternatives_selection == 'room_search') {
                $room_id = Request::get('searched_room_id');
                if ($this->request->resource_id != $room_id && !in_array($room_id, $previously_selected_room_ids)) {
                    $room = Resource::find($room_id);
                    if ($room) {
                        $room = $room->getDerivedClassInstance();
                        if ($room instanceof Room) {
                            if ($room->userHasPermission($this->current_user, 'autor', [])) {
                                $this->alternative_rooms[] = $room;
                                $this->room_search->defaultValue($room->id, $room->name);
                            }
                        }
                    }
                }
            } elseif ($this->alternatives_selection == 'my_rooms') {
                //Get all rooms where the user has permanent autor permissions:
                $my_rooms = RoomManager::getUserRooms(
                    $this->current_user,
                    'autor',
                    true,
                    null,
                    'resources.id NOT IN ( :room_ids ) ',
                    ['room_ids' => array_merge([$this->request->resource_id], $previously_selected_room_ids)]
                );
                $this->alternative_rooms = array_merge($this->alternative_rooms, $my_rooms);
            } elseif ($this->alternatives_selection == 'request' && !$this->config->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY) {
                //Find rooms by the request's properties:
                $request_rooms = RoomManager::findRoomsByRequest(
                    $this->request,
                    $previously_selected_room_ids
                );
                $this->alternative_rooms = array_merge($this->alternative_rooms, $request_rooms);
            }
        }
        foreach ($this->alternative_rooms as $room) {
            $this->metadate_available[$room->id] = [];
            $this->room_availability[$room->id] = [];
            foreach ($this->request_time_intervals as $metadate_id => $data) {
                $this->metadate_available[$room->id][$metadate_id] = true;
                $this->room_availability[$room->id][$metadate_id] =
                    $this->getRoomAvailability(
                        $room,
                        $data['intervals']
                    );
            }
            $this->room_availability_share[$room->id] = 1.0;
            $date_c = 0;
            $unavailable_c = 0;
            foreach ($this->room_availability[$room->id] as $metadate_id => $metadate_availability) {
                $date_c++;
                foreach ($metadate_availability as $available) {
                    if (!$available) {
                        $this->room_fully_available[$room->id] = false;
                        $this->metadate_available[$room->id][$metadate_id] = false;
                        $unavailable_c++;
                        break;
                    }
                }
            }
            if ($date_c > 0) {
                $this->room_availability_share[$room->id] =
                    ($date_c - $unavailable_c) / $date_c;
            }

            if ($this->request->getProperty('seats') > 0) {
                $this->room_underload[$room->id] =
                    round(((int)$room->seats / (int)$this->request->getProperty('seats')) * 100);
            }
        }

        $this->show_form = true;

        $force_resolve = Request::submitted('force_resolve');
        $resolve = Request::submitted('resolve') || $force_resolve;
        $this->show_force_resolve_button = false;

        if ($resolve) {
            CSRFProtection::verifyUnsafeRequest();
            $this->selected_rooms = array_filter(Request::getArray('selected_rooms'));
            $this->notification_settings = Request::get('notification_settings');
            $this->reply_comment = Request::get('reply_comment');

            $this->request->reply_comment = $this->reply_comment;
            if ($this->request->isDirty()) {
                $this->request->store();
            }
            if (!$this->selected_rooms) {
                PageLayout::postError(
                    _('Es wurde kein Raum ausgewählt!')
                );
                return;
            }

            if (count($this->selected_rooms) < $this->visible_dates && !$force_resolve) {
                PageLayout::postWarning(
                    _('Es wurden nicht für alle Termine der Anfrage Räume ausgewählt! Soll die Anfrage wirklich aufgelöst werden?')
                );
                $this->show_force_resolve_button = true;
                return;
            }

            $errors = [];
            $bookings = [];

            foreach ($this->selected_rooms as $range_str => $room_id) {
                //Get room and check room permissions:
                $room = Resource::find($room_id);
                if (!$room) {
                    PageLayout::postError(
                        sprintf(
                            _('Es wurde kein Raum ausgewählt!'),
                            htmlReady($room_id)
                        )
                    );
                    return;
                }
                $room = $room->getDerivedClassInstance();
                if (!($room instanceof Room)) {
                    PageLayout::postError(
                        sprintf(
                            _('Die Ressource mit der ID %s ist kein Raum!'),
                            htmlReady($room_id)
                        )
                    );
                    return;
                }

                if (!$room->userHasPermission($this->current_user, 'autor')) {
                    PageLayout::postError(
                        sprintf(
                            _('Unzureichende Berechtigungen zum Buchen des Raumes %s!'),
                            htmlReady($room->name)
                        )
                    );
                    return;
                }

                $booking = null;
                //Get the range object:
                $range_data = explode('_', $range_str);
                if ($range_data[0] == 'CourseDate') {
                    $course_date = CourseDate::find($range_data[1]);
                    if (!($course_date instanceof CourseDate)) {
                        PageLayout::postError(
                            sprintf(
                                _('Der Veranstaltungstermin mit der ID %s wurde nicht gefunden!'),
                                htmlReady($range_data[1])
                            )
                        );
                        return;
                    }

                    try {
                        $booking = $room->createBooking(
                            $this->current_user,
                            $course_date->id,
                            [
                                [
                                    'begin' => $course_date->date,
                                    'end'   => $course_date->end_time
                                ]
                            ],
                            null,
                            0,
                            $course_date->end_time,
                            $this->request->preparation_time
                        );
                        if ($booking instanceof ResourceBooking) {
                            $bookings[] = $booking;
                        }
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                        continue;
                    }
                } elseif ($range_data[0] == 'SeminarCycleDate') {
                    //Get the dates of the metadate and create a booking for
                    //each of them.
                    $metadate = SeminarCycleDate::find($range_data[1]);
                    if (!($metadate instanceof SeminarCycleDate)) {
                        PageLayout::postError(
                            sprintf(
                                _('Die Terminserie mit der ID %s wurde nicht gefunden!'),
                                htmlReady($range_data[1])
                            )
                        );
                        return;
                    }
                    if ($metadate->dates) {
                        foreach ($metadate->dates as $date) {
                            try {
                                $booking = $room->createBooking(
                                    $this->current_user,
                                    $date->id,
                                    [
                                        [
                                            'begin' => $date->date,
                                            'end'   => $date->end_time
                                        ]
                                    ],
                                    null,
                                    0,
                                    $course_date->end_time,
                                    $this->request->preparation_time
                                );
                                if ($booking instanceof ResourceBooking) {
                                    $bookings[] = $booking;
                                }
                            } catch (Exception $e) {
                                $errors[] = $e->getMessage();
                                continue;
                            }
                        }
                    }
                } elseif ($range_data[0] == 'User') {
                    $user = User::find($range_data[1]);
                    if (!($user instanceof User)) {
                        PageLayout::postError(
                            sprintf(
                                _('Die Person mit der ID %s wurde nicht gefunden!'),
                                htmlReady($range_data[1])
                            )
                        );
                        return;
                    }
                    try {
                        $booking = $room->createBooking(
                            $this->current_user,
                            $user->id,
                            [
                                [
                                    'begin' => $this->request->begin,
                                    'end'   => $this->request->end
                                ]
                            ],
                            null,
                            0,
                            null,
                            $this->request->preparation_time
                        );
                        if ($booking instanceof ResourceBooking) {
                            $bookings[] = $booking;
                        }
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                        continue;
                    }
                } else {
                    PageLayout::postError(
                        sprintf(
                            _('Der Termin mit der ID %s ist mit einem unpassenden Stud.IP-Objekt verknüpft!'),
                            htmlReady($range_data[1])
                        )
                    );
                    return;
                }
            }

            if ($errors) {
                //Delete all bookings that have been made:
                foreach ($bookings as $booking) {
                    $booking->delete();
                }
                PageLayout::postError(
                    _('Es traten Fehler beim Auflösen der Anfrage auf!'),
                    $errors
                );
            } else {
                //No errors: We can close the request.
                $success = $this->request->closeRequest(
                    $this->notification_settings == 'creator_and_lecturers',
                    $bookings
                );

                if ($success) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Die Anfrage wurde aufgelöst!')
                    );
                } else {
                    PageLayout::postWarning(
                        _('Die Anfrage wurde aufgelöst, konnte aber nicht geschlossen werden!')
                    );
                }
            }
        }
    }

    public function decline_action($request_id = null)
    {
        $this->request = ResourceRequest::find($request_id);
        if (!$this->request) {
            PageLayout::postError(
                _('Die angegebene Anfrage wurde nicht gefunden!')
            );
            return;
        }
        $this->delete_mode = Request::get('delete');
        $request_ids = $this->getFilteredRoomRequests();
        if ($request_ids && count($request_ids) > 1) {
            $this->setRequestForPagination($request_ids);
        }
        if ($this->request->resource) {
            $user_has_permission = $this->request->resource->userHasPermission(
                $this->current_user,
                'tutor'
            );
            if ($this->delete_mode) {
                PageLayout::setTitle(
                    sprintf(
                        _('%s: Anfrage löschen'),
                        $this->request->resource->getFullName()
                    )
                );
            } else {
                PageLayout::setTitle(
                    sprintf(
                        _('%s: Anfrage ablehnen'),
                        $this->request->resource->getFullName()
                    )
                );
            }
        } else {
            $user_has_permission = ResourceManager::userHasGlobalPermission(
                $this->current_user,
                'tutor'
            );
            if ($this->delete_mode) {
                PageLayout::setTitle(_('Anfrage löschen'));
            } else {
                PageLayout::setTitle(_('Anfrage ablehnen'));
            }
        }
        if (!$user_has_permission) {
            throw new AccessDeniedException();
        }

        if (($this->request->closed >= 3) && !$this->delete_mode) {
            PageLayout::postInfo(
                _('Die angegebene Anfrage wurde bereits abgelehnt!')
            );
            return;
        }

        $this->show_form = true;

        if (Request::submitted('confirm')) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->delete_mode) {
                if ($this->request->delete()) {
                    $this->show_form = false;
                    PageLayout::postSuccess(_('Die Anfrage wurde gelöscht!'));
                } else {
                    PageLayout::postError(_('Fehler beim Löschen der Anfrage!'));
                }
            } else {
                $this->reply_comment = Request::get('reply_comment');
                $this->request->reply_comment = $this->reply_comment;
                $this->request->closed = '3';
                $this->request->last_modified_by = $this->current_user->id;
                if ($this->request->isDirty()) {
                    if ($this->request->store()) {
                        $this->show_form = false;
                        PageLayout::postSuccess(
                            _('Die Anfrage wurde abgelehnt!')
                        );
                    } else {
                        PageLayout::postError(
                            _('Fehler beim Ablehnen der Anfrage!')
                        );
                    }
                }
            }
        }
    }

    protected function setRequestForPagination(array $request_ids)
    {
        $pos = array_search($this->filter['filter_request_id'], $request_ids);
        $max = count($request_ids);
        if($pos === 0) {
            $prev_pos = $max-1;
            $next_pos = $pos+1;
        } else {
            $prev_pos = $pos-1;
            $next_pos = $pos+1;

            if($next_pos === $max) {
                $next_pos = 0;
            }
        }
        $this->prev_request = $request_ids[$prev_pos];
        $this->next_request = $request_ids[$next_pos];
    }

    protected function getSingleDateDataForExportRow(CourseDate $date)
    {
        return [
            'date_amount'    => '1',
            'day_of_week'    => getWeekday(date('w', $date->date)),
            'time_string'    => sprintf(
                '%1$s - %2$s',
                date('H:i', $date->date),
                date('H:i', $date->end_time)
            ),
            'first_date_str' => date('d.m.Y', $date->date)
        ];
    }


    protected function getMetadateDataForExportRow(SeminarCycleDate $metadate)
    {
        $data = [
            'date_amount' => count($metadate->dates),
            'day_of_week' => getWeekday($metadate->weekday),
            'time_string' => sprintf(
                '%1$s:%2$02d - %3$s:%4$02d',
                $metadate->start_hour,
                $metadate->start_minute,
                $metadate->end_hour,
                $metadate->end_minute
            )
        ];
        $first_date = $metadate->dates[0];
        if ($first_date instanceof CourseDate) {
            $data['first_date_str'] = date('d.m.Y', $first_date->date);
        }
        return $data;
    }


    public function export_list_action()
    {
        $requests = $this->getFilteredRoomRequests();

        $table_head = [
            [
                _('Anfragende Person'),
                _('Fakultät'),
                _('Institut'),
                _('Veranstaltungsnummer'),
                _('Veranstaltungstitel'),
                _('Lehrende Person(en)'),
                _('Angefragter Raum'),
                _('Erwartete Teilnehmerzahl'),
                _('Sitzplätze'),
                _('Wochentag'),
                _('Uhrzeit'),
                _('Rüstzeit'),
                _('Anzahl der Termine'),
                _('Art der Anfrage'),
                _('Datum des ersten Termins'),
                _('Datum der Anfrage'),
                _('Letzte Änderungen'),
                _('Markierung'),
                _('Priorität'),
                _('Bemerkungen')
            ]
        ];

        $table_body = [];
        foreach ($requests as $request) {
            $request = $request->getDerivedClassInstance();
            if (!$request instanceof RoomRequest) {
                continue;
            }
            $faculty_name = '';
            $institute_name = '';
            $lecturer_names = [];
            $course_number = '';
            $course_name = '';
            $room_name = '';
            $room_seats = '';
            if ($request->resource instanceof Resource) {
                $room = $request->resource->getDerivedClassInstance();
                if ($room instanceof Room) {
                    $room_name = $room->name;
                    $room_seats = $room->seats;
                }
            }
            if ($request->course instanceof Course) {
                $institutes = $request->course->institutes;
                foreach ($institutes as $institute) {
                    if ($institute instanceof Institute) {
                        if ($institute->isFaculty()) {
                            if ($faculty_name) {
                                $faculty_name .= "\n";
                            }
                            $faculty_name .= $institute->name;
                        } else {
                            if ($institute_name) {
                                $institute_name .= "\n";
                            }
                            $institute_name .= $institute->name;
                            if ($institute->faculty instanceof Institute) {
                                if ($faculty_name) {
                                    $faculty_name .= "\n";
                                }
                                $faculty_name .= $institute->faculty->name;
                            }
                        }
                    }
                }
                $course_name = $request->course->name;
                $course_number = $request->course->veranstaltungsnummer;
                $lecturers = $request->course->getMembersWithStatus('dozent');
                foreach ($lecturers as $lecturer) {
                    $lecturer_names[] = $lecturer->user->getFullName('no_title_rev');
                }
            }

            $date_data = [];
            $request_type = $request->getType();
            if ($request_type == 'course') {
                //Produce one row for each metadate and each single date.
                $metadates = $request->course->cycles;
                $single_dates = $request->course->dates;
                foreach ($metadates as $metadate) {
                    if ($metadate instanceof SeminarCycleDate) {
                        $date_data[] = $this->getMetadateDataForExportRow($metadate);
                    }
                }
                foreach ($single_dates as $single_date) {
                    if ($single_date instanceof CourseDate) {
                        if (!$single_date->cycle) {
                            $date_data[] = $this->getSingleDateDataForExportRow($single_date);
                        }
                    }
                }
            } elseif ($request_type == 'cycle' && $request->cycle instanceof SeminarCycleDate) {
                //Produce one row for the metadate.
                $date_data[] = $this->getMetadateDataForExportRow($request->cycle);
            } elseif ($request_type == 'date' && $request->date instanceof CourseDate) {
                //Produce one row for the single date.
                $date_data[] = $this->getSingleDateDataForExportRow($request->date);
            } else {
                //It is a simple request.
                //Produce one row for each date of the request.
                $time_intervals = $request->getTimeIntervals();
                foreach ($time_intervals as $interval) {
                    $first_date_str = date('d.m.Y', $interval['begin']);
                    $day_of_week = getWeekday(date('w', $interval['begin']));
                    $time_string = sprintf(
                        '%1$s - %2$s',
                        date('H:i', $interval['begin']),
                        date('H:i', $interval['end'])
                    );
                    $date_data[] = [
                        'date_amount'    => '1',
                        'day_of_week'    => $day_of_week,
                        'time_string'    => $time_string,
                        'first_date_str' => $first_date_str
                    ];
                }
            }

            //Build table rows:
            foreach ($date_data as $date_row) {
                $table_body[] = [
                    (                                  //Anfragende Person
                    $request->user instanceof User
                        ? $request->user->getFullName()
                        : ''
                    ),
                    $faculty_name,                     //Fakultät
                    $institute_name,                   //Institut
                    $course_number,                    //VA-Nummer
                    $course_name,                      //VA-Titel
                    implode(', ', $lecturer_names),    //Lehrende Personen
                    $room_name,                        //Angefragter Raum
                    $request->getProperty('seats'),    //Erwartete Teilnehmerzahl
                    $room_seats,                       //Sitzplätze
                    $date_row['day_of_week'],          //Wochentag
                    $date_row['time_string'],          //Uhrzeit
                    ($request->preparation_time / 60), //Rüstzeit
                    $date_row['date_amount'],          //Anzahl Termine
                    $request->getTypeString(true),     //Art der Anfrage
                    $date_row['first_date_str'],       //Datum des ersten Termins
                    date('d.m.Y', $request->mkdate),   //Datum der Anfrage
                    date('d.m.Y', $request->chdate),   //Letzte Änderungen
                    $request->marked,                  //Markierung
                    $request->getPriority(),           //Priorität
                    $request->comment                  //Bemerkungen
                ];
            }
        }
        $file_name = sprintf(
            _('Raumanfragen-%s') . '.csv',
            date('Y-m-d')
        );
        $this->render_csv(
            array_merge($table_head, $table_body),
            FileManager::cleanFileName($file_name)
        );
    }

    public function rerequest_booking_action($booking_id)
    {
        PageLayout::setTitle(_('Buchung in Anfrage wandeln'));

        $booking = ResourceBooking::find($booking_id);
        $cdate = CourseDate::find($booking->range_id);

        if (!$cdate) {
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
            return;
        }

        if (Request::submitted('delete_confirm')) {
            CSRFProtection::verifyUnsafeRequest();

            if (!$booking->resource->userHasPermission($this->current_user, 'tutor') && !$GLOBALS['perm']->have_perm('root')) {
                //The user must not delete this booking!
                throw new AccessDeniedException();
            }

            $cycle = $cdate->cycle;
            $resource = Resource::find($booking->resource_id);
            $request = ResourceRequest::findByMetadate($cycle->metadate_id);
            if ($request && $request->closed > 0) {
                $request->closed = 0;
            } else {
                $request = new ResourceRequest();
                $request->course_id = $cycle->seminar_id;
                $request->termin_id = '';
                $request->metadate_id = $cycle->metadate_id;
                $request->user_id = $booking->booking_user_id;
                $request->resource_id = $booking->resource_id;
                $request->category_id = $resource->category_id;
                $request->setProperty('seats', $resource->getProperty('seats'));
            }

            if ($request->store()) {
                $booking_deleted = false;
                foreach ($cycle->getAllDates() as $bcdate) {
                    $bcdate_booking = ResourceBooking::findOneBySQL('range_id=?', [$bcdate->id]);
                    if ($bcdate_booking && $bcdate_booking->resource_id == $booking->resource_id) {
                        $booking_deleted = boolVal($bcdate_booking->delete());
                    }
                }

                if ($booking_deleted) {
                    PageLayout::postSuccess(
                        _('Die Buchung wurde in eine Anfrage umgewandelt!')
                    );
                } else {
                    PageLayout::postError(
                        _('Die Buchung konnte nicht gelöscht werden!')
                    );
                    $request->delete();
                }
            } else {
                PageLayout::postError(
                    _('Die Buchung konnte nicht in eine Anfrage umgewandelt werden!')
                );
            }

            $this->relocate($this->url_for("resources/room_request/planning"));
        }

        $this->booking = $booking;

        $this->user_has_user_perms = $this->booking->resource->userHasPermission(
                $this->current_user,
                'user'
            ) || $GLOBALS['perm']->have_perm('root');

        if (!$this->user_has_user_perms) {
            $resource = $this->booking->resource->getDerivedClassInstance();
            //The user has no permissions on the resource.
            //But if it is a room resource, we must check if the booking plan
            //is public. In such a case, viewing of the booking can be granted.
            if (!($resource->bookingPlanVisibleForUser($this->current_user))) {
                throw new AccessDeniedException();
            }
        }

    }

    public function quickbook_action($request_id, $room_id, $range_str)
    {
        $this->request = ResourceRequest::find($request_id);
        $this->selected_rooms = Request::getArray('selected_rooms');
        $this->notification_settings = Request::get('notification_settings');
        $this->show_force_resolve_button = true;

        $errors = [];
        $bookings = [];

        //Get room and check room permissions:
        $room = Resource::find($room_id);
        if (!$room) {
            PageLayout::postError(
                sprintf(
                    _('Es wurde kein Raum ausgewählt!'),
                    htmlReady($room_id)
                )
            );
            return;
        }
        $room = $room->getDerivedClassInstance();
        if (!($room instanceof Room)) {
            PageLayout::postError(
                sprintf(
                    _('Die Ressource mit der ID %s ist kein Raum!'),
                    htmlReady($room_id)
                )
            );
            return;
        }

        if (!$room->userHasPermission($this->current_user, 'autor')) {
            PageLayout::postError(
                sprintf(
                    _('Unzureichende Berechtigungen zum Buchen des Raumes %s!'),
                    htmlReady($room->name)
                )
            );
            return;
        }

        $booking = null;
        //Get the range object:
        $range_data = explode('_', $range_str);
        if ($range_data[0] == 'CourseDate') {
            $course_date = CourseDate::find($range_data[1]);
            if (!($course_date instanceof CourseDate)) {
                PageLayout::postError(
                    sprintf(
                        _('Der Veranstaltungstermin mit der ID %s wurde nicht gefunden!'),
                        htmlReady($range_data[1])
                    )
                );
                return;
            }
            $range_name = $course_date->course->getFullname();
            try {
                $booking = $room->createBooking(
                    $this->current_user,
                    $course_date->id,
                    [
                        [
                            'begin' => $course_date->date,
                            'end'   => $course_date->end_time
                        ]
                    ],
                    null,
                    0,
                    $course_date->end_time,
                    $this->request->preparation_time
                );
                if ($booking instanceof ResourceBooking) {
                    $bookings[] = $booking;
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        } elseif ($range_data[0] == 'SeminarCycleDate') {
            //Get the dates of the metadate and create a booking for
            //each of them.
            $metadate = SeminarCycleDate::find($range_data[1]);
            if (!($metadate instanceof SeminarCycleDate)) {
                PageLayout::postError(
                    sprintf(
                        _('Die Terminserie mit der ID %s wurde nicht gefunden!'),
                        htmlReady($range_data[1])
                    )
                );
                return;
            }
            $range_name = $metadate->course->getFullname();
            if ($metadate->dates) {
                foreach ($metadate->dates as $date) {
                    try {
                        $booking = $room->createBooking(
                            $this->current_user,
                            $date->id,
                            [
                                [
                                    'begin' => $date->date,
                                    'end'   => $date->end_time
                                ]
                            ],
                            null,
                            0,
                            $date->end_time,
                            $this->request->preparation_time
                        );
                        if ($booking instanceof ResourceBooking) {
                            $bookings[] = $booking;
                        }
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                        continue;
                    }
                }
            }
        } elseif ($range_data[0] == 'User') {
            $user = User::find($range_data[1]);
            if (!($user instanceof User)) {
                PageLayout::postError(
                    sprintf(
                        _('Die Person mit der ID %s wurde nicht gefunden!'),
                        htmlReady($range_data[1])
                    )
                );
                return;
            }
            $range_name = $user->getFullName();
            try {
                $booking = $room->createBooking(
                    $this->current_user,
                    $user->id,
                    [
                        [
                            'begin' => $this->request->begin,
                            'end'   => $this->request->end
                        ]
                    ],
                    null,
                    0,
                    null,
                    $this->request->preparation_time
                );
                if ($booking instanceof ResourceBooking) {
                    $bookings[] = $booking;
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        } else {
            PageLayout::postError(
                sprintf(
                    _('Der Termin mit der ID %s ist mit einem unpassenden Stud.IP-Objekt verknüpft!'),
                    htmlReady($range_data[1])
                )
            );
            return;
        }

        if ($errors) {
            //Delete all bookings that have been made:
            foreach ($bookings as $booking) {
                $booking->delete();
            }
            PageLayout::postError(
                sprintf(
                    _('Es traten Fehler beim Auflösen der Anfrage auf für %s!'),
                    htmlReady($range_name)
                ),
                $errors
            );
        } else {
            //No errors: We can close the request.
            $success = $this->request->closeRequest(
                $this->notification_settings == 'creator_and_lecturers',
                $bookings
            );

            if ($success) {
                $this->show_form = false;
                PageLayout::postSuccess(
                    sprintf(_('Die Anfrage für %s wurde aufgelöst!'), htmlReady($range_name))
                );
            } else {
                PageLayout::postWarning(
                    sprintf(
                        _('Die Anfrage für %s wurde aufgelöst, konnte aber nicht geschlossen werden!'),
                        htmlReady($range_name)
                    )
                );
            }
        }

        if (Request::isAjax()) {
            $this->render_nothing();
        } else {
            $this->relocate($this->url_for("resources/room_request/planning"));
        }
    }

    public function planning_action()
    {
        if (Navigation::hasItem('/resources/planning/requests_planning')) {
            Navigation::activateItem('/resources/planning/requests_planning');
        }

        PageLayout::setTitle(_('Anfragenliste'));
        PageLayout::allowFullscreenMode();

        $sidebar = Sidebar::get();

        if ($this->show_filter_reset_button) {
            $filter_reset_widget = new ActionsWidget();
            $filter_reset_widget->addLink(
                _('Filter zurücksetzen'),
                $this->planningURL(['reset_filter' => '1']),
                Icon::create('filter+decline')
            );
            $sidebar->addWidget($filter_reset_widget);
        }

        $institute_selector = new InstituteSelectWidget(
            '',
            'institut_id',
            'get'
        );
        $institute_selector->includeAllOption();
        $institute_selector->setSelectedElementIds($this->filter['institute']);
        $sidebar->addWidget($institute_selector);

        $semester_selector = new SemesterSelectorWidget(
            '',
            'semester_id',
            'get'
        );
        $semester_selector->setSelection($this->filter['semester']);
        $sidebar->addWidget($semester_selector);

        $list = new SelectWidget(
            _('Veranstaltungstypfilter'),
            $this->planningURL(),
            'course_type'
        );
        $list->addElement(
            new SelectElement(
                'all',
                _('Alle'),
                empty($this->filter['course_type'])
            ),
            'course-type-all'
        );

        foreach (SemClass::getClasses() as $class_id => $class) {
            if ($class['studygroup_mode']) {
                continue;
            }

            $element = new SelectElement(
                $class_id,
                $class['name'],
                $this->filter['course_type'] === (string)$class_id
            );
            $list->addElement(
                $element->setAsHeader(),
                'course-type-' . $class_id
            );

            foreach ($class->getSemTypes() as $id => $result) {
                $element = new SelectElement(
                    $class_id . '_' . $id,
                    $result['name'],
                    $this->filter['course_type'] === $class_id . '_' . $id
                );
                $list->addElement(
                    $element->setIndentLevel(1),
                    'course-type-' . $class_id . '_' . $id
                );
            }
        }
        $sidebar->addWidget($list, 'filter-course-type');


        $widget = new OptionsWidget(_('Filter'));
        $widget->addRadioButton(
            _('Alle Anfragen'),
            $this->planningURL($this->filter['marked'] != '-1' ? ['marked' => '-1'] : []),
            $this->filter['marked'] == -1
        );
        $widget->addRadioButton(
            _('Nur markierte Anfragen'),
            $this->planningURL($this->filter['marked'] != '1' ? ['marked' => '1'] : []),
            $this->filter['marked'] == 1
        );
        $widget->addRadioButton(
            _('Nur unmarkierte Anfragen'),
            $this->planningURL($this->filter['marked'] != '0' ? ['marked' => '0'] : []),
            $this->filter['marked'] == 0
        );
        $widget->addElement(new WidgetElement('<br>'));
        $widget->addCheckbox(
            _('Nur mit Raumangabe'),
            $this->filter['specific_requests'],
            $this->planningURL(['toggle_specific_requests' => 1])
        );
        $widget->addCheckbox(
            _('Eigene Anfragen anzeigen'),
            $this->filter['own_requests'],
            $this->planningURL(['toggle_own_requests' => 1])
        );
        $sidebar->addWidget($widget);

        $this->requests = $this->getFilteredRoomRequests();

        if ($this->filter['room_id']) {
            $this->resource = Resource::find($this->filter['room_id']);
            if (!$this->resource) {
                PageLayout::postError(
                    _('Die angegebene Ressource wurde nicht gefunden!')
                );
                return;
            }

            URLHelper::addLinkParam('resource_id', $this->resource->id);
            $this->resource = $this->resource->getDerivedClassInstance();
            $this->privileged = $this->resource->userHasPermission($this->current_user, 'autor');

            if ($this->filter['semester']) {
                $this->semester = Semester::find($this->filter['semester']);
            } else {
                $this->semester = Semester::find(Semester::findCurrent()->id);
            }

            $booking_colour = ColourValue::find('Resources.BookingPlan.Booking.Bg');
            $course_booking_colour = ColourValue::find('Resources.BookingPlan.CourseBooking.Bg');
            $lock_colour = ColourValue::find('Resources.BookingPlan.Lock.Bg');
            $preparation_colour = ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
            $reservation_colour = ColourValue::find('Resources.BookingPlan.Reservation.Bg');
            $request_colour = ColourValue::find('Resources.BookingPlan.Request.Bg');
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
                ],
            ];
            $this->event_color = $request_colour;
        }
    }
}
