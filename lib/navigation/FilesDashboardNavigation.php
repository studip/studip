<?php

/**
 * FilesDashboardNavigation.php - navigation for files dashboard
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
class FilesDashboardNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Dateien'));

        $this->setImage(Icon::create('files', 'navigation', ['title' => _('Zum Dashboard des Dateimanagements')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $this->addSubNavigation('dashboard', new Navigation(_('Dateien'), 'dispatch.php/files_dashboard'));
        $this->addSubNavigation('files', new Navigation(_('Meine Dateien'), 'dispatch.php/files'));
        $this->addSubNavigation('search', new Navigation(_('Suche'), 'dispatch.php/files_dashboard/search'));

    }
}
