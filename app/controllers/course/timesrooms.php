<?php

/**
 * @author  David Siegfried <david.siegfried@uni-vechta.de>
 * @license GPL2 or any later version
 * @since   3.4
 */
class Course_TimesroomsController extends AuthenticatedController
{
    /**
     * Common actions before any other action
     *
     * @param String $action Action to be executed
     * @param Array  $args Arguments passed to the action
     *
     * @throws Trails_Exception when either no course was found or the user
     *                          may not access this area
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Try to find a valid course
        if (!Course::findCurrent()) {
            throw new Trails_Exception(404, _('Es wurde keine Veranstaltung ausgewählt!'));
        }

        if (!$GLOBALS['perm']->have_studip_perm('tutor', Course::findCurrent()->id)) {
            throw new AccessDeniedException();
        }

        // Get seminar instance
        $this->course = new Seminar(Course::findCurrent());

        if (Navigation::hasItem('course/admin/dates')) {
            Navigation::activateItem('course/admin/dates');
        }
        $this->locked = false;

        if (LockRules::Check($this->course->id, 'room_time')) {
            $this->locked     = true;
            $this->lock_rules = LockRules::getObjectRule($this->course->id);
            PageLayout::postInfo(_('Diese Seite ist für die Bearbeitung gesperrt. Sie können die Daten einsehen, jedoch nicht verändern.')
                                 . ($this->lock_rules['description'] ? '<br>' . formatLinks($this->lock_rules['description']) : ''));
        }

        $this->show = [
            'regular'     => true,
            'irregular'   => true,
            'roomRequest' => !$this->locked && Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS,
        ];

        PageLayout::setHelpKeyword('Basis.Veranstaltungen');

        $title = _('Verwaltung von Zeiten und Räumen');
        $title = $this->course->getFullname() . ' - ' . $title;

        PageLayout::setTitle($title);


        URLHelper::bindLinkParam('semester_filter', $this->semester_filter);

        if (empty($this->semester_filter)) {
            if (!$this->course->hasDatesOutOfDuration() && count($this->course->semesters) == 1) {
                $this->semester_filter = $this->course->start_semester->id;
            } else {
                $this->semester_filter = 'all';
            }
        }
        if ($this->semester_filter == 'all') {
            $this->course->applyTimeFilter(0, 0);
        } else {
            $semester = Semester::find($this->semester_filter);
            $this->course->applyTimeFilter($semester['beginn'], $semester['ende']);
        }

        $selectable_semesters = new SimpleCollection(Semester::getAll());
        $start                = $this->course->start_time;
        $end                  = $this->course->getEndSemester()->ende;
        $selectable_semesters = $selectable_semesters->findBy('beginn', [$start, $end], '>=<=')->toArray();
        if (count($selectable_semesters) > 1 || (count($selectable_semesters) == 1 && $this->course->hasDatesOutOfDuration())) {
            $selectable_semesters[] = ['name' => _('Alle Semester'), 'semester_id' => 'all'];
        }
        $this->selectable_semesters = array_reverse($selectable_semesters);

        if (!Request::isXhr()) {
            $this->setSidebar();
        } elseif (Request::isXhr() && $this->flash['update-times']) {
            $semester_id = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
            if ($semester_id === 'all') {
                $semester_id = '';
            }
            $this->response->add_header(
                'X-Raumzeit-Update-Times',
                json_encode([
                    'course_id' => $this->course->id,
                    'html'      => $this->course->getDatesHTML([
                        'semester_id' => $semester_id,
                        'show_room'   => true,
                    ]) ?: _('nicht angegeben'),
                ])
            );
        }
    }

    /**
     * Displays the times and rooms of a course
     *
     * @param mixed $course_id Id of the course (optional, defaults to
     *                         globally selected)
     */
    public function index_action()
    {
        Helpbar::get()->addPlainText(_('Rot'), _('Kein Termin hat eine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Gelb'), _('Mindestens ein Termin hat keine Raumbuchung.'));
        Helpbar::get()->addPlainText(_('Grün'), _('Alle Termine haben eine Raumbuchung.'));

        if (Request::isXhr()) {
            $this->show = [
                'regular'     => true,
                'irregular'   => true,
                'roomRequest' => false,
            ];
        }
        $this->linkAttributes   = ['fromDialog' => Request::isXhr() ? 1 : 0];
        $this->semester         = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
        $this->cycle_dates      = [];
        $matched                = [];

        $this->cycle_room_names = [];

        foreach ($this->course->cycles as $cycle) {
            $cycle_has_multiple_rooms = false;
            foreach ($cycle->getAllDates() as $val) {
                foreach ($this->semester as $sem) {
                    if ($this->semester_filter !== 'all' && $this->semester_filter !== $sem->id) {
                        continue;
                    }

                    if ($sem->beginn <= $val->date && $sem->ende >= $val->date) {
                        if (!isset($this->cycle_dates[$cycle->metadate_id])) {
                            $this->cycle_dates[$cycle->metadate_id] = [
                                'cycle'        => $cycle,
                                'dates'        => [],
                                'room_request' => [],
                            ];
                        }
                        if (!isset($this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id])) {
                            $this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id] = [];
                        }
                        $this->cycle_dates[$cycle->metadate_id]['dates'][$sem->id][] = $val;
                        if ($val->getRoom()) {
                            $this->cycle_dates[$cycle->metadate_id]['room_request'][] = $val->getRoom();
                        }
                        $matched[] = $val->termin_id;
                        //Check if a room is booked for the date:
                        if (($val->room_booking instanceof ResourceBooking)
                            && !$cycle_has_multiple_rooms) {
                            $date_room = $val->room_booking->resource->name;
                            if ($this->cycle_room_names[$cycle->id]) {
                                if ($date_room
                                    && ($date_room != $this->cycle_room_names[$cycle->id])) {
                                    $cycle_has_multiple_rooms = true;
                                }
                            } elseif ($date_room) {
                                $this->cycle_room_names[$cycle->id] = $date_room;
                            }
                        }
                    }
                }
            }
            if ($cycle_has_multiple_rooms) {
                $this->cycle_room_names[$cycle->id] = _('mehrere gebuchte Räume');
            }
        }

        $dates = $this->course->getDatesWithExdates();

        $check_room_requests = Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS;
        $single_dates  = [];
        $this->single_date_room_request_c = 0;
        foreach ($dates as $id => $val) {
            foreach ($this->semester as $sem) {
                if ($this->semester_filter !== 'all' && $this->semester_filter !== $sem->id) {
                    continue;
                }

                if ($sem->beginn > $val->date || $sem->ende < $val->date || $val->metadate_id != '') {
                    continue;
                }

                if (!isset($single_dates[$sem->id])) {
                    $single_dates[$sem->id] = new SimpleCollection();
                }
                $single_dates[$sem->id]->append($val);

                $matched[] = $val->id;
                if ($check_room_requests) {
                    $this->single_date_room_request_c += ResourceRequest::countBySql(
                        "resource_requests.closed < '2' AND termin_id = :termin_id",
                        [
                            'termin_id' => $val->id
                        ]
                    );
                }
            }
        }

        if ($this->semester_filter === 'all') {
            $out_of_bounds = $dates->findBy('id', $matched, '!=');
            if (count($out_of_bounds)) {
                $single_dates['none'] = $out_of_bounds;
            }
        }

        $this->single_dates  = $single_dates;
        $this->checked_dates = $_SESSION['_checked_dates'];
        unset($_SESSION['_checked_dates']);
    }

    /**
     * Edit the start-semester of a course
     *
     * @throws Trails_DoubleRenderError
     */
    public function editSemester_action()
    {
        URLHelper::addLinkParam('origin', Request::option('origin', 'course_timesrooms'));
        $this->semester = array_reverse(Semester::getAll());
        $this->current_semester = Semester::findCurrent();
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            $start_semester = Semester::find(Request::get('startSemester'));
            if (Request::get('endSemester') != '-1' && Request::get('endSemester') != '0') {
                $end_semester = Semester::find(Request::get('endSemester'));
            } else {
                $end_semester = Request::int('endSemester');
            }

            $course = Course::findCurrent();

            if ($start_semester == $end_semester) {
                $end_semester = 0;
            }

            if ($end_semester != 0 && $end_semester != -1 && $start_semester->beginn >= $end_semester->beginn) {
                PageLayout::postError(_('Das Startsemester liegt nach dem Endsemester!'));
            } else {
                //set the new semester array:
                if ($end_semester == -1) {
                    $course->setSemesters([]);
                } elseif($end_semester == 0)  {
                    $course->setSemesters([$start_semester]);
                } else {
                    $selected_semesters = [];
                    foreach (Semester::getAll() as $sem) {
                        if ($sem['beginn'] >= $start_semester['beginn'] && $sem['ende'] <= $end_semester['ende']) {
                            $selected_semesters[] = $sem;
                        }
                    }
                    $course->setSemesters($selected_semesters);
                }

                //deprecated: this is the old way
                $course['start_time'] = $start_semester['beginn'];
                $course['duration_time'] = $end_semester <= 0
                    ? $end_semester
                    : $end_semester['ende'] - $start_semester['beginn'];

                $old_start_weeks = $course->end_semester ? $course->end_semester->getStartWeeks() : [];
                // set the semester-chooser to the first semester
                $this->course->setFilter($course->getStartSemester());
                $this->semester_filter = $start_semester->semester_id;

                $course->store();

                $new_start_weeks = $course->start_semester->getStartWeeks();
                SeminarCycleDate::removeOutRangedSingleDates($this->course->getStartSemester(), $this->course->getEndSemesterVorlesEnde(), $course->id);
                $cycles = SeminarCycleDate::findBySeminar_id($course->id);
                foreach ($cycles as $cycle) {
                    $cycle->end_offset = $this->getNewEndOffset($cycle, $old_start_weeks, $new_start_weeks);
                    $cycle->generateNewDates();
                    $cycle->store();
                }

                $messages = $this->course->getStackedMessages();
                foreach ($messages as $type => $msg) {
                    PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
                }
                $this->relocate(str_replace('_', '/', Request::option('origin')));
            }
        }
    }

    /**
     * Primary function to edit date-informations
     *
     * @param      $termin_id
     * @param null $metadate_id
     */
    public function editDate_action($termin_id)
    {
        PageLayout::setTitle(_('Einzeltermin bearbeiten'));
        $this->date       = CourseDate::find($termin_id) ?: CourseExDate::find($termin_id);
        $this->attributes = [];

        if ($request = RoomRequest::findByDate($this->date->id)) {
            $this->params = ['request_id' => $request->getId()];
        } else {
            $this->params = ['new_room_request_type' => 'date_' . $this->date->id];
        }
        $this->only_bookable_rooms = Request::submitted('only_bookable_rooms');

        if (Config::get()->RESOURCES_ENABLE) {
            $room_booking_id = '';
            if ($this->date->room_booking instanceof ResourceBooking) {
                $room_booking_id = $this->date->room_booking->id;
                $room = $this->date->room_booking->resource;
                if ($room instanceof Resource) {
                    $room = $room->getDerivedClassInstance();
                    if (!$room->userHasBookingRights(User::findCurrent())) {
                        PageLayout::postWarning(
                            _('Die Raumbuchung zu diesem Termin wird bei der Verlängerung des Zeitbereiches gelöscht, da sie keine Buchungsrechte am Raum haben!')
                        );
                    }
                }
            }
            $this->setAvailableRooms([$this->date], [$room_booking_id], $this->only_bookable_rooms);
        }

        $this->teachers          = $this->course->getMembersWithStatus('dozent');
        $this->assigned_teachers = $this->date->dozenten;

        $this->groups          = $this->course->statusgruppen;
        $this->assigned_groups = $this->date->statusgruppen;

        $this->preparation_time = $this->date->room_booking instanceof ResourceBooking
                                ? $this->date->room_booking->preparation_time / 60
                                : '0';
        $this->max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;
    }


    /**
     * Save date-information
     *
     * @param $termin_id
     *
     * @throws Trails_DoubleRenderError
     */
    public function saveDate_action($termin_id)
    {
        // TODO :: TERMIN -> SINGLEDATE
        CSRFProtection::verifyUnsafeRequest();
        $termin   = CourseDate::find($termin_id);
        $date     = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('start_time')));
        $end_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('end_time')));
        $max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;

        if ($date === false || $end_time === false || $date > $end_time) {
            $date     = $termin->date;
            $end_time = $termin->end_time;
            PageLayout::postError(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!'));
        }

        $time_changed = ($date != $termin->date || $end_time != $termin->end_time);
        //time changed for regular date. create normal singledate and cancel the regular date
        if ($termin->metadate_id != '' && $time_changed) {
            $termin_values = $termin->toArray();
            $termin_info   = $termin->getFullname();

            $termin->cancelDate();
            PageLayout::postInfo(sprintf(_('Der Termin %s wurde aus der Liste der regelmäßigen Termine'
                                           . ' gelöscht und als unregelmäßiger Termin eingetragen, da Sie die Zeiten des Termins verändert haben,'
                                           . ' so dass dieser Termin nun nicht mehr regelmäßig ist.'), htmlReady($termin_info)));

            $termin = new CourseDate();
            unset($termin_values['metadate_id']);
            $termin->setData($termin_values);
            $termin->setId($termin->getNewId());
        }
        $termin->date_typ = Request::get('course_type');

        // Set assigned teachers
        $assigned_teachers = Request::optionArray('assigned_teachers');
        $dozenten          = $this->course->getMembers('dozent');
        $termin->dozenten  = count($dozenten) !== count($assigned_teachers)
                          ? User::findMany($assigned_teachers)
                          : [];

        // Set assigned groups
        $assigned_groups       = Request::optionArray('assigned_groups');
        $termin->statusgruppen = Statusgruppen::findMany($assigned_groups);

        $termin->store();

        if ($time_changed) {
            NotificationCenter::postNotification('CourseDidChangeSchedule', $this->course);
        }

        // Set Room
        $old_room_id = $termin->room_booking->resource_id;
        $singledate = new SingleDate($termin);
        if ($singledate->setTime($date, $end_time)) {
            $singledate->store();
        }

        if ((Request::option('room') == 'room') || Request::option('room') == 'nochange') {
            //Storing the SingleDate above has deleted the room booking
            //(see SingleDateDB::storeSingleDate). Therefore, the booking
            //has to be recereated, even if the room option
            //is set to "nochange".
            $room_id = null;
            $preparation_time = Request::get('preparation_time');
            if (Request::option('room') == 'room') {
                $room_id = Request::get('room_id');
                if ($preparation_time > $max_preparation_time) {
                    PageLayout::postError(
                        sprintf(
                            _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                            $max_preparation_time
                        )
                    );
                }
            }
            if ($room_id) {
                if ($room_id != $singledate->resource_id) {
                    if ($resObj = $singledate->bookRoom($room_id, $preparation_time ?: 0)) {
                        $messages = $singledate->getMessages();
                        $this->course->appendMessages($messages);
                    } else if (!$singledate->ex_termin) {
                        $this->course->createError(
                            sprintf(
                                _("Der angegebene Raum konnte für den Termin %s nicht gebucht werden!"),
                                '<b>' . $singledate->toString() . '</b>')
                        );
                    }
                } elseif ($termin->room_booking->preparation_time != ($preparation_time * 60)) {
                    $singledate->bookRoom($room_id, $preparation_time ?: 0);
                }
            } else if ($old_room_id && !$singledate->resource_id) {
                $this->course->createInfo(sprintf(_("Die Raumbuchung für den Termin %s wurde aufgehoben, da die neuen Zeiten außerhalb der alten liegen!"), '<b>'. $singledate->toString() .'</b>'));
            } else if (Request::get('room_id_parameter')) {
                $this->course->createInfo(_("Um eine Raumbuchung durchzuführen, müssen Sie einen Raum aus dem Suchergebnis auswählen!"));
            }
        } elseif (Request::option('room') == 'freetext') {
            $singledate->setFreeRoomText(Request::get('freeRoomText_sd'));
            $singledate->killAssign();
            $singledate->store();
            $this->course->createMessage(sprintf(_("Der Termin %s wurde geändert, etwaige Raumbuchung wurden entfernt und stattdessen der angegebene Freitext eingetragen!"), '<b>' . $singledate->toString() . '</b>'));
        } elseif (Request::option('room') == 'noroom') {
            $singledate->setFreeRoomText('');
            $singledate->killAssign();
            $singledate->store();
            $this->course->createMessage(sprintf(_("Der Termin %s wurde geändert, etwaige freie Ortsangaben und Raumbuchungen wurden entfernt."), '<b>' . $singledate->toString() . '</b>'));
        }
        if ($singledate->messages['error']) {
            PageLayout::postError(
                _('Die folgenden Fehler traten beim Bearbeiten des Termins auf:'),
                $singledate->messages['error']
            );
        }

        $this->displayMessages();
        $this->redirect($this->url_for('course/timesrooms/index', ['contentbox_open' => $termin->metadate_id]));
    }


    /**
     * Create Single Date
     */
    public function createSingleDate_action()
    {
        PageLayout::setTitle(Course::findCurrent()->getFullname() . " - " . _('Einzeltermin anlegen'));
        $this->restoreRequest(words('date start_time end_time room related_teachers related_statusgruppen freeRoomText dateType fromDialog course_type'));

        if (Config::get()->RESOURCES_ENABLE) {
            $this->setAvailableRooms(null);
        }
        $this->teachers = $this->course->getMembers('dozent');
        $this->groups   = Statusgruppen::findBySeminar_id($this->course->id);
    }

    /**
     * Save Single Date
     *
     * @throws Trails_DoubleRenderError
     */
    public function saveSingleDate_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $start_time = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('start_time')));
        $end_time   = strtotime(sprintf('%s %s:00', Request::get('date'), Request::get('end_time')));

        if ($start_time === false || $end_time === false || $start_time > $end_time) {
            $this->storeRequest();

            PageLayout::postError(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!'));
            $this->redirect('course/timesrooms/createSingleDate');

            return;
        }

        $termin            = new CourseDate();
        $termin->termin_id = $termin->getNewId();
        $termin->range_id  = $this->course->id;
        $termin->date      = $start_time;
        $termin->end_time  = $end_time;
        $termin->autor_id  = $GLOBALS['user']->id;
        $termin->date_typ  = Request::get('dateType');

        $current_count = CourseMember::countByCourseAndStatus($this->course->id, 'dozent');
        $related_ids   = Request::optionArray('related_teachers');
        if ($related_ids && count($related_ids) !== $current_count) {
            $termin->dozenten = User::findMany($related_ids);
        }

        $groups = Statusgruppen::findBySeminar_id($this->course->id);
        $related_groups = Request::getArray('related_statusgruppen');
        if ($related_groups && count($related_groups) !== count($groups)) {
            $termin->statusgruppen = Statusgruppen::findMany($related_groups);
        }

        if (!Request::get('room_id')) {
            $termin->raum = Request::get('freeRoomText');
            $termin->store();
        } else {
            $termin->store();
            $singledate = new SingleDate($termin);
            $singledate->bookRoom(Request::option('room_id'));
            $this->course->appendMessages($singledate->getMessages());
        }
        if (Request::get('room_id_parameter')) {
            $this->course->createInfo(_("Um eine Raumbuchung durchzuführen, müssen Sie einen Raum aus dem Suchergebnis auswählen!"));
        }


        if ($start_time < $this->course->filterStart || $end_time > $this->course->filterEnd) {
            $this->course->setFilter('all');
        }

        $this->course->createMessage(sprintf(_('Der Termin %s wurde hinzugefügt!'), $termin->getFullname()));
        $this->course->store();
        $this->displayMessages();

        $this->relocate('course/timesrooms/index');
    }

    /**
     * Restores a previously removed date.
     *
     * @param String $termin_id Id of the previously removed date
     */
    public function undeleteSingle_action($termin_id, $from_dates = false)
    {
        $ex_termin = CourseExDate::find($termin_id);
        $termin    = $ex_termin->unCancelDate();
        if ($termin) {
            $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->getFullname()));
            $this->displayMessages();
        }

        if ($from_dates) {
            $this->redirect("course/dates#date_{$termin_id}");
        } else {
            $params = [];
            if ($termin->metadate_id != '') {
                $params['contentbox_open'] = $termin->metadate_id;
            }
            $this->redirect($this->url_for('course/timesrooms/index', $params));
        }
    }

    /**
     * Performs a stack action defined by url parameter method.
     *
     * @param String $cycle_id Id of the cycle the action should be performed
     *                         upon
     */
    public function stack_action($cycle_id = '')
    {
        $_SESSION['_checked_dates'] = Request::optionArray('single_dates');
        if (empty($_SESSION['_checked_dates']) && isset($_SESSION['_checked_dates'])) {
            PageLayout::postError(_('Sie haben keine Termine ausgewählt!'));
            $this->redirect($this->url_for('course/timesrooms/index', ['contentbox_open' => $cycle_id]));

            return;
        }

        $this->linkAttributes = ['fromDialog' => Request::int('fromDialog') ? 1 : 0];

        switch (Request::get('method')) {
            case 'edit':
                PageLayout::setTitle(_('Termine bearbeiten'));
                $this->editStack($cycle_id);
                break;
            case 'preparecancel':
                PageLayout::setTitle(_('Termine ausfallen lassen'));
                $this->prepareCancel($cycle_id);
                break;
            case 'undelete':
                PageLayout::setTitle(_('Termine stattfinden lassen'));
                $this->unDeleteStack($cycle_id);
                break;
            case 'request':
                PageLayout::setTitle(
                    _('Anfrage auf ausgewählte Termine stellen')
                );
                $this->requestStack($cycle_id);
        }
    }


    /**
     * Edits a stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be edited.
     */
    private function editStack($cycle_id)
    {
        $this->cycle_id = $cycle_id;
        $this->teachers = $this->course->getMembers('dozent');
        $this->gruppen  = Statusgruppen::findBySeminar_id($this->course->id);
        $checked_course_dates = CourseDate::findMany($_SESSION['_checked_dates']);
        $this->only_bookable_rooms = Request::submitted('only_bookable_rooms');

        $date_booking_ids = [];
        if ($this->only_bookable_rooms) {
            $date_ids = [];
            foreach ($checked_course_dates as $date) {
                $date_ids[] = $date->termin_id;
            }
            $db = DBManager::get();
            $stmt = $db->prepare(
                "SELECT DISTINCT `id` FROM `resource_bookings`
                WHERE `range_id` IN ( :date_ids )"
            );
            $stmt->execute(['date_ids' => $date_ids]);
            $date_booking_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $this->setAvailableRooms($checked_course_dates, $date_booking_ids, $this->only_bookable_rooms);

        /*
         * Extract a single date for start and end time
         * (all cycle dates have the same start and end time,
         * so it doesn't matter which date we get).
         */
        $this->date = CourseDate::findOneByMetadate_id($cycle_id);
        $this->checked_dates = $_SESSION['_checked_dates'];
        $this->preparation_time = '0';
        //Check if the preparation time is the same for all dates.
        //In that case, use the common value as preparation time.
        $i = 0;
        $ptime = 0;
        foreach ($checked_course_dates as $course_date) {
            $current_ptime = $course_date->room_booking->preparation_time;
            if ($i == 0) {
                $ptime = $current_ptime;
            } else {
                if ($current_ptime != $ptime) {
                    $ptime = -1;
                    break;
                }
            }
            $i++;
        }
        if ($ptime > -1) {
            $this->preparation_time = $ptime / 60;
        }
        $this->max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;
        $this->render_template('course/timesrooms/editStack');
    }


    /**
     *
     */
    protected function requestStack($cycle_id)
    {
        $this->cycle_id = $cycle_id;

        $appointment_ids = $_SESSION['_checked_dates'];
        $this->redirect(
            $this->url_for(
                'course/room_requests/request_start',
                [
                    'range' => 'date-multiple',
                    'range_ids' => $appointment_ids
                ]
            )
        );
    }


    /**
     * Prepares a stack/cycle to be canceled.
     *
     * @param String $cycle_id Id of the cycle to be canceled.
     */
    private function prepareCancel($cycle_id)
    {
        $this->cycle_id = $cycle_id;
        $this->render_template('course/timesrooms/cancelStack');
    }

    /**
     * Restores a previously deleted stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be restored.
     */
    private function unDeleteStack($cycle_id = '')
    {
        foreach ($_SESSION['_checked_dates'] as $id) {
            $ex_termin = CourseExDate::find($id);
            if ($ex_termin === null) {
                continue;
            }
            $ex_termin->content = '';
            $termin             = $ex_termin->unCancelDate();
            if ($termin !== null) {
                $this->course->createMessage(sprintf(_('Der Termin %s wurde wiederhergestellt!'), $termin->getFullname()));
            }
        }
        $this->displayMessages();

        $this->relocate('course/timesrooms/index', ['contentbox_open' => $cycle_id]);
    }

    /**
     * Saves a stack/cycle.
     *
     * @param String $cycle_id Id of the cycle to be saved.
     */
    public function saveStack_action($cycle_id = '')
    {
        CSRFProtection::verifyUnsafeRequest();
        if (Request::submitted('only_bookable_rooms')) {
            //Redirect to the editStack method and do not save anything.
            $this->editStack($cycle_id);
            return;
        }
        switch (Request::get('method')) {
            case 'edit':
                $this->saveEditedStack();
                break;
            case 'preparecancel':
                $this->saveCanceledStack();
                break;
            case 'request':
                $this->saveRequestStack();
        }

        $this->displayMessages();

        $this->relocate('course/timesrooms/index', ['contentbox_open' => $cycle_id]);
    }

    /**
     * Saves a canceled stack/cycle.
     *
     * @param String $cycle_id Id of the canceled cycle to be saved.
     */
    private function saveCanceledStack()
    {
        $deleted_dates = [];
        $cancel_comment = trim(Request::get('cancel_comment'));
        $cancel_send_message = Request::int('cancel_send_message');

        foreach ($_SESSION['_checked_dates'] as $id) {
            $termin = CourseDate::find($id);
            if ($termin) {
                $deleted_dates[] = $this->deleteDate($termin, $cancel_comment);
            }
        }

        if ($cancel_send_message && $cancel_comment != '' && count($deleted_dates) > 0) {
            $snd_messages = raumzeit_send_cancel_message($cancel_comment, $deleted_dates);
            if ($snd_messages > 0) {
                $this->course->createMessage(_('Alle Teilnehmenden wurden benachrichtigt.'));
            }
        }
    }

    /**
     * Saves an edited stack/cycle.
     *
     * @param String $cycle_id Id of the edited cycle to be saved.
     */
    private function saveEditedStack()
    {
        $persons         = Request::getArray('related_persons');
        $action          = Request::get('related_persons_action');
        $groups          = Request::getArray('related_groups');
        $group_action    = Request::get('related_groups_action');
        $lecture_changed = false;
        $groups_changed  = false;
        $singledates     = [];

        if (is_array($_SESSION['_checked_dates'])) {
            foreach ($_SESSION['_checked_dates'] as $singledate_id) {
                $singledate = CourseDate::find($singledate_id);
                if (!isset($singledate)) {
                    $singledate = CourseExDate::find($singledate_id);
                }
                $singledates[] = $singledate;
            }
        }

        // Update related persons
        if (in_array($action, words('add delete'))) {
            $course_lectures = $this->course->getMembers('dozent');
            $persons         = User::findMany($persons);
            foreach ($singledates as $singledate) {
                if ($action === 'add') {
                    if (count($course_lectures) === count($persons)) {
                        $singledate->dozenten = [];
                    } else {
                        foreach ($persons as $person) {
                            if (!count($singledate->dozenten->findBy('id', $person->id))) {
                                $singledate->dozenten[] = $person;
                            }
                        }
                        if (count($singledate->dozenten) === count($course_lectures)) {
                            $singledate->dozenten = [];
                        }
                    }

                    $lecture_changed = true;
                }

                if ($action === 'delete') {
                    foreach ($persons as $person) {
                        $singledate->dozenten->unsetBy('id', $person->id);
                    }
                    $lecture_changed = true;
                }
                $singledate->store();
            }
        }

        if ($lecture_changed) {
            $this->course->createMessage(_('Zuständige Personen für die Termine wurden geändert.'));
        }

        if (in_array($group_action, words('add delete'))) {
            $course_groups = Statusgruppen::findBySeminar_id($this->course->id);
            $groups        = Statusgruppen::findMany($groups);
            foreach ($singledates as $singledate) {
                if ($group_action === 'add') {
                    if (count($course_groups) === count($groups)) {
                        $singledate->statusgruppen = [];
                    } else {

                        foreach ($groups as $group) {
                            if (!count($singledate->statusgruppen->findBy('id', $group->id))) {
                                $singledate->statusgruppen[] = $group;
                            }
                        }
                        if (count($singledate->statusgruppen) === count($course_groups)) {
                            $singledate->statusgruppen = [];
                        }
                    }
                    $groups_changed = true;
                }
                if ($group_action === 'delete') {
                    foreach ($groups as $group) {
                        $singledate->statusgruppen->unsetByPk($group->id);
                    }
                    $groups_changed = true;
                }
                $singledate->store();
            }
        }

        if ($groups_changed) {
            $this->course->createMessage(_('Zugewiesene Gruppen für die Termine wurden geändert.'));
        }

        if (in_array(Request::get('action'), ['room', 'freetext', 'noroom']) || Request::get('course_type')) {
            foreach ($singledates as $key => $singledate) {
                $date = new SingleDate($singledate);
                if (Request::option('action') == 'room') {
                    $preparation_time = Request::get('preparation_time');
                    $max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;
                    if ($preparation_time > $max_preparation_time) {
                        $this->course->createError(
                            sprintf(
                                _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                                $max_preparation_time
                            )
                        );
                        continue;
                    }
                    if (Request::option('room_id')) {
                        if (Request::option('room_id') != $singledate->room_booking->resource_id) {
                            if ($resObj = $date->bookRoom(Request::option('room_id'), $preparation_time)) {
                                $messages = $date->getMessages();
                                $this->course->appendMessages($messages);
                            } else if (!$date->ex_termin) {
                                $this->course->createError(sprintf(_("Der angegebene Raum konnte für den Termin %s nicht gebucht werden!"),
                                    '<strong>' . $date->toString() . '</strong>'));
                            }
                        } elseif (($preparation_time * 60) != $singledate->room_booking->preparation_time) {
                            $date->bookRoom(Request::option('room_id'), $preparation_time);
                            $messages = $date->getMessages();
                            $this->course->appendMessages($messages);
                        }
                    } else if (Request::get('room_id_parameter')) {
                        $this->course->createInfo(_("Um eine Raumbuchung durchzuführen, müssen Sie einen Raum aus dem Suchergebnis auswählen!"));
                    }
                } elseif (Request::option('action') == 'freetext') {
                    $date->setFreeRoomText(Request::get('freeRoomText'));
                    $date->store();
                    $date->killAssign();
                    $this->course->createMessage(sprintf(_("Der Termin %s wurde geändert, etwaige Raumbuchung wurden entfernt und stattdessen der angegebene Freitext eingetragen!"),
                                                         '<strong>' . $date->toString() . '</strong>'));
                } elseif (Request::option('action') == 'noroom') {
                    $date->setFreeRoomText('');
                    $date->store();
                    $date->killAssign();
                    $this->course->createMessage(sprintf(_("Der Termin %s wurde geändert, etwaige freie Ortsangaben und Raumbuchungen wurden entfernt."),
                                                         '<strong>' . $date->toString() . '</strong>'));
                }

                if (Request::get('course_type') != '') {
                    $date->setDateType(Request::get('course_type'));
                    $date->store();
                    $this->course->createMessage(sprintf(_("Die Art des Termins %s wurde geändert."), '<strong>' . $date->toString() . '</strong>'));
                }
            }
        }
    }


    /**
     * The data saving part of the action to create one request
     * for multiple appointments.
     */
    public function saveRequestStack($cycle_id = null)
    {
        //The properties[] array is set by $rp->definition->toHtmlInput.
        //The default name for toHtmlInput input elements is:
        //properties[$property->id].
        $set_property_values = Request::getArray('properties');
        $set_properties = [];
        foreach ($set_property_values as $id => $value) {
            if (!ResourcePropertyDefinition::exists($id)) {
                continue;
            }
            $property = new ResourceRequestProperty();
            $property->property_id = $id;
            $property->state = $value;
            $set_properties[] = $property;
        }

        $appointments = [];
        foreach ($_SESSION['_checked_dates'] as $appointment_id) {
            $appointment = CourseDate::find($appointment_id);
            if ($appointment) {
                $appointments[] = $appointment;
            }
        }

        if (!$appointments) {
            $this->course->createError(
                _('Es wurden keine gültigen Termin-IDs übergeben!')
            );
            return;
        }

        $request = new RoomRequest();
        $request->course_id = $this->course->getId();
        $request->user_id = $GLOBALS['user']->id;
        $request->comment = Request::get('comment');
        $request->closed = '0';
        if (!$request->store()) {
            $this->course->createError(
                _('Fehler beim Speichern der Anfrage!')
            );
            return;
        }

        //Now we store the requested properties:
        $successfully_stored = 0;
        foreach ($set_properties as $property) {
            $property->request_id = $request->id;
            if ($property->store()) {
                $successfully_stored++;
            }
        }

        if (($successfully_stored < count($set_properties))
            and count($set_properties)) {
            $this->course->createError(
                _('Es wurden nicht alle zur Anfrage gehörenden Eigenschaften gespeichert!')
            );
        }

        //Finally we can create ResourceRequestAppointment
        //objects for each appointment:

        $successfully_stored = 0;
        foreach ($appointments as $appointment) {
            $rra = new ResourceRequestAppointment();
            $rra->request_id = $request->id;
            $rra->appointment_id = $appointment->id;
            if ($rra->store()) {
                $successfully_stored++;
            }
        }

        if (($successfully_stored < count($appointments))
            and count($appointments)) {
            $this->course->createError(
                _('Es wurden nicht alle zur Anfrage gehörenden Terminzuordnungen gespeichert!')
            );
            return;
        }

        $this->course->createMessage(
            _('Die Raumanfrage wurde gespeichert!')
        );
    }


    /**
     * Creates a cycle.
     *
     * @param String $cycle_id Id of the cycle to be created (optional)
     */
    public function createCycle_action($cycle_id = null)
    {
        PageLayout::setTitle(Course::findCurrent()->getFullname() . " - " . _('Regelmäßige Termine anlegen'));
        $this->restoreRequest(words('day start_time end_time description cycle startWeek teacher_sws fromDialog course_type'));

        $this->cycle = new SeminarCycleDate($cycle_id);

        if ($this->cycle->isNew()) {
            $this->has_bookings = false;
        } else {
            $ids = $this->cycle->dates->pluck('termin_id');

            $count = ResourceBooking::countBySQL(
                'range_id IN ( :range_ids )',
                [
                    'range_ids' => ($ids ?: '')
                ]
            );
            $this->has_bookings = $count > 0;
        }


        $course = Course::find($this->course->id);
        if ($this->course->isOpenEnded()) { // course with endless lifespan
            $end_semester = Semester::findBySQL("beginn >= ? ", [$this->course->start_time]);
        } else { // course over more than one semester
            $end_semester = $course->semesters;
        }

        $this->start_weeks = $this->course->end_semester->getStartWeeks();

        if (!empty($end_semester)) {
            $this->end_semester_weeks = [];

            foreach ($end_semester as $sem) {

                $sem_duration = $sem->ende - $sem->beginn;
                $weeks        = $sem->getStartWeeks($sem_duration);

                foreach ($this->start_weeks as $key => $week) {
                    if (mb_strpos($week, mb_substr($weeks[0], -15)) !== false) {
                        $this->end_semester_weeks['start'][] = ['value' => $key, 'label' => sprintf(_('Anfang %s'), $sem->name)];
                    }
                    if (mb_strpos($week, mb_substr($weeks[count($weeks) - 1], -15)) !== false) {
                        $this->end_semester_weeks['ende'][] = ['value' => $key, 'label' => sprintf(_('Ende %s'), $sem->name)];
                    }
                    foreach ($weeks as $val) {
                        if (mb_strpos($week, mb_substr($val, -15)) !== false) {
                            $this->clean_weeks[(string) $sem->name][$key] = $val;
                        }
                    }
                }
            }

            if (count($end_semester) > 1) {
                $this->end_semester_weeks['ende'][] = ['value' => -1, 'label' => _('Alle Semester')];
            }
        }
    }

    /**
     * Saves a cycle
     */
    public function saveCycle_action()
    {
        CSRFProtection::verifyRequest();


        $start = strtotime(Request::get('start_time'));
        $end   = strtotime(Request::get('end_time'));

        if ($start === false || $end === false || $start > $end
        || !$this->validate_datetime(Request::get('start_time'))
        || !$this->validate_datetime(Request::get('end_time'))) {
            $this->storeRequest();
            PageLayout::postError(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!'));
            $this->redirect('course/timesrooms/createCycle');

            return;
        } elseif (Request::int('startWeek') > Request::int('endWeek') && Request::int('endWeek') != -1) {
            $this->storeRequest();
            PageLayout::postError(_('Die Endwoche liegt vor der Startwoche. Bitte überprüfen Sie diese Angabe!'));
            $this->redirect('course/timesrooms/createCycle');

            return;
        }

        $cycle              = new SeminarCycleDate();
        $cycle->seminar_id  = $this->course->id;
        $cycle->weekday     = Request::int('day');
        $cycle->description = Request::get('description');
        $cycle->sws         = round(Request::float('teacher_sws'), 1);
        $cycle->cycle       = Request::int('cycle');
        $cycle->week_offset = Request::int('startWeek');
        $cycle->end_offset  = Request::int('endWeek');
        $cycle->start_time  = date('H:i:00', $start);
        $cycle->end_time    = date('H:i:00', $end);

        if ($cycle->end_offset == -1) {
            $cycle->end_offset = NULL;
        }

        if ($cycle->store()) {

            if(Request::int('course_type')) {
                $cycle->setSingleDateType(Request::int('course_type'));
            }

            $cycle_info = $cycle->toString();
            NotificationCenter::postNotification('CourseDidChangeSchedule', $this->course);

            $this->course->createMessage(sprintf(_('Die regelmäßige Veranstaltungszeit %s wurde hinzugefügt!'), $cycle_info));
            $this->displayMessages();
            $this->relocate('course/timesrooms/index');
        } else {
            $this->storeRequest();
            $this->course->createError(_('Die regelmäßige Veranstaltungszeit konnte nicht hinzugefügt werden! Bitte überprüfen Sie Ihre Eingabe.'));
            $this->displayMessages();
            $this->redirect('course/timesrooms/createCycle');
        }
    }

    /**
     * Edits a cycle
     *
     * @param String $cycle_id Id of the cycle to be edited
     */
    public function editCycle_action($cycle_id)
    {
        $cycle = SeminarCycleDate::find($cycle_id);

        $start = strtotime(Request::get('start_time'));
        $end   = strtotime(Request::get('end_time'));

        // Prepare Request for saving Request
        if ($start === false || $end === false || $start > $end) {
            PageLayout::postError(_('Die Zeitangaben sind nicht korrekt. Bitte überprüfen Sie diese!'));
        } else {
            $cycle->start_time  = date('H:i:00', $start);
            $cycle->end_time    = date('H:i:00', $end);
        }
        $cycle->weekday     = Request::int('day');
        $cycle->description = Request::get('description');
        $cycle->sws         = Request::get('teacher_sws');
        $cycle->cycle       = Request::get('cycle');
        $cycle->week_offset = Request::int('startWeek');
        $cycle->end_offset  = Request::int('endWeek');

        if ($cycle->end_offset == -1) {
            $cycle->end_offset = NULL;
        }

        $changed_dates = 0;
        if (Request::int('course_type')) {
            $changed_dates = $cycle->setSingleDateType(Request::int('course_type'));
        }

        if ($changed_dates > 0 || $cycle->isDirty()) {
            $cycle->chdate = time();
            $cycle->store();

            if ($changed_dates > 0) {
                PageLayout::postSuccess(sprintf(ngettext(
                    _('Die Art des Termins wurde bei 1 Termin geändert'),
                    _('Die Art des Termins wurde bei %u Terminen geändert'),
                    $changed_dates
                ), $changed_dates));
            } else {
                PageLayout::postSuccess(_('Änderungen gespeichert!'));
            }
        } else {
            PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen'));
        }

        $this->relocate('course/timesrooms/index');
    }

    /**
     * Deletes a cycle
     *
     * @param String $cycle_id Id of the cycle to be deleted
     */
    public function deleteCycle_action($cycle_id)
    {
        CSRFProtection::verifyRequest();
        $cycle = SeminarCycleDate::find($cycle_id);
        if ($cycle === null) {
            $message = sprintf(_('Es gibt keinen regelmäßigen Eintrag "%s".'), $cycle_id);
            PageLayout::postError($message);
        } else {
            $cycle_string = $cycle->toString();
            if ($cycle->delete()) {
                $message = sprintf(_('Der regelmäßige Eintrag "%s" wurde gelöscht.'),
                                   '<strong>' . $cycle_string . '</strong>');
                PageLayout::postSuccess($message);
            }
        }

        $this->redirect('course/timesrooms/index');
    }

    /**
     * Add information to canceled / holiday date
     *
     * @param String $termin_id Id of the date
     */
    public function cancel_action($termin_id)
    {
        PageLayout::setTitle(_('Kommentar hinzufügen'));
        $this->termin = CourseDate::find($termin_id) ?: CourseExDate::find($termin_id);
    }

    /**
     * Saves a comment for a given date.
     *
     * @param String $termin_id Id of the date
     */
    public function saveComment_action($termin_id)
    {
        $termin = CourseExDate::find($termin_id);

        if (is_null($termin)) {
            $termin = CourseDate::find($termin_id);
        }
        if (Request::get('cancel_comment') != $termin->content) {
            $termin->content = Request::get('cancel_comment');
            if ($termin->store()) {
                $this->course->createMessage(sprintf(_('Der Kommtentar des gelöschten Termins %s wurde geändert.'), $termin->getFullname()));
            } else {
                $this->course->createInfo(sprintf(_('Der gelöschte Termin %s wurde nicht verändert.'), $termin->getFullname()));
            }
        } else {
            $this->course->createInfo(sprintf(_('Der gelöschte Termin %s wurde nicht verändert.'), $termin->getFullname()));
        }
        if (Request::int('cancel_send_message')) {
            $snd_messages = raumzeit_send_cancel_message(Request::get('cancel_comment'), $termin);
            if ($snd_messages > 0) {
                $this->course->createInfo(_('Alle Teilnehmenden wurden benachrichtigt.'));
            }
        }
        $this->displayMessages();
        $this->redirect($this->url_for('course/timesrooms/index', ['contentbox_open' => $termin->metadate_id]));
    }

    /**
     * Creates the sidebar
     */
    private function setSidebar()
    {
        if (!$this->locked) {
            $actions = new ActionsWidget();
            $actions->addLink(
                sprintf(
                    _('Semester ändern (%s)'),
                    $this->course->getFullname('sem-duration-name')
                ),
                $this->url_for('course/timesrooms/editSemester'),
                Icon::create('date', 'clickable')
            )->asDialog('size=400');
            Sidebar::Get()->addWidget($actions);
        }

        $widget = new SelectWidget(_('Semesterfilter'), $this->url_for('course/timesrooms/index'), 'semester_filter');
        foreach ($this->selectable_semesters as $item) {
            $element = new SelectElement($item['semester_id'],
                                         $item['name'],
                                         $item['semester_id'] == $this->semester_filter);
            $widget->addElement($element);
        }
        Sidebar::Get()->addWidget($widget);

        if ($GLOBALS['perm']->have_perm('admin')) {
            $list = new SelectWidget(_('Veranstaltungen'), $this->url_for('course/timesrooms/index'), 'cid');

            foreach (AdminCourseFilter::get()->getCoursesForAdminWidget() as $seminar) {
                $list->addElement(new SelectElement(
                    $seminar['Seminar_id'],
                    $seminar['Name'],
                    $seminar['Seminar_id'] === Context::getId(),
                    $seminar['VeranstaltungsNummer'] . ' ' . $seminar['Name']
                ));
            }
            $list->size = 8;
            Sidebar::Get()->addWidget($list);
        }
    }

    /**
     * Calculates new end_offset value for given SeminarCycleDate Object
     *
     * @param object of SeminarCycleDate
     * @param array
     * @param array
     *
     * @return int
     */
    public function getNewEndOffset($cycle, $old_start_weeks, $new_start_weeks)
    {
        // if end_offset is null (endless lifespan) it should stay null
        if (is_null($cycle->end_offset)) {
            return null;
        }
        $old_offset_string = $old_start_weeks[$cycle->end_offset];
        $new_offset_value  = 0;

        foreach ($new_start_weeks as $value => $label) {
            if (mb_strpos($label, mb_substr($old_offset_string, -15)) !== false) {
                $new_offset_value = $value;
            }
        }
        if ($new_offset_value == 0) {
            return count($new_start_weeks) - 1;
        }

        return $new_offset_value;
    }

    /**
     * Displays messages.
     *
     * @param Array $messages Messages to display (optional, defaults to
     *                        potential stored messages on course object)
     */
    private function displayMessages(array $messages = [])
    {
        $messages = $messages ?: $this->course->getStackedMessages();
        foreach ((array)$messages as $type => $msg) {
            PageLayout::postMessage(MessageBox::$type($msg['title'], $msg['details']));
        }
    }

    /**
     * Deletes a date.
     *
     * @param String $termin CourseDate of the date
     * @param String $cancel_comment cancel mesessage (if non empty)
     *
     * @return CourseDate|CourseExDate deleted date
     */
    private function deleteDate($termin, $cancel_comment)
    {
        $seminar_id = $termin->range_id;
        $termin_room = $termin->getRoomName();
        $termin_date = $termin->getFullname();
        $has_topics  = $termin->topics->count();

        if ($cancel_comment != '') {
            $termin->content = $cancel_comment;
        }

        //cancel cycledate entry
        if ($termin->metadate_id || $cancel_comment != '') {
            $termin = $termin->cancelDate();
            StudipLog::log('SEM_DELETE_SINGLEDATE', $termin->id, $seminar_id, 'Cycle_id: ' . $termin->metadate_id);
        } else {
            if ($termin->delete()) {
                StudipLog::log("SEM_DELETE_SINGLEDATE", $termin->id, $seminar_id, 'appointment cancelled');
            }
        }

        if ($has_topics) {
            $this->course->createMessage(sprintf(_('Dem Termin %s war ein Thema zugeordnet. Sie können das Thema im Ablaufplan einem anderen Termin (z.B. einem Ausweichtermin) zuordnen.'),
                $termin_date, '<a href="' . URLHelper::getLink('dispatch.php/course/topics') . '">', '</a>'));
        }
        if ($termin_room) {
            $this->course->createMessage(sprintf(_('Der Termin %s wurde gelöscht! Die Buchung für den Raum %s wurde gelöscht.'),
                $termin_date, $termin_room));
        } else {
            $this->course->createMessage(sprintf(_('Der Termin %s wurde gelöscht!'), $termin_date));
        }

        return $termin;
    }


    /**
     * Redirects to another location.
     *
     * @param String $to New location
     */
    public function redirect($to)
    {
        $arguments = func_get_args();

        if (Request::isXhr()) {
            $url       = call_user_func_array('parent::url_for', $arguments);
            $url_chunk = Trails_Inflector::underscore(mb_substr(get_class($this), 0, -10));
            $index_url = $url_chunk . '/index';

            if (mb_strpos($url, $index_url) !== false) {
                $this->flash['update-times'] = $this->course->id;
            }
        }

        return call_user_func_array('parent::redirect', $arguments);
    }

    /**
     * Stores a request into trails' flash object
     */
    private function storeRequest()
    {
        $this->flash['request'] = Request::getInstance();
    }

    /**
     * Restores a previously stored request from trails' flash object
     */
    private function restoreRequest(array $fields)
    {
        $request = $this->flash['request'];

        if ($request) {
            foreach ($fields as $field) {
                Request::set($field, $request[$field]);
            }
        }
    }

    /**
     * Relocates to another location if not from dialog
     *
     * @param String $to New location
     */
    public function relocate($to)
    {
        if (Request::int('fromDialog')) {
            $url = call_user_func_array([$this, 'url_for'], func_get_args());
            $this->redirect($url);
        } else {
            call_user_func_array('parent::relocate', func_get_args());
        }
    }


    protected function setAvailableRooms($dates, $date_booking_ids = [], $only_bookable_rooms = false)
    {
        if (Config::get()->RESOURCES_ENABLE) {
            //Check for how many rooms the user has booking permissions.
            //In case these permissions exist for more than 50 rooms
            //show a quick search. Otherwise show a select field
            //with the list of rooms.

            $current_user = User::findCurrent();
            $current_user_is_resource_admin = ResourceManager::userHasGlobalPermission(
                $current_user,
                'admin'
            );
            $all_time_intervals = [];
            if (!empty($dates)) {
                $dates = SimpleCollection::createFromArray($dates);
                foreach ($dates as $date) {
                    $begin = new DateTime();
                    $begin->setTimestamp($date->date);
                    $end = new DateTime();
                    $end->setTimestamp($date->end_time);
                    $all_time_intervals[] = [
                        'begin' => $begin,
                        'end' => $end
                    ];
                }
            }
            $this->selectable_rooms = [];
            $rooms_with_booking_permissions = 0;
            if ($current_user_is_resource_admin) {
                $rooms_with_booking_permissions = Room::countAll();
            } else {
                $user_rooms = RoomManager::getUserRooms($current_user);
                foreach ($user_rooms as $room) {
                    if ($room->userHasBookingRights($current_user, $begin, $end)) {
                        $rooms_with_booking_permissions++;
                        if ($only_bookable_rooms) {
                            foreach ($all_time_intervals as $interval) {
                                $available = $room->isAvailable($interval['begin'], $interval['end'], $date_booking_ids);
                                if (!$available) {
                                    continue 2;
                                }
                            }
                            //At this point, the room is available on all
                            //time intervals.
                            $this->selectable_rooms[] = $room;
                        } else {
                            $this->selectable_rooms[] = $room;
                        }
                    }
                }
            }

            if (($rooms_with_booking_permissions > 50) && !$only_bookable_rooms) {
                $room_search_type = new RoomSearch();
                $room_search_type->with_seats = 0;
                $room_search_type->setAcceptedPermissionLevels(
                    ['autor', 'tutor', 'admin']
                );
                $room_search_type->setAdditionalDisplayProperties(
                    ['seats']
                );
                $room_search_type->setAdditionalPropertyFormat(_('(%s Sitzplätze)'));
                $this->room_search = new QuickSearch(
                    'room_id',
                    $room_search_type
                );
            } else {
                if (ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
                    if ($only_bookable_rooms) {
                        $rooms = Room::findAll();
                        foreach ($rooms as $room) {
                            foreach ($all_time_intervals as $interval) {
                                $available = $room->isAvailable($interval['begin'], $interval['end'], $date_booking_ids);
                                $booking_rights = $room->userHasBookingRights($current_user, $interval['begin'], $interval['end']);

                                if (!$available || !$booking_rights) {
                                    continue 2;
                                }
                            }
                            $this->selectable_rooms[] = $room;
                        }
                    } else {
                        $this->selectable_rooms = Room::findAll();
                    }
                }
            }
        }
    }
}
