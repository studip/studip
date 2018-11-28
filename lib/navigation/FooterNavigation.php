<?php
/*
 * FooterNavigation.php - navigation for the footer on every page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.1
 */

class FooterNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Footer'));
    }

    public function initSubNavigation()
    {
        parent::initSubNavigation();

        // sitemap
        if (is_object($GLOBALS['user']) && $GLOBALS['user']->id !== 'nobody') {
            $this->addSubNavigation('sitemap', new Navigation(_('Sitemap'), 'dispatch.php/sitemap/'));
        }

        //studip
        $this->addSubNavigation('studip', new Navigation(_('Stud.IP'), 'http://www.studip.de/'));

        // imprint
        $this->addSubNavigation('siteinfo', new Navigation(_('Impressum'), 'dispatch.php/siteinfo/show?cancel_login=1'));

        // Datenschutzerklärung
        $this->addSubNavigation('privacy', new Navigation(_('Datenschutz'), Config::get()->PRIVACY_URL));
    }
}
