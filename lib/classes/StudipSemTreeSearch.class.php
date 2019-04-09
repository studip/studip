<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemTreeSearch.class.php
// Class to build search formular and execute search
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

/**
* Class to build search formular and execute search
*
*
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package  DBTools
**/
class StudipSemTreeSearch {

    var $view;

    var $num_search_result = false;

    var $num_inserted;

    var $num_deleted;

    var $form_name;

    var $tree;

    var $seminar_id;

    var $institut_id = [];

    var $sem_tree_ranges = [];

    var $sem_tree_ids = [];

    var $selected = [];

    var $search_result = [];

    function __construct($seminar_id,$form_name = "search_sem_tree", $auto_search = true){
        $this->view = DbView::getView('sem_tree');
        $this->form_name = $form_name;
        $this->tree = TreeAbstract::GetInstance("StudipSemTree", false);
        $this->seminar_id = $seminar_id;
        $this->view->params[0] = $seminar_id;
        $rs = $this->view->get_query("view:SEM_GET_INST");
        while($rs->next_record()){
            $this->institut_id[] = $rs->f(0);
        }
        $this->init();
        if($auto_search){
            $this->doSearch();
        }
    }

    function init(){
        $this->sem_tree_ranges = [];
        $this->sem_tree_ids = [];
        $this->selected = [];
        $this->view->params[0] = $this->seminar_id;
        $rs = $this->view->get_query("view:SEMINAR_SEM_TREE_GET_IDS");
        while($rs->next_record()){
            if (!$this->tree->hasKids($rs->f("sem_tree_id"))){
                $this->sem_tree_ranges[$rs->f("parent_id")][] = $rs->f("sem_tree_id");
                $this->sem_tree_ids[] = $rs->f("sem_tree_id");
                $this->selected[$rs->f("sem_tree_id")] = true;
            }
        }
    }
    /* fuzzy !!!
    function getExpectedRanges(){
        $this->view->params[0] = $this->institut_id;
        $this->view->params[1] = $this->sem_tree_ids;
        $rs = $this->view->get_query("view:SEMINAR_SEM_TREE_GET_EXP_IDS");
        while ($rs->next_record()){
            if (!$this->tree->hasKids($rs->f("sem_tree_id"))){
                $this->sem_tree_ranges[$rs->f("parent_id")][] = $rs->f("sem_tree_id");
                $this->sem_tree_ids[] = $rs->f("sem_tree_id");
            }
        }
    }
    */

    //not fuzzy
    function getExpectedRanges(){
        $this->view->params[0] = $this->institut_id;
        $rs = $this->view->get_query("view:SEM_TREE_GET_FAK");
        while($rs->next_record()){
            $the_kids = $this->tree->getKidsKids($rs->f("sem_tree_id"));
            $the_kids[] = $rs->f("sem_tree_id");
            for ($i = 0; $i < count($the_kids); ++$i){
                if (!$this->tree->hasKids($the_kids[$i]) && !in_array($the_kids[$i],$this->sem_tree_ids)){
                    $this->sem_tree_ranges[$this->tree->tree_data[$the_kids[$i]]['parent_id']][] = $the_kids[$i];
                    $this->sem_tree_ids[] = $the_kids[$i];
                }
            }
        }
    }

    function prepRangePath($path, $cols) {
        $parts=explode(">",$path);
        $paths=[];
        $currpath="";
        foreach ($parts as $part) {
            if (mb_strlen($part)>$cols) {
                $p=my_substr($part, 0, $cols);
            } else {
                $p = $part;
            }
            if (mb_strlen($currpath)+mb_strlen($p)+3 > $cols) {
                $paths[]=htmlReady($currpath);
                $currpath="   >> " . $p;
            } else {
                if (count($paths)==0 && mb_strlen($currpath)==0) {
                    $currpath.=$p;
                } else {
                    $currpath.=" > ".$p;
                }
            }
        }
        $paths[]=htmlReady($currpath);
        return $paths;
    }

