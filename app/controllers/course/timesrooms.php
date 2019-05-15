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
            if (!$this->course->hasDatesOutOfDuration() && $this->course->duration_time == 0) {
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
        $end                  = $this->course->duration_time == -1 ? PHP_INT_MAX : $this->course->end_time;
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

        foreach ($this->course->cycles as $cycle) {
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
                    }
                }
            }
        }

        $dates = $this->course->getDatesWithExdates();

        $single_dates  = [];
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
            $current_semester = Semester::findCurrent();
            $start_semester = Semester::find(Request::get('startSemester'));
            if (Request::get('endSemester') != '-1' && Request::get('endSemester') != '0') {
                $end_semester = Semester::find(Request::get('endSemester'));
            } else {
                $end_semester = Request::int('endSemester');
            }

            $course = $this->course;

            if ($start_semester == $end_semester) {
                $end_semester = 0;
            }

            if ($end_semester != 0 && $end_semester != -1 && $start_semester->beginn >= $end_semester->beginn) {
                PageLayout::postError(_('Das Startsemester liegt nach dem Endsemester!'));
            } else {

                $course->setStartSemester($start_semester->beginn);
                if (is_object($end_semester)) {
                    $course->setEndSemester($end_semester->beginn);
                } else {
                    $course->setEndSemester($end_semester);
                }
                $old_start_weeks = isset($course->start_semester) ? $course->start_semester->getStartWeeks($course->duration_time) : [];
                // If the new duration includes the current semester, we set the semester-chooser to the current semester
                if ($current_semester->beginn >= $course->getStartSemester() && $current_semester->beginn <= $course->getEndSemesterVorlesEnde()) {
                    $course->setFilter($current_semester->beginn);
                    $this->semester_filter = $current_semester->semester_id;
                } else {
                    // otherwise we set it to the first semester
                    $course->setFilter($course->getStartSemester());
                    $this->semester_filter = $start_semester->semester_id;
                }


                $course->store();

                $new_start_weeks = $course->start_semester->getStartWeeks($course->duration_time);
                SeminarCycleDate::removeOutRangedSingleDates($course->getStartSemester(), $course->getEndSemesterVorlesEnde(), $course->id);
                $cycles = SeminarCycleDate::findBySeminar_id($course->seminar_id);
                foreach ($cycles as $cycle) {
                    $cycle->end_offset = $this->getNewEndOffset($cycle, $old_start_weeks, $new_start_weeks);
                    $cycle->store();
                }

                $messages = $course->getStackedMessages();
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

        if (Config::get()->RESOURCES_ENABLE) {
            $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, true, true);
        }

        $this->teachers          = $this->course->getMembersWithStatus('dozent');
        $this->assigned_teachers = $this->date->dozenten;

        $this->groups          = $this->course->statusgruppen;
        $this->assigned_groups = $this->date->statusgruppen;
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
                                           . ' so dass dieser Termin nun nicht mehr regelmäßig ist.'), $termin_info));

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

        if ($termin->store()) {
            NotificationCenter::postNotification('CourseDidChangeSchedule', $this->course);
        }

        // Set Room
        $old_room_id = $termin->room_assignment->resource_id;
        $singledate = new SingleDate($termin);
        if ($singledate->setTime($date, $end_time)) {
            $singledate->store();
        }

        if (Request::option('room') == 'room') {
            $room_id = Request::option('room_sd');

            if ($room_id) {
                if ($room_id != $singledate->resource_id) {
                    if ($resObj = $singledate->bookRoom($room_id)) {
                        $messages = $singledate->getMessages();
                        $this->course->appendMessages($messages);
                    } else if (!$singledate->ex_termin) {
                        $this->course->createError(sprintf(_("Der angegebene Raum konnte für den Termin %s nicht gebucht werden!"),
                                                           '<b>' . $singledate->toString() . '</b>'));
                    }
                }
            } else if ($old_room_id && !$singledate->resource_id) {
                $this->course->createInfo(sprintf(_("Die Raumbuchung für den Termin %s wurde aufgehoben, da die neuen Zeiten außerhalb der alten liegen!"), '<b>'. $singledate->toString() .'</b>'));
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
            $this->resList = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, true, true);
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

        if (!Request::get('room') || Request::get('room') === 'nothing') {
            $termin->raum = Request::get('freeRoomText');
            $termin->store();
        } else {
            $termin->store();
            $singledate = new SingleDate($termin);
            $singledate->bookRoom(Request::get('room'));
            $this->course->appendMessages($singledate->getMessages());
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
        $_SESSION['_checked_dates'] = Request::getArray('single_dates');
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
        $this->resList  = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, true, true, true);
        $this->render_template('course/timesrooms/editStack');
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
        switch (Request::get('method')) {
            case 'edit':
                $this->saveEditedStack();
                break;
            case 'preparecancel':
                $this->saveCanceledStack();
                break;
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

        foreach ($_SESSION['_checked_dates'] as $singledate_id) {
            $singledate = CourseDate::find($singledate_id);
            if (!isset($singledate)) {
                $singledate = CourseExDate::find($singledate_id);
            }
            $singledates[] = $singledate;
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
                if (Request::option('action') == 'room' && Request::option('room')) {
                    if (Request::option('room') != $singledate->room_assignment->resource_id) {
                        if ($resObj = $date->bookRoom(Request::option('room'))) {
                            $messages = $date->getMessages();
                            $this->course->appendMessages($messages);
                        } else if (!$date->ex_termin) {
                            $this->course->createError(sprintf(_("Der angegebene Raum konnte für den Termin %s nicht gebucht werden!"),
                                                               '<strong>' . $date->toString() . '</strong>'));
                        }
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

            $count              = ResourceAssignment::countBySQL('assign_user_id IN (?)', [$ids ?: '']);
            $this->has_bookings = $count > 0;
        }


        $duration = $this->course->duration_time;
        if ($duration == -1) { // course with endless lifespan
            $end_semester = Semester::findBySQL('beginn >= :beginn ORDER BY beginn',
                                                [':beginn' => $this->course->getStartSemester()]);
        } else if ($duration > 0) { // course over more than one semester
            $end_semester = Semester::findBySQL('beginn >= :beginn AND beginn <= :ende ORDER BY beginn',
                                                [':beginn' => $this->course->getStartSemester(),
                                                      ':ende'   => $this->course->getEndSemester()]);
        } else { // one semester course
            $end_semester[] = $this->course->start_semester;
        }

        $this->start_weeks = $this->course->start_semester->getStartWeeks($duration);

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

        if ($start === false || $end === false || $start > $end) {
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
            }
        } else {
            PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen'));
        }

        $this->redirect('course/timesrooms/index');
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
            return count($new_start_weeks);
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
            StudipLog::log('SEM_DELETE_SINGLEDATE', $termin_id, $seminar_id, 'Cycle_id: ' . $termin->metadate_id);
        } else {
            if ($termin->delete()) {
                StudipLog::log("SEM_DELETE_SINGLEDATE", $termin_id, $seminar_id, 'appointment cancelled');
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
}
