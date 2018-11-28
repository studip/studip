<?php
/*
 * QuickSelection.php - widget plugin for start page
 *
 * Copyright (C) 2014 - Nadine Werner <nadwerner@uos.de>
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class QuickSelection extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Schnellzugriff');
    }

    public function getPortalTemplate()
    {
        PageLayout::addScript($this->getPluginUrl() . '/js/QuickSelection.js');
        $names = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'QUICK_SELECTION');

        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('list');
        $template->navigation = $this->getFilteredNavigation($names);

        $navigation = new Navigation('', PluginEngine::getLink($this, array(), 'configuration'));
        $navigation->setImage(Icon::create('edit', 'clickable', ["title" => _('Konfigurieren')]), array('data-dialog'=>'size=auto'));

        $template->icons = array($navigation);

        return $template;
    }

    private function getFilteredNavigation($items)
    {
        $navigation = Navigation::getItem('/start');
        $result = array();

        foreach ($navigation as $name => $nav) {
            if (empty($items) || in_array($name, $items)) {
                $result[] = $nav;
            }
        }

        return $result;
    }

    public function save_action()
    {
        if (get_config('QUICK_SELECTION') === NULL) {
            Config::get()->create('QUICK_SELECTION', array('range' => 'user', 'type' => 'array', 'description' => 'Einstellungen des QuickSelection-Widgets'));
        }

        $names = Request::optionArray('add_removes');
        WidgetHelper::addWidgetUserConfig($GLOBALS['user']->id, 'QUICK_SELECTION', $names);

        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('list');
        $template->navigation = $this->getFilteredNavigation($names);

        header('X-Dialog-Close: 1');
        header('X-Dialog-Execute: STUDIP.QuickSelection.update');

        echo $template->render();
    }

    public function configuration_action()
    {
        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('edit');
        $template->links = Navigation::getItem('/start');
        $template->config = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'QUICK_SELECTION');
        $template->plugin = $this;

        header('X-Title: ' . _('Schnellzugriff konfigurieren'));
        echo $template->render();
    }
}
