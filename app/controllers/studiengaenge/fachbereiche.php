<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once dirname(__FILE__) . '/studiengaenge.php';

class Studiengaenge_FachbereicheController extends Studiengaenge_StudiengaengeController
{
    /**
     * Liste der Studiengänge gruppiert nach Fachbereiche (Fachbereich ist
     * die  verantwortliche Einrichtung des zugeordneten Faches, nicht die des
     * Studiengangs!)
     */
    public function index_action($studiengang_id = null)
    {
        PageLayout::setTitle(_('Studiengänge gruppiert nach Fachbereichen'));
        
        // Nur Fachbereiche an denen der User eine Rolle hat
        $perm_institutes = MvvPerm::getOwnInstitutes();
        
        $this->initPageParams('fachbereiche');
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        $this->fachbereiche = Fachbereich::getFachbereiche(
            $this->sortby,
            $this->order,
            ['Institute.Institut_id' => $perm_institutes]
        );
        
        if ($studiengang_id) {
            $studiengang = Studiengang::find($studiengang_id);
            $this->details_action($studiengang->institut_id, $studiengang->id);
        }

        $this->setSidebar();
    }
    
    /**
     * shows the studiengaenge of a fachbereich
     * 
     * @param string $fachbereich_id the id of the fachbereich
     */
    public function details_action($fachbereich_id, $studiengang_id = null, $stgteil_bez_id = null)
    {
        $perm_institutes = MvvPerm::getOwnInstitutes();
        $this->fachbereich_id = $fachbereich_id;
        if (count($perm_institutes)) {
            if (!in_array($this->fachbereich_id, $perm_institutes)) {
                throw new Trails_Exception(403);
            }
        }
        
        $this->parent_id = $this->fachbereich_id;
        $this->studiengaenge = Studiengang::findByFachbereich($this->fachbereich_id);
        if ($studiengang_id) {
            $this->studiengang_id = $studiengang_id;
            $this->set_studiengangteile($studiengang_id, $stgteil_bez_id);
        }
        
        if (Request::isXhr()) {
            if ($this->studiengang) {
                if ($this->studiengang->typ == 'einfach') {
                    $this->render_template('studiengaenge/studiengaenge/studiengangteile');
                } else {
                    if ($stgteil_bez_id) {
                        $this->render_template('studiengaenge/studiengaenge/studiengangteile');
                    } else {
                        $this->render_template('studiengaenge/studiengaenge/stgteil_bezeichnungen');
                    }
                }
            } else {
                $this->render_template('studiengaenge/studiengaenge/details');
            }
        } else {
            $this->perform_relayed('index');
        }
    }
}