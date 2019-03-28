<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once __DIR__ . '/studiengaenge.php';

class Studiengaenge_AbschluesseController extends Studiengaenge_StudiengaengeController {
    
    /**
     * Liste der Studiengänge gruppiert nach Fachbereiche (Fachbereich ist
     * die  verantwortliche Einrichtung des zugeordneten Faches, nicht die des
     * Studiengangs!)
     */
    public function index_action($studiengang_id = null)
    {
        PageLayout::setTitle(_('Studiengänge gruppiert nach Abschlüssen'));
        
        // Nur Abschlüsse von Fachbereichen an denen der User eine Rolle hat
        $perm_institutes = MvvPerm::getOwnInstitutes();
        
        $this->initPageParams('abschluesse');
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        $this->abschluesse = Abschluss::getAllEnriched(
            $this->sortby,
            $this->order,
            null,
            null,
            ['mvv_fach_inst.institut_id' => $perm_institutes]
        );
        
        if ($studiengang_id) {
            $studiengang = Studiengang::find($studiengang_id);
            $this->details_action($studiengang->abschluss_id, $studiengang->id);
        }

        $this->setSidebar();
    }
    
    /**
     * shows the studiengaenge of a fachbereich
     * 
     * @param string $fachbereich_id the id of the fachbereich
     */
    public function details_action($abschluss_id, $studiengang_id = null, $stgteil_bez_id = null)
    {
        $perm_institutes = MvvPerm::getOwnInstitutes();
        $abschluss = Abschluss::find($abschluss_id);
        if (!$abschluss) {
            throw new Trails_Exception(404);
        }
        $this->abschluss_id = $abschluss->id;
        if (count($perm_institutes)) {
            $institutes_abschluss = array_intersect(
                array_keys($abschluss->getAssignedInstitutes()),
                $perm_institutes
            );
            if (!count($institutes_abschluss)) {
                throw new Trails_Exception(403);
            }
            $this->studiengaenge = SimpleORMapCollection::createFromArray(
                Studiengang::findByAbschluss_id($this->abschluss_id)
            )->findBy('institut_id', $institutes_abschluss);
        } else {
            $this->studiengaenge = Studiengang::findByAbschluss_id($this->abschluss_id);
        }
        
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