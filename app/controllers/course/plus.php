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

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $id = Context::get()->getId();
        $object_type = Context::getType();
        if (!$id || !$GLOBALS['perm']->have_studip_perm($object_type === 'course' ? 'tutor' : 'admin', $id)) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem('/course/modules');

        if ($object_type === 'course') {
            $this->sem = Context::get();
            $this->sem_class = $this->sem->getSemClass();
        } else {
            $this->sem = Context::get();
            $this->sem_class = SemClass::getDefaultInstituteClass($this->sem['type']);
        }
        PageLayout::setTitle(_("Mehr Funktionen"));
    }

    public function index_action()
    {

        PageLayout::setTitle($this->sem->getFullname() . " - " . PageLayout::getTitle());
        PageLayout::addSqueezePackage('statusgroups'); //sortier css

        $this->setupSidebar();
        $this->available_modules = $this->getSortedList($this->sem);

        if (Request::submitted('deleteContent')) {
            $this->deleteContent($this->available_modules);
        }
    }

    public function trigger_action()
    {
        $context = Context::get();

        if (!$GLOBALS['perm']->have_studip_perm($context->getRangeType() === 'course' ? 'tutor' : 'admin', $context->getId())) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            if ($context->getRangeType() === 'course') {
                $sem_class = $context->getSemClass();
            } else {
                $sem_class = SemClass::getDefaultInstituteClass($context->type);
            }
            $moduleclass = Request::get("moduleclass");
            $active = Request::int("active", 0);
            $module = new $moduleclass;
            if ($module->isActivatableForContext($context)) {
                PluginManager::getInstance()->setPluginActivated($module->getPluginId(), $context->getId(), $active);
                if (Context::isCourse()) {
                    if ($active) {
                        StudipLog::log('PLUGIN_ENABLE', Context::getId(), $module->getPluginId(), $GLOBALS['user']->id);
                        NotificationCenter::postNotification('PluginDidActivate', Context::getId(), $module->getPluginId());
                    } else {
                        StudipLog::log('PLUGIN_DISABLE', Context::getId(), $module->getPluginId(), $GLOBALS['user']->id);
                        NotificationCenter::postNotification('PluginDidDeactivate', Context::getId(), $module->getPluginId());
                    }
                }
            }
            if ($active) {
                $default_position = array_search(get_class($module), $sem_class->getActivatedModules());
                if ($default_position !== false) {
                    $active_tool = ToolActivation::find([$context->getId(), $module->getPluginId()]);
                    if ($active_tool) {
                        $active_tool->position = $default_position;
                        $active_tool->store();
                    }
                }
            }
            $this->redirect("course/plus/trigger", ['cid' => $context->getId()]);
        } else {
            $template = $GLOBALS['template_factory']->open("tabs.php");
            $template->navigation = Navigation::getItem("/course");
            $this->render_json([
                'tabs' => $template->render()
            ]);
        }
    }

    public function sorttools_action()
    {
        PageLayout::setTitle(_('Reihenfolge der Werkzeuge ändern'));
        if (Request::submitted('order')) {
            CSRFProtection::verifyUnsafeRequest();
            $plugin_id = Request::get('id');
            $newpos = Request::get('index') + 1;
            if ($this->sem->tools->findOneBy('plugin_id', $plugin_id)) {
                $this->sem->tools->findBy('position', $newpos, '>=')->each(function ($p) {$p->position++;});
                $this->sem->tools->findOneBy('plugin_id', $plugin_id)->position = $newpos;
                $this->sem->tools->orderBy('position asc')->each(function ($p) {static $pos = 0; $p->position = $pos++;});
                $this->sem->tools->store();
                $this->render_nothing();
                return;
            }
        }

    }

    public function edittool_action($plugin)
    {
        PageLayout::setTitle(_('Optionen des Werkzeugs ändern'));
        $id = explode('_', $plugin)[1];
        $this->tool = ToolActivation::find([$this->sem->id, $id]);
        if (!$this->tool) {
            return $this->render_nothing();
        }
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            $displayname = trim(Request::get('displayname'));
            if ($displayname !== $this->tool->getDisplayname()) {
                if (strlen($displayname)) {
                    $this->tool->metadata['displayname'] = $displayname;
                } else {
                    unset($this->tool->metadata['displayname']);
                }

            }
            if (Request::get('permission') === 'tutor') {
                $this->tool->metadata['visibility'] = 'tutor';
            } else {
                unset($this->tool->metadata['visibility']);
            }
            if ($this->tool->store()) {
                PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
            }
            $this->redirect($this->url_for('/index'));
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
        if (Request::get('mode') !== null) {
            $_SESSION['plus']['View'] = Request::get('mode');
        }
        if (Request::get('displaystyle') !== null) {
            $_SESSION['plus']['displaystyle'] = Request::get('displaystyle');
        }

        $sidebar = Sidebar::get();

        $widget = new OptionsWidget();
        $widget->setTitle(_('Kategorien'));

        foreach ($_SESSION['plus']['Kategorie'] as $key => $val) {

            if (Request::get(md5('cat_' . $key)) !== null) {
                $_SESSION['plus']['Kategorie'][$key] = Request::get(md5('cat_' . $key));
            }

            if ($_SESSION['plus']['displaystyle'] == 'alphabetical') {
                $_SESSION['plus']['Kategorie'][$key] = 1;
            }

            if ($key == 'Sonstiges') {
                continue;
            }
            $widget->addCheckbox(
                $key,
                $_SESSION['plus']['Kategorie'][$key],
                URLHelper::getURL('?', [md5('cat_' . $key) => 1, 'displaystyle' => 'category']),
                URLHelper::getURL('?', [md5('cat_' . $key) => 0, 'displaystyle' => 'category'])
            );

        }

        $widget->addCheckbox(
            _('Sonstiges'),
            $_SESSION['plus']['Kategorie']['Sonstiges'],
            URLHelper::getURL('?', [md5('cat_Sonstiges') => 1, 'displaystyle' => 'category']),
            URLHelper::getURL('?', [md5('cat_Sonstiges') => 0, 'displaystyle' => 'category'])
        );

        $sidebar->addWidget($widget, 'Kategorien');

        $widget = new ActionsWidget();
        $widget->setTitle(_('Ansichten'));

        if ($_SESSION['plus']['View'] === 'openall') {
            $widget->addLink(
                _('Alles zuklappen'),
                URLHelper::getURL('?', ['mode' => 'closeall']),
                Icon::create('assessment')
            );
        } else {
            $widget->addLink(
                _('Alles aufklappen'),
                URLHelper::getURL('?', ['mode' => 'openall']),
                Icon::create('assessment')
            );
        }

        if ($_SESSION['plus']['displaystyle'] === 'category') {
            $widget->addLink(
                _('Alphabetische Anzeige ohne Kategorien'),
                URLHelper::getURL('?', ['displaystyle' => 'alphabetical']),
                Icon::create('assessment')
            );
        } else {
            $widget->addLink(
                _('Anzeige nach Kategorien'),
                URLHelper::getURL('?', ['displaystyle' => 'category']),
                Icon::create('assessment')
            );
        }

        $sidebar->addWidget($widget, 'ansicht');

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Werkzeugreihenfolge ändern'),
            $this->url_for('/sorttools'),
            Icon::create('arr_2down')
        )->asDialog('size=500;reload-on-close');

        $sidebar->addWidget($actions, 'aktion');


        unset($_SESSION['plus']['Kategorielist']);
        $plusconfig['course_plus'] = $_SESSION['plus'];
        UserConfig::get($GLOBALS['user']->id)->store('PLUS_SETTINGS', $plusconfig);
    }

    private function getSortedList(Range $context)
    {

        $list = [];
        $cat_index = [];

        foreach (PluginEngine::getPlugins('StudipModule') as $plugin) {
            if (!$plugin->isActivatableForContext($context)) {
                continue;
            }



            if (!$this->sem_class->isModuleMandatory(get_class($plugin))
                    && $this->sem_class->isModuleAllowed(get_class($plugin))
            ) {

                $info = $plugin->getMetadata();

                $indcat = isset($info['category']) ? $info['category'] : 'Sonstiges';
                if (!array_key_exists($indcat, $cat_index)) {
                    array_push($cat_index, $indcat);
                }
                $plugin_id = 'plugin_' . $plugin->getPluginId();
                $tool = ToolActivation::find([$context->getRangeId(), $plugin->getPluginId()]);
                $displayname = $info['displayname'] ?? $plugin->getPluginname();
                if ($tool && $tool->metadata['displayname']) {
                    $displayname .= ' (' .$tool->getDisplayname() . ')';
                }
                $visibility = $tool && $tool->metadata['visibility'] ? $tool->metadata['visibility'] : 'autor';

                if ($_SESSION['plus']['displaystyle'] != 'category') {


                    $list['Funktionen von A-Z'][$plugin_id]['object'] = $plugin;
                    $list['Funktionen von A-Z'][$plugin_id]['type'] = 'plugin';
                    $list['Funktionen von A-Z'][$plugin_id]['moduleclass'] = get_class($plugin);
                    $list['Funktionen von A-Z'][$plugin_id]['sorter'] = mb_strtolower($displayname);
                    $list['Funktionen von A-Z'][$plugin_id]['displayname'] = $displayname;
                    $list['Funktionen von A-Z'][$plugin_id]['visibility'] = $visibility;
                } else {

                    $cat = isset($info['category']) ? $info['category'] : 'Sonstiges';

                    if (!isset($_SESSION['plus']['Kategorie'][$cat])) {
                        $_SESSION['plus']['Kategorie'][$cat] = 1;
                    }

                    $list[$cat][$plugin_id]['object'] = $plugin;
                    $list[$cat][$plugin_id]['moduleclass'] = get_class($plugin);
                    $list[$cat][$plugin_id]['type'] = 'plugin';
                    $list[$cat][$plugin_id]['sorter'] = mb_strtolower($displayname);
                    $list[$cat][$plugin_id]['displayname'] = $displayname;
                    $list[$cat][$plugin_id]['visibility'] = $visibility;
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

}
