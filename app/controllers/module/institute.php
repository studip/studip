<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once __DIR__ . '/module.php';

class Module_InstituteController extends Module_ModuleController
{
    public $institut_id;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        URLHelper::bindLinkParam('institut_id', $this->institut_id);

        if (Request::isXhr()) {
            $this->set_layout(null);
        }
    }

    /**
     * Lists all institutes responsible for modules.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Module gruppiert nach verantwortlichen Einrichtungen'));

        $this->initPageParams();
        $this->initSearchParams('module');

        $search_result = $this->getSearchResult('Modul');

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $filter = array_merge(
            [
                'mvv_modul.modul_id'         => $search_result,
                'mvv_modul_inst.gruppe'      => 'hauptverantwortlich',
                'mvv_modul_inst.institut_id' => MvvPerm::getOwnInstitutes()
            ],
            (array)$this->filter
        );
    
        $this->institute = Modul::getAllAssignedInstitutes(
            $this->sortby, $this->order, $filter
        );

        $this->modul_ids = $search_result;
        $this->show_sidebar_search = true;
        $this->show_sidebar_filter = true;
        $this->setSidebar();
    }

    /**
     * Shows all modules by a given institute that is responsible for them.
     */
    public function details_action($modul_id = null, $modulteil_id = null)
    {
        $institut = Institute::find(Request::option('institut_id'));
        if (!$institut) {
            throw new Exception(_('Unbekannte Einrichtung.'));
        }

        $this->inst_id = $institut->id;
        if ($modul_id) {
            $this->modul = Modul::find($modul_id);
            if ($this->modul) {
                $this->modul_id = $this->modul->id;
            }
        }
        if ($modulteil_id) {
            $this->modulteil = Modulteil::find($modulteil_id);
            if ($this->modulteil) {
                $this->modulteil_id = $this->modulteil->id;
            }
        }

        $search_result = $this->getSearchResult('Modul');

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $own_institutes = MvvPerm::getOwnInstitutes();
        if (count($own_institutes)) {
            $institute_filter = array_intersect($own_institutes,
                [$this->inst_id]
            );
        } else {
            $institute_filter = $this->inst_id;
        }
    
        $filter = array_merge(
            (array)$this->filter,
            [
                'mvv_modul.modul_id'         => $search_result,
                'mvv_modul_inst.gruppe'      => 'hauptverantwortlich',
                'mvv_modul_inst.institut_id' => $this->inst_id
            ]
        );
        $this->module = Modul::findByInstitut('bezeichnung', 'ASC', $filter);

        if (Request::isXhr()) {
            if ($this->modul_id) {
                $this->render_template('module/module/details');
            } else {
                $this->render_template('module/institute/details');
            }
        } else {
            $this->redirect($this->url_for('/index'));
        }
    }
}