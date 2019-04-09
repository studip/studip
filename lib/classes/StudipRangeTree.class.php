<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTree.class.php
// Class to handle structure of the "range tree"
//
// Copyright (c) 2002 André Noack <noack@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
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

/**
* class to handle the "range tree"
*
* This class provides an interface to the structure of the "range tree"
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipRangeTree extends TreeAbstract {

    var $sem_number;

    var $sem_status;

    var $sem_dates;

    /**
    * constructor
    *
    * do not use directly, call TreeAbstract::GetInstance("StudipRangeTree")
    * @access private
    */
    function __construct($args) {
        DbView::addView('range_tree');

        $this->root_name = Config::get()->UNI_NAME_CLEAN;
        $this->studip_objects['inst'] = ['pk' => 'Institut_id', 'table' => 'Institute'];
        $this->studip_objects['fak'] = ['pk' => 'Institut_id', 'table' => 'Institute'];
        if (isset($args['sem_number']) ){
            $this->sem_number = array_map('intval', $args['sem_number']);
        }
        if (isset($args['sem_status']) ){
            $this->sem_status = array_map('intval', $args['sem_status']);
        }
        $this->visible_only = (int)$args['visible_only'];
        parent::__construct(); //calling the baseclass constructor
        $this->sem_dates = SemesterData::GetSemesterArray();
    }

    /**
    * initializes the tree
    *
    * stores all rows from table range_tree in array $tree_data
    * @access public
    */
    function init(){
        parent::init();
        $this->tree_data['root']['studip_object_id'] = 'root';
        $db = $this->view->get_query("view:TREE_GET_DATA");
        while ($db->next_record()){
            $item_name = $db->f("name");
            if ($db->f("studip_object")){
                $item_name = $db->f("studip_object_name");
            }
            $this->tree_data[$db->f("item_id")] = ["studip_object" => $db->f("studip_object"),
                                                    "studip_object_id" => $db->f("studip_object_id"),
                                                    "fakultaets_id" => $db->f("fakultaets_id"),"entries" => 0];
            $this->storeItem($db->f("item_id"), $db->f("parent_id"), $item_name, $db->f("priority"));
        }
    }

    function initEntries(){
        $this->view->params[0] = (isset($this->sem_status)) ? " AND d.status IN('" . join("','", $this->sem_status) . "')" : " ";
        $this->view->params[0] .= $this->visible_only ? " AND visible=1 " : "";
        $this->view->params[1] = (isset($this->sem_number)) ? " WHERE ((" . $this->view->sem_number_sql
                                . ") IN (" . join(",",$this->sem_number) .") OR ((" . $this->view->sem_number_sql
                                .") <= " . $this->sem_number[count($this->sem_number)-1]
                                . "  AND ((" . $this->view->sem_number_end_sql . ") >= " . $this->sem_number[count($this->sem_number)-1]
                                . " OR (" . $this->view->sem_number_end_sql  . ") = -1))) " : "";

        $db = $this->view->get_query("view:TREE_GET_SEM_ENTRIES");
        while ($db->next_record()){
            $this->tree_data[$db->f("item_id")]['entries'] = $db->f('entries');
        }
        $this->entries_init_done = true;
    }

    /**
    * Returns Stud.IP range_id of the next "real" object
    *
    * This function finds the next item wich is a real Stud.IP Object, either an "Einrichtung" or a "Fakultaet"<br>
    * useful for the user rights management
    * @access   public
    * @param    string  $item_id
    * @return   array   of primary keys from table "institute"
    */
    function getAdminRange($item_id) {
        if (!$this->tree_data[$item_id]) {
            return false;
        }
        
        $found = false;
        $ret = false;
        $next_link = $item_id;
        
        while(($next_link = $this->getNextLink($next_link)) != 'root') {
            if ($this->tree_data[$next_link]['studip_object'] == 'inst') {
                $found[] = $next_link;
            }
            
            if ($this->tree_data[$next_link]['studip_object'] == 'fak') {
                if (is_array($found) && count($found)) {
                    foreach($found as $f) {
                        if ($this->tree_data[$f]['fakultaets_id'] == $this->tree_data[$next_link]['studip_object_id']) {
                            $ret[] = $this->tree_data[$f]['studip_object_id'];
                        }
                    }
                    
                    $ret[] = $this->tree_data[$next_link]['studip_object_id'];
                } else {
                    $ret[] = $this->tree_data[$next_link]['studip_object_id'];
                }
                break;
            }
            $next_link = $this->tree_data[$next_link]['parent_id'];
        }
        
        if (!$ret){
            $ret[] = $next_link;
        }
        
        return $ret;
    }
    /**
    * returns the next item_id upwards the tree which is a Stud.IP object
    *
    * help function for getAdminRange()
    *
    * @access   private
    * @param    string  $item_id
    * @return   string
    */

    function getNextLink($item_id){
    if (!$this->tree_data[$item_id])
            return false;
        $ret_id = $item_id;
        while (!$this->tree_data[$ret_id]['studip_object_id']){
            $ret_id = $this->tree_data[$ret_id]['parent_id'];
        }
        return $ret_id;
    }

    function getSemIds($item_id,$ids_from_kids = false){
        if (!$this->tree_data[$item_id])
            return false;
        if ($ids_from_kids){
            $this->view->params[0] = $this->getKidsKids($item_id);
        }
        $this->view->params[0][] = $item_id;
        $this->view->params[1] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end >= " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : " ";
        $ret = false;
        $rs = $this->view->get_query("view:RANGE_TREE_GET_SEMIDS");
        while($rs->next_record()){
            $ret[] = $rs->f(0);
        }
        return $ret;
    }

    function getNumEntries($item_id, $num_entries_from_kids = false){
        if (!$this->tree_data[$item_id])
            return false;
        if (!$this->entries_init_done) $this->initEntries();

        return parent::getNumEntries($item_id, $num_entries_from_kids);
    }

    function InsertItem($item_id, $parent_id, $item_name, $priority,$studip_object,$studip_object_id){
        $view = new DbView();
        $view->params = [$item_id,$parent_id,$item_name,$priority,$studip_object,$studip_object_id];
        $rs = $view->get_query("view:TREE_INS_ITEM");
        return $rs->affected_rows();
    }

    function UpdateItem($item_name,$studip_object,$studip_object_id,$item_id){
        $view = new DbView();
        $view->params = [$item_name,$studip_object,$studip_object_id,$item_id];
        $rs = $view->get_query("view:TREE_UPD_ITEM");
        return $rs->affected_rows();
    }

    function DeleteItems($items_to_delete){
        $view = new DbView();
        $view->params[0] = (is_array($items_to_delete)) ? $items_to_delete : [$items_to_delete];
        $view->auto_free_params = false;
        $rs = $view->get_query("view:TREE_DEL_ITEM");
        $deleted['items'] = $rs->affected_rows();
        $rs = $view->get_query("view:CAT_DEL_RANGE");
        $deleted['categories'] = $rs->affected_rows();
        return $deleted;
    }
}
?>
