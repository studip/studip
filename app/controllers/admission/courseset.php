<?php

/**
 * CoursesetController - Course sets
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

require_once 'app/models/courseset.php';
require_once 'lib/admission.inc.php';

class Admission_CoursesetController extends AuthenticatedController
{
    /**
     * Things to do before every page load.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Request::isXhr()) {
            PageLayout::setTitle(_('Anmeldesets'));
            // Get only own courses if user doesn't have permission to edit institute-wide coursesets.
            $this->onlyOwnCourses = true;
            if ($GLOBALS['perm']->have_perm('admin') || ($GLOBALS['perm']->have_perm('dozent') && get_config('ALLOW_DOZENT_COURSESET_ADMIN'))) {
                // We have access to institute-wide course sets, so all courses may be assigned.
                $this->onlyOwnCourses = false;
                Navigation::activateItem('/tools/coursesets/sets');
            } else {
                throw new AccessDeniedException();
            }
        }
        PageLayout::addScript('studip-admission.js');

        $views = new ActionsWidget();
        $views->addLink(
            _('Anmeldeset anlegen'),
            $this->url_for('admission/courseset/configure'),
            Icon::create('add', 'clickable')
        )->setActive($action == 'configure');
        Sidebar::Get()->addWidget($views);

    }

    /**
     * Show all coursesets the current user has access to.
     */
    public function index_action() {
        $this->course_set_details = Request::option('course_set_details');
        if ($this->course_set_details && Request::isXhr()) {
            $courseset = new CourseSet($this->course_set_details);
            return $this->render_text($courseset->toString());
        }
        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules(false);
        $this->coursesets = [];
        foreach (words('current_institut_id current_rule_types set_name_prefix current_semester_id current_rule_types') as $param) {
            $this->$param = $_SESSION[get_class($this)][$param];
        }
        if (Request::submitted('choose_institut')) {
            $this->current_institut_id = Request::option('choose_institut_id');
            $this->current_rule_types = Request::getArray('choose_rule_type');
            $this->set_name_prefix = trim(Request::get('set_name_prefix'));
            $this->current_semester_id = Request::option('select_semester_id');
        }
        if ($this->current_semester_id === null) {
            $this->current_semester_id = $_SESSION['_default_sem'];
        } else if ($this->current_semester_id !== '0') {
            $_SESSION['_default_sem'] = $this->current_semester_id;
        }
        if (!isset($this->current_rule_types)) {
            $this->current_rule_types['ParticipantRestrictedAdmission'] = true;
        }
        $filter['course_set_name'] = $this->set_name_prefix;
        $filter['semester_id'] = $this->current_semester_id != 'all' ? $this->current_semester_id : null;
        $filter['rule_types'] = array_keys($this->current_rule_types);
        $this->myInstitutes = CoursesetModel::getInstitutes($filter);
        if (!$this->current_institut_id) {
            if ($this->myInstitutes['all']['num_sets'] < 100) {
                $this->current_institut_id = 'all';
            } else {
                next($this->myInstitutes);
                $this->current_institut_id = key($this->myInstitutes);
                reset($this->myInstitutes);
            }
        }
        list($institut_id, $all) = explode('_', $this->current_institut_id);
        if ($institut_id == 'all') {
            $institutes = array_keys($this->myInstitutes);
        } else if ($all == 'all') {
            $institutes[] = $institut_id;
            $institutes = array_merge($institutes, Institute::find($institut_id)->sub_institutes->pluck('institut_id'));
        } else {
            $institutes = [$institut_id];
        }

        foreach ($institutes as $one) {
            if ($this->myInstitutes[$one]['num_sets']) {
                $sets = CourseSet::getCoursesetsByInstituteId($one, $filter);
                foreach ($sets as $set) {
                    $courseset = new CourseSet($set['set_id']);
                    $this->coursesets[$set['set_id']] = $courseset;
                }
            }
        }
        uasort($this->coursesets, function($a,$b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });
        foreach (words('current_institut_id current_rule_types set_name_prefix current_semester_id current_rule_types') as $param) {
            $_SESSION[get_class($this)][$param] = $this->$param;
        }
        $not_distributed_coursesets = array_filter(array_map(function ($cs) {
                return ($cs->isSeatDistributionEnabled() && $cs->getSeatDistributionTime() < (time() - 1000) && !$cs->hasAlgorithmRun())
                        ? $cs->getName()
                        : null;
        }, $this->coursesets));
        if (count($not_distributed_coursesets)) {
            PageLayout::postMessage(MessageBox::info(
                _("Es existieren Anmeldesets, die zum Zeitpunkt der Platzverteilung nicht gelost wurden. Stellen Sie sicher, dass der Cronjob \"Losverfahren überprüfen\" ausgeführt wird."),
                array_unique($not_distributed_coursesets)));
        }
    }

    /**
     * Configure a new or existing course set.
     */
    public function configure_action($coursesetId='') {
        if ($GLOBALS['perm']->have_perm('root')) {
            if ($coursesetId) {
                // Load course set data.
                $this->courseset = new CourseSet($coursesetId);
                $this->myInstitutes = [];
                $selectedInstitutes = $this->courseset->getInstituteIds();
                foreach ($selectedInstitutes as $id => $selected) {
                    $this->myInstitutes[$id] = new Institute($id);
                }
                $this->selectedInstitutes = $this->myInstitutes;
                $selectedCourses = $this->courseset->getCourses();
                if (!$this->instant_course_set_view) {
                    $allCourses = CoursesetModel::getInstCourses(array_keys($this->selectedInstitutes), $coursesetId, [], $this->courseset->getSemester());
                    $this->selectedSemester = $this->courseset->getSemester();
                }
            } else {
                $this->myInstitutes = [];
                $this->selectedInstitutes = [];
                $allCourses = [];
                $selectedCourses = [];
                $this->selectedSemester = $_SESSION['_default_sem'] ?: Semester::findCurrent()->semester_id;
            }
            Config::get()->AJAX_AUTOCOMPLETE_DISABLED = false;
            $this->instSearch = QuickSearch::get("institute_id", new StandardSearch("Institut_id"))
                ->withoutButton()
                ->render();
        } else {
            $this->myInstitutes = [];
            $myInstitutes = Institute::getMyInstitutes();
            foreach ($myInstitutes as $institute) {
                $this->myInstitutes[$institute['Institut_id']] = $institute;
            }
            if ($coursesetId) {
                // Load course set data.
                $this->courseset = new CourseSet($coursesetId);
                $selectedInstitutes = $this->courseset->getInstituteIds();
                $this->selectedInstitutes = [];
                foreach ($selectedInstitutes as $id => $selected) {
                    $this->selectedInstitutes[$id] = new Institute($id);
                }
                $selectedCourses = $this->courseset->getCourses();
                if (!$this->instant_course_set_view) {
                    $allCourses = CoursesetModel::getInstCourses(array_keys($this->selectedInstitutes), $coursesetId, [], $this->courseset->getSemester(), $this->onlyOwnCourses);
                    $this->selectedSemester = $this->courseset->getSemester();
                }
            } else {
                $this->selectedSemester = $_SESSION['_default_sem'] ?: Semester::findCurrent()->semester_id;
                $this->selectedInstitutes = $this->myInstitutes;
                $allCourses = CoursesetModel::getInstCourses(array_keys($this->myInstitutes), $coursesetId, [], $this->selectedSemester, $this->onlyOwnCourses);
                $selectedCourses = [];
            }
        }
        // If an institute search has been conducted, we need to consider parameters from flash.
        if ($this->flash['name'] || $this->flash['institutes'] || $this->flash['courses'] ||
                $this->flash['rules'] || $this->flash['userlists'] || $this->flash['infotext'] ||
                $this->flash['semester']) {
            if (!$this->courseset) {
                $this->courseset = new CourseSet($coursesetId);
            }
            if ($this->flash['name']) {
                $this->courseset->setName($this->flash['name']);
            }
            if ($this->flash['institutes']) {
                $institutes = $this->flash['institutes'];
                $this->courseset->setInstitutes($institutes);
                if ($GLOBALS['perm']->have_perm('root')) {
                    $this->myInstitutes = [];
                    foreach ($institutes as $id) {
                        $this->myInstitutes[$id] = new Institute($id);
                        $this->selectedInstitutes[$id] = $this->myInstitutes[$id];
                    }
                }
                $selectedCourses = $this->courseset->getCourses();
                $allCourses = CoursesetModel::getInstCourses(array_flip($institutes), $coursesetId, $selectedCourses, $this->selectedSemester, $this->onlyOwnCourses);
            }
            if ($this->flash['courses']) {
                $courses = $this->flash['courses'];
                $this->courseset->setCourses($courses);
                $selectedCourses = $courses;
            }
            if ($this->flash['rules']) {
                $this->courseset->setAdmissionRules($this->flash['rules']);
            }
            if ($this->flash['userlists']) {
                $this->courseset->setUserlists($this->flash['userlists']);
            }
            if ($this->flash['infotext']) {
                $this->courseset->setInfoText($this->flash['infotext']);
            }
            if ($this->flash['private']) {
                $this->courseset->setPrivate($this->flash['private']);
            }
            if ($this->flash['semester']) {
                $this->selectedSemester = $this->flash['semester'];
            }
        }
        // Fetch all lists with special user chances.
        $this->myUserlists = AdmissionUserList::getUserLists($GLOBALS['user']->id);
        $fac = $this->get_template_factory();
        $tpl = $fac->open('admission/courseset/instcourses');
        $tpl->set_attribute('allCourses', $allCourses);
        $tpl->set_attribute('selectedCourses', $selectedCourses);
        $this->coursesTpl = $tpl->render();
        $tpl = $fac->open('admission/courseset/institutes');
        if ($coursesetId) {
            $tpl->set_attribute('courseset', $this->courseset);
        }
        $tpl->set_attribute('instSearch', $this->instSearch);
        $tpl->set_attribute('selectedInstitutes', $this->selectedInstitutes);
        $tpl->set_attribute('myInstitutes', $this->myInstitutes);
        $tpl->set_attribute('controller', $this);
        if ($GLOBALS['perm']->have_perm('admin') || ($GLOBALS['perm']->have_perm('dozent') && get_config('ALLOW_DOZENT_COURSESET_ADMIN'))) {
            $tpl->set_attribute('rights', true);
        } else {
            $tpl->set_attribute('rights', false);
        }
        $this->instTpl = $tpl->render();
    }

    /**
     * Saves the given course set to database.
     *
     * @param String $coursesetId the course set to save or empty if it is a
     * new course set
     */
    public function save_action($coursesetId='') {
        if (!$this->instant_course_set_view && (!Request::submitted('submit') || !Request::get('name') || !Request::getArray('institutes'))) {
            $this->flash['name'] = Request::get('name');
            $this->flash['institutes'] = Request::getArray('institutes');
            $this->flash['courses'] = Request::getArray('courses');
            $this->flash['rules'] = Request::getArray('rules');
            $this->flash['userlists'] = Request::getArray('userlists');
            $this->flash['infotext'] = Request::get('infotext');
            $this->flash['private'] = (bool) Request::get('private');
            $this->flash['semester'] = Request::option('semester');
            if (Request::submitted('add_institute')) {
                $this->flash['institutes'] = array_merge($this->flash['institutes'], [Request::option('institute_id')]);
            } else {
                $this->flash['institute_id'] = Request::get('institute_id');
                $this->flash['institute_id_parameter'] = Request::get('institute_id_parameter');
            }
            if (!Request::submitted('add_institute') && !Request::option('name')) {
                $this->flash['error'] = _('Bitte geben Sie einen Namen für das Anmeldeset an!');
            }
            if (!Request::submitted('add_institute') && !Request::getArray('institutes')) {
                $this->flash['error'] = _('Bitte geben Sie mindestens eine Einrichtung an, zu der das Anmeldeset gehört!');
            }
            $this->redirect($this->url_for('admission/courseset/configure', $coursesetId));
        } else {
            $courseset = new CourseSet($coursesetId);
            if (!$courseset->getUserId()) {
                $courseset->setUserId($GLOBALS['user']->id);
            }
            $courseset->setName(Request::get('name'));
            if (Request::submitted('institutes')) {
                $courseset->setInstitutes(Request::getArray('institutes'));
            }
            if (Request::submitted('semester')) {
                $courseset->setCourses(Request::getArray('courses'));
            }
            if (Request::submitted('userlists')) {
                $courseset->setUserLists(Request::getArray('userlists'));
            }
            if (!$this->instant_course_set_view && $courseset->isUserAllowedToEdit($GLOBALS['user']->id)) {
                $courseset->setPrivate((bool) Request::get('private'));
            }
            if (Request::submitted('infotext')) {
                $courseset->setInfoText(Request::get('infotext'));
            }
            $courseset->clearAdmissionRules();
            foreach (Request::getManyObjects('rules', 'AdmissionRule') as $rule) {
                $courseset->addAdmissionRule($rule);
            }
            $courseset->store();
            if (Request::submitted('semester')) {
                $_SESSION['_default_sem'] = Request::option('semester');
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("Das Anmeldeset: %s wurde gespeichert"), htmlReady($courseset->getName()))));
            if ($this->instant_course_set_view) {
                $this->redirect($this->url_for('course/admission'));
            } else {
                $this->redirect($this->url_for('admission/courseset/configure', $courseset->getId()));
            }
        }
    }

    /**
     * Deletes the given course set.
     *
     * @param String $coursesetId the course set to delete
     */
    public function delete_action($coursesetId) {
        $this->courseset = new CourseSet($coursesetId);
        if (Request::int('really')) {
            $this->courseset->delete();
            $this->redirect($this->url_for('admission/courseset'));
        }
        if (Request::int('cancel')) {
            $this->redirect($this->url_for('admission/courseset'));
        }
    }

    /**
     * Fetches courses at institutes specified by a given course set, filtered by a
     * given semester.
     *
     * @param String $coursesetId The courseset to fetch institute assignments
     * from
     * @see CoursesetModel::getInstCourses
     */
    public function instcourses_action($coursesetId='') {
        CSRFProtection::verifyUnsafeRequest();
        $this->selectedCourses = [];
        //autoload
        $courseset = new CourseSet();
        if ($coursesetId && !Request::getArray('courses')) {
            $courseset = new CourseSet($coursesetId);
            $this->selectedCourses = $courseset->getCourses();
        } else if (Request::getArray('courses')) {
            $this->selectedCourses = Request::getArray('courses');
        }
        $this->allCourses = CoursesetModel::getInstCourses(Request::getArray('institutes'),
            $coursesetId, $this->selectedCourses, Request::option('semester'), $this->onlyOwnCourses ?: Request::get('course_filter'));
    }

    /**
     * Fetches available institutes for the current user.
     */
    public function institutes_action() {
        CSRFProtection::verifyUnsafeRequest();
        $this->myInstitutes = Institute::getMyInstitutes();
        $this->selectedInstitutes = [];
        foreach(Request::getArray('institutes') as $institute) {
            $this->selectedInstitutes[$institute] = new Institute($institute);
        }
        Config::get()->AJAX_AUTOCOMPLETE_DISABLED = false;
        $this->instSearch = QuickSearch::get("institute_id", new StandardSearch("Institut_id"))
            ->withOutButton()
            ->render();
    }

    /**
     * Configure settings for several courses at once.
     *
     * @param String $set_id course set ID to fetch courses from
     * @param String $csv    export course members to file
     */
    public function configure_courses_action($set_id, $csv = null)
    {
        PageLayout::setTitle(_('Ausgewählte Veranstaltungen konfigurieren'));

        $courseset = new CourseSet($set_id);
        $this->set_id = $courseset->getId();
        $this->courses = Course::findMany($courseset->getCourses(), "ORDER BY VeranstaltungsNummer, Name");
        $this->applications = AdmissionPriority::getPrioritiesStats($courseset->getId());
        $distinct_members = [];
        $multi_members = [];
        foreach($this->courses as $course) {
            $all_members = $course->members->findBy('status', words('user autor'))->pluck('user_id');
            $all_members = array_merge($all_members, $course->admission_applicants->findBy('status', words('accepted awaiting'))->pluck('user_id'));
            $all_members = array_unique($all_members);
            foreach ($all_members as $one) {
                $multi_members[$one]++;
            }
            $distinct_members = array_unique(array_merge($distinct_members, $all_members));
        }

        $multi_members = array_filter($multi_members, function($a) {return $a > 1;});
        $this->count_distinct_members = count($distinct_members);
        $this->count_multi_members = count($multi_members);

        if ($csv === 'csv') {
            $captions = [
                _('Nummer'), _('Name'), _('versteckt'), _('Zeiten'), _('Dozenten'),
                _('max. Teilnehmende'), _('Teilnehmende aktuell'), _('Anzahl Anmeldungen'),
                _('Anzahl Anmeldungen Prio 1'), _('Warteliste'), _('max. Anzahl Warteliste'),
                _('automatisches Nachrücken aus der Warteliste') , _('vorläufige Anmeldung'),
                _('verbindliche Anmeldung'),
            ];
            $data = [];
            foreach ($this->courses as $course) {
                $row = [];
                $row[] = $course->veranstaltungsnummer;
                $row[] = $course->name;
                $row[] = $course->visible ? _("nein") : _("ja");
                $row[] = join('; ', $course->cycles->toString());
                $row[] = join(', ', $course->members->findBy('status','dozent')->orderBy('position')->pluck('Nachname'));
                $row[] = $course->admission_turnout;
                $row[] = $course->getNumParticipants();
                $row[] = $this->applications[$course->id]['c'];
                $row[] = $this->applications[$course->id]['h'];
                $row[] = $course->admission_disable_waitlist ? _('nein') : _('ja');
                $row[] = $course->admission_waitlist_max > 0 ? $course->admission_waitlist_max : '';
                $row[] = $course->admission_disable_waitlist_move ? _('nein') : _('ja');
                $row[] = $course->admission_prelim ? _('ja') : _('nein');
                $row[] = $course->admission_binding ? _('ja') : _('nein');

                $data[] = $row;
            }
            $tmpname = md5(uniqid('tmp'));
            if (array_to_csv($data, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
                $this->redirect(
                    FileManager::getDownloadURLForTemporaryFile(
                        $tmpname,
                        'Veranstaltungen_' . $courseset->getName() . '.csv'
                    )
                );
                return;
            }
        }
        if (in_array($csv, words('download_all_members download_multi_members'))) {
            $liste = [];
            $multi_members = $all_participants = [];
            foreach($this->courses as $course) {
                $participants = $course->members->findBy('status', words('user autor'))->toGroupedArray('user_id', words('username vorname nachname email status'));
                $participants += $course->admission_applicants->findBy('status', words('accepted awaiting'))->toGroupedArray('user_id', words('username vorname nachname email status'));
                $all_participants += $participants;
                foreach (array_keys($participants) as $one) {
                    $multi_members[$one][] = $course->name . ($course->veranstaltungsnummer ? '|'. $course->veranstaltungsnummer : '');
                }
                foreach ($participants as $user_id => $part) {
                    $liste[] = [$part['username'], $part['vorname'], $part['nachname'], $part['email'], $course->name . ($course->veranstaltungsnummer ? '|'. $course->veranstaltungsnummer : '') , $part['status']];
                }
            }
            if ($csv == 'download_all_members') {
                $captions = [_("Username"), _("Vorname"), _("Nachname"), _("E-Mail"), _("Veranstaltung"), _("Status")];
                if (count($liste)) {
                    $tmpname = md5(uniqid('tmp'));
                    if (array_to_csv($liste, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
                        $this->redirect(
                            FileManager::getDownloadURLForTemporaryFile(
                                $tmpname,
                                'Gesamtteilnehmendenliste_' . $courseset->getName() . '.csv'
                            )
                        );
                        return;
                    }
                }
            } else {
                $liste = [];
                $multi_members = array_filter($multi_members, function ($a) {return count($a) > 1;});
                $c = 0;
                $max_count = [];
                foreach ($multi_members as $user_id => $courses) {
                    $member = $all_participants[$user_id];
                    $liste[$c] = [$member['username'], $member['vorname'], $member['nachname'], $member['email']];
                    foreach ($courses as  $one) {
                        $liste[$c][] = $one;
                    }
                    $max_count[] = count($courses);
                    $c++;
                }
                $captions = [_("Nutzername"), _("Vorname"), _("Nachname"), _("E-Mail")];
                foreach (range(1,max($max_count)) as $num) {
                    $captions[] = _("Veranstaltung") . ' ' . $num;
                }
            if (count($liste)) {
                    $tmpname = md5(uniqid('tmp'));
                    if (array_to_csv($liste, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
                        $this->redirect(
                            FileManager::getDownloadURLForTemporaryFile(
                                $tmpname,
                                'Mehrfachanmeldungen_' . $courseset->getName() . '.csv'
                            )
                        );
                        return;
                    }
                }
            }
        }

        if (Request::submitted('configure_courses_save')) {
            CSRFProtection::verifyUnsafeRequest();
            $admission_turnouts = Request::intArray('configure_courses_turnout');
            $admission_waitlists = Request::intArray('configure_courses_disable_waitlist');
            $admission_waitlists_max = Request::intArray('configure_courses_waitlist_max');
            $admission_disable_waitlist_move = Request::intArray('admission_disable_waitlist_move');
            $admission_bindings = Request::intArray('configure_courses_binding');
            $admission_prelims = Request::intArray('configure_courses_prelim');
            $hidden = Request::intArray('configure_courses_hidden');
            $ok = 0;
            foreach($this->courses as $course) {
                if ($GLOBALS['perm']->have_studip_perm('admin', $course->id)) {
                    $do_update_admission = $course->admission_turnout < $admission_turnouts[$course->id];
                    $course->admission_turnout = $admission_turnouts[$course->id];
                    $course->admission_disable_waitlist = isset($admission_waitlists[$course->id]) ? 0 : 1;
                    $course->admission_waitlist_max = $course->admission_disable_waitlist ? 0 : $admission_waitlists_max[$course->id];
                    $course->admission_disable_waitlist_move = isset($admission_disable_waitlist_move[$course->id]) ? 0 : 1;
                    $course->admission_binding = @$admission_bindings[$course->id] ?: 0;
                    $course->admission_prelim = @$admission_prelims[$course->id] ?: 0;
                    $course->visible = @$hidden[$course->id] ? 0 : 1;

                    $ok += $course->store();
                    if ($do_update_admission) {
                        update_admission($course->id);
                    }
                }
            }
            if ($ok) {
                PageLayout::postMessage(MessageBox::success(_("Die zugeordneten Veranstaltungen wurden konfiguriert.")));
            }
            $this->redirect($this->url_for('admission/courseset/configure/' . $courseset->getId()));
            return;
        }
    }

    /**
     * Show users who are on an assigned user factor list.
     *
     * @param String $set_id course set to fetch the user lists from
     */
    public function factored_users_action($set_id)
    {
        PageLayout::setTitle(_('Liste der Personen'));

        $courseset = new CourseSet($set_id);
        $factored_users = $courseset->getUserFactorList();
        $applicants = AdmissionPriority::getPriorities($set_id);
        $this->users = User::findAndMapMany(function($u) use ($factored_users, $applicants) {
            return array_merge(
                $u->toArray('username vorname nachname'),
                ['applicant' => isset($applicants[$u->id]), 'factor' => $factored_users[$u->id]]
            );
        }, array_keys($factored_users), 'ORDER BY Nachname, Vorname');
    }

    /**
     * Gets the list of applicants for the courses belonging to this course set.
     *
     * @param String $set_id course set ID
     * @param String $csv    export users to file
     */
    public function applications_list_action($set_id, $csv = null)
    {
        PageLayout::setTitle(_('Liste der Anmeldungen'));

        $courseset = new CourseSet($set_id);
        $applicants = AdmissionPriority::getPriorities($set_id);
        $users = User::findMany(array_keys($applicants), 'ORDER BY Nachname, Vorname');
        $courses = SimpleCollection::createFromArray(Course::findMany($courseset->getCourses()));
        $captions = [_('Nachname'), _('Vorname'), _('Nutzername'), _('Veranstaltung'), _('Nummer'), _('Studiengang'), _('Priorität')];
        $data = [];
        $studycourses = function ($st) {
            return sprintf(
                '%s (%s)',
                trim($st->studycourse_name . ' ' . $st->degree_name),
                $st->semester
            );
        };
        foreach ($users as $user) {
            $row = [];
            $app_courses = $applicants[$user->id];
            asort($app_courses);

            foreach ($app_courses as $course_id => $prio) {
                $row = [];
                $row[] = $user->nachname;
                $row[] = $user->vorname;
                $row[] = $user->username;
                $row[] = $courses->findOneBy('id', $course_id)->name;
                $row[] = $courses->findOneBy('id', $course_id)->veranstaltungsnummer;
                $row[] = implode('; ', $user->studycourses->map($studycourses));
                $row[] = $prio;
                if ($csv) {
                    $row[] = $user->email;
                }
                $data[] = $row;
            }
        }
        if ($csv) {
            $tmpname = md5(uniqid('tmp'));
            $captions[] = _("E-Mail");
            if (array_to_csv($data, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
                $this->redirect(
                    FileManager::getDownloadURLForTemporaryFile(
                        $tmpname,
                        'Anmeldungen_' . $courseset->getName() . '.csv'
                    )
                );
                return;
            }
        }
        $this->captions = $captions;
        $this->data = $data;
        $this->set_id = $courseset->getId();
    }

    public function applicants_message_action($set_id)
    {
        $courseset = new CourseSet($set_id);
        $applicants = AdmissionPriority::getPriorities($set_id);
        $_SESSION['sms_data'] = [];
        $_SESSION['sms_data']['p_rec'] = User::findAndMapMany(function ($u) {
            return $u->username;
        }, array_unique(array_keys($applicants)));
        $this->redirect(URLHelper::getURL('dispatch.php/messages/write',
            ['default_subject' => _("Anmeldung:") . ' ' . $courseset->getName(),
                  'emailrequest'    => 1
            ]
        ));
    }

    function copy_action($set_id)
    {
        $courseset = new CourseSet($set_id);
        $cloned_courseset = clone $courseset;
        $cloned_courseset->setName(_("Kopie von:") . ' ' . $cloned_courseset->getName());
        $cloned_courseset->store();
        foreach ($cloned_courseset->getAdmissionRules() as $id => $rule) {
            if ($rule instanceOf ParticipantRestrictedAdmission) {
                if ($rule->getDistributionTime() && $rule->getDistributionTime() < time()) {
                    $rule->setDistributionTime(strtotime('+1 month 23:59'));
                    $rule->store();
                    $cloned_courseset->setAlgorithmRun(false);
                    PageLayout::postMessage(MessageBox::info(sprintf(_("Bitte passen Sie das Datum der automatischen Platzverteilung an, es wurde automatisch auf %s festgelegt!"), strftime('%x %X', $rule->getDistributiontime()))));
                }
            } else if ($rule->getEndTime() && $rule->getEndTime() < time()) {
                PageLayout::postMessage(MessageBox::info(sprintf(_("Der Gültigkeitszeitraum der Regel %s endet in der Vergangenheit!"), $rule->getName())));
            }
        }
        $this->redirect(URLHelper::getURL('dispatch.php/admission/courseset/configure/' .
            $cloned_courseset->getId(), ['is_copy' => 1]));
    }

    /**
     * Gets courses fulfilling the given condition.
     *
     * @param String $seminare_condition SQL condition
     */
    function get_courses($seminare_condition)
    {
        global $perm, $user;

        list($institut_id, $all) = explode('_', $this->current_institut_id);
        // Prepare count statements
        $query = "SELECT count(*)
                FROM seminar_user
                WHERE seminar_id = ? AND status IN ('user', 'autor')";
        $count0_statement = DBManager::get()->prepare($query);

        $query = "SELECT SUM(status = 'accepted') AS count2,
                SUM(status = 'awaiting') AS count3
                FROM admission_seminar_user
                WHERE seminar_id = ?
                GROUP BY seminar_id";
        $count1_statement = DBManager::get()->prepare($query);

        $parameters = [];

        $sql = "SELECT seminare.seminar_id,seminare.Name as course_name,seminare.VeranstaltungsNummer as course_number,
                admission_prelim, admission_turnout,seminar_courseset.set_id
                FROM seminar_courseset
                INNER JOIN courseset_rule csr ON csr.set_id=seminar_courseset.set_id AND csr.type='ParticipantRestrictedAdmission'
                INNER JOIN seminare ON seminar_courseset.seminar_id=seminare.seminar_id
                ";
        if ($institut_id == 'all'  && $perm->have_perm('root')) {
            $sql .= "WHERE 1 {$seminare_condition} ";
        } elseif ($all == 'all') {
            $sql .= "INNER JOIN Institute USING (Institut_id)
            WHERE Institute.fakultaets_id = ? {$seminare_condition}
            ";
            $parameters[] = $institut_id;
        } else {
            $sql .= "WHERE seminare.Institut_id = ? {$seminare_condition}
            ";
            $parameters[] = $institut_id;
        }
        $sql .= "GROUP BY seminare.Seminar_id ORDER BY seminar_courseset.set_id, seminare.Name";

        $statement = DBManager::get()->prepare($sql);
        $statement->execute($parameters);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $seminar_id = $row['seminar_id'];
            $ret[$seminar_id] = $row;

            $count0_statement->execute([$seminar_id]);
            $count = $count0_statement->fetchColumn();

            $ret[$seminar_id]['count_teilnehmer']     = $count;

            $count1_statement->execute([$seminar_id]);
            $counts = $count1_statement->fetch(PDO::FETCH_ASSOC);

            $ret[$seminar_id]['count_prelim'] = (int)$counts['count2'];
            $ret[$seminar_id]['count_waiting']  = (int)$counts['count3'];
            $cs = new CourseSet($row['set_id']);
            $ret[$seminar_id]['cs_name'] = $cs->getName();
            $ret[$seminar_id]['distribution_time'] = $cs->getSeatDistributionTime();
            if ($ta = $cs->getAdmissionRule('TimedAdmission')) {
                $ret[$seminar_id]['start_time'] = $ta->getStartTime();
                $ret[$seminar_id]['end_time'] = $ta->getEndTime();
            }
            if (!$cs->hasAlgorithmRun()) {
                $ret[$seminar_id]['count_claiming'] = $cs->getNumApplicants();
            }
        }
        return $ret;
    }

}

?>
