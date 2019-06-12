<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemSearchHelper.class.php
//
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

class StudipSemSearchHelper {

    public static function GetQuickSearchFields(){
        return [   'all' =>_("alles"),
                        'title_lecturer_number' => _("Titel") . ', ' . _("Lehrende") . ', ' . _("Nummer"),
                        'title' => _("Titel"),
                        'sub_title' => _("Untertitel"),
                        'lecturer' => _("Lehrende"),
                        'number' => _("Nummer"),
                        'comment' => _("Kommentar"),
                        'scope' => _("Bereich")];
    }

    private $search_result;
    private $found_rows = false;
    private $params = [];
    private $visible_only;

    function __construct($form = null, $visible_only = null){
        $params = [];
        if($form instanceof StudipForm){
            foreach($form->getFormFieldsByName(true) as $name){
                $params[$name] = $form->getFormFieldValue($name);
            }
        }
        $this->setParams($params, $visible_only);
    }

    public function setParams($params, $visible_only = null){
        if(isset($params['quick_search']) && isset($params['qs_choose'])){
            if($params['qs_choose'] == 'all'){
                foreach (self::GetQuickSearchFields() as $key => $value){
                    $params[$key] = $this->trim($params['quick_search']);
                }
                $params['combination'] = 'OR';
            } elseif($params['qs_choose'] == 'title_lecturer_number') {
                foreach (explode('_', 'title_lecturer_number') as $key){
                    $params[$key] = $this->trim($params['quick_search']);
                }
                $params['combination'] = 'OR';
            } else {
                $params[$params['qs_choose']] = $this->trim($params['quick_search']);
            }
        }
        if(!isset($params['combination'])) $params['combination'] = 'AND';
        $this->params = $params;
        $this->visible_only = $visible_only;
    }

