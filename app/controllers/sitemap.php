<?php
# Lifter010: TODO
/*
 * SitemapController - Shows a global sitemap for all available pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.0
 */

/**
 * The sitemap is only visible for logged in users, because the sitemap depends
 * on the permissions of the user.
 */
class SitemapController extends AuthenticatedController
{
    /**
     * The only method, loads the navigation object and displays it.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Sitemap'));

        foreach (Navigation::getItem('/start') as $nav) {
            $nav->setEnabled(false);
        }

        $this->navigation = Navigation::getItem('/');
        $this->quicklinks = Navigation::getItem('/links');
        $this->footer     = Navigation::getItem('/footer');
        
        // Add sidebar
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $info = new SidebarWidget();
        $info->setTitle(_('Hinweise'));
        $info->addElement(new WidgetElement(_('Auf dieser Seite finden Sie eine Übersicht über alle verfügbaren Seiten.')));
        $sidebar->addWidget($info);
    }
}
