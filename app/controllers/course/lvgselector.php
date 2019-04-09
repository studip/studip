<?php
/**
 * lvgselector.php - LvgselectorController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require 'config/mvv_config.php';

class Course_LvgselectorController extends AuthenticatedController
{

    // see Trails_Controller#before_filter
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
        $this->course = Course::findCurrent();
        if (!$this->course) {
            throw new Trails_Exception(404, _('Es wurde keine Veranstaltung ausgewählt!'));
        }
        $this->course_id = $this->course->id;
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            throw new AccessDeniedException();
        }
        $this->selection = new StudipLvgruppeSelection($this->course_id);
        $this->semester_id = $this->course->start_semester->id;
    }

    /**
     * This method shows the lvgruppen selection form for a given course ID.
     *
     * @return void
     */
    public function index_action()
    {
        if (Request::get('from')) {
            $this->url_params['from'] = Request::get('from');
        }

        if (!Request::isXHR()) {
            Navigation::activateItem('/course/admin/lvgruppen');
        }
        PageLayout::setTitle(sprintf('%s - %s',
                                     $this->course->getFullname(),
                                     _('Lehrveranstaltungsgruppen')));

        // is locked?
        // Set global state in MVV_ACCESS_ASSIGN_LVGRUPPEN
        $this->locked = $this->is_locked($this->course_id);

        // DOES the course's class permit "lvgruppen"?
        $this->lvgruppen_not_allowed = !$this->course->getSemClass()->offsetGet('module');

        if ($this->lvgruppen_not_allowed) {
            return $this->render_text(MessageBox::info(_('Für diesen Veranstaltungstyp ist die Zuordnung zu Lehrveranstaltungsgruppen nicht vorgesehen.')));
        }
        $this->open_lvg_nodes = [];
        if (Request::submitted('open_nodes')) {
            $already_open_nodes = (array)json_decode(Request::get('open_nodes'));
            foreach ($already_open_nodes as $open_lvgnode) {
                $this->open_lvg_nodes[] = $open_lvgnode;
            }
        }

        if (!$this->locked && !$this->lvgruppen_not_allowed) {
            if (Request::get('open_node')) {
                $node_to_open = Request::get('open_node');
                if (!in_array($node_to_open, $this->open_lvg_nodes)) {
                    $this->open_lvg_nodes[] = $node_to_open;
                } else {
                    $k = array_search($node_to_open, $this->open_lvg_nodes);
                    unset($this->open_lvg_nodes[$k]);
                }
            }

            if (Request::submitted('lvgruppe_selection')) {
                $lvgruppe_selection = Request::getArray('lvgruppe_selection');
                if(isset($lvgruppe_selection['details'])) {
                    foreach (array_keys($lvgruppe_selection['details']) as $lvgid) {
                        $detail = $this->getLVGroupDetails($lvgid);
                        $this->selection_details[$detail['id']] = $detail['html_string'];
                    }
                }
                if (isset($lvgruppe_selection['remove'])) {
                    foreach (array_keys($lvgruppe_selection['remove']) as $lvgid) {
                        $this->selection->remove($lvgid);
                    }
                    $this->store_selection($this->course_id, $this->selection);
                }
            }

            if ($assign = array_keys(Request::getArray('assign'))) {
                $this->selection->add($assign[0]);
            }

            if (Request::submitted('save')) {
                $this->save_action();
            }

            $lvgtree = new StudipLvgruppeSelection();
            $this->tree = $lvgtree->getRootItem()->getChildren();

        }

        $this->ajax_url = $this->url_for('course/lvgselector/ajax');
        $this->url = $this->url_for('/index');
    }

    /**
     * Wrapper for ajax calls to step classes. Three things must be given
     * via Request:
     * - step number
     * - method to call in target step
     * - parameters for the target method (will be passed in given order)
     */
    public function ajax_action()
    {
        $stepNumber = Request::int('step');
        $method = Request::get('method');
        $parameters = Request::getArray('parameter');
        $result = call_user_func_array(['LVGroupsWizardStep', $method], $parameters);
        if (is_array($result) || is_object($result)) {
            $this->render_json($result);
        } else {
            $this->render_text($result);
        }
    }

    /**
     * Returns lvgroup details of a given lvgroup ID.
     *
     * @param  string     either the MD5ish ID of a lvgroup
     *
     * @return array      lvgroup id, html string with lvgroup details
     */
    public function getLVGroupDetails($id)
    {
        $mvvid = explode('-', $id);
        $this->area = Lvgruppe::find($mvvid[0]);
        $data = [
            'id' => $this->area->id,
            'html_string' => $this->render_template_as_string('course/lvgselector/entry_trails')
        ];
        if (Request::isXhr()) {
            return json_encode($data);
        } else {
            return $data;
        }
    }

    /**
     * Saves the changes to the LvGruppen-Selection a given course ID.
     *
     * @param  string     either the MD5ish ID of a course or something falsy to
     *                    indicate a course that is currently being created
     *
     */
    public function save_action()
    {
        if ($this->is_locked($this->course_id)) {
            throw new AccessDeniedException();
        }
        $selected = Request::getArray('lvgruppe_selection');

        $selection = new StudipLvgruppeSelection();

        if (!empty($selected['areas'])) {
            foreach ($selected['areas'] as $area_id) {
                $lvgroup = Lvgruppe::find($area_id);
                $selection->add($lvgroup);
                $open_nodes[] = $area_id;
            }
        }
        $this->store_selection($this->course_id, $selection);

        if (Request::get('from')) {
            $url = URLHelper::getURL('dispatch.php/'.Request::get('from'));
        } else {
            $url = $this->url_for('/index');
        }


        $this->redirect($url);
    }

    /**
     * This method is sent using AJAX to remove a lvgruppe from a course.
     *
     * @param
     *            string the MD5ish ID of the course
     * @return void
     */
    public function remove_action()
    {
        if ($this->is_locked($this->course_id)) {
            throw new AccessDeniedException();
        }

        $id = isset($_POST['id']) ? $_POST['id'] : NULL;

        if ($id === NULL) {
            $this->set_status(400);
            return $this->render_nothing();
        }

        $selection = new StudipLvgruppeSelection($this->course_id);

        // MVV: no problem here to delete LvGruppe

        if ($selection->size() == 1) {
            $this->set_status(409);
            return $this->render_nothing();
        }
        $selection->remove($id);
        $this->store_selection($this->course_id, $selection);
        $this->render_nothing();
    }

    /**
     * Returns the lock state of a given course ID.
     *
     * @param  string     either the MD5ish ID of a course or something falsy to
     *                    indicate a course that is currently being created
     *
     * @return bool
     */
    public function is_locked($course_id)
    {
        global $perm;

        // Has user access to this function? Access state is configured in global config.
        $access_right = get_config('MVV_ACCESS_ASSIGN_LVGRUPPEN');
        if ($perm->have_perm('root')) {
            return false;
        } else if (LockRules::Check($course_id, 'mvv_lvgruppe')) {
            return true;
        } else {
            if ($access_right == 'fakadmin') {
                if ($perm->have_perm('admin')) {
                    $db = DBManager::get();
                    $st = $db->prepare("SELECT Seminar_id FROM user_inst a
                                LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
                                LEFT JOIN Institute c ON (b.Institut_id=c.fakultaets_id)
                                LEFT JOIN seminare d ON (d.Institut_id=c.Institut_id)
                                WHERE a.user_id = ? AND a.inst_perms='admin' AND d.Seminar_id = ? LIMIT 1");
                   $st->execute([$GLOBALS['user']->id, $course_id]);
                    if ($st->fetchColumn()) {
                        return false;
                    }
                }
                return true;
            }
        }
        return !$perm->have_studip_perm($access_right, $course_id);
    }

    /**
     * Stores a LvGruppen-Selection object for a given course ID.
     *
     * @param  string     either the MD5ish ID of a course or something falsy to
     *                    indicate a course that is currently being created
     * @param  StudipStudyAreaSelection     a LvGruppen-Selection object
     */
    public function store_selection($course_id, $selection)
    {

        if ($this->is_locked($course_id)) {
            throw new AccessDeniedException();
        }

        // write the new lvgruppen to the db
        LvGruppe::setLvgruppen($course_id, $selection->getLvgruppenIDs());
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf dieser Seite kann die Veranstaltung ausgewählten Lehrveranstaltungsgruppen zugeordnet werden.')));
        $helpbar->addWidget($widget);

        if ($GLOBALS['perm']->have_perm('admin')) {
            $admin_list_template = AdminList::getInstance()
                    ->getSelectTemplate($this->course_id);
            if ($admin_list_template) {
                $sidebar = Sidebar::get();
                $widget  = new SidebarWidget();
                $widget->setTitle('Veranstaltungliste');
                $widget->addElement(new WidgetElement($admin_list_template->render()));
                $sidebar->addWidget($widget, 'Veranstaltungliste');

            }
        }
    }

}
