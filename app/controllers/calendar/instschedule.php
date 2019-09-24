<?php
# Lifter010: TODO

/*
 * This controller displays an institute-calendar for seminars
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'app/models/calendar/schedule.php';
require_once 'app/models/calendar/instschedule.php';

/**
 * Controller of the institutes' schedules.
 * *
 * @since      2.0
 */
class Calendar_InstscheduleController extends AuthenticatedController
{
    /**
     * this action is the main action of the schedule-controller, setting the environment for the timetable,
     * accepting a comma-separated list of days.
     *
     * @param  string  a list of an arbitrary mix of the numbers 0-6, separated with a comma (e.g. 1,2,3,4,5 (for Monday to Friday, the default))
     */
    function index_action($days = false)
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            $inst_mode = true;
        }
        $my_schedule_settings = $GLOBALS['user']->cfg->SCHEDULE_SETTINGS;
        // set the days to be displayed
        if ($days === false) {
            if (Request::getArray('days')) {
                $this->days = array_keys(Request::getArray('days'));
            } else {
                $this->days = CalendarScheduleModel::getDisplayedDays($my_schedule_settings['glb_days']);
            }
        } else {
            $this->days = explode(',', $days);
        }

        // try to find the correct institute-id
        $institute_id = Request::option('institute_id', Context::getId());

        if (!$institute_id) {
            $institute_id = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;
        }

        if (!$institute_id || (in_array(get_object_type($institute_id), words('inst fak')) === false)) {
            throw new Exception(sprintf(_('Kann Einrichtungskalendar nicht anzeigen!'
                . 'Es wurde eine ungültige Instituts-Id übergeben (%s)!', $institute_id)));
        }

        // load semester-data and current semester
        $this->semesters = SemesterData::getAllSemesterData();

        if (Request::option('semester_id')) {
            $this->current_semester = SemesterData::getSemesterData(Request::option('semester_id'));
        } else {
            $this->current_semester = SemesterData::getCurrentSemesterData();
        }

        $this->entries = (array)CalendarInstscheduleModel::getInstituteEntries($GLOBALS['user']->id,
            $this->current_semester, 8, 20, $institute_id, $this->days);

        Navigation::activateItem('/course/main/schedule');
        PageLayout::setHelpKeyword('Basis.TerminkalenderStundenplan');
        PageLayout::setTitle(Context::getHeaderLine().' - '._('Veranstaltungs-Stundenplan'));

        $zoom = Request::int('zoom', 0);
        $this->controller = $this;
        $this->calendar_view = new CalendarWeekView($this->entries, 'instschedule');
        $this->calendar_view->setHeight(40 + (20 * $zoom));
        $this->calendar_view->setRange($my_schedule_settings['glb_start_time'], $my_schedule_settings['glb_end_time']);
        $this->calendar_view->groupEntries();  // if enabled, group entries with same start- and end-date

        URLHelper::addLinkParam('zoom', $zoom);
        URLHelper::addLinkParam('semester_id', $this->current_semester['semester_id']);

        $style_parameters = [
            'whole_height' => $this->calendar_view->getOverallHeight(),
            'entry_height' => $this->calendar_view->getHeight()
        ];

        $factory = new Flexi_TemplateFactory($this->dispatcher->trails_root . '/views');
        PageLayout::addStyle($factory->render('calendar/stylesheet', $style_parameters));

        if (Request::option('printview')) {
            PageLayout::addStylesheet('print.css');

            // remove all stylesheets that are not used for printing to have a more reasonable printing preview
            PageLayout::addHeadElement('script', [], "$('head link[media=screen]').remove();");
        } else {
            PageLayout::addStylesheet('print.css', ['media' => 'print']);
        }

        Helpbar::Get()->addPlainText(_('Information'), _('Der Stundenplan zeigt die regelmäßigen Veranstaltungen dieser Einrichtung.'), Icon::create('info'));

        $views = new ViewsWidget();
        $views->addLink(_('klein'), URLHelper::getURL('', ['zoom' => 0]))->setActive($zoom == 0);
        $views->addLink(_('mittel'), URLHelper::getURL('', ['zoom' => 2]))->setActive($zoom == 2);
        $views->addLink(_('groß'), URLHelper::getURL('', ['zoom' => 4]))->setActive($zoom == 4);
        $views->addLink(_('extra groß'), URLHelper::getURL('', ['zoom' => 7]))->setActive($zoom == 7);

        Sidebar::Get()->addWidget($views);
        $actions = new ActionsWidget();
        $actions->addLink(_('Druckansicht'),
            $this->url_for('calendar/instschedule/index/'. implode(',', $this->days),
                ['printview'    => 'true',
                 'semester_id'  => $this->current_semester['semester_id']]),
            Icon::create('print', 'clickable'),
            ['target' => '_blank']);

        // Only admins should have the ability to change their schedule settings here - they have no other schedule
        if ($GLOBALS['perm']->have_perm('admin')) {
            $actions->addLink(_("Darstellung ändern"),
                $this->url_for('calendar/schedule/settings'),
                Icon::create('admin', 'clickable'),
                ['data-dialog' => '']
            );

            // only show this setting if we have indeed a faculty where children might exist
            if (Context::get()->isFaculty()) {
                if ($GLOBALS['user']->cfg->MY_INSTITUTES_INCLUDE_CHILDREN) {
                    $actions->addLink(_("Untergeordnete Institute ignorieren"),
                        $this->url_for('calendar/instschedule/include_children/0'),
                        Icon::create('checkbox-checked', 'clickable')
                    );
                } else {
                    $actions->addLink(_("Untergeordnete Institute einbeziehen"),
                        $this->url_for('calendar/instschedule/include_children/1'),
                        Icon::create('checkbox-unchecked', 'clickable')
                    );
                }
            }
        }

        Sidebar::Get()->addWidget($actions);
        $semesterSelector = new SemesterSelectorWidget($this->url_for('calendar/instschedule'), 'semester_id', 'post');
        $semesterSelector->includeAll(false);
        Sidebar::Get()->addWidget($semesterSelector);

    }

    /**
     * Returns an HTML fragment of a grouped entry in the schedule of an institute.
     *
     * @param string $start the start time of the group, e.g. "1000"
     * @param string $end   the end time of the group, e.g. "1200"
     * @param string $seminars  the IDs of the courses
     * @param string $day  numeric day to show
     *
     * @return void
     */
    function groupedentry_action($start, $end, $seminars, $day)
    {
        $this->response->add_header('Content-Type', 'text/html; charset=utf-8');

        // strucutre of an id: seminar_id-cycle_id
        // we do not need the cycle id here, so we trash it.
        $seminar_list = [];

        foreach (explode(',', $seminars) as $seminar) {
            $zw = explode('-', $seminar);
            $this->seminars[$zw[0]] = Seminar::getInstance($zw[0]);
        }

        $this->start = mb_substr($start, 0, 2) .':'. mb_substr($start, 2, 2);
        $this->end   = mb_substr($end, 0, 2) .':'. mb_substr($end, 2, 2);

        $day_names  = [_("Montag"),_("Dienstag"),_("Mittwoch"),
            _("Donnerstag"),_("Freitag"),_("Samstag"),_("Sonntag")];

        $this->day   = $day_names[(int)$day];

        $this->render_template('calendar/instschedule/_entry_details');
    }

    /**
     * Toggle config setting to include children in schedule for the current faculty
     *
     * @param  int $include_childs  0 / false to exclude children 1 / true to include them
     */
    function include_children_action($include_childs)
    {
        $GLOBALS['user']->cfg->store('MY_INSTITUTES_INCLUDE_CHILDREN', $include_childs ? 1 : 0);

        $this->redirect('calendar/instschedule/index');
    }
}
