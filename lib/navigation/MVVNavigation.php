<?php
# Lifter010: TODO
/*
 * MVVNavigation.php - navigation for MVV pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */

class MVVNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Module'));

        $this->setImage(Icon::create('learnmodule', 'navigation', ['title' => _('Module')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $auth, $perm;

        parent::initSubNavigation();

        $stg_navigation = new Navigation(_('Studiengänge'));
        $stg_navigation->addSubNavigation('studiengaenge',
                new Navigation(_('Studiengänge'),
                'dispatch.php/studiengaenge/studiengaenge'));
        $stg_navigation->addSubNavigation('studiengangteile',
                new Navigation(_('Studiengangteile'),
                'dispatch.php/studiengaenge/studiengangteile'));
        $stg_navigation->addSubNavigation('versionen',
                new Navigation(_('Versionen'),
                'dispatch.php/studiengaenge/versionen'));
        $stg_navigation->addSubNavigation('stgteilbezeichnungen',
                new Navigation(_('Studiengangteil-Bezeichnungen'),
                'dispatch.php/studiengaenge/stgteilbezeichnungen'));
        $stg_navigation->addSubNavigation('informationen',
                new Navigation(_('Studiengangsinformationen'),
                'dispatch.php/studiengaenge/informationen'));
        $this->addSubNavigation('studiengaenge', $stg_navigation);

        $modul_navigation = new Navigation(_('Module'));
        $modul_navigation->addSubNavigation('module',
                new Navigation(_('Module'),
                'dispatch.php/module/module'));
        $this->addSubNavigation('module', $modul_navigation);

        $lvg_navigation = new Navigation(_('LV-Gruppen'));
        $lvg_navigation->addSubNavigation('lvgruppen',
                new Navigation(_('Lehrveranstaltungsgruppen'),
                'dispatch.php/lvgruppen/lvgruppen'));
        $this->addSubNavigation('lvgruppen', $lvg_navigation);

        $fa_navigation = new Navigation(_('Fächer/Abschlüsse'));
        $fa_navigation->addSubNavigation(
                'faecher', new Navigation(_('Fächer'),
                'dispatch.php/fachabschluss/faecher'));
        $fa_navigation->addSubNavigation(
                'abschluesse', new Navigation(_('Abschlüsse'),
                'dispatch.php/fachabschluss/abschluesse'));
        $fa_navigation->addSubNavigation(
                'kategorien', new Navigation(_('Abschluss-Kategorien'),
                'dispatch.php/fachabschluss/kategorien'));
        $this->addSubNavigation('fachabschluss', $fa_navigation);

        $dok_navigation = new Navigation(_('Materialien/Dokumente'));
        $dok_navigation->addSubNavigation(
            'files', new Navigation(_('Dokumente'),
            'dispatch.php/materialien/files'));
        // $dok_navigation->addSubNavigation(
        //         'dokumente', new Navigation(_('Materialien/Dokumente'),
        //         'dispatch.php/materialien/dokumente'));
        $this->addSubNavigation('materialien', $dok_navigation);

        $contact_navigation = new Navigation(_('Ansprechpartner'));
        $contact_navigation->addSubNavigation(
                'index', new Navigation(_('Ansprechpartner'),
                'dispatch.php/shared/contacts'));
        $this->addSubNavigation('contacts', $contact_navigation);

    }

}
