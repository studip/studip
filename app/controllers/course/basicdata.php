<?php
# Lifter010: TODO
/*
 * studygroup.php - contains Course_BasicdataController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.0
 */

class Course_BasicdataController extends AuthenticatedController
{
    public $msg = [];

    /**
     * Set up the list of input fields. Some fields may be locked for
     * some reasons (lock rules, insufficient permissions etc.). This
     * method does not return anything, it just sets up $this->attributes
     * and $this->descriptions.
     *
     * @param Seminar $sem
     */
    private function setupInputFields($sem)
    {
        $course_id = $sem->getId();
        $data = $sem->getData();

        $this->attributes = [];
        $this->attributes[] = [
            'title' => _("Name der Veranstaltung"),
            'name' => "course_name",
            'must' => true,
            'type' => 'text',
            'i18n' => true,
            'value' => $data['name'],
            'locked' => LockRules::Check($course_id, 'Name')
        ];
        $this->attributes[] = [
            'title' => _("Untertitel der Veranstaltung"),
            'name' => "course_subtitle",
            'type' => 'text',
            'i18n' => true,
            'value' => $data['subtitle'],
            'locked' => LockRules::Check($course_id, 'Untertitel')
        ];

        $this->attributes[] = [
            'title'     => _('Typ der Veranstaltung'),
            'name'      => 'course_status',
            'must'      => true,
            'type'      => 'select',
            'value'     => $data['status'],
            'locked'    => LockRules::Check($course_id, 'status'),
            'choices'   => $this->_getTypes($sem, $data, $changable = true),
            'changable' => $changable,
        ];

        $this->attributes[] = [
            'title' => _("Art der Veranstaltung"),
            'name' => "course_form",
            'type' => 'text',
            'i18n' => true,
            'value' => $data['form'],
            'locked' => LockRules::Check($course_id, 'art')
        ];
        $course_number_format_config = Config::get()->getMetadata('COURSE_NUMBER_FORMAT');
        $this->attributes[] = [
            'title' => _("Veranstaltungsnummer"),
            'name' => "course_seminar_number",
            'type' => 'text',
            'value' => $data['seminar_number'],
            'locked' => LockRules::Check($course_id, 'VeranstaltungsNummer'),
            'description' => $course_number_format_config['comment'],
            'pattern' => Config::get()->COURSE_NUMBER_FORMAT
        ];
        $this->attributes[] = [
            'title' => _("ECTS-Punkte"),
            'name' => "course_ects",
            'type' => 'text',
            'value' => $data['ects'],
            'locked' => LockRules::Check($course_id, 'ects')
        ];
        $this->attributes[] = [
            'title' => _("max. Teilnehmendenzahl"),
            'name' => "course_admission_turnout",
            'must' => false,
            'type' => 'number',
            'value' => $data['admission_turnout'],
            'locked' => LockRules::Check($course_id, 'admission_turnout'),
            'min' => '0'
        ];
        $this->attributes[] = [
            'title' => _("Beschreibung"),
            'name' => "course_description",
            'type' => 'textarea',
            'i18n' => true,
            'value' => $data['description'],
            'locked' => LockRules::Check($course_id, 'Beschreibung')
        ];

        $this->institutional = [];
        $my_institutes = Institute::getMyInstitutes();
        $institutes = Institute::getInstitutes();
        foreach ($institutes as $institute) {
            if ($institute['Institut_id'] === $data['institut_id']) {
                $found = false;
                foreach ($my_institutes as $inst) {
                    if ($inst['Institut_id'] === $institute['Institut_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
	                $my_institutes[] = $institute;
                }
                break;
            }
        }
        $this->institutional[] = [
            'title'   => _('Heimat-Einrichtung'),
            'name'    => 'course_institut_id',
            'must'    => true,
            'type'    => 'nested-select',
            'value'   => $data['institut_id'],
            'choices' => $this->instituteChoices($my_institutes),
            'locked'  => LockRules::Check($course_id, 'Institut_id')
        ];

        $sem_institutes = $sem->getInstitutes();
        $this->institutional[] = [
            'title'    => _('beteiligte Einrichtungen'),
            'name'     => 'related_institutes[]',
            'type'     => 'nested-select',
            'value'    => array_diff($sem_institutes, [$sem->institut_id]),
            'choices'  => $this->instituteChoices($institutes),
            'locked'   => LockRules::Check($course_id, 'seminar_inst'),
            'multiple' => true,
        ];

        $this->descriptions = [];
        $this->descriptions[] = [
            'title' => _("Teilnehmende"),
            'name' => "course_participants",
            'type' => 'textarea',
            'i18n' => true,
            'value' => $data['participants'],
            'locked' => LockRules::Check($course_id, 'teilnehmer')
        ];
        $this->descriptions[] = [
            'title' => _("Voraussetzungen"),
            'name' => "course_requirements",
            'type' => 'textarea',
            'i18n' => true,
            'value' => $data['vorrausetzungen'],
            'locked' => LockRules::Check($course_id, 'voraussetzungen')
        ];
        $this->descriptions[] = [
            'title' => _("Lernorganisation"),
            'name' => "course_orga",
            'type' => 'textarea',
            'i18n' => true,
            'value' => $data['orga'],
            'locked' => LockRules::Check($course_id, 'lernorga')
        ];
        $this->descriptions[] = [
            'title' => _("Leistungsnachweis"),
            'name' => "course_leistungsnachweis",
            'type' => 'textarea',
            'i18n' => true,
            'value' => $data['leistungsnachweis'],
            'locked' => LockRules::Check($course_id, 'leistungsnachweis')
        ];
        $this->descriptions[] = [
            'title' => _("Ort") .
                "<br><span style=\"font-size: 0.8em\"><b>" .
                _("Achtung:") .
                "&nbsp;</b>" .
                _("Diese Ortsangabe wird nur angezeigt, wenn keine " .
                  "Angaben aus Zeiten oder Sitzungsterminen gemacht werden können.") .
                "</span>",
            'i18n' => true,
            'name' => "course_location",
            'type' => 'textarea',
            'value' => $data['ort'],
            'locked' => LockRules::Check($course_id, 'Ort')
        ];

        $datenfelder = DataFieldEntry::getDataFieldEntries($course_id, 'sem', $data["status"]);
        if ($datenfelder) {
            foreach($datenfelder as $datenfeld) {
                if ($datenfeld->isVisible()) {
                    $locked = !$datenfeld->isEditable()
                              || LockRules::Check($course_id, $datenfeld->getID());
                    $desc = $locked ? _('Diese Felder werden zentral durch die zuständigen Administratoren erfasst.') : $datenfeld->getDescription();
                    $this->descriptions[] = [
                        'title' => $datenfeld->getName(),
                        'must' =>  $datenfeld->isRequired(),
                        'name' => "datafield_".$datenfeld->getID(),
                        'type' => "datafield",
                        'html_value' => $datenfeld->getHTML("datafields", [
                            'tooltip' => $desc
                        ]),
                        'display_value' => $datenfeld->getDisplayValue(),
                        'locked' => $locked,
                        'description' => $desc
                    ];
                }
            }
        }
        $this->descriptions[] = [
            'title' => _("Sonstiges"),
            'name' => "course_misc",
            'type' => 'textarea',
            'value' => $data['misc'],
            'locked' => LockRules::Check($course_id, 'Sonstiges')
        ];
    }

    /**
     * Helper function to populate the list of institute choices.
     *
     * @param array $institutes
     */
    private function instituteChoices($institutes)
    {
        $faculty_id = null;
        $result = [];

        foreach ($institutes as $inst) {
            if ($inst['is_fak']) {
                $result[$inst['Institut_id']] = [
                    'label'    => $inst['Name'],
                    'children' => [],
                ];
                $faculty_id = $inst['Institut_id'];
            } elseif (!isset($result[$inst['fakultaets_id'] ?: $faculty_id])) {
                $result[] = [
                    'label'    => false,
                    'children' => [$inst['Institut_id'] => $inst['Name']],
                ];
            } else {
                $result[$inst['fakultaets_id'] ?: $faculty_id]['children'][$inst['Institut_id']] = $inst['Name'];
            }
        }

        return $result;
    }

    /**
     * Zeigt die Grunddaten an. Man beachte, dass eventuell zuvor eine andere
     * Action wie Set ausgeführt wurde, von der hierher weitergeleitet worden ist.
     * Wichtige Daten dazu wurden dann über $this->flash übertragen.
     *
     * @param md5 $course_id
     */
    public function view_action($course_id = null)
    {
        global $user, $perm, $_fullname_sql;

        $deputies_enabled = get_config('DEPUTIES_ENABLE');

        //damit QuickSearch funktioniert:
        Request::set('new_doz_parameter', $this->flash['new_doz_parameter']);
        if ($deputies_enabled) {
            Request::set('new_dep_parameter', $this->flash['new_dep_parameter']);
        }
        Request::set('new_tut_parameter', $this->flash['new_tut_parameter']);

        $this->course_id = Request::option('cid', $course_id);

        Navigation::activateItem('/course/admin/details');

        //Berechtigungscheck:
        if (!$perm->have_studip_perm("tutor",$this->course_id)) {
            throw new AccessDeniedException(_("Sie haben keine Berechtigung diese " .
                    "Veranstaltung zu verändern."));
        }

        //Kopf initialisieren:
        PageLayout::setHelpKeyword("Basis.VeranstaltungenVerwaltenGrunddaten");
        PageLayout::setTitle(_("Verwaltung der Grunddaten"));
        if ($this->course_id) {
            PageLayout::setTitle(Course::find($this->course_id)->getFullname()." - ".PageLayout::getTitle());
        }

        //Daten sammeln:
        $sem = Seminar::getInstance($this->course_id);
        $data = $sem->getData();

        //Erster, zweiter und vierter Reiter des Akkordions: Grundeinstellungen
        $this->setupInputFields($sem);

        $sem_institutes = $sem->getInstitutes();
        $this->dozent_is_locked = LockRules::Check($this->course_id, 'dozent');
        $this->tutor_is_locked = LockRules::Check($this->course_id, 'tutor');

        //Dritter Reiter: Personal
        $this->dozenten = $sem->getMembers('dozent');
        $instUsers = new SimpleCollection(InstituteMember::findByInstituteAndStatus($sem->getInstitutId(), 'dozent'));
        $this->lecturersOfInstitute = $instUsers->pluck('user_id');

        if (SeminarCategories::getByTypeId($sem->status)->only_inst_user) {
            $search_template = "user_inst_not_already_in_sem";
        } else {
            $search_template = "user_not_already_in_sem";
        }

        $this->dozentUserSearch = new PermissionSearch(
                            $search_template,
                            sprintf(_("%s suchen"), get_title_for_status('dozent', 1, $sem->status)),
                            "user_id",
                            ['permission' => 'dozent',
                                  'seminar_id' => $this->course_id,
                                  'sem_perm' => 'dozent',
                                  'institute' => $sem_institutes
                                 ]
                            );
        $this->dozenten_title = get_title_for_status('dozent', 1, $sem->status);
        $this->deputies_enabled = $deputies_enabled;

        if ($this->deputies_enabled) {
            $this->deputies = getDeputies($this->course_id);
            $this->deputySearch = new PermissionSearch(
                    "user_not_already_in_sem_or_deputy",
                    sprintf(_("%s suchen"), get_title_for_status('deputy', 1, $sem->status)),
                    "user_id",
                    ['permission' => getValidDeputyPerms(), 'seminar_id' => $this->course_id]
                );

            $this->deputy_title = get_title_for_status('deputy', 1, $sem->status);
        }
        $this->tutoren = $sem->getMembers('tutor');

        $this->tutorUserSearch = new PermissionSearch(
                            $search_template,
                            sprintf(_("%s suchen"), get_title_for_status('tutor', 1, $sem->status)),
                            "user_id",
                            ['permission' => ['dozent','tutor'],
                                  'seminar_id' => $this->course_id,
                                  'sem_perm' => ['dozent','tutor'],
                                  'institute' => $sem_institutes
                                 ]
                            );
        $this->tutor_title = get_title_for_status('tutor', 1, $sem->status);
        $instUsers = new SimpleCollection(InstituteMember::findByInstituteAndStatus($sem->getInstitutId(), 'tutor'));
        $this->tutorsOfInstitute = $instUsers->pluck('user_id');
        unset($instUsers);

        $this->perm_dozent = $perm->have_studip_perm("dozent", $this->course_id);
        $this->mkstring = $data['mkdate'] ? date("d.m.Y, H:i", $data['mkdate']) : _("unbekannt");
        $this->chstring = $data['chdate'] ? date("d.m.Y, H:i", $data['chdate']) : _("unbekannt");
        $lockdata = LockRules::getObjectRule($this->course_id);
        if ($lockdata['description'] && LockRules::CheckLockRulePermission($this->course_id, $lockdata['permission'])){
            $this->flash['msg'] = array_merge((array)$this->flash['msg'], [["info", formatLinks($lockdata['description'])]]);
        }
        $this->flash->discard(); //schmeißt ab jetzt unnötige Variablen aus der Session.
        $sidebar = Sidebar::get();
        $sidebar->setImage("sidebar/admin-sidebar.png");

        $widget = new ActionsWidget();
        $widget->addLink(_('Bild ändern'),
                         $this->url_for('avatar/update/course', $course_id),
                         Icon::create('edit', 'clickable'));
        if ($this->deputies_enabled) {
            if (isDeputy($user->id, $this->course_id)) {
                $newstatus = 'dozent';
                $text = _('Lehrende werden');
            } else if (in_array($user->id, array_keys($this->dozenten)) && sizeof($this->dozenten) > 1) {
                $newstatus = 'deputy';
                $text = _('Vertretung werden');
            }
            $widget->addLink($text,
                             $this->url_for('course/basicdata/switchdeputy', $this->course_id, $newstatus),
                             Icon::create('persons', 'clickable'));
        }
        $sidebar->addWidget($widget);
        // Entry list for admin upwards.
        if ($perm->have_studip_perm('admin', $this->course_id)) {
            $list = new SelectWidget(_('Veranstaltungen'), '?#admin_top_links', 'cid');

            foreach (AdminCourseFilter::get()->getCoursesForAdminWidget() as $seminar) {
                $list->addElement(new SelectElement(
                    $seminar['Seminar_id'],
                    $seminar['Name'],
                    $seminar['Seminar_id'] === Context::getId(),
                    $seminar['VeranstaltungsNummer'] . ' ' . $seminar['Name']
                ));
            }
            $list->size = 8;
            $sidebar->addWidget($list);
        }
    }

    /**
     * Ändert alle Grunddaten der Veranstaltung (bis auf Personal) und leitet
     * danach weiter auf View.
     */
    public function set_action($course_id)
    {
        global $perm;

        CSRFProtection::verifySecurityToken();

        $course_number_format = Config::get()->COURSE_NUMBER_FORMAT;
        $sem = Seminar::getInstance($course_id);
        $this->msg = [];
        $old_settings = $sem->getSettings();
        //Seminar-Daten:
        if ($perm->have_studip_perm("tutor", $sem->getId())) {
            $this->setupInputFields($sem);
            $changemade = false;
            $invalid_datafields = [];
            $all_fields_types = DataFieldEntry::getDataFieldEntries($sem->id, 'sem', $sem->status);
            $datafield_values = Request::getArray('datafields');

            foreach (array_merge($this->attributes, $this->institutional, $this->descriptions) as $field) {
                if (!$field['locked']) {
                    if ($field['type'] == 'datafield') {
                        $datafield_id = mb_substr($field['name'], 10);
                        $datafield = $all_fields_types[$datafield_id];
                        $datafield->setValueFromSubmit($datafield_values[$datafield_id]);
                        if ($datafield->isValid()) {
                            if ($datafield->store()) {
                                $changemade = true;
                            }
                        } else {
                            $invalid_datafields[] = $datafield->getName();
                        }
                    } else if ($field['name'] == 'related_institutes[]') {
                        // only related_institutes supported for now
                        if ($sem->setInstitutes(Request::optionArray('related_institutes'))) {
                            $changemade = true;
                        }
                    } else {
                        // format of input element name is "course_xxx"
                        $varname = mb_substr($field['name'], 7);
                        if ($field['i18n']) {
                            $req_value = Request::i18n($field['name']);
                        } else {
                            $req_value = Request::get($field['name']);
                        }

                        if ($varname === "name" && !$req_value) {
                            $this->msg[] = ["error", _("Name der Veranstaltung darf nicht leer sein.")];
                        } elseif ($varname === "seminar_number" && $req_value && $course_number_format &&
                                  !preg_match('/^' . $course_number_format . '$/', $req_value)) {
                            $this->msg[] = ['error', _('Die Veranstaltungsnummer hat ein ungültiges Format.')];
                        } else if ($field['type'] == 'select' && !in_array($req_value, array_flatten(array_values(array_map('array_keys', $field['choices']))))) {
                            // illegal value - just ignore this
                        } else if ($sem->{$varname} != $req_value) {
                            $sem->{$varname} = $req_value;
                            $changemade = true;
                        }
                    }
                }
            }
            //Datenfelder:
            if (count($invalid_datafields)) {
                $message = ngettext(
                    'Das folgende Datenfeld der Veranstaltung wurde falsch angegeben, bitte korrigieren Sie dies unter "Beschreibungen": %s',
                    'Die folgenden Datenfelder der Veranstaltung wurden falsch angegeben, bitte korrigieren Sie dies unter "Beschreibungen": %s',
                    count($invalid_datafields)
                );
                $message = sprintf($message, join(', ', array_map('htmlReady', $invalid_datafields)));
                $this->msg[] = ['error',  $message];
            }

            $sem->store();

            // Logging
            $before = array_diff_assoc($old_settings, $sem->getSettings());
            $after  = array_diff_assoc($sem->getSettings(), $old_settings);

            //update admission, if turnout was raised
            if($after['admission_turnout'] > $before['admission_turnout'] && $sem->isAdmissionEnabled()) {
                update_admission($sem->getId());
            }

            if (sizeof($before) && sizeof($after)) {
                foreach($before as $k => $v) $log_message .= "$k: $v => " . $after[$k] . " \n";
                StudipLog::log('CHANGE_BASIC_DATA', $sem->getId(), " ", $log_message);
                NotificationCenter::postNotification('SeminarBasicDataDidUpdate', $sem->id , $GLOBALS['user']->id);
            }
            // end of logging

            if ($changemade) {
                $this->msg[] = ["msg", _("Die Grunddaten der Veranstaltung wurden verändert.")];
            }

        } else {
            $this->msg[] = ["error", _("Sie haben keine Berechtigung diese Veranstaltung zu verändern.")];
        }

        //Labels/Funktionen für Dozenten und Tutoren
        if ($perm->have_studip_perm("dozent", $sem->getId())) {
            foreach (Request::getArray("label") as $user_id => $label) {
                $sem->setLabel($user_id, $label);
            }
        }

        foreach($sem->getStackedMessages() as $key => $messages) {
            foreach($messages['details'] as $message) {
                $this->msg[] = [($key !== "success" ? $key : "msg"), $message];
            }
        }
        $this->flash['msg'] = $this->msg;
        $this->flash['open'] = Request::get("open");
        $this->redirect($this->url_for('course/basicdata/view/' . $sem->getId()));
    }

    public function add_member_action($course_id, $status = 'dozent')
    {
        CSRFProtection::verifySecurityToken();

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_member_{$status}{$course_id}");

        switch($status) {
            case 'tutor' :
                $func = 'addTutor';
                break;
            case 'deputy':
                $func = 'addDeputy';
                break;
            default:
                $func = 'addTeacher';
                break;
        }

        $succeeded = [];
        $failed = [];
        foreach ($mp->getAddedUsers() as $a) {
            $result = $this->$func($a, $course_id);
            if ($result !== false) {
                $succeeded[] = User::find($a)->getFullname('no_title_rev');
            } else {
                $failed[] = User::find($a)->getFullname('no_title_rev');
            }
        }
        // Only show the success messagebox once
        if ($succeeded) {
            $sem = Seminar::GetInstance($course_id);
            $status_title = get_title_for_status($status, count($succeeded), $sem->status);
            if (count($succeeded) > 1) {
                $messagetext = sprintf(
                    _("%u %s wurden hinzugefügt."),
                    count($succeeded),
                    $status_title
                );
            } else {
                $messagetext = sprintf(
                    _('%s wurde hinzugefügt.'),
                    $status_title
                );
            }
            PageLayout::postSuccess(
                htmlReady($messagetext),
                array_map('htmlReady', $succeeded),
                true
            );
        }

        // only show an error messagebox once with list of errors!
        if ($failed) {
            PageLayout::postError(
                _('Bei den folgenden Nutzer/-innen ist ein Fehler aufgetreten') ,
                array_map('htmlReady', $failed)
            );
        }
        $this->flash['open'] = 'bd_personal';

        $redirect = Request::get('from', "course/basicdata/view/{$course_id}");
        $this->redirect($this->url_for($redirect));
    }

    private function addTutor($tutor, $course_id)
    {
        //Tutoren hinzufügen:
        if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            $sem = Seminar::GetInstance($course_id);
            if ($sem->addMember($tutor, 'tutor')) {
                // Check if we need to add user to course parent as well.
                if ($sem->parent_course) {
                    $this->addTutor($tutor, $sem->parent_course);
                }

                return true;
            }
        }
        return false;
    }

    private function addDeputy($deputy, $course_id)
    {
        //Vertretung hinzufügen:
        if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            $sem = Seminar::GetInstance($course_id);
            if (addDeputy($deputy, $sem->getId())) {
                return true;
            }
        }
        return false;
    }

