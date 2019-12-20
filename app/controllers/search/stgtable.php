<?php
/**
 * matrix.php - Search_MatrixController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */


require_once dirname(__FILE__) . '/studiengaenge.php';

class Search_StgtableController extends Search_StudiengaengeController
{

    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        MVVController::before_filter($action, $args);

        // set navigation
        Navigation::activateItem('/search/courses/module');

        $sidebar = Sidebar::get();

        $views = new ViewsWidget();
        $views->addLink(_('Modulsuche'), $this->url_for('search/module'));
        $views->addLink(_('Studienangebot'), $this->url_for('search/angebot'));
        $views->addLink(_('Studiengänge'), $this->url_for('search/studiengaenge'));
        $views->addLink(_('Fach-Abschluss-Kombinationen'), $this->url_for('search/stgtable'))
                ->setActive(true);

        $sidebar->addWidget($views);

        $this->breadcrumb = new BreadCrumb();
        $this->action = $action;
        $this->verlauf_url = 'search/stgtable/verlauf';
        PageLayout::setTitle(_('Modulverzeichnis - Fach-Abschluss-Kombinationen'));
    }

    public function index_action()
    {
        $this->kategorien = [];
        foreach (AbschlussKategorie::getAllEnriched() as $kategorie) {
            if ($kategorie->count_studiengaenge) {
                $this->kategorien[$kategorie->id]  = $kategorie;
            }
        }

        $public_status = [];

        // combine all Studiengänge with the same name to one entry
        $this->stgs = [];
        foreach (SimpleORMapCollection::createFromArray(Studiengang::getAll())
                    ->orderBy('name') as $studiengang) {
            // show only public visible
            if ($studiengang->hasPublicStatus()) {
                $this->stgs[(string) $studiengang->name][$studiengang->abschluss->kategorie_id] = $studiengang->id;
            }
        }

        $this->breadcrumb->init();
        $this->breadcrumb->append(_('Fach-Abschluss-Kombinationen'), 'index');
    }

    public function matrix_detail_action($fach_id, $abschluss_id, $studiengang_id = null)
    {
        $this->relocate('detail/', $fach_id, $abschluss_id, $studiengang_id);
    }
}
