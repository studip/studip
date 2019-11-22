<?php

require_once 'lib/messaging.inc.php';
require_once 'lib/user_visible.inc.php';

/**
 * This controller realises the basal functionalities of a studygroup.
 *
 * @license GPL2 or any later version
 */
class Course_StudygroupController extends AuthenticatedController
{

    // see Trails_Controller#before_filter
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Config::Get()->STUDYGROUPS_ENABLE
            || in_array($action, words('globalmodules savemodules deactivate'))
        ) {

            // args at position zero is always the studygroup-id
            if ($args[0] && $action == 'details') {
                if (SeminarCategories::GetBySeminarId($args[0])->studygroup_mode == false) {
                    throw new Exception(_('Dieses Seminar ist keine Studiengruppe!'));
                }
            }
            PageLayout::setTitle(_("Studiengruppe bearbeiten"));
            PageLayout::setHelpKeyword('Basis.Studiengruppen');
        } else {
            throw new Exception(_("Die von Ihnen gewählte Option ist im System nicht aktiviert."));
        }

        Sidebar::get()->setImage('sidebar/studygroup-sidebar.png');

        if (Context::getId()) {
            $this->view = $this->getView(Context::getId());
        }
    }

    private function getView($course_id)
    {
        // Obtain user config
        if (isset($GLOBALS['user']->cfg->STUDYGROUP_VIEWS)) {
            $user_cfg = json_decode($GLOBALS['user']->cfg->STUDYGROUP_VIEWS, true);
        } else {
            $user_cfg = [];
        }

        // Obtain default view
        $default_view = $user_cfg[$course_id] ?: 'gallery';
        $view = Request::option('view', $default_view);
        if (!in_array($view, words('gallery list'))) {
            $view = 'gallery';
        }

        // Store default view
        if ($view !== 'gallery') {
            $user_cfg[$course_id] = $view;
        } elseif (isset($user_cfg[$course_id])) {
            unset($user_cfg[$course_id]);
        }
        $GLOBALS['user']->cfg->store('STUDYGROUP_VIEWS', json_encode($user_cfg));

        return $view;
    }

    /**
     * shows details of a studygroup
     *
     * @param string id of a studygroup
     * @return void
     */
    public function details_action($id = null)
    {
        global $perm;

        if (!$id) {
            $id = Context::getId();
        }

        $studygroup = new Seminar($id);
        if (Request::isXhr()) {
            PageLayout::setTitle(_('Studiengruppendetails'));
        } else {
            PageLayout::setTitle((Context::getHeaderLine() ?: Course::find($id)->getFullname()) . ' - ' . _('Studiengruppendetails'));
            PageLayout::setHelpKeyword('Basis.StudiengruppenAbonnieren');

            $stmt = DBManager::get()->prepare("SELECT * FROM admission_seminar_user"
                                              . " WHERE user_id = ? AND seminar_id = ?");
            $stmt->execute([$GLOBALS['user']->id, $id]);
            $data = $stmt->fetch();

            if ($data['status'] == 'accepted') {
                $membership_requested = true;
            }
            $invited = StudygroupModel::isInvited($GLOBALS['user']->id, $id);

            $participant = $perm->have_studip_perm('autor', $id);

            if (!preg_match('/^(' . preg_quote($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'], '/') . ')?([a-zA-Z0-9_-]+\.php)([a-zA-Z0-9_?&=-]*)$/', Request::get('send_from_search_page'))) {
                $send_from_search_page = '';
            } else {
                $send_from_search_page = Request::get('send_from_search_page');
            }

            $icon = Icon::create('door-enter', 'clickable');
            if ($GLOBALS['perm']->have_studip_perm('autor', $studygroup->id) || $membership_requested) {
                $action = _('Persönlicher Status:');
                if ($membership_requested) {
                    $icon = $icon->copyWithRole('info');
                    $infotext = _('Mitgliedschaft bereits beantragt!');
                } else {
                    $infolink = URLHelper::getURL('seminar_main.php', ['auswahl' => $studygroup->id]);
                    $infotext = _('Direkt zur Studiengruppe');
                }
            } else if ($GLOBALS['perm']->have_perm('admin')) {
                $action   = _('Hinweis');
                $infotext = _('Sie sind Admin und können sich daher nicht für Studiengruppen anmelden.');
                $icon     = Icon::create('decline', 'attention');
            } else {
                $action           = _('Aktionen');
                $infolink         = $this->url_for("course/enrolment/apply/{$studygroup->id}");
                $infolink_options = ['data-dialog' => ''];
                // customize link text if user is invited or group access is restricted
                if ($invited) {
                    $infotext = _('Einladung akzeptieren');
                } elseif ($studygroup->admission_prelim) {
                    $infotext = _('Mitgliedschaft beantragen');
                } else {
                    $infotext = _('Studiengruppe beitreten');
                }
            }
            $sidebar = Sidebar::get();
            $sidebar->setTitle(_('Details'));
            $sidebar->setContextAvatar(StudygroupAvatar::getAvatar($studygroup->id));

            $iwidget = new SidebarWidget();
            $iwidget->setTitle(_('Information'));
            $iwidget->addElement(new WidgetElement(_('Hier sehen Sie weitere Informationen zur Studiengruppe. Außerdem können Sie ihr beitreten/eine Mitgliedschaft beantragen.')));
            $sidebar->addWidget($iwidget);

            $awidget = new LinksWidget();
            $awidget->setTitle($action);
            $awidget->addLink($infotext, $infolink, $icon, $infolink_options);
            if ($send_from_search_page) {
                $awidget->addLink(
                    _('zurück zur Suche'),
                    URLHelper::getURL($send_from_search_page),
                    Icon::create('link-intern')
                );
            }
            $sidebar->addWidget($awidget);

            $this->sidebarActions = $awidget->getElements();

            $ashare = new ShareWidget();
            $ashare->addCopyableLink(
                _('Link zu dieser Studiengruppe kopieren'),
                $this->link_for("course/studygroup/details/" . $studygroup->id, ['cid' => null]),
                Icon::create('group')
            );
            $sidebar->addWidget($ashare);
        }
        $this->studygroup = $studygroup;
    }

    /**
     * @addtogroup notifications
     *
     * Creating a new studygroup triggers a StudygroupDidCreate
     * notification. The ID of the studygroup is transmitted as
     * subject of the notification.
     */

    /**
     * creates a new studygroup with respect to given form data
     *
     * Triggers a StudygroupDidCreate notification using the ID of the
     * new studygroup as subject.
     *
     * @return void
     */
    public function create_action()
    {
        global $perm;

        $admin  = $perm->have_perm('admin');
        $errors = [];

        CSRFProtection::verifyUnsafeRequest();

        foreach ($GLOBALS['SEM_CLASS'] as $key => $class) {
            if ($class['studygroup_mode']) {
                $sem_class = $class;
                break;
            }
        }

        if (Request::getArray('founders')) {
            $founders                = Request::optionArray('founders');
            $this->flash['founders'] = $founders;
        }
        // search for founder
        if ($admin && Request::submitted('search_founder')) {
            $search_for_founder = Request::get('search_for_founder');

            // do not allow to search with the empty string
            if ($search_for_founder) {
                // search for the user
                $query     = "SELECT user_id, {$GLOBALS['_fullname_sql']['full_rev']} AS fullname, username, perms
                          FROM auth_user_md5
                          LEFT JOIN user_info USING (user_id)
                          WHERE perms NOT IN ('root', 'admin')
                            AND (username LIKE CONCAT('%', :needle, '%')
                              OR Vorname LIKE CONCAT('%', :needle, '%')
                              OR Nachname LIKE CONCAT('%', :needle, '%'))
                          LIMIT 500";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':needle', $search_for_founder);
                $statement->execute();
                $results_founders = $statement->fetchGrouped(PDO::FETCH_ASSOC);
            }

            if (is_array($results_founders)) {
                PageLayout::postSuccess(sprintf(
                    ngettext(
                        'Es wurde %s Person gefunden:',
                        'Es wurden %s Personen gefunden:',
                        count($results_founders)
                    ),
                    count($results_founders)
                ));
            } else {
                PageLayout::postInfo(_('Es wurden keine Personen gefunden.'));
            }

            $this->flash['create']                  = true;
            $this->flash['results_choose_founders'] = $results_founders;
            $this->flash['request']                 = Request::getInstance();

            // go to the form again
            $this->redirect('course/studygroup/new/');
        } // add a new founder
        else if ($admin && Request::submitted('add_founder')) {
            $founders = [Request::option('choose_founder')];

            $this->flash['founders'] = $founders;
            $this->flash['create']   = true;
            $this->flash['request']  = Request::getInstance();

            $this->redirect('course/studygroup/new/');
        } // remove a founder
        else if ($admin && Request::submitted('remove_founder')) {
            unset($founders);

            $this->flash['founders'] = $founders;
            $this->flash['create']   = true;
            $this->flash['request']  = Request::getInstance();

            $this->redirect('course/studygroup/new/');
        } // reset search
        else if ($admin && Request::submitted('new_search')) {

            $this->flash['create']  = true;
            $this->flash['request'] = Request::getInstance();

            $this->redirect('course/studygroup/new/');
        } //checks
        else {
            if (!Request::get('groupname')) {
                $errors[] = _("Bitte Gruppennamen angeben");
            } else {
                $query     = "SELECT 1 FROM seminare WHERE name = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    Request::get('groupname'),
                ]);
                if ($statement->fetchColumn()) {
                    $errors[] = _("Eine Veranstaltung/Studiengruppe mit diesem Namen existiert bereits. Bitte wählen Sie einen anderen Namen");
                }
            }

            if (!Request::get('grouptermsofuse_ok')) {
                $errors[] = _("Sie müssen die Nutzungsbedingungen durch Setzen des Häkchens bei 'Einverstanden' akzeptieren.");
            }

            if ($admin && (!is_array($founders) || !sizeof($founders))) {
                $errors[] = _("Sie müssen mindestens einen Gruppengründer eintragen!");
            }

            if (count($errors)) {
                $this->flash['errors']  = $errors;
                $this->flash['create']  = true;
                $this->flash['request'] = Request::getInstance();
                $this->redirect('course/studygroup/new/');
            } else {
                // Everything seems fine, let's create a studygroup

                $sem_types        = studygroup_sem_types();
                $sem              = new Seminar();
                $sem->name        = Request::get('groupname');         // seminar-class quotes itself
                $sem->description = Request::get('groupdescription');  // seminar-class quotes itself
                $sem->status      = $sem_types[0];
                $sem->read_level  = 1;
                $sem->write_level = 1;
                $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
                $mods             = new Modules();
                $bitmask          = 0;
                $sem->visible     = 1;
                if (Request::get('groupaccess') == 'all') {
                    $sem->admission_prelim = 0;
                } else {
                    $sem->admission_prelim = 1;
                    if (Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED && Request::get('groupaccess') == 'invisible') {
                        $sem->visible = 0;
                    }
                    $sem->admission_prelim_txt = _("Die ModeratorInnen der Studiengruppe können Ihren Aufnahmewunsch bestätigen oder ablehnen. Erst nach Bestätigung erhalten Sie vollen Zugriff auf die Gruppe.");
                }
                $sem->admission_binding = 0;

                $this_semester               = SemesterData::getSemesterDataByDate(time());
                $sem->semester_start_time    = $this_semester['beginn'];
                $sem->semester_duration_time = -1;

                if ($admin) {
                    // insert founder(s)
                    foreach ($founders as $user_id) {
                        $stmt = DBManager::get()->prepare("INSERT INTO seminar_user
                            (seminar_id, user_id, status, gruppe)
                            VALUES (?, ?, 'dozent', 8)");
                        $stmt->execute([$sem->id, $user_id]);
                    }

                    $this->founders          = null;
                    $this->flash['founders'] = null;
                } else {
                    $user_id = $GLOBALS['auth']->auth['uid'];
                    // insert dozent
                    $query     = "INSERT INTO seminar_user (seminar_id, user_id, status, gruppe)
                              VALUES (?, ?, 'dozent', 8)";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$sem->id, $user_id]);
                }

                // de-/activate modules
                $mods              = new Modules();
                $admin_mods        = new AdminModules();
                $bitmask           = 0;
                $available_modules = StudygroupModel::getInstalledModules();
                $active_plugins    = Request::getArray('groupplugin');

                foreach ($available_modules as $key => $enable) {
                    $module_name = $sem_class->getSlotModule($key);
                    if ($module_name
                        && ($sem_class->isModuleMandatory($module_name)
                            || !$sem_class->isModuleAllowed($module_name))
                    ) {
                        continue;
                    }
                    if (!$module_name) {
                        $module_name = $key;
                    }

                    if ($active_plugins[$module_name]) {
                        // activate modules
                        $mods->setBit($bitmask, $mods->registered_modules[$key]["id"]);
                        $methodActivate = "module" . ucfirst($key) . "Activate";
                        if (method_exists($admin_mods, $methodActivate)) {
                            $admin_mods->$methodActivate($sem->id);
                        }
                    }
                }
                // always activate participants list
                $mods->setBit($bitmask, $mods->registered_modules["participants"]["id"]);

                $sem->modules = $bitmask;
                $sem->store();

                // de-/activate plugins
                $available_plugins = StudygroupModel::getInstalledPlugins();
                $plugin_manager    = PluginManager::getInstance();

                foreach ($available_plugins as $key => $name) {
                    if (!$sem_class->isModuleAllowed($key)) {
                        continue;
                    }
                    $plugin    = $plugin_manager->getPlugin($key);
                    $plugin_id = $plugin->getPluginId();

                    if ($active_plugins[$key] && $name) {
                        $plugin_manager->setPluginActivated($plugin_id, $sem->id, true);
                    } else {
                        $plugin_manager->setPluginActivated($plugin_id, $sem->id, false);
                    }
                }

                NotificationCenter::postNotification('StudygroupDidCreate', $sem->id);

                // the work is done. let's visit the brand new studygroup.
                $this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $sem->id));

            }
        }
    }

    /**
     * displays a form for editing studygroups with corresponding data
     *
     * @param string id of a studygroup
     *
     * @return void
     */
    public function edit_action()
    {
        global $perm;

        $id = Context::getId();

        PageLayout::setHelpKeyword('Basis.StudiengruppenBearbeiten');

        // if we are permitted to edit the studygroup get some data...
        if ($perm->have_studip_perm('dozent', $id)) {
            $sem = Seminar::getInstance($id);

            PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Studiengruppe bearbeiten'));
            Navigation::activateItem('/course/admin/main');

            $this->sem_id            = $id;
            $this->sem               = $sem;
            $this->sem_class         = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$sem->status]['class']];
            $this->tutors            = $sem->getMembers('tutor');
            $this->available_modules = StudygroupModel::getInstalledModules();
            $this->available_plugins = StudygroupModel::getInstalledPlugins();
            $this->enabled_plugins   = StudygroupModel::getEnabledPlugins($id);
            $this->modules           = new Modules();
            $this->founders          = StudygroupModel::getFounders($id);

            $actions = new ActionsWidget();

            $actions->addLink(_('Neue Studiengruppe anlegen'),
                $this->url_for('course/wizard?studygroup=1'), Icon::create('studygroup+add', 'clickable'));
            if ($GLOBALS['perm']->have_studip_perm('tutor', $id)) {
                $actions->addLink(_('Bild ändern'),
                    $this->url_for('avatar/update/course/' . $id), Icon::create('edit', 'clickable'));
            }
            $actions->addLink(_('Diese Studiengruppe löschen'),
                $this->url_for('course/studygroup/delete/?cid=' . $id), Icon::create('trash', 'clickable'));

            Sidebar::get()->addWidget($actions);
        } // ... otherwise redirect us to the seminar
        else {
            $this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
        }
    }

    /**
     * updates studygroups with respect to the corresponding form data
     *
     * @param string id of a studygroup
     *
     * @return void
     */
    public function update_action()
    {
        global $perm;

        $id = Context::getId();

        // if we are permitted to edit the studygroup get some data...
        if ($perm->have_studip_perm('dozent', $id)) {
            $errors    = [];
            $admin     = $perm->have_studip_perm('admin', $id);
            $founders  = StudygroupModel::getFounders($id);
            $sem       = new Seminar($id);
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$sem->status]['class']];

            CSRFProtection::verifyUnsafeRequest();

            if (Request::submitted('replace_founder')) {

                // retrieve old founder
                $old_dozent = current(StudygroupModel::getFounder($id));

                // remove old founder
                StudygroupModel::promote_user($old_dozent['uname'], $id, 'tutor');

                // add new founder
                $new_founder = Request::option('choose_founder');
                StudygroupModel::promote_user(get_username($new_founder), $id, 'dozent');

                //checks
            } else {
                // test whether we have a group name...
                if (!Request::get('groupname')) {
                    $errors[] = _("Bitte Gruppennamen angeben");
                    //... if so, test if this is not taken by another group
                } else {
                    $query     = "SELECT 1 FROM seminare WHERE name = ? AND Seminar_id != ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([
                        Request::get('groupname'),
                        $id,
                    ]);
                    if ($statement->fetchColumn()) {
                        $errors[] = _("Eine Veranstaltung/Studiengruppe mit diesem Namen existiert bereits. Bitte wählen Sie einen anderen Namen");
                    }
                }
                if (count($errors)) {
                    $this->flash['errors'] = $errors;
                    $this->flash['edit']   = true;
                    // Everything seems fine, let's update the studygroup
                } else {
                    $sem->name        = Request::get('groupname');         // seminar-class quotes itself
                    $sem->description = Request::get('groupdescription');  // seminar-class quotes itself
                    $sem->read_level  = 1;
                    $sem->write_level = 1;
                    $sem->visible     = 1;

                    if (Request::get('groupaccess') == 'all') {
                        $sem->admission_prelim = 0;
                    } else {
                        $sem->admission_prelim = 1;
                        if (Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED && Request::get('groupaccess') == 'invisible') {
                            $sem->visible = 0;
                        }
                        $sem->admission_prelim_txt = _("Die ModeratorInnen der Studiengruppe können Ihren Aufnahmewunsch bestätigen oder ablehnen. Erst nach Bestätigung erhalten Sie vollen Zugriff auf die Gruppe.");
                    }

                    // get the current bitmask
                    $mods       = new Modules();
                    $admin_mods = new AdminModules();
                    $bitmask    = $sem->modules;

                    // de-/activate modules
                    $available_modules = StudygroupModel::getInstalledModules();
                    $orig_modules      = $mods->getLocalModules($sem->id, "sem");
                    $active_plugins    = Request::getArray("groupplugin");

                    foreach (array_keys($available_modules) as $key) {
                        $module_name = $sem_class->getSlotModule($key);
                        if (!$module_name || ($module_name
                                              && ($sem_class->isModuleMandatory($module_name)
                                                  || !$sem_class->isModuleAllowed($module_name)))
                        ) {
                            continue;
                        }
                        if (!$module_name) {
                            $module_name = $key;
                        }
                        if ($active_plugins[$module_name]) {
                            // activate modules
                            $mods->setBit($bitmask, $mods->registered_modules[$key]["id"]);
                            if (!$orig_modules[$key]) {
                                $methodActivate = "module" . ucfirst($key) . "Activate";
                                if (method_exists($admin_mods, $methodActivate)) {
                                    $admin_mods->$methodActivate($sem->id);
                                    $studip_module = $sem_class->getModule($key);
                                    if (is_a($studip_module, "StandardPlugin")) {
                                        PluginManager::getInstance()->setPluginActivated(
                                            $studip_module->getPluginId(),
                                            $id,
                                            true
                                        );
                                    }
                                }
                            }
                        } else {
                            // prepare for deactivation
                            $mods->clearBit($bitmask, $mods->registered_modules[$key]["id"]);
                            if ($orig_modules[$key]) {
                                $methodDeactivate = "module" . ucfirst($key) . "Deactivate";
                                if (method_exists($admin_mods, $methodDeactivate)) {
                                    $admin_mods->$methodDeactivate($sem->id);
                                    $studip_module = $sem_class->getModule($key);
                                    if (is_a($studip_module, "StandardPlugin")) {
                                        PluginManager::getInstance()->setPluginActivated(
                                            $studip_module->getPluginId(),
                                            $id,
                                            false
                                        );
                                    }
                                }
                            }
                        }
                    }
                    $sem->modules = $bitmask;
                    $sem->store();

                    // de-/activate plugins
                    $available_plugins  = StudygroupModel::getInstalledPlugins();
                    $plugin_manager     = PluginManager::getInstance();

                    foreach ($available_plugins as $key => $name) {
                        $plugin    = $plugin_manager->getPlugin($key);
                        $plugin_id = $plugin->getPluginId();
                        if ($active_plugins[$key] && $name && $sem_class->isModuleAllowed($key)) {
                            $plugin_manager->setPluginActivated($plugin_id, $id, true);
                        } else {
                            if ($plugin_manager->isPluginActivated($plugin_id, $id) && !$sem_class->isSlotModule($key)) {
                                $plugin_manager->setPluginActivated($plugin_id, $id, false);
                            }
                        }
                    }
                }
            }
        }

        if (!$this->flash['errors']) {
            // Everything seems fine
            PageLayout::postSuccess(_('Die Änderungen wurden erfolgreich übernommen.'));
        }
        // let's go to the studygroup
        $this->redirect('course/studygroup/edit/?cid=' . $id);
    }


    /**
     * displays a paginated member overview of a studygroup
     *
     * @param string id of a studypgroup
     * @param string page number the current page
     *
     * @return void
     *
     */
    public function members_action()
    {
        $sem = Context::get();
        $id = $sem->id;

        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Teilnehmende'));
        Navigation::activateItem('/course/members');
        PageLayout::setHelpKeyword('Basis.StudiengruppenBenutzer');

        Request::set('choose_member_parameter', $this->flash['choose_member_parameter']);

        object_set_visit_module('participants');


        $this->last_visitdate   = object_get_visit($id, 'participants');
        $this->anzahl           = StudygroupModel::countMembers($id);
        $this->groupname        = $sem->getFullname();
        $this->sem_id           = $id;
        $this->groupdescription = $sem->beschreibung;
        $this->moderators       = $sem->getMembersWithStatus('dozent');
        $this->tutors           = $sem->getMembersWithStatus('tutor');
        $this->autors           = $sem->getMembersWithStatus('autor');
        $this->accepted         = $sem->admission_applicants->findBy('status', 'accepted');
        $this->sem_class        = $sem->getSemClass();
        $this->invitedMembers   = StudygroupModel::getInvitations($id);
        $this->rechte           = $GLOBALS['perm']->have_studip_perm('tutor', $id);

        $this->setupMembersSidebar($sem);
    }

    /**
     *
     */
    private function setupMembersSidebar(Course $course)
    {
        $actions = new ActionsWidget();
        if ($this->rechte) {
            $quoted_id = DBManager::get()->quote($course->id);
            $vis_query = get_vis_query();

            $query = "SELECT auth_user_md5.user_id,
                             {$GLOBALS['_fullname_sql']['full_rev']} AS fullname,
                             username, perms
                      FROM auth_user_md5
                      LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id)
                      LEFT JOIN seminar_user ON (auth_user_md5.user_id = seminar_user.user_id AND seminar_user.Seminar_id = {$quoted_id})
                      WHERE perms  NOT IN ('root', 'admin')
                        AND {$vis_query}
                        AND (username LIKE :input
                          OR CONCAT(Vorname, ' ', Nachname) LIKE :input
                          OR CONCAT(Nachname, ' ', Vorname) LIKE :input
                          OR {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input)
                      ORDER BY fullname ASC";

            $inviting_search = new SQLSearch($query, _('Nutzer suchen'), 'user_id');

            $mp  = MultiPersonSearch::get('studygroup_invite_' . $course->id)
                                    ->setLinkText(_('Neue Gruppenmitglieder einladen'))
                                    ->setLinkIconPath('')
                                    ->setTitle(_('Neue Gruppenmitglieder einladen'))
                                        ->setExecuteURL($this->url_for('course/studygroup/execute_invite/', ['cid' => $course->id, 'view' => $this->view]))
                                        ->setSearchObject($inviting_search)
                                        ->addQuickfilter(_('Adressbuch'), User::findCurrent()->contacts->pluck('user_id'))
                                        ->setNavigationItem('/course/members')
                                        ->render();

            $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
            $actions->addElement($element);
        }

        if ($this->rechte || $course->getSemClass()['studygroup_mode']) {
            $actions->addLink(
                _('Nachricht an alle Gruppenmitglieder verschicken'),
                $this->url_for('course/studygroup/message/?cid=' . $course->id),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => 1]
            );
        }
        if ($actions->hasElements()) {
            Sidebar::get()->addWidget($actions);
        }

        $views = new ViewsWidget();
        $views->addLink(
            _('Galerie'),
            $this->url_for('course/studygroup/members/', ['cid' => $course->id, 'view' => 'gallery'])
        )->setActive($this->view === 'gallery');
        $views->addLink(
            _('Liste'),
            $this->url_for('course/studygroup/members/', ['cid' => $course->id, 'view' => 'list'])
        )->setActive($this->view === 'list');
        Sidebar::get()->addWidget($views);
    }

    /**
     * offers specific member functions wrt perms
     *
     * @param string id of a studypgroup
     * @param string action that has to be performed
     * @param string status if applicable (e.g. tutor)
     *
     * @return void
     */
    public function edit_members_action($action, $status = '')
    {
        global $perm;
        $id = Context::getId();

        $user = Request::get('user');
        $user = preg_replace('/[^\w@\.\-]/', '', $user);

        if ($perm->have_studip_perm('tutor', $id)) {

            if (!$action) {
                PageLayout::postError(_('Es wurde keine korrekte Option gewählt.'));
            } elseif ($action === 'accept') {
                StudygroupModel::accept_user($user, $id);
                PageLayout::postSuccess(sprintf(
                    _('Der Nutzer %s wurde akzeptiert.'),
                    htmlReady(get_fullname_from_uname($user, 'full'))
                ));
            } elseif ($action === 'deny') {
                StudygroupModel::deny_user($user, $id);
                PageLayout::postInfo(sprintf(
                    _('Der Nutzer %s wurde nicht akzeptiert.'),
                    htmLReady(get_fullname_from_uname($user, 'full'))
                ));
            } elseif ($action === 'cancelInvitation') {
                StudygroupModel::cancelInvitation($user, $id);
                PageLayout::postSuccess(sprintf(
                    _('Die Einladung des Nutzers %s wurde gelöscht.'),
                    htmlReady(get_fullname_from_uname($user, 'full'))
                ));
            } elseif ($perm->have_studip_perm('tutor', $id)) {
                if (!$perm->have_studip_perm('dozent', $id, get_userid($user)) || count(Course::find($id)->getMembersWithStatus('dozent')) > 1) {
                    if ($action == 'promote' && $perm->have_studip_perm('dozent', $id)) {
                        $status = $perm->have_studip_perm('tutor', $id, get_userid($user)) ? "dozent" : "tutor";
                        StudygroupModel::promote_user($user, $id, $status);
                        PageLayout::postSuccess(sprintf(
                            _('Der Status des Nutzers %s wurde geändert.'),
                            htmlReady(get_fullname_from_uname($user, 'full'))
                        ));
                    } elseif ($action === "downgrade" && $perm->have_studip_perm('dozent', $id)) {
                        $status = $perm->have_studip_perm('dozent', $id, get_userid($user)) ? "tutor" : "autor";
                        StudygroupModel::promote_user($user, $id, $status);
                        PageLayout::postSuccess(sprintf(
                            _('Der Status des Nutzers %s wurde geändert.'),
                            htmlReady(get_fullname_from_uname($user, 'full'))
                        ));
                    } elseif ($action === 'remove') {
                        PageLayout::postQuestion(
                            sprintf(
                                _('Möchten Sie wirklich den Nutzer %s aus der Studiengruppe entfernen?'),
                                htmlReady(get_fullname_from_uname($user, 'full'))
                            ),
                            $this->url_for("course/studygroup/edit_members/remove_approved", compact('user', 'id'))
                        )->includeTicket();
                    } elseif ($action == 'remove_approved' && check_ticket(Request::get('studip_ticket'))) {
                        StudygroupModel::remove_user($user, $id);
                        PageLayout::postSuccess(sprintf(
                            _('Der Nutzer %s wurde aus der Studiengruppe entfernt.'),
                            htmlReady(get_fullname_from_uname($user, 'full'))
                        ));
                    }
                } else {
                    PageLayout::postError(_('Jede Studiengruppe muss mindestens einen Gruppengründer haben!'));
                }
            }
            //Für die QuickSearch-Suche:
            if (Request::get('choose_member_parameter') && Request::get('choose_member_parameter') !== _("Nutzer suchen")) {
                $this->flash['choose_member_parameter'] = Request::get('choose_member_parameter');
            }
            $this->redirect($this->url_for('course/studygroup/members/', ['cid' => $id, 'view' => $this->view]));
        } else {
            $this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
        }
    }

    /**
     * invites members to a studygroup.
     */
    public function execute_invite_action()
    {
        // Security Check
        global $perm;

        $id = Context::getId();

        if (!$perm->have_studip_perm('tutor', $id)) {
            $this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
            exit;
        }

        // load MultiPersonSearch object
        $mp         = MultiPersonSearch::load("studygroup_invite_" . $id);
        $fail       = false;
        $count      = 0;
        $addedUsers = "";
        foreach ($mp->getAddedUsers() as $receiver) {
            // save invite in database
            StudygroupModel::inviteMember($receiver, $id);
            // send invite message to user
            $msg     = new Messaging();
            $sem     = new Seminar($id);
            $message = sprintf(_("%s möchte Sie auf die Studiengruppe %s aufmerksam machen. Klicken Sie auf den untenstehenden Link, um direkt zur Studiengruppe zu gelangen.\n\n %s"),
                get_fullname(), $sem->name, URLHelper::getlink("dispatch.php/course/studygroup/details/" . $id, ['cid' => null]));
            $subject = _("Sie wurden in eine Studiengruppe eingeladen");
            $msg->insert_message($message, get_username($receiver), '', '', '', '', '', $subject);

            if ($count > 0) {
                $addedUsers .= ", ";
            }
            $addedUsers .= get_fullname($receiver, 'full', true);
            $count++;

        }
        if ($count == 1) {
            PageLayout::postSuccess(sprintf(
                _('%s wurde in die Studiengruppe eingeladen.'),
                $addedUsers
            ));
        } else if ($count >= 1) {
            pageLayout::postSuccess(sprintf(
                _('%s wurden in die Studiengruppe eingeladen.'),
                $addedUsers
            ));
        }

        $this->redirect($this->url_for('course/studygroup/members/', ['cid' => $id, 'view' => $this->view]));
    }

    /**
     * deletes a studygroup
     *
     * @param string id of a studypgroup
     * @param boolean approveDelete
     * @param string studipticket
     *
     * @return void
     *
     */
    public function delete_action($approveDelete = false)
    {
        global $perm;

        $id = Context::getId();

        if ($perm->have_studip_perm('dozent', $id)) {

            if ($approveDelete && check_ticket(Request::get('studip_ticket'))) {
                $messages = [];
                $sem      = new Seminar($id);
                $sem->delete();
                if ($messages = $sem->getStackedMessages()) {
                    $this->flash['messages'] = $messages;
                }
                unset($sem);

                // Weiterleitung auf die "meine Seminare", wenn es kein Admin
                // ist, ansonsten auf die Studiengruppenseite
                if (!$perm->have_perm('root')) {
                    $this->redirect(URLHelper::getURL('dispatch.php/my_courses'));
                } else {
                    $this->redirect(URLHelper::getURL('dispatch.php/studygroup/browse'));
                }
                return;
            } else if (!$approveDelete) {
                PageLayout::postQuestion(
                    _('Sind Sie sicher, dass Sie diese Studiengruppe löschen möchten?'),
                    $this->url_for("course/studygroup/delete/true", ['cid' => $id])
                )->includeTicket();

                $this->redirect('course/studygroup/edit/', ['cid' => $id]);
                return;
            }
        }
        throw new Trails_Exception(401);
    }


    /**
     * Displays admin settings concerning the modules and plugins which that are globally available for studygroups
     *
     * @return void
     */
    public function globalmodules_action()
    {
        global $perm;
        $perm->check("root");
        PageLayout::setHelpKeyword('Admin.Studiengruppen');

        // get institutes
        $institutes   = StudygroupModel::getInstitutes();
        $default_inst = Config::Get()->STUDYGROUP_DEFAULT_INST;

        // Nutzungsbedingungen
        $terms = Config::Get()->STUDYGROUP_TERMS;

        if ($this->flash['institute']) {
            $default_inst = $this->flash['institute'];
        }
        if ($this->flash['modules']) {
            foreach ($this->flash['modules'] as $module => $status) {
                $enabled[$module] = $status === 'on';
            }
        }
        if ($this->flash['terms']) {
            $terms = $this->flash['terms'];
        }

        PageLayout::setTitle(_('Verwaltung studentischer Arbeitsgruppen'));
        Navigation::activateItem('/admin/config/studygroup');

        $query     = "SELECT COUNT(*) FROM seminare WHERE status IN (?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([studygroup_sem_types()]);

        // set variables for view
        $this->can_deactivate = $statement->fetchColumn() == 0;
        $this->current_page   = _("Verwaltung erlaubter Inhaltselemente und Plugins für Studiengruppen");
        $this->configured     = count(studygroup_sem_types()) > 0;
        $this->institutes     = $institutes;
        $this->default_inst   = $default_inst;
        $this->terms          = $terms;
    }

    /**
     * sets the global module and plugin settings for studygroups
     *
     * @return void
     */
    public function savemodules_action()
    {
        global $perm;
        $perm->check("root");
        PageLayout::setHelpKeyword('Admin.Studiengruppen');

        if (!Request::get('institute')) {
            $errors[] = _('Bitte wählen Sie eine Einrichtung aus, der die Studiengruppen zugeordnet werden sollen!');
        }

        if (!trim(Request::get('terms'))) {
            $errors[] = _('Bitte tragen Sie Nutzungsbedingungen ein!');
        }

        if ($errors) {
            $this->flash['messages']  = ['error' => ['title' => 'Die Studiengruppen konnten nicht aktiviert werden!', 'details' => $errors]];
            $this->flash['institute'] = Request::get('institute');
            $this->flash['terms']     = Request::get('terms');
        }

        if (!$errors) {
            $cfg = Config::get();
            if ($cfg->STUDYGROUPS_ENABLE == false && count(studygroup_sem_types()) > 0) {
                $cfg->store("STUDYGROUPS_ENABLE", true);
                PageLayout::postSuccess(_('Die Studiengruppen wurden aktiviert.'));
            }

            if (Request::get('institute')) {
                $cfg->store('STUDYGROUP_DEFAULT_INST', Request::get('institute'));
                $cfg->store('STUDYGROUP_TERMS', Request::get('terms'));
                PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert!'));
            } else {
                PageLayout::postError(_('Fehler beim Speichern der Einstellung!'));
            }
        }
        $this->redirect('course/studygroup/globalmodules');
    }

    /**
     * globally deactivates the studygroups
     *
     * @return void
     */
    public function deactivate_action()
    {
        global $perm;
        $perm->check("root");
        PageLayout::setHelpKeyword('Admin.Studiengruppen');

        $query     = "SELECT COUNT(*) FROM seminare WHERE status IN (?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([studygroup_sem_types()]);

        if (($count = $statement->fetchColumn()) != 0) {
            PageLayout::postError(sprintf(
                _('Sie können die Studiengruppen nicht deaktivieren, da noch %s Studiengruppen vorhanden sind!'),
                $count
            ));
        } else {
            Config::get()->store("STUDYGROUPS_ENABLE", false);
            PageLayout::postSuccess(_('Die Studiengruppen wurden deaktiviert.'));
        }
        $this->redirect('course/studygroup/globalmodules');
    }

    /**
     * sends a message to all members of a studygroup
     *
     * @param string id of a studygroup
     *
     * @return void
     */

    public function message_action()
    {
        $id = Context::getId();
        $sem = Context::get();

        if (mb_strlen($sem->getFullname()) > 32) {//cut subject if to long
            $subject = sprintf(_("[Studiengruppe: %s...]"), mb_substr($sem->getFullname(), 0, 30));
        } else {
            $subject = sprintf(_("[Studiengruppe: %s]"), $sem->getFullname());
        }

        $this->redirect($this->url_for('messages/write', ['course_id' => $id, 'default_subject' => $subject, 'filter' => 'all', 'emailrequest' => 1]));
    }


}
