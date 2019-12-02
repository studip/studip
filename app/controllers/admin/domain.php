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
        if ($action === 'new') {
            $action = 'edit';
        }

        parent::before_filter($action, $args);

        # user must have root permission
        $GLOBALS['perm']->check('root');

        $this->set_sidebar();

        # set page title
        PageLayout::setTitle(_('Verwaltung der Nutzerdomänen'));
        PageLayout::setHelpKeyword('Admins.Nutzerdomaenen');
        Navigation::activateItem('/admin/user/user_domains');
    }

    /**
     * Extracts action and args from a string. Specialized to allow dots
     * in the argument.
     *
     * @param  string       the processed string
     * @return array        an array with two elements - a string containing the
     *                      action and an array of strings representing the args
     */
    public function extract_action_and_args($string) {

      if ('' === $string) {
        return $this->default_action_and_args();
      }

      $args = explode('/', $string);
      $action = array_shift($args);
      return [$action, $args, null];
    }

    /**
     * Validate arguments based on a list of given types. The types are:
     * 'int', 'float', 'option' and 'string'. If the list of types is NULL
     * or shorter than the argument list, 'option' is assumed for all
     * remaining arguments. 'option' differs from Request::option() in
     * that it also accepts the charaters '-' and ',' in addition to all
     * word charaters.
     *
     * @param array   an array of arguments to the action
     * @param array   list of argument types (optional)
     */
    public function validate_args(&$args, $types = NULL)
    {
        foreach ($args as $arg) {
            if (!preg_match('/' . UserDomain::REGEXP . '/', $arg)) {
                throw new Trails_Exception(400);
            }
        }

        reset($args);
    }

    /**
     * Display the list of user domains.
     */
    public function index_action()
    {
        $this->domains = UserDomain::getUserDomains();
    }

    /**
     * Edit an existing user domain.
     */
    public function edit_action($id = null)
    {
        $this->domain = new UserDomain($id);

        if ($id !== null && $this->domain === null) {
            throw new Exception(_('Die zu bearbeitende Domäne konnte nicht gefunden werden.'));
        }
    }

    /**
     * Save changes to a user domain.
     */
    public function save_action($domain_id = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        $id   = Request::get('id');
        $name = Request::get('name');

        if ($id && $name) {
            try {
                $domain = new UserDomain($domain_id ?: $id);
                if (!$domain_id && !$domain->isNew()) {
                    throw new Exception(_('Diese ID wird bereits verwendet'));
                }

                $domain->id                = $id;
                $domain->name              = $name;
                $domain->restricted_access = Request::int('restricted_access', 0);
                $domain->store();

                PageLayout::postSuccess(_('Die Domäne wurde gespeichert'));
            } catch (Exception $ex) {
                PageLayout::postError($ex->getMessage());
            }
        } else {
            PageLayout::postError(_('Sie haben keinen Namen und keine ID angegeben.'));
        }

        $this->redirect('admin/domain');
    }

    /**
     * Delete an existing user domain.
     */
    public function delete_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $domain = new UserDomain($id);

        if (count($domain->getUsers()) == 0) {
            $domain->delete();
        } else {
            PageLayout::postError(_('Domänen, denen noch Personen zugewiesen sind, können nicht gelöscht werden.'));
        }

        $this->redirect('admin/domain');
    }

    /**
     * Get contents of the info box for this action.
     */
    private function set_sidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Neue Nutzerdomäne anlegen'),
            $this->url_for('admin/domain/new'),
            Icon::create('add')
        )->asDialog('size=auto');
    }
}