    function getChooserField($attributes = [], $cols = 70, $field_name = 'chooser'){
        if ($this->institut_id){
            $this->getExpectedRanges();
        }
        $element_name = "{$this->form_name}_{$field_name}[]";
        $ret = "\n<div class=\"selectbox\"";
        foreach ($attributes as $key => $value){
            $ret .= " " . $key . "=\"" . htmlReady($value) . "\"";
        }
        $ret .= ">";
        foreach ($this->sem_tree_ranges as $range_id => $sem_tree_id){
            $paths=$this->prepRangePath($this->getPath($range_id), $cols);
            foreach ($paths as $p) {
                $ret .= "\n<div style=\"margin-top:5px;font-weight:bold;color:red;\">" . $p ."</div>";
            }
            $ret .= "\n<div style=\"font-weight:bold;color:red;\">" . str_repeat("¯",$cols) . "</div>";
            for ($i = 0; $i < count($sem_tree_id); ++$i){
                $id = $this->form_name . '_' . $field_name . '_' . $sem_tree_id[$i];
                $ret .= "\n<div>";
                $ret .= "\n<label for=\"$id\"><input style=\"vertical-align:middle;\" id=\"$id\" type=\"checkbox\" name=\"$element_name\" value=\"".$sem_tree_id[$i]."\" " . (($this->selected[$sem_tree_id[$i]]) ? " checked " : "");
                $ret .= ">&nbsp;";
                $ret .= "<span ". (($this->search_result[$sem_tree_id[$i]]) ? " style=\"color:blue;\" " : "") . ">";
                $ret .= htmlReady(my_substr($this->tree->tree_data[$sem_tree_id[$i]]['name'],0,$cols)) . "</span></label>";
                $ret .= "\n</div>";
            }
        }
        $ret .= "</div>";
        return $ret;
    }

    function getPath($item_id,$delimeter = ">"){
        return $this->tree->getShortPath($item_id);
    }


    function getSearchField($attributes = []){
        $ret = "\n<input type=\"text\" name=\"{$this->form_name}_search_field\"";
        foreach ($attributes as $key => $value){
            $ret .= " " . $key . "=\"" . htmlReady($value) ."\"";
        }
        $ret .= ">";
        return $ret;
    }

    function getSearchButton($attributes = [])
    {
        $ret = Icon::create('search', 'clickable', ['title' => _('Suche nach Studienbereichen starten')])
                ->asInput(["type" => "image", "class" => "middle", "name" => $this->form_name . "_do_search"]);

        return $ret;
    }

    function getFormStart($action = ""){
        if (!$action){
            $action = URLHelper::getLink();
        }
        $ret = "\n<form action=\"$action\" method=\"post\" name=\"{$this->form_name}\">";
        $ret .= CSRFProtection::tokenTag();
        return $ret;
    }

    function getFormEnd(){

        return "\n<input type=\"hidden\" name=\"{$this->form_name}_send\" value=\"1\">\n</form>";
    }

    function doSearch(){
        if (Request::submitted($this->form_name . "_do_search") || Request::submitted($this->form_name . "_send")){
            if(mb_strlen($_REQUEST[$this->form_name . "_search_field"]) > 2){
                $this->view->params[0] = "%" . Request::quoted($this->form_name . "_search_field") . "%";
                $this->view->params[1] = $this->sem_tree_ids;
                $rs = $this->view->get_query("view:SEM_TREE_SEARCH_ITEM");
                while($rs->next_record()){
                    $this->sem_tree_ranges[$rs->f("parent_id")][] = $rs->f("sem_tree_id");
                    $this->sem_tree_ids[] = $rs->f("sem_tree_id");
                    $this->search_result[$rs->f("sem_tree_id")] = true;
                }
                $this->num_search_result = $rs->num_rows();
            }
            $this->search_done = true;
        }
        return;
    }

    public function insertSelectedRanges($selected = null)
    {
        if (!$selected){
            $selected = array_filter(Request::quotedArray("{$this->form_name}_chooser"));
        }

        if (is_array($selected)){
            $count_intersect = count(array_intersect($selected,array_keys($this->selected)));
            if (count($this->selected) != $count_intersect || count($selected) != $count_intersect){
                $count_del = (count($this->selected)) ? $this->tree->DeleteSemEntries(array_keys($this->selected),$this->seminar_id) : 0;
                for ($i = 0; $i < count($selected); ++$i){
                    $new_selected[$selected[$i]] = true;
                    $count_ins += $this->tree->InsertSemEntry($selected[$i], $this->seminar_id);
                }
                $this->num_inserted = $count_ins - $count_intersect;
                $this->num_deleted = $count_del - $count_intersect;
                $this->selected = $new_selected;
            }
        }
    }
}
