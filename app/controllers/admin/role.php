<?php
/**
 * plugin.php - role administration controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Dennis Reil
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */
class Admin_RoleController extends AuthenticatedController
{
    /**
     * Returns the role with the given id.
     * @param string $role_id Id of the role
     * @return Role
     */
    public static function getRole($role_id)
    {
        static $roles = null;
        if ($roles === null) {
            $roles = RolePersistence::getAllRoles();
        }

        return $roles[$role_id];
    }

    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        // set page title and navigation
        PageLayout::setTitle(_('Verwaltung von Rollen'));
        Navigation::activateItem('/admin/config/roles');

        $this->setSidebar($action);
    }

    /**
     * Display a list of all existing roles and some statistics.
     */
    public function index_action()
    {
        $this->roles = RolePersistence::getAllRoles();
        $this->stats = RolePersistence::getStatistics();
    }

    /**
     * Create a new role.
     */
    public function add_action()
    {
        PageLayout::setTitle(_('Neue Rolle anlegen'));

        if (Request::isPost()) {
            $this->check_ticket();

            $name = Request::get('name');
            $name = trim($name);

            if ($name !== '') {
                $role = new Role();
                $role->setRolename($name);
                RolePersistence::saveRole($role);

                $message = sprintf(_('Die Rolle "%s" wurde angelegt.'), htmlReady($name));
            } else {
                $message = _('Sie haben keinen Namen eingegeben.');
            }
            PageLayout::postSuccess($message);

            $this->redirect('admin/role');
        }
    }

    /**
     * Ask for confirmation from the user before deleting a role.
     *
     * @param integer   id of role to delete
     */
    public function ask_remove_role_action($role_id)
    {
        PageLayout::postQuestion(
            sprintf(
                _('Wollen Sie wirklich die Rolle "%s" löschen?'),
                htmlReady(self::getRole($role_id)->getRolename())
            ),
            $this->url_for("admin/role/remove_role/{$role_id}")
        )->includeTicket();

        $this->redirect('admin/role');
    }

    /**
     * Completely delete a role (including all its assignments).
     *
     * @param integer   id of role to delete
     */
    public function remove_role_action($roleid)
    {
        $this->check_ticket();

        $role = self::getRole($roleid);
        RolePersistence::deleteRole($role);

        $message = _('Die Rolle und alle dazugehörigen Zuweisungen wurden gelöscht.');
        PageLayout::postSuccess($message);

        $this->redirect('admin/role');
    }

    /**
     * Search for users containing the given string in either
     * first name, last name oder user name.
     *
     * @param string    text to match agaist
     *
     * @return array    list of User objects
     */
    private function search_user($searchtxt)
    {
        $searchtxt = "%{$searchtxt}%";

        $condition = "username LIKE ?
                      OR CONCAT(Nachname, ', ', Vorname, ' ', Nachname) LIKE ?
                      ORDER BY Vorname, Nachname, username";
        return User::findBySQL($condition, [$searchtxt, $searchtxt]);
    }

    /**
     * Display all roles assigned to a particular user.
     *
     * @param string    user id (optional)
     */
    public function assign_role_action($userid = NULL)
    {
        $usersel = Request::option('usersel', $userid);

        if (isset($usersel) && Request::isPost()) {
            $this->redirect("admin/role/assign_role/{$usersel}");
            return;
        }

        // user search was started
        if (Request::submitted('search')) {
            $username = Request::get('username');

            if ($username == '') {
                $this->error = _('Es wurde kein Suchwort eingegeben.');
            } else {
                $this->users = $this->search_user($username);

                if (count($this->users) === 0) {
                    $this->error = _('Es wurde keine Person gefunden.');
                    $this->username = $username;
                }
            }
        }

        // a user was selected
        if (isset($usersel)) {
            $this->users[$usersel] = User::find($usersel);
            $this->currentuser = $this->users[$usersel];
            $this->assignedroles = $this->currentuser->getRoles();
            $this->all_userroles = $this->currentuser->getRoles(true);
            $this->roles = RolePersistence::getAllRoles();
            foreach ($this->assignedroles as $role) {
                $institutes = SimpleCollection::createFromArray(Institute::findMany(RolePersistence::getAssignedRoleInstitutes($usersel, $role->getRoleid())));
                $this->assignedroles_institutes[$role->getRoleid()] = $institutes->orderBy('name')->pluck('name');
            }
        }
    }

    /**
     * Change the roles assigned to a particular user.
     *
     * @param string    user id
     */
    public function save_role_action($userid)
    {
        $selecteduser = User::find($userid);

        $this->check_ticket();

        if (Request::submitted('assign_role')) {
            // assign roles
            foreach (Request::intArray('rolesel') as $selroleid) {
                $role = self::getRole($selroleid);
                RolePersistence::assignRole($selecteduser, $role);
            }
        } else if (Request::submitted('remove_role')) {
            // delete role assignment
            foreach (Request::intArray('assignedroles') as $roleid) {
                $role = self::getRole($roleid);
                RolePersistence::deleteRoleAssignment($selecteduser, $role);
            }
        }

        $message = _('Die Rollenzuweisungen wurden gespeichert.');
        PageLayout::postSuccess($message);

        $this->redirect('admin/role/assign_role/'.$userid);
    }

    /**
     * Display all roles assigned to a particular plugin.
     *
     * @param integer   plugin id (optional)
     */
    public function assign_plugin_role_action($pluginid = NULL)
    {
        $pluginid = Request::int('pluginid', $pluginid);

        $this->plugins = PluginManager::getInstance()->getPluginInfos();
        $this->assigned = RolePersistence::getAssignedPluginRoles($pluginid);
        $this->roles = RolePersistence::getAllRoles();
        $this->pluginid = $pluginid;
    }

    /**
     * Change the roles assigned to a particular plugin.
     *
     * @param integer   plugin id
     */
    public function save_plugin_role_action($pluginid)
    {
        $this->check_ticket();

        if (Request::submitted('assign_role')) {
            // assign roles
            $selroles = Request::intArray('rolesel');
            RolePersistence::assignPluginRoles($pluginid, $selroles);
        } else if (Request::submitted('remove_role')) {
            // delete role assignment
            $delassignedrols = Request::intArray('assignedroles');
            RolePersistence::deleteAssignedPluginRoles($pluginid, $delassignedrols);
        }

        $message = _('Die Rechteeinstellungen wurden gespeichert.');
        PageLayout::postSuccess($message);

        $this->redirect("admin/role/assign_plugin_role/{$pluginid}");
    }

    /**
     * Check role access permission for the given plugin.
     *
     * @param array     plugin meta data
     * @param integer   role id of role
     */
    private function check_role_access($plugin, $role_id)
    {
        $plugin_roles = RolePersistence::getAssignedPluginRoles($plugin['id']);

        foreach ($plugin_roles as $plugin_role) {
            if ($plugin_role->getRoleid() == $role_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display a list of user and role assignments for a given role.
     *
     * @param integer   role id (optional)
     */
    public function show_role_action($roleid = null)
    {
        $roleid = Request::int('role', $roleid);

        $this->roles = RolePersistence::getAllRoles();

        if ($roleid) {
            $sql = "SELECT DISTINCT Vorname,Nachname,user_id,username,perms
                    FROM auth_user_md5
                    JOIN roles_user ON userid = user_id
                    WHERE roleid = ?
                    ORDER BY Nachname, Vorname";
            $statement = DBManager::get()->prepare($sql);
            $statement->execute([$roleid]);

            $users = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as $key => $user) {
                $institutes = new SimpleCollection(Institute::findMany(RolePersistence::getAssignedRoleInstitutes($user['user_id'], $roleid)));
                $users[$key]['institutes'] = $institutes->orderBy('name')->pluck('name');
            }

            $plugins = PluginManager::getInstance()->getPluginInfos();
            foreach ($plugins as $id => $plugin) {
                if (!$this->check_role_access($plugin, $roleid)) {
                    unset($plugins[$id]);
                }
            }

            $this->implicit_count = RolePersistence::countImplicitUsers($roleid);

            $this->users   = $users;
            $this->plugins = $plugins;
            $this->role    = self::getRole($roleid);
            $this->roleid  = $roleid;

            $this->mps = $this->getMultiPersonSearch($roleid);
        }
    }

    /**
     *
     */
    public function add_user_action($role_id, $user_id)
    {
        $role = self::getRole($role_id);
        $ids  = $this->getUsers($role_id, $user_id);
        $users = User::findMany($ids);

        foreach ($users as $user) {
            RolePersistence::assignRole($user, $role);
        }

        $template = ngettext(
            'Der Rolle wurde eine weitere Person hinzugefügt.',
            'Der Rolle wurden %u weitere Personen hinzugefügt.',
            count($ids)
        );
        $message = sprintf($template, count($ids));
        PageLayout::postSuccess($message);

        $this->redirect("admin/role/show_role/{$role_id}");
    }

    /**
     *
     */
    public function remove_user_action($role_id, $user_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        $role  = self::getRole($role_id);
        $ids   = $this->getUsers($role_id, $user_id);
        $users = User::findMany($ids);

        foreach ($users as $user) {
            RolePersistence::deleteRoleAssignment($user, $role);
        }

        $template = ngettext(
            'Einer Person wurde die Rolle entzogen.',
            '%u Personen wurde die Rolle entzogen.',
            count($ids)
        );
        $message = sprintf($template, count($ids));
        PageLayout::postSuccess($message);

        $this->redirect("admin/role/show_role/{$role_id}");
    }

    /**
     *
     */
    public function add_plugin_action($role_id)
    {
        PageLayout::setTitle(_('Plugins zur Rolle hinzufügen'));

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $plugin_ids = Request::intArray('plugin_ids');

            if (count($plugin_ids) > 0) {
                foreach ($plugin_ids as $id) {
                    RolePersistence::assignPluginRoles($id, [$role_id]);
                }

                $template = ngettext(
                    'Der Rolle wurde ein weiteres Plugin hinzugefügt.',
                    'Der Rolle wurden %u weitere Plugins hinzugefügt.',
                    count($plugin_ids)
                );
                $message = sprintf($template, count($plugin_ids));
                PageLayout::postSuccess($message);
            }

            $this->redirect("admin/role/show_role/{$role_id}");
        }

        $this->role_id = $role_id;

        $plugins    = PluginManager::getInstance()->getPluginInfos();
        $controller = $this;

        $this->plugins = array_filter($plugins, function ($plugin) use ($controller, $role_id) {
            return !$controller->check_role_access($plugin, $role_id);
        });
    }

    /**
     *
     */
    public function remove_plugin_action($role_id, $plugin_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        $role = self::getRole($role_id);
        $ids  = $this->getPlugins($role_id, $plugin_id);

        foreach ($ids as $id) {
            RolePersistence::deleteAssignedPluginRoles($id, [$role_id]);
        }

        $template = ngettext(
            'Einem Plugin wurde die Rolle entzogen.',
            '%u Plugins wurde die Rolle entzogen.',
            count($ids)
        );
        $message = sprintf($template, count($ids));
        PageLayout::postSuccess($message);

        $this->redirect("admin/role/show_role/{$role_id}");
    }

    /**
     *
     */
    private function getUsers($role_id, $user_id)
    {
        // From form
        if (Request::getInstance()->offsetExists('ids')) {
            return Request::optionArray('ids');
        }

        // From multi person search
        if ($user_id === 'bulk') {
            return $this->getMultiPersonSearch($role_id)->getAddedUsers();
        }

        // From url
        return [$user_id];
    }

    /**
     *
     */
    private function getPlugins($role_id, $plugin_id)
    {
        // From form
        if (Request::getInstance()->offsetExists('ids')) {
            return Request::optionArray('ids');
        }

        // From url
        return [$plugin_id];
    }

    /**
     *
     */
    protected function getMultiPersonSearch($role_id)
    {
        // Multiperson search
        $query = "SELECT aum.user_id,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname,
                         username, perms
                  FROM auth_user_md5 AS aum
                  LEFT JOIN user_info AS ui ON (aum.user_id = ui.user_id)
                  LEFT JOIN roles_user AS ru ON (aum.user_id = ru.userid AND ru.roleid = {$role_id})
                  WHERE ru.userid IS NULL
                     AND (
                         username LIKE :input
                         OR CONCAT(Vorname, ' ', Nachname) LIKE :input
                         OR CONCAT(Nachname, ' ', Vorname) LIKE :input
                         OR {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input
                     )
                  ORDER BY fullname ASC";
        $url = URLHelper::getURL('dispatch.php/admin/role/add_user/' . $role_id . '/bulk');
        return MultiPersonSearch::get('add_role_users')
            ->setLinkText(_('Personen hinzufügen'))
            ->setTitle(_('Personen zur Rolle hinzufügen'))
            ->setExecuteURL($url)
            ->setSearchObject(new SQLSearch($query, _('Nutzer suchen'), 'user_id'));
    }

    public function assign_role_institutes_action($role_id, $user_id)
    {
        if (Request::submitted('add_institute') && $institut_id = Request::option('institute_id')) {
            $roles = RolePersistence::getAllRoles();
            $role = $roles[$role_id];
            $user = User::find($user_id);
            RolePersistence::assignRole($user, $role, Request::option('institute_id'));
            PageLayout::postSuccess(_('Die Einrichtung wurde zugewiesen.'));
        }
        if ($remove_institut_id = Request::option('remove_institute')) {
            $roles = RolePersistence::getAllRoles();
            $role = $roles[$role_id];
            $user = User::find($user_id);
            RolePersistence::deleteRoleAssignment($user, $role, $remove_institut_id);
            PageLayout::postSuccess(_('Die Einrichtung wurde entfernt.'));

        }
        $roles = RolePersistence::getAllRoles();
        $this->role = $roles[$role_id];
        $this->user = new User($user_id);
        $this->institutes = SimpleCollection::createFromArray(Institute::findMany(RolePersistence::getAssignedRoleInstitutes($user_id, $role_id)));
        $this->institutes->orderBy('name');
        $this->qsearch = QuickSearch::get('institute_id', new StandardSearch('Institut_id'));

        if (Request::isXhr()) {
            $this->qsearch->withoutButton();
        }
    }

    private function setSidebar($action)
    {
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(PageLayout::getTitle() ?: _('Rollen'));
        $sidebar->setImage('sidebar/roles-sidebar.png');

        $views = $sidebar->addWidget(new ViewsWidget());
        $views->addLink(
            _('Rollen verwalten'),
            $this->url_for('admin/role')
        )->setActive($action === 'index');
        $views->addLink(
            _('Personenzuweisungen bearbeiten'),
            $this->url_for('admin/role/assign_role')
        )->setActive($action === 'assign_role');
        $views->addLink(
            _('Pluginzuweisungen bearbeiten'),
            $this->url_for('admin/role/assign_plugin_role')
        )->setActive($action === 'assign_plugin_role');
        $views->addLink(
            _('Rollenzuweisungen anzeigen'),
            $this->url_for('admin/role/show_role')
        )->setActive($action === 'show_role');

        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Neue Rolle anlegen'),
            $this->url_for('admin/role/add'),
            Icon::create('add', 'clickable')
        )->asDialog('size=auto');
    }

    /**
     * Validate ticket (passed via request environment).
     * This method always checks Request::quoted('ticket').
     *
     * @throws InvalidArgumentException  if ticket is not valid
     */
    private function check_ticket()
    {
        if (!check_ticket(Request::option('studip_ticket'))) {
            throw new InvalidArgumentException(_('Das Ticket für diese Aktion ist ungültig.'));
        }

    }
}
