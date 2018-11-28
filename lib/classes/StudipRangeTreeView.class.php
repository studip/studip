<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTreeView.class.php
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

/**
* class to print out the "range tree"
*
* This class prints out a html representation of the whole or part of the tree
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipRangeTreeView extends TreeView{

    /**
    * constructor
    *
    * @access public
    */
    function __construct(){
        $this->root_content = $GLOBALS['UNI_INFO'];
        parent::__construct("StudipRangeTree"); //calling the baseclass constructor
    }

    function getItemContent($item_id){
        $content = "\n<table width=\"100%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
        if ($item_id == "root"){
            $content .= "\n<tr><td class=\"table_header_bold\" align=\"left\">" . htmlReady($this->tree->root_name) ." </td></tr>";
            $content .= "\n<tr><td class=\"blank\" align=\"left\">" . htmlReady($this->root_content) ." </td></tr>";
            $content .= "\n</table>";
            return $content;
        }
        $range_object = RangeTreeObject::GetInstance($item_id);
        $name = ($range_object->item_data['type']) ? $range_object->item_data['type'] . ": " : "";
        $name .= $range_object->item_data['name'];
        $content .= "\n<tr><td class=\"table_header_bold\" align=\"left\">" . htmlReady($name) ." </td></tr>";
        if (is_array($range_object->item_data_mapping)){
            $content .= "\n<tr><td class=\"blank\" align=\"left\">";
            foreach ($range_object->item_data_mapping as $key => $value){
                if ($range_object->item_data[$key]){
                    $content .= "<b>" . htmlReady($value) . ":</b>&nbsp;";
                    $content .= formatLinks($range_object->item_data[$key]) . "&nbsp; ";
                }
            }
            $content .= "</td></tr><tr><td class=\"blank\" align=\"left\">" .
                    "<a href=\"".URLHelper::getLink("dispatch.php/institute/overview?auswahl=".$range_object->item_data['studip_object_id'])."\"" .
                    tooltip(_("Seite dieser Einrichtung in Stud.IP aufrufen")) . ">" .
                    htmlReady($range_object->item_data['name']) . "</a>&nbsp;" ._("in Stud.IP") ."</td></tr>";

        } elseif (!$range_object->item_data['studip_object']){
            $content .= "\n<tr><td class=\"blank\" align=\"left\">" .
                        _("Dieses Element ist keine Stud.IP-Einrichtung, es hat daher keine Grunddaten.") . "</td></tr>";
        } else {
            $content .= "\n<tr><td class=\"blank\" align=\"left\">" . _("Keine Grunddaten vorhanden!") . "</td></tr>";
        }
        $content .= "\n<tr><td>&nbsp;</td></tr>";
        $kategorien =& $range_object->getCategories();
        if ($kategorien->numRows){
            while($kategorien->nextRow()){
                $content .= "\n<tr><td class=\"table_header_bold\">" . htmlReady($kategorien->getField("name")) . "</td></tr>";
                $content .= "\n<tr><td class=\"blank\">" . formatReady($kategorien->getField("content")) . "</td></tr>";
            }
        } else {
            $content .= "\n<tr><td class=\"blank\">" . _("Keine weiteren Daten vorhanden!") . "</td></tr>";
        }
        $content .= "</table>";
        return $content;
    }
}
//test
//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
//include 'lib/include/html_head.inc.php';
//$test = new StudipRangeTreeView();
//$test->showTree();
//echo "</table>";
//page_close();
