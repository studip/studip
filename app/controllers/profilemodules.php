<?php

/**
 * ProfileModulesController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @author      Florian Bieringer
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

/**
 * Controller for the (de-)activation of homepage plugins for every user.
 */
class ProfileModulesController extends AuthenticatedController
{

    var $user_id = '';
    var $modules = array();
    var $plugins = array();

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

        $this->modules = array();

        // Set Navigation
        PageLayout::setHelpKeyword("Basis.ProfileModules");
        PageLayout::setTitle(_("Mehr Funktionen"));
        Navigation::activateItem('/profile/modules');

        // Get current user.
        $this->username = Request::username('username', $GLOBALS['user']->username);
        $this->user_id = get_userid($this->username);

        $this->plugins = array();
        $blubber = PluginEngine::getPlugin('Blubber');
        // Add blubber to plugin list so status can be updated.
        if ($blubber) {
            $this->plugins[] = $blubber;
        }

        // Get homepage plugins from database.
        $this->plugins = array_merge($this->plugins, PluginEngine::getPlugins('HomepagePlugin'));

        // Show info message if user is not on his own profile
        if ($this->user_id != $GLOBALS['user']->id) {
            $current_user = User::find($this->user_id);
            $message = sprintf(_('Daten von: %s %s (%s), Status: %s'),
                htmlReady($current_user->Vorname),
                htmlReady($current_user->Nachname),
                htmlReady($current_user->username),
                htmlReady($current_user->perms));
            $mbox = MessageBox::info($message);
            PageLayout::postMessage($mbox, 'settings-user-anncouncement');
        }

