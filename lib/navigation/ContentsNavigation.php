<?php

/**
 * ContensDashboardNavigation.php - navigation for contents dashboard
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Ron Lucke <lucke@elan-ev.de>
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
        global $perm;

        $overview = new Navigation(_('Inhaltsübersicht'));
        $overview->addSubNavigation(
            'index',
            new Navigation(_('Übersicht'), 'dispatch.php/contents/overview')
        );

        $this->addSubNavigation('overview', $overview);


        $courseware = new Navigation(_('Courseware'));
        $courseware->addSubNavigation(
            'projects',
            new Navigation(_('Übersicht'), 'dispatch.php/contents/courseware/index')
        );
        $courseware->addSubNavigation(
            'courseware',
            new Navigation(_('Persönliche Lernmaterialien'), 'dispatch.php/contents/courseware/courseware')
        );
        $courseware->addSubNavigation(
            'courseware_manager',
            new Navigation(_('Verwaltung persönlicher Lernmaterialien'), 'dispatch.php/contents/courseware/courseware_manager')
        );
        $courseware->addSubNavigation(
            'bookmarks',
            new Navigation(_('Lesezeichen'), 'dispatch.php/contents/courseware/bookmarks')
        );
        $courseware->addSubNavigation(
            'courses_overview',
            new Navigation(_('Meine Veranstaltungen'), 'dispatch.php/contents/courseware/courses_overview')
        );

        $this->addSubNavigation('courseware', $courseware);


        $files = new Navigation(_('Dateien'));
        $files->addSubNavigation(
            'overview',
            new Navigation(_('Übersicht'), 'dispatch.php/files/overview')
        );
        $files->addSubNavigation(
            'my_files',
            new Navigation(_('Persönliche Dateien'), 'dispatch.php/files/index')
        );
        $files->addSubNavigation(
            'search',
            new Navigation(_('Suche'), 'dispatch.php/files_dashboard/search')
        );

        $this->addSubNavigation('files', $files);


        if (Config::get()->OERCAMPUS_ENABLED && $perm && $perm->have_perm(Config::get()->OER_PUBLIC_STATUS)) {
            $oer = new Navigation(Config::get()->OER_TITLE);
            $oer->addSubNavigation(
                'market',
                new Navigation(Config::get()->OER_TITLE, 'dispatch.php/oer/market')
            );
    
            if ($perm->have_perm('autor')) {
                $oer->addSubNavigation(
                    'mymaterial',
                    new Navigation(_('Meine Materialien'), 'dispatch.php/oer/mymaterial')
                );
            }

            $this->addSubNavigation('oer', $oer);
        }
    }
}
