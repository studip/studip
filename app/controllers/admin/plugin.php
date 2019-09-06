<?php
# Lifter010: TODO
/**
 * plugin.php - plugin administration controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

class Admin_PluginController extends AuthenticatedController
{
    private $plugin_admin;

    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        // user must have root permission
        $perm->check('root');

        // set page title and navigation
        PageLayout::setTitle(_('Verwaltung von Plugins'));
        Navigation::activateItem('/admin/config/plugins');

        $this->plugin_admin = new PluginAdministration();

        if (Request::int('reset_filter')) {
            $GLOBALS['user']->cfg->delete('PLUGINADMIN_DISPLAY_SETTINGS');
        }
        // Extract display settings
        $settings = $current = $GLOBALS['user']->cfg->PLUGINADMIN_DISPLAY_SETTINGS;

        foreach ((array)$settings as $key => $value) {
            $settings[$key] = Request::option($key, $settings[$key]) ?: null;
        }

        if ($settings !== $current) {
            $GLOBALS['user']->cfg->store('PLUGINADMIN_DISPLAY_SETTINGS', $settings);
        }

        $this->plugin_filter = $settings['plugin_filter'];
        $this->core_filter   = $settings['core_filter'];

        $views = Sidebar::get()->addWidget(new ViewsWidget());
        $views->addLink(
            _('Pluginverwaltung'),
            $this->indexURL(),
            Icon::create('plugin')
        )->setActive($action === 'index');
        $views->addLink(
            _('Weitere Plugins installieren'),
            $this->searchURL(),
            Icon::create('search')
        )->setActive($action === 'search');
        $views->addLink(
            _('Vorhandene Plugins registrieren'),
            $this->unregisteredURL(),
            Icon::create('plugin+add')
        )->setActive($action === 'unregistered');
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

    /**
     * Try to get update information for a list of plugins. If no
     * update information is available, an error message is set in
     * this controller and an empty array is returned.
     *
     * @param array     array of plugin meta data
     */
    private function get_update_info($plugins)
    {
        try {
            return $this->plugin_admin->getUpdateInfo($plugins);
        } catch (Exception $ex) {
            PageLayout::postError(
                _('Informationen über Plugin-Updates sind nicht verfügbar.'),
                [$ex->getMessage()]
            );

            // Read current information from local files
            $update_info = [];
            $plugin_manager = PluginManager::getInstance();
            foreach ($plugins as $plugin) {
                $plugin_path = get_config('PLUGINS_PATH') . '/' . $plugin['path'];
                $manifest    = $plugin_manager->getPluginManifest($plugin_path);
                $update_info[$plugin['id']] = ['version' => $manifest['version']];
            }
            return $update_info;
        }
    }

    /**
     * Display the list of installed plugins and show all available
     * updates (if any).
     */
    public function index_action()
    {
        // Check if an activation error has been flashed from the last request
        if (isset($this->flash['activation-error'])) {
            PageLayout::postError(
                $this->get_template_factory()->render(
                    'admin/plugin/activation-error-form.php',
                    $this->flash['activation-error'] + ['controller' => $this]
                )
            );
        }

        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');

        $plugins = $plugin_manager->getPluginInfos($this->plugin_filter);

        if ($this->core_filter && $this->core_filter !== 'yes') {
            $plugins = array_filter($plugins, function ($plugin) {
                return ($this->core_filter === 'no' && !$plugin['core'])
                    || ($this->core_filter === 'only' && $plugin['core']);
            });
        }

        $this->plugins       = $plugins;
        $this->plugin_types  = $this->plugin_admin->getPluginTypes();
        $this->update_info   = $this->get_update_info($this->plugins);
        $this->migrations    = $this->plugin_admin->getMigrationInfo();

        foreach ($this->update_info as $id => $info) {
            if (isset($info['update']) && !$this->plugins[$id]['depends']) {
                $this->num_updates += 1;
            }
        }
    }

    /**
     * Save the modified plugin configuration (status and position).
     */
    public function save_action()
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');
        $type = $plugin_filter != '' ? $plugin_filter : NULL;
        $plugins = $plugin_manager->getPluginInfos($type);

        $force = (bool) Request::int('force');

        $this->check_ticket();

        // update enabled/disabled status and position if set
        $messages = [];
        $errors   = [];
        $memory   = [];
        foreach ($plugins as $plugin){
            // Skip plugins that are currently not visible due to filter settings
            if (!Request::submittedSome('position_' . $plugin['id'], 'enabled_' . $plugin['id'])) {
                continue;
            }

            $enabled = Request::int('enabled_' . $plugin['id'], 0);
            $navpos = Request::int('position_' . $plugin['id']);

            $result = $plugin_manager->setPluginEnabled($plugin['id'], $enabled, $force);
            if ($result === false) {
                $error = $enabled
                       ? _('Plugin "%s" hat die Aktivierung verhindert')
                       : _('Plugin "%s" hat die Deaktivierung verhindert');
                $errors[$plugin['id']] = sprintf($error, $plugin['name']);

                $memory[$plugin['id']] = $enabled;
            } elseif ($result === true) {
                $message = $enabled
                         ? _('Plugin "%s" wurde aktiviert')
                         : _('Plugin "%s" wurde deaktiviert');
                $messages[] = sprintf($message, $plugin['name']);
            }

            if (isset($navpos)) {
                $result = $plugin_manager->setPluginPosition($plugin['id'], max($navpos, 1));
                if ($result) {
                    $messages[] = sprintf(
                        _('Die Position von Plugin "%s" wurde verändert.'),
                        $plugin['name']
                    );
                    $changed = true;
                }
            }
        }

        if (count($errors) > 0) {
            // Unfortunately, we need to flash this since it needs a fresh
            // ticket (the current one is invalid due to the redirection)
            $this->flash['activation-error'] = compact('memory', 'errors');
        }
        if (count($messages) > 0) {
            PageLayout::postSuccess(
                _('Die folgenden Änderungen wurden durchgeführt:'),
                array_map('htmlReady', $messages)
            );
        }
        $this->redirect('admin/plugin?plugin_filter=' . $plugin_filter);
    }

    /**
     * Compare two plugins by their score (used for sorting).
     */
    private function compare_score($plugin1, $plugin2)
    {
        return $plugin2['score'] - $plugin1['score'];
    }

    /**
     * Search the list of available plugins or display the most
     * recommended plugins if the user did not trigger a search.
     */
    public function search_action()
    {
        Helpbar::Get()->addPlainText(_('Empfohlene Plugins'), _('In der Liste "Empfohlene Plugins" finden Sie von anderen Betreibern empfohlene Plugins.'), Icon::create('info'));
        Helpbar::Get()->addPlainText(_('Upload'), _('Alternativ können Plugins und Plugin-Updates auch als ZIP-Datei hochgeladen werden.'), Icon::create('info'));

        $search = Request::int('reset-search')
                ? null
                : Request::get('search');

        // search for plugins in all repositories
        try {
            $repository = new PluginRepository();
            $search_results = $repository->getPlugins($search);
        } catch (Exception $ex) {
            $search_results = [];
        }

        $plugins = PluginManager::getInstance()->getPluginInfos();

        // filter out already installed plugins
        foreach ($plugins as $plugin) {
            if (isset($search_results[$plugin['name']])) {
                unset($search_results[$plugin['name']]);
            }
        }

        if ($search === null) {
            // sort plugins by score
            uasort($search_results, [$this, 'compare_score']);
            $search_results = array_slice($search_results, 0, 6);
        } else {
            // sort plugins by name
            uksort($search_results, 'strnatcasecmp');
        }

        $this->search         = $search;
        $this->search_results = $search_results;
        $this->plugins        = $plugins;

        $search_widget = Sidebar::get()->addWidget(new SearchWidget());
        $search_widget->setTitle(_('Plugins suchen'));
        $search_widget->addNeedle(_('Pluginname'), 'search', true, null, null, $search);

        $links = Sidebar::get()->addWidget(new LinksWidget());
        $links->setTitle(_('Verweise'));
        $links->addLink(
            _('Alle Plugins im Plugin-Marktplatz'),
            'http://plugins.studip.de/',
            Icon::create('export'),
            ['target' => '_blank', 'rel' => 'noopener noreferrer']
        );
    }

    /**
     * Install a given plugin, either by URL (from the repository)
     * or using a file uploaded by the administrator.
     */
    public function install_action()
    {
        $this->check_ticket();

        $plugin_manager = PluginManager::getInstance();
        $this->flash['plugins_disabled'] = $plugin_manager->isPluginsDisabled();
        $this->flash['plugin_url'] = Request::get('plugin_url');

        if (isset($_FILES['upload_file'])) {
            $upload_file = tempnam(get_config('TMP_PATH'), 'plugin');

            if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $upload_file)) {
                $this->flash['upload_file'] = $upload_file;
            }
        }

        $plugin_manager->setPluginsDisabled(true);
        $this->redirect('admin/plugin/internal_install');
    }

    /**
     * Install a given plugin, either by URL (from the repository)
     * or using a file uploaded by the administrator.
     * Note: This action is only called internally via redirect.
     */
    public function internal_install_action()
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_manager->setPluginsDisabled($this->flash['plugins_disabled']);

        $plugin_url = $this->flash['plugin_url'];

        try {
            if (isset($plugin_url)) {
                $this->plugin_admin->installPluginFromURL($plugin_url);
            } else if (get_config('PLUGINS_UPLOAD_ENABLE')) {
                // process the upload and register plugin in the database
                $upload_file = $this->flash['upload_file'];
                $this->plugin_admin->installPlugin($upload_file);
            }

            PageLayout::postSuccess(_('Das Plugin wurde erfolgreich installiert.'));
        } catch (PluginInstallationException $ex) {
            PageLayout::postError($ex->getMessage());
        }

        if (isset($upload_file)) {
            unlink($upload_file);
        }

        $this->redirect('admin/plugin');
    }

    /**
     * Ask for confirmation from the user before deleting a plugin.
     *
     * @param integer   id of plugin to delete
     */
    public function ask_delete_action($plugin_id)
    {
        $plugin = PluginManager::getInstance()->getPluginInfoById($plugin_id);

        if (!$plugin['core']) {
            PageLayout::postQuestion(
                sprintf(
                    _('Wollen Sie wirklich "%s" deinstallieren?'),
                    htmlReady($plugin['name'])
                ),
                $this->url_for("admin/plugin/delete/{$plugin_id}")
            )->includeTicket();
        }

        $this->redirect('admin/plugin');
    }

    /**
     * Completely delete a plugin from the system.
     *
     * @param integer   id of plugin to delete
     */
    public function delete_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);

        $this->check_ticket();

        if (isset($plugin) && !$plugin['core']) {
            $this->plugin_admin->uninstallPlugin($plugin);
            PageLayout::postSuccess(sprintf(
                _('Das Plugin "%s" wurde deinstalliert.'),
                $plugin['name']
            ));
        }

        $this->redirect('admin/plugin?plugin_filter=' . $plugin_filter);
    }

    /**
     * Download a ZIP file containing the given plugin.
     *
     * @param integer   id of plugin to download
     */
    public function download_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);

        // prepare file name for download
        $pluginpath = get_config('PLUGINS_PATH') . '/' . $plugin['path'];
        $manifest = $plugin_manager->getPluginManifest($pluginpath);
        $filename = $plugin['class'] . '-' . $manifest['version'] . '.zip';
        $filepath = get_config('TMP_PATH') . '/' . $filename;

        FileArchiveManager::createArchiveFromPhysicalFolder(
            $pluginpath,
            $filepath
        );

        $this->render_temporary_file($filepath, $filename, 'application/zip');
    }

    /**
     * Install updates for all selected plugins.
     */
    public function install_updates_action()
    {
        $this->check_ticket();

        $plugin_manager = PluginManager::getInstance();
        $this->flash['plugins_disabled'] = $plugin_manager->isPluginsDisabled();
        $this->flash['plugin_filter'] = Request::option('plugin_filter', '');
        $this->flash['update'] = Request::intArray('update');

        $plugin_manager->setPluginsDisabled(true);
        $this->redirect('admin/plugin/internal_install_updates');
    }

    /**
     * Install updates for all selected plugins.
     * Note: This action is only called internally via redirect.
     */
    public function internal_install_updates_action()
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_manager->setPluginsDisabled($this->flash['plugins_disabled']);

        $plugins = $plugin_manager->getPluginInfos();
        $plugin_filter = $this->flash['plugin_filter'];
        $update_info = $this->plugin_admin->getUpdateInfo($plugins);

        $update = $this->flash['update'];
        $update_status = [];

        // update each plugin in turn
        foreach ($update as $id) {
            if (isset($update_info[$id]['update'])) {
                try {
                    $update_url = $update_info[$id]['update']['url'];
                    $this->plugin_admin->installPluginFromURL($update_url);
                } catch (PluginInstallationException $ex) {
                    $update_errors[] = sprintf('%s: %s', $plugins[$id]['name'], $ex->getMessage());
                }
            }
        }

        // collect and report errors
        if (isset($update_errors)) {
            $error = ngettext(
                'Beim Update ist ein Fehler aufgetreten:',
                'Beim Update sind Fehler aufgetreten:',
                count($update_errors)
            );
            PageLayout::postError($error, $update_errors);
        } else {
            PageLayout::postSuccess(_('Update erfolgreich installiert.'));
        }

        $this->redirect('admin/plugin?plugin_filter=' . $plugin_filter);
    }

    /**
     * Show a page describing this plugin's meta data and description,
     * if available.
     */
    public function manifest_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);
        PageLayout::setTitle(sprintf(_('Details von %s'), $plugin['name']));

        // retrieve manifest
        $pluginpath = Config::get()->PLUGINS_PATH . '/' . $plugin['path'];
        $manifest = $plugin_manager->getPluginManifest($pluginpath);

        $this->plugin   = $plugin;
        $this->manifest = $manifest;
    }

    /**
     * Display the default activation set for this plugin.
     */
    public function default_activation_action($plugin_id)
    {
        Helpbar::Get()->addPlainText(_('Einrichtungen'), _('Wählen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll.'), Icon::create('info'));
        $actions = new ActionsWidget();
        $actions->addLink(_('Pluginverwaltung'), $this->url_for('admin/plugin'), Icon::create('plugin', 'clickable'));
        Sidebar::Get()->addWidget($actions);
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);
        $selected_inst = $plugin_manager->getDefaultActivations($plugin_id);

        $this->plugin_name   = $plugin['name'];
        $this->plugin_id     = $plugin_id;
        $this->selected_inst = $selected_inst;
        $this->institutes    = $this->plugin_admin->getInstitutes();
    }

    /**
     * Change the default activation for this plugin.
     */
    public function save_default_activation_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $selected_inst = Request::optionArray('selected_inst');

        $this->check_ticket();

        // save selected institutes (if any)
        $plugin_manager->setDefaultActivations($plugin_id, $selected_inst);

        if (count($selected_inst) === 0) {
            PageLayout::postSuccess(_('Die Default-Aktivierung wurde ausgeschaltet.'));
        } else {
            $message = ngettext(
                'Für die ausgewählte Einrichtung wurde das Plugin standardmäßig aktiviert.',
                'Für die ausgewählten Einrichtungen wurde das Plugin standardmäßig aktiviert.',
                count($selected_inst)
            );
            PageLayout::postSuccess($message);
        }

        $this->redirect('admin/plugin/default_activation/' . $plugin_id);
    }

    /**
     * migrate a plugin to top version
     *
     * @param integer   id of plugin to migrate
     */
    public function migrate_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);
        $log = $this->plugin_admin->migratePlugin($plugin_id);
        if ($log) {
            PageLayout::postMessage(MessageBox::success(_('Die Migration wurde durchgeführt.'), array_map('htmlReady', explode("\n", trim($log)))));
        } else {
            PageLayout::postMessage(MessageBox::error(_('Die Migration konnte nicht durchgeführt werden.')));
        }
        $this->redirect('admin/plugin?plugin_filter=' . $plugin_filter);
    }

    public function unregistered_action()
    {
        $this->unknown_plugins = $this->plugin_admin->scanPluginDirectory();
    }

    /**
     * register a plugin in database when it
     * already exists in file system
     *
     * @param integer   number of found plugin
     */
    public function register_action($number)
    {
        CSRFProtection::verifyUnsafeRequest();
        $unknown_plugins = $this->plugin_admin->scanPluginDirectory();
        $plugin = $unknown_plugins[$number];

        try {
            $this->plugin_admin->registerPlugin($plugin['path']);
            PageLayout::postSuccess(_('Das Plugin wurde erfolgreich installiert.'));
        } catch (PluginInstallationException $ex) {
            PageLayout::postError($ex->getMessage());
        }
        $this->redirect('admin/plugin');
    }

    public function edit_automaticupdate_action($plugin_id = null)
    {
        $this->plugin = $plugin_id
                        ? PluginManager::getInstance()->getPluginInfoById($plugin_id)
                        : [];
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $this->check_ticket();
            if (!$plugin_id) {
                $plugin_id = $this->plugin_admin->installPluginFromURL(Request::get('automatic_update_url'));
                $this->plugin = PluginManager::getInstance()->getPluginInfoById($plugin_id);
            }
            $token = $this->plugin['automatic_update_secret'] ?: md5(uniqid());
            $statement = DBManager::get()->prepare("
                UPDATE plugins
                SET automatic_update_url = :url,
                    automatic_update_secret = :secret
                WHERE pluginid = :id
            ");
            $statement->execute([
                'id'     => $plugin_id,
                'url'    => Request::get('automatic_update_url'),
                'secret' => Request::get('use_security_token') ? $token : null
            ]);
            PageLayout::postMessage(MessageBox::success(_('Daten gespeichert.')));
            if (Request::get('automatic_update_url') && Request::get('use_security_token')) {
                PageLayout::postInfo(_('Unten können Sie den Security Token jetzt heraus kopieren.'));
            }
            $this->redirect("admin/plugin/edit_automaticupdate/{$plugin_id}");
        }

        if ($plugin_id) {
            PageLayout::setTitle(sprintf(_('Automatisches Update für %s'), $this->plugin['name']));
        } else {
            PageLayout::setTitle(_('Plugin von URL installieren'));
        }
    }

}
