<?php
/**
 * ProfileModulesController
 *
 * Controller for the (de-)activation of homepage plugins for every user.
 *
 * @author    Thomas Hackl <thomas.hackl@uni-passau.de>
 * @author    Florian Bieringer
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @since     2.4
 */
class ProfileModulesController extends AuthenticatedController
{
    protected $user;
    protected $plugins = [];

    /**
     * This function is called before any output is generated or any other
     * actions are performed. Initializations happen here.
     *
     * @param $action Name of the action to perform
     * @param $args   Arguments for the given action
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Set Navigation
        PageLayout::setHelpKeyword('Basis.ProfileModules');
        PageLayout::setTitle(_('Mehr Funktionen'));
        Navigation::activateItem('/profile/modules');

        // Get current user.
        $this->username = Request::username('username', $GLOBALS['user']->username);
        $this->user     = User::findByUsername($this->username);

        $this->plugins = $this->getPlugins();

        // Show info message if user is not on his own profile
        if ($this->user->id !== $GLOBALS['user']->id) {
            PageLayout::postInfo(htmlReady(sprintf(
                _('Daten von: %s %s (%s), Status: %s'),
                $this->user->Vorname,
                $this->user->Nachname,
                $this->user->username,
                $this->user->perms
            )));
        }

        $this->config = $this->getConfig();
        $this->processRequest($this->config);
    }

    private function getConfig()
    {
        $config = $GLOBALS['user']->cfg->PLUS_SETTINGS;
        if (!$config || !isset($config['profile_plus'])) {
            return [
                'view'         => 'openall',
                'displaystyle' => 'category',
                'hidden'       => [],
            ];
        }

        return array_merge(['hidden' => []], $config['profile_plus']);
    }

    private function storeConfig(array $config)
    {
        $cfg = $GLOBALS['user']->cfg->PLUS_SETTINGS;
        if (!$cfg) {
            $cfg = [];
        }
        $cfg['profile_plus'] = $config;

        $GLOBALS['user']->cfg->store('PLUS_SETTINGS', $cfg);
    }

    private function processRequest(array $config)
    {
        $initial = $config;

        if (Request::submitted('mode')) {
            $config['view'] = Request::get('mode');
        }
        if (Request::submitted('displaystyle')) {
            $config['displaystyle'] = Request::get('displaystyle');
        }
        if (Request::submitted('show')) {
            $config['hidden'] = array_diff(
                $config['hidden'],
                [Request::get('show')]
            );
        }
        if (Request::submitted('hide')) {
            $config['hidden'][] = Request::get('hide');
        }

        if ($initial != $config) {
            $this->storeConfig($config);
            $this->redirect('profilemodules');
        }
    }

    private function getPlugins()
    {
        $plugins = [];

        // Add blubber to plugin list so status can be updated.
        if ($blubber = PluginEngine::getPlugin('Blubber')) {
            $plugins[$blubber->getPluginId()] = $blubber;
        }

        // Get homepage plugins from database.
        foreach (PluginEngine::getPlugins('HomepagePlugin') as $plugin) {
            $plugins[$plugin->getPluginId()] = $plugin;
        }

        return $plugins;
    }

    /**
     * Creates the sidebar.
     */
    private function setupSidebar(array $list, array $config)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/plugin-sidebar.png');
        $sidebar->setTitle(PageLayout::getTitle());

        if ($config['displaystyle'] === 'category') {
            $widget = $sidebar->addWidget(new OptionsWidget());
            $widget->setTitle(_('Kategorien'));

            foreach (array_keys($list) as $key) {
                $widget->addCheckbox(
                    $key,
                    !in_array($key, $config['hidden']),
                    $this->link_for('profilemodules', ['show' => $key]),
                    $this->link_for('profilemodules', ['hide' => $key])
                );
            }
        }

        $widget = $sidebar->addWidget(new ActionsWidget());
        $widget->setTitle(_('Ansichten'));
        if ($config['view'] === 'openall') {
            $widget->addLink(
                _('Alles zuklappen'),
                $this->url_for('profilemodules', ['mode' => 'closeall']),
                Icon::create('assessment')
            );
        } else {
            $widget->addLink(
                _('Alles aufklappen'),
                $this->url_for('profilemodules', ['mode' => 'openall']),
                Icon::create('assessment')
            );
        }

        if ($config['displaystyle'] === 'category') {
            $widget->addLink(
                _('Alphabetische Anzeige ohne Kategorien'),
                $this->url_for('profilemodules', ['displaystyle' => 'alphabetical']),
                Icon::create('assessment')
            );
        } else {
            $widget->addLink(
                _('Anzeige nach Kategorien'),
                $this->url_for('profilemodules', ['displaystyle' => 'category']),
                Icon::create('assessment')
            );
        }

