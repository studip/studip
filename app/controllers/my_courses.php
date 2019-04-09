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
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 *              David Siegfried <david@ds-labs.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @since       3.1
 */
require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';

class MyCoursesController extends AuthenticatedController
{
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

    function after_filter($action, $args)
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
    public function index_action($order_by = null, $order = 'asc')
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        if ($GLOBALS['perm']->have_perm('admin')) {
            $this->redirect('my_courses/admin');
            return;
        }
        Navigation::activateItem('/browse/my_courses/list');
        PageLayout::setHelpKeyword("Basis.MeineVeranstaltungen");
        PageLayout::setTitle(_("Meine Veranstaltungen"));

        $config_sem = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
        if (!Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS && $config_sem == 'all') {
            $config_sem = 'future';
        }
        $this->_my_sem_open           = $GLOBALS['user']->cfg->MY_COURSES_OPEN_GROUPS;
        $group_field                  = $GLOBALS['user']->cfg->MY_COURSES_GROUPING;
        $deputies_enabled             = Config::get()->DEPUTIES_ENABLE;
        $default_deputies_enabled     = Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE;
        $deputies_edit_about_enabled  = Config::get()->DEPUTIES_EDIT_ABOUT_ENABLE;
        $studygroups_enabled          = Config::get()->MY_COURSES_ENABLE_STUDYGROUPS;
        $this->config_sem_number      = Config::get()->IMPORTANT_SEMNUMBER;
        $sem_create_perm              = (in_array(Config::get()->SEM_CREATE_PERM, ['root', 'admin',
            'dozent']) ? Config::get()->SEM_CREATE_PERM : 'dozent');

        $this->sem_data = Semester::getAllAsArray();

        $sem = ($config_sem && $config_sem != '0' ? $config_sem : Config::get()->MY_COURSES_DEFAULT_CYCLE);
        if (Request::option('sem_select')) {
            $sem = Request::get('sem_select', $sem);
        }

        if (!in_array($sem, words('future all last current')) && isset($sem)) {
            Request::set('sem_select', $sem);
        }

        $forced_grouping = in_array(Config::get()->MY_COURSES_FORCE_GROUPING, getValidGroupingFields())
            ? Config::get()->MY_COURSES_FORCE_GROUPING
            : 'sem_number';

        if ($forced_grouping == 'not_grouped') {
            $forced_grouping = 'sem_number';
        }

        if (!$group_field || !in_array($group_field, getValidGroupingFields())) {
            $group_field = 'sem_number';
        }
        if ($group_field == 'sem_number' && $forced_grouping != 'sem_number') {
            $group_field = $forced_grouping;
        }

        $this->group_field = $group_field === 'not_grouped' ? 'sem_number' : $group_field;

        // Needed parameters for selecting courses
        $params = ['group_field'         => $this->group_field,
                        'order_by'            => $order_by,
                        'order'               => $order,
                        'studygroups_enabled' => $studygroups_enabled,
                        'deputies_enabled'    => $deputies_enabled];


        // Save the semester in session
        $this->sem_courses  = MyRealmModel::getPreparedCourses($sem, $params);

        $this->waiting_list = MyRealmModel::getWaitingList($GLOBALS['user']->id);

        $this->sem                          = $sem;
        $this->order                        = $order;
        $this->order_by                     = $order_by;
        $this->default_deputies_enabled     = $default_deputies_enabled;
        $this->deputies_edit_about_enabled  = $deputies_edit_about_enabled;
        $this->my_bosses                    = $default_deputies_enabled ? getDeputyBosses($GLOBALS['user']->id) : [];

        // Check for new contents
        $new_contents = $this->check_for_new($this->sem_courses, $this->group_field);
        $this->nav_elements = MyRealmModel::calc_nav_elements($this->sem_courses, $this->group_field);

        //
        if ($tabularasa = $this->flash['tabularasa']) {
            $details = [];
            if ($new_contents) {
                $details[] = sprintf(
                    _('Seit Ihrem letzten Seitenaufruf (%s) sind allerdings neue Inhalte hinzugekommen.'),
                    reltime($tabularasa)
                );
            }

            PageLayout::postSuccess(_('Alles als gelesen markiert!'), $details);
        }


        // create settings url depended on selected cycle
        if (isset($sem) && !in_array($sem, words('future all last current'))) {
            $this->settings_url = "dispatch.php/my_courses/groups/{$sem}";
        } else {
            $this->settings_url = 'dispatch.php/my_courses/groups';
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/seminar-sidebar.png');
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

        $this->setGroupingSelector($this->group_field);

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

    /**
     * PDF export of course overview
     */
    public function courseexport_action()
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $this->with_modules = (bool) Request::int('modules');

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

        $this->render_nothing();
        while (ob_end_clean());
        $doc = new ExportPDF();
        $doc->addPage();
        $doc->SetFont('helvetica', '', 10);
        $doc->writeHTML($template->render(), false, false, true);
        $doc->Output('courseexport.pdf', 'D');
    }

