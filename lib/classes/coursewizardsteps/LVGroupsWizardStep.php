<?php
/**
 * LVGroupsWizardStep.php
 * Course wizard step for assigning LV Groups.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once dirname(__FILE__) . '/../StudipLvgruppeSelection.class.php';

class LVGroupsWizardStep implements CourseWizardStep
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
        // retrieve class of step 1 from step registry
        $step_one_class = CourseWizardStepRegistry::findOneBySQL('number = 1 AND enabled = 1')
                ->classname;

        // store start time of semester selected in first step
        $course_start_time = $values[$step_one_class]['start_time'];

        // We only need our own stored values here.
        $values = $values[__CLASS__];

        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/app/views/course/wizard/steps');
        $tpl = $factory->open('lvgroups/index');
        $tpl->set_attribute('values', $values);

        $lvgtree = new StudipLvgruppeSelection();

        $selection = self::get_selection($temp_id);
        if (!empty($values['lvgruppe_selection']['areas'])) {
            foreach ($values['lvgruppe_selection']['areas'] as $area_id) {
                $lvgroup = Lvgruppe::find($area_id);
                $selection->add($lvgroup);
            }
        }

        $selection_details = $values['lvgruppe_selection']['area_details'];

        if ($_SESSION[__CLASS__]['course_start_time'] != $course_start_time) {
            // don't store previously opened nodes
            // because we get in trouble if the semester has changed
            $open_nodes = [];
        } else {
            $open_nodes = !empty($values['open_lvg_nodes']) ? $values['open_lvg_nodes'] : [];
        }

        $_SESSION[__CLASS__]['course_start_time'] = $course_start_time;

        $tpl->set_attribute('open_lvg_nodes', $open_nodes);
        $tpl->set_attribute('selection', $selection);
        $tpl->set_attribute('selection_details', $selection_details);
        $tpl->set_attribute('tree', $lvgtree->getRootItem()->getChildren());

        $tpl->set_attribute('ajax_url', $values['ajax_url'] ?: URLHelper::getLink('dispatch.php/course/wizard/ajax'));
        $tpl->set_attribute('no_js_url', $values['no_js_url'] ?: 'dispatch.php/course/wizard/forward/'.$stepnumber.'/'.$temp_id);
        $tpl->set_attribute('stepnumber', $stepnumber);
        $tpl->set_attribute('temp_id', $temp_id);
        return $tpl->render();
    }

    /**
     * Returns a LvGruppen-Selection object for a given course ID.
     *
     * @param  string     either the MD5ish ID of a course or something falsy to
     *                    indicate a course that is currently being created
     *
     * @return mixed      LvGruppen-Selection object representing
     *                    the selection form
     */
    public function get_selection($course_id)
    {
        if (self::isCourseId($course_id)) {
            $selection = new StudipLvgruppeSelection($course_id);
        } else {
            $lvgruppen = [];
            if (isset($GLOBALS['sem_create_data'])
                && isset($GLOBALS['sem_create_data']['sem_lvgruppen'])) {
                    $lvgruppen = $GLOBALS['sem_create_data']['sem_lvgruppen'];
            }
            $selection = new StudipLvgruppeSelection();
            $selection->setLvgruppen($lvgruppen);
        }
        return $selection;
    }

    /**
     * Every (non-empty) string is a valid course ID except the string '-'
     *
     * @param mixed  the value to check
     * @return bool  TRUE if it is courseID-ish, FALSE otherwise
     */
    public static function isCourseId($id)
    {
        return is_string($id) && $id !== '' && $id !== '-';
    }

    public function getLVGroupTreeLevel($parentId, $parentClass)
    {
        $level = [];
        $children = [];
        $searchtree = [];

        $course = Course::findCurrent();
        if ($course) {
            $course_start = $course->start_time;
            $course_end = ($course->end_time < 0 || is_null($course->end_time)) ? PHP_INT_MAX : $course->end_time;
        } else {
            $semester = Semester::findByTimestamp($_SESSION[__CLASS__]['course_start_time']);
            $course_start = $semester->beginn;
            $course_end = $semester->ende;
        }

        $mvvid = explode('-', $parentId);
        $mvvobj = $parentClass::find($mvvid[0]);
        $children = $mvvobj->getChildren();

        $i = 1;
        foreach ($children as $c) {

            if (isset($c->stat)) {
                if ($c->stat != 'genehmigt') {
                    continue;
                } elseif (isset($c->start) || isset($c->end)) {
                    $mvv_start = Semester::find($c->start);
                    $mvv_start = $mvv_start ? $mvv_start->beginn : 0;
                    $mvv_end = Semester::find($c->end);
                    $mvv_end = $mvv_end ? $mvv_end->ende : PHP_INT_MAX;

                    if ($course_end < $mvv_start || $course_start > $mvv_end) {
                        continue;
                    }
                }
            }

            // name of module maybe differs from original module title if it
            // is assigned to a Studiengangteilabschnitt
            if (is_a($c, 'Modul')) {
                $stgteilabschnitt_modul = StgteilabschnittModul::findOneBySql(
                        '`abschnitt_id` = ? AND `modul_id` = ?', [$mvvid[0], $c->id]);
                $name = $stgteilabschnitt_modul->getDisplayName();
            } else {
                $name = $c->getDisplayName();
            }

            $level[] = [
                'id' => $c->id . '-' . $mvvid[1] . $i++,
                'name' => $name,
                'has_children' => $c->hasChildren(),
                'parent' => $c->getTrailParentId(),
                'assignable' => $c->isAssignable(),
                'mvvclass' => get_class($c)
            ];
        }

        if (Request::isXhr()) {
            return json_encode($level);
        } else {
            return $level;
        }
    }

    public function searchLVGroupTree($searchterm)
    {
        $result = [];
        $selection = self::get_selection(Request::get('cid'));
        $selectedlvg = [];
        if (!empty($selection)) {
            $selectedlvg = $selection->getLvGruppenIDs();
        }

        $course = Course::findCurrent();
        if ($course) {
            $course_start = $course->start_time;
            $course_end = ($course->end_time < 0 || is_null($course->end_time)) ? PHP_INT_MAX : $course->end_time;
        } else {
            $semester = Semester::findByTimestamp($_SESSION[__CLASS__]['course_start_time']);
            $course_start = $semester->beginn;
            $course_end = $semester->ende;
        }

        $status_modul = [];
        foreach ($GLOBALS['MVV_MODUL']['STATUS']['values'] as $name => $status) {
            if ($status['public'] && $status['visible']) {
                $status_modul[] = $name;
            }
        }

        $status_version = [];
        foreach ($GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'] as $name => $status) {
            if ($status['public'] && $status['visible']) {
                $status_version[] = $name;
            }
        }

        $filter = [
            'mvv_modul.stat'          => $status_modul,
            'mvv_stgteilversion.stat' => $status_version,
            'start_sem.beginn'        => $course_end,
            'end_sem.ende'            => $course_start
        ];

        foreach (Lvgruppe::findBySearchTerm($searchterm, $filter) as $area) {
            if (in_array($area->id, $selectedlvg)) {
                continue;
            }

            $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/app/views');
            $html = $factory->render('course/wizard/steps/lvgroups/lvgroup_searchentry', compact('area', 'inlist'));
            $data = [
                'id' => $area->id,
                'html_string' => $html
            ];
            $result[] = $data;
        }
        return json_encode($result);
    }

    public function getLVGroupDetails($id) {
        $mvvid = explode('-', $id);
        $area = Lvgruppe::find($mvvid[0]);

        $course = Course::findCurrent();
        if ($course) {
            $course_start = $course->start_time;
            $course_end = ($course->end_time < 0 || is_null($course->end_time)) ? PHP_INT_MAX : $course->end_time;
        } else {
            $semester = Semester::findByTimestamp($_SESSION[__CLASS__]['course_start_time']);
            $course_start = $semester->beginn;
            $course_end = $semester->ende;
        }

        $status_modul = [];
        foreach ($GLOBALS['MVV_MODUL']['STATUS']['values'] as $name => $status) {
            if ($status['public'] && $status['visible']) {
                $status_modul[] = $name;
            }
        }

        ModuleManagementModelTreeItem::setObjectFilter('Modul',
            function ($modul) use ($course_start, $course_end, $status_modul) {
                if (!in_array($modul->stat, $status_modul)) {
                    return false;
                }
                $modul_start = Semester::find($modul->start)->beginn ?: 0;
                $modul_end = Semester::find($modul->end)->ende ?: PHP_INT_MAX;
                return ($modul_start <= $course_end && $modul_end >= $course_start);
            });

        $status_version = [];
        foreach ($GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'] as $name => $status) {
            if ($status['public'] && $status['visible']) {
                $status_version[] = $name;
            }
        }

        ModuleManagementModelTreeItem::setObjectFilter('StgteilVersion',
            function ($version) use ($status_version) {
                return in_array($version->stat, $status_version);
            });

        $trails = $area->getTrails([
            'Modulteil',
            'StgteilabschnittModul',
            'StgteilAbschnitt',
            'StgteilVersion',
            'Studiengang']);
        $pathes = ModuleManagementModelTreeItem::getPathes($trails);

        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/app/views');
        $html = $factory->render('course/lvgselector/entry_trails',
                compact('area', 'pathes'));

        $data = [
            'id' => $area->id,
            'html_string' => $html
        ];
        if (Request::isXhr()) {
            return json_encode($data);
        } else {
            return $data;
        }
    }

    public function getAncestorTree($id)
    {
        $mvvid = explode('-', $id);
        $area = Lvgruppe::find($mvvid[0]);

        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/app/views');
        $html = $factory->render('course/wizard/steps/lvgroups/lvgroup_entry', compact('area'));

        $data = [
            'id' => $area->id,
            'html_string' => $html
        ];
        return json_encode($data);
    }


    /**
     * Catch form submits other than "previous" and "next" and handle the
     * given values. This is only important for no-JS situations.
     * @param Array $values currently set values for the wizard.
     * @return bool
     */
    public function alterValues($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];

        if (Request::submitted('open_nodes')) {
            $already_open_nodes = unserialize(Request::get('open_nodes'));
            foreach ($already_open_nodes as $open_lvgnode) {
                $values['open_lvg_nodes'][] = $open_lvgnode;
            }
        }

        if (Request::option('open_node')) {
            $node_to_open = Request::get('open_node');
            if (!in_array($node_to_open, $values['open_lvg_nodes'])) {
                $values['open_lvg_nodes'][] = $node_to_open;
            } else {
                $k = array_search($node_to_open, $values['open_lvg_nodes']);
                unset($values['open_lvg_nodes'][$k]);
            }
        }

        if (Request::submitted('lvgruppe_selection')) {

            $lvgruppe_selection = Request::getArray('lvgruppe_selection');

            if (isset($lvgruppe_selection['details'])) {
                foreach (array_keys($lvgruppe_selection['details']) as $lvgid) {
                    $detail = $this->getLVGroupDetails($lvgid);
                    $values['lvgruppe_selection']['area_details'][$detail['id']] = $detail['html_string'];
                }
            }

            if (isset($lvgruppe_selection['remove'])) {
                $new_areas = [];
                foreach ($lvgruppe_selection['areas'] as $area) {
                    if(!key_exists($area, $lvgruppe_selection['remove'])) {
                        $new_areas[] = $area;
                    }
                }
                $values['lvgruppe_selection']['areas'] = $new_areas;
            }
        }

        if ($assign = array_keys(Request::getArray('assign'))) {
            $values['lvgruppe_selection']['areas'][] = $assign[0];
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
        $ok = true;
        $errors = [];

        // optional step if study areas step is activated and at least one area is assigned
        if (!count($values['StudyAreasWizardStep']['studyareas'])
                && !count($values[__CLASS__]['lvgruppe_selection']['areas'])) {
            $ok = false;
            $errors[] = _('Der Veranstaltung muss mindestens eine Lehrveranstaltungsgruppe zugeordnet sein.');
        }
        if ($errors) {
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
        if ($this->is_locked($values)) {
            throw new AccessDeniedException();
        }

        // Leave early if no values are set
        if (!isset($values[__CLASS__]['lvgruppe_selection']['areas'])
            || !is_array($values[__CLASS__]['lvgruppe_selection']['areas']))
        {
            return $course;
        }

        $selection = new StudipLvgruppeSelection($course->id);
        foreach ($values[__CLASS__]['lvgruppe_selection']['areas'] as $lvg_id) {
            $area = Lvgruppe::find($lvg_id);
            $selection->add($area);
        }
        LvGruppe::setLvgruppen($course->id, $selection->getLvgruppenIDs());

        return $course;
    }

    /**
     * Checks if the current step needs to be executed according
     * to already given values.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        // is locked?
        // Set global state in MVV_ACCESS_ASSIGN_LVGRUPPEN
        $locked = $this->is_locked($values);

        $coursetype = 1;
        foreach ($values as $class)
        {
            if ($class['coursetype'])
            {
                $coursetype = $class['coursetype'];
                break;
            }
        }
        $category = SeminarCategories::GetByTypeId($coursetype);

        return (!$locked && $category->module);
    }

    public function is_locked($values)
    {
        global $perm;

        // Has user access to this function? Access state is configured in global config.
        $access_right = get_config('MVV_ACCESS_ASSIGN_LVGRUPPEN');

        // the id of the home institute
        // get the institute from the first step (normally "BasicDataWizardStep")
        $inst_id = reset($values)['institute'];
        if ($access_right == 'fakadmin') {
            // is fakadmin at faculty of given home institute
            $db = DBManager::get();
            $st = $db->prepare("SELECT a.Institut_id FROM user_inst a
                LEFT JOIN Institute b ON (a.Institut_id = b.Institut_id AND b.Institut_id = b.fakultaets_id)
                LEFT JOIN Institute c ON (c.Institut_id = b.Institut_id)
                WHERE a.user_id = ? AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id)
                AND c.Institut_id = ? LIMIT 1");
            $st->execute([$GLOBALS['user']->id, $inst_id]);
            return !((bool) $st->fetchColumn());
        }
        return !$perm->have_studip_perm($access_right, $inst_id);

    }

    /**
     * Copy values for study areas wizard step from given course.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        $data = [];
        $selection = new StudipLvgruppeSelection($course->id);
        foreach ($selection->getAreas() as $a) {
            /*
             * Check if areas assigned to given course are
             * still assignable.
             */
            if ($a->isAssignable()) {
                $data[] = $a->id;
            }
        }

        $values[__CLASS__]['lvgruppe_selection']['areas'] = $data;
        return $values;
    }

}