        $widget = $sidebar->addWidget(new ActionsWidget());
        $widget->addLink(
            _('Alle Inhaltselemente aktivieren'),
            $this->url_for('profilemodules/reset/1'),
            Icon::create('accept')
        );
        $widget->addLink(
            _('Alle Inhaltselemente deaktivieren'),
            $this->url_for('profilemodules/reset/0'),
            Icon::create('decline')
        );
    }

    /**
     * Generates an overview of installed plugins and provides the possibility
     * to (de-)activate each of them.
     */
    public function index_action()
    {
        $list = $this->getSortedList($this->user, $this->config);

        $this->setupSidebar($list, $this->config);

        $this->list = [];
        foreach ($list as $category => $value) {
            if (!in_array($category, $this->config['hidden'])) {
                $this->list[$category] = $value;
            }
        }

        // TODO: Activate this as the dev board reliably speaks PHP7
        //       and remove the above list creation

        // $this->list = array_filter($list, function ($category) {
        //     return !in_array($category, $this->config['hidden']);
        // }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Updates the activation status of user's homepage plugins.
     */
    public function update_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $manager = PluginManager::getInstance();
        $modules = Request::intArray('modules');

        $success = null;
        $anchor = '';

        foreach ($modules as $item => $state) {
            list($changed, $name) = $this->updateItem($item, $state);

            $success = $success || $changed !== null;

            if ($changed !== null) {
                PageLayout::postSuccess(sprintf(
                    $changed ? _('"%s" wurde aktiviert.') : _('"%s" wurde deaktiviert.'),
                    htmlReady($name)
                ));

                $anchor = "#p_{$item}";
            }
        }

        if ($success === false) {
            PageLayout::postError(_('Ihre Änderungen konnten nicht gespeichert werden.'));
        }

        $this->redirect($this->url_for("profilemodules{$anchor}", ['username' => $this->username]));
    }

    /**
     * Resets/deactivates all profile modules.
     */
    public function reset_action($state = false)
    {
        foreach ($this->plugins as $id => $plugin) {
            $this->updateItem($id, $state);
        }

        PageLayout::postSuccess(_('Ihre Änderungen wurden gespeichert.'));
        $this->redirect($this->url_for('profilemodules', ['username' => $this->username]));
    }

    private function updateItem($item, $state)
    {
        static $manager = null;

        if ($manager === null) {
            $manager = $manager = PluginManager::getInstance();
        }

        $state = (bool) $state;
        if ($state != $manager->isPluginActivatedForUser($item, $this->user->id)
            && $manager->setPluginActivated($item, $this->user->id, $state, 'user'))
        {
            return [$state, $this->plugins[$item]->getPluginName()];
        }

        return null;
    }

    private function getSortedList(Range $context, array $config)
    {
        $list = [];

        $manager = PluginManager::getInstance();

        // Now loop through all found plugins.
        foreach ($this->plugins as $plugin) {
            if (!$plugin->isActivatableForContext($context)) {
                continue;
            }

            // Check local activation status.

            // Load plugin data (e.g. name and description)
            $metadata = $plugin->getMetadata();

            if ($config['displaystyle'] !== 'category'){
                $cat = 'Funktionen von A-Z';
            } else {
                $cat = $metadata['category'] ?: 'Sonstiges';
            }

            $item = [
                'id'          => $plugin->getPluginId(),
                'name'        => $metadata['displayname'] ?: $plugin->getPluginname(),
                'url'         => $plugin->getPluginURL(),
                'activated'   => $manager->isPluginActivatedForUser($plugin->getPluginId(), $this->user->id),
                'icon'        => $metadata['icon'] ? "{$plugin->getPluginURL()}/{$metadata['icon']}" : null,
                'abstract'    => str_replace('\n', ' ', $metadata['descriptionshort'] ?: $metadata['summary']),
                'description' => str_replace('\n', ' ', $metadata['descriptionlong'] ?: $metadata['description']),
                'screenshots' => [],
                'keywords'    => $metadata['keywords'] ? explode(';', $metadata['keywords']) : [],
                'homepage'    => $metadata['homepage'],
                'helplink'    => $metadata['helplink'],
            ];

            if (isset($metadata['screenshot'])) {
                $ext = end(explode('.', $metadata['screenshot']));
                $title  = str_replace('_', ' ', basename($metatdata['screenshot'], ".{$ext}"));
                $source = "{$plugin->getPluginURL()}/{$metadata['screenshot']}";

                $item['screenshots'][] = compact('title', 'source');
            }
            if (isset($metadata['additionalscreenshots'])) {
                foreach ($metadata['additionalscreenshots'] as $picture) {
                    $ext = end(explode('.', $picture));
                    $title  = str_replace('_', ' ', basename($picture, ".{$ext}"));
                    $source = "{$plugin->getPluginURL()}/{$picture}";

                    $item['screenshots'][] = compact('title', 'source');
                }
            }
            if (isset($metadata['screenshots'])) {
                foreach ($metadata['screenshots']['pictures'] as $picture) {
                    $title  = $picture['title'];
                    $source = "{$plugin->getPluginURL()}/{$metadata['screenshots']['path']}/{$picture['source']}";

                    $item['screenshots'][] = compact('title', 'source');
                }
            }

            $list[$cat][$plugin->getPluginId()] = $item;
        }

        foreach ($list as $cat_key => $cat_val) {
            uasort($cat_val, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $list[$cat_key] = $cat_val;
            if ($cat_key !== 'Sonstiges') {
                $sortedcats[$cat_key] = $list[$cat_key];
            }
        }

        if (isset($list['Sonstiges'])) {
            $sortedcats['Sonstiges'] = $list['Sonstiges'];
        }

        return $sortedcats;
    }
}
