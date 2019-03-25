<?php
/*
 * news.php - News controller
 *
 * Copyright (C) 2014 - Nadine Werner <nadwerner@uos.de>
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'app/controllers/news.php';

class NewsWidget extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Ankündigungen');
    }

    function getPortalTemplate()
    {
        $dispatcher = new StudipDispatcher();
        $controller = new NewsController($dispatcher);
        $response = $controller->relay('news/display/studip');
        $template = $GLOBALS['template_factory']->open('shared/string');
        $template->content = $response->body;

        if (StudipNews::CountUnread() > 0) {
            $navigation = new Navigation('', PluginEngine::getLink($this, array(), 'read_all'));
            $navigation->setImage(Icon::create('refresh', 'clickable', ["title" => _('Alle als gelesen markieren')]));
            $icons[] = $navigation;
        }

        if (Config::get()->NEWS_RSS_EXPORT_ENABLE) {
            if ($rss_id = StudipNews::GetRssIdFromRangeId('studip')) {
                $navigation = new Navigation('', 'rss.php', array('id' => $rss_id));
                $navigation->setImage(Icon::create('rss', 'clickable', ["title" => _('RSS-Feed')]));
                $icons[] = $navigation;
            }
        }

        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation = new Navigation('', 'dispatch.php/news/edit_news/new/studip');
            $navigation->setImage(Icon::create('add', 'clickable', ["title" => _('Ankündigungen bearbeiten')]), ['rel' => 'get_dialog']);
            $icons[] = $navigation;
            if (Config::get()->NEWS_RSS_EXPORT_ENABLE) {
                $navigation = new Navigation('', 'dispatch.php/news/rss_config/studip');
                $navigation->setImage(Icon::create('rss+add', 'clickable', ["title" => _('RSS-Feed konfigurieren')]), ["rel" => 'size=auto']);
                $icons[] = $navigation;
            }
        }

        $template->icons = $icons;

        return $template;
    }


    public function perform($unconsumed)
    {
        if ($unconsumed !== 'read_all') {
            return;
        }

        $global_news = StudipNews::GetNewsByRange('studip', true);
        foreach ($global_news as $news) {
            object_add_view($news['news_id']);
            object_set_visit($news['news_id'], 'news');
        }

        if (Request::isXhr()) {
            echo json_encode(true);
        } else {
            PageLayout::postMessage(MessageBox::success(_('Alle Ankündigungen wurden als gelesen markiert.')));
            header('Location: '. URLHelper::getLink('dispatch.php/start'));
        }
    }
}
