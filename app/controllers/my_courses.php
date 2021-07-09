<?php
/**
 * my_courses.php - Controller for user and seminar related
 * pages under "Meine Veranstaltungen"
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author    David Siegfried <david@ds-labs.de>
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category  Stud.IP
 * @since     3.1
 */
require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';

class MyCoursesController extends AuthenticatedController
{
    private $performance_timer = null;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['perm']->have_perm('admin')) {
            $this->redirect('admin/courses/index');
            return;
        }

        // we are defintely not in an lecture or institute
        closeObject();
        $_SESSION['links_admin_data'] = '';

        // measure performance of #index_action
        if ($action === 'index') {
            $this->performance_timer = Metrics::startTimer();
        }
    }

    public function after_filter($action, $args)
    {
        parent::after_filter($action, $args);

        // send performance metric
        if (isset($this->performance_timer)) {
            $timer = $this->performance_timer;
            $timer('core.my_courses_time');
        }
    }

    /**
     * Autor / Tutor / Teacher action
     */
    public function index_action()
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        if ($GLOBALS['perm']->have_perm('admin')) {
            $this->redirect('my_courses/admin');
            return;
        }

        Navigation::activateItem('/browse/my_courses/list');
        PageLayout::setHelpKeyword('Basis.MeineVeranstaltungen');
        PageLayout::setTitle(_('Meine Veranstaltungen'));
        SkipLinks::addIndex(_('Meine Veranstaltungen'), 'mycourses');

        $this->sem_data = Semester::getAllAsArray();

        $sem_key     = $this->getSemesterKey();
        $group_field = $this->getGroupField();

        $sem_courses  = MyRealmModel::getPreparedCourses($sem_key, [
            'group_field'         => $group_field,
            'order_by'            => null,
            'order'               => 'asc',
            'studygroups_enabled' => Config::get()->MY_COURSES_ENABLE_STUDYGROUPS,
            'deputies_enabled'    => Config::get()->DEPUTIES_ENABLE,
        ]);

        // Waiting list
        $this->waiting_list = MyRealmModel::getWaitingList($GLOBALS['user']->id);

        // Deputies
        $this->my_bosses                   = Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE ? Deputy::findDeputyBosses() : [];
        $this->deputies_edit_about_enabled = Config::get()->DEPUTIES_EDIT_ABOUT_ENABLE;

        // Check for new contents
        if ($tabularasa = $this->flash['tabularasa']) {
            $details = [];
            if ($this->check_for_new($sem_courses, $group_field)) {
                $details[] = sprintf(
                    _('Seit Ihrem letzten Seitenaufruf (%s) sind allerdings neue Inhalte hinzugekommen.'),
                    reltime($tabularasa)
                );
            }

            PageLayout::postSuccess(_('Alles als gelesen markiert!'), $details);
        }

        $this->setupSidebar($sem_key, $group_field, $this->check_for_new($sem_courses, $group_field));

        $temp_courses = [];
        $groups = [];
        if (is_array($sem_courses)) {
            foreach ($sem_courses as $_outer_index => $_outer) {
                if ($group_field === 'sem_number') {
                    $_courses = [];

                    foreach ($_outer as $course) {
                        $_courses[$course['seminar_id']] = $course;
                        if ($course['children']) {
                            foreach ($course['children'] as $child) {
                                $_courses[$child['seminar_id']] = $child;
                            }
                        }
                    }

                    $groups[] = [
                        'id' => $_outer_index,
                        'name' => (string)$this->sem_data[$_outer_index]['name'],
                        'data' => [
                            [
                                'id' => md5($_outer_index),
                                'label' => false,
                                'ids' => array_keys($_courses),
                            ],
                        ],
                    ];
                    $temp_courses = array_merge($temp_courses, $_courses);
                } else {
                    $count = 1;
                    $_groups = [];
                    foreach ($_outer as $_inner_index => $_inner) {
                        $_courses = [];

                        foreach ($_inner as $course) {
                            $_courses[$course['seminar_id']] = $course;
                            if ($course['children']) {
                                foreach ($course['children'] as $child) {
                                    $_courses[$child['seminar_id']] = $child;
                                }
                            }
                        }

                        $label = $_inner_index;
                        if ($group_field === 'sem_tree_id' && !$label) {
                            $label = _('keine Zuordnung');
                        } elseif ($group_field === 'gruppe') {
                            $label = _('Gruppe') . ' ' . $count++;
                        }

                        $_groups[] = [
                            'id' => md5($_outer_index . $_inner_index),
                            'label' => $label,
                            'ids' => array_keys($_courses),
                        ];

                        $temp_courses = array_merge($temp_courses, $_courses);
                    }

                    $groups[] = [
                        'id' => $_outer_index,
                        'name' => (string)$this->sem_data[$_outer_index]['name'],
                        'data' => $_groups,
                    ];
                }
            }
        }

        $data = [
            'courses' => $this->sanitizeNavigations(array_map([$this, 'convertCourse'], $temp_courses)),
            'groups'  => $groups,
            'user_id' => $GLOBALS['user']->id,
            'config'  => [
                'allow_dozent_visibility'  => Config::get()->ALLOW_DOZENT_VISIBILITY,
                'open_groups'              => $GLOBALS['user']->cfg->MY_COURSES_OPEN_GROUPS,
                'sem_number'               => Config::get()->IMPORTANT_SEMNUMBER,
                'display_type'             => Config::get()->MY_COURSES_ALLOW_TILED_DISPLAY && $GLOBALS['user']->cfg->MY_COURSES_TILED_DISPLAY ? 'tiles' : 'tables',
                'responsive_type'          => Config::get()->MY_COURSES_ALLOW_TILED_DISPLAY && $GLOBALS['user']->cfg->MY_COURSES_TILED_DISPLAY_RESPONSIVE ? 'tiles' : 'tables',
                'navigation_show_only_new' => $GLOBALS['user']->cfg->MY_COURSES_SHOW_NEW_ICONS_ONLY,
                'group_by'                 => $this->getGroupField(),

            ],
        ];

        PageLayout::addHeadElement(
            'script',
            ['type' => 'text/javascript'],
            'window.STUDIP.MyCoursesData = ' . json_encode($data) . ';'
        );
    }

    /**
     * PDF export of course overview
     */
    public function courseexport_action()
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $this->with_modules = Request::bool('modules');

        $this->sem_data = Semester::getAllAsArray();

        $this->group_field = 'sem_number';

        // Needed parameters for selecting courses
        $params = [
            'group_field'         => $this->group_field,
            'order_by'            => null,
            'order'               => 'asc',
            'studygroups_enabled' => Config::get()->MY_COURSES_ENABLE_STUDYGROUPS,
            'deputies_enabled'    => Config::get()->DEPUTIES_ENABLE,
        ];

        $this->sem_courses  = MyRealmModel::getPreparedCourses('all', $params);

        $factory  = $this->get_template_factory();
        $template = $factory->open('my_courses/courseexport');
        $template->set_attributes($this->get_assigned_variables());
        $template->image_style = 'height: 6px; width: 8px;';

        $doc = new ExportPDF();
        $doc->addPage();
        $doc->SetFont('helvetica', '', 10);
        $doc->writeHTML($template->render(), false, false, true);

        $this->render_pdf($doc, 'courseexport.pdf');
    }

    /**
     * Seminar group administration - cluster your seminars by colors or
     * change grouping mechanism
     */
    public function groups_action($sem = null, $studygroups = false)
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $this->title = _('Meine Veranstaltungen') . ' - ' . _('Farbgruppierungen');

        PageLayout::setTitle($this->title);


        PageLayout::setHelpKeyword('Basis.VeranstaltungenOrdnen');
        Navigation::activateItem('/browse/my_courses/list');

        $this->current_semester = $sem ?: Semester::findCurrent()->semester_id;
        $this->semesters = Semester::findAllVisible();

        $forced_grouping = Config::get()->MY_COURSES_FORCE_GROUPING;
        if ($forced_grouping == 'not_grouped') {
            $forced_grouping = 'sem_number';
        }

        $no_grouping_allowed = ($forced_grouping == 'sem_number' || !in_array($forced_grouping, getValidGroupingFields()));

        $group_field = $GLOBALS['user']->cfg->MY_COURSES_GROUPING ? : $forced_grouping;

        $groups     = [];
        $add_fields = '';
        $add_query  = '';

        if ($group_field == 'sem_tree_id') {
            $add_fields = ', sem_tree_id';
            $add_query  = "LEFT JOIN seminar_sem_tree sst ON (sst.seminar_id=seminare.Seminar_id)";
        } else if ($group_field == 'dozent_id') {
            $add_fields = ', su1.user_id as dozent_id';
            $add_query  = "LEFT JOIN seminar_user as su1 ON (su1.seminar_id=seminare.Seminar_id AND su1.status='dozent')";
        }

        $dbv = DbView::getView('sem_tree');

        $query = "SELECT seminare.VeranstaltungsNummer AS sem_nr, seminare.Name, seminare.Seminar_id,
                         seminare.status AS sem_status, seminar_user.gruppe, seminare.visible,
                         {$dbv->sem_number_sql} AS sem_number,
                         {$dbv->sem_number_end_sql} AS sem_number_end {$add_fields}
                  FROM seminar_user
                  JOIN semester_data sd
                  LEFT JOIN seminare USING (Seminar_id)
                  {$add_query}
                  WHERE seminar_user.user_id = ?";
        if (Config::get()->MY_COURSES_ENABLE_STUDYGROUPS && !$studygroups) {
            $studygroup_types = DBManager::get()->quote(studygroup_sem_types());
            $query .= " AND seminare.status NOT IN ({$studygroup_types})";
        } elseif ($studygroups) {
            $studygroup_types = DBManager::get()->quote(studygroup_sem_types());
            $query .= " AND seminare.status IN ({$studygroup_types})";
        }

        if (Config::get()->DEPUTIES_ENABLE) {
            $query .= " UNION "
                . Deputy::getMySeminarsQuery('gruppe', $dbv->sem_number_sql, $dbv->sem_number_end_sql, $add_fields, $add_query);
        }
        $query .= " ORDER BY sem_nr ASC";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([$GLOBALS['user']->id, studygroup_sem_types()]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $my_sem[$row['Seminar_id']] = [
                'obj_type'       => 'sem',
                'sem_nr'         => $row['sem_nr'],
                'name'           => $row['Name'],
                'visible'        => $row['visible'],
                'gruppe'         => $row['gruppe'],
                'sem_status'     => $row['sem_status'],
                'sem_number'     => $row['sem_number'],
                'sem_number_end' => $row['sem_number_end'],
            ];
            if ($group_field) {
                fill_groups($groups, $row[$group_field], [
                    'seminar_id' => $row['Seminar_id'],
                    'sem_nr'     => $row['sem_nr'],
                    'name'       => $row['Name'],
                    'gruppe'     => $row['gruppe']
                ]);
            }
        }

        if ($group_field == 'sem_number') {
            correct_group_sem_number($groups, $my_sem);
        } else {
            add_sem_name($my_sem);
        }

        sort_groups($group_field, $groups);

        // Ensure that a seminar is never in multiple groups
        $sem_ids = [];
        foreach ($groups as $group_id => $seminars) {
            foreach ($seminars as $index => $seminar) {
                if (in_array($seminar['seminar_id'], $sem_ids)) {
                    unset($seminars[$index]);
                } else {
                    $sem_ids[] = $seminar['seminar_id'];
                }
            }
            if (empty($seminars)) {
                unset($groups[$group_id]);
            } else {
                $groups[$group_id] = $seminars;
            }
        }
        $this->studygroups         = $studygroups;
        $this->no_grouping_allowed = $no_grouping_allowed;
        $this->groups              = $groups;
        $this->group_names         = get_group_names($group_field, $groups);
        $this->group_field         = $group_field;
        $this->my_sem              = $my_sem;
        $this->cid                 = Request::get('cid') ? Request::get('cid') : '';
    }

    /**
     * Storage function for the groups action.
     * Stores selected grouping category and actual group settings.
     */
    public function store_groups_action($studygroups = false)
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $deputies_enabled = Config::get()->DEPUTIES_ENABLE;
        $GLOBALS['user']->cfg->store(
            'MY_COURSES_GROUPING',
            Request::get('select_group_field', $GLOBALS['user']->cfg->MY_COURSES_GROUPING)
        );
        $gruppe = Request::getArray('gruppe');
        if (!empty($gruppe)) {
            $query = "UPDATE seminar_user SET gruppe = ? WHERE Seminar_id = ? AND user_id = ?";
            $user_statement = DBManager::get()->prepare($query);

            $query = "UPDATE deputies SET gruppe = ? WHERE range_id = ? AND user_id = ?";
            $deputy_statement = DBManager::get()->prepare($query);

            foreach ($gruppe as $key => $value) {
                $user_statement->execute([$value,
                    $key,
                    $GLOBALS['user']->id
                ]);
                $updated = $user_statement->rowCount();

                if ($deputies_enabled && !$updated) {
                    $deputy_statement->execute([
                        $value,
                        $key,
                        $GLOBALS['user']->id
                    ]);
                }
            }
        }

        if (Request::get('cid')) {
            $redirect = "course/overview?cid=" . Request::get('cid');
        } else {
            $redirect = 'my_courses/index';
        }

        $this->redirect($studygroups ? 'my_studygroups/index' : $redirect);
    }


    /**
     * @param string $type
     * @param string $sem
     */
    public function tabularasa_action($sem = 'all', $timestamp = null)
    {
        NotificationCenter::postNotification('OverviewWillClear', $GLOBALS['user']->id);

        $timestamp        = $timestamp ?: time();
        $deputies_enabled = Config::get()->DEPUTIES_ENABLE;

        $semesters   = MyRealmModel::getSelectedSemesters($sem);
        $min_sem_key = min($semesters);
        $max_sem_key = max($semesters);
        $courses     = MyRealmModel::getCourses($min_sem_key, $max_sem_key, compact('deputies_enabled'));
        foreach ($courses as $index => $course) {
            MyRealmModel::setObjectVisits($course, $GLOBALS['user']->id, $timestamp);
        }

        NotificationCenter::postNotification('OverviewDidClear', $GLOBALS['user']->id);

        $this->flash['tabularasa'] = $timestamp;
        $this->redirect('my_courses/index');
    }


    /**
     * This action display only a message
     */
    public function decline_binding_action()
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }
        PageLayout::postError(_('Die Anmeldung ist verbindlich. Bitte wenden Sie sich an die Lehrenden.'));
        $this->redirect('my_courses/index');
    }

    /**
     * This action remove a user from course
     * @param $course_id
     */
    public function decline_action($course_id, $waiting = null)
    {
        $current_seminar = Seminar::getInstance($course_id);
        $ticket_check    = Seminar_Session::check_ticket(Request::option('studipticket'));
        if (LockRules::Check($course_id, 'participants')) {
            $lockdata = LockRules::getObjectRule($course_id);
            PageLayout::postError(sprintf(
                _('Sie können sich nicht von der Veranstaltung <b>%s</b> abmelden.'),
                htmlReady($current_seminar->name)
            ));
            if ($lockdata['description']) {
                PageLayout::postInfo(formatLinks($lockdata['description']));
            }
            $this->redirect('my_courses/index');
            return;
        }

        // Ensure last teacher cannot leave course
        $course = Course::find($course_id);
        if ($course->members->findOneBy('user_id', $GLOBALS['user']->id)->status === 'dozent'
            && count($course->getMembersWithStatus('dozent')) === 1
        ) {
            PageLayout::postError(sprintf(
                _('Sie können sich nicht von der Veranstaltung <b>%s</b> abmelden.'),
                htmlReady($current_seminar->name)
            ));
            $this->redirect('my_courses/index');
            return;
        }

        if (Request::option('cmd') == 'back') {
            $this->redirect('my_courses/index');
            return;
        }

        if (Request::option('cmd') != 'kill' && Request::option('cmd') != 'kill_admission') {
            if ($current_seminar->admission_binding && Request::get('cmd') != 'suppose_to_kill_admission' && !LockRules::Check($current_seminar->getId(), 'participants')) {
                PageLayout::postError(sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt.
                    Wenn Sie sich abmelden wollen, müssen Sie sich an die Lehrende der Veranstaltung wenden."),
                    htmlReady($current_seminar->name)));
                $this->redirect('my_courses/index');
                return;
            }

            if (Request::get('cmd') == 'suppose_to_kill') {
                // check course admission
                list(,$admission_end_time) = @array_values($current_seminar->getAdmissionTimeFrame());

                $admission_enabled = $current_seminar->isAdmissionEnabled();
                $admission_locked   = $current_seminar->isAdmissionLocked();

                if ($admission_enabled || $admission_locked || (int)$current_seminar->admission_prelim == 1) {
                    $message = sprintf(
                        _('Wollen Sie sich von der teilnahmebeschränkten Veranstaltung "%s" wirklich abmelden? Sie verlieren damit die Berechtigung für die Veranstaltung und müssen sich ggf. neu anmelden!'),
                        htmlReady($current_seminar->name)
                    );
                } else if (isset($admission_end_time) && $admission_end_time < time()) {
                    $message = sprintf(
                        _('Wollen Sie sich von der teilnahmebeschränkten Veranstaltung "%s" wirklich abmelden? Der Anmeldezeitraum ist abgelaufen und Sie können sich nicht wieder anmelden!'),
                        htmlReady($current_seminar->name)
                    );
                } else {
                    $message = sprintf(_('Wollen Sie sich von der Veranstaltung "%s" wirklich abmelden?'), htmlReady($current_seminar->name));
                }
                $cmd = 'kill';
            } else {
                if (admission_seminar_user_get_position($GLOBALS['user']->id, $course_id) === false) {
                    $message = sprintf(
                        _('Wollen Sie sich von der Anmeldeliste der Veranstaltung "%s" wirklich abmelden?'),
                        htmlReady($current_seminar->name)
                    );
                } else {
                    $message = sprintf(
                        _('Wollen Sie sich von der Warteliste der Veranstaltung "%s" wirklich abmelden? Sie verlieren damit die bereits erreichte Position und müssen sich ggf. neu anmelden!'),
                        htmlReady($current_seminar->name)
                    );
                }
                $cmd = 'kill_admission';
            }

            PageLayout::postQuestion(
                $message,
                $this->declineURL($course_id, ['cmd' => $cmd, 'studipticket' => Seminar_Session::get_ticket()]),
                $this->declineURL($course_id, ['cmd' => 'back', 'studipticket' => Seminar_Session::get_ticket()])
            );
            $this->redirect('my_courses/index');
            return;
        } else {
            if (!LockRules::Check($course_id, 'participants') && $ticket_check && Request::option('cmd') != 'back' && Request::get('cmd') != 'kill_admission') {
                $query     = "DELETE FROM seminar_user WHERE user_id = ? AND Seminar_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$GLOBALS['user']->id, $course_id]);
                if ($statement->rowCount() == 0) {
                    PageLayout::postError(
                        _('In der ausgewählten Veranstaltung wurde die gesuchten Personen nicht gefunden und konnte daher nicht ausgetragen werden.')
                    );
                } else {
                    // LOGGING
                    StudipLog::log('SEM_USER_DEL', $course_id, $GLOBALS['user']->id, 'Hat sich selbst ausgetragen');
                    // enable others to do something after the user has been deleted
                    NotificationCenter::postNotification('UserDidLeaveCourse', $course_id, $GLOBALS['user']->id);

                    // Delete from statusgroups
                    foreach (Statusgruppen::findBySeminar_id($course_id) as $group) {
                        $group->removeUser($GLOBALS['user']->id, true);
                    }

                    // Are successor available
                    update_admission($course_id);

                    // If this course is a child of another course...
                    if ($current_seminar->parent_course) {
                        // ... check if user is member in another sibling ...
                        $other = CourseMember::findBySQL(
                            "`user_id` = :user AND `Seminar_id` IN (:courses) AND `Seminar_id` != :this",
                            [
                                'user' => $GLOBALS['user']->id,
                                'courses' => $current_seminar->parent->children->pluck('seminar_id'),
                                'this' => $course_id
                            ]
                        );

                        // ... and delete from parent course if this was the only
                        // course membership in this family.
                        if (count($other) == 0) {
                            $m = CourseMember::find([$current_seminar->parent_course, $GLOBALS['user']->id]);
                            if ($m) {
                                $m->delete();
                            }
                        }
                    }

                    PageLayout::postSuccess(sprintf(
                        _("Erfolgreich von Veranstaltung <b>%s</b> abgemeldet."),
                        htmlReady($current_seminar->name)
                    ));
                }
            } else {
                // LOGGING
                StudipLog::log('SEM_USER_DEL', $course_id, $GLOBALS['user']->id, 'Hat sich selbst aus der Warteliste ausgetragen');
                if ($current_seminar->isAdmissionEnabled()) {
                    $prio_delete = AdmissionPriority::unsetPriority($current_seminar->getCourseSet()->getId(), $GLOBALS['user']->id, $course_id);
                }
                $query     = "DELETE FROM admission_seminar_user WHERE user_id = ? AND seminar_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$GLOBALS['user']->id,
                    $course_id]);
                NotificationCenter::postNotification('UserDidLeaveWaitingList', $course_id, $GLOBALS['user']->id);
                if ($statement->rowCount() || $prio_delete) {
                    //Warteliste neu sortieren
                    renumber_admission($course_id);
                    //Pruefen, ob es Nachruecker gibt
                    update_admission($course_id);
                    PageLayout::postSuccess(sprintf(
                        _("Der Eintrag in der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b> wurde aufgehoben. Wenn Sie an der Veranstaltung teilnehmen wollen, müssen Sie sich erneut bewerben."),
                        htmlReady($current_seminar->name)
                    ));
                }
            }
            $this->redirect('my_courses/index');
        }
    }


    /**
     * Overview for achived courses
     */
    public function archive_action()
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(_('Meine archivierten Veranstaltungen'));
        PageLayout::setHelpKeyword('Basis.MeinArchiv');
        Navigation::activateItem('/browse/my_courses/archive');
        SkipLinks::addIndex(_('Hauptinhalt'), 'layout_content', 100);

        if (Config::get()->ENABLE_ARCHIVE_SEARCH) {
            $actions = Sidebar::get()->addWidget(new ActionsWidget());
            $actions->addLink(
                _('Suche im Archiv'),
                URLHelper::getURL('dispatch.php/search/archive'),
                Icon::create('search')
            );
        }

        $sortby = Request::option('sortby', 'name');

        $query = "SELECT semester, name, seminar_id, status, archiv_file_id,
                         LENGTH(forumdump) > 0 AS forumdump, # Test for existence
                         LENGTH(wikidump) > 0 AS wikidump    # Test for existence
                  FROM archiv_user
                  LEFT JOIN archiv USING (seminar_id)
                  WHERE user_id = :user_id
                  GROUP BY seminar_id
                  ORDER BY start_time DESC, :sortby";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->bindValue(':sortby', $sortby, StudipPDO::PARAM_COLUMN);
        $statement->execute();
        $this->seminars = $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); // Groups by semester
    }

    /**
     * Checks the whole course selection deppending on grouping eneabled or not
     * @param $my_obj
     * @param string $group_field
     * @return bool
     */
    public function check_for_new($my_obj, $group_field = 'sem_number')
    {
        if (empty($my_obj)) {
            return false;
        }

        foreach ($my_obj as $courses) {

            if (is_array($courses)) {
                if ($group_field !== 'sem_number') {
                    // tlx: If array is 2-dimensional, merge it into a 1-dimensional
                    $courses = call_user_func_array('array_merge', $courses);
                }

                foreach ($courses as $course) {
                    if ($this->check_course($course)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }



    /**
     * Set the selected semester and redirects to index
     * @param null $sem
     */
    public function set_semester_action()
    {
        $sem = Request::option('sem_select', null);
        if (!is_null($sem)) {
            $GLOBALS['user']->cfg->store('MY_COURSES_SELECTED_CYCLE', $sem);
            PageLayout::postSuccess(
                _('Das gewünschte Semester bzw. die gewünschte Semester-Filteroption wurde ausgewählt!')
            );
        }

        $this->redirect('my_courses/index');
    }

    /**
     * Checks the selected courses for news (e.g. forum posts,...)
     * Returns true if something new happens and enables the reset function
     * @param $seminar_content
     * @return bool
     */
    public function check_course($seminar_content)
    {

        if ($seminar_content['visitdate'] <= $seminar_content['chdate'] || $seminar_content['last_modified'] > 0) {
            $last_modified = $seminar_content['visitdate'] <= $seminar_content['chdate']
            && $seminar_content['chdate'] > $seminar_content['last_modified']
                ? $seminar_content['chdate']
                : $seminar_content['last_modified'];
            if ($last_modified) {
                return true;
            }
        }

        $plugins_navigation = $seminar_content['navigation'];

        foreach ($plugins_navigation as $navigation) {
            if ($navigation && $navigation->isVisible(true) && $navigation->hasBadgeNumber()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove yourself as default deputy of the given boss.
     * @param $boss_id
     */
    public function delete_boss_action($boss_id)
    {
        $deputy = Deputy::find([$boss_id, $GLOBALS['user']->id]);
        $boss = $deputy->boss;
        if ($deputy && $deputy->delete()) {
            PageLayout::postSuccess(sprintf(
                _('Sie wurden als Standardvertretung von %s entfernt.'),
                htmlReady($boss->getFullname())
            ));
        } else {
            PageLayout::postError(sprintf(
                _('Sie konnten nicht als Standardvertretung von %s entfernt werden.'),
                htmlReady($boss->getFullname())
            ));
        }
        $this->redirect($this->url_for('my_courses'));
    }

    /**
     * Changes a config setting for the current use
     * @param string $config Config setting
     * @param bool   $state  State of setting
     */
    public function config_action($config, $state = null)
    {
        if ($config === 'tiled') {
            if ($state === null) {
                $state = !$GLOBALS['user']->cfg->MY_COURSES_TILED_DISPLAY;
            }
            $GLOBALS['user']->cfg->store('MY_COURSES_TILED_DISPLAY', (bool) $state);
        } elseif ($config === 'new') {
            if ($state === null) {
                $state = !$GLOBALS['user']->cfg->MY_COURSES_SHOW_NEW_ICONS_ONLY;
            }
            $GLOBALS['user']->cfg->store('MY_COURSES_SHOW_NEW_ICONS_ONLY', (bool) $state);
        }

        $this->redirect('my_courses');
    }

    /**
     * Get widget for grouping selected courses (e.g. by colors, ...)
     * @param      $action
     * @param bool $selected
     * @return string
     */
    private function setGroupingSelector($group_field)
    {
        $groups  = [
            'sem_number'  => _('Standard'),
            'sem_tree_id' => _('Studienbereich'),
            'sem_status'  => _('Typ'),
            'gruppe'      => _('Farbgruppen'),
            'dozent_id'   => _('Lehrende'),
        ];
        $views = Sidebar::get()->addWidget(new ViewsWidget());
        $views->setTitle(_('Gruppierung'));
        foreach ($groups as $key => $group) {
            $views->addLink(
                $group,
                $this->url_for('my_courses/store_groups', ['select_group_field' => $key])
            )->setActive($key === $group_field);
        }
    }

    /**
     * Returns a widget for semester selection
     * @param $sem
     * @return OptionsWidget
     */
    private function setSemesterWidget(&$sem)
    {
        $semesters = new SimpleCollection(Semester::getAll());
        $semesters = $semesters->orderBy('beginn desc');

        $sidebar = Sidebar::Get();

        $widget = new SelectWidget(_('Semesterfilter'), $this->url_for('my_courses/set_semester'), 'sem_select');
        $widget->setMaxLength(50);
        $widget->addElement(new SelectElement('current', _('Aktuelles Semester'), $sem == 'current'));
        $widget->addElement(new SelectElement('future', _('Aktuelles und nächstes Semester'), $sem == 'future'));
        $widget->addElement(new SelectElement('last', _('Aktuelles und letztes Semester'), $sem == 'last'));
        $widget->addElement(new SelectElement('lastandnext', _('Letztes, aktuelles, nächstes Semester'), $sem == 'lastandnext'));
        if (Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS) {
            $widget->addElement(new SelectElement('all', _('Alle Semester'), $sem == 'all'));
        }

        $query = "SELECT semester_data.semester_id
                  FROM seminare
                  LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id)
                  LEFT JOIN semester_data ON (semester_courses.semester_id = semester_data.semester_id)
                  LEFT JOIN seminar_user USING (Seminar_id)
                  WHERE seminar_user.user_id = ? AND seminare.status NOT IN (?)
                  GROUP BY semester_data.semester_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $GLOBALS['user']->id,
            studygroup_sem_types(),
        ]);
        $courses = $statement->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($semesters)) {
            $group = new SelectGroupElement(_('Semester auswählen'));
            foreach ($semesters as $semester) {
                if ($semester->visible || in_array($semester->id,$courses)) {
                    $group->addElement(new SelectElement($semester->id, $semester->name, $sem == $semester->id));
                }
            }
            $widget->addElement($group);
        }
        $sidebar->addWidget($widget);
    }

    protected function setupSidebar($sem, $group_field, $new_contents)
    {
        // Get permission that allows creating courses
        $sem_create_perm = Config::get()->SEM_CREATE_PERM;
        if (!in_array($sem_create_perm, ['root', 'admin', 'dozent'])) {
            $sem_create_perm = 'dozent';
        }

        // create settings url depended on selected cycle
        if (isset($sem) && !in_array($sem, words('future all last current'))) {
            $this->settings_url = "dispatch.php/my_courses/groups/{$sem}";
        } else {
            $this->settings_url = 'dispatch.php/my_courses/groups';
        }

        $sidebar = Sidebar::get();
        $this->setSemesterWidget($sem);

        $setting_widget = $sidebar->addWidget(new ActionsWidget());

        if ($new_contents) {
            $setting_widget->addLink(
                _('Alles als gelesen markieren'),
                $this->url_for("my_courses/tabularasa/{$sem}/" . time()),
                Icon::create('accept')
            );
        }
        $setting_widget->addLink(
            _('Farbgruppierung ändern'),
            URLHelper::getURL($this->settings_url),
            Icon::create('group4')
        )->asDialog();

        if (Config::get()->MAIL_NOTIFICATION_ENABLE) {
            $setting_widget->addLink(
                _('Benachrichtigungen anpassen'),
                URLHelper::getURL('dispatch.php/settings/notification'),
                Icon::create('mail')
            );
        }

        if ($sem_create_perm === 'dozent' && $GLOBALS['perm']->have_perm('dozent')) {
            $setting_widget->addLink(
                _('Neue Veranstaltung anlegen'),
                URLHelper::getURL('dispatch.php/course/wizard'),
                Icon::create('seminar+add')
            );
        }

        $setting_widget->addLink(
            _('Veranstaltung hinzufügen'),
            URLHelper::getURL('dispatch.php/search/courses'),
            Icon::create('seminar')
        );
        if (Config::get()->STUDYGROUPS_ENABLE) {
            $setting_widget->addLink(
                _('Neue Studiengruppe anlegen'),
                URLHelper::getURL('dispatch.php/course/wizard', ['studygroup' => 1]),
                Icon::create('studygroup+add')
            )->asDialog('size=auto');
        }

        $this->setGroupingSelector($group_field);

        if (Config::get()->MY_COURSES_ALLOW_TILED_DISPLAY) {
            $views = $sidebar->addWidget(new ViewsWidget());
            $views->id = 'tiled-courses-sidebar-switch';
            $views->addLink(
                _('Tabellarische Ansicht'),
                $this->config('tiled', 0)
            )->setActive(!$GLOBALS['user']->cfg->MY_COURSES_TILED_DISPLAY);
            $views->addLink(
                _('Kachelansicht'),
                $this->config('tiled', 1)
            )->setActive($GLOBALS['user']->cfg->MY_COURSES_TILED_DISPLAY);

            $options = $sidebar->addWidget(new OptionsWidget());
            $options->id = 'tiled-courses-new-contents-toggle';
            $options->addCheckbox(
                _('Nur neue Inhalte anzeigen'),
                $GLOBALS['user']->cfg->MY_COURSES_SHOW_NEW_ICONS_ONLY,
                $this->config('new')
            );
        }

        $export_widget = $sidebar->addWidget(new ExportWidget());
        $export_widget->addLink(
            _('Veranstaltungsübersicht exportieren'),
            $this->url_for('my_courses/courseexport', ['modules' => '1']),
            Icon::create('file-pdf')
        );
        $export_widget->addLink(
            _('Veranstaltungsübersicht ohne Module exportieren'),
            $this->url_for('my_courses/courseexport'),
            Icon::create('file-pdf')
        );
    }

    private function convertCourse($course)
    {
        $is_teacher = in_array($course['user_status'], ['tutor', 'dozent']);

        $avatar = $course['sem_class']['studygroup_mode']
                ? StudygroupAvatar::getAvatar($course['seminar_id'])
                : CourseAvatar::getAvatar($course['seminar_id']);

        $extra_navigation = false;
        if ($is_teacher) {
            $adminmodule = $course['sem_class']->getAdminModuleObject();
            if ($adminmodule) {
                $adminnavigation = $adminmodule->getIconNavigation($course['seminar_id'], 0, $GLOBALS['user']->id);
                $extra_navigation = [
                    'url'   => URLHelper::getURL($adminnavigation->getURL(), ['cid' => $course['seminar_id']]),
                    'icon'  => $adminnavigation->getImage()->getShape(),
                    'label' => $adminnavigation->getLinkAttributes()['title'] ?: _('Verwaltung'),
                ];
            }
        }

        return [
            'id'                => (string) $course['seminar_id'],
            'name'              => (string) $course['name'],
            'number'            => (string) $course['veranstaltungsnummer'],
            'group'             => (int) $course['gruppe'],
            'admission_binding' => (bool) $course['admission_binding'],
            'children'          => array_column($course['children'] ?? [], 'seminar_id'),
            'parent'            => $course['parent_course'] ?? null,

            'is_teacher'    => in_array($course['user_status'], ['tutor', 'dozent']),
            'is_studygroup' => (bool) $course['sem_class']['studygroup_mode'],
            'is_hidden'     => !$course['visible'],
            'is_deputy'     => (bool) $course['is_deputy'],
            'is_group'      => (bool) $course['is_group'],

            'avatar' => $avatar->getURL(Avatar::MEDIUM),

            'navigation'       => $this->reduceNavigation($course['navigation']),
            'extra_navigation' => $extra_navigation,
        ];
    }

    private function reduceNavigation($nav)
    {
        if (!$nav) {
            return [];
        }

        $result = [];
        foreach (MyRealmModel::array_rtrim($nav) as $key => $n) {
            if (!$n || !$n->isVisible(true)) {
                $item = false;
            } else {
                $attr = $n->getLinkAttributes();
                if (empty($attr['title']) && $n->getImage()) {
                    $attr['title'] = (string) $n->getImage()->getAttributes()['title'];
                }
                if (empty($attr['title'])) {
                    $attr['title'] = (string) $n->getTitle();
                }
                $attr['title'] = (string) $attr['title'];

                $item = [
                    'url'       => $n->getURL(),
                    'icon'      => $this->convertIcon($n->getImage()),
                    'attr'      => $attr,
                    'important' => in_array($n->getImage()->getRole(), [Icon::ROLE_ATTENTION, Icon::ROLE_STATUS_RED, Icon::ROLE_NEW])
                ];
            }
            $result[$key] = $item;
        }

        return $result;
    }

    private function sanitizeNavigations(array $courses)
    {
        // Count occurences of slots
        $counters = [];
        foreach ($courses as $course) {
            foreach ($course['navigation'] as $key => $value) {
                if (!isset($counters[$key])) {
                    $counters[$key] = 0;
                }
                if ($value) {
                    $counters[$key] += 1;
                }
            }
        }

        // Detect which slots are not set at all
        $remove = array_keys(array_filter($counters, function ($counter) {
            return !$counter;
        }));

        // Set positions by predefined positions without the always empty slots
        $positions = array_diff(array_keys(MyRealmModel::getDefaultModules()), $remove);

        // Get other positions based on count
        arsort($counters);
        foreach ($counters as $key => $count) {
            if ($count && !in_array($key, $positions)) {
                $positions[] = $key;
            }
        }

        // Sort and filter course navigations
        return array_map(
            function ($course) use ($positions) {
                $course['navigation'] = array_filter($course['navigation'], function ($key) use ($positions) {
                    return in_array($key, $positions);
                }, ARRAY_FILTER_USE_KEY);
                uksort($course['navigation'], function ($a, $b) use ($positions) {
                    return array_search($a, $positions) - array_search($b, $positions);
                });
                return $course;
            },
            $courses
        );
    }

    private function convertIcon(Icon $icon)
    {
        return [
            'role' => $icon->getRole(),
            'shape' => $icon->getShape(),
        ];
    }

    private function getSemesterKey()
    {
        $config_sem = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
        if (!Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS && $config_sem === 'all') {
            $config_sem = 'future';
        }

        $sem = Request::get(
            'sem_select',
            $config_sem ?: Config::get()->MY_COURSES_DEFAULT_CYCLE
        );

        if ($sem && !in_array($sem, ['future', 'all', 'last', 'current'])) {
            Request::set('sem_select', $sem);
        }

        return $sem;
    }

    private function getGroupField()
    {
        $group_field = $GLOBALS['user']->cfg->MY_COURSES_GROUPING;

        $forced_grouping = in_array(Config::get()->MY_COURSES_FORCE_GROUPING, getValidGroupingFields())
                         ? Config::get()->MY_COURSES_FORCE_GROUPING
                         : 'sem_number';

        if ($forced_grouping === 'not_grouped') {
            $forced_grouping = 'sem_number';
        }

        if (!$group_field || !in_array($group_field, getValidGroupingFields())) {
            $group_field = 'sem_number';
        }

        if ($group_field === 'sem_number' && $forced_grouping !== 'sem_number') {
            $group_field = $forced_grouping;
        }

        return $group_field === 'not_grouped' ? 'sem_number' : $group_field;
    }
}