    public function set_open_group_action($id)
    {
        $_my_sem_open = $GLOBALS['user']->cfg->MY_COURSES_OPEN_GROUPS;
        if (isset($_my_sem_open[$id])) {
            unset($_my_sem_open[$id]);
        } else {
            $_my_sem_open[$id] = true;
        }
        $GLOBALS['user']->cfg->store('MY_COURSES_OPEN_GROUPS', $_my_sem_open);
        $this->redirect('my_courses/index');
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

        $this->current_semester = $sem ? : Semester::findCurrent()->semester_id;

        $this->semesters = SemesterData::GetSemesterArray();
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
            $query .= " AND seminare.status != 99";
        }

        if ($studygroups) {
            $query .= " AND seminare.status = 99";
        }
        if (get_config('DEPUTIES_ENABLE')) {
            $query .= " UNION "
                . getMyDeputySeminarsQuery('gruppe', $dbv->sem_number_sql, $dbv->sem_number_end_sql, $add_fields, $add_query);
        }
        $query .= " ORDER BY sem_nr ASC";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([$GLOBALS['user']->id]);
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
        $GLOBALS['user']->cfg->store('MY_COURSES_GROUPING', Request::get('select_group_field'));
        $gruppe = Request::getArray('gruppe');
        if (!empty($gruppe)) {
            $query          = "UPDATE seminar_user SET gruppe = ? WHERE Seminar_id = ? AND user_id = ?";
            $user_statement = DBManager::get()->prepare($query);

            $query            = "UPDATE deputies SET gruppe = ? WHERE range_id = ? AND user_id = ?";
            $deputy_statement = DBManager::get()->prepare($query);

            foreach ($gruppe as $key => $value) {
                $user_statement->execute([$value,
                    $key,
                    $GLOBALS['user']->id]);
                $updated = $user_statement->rowCount();

                if ($deputies_enabled && !$updated) {
                    $deputy_statement->execute([$value,
                        $key,
                        $GLOBALS['user']->id]);
                }
            }
        }

