<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemRangeTreeViewSimple.class.php
// Class to print out the seminar tree
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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
* class to print out the range tree
*
* This class prints out a html representation a part of the tree
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipSemRangeTreeViewSimple {


    var $tree;
    var $show_entries;

    /**
    * constructor
    *
    * @access public
    */
    function __construct($start_item_id = "root", $sem_number = false, $sem_status = false, $visible_only = false){
        $this->start_item_id = ($start_item_id) ? $start_item_id : "root";
        $this->root_content = $GLOBALS['UNI_INFO'];
        $args = null;
        if ($sem_number !== false){
            $args['sem_number'] = $sem_number;
        }
        if ($sem_status !== false){
            $args['sem_status'] =  $sem_status;
        }
        $args['visible_only'] = $visible_only;
        $this->tree = TreeAbstract::GetInstance("StudipRangeTree",$args);
        if (!$this->tree->tree_data[$this->start_item_id]){
            $this->start_item_id = "root";
        }
    }

    public function showSemRangeTree($start_id = null)
    {
        echo '
            <table class="show-tree">
                <tr>
                    <td style="text-align:left; vertical-align:top; font-size:10pt; padding-bottom: 10px;">
                        <div style="font-size:10pt; margin-left:10px">' .
                            $this->getSemPath($start_id);
                                echo '
                                <div class="sem_path_info">
                                    <div class="sem_path_title">'.
                                    formatReady($this->tree->getValue($this->start_item_id, 'name')).
                                    '</div>
                                    <div class="sem_path_text">' .
                                    
                                    formatReady($this->getTooltip($this->start_item_id)) .
                                    '</div>
                                </div>';
                    echo        
                        '</div>
                    </td>
                    <td nowrap style="text-align:right; vertical-align:top; padding-top: 1em;">';
        if ($this->start_item_id != 'root') {
            echo '
                <a href="' .
                    URLHelper::getLink($this->getSelf('start_item_id=' . $this->tree->tree_data[$this->start_item_id]['parent_id'], false)) .
                    '">' .
                    Icon::create('arr_2left', 'clickable')->asImg(['class' => 'text-top', 'title' =>_('eine Ebene zurück')]) .
                '</a>';
        } else {
            echo '&nbsp;';
        }
        echo '
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;" class="b-top-va-center">';
        $this->showKids($this->start_item_id);
        echo '
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:left;" class="b-top-va-center">';
        $this->showContent($this->start_item_id);
        echo '
                    </td>
                </tr>
            </table>';
    }

    function showKids($item_id){
        $num_kids = $this->tree->getNumKids($item_id);
        $kids = $this->tree->getKids($item_id);
        $kids_table = '
          <table class="show-tree-kids">
            <tr>
                <td class="table_row_even kids-tree-row" align="left" valign="top">
                    <ul class="semtree">';
        for ($i = 0; $i < $num_kids; ++$i){
            $num_entries = $this->tree->getNumEntries($kids[$i],true);
            $kids_table .=  "<li><a " . tooltip(sprintf(_("%s Einträge in allen Unterebenen vorhanden"), $num_entries)) . " href=\"" .URLHelper::getLink($this->getSelf("start_item_id={$kids[$i]}", false)) . "\">";
            $kids_table .=  htmlReady($this->tree->tree_data[$kids[$i]]['name']);
            $kids_table .=  " ($num_entries)";
            $kids_table .=  "</a></li>";
            if ($i == ceil($num_kids / 2)-1){
                $kids_table .= '
                    </ul>
                </td>
                <td class="table_row_even kids-tree-row" align="left" valign="top">
                    <ul class="semtree">';
            }
        }
        if (!$num_kids){
            $kids_table .=  "<li>";
            $kids_table .=  _("Auf dieser Ebene existieren keine weiteren Unterebenen.");
            $kids_table .=  "</li>";
        }
        $kids_table .=  "</ul></td></tr></table>";
        echo $kids_table;
    }

    function getTooltip($item_id){
        if ($item_id == "root"){
            $ret = ($this->root_content) ? $this->root_content : _("Keine weitere Info vorhanden");
        } else {
            $range_object = RangeTreeObject::GetInstance($item_id);
            if (is_array($range_object->item_data_mapping)){
                foreach ($range_object->item_data_mapping as $key => $value){
                    if ($range_object->item_data[$key]){
                        $info .= $value . ": ";
                        $info .= $range_object->item_data[$key].  " ";
                    }
                }
            }
            $ret = ($info) ? $info :  _("Keine weitere Info vorhanden");
        }
        return $ret;
    }

    public function getInfoIcon($item_id)
    {
        if ($item_id === 'root') {
            $info = $this->root_content;
        }
        $ret = $info ? tooltipicon(kill_format($info)) : '';
        return $ret;
    }

    function showContent($item_id){
        echo "\n<div align=\"left\" style=\"margin-left:10px;margin-top:10px;margin-bottom:10px;font-size:10pt\">";
        if ($item_id != "root"){
            
            if ($num_entries = $this->tree->getNumEntries($item_id)){
                if ($this->show_entries != "level"){
                    echo "<a " . tooltip(_("alle Einträge auf dieser Ebene anzeigen")) ." href=\"" . URLHelper::getLink($this->getSelf("cmd=show_sem_range_tree&item_id=$item_id")) ."\">";
                }
                printf(_("<b>%s</b> Einträge auf dieser Ebene.&nbsp;"),$num_entries);
                if ($this->show_entries != "level"){
                    echo "</a>";
                }
            } else {
                    echo _("Keine Einträge auf dieser Ebene vorhanden!");
            }
            if ($this->tree->hasKids($item_id) && ($num_entries = $this->tree->getNumEntries($this->start_item_id,true))){
                echo "&nbsp;&nbsp;&sol;&nbsp;&nbsp;";
                if ($this->show_entries != "sublevels"){
                    echo "<a " . tooltip(_("alle Einträge in allen Unterebenen anzeigen")) ." href=\"" . URLHelper::getLink($this->getSelf("cmd=show_sem_range_tree&item_id={$this->start_item_id}_withkids")) ."\">";
                }
                printf(_("<b>%s</b> Einträge in allen Unterebenen vorhanden"), $num_entries);
                if ($this->show_entries != "sublevels"){
                    echo "</a>";
                }
            }
        }
        echo "\n</div>";
    }

    public function getSemPath($start_id = null)
    {
        $parents = $this->tree->getParents($this->start_item_id);
        if ($parents) {
            $add_item = false;
            $start_id = $start_id === null ? 'root' : $start_id;
            for($i = count($parents) - 1; $i >= 0; --$i) {
                if ($add_item || $start_id == $parents[$i]) {
                    $ret .= ($add_item === TRUE ? '&nbsp;&sol;&nbsp;' : '')
                            . '<a href="'
                            . URLHelper::getLink($this->getSelf('start_item_id=' . $parents[$i], false))
                            . '">'
                            . (($add_item === FALSE) ? Icon::create('institute', 'clickable')->asImg(24,['role'=>'root-icon']) : htmlReady($this->tree->tree_data[$parents[$i]]['name']))
                            . '</a>';
                    $add_item = true;
                }
            }
        }
        if ($this->start_item_id == 'root') {
            $ret = '<a href="'
                    . URLHelper::getLink($this->getSelf('start_item_id=root', false))
                    . '">'
                    . Icon::create('institute', 'clickable')->asImg(24,['role'=>'root-icon'])
                    . '</a>';
        } else {
            $ret .= '&nbsp;&sol;&nbsp;<a href="'
                    . URLHelper::getLink($this->getSelf('start_item_id=' . $this->start_item_id, false))
                    . '">'
                    . htmlReady($this->tree->tree_data[$this->start_item_id]['name'])
                    . '</a>';
        }
        $ret .= '&nbsp;&sol;&nbsp;&nbsp;';
        return $ret;
    }



    function getSelf($param = '', $with_start_item = true){
        $url_params = (($with_start_item) ? 'start_item_id=' . $this->start_item_id . '&' : '') . $param ;
        return '?' . $url_params;
    }
}
?>