    public function doSearch(){
        if(!count($this->params)) return false;
        $this->params = array_map('addslashes', $this->params);
        $clause = "";
        $and_clause = "";
        $this->search_result = new DbSnapshot();
        $combination = $this->params['combination'];

        $view = DbView::getView('sem_tree');

        if (isset($this->params['sem']) && $this->params['sem'] != 'all'){
            $sem_number = (int)$this->params['sem'];
            $clause = " HAVING (sem_number <= $sem_number AND (sem_number_end >= $sem_number OR sem_number_end = -1)) ";
        }

        if (isset($this->params['category']) && $this->params['category'] != 'all'){
            foreach($GLOBALS['SEM_TYPE'] as $type_key => $type_value){
                if($type_value['class'] == $this->params['category'])
                    $sem_types[] = $type_key;
            }
        }

        if (isset($this->params['type']) && $this->params['type'] != 'all'){
            unset($sem_types);
            $sem_types[0] = $this->params['type'];
        }
        if (is_array($sem_types)){
            $clause = " AND c.status IN('" . join("','",$sem_types) . "') " . $clause;
        }

        if ($this->params['scope_choose'] && $this->params['scope_choose'] != 'root'){
            $sem_tree = TreeAbstract::GetInstance("StudipSemTree", false);
            $view->params[0] = (is_array($sem_types) ? $sem_types : $sem_tree->sem_status);
            $view->params[1] = $this->visible_only ? "c.visible=1" : "1";

            $view->params[2] = $sem_tree->getKidsKids($this->params['scope_choose']);
            $view->params[2][] = $this->params['scope_choose'];
            $view->params[3] = $clause;
            $snap = new DbSnapshot($view->get_query("view:SEM_TREE_GET_SEMIDS"));
            if ($snap->numRows){
                $clause = " AND c.seminar_id IN('" . join("','",$snap->getRows("seminar_id")) ."')" . $clause;
            } else {
                return 0;
            }
            unset($snap);
        }

        if ($this->params['range_choose'] && $this->params['range_choose'] != 'root'){
            $range_object = RangeTreeObject::GetInstance($this->params['range_choose']);
            $view->params[0] = $range_object->getAllObjectKids();
            $view->params[0][] = $range_object->item_data['studip_object_id'];
            $view->params[1] = ($this->visible_only ? " AND c.visible=1 " : "");
            $view->params[2] = $clause;
            $snap = new DbSnapshot($view->get_query("view:SEM_INST_GET_SEM"));
            if ($snap->numRows){
                $clause = " AND c.seminar_id IN('" . join("','",$snap->getRows("Seminar_id")) ."')" . $clause;
            } else {
                return 0;
            }
            unset($snap);
        }


        if (isset($this->params['lecturer']) && mb_strlen($this->params['lecturer']) > 2){
            $view->params[0] = "%" . $this->trim($this->params['lecturer']) . "%";
            $view->params[1] = "%" . $this->trim($this->params['lecturer']) . "%";
            $view->params[2] = "%" . $this->trim($this->params['lecturer']) . "%";
            $view->params[3] = "%" . $this->trim($this->params['lecturer']) . "%";
            $view->params[4] = "%" . $this->trim($this->params['lecturer']) . "%";
            $result = $view->get_query("view:SEM_SEARCH_LECTURER");

            $lecturers = [];
            while ($result->next_record()) {
                $lecturers[] = $result->f('user_id');
            }

            if (count($lecturers)) {
                $view->params[0] = $this->visible_only ? "c.visible=1" : "1";
                $view->params[1] = $lecturers;
                $view->params[2] = $clause;
                $snap = new DbSnapshot($view->get_query("view:SEM_SEARCH_LECTURER_ID"));
                $this->search_result = $snap;
                $this->found_rows = $this->search_result->numRows;
            }
        }


        if ($combination == "AND" && $this->search_result->numRows){
            $and_clause = " AND c.seminar_id IN('" . join("','",$this->search_result->getRows("seminar_id")) ."')";
        }

        if ((isset($this->params['title']) && mb_strlen($this->params['title']) > 2) ||
            (isset($this->params['sub_title']) && mb_strlen($this->params['sub_title']) > 2) ||
            (isset($this->params['number']) && mb_strlen($this->params['number']) > 2) ||
            (isset($this->params['comment']) && mb_strlen($this->params['comment']) > 2)){

            $toFilter = explode(" ", $this->params['title']);
            $search_for = "(Name LIKE '%" . implode("%' AND Name LIKE '%", $toFilter) . "%')";
            $view->params[0] .= ($this->params['title']) ? $search_for . " " : " ";

            $view->params[0] .= ($this->params['title'] && $this->params['sub_title']) ? $combination : " ";
            $view->params[0] .= ($this->params['sub_title']) ? " Untertitel LIKE '%" . $this->trim($this->params['sub_title']) . "%' " : " ";
            $view->params[0] .= (($this->params['title'] || $this->params['sub_title']) && $this->params['comment']) ? $combination : " ";
            $view->params[0] .= ($this->params['comment']) ? " Beschreibung LIKE '%" . $this->trim($this->params['comment']) . "%' " : " ";
            $view->params[0] .= (($this->params['title'] || $this->params['sub_title'] || $this->params['comment']) && $this->params['number']) ? $combination : " ";
            $view->params[0] .= ($this->params['number']) ? " VeranstaltungsNummer LIKE '%" . $this->trim($this->params['number']) . "%' " : " ";
            $view->params[0] = ($this->visible_only ? " c.visible=1 AND " : "") . "(" . $view->params[0] .")";
            $view->params[1] =  $and_clause . $clause;
            $snap = new DbSnapshot($view->get_query("view:SEM_SEARCH_SEM"));
            if ($this->found_rows === false){
                $this->search_result = $snap;
            } else {
                $this->search_result->mergeSnapshot($snap,"seminar_id",$combination);
            }
            $this->found_rows = $this->search_result->numRows;
        }

        if ($combination == "AND" && $this->search_result->numRows){
            $and_clause = " AND c.seminar_id IN('" . join("','",$this->search_result->getRows("seminar_id")) ."')";
        }

        if (isset($this->params['scope']) && mb_strlen($this->params['scope']) > 2){
            $view->params[0] = $this->visible_only ? "c.visible=1" : "1";
            $view->params[1] = "%" . $this->trim($this->params['scope']) . "%";
            $view->params[2] = $and_clause . $clause;
            $snap = new DbSnapshot($view->get_query("view:SEM_TREE_SEARCH_SEM"));
            if ($this->found_rows === false){
                $this->search_result = $snap;
            } else {
                $this->search_result->mergeSnapshot($snap,"seminar_id",$combination);
            }
            $this->found_rows = $this->search_result->numRows;
        }
        return $this->found_rows;
    }

    public function getSearchResultAsSnapshot(){
        return $this->search_result;
    }

    public function getSearchResultAsArray(){
        if($this->search_result instanceof DBSnapshot && $this->search_result->numRows){
            return array_unique($this->search_result->getRows('seminar_id'));
        } else {
            return [];
        }
    }

    private function trim($what)
    {
        $what = trim($what);
        $what = preg_replace("/^\x{00A0}+|\x{00A0}+$/Su", '', $what);
        return $what;
    }
}
