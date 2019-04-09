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

        $navigation = new Navigation('', PluginEngine::getLink($this, [], 'configuration'));
        $navigation->setImage(Icon::create('edit', 'clickable', ["title" => _('Konfigurieren')]), ['data-dialog'=>'size=auto']);

        $template->icons = [$navigation];

        return $template;
    }

    private function getFilteredNavigation($items)
    {
        $result = [];

        $navigation = Navigation::getItem('/start');
        foreach ($navigation as $name => $nav) {
            // if config is new (key:value) display values which are not in config array
            // otherwise hide items which are not in config array
            // This is important for patching.
            if (!isset($items[$name]) || $items[$name] !== 'deactivated') {
                $result[] = $nav;
            }
        }

        return $result;
    }

    public function save_action()
    {
        if (Config::get()->QUICK_SELECTION === null) {
            Config::get()->create('QUICK_SELECTION', [
                'range'       => 'user',
                'type'        => 'array',
                'description' => 'Einstellungen des QuickSelection-Widgets',
            ]);
        }

        $add_removes = Request::optionArray('add_removes');

        // invert add_removes so that only unchecked values are stored into config
        $names = [];

        $navigation = Navigation::getItem('/start');
        foreach ($navigation as $name => $nav) {
            if (!in_array($name, $add_removes)) {
                $names[$name] = 'deactivated';
            }

        }

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