        $this->setupSidebar();
    }

    /**
     * Creates the sidebar.
     */
    private function setupSidebar()
    {

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/plugin-sidebar.png');
        $sidebar->setTitle(PageLayout::getTitle());

        $plusconfig = UserConfig::get($GLOBALS['user']->id)->PLUS_SETTINGS;

        if (!isset($_SESSION['profile_plus'])) {
            if (is_array($plusconfig['profile_plus'])){
                $_SESSION['profile_plus'] = $plusconfig['profile_plus'];
            } else {
                //$_SESSION['profile_plus']['Kategorie']['Lehrorganisation'] = 1;
                $_SESSION['profile_plus']['Kategorie']['Kommunikation und Zusammenarbeit'] = 1;
                //$_SESSION['profile_plus']['Kategorie']['Aufgaben'] = 1;
                $_SESSION['profile_plus']['Kategorie']['Sonstiges'] = 1;
                //$_SESSION['profile_plus']['Kategorie']['Projekte und Entwicklung'] = 1;
                /*$_SESSION['profile_plus']['Komplex'][1] = 1;
                $_SESSION['profile_plus']['Komplex'][2] = 1;
                $_SESSION['profile_plus']['Komplex'][3] = 1;*/
                $_SESSION['profile_plus']['View'] = 'openall';
                $_SESSION['profile_plus']['displaystyle'] = 'category';
            }
        }

        /*if (Request::Get('Komplex1') != null) $_SESSION['profile_plus']['Komplex'][1] = Request::Get('Komplex1');
        if (Request::Get('Komplex2') != null) $_SESSION['profile_plus']['Komplex'][2] = Request::Get('Komplex2');
        if (Request::Get('Komplex3') != null) $_SESSION['profile_plus']['Komplex'][3] = Request::Get('Komplex3');*/
        if (Request::Get('mode') != null) $_SESSION['profile_plus']['View'] = Request::Get('mode');
        if (Request::Get('displaystyle') != null) $_SESSION['profile_plus']['displaystyle'] = Request::Get('displaystyle');


        $widget = new OptionsWidget();
        $widget->setTitle(_('Kategorien'));

        foreach ($_SESSION['profile_plus']['Kategorie'] as $key => $val) {
            if ($key == 'Sonstiges') continue;
            if (Request::Get(md5('cat_' . $key)) != null) $_SESSION['profile_plus']['Kategorie'][$key] = Request::Get(md5('cat_' . $key));

            $widget->addCheckbox($key, $_SESSION['profile_plus']['Kategorie'][$key],
                URLHelper::getLink('?', array(md5('cat_' . $key) => 1)), URLHelper::getLink('?', array(md5('cat_' . $key) => 0)));

        }

        if (Request::Get(md5('cat_Sonstiges')) != null) $_SESSION['profile_plus']['Kategorie']['Sonstiges'] = Request::Get(md5('cat_Sonstiges'));

        $widget->addCheckbox(_('Sonstiges'), $_SESSION['profile_plus']['Kategorie']['Sonstiges'],
            URLHelper::getLink('?', array(md5('cat_Sonstiges') => 1)), URLHelper::getLink('?', array(md5('cat_Sonstiges') => 0)));

        $sidebar->addWidget($widget, "Kategorien");


        /*$widget = new OptionsWidget();
        $widget->setTitle(_('Komplexität'));
        $widget->addCheckbox(_('Standard'), $_SESSION['profile_plus']['Komplex'][1],
            URLHelper::getLink('?', array('Komplex1' => 1)), URLHelper::getLink('?', array('Komplex1' => 0)));
        $widget->addCheckbox(_('Erweitert'), $_SESSION['profile_plus']['Komplex'][2],
            URLHelper::getLink('?', array('Komplex2' => 1)), URLHelper::getLink('?', array('Komplex2' => 0)));
        $widget->addCheckbox(_('Intensiv'), $_SESSION['profile_plus']['Komplex'][3],
            URLHelper::getLink('?', array('Komplex3' => 1)), URLHelper::getLink('?', array('Komplex3' => 0)));
        $sidebar->addWidget($widget, "Komplex");*/


        $widget = new ActionsWidget();
        $widget->setTitle(_("Ansichten"));
        if ($_SESSION['profile_plus']['View'] == 'openall') {
            $widget->addLink(_("Alles zuklappen"),
                URLHelper::getLink('?', array('mode' => 'closeall')), Icon::create('assessment', 'clickable'));
        } else {
            $widget->addLink(_("Alles aufklappen"),
                URLHelper::getLink('?', array('mode' => 'openall')), Icon::create('assessment', 'clickable'));
        }

        if ($_SESSION['profile_plus']['displaystyle'] == 'category') {
            $widget->addLink(_("Alphabetische Anzeige ohne Kategorien"),
                    URLHelper::getLink('?', array('displaystyle' => 'alphabetical')), Icon::create('assessment', 'clickable'));
        } else {
            $widget->addLink(_("Anzeige nach Kategorien"),
                    URLHelper::getLink('?', array('displaystyle' => 'category')), Icon::create('assessment', 'clickable'));
        }


        $widget->addLink(_('Alle Inhaltselemente aktivieren'),
            $this->url_for('profilemodules/reset/true'), Icon::create('accept', 'clickable'));
        $widget->addLink(_('Alle Inhaltselemente deaktivieren'),
            $this->url_for('profilemodules/reset'), Icon::create('decline', 'clickable'));
        $sidebar->addWidget($widget);

        $plusconfig['profile_plus'] = $_SESSION['profile_plus'];
        UserConfig::get($GLOBALS['user']->id)->store(PLUS_SETTINGS,$plusconfig);
    }

    /**
     * Generates an overview of installed plugins and provides the possibility
     * to (de-)activate each of them.
     */
    public function index_action()
    {
        $this->sortedList = $this->getSortedList(User::find($this->user_id));
        if (Request::submitted('deleteContent')) $this->deleteContent($this->sortedList);
    }

    /**
     * Updates the activation status of user's homepage plugins.
     */
    public function update_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $manager = PluginManager::getInstance();
        $modules = Request::optionArray('modules');

        $success = null;
        $anchor = "";
        // Plugins
        foreach ($this->plugins as $plugin) {
            // Check local activation status.
            $id = $plugin->getPluginId();

            $state_before = $manager->isPluginActivatedForUser($id, $this->user_id);
            $state_after = in_array($id, $modules);

            if ($state_before !== $state_after) {
                $updated = $manager->setPluginActivated($id, $this->user_id, $state_after, 'user');

                $success = $success || $updated;

                if ($state_after) {
                    PageLayout::postSuccess(sprintf(_('"%s" wurde aktiviert.'), $plugin->getPluginName()));
                } else {
                    PageLayout::postSuccess(sprintf(_('"%s" wurde deaktiviert.'), $plugin->getPluginName()));
                }
                $anchor = '#p_' . $plugin->getPluginId();
            }
        }

        if ($success === false) {
            $message = MessageBox::error(_('Ihre Änderungen konnten nicht gespeichert werden.'));
        }
        if ($message) {
            PageLayout::postMessage($message);
        }

        $this->redirect($this->url_for('profilemodules/index' . $anchor, array('username' => $this->username)));
    }

    /**
     * Resets/deactivates all profile modules.
     */
    public function reset_action($state = false)
    {
        $manager = PluginManager::getInstance();
        foreach ($this->plugins as $plugin) {
            // Check local activation status.
            $id = $plugin->getPluginId();

            $manager->setPluginActivated($plugin->getPluginId(), $this->user_id, $state, 'user');
        }

        PageLayout::postMessage(MessageBox::success(_('Ihre Änderungen wurden gespeichert.')));
        $this->redirect($this->url_for('profilemodules/index', array('username' => $this->username)));
    }


    private function getSortedList(Range $context)
    {
        $list = array();

        $manager = PluginManager::getInstance();

        // Now loop through all found plugins.
        foreach ($this->plugins as $plugin) {
            if (!$plugin->isActivatableForContext($context)) {
                continue;
            }

            // Check local activation status.
            $id = $plugin->getPluginId();
            $activated = $manager->isPluginActivatedForUser($id, $this->user_id);
            // Load plugin data (e.g. name and description)
            $metadata = $plugin->getMetadata();

            if($_SESSION['profile_plus']['displaystyle'] != 'category'){

                $key = isset($info['displayname']) ? $info['displayname'] : $plugin->getPluginname();


                $list['Funktionen von A-Z'][mb_strtolower($key)]['object'] = $plugin;
                $list['Funktionen von A-Z'][mb_strtolower($key)]['activated'] = $activated;


            } else {

                $cat = isset($metadata['category']) ? $metadata['category'] : 'Sonstiges';

                if (!isset($_SESSION['profile_plus']['Kategorie'][$cat])) $_SESSION['profile_plus']['Kategorie'][$cat] = 1;

                $key = isset($metadata['displayname']) ? $metadata['displayname'] : $plugin->getPluginname();

                $list[$cat][mb_strtolower($key)]['object'] = $plugin;
                $list[$cat][mb_strtolower($key)]['activated'] = $activated;

            }
        }

        $sortedcats['Lehrorganisation'] = array();
        $sortedcats['Kommunikation und Zusammenarbeit'] = array();
        $sortedcats['Aufgaben'] = array();

        foreach ($list as $cat_key => $cat_val) {
            ksort($cat_val);
            $list[$cat_key] = $cat_val;
            if ($cat_key != 'Sonstiges') $sortedcats[$cat_key] = $list[$cat_key];
        }

        if (isset($list['Sonstiges'])) $sortedcats['Sonstiges'] = $list['Sonstiges'];

        return $sortedcats;
    }


    private function deleteContent($plugmodlist)
    {
        $name = Request::Get('name');

        foreach ($plugmodlist as $key => $val) {
            if (array_key_exists($name, $val)) {
                if ($val[$name]['type'] == 'plugin') {
                    $class = PluginEngine::getPlugin(get_class($val[$name]['object']));
                    $displayname = $class->getPluginName();
                } elseif ($val[$name]['type'] == 'modul') {
                    if ($this->sem_class) {
                        $class = $this->sem_class->getModule($this->sem_class->getSlotModule($val[$name]['modulkey']));
                        $displayname = $val[$name]['object']['name'];
                    }
                }
            }
        }

        if (Request::submitted('check')) {
            if (method_exists($class, 'deleteContent')) {
                $class->deleteContent();
            } else {
                PageLayout::postMessage(MessageBox::info(_("Das Plugin/Modul enthält keine Funktion zum Löschen der Inhalte.")));
            }
        } else {
            PageLayout::postMessage(MessageBox::info(sprintf(_("Sie beabsichtigen die Inhalte von %s zu löschen."), $displayname)
                . "<br>" . _("Wollen Sie die Inhalte wirklich löschen?") . "<br>"
                . LinkButton::createAccept(_('Ja'), URLHelper::getURL("?deleteContent=true&check=true&name=" . $name))
                . LinkButton::createCancel(_('Nein'))));
        }
    }


}
