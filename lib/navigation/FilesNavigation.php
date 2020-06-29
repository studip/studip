<?php

/**
 * FilesNavigation.php - A navigation class for the files main navigation item
 * and its second layer navigation items.
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
class FilesNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Dateien'), 'dispatch.php/files/overview');
        $this->setImage(
            Icon::create(
                'files',
                'navigation',
                ['title' => _('Alle Dateien')]
            )
        );
    }


    /**
     * Initialise the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $this->addSubNavigation(
            'overview',
            new Navigation(_('Übersicht'), 'dispatch.php/files/overview')
        );
        $this->addSubNavigation(
            'my_files',
            new Navigation(_('Persönliche Dateien'), 'dispatch.php/files/index')
        );
        $this->addSubNavigation(
            'search',
            new Navigation(_('Suche'), 'dispatch.php/files_dashboard/search')
        );
    }
}
