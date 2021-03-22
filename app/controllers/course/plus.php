<?php
# Lifter001: TODO
/*
 * Copyright (C) 2012 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

use Studip\Button, Studip\LinkButton;

class Course_PlusController extends AuthenticatedController
{

    public function index_action($range_id = null)
    {
        PageLayout::setTitle(_("Mehr Funktionen"));

        $id = Context::getId();
        $object_type = Context::getClass();

        Navigation::activateItem('/course/modules');

        if (!$id || !$GLOBALS['perm']->have_studip_perm($object_type === 'sem' ? 'tutor' : 'admin', $id)) {
            throw new AccessDeniedException();
        }

        if ($object_type === "sem") {
            $this->sem = Course::find($id);
            $this->sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$this->sem->status]['class']];
        } else {
            $this->sem = Institute::find($id);
            $this->sem_class = SemClass::getDefaultInstituteClass($this->sem['type']);
        }

        PageLayout::setTitle($this->sem->getFullname() . " - " . PageLayout::getTitle());

        $this->modules = new AdminModules();
        $this->registered_modules = $this->modules->registered_modules;

        if (!Request::submitted('uebernehmen')) {
            $_SESSION['admin_modules_data']["modules_list"] = $this->modules->getLocalModules($id);
            $_SESSION['admin_modules_data']["orig_bin"] = $this->modules->getBin($id);
            $_SESSION['admin_modules_data']["changed_bin"] = $this->modules->getBin($id);
            $_SESSION['admin_modules_data']["range_id"] = $id;
            $_SESSION['admin_modules_data']["conflicts"] = [];
            $_SESSION['plugin_toggle'] = [];
        }

        $this->setupSidebar();
        $this->available_modules = $this->getSortedList($this->sem);

        if (Request::submitted('deleteContent')) {
            $this->deleteContent($this->available_modules);
        }
    }

    public function trigger_action()
    {
        $id = Context::getId();
        $object_type = Context::getClass();

        Navigation::activateItem('/course/modules');

        if (!$id || !$GLOBALS['perm']->have_studip_perm($object_type === 'sem' ? 'tutor' : 'admin', $id)) {
            throw new AccessDeniedException();
        }

        if (Request::isPost()) {
            if ($object_type === "sem") {
                $this->sem = Course::find($id);
                $this->sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$this->sem->status]['class']];
            } else {
                $this->sem = Institute::find($id);
                $this->sem_class = SemClass::getDefaultInstituteClass($this->sem['type']);
            }
            $this->setupSidebar();
            $module = Request::get("moduleclass");
            $active = Request::int("active", 0);

            if ($this->sem_class->isSlotModule($module)) {
                $modules = new AdminModules();
                $bitmask = $modules->getBin($id, $object_type);

                foreach ($modules->registered_modules as $key => $val) {
                    $studip_module = $this->sem_class->getModule($key);
                    if ($studip_module) {
                        $info = $studip_module->getMetadata();
                        $info["category"] = $info["category"] ?: 'Sonstiges';

                        if ($key === Request::get("key")) {
                            if ($active) {
                                $modules->setBit($bitmask, $modules->registered_modules[$key]["id"]);
                            } else {
                                $modules->clearBit($bitmask, $modules->registered_modules[$key]["id"]);
                            }
                            break;
                        }
                    }

                }

                $modules->writeBin($id, $bitmask, $object_type);
            }
            if (is_a($module, 'StandardPlugin', true)) {
                $plugin = PluginEngine::getPlugin($module);
                $this->setPluginActivated($plugin, $active);
            }
            $this->redirect("course/plus/trigger", ['cid' => $id]);
        } else {
            $template = $GLOBALS['template_factory']->open("tabs.php");
            $template->navigation = Navigation::getItem("/course");
            $this->render_json([
                'tabs' => $template->render()
            ]);
        }
    }

    private function deleteContent($plugmodlist)
    {
        $name = Request::get('name');

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
            PageLayout::postMessage(MessageBox::info(sprintf(_("Sie beabsichtigen die Inhalte von %s zu löschen."), htmlReady($displayname))
                . "<br>" . _("Wollen Sie die Inhalte wirklich löschen?") . "<br>"
                . LinkButton::createAccept(_('Ja'), URLHelper::getURL("?deleteContent=true&check=true&name=" . $name))
                . LinkButton::createCancel(_('Nein'))));
        }
    }

    private function setupSidebar()
    {

        $plusconfig = UserConfig::get($GLOBALS['user']->id)->PLUS_SETTINGS;

        if (!isset($_SESSION['plus'])) {
            if (isset($plusconfig['course_plus'])){
                $usr_conf = $plusconfig['course_plus'];

                $_SESSION['plus']['Kategorie']['Lehr- und Lernorganisation'] = $usr_conf['Kategorie']['Lehr- und Lernorganisation'];
                $_SESSION['plus']['Kategorie']['Kommunikation und Zusammenarbeit'] = $usr_conf['Kategorie']['Kommunikation und Zusammenarbeit'];
                $_SESSION['plus']['Kategorie']['Inhalte und Aufgabenstellungen'] = $usr_conf['Kategorie']['Inhalte und Aufgabenstellungen'];
                $_SESSION['plus']['Kategorie']['Sonstiges'] = $usr_conf['Kategorie']['Sonstiges'];

                foreach ($usr_conf['Kategorie'] as $key => $val){
                    if(!array_key_exists($key, $_SESSION['plus']['Kategorie'])){
                        $_SESSION['plus']['Kategorie'][$key] = $val;
                    }
                }

                $_SESSION['plus']['View'] = $usr_conf['View'];
                $_SESSION['plus']['displaystyle'] = $usr_conf['displaystyle'];

            } else {
                $_SESSION['plus']['Kategorie']['Lehr- und Lernorganisation'] = 1;
                $_SESSION['plus']['Kategorie']['Kommunikation und Zusammenarbeit'] = 1;
                $_SESSION['plus']['Kategorie']['Inhalte und Aufgabenstellungen'] = 1;
                $_SESSION['plus']['Kategorie']['Sonstiges'] = 1;
                $_SESSION['plus']['View'] = 'openall';
                $_SESSION['plus']['displaystyle'] = 'category';
            }
        }

        if(isset($_SESSION['plus']['Kategorielist'])){
            foreach ($_SESSION['plus']['Kategorie'] as $key => $val){
                if(!array_key_exists($key, $_SESSION['plus']['Kategorielist']) && $key != 'Sonstiges'){
                    unset($_SESSION['plus']['Kategorie'][$key]);
                }
            }
        }
        if (Request::get('mode') != null) $_SESSION['plus']['View'] = Request::get('mode');
        if (Request::get('displaystyle') != null) $_SESSION['plus']['displaystyle'] = Request::get('displaystyle');

        $sidebar = Sidebar::get();

        $widget = new OptionsWidget();
        $widget->setTitle(_('Kategorien'));

        foreach ($_SESSION['plus']['Kategorie'] as $key => $val) {

            if (Request::get(md5('cat_' . $key)) != null) $_SESSION['plus']['Kategorie'][$key] = Request::get(md5('cat_' . $key));

            if ($_SESSION['plus']['displaystyle'] == 'alphabetical') {
                $_SESSION['plus']['Kategorie'][$key] = 1;
            }

            if ($key == 'Sonstiges') continue;
            $widget->addCheckbox($key, $_SESSION['plus']['Kategorie'][$key],
                URLHelper::getURL('?', [md5('cat_' . $key) => 1, 'displaystyle' => 'category']), URLHelper::getURL('?', [md5('cat_' . $key) => 0, 'displaystyle' => 'category']));

        }

        $widget->addCheckbox(_('Sonstiges'), $_SESSION['plus']['Kategorie']['Sonstiges'],
            URLHelper::getURL('?', [md5('cat_Sonstiges') => 1, 'displaystyle' => 'category']), URLHelper::getURL('?', [md5('cat_Sonstiges') => 0, 'displaystyle' => 'category']));

        $sidebar->addWidget($widget, "Kategorien");

        $widget = new ActionsWidget();
        $widget->setTitle(_('Ansichten'));

        if ($_SESSION['plus']['View'] == 'openall') {
            $widget->addLink(_("Alles zuklappen"),
                URLHelper::getURL('?', ['mode' => 'closeall']), Icon::create('assessment', 'clickable'));
        } else {
            $widget->addLink(_("Alles aufklappen"),
                URLHelper::getURL('?', ['mode' => 'openall']), Icon::create('assessment', 'clickable'));
        }

        if ($_SESSION['plus']['displaystyle'] == 'category') {
            $widget->addLink(_("Alphabetische Anzeige ohne Kategorien"),
                    URLHelper::getURL('?', ['displaystyle' => 'alphabetical']), Icon::create('assessment', 'clickable'));
        } else {
            $widget->addLink(_("Anzeige nach Kategorien"),
                    URLHelper::getURL('?', ['displaystyle' => 'category']), Icon::create('assessment', 'clickable'));
        }

        $sidebar->addWidget($widget, "aktion");

        unset($_SESSION['plus']['Kategorielist']);
        $plusconfig['course_plus'] = $_SESSION['plus'];
        UserConfig::get($GLOBALS['user']->id)->store('PLUS_SETTINGS', $plusconfig);
    }


    private function getSortedList(Range $context)
    {

        $list = [];
        $cat_index = [];

        foreach (PluginEngine::getPlugins('StandardPlugin') as $plugin) {
            if (!$plugin->isActivatableForContext($context)) {
                continue;
            }



            if ((!$this->sem_class && !$plugin->isCorePlugin())
                || ($this->sem_class && !$this->sem_class->isModuleMandatory(get_class($plugin))
                    && $this->sem_class->isModuleAllowed(get_class($plugin))
                    && !$this->sem_class->isSlotModule(get_class($plugin)))
            ) {

                $info = $plugin->getMetadata();

                $indcat = isset($info['category']) ? $info['category'] : 'Sonstiges';
                if (!array_key_exists($indcat, $cat_index)) {
                    array_push($cat_index, $indcat);
                }
                $plugin_id = 'plugin_' . $plugin->getPluginId();
                $displayname = mb_strtolower(isset($info['displayname']) ? $info['displayname'] : $plugin->getPluginname());
                if ($_SESSION['plus']['displaystyle'] != 'category') {


                    $list['Funktionen von A-Z'][$plugin_id]['object'] = $plugin;
                    $list['Funktionen von A-Z'][$plugin_id]['type'] = 'plugin';
                    $list['Funktionen von A-Z'][$plugin_id]['moduleclass'] = get_class($plugin);
                    $list['Funktionen von A-Z'][$plugin_id]['sorter'] = $displayname;

                } else {

                    $cat = isset($info['category']) ? $info['category'] : 'Sonstiges';

                    if (!isset($_SESSION['plus']['Kategorie'][$cat])) {
                        $_SESSION['plus']['Kategorie'][$cat] = 1;
                    }

                    $list[$cat][$plugin_id]['object'] = $plugin;
                    $list[$cat][$plugin_id]['moduleclass'] = get_class($plugin);
                    $list[$cat][$plugin_id]['type'] = 'plugin';
                    $list[$cat][$plugin_id]['sorter'] = $displayname;

                }
            }
        }

        foreach ($this->registered_modules as $key => $val) {

            if ($this->sem_class) {
                $mod = $this->sem_class->getSlotModule($key);
                $slot_editable = $mod && $this->sem_class->isModuleAllowed($mod) && !$this->sem_class->isModuleMandatory($mod);
            }

            if ($this->modules->isEnableable($key, $_SESSION['admin_modules_data']["range_id"]) && (!$this->sem_class || $slot_editable)) {

                if ($this->sem_class) {
                    $studip_module = $this->sem_class->getModule($mod);
                    if (method_exists($studip_module, 'isActivatableForContext') && !$studip_module->isActivatableForContext($context)) {
                        continue;
                    }
                }

                $info = ($studip_module instanceOf StudipModule) ? $studip_module->getMetadata() : ($val['metadata'] ? $val['metadata'] : []);

                $indcat = isset($info['category']) ? $info['category'] : 'Sonstiges';
                if (!array_key_exists($indcat, $cat_index)) {
                    array_push($cat_index, $indcat);
                }

                if ($_SESSION['plus']['displaystyle'] != 'category') {

                    $list['Funktionen von A-Z'][$key]['object'] = $val;
                    $list['Funktionen von A-Z'][$key]['moduleclass'] = $mod;
                    $list['Funktionen von A-Z'][$key]['type'] = 'modul';
                    $list['Funktionen von A-Z'][$key]['modulkey'] = $key;
                    $list['Funktionen von A-Z'][$key]['sorter'] = mb_strtolower($info['displayname'] ?: $val['name']);


                } else {

                    $cat = isset($info['category']) ? $info['category'] : 'Sonstiges';

                    if (!isset($_SESSION['plus']['Kategorie'][$cat])) $_SESSION['plus']['Kategorie'][$cat] = 1;

                    $list[$cat][$key]['object'] = $val;
                    $list[$cat][$key]['moduleclass'] = $mod;
                    $list[$cat][$key]['type'] = 'modul';
                    $list[$cat][$key]['modulkey'] = $key;
                    $list[$cat][$key]['sorter'] = mb_strtolower($info['displayname'] ?: $val['name']);

                }
            }
        }

        $sortedcats['Lehr- und Lernorganisation'] = [];
        $sortedcats['Kommunikation und Zusammenarbeit'] = [];
        $sortedcats['Inhalte und Aufgabenstellungen'] = [];

        foreach ($list as $cat_key => $cat_val) {
            uasort($cat_val, function ($a, $b) {return strcmp($a['sorter'], $b['sorter']);});
            $list[$cat_key] = $cat_val;
            if ($cat_key != 'Sonstiges')  {
                $sortedcats[$cat_key] = $list[$cat_key];
            }
        }

        if (isset($list['Sonstiges'])) {
            $sortedcats['Sonstiges'] = $list['Sonstiges'];
        }


        $_SESSION['plus']['Kategorielist'] = array_flip($cat_index);

        return $sortedcats;
    }

    private function setPluginActivated(StudIPModule $plugin = null, bool $state = null)
    {
        static $manager = null;
        if ($manager === null) {
            $manager = PluginManager::getInstance();
        }

        if (
            !is_a($plugin, 'StandardPlugin')
            || !$plugin->isActivatableForContext(Context::get())
        ) {
            return null;
        }

        if ($state === null) {
            $state = !$manager->isPluginActivated($plugin->getPluginId(), Context::getId());
        }

        $manager->setPluginActivated($plugin->getPluginId(), Context::getId(), $state);

        if (Context::isCourse()) {
            if ($state) {
                StudipLog::log('PLUGIN_ENABLE', Context::getId(), $plugin->getPluginId(), $GLOBALS['user']->id);
                NotificationCenter::postNotification('PluginDidActivate', Context::getId(), $plugin->getPluginId());
            } else {
                StudipLog::log('PLUGIN_DISABLE', Context::getId(), $plugin->getPluginId(), $GLOBALS['user']->id);
                NotificationCenter::postNotification('PluginDidDeactivate', Context::getId(), $plugin->getPluginId());
            }
        }

        return $manager->isPluginActivated($plugin->getPluginId(), Context::getId());
    }
}
