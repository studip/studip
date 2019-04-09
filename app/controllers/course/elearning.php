<?php
/**
 * Elearning Interface für Veranstaltungen/ Einrichtungen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Arne Schröder <schroeder@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.1
 */

class Course_ElearningController extends AuthenticatedController
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

        if (!Config::Get()->ELEARNING_INTERFACE_ENABLE ) {
            throw new AccessDeniedException(_('Elearning-Interface ist nicht aktiviert.'));
        } else
            $this->elearning_active = true;

        PageLayout::setHelpKeyword('Basis.Ilias');
        PageLayout::setTitle(Context::getHeaderLine(). " - " . _("Lernmodule"));

        checkObject(); // do we have an open object?
        checkObjectModule('elearning_interface');
        object_set_visit_module('elearning_interface');

        $this->search_key = Request::get('search_key');
        $GLOBALS['search_key'] = $this->search_key;
        $this->cms_select = Request::quoted('cms_select');
        $GLOBALS['cms_select'] = $this->cms_select;
        $this->open_all = Request::get('open_all');
        $this->close_all = Request::get('close_all');
        $this->new_account_cms = Request::get('new_account_cms');
        $this->module_system_type = Request::get('module_system_type');
        $this->module_id = Request::get('module_id');
        $this->module_type = Request::get('module_type');
        $this->anker_target = Request::get('anker_target');
        $this->seminar_id = Context::getId();
        $this->rechte = $GLOBALS['perm']->have_studip_perm('tutor', $this->seminar_id);
        if (!isset($GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->new_account_cms])) {
            unset($this->new_account_cms);
        }
        if (!isset($GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_select])) {
            unset($this->cms_select);
        }
        if ($this->seminar_id != $_SESSION['elearning_open_close']["id"]) {
            unset($_SESSION['cache_data']);
            unset($_SESSION['elearning_open_close']);
        }
        if ($this->open_all != "")
            $_SESSION['elearning_open_close']["all open"] = true;
        elseif ($this->close_all != "")
            $_SESSION['elearning_open_close']["all open"] = "";
        $_SESSION['elearning_open_close']["type"] = "seminar";
        $_SESSION['elearning_open_close']["id"] = $this->seminar_id;
        if (Request::get('do_open')) {
            $this->anker_target = Request::get('do_open');
            $_SESSION['elearning_open_close'][Request::get('do_open')] = true;
        } elseif (Request::get('do_close')) {
            $this->anker_target = Request::get('do_close');
            $_SESSION['elearning_open_close'][Request::get('do_close')] = false;
        }
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/learnmodule-sidebar.png');
        $this->sidebar->setContextAvatar(CourseAvatar::getAvatar($this->seminar_id));
    }

    /**
     * Displays a page.
     */
    public function show_action($id = null)
    {
        global $connected_cms, $current_module;
        Navigation::activateItem('/course/elearning/show');
        $GLOBALS['view'] = 'show';

        // Zugeordnete Ilias-Kurse ermitteln und ggf. aktualisieren
        $this->course_output = ELearningUtils::getIliasCourses($this->seminar_id);
        if (!empty($this->new_account_cms)) {
            //Dummy-Instanz der Zuordnungs-Klasse ohne Verbindung zur Veranstaltung
            $object_connections = new ObjectConnections();
        } else {
            //Instanz mit den Zuordnungen von Content-Modulen zur Veranstaltung
            $object_connections = new ObjectConnections($this->seminar_id);

            $connected_modules = $object_connections->getConnections();
        }
        $this->module_count = 0;
        $content_modules_list = [];
        if ($object_connections->isConnected()) {
            $caching_active = true;
            foreach ($connected_modules as $key => $connection) {
                if (ELearningUtils::isCMSActive($connection["cms"])) {

                    ELearningUtils::loadClass($connection["cms"]);

                    $connected_cms[$connection["cms"]]->newContentModule($connection["id"], $connection["type"], true);
                    $connected_modules[$key]['title'] = $connected_cms[$connection["cms"]]->content_module[$connection["id"]]->getTitle();
                    $title_tmp[$key] = str_replace(['ä','ö','ü','ß'],['ae','oe','ue','ss'],mb_strtolower($connected_modules[$key]['title']));
                    $type_tmp[$key] = array_search($connection['type'], array_keys($GLOBALS['ELEARNING_INTERFACE_MODULES'][$connection["cms"]]['types']));
                    $class_tmp[$key] = $GLOBALS['ELEARNING_INTERFACE_MODULES'][$connection["cms"]]["CLASS_PREFIX"];
                }
            }
            array_multisort($class_tmp, SORT_ASC, $type_tmp, SORT_ASC, $title_tmp, SORT_ASC, $connected_modules);

            foreach ($connected_modules as $index => $connection) {
                $current_module = $connection["id"];

                if ($this->module_count == 0)
                    $content_modules_list[$index]['show_header'] = true;
                $this->module_count++;
                $this->module_system_count[$connection["cms"]]++;

                if ($this->open_all != "")
                    $_SESSION['elearning_open_close'][$connected_cms[$connection["cms"]]->content_module[$connection["id"]]->getReferenceString()] = true;
                elseif ($this->close_all != "")
                    $_SESSION['elearning_open_close'][$connected_cms[$connection["cms"]]->content_module[$connection["id"]]->getReferenceString()] = false;

                $connected_cms[$connection["cms"]]->content_module[$connection["id"]]->view->setChangeDate($connection["chdate"]);
                $content_modules_list[$index]['module'] = $connected_cms[$connection["cms"]]->content_module[$connection["id"]]->view->show();
            }
        }
        if (($this->module_count == 0) AND ($this->new_account_cms == "")) {
            if (Context::isInstitute()) {
                PageLayout::postMessage(MessageBox::info(_('Momentan sind dieser Einrichtung keine Lernmodule zugeordnet.')));
            } else {
                PageLayout::postMessage(MessageBox::info(_('Momentan sind dieser Veranstaltung keine Lernmodule zugeordnet.')));
            }
        }

        $widget = new ActionsWidget();
        $widget->addLink(_('Externe Accounts verwalten'), URLHelper::getURL('dispatch.php/elearning/my_accounts'), Icon::create('person', 'clickable'));
        if (count($this->course_output['courses']))
            foreach ($this->course_output['courses'] as $course) {
                $widget->addLink(
                    sprintf(_('Direkt zum Kurs in %s'), $course['cms_name']),
                    $course['url'],
                    Icon::create('link-extern', 'clickable'),
                    ['target' => '_blank', 'rel' => 'noopener noreferrer']
                );
            }
        $this->sidebar->addWidget($widget);
        $this->new_account = $this->new_account_cms;
        $this->content_modules = $content_modules_list;
    }

    /**
     * Displays a page.
     */
    public function edit_action($id = null)
    {
        global $connected_cms, $current_module;
        if (! $this->rechte)
            throw new AccessDeniedException(_('Keine Berechtigung zum Bearbeiten der Lernmodul-Verknüpfungen.'));
        Navigation::activateItem('/course/elearning/edit');
        $GLOBALS['view'] = 'edit';
        // ggf. neuen Ilias4-Kurs anlegen
        if (Request::submitted('create_course') AND $this->rechte) {
            ELearningUtils::loadClass($this->cms_select);
            if ((method_exists($connected_cms[$this->cms_select], "createCourse")))
                if ($connected_cms[$this->cms_select]->createCourse($this->seminar_id))
                    PageLayout::postMessage(MessageBox::info(_('Kurs wurde angelegt.')));
        }

        // ggf. bestehenden Ilias4-Kurs zuordnen
        if (Request::submitted('connect_course')) {
            if ((ObjectConnections::getConnectionModuleId(Request::option("connect_course_sem_id"), "crs", $this->cms_select)) AND ($GLOBALS['perm']->have_studip_perm("dozent", Request::option("connect_course_sem_id")))) {
                ObjectConnections::setConnection($this->seminar_id, ObjectConnections::getConnectionModuleId(Request::option("connect_course_sem_id"), "crs", $this->cms_select), "crs", $this->cms_select);
                PageLayout::postMessage(MessageBox::info(_('Zuordnung wurde gespeichert.')));
                ELearningUtils::loadClass($this->cms_select);
                if ((method_exists($connected_cms[$this->cms_select], "updateConnections")))
                    $connected_cms[$this->cms_select]->updateConnections( ObjectConnections::getConnectionModuleId(Request::option("connect_course_sem_id"), "crs", $this->cms_select) );
            }
        }

        // Zugeordnete Ilias-Kurse ermitteln und ggf. aktualisieren
        $this->course_output = ELearningUtils::getIliasCourses($this->seminar_id);
        if ($this->new_account_cms == "") {
            if ($this->module_system_type != "") {
                $user_crs_role = $connected_cms[$this->module_system_type]->crs_roles[$GLOBALS['auth']->auth["perm"]];
                ELearningUtils::loadClass($this->module_system_type);
            }
            if (Request::submitted('remove')) {
                $connected_cms[$this->module_system_type]->newContentModule($this->module_id, $this->module_type, true);
                if ($connected_cms[$this->module_system_type]->content_module[$this->module_id]->unsetConnection($this->seminar_id, $this->module_id, $this->module_type, $this->module_system_type))
                    PageLayout::postMessage(MessageBox::info(_('Die Zuordnung wurde entfernt.')));
                unset($connected_cms[$this->module_system_type]->content_module[$this->module_id]);
            } elseif (Request::submitted('add')) {
                $connected_cms[$this->module_system_type]->newContentModule($this->module_id, $this->module_type, true);
                if ($connected_cms[$this->module_system_type]->content_module[$this->module_id]->setConnection($this->seminar_id))
                    PageLayout::postMessage(MessageBox::info(_('Die Zuordnung wurde gespeichert.')));
                unset($connected_cms[$this->module_system_type]->content_module[$this->module_id]);
            }
            if ($this->search_key != "") {
                ELearningUtils::loadClass($this->cms_select);
                if ( mb_strlen( trim($this->search_key) ) > 2)
                    $searchresult_content_modules = $connected_cms[$this->cms_select]->searchContentModules($this->search_key);
                else
                    PageLayout::postMessage(MessageBox::error(_('Jeder Suchbegriff muss mindestens 3 Zeichen lang sein!')));
            }
        }
        //Instanz mit den Zuordnungen von Content-Modulen zur Veranstaltung
        $object_connections = new ObjectConnections($this->seminar_id);

        $connected_modules = $object_connections->getConnections();
        $this->module_count = 0;
        $content_modules_list = [];
        $user_modules_list = [];
        $search_modules_list = [];
        if ($object_connections->isConnected()) {
            $caching_active = true;
            foreach ($connected_modules as $key => $connection) {
                if (ELearningUtils::isCMSActive($connection["cms"])) {

                    ELearningUtils::loadClass($connection["cms"]);

                    $connected_cms[$connection["cms"]]->newContentModule($connection["id"], $connection["type"], true);
                    $connected_modules[$key]['title'] = $connected_cms[$connection["cms"]]->content_module[$connection["id"]]->getTitle();
                    $title_tmp[$key] = str_replace(['ä','ö','ü','ß'],['ae','oe','ue','ss'],mb_strtolower($connected_modules[$key]['title']));
                    $type_tmp[$key] = array_search($connection['type'], array_keys($GLOBALS['ELEARNING_INTERFACE_MODULES'][$connection["cms"]]['types']));
                    $class_tmp[$key] = $GLOBALS['ELEARNING_INTERFACE_MODULES'][$connection["cms"]]["CLASS_PREFIX"];
                }
            }
            array_multisort($class_tmp, SORT_ASC, $type_tmp, SORT_ASC, $title_tmp, SORT_ASC, $connected_modules);

            foreach ($connected_modules as $index => $connection) {
                $current_module = $connection["id"];

                if ($this->module_count == 0)
                    $content_modules_list[$index]['show_header'] = true;
                $this->module_count++;
                $this->module_system_count[$connection["cms"]]++;

                if ($this->open_all != "")
                    $_SESSION['elearning_open_close'][$connected_cms[$connection["cms"]]->content_module[$connection["id"]]->getReferenceString()] = true;
                elseif ($this->close_all != "")
                    $_SESSION['elearning_open_close'][$connected_cms[$connection["cms"]]->content_module[$connection["id"]]->getReferenceString()] = false;
                    $content_modules_list[$index]['module'] = $connected_cms[$connection["cms"]]->content_module[$connection["id"]]->view->showAdmin();
            }
        }
        if (($this->module_count == 0) AND ($this->new_account_cms == "")) {
            if (Context::isInstitute()) {
                PageLayout::postMessage(MessageBox::info(_('Momentan sind dieser Einrichtung keine Lernmodule zugeordnet.')));
            } else {
                PageLayout::postMessage(MessageBox::info(_('Momentan sind dieser Veranstaltung keine Lernmodule zugeordnet.')));
            }
        }
        $this->caching_active = false;
        if (isset($GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_select]["name"])) {
            ELearningUtils::loadClass($this->cms_select);

            $user_content_modules = $connected_cms[$this->cms_select]->getUserContentModules();
            if (! ($user_content_modules == false)) {
                foreach ($user_content_modules as $key => $connection) {
                    // show only those modules which are not already connected to the seminar
                    if (is_object($connected_cms[$this->cms_select]->content_module[$connection["ref_id"]]))
                            continue;
                    $connected_cms[$this->cms_select]->setContentModule($connection, false);
                    $connected_cms[$this->cms_select]->content_module[$current_module]->view->showAdmin();
                    $user_modules_list[$key]['module'] = $connected_cms[$this->cms_select]->content_module[$current_module]->view->showAdmin();
                }
            }

             if (!$connected_cms[$this->cms_select]->isAuthNecessary()
                || $connected_cms[$this->cms_select]->user->isConnected()) {
                $this->show_search = true;
            }

            if (! ($searchresult_content_modules == false)) {
                foreach ($searchresult_content_modules as $key => $connection) {
                    // show only those modules which are not already connected to the seminar
                    if (is_object($connected_cms[$this->cms_select]->content_module[$connection["ref_id"]]))
                        continue;
                    $connected_cms[$this->cms_select]->setContentModule($connection, false);
                    $search_modules_list[$key]['module'] = $connected_cms[$this->cms_select]->content_module[$current_module]->view->showAdmin();
                }
            }

                // ILIAS 4: Leeren Kurs anlegen oder Kurse von anderen Veranstaltungen zuordnen
                if ((method_exists($connected_cms[$this->cms_select], "updateConnections")) AND ! ($this->module_system_count[$this->cms_select]) AND ! (ObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $this->cms_select)))
                {
                    $show_ilias_empty_course = true;
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
                    $statement->execute([$this->cms_select]);
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        if ($GLOBALS['perm']->have_studip_perm('dozent', $row['object_id'])) {
                            $existing_courses[$row['object_id']] = my_substr($row['Name'],0,60)." ".sprintf(_("(Kurs-ID %s)"), $row['module_id']);
                        }
                    }
                }

                // ILIAS 4: ggf. Hinweis auf Möglichkeit, weitere Modulformen als Link einzubinden
                elseif (method_exists($connected_cms[$this->cms_select], "updateConnections") AND count($connected_cms[$this->cms_select]->types['webr'])) {
                    $this->show_ilias_link_info = true;
                    $crs_data = ObjectConnections::getConnectionModuleId($this->seminar_id, "crs", $this->cms_select);
                }
            }

        // Cachen der SOAP-Daten
        if (is_array($connected_cms))
            foreach($connected_cms as $system)
                $system->terminate();

        $widget = new ActionsWidget();
        if (count($this->course_output['courses'])) {
            $widget->addLink(_('Zuordnungen aktualisieren'), URLHelper::getURL('?view=edit&cms_select='.$this->cms_select.'&update=1'), Icon::create('refresh', 'clickable'));
        }
        $this->sidebar->addWidget($widget);
        $this->new_account = $this->new_account_cms;
        $this->is_inst = Context::isInstitute();
        if ($this->cms_select) {
            $this->cms_name = $connected_cms[$this->cms_select]->getName();
            $this->cms_logo = $connected_cms[$this->cms_select]->getLogo();
            $this->user_modules = $user_modules_list;
            $this->search_modules = $search_modules_list;
            $this->existing_courses = $existing_courses;
            $this->show_ilias_empty_course = $show_ilias_empty_course;
        }
        $this->content_modules = $content_modules_list;
    }
}
