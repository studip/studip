<?php

/**
 * ContensDashboardNavigation.php - navigation for contents dashboard
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @category    Stud.IP
 */
class ContentsNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Inhalte'));

        $this->setImage(Icon::create('content', 'navigation', ['title' => _('Inhalte')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $overview = new Navigation(_('Inhaltsübersicht'));
        $overview->addSubNavigation('index', new Navigation(_('Übersicht'), 'dispatch.php/contents/overview'));

        $this->addSubNavigation('overview', $overview);

        $courseware = new Navigation(_('Courseware'));
        $courseware->addSubNavigation('projects', new Navigation(_('Projekte'), 'dispatch.php/contents/courseware/index'));
        $courseware->addSubNavigation('courseware', new Navigation(_('Inhalt'), 'dispatch.php/contents/courseware/courseware'));
        $courseware->addSubNavigation('courseware_manager', new Navigation(_('Verwaltung'), 'dispatch.php/contents/courseware/courseware_manager'));
        $courseware->addSubNavigation('bookmarks', new Navigation(_('Lesezeichen'), 'dispatch.php/contents/courseware/bookmarks'));

        $this->addSubNavigation('courseware', $courseware);

        $this->addSubNavigation('files', new Navigation(_('Dateien'), 'dispatch.php/contents/files'));
        $this->addSubNavigation('oer', new Navigation(_('OER Campus'), 'dispatch.php/contents/oer'));
    }
}
