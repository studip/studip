<?php
/*
 * my_ilias_accounts.php - ILIAS interface for courses and institutes
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schroeder <schroeder@data-quest.de>
 * @copyright   2018 Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.3
 */
class Course_IliasInterfaceController extends AuthenticatedController
{
    /**
     * Before filter, set up the page by initializing the session and checking
     * all conditions.
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Request::isXhr()) {
            $this->dialog = true;
        }
        if (!Config::Get()->ILIAS_INTERFACE_ENABLE ) {
            throw new AccessDeniedException(_('ILIAS-Interface ist nicht aktiviert.'));
        } else
            $this->elearning_active = true;

        PageLayout::setHelpKeyword('Basis.Ilias');
        PageLayout::setTitle(Context::getHeaderLine(). " - " . _("ILIAS"));

        checkObject(); // do we have an open object?
        object_set_visit_module('ilias_interface');

        $this->ilias_interface_config = Config::get()->getValue('ILIAS_INTERFACE_BASIC_SETTINGS');

        $this->search_key = Request::get('search_key');
        $this->seminar_id = Context::getId();
        $this->edit_permission = $GLOBALS['perm']->have_studip_perm('tutor', $this->seminar_id);
        $this->author_permission = false;
        $this->change_course_permission = $GLOBALS['auth']->auth["perm"] == "root" || ($GLOBALS['perm']->have_studip_perm('tutor', $this->seminar_id) && $this->ilias_interface_config['allow_change_course']);
        $this->add_own_course_permission = $GLOBALS['perm']->have_studip_perm('tutor', $this->seminar_id) && $this->ilias_interface_config['allow_add_own_course'];
        $this->course_permission = $GLOBALS['perm']->have_studip_perm('tutor', $this->seminar_id);

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/learnmodule-sidebar.png');
        $this->sidebar->setContextAvatar(CourseAvatar::getAvatar($this->seminar_id));
    }

    /**
     * Displays a page.
     */
    public function index_action($id = null)
    {
        Navigation::activateItem('/course/ilias_interface/view');

        // Zugeordnete Ilias-Kurse ermitteln und ggf. aktualisieren
        $missing_course = false;
        $this->courses = [];
        $this->ilias_list = [];
        $module_count = 0;
        foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
            if ($ilias_config['is_active']) {
                $this->ilias_list[$ilias_index] = new ConnectedIlias($ilias_index);
                if ($GLOBALS['perm']->have_perm($this->ilias_list[$ilias_index]->ilias_config['author_perm'])) {
                    $this->author_permission = true;
                }
                $crs_id = IliasObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $ilias_index);
                if ($crs_id) {
                    $this->ilias_list[$ilias_index]->checkUserCoursePermissions($crs_id);
                    $this->course_objects[$ilias_index] = $this->ilias_list[$ilias_index]->getModule($crs_id);
                    if ($this->course_objects[$ilias_index]->isAllowed('start')) {
                        $this->courses[$ilias_index] = $crs_id;
                        $this->ilias_list[$ilias_index]->updateCourseConnections($crs_id);
                        $module_count += count($this->ilias_list[$ilias_index]->getCourseModules());
                    }
                } else {
                    $missing_course = true;
                }
            }
        }

        if (($this->module_count == 0) && (!$this->courses)) {
            if (Context::isInstitute()) {
                PageLayout::postInfo(_('Momentan sind dieser Einrichtung keine Lernobjekte zugeordnet.'));
            } else {
                PageLayout::postInfo(_('Momentan sind dieser Veranstaltung keine Lernobjekte zugeordnet.'));
            }
        }

        if ($this->edit_permission) {
            $widget = new ActionsWidget();
            $widget->setTitle(_('Lernobjekte hinzufügen'));
            if ($this->ilias_interface_config['search_active']) {
                $widget->addLink(
                    _('Lernobjekte suchen'),
                    $this->url_for('course/ilias_interface/add_object/search'),
                    Icon::create('learnmodule+add', 'clickable'),
                    ['data-dialog' => '']
                    );
            }
            if ($this->author_permission) {
                $widget->addLink(
                    _('Meine Lernobjekte'),
                    $this->url_for('course/ilias_interface/add_object/my_modules'),
                    Icon::create('learnmodule+add', 'clickable'),
                    ['data-dialog' => '']
                    );
            }
            if ($this->ilias_interface_config['search_active'] || $this->author_permission) {
                    $this->sidebar->addWidget($widget);
            }
        }

        $widget = new ActionsWidget();
        $widget->setTitle(count($this->ilias_list) > 1 ? _('ILIAS-Kurse') : _('ILIAS-Kurs'));
        if ($this->edit_permission) {
            if ($missing_course) {
                $widget->addLink(
                        _('Neuen ILIAS-Kurs anlegen'),
                        $this->url_for('course/ilias_interface/add_object/new_course'),
                        Icon::create('seminar+add', 'clickable'),
                        ['data-dialog' => 'size=auto;reload-on-close']
                        );
                if ($this->change_course_permission) $widget->addLink(
                        _('ILIAS-Kurs aus einer anderen Veranstaltung zuordnen'),
                        $this->url_for('course/ilias_interface/add_object/assign_course'),
                        Icon::create('seminar+add', 'clickable'),
                        ['data-dialog' => 'size=auto;reload-on-close']
                        );
                if ($this->change_course_permission) $widget->addLink(
                        _('Eigenen ILIAS-Kurs zuordnen'),
                        $this->url_for('course/ilias_interface/add_object/assign_own_course'),
                        Icon::create('seminar+add', 'clickable'),
                        ['data-dialog' => 'size=auto;reload-on-close']
                        );
            }
        }
        foreach ($this->courses as $ilias_index => $crs_id) {
            $widget->addLink(
                    sprintf(_('Kurs in %s'), $this->ilias_list[$ilias_index]->getName()).($this->course_objects[$ilias_index]->is_offline ? ' '._('(offline)') : ''),
                    $this->url_for('my_ilias_accounts/redirect/'.$ilias_index.'/start/'.$crs_id.'/crs'),
                    Icon::create('link-extern', 'clickable'),
                    ['target' => '_blank', 'rel' => 'noopener noreferrer']
                    );
            if ($this->change_course_permission) {
                $widget->addLink(
                        sprintf(_('Verknüpfung zu %s entfernen'), $this->ilias_list[$ilias_index]->getName()),
                        $this->url_for('course/ilias_interface/remove_course/'.$ilias_index.'/'.$crs_id),
                        Icon::create('seminar+remove', 'clickable'),
                        ['data-confirm' => sprintf(_('Verknüpfung zum Kurs in %s entfernen? Hierdurch werden auch die Verknüpfungen zu allen Objekten innerhalb des Kurses entfernt.'), $this->ilias_list[$ilias_index]->getName())]
                        );
            }
        }
        $this->sidebar->addWidget($widget);

        if ($this->author_permission || $this->edit_permission) {
            $widget = new ActionsWidget();
            if ($this->edit_permission && $this->ilias_interface_config['add_statusgroups']) {
                $widget->addLink(
                        _('Gruppen übertragen'),
                        $this->url_for('course/ilias_interface/add_groups'),
                        Icon::create('group2+refresh', 'clickable'),
                        ['data-dialog' => 'size=auto']
                        );
            }
            if ($this->author_permission) {
                $widget->addLink(
                        _('Externe Accounts verwalten'),
                        $this->url_for('my_ilias_accounts'),
                        Icon::create('person', 'clickable'));
            }
            if ($this->edit_permission && $this->ilias_interface_config['edit_moduletitle']) {
                $widget->addLink(
                        _('Seite umbenennen'),
                        $this->url_for('course/ilias_interface/edit_moduletitle'),
                        Icon::create('edit', 'clickable'),
                        ['data-dialog' => 'size=auto']
                        );
            }
            $this->sidebar->addWidget($widget);
        }

        // show error messages
        foreach ($this->ilias_list as $ilias_index => $ilias) {
            foreach ($ilias->getError() as $error) {
                PageLayout::postError($error);
            }
        }
    }


    /**
     * edit module connection
     * @param $index Index of ILIAS installation
     */
    public function edit_object_assignment_action($index)
    {
        if (!$this->edit_permission) {
            throw new AccessDeniedException();
        }

        $this->ilias = new ConnectedIlias($index);
        $this->module_id = Request::int('ilias_module_id');
        $this->ilias_index = $index;
        $module = $this->ilias->getModule(Request::int('ilias_module_id'));
        if ($module->isAllowed('edit') || $module->isAllowed('copy')) {
            $permission_level = $this->ilias->ilias_config['author_perm'];
        } else {
            $permission_level = '';
        }
        if (Request::submitted('remove_module') && $module->isAllowed('delete')) {
            if ($this->ilias->unsetCourseModuleConnection($this->seminar_id, Request::int('ilias_module_id'), $module->getModuleType())) {
                PageLayout::postInfo(_('Die Zuordnung wurde entfernt.'));
            }
        } elseif (Request::get('ilias_add_mode') == 'copy') {
            if ($this->ilias->setCourseModuleConnection($this->seminar_id, Request::int('ilias_module_id'), $module->getModuleType(), 'copy', $permission_level)) {
                PageLayout::postInfo(_('Die Zuordnung wurde gespeichert.'));
            }
        } elseif (Request::get('ilias_add_mode') == 'reference') {
            if ($this->ilias->setCourseModuleConnection($this->seminar_id, Request::int('ilias_module_id'), $module->getModuleType(), 'reference', '')) {
                PageLayout::postInfo(_('Die Zuordnung wurde gespeichert.'));
            }
        }
        if (!Request::submitted('add_module')) {
            $this->redirect($this->url_for('course/ilias_interface'));
        }
    }

    /**
     * Add module to course
     * @param $index Index of ILIAS installation
     */
    public function add_object_action($mode = 'search', $index = '')
    {
        PageLayout::setTitle(_('Lernobjekt hinzufügen'));

        if (!$this->edit_permission) {
            throw new AccessDeniedException();
        }

        // get active ILIAS installations
        $this->ilias_list = [];
        $this->mode = $mode;
        foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
            if ($ilias_config['is_active']) {
                $this->ilias_list[$ilias_index] = new ConnectedIlias($ilias_index);
                $last_ilias_index = $ilias_index;
                if (Request::get('ilias_index') == $ilias_index) {
                    $index = $ilias_index;
                }
            }
        }

        if (($mode == 'new_course') || ($mode == 'assign_course') || ($mode == 'assign_own_course')) {
            // allow add course only if no course exists
            foreach ($this->ilias_list as $ilias_index => $ilias) {
                if (IliasObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $ilias_index)) {
                    unset($this->ilias_list[$ilias_index]);
                } else {
                    $last_ilias_index = $ilias_index;
                }
            }
        }

        if (!$index && (count($this->ilias_list) > 1)) {
            // if several installations available yet no index given show index selection dialog
            $this->submit_text =  _('Weiter');

        } elseif (count($this->ilias_list)) {
            // skip installation selection if only one ILIAS installation is active
            if (!$index) {
                $index = $last_ilias_index;
            }
            $this->ilias = $this->ilias_list[$index];
            $this->ilias_index = $index;
            $this->ilias_modules = [];
            $object_connections = new IliasObjectConnections($this->seminar_id);
            $course_modules = $object_connections->getConnections();

            if ($mode == 'search') {
                // perform search
                $this->ilias_search = Request::quoted('ilias_search');
                if (strlen($this->ilias_search) > 2) {
                    $this->ilias_modules = $this->ilias->searchModules($this->ilias_search);
                    foreach ($this->ilias_modules as $search_module_id => $search_module_object) {
                        if (!$search_module_object->isAllowed('copy') && !$search_module_object->isAllowed('edit')) {
                            unset($this->ilias_modules[$search_module_id]);
                        }
                    }
                } elseif (strlen($this->ilias_search) > 0) {
                    PageLayout::postInfo(_('Der Suchbegriff muss mindestens drei Zeichen lang sein.'));
                }
                if (count($this->ilias_modules)) {
                    $this->submit_text = _('Zurück zur Suche');
                } else {
                    $this->submit_text = _('Suchen');
                }

            } elseif ($mode == 'my_modules') {
                // get user modules
                $this->ilias_modules = $this->ilias->getUserModules();

            } elseif ($mode == 'new_course') {
                $this->submit_text = _('Kurs anlegen');
                if (Request::get('cmd') ==  'add_course') {
                    $crs_id = $this->ilias->addCourse($this->seminar_id);
                    if ($crs_id) {
                        PageLayout::postInfo(_('Neuer Kurs wurde angelegt.'));
                        $this->redirect($this->url_for('course/ilias_interface'));
                    }
                }

            } elseif ($mode == 'assign_course') {
                $this->submit_text = _('Kurs zuordnen');
                if ($GLOBALS['perm']->have_perm('root')) {
                    $query = "SELECT DISTINCT object_id, module_id, Name
                                  FROM object_contentmodules
                                  LEFT JOIN seminare ON (object_id = Seminar_id)
                                  WHERE module_type = 'crs' AND system_type = ?";
                } else {
                    $query = "SELECT DISTINCT object_id, module_id, Name
                                  FROM object_contentmodules
                                  LEFT JOIN seminare ON (object_id = Seminar_id)
                                  LEFT JOIN seminar_user USING (Seminar_id)
                                  WHERE module_type = 'crs' AND system_type = ? AND seminar_user.status = 'dozent'";
                }
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$this->ilias_index]);
                $this->studip_course_list = [];
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $this->studip_course_list[$row['module_id']] = my_substr($row['Name'],0,60)." ".sprintf(_("(Kurs-ID %s)"), $row['module_id']);
                }

                if (Request::get('cmd') ==  'assign_course') {
                    $crs_id = IliasObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $this->ilias_index);
                    if (!$crs_id) {
                        IliasObjectConnections::setConnection($this->seminar_id, Request::get(ilias_course_id), "crs", $this->ilias_index);
                        PageLayout::postInfo(_('Kurs wurde zugeordnet.'));
                        $this->redirect($this->url_for('course/ilias_interface'));
                    }
                }
            } elseif ($mode == 'assign_own_course') {
                $own_courses = $this->ilias->soap_client->getCoursesForUser($this->ilias->user->getId(), 12);
                if (is_array($own_courses) && count($own_courses)) {
                    $this->submit_text = _('Kurs zuordnen');
                    foreach ($own_courses as $own_course_id => $own_course_name) {
                        $this->studip_course_list[$own_course_id] = my_substr($own_course_name,0,60)." ".sprintf(_("(Kurs-ID %s)"), $own_course_id);
                    }
                } else {
                    $this->submit_text = '';
                }

                if (Request::get('cmd') ==  'assign_course') {
                    $crs_id = IliasObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $this->ilias_index);
                    if (!$crs_id) {
                        IliasObjectConnections::setConnection($this->seminar_id, Request::get(ilias_course_id), "crs", $this->ilias_index);
                        PageLayout::postInfo(_('Kurs wurde zugeordnet.'));
                        $this->redirect($this->url_for('course/ilias_interface'));
                    }
                }
            }
            // exclude all modules that are already assigned to course
            foreach ($this->ilias_modules as $module_id => $module) {
                if ($course_modules[$this->ilias_index][$module_id]) {
                    unset($this->ilias_modules[$module_id]);
                }
            }
            // show error messages
            foreach ($this->ilias->getError() as $error) {
                PageLayout::postError($error);
            }
        }
    }

    /**
     * Add/Update status groups
     * @param $index Index of ILIAS installation
     */
    public function add_groups_action($index = '')
    {
        PageLayout::setTitle(_('Gruppen übertragen'));

        if (!$this->edit_permission) {
            throw new AccessDeniedException();
        }

        $this->groups = Statusgruppen::findBySeminar_id($this->seminar_id);

        // get active ILIAS installations
        $this->ilias_list = [];
        foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
            if ($ilias_config['is_active'] && IliasObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $ilias_index)) {
                $this->ilias_list[$ilias_index] = new ConnectedIlias($ilias_index);
                $last_ilias_index = $ilias_index;
                if (Request::get('ilias_index') == $ilias_index) {
                    $index = $ilias_index;
                }
            }
        }

        if (!$index && (count($this->ilias_list) > 1)) {
            // if several installations available yet no index given show index selection dialog
            $this->submit_text =  _('Weiter');

        } elseif (count($this->ilias_list)) {
            // skip installation selection if only one ILIAS installation is active
            if (!$index) {
                $index = $last_ilias_index;
            }
            $this->ilias = $this->ilias_list[$index];
            $this->ilias_index = $index;
            $this->ilias_groups = [];
            $this->groups_exist = false;
            $this->submit_text =  _('Gruppen anlegen');
            $course_id = IliasObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $this->ilias_index);

            if ((Request::get('cmd') == 'create_groups') && $course_id) {
                // add groups
                foreach ($this->groups as $group) {
                    $group_data = [
                                    'title' => $group->getName(),
                                    'owner' => $this->ilias->user->getId()
                    ];
                    if ($group_id = IliasObjectConnections::getConnectionModuleId($group->getId(), "group", $this->ilias_index)) {
                        // update existing group
                        $this->ilias->soap_client->updateGroup($group_data, $group_id);
                        $ilias_group = $this->ilias->soap_client->getGroup($group_id);
                        $member_count = 0;
                        // add assigned Stud.IP members
                        foreach ($group->members as $member) {
                            $query = "SELECT external_user_id FROM auth_extern WHERE studip_user_id = ? AND external_user_system_type = ?";
                            $statement = DBManager::get()->prepare($query);
                            $statement->execute([$member->user_id, $this->ilias_index]);
                            $data = $statement->fetch(PDO::FETCH_ASSOC);
                            if ($data) {
                                $member_count++;
                                $position = array_search($data['external_user_id'], $ilias_group['members']);
                                if ($position === false) {
                                    $this->ilias->soap_client->assignGroupMember($group_id, $data['external_user_id'], 'Member');
                                } else {
                                    unset($ilias_group['members'][$position]);
                                }
                            }
                        }
                        // remove remaining ILIAS members
                        foreach ($ilias_group['members'] as $member) {
                            $this->ilias->soap_client->excludeGroupMember($group_id, $member);
                        }
                        PageLayout::postSuccess(sprintf(_('Gruppe "%s" (%s Teilnehmende) aktualisiert.'), $group->getName(), $member_count));
                    } elseif ($group_id = $this->ilias->soap_client->addGroup($group_data, $course_id)) {
                        // create new group
                        IliasObjectConnections::setConnection($group->getId(), $group_id, 'group', $this->ilias_index);
                        // add members
                        $member_count = 0;
                        foreach ($group->members as $member) {
                            $query = "SELECT external_user_id FROM auth_extern WHERE studip_user_id = ? AND external_user_system_type = ?";
                            $statement = DBManager::get()->prepare($query);
                            $statement->execute([$member->user_id, $this->ilias_index]);
                            $data = $statement->fetch(PDO::FETCH_ASSOC);
                            if ($data) {
                                $member_count++;
                                $this->ilias->soap_client->assignGroupMember($group_id, $data['external_user_id'], 'Member');
                            }
                        }
                        PageLayout::postSuccess(sprintf(_('Gruppe "%s" (%s Teilnehmende) angelegt.'), $group->getName(), $member_count));
                    }
                }
                $this->redirect($this->url_for('course/ilias_interface'));
            } else {
                foreach ($this->groups as $group) {
                    if ($group_id = IliasObjectConnections::getConnectionModuleId($group->getId(), "group", $this->ilias_index)) {
                        $this->submit_text =  _('Gruppen aktualisieren');
                        $this->groups_exist = true;
                    }
                }
            }

            // show error messages
            foreach ($this->ilias->getError() as $error) {
                PageLayout::postError($error);
            }
        }
    }

    /**
     * Remove course connection
     * @param $index Index of ILIAS installation
     * @param $crs_id course ID
     */
    public function remove_course_action($index, $crs_id)
    {
        if (!$this->edit_permission) {
            throw new AccessDeniedException();
        }

        $this->ilias = new ConnectedIlias($index);
        if ($this->ilias->isActive()) {
            if (IliasObjectConnections::DeleteAllConnections($this->seminar_id, $index)) {
                PageLayout::postSuccess(_("Kurs-Verknüpfung entfernt."));
            }
        } else {
            PageLayout::postError(_("Diese ILIAS-Installation ist nicht aktiv."));
        }
        $this->redirect($this->url_for('course/ilias_interface'));
    }

    /**
     * View ILIAS module Details
     * @param $index Index of ILIAS installation
     * @param $module_id module ID
     */
    public function view_object_action($index, $module_id)
    {
        $this->ilias = new ConnectedIlias($index);
        if ($this->ilias->isActive()) {
            //TODO: check context
            $this->module = $this->ilias->getModule($module_id);
            $this->module->setConnectionType(IliasObjectConnections::isObjectConnected($index, $module_id));
            $this->module_id = $module_id;
            $this->ilias_search = Request::get('ilias_search');
            $this->mode = Request::get('mode');
            $this->ilias_index = $index;
            PageLayout::setTitle($this->module->getTitle());
        } else {
            PageLayout::postError(_("Diese ILIAS-Installation ist nicht aktiv."));
        }
    }

    /**
     * Edit course module title
     */
    public function edit_moduletitle_action()
    {
        $this->ilias_interface_moduletitle = CourseConfig::get($this->seminar_id)->getValue('ILIAS_INTERFACE_MODULETITLE');
        if (Request::get('ilias_interface_moduletitle')) {
            if (Request::get('ilias_interface_moduletitle') != Config::get()->getValue('ILIAS_INTERFACE_MODULETITLE')) {
                CourseConfig::get($this->seminar_id)->store('ILIAS_INTERFACE_MODULETITLE', Request::get('ilias_interface_moduletitle'));
                PageLayout::postSuccess(_('Seitentitel gespeichert'));
                $this->redirect($this->url_for('course/ilias_interface'));
            } else {
                CourseConfig::get($this->seminar_id)->delete('ILIAS_INTERFACE_MODULETITLE');
                PageLayout::postSuccess(_('Seitentitel zurückgesetzt'));
                $this->redirect($this->url_for('course/ilias_interface'));
            }
        }
    }
}
