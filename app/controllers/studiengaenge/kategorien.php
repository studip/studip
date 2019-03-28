<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once __DIR__ . '/studiengaenge.php';

class Studiengaenge_KategorienController extends Studiengaenge_StudiengaengeController
{
    /**
     * Liste der Studiengänge gruppiert nach Fachbereiche (Fachbereich ist
     * die  verantwortliche Einrichtung des zugeordneten Faches, nicht die des
     * Studiengangs!)
     */
    public function index_action($studiengang_id = null)
    {
        PageLayout::setTitle(_('Studiengänge gruppiert nach Abschluss-Kategorien'));
        
        // Nur Abschlüsse von Fachbereichen an denen der User eine Rolle hat
        $perm_institutes = MvvPerm::getOwnInstitutes();
        
        $this->initPageParams('kategorien');
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        $abschluss_ids = Abschluss::getAllEnriched(
            null,
            null,
            null,
            null,
            ['mvv_fach_inst.institut_id' => $perm_institutes]
        )->pluck('abschluss_id');
    
        $this->kategorien = AbschlussKategorie::getAllEnriched(
            $this->sortby,
            $this->order,
            null,
            null,
            [
                'mvv_abschl_zuord.abschluss_id' => $abschluss_ids,
                'mvv_studiengang.institut_id'   => $perm_institutes
            ]
        );
        
        if ($studiengang_id) {
            $studiengang = Studiengang::find($studiengang_id);
            $this->details_action($studiengang->abschluss->kategorie_id, $studiengang->id);
        }
        
        $this->setSidebar();
    }
    
    /**
     * shows the studiengaenge of a fachbereich
     * 
     * @param string $fachbereich_id the id of the fachbereich
     */
    public function details_action($kategorie_id, $studiengang_id = null, $stgteil_bez_id = null)
    {
        $perm_institutes = MvvPerm::getOwnInstitutes();
        $this->kategorie_id = $kategorie_id;
        
        $filter = [
            'mvv_abschl_kategorie.kategorie_id' => $this->kategorie_id,
            'mvv_studiengang.institut_id'       => $perm_institutes
        ];
        $this->studiengaenge = Studiengang::getAllEnriched('name', 'ASC', $filter);
        
        if ($studiengang_id) {
            $studiengang = Studiengang::find($studiengang_id);
            $this->studiengang_id = $studiengang->id;
            
            if (count($perm_institutes)) {
                if (!in_array($studiengang->institut_id, $perm_institutes)) {
                    throw new Trails_Exception(403);
                }
            }
            
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