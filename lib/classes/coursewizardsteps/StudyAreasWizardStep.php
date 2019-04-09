<?php
/**
 * StudyAreasWizardStep.php
 * Course wizard step for assigning study areas.
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

class StudyAreasWizardStep implements CourseWizardStep
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
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'].'/app/views/course/wizard/steps');
        $tpl = $factory->open('studyareas/index');
        if ($values['studyareas'])
        {
            $tree = $this->buildPartialSemTree(StudipStudyArea::backwards(StudipStudyArea::findMany($values['studyareas'])));
            $tpl->set_attribute('assigned', $tree);
        } else {
            $tpl->set_attribute('assigned', []);
        }
        $tpl->set_attribute('values', $values);

        // First tree level is always shown.
        $tree = StudipStudyArea::findByParent(StudipStudyArea::ROOT);

        if (count($tree) == 0) {
            PageLayout::postError(formatReady(_('Das Anlegen einer ' .
                'Veranstaltung ist nicht möglich, da keine Studienbereiche ' .
                'existieren. Bitte wenden Sie sich an [die ' .
                'Stud.IP-Administration]' .
                URLHelper::getLink('dispatch.php/siteinfo/show') . ' .')));
            return false;
        }

        /*
         * Someone works without JS activated, load all ancestors and
         * children of open node.
         */
        if ($values['open_node']) {
            $tpl->set_attribute('open_nodes',
                $this->buildPartialSemTree(
                    StudipStudyArea::backwards(
                        StudipStudyArea::findByParent(
                            $values['open_node'])), true));
        }
        /*
         * Someone works without JS and has entered a search term:
         * build the partial tree with search results.
         */
        if ($values['searchterm']) {
            $search = $this->searchSemTree($values['searchterm'], true);
            if ($search) {
                $tpl->set_attribute('open_nodes', $search);
                $tpl->set_attribute('search_result', $search);
                unset($values['open_node']);
            } else {
                PageLayout::postMessage(MessageBox::info(_('Es wurde kein Suchergebnis gefunden.')));
                unset($values['searchterm']);
            }
        }
        $tpl->set_attribute('tree', $tree);
        $tpl->set_attribute('ajax_url', $values['ajax_url'] ?: URLHelper::getLink('dispatch.php/course/wizard/ajax'));
        $tpl->set_attribute('no_js_url', $values['no_js_url'] ? : 'dispatch.php/course/wizard/forward/'.$stepnumber.'/'.$temp_id);
        $tpl->set_attribute('stepnumber', $stepnumber);
        $tpl->set_attribute('temp_id', $temp_id);
        return $tpl->render();
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
        // A node has been clicked in order to open the subtree.
        if (Request::option('open_node')) {
            $values['open_node'] = Request::get('open_node');
        }
        // Assign a node to the course.
        if ($assign = array_keys(Request::getArray('assign'))) {
            if ($values['studyareas']) {
                $values['studyareas'][] = $assign[0];
            } else {
                $values['studyareas'] = $assign;
            }
        }
        // Unassign an assigned node.
        if ($unassign = array_keys(Request::getArray('unassign'))) {
            $unassign = $unassign[0];
            // Use array_filter to remove the given entry from assigned nodes.
            $values['studyareas'] = array_filter(
                $values['studyareas'],
                function ($e) use ($unassign) { return $e != $unassign; }
            );
        }
        // Search for a given term in the semtree.
        if (Request::submitted('start_search')) {
            $values['searchterm'] = Request::get('search');
            unset($values['open_node']);
        }
        // Reset search -> normal semtree view.
        if (Request::submitted('reset_search')) {
            unset($values['searchterm']);
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
        if (!$values['studyareas']) {
            $ok = false;
            $errors[] = _('Die Veranstaltung muss mindestens einem Studienbereich zugeordnet sein.');
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
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        $course->study_areas = SimpleORMapCollection::createFromArray(
            StudipStudyArea::findMany($values['studyareas']));
        if ($course->store()) {
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
        return $category->bereiche;
    }

    /**
     * Copy values for study areas wizard step from given course.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        $data = [];
        foreach ($course->study_areas as $a) {
            /*
             * Check if areas assigned to given course are
             * still assignable.
             */
            if ($a->isAssignable()) {
                $data['studyareas'][] = $a->id;
            }
        }
        $values[__CLASS__] = $data;
        return $values;
    }

    public function getSemTreeLevel($parentId)
    {
        $level = [];
        $children = StudipStudyArea::findByParent($parentId);
        foreach ($children as $c) {
            if (!$c->isHidden()) {
                $level[] = [
                    'id' => $c->sem_tree_id,
                    'name' => (string) $c->getName(),
                    'has_children' => $c->hasChildren(),
                    'parent' => $parentId,
                    'assignable' => $c->isAssignable()
                ];
            }
        }
        if (Request::isXhr()) {
            return json_encode($level);
        } else {
            return $level;
        }
    }

    public function searchSemTree($searchterm, $id_only=false)
    {
        $result = [];
        $search = StudipStudyArea::search($searchterm);
        $search = array_filter($search, function($a) { return !$a->isHidden(); });
        $root = StudipStudyArea::backwards($search);
        $result = $this->buildPartialSemTree($root, $id_only);
        if ($id_only) {
            return $result;
        } else {
            return json_encode($result);
        }
    }

    public function getAncestorTree($id)
    {
        $result = [];
        $node = StudipStudyArea::find($id);
        $root = StudipStudyArea::backwards([$node]);
        $result = $this->buildPartialSemTree($root);
        return json_encode($result);
    }

    private function buildPartialSemTree($node, $id_only=false) {
        $children = [];
        foreach ($node->required_children as $c)
        {
            if ($id_only) {
                $children[] = $c->id;
                $children = array_merge($children, $this->buildPartialSemTree($c, $id_only));
            } else {
                $data = [
                    'id' => $c->id,
                    'name' => (string)$c->name,
                    'has_children' => $c->hasChildren(),
                    'parent' => $node->id,
                    'assignable' => $c->isAssignable(),
                    'children' => $this->buildPartialSemTree($c)
                ];
                $children[] = $data;
            }
        }
        return $children;
    }

}
