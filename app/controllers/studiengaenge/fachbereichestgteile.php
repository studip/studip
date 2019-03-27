<?php
/**
 * fachbereichestgteile.php - Studiengaenge_FachbereichestgteileController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */


require_once dirname(__FILE__) . '/studiengangteile.php';

class Studiengaenge_FachbereichestgteileController extends Studiengaenge_StudiengangteileController
{
    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung der Studiengangteile - Studiengangteile gruppiert nach Fachbereichen'));
        
        $this->initPageParams();
        $this->initSearchParams();
        
        $search_result = $this->getSearchResult('StudiengangTeil');
        
        // Nur Studiengangteile mit zugeordnetem Fach an dessen verantwortlicher
        // Einrichtung der User eine Rolle hat
        $filter['mvv_fach_inst.institut_id'] = MvvPerm::getOwnInstitutes();
        $this->sortby = $this->sortby ?: 'fachbereich';
        $this->order = $this->order ?: 'ASC';
        //get data
        if (count($search_result)) {
            $filter['mvv_stgteil.stgteil_id'] = $search_result;
        }
        $this->fachbereiche = StudiengangTeil::getAssignedFachbereiche(
            $this->sortby,
            $this->order,
            $filter
        );
        $this->stgteil_ids = $this->search_result;
        $this->show_sidebar_search = true;
        $this->setSidebar();
    }
    
    /**
     * Shows the studiengangteile of a Fachbereich.
     * 
     * @param string $fachbereich_id the id of the Fachbereich
     */
    public function details_fachbereich_action($fachbereich_id)
    {
        $this->details_id = $fachbereich_id;
        $this->stgteile = StudiengangTeil::findByFachbereich(
            $this->details_id,
            array('mvv_stgteil.stgteil_id' => $this->getSearchResult('StudiengangTeil')),
            'fach_name,zusatz,kp',
            'ASC'
        );
        if (Request::isXhr()) {
            $this->render_template('studiengaenge/studiengangteile/details_grouped');
        } else {
            $this->performe_relayed('index');
        }
    }
    
    public function stgteil_fachbereich_action($fachbereich_id)
    {
        $fachbereich = Institute::find($fachbereich_id);
        if ($fachbereich) {
            $this->faecher = Fach::findByFachbereich($fachbereich->getId());
            $this->fachbereich = $fachbereich;
            $this->perform_relayed('stgteil');
        } else {
            throw new Trails_Exception(404);
        }
    }
}