<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once __DIR__ . '/studiengangteile.php';

class Studiengaenge_FaecherController extends Studiengaenge_StudiengangteileController
{
    public function index_action()
    {
        $this->initPageParams();
        $this->initSearchParams();
        
        $search_result = $this->getSearchResult('StudiengangTeil');
        
        // Nur Studiengangteile mit zugeordnetem Fach an dessen verantwortlicher
        // Einrichtung der User eine Rolle hat
        $filter['mvv_fach_inst.institut_id'] = MvvPerm::getOwnInstitutes();
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        //get data
        if (count($search_result)) {
            $this->faecher = Fach::findByIdsStgteile(
                $search_result,
                $this->sortby, $this->order,
                self::$items_per_page,
                self::$items_per_page * ($this->page - 1),
                $filter
            );
            $this->count = count($search_result);
        } else {
            $this->faecher = Fach::getAllEnrichedByStgteile(
                $this->sortby,
                $this->order,
                self::$items_per_page,
                self::$items_per_page * ($this->page - 1),
                $filter
            );
            if (count($this->faecher) === 0) {
                PageLayout::postInfo(_('Es wurden noch keine Studiengangteile angelegt.'));
            }
            $this->count = StudiengangTeil::getCountAssignedFaecher($filter);
        }
        if (!isset($this->fach_id)) {
            $this->fach_id = null;
        }
        $this->show_sidebar_search = true;
        $this->setSidebar();
    
        PageLayout::setTitle(sprintf(
            _('Verwaltung der Studiengangteile - Studiengangteile gruppiert nach Fächern (%u)'),
            $this->count
        ));
    }
    
    /**
     * Shows the studiengangteile of a Fach.
     * 
     * @param string $fach_id the id of the Fach
     */
    public function details_fach_action($fach_id)
    {
        $this->fach = Fach::get($fach_id);
        if ($this->fach->isNew()) {
            $this->details_id = null;
        } else {
            $this->details_id = $this->fach->id;
        }
        $this->stgteile = StudiengangTeil::findByFach(
            $this->details_id,
            ['mvv_stgteil.stgteil_id' => $this->getSearchResult('StudiengangTeil')],
            'zusatz,kp',
            'ASC'
        );
        if (Request::isXhr()) {
            $this->render_template('studiengaenge/studiengangteile/details_grouped');
        } else {
            $this->perform_relayed('index');
        }
    }
    
    public function stgteil_fach_action($fach_id)
    {
        $fach = Fach::find($fach_id);
        if ($fach) {
            $this->stgteil = StudiengangTeil::get();
            $this->stgteil->assignFach($fach->getId());
        } else {
            throw new Trails_Exception(404);
        }
        $this->perform_relayed('stgteil');
    }
}