<?php

/*
 * MembersController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-oldenburg.de>
 * @author      Sebastian Hobert <sebastian.hobert@uni-goettingen.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.5
 */

require_once 'app/models/members.php';
require_once 'lib/messaging.inc.php'; //Funktionen des Nachrichtensystems

require_once 'lib/admission.inc.php'; //Funktionen der Teilnehmerbegrenzung
require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionne für den Export
require_once 'lib/export/export_linking_func.inc.php';


class Course_MembersController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        global $perm;

        checkObject();
        checkObjectModule("participants");

        $this->course_id    = Context::getId();
        $this->course_title = Context::get()->Name;
        $this->user_id      = $GLOBALS['user']->id;

        // Check perms
        $this->is_dozent = $perm->have_studip_perm('dozent', $this->course_id);
        $this->is_tutor  = $perm->have_studip_perm('tutor', $this->course_id);
        $this->is_autor  = $perm->have_studip_perm('autor', $this->course_id);

        if ($this->is_tutor) {
            PageLayout::setHelpKeyword("Basis.VeranstaltungenVerwaltenTeilnehmer");
        } else {
            PageLayout::setHelpKeyword("Basis.InVeranstaltungTeilnehmer");
        }

        // Check lock rules
        $this->dozent_is_locked = LockRules::Check($this->course_id, 'dozent');
        $this->tutor_is_locked  = LockRules::Check($this->course_id, 'tutor');
        $this->is_locked        = LockRules::Check($this->course_id, 'participants');

        // Layoutsettings
        PageLayout::setTitle(sprintf('%s - %s', Course::findCurrent()->getFullname(), _("Teilnehmende")));

        SkipLinks::addIndex(Navigation::getItem('/course/members')->getTitle(), 'main_content', 100);

        object_set_visit_module('participants');
        $this->last_visitdate = object_get_visit($this->course_id, 'participants');

        // Check perms and set the last visit date
        if (!$this->is_tutor) {
            $this->last_visitdate = time() + 10;
        }

        // Get the max-page-value for the pagination
        $this->max_per_page = Config::get()->ENTRIES_PER_PAGE;
        $this->status_groups = [
            'dozent' => get_title_for_status('dozent', 2),
            'tutor' => get_title_for_status('tutor', 2),
            'autor' => get_title_for_status('autor', 2),
            'user' => get_title_for_status('user', 2),
            'accepted' => get_title_for_status('accepted', 2),
            'awaiting' => _("Wartende Personen"),
            'claiming' => _("Wartende Personen")
        ];

        // StatusGroups for the view
        $this->decoratedStatusGroups = [
            'dozent' => get_title_for_status('dozent', 1),
            'autor' => get_title_for_status('autor', 1),
            'tutor' => get_title_for_status('tutor', 1),
            'user' => get_title_for_status('user', 1)
        ];

        //check for admission / waiting list
        update_admission($this->course_id);

        // Create new MembersModel, to get additionanl informations to a given Seminar
        $this->members = new MembersModel($this->course_id, $this->course_title);
        $this->members->checkUserVisibility();

        // Set default sidebar image
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/person-sidebar.png');
    }

    public function index_action()
    {

        $sem                = Seminar::getInstance($this->course_id);
        $this->sort_by      = Request::option('sortby', 'nachname');
        $this->order        = Request::option('order', 'desc');
        $this->sort_status  = Request::get('sort_status');

        Navigation::activateItem('/course/members/view');
        if (Request::int('toggle')) {
            $this->order = $this->order == 'desc' ? 'asc' : 'desc';
        }

        $filtered_members = $this->members->getMembers($this->sort_status, $this->sort_by . ' ' . $this->order, !$this->is_tutor ? $this->user_id : null);

        if ($this->is_tutor) {
            $filtered_members = array_merge($filtered_members, $this->members->getAdmissionMembers($this->sort_status, $this->sort_by . ' ' . $this->order ));
            $this->awaiting = $filtered_members['awaiting']->toArray('user_id username vorname nachname visible mkdate');
            $this->accepted = $filtered_members['accepted']->toArray('user_id username vorname nachname visible mkdate');
            $this->claiming = $filtered_members['claiming']->toArray('user_id username vorname nachname visible mkdate');
        }

        // Check autor-perms
        if (!$this->is_tutor) {
            SkipLinks::addIndex(_("Sichtbarkeit ändern"), 'change_visibility');
            // filter invisible user
            $this->invisibles = count($filtered_members['autor']->findBy('visible', 'no')) + count($filtered_members['user']->findBy('visible', 'no'));
            $current_user_id = $this->user_id;
            $exclude_invisibles =
                    function ($user) use ($current_user_id) {
                        return ($user['visible'] != 'no' || $user['user_id'] == $current_user_id);
                    };
            $filtered_members['autor'] = $filtered_members['autor']->filter($exclude_invisibles);
            $filtered_members['user'] = $filtered_members['user']->filter($exclude_invisibles);
            $this->my_visibility = $this->getUserVisibility();
            if (!$this->my_visibility['iam_visible']) {
                $this->invisibles--;
            }
        }

        // get member informations
        $this->dozenten = $filtered_members['dozent']->toArray('user_id username vorname nachname');
        $this->tutoren = $filtered_members['tutor']->toArray('user_id username vorname nachname mkdate');
        $this->autoren = $filtered_members['autor']->toArray('user_id username vorname nachname visible mkdate');
        $this->users = $filtered_members['user']->toArray('user_id username vorname nachname visible mkdate');
        $this->studipticket = Seminar_Session::get_ticket();
        $this->subject = $this->getSubject();
        $this->groups = $this->status_groups;
        // Check Seminar
        if ($this->is_tutor && $sem->isAdmissionEnabled()) {
            $this->course = $sem;
            $distribution_time = $sem->getCourseSet()->getSeatDistributionTime();
            if ($sem->getCourseSet()->hasAlgorithmRun()) {
                $this->waitingTitle = _("Warteliste");
                if (!$sem->admission_disable_waitlist_move) {
                    $this->waitingTitle .= ' (' . _("automatisches Nachrücken ist eingeschaltet") . ')';
                } else {
                    $this->waitingTitle .= ' (' . _("automatisches Nachrücken ist ausgeschaltet") . ')';
                }
                $this->semAdmissionEnabled = 2;
                $this->waiting_type = 'awaiting';
            } else {
                $this->waitingTitle = sprintf(_("Anmeldeliste (Platzverteilung am %s)"), strftime('%x %R', $distribution_time));
                $this->semAdmissionEnabled = 1;
                $this->awaiting = $this->claiming;
                $this->waiting_type = 'claiming';
            }
        }
        // Set the infobox
        $this->createSidebar($filtered_members);

        if ($this->is_locked && $this->is_tutor) {
            $lockdata = LockRules::getObjectRule($this->course_id);
            if ($lockdata['description']) {
                PageLayout::postMessage(MessageBox::info(formatLinks($lockdata['description'])));
            }
        }

        // Check for waitlist availability (influences available actions)
        // People can be moved to waitlist if waitlist available and no automatic moving up.
        if (!$sem->admission_disable_waitlist && ((count($this->autoren) + count($this->users) > $sem->admission_turnout) || $sem->admission_disable_waitlist_move)
        && $sem->isAdmissionEnabled() && $sem->getCourseSet()->hasAlgorithmRun()) {
            $this->to_waitlist_actions = true;
        }
    }

    /*
     * Returns an array with emails of members
     */
    public function getEmailLinkByStatus($status, $members)
    {
        if (!get_config('ENABLE_EMAIL_TO_STATUSGROUP')) {
            return;
        }

        if (in_array($status, words('accepted awaiting claiming'))) {
            $textStatus = _('Wartenden');
        } else {
            $textStatus = $this->status_groups[$status];
        }

        $results = SimpleCollection::createFromArray($members)->pluck('email');

        if (!empty($results)) {
            return sprintf('<a href="mailto:%s">%s</a>', htmlReady(join(',', $results)), Icon::create('mail+move_right', 'clickable', ['title' => sprintf('E-Mail an alle %s versenden',$textStatus)])->asImg(16));
        } else {
            return null;
        }
    }

    /**
     * Show dialog to enter a comment for this user
     * @param String $user_id
     * @throws AccessDeniedException
     */
    public function add_comment_action($user_id = null)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        $course_member = CourseMember::find([$this->course_id, $user_id]);
        if (!$course_member) {
            $course_member = AdmissionApplication::find([$user_id, $this->course_id]);
        }
        if (is_null($course_member)) {
            throw new Trails_Exception(400);
        }
        $this->comment = $course_member->comment;
        $this->user = User::find($user_id);
        PageLayout::setTitle(sprintf(_('Bemerkung für %s'), $this->user->getFullName()));

        // Output as dialog (Ajax-Request) or as Stud.IP page?
        $this->xhr = Request::isXhr();
        if ($this->xhr) {
            $this->set_layout(null);
        } else {
            Navigation::activateItem('/course/members/view');
        }
    }

    /**
     * Store a comment for this user
     * @param String $user_id
     * @throws AccessDeniedException
     */
    public function set_comment_action($user_id = null)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        CSRFProtection::verifyUnsafeRequest();
        $course_member = CourseMember::find([$this->course_id, $user_id]);
        if (!$course_member) {
            $course_member = AdmissionApplication::find([$user_id, $this->course_id]);
        }
        if (!Request::submitted('save') || is_null($course_member)) {
            throw new Trails_Exception(400);
        }
        $course_member->comment = Request::get('comment');

        if ($course_member->store() !== false) {
            PageLayout::postSuccess(_('Bemerkung wurde erfolgreich gespeichert.'));
        } else {
            PageLayout::postError(_('Bemerkung konnte nicht erfolgreich gespeichert werden.'));
        }
        $this->redirect('course/members/index');
    }

    /**
     * Add members to a seminar.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_autor_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_autor" . $this->course_id);
//        $sem = Seminar::GetInstance($this->course_id);

        $countAdded = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if($this->members->addMember($a, 'autor', Request::get('consider_contingent'))) {
                $countAdded++;
            }
        }

        if ($countAdded == 1) {
            $text = _("Es wurde eine neue Person hinzugefügt.");
        } else {
            $text = sprintf(_("Es wurden %s neue Personen hinzugefügt."), $countAdded);
        }
        PageLayout::postSuccess($text);
        $this->redirect('course/members/index');
    }

     /**
     * Add dozents to a seminar.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_dozent_action()
    {
        // Security Check
        if (!$this->is_dozent) {
            throw new AccessDeniedException('Sie sind nicht bereichtig, auf diesen Bereich von Stud.IP zuzugreifen.');
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_dozent" . $this->course_id);
        $sem = Seminar::GetInstance($this->course_id);
        $countAdded = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if($this->addDozent($a)) {
                $countAdded++;
            }
        }
        if($countAdded > 0) {
            $status = get_title_for_status('dozent', $countAdded, $sem->status);
            if ($countAdded == 1) {
                PageLayout::postSuccess(sprintf(_('Ein %s wurde hinzugefügt.'), $status));
            } else {
                PageLayout::postSuccess(sprintf(_("Es wurden %s %s Personen hinzugefügt."), $countAdded, $status));
            }
        }

        $this->redirect('course/members/index');
    }

    /**
     * Add people to a course waitlist.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_waitlist_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load('add_waitlist' . $this->course_id);
        $countAdded = 0;
        $countFailed = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if ($this->members->addToWaitlist($a)) {
                $countAdded++;
            } else {
                $countFailed++;
            }
        }

        if ($countAdded) {
            PageLayout::postSuccess(sprintf(ngettext('Es wurde %u neue Person auf der Warteliste hinzugefügt.',
                'Es wurden %u neue Personen auf der Warteliste hinzugefügt.', $countAdded), $countAdded));
        }
        if ($countFailed) {
            PageLayout::postError(sprintf(ngettext('%u Person konnte nicht auf die Warteliste eingetragen werden.',
                '%u neue Personen konnten nicht auf die Warteliste eingetragen werden.', $countFailed),
                $countFailed));
        }
        $this->redirect('course/members/index');
    }

    /**
     * Helper function to add dozents to a seminar.
     */
    private function addDozent($dozent)
    {
        $sem = Seminar::GetInstance($this->course_id);
        if ($sem->addMember($dozent, "dozent")) {
            // Only applicable when globally enabled and user deputies enabled too
            if (Config::get()->DEPUTIES_ENABLE) {
                // Check whether chosen person is set as deputy
                // -> delete deputy entry.
                if (isDeputy($dozent, $this->course_id)) {
                    deleteDeputy($dozent, $this->course_id);
                }
                // Add default deputies of the chosen lecturer...
                if (Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                    $deputies = getDeputies($dozent);
                    $lecturers = $sem->getMembers('dozent');
                    foreach ($deputies as $deputy) {
                        // ..but only if not already set as lecturer or deputy.
                        if (!isset($lecturers[$deputy['user_id']]) &&
                                !isDeputy($deputy['user_id'], $this->course_id)) {
                            addDeputy($deputy['user_id'], $this->course_id);
                        }
                    }
                }
            }
           return true;
        } else {
            return false;
        }
    }

    /**
     * Add tutors to a seminar.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_tutor_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_tutor" . $this->course_id);
        $sem = Seminar::GetInstance($this->course_id);
        $countAdded = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if ($this->addTutor($a)) {
                $countAdded++;
            }
        }
        if($countAdded) {
            PageLayout::postMessage(MessageBox::success(sprintf(_('%s wurde hinzugefügt.'), get_title_for_status('tutor', $countAdded, $sem->status))));
        }
        $this->redirect('course/members/index');
    }

    private function addTutor($tutor) {
        $sem = Seminar::GetInstance($this->course_id);
        if ($sem->addMember($tutor, "tutor")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Provides a dialog to move or copy selected users to another course.
     */
    public function select_course_action()
    {
        if (Request::submitted('submit')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->flash['users_to_send'] = Request::getArray('users');
            $this->flash['target_course'] = Request::option('course_id');
            $this->flash['move'] = Request::int('move');
            $this->redirect('course/members/send_to_course');
        } else {
            global $perm;
            if ($perm->have_perm('root')) {
                $parameters = [
                    'semtypes' => studygroup_sem_types() ?: [],
                    'exclude' => [Context::getId()],
                    'semesters' => array_map(function ($s) { return $s->semester_id; }, Semester::getAll())
                ];
            } else if ($perm->have_perm('admin')) {
                $parameters = [
                    'semtypes' => studygroup_sem_types() ?: [],
                    'institutes' => array_map(function ($i) {
                        return $i['Institut_id'];
                    }, Institute::getMyInstitutes()),
                    'exclude' => [Context::getId()],
                    'semesters' => array_map(function ($s) { return $s->semester_id; }, Semester::getAll())
                ];

            } else {
                $parameters = [
                    'userid' => $GLOBALS['user']->id,
                    'semtypes' => studygroup_sem_types() ?: [],
                    'exclude' => [Context::getId()],
                    'semesters' => array_map(function ($s) { return $s->semester_id; }, Semester::getAll())
                ];
            }
            $coursesearch = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);
            $this->search = QuickSearch::get('course_id', $coursesearch)
                ->setInputStyle('width:100%')
                ->withButton()
                ->render();
            $this->course_id = Request::option('course_id');
            $this->course_id_parameter = Request::get('course_id_parameter');
            if (!empty($this->flash['users']) || Request::getArray('users')) {
                $users = $this->flash['users'] ?: Request::getArray('users');
                // create a usable array
                foreach ($this->flash['users'] as $user => $val) {
                    if ($val) {
                        $this->users[] = $user;
                    }
                }

                PageLayout::setTitle( _('Zielveranstaltung auswählen'));
            } else {
                if (Request::isXhr()) {
                    $this->response->add_header('X-Dialog-Close', '1');
                    $this->render_nothing();
                } else {
                $this->redirect('course/members/index');
            }
        }
    }
    }

    /**
     * Copies or moves selected users to the selected target course.
     */
    public function send_to_course_action()
    {
        if ($target = $this->flash['target_course']) {
            $msg = $this->members->sendToCourse(
                $this->flash['users_to_send'],
                $target,
                $this->flash['move']
            );
            if ($msg['success']) {
                if (sizeof($msg['success']) == 1) {
                    $text = _('Eine Person wurde in die Zielveranstaltung eingetragen.');
                } else {
                    $text = sprintf(_('%s Person(en) wurde(n) in die Zielveranstaltung eingetragen.'),
                        sizeof($msg['success']));
                }
                PageLayout::postSuccess($text);
            }
            if ($msg['existing']) {
                if (sizeof($msg['existing']) == 1) {
                    $text = _('Eine Person ist bereits in die Zielveranstaltung eingetragen ' .
                                'und kann daher nicht verschoben/kopiert werden.');
                } else {
                    $text = sprintf(_('%s Person(en) sind bereits in die Zielveranstaltung eingetragen ' .
                        'und konnten daher nicht verschoben/kopiert werden.'),
                        sizeof($msg['existing']));
                }
                PageLayout::postInfo($text);
            }
            if ($msg['failed']) {
                if (sizeof($msg['failed']) == 1) {
                    $text = _('Eine Person kann nicht in die Zielveranstaltung eingetragen werden.');
                } else {
                    $text = sprintf(_('%s Person(en) konnten nicht in die Zielveranstaltung eingetragen werden.'),
                            sizeof($msg['failed']));
                }
                PageLayout::postError($text);
            }
        } else {
            PageLayout::postError(_('Bitte wählen Sie eine Zielveranstaltung.'));
        }
        $this->redirect('course/members/index');
    }

    /**
     * Send Stud.IP-Message to selected users
     */
    public function send_message_action()
    {
        if (!empty($this->flash['users'])) {
            // create a usable array
            foreach ($this->flash['users'] as $user => $val) {
                if ($val) {
                    $users[] = User::find($user)->username;
                }
            }
            $_SESSION['sms_data'] = [];
            $_SESSION['sms_data']['p_rec'] = array_filter($users);
            $this->redirect(URLHelper::getURL('dispatch.php/messages/write', ['default_subject' => $this->getSubject(), 'tmpsavesnd' => 1]));
        } else {
            if (Request::isXhr()) {
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_nothing();
            } else {
            $this->redirect('course/members/index');
        }
    }
    }

    public function import_autorlist_action()
    {
        if (!Request::isXhr()) {
            Navigation::activateItem('/course/members/view');
        }
        $datafields = DataField::getDataFields('user', 1 | 2 | 4 | 8, true);
        foreach ($datafields as $df) {
            if ($df->accessAllowed() && in_array($df->getId(), $GLOBALS['TEILNEHMER_IMPORT_DATAFIELDS'])) {
                $accessible_df[] = $df;
            }
        }
        $this->accessible_df = $accessible_df;

    }

    /**
     * Old version of CSV import (copy and paste from teilnehmer.php
     * @return type
     * @throws AccessDeniedException
     */
    public function set_autor_csv_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        // prepare CSV-Lines
        $messaging = new messaging();
        $csv_request = preg_split('/(\n\r|\r\n|\n|\r)/', trim(Request::get('csv_import')));
        $csv_mult_founds = [];
        $csv_count_insert = 0;
        $csv_count_multiple = 0;
        $datafield_id = null;

        if (Request::get('csv_import_format') && !in_array(Request::get('csv_import_format'), words('realname username'))) {
            foreach (DataField::getDataFields('user', 1 | 2 | 4 | 8, true) as $df) {
                if ($df->accessAllowed() && in_array($df->getId(), $GLOBALS['TEILNEHMER_IMPORT_DATAFIELDS']) && $df->getId() == Request::quoted('csv_import_format')) {
                    $datafield_id = $df->getId();
                    break;
                }
            }
        }

        if (Request::get('csv_import')) {
            // remove duplicate users from csv-import
            $csv_lines = array_unique($csv_request);
            $csv_count_contingent_full = 0;

            foreach ($csv_lines as $csv_line) {
                $csv_name = preg_split('/[,\t]/', mb_substr($csv_line, 0, 100), -1, PREG_SPLIT_NO_EMPTY);
                $csv_nachname = trim($csv_name[0]);
                $csv_vorname = trim($csv_name[1]);

                if ($csv_nachname) {
                    if (Request::quoted('csv_import_format') == 'realname') {
                        $csv_users = $this->members->getMemberByIdentification($csv_nachname, $csv_vorname);
                    } elseif (Request::quoted('csv_import_format') == 'username') {
                        $csv_users = $this->members->getMemberByUsername($csv_nachname);
                    } else {
                        $csv_users = $this->members->getMemberByDatafield($csv_nachname, $datafield_id);
                    }
                }

                // if found more then one result to given name
                if (count($csv_users) > 1) {

                    // if user have two accounts
                    $csv_count_present = 0;
                    foreach ($csv_users as $row) {

                        if ($row['is_present']) {
                            $csv_count_present++;
                        } else {
                            $csv_mult_founds[$csv_line][] = $row;
                        }
                    }

                    if (is_array($csv_mult_founds[$csv_line])) {
                        $csv_count_multiple++;
                    }
                } elseif (count($csv_users) > 0) {
                    $row = reset($csv_users);
                    if (!$row['is_present']) {
                        $consider_contingent = Request::option('consider_contingent_csv');

                        if (insert_seminar_user($this->course_id, $row['user_id'], 'autor', isset($consider_contingent), $consider_contingent)) {
                            $csv_count_insert++;
                            setTempLanguage($this->user_id);

                            $message = sprintf(_('Sie wurden in die Veranstaltung **%s** eingetragen.'), $this->course_title);

                            restoreLanguage();
                            $messaging->insert_message($message, $row['username'], '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'), _('Eintragung in Veranstaltung')), TRUE);
                        } elseif (isset($consider_contingent)) {
                            $csv_count_contingent_full++;
                        }
                    } else {
                        $csv_count_present++;
                    }
                } else {
                    // not found
                    $csv_not_found[] = stripslashes($csv_nachname) . ($csv_vorname ? ', ' . stripslashes($csv_vorname) : '');
                }
            }
        }
        $selected_users = Request::getArray('selected_users');

        if (!empty($selected_users) && count($selected_users) > 0) {
            foreach ($selected_users as $selected_user) {
                if ($selected_user) {
                    if (insert_seminar_user($this->course_id, get_userid($selected_user), 'autor', isset($consider_contingent), $consider_contingent)) {
                        $csv_count_insert++;
                        setTempLanguage($this->user_id);
                        $message = sprintf(_('Sie wurden manuell in die Veranstaltung **%s** eingetragen.'), $this->course_title);
                        restoreLanguage();
                        $messaging->insert_message($message, $selected_user, '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'), _('Eintragung in Veranstaltung')), TRUE);
                    } elseif (isset($consider_contingent)) {
                        $csv_count_contingent_full++;
                    }
                }
            }
        }

        // no results
        if (empty($csv_lines) && empty($selected_users)) {
            PageLayout::postError(_("Niemanden gefunden!"));
        }

        if ($csv_count_insert) {
            PageLayout::postSuccess(sprintf(_('%s Personen in die Veranstaltung eingetragen!'), $csv_count_insert));
        }

        if ($csv_count_present) {
            PageLayout::postMessage(MessageBox::info(sprintf(_('%s Personen waren bereits in der Veranstaltung eingetragen!'), $csv_count_present)));
        }

        // redirect to manual assignment
        if ($csv_mult_founds) {
            PageLayout::postMessage(MessageBox::info(sprintf(_('%s Personen konnten <b>nicht eindeutig</b>
                zugeordnet werden! Nehmen Sie die Zuordnung bitte manuell vor.'), $csv_count_multiple)));
            $this->flash['csv_mult_founds'] = $csv_mult_founds;
            $this->redirect('course/members/csv_manual_assignment');
            return;
        }
        if (is_array($csv_not_found) && count($csv_not_found) > 0) {
            PageLayout::postError(sprintf(_('%s konnten <b>nicht</b> zugeordnet werden!'), htmlReady(join(',', $csv_not_found))));
        }

        if ($csv_count_contingent_full) {
            PageLayout::postError(sprintf(_('%s Personen konnten <b>nicht</b> zugeordnet werden, da das ausgewählte Kontingent keine freien Plätze hat.'),
                $csv_count_contingent_full));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Select manual the assignment of a given user or of a group of users
     * @global Object $perm
     * @throws AccessDeniedException
     */
    public function csv_manual_assignment_action()
    {
        global $perm;
        // Security. If user not autor, then redirect to index
        if (!$perm->have_studip_perm('tutor', $this->course_id)) {
            throw new AccessDeniedException();
        }

        if (empty($this->flash['csv_mult_founds'])) {
            $this->redirect('course/members/index');
        }
    }

    /**
     * Change the visibilty of an autor
     * @return Boolean
     */
    public function change_visibility_action($cmd, $mode)
    {
        global $perm;
        // Security. If user not autor, then redirect to index
        if ($perm->have_studip_perm('tutor', $this->course_id)) {
            throw new AccessDeniedException();
        }

        // Check for visibile mode
        if ($cmd == 'make_visible') {
            $command = 'yes';
        } else {
            $command = 'no';
        }

        if ($mode == 'awaiting') {
            $result = $this->members->setAdmissionVisibility($this->user_id, $command);
        } else {
            $result = $this->members->setVisibilty($this->user_id, $command);
        }

        if ($result > 0) {
            PageLayout::postSuccess(_('Ihre Sichtbarkeit wurde erfolgreich geändert.'));
        } else {
            PageLayout::postError(_('Leider ist beim Ändern der Sichtbarkeit ein Fehler aufgetreten. Die Einstellung konnte nicht vorgenommen werden.'));
        }
        $this->redirect('course/members/index');
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_tutor_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('tutor');

        // select the additional method
        switch (Request::get('action_tutor')) {
            case '':
                $target = 'course/members/index';
                break;
            case 'downgrade':
                $target = 'course/members/downgrade_user/tutor/autor';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/tutor';
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_autor_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('autor');

        switch (Request::get('action_autor')) {
            case '':
                $target = 'course/members/index';
                break;
            case 'upgrade':
                $target = 'course/members/upgrade_user/autor/tutor';
                break;
            case 'downgrade':
                $target = 'course/members/downgrade_user/autor/user';
                break;
            case 'to_admission_first':
                $target = 'course/members/to_waitlist/first';
                break;
            case 'to_admission_last':
                $target = 'course/members/to_waitlist/last';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/autor';
                break;
            case 'to_course':
                $this->redirect('course/members/select_course');
                return;
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_user_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('user');
        $this->flash['consider_contingent'] = Request::get('consider_contingent');

        // select the additional method
        switch (Request::get('action_user')) {
            case '':
                $target = 'course/members/index';
                break;
            case 'upgrade':
                $target = 'course/members/upgrade_user/user/autor';
                break;
            case 'to_admission_first':
                $target = 'course/members/to_waitlist/first';
                break;
            case 'to_admission_last':
                $target = 'course/members/to_waitlist/last';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/user';
                break;
            case 'to_course':
                $this->redirect('course/members/select_course');
                return;
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_awaiting_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('awaiting');
        $this->flash['consider_contingent'] = Request::get('consider_contingent');
        $waiting_type = Request::option('waiting_type');
        // select the additional method
        switch (Request::get('action_awaiting')) {
            case '':
                $target = 'course/members/index';
                break;
            case 'upgrade_autor':
                $target = 'course/members/insert_admission/awaiting/collection';
                break;
            case 'upgrade_user':
                $target = 'course/members/insert_admission/awaiting/collection/user';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/' . $waiting_type;
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_accepted_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('accepted');
        $this->flash['consider_contingent'] = Request::get('consider_contingent');


        // select the additional method
        switch (Request::get('action_accepted')) {
            case '':
                $target = 'course/members/index';
                break;
            case 'upgrade':
                $target = 'course/members/insert_admission/accepted/collection';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/accepted';
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Insert a user to a given seminar or a group of users
     * @param String $status
     * @param String $cmd
     * @param String $target_status
     * @return String
     * @throws AccessDeniedException
     */
    public function insert_admission_action($status, $cmd, $target_status = 'autor')
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        if (isset($this->flash['consider_contingent'])) {
            Request::set('consider_contingent', $this->flash['consider_contingent']);
        }

        // create a usable array
        $users = array_filter($this->flash['users'], function ($user) {
                    return $user;
                });

        if ($users) {
            $msgs = $this->members->insertAdmissionMember($users, $target_status, Request::get('consider_contingent'), $status == 'accepted');
            if ($msgs) {
                if ($cmd == 'add_user') {
                    $message = sprintf(_('%s wurde in die Veranstaltung mit dem Status <b>%s</b> eingetragen.'), htmlReady(join(',', $msgs)), $this->decoratedStatusGroups['autor']);
                } else {
                    if ($status == 'awaiting') {
                        $message = sprintf(_('%s wurde aus der Anmelde bzw. Warteliste mit dem Status
                            <b>%s</b> in die Veranstaltung eingetragen.'), htmlReady(join(', ', $msgs)), $this->decoratedStatusGroups[$target_status]);
                    } else {
                        $message = sprintf(_('%s wurde mit dem Status <b>%s</b> endgültig akzeptiert
                            und damit in die Veranstaltung aufgenommen.'), htmlReady(join(', ', $msgs)), $this->decoratedStatusGroups[$target_status]);
                    }
                }

                PageLayout::postSuccess($message);
            } else {
                $message = _("Es stehen keine weiteren Plätze mehr im Teilnehmendenkontingent zur Verfügung.");
                PageLayout::postError($message);
            }
        } else {
            PageLayout::postError(_('Sie haben niemanden zum Hochstufen ausgewählt.'));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Cancel the subscription of a selected user or group of users
     * @param String $cmd
     * @param String $status
     * @param String $user_id
     * @throws AccessDeniedException
     */
    public function cancel_subscription_action($cmd, $status, $user_id = null)
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        if (!Request::submitted('no')) {

            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                $users = Request::getArray('users');
                if (!empty($users)) {
                    if (in_array($status, words('accepted awaiting claiming'))) {
                        $msgs = $this->members->cancelAdmissionSubscription($users, $status);
                    } else {
                        $msgs = $this->members->cancelSubscription($users);
                    }

                    // deleted authors
                    if (!empty($msgs)) {
                        if (count($msgs) <= 5) {
                            PageLayout::postSuccess(sprintf(
                                _("%s %s wurde aus der Veranstaltung ausgetragen."),
                                htmlReady($this->status_groups[$status]),
                                htmlReady(join(', ', $msgs))
                            ));
                        } else {
                            PageLayout::postSuccess(sprintf(
                                _("%u %s wurden aus der Veranstaltung entfernt."),
                                count($msgs),
                                htmlReady($this->status_groups[$status])
                            ));
                        }
                    }
                } else {
                    PageLayout::postWarning(sprintf(
                        _('Sie haben keine %s zum Austragen ausgewählt'),
                        $this->status_groups[$status]
                    ));
                }
            } else {
                if ($cmd === 'singleuser') {
                    $users = [$user_id];
                } else {
                    // create a usable array
                    foreach ($this->flash['users'] as $user => $val) {
                        if ($val) {
                            $users[] = $user;
                        }
                    }
                }

                PageLayout::postQuestion(
                    sprintf(
                        _('Wollen Sie die/den "%s" wirklich austragen?'),
                        htmlReady($this->status_groups[$status])
                    )
                )->setAcceptURL(
                    $this->url_for("course/members/cancel_subscription/collection/{$status}"),
                    compact('users')
                );
                $this->flash['checked'] = $users;
            }
        }



        $this->redirect('course/members/index');
    }

    /**
     * Upgrade a user to a selected status
     * @param type $status
     * @param type $next_status
     * @param type $username
     * @param type $cmd
     * @throws AccessDeniedException
     */
    public function upgrade_user_action($status, $next_status)
    {
        global $perm;

        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        if ($this->is_tutor && $perm->have_studip_perm('tutor', $this->course_id) && $next_status != 'autor' && !$perm->have_studip_perm('dozent', $this->course_id)) {
            throw new AccessDeniedException();
        }

        // create a usable array
        if(!empty($this->flash['users'])) {
            foreach ($this->flash['users'] as $user => $val) {
                if ($val) {
                    $users[] = $user;
                }
            }
        }

        if (!empty($users)) {
            // insert admission user to autorlist
            $msgs = $this->members->setMemberStatus($users, $status, $next_status, 'upgrade');

            if ($msgs['success']) {
                PageLayout::postSuccess(sprintf(
                    _('Das Hochstufen auf den Status  %s von %s wurde erfolgreich durchgeführt'),
                    htmlReady($this->decoratedStatusGroups[$next_status]),
                    htmlReady(join(', ', $msgs['success']))
                ));
            }

            if ($msgs['no_tutor']) {
                PageLayout::postError(sprintf(
                    _('Das Hochstufen auf den Status %s von %s konnte nicht durchgeführt werden, weil die globale Rechtestufe "tutor" fehlt.') . ' ' . _('Bitte wenden Sie sich an den Support.'),
                    htmlReady($this->decoratedStatusGroups[$next_status]),
                    htmlReady(join(', ', $msgs['no_tutor']))
                ));
            }
        } else {
            PageLayout::postError(sprintf(_('Sie haben keine %s zum Hochstufen ausgewählt'), htmlReady($this->status_groups[$status])));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Downgrade a user to a selected status
     * @param type $status
     * @param type $next_status
     * @param type $username
     * @param type $cmd
     * @throws AccessDeniedException
     */
    public function downgrade_user_action($status, $next_status)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        // TODO: Check this
        if ($this->is_tutor && $next_status !== 'user' && !$this->is_dozent) {
            throw new AccessDeniedException();
        }

        if (!empty($this->flash['users'])) {
            foreach ($this->flash['users'] as $user => $val) {
                if ($val) {
                    $users[] = $user;
                }
            }
        }

        if (!empty($users)) {
            $msgs = $this->members->setMemberStatus($users, $status, $next_status, 'downgrade');

            if ($msgs['success']) {
                PageLayout::postSuccess(sprintf(
                    _('Der/die %s %s wurde auf den Status %s heruntergestuft.'),
                    htmlReady($this->decoratedStatusGroups[$status]),
                    htmlReady(join(', ', $msgs['success'])),
                    $this->decoratedStatusGroups[$next_status]));
            }
        } else {
            PageLayout::postError(sprintf(
                _('Sie haben keine %s zum Herunterstufen ausgewählt'),
                htmlReady($this->status_groups[$status])
            ));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Moves selected users to waitlist, either at the top or at the end.
     * @param $which_end 'first' or 'last': append to top or to end of waitlist?
     */
    public function to_waitlist_action($which_end)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        $users = [];
        if (!empty($this->flash['users'])) {
            $users = array_keys(array_filter($this->flash['users']));
        }
        
        if (!empty($users)) {
            $msg = $this->members->moveToWaitlist($users, $which_end);
            if (count($msg['success'])) {
                PageLayout::postSuccess(sprintf(_('%s Person(en) wurden auf die Warteliste verschoben.'),
                    count($msg['success'])),
                    count($msg['success']) <= 5 ? $msg['success'] : []);
            }
            if (count($msg['errors'])) {
                PageLayout::postError(sprintf(_('%s Person(en) konnten nicht auf die Warteliste verschoben werden.'),
                    count($msg['errors'])),
                    count($msg['error']) <= 5 ? $msg['error'] : []);
            }
        } else {
            PageLayout::postError(_('Sie haben keine Personen zum Verschieben auf die Warteliste ausgewählt'));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Displays all members of the course and their aux data
     * @return int fake return to stop after redirect;
     */
    public function additional_action($format = null)
    {
        // Users get forwarded to aux_input
        if (!($this->is_dozent || $this->is_tutor)) {
            $this->redirect('course/members/additional_input');
            return 0;
        }

        Navigation::activateItem('/course/members/additional');

        // fetch course and aux data
        $course    = Course::findCurrent();
        $this->aux = $course->aux->getCourseData($course);

        $export_widget = new ExportWidget();
        $export_widget->addLink(
            _('Zusatzangaben exportieren'),
            $this->url_for('course/members/export_additional'),
            Icon::create('file-excel')
        );

        Sidebar::Get()->addWidget($export_widget);
    }

    /**
     * Stora all members of the course and their aux data
     */
    public function store_additional_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $course = Course::findCurrent();

        foreach ($course->members->findBy('status', 'autor') as $member) {
            $course->aux->updateMember($member, Request::getArray($member->user_id));
        }

        $this->redirect('course/members/additional');
    }

    /**
     * Export all members of the course and their aux data to CSV
     */
    public function export_additional_action()
    {
        $course  = Course::findCurrent();
        $aux     = $course->aux->getCourseData($course, true);
        $tmpname = md5(uniqid('Zusatzangaben'));

        if(array_to_csv($aux['rows'], $GLOBALS['TMP_PATH'] . '/' . $tmpname, $aux['head'])) {
            $this->redirect(
                FileManager::getDownloadURLForTemporaryFile(
                    $tmpname,
                    _('Zusatzangaben') . '.csv'
                )
            );
        }
    }

    /**
     * Aux input for users
     */
    public function additional_input_action()
    {
        // Activate the autoNavi otherwise we dont find this page in navi
        Navigation::activateItem('/course/members/additional');

        // Fetch datafields for the user
        $course = Course::findCurrent();
        $member = $course->members->findOneBy('user_id', $GLOBALS['user']->id);
        $this->datafields = $member ? $course->aux->getMemberData($member) : [];
        // We need aux data in the view
        $this->aux = $course->aux;

        // Update em if they got submittet
        if (Request::submitted('save')) {
            $datafields = SimpleCollection::createFromArray($this->datafields);
            foreach (Request::getArray('aux') as $aux => $value) {
                $datafield = $datafields->findOneBy('datafield_id', $aux);
                if ($datafield) {
                    $typed = $datafield->getTypedDatafield();
                    if ($typed->isEditable()) {
                        $typed->setValueFromSubmit($value);
                        $typed->store();
                    }
                }
            }
        }
    }

    /**
     * Get the visibility of a user in a seminar
     * @param String $user_id
     * @param String $seminar_id
     * @return Array
     */
    private function getUserVisibility()
    {
        $member = CourseMember::find([$this->course_id, $this->user_id]);

        $visibility = $member->visible;
        $status = $member->status;
        #echo "<pre>"; var_dump($member); echo "</pre>";
        $result['visible_mode'] = false;

        if ($visibility) {
            $result['iam_visible'] = ($visibility == 'yes' || $visibility == 'unknown');

            if ($status == 'user' || $status == 'autor') {
                $result['visible_mode'] = 'participant';
            } else {
                $result['iam_visible'] = true;
                $result['visible_mode'] = false;
            }
        }

        return $result;
    }

    /**
     * Returns the Subject for the Messaging
     * @return String
     */
    private function getSubject()
    {
        $result = Seminar::GetInstance($this->course_id)->getNumber();

        $subject = ($result == '') ? sprintf('[%s]', $this->course_title) :
                sprintf(_('[%s: %s]'), $result, $this->course_title);

        return $subject;
    }

    private function createSidebar($filtered_members)
    {
        $sem = Seminar::GetInstance($this->course_id);
        $config = CourseConfig::get($this->course_id);

        $sidebar = Sidebar::get();
        $widget  = $sidebar->addWidget(new ActionsWidget());

        if ($this->is_tutor || $config->COURSE_STUDENT_MAILING) {
            $url = URLHelper::getURL('dispatch.php/messages/write', [
                'course_id'       => $this->course_id,
                'default_subject' => $this->subject,
                'filter'          => 'all',
                'emailrequest'    => 1
            ]);
            $widget->addLink(
                _('Nachricht an alle (Rundmail)'),
                $url,
                Icon::create('inbox')
            )->asDialog();
        }
        if ($this->is_tutor) {
            if ($this->is_dozent) {
                if (!$this->dozent_is_locked) {
                    $sem_institutes = $sem->getInstitutes();

                    if (SeminarCategories::getByTypeId($sem->status)->only_inst_user) {
                        $search_template = "user_inst";
                    } else {
                        $search_template = "user";
                    }

                    // create new search for dozent
                    $searchtype = new PermissionSearch(
                            $search_template, sprintf(_("%s suchen"), get_title_for_status('dozent', 1, $sem->status)), "user_id", ['permission' => 'dozent',
                            'exclude_user' => [],
                            'institute' => $sem_institutes]
                    );


                    // quickfilter: dozents of institut
                    $sql = "SELECT user_id FROM user_inst WHERE Institut_id = ? AND inst_perms = 'dozent'";
                    $db = DBManager::get();
                    $statement = $db->prepare($sql, [PDO::FETCH_NUM]);
                    $statement->execute([Seminar::getInstance($this->course_id)->getInstitutId()]);
                    $membersOfInstitute = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                    // add "add dozent" to infobox
                    $mp = MultiPersonSearch::get('add_dozent' . $this->course_id)
                        ->setLinkText(sprintf(_('%s eintragen'), get_title_for_status('dozent', 1)))
                        ->setDefaultSelectedUser($filtered_members['dozent']->pluck('user_id'))
                        ->setLinkIconPath("")
                        ->setTitle(sprintf(_('%s eintragen'), get_title_for_status('dozent', 1)))
                        ->setExecuteURL(URLHelper::getLink('dispatch.php/course/members/execute_multipersonsearch_dozent'))
                        ->setSearchObject($searchtype)
                        ->addQuickfilter(sprintf(_('%s der Einrichtung'), $this->status_groups['dozent']), $membersOfInstitute)
                        ->setNavigationItem('/course/members/view')
                        ->render();
                    $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
                    $widget->addElement($element);
                }
                if (!$this->tutor_is_locked) {
                    $sem_institutes = $sem->getInstitutes();

                    if (SeminarCategories::getByTypeId($sem->status)->only_inst_user) {
                        $search_template = 'user_inst';
                    } else {
                        $search_template = 'user';
                    }

                    // create new search for tutor
                    $searchType = new PermissionSearch(
                            $search_template, sprintf(_('%s suchen'), get_title_for_status('tutor', 1, $sem->status)), 'user_id', ['permission' => ['dozent', 'tutor'],
                        'exclude_user' => [],
                        'institute' => $sem_institutes]
                    );

                    // quickfilter: tutors of institut
                    $sql = "SELECT user_id FROM user_inst WHERE Institut_id = ? AND inst_perms = 'tutor'";
                    $db = DBManager::get();
                    $statement = $db->prepare($sql, [PDO::FETCH_NUM]);
                    $statement->execute([Seminar::getInstance($this->course_id)->getInstitutId()]);
                    $membersOfInstitute = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                    // add "add tutor" to infobox
                    $mp = MultiPersonSearch::get("add_tutor" . $this->course_id)
                        ->setLinkText(sprintf(_('%s eintragen'), get_title_for_status('tutor', 1)))
                        ->setDefaultSelectedUser($filtered_members['tutor']->pluck('user_id'))
                        ->setLinkIconPath("")
                        ->setTitle(sprintf(_('%s eintragen'), get_title_for_status('tutor', 1)))
                        ->setExecuteURL(URLHelper::getLink('dispatch.php/course/members/execute_multipersonsearch_tutor'))
                        ->setSearchObject($searchType)
                        ->addQuickfilter(sprintf(_('%s der Einrichtung'), $this->status_groups['tutor']), $membersOfInstitute)
                        ->setNavigationItem('/course/members/view')
                        ->render();
                    $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
                    $widget->addElement($element);
                }
            }
            if (!$this->is_locked) {
                // create new search for members
                $searchType = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(" . $GLOBALS['_fullname_sql']['full'] .
                    ", \" (\", auth_user_md5.username, \")\") as fullname " .
                    "FROM auth_user_md5 " .
                    "LEFT JOIN user_info ON (user_info.user_id = auth_user_md5.user_id) " .
                    "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input " .
                    "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input " .
                    "OR auth_user_md5.username LIKE :input) " .
                    "AND auth_user_md5.perms IN ('autor', 'tutor', 'dozent') " .
                    " AND auth_user_md5.visible <> 'never' " .
                    "ORDER BY Vorname, Nachname", _("Teilnehmende/n suchen"), "username");

                // quickfilter: tutors of institut
                $sql = "SELECT user_id FROM user_inst WHERE Institut_id = ? AND inst_perms = 'autor'";
                $db = DBManager::get();
                $statement = $db->prepare($sql, [PDO::FETCH_NUM]);
                $statement->execute([Seminar::getInstance($this->course_id)->getInstitutId()]);
                $membersOfInstitute = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                // add "add autor" to infobox
                $mp = MultiPersonSearch::get("add_autor" . $this->course_id)
                    ->setLinkText(sprintf(_('%s eintragen'), get_title_for_status('autor', 1)))
                    ->setDefaultSelectedUser($filtered_members['autor']->pluck('user_id'))
                    ->setLinkIconPath("")
                    ->setTitle(sprintf(_('%s eintragen'), get_title_for_status('autor', 1)))
                    ->setExecuteURL(URLHelper::getLink('dispatch.php/course/members/execute_multipersonsearch_autor'))
                    ->setSearchObject($searchType)
                    ->addQuickfilter(sprintf(_('%s der Einrichtung'), $this->status_groups['autor']), $membersOfInstitute)
                    ->setNavigationItem('/course/members/view')
                    ->render();
                $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
                $widget->addElement($element);

                // add "add person to waitlist" to sidebar
                if ($sem->isAdmissionEnabled() && $sem->getCourseSet()->hasAlgorithmRun()
                    && !$sem->admission_disable_waitlist &&
                    (!$sem->getFreeSeats() || $sem->admission_disable_waitlist_move)) {
                    $ignore = array_merge(
                        $filtered_members['dozent']->pluck('user_id'),
                        $filtered_members['tutor']->pluck('user_id'),
                        $filtered_members['autor']->pluck('user_id'),
                        $filtered_members['user']->pluck('user_id'),
                        $filtered_members['awaiting']->pluck('user_id')
                    );
                    $mp = MultiPersonSearch::get('add_waitlist' . $this->course_id)
                        ->setLinkText(_('Person(en) auf Warteliste eintragen'))
                        ->setDefaultSelectedUser($ignore)
                        ->setLinkIconPath('')
                        ->setTitle(_('Person(en) auf Warteliste eintragen'))
                        ->setExecuteURL(URLHelper::getLink('dispatch.php/course/members/execute_multipersonsearch_waitlist'))
                        ->setSearchObject($searchType)
                        ->addQuickfilter(_('Mitglieder der Einrichtung'), $membersOfInstitute)
                        ->setNavigationItem('/course/members/view')
                        ->render();
                    $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
                    $widget->addElement($element);
                }
                $widget->addLink(_('Teilnehmendenliste importieren'),
                    $this->url_for('course/members/import_autorlist'), Icon::create('community+add', 'clickable'));
            }

            if (Config::get()->EXPORT_ENABLE) {
                $widget = new ExportWidget();

                // create csv-export link
                $csvExport = export_link(
                    $this->course_id,
                    'person',
                    sprintf('%s %s', $this->status_groups['autor'], $this->course_title),
                    'csv',
                    'csv-teiln',
                    '',
                    _('Liste als csv-Dokument exportieren'),
                    'passthrough'
                );
                $widget->addLinkFromHTML(
                    $csvExport,
                    Icon::create('file-office', 'clickable')
                );

                // create csv-export link
                $rtfExport = export_link(
                    $this->course_id,
                    'person',
                    sprintf('%s %s', $this->status_groups['autor'], $this->course_title),
                    'rtf',
                    'rtf-teiln',
                    '',
                    _('Liste als rtf-Dokument exportieren'),
                    'passthrough'
                );
                $widget->addLinkFromHTML(
                    $rtfExport,
                    Icon::create('file-text', 'clickable')
                );

                if (count($this->awaiting) > 0) {
                    $awaiting_rtf = export_link(
                        $this->course_id,
                        'person',
                        sprintf(_('Warteliste %s'), $this->course_title),
                        'rtf',
                        'rtf-warteliste',
                        $this->waiting_type,
                        _('Warteliste als rtf-Dokument exportieren'),
                        'passthrough'
                    );
                    $widget->addLinkFromHTML(
                        $awaiting_rtf,
                        Icon::create('file-office+export', 'clickable')
                    );

                    $awaiting_csv = export_link(
                        $this->course_id,
                        'person',
                        sprintf(_('Warteliste %s'), $this->course_title),
                        'csv',
                        'csv-warteliste',
                        $this->waiting_type,
                        _('Warteliste als csv-Dokument exportieren'),
                        'passthrough'
                    );
                    $widget->addLinkFromHTML(
                        $awaiting_csv,
                        Icon::create('file-text+export', 'clickable')
                    );
                }

                $sidebar->addWidget($widget);
            }

            if ($this->is_dozent) {
                $options = new OptionsWidget();
                $options->addCheckbox(
                    _('Rundmails von Studierenden erlauben'),
                    $config->COURSE_STUDENT_MAILING,
                    $this->url_for('course/members/toggle_student_mailing/1'),
                    $this->url_for('course/members/toggle_student_mailing/0'),
                    ['title' => _('Über diese Option können Sie Studierenden das Schreiben von Nachrichten an alle anderen Teilnehmenden der Veranstaltung erlauben')]
                );
                $sidebar->addWidget($options);
            }
        } else if ($this->is_autor || $this->is_user) {
            // Visibility preferences
            if (!$this->my_visibility['iam_visible']) {
                $text = _('Sie sind für andere Teilnehmenden auf der Teilnehmendenliste nicht sichtbar.');
                $icon = Icon::create('visibility-visible', 'clickable');
                $modus = 'make_visible';
                $link_text = _('Klicken Sie hier, um sichtbar zu werden.');
            } else {
                $text = _('Sie sind für andere Teilnehmenden auf der Teilnehmendenliste sichtbar.');
                $icon = Icon::create('visibility-invisible', 'clickable');
                $modus = 'make_invisible';
                $link_text = _('Klicken Sie hier, um unsichtbar zu werden.');
            }


            $actions = new ActionsWidget();
            $actions->addLink(
                $link_text,
                $this->url_for('course/members/change_visibility', $modus, $this->my_visibility['visible_mode']),
                $icon,
                ['title' => $text]
            );
            $sidebar->addWidget($actions);
        }
    }

    public function export_members_csv_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        $filtered_members = $this->members->getMembers($this->sort_status, $this->sort_by . ' ' . $this->order);
        $filtered_members = array_merge($filtered_members, $this->members->getAdmissionMembers($this->sort_status, $this->sort_by . ' ' . $this->order ));
        $dozenten = $filtered_members['dozent']->toArray('user_id username vorname nachname visible mkdate');
        $tutoren = $filtered_members['tutor']->toArray('user_id username vorname nachname visible mkdate');
        $autoren = $filtered_members['autor']->toArray('user_id username vorname nachname visible mkdate');


        $header = [_('Titel'), _('Vorname'), _('Nachname'), _('Titel2'), _('Nutzernamen'), _('Privatadr'), _('Privatnr'), _('E-Mail'), _('Anmeldedatum'), _('Studiengänge')];
        $data = [$header];
        foreach ([$dozenten, $tutoren, $autoren] as $usergroup) {
            foreach ($usergroup as $dozent) {
                $line = [
                    '',
                    $dozent['Vorname'],
                    $dozent['Nachname'],
                    '',
                    $dozent['username']
                ];
                $data[] = $line;
            }
        }
        $csv = array_to_csv($data);
    }

    public function toggle_student_mailing_action($state)
    {
        if (!$this->is_dozent) {
            throw new AccessDeniedException();
        }

        $config = CourseConfig::get($this->course_id);
        $config->store('COURSE_STUDENT_MAILING', $state);

        $this->redirect('course/members');
    }
}
