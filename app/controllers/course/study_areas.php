<?php
/**
 * Course_StudyAreasController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 *
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @category    Stud.IP
 * @since       3.2
 */

require_once 'lib/webservices/api/studip_lecture_tree.php';
require_once 'lib/classes/coursewizardsteps/StudyAreasWizardStep.php';

class Course_StudyAreasController extends AuthenticatedController
{


    // see Trails_Controller#before_filter
    function before_filter(&$action, &$args)
    {

        global $perm;

        parent::before_filter($action, $args);

        // Search for course object
        $this->course = Course::findCurrent();
        $this->locked = LockRules::Check($this->course->id, 'sem_tree');

        // check course object and perms
        if (!is_null($this->course)
            && !$perm->have_studip_perm("tutor", $this->course->id)
        ) {
            $this->set_status(403);
            return FALSE;
        }

        // Init Studyareas-Step for
        $this->step = new StudyAreasWizardStep();
        $this->values = [];
        $this->values['StudyAreasWizardStep']['studyareas'] = $this->get_area_ids($this->course->id);
        $this->values['StudyAreasWizardStep']['ajax_url'] = $this->url_for('course/study_areas/ajax');
        $this->values['StudyAreasWizardStep']['no_js_url'] = $this->url_for('course/study_areas/show');

        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Studienbereiche'));
    }


    function show_action()
    {
        $this->url_params = [];
        if (Request::get('from')) {
            $this->url_params['from'] = Request::get('from');
        }
        if (Request::get('open_node')) {
            $this->url_params['open_node'] = Request::get('open_node');
        }
        if (!Request::isXhr()) {

            Navigation::activateItem('course/admin/study_areas');
            $sidebar = Sidebar::get();
            $sidebar->setImage('sidebar/admin-sidebar.png');

            if ($this->course) {
                // Entry list for admin upwards.
                if ($GLOBALS['perm']->have_studip_perm('admin', $GLOBALS['SessionSeminar'])) {
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
        }
        if (Request::get('open_node')) {
            $this->values['StudyAreasWizardStep']['open_node'] = Request::get('open_node');
        }

        $this->values['StudyAreasWizardStep']['locked'] = $this->locked;
        $this->tree                                     = $this->step->getStepTemplate($this->values, 0, 0);
    }

    function ajax_action()
    {
        $parameter = Request::getArray('parameter');
        $method = Request::get('method');

        switch ($method) {
            case 'searchSemTree':
                $json = $this->step->searchSemTree($parameter[0]);
                break;
            case 'getSemTreeLevel':
                $json = $this->step->getSemTreeLevel($parameter[0]);
                break;
            case 'getAncestorTree':
                $json = $this->step->getAncestorTree($parameter[0]);
                break;
            default:
                $json = $this->step->getAncestorTree($parameter[0]);
                break;
        }

        $this->render_json($json);
    }

    function save_action()
    {
        if($this->locked) {
            throw new Trails_Exception(403);
        }

        $params = [];
        if(Request::get('open_node')) {
            $params['open_node'] = Request::get('open_node');
        }
        if (Request::get('from')) {
            $url = $this->url_for(Request::get('from'));
        } else {
            $url = $this->url_for('course/study_areas/show/' . $this->course->id);
        }

        if (Request::submittedSome('assign', 'unassign')) {
            if (Request::submitted('assign')) {
                $msg = $this->assign();
            }

            if (Request::submitted('unassign')) {
                $msg = $this->unassign();
            }


        } else {
            $studyareas = Request::getArray('studyareas');

            if (empty($studyareas)) {
                PageLayout::postMessage(MessageBox::error(_('Sie müssen mindestens einen Studienbereich auswählen')));
                $this->redirect($url);
                return;
            }

            try {
                $this->course->setStudyAreas($studyareas);
            } catch (UnexpectedValueException $e) {
                PageLayout::postError($e->getMessage());
            }
        }

        if (!$msg) {
            PageLayout::postMessage(MessageBox::success(_('Die Studienbereichszuordnung wurde übernommen.')));
        } else {
            PageLayout::postMessage(MessageBox::error($msg));
        }
        $this->redirect($url);
    }

    public function unassign()
    {
        $msg = null;
        $assigned = $this->course->study_areas->pluck('sem_tree_id');
        foreach (array_keys(Request::getArray('unassign')) as $remove) {
            if (false !== ($pos = array_search($remove, $assigned))) {
                unset($assigned[$pos]);
            }
        }

        if (empty($assigned)) {
            return _('Sie müssen mindestens einen Studienbereich auswählen');
        }

        $this->course->setStudyAreas($assigned);

        return $msg;
    }

    public function assign()
    {
        $msg = null;
        $assigned = array_keys(Request::getArray('assign'));

        if ($this->course->study_areas) {
            $assigned = array_unique(array_merge($assigned, $this->course->study_areas->pluck('sem_tree_id')));
        }

        $this->course->setStudyAreas($assigned);

        return $msg;
    }


    function get_area_ids($course_id)
    {
        $selection = StudipStudyArea::getStudyAreasForCourse($course_id);

        return array_keys($selection->toGroupedArray('sem_tree_id'));
    }
}
