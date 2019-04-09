<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * my_ilias_accounts.php
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
class MyIliasAccountsController extends AuthenticatedController
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

        if (!Config::Get()->ILIAS_INTERFACE_ENABLE ) {
            throw new AccessDeniedException(_('Ilias-Interface ist nicht aktiviert.'));
        } else
            $this->ilias_active = true;

            PageLayout::setHelpKeyword('Basis.Ilias');

            $this->sidebar = Sidebar::get();
            $this->sidebar->setImage('sidebar/learnmodule-sidebar.png');
    }

    /**
     * Displays accounts and ilias_interface modules for active user
     */
    public function index_action()
    {
        Navigation::activateItem('/tools/my_ilias_accounts');

        PageLayout::setTitle(_("Meine Lernobjekte und ILIAS-Accounts"));

        $this->ilias_list = [];
        foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
            if ($ilias_config['is_active']) {
                $this->ilias_list[$ilias_index] = new ConnectedIlias($ilias_index);
                $this->ilias_list[$ilias_index]->soap_client->clearCache();
            }
        }

        $widget = new ActionsWidget();
        foreach($this->ilias_list as $ilias_list_index => $ilias) {
            if ($GLOBALS['perm']->have_perm('autor')) {
                $widget->addLink(
                        sprintf(_('Zur %s-Startseite'), $ilias->getName()),
                        $this->url_for('my_ilias_accounts/redirect/'.$ilias_list_index.'/login'),
                        Icon::create('link-extern', 'clickable'),
                        ['target' => '_blank', 'rel' => 'noopener noreferrer']
                        );
            }
        }
        $this->sidebar->addWidget($widget);
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
            $modules = $this->ilias->getUserModules();
            $this->module = $modules[$module_id];
            PageLayout::setTitle($this->module->getTitle());
            $this->ilias_index = $index;
        } else {
            PageLayout::postError(_("Diese ILIAS-Installation ist nicht aktiv."));
        }
    }

    /**
     * Add module to ILIAS installation
     * @param $index Index of ILIAS installation
     */
    public function add_object_action($index)
    {
        $this->ilias = new ConnectedIlias($index);
        if ($this->ilias->isActive()) {
            $this->ilias_ref_id = $this->ilias->user->getCategory();
            $this->ilias_index = $index;
        } else {
            PageLayout::postError(_("Diese ILIAS-Installation ist nicht aktiv."));
        }
    }

    /**
     * Set new account for ILIAS installation
     * @param $index Index of ILIAS installation
     */
    public function new_account_action($index)
    {
        $ilias_configs = Config::get()->ILIAS_INTERFACE_SETTINGS;
        if ($ilias_configs[$index]['is_active']) {
            $this->ilias = new ConnectedIlias($index);
            $this->ilias_index = $index;
        }
    }

    /**
     * Change/update account for ILIAS installation
     * @param $index Index of ILIAS installation
     */
    public function change_account_action($index, $mode)
    {
        $ilias_configs = Config::get()->ILIAS_INTERFACE_SETTINGS;
        if ($ilias_configs[$index]['is_active']) {
            $this->ilias = new ConnectedIlias($index);
            $this->ilias_index = $index;
            switch ($mode) {
                case 'update' : 
                    // update user account
                    if ($this->ilias->updateUser($GLOBALS['user'])) {
                        PageLayout::postSuccess(_("ILIAS-Account aktualisiert."));
                    }
                    break;
                case 'add' : 
                    // set new user account
                    if ($this->ilias->soap_client->checkPassword(Request::get('ilias_login'), Request::get('ilias_password'))) {
                        // login data valid
                        $user_id = $this->ilias->soap_client->lookupUser(Request::get('ilias_login'));
                        if ($user_id) {
                            $this->ilias->user->setUsername(Request::get('ilias_login'));
                            $this->ilias->user->setPassword('');
                            $this->ilias->user->setId($user_id);
                            $this->ilias->user->setConnection(IliasUser::USER_TYPE_ORIGINAL);
                            PageLayout::postSuccess(_("ILIAS-Account zugeordnet."));
                            $this->ilias->soap_client->clearCache();
                        }
                    } else {
                        // wrong login
                        PageLayout::postError(_("Login fehlgeschlagen. Die Zuordnung konnte nicht geändert werden."));
                    }
                    break;
                case 'remove' : 
                    $this->ilias->user->unsetConnection();
                    PageLayout::postSuccess(_("Account-Zuordnung entfernt."));
                    break;
            }
        }
        $this->redirect($this->url_for('my_ilias_accounts/index'));
    }

    /**
     * Redirect to ILIAS installation
     * @param $index Index of ILIAS installation
     */
    public function redirect_action($index, $target, $module_id = '', $module_type = '')
    {
        $ilias_configs = Config::get()->ILIAS_INTERFACE_SETTINGS;
        if ($ilias_configs[$index]['is_active']) {
            $this->ilias = new ConnectedIlias($index);
            $token = $this->ilias->user->getToken();
            $session_id = $this->ilias->soap_client->loginUser($this->ilias->user->getUsername(), $token);
            if ($this->ilias->ilias_config['category_create_on_add_module'] && ! $module_id) {
                $this->ilias->newUserCategory();
                $module_id = $this->ilias->user->category;
            }
            // display error message if session is invalid
            if (!$session_id) {
                PageLayout::postError(sprintf(_("Automatischer Login für %s-Installation (Nutzername %s) fehlgeschlagen."),
                        htmlReady($this->ilias->getName()),
                        htmlReady($this->ilias->user->getUsername())));
            } elseif (($target == 'new') AND ! $module_id) {
                PageLayout::postError(sprintf(_("Keine Kategorie zum Anlegen neuer Lernobjekte in der %s-Installation vorhanden."),
                        htmlReady($this->ilias->getName())));
            } else {
                // remove client id from session id
                $session_array = explode("::", $session_id);
                $session_id = $session_array[0];

                if (Request::get('ilias_module_type')) $module_type = Request::get('ilias_module_type');

                // build target link
                $parameters = '?sess_id='.$session_id;
                if (!empty($this->ilias->getClientId())) {
                    $parameters .= "&client_id=".$this->ilias->getClientId();
                    if ($target) {
                        $parameters .= "&target=".$target;
                    }
                    if ($module_id) {
                        $parameters .= "&ref_id=".$module_id;
                    }
                    if ($module_type) {
                        $parameters .= "&type=".$module_type;
                    }

                    // refer to ILIAS target file
                    header("Location: ". $this->ilias->getTargetFile() . $parameters);
                    $this->render_nothing();
                }
            }
        }
    }
}