        $this->redirect($studygroups ? 'my_studygroups/index' : 'my_courses/index');
    }


    /**
     * TODO: Caching
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
        $courses     = $courses->toArray('seminar_id modules status');

        $modules = new Modules();
        foreach ($courses as $index => $course) {
            $courses[$index]['modules']  = $modules->getLocalModules($course['seminar_id'], 'sem', $course['modules'], $course['status']);
            $courses[$index]['obj_type'] = 'sem';
            MyRealmModel::setObjectVisits($courses[$index], $course['seminar_id'], $GLOBALS['user']->id, $timestamp);
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
        PageLayout::postMessage(MessageBox::error(_('Die Anmeldung ist verbindlich. Bitte wenden Sie sich an die Lehrenden.')));
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
            PageLayout::postMessage(MessageBox::error(sprintf(_("Sie können sich nicht von der Veranstaltung <b>%s</b> abmelden."),
                htmlReady($current_seminar->name))));
            if ($lockdata['description']) PageLayout::postMessage(MessageBox::info(formatLinks($lockdata['description'])));
            $this->redirect('my_courses/index');
            return;
        }

        if (Request::option('cmd') == 'back') {
            $this->redirect('my_courses/index');
            return;
        }

        if (Request::option('cmd') != 'kill' && Request::option('cmd') != 'kill_admission') {

            if ($current_seminar->admission_binding && Request::get('cmd') != 'suppose_to_kill_admission' && !LockRules::Check($current_seminar->getId(), 'participants')) {
                PageLayout::postMessage(MessageBox::error(sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt.
                    Wenn Sie sich abmelden wollen, müssen Sie sich an die Lehrende der Veranstaltung wenden."),
                    htmlReady($current_seminar->name))));
                $this->redirect('my_courses/index');
                return;
            }

            if (Request::get('cmd') == 'suppose_to_kill') {
                // check course admission
                list(,$admission_end_time) = @array_values($current_seminar->getAdmissionTimeFrame());

                $admission_enabled = $current_seminar->isAdmissionEnabled();
                $admission_locked   = $current_seminar->isAdmissionLocked();

                if ($admission_enabled || $admission_locked || (int)$current_seminar->admission_prelim == 1) {
                    $message = sprintf(_('Wollen Sie sich von der teilnahmebeschränkten Veranstaltung "%s" wirklich abmelden? Sie verlieren damit die Berechtigung für die Veranstaltung und müssen sich ggf. neu anmelden!'), $current_seminar->name);
                } else if (isset($admission_end_time) && $admission_end_time < time()) {
                    $message = sprintf(_('Wollen Sie sich von der teilnahmebeschränkten Veranstaltung "%s" wirklich abmelden? Der Anmeldzeitraum ist abgelaufen und Sie können sich nicht wieder anmelden!'), $current_seminar->name);
                } else {
                    $message = sprintf(_('Wollen Sie sich von der Veranstaltung "%s" wirklich abmelden?'), $current_seminar->name);
                }
                $this->flash['cmd'] = 'kill';
            } else {
                if (admission_seminar_user_get_position($GLOBALS['user']->id, $course_id) === false) {
                    $message = sprintf(_('Wollen Sie sich von der Anmeldeliste der Veranstaltung "%s" wirklich abmelden?'), $current_seminar->name);
                } else {
                    $message = sprintf(_('Wollen Sie sich von der Warteliste der Veranstaltung "%s" wirklich abmelden? Sie verlieren damit die bereits erreichte Position und müssen sich ggf. neu anmelden!'), $current_seminar->name);
                }
                $this->flash['cmd'] = 'kill_admission';
            }

            $this->flash['decline_course'] = true;
            $this->flash['course_id']      = $course_id;
            $this->flash['message']        = $message;
            $this->flash['studipticket']   = Seminar_Session::get_ticket();
            $this->redirect('my_courses/index');
            return;
        } else {
            if (!LockRules::Check($course_id, 'participants') && $ticket_check && Request::option('cmd') != 'back' && Request::get('cmd') != 'kill_admission') {
                $query     = "DELETE FROM seminar_user WHERE user_id = ? AND Seminar_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$GLOBALS['user']->id, $course_id]);
                if ($statement->rowCount() == 0) {
                    PageLayout::postMessage(MessageBox::error(_('In der ausgewählten Veranstaltung wurde die gesuchten Personen nicht gefunden und konnte daher nicht ausgetragen werden.')));
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

                    PageLayout::postMessage(MessageBox::success(sprintf(_("Erfolgreich von Veranstaltung <b>%s</b> abgemeldet."),
                        htmlReady($current_seminar->name))));
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
                    PageLayout::postMessage(MessageBox::success(sprintf(_("Der Eintrag in der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b> wurde aufgehoben.
                    Wenn Sie an der Veranstaltung teilnehmen wollen, müssen Sie sich erneut bewerben."), htmlReady($current_seminar->name))));
                }
            }

            $this->redirect('my_courses/index');
        }
    }


    /**
     * Overview for achived courses
     * TODO: Caching?
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
        $sortby = Request::option('sortby', 'name');

        $query     = "SELECT semester, name, seminar_id, status, archiv_file_id,
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
    function check_for_new($my_obj, $group_field = 'sem_number')
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
            PageLayout::postMessage(MessageBox::success(_('Das gewünschte Semester bzw.
            die gewünschte Semester Filteroption wurde ausgewählt!')));
        }

        $this->redirect('my_courses/index');
    }

    /**
     * Checks the selected courses for news (e.g. forum posts,...)
     * Returns true if something new happens and enables the reset function
     * @param $seminar_content
     * @return bool
     */
    function check_course($seminar_content)
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
        if (deleteDeputy($GLOBALS['user']->id, $boss_id)) {
            PageLayout::postSuccess(
                sprintf(_('Sie wurden als Standardvertretung von %s entfernt.'),
                User::find($boss_id)->getFullname()));
        } else {
            PageLayout::postError(
                sprintf(_('Sie konnten nicht als Standardvertretung von %s entfernt werden.'),
                User::find($boss_id)->getFullname()));
        }
        $this->redirect($this->url_for('my_courses'));
    }

    /**
     * Get widget for grouping selected courses (e.g. by colors, ...)
     * @param      $action
     * @param bool $selected
     * @return string
     */
    private function setGroupingSelector(&$group_field)
    {
        $sidebar = Sidebar::Get();
        $groups  = [
            'sem_number'  => _('Standard'),
            'sem_tree_id' => _('Studienbereich'),
            'sem_status'  => _('Typ'),
            'gruppe'      => _('Farbgruppen'),
            'dozent_id'   => _('Dozenten'),
        ];
        $view    = new ViewsWidget();
        foreach ($groups as $key => $group) {
            $view->addLink($group, $this->url_for('my_courses/store_groups?select_group_field=' . $key))->setActive($key === $group_field);
        }
        $sidebar->addWidget($view);
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
                  LEFT JOIN semester_data ON (semester_data.beginn >= seminare.start_time
                      AND (semester_data.beginn <= seminare.start_time + seminare.duration_time OR seminare.duration_time = -1))
                  LEFT JOIN seminar_user USING (Seminar_id)
                  WHERE seminar_user.user_id = ? AND seminare.status <> 99
                  GROUP BY semester_data.semester_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$GLOBALS['user']->id]);
        foreach($statement->fetchAll(PDO::FETCH_ASSOC) as $semester_courses) {
            $courses[] = $semester_courses['semester_id'];
        }

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
}
