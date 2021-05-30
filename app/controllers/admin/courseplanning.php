<?php
/**
 * Lehrveranstaltungsplanungskomponente
 *
 * @author  Timo Hartge <hartge@data-quest.de>
 * @license GPL2 or any version
 * @since   Stud.IP 4.5
 */
class Admin_CourseplanningController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['perm']->have_perm('admin')) {
            Navigation::activateItem('/browse/my_courses/schedule');
        }

        $this->insts = Institute::getMyInstitutes($GLOBALS['user']->id);

        if (empty($this->insts) && !$GLOBALS['perm']->have_perm('root')) {
            PageLayout::postError(_('Sie wurden noch keiner Einrichtung zugeordnet'));
        }

        if (!$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT) {
            $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', $this->insts[0]['Institut_id']);
        }

        // Semester selection
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE) {
            $this->semester = Semester::find($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE);
        }

        if (Request::submitted('teacher_filter')) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_TEACHERFILTER', Request::option('teacher_filter'));
        }

        if (in_array($action, ['index', 'weekday'])) {
            PageLayout::allowFullscreenMode();
        }
    }

    private function getPlanTitle()
    {
        $plan_title = _('Veranstaltungs-Stundenplan');
        if ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT && $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT != 'all') {
            $inst = Institute::find($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT);
            $plan_title .= ' - ' . $inst['Name'];
        }
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL && $GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL != 'all') {
            $stgteil = StudiengangTeil::find($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL);
            $plan_title .= ' - ' . $stgteil->getDisplayName();
        }
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE && $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE != 'all') {
            $plan_title .= ' - ' . $this->semester->name;
        }
        return $plan_title;
    }

    public function index_action()
    {
        PageLayout::setHelpKeyword('Basis.TerminkalenderStundenplan');
        PageLayout::setTitle(_('Veranstaltungs-Stundenplan'));

        // build the sidebar:
        $this->buildSidebar($GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER);
        $sidebar = Sidebar::get();
        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Veranstaltungs-Stundenplan PDF'),
            'javascript:STUDIP.Fullcalendar.downloadPDF();',
            Icon::create('file-pdf')
        );

        $this->courses = $this->getFilteredCourses();
        $this->events = InstituteCalendarHelper::getEvents(
            $this->courses,
            $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT,
            $this->semester
        );
        $this->eventless_courses = InstituteCalendarHelper::getEventlessCourses($this->courses, $this->semester);
        $this->plan_title = $this->getPlanTitle();

        foreach ($this->events as $event) {
            $start_date_time = explode('T', $event['start']);
            $time_elements = explode(':', $start_date_time[1]);
            if (!$event['comform'] || $time_elements[0] % 2) {
                Sidebar::get()->getWidget('actions')->addLink(
                    _('Veranstaltungen außerhalb des Rasters'),
                    $this->nonconformURL(),
                    Icon::create('date-cycle')
                )->asDialog();

                break;
            }
        }
    }

    public function weekday_action($day_of_week)
    {
        PageLayout::setHelpKeyword('Basis.TerminkalenderStundenplan');
        PageLayout::setTitle(_('Veranstaltungs-Stundenplan'));

        $this->selected_weekday = $day_of_week;

        $fixed_day_of_week = ($day_of_week == 0) ? 7 : $day_of_week;
        $sub_days = date('w') - $fixed_day_of_week;
        $cal_date = new DateTime();
        if ($sub_days > 0) {
            $cal_date->sub(new DateInterval('P' . $sub_days . 'D'));
        } else {
            $cal_date->add(new DateInterval('P' . -$sub_days . 'D'));
        }

        //build the sidebar:
        $this->buildSidebar($GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER);
        $sidebar = Sidebar::get();
        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Veranstaltungs-Stundenplan PDF'),
            'javascript:STUDIP.Fullcalendar.downloadPDF("landscape", true);',
            Icon::create('file-pdf')
        );

        Sidebar::get()->getWidget('actions')->addLink(
            _('Spalten Anzeige'),
            $this->viewcolumnsURL($cal_date->format('w')),
            Icon::create('admin')
        )->asDialog('size=auto');

        $this->cal_date = $cal_date;
        $this->courses = $this->getFilteredCourses();
        $this->events = InstituteCalendarHelper::getEvents($this->courses, $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT, $this->semester, $day_of_week);
        $this->eventless_courses = InstituteCalendarHelper::getEventlessCourses($this->courses, $this->semester);
        $this->columns = InstituteCalendarHelper::getResourceColumns($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT, true);
        $this->plan_title = $this->getPlanTitle();

        foreach ($this->events as $event) {
            $start_date_time = explode('T', $event['start']);
            $time_elements = explode(':', $start_date_time[1]);
            if (!$event['conform'] || $time_elements[0] % 2) {
                Sidebar::get()->getWidget('actions')->addLink(
                    _('Veranstaltungen außerhalb des Rasters'),
                    $this->nonconformURL($day_of_week),
                    Icon::create('date-cycle')
                )->asDialog('size=medium');

                break;
            }
        }
    }

    public function remove_column_action($institute_id, $column_id, $week_day)
    {
        $inst_column = InstitutePlanColumn::find([$institute_id, $column_id]);
        if ($inst_column) {
            $col_name = $inst_column->name;

            if ($institute_id !== 'all') {

                $datafield_entries = DatafieldEntryModel::findBySQL(
                    "`datafield_id` = :df_id AND `content` LIKE :institut_id",
                    [
                        'df_id'       => InstituteCalendarHelper::COLUMN_DATAFIELD_ID,
                        'institut_id' => "%{$institute_id}%",
                    ]
                );
                foreach ($datafield_entries as $df) {
                    $df_content = unserialize($df->content);
                    foreach ($df_content as $cdate => $inst_col) {
                        if (!is_array($inst_col)) {
                            continue;
                        }
                        foreach ($inst_col as $inst => $col) {
                            if ($inst == $institute_id && $col == $column_id) {
                                $df_content[$cdate][$inst] = 0;
                            }
                        }
                    }
                    $df->content = serialize($df_content);
                    $df->store();
                }

                if ($inst_column->delete()) {
                    PageLayout::postSuccess(sprintf(
                        _('Die Spalte %s wurde gelöscht.'),
                        htmlReady($col_name)
                    ));
                }
            }
        }

        $redirect_url = $this->url_for('admin/courseplanning/weekday/' . $week_day);
        $this->relocate($redirect_url);
    }

    public function rename_column_action($column_id, $week_day)
    {
        PageLayout::setTitle(_('Spalte umbenennen'));

        $redirect_url = $this->url_for('admin/courseplanning/weekday/' . $week_day);
        $institute_id = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;

        if ($institute_id) {
            if (Request::submitted('save')) {
                $column_name = Request::get('column_name');
                if ($column_name) {
                    $instplan_col = InstitutePlanColumn::find([$institute_id, $column_id]);
                    if (!$instplan_col) {
                        $stored = InstituteCalendarHelper::addResourceColumn($institute_id, $column_name, $column_id);
                    } else {
                        $instplan_col->name = $column_name;
                        $stored = $instplan_col->store();
                    }
                    if ($stored) {
                        PageLayout::postSuccess(_('Die Spalte wurde umbenannt.'));
                        $this->relocate($redirect_url);
                        return false;
                    }
                }
            }

            $inst_columns = InstituteCalendarHelper::getResourceColumns($institute_id);
            foreach ($inst_columns as $inst_column) {
                if ($inst_column['id'] == $column_id) {
                    $column_name = $inst_column['title'];
                }
            }

            $this->column_id = $column_id;
            $this->column_name = $column_name;
            $this->week_day = $week_day;
        } else {
            PageLayout::postError(_('Bei der Ermittlung der gewählten Einrichtung ist ein Fehler aufgetreten.'));
            $this->relocate($redirect_url);
        }
    }

    public function viewcolumns_action($week_day)
    {
        PageLayout::setTitle(_('Spalten Anzeige'));

        $redirect_url = $this->url_for('admin/courseplanning/weekday/' . $week_day);
        $institute_id = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;

        $this->columns =  InstituteCalendarHelper::getResourceColumns($institute_id);
        $this->week_day = $week_day;

        if (Request::submitted('save')) {
            $vis_cols = [];
            $invis_cols = [];
            foreach ($this->columns as $col) {
                if ($col['id'] == 0) continue;
                if (in_array($col['id'], Request::getArray('column_view')) && $col['visible'] != '1' ) {
                    $vis_cols[] = $col['id'];
                } else if (!in_array($col['id'], Request::getArray('column_view')) && $col['visible'] != '0') {
                    $invis_cols[] = $col['id'];
                }
            }

            if (!empty($vis_cols)) {
                $changes = InstitutePlanColumn::setVisbilities($institute_id, $vis_cols, 1);
                if ($changes) PageLayout::postSuccess(sprintf(_('%s Spalte(n) sichtbar geschaltet.'), $changes));
            }
            if (!empty($invis_cols)) {
                $changes = InstitutePlanColumn::setVisbilities($institute_id, $invis_cols, 0);
                if ($changes) PageLayout::postSuccess(sprintf(_('%s Spalte(n) ausgeblendet.'), $changes));
            }

            if (Request::get('column_name')) {
                InstituteCalendarHelper::addResourceColumn($institute_id, Request::get('column_name'));
                PageLayout::postSuccess(_('Spalte hinzugefügt.'));
            }

            $this->relocate($redirect_url);
        }

        $this->institute_id = $institute_id;

    }

    public function nonconform_action($day_of_week = null)
    {
        $courses = $this->getFilteredCourses();
        $non_rasters = [];

        $min_time = explode(':',Config::get()->INSTITUTE_COURSE_PLAN_START_HOUR);
        $minbigtime = (int) $min_time[0];
        $max_time = explode(':',Config::get()->INSTITUTE_COURSE_PLAN_END_HOUR);
        $maxbigtime = (int) $max_time[0];

        foreach ($courses as $cid => $course_data) {
            $course = Course::find($cid);

            foreach (SeminarCycleDate::findBySeminar($cid) as $cycle_date) {
                if (is_numeric($day_of_week) && $day_of_week != $cycle_date['weekday']) {
                    continue;
                }

                $conform = true;

                $start_time = explode(':', $cycle_date['start_time']);
                $bigtime = (int) $start_time[0];
                if ($bigtime > $maxbigtime || $bigtime < $minbigtime) {
                    $conform = false;
                } elseif ($bigtime % 2) {
                    $bigtime--;
                }
                $start_time = "{$bigtime}:00:00";

                $end_time = explode(':', $start_time);
                $end_time[0] += 2;
                $end_time = implode(':', $end_time);

                if (!$conform
                    || ($start_time != $cycle_date['start_time'] && $end_time != $cycle_date['end_time'])
                ) {
                    $non_rasters[] = [
                        'cid'   => $cid,
                        'nr'    => $course->veranstaltungsnummer,
                        'name'  => $course->name,
                        'start' => $cycle_date['start_time'],
                        'end'   => $cycle_date['end_time'],
                    ];
                }
            }
        }

        $this->non_conform_dates = $non_rasters;
    }

    public function add_event_action()
    {
        $course_id = Request::option('course_id');
        $begin = Request::get('begin');
        $end = Request::get('end');
        $success = false;
        $cycle_start = null;
        $cycle_end = null;

        if ($course_id && $this->semester) {
            $begin_date = new DateTime($begin);
            $end_date = new DateTime($end);
            $begin_date->setTimezone(new DateTimeZone('UTC'));
            $end_date->setTimezone(new DateTimeZone('UTC'));

            $course = Course::find($course_id);
            $this->seminar = new Seminar($course);
            if ($course->isOpenEnded() || count($course->semesters) > 1) { // course over more than one semester
                $start_weeks = $this->seminar->end_semester->getStartWeeks();

                $sem_duration = $this->semester->ende - $this->semester->beginn;
                $sem_weeks        = $this->semester->getStartWeeks($sem_duration);
                $sem_weeks_start = explode(' Semesterwoche ', $sem_weeks[0]);
                $sem_weeks_end = explode(' Semesterwoche ', end($sem_weeks));

                foreach ($start_weeks as $week_num => $week_text) {
                    if ($cycle_start && $cycle_end) break;
                    if (strstr($week_text, $sem_weeks_start[1]) !== false) {
                        $cycle_start = $week_num;
                    }
                    if (strstr($week_text, $sem_weeks_end[1]) !== false) {
                        $cycle_end = $week_num;
                    }
                }
            }

            $cycle              = new SeminarCycleDate();
            $cycle->seminar_id  = $course_id;
            $cycle->weekday     = $begin_date->format('w');
            $cycle->description = '';
            $cycle->sws         = floatVal(0.0);
            $cycle->cycle       = 0;
            $cycle->week_offset = $cycle_start?$cycle_start:0;
            $cycle->end_offset  = $cycle_end?$cycle_end:intVal(count($this->semester->getStartWeeks()) -1);
            $cycle->start_time  = $begin_date->format('H:i:s');
            $cycle->end_time    = $end_date->format('H:i:s');
            $success = $cycle->store();
        }

        if ($success) {
            if (Request::submitted('resource_id')) {
                InstituteCalendarHelper::setCourseEventcolumn(
                    Course::find($cycle->seminar_id),
                    $cycle->metadate_id,
                    $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT,
                    Request::get('resource_id', '0')
                );
            }
            echo json_encode(InstituteCalendarHelper::getCycleEvent($cycle, $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT));
        }

        $this->render_nothing();
        return;
    }

    public function pick_color_action($metadate_id, $from_action, $weekday = null)
    {
        PageLayout::setTitle(_('Farbwähler'));

        $cdate = SeminarCycleDate::find($metadate_id);
        if ($cdate) {
            $course = Course::find($cdate->Seminar_id);
            $semtype = $course->getSemType();

            if (Request::submitted('save') && Request::submitted('event_color')) {
                if (Request::get('event_color_semtype')) {
                    if (InstituteCalendarHelper::setSemtypeEventcolor(
                        Course::find($cdate->seminar_id),
                        $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT,
                        Request::get('event_color')
                    )) {
                        PageLayout::postSuccess(sprintf(
                            _('Die Farbe wurde allen VA des Typs %s zugewiesen.'),
                            htmlReady($semtype['name'])
                        ));
                    } else {
                        PageLayout::postError(_('Bei der Farbzuweisung ist ein Fehler aufgetreten.'));
                    }
                } else {
                    if (InstituteCalendarHelper::setCourseEventcolor(
                        Course::find($cdate->seminar_id),
                        $metadate_id,
                        $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT,
                        Request::get('event_color')
                    )) {
                        PageLayout::postSuccess(_('Die Farbe wurde der VA zugewiesen.'));
                    } else {
                        PageLayout::postError(_('Bei der Farbzuweisung ist ein Fehler aufgetreten.'));
                    }
                }

                $this->relocate($this->action_url($from_action, $weekday));
                return;
            }

            $course_colors = InstituteCalendarHelper::getCourseEventcolors($course);
            if (array_key_exists($metadate_id, $course_colors)) {
                $this->color = $course_colors[$metadate_id][$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT];
            } else {
                $this->color = '#28497c';
            }

            $this->semtype = $semtype['name'];
        }
        $this->metadate_id = $metadate_id;
        $this->from_action = $from_action;
        $this->weekday     = $weekday;
    }

    public function move_event_action()
    {
        $metadate_id = Request::option('cycle_id');
        $begin = Request::get('begin');
        $end = Request::get('end');
        $success = false;

        $cdate = SeminarCycleDate::find($metadate_id);
        if ($cdate) {
            if (Request::submitted('resource_id')) {
                InstituteCalendarHelper::setCourseEventcolumn(
                    Course::find($cdate->seminar_id),
                    $metadate_id,
                    $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT,
                    Request::get('resource_id', '0')
                );
            }

            $begin_date = new DateTime($begin);
            $end_date = new DateTime($end);
            $begin_date->setTimezone(new DateTimeZone('UTC'));
            $end_date->setTimezone(new DateTimeZone('UTC'));
            $weekday = $begin_date->format('w');
            $cdate->start_time = $begin_date->format('H:i:s');
            $cdate->end_time = $end_date->format('H:i:s');
            $cdate->weekday = $weekday;
            $success = $cdate->store();
        }

        $this->render_text($success);
    }

    private function getFilteredCourses()
    {
        if (Request::get('sortFlag')) {
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT_FLAG', Request::get('sortFlag') == 'asc' ? 'DESC' : 'ASC');
        }
        if (Request::option('sortby')) {
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT', Request::option('sortby'));
        }

        $this->sortby = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT;
        $this->sortFlag = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT_FLAG ?: 'ASC';

        return $this->getCourses([
            'sortby'      => $this->sortby,
            'sortFlag'    => $this->sortFlag,
            'view_filter' => [],//$this->view_filter,
            'typeFilter'  => $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER,
            'datafields' => []//$this->getDatafieldFilters()
        ], Request::get('display') === 'all');
    }

    /**
     * This method is responsible for building the sidebar.
     *
     * Depending on the sidebar elements the user has selected some of those
     * elements are shown or not. To find out what elements
     * the user has selected the user configuration is accessed.
     *
     * @param string courseTypeFilterConfig The selected value for the course type filter field, defaults to null.
     * @return null This method does not return any value.
     */
    private function buildSidebar($courseTypeFilterConfig = null)
    {
        $this->setInstSelector();
        $this->setSemesterSelector();
        $this->setStgteilSelector();
        $this->setCourseTypeWidget($courseTypeFilterConfig);
        $this->setTeacherWidget();
    }


    /**
     * Returns the default element configuration.
     *
     * @return array containing the default element configuration
     */
    private function getActiveElementsDefault()
    {
        return [
            'search'     => false,
            'institute'  => true,
            'semester'   => true,
            'courseType' => true,
            'teacher'    => true,
            'viewFilter' => false,
        ];
    }

    /**
     * Returns the active element configuration of the current user.
     *
     * @return array containing the active element configuration
     */
    private function getActiveElements()
    {
/*         $active_elements = $GLOBALS['user']->cfg->ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS;

        if ($active_elements) {
            return json_decode($active_elements, true);
        } else { */
            return $this->getActiveElementsDefault();
        //}
    }


    /**
     * Adds the institutes selector to the sidebar
     */
    private function setInstSelector()
    {
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(
            _('Einrichtung'),
            $this->url_for('admin/courseplanning/set_selection/' . $this->selected_weekday),
            'institute'
        );
        $list->class = 'institute-list';

        foreach ($this->insts as $institut) {
            if (!$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT ||$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT == 'all') {
                $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', $institut['Institut_id']);
            }

            $list->addElement(
                new SelectElement(
                    $institut['Institut_id'],
                    (!$institut['is_fak'] ? ' ' : '') . $institut['Name'],
                    $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id']
                ),
                "select-{$institut['Institut_id']}"
            );

            //check if the institute is a faculty.
            //If true, then add another option to display all courses
            //from that faculty and all its institutes.

            //$institut is an array, we can't use the method isFaculty() here!
            if ($institut['fakultaets_id'] == $institut['Institut_id']) {
                $list->addElement(
                    new SelectElement(
                        $institut['Institut_id'] . '_withinst', //_withinst = with institutes
                        ' ' . $institut['Name'] . ' +' . _('Institute'),
                        ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id'] && $GLOBALS['user']->cfg->MY_INSTITUTES_INCLUDE_CHILDREN)
                    ),
                    'select-' . $institut['Name'] . '-with_institutes'
                );
            }
        }

        $sidebar->addWidget($list, 'filter_institute');
    }

    /**
     * Adds the semester selector to the sidebar
     */
    private function setSemesterSelector()
    {
        $semesters = array_reverse(Semester::getAll());
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Semester'), $this->url_for('admin/courseplanning/set_selection/' . $this->selected_weekday), 'sem_select');
        foreach ($semesters as $semester) {
            if (!$GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE ||$GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE == 'all') {
                $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_CYCLE', $semester->id);
            }
            $list->addElement(new SelectElement(
                $semester->id,
                $semester->name,
                $semester->id === $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE
            ), "sem_select-{$semester->id}");
        }

        $sidebar->addWidget($list, 'filter_semester');
    }

        /**
     * Adds the studiengangteil selector to the sidebar
     */
    private function setStgteilSelector()
    {
        $stgteile = StudiengangTeil::getAllEnriched('fach_name','ASC', ['mvv_fach_inst.institut_id' => $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT]);
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Studiengangteil'), $this->url_for('admin/courseplanning/set_selection/' . $this->selected_weekday), 'stgteil_select');
        if ($this->selected_weekday) {
            $list->addElement(new SelectElement('all', _('Alle')), 'stgteil_select-all');
        }
        foreach ($stgteile as $stgteil) {
            if (!$GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL || ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL == 'all' && !$this->selected_weekday)) {
                $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_STGTEIL', $stgteil->id);
            }
            $list->addElement(new SelectElement(
                $stgteil->id,
                $stgteil->getDisplayName(),
                $stgteil->id === $GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL
            ), 'stgteil_select-' . $stgteil->id);
        }

        $sidebar->addWidget($list, 'filter_stgteil');
    }

    /**
     * Returns a course type widthet depending on all available courses and theirs types
     * @param string $selected
     * @param array $params
     * @return ActionsWidget
     */
    private function setCourseTypeWidget($selected = 'all')
    {
        $sidebar = Sidebar::get();
        $this->url = $this->url_for('admin/courseplanning/set_course_type');
        $this->types = [];
        $this->selected = $selected;

        $list = new SelectWidget(
            _('Veranstaltungstypfilter'),
            $this->url_for('admin/courseplanning/set_course_type'),
            'course_type'
        );
        $list->addElement(new SelectElement(
            'all', _('Alle'), $selected === 'all'
        ), 'course-type-all');
        foreach ($GLOBALS['SEM_CLASS'] as $class_id => $class) {
            if ($class['studygroup_mode']) {
                continue;
            }

            $element = new SelectElement(
                $class_id,
                $class['name'],
                $selected === (string)$class_id
            );
            $list->addElement(
                $element->setAsHeader(),
                'course-type-' . $class_id
            );

            foreach ($class->getSemTypes() as $id => $result) {
                $element = new SelectElement(
                    $class_id . '_' . $id,
                    $result['name'],
                    $selected === $class_id . '_' . $id
                );
                $list->addElement(
                    $element->setIndentLevel(1),
                    'course-type-' . $class_id . '_' . $id
                );
            }
        }
        $sidebar->addWidget($list, 'filter-course-type');
    }

    /**
     * Returns a widget to selected a specific teacher
     * @param array $teachers
     * @return ActionsWidget|null
     */
    private function setTeacherWidget()
    {
        if (!$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT || $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === "all") {
            return;
        }
        $teachers = DBManager::get()->fetchAll("
            SELECT auth_user_md5.*, user_info.*
            FROM auth_user_md5
                LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id)
                INNER JOIN user_inst ON (user_inst.user_id = auth_user_md5.user_id)
                INNER JOIN Institute ON (Institute.Institut_id = user_inst.Institut_id)
            WHERE (Institute.Institut_id = :institut_id OR Institute.fakultaets_id = :institut_id)
                AND auth_user_md5.perms = 'dozent'
            ORDER BY auth_user_md5.Nachname ASC, auth_user_md5.Vorname ASC
        ", [
            'institut_id' => $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT
        ],
        function ($data) {
            $ret['user_id'] = $data['user_id'];
            unset($data['user_id']);
            $ret['fullname'] = User::build($data)->getFullName("full_rev");
            return $ret;
        }
        );


        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Lehrendenfilter'), $this->url_for('admin/courseplanning/index'), 'teacher_filter');
        $list->addElement(new SelectElement('all', _('alle'), Request::get('teacher_filter') == 'all'), 'teacher_filter-all');

        foreach ($teachers as $teacher) {
            $list->addElement(new SelectElement(
                $teacher['user_id'],
                $teacher['fullname'],
                $GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER === $teacher['user_id']
            ), 'teacher_filter-' . $teacher['user_id']);
        }

        $sidebar->addWidget($list, 'filter_teacher');
    }

    /**
     * Returns the teacher for a given cours
     *
     * @param String $course_id Id of the course
     * @return array of user infos [user_id, username, Nachname, fullname]
     */
    private function getTeacher($course_id)
    {
        $teachers   = CourseMember::findByCourseAndStatus($course_id, 'dozent');
        $collection = SimpleCollection::createFromArray($teachers);
        return $collection->map(function (CourseMember $teacher) {
            return [
                'user_id'  => $teacher->user_id,
                'username' => $teacher->username,
                'Nachname' => $teacher->nachname,
                'fullname' => $teacher->getUserFullname('no_title_rev'),
            ];
        });
    }

    /**
     * Returns all courses matching set criteria.
     *
     * @param Array $params Additional parameters
     * @param String $parent_id Fetch only subcourses of this parent
     * @param display_all : boolean should we show all courses or check for a limit of 500 courses?
     * @return Array of courses
     */
    private function getCourses($params = [], $display_all = false)
    {
        // Init
        if ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === "all") {
            $inst = new SimpleCollection($this->insts);
            $inst->filter(function ($a) use (&$inst_ids) {
                $inst_ids[] = $a->Institut_id;
            });
        } else {
            //We must check, if the institute ID belongs to a faculty
            //and has the string _i appended to it.
            //In that case we must display the courses of the faculty
            //and all its institutes.
            //Otherwise we just display the courses of the faculty.

            $inst_id = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;

            $institut = new Institute($inst_id);

            if (!$institut->isFaculty() || $GLOBALS['user']->cfg->MY_INSTITUTES_INCLUDE_CHILDREN) {
                // If the institute is not a faculty or the child insts are included,
                // pick the institute IDs of the faculty/institute and of all sub-institutes.
                $inst_ids[] = $inst_id;
                if ($institut->isFaculty()) {
                    foreach ($institut->sub_institutes->pluck("Institut_id") as $institut_id) {
                        $inst_ids[] = $institut_id;
                    }
                }
            } else {
                // If the institute is a faculty and the child insts are not included,
                // pick only the institute id of the faculty:
                $inst_ids[] = $inst_id;
            }
        }

        $active_elements = $this->getActiveElements();

        $filter = AdminCourseFilter::get(true);

        $filter->where("sem_classes.studygroup_mode = '0'");


        if (is_object($this->semester)) {
            $filter->filterBySemester($this->semester->getId());
        }
        if ($params['typeFilter'] && $params['typeFilter'] !== "all") {
            list($class_filter,$type_filter) = explode('_', $params['typeFilter']);
            if (!$type_filter && !empty($GLOBALS['SEM_CLASS'][$class_filter])) {
                $type_filter = array_keys($GLOBALS['SEM_CLASS'][$class_filter]->getSemTypes());
            }
            $filter->filterByType($type_filter);
        }

        if ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER && ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER !== "all")) {
            $filter->filterByDozent($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER);
        }
        if ($active_elements['institute'] && $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT !== "all") {
            $filter->filterByInstitute($inst_ids);
        }

        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL && $GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL !== 'all') {
            $filter->filterByStgTeil($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL);
        }

        $filter->storeSettings();
        $this->count_courses = $filter->countCourses();
        if ($this->count_courses && ($this->count_courses <= $filter->max_show_courses || $display_all)) {
            $courses = $filter->getCourses();
        } else {
            return [];
        }

        if (in_array('contents', $params['view_filter'])) {
            $sem_types = SemType::getTypes();
        }

        $seminars   = array_map('reset', $courses);
        $visit_data = get_objects_visits(array_keys($seminars), 'sem', null, null, MyRealmModel::getDefaultModules());

        if (!empty($seminars)) {
            foreach ($seminars as $seminar_id => $seminar) {
                $seminars[$seminar_id]['seminar_id'] = $seminar_id;
                $seminars[$seminar_id]['obj_type'] = 'sem';
                $dozenten = $this->getTeacher($seminar_id);
                $seminars[$seminar_id]['dozenten'] = $dozenten;

                if (in_array('contents', $params['view_filter'])) {
                    $seminars[$seminar_id]['visitdate'] = $visit_data[$seminar_id][0]['visitdate'];
                    $seminars[$seminar_id]['last_visitdate'] = $visit_data[$seminar_id][0]['last_visitdate'];
                    $seminars[$seminar_id]['sem_class'] = $sem_types[$seminar['status']]->getClass();
                    $seminars[$seminar_id]['navigation'] = MyRealmModel::getAdditionalNavigations(
                        $seminar_id,
                        $seminars[$seminar_id],
                        $seminars[$seminar_id]['sem_class'],
                        $GLOBALS['user']->id,
                        $visit_data[$seminar_id]
                    );
                }
                //add last activity column:
                if (in_array('last_activity', $params['view_filter'])) {
                    $seminars[$seminar_id]['last_activity'] = lastActivity($seminar_id);
                }
                if ($this->selected_action == 17) {
                    $seminars[$seminar_id]['admission_locked'] = false;
                    if ($seminar['course_set']) {
                        $set = new CourseSet($seminar['course_set']);
                        if (!is_null($set) && $set->hasAdmissionRule('LockedAdmission')) {
                            $seminars[$seminar_id]['admission_locked'] = 'locked';
                        } else {
                            $seminars[$seminar_id]['admission_locked'] = 'disable';
                        }
                        unset($set);
                    }
                }
            }
        }

        return $seminars;
    }


    /**
     * Set the selected institute or semester
     */
    public function set_selection_action($week_day = NULL)
    {
        if (Request::option('institute')) {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_TEACHERFILTER', null);
            $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_STGTEIL', null);
            $inst = explode('_', Request::option('institute'));
            $GLOBALS['user']->cfg->store('MY_INSTITUTES_DEFAULT', $inst[0]);

            if ($inst[1] === 'withinst') {
                $GLOBALS['user']->cfg->store('MY_INSTITUTES_INCLUDE_CHILDREN', 1);
            } else {
                $GLOBALS['user']->cfg->store('MY_INSTITUTES_INCLUDE_CHILDREN', 0);
            }

            PageLayout::postSuccess(_('Die gewünschte Einrichtung wurde ausgewählt!'));
        }

        if (Request::option('sem_select')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_CYCLE', Request::option('sem_select'));
            if (Request::option('sem_select') !== 'all') {
                PageLayout::postSuccess(sprintf(
                    _('Das %s wurde ausgewählt'),
                    htmlReady(Semester::find(Request::option('sem_select'))->name)
                ));
            } else {
                PageLayout::postSuccess(_('Semesterfilter abgewählt'));
            }
        }

        if (Request::option('stgteil_select')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_STGTEIL', Request::option('stgteil_select'));
            if (Request::option('stgteil_select') !== 'all') {
                PageLayout::postSuccess(sprintf(
                    _('Der Studiengangteil %s wurde ausgewählt'),
                    htmlReady(StudiengangTeil::find(Request::option('stgteil_select'))->getDisplayName())
                ));
            } else {
                PageLayout::postSuccess(_('Studiengangteilfilter abgewählt'));
            }
        }

        if ($week_day) {
            $this->redirect('admin/courseplanning/weekday/' . $week_day);
        } else {
            $this->redirect('admin/courseplanning/index');
        }
    }

    /**
     * Set the selected course type filter and store the selection in configuration
     */
    public function set_course_type_action()
    {
        if (Request::option('course_type')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_TYPE_FILTER', Request::option('course_type'));
            PageLayout::postSuccess(_('Der gewünschte Veranstaltungstyp wurde übernommen!'));
        }
        $this->redirect('admin/courseplanning/index');
    }
}
