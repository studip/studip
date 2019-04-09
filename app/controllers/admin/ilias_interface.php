<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * ilias_interface.php
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
*/
class Admin_IliasInterfaceController extends AuthenticatedController
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

        if (! $GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException(_('Keine Berechtigung zum Verwalten der ILIAS-Schnittstelle.'));
        }

        // check SOAP status
        if (!Config::get()->SOAP_ENABLE) {
            PageLayout::postError(sprintf(_("Das Stud.IP-Modul für die SOAP-Schnittstelle ist nicht aktiviert. Dieses Modul wird für die Nutzung der ILIAS-Schnittstelle benötigt. Ändern Sie den entsprechenden Eintrag in der %sStud.IP-Konfiguration%s."), '<a href="'.$this->url_for('admin/configuration/configuration?needle=SOAP').'">', '</a>'));
        }

        // check if interface is active
        if (!Config::Get()->ILIAS_INTERFACE_ENABLE ) {
            throw new AccessDeniedException(_('Ilias-Interface ist nicht aktiviert.'));
        } else {
            $this->ilias_active = true;
        }

        // get basic settings
        $this->ilias_interface_config = Config::get()->getValue('ILIAS_INTERFACE_BASIC_SETTINGS');
        if (!is_array($this->ilias_interface_config)) {
            throw new AccessDeniedException(_('ILIAS-Grundeinstellungen nicht gefunden.'));
        }
        $this->ilias_interface_moduletitle = Config::get()->getValue('ILIAS_INTERFACE_MODULETITLE');

        // get ILIAS installation settings
        $this->ilias_configs = Config::get()->getValue('ILIAS_INTERFACE_SETTINGS');
        PageLayout::setHelpKeyword('Basis.Ilias');

        $this->modules_available = ConnectedIlias::getSupportedModuleTypes();
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/learnmodule-sidebar.png');
    }

    /**
     * Displays connected ILIAS installations
     */
    public function index_action()
    {
        Navigation::activateItem('admin/config/ilias_interface');

        PageLayout::setTitle(_("Verwaltung der ILIAS-Schnittstelle"));

        $widget = new ActionsWidget();

        $widget->addLink(
                _('Schnittstelle konfigurieren'),
                $this->url_for('admin/ilias_interface/edit_interface_settings'),
                Icon::create('admin', 'clickable'),
                ['data-dialog' => 'size=auto']
                );
        $widget->addLink(
                _('ILIAS-Installation hinzufügen'),
                $this->url_for('admin/ilias_interface/edit_server/new'),
                Icon::create('add', 'clickable'),
                ['data-dialog' => 'size=auto']
                );
        $this->sidebar->addWidget($widget);
    }

    /**
     * edit ILIAS interface basic settings
     */
    public function edit_interface_settings_action()
    {
    }

    /**
     * save ILIAS interface basic settings
     */
    public function save_interface_settings_action()
    {
        if (Request::submitted('submit')) {
            $this->ilias_interface_config['edit_moduletitle'] = (boolean)Request::get('ilias_interface_edit_moduletitle');
            $this->ilias_interface_config['show_offline'] = (boolean)Request::get('ilias_interface_show_offline');
            $this->ilias_interface_config['search_active'] = (boolean)Request::get('ilias_interface_search_active');
            $this->ilias_interface_config['add_statusgroups'] = (boolean)Request::get('ilias_interface_add_statusgroups');
            $this->ilias_interface_config['cache'] = (boolean)Request::get('ilias_interface_cache');
            $this->ilias_interface_config['allow_change_course'] = Request::get('ilias_interface_allow_change_course');
            
            //store config entry
            Config::get()->store('ILIAS_INTERFACE_BASIC_SETTINGS', $this->ilias_interface_config);
            Config::get()->store('ILIAS_INTERFACE_MODULETITLE', Request::quoted('ilias_interface_moduletitle'));
            PageLayout::postSuccess(_('Einstellungen wurden gespeichert.'));
        }
        $this->redirect($this->url_for('admin/ilias_interface'));
    }

    /**
     * add/edit connected ILIAS server settings
     * @param $index Index of ILIAS settings
     */
    public function edit_server_action($index)
    {
        $this->valid_url = false;
        $this->ilias_version = '';
        $this->ilias_version_date = '';
        $this->clients = [];
        if ($index == 'new') {
            // default values
            $this->ilias_config = [
                            'is_active' => false,
                            'name' => '',
                            'version' => '',
                            'url' => _('https://<URL zur ILIAS-Installation>'),
                            'client' => '',
                            'ldap_enable' => '',
                            'admin' => 'ilias_soap_admin',
                            'admin_pw' => '',

                            'root_category_name' => '',
                            'root_category' => '',
                            'user_prefix' => 'studip_',
                            'user_data_category' => '',
                            'allow_change_account' => false,
                            'category_create_on_add_module' => false,
                            'category_to_desktop' => false,
                            'cat_semester' => '',
                            'course_semester' => '',
                            'course_veranstaltungsnummer' => false,
                            'modules' => [],

                            'author_role_name' => 'Author',
                            'author_role' => '',
                            'author_perm' => 'tutor'
            ];

            // fetch existing indicies from previously connected ILIAS installations
            $this->existing_indices = ConnectedIlias::getExistingIndices();

            foreach ($this->ilias_configs as $ilias_index => $ilias_config) {
                unset($this->existing_indices[$ilias_index]);
            }

            // get ILIAS server info
            if (Request::get('ilias_url')) {
                $info = ConnectedIlias::getIliasInfo(Request::get('ilias_url'));
                if (count($info)) {
                    $this->valid_url = true;
                    $this->ilias_config['url'] = Request::get('ilias_url');
                    if ($info['version']) {
                        $this->ilias_version = $info['version'];
                        $this->ilias_version_date = $info['version_date'];
                        $this->ilias_clients = $info['clients'];
                    } else {
                        $this->ilias_version = '';
                    }
                    if (Request::get('ilias_index') != 'new') {
                        // use data from previously connected ILIAS
                        $index = Request::get('ilias_index');
                    } else {
                        // read existing indices
                        $this->existing_indices = ConnectedIlias::getExistingIndices();
                        foreach ($this->ilias_configs as $ilias_index => $ilias_config) {
                            unset($this->existing_indices[$ilias_index]);
                        }
                        // find new unique index
                        $index = 'ilias'.ConnectedIlias::getIntVersion($this->ilias_version);
                        if (is_array($this->ilias_configs[$index]) OR is_array($this->existing_indices[$index])) {
                            $i = 1;
                            while (is_array($this->ilias_configs[$index.'-'.$i]) OR is_array($this->existing_indices[$index.'-'.$i])) {
                                $i++;
                            }
                            $index = $index.'-'.$i;
                        }
                    }
                } else {
                    PageLayout::postError(sprintf(_('Die URL "%s" ist nicht erreichbar.'), htmlReady(Request::get('ilias_url'))));
                }
                if (Request::get('ilias_name') || ! $this->valid_url) {
                    $this->ilias_config['name'] = Request::get('ilias_name');
                } else {
                    $this->valid_url = false;
                    PageLayout::postError(_('Name der Installation darf nicht leer sein.'));
                }
            }
        } else {
            $this->valid_url = true;
            $this->ilias_config = $this->ilias_configs[$index];
            $ldap_options = [];
            foreach (StudipAuthAbstract::GetInstance() as $plugin) {
                if ($plugin instanceof StudipAuthLdap) {
                    $ldap_options[] = '<option '.($plugin->plugin_name == $this->ilias_config['ldap_enable'] ? 'selected' : '').'>' . $plugin->plugin_name . '</option>';
                }
            }
            $this->ldap_options = count($ldap_options) ? join("\n", array_merge(['<option></option>'], $ldap_options)) : '';
            if (Request::get('ilias_name')) {
                $this->ilias_config['name'] = Request::get('ilias_name');
                $this->ilias_config['url'] = Request::get('ilias_url');
            }
            $info = ConnectedIlias::getIliasInfo($this->ilias_config['url']);
            if (count($info)) {
                if ($info['version']) {
                    $this->ilias_version = $info['version'];
                    $this->ilias_version_date = $info['version_date'];
                    $this->ilias_clients = $info['clients'];
                } else {
                    PageLayout::postInfo(_('ILIAS-Version und Clients konnten nicht automatisch erkannt werden.'));
                }
            }
        }
        $this->ilias_index = $index;
    }

    /**
     * edit connected ILIAS content settings
     * @param $index Index of ILIAS settings
     */
    public function edit_content_action($index)
    {
        $this->ilias_config = $this->ilias_configs[$index];
        $this->ilias_index = $index;
    }

    /**
     * edit connected ILIAS permissions settings
     * @param $index Index of ILIAS settings
     */
    public function edit_permissions_action($index)
    {
        $this->ilias_config = $this->ilias_configs[$index];
        $this->ilias_index = $index;
    }

    /**
     * Deletes given ILIAS settings from configuration
     * @param $index Index of ILIAS settings
     */
    public function delete_action($index)
    {
        CSRFProtection::verifyUnsafeRequest();

        unset($this->ilias_configs[$index]);
        Config::get()->store('ILIAS_INTERFACE_SETTINGS', $this->ilias_configs);
        PageLayout::postSuccess(_('ILIAS-Konfiguration wurde entfernt.'));

        $this->redirect($this->url_for('admin/ilias_interface'));
    }

    /**
     * Save connected ILIAS installation settings
     * @param $index Index of ILIAS settings
     */
    public function save_action($index)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::submitted('submit')) {
            // set basic server settings
            if (Request::getInstance()->offsetExists('ilias_name')) {
                $this->ilias_configs[$index]['name'] = Request::get('ilias_name');
                if (Request::getInstance()->offsetExists('ilias_version')) {
                    $this->ilias_configs[$index]['version'] = Request::get('ilias_version');
                }
                $this->ilias_configs[$index]['url'] = Request::get('ilias_url');
                if (Request::getInstance()->offsetExists('ilias_client')) {
                    $this->ilias_configs[$index]['client'] = Request::get('ilias_client');
                }
                if (Request::getInstance()->offsetExists('ilias_ldap_enable')) {
                    $this->ilias_configs[$index]['ldap_enable'] = Request::get('ilias_ldap_enable');
                }
                $this->ilias_configs[$index]['admin'] = Request::get('ilias_admin');
                $this->ilias_configs[$index]['admin_pw'] = Request::get('ilias_admin_pw');

                //store config entry
                Config::get()->store('ILIAS_INTERFACE_SETTINGS', $this->ilias_configs);
                PageLayout::postSuccess(_('ILIAS-Servereinstellungen wurden gespeichert.'));
            }

            // check stored configuration
            $connected_ilias = new ConnectedIlias($index);
            if ($connected_ilias->getConnectionSettingsStatus()) {
                // set content settings
                if (Request::getInstance()->offsetExists('ilias_content_settings')) {
                    if (Request::get('ilias_root_category_name')) {
                        $this->ilias_configs[$index]['root_category_name'] = Request::get('ilias_root_category_name');
                    }
                    if (Request::getInstance()->offsetExists('ilias_user_prefix')) {
                        $this->ilias_configs[$index]['user_prefix'] = Request::get('ilias_user_prefix');
                    }
                    if (Request::getInstance()->offsetExists('ilias_cat_semester')) {
                        $this->ilias_configs[$index]['cat_semester'] = Request::get('ilias_cat_semester');
                    }
                    if (Request::getInstance()->offsetExists('ilias_course_semester')) {
                        $this->ilias_configs[$index]['course_semester'] = Request::get('ilias_course_semester');
                    }
                    if (Request::getInstance()->offsetExists('ilias_course_veranstaltungsnummer')) {
                        $this->ilias_configs[$index]['course_veranstaltungsnummer'] = Request::get('ilias_course_veranstaltungsnummer');
                    }
                    $this->ilias_configs[$index]['category_create_on_add_module'] = Request::get('ilias_category_create_on_add_module');
                    $this->ilias_configs[$index]['category_to_desktop'] = Request::get('ilias_category_to_desktop');
                    foreach ($this->modules_available as $module_index => $module_name) {
                        if (Request::get('ilias_modules_'.$module_index)) {
                            $this->ilias_configs[$index]['modules'][$module_index] = $module_name;
                        }
                    }

                    //store config entry
                    Config::get()->store('ILIAS_INTERFACE_SETTINGS', $this->ilias_configs);
                    PageLayout::postSuccess(_('ILIAS-Inhaltseinstellungen wurden gespeichert.'));

                    //check stored configuration
                    $connected_ilias->loadSettings();
                    $connected_ilias->getContentSettingsStatus();
                }

                // set permissions settings
                if (Request::getInstance()->offsetExists('ilias_author_role_name')) {
                    $this->ilias_configs[$index]['author_role_name'] = Request::get('ilias_author_role_name');
                    $this->ilias_configs[$index]['author_perm'] = Request::get('ilias_author_perm');
                    $this->ilias_configs[$index]['allow_change_account'] = Request::get('ilias_allow_change_account');
                    
                    //store config entry
                    Config::get()->store('ILIAS_INTERFACE_SETTINGS', $this->ilias_configs);
                    PageLayout::postSuccess(_('ILIAS-Berechtigungseinstellungen wurden gespeichert.'));

                    //check stored configuration
                    $connected_ilias->loadSettings();
                    $connected_ilias->getPermissionsSettingsStatus();
                }
            }
        }
        // show error messages
        foreach ($connected_ilias->getError() as $error) {
            PageLayout::postError($error);
        }
        $this->redirect($this->url_for('admin/ilias_interface'));
    }

    /**
     * Activate connected ILIAS installation
     * @param $index Index of ILIAS settings
     */
    public function activate_action($index)
    {
        $this->ilias_configs[$index]['is_active'] = true;

        //check stored configuration
        $connected_ilias = new ConnectedIlias($index);
        if ($connected_ilias->getConnectionSettingsStatus() AND $connected_ilias->getContentSettingsStatus() AND $connected_ilias->getPermissionsSettingsStatus()) {
            //store config entry
            Config::get()->store('ILIAS_INTERFACE_SETTINGS', $this->ilias_configs);
            PageLayout::postSuccess(_('ILIAS-Installation aktiviert.'));
        }

        // show error messages
        foreach ($connected_ilias->getError() as $error) {
            PageLayout::postError($error);
        }

        $this->redirect($this->url_for('admin/ilias_interface'));
    }

    /**
     * Deactivate connected ILIAS installation
     * @param $index Index of ILIAS settings
     */
    public function deactivate_action($index)
    {
        $this->ilias_configs[$index]['is_active'] = false;

        //store config entry
        Config::get()->store('ILIAS_INTERFACE_SETTINGS', $this->ilias_configs);
        PageLayout::postSuccess(_('ILIAS-Installation deaktiviert.'));

        $this->redirect($this->url_for('admin/ilias_interface'));
    }

    /**
     * test soap methods
     * @param $index Index of ILIAS settings
     */
    public function soap_methods_action($index)
    {
        if ($this->ilias_configs[$index]['is_active']) {
            $ilias = new ConnectedIlias($index);
            $this->soap_methods = $ilias->getSoapMethods();
            ksort($this->soap_methods);
            $this->ilias_index = $index;
            if (Request::get('ilias_soap_method')) {
                $this->ilias_soap_method = Request::get('ilias_soap_method');
                foreach ($this->soap_methods[Request::get('ilias_soap_method')] as $param) {
                    switch ($param) {
                        case "sid" : $this->params[$param] = $ilias->soap_client->getSID();
                        break;
                        case "user_id" : $this->params[$param] = $ilias->user->getId();
                        break;
                    }
                }
            } elseif (Request::get('ilias_call')) {
                foreach ($this->soap_methods[Request::get('ilias_call')] as $param) {
                    $params[$param] = Request::get('ilias_soap_param_'.$param);
                }
                $this->result = $ilias->soap_client->call(Request::get('ilias_call'), $params);
            }
        }
    }
}
