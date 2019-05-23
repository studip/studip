<?php
/**
 * BasicDataWizardStep.php
 * Course wizard step for getting the basic course data.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class BasicDataWizardStep implements CourseWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which number has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/app/views/course/wizard/steps');
        if ($values[__CLASS__]['studygroup']) {
            $tpl = $factory->open('basicdata/index_studygroup');
            $values[__CLASS__]['lecturers'][$GLOBALS['user']->id] = 1;
        } else {
            $tpl = $factory->open('basicdata/index');
        }
        if ($this->setupTemplateAttributes($tpl, $values, $stepnumber, $temp_id)) {
            return $tpl->render();
        }
    }

    protected function setupTemplateAttributes($tpl, $values, $stepnumber, $temp_id)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        // Get all available course types and their categories.
        $typestruct = [];
        foreach (SemType::getTypes() as $type) {
            $class = $type->getClass();
            // Creates a studygroup.
            if ($values['studygroup']) {
                // Get all studygroup types.
                if ($class['studygroup_mode']) {
                    $typestruct[$class['name']][] = $type;
                }
                // Pre-set institute for studygroup assignment.
                $values['institute'] = Config::get()->STUDYGROUP_DEFAULT_INST;
            // Normal course.
            } else {
                if (!$class['course_creation_forbidden'] && !$class['studygroup_mode']) {
                    $typestruct[$class['name']][] = $type;
                }
            }
        }
        $tpl->set_attribute('types', $typestruct);
        // Select a default type if none is given.
        if (!$values['coursetype']) {
            if ($GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER && Request::isXhr()) {
                $values['coursetype'] = $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER;
            } else {
                $values['coursetype'] = 1;
            }
        }

        // Semester selection.
        $semesters = [];
        $now = mktime();
        // Allow only current or future semesters for selection.
        foreach (Semester::getAll() as $s) {
            if ($s->ende >= $now) {
                if ($GLOBALS['perm']->have_perm("admin")) {
                    if ($s->id == $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE &&
                        !$values['start_time'] && Request::isXhr()) {
                        $values['start_time'] = $s->beginn;
                    }
                } else {
                    if ((time() >= $s->beginn - Config::get()->SEMESTER_TIME_SWITCH * 86400 * 7)
                        && (time() < $s->ende - Config::get()->SEMESTER_TIME_SWITCH * 86400 * 7)) {
                        $values['start_time'] = $s->beginn;
                    }
                }
                $semesters[] = $s;
            }
        }
        if ($values['studygroup'] && (!count($typestruct) || !$values['institute']) ) {
            $message = sprintf(_('Die Konfiguration der Studiengruppen ist unvollständig. ' .
                'Bitte wenden Sie sich an [die Stud.IP-Administration]%s .'),
                URLHelper::getLink('dispatch.php/siteinfo/show')
            );
            PageLayout::postError(formatReady($message));
            return false;
        }
        if (count($semesters) > 0) {
            $tpl->set_attribute('semesters', array_reverse($semesters));
            // If no semester is set, use current as selected default.
            if (!$values['start_time']) {
                $values['start_time'] = Semester::findCurrent()->beginn;
            }
        } else {
            $message = sprintf(_('Veranstaltungen können nur ' .
                'im aktuellen oder in zukünftigen Semestern angelegt werden. ' .
                'Leider wurde kein passendes Semester gefunden. Bitte wenden ' .
                'Sie sich an [die Stud.IP-Administration]%s .'),
                URLHelper::getLink('dispatch.php/siteinfo/show')
            );
            PageLayout::postError(formatReady($message));
            return false;
        }

        // Get all allowed home institutes (my own).
        $institutes = Institute::getMyInstitutes();
        if ($values['studygroup'] || count($institutes) > 0) {
            $tpl->set_attribute('institutes', $institutes);
            if (!$values['institute']) {
                if ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT && Request::isXhr()) {
                    $values['institute'] = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;
                } else {
                    $values['institute'] = InstituteMember::getDefaultInstituteIdForUser($GLOBALS['user']->id);

                    // if for some reason no default institute is set, use the first one listed
                    if (!$values['institute']) {
                        $values['institute'] = $institutes[0]['Institut_id'];
                    }
                }
            }
        } else {
            $message = sprintf(_('Um Veranstaltungen ' .
                'anlegen zu können, muss Ihr Account der Einrichtung, ' .
                'für die Sie eine Veranstaltung anlegen möchten, zugeordnet ' .
                'werden. Bitte wenden Sie sich an [die ' .
                'Stud.IP-Administration]%s .'),
                URLHelper::getLink('dispatch.php/siteinfo/show')
            );
            PageLayout::postError(formatReady($message));
            return false;
        }

        // QuickSearch for participating institutes.
        // No JS: Keep search value and results for displaying in search select box.
        if ($values['part_inst_id']) {
            Request::getInstance()->offsetSet('part_inst_id', $values['part_inst_id']);
        }
        if ($values['part_inst_id_parameter']) {
            Request::getInstance()->offsetSet('part_inst_id_parameter', $values['part_inst_id_parameter']);
        }
        $instsearch = new StandardSearch('Institut_id',
            _('Beteiligte Einrichtung hinzufügen'),
            'part_inst_id'
        );
        $tpl->set_attribute('instsearch', QuickSearch::get('part_inst_id', $instsearch)
            ->withButton(['search_button_name' => 'search_part_inst', 'reset_button_name' => 'reset_instsearch'])
            ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addParticipatingInst')
            ->render());
        if (!$values['participating']) {
            $values['participating'] = [];
        }

        // Quicksearch for lecturers.
        // No JS: Keep search value and results for displaying in search select box.
        if ($values['lecturer_id']) {
            Request::getInstance()->offsetSet('lecturer_id', $values['lecturer_id']);
        }
        if ($values['lecturer_id_parameter']) {
            Request::getInstance()->offsetSet('lecturer_id_parameter', $values['lecturer_id_parameter']);
        }

        // Check for deputies.
        $deputies = Config::get()->DEPUTIES_ENABLE;
        /*
         * No lecturers set, add yourself so that at least one lecturer is
         * present. But this can only be done if your own permission level
         * is 'dozent'.
         */
        if (!$values['lecturers'] && $GLOBALS['perm']->have_perm('dozent') && !$GLOBALS['perm']->have_perm('admin')) {
            $values['lecturers'][$GLOBALS['user']->id] = true;
            // Remove from deputies if set.
            if ($deputies && $values['deputies'][$GLOBALS['user']->id]) {
                unset($values['deputies'][$GLOBALS['user']->id]);
            }
            // Add your own default deputies if applicable.
            if ($deputies && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                $values['deputies'] = array_merge($values['deputies'] ?: [],
                    array_flip(array_keys(getDeputies($GLOBALS['user']->id))));
            }
        }
        // Add lecturer from my courses filter.
        if ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER && !$values['lecturers'] && Request::isXhr()) {
            $values['lecturers'][$GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER] = true;
            // Add this lecturer's default deputies if applicable.
            if ($deputies && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                $values['deputies'] = array_merge($values['deputies'] ?: [],
                    array_flip(array_keys(getDeputies($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER))));
            }
        }
        if (!$values['lecturers']) {
            $values['lecturers'] = [];
        }
        if ($deputies && !$values['deputies']) {
            $values['deputies'] = [];
        }



        // Quicksearch for deputies if applicable.
        if ($deputies) {
            // No JS: Keep search value and results for displaying in search select box.
            if ($values['deputy_id']) {
                Request::getInstance()->offsetSet('deputy_id', $values['deputy_id']);
            }
            if ($values['deputy_id_parameter']) {
                Request::getInstance()->offsetSet('deputy_id_parameter', $values['deputy_id_parameter']);
            }
            $deputysearch = new PermissionSearch('user',
                _('Vertretung hinzufügen'),
                'user_id',
                ['permission' => 'dozent',
                    'exclude_user' => array_keys($values['deputies'])]
            );
            $tpl->set_attribute('dsearch', QuickSearch::get('deputy_id', $deputysearch)
                ->withButton(['search_button_name' => 'search_deputy', 'reset_button_name' => 'reset_dsearch'])
                ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addDeputy')
                ->render());
        }

        if (!$values['tutors']) {
            $values['tutors'] = [];
        }

        list($lsearch, $tsearch)  = array_values($this->getSearch($values['coursetype'],
            array_merge([$values['institute']], array_keys($values['participating'])),
            array_keys($values['lecturers']), array_keys($values['tutors'])));
        // Quicksearch for lecturers.
        $tpl->set_attribute('lsearch', $lsearch);
        $tpl->set_attribute('tsearch', $tsearch);
        $tpl->set_attribute('values', $values);
        // AJAX URL needed for default deputy checking.
        $tpl->set_attribute('ajax_url', $values['ajax_url'] ?: URLHelper::getLink('dispatch.php/course/wizard/ajax'));
        $tpl->set_attribute('default_deputies_enabled',
            ($deputies && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) ? 1 : 0);

        return $tpl;
    }

    /**
     * The function only needs to handle person adding and removing
     * as other actions are handled by normal request processing.
     * @param Array $values currently set values for the wizard.
     * @return bool
     */
    public function alterValues($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        // Add a participating institute.
        if (Request::submitted('add_part_inst') && Request::option('part_inst_id')) {
            $values['participating'][Request::option('part_inst_id')] = true;
            unset($values['part_inst_id']);
            unset($values['part_inst_id_parameter']);
        }
        // Remove a participating institute.
        if ($remove = array_keys(Request::getArray('remove_participating'))) {
            $remove = $remove[0];
            unset($values['participating'][$remove]);
        }
        // Add a lecturer.
        if (Request::submitted('add_lecturer') && Request::option('lecturer_id')) {
            $values['lecturers'][Request::option('lecturer_id')] = true;
            unset($values['lecturer_id']);
            unset($values['lecturer_id_parameter']);
            // Add default deputies if applicable.
            if (Config::get()->DEPUTIES_ENABLE && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                $values['deputies'] = array_merge($values['deputies'] ?: [],
                    array_flip(array_keys(Request::option('lecturer_id'))));
            }
        }
        // Remove a lecturer.
        if ($remove = array_keys(Request::getArray('remove_lecturer'))) {
            $remove = $remove[0];
            unset($values['lecturers'][$remove]);
        }
        // Add a deputy.
        if (Request::submitted('add_deputy')) {
            $values['deputies'][Request::option('deputy_id')] = true;
            unset($values['deputy_id']);
            unset($values['deputy_id_parameter']);
        }
        // Remove a deputy.
        if ($remove = array_keys(Request::getArray('remove_deputy'))) {
            $remove = $remove[0];
            unset($values['deputies'][$remove]);
        }
        // Add a tutor.
        if (Request::submitted('add_tutor') && Request::option('tutor_id')) {
            $values['tutors'][Request::option('tutor_id')] = true;
            unset($values['tutor_id']);
            unset($values['tutor_id_parameter']);
        }
        // Remove a tutor.
        if ($remove = array_keys(Request::getArray('remove_tutor'))) {
            $remove = $remove[0];
            unset($values['tutors'][$remove]);
        }
        return $values;
    }

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        $ok = true;
        $errors = [];
        if (!trim($values['name'])) {
            $errors[] = _('Bitte geben Sie den Namen der Veranstaltung an.');
        }
        if ($values['number'] != '') {
            $course_number_format = Config::get()->COURSE_NUMBER_FORMAT;
            if ($course_number_format && !preg_match('/^' . $course_number_format . '$/', $values['number'])) {
                $errors[] = _('Die Veranstaltungsnummer hat ein ungültiges Format.');
            }
        }
        if (!$values['lecturers']) {
            $errors[] = sprintf(_('Bitte tragen Sie mindestens eine Person als %s ein.'),
                get_title_for_status('dozent', 1, $values['coursetype']));
        }
        if (!$values['lecturers'][$GLOBALS['user']->id] && !$GLOBALS['perm']->have_perm('admin')) {
            if (Config::get()->DEPUTIES_ENABLE) {
                if (!$values['deputies'][$GLOBALS['user']->id]) {
                    $errors[] = sprintf(_('Sie selbst müssen entweder als %s oder als Vertretung eingetragen sein.'),
                        get_title_for_status('dozent', 1, $values['coursetype']));
                }
            } else {
                $errors[] = sprintf(_('Sie müssen selbst als %s eingetragen sein.'),
                    get_title_for_status('dozent', 1, $values['coursetype']));
            }
        }
        if (in_array($values['coursetype'], studygroup_sem_types())) {
            if (!$values['accept']) {
                $errors[] = _('Sie müssen die Nutzungsbedingungen akzeptieren.');
            }
        }
        if ($errors) {
            $ok = false;
            PageLayout::postMessage(MessageBox::error(
                _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors));
        }
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        // We only need our own stored values here.
        if (@$values['copy_basic_data'] === true) {
            $source = Course::find($values['source_id']);
        }
        $values = $values[__CLASS__];
        $seminar = new Seminar($course);

        if (isset($source)) {
            $course->setData($source->toArray('untertitel ort sonstiges art teilnehmer vorrausetzungen lernorga leistungsnachweis ects admission_turnout modules'));
            foreach ($source->datafields as $one) {
                $df = $one->getTypedDatafield();
                if ($df->isEditable()) {
                    $course->datafields->findOneBy('datafield_id', $one->datafield_id)->content = $one->content;
                }
            }
        }

        $course->status = $values['coursetype'];
        $course->start_time = $values['start_time'];
        $course->duration_time = 0;
        $course->name = $values['name'];
        $course->veranstaltungsnummer = $values['number'];
        $course->beschreibung = $values['description'];
        $course->institut_id = $values['institute'];

        $semclass = $seminar->getSemClass();
        $course->visible = $semclass['visible'];
        $course->admission_prelim = $semclass['admission_prelim_default'];
        $course->lesezugriff = $semclass['default_read_level'] ?: 1;
        $course->schreibzugriff = $semclass['default_write_level'] ?: 1;

        // Studygroups: access and description.
        if (in_array($values['coursetype'], studygroup_sem_types())) {
            $course->visible = 1;
            $course->duration_time = -1;
            switch ($values['access']) {
                case 'all':
                    $course->admission_prelim = 0;
                    break;
                case 'invisible':
                    if (!Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED) {
                        $course->visible = 0;
                    }
                case 'invite':
                    $course->admission_prelim = 1;
                    $course->admission_prelim_txt = Config::get()->STUDYGROUP_ACCEPTANCE_TEXT;
                    break;
            }
        }
        if ($course->store()) {
            StudipLog::log('SEM_CREATE', $course->id, null, 'Veranstaltung mit Assistent angelegt');
            $institutes = [$values['institute']];
            if (isset($values['participating']) && is_array($values['participating'])) {
                $institutes = array_merge($institutes, array_keys($values['participating']));
            }
            $seminar->setInstitutes($institutes);
            if (isset($values['lecturers']) && is_array($values['lecturers'])) {
                foreach (array_keys($values['lecturers']) as $user_id) {
                    $seminar->addMember($user_id, 'dozent');
                }
            }
            if (isset($values['tutors']) && is_array($values['tutors'])) {
                foreach (array_keys($values['tutors']) as $user_id) {
                    $seminar->addMember($user_id, 'tutor');
                }
            }
            if (Config::get()->DEPUTIES_ENABLE && isset($values['deputies']) && is_array($values['deputies'])) {
                foreach ($values['deputies'] as $d => $assigned) {
                    addDeputy($d, $course->id);
                }
            }
            if ($semclass['admission_type_default'] == 3) {
                $course_set_id = CourseSet::getGlobalLockedAdmissionSetId();
                CourseSet::addCourseToSet($course_set_id, $course->id);
            }
            return $course;
        } else {
            return false;
        }
    }

    /**
     * Checks if the current step needs to be executed according
     * to already given values. A good example are study areas which
     * are only needed for certain sem_classes.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        return true;
    }

    /**
     * Copy values for basic data wizard step from given course.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        $data = [
            'coursetype' => $course->status,
            'start_time' => $course->start_time,
            'name' => $course->name,
            'number' => $course->veranstaltungsnummer,
            'institute' => $course->institut_id,
            'description' => $course->beschreibung
        ];
        $lecturers = $course->members->findBy('status', 'dozent')->pluck('user_id');
        $data['lecturers'] = array_flip($lecturers);
        $tutors = $course->members->findBy('status', 'tutor')->pluck('user_id');
        $data['tutors'] = array_flip($tutors);
        $participating = $course->institutes->pluck('institut_id');
        $data['participating'] = array_flip($participating);
        unset($data['participating'][$course->institut_id]);
        if (Config::get()->DEPUTIES_ENABLE) {
            $deputies = getDeputies($course->id);
            $data['deputies'] = array_flip(array_keys($deputies));
        }
        $values[__CLASS__] = $data;
        return $values;
    }

    /**
     * Fetches the default deputies for a given person if the necessary
     * config options are set.
     * @param $user_id user whose default deputies to get
     * @return Array Default deputy user_ids.
     */
    public function getDefaultDeputies($user_id)
    {
        if (Config::get()->DEPUTIES_ENABLE && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
            $deputies = getDeputies($user_id, 'full_rev_username');
            $result = [];
            foreach ($deputies as $d) {
                $result[] = [
                    'id' => $d['user_id'],
                    'name' => $d['fullname']
                ];
            }
            return $result;
        } else {
            return [];
        }
    }

    public function getSearch($course_type, $institute_ids, $exclude_lecturers = [],$exclude_tutors = [])
    {
        if (SeminarCategories::getByTypeId($course_type)->only_inst_user) {
            $search = 'user_inst';
        } else {
            $search = 'user';
        }
        $psearch = new PermissionSearch($search,
            sprintf(_("%s hinzufügen"), get_title_for_status('dozent', 1, $course_type)),
            'user_id',
            __CLASS__ . '::lsearchHelper'
        );
        $lsearch = QuickSearch::get('lecturer_id', $psearch)
            ->withButton(['search_button_name' => 'search_lecturer', 'reset_button_name' => 'reset_lsearch'])
            ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addLecturer')
            ->render();

        $tutor_psearch = new PermissionSearch($search,
            sprintf(_("%s hinzufügen"), get_title_for_status('tutor', 1, $course_type)),
            'user_id',
            __CLASS__ . '::tsearchHelper'
        );
        $tsearch = QuickSearch::get('tutor_id', $tutor_psearch)
            ->withButton(['search_button_name' => 'search_tutor', 'reset_button_name' => 'reset_tsearch'])
            ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addTutor')
            ->render();

        return compact('lsearch', 'tsearch');
    }

    public static function tsearchHelper($psearch, $context)
    {
        $ret['permission'] = ['tutor', 'dozent'];
        $ret['exclude_user'] = array_keys((array)$context['tutors']);
        $ret['institute'] = array_merge([$context['institute']], array_keys((array)$context['participating']));
        return $ret;
    }

    public static function lsearchHelper($psearch, $context)
    {
        $ret['permission'] = 'dozent';
        $ret['exclude_user'] = array_keys((array)$context['lecturers']);
        $ret['institute'] = array_merge([$context['institute']], array_keys((array)$context['participating']));
        return $ret;
    }

}
