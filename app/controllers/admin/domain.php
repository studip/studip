<?php
# Lifter010: TODO

/**
 * domain.php - user domain admin controller
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */
class Admin_DomainController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->set_sidebar();

        # user must have root permission
        $GLOBALS['perm']->check('root');

        # set page title
        PageLayout::setTitle(_('Verwaltung der Nutzerdomänen'));
        PageLayout::setHelpKeyword('Admins.Nutzerdomaenen');
        Navigation::activateItem('/admin/user/user_domains');

        # fetch user domain
        $this->domains = UserDomain::getUserDomains();
    }

    /**
     * Display the list of user domains.
     */
    public function index_action()
    {
    }

    /**
     * Create a new user domain.
     */
    public function new_action()
    {
        $this->render_action('edit');
    }

    /**
     * Edit an existing user domain.
     */
    public function edit_action()
    {
        $this->edit_id = Request::get('id');
    }

    /**
     * Save changes to a user domain.
     */
    function save_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $id   = Request::get('id');
        $name = Request::get('name');

        if ($id && $name) {
            try {
                $domain   = new UserDomain($id);
                $old_name = $domain->getName();

                if (Request::get('new_domain') && isset($old_name)) {
                    throw new Exception(_('Diese ID wird bereits verwendet'));
                }

                $domain->setName($name);
                $domain->store();
            } catch (Exception $ex) {
                PageLayout::postError($ex->getMessage());
            }
        } else {
            PageLayout::postError(_('Sie haben keinen Namen und keine ID angegeben.'));
        }

        $this->domains = UserDomain::getUserDomains();
        $this->render_action('index');
    }

    /**
     * Delete an existing user domain.
     */
    public function delete_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $id     = Request::get('id');
        $domain = new UserDomain($id);

        if (count($domain->getUsers()) == 0) {
            $domain->delete();
        } else {
            PageLayout::postError(_('Domänen, denen noch Personen zugewiesen sind, können nicht gelöscht werden.'));
        }

        $this->domains = UserDomain::getUserDomains();
        $this->render_action('index');
    }

    /**
     * Get contents of the info box for this action.
     */
    private function set_sidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/admin-sidebar.png');
        $actions = new ActionsWidget();
        $actions->addLink(_('Neue Nutzerdomäne anlegen'), $this->url_for('admin/domain/new'), Icon::create('add', 'clickable'));
        $sidebar->addWidget($actions);
    }
}
