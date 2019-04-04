<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTreeViewAdmin.class.php
// Class to print out the "range tree"
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

use Studip\Button, Studip\LinkButton;

/**
* class to print out the admin view of the "range tree"
*
* This class prints out a html representation of the whole or part of the tree, <br>
* it also contains all functions for administrative tasks on the tree
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipRangeTreeViewAdmin extends TreeView{


    var $tree_status;

    var $mode;

    var $edit_item_id;

    var $move_item_id;

    var $search_result;

    var $msg;

    var $marked_item;

    var $edit_cat_snap;

    var $edit_cat_item_id;

    /**
    * constructor
    *
    * calls the base class constructor, registers a session variable, calls the init function and the command parser
    * @access public
    */
    function __construct(){

        $this->root_content = $GLOBALS['UNI_INFO'];
        parent::__construct("StudipRangeTree"); //calling the baseclass constructor
        $this->marked_item =& $_SESSION['_marked_item'];

        $this->initTreeStatus();
        $this->parseCommand();
    }

    function initTreeStatus()
    {
        $view = DbView::getView('range_tree');
        $user_id = $GLOBALS['auth']->auth['uid'];
        $user_perm = $GLOBALS['auth']->auth['perm'];
        $studip_object_status = null;

        if (is_array($this->open_items)){
            foreach ($this->open_items as $key => $value) {
                if ($key != 'root') {
                    $tmp = $this->tree->getAdminRange($key);

                    if(!empty($tmp)) {
                        foreach($tmp as $i) {
                            if($i) {
                                $studip_object_status[$i] = ($user_perm == "root") ? 1 : -1;
                            }
                        }

                    }
                }
            }
        }

        if (is_array($this->open_ranges)){
            foreach ($this->open_ranges as $key => $value) {
                if ($key != 'root'){
                    $tmp = $this->tree->getAdminRange($key);

                    if(!empty($tmp)) {
                        foreach($tmp as $i) {
                            if($i) {
                                $studip_object_status[$i] = ($user_perm == "root") ? 1 : -1;
                            }
                        }
                    }

                    $tmp = $this->tree->getAdminRange($this->tree->tree_data[$key]['parent_id']);

                    if(!empty($tmp)) {
                        foreach($tmp as $i) {
                            if ($i) {
                                $studip_object_status[$i] = ($user_perm == "root") ? 1 : -1;
                            }
                        }
                    }

                }
            }
        }
        if (is_array($studip_object_status) && $user_perm != 'root'){
            $view->params = array(array_keys($studip_object_status), $user_id);
            $rs = $view->get_query("view:TREE_INST_STATUS");
            while ($rs->next_record()){
                $studip_object_status[$rs->f("Institut_id")] = 1;
            }
            $view->params = array(array_keys($studip_object_status), $user_id);
            $rs = $view->get_query("view:TREE_FAK_STATUS");
            while ($rs->next_record()){
                $studip_object_status[$rs->f("Fakultaets_id")] = 1;
                if ($rs->f("Institut_id") && isset($studip_object_status[$rs->f("Institut_id")])){
                    $studip_object_status[$rs->f("Institut_id")] = 1;
                }
            }
        }
        $studip_object_status['root'] = ($user_perm == "root") ? 1 : -1;
    $this->tree_status = $studip_object_status;
    }

    function parseCommand(){
        if (Request::quoted('mode'))
            $this->mode = Request::quoted('mode');
        if (Request::option('cmd')){
            $exec_func = "execCommand" . Request::option('cmd');
            if (method_exists($this,$exec_func)){
                if ($this->$exec_func()){
                    $this->tree->init();
                    $this->initTreeStatus();
                }
            }
        }
        if ($this->mode == "MoveItem")
            $this->move_item_id = $this->marked_item;
    }

    function execCommandOrderItem(){
        $direction = Request::quoted('direction');
        $item_id = Request::option('item_id');
        $items_to_order = $this->tree->getKids($this->tree->tree_data[$item_id]['parent_id']);
        if (!$this->isParentAdmin($item_id) || !$items_to_order)
            return false;
        for ($i = 0; $i < count($items_to_order); ++$i){
            if ($item_id == $items_to_order[$i])
                break;
        }
        if ($direction == "up" && isset($items_to_order[$i-1])){
            $items_to_order[$i] = $items_to_order[$i-1];
            $items_to_order[$i-1] = $item_id;
        } elseif (isset($items_to_order[$i+1])){
            $items_to_order[$i] = $items_to_order[$i+1];
            $items_to_order[$i+1] = $item_id;
        }
        $view = DbView::getView('range_tree');
        for ($i = 0; $i < count($items_to_order); ++$i){
            $view->params = array($i, $items_to_order[$i]);
            $rs = $view->get_query("view:TREE_UPD_PRIO");
        }
        $this->mode = "";
        $this->msg[$item_id] = "msg§" . (($direction == "up") ? _("Element wurde um eine Position nach oben verschoben.") : _("Element wurde um eine Position nach unten verschoben."));
        return true;
    }

    function execCommandNewItem(){
        $item_id = Request::option('item_id');
        if ($this->isItemAdmin($item_id)){
            $new_item_id = DbView::get_uniqid();
            $this->tree->storeItem($new_item_id,$item_id,_("Neues Element"), $this->tree->getNumKids($item_id) +1);
            $this->anchor = $new_item_id;
            $this->edit_item_id = $new_item_id;
            $this->open_ranges[$item_id] = true;
            $this->open_items[$new_item_id] = true;
            if ($this->mode != "NewItem")
                $this->msg[$new_item_id] = "info§" . _("Wählen Sie einen Namen für dieses Element, oder verlinken Sie es mit einer Einrichtung in Stud.IP");
            $this->mode = "NewItem";
        }
        return false;
    }

    function execCommandSearchStudip(){
        $item_id = Request::option('item_id');
        $parent_id = Request::quoted('parent_id');
        $search_str = Request::quoted('edit_search');
        $view = DbView::getView('range_tree');
        if(mb_strlen($search_str) > 1){
            $view->params[0] = $search_str;
            $rs = $view->get_query("view:TREE_SEARCH_INST");
            while ($rs->next_record()){
                $this->search_result[$rs->f("Institut_id")]['name'] = $rs->f("Name");
                $this->search_result[$rs->f("Institut_id")]['studip_object'] = "inst";
            }
            if ($parent_id == "root"){
                $view->params[0] = $search_str;
                $rs = $view->get_query("view:TREE_SEARCH_FAK");
                while ($rs->next_record()){
                    $this->search_result[$rs->f("Fakultaets_id")]['name'] = $rs->f("Name");
                    $this->search_result[$rs->f("Fakultaets_id")]['studip_object'] = "fak";
                }
            }
        $search_msg = "info§" . sprintf(_("Ihre Suche ergab %s Treffer."),count($this->search_result));
        } else {
            $search_msg = "error§" . _("Sie haben keinen Suchbegriff eingegeben.");
        }
        if ($this->mode == "NewItem"){
            Request::set('item_id', $parent_id);
            $this->execCommandNewItem();
        } else {
            $this->anchor = $item_id;
            $this->edit_item_id = $item_id;
        }
        $this->msg[$this->edit_item_id] = $search_msg;
        return false;
    }

    function execCommandEditItem(){
        $item_id = Request::option('item_id');
        if ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id)){
            $this->mode = "EditItem";
            $this->anchor = $item_id;
            $this->edit_item_id = $item_id;
            $this->msg[$item_id] = "info§" . _("Wählen Sie einen Namen für dieses Element, oder verlinken Sie es mit einer Einrichtung in Stud.IP");
        }
        return false;
    }

    function execCommandInsertItem(){
        $item_id = Request::option('item_id');
        $parent_id = Request::option('parent_id');
        $item_name = Request::quoted('edit_name');
        $tmp = explode(":",Request::quoted('edit_studip_object'));
        if ($tmp[1] == "fak" || $tmp[1] == "inst"){
            $studip_object = $tmp[1];
            $studip_object_id = $tmp[0];
        } else {
            $studip_object = "";
            $studip_object_id = "";
        }
        if ($this->mode == "NewItem" && $item_id){
            if ($this->isItemAdmin($parent_id)){
                $priority = count($this->tree->getKids($parent_id));
                $affected_rows = $this->tree->InsertItem($item_id,$parent_id,$item_name,$priority,$studip_object,$studip_object_id);
                if ($affected_rows){
                    $this->mode = "";
                    $this->anchor = $item_id;
                    $this->open_items[$item_id] = true;
                    $this->msg[$item_id] = "msg§" . _("Dieses Element wurde neu eingefügt.");
                }
            }
        }
        if ($this->mode == "EditItem"){
            if ($this->isParentAdmin($item_id)){
                $affected_rows = $this->tree->UpdateItem($item_name,$studip_object,$studip_object_id,$item_id);
                if ($affected_rows){
                    $this->msg[$item_id] = "msg§" . _("Element wurde geändert.");
                } else {
                    $this->msg[$item_id] = "info§" . _("Keine Veränderungen vorgenommen.");
                }
                $this->mode = "";
                $this->anchor = $item_id;
                $this->open_items[$item_id] = true;
            }
        }
        return true;
    }

    function execCommandInsertFak(){
        $studip_object_id = Request::quoted('insert_fak');
        $parent_id = 'root';
        if ($this->isItemAdmin($parent_id)){
            $item_id = DbView::get_uniqid();
            $priority = count($this->tree->getKids($parent_id));
            $affected_rows = $this->tree->InsertItem($item_id,$parent_id,'',$priority,'fak',$studip_object_id);
            if($affected_rows){
                $view = DbView::getView('range_tree');
                $priority = 0;
                $rs = $view->get_query("SELECT * FROM Institute WHERE fakultaets_id <> Institut_id AND fakultaets_id = '$studip_object_id' ORDER BY Name");
                while($rs->next_record()){
                    $affected_rows += $this->tree->InsertItem(DbView::get_uniqid(),$item_id,addslashes($rs->f('name')),$priority++,'inst',$rs->f('Institut_id'));
                }
                $this->msg[$item_id] = "msg§" . sprintf(_("%s Elemente wurden eingefügt."), $affected_rows);
                $this->mode = "";
                $this->anchor = $item_id;
                $this->open_items[$item_id] = true;
                $this->open_ranges[$item_id] = true;
            }
        }
        return true;
    }

    function execCommandAssertDeleteItem(){
        $item_id = Request::option('item_id');
        if ($this->isParentAdmin($item_id)){
            $this->mode = "AssertDeleteItem";
            $this->msg[$item_id] = "info§" ._("Sie beabsichtigen dieses Element, inklusive aller Unterelemente, zu löschen. ")
                        . sprintf(_("Es werden insgesamt %s Elemente gelöscht!"),count($this->tree->getKidsKids($item_id))+1)
                        . "<br>" . _("Wollen Sie diese Elemente wirklich löschen?") . "<br>"
                        . '<div class="button-group">'
                        . LinkButton::createAccept(_("JA"), URLHelper::getURL($this->getSelf("cmd=DeleteItem&item_id=$item_id")))
                        . LinkButton::createCancel(_("NEIN"), URLHelper::getURL($this->getSelf("cmd=Cancel&item_id=$item_id")))
                        . "</div>";
        }
        return false;
    }

    function execCommandDeleteItem(){
        $item_id = Request::option('item_id');
        $deleted = 0;
        $item_name = $this->tree->tree_data[$item_id]['name'];
        if ($this->isParentAdmin($item_id) && $this->mode == "AssertDeleteItem"){
            $this->anchor = $this->tree->tree_data[$item_id]['parent_id'];
            $items_to_delete = $this->tree->getKidsKids($item_id);
            $items_to_delete[] = $item_id;
            $deleted = $this->tree->DeleteItems($items_to_delete);
            if ($deleted['items']){
                $this->msg[$this->anchor] = "msg§" . sprintf(_("Das Element <b>%s</b> und alle Unterelemente (insgesamt %s) wurden gelöscht. "),htmlReady($item_name),$deleted['items']);
            } else {
                $this->msg[$this->anchor] = "error§" . _("Fehler, es konnten keine Elemente gelöscht werden!");
            }
            if ($deleted['categories']){
                $this->msg[$this->anchor] .= sprintf(_("<br>Es wurden %s Datenfelder gelöscht. "),$deleted['categories']);
            }
            $this->mode = "";
            $this->open_items[$this->anchor] = true;
        }
        return true;
    }

    function execCommandMoveItem(){
        $item_id = Request::option('item_id');
        $this->anchor = $item_id;
        $this->marked_item = $item_id;
        $this->mode = "MoveItem";
        return false;
    }

    function execCommandDoMoveItem(){
        $item_id = Request::option('item_id');
        $item_to_move = $this->marked_item;
        if ($this->mode == "MoveItem" && ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id))
            && ($item_to_move != $item_id) && ($this->tree->tree_data[$item_to_move]['parent_id'] != $item_id)
            && !$this->tree->isChildOf($item_to_move,$item_id)){
            $view = DbView::getView('range_tree');
            $view->params = array($item_id, count($this->tree->getKids($item_id)), $item_to_move);
            $rs = $view->get_query("view:TREE_MOVE_ITEM");
            if ($rs->affected_rows()){
                    $this->msg[$item_to_move] = "msg§" . _("Element wurde verschoben.");
                } else {
                    $this->msg[$item_to_move] = "error§" . _("Keine Verschiebung durchgeführt.");
                }
            }
        $this->anchor = $item_to_move;
        $this->open_ranges[$item_id] = true;
        $this->open_items[$item_to_move] = true;
        $this->mode = "";
        return true;
    }

    function execCommandOrderCat(){
        $item_id = Request::option('item_id');
        $direction = Request::quoted('direction');
        $cat_id = Request::option('cat_id');
        $items_to_order = array();
        if ($this->isItemAdmin($item_id)){
            $range_object = RangeTreeObject::GetInstance($item_id);
            $categories =& $range_object->getCategories();
            while($categories->nextRow()){
                $items_to_order[] = $categories->getField("kategorie_id");
            }
            for ($i = 0; $i < count($items_to_order); ++$i){
                if ($cat_id == $items_to_order[$i])
                    break;
            }
            if ($direction == "up" && isset($items_to_order[$i-1])){
                $items_to_order[$i] = $items_to_order[$i-1];
                $items_to_order[$i-1] = $cat_id;
            } elseif (isset($items_to_order[$i+1])){
                $items_to_order[$i] = $items_to_order[$i+1];
                $items_to_order[$i+1] = $cat_id;
            }
            $view = DbView::getView('range_tree');
            for ($i = 0; $i < count($items_to_order); ++$i){
                $view->params = array($i,$items_to_order[$i]);
                $rs = $view->get_query("view:CAT_UPD_PRIO");
            }
            $this->msg[$item_id] = "msg§" . _("Datenfelder wurden neu geordnet");
        }
        $this->anchor = $item_id;
        return false;
    }

    function execCommandNewCat(){
        $item_id = Request::option('item_id');
        if ($this->isItemAdmin($item_id)){
            $range_object = RangeTreeObject::GetInstance($item_id);
            $this->edit_cat_snap =& $range_object->getCategories();
            $this->edit_cat_snap->result[$this->edit_cat_snap->numRows] =
                array("kategorie_id" => "new_entry", "range_id" => $item_id, "name" => "Neues Datenfeld", "content" => "Neues Datenfeld",
                        "priority" => $this->edit_cat_snap->numRows);
            ++$this->edit_cat_snap->numRows;
            $this->edit_cat_item_id = $item_id;
            $this->mode = "NewCat";
        }
        $this->anchor = $item_id;
        return false;
    }

    function execCommandUpdateCat(){
        $item_id = Request::option('item_id');
        $cat_name = Request::getArray('cat_name');
        $cat_content = Request::getArray('cat_content');
        $cat_prio = Request::getArray('cat_prio');
        $inserted = false;
        $updated = 0;
        if ($this->isItemAdmin($item_id)){
            $view = DbView::getView('range_tree');
            if (isset($cat_name['new_entry'])){
                $view->params = array(DbView::get_uniqid(),$item_id,$cat_name['new_entry'],$cat_content['new_entry'],$cat_prio['new_entry']);
                $rs = $view->get_query("view:CAT_INS_ALL");
                if ($rs->affected_rows()){
                    $inserted = true;
                }
                unset($cat_name['new_entry']);
            }
            foreach ($cat_name as $key => $value){
                $view->params = array($value,$cat_content[$key],$key);
                $rs = $view->get_query("view:CAT_UPD_CONTENT");
                if ($rs->affected_rows()){
                    ++$updated;
                }
            }
            if ($updated){
                $this->msg[$item_id] = "msg§" . sprintf(_("Es wurden %s Datenfelder aktualisiert."),$updated);
                if ($inserted) {
                    $this->msg[$item_id] .= _("Ein neues Datenfeld wurde eingefügt.");
                }
            } elseif ($inserted){
                $this->msg[$item_id] = "msg§" . _("Ein neues Datenfeld wurde eingefügt.");
            } else {
                $this->msg[$item_id] = "info§" . _("Keine Veränderungen vorgenommen.");
            }
        }
        $this->anchor = $item_id;
        $this->mode = "";
        return false;
    }

    function execCommandDeleteCat(){
        $item_id = Request::option('item_id');
        $cat_id = Request::option('cat_id');
        if ($this->isItemAdmin($item_id)){
            $view = DbView::getView('range_tree');
            $view->params[0] = $cat_id;
            $rs = $view->get_query("view:CAT_DEL");
            if ($rs->affected_rows()){
                $this->msg[$item_id] = "msg§" . _("Ein Datenfeld wurde gelöscht.");
            }
        }
        $this->mode = "";
        $this->anchor = $item_id;
        return false;
    }


    function execCommandCancel(){
        $item_id = Request::option('item_id');
        $this->mode = "";
        $this->anchor = $item_id;
        return false;
    }

    function isItemAdmin($item_id){
        $admin_ranges = $this->tree->getAdminRange($item_id);
        for ($i = 0; $i < count($admin_ranges); ++$i){
            if ($this->tree_status[$admin_ranges[$i]] == 1){
                return true;
            }
        }
        return false;
    }

    function isParentAdmin($item_id){
        $admin_ranges = $this->tree->getAdminRange($this->tree->tree_data[$item_id]['parent_id']);
        for ($i = 0; $i < count($admin_ranges); ++$i){
            if ($this->tree_status[$admin_ranges[$i]] == 1){
                return true;
            }
        }
        return false;
    }

    function getItemContent($item_id){

        if ($item_id == $this->edit_item_id )
            return $this->getEditItemContent();
        if ($item_id == $this->move_item_id){
            $this->msg[$item_id] = "info§" . sprintf(_("Dieses Element wurde zum Verschieben markiert. Bitte wählen Sie ein Einfügesymbol %s aus, um das Element zu verschieben."), Icon::create('arr_2right', 'sort', ['title' => "Einfügesymbol"])->asImg(16, ["alt" => "Einfügesymbol"]));
            }
        $content = "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
        $content .= $this->getItemMessage($item_id);
        $content .= "\n<tr><td>";

        if ($this->isItemAdmin($item_id)){
            $content .= LinkButton::create(_("Neues Objekt"),
                URLHelper::getURL($this->getSelf("cmd=NewItem&item_id=$item_id")),
                array('title' => _("Innerhalb dieser Ebene ein neues Element einfügen")));
        }

        if ($this->isParentAdmin($item_id) && $item_id !=$this->start_item_id && $item_id != "root"){
            $content .= LinkButton::create(_("Bearbeiten"),
                URLHelper::getURL($this->getSelf("cmd=EditItem&item_id=$item_id")));

            $content .= LinkButton::create(_("Löschen"),
                URLHelper::getURL($this->getSelf("cmd=AssertDeleteItem&item_id=$item_id")));

            if ($this->move_item_id == $item_id && $this->mode == "MoveItem"){
                $content .= LinkButton::create(_("Abbrechen"),
                    URLHelper::getURL($this->getSelf("cmd=Cancel&item_id=$item_id")));
            } else {
                $content .= LinkButton::create(_("Verschieben"),
                    URLHelper::getURL($this->getSelf("cmd=MoveItem&item_id=$item_id")));
            }
        }

        $content .= "</td></tr></table>";
        $content .= "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
        if ($item_id == "root"){
            if ($this->isItemAdmin($item_id)){
                $view = DbView::getView('range_tree');
                $rs = $view->get_query("SELECT i1.Name,i1.Institut_id,COUNT(i2.Institut_id) as num FROM Institute i1 LEFT JOIN Institute i2 ON i1.Institut_id = i2.fakultaets_id AND i2.fakultaets_id<>i2.Institut_id WHERE i1.fakultaets_id=i1.Institut_id GROUP BY i1.Institut_id ORDER BY Name");
                $content .= "\n<tr><td>";
                $content .= "\n<form action=\"" . URLHelper::getLink($this->getSelf("cmd=InsertFak")) . "\" method=\"post\" class=\"default\">"
                    .  CSRFProtection::tokenTag()
                    . '<div class="col-1"><label>'
                    . _("Stud.IP Fakultät einfügen:")
                    . "\n<select style=\"width:300px;vertical-align:middle;\" name=\"insert_fak\">";
                while($rs->next_record()){
                    $content .= "\n<option value=\"" . $rs->f("Institut_id") . "\">" . htmlReady(my_substr($rs->f("Name") . '('.$rs->f('num').')',0,60)) . "</option>";
                }
                $content .= "</select>"
                    . '</label></div><div class="col-1">'
                    . Button::create(_("Fakultät einfügen"))
                    ."</div></form>";
                $content .= " </td></tr>";
            }
            $content .= "\n<tr><td class=\"table_header_bold\" align=\"left\">" . htmlReady($this->tree->root_name) ." </td></tr>";
            $content .= "\n<tr><td class=\"blank\" align=\"left\">" . htmlReady($this->root_content) ." </td></tr>";
            $content .= "\n</table>";
            return $content;
        }
        $range_object = RangeTreeObject::GetInstance($item_id);
        $name = ($range_object->item_data['type']) ? $range_object->item_data['type'] . ": " : "";
        $name .= $range_object->item_data['name'];
        $content .= "\n<tr><td class=\"table_header_bold\" align=\"left\" style=\"font-size:10pt\">" . htmlReady($name) ." </td></tr>";
        if (is_array($range_object->item_data_mapping)){
            $content .= "\n<tr><td class=\"blank\" align=\"left\" style=\"font-size:10pt\">";
            foreach ($range_object->item_data_mapping as $key => $value){
                if ($range_object->item_data[$key]){
                    $content .= "<b>" . htmlReady($value) . ":</b>&nbsp;";
                    $content .= formatLinks($range_object->item_data[$key]) . "&nbsp; ";
                }
            }
            $content .= "&nbsp;";
        } elseif (!$range_object->item_data['studip_object']){
            $content .= "\n<tr><td class=\"blank\" align=\"left\" style=\"font-size:10pt\">" .
                        _("Dieses Element ist keine Stud.IP-Einrichtung, es hat daher keine Grunddaten.");
        } else {
            $content .= "\n<tr><td class=\"blank\" align=\"left\" style=\"font-size:10pt\">" . _("Keine Grunddaten vorhanden!");
        }
        $content .= "\n<div class=\"blank\" align=\"left\" style=\"font-size:10pt\"><b>" . _("Mitarbeiter:") . "</b> " . $range_object->getNumStaff() . "</b></div>";
        if ($this->isItemAdmin($item_id) && $range_object->item_data['studip_object']){
            $content .= "\n<div class=\"blank\">";

            $content .= LinkButton::create(_("Grunddaten in Stud.IP bearbeiten"), "dispatch.php/institute/basicdata/index?admin_inst_id=" . $range_object->item_data['studip_object_id']);
            $content .= "</div>";
        }

        $content .= "</td></tr><tr><td>&nbsp;</td></tr>";

        if ($this->mode == "NewCat" && ($this->edit_cat_item_id == $item_id)){
            $categories =& $this->edit_cat_snap;
        } else {
            $categories =& $range_object->getCategories();
        }
        if (!$this->isItemAdmin($item_id)){
            if ($categories->numRows){
                while($categories->nextRow()){
                    $content .= "\n<tr><td class=\"table_header_bold\" style=\"font-size:10pt\">" . formatReady($categories->getField("name")) . "</td></tr>";
                    $content .= "\n<tr><td class=\"blank\" style=\"font-size:10pt\">" . formatReady($categories->getField("content")) . "</td></tr>";
                }
            } else {
                $content .= "\n<tr><td class=\"blank\" style=\"font-size:10pt\">" . _("Keine weiteren Daten vorhanden!") . "</td></tr>";
            }
        } else {
            $content .= "<tr><td class=\"blank\" style=\"font-size:10pt\">" . $this->getEditCatContent($item_id,$categories) . "</td></tr>";
        }
        $content .= "</table>";
        return $content;
    }

    function getItemHead($item_id){
        $head = "";
        if ($this->mode == "MoveItem" && ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id))
            && ($this->move_item_id != $item_id) && ($this->tree->tree_data[$this->move_item_id]['parent_id'] != $item_id)
            && !$this->tree->isChildOf($this->move_item_id,$item_id)){
            $head .= "<a href=\"" . URLHelper::getLink($this->getSelf("cmd=DoMoveItem&item_id=$item_id")) . "\">"
            .  Icon::create('arr_2right', 'sort', ['title' => "An dieser Stelle einfügen"])->asImg(16, ["alt" => "An dieser Stelle einfügen"])."</a>&nbsp;";
        }
        $head .= parent::getItemHead($item_id);
        if ($item_id != $this->start_item_id && $this->isParentAdmin($item_id) && $item_id != $this->edit_item_id){
            $head .= "</td><td nowrap align=\"right\" valign=\"bottom\" class=\"printhead\">";
            if (!$this->tree->isFirstKid($item_id)){
                $head .= "<a href=\"". URLHelper::getLink($this->getSelf("cmd=OrderItem&direction=up&item_id=$item_id")) .
                "\">" . Icon::create('arr_2up', 'sort')->asImg(['class' => 'text-top', 'title' => _("Element nach oben verschieben")]) . " ".
                "</a>";
            }
            if (!$this->tree->isLastKid($item_id)){
                $head .= "<a href=\"". URLHelper::getLink($this->getSelf("cmd=OrderItem&direction=down&item_id=$item_id")) .
                "\">" . Icon::create('arr_2down', 'sort')->asImg(['class' => 'text-top', 'title' => _("Element nach unten verschieben")]) . " ".
                "</a>";
            }
            $head .= "&nbsp;";
        }
        return $head;
    }

    function getEditItemContent(){
        ob_start();
        ?>
        <div style="margin: 0px 5%">
            <form name="item_form" method="post" class="default"
                  action="<?= URLHelper::getLink($this->getSelf("cmd=InsertItem&item_id={$this->edit_item_id}")) ?>">
                <?= CSRFProtection::tokenTag(); ?>

                <input type="hidden" name="parent_id" value="<?= $this->tree->tree_data[$this->edit_item_id]['parent_id'] ?>">

                <table>
                    <?= $this->getItemMessage($this->edit_item_id, 2) ?>
                </table>

                <fieldset>
                    <legend>
                        <?= _("Element bearbeiten") ?>
                    </legend>

                    <label>
                        <?= _("Name des Elements") ?>
                        <input type="text" name="edit_name" size="50"
                               value="<?= htmlReady($this->tree->tree_data[$this->edit_item_id]['name']) ?>">
                    </label>
                </fieldset>

                <fieldset>
                    <legend>
                        <?= _("Element mit einer Stud.IP-Einrichtung verlinken") ?>
                    </legend>

                    <label>
                        <?= _("Stud.IP-Einrichtung:") ?>

                        <select name="edit_studip_object" onChange="document.item_form.edit_name.value=document.item_form.edit_studip_object.options[document.item_form.edit_studip_object.selectedIndex].text;">
                            <option value="none"
                                <?= ($this->tree->tree_data[$this->edit_item_id]['studip_object']) ? '' : 'selected' ?>
                            >
                                <?= _("Kein Link") ?>
                            </option>
                            <? if ($this->tree->tree_data[$this->edit_item_id]['studip_object']) : ?>
                            <option selected value="<?= $this->tree->tree_data[$this->edit_item_id]['studip_object_id']
                                .':'. $this->tree->tree_data[$this->edit_item_id]['studip_object'] ?>"
                            >
                                <?= htmlReady($this->tree->tree_data[$this->edit_item_id]['name']) ?>
                            </option>
                            <? endif ?>

                            <? if (is_array($this->search_result) && count($this->search_result)) : ?>
                                <? foreach ($this->search_result as $key => $value) : ?>
                                <option value="<?= $key .':'. $value['studip_object'] ?>">
                                    <?= $value['name'] ?>
                                </option>
                                <? endforeach ?>
                            <? endif ?>
                        </select>
                    </label>

                    <label>
                        <?= _("Stud.IP-Einrichtung suchen:") ?>
                        <input type="text" name="edit_search">
                    </label>
                </fieldset>

                <footer>
                    <?= Button::createAccept(_("Absenden")) ?>
                    <?= Button::create(_("Suchen"), [
                        'formaction' => URLHelper::getURL($this->getSelf("cmd=SearchStudIP&item_id={$this->edit_item_id}"))
                    ]);  ?>

                    <?= LinkButton::createCancel(_("Abbrechen"), URLHelper::getURL($this->getSelf("cmd=Cancel&item_id=" . ($this->mode == "NewItem" ? $this->tree->tree_data[$this->edit_item_id]['parent_id'] : $this->edit_item_id)))) ?>
                </footer>
            </form>
        </div>

        <?
        $content .= ob_get_clean();
        return $content;
    }

    function getEditCatContent($item_id, $cat_snap)
    {
        ob_start();
        ?>
        <form name="cat_form_<?= $item_id ?>" method="post" class="default"
              action="<?= URLHelper::getLink($this->getSelf("cmd=UpdateCat&item_id=$item_id"))  ?>">
            <?= CSRFProtection::tokenTag(); ?>

            <? if ($cat_snap->numRows) while ($cat_snap->nextRow()) :  ?>
                <fieldset>
                    <legend>
                        <!-- change position icons -->
                        <? if ($cat_snap->pos && $cat_snap->getField("kategorie_id") != "new_entry") : ?>
                            <a href="<?= URLHelper::getLink($this->getSelf("cmd=OrderCat&direction=up&item_id=$item_id&cat_id=" . $cat_snap->getField("kategorie_id"))) ?>">
                                <?= Icon::create('arr_2up', 'sort')->asImg(['class' => 'text-top', 'title' => _("Datenfeld nach oben")]) ?>
                            </a>
                        <? endif ?>

                        <? if ($cat_snap->pos != $cat_snap->numRows-1 && $cat_snap->getField("kategorie_id") != "new_entry") : ?>
                            <a href="<?= URLHelper::getLink($this->getSelf("cmd=OrderCat&direction=down&item_id=$item_id&cat_id=" . $cat_snap->getField("kategorie_id"))) ?>">
                                <?= Icon::create('arr_2down', 'sort')->asImg(['class' => 'text-top', 'title' => _("Datenfeld nach unten")]) ?>
                            </a>
                        <? endif ?>

                        <?= _('Datenfeld bearbeiten') ?>

                        <a href="<?= URLHelper::getURL($this->getSelf("cmd=DeleteCat&item_id=$item_id&cat_id=" . $cat_snap->getField("kategorie_id"))) ?>">
                            <?= Icon::create('trash', 'clickable')->asImg(['class' => 'text-top', 'title' => _("Datenfeld löschen")]) ?>
                        </a>
                    </legend>

                    <label>
                        <?= _('Titel') ?>
                        <input type="text" name="cat_name[<?= $cat_snap->getField("kategorie_id") ?>]"
                               value="<?= htmlReady($cat_snap->getField("name")) ?>">
                        <input type="hidden" name="cat_prio[<?= $cat_snap->getField("kategorie_id") ?>]"
                               value="<?= htmlReady($cat_snap->getField("priority")) ?>">
                    </label>

                    <label>
                        <?= _('Inhalt') ?>
                        <textarea name="cat_content[<?= htmlReady($cat_snap->getField("kategorie_id")) ?>]"><?= htmlReady($cat_snap->getField("content")) ?></textarea>
                    </label>
                </fieldset>
            <? endwhile ?>

            <footer>
                <? if ($cat_snap->numRows) : ?>
                <?= Button::create(_("Datenfelder speichern"), 'übernehmen') ?>
                <? endif ?>
                <?= LinkButton::create(_("Neues Datenfeld anlegen"), URLHelper::getURL($this->getSelf("cmd=NewCat&item_id=$item_id"))) ?>
            </footer>
        </form>

        <? $content .= ob_get_clean() . '</td></tr>';

        return $content;
    }

    function getItemMessage($item_id, $colspan = 1)
    {
        $content = "";
        if ($this->msg[$item_id]){
            $msg = explode("§", $this->msg[$item_id]);
            $pics = array(
                'error' => Icon::create('decline', 'attention'),
                'info'  => Icon::create('exclaim', 'inactive'),
                'msg'   => Icon::create('accept', 'accept'));
            $content = "\n<tr><td colspan=\"{$colspan}\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\" style=\"font-size:10pt\">
                        <tr><td class=\"blank\" align=\"center\" width=\"25\">" .  $pics[$msg[0]]->asImg(['class' => 'text-top']) . "</td>
                        <td class=\"blank\" align=\"left\">" . $msg[1] . "</td></tr>
                        </table></td></tr><tr>";
        }
        return $content;
    }

    function getSelf($param = ''){
        $url_params = "foo=" . DbView::get_uniqid();
        if ($this->mode) $url_params .= "&mode=" . $this->mode;
        if ($param) $url_params .= '&' . $param;
        return parent::getSelf($url_params);
    }
}

//test
//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
//include 'lib/include/html_head.inc.php';
//$test = new StudipRangeTreeViewAdmin();
//$test->showTree();
//echo "</table>";
//page_close();
?>