    private function addTeacher($dozent, $course_id)
    {
        $deputies_enabled = Config::get()->DEPUTIES_ENABLE;
        $sem = Seminar::GetInstance($course_id);
        if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            if ($sem->addMember($dozent, 'dozent')) {
                // Check if we need to add user to course parent as well.
                if ($sem->parent_course) {
                    $this->addTeacher($dozent, $sem->parent_course);
                }

                // Only applicable when globally enabled and user deputies enabled too
                if ($deputies_enabled) {
                    // Check whether chosen person is set as deputy
                    // -> delete deputy entry.
                    if (isDeputy($dozent, $course_id)) {
                        deleteDeputy($dozent, $course_id);
                    }
                    // Add default deputies of the chosen lecturer...
                    if (Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                        $deputies  = getDeputies($dozent);
                        $lecturers = $sem->getMembers('dozent');
                        foreach ($deputies as $deputy) {
                            // ..but only if not already set as lecturer or deputy.
                            if (!isset($lecturers[$deputy['user_id']]) &&
                                !isDeputy($deputy['user_id'], $course_id)
                            ) {
                                addDeputy($deputy['user_id'], $course_id);
                            }
                        }
                    }
                }

                return true;
            }
        }
        return false;
    }

    /**
     * Löscht einen Dozenten (bis auf den letzten Dozenten)
     * Leitet danach weiter auf View und öffnet den Reiter Personal.
     *
     * @param string $course_id
     * @param string $teacher_id
     */
    public function deletedozent_action($course_id, $teacher_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!$GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            PageLayout::postError(_('Sie haben keine Berechtigung diese Veranstaltung zu verändern.'));
        } elseif ($dozent === $GLOBALS['user']->id) {
            PageLayout::postError(_('Sie dürfen sich nicht selbst aus der Veranstaltung austragen.'));
        } else {
            $sem = Seminar::getInstance($course_id);
            $sem->deleteMember($teacher_id);

            // Remove user from subcourses as well.
            foreach ($sem->children as $child) {
                $child->deleteMember($teacher_id);
            }

            $this->msg = [];
            foreach ($sem->getStackedMessages() as $key => $messages) {
                foreach ($messages['details'] as $message) {
                    $this->msg[] = [
                        $key !== 'success' ? $key : 'msg',
                        $message
                    ];
                }
            }
            $this->flash['msg'] = $this->msg;
        }

        $this->flash['open'] = 'bd_personal';
        $this->redirect("course/basicdata/view/{$course_id}");
    }

    /**
     * Löscht einen Stellvertreter.
     * Leitet danach weiter auf View und öffnet den Reiter Personal.
     *
     * @param string $course_id
     * @param string $deputy_id
     */
    public function deletedeputy_action($course_id, $deputy_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!$GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            PageLayout::postError(_('Sie haben keine Berechtigung diese Veranstaltung zu verändern.'));
        } elseif ($deputy_id === $GLOBALS['user']->id) {
            PageLayout::postError(_('Sie dürfen sich nicht selbst aus der Veranstaltung austragen.'));
        } else {
            $sem = Seminar::getInstance($course_id);

            if (deleteDeputy($deputy_id, $course_id)) {
                // Remove user from subcourses as well.
                foreach ($sem->children as $child) {
                    deleteDeputy($deputy_id, $child->id);
                }

                PageLayout::postSuccess(sprintf(
                    _('%s wurde entfernt.'),
                    htmlReady(get_title_for_status('deputy', 1, $sem->status))
                ));
            } else {
                PageLayout::postError(sprintf(
                    _('%s konnte nicht entfernt werden.'),
                    htmlReady(get_title_for_status('deputy', 1, $sem->status))
                ));
            }
        }

        $this->flash['open'] = 'bd_personal';
        $this->redirect("course/basicdata/view/{$course_id}");
    }

    /**
     * Löscht einen Tutor
     * Leitet danach weiter auf View und öffnet den Reiter Personal.
     *
     * @param string $course_id
     * @param string $tutor_id
     */
    public function deletetutor_action($course_id, $tutor_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!$GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            PageLayout::postError( _('Sie haben keine Berechtigung diese Veranstaltung zu verändern.'));
        } else {
            $sem = Seminar::getInstance($course_id);

            $sem->deleteMember($tutor_id);
            // Remove user from subcourses as well.
            foreach ($sem->children as $child) {
                $child->deleteMember($tutor_id);
            }

            $this->msg = [];
            foreach ($sem->getStackedMessages() as $key => $messages) {
                foreach ($messages['details'] as $message) {
                    $this->msg[] = [
                        $key !== 'success' ? $key : 'msg',
                        $message
                    ];
                }
            }
            $this->flash['msg'] = $this->msg;
        }

        $this->flash['open'] = 'bd_personal';
        $this->redirect("course/basicdata/view/{$course_id}");
    }

    /**
     * Falls eine Person in der >>Reihenfolge<< hochgestuft werden soll.
     * Leitet danach weiter auf View und öffnet den Reiter Personal.
     *
     * @param md5 $user_id
     * @param string $status
     */
    public function priorityupfor_action($course_id, $user_id, $status = "dozent")
    {
        global $user, $perm;

        CSRFProtection::verifySecurityToken();

        $sem = Seminar::getInstance($course_id);
        $this->msg = [];
        if ($perm->have_studip_perm("dozent", $sem->getId())) {
            $teilnehmer = $sem->getMembers($status);
            $members = [];
            foreach($teilnehmer as $key => $member) {
                $members[] = $member["user_id"];
            }
            foreach($members as $key => $member) {
                if ($key > 0 && $member == $user_id) {
                    $temp_member = $members[$key-1];
                    $members[$key-1] = $member;
                    $members[$key] = $temp_member;
                }
            }
            $sem->setMemberPriority($members, $status);
        } else {
            $this->msg[] = ["error", _("Sie haben keine Berechtigung diese Veranstaltung zu verändern.")];
        }
        $this->flash['msg'] = $this->msg;
        $this->flash['open'] = "bd_personal";
        $this->redirect($this->url_for('course/basicdata/view/' . $sem->getId()));
    }

    /**
     * Falls eine Person in der >>Reihenfolge<< runtergestuft werden soll.
     * Leitet danach weiter auf View und öffnet den Reiter Personal.
     *
     * @param md5 $user_id
     * @param string $status
     */
    public function prioritydownfor_action($course_id, $user_id, $status = "dozent")
    {
        global $user, $perm;

        CSRFProtection::verifySecurityToken();

        $sem = Seminar::getInstance($course_id);
        $this->msg = [];
        if ($perm->have_studip_perm("dozent", $sem->getId())) {
            $teilnehmer = $sem->getMembers($status);
            $members = [];
            foreach($teilnehmer as $key => $member) {
                $members[] = $member["user_id"];
            }
            foreach($members as $key => $member) {
                if ($key < count($members)-1 && $member == $user_id) {
                    $temp_member = $members[$key+1];
                    $members[$key+1] = $member;
                    $members[$key] = $temp_member;
                }
            }
            $sem->setMemberPriority($members, $status);
        } else {
            $this->msg[] = ["error", _("Sie haben keine Berechtigung diese Veranstaltung zu verändern.")];
        }
        $this->flash['msg'] = $this->msg;
        $this->flash['open'] = "bd_personal";
        $this->redirect($this->url_for('course/basicdata/view/' . $sem->getId()));
    }

    public function switchdeputy_action($course_id, $newstatus) {
        CSRFProtection::verifySecurityToken();

        $course = Seminar::getInstance($course_id);
        switch($newstatus) {
            case 'dozent':
                $dozent = new CourseMember();
                $dozent->seminar_id = $course_id;
                $dozent->user_id = $GLOBALS['user']->id;
                $dozent->status = 'dozent';
                $dozent->comment = '';
                if ($dozent->store()) {
                    deleteDeputy($GLOBALS['user']->id, $course_id);
                    PageLayout::postSuccess(sprintf(_('Sie wurden als %s eingetragen.'),
                        get_title_for_status('dozent', 1)));
                } else {
                    PageLayout::postError(sprintf(_('Sie konnten nicht als %s eingetragen werden.'),
                        get_title_for_status('dozent', 1)));
                }
                break;
            case 'deputy':
                $dozent = Course::find($course_id)->members->findOneBy('user_id', $GLOBALS['user']->id);
                if (addDeputy($GLOBALS['user']->id, $course_id)) {
                    $dozent->delete();
                    PageLayout::postSuccess(_('Sie wurden als Vertretung eingetragen.'));
                } else {
                    PageLayout::postError(_('Sie konnten nicht als Vertretung eingetragen werden.'));
                }
                break;
        }
        $this->flash['open'] = "bd_personal";
        $this->redirect($this->url_for('course/basicdata/view/'.$course_id));
    }

    private function _getTypes($sem, $data, &$changable = true)
    {
        $sem_types = [];
        if ($GLOBALS['perm']->have_perm("admin")) {
            foreach (SemClass::getClasses() as $sc) {
                if (!$sc['course_creation_forbidden']) {
                    $sem_types[$sc['name']] = array_map(function ($st) {
                        return $st['name'];
                    }, $sc->getSemTypes());
                }
            }
        } else {
            $sc = $sem->getSemClass();
            $sem_types[$sc['name']] = array_map(function ($st) {
                return $st['name'];
            }, $sc->getSemTypes());
        }
        if (!in_array($data['status'], array_flatten(array_values(array_map('array_keys', $sem_types))))) {
            $class_name = $sem->getSemClass()->offsetGet('name');
            if (!isset($sem_types[$class_name])) {
                $sem_types[$class_name] = [];
            }
            $sem_types[$class_name][] = $sem->getSemType()->offsetGet('name');

            $changable = false;
        }
        return $sem_types;
    }
}
