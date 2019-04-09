<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// TreeView.class.php
// Class to print out html represantation of a tree object based on TreeAbstract.class.php
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
* Class to print out html represantation of a tree object based on TreeAbstract.class.php
*
* Class to print out html represantation of a tree object based on TreeAbstract.class.php
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class TreeView {

    /**
    * Reference to the tree structure
    *
    * @access   private
    * @var  object StudipRangeTree $tree
    */
    var $tree;
    /**
    * name of used tree class
    *
    * @access   private
    * @var  string $tree_class_name
    */
    var $tree_class_name;
    /**
    * contains the item with the current html anchor
    *
    * @access   public
    * @var  string  $anchor
    */
    var $anchor;
    /**
    * array containing all open items
    *
    * this is a reference to a global session variable, managed by PHPLib
    * @access   public
    * @var  array   $open_items
    */
    var $open_items;
    /**
    * array containing all open item nodes
    *
    * this is a reference to a global session variable, managed by PHPLib
    * @access   public
    * @var  array   $open_ranges
    */
    var $open_ranges;
    /**
    * the item to start with
    *
    * @access   private
    * @var  string  $start_item_id
    */
    var $start_item_id;
    /**
    * the content of the root element
    *
    * @access   public
    * @var  string  $root_content
    */
    var $root_content;

    /**
    * the maximum amount of columns in a title
    *
    * @access   public
    * @var  string  $max_cols
    */
    var $max_cols = 80;

    /**
    * draw red icons
    *
    * @access   public
    * @var  boolean $use_aging
    */
    var $use_aging = false;

    /**
    * constructor
    *
    * @access public
    * @param    string  $tree_class_name    name of used tree class
    * @param    mixed   $args               argument passed to the tree class
    */
    public function __construct($tree_class_name,$args = null)
    {
        $this->tree_class_name = $tree_class_name;
        $this->tree            = TreeAbstract::GetInstance($tree_class_name, $args);
        // TODO Die Logik hinter forumgrau2 und forumgraurunt2 muss
        // komplett erneuert werden; dann können auch Instanzen der
        // Klasse "Icon" verwendet werden.
        $this->pic_open        = $this->use_aging
                               ? 'forumgraurunt2.png'
                               : 'icons/blue/arr_1down.svg';
        $this->pic_close       = $this->use_aging
                               ? 'forumgrau2.png'
                               : 'icons/blue/arr_1right.svg';

        URLHelper::bindLinkParam('open_ranges', $this->open_ranges);
        URLHelper::bindLinkParam('open_items', $this->open_items);

        $this->handleOpenRanges();
    }

    /**
    * manages the link parameters used for the open/close thing
    *
    * @access   private
    */
    private function handleOpenRanges()
    {
        $close_range = Request::option('close_range');
        if ($close_range) {
            if ($close_range === 'root'){
                $this->open_ranges = null;
                $this->open_items  = null;
            } else {
                $kidskids   = $this->tree->getKidsKids($close_range);
                $kidskids[] = $close_range;
                foreach ($kidskids as $kid) {
                    unset($this->open_ranges[$kid]);
                    unset($this->open_items[$kid]);
                }
            }
            $this->anchor = $close_range;
        }

        $open_range = Request::option('open_range');
        if ($open_range) {
            $kidskids   = $this->tree->getKidsKids($open_range);
            $kidskids[] = $open_range;
            foreach ($kidskids as $kid) {
                $this->open_ranges[$kid] = true;
            }
            $this->anchor = $open_range;
        }

        $toggle_item = Request::option('close_item') ?: Request::option('open_item');
        if ($toggle_item){
            if ($this->open_items[$toggle_item]) {
                unset($this->open_items[$toggle_item]);
            } else {
                $this->openItem($toggle_item);
                $this->openRange($toggle_item);
            }
            $this->anchor = $toggle_item;
        }

        if (Request::option('item_id')) {
            $this->anchor = Request::option('item_id');
        }
    }

    function openItem($item_id)
    {
        $this->open_items[$item_id] = true;
        $this->openRange($this->tree->tree_data[$item_id]['parent_id']);
    }

    function openRange($item_id)
    {
        $this->open_ranges[$item_id] = true;

        $parents = $this->tree->getParents($item_id);
        foreach ($parents as $parent) {
            $this->open_ranges[$parent] = true;
        }
    }

    /**
    * prints out the tree beginning with a given item
    *
    * @access   public
    * @param    string  $item_id
    */
    function showTree($item_id = "root"){
    $items = [];
    if (!is_array($item_id)){
        $items[0] = $item_id;
        $this->start_item_id = $item_id;
    } else {
        $items = $item_id;
    }
    $num_items = count($items);
    for ($j = 0; $j < $num_items; ++$j){
        $this->printLevelOutput($items[$j]);
        $this->printItemOutput($items[$j]);
        if ($this->tree->hasKids($items[$j]) && $this->open_ranges[$items[$j]]) {
            $this->showTree($this->tree->tree_childs[$items[$j]]);
        }
    }
    return;
}

    /**
    * prints out the lines before an item ("Strichlogik" (c) rstockm)
    *
    * @access   private
    * @param    string  $item_id
    */
    function printLevelOutput($item_id)
    {
        $level_output = "";
        if ($item_id != $this->start_item_id){
            if ($this->tree->isLastKid($item_id))
                $level_output = "<td class=\"blank tree-indent\" valign=\"top\" nowrap>"
                              . Assets::img('forumstrich2.gif')
                              . "</td>"; //last
            else
                $level_output = "<td class=\"blank tree-indent\" valign=\"top\" nowrap>"
                              . Assets::img('forumstrich3.gif')
                              . "</td>"; //crossing
            $parent_id = $item_id;
            while($this->tree->tree_data[$parent_id]['parent_id'] != $this->start_item_id){
                $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];
                if ($this->tree->isLastKid($parent_id))
                    $level_output = "<td class=\"blank tree-indent\" valign=\"top\" width=\"10\" nowrap>"
                                  . Assets::img('forumleer.gif', ['size' => '10@20'])
                                  . "</td>" . $level_output; //nothing
                else
                    $level_output = "<td class=\"blank tree-indent\" valign=\"top\" nowrap>"
                                  . Assets::img('forumstrich.gif')
                                  . "</td>" . $level_output; //vertical line
            }
        }
        echo "\n<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr>$level_output";
        return;
    }

    /**
    * prints out one item
    *
    * @access   private
    * @param    string  $item_id
    */
    function printItemOutput($item_id)
    {
        echo $this->getItemHeadPics($item_id);
        echo "\n<td class=\"printhead\" nowrap width=\"1\" valign=\"middle\">";
        if ($this->anchor == $item_id)
            echo "<a name=\"anchor\">";
        echo Assets::img('forumleer.gif', ['size' => '1@20']);
        if ($this->anchor == $item_id)
            echo "</a>";
        echo "\n</td><td class=\"printhead\" align=\"left\" width=\"99%\" nowrap valign=\"bottom\">";
        echo $this->getItemHead($item_id);
        echo "</td></tr></table>";
        if ($this->open_items[$item_id]) {
            $this->printItemDetails($item_id);
        }
        return;
    }

    /**
    * prints out the details for an item, if item is open
    *
    * @access   private
    * @param    string  $item_id
    */
    function printItemDetails($item_id){
        if (!$this->tree->hasKids($item_id) || !$this->open_ranges[$item_id] || $item_id == $this->start_item_id)
            $level_output = "<td class=\"blank\" background=\"" . Assets::image_path('forumleer.gif') . "\">"
                          . Assets::img('forumleer.gif', ['size' => '10@20'])
                          . "</td>" . $level_output;
        else
            $level_output = "<td class=\"blank\" background=\"" . Assets::image_path('forumstrich.gif') . "\">"
                          . Assets::img('forumleer.gif', ['size' => '10@20'])
                          . "</td>" . $level_output;

        if (($this->tree->isLastKid($item_id) && !($item_id == $this->start_item_id)) || (!$this->open_ranges[$item_id] && $item_id == $this->start_item_id) || ($item_id == $this->start_item_id && !$this->tree->hasKids($item_id)))
            $level_output = "<td class=\"blank\" background=\"" . Assets::image_path('forumleer.gif') . "\">"
                          . Assets::img('forumleer.gif', ['size' => '10@20'])
                          . "</td>" . $level_output;
        else
            $level_output = "<td class=\"blank\" background=\"" . Assets::image_path('forumstrich.gif') ."\">"
                          . Assets::img('forumleer.gif', ['size' => '10@20'])
                          . "</td>" . $level_output;
        if ($item_id != $this->start_item_id){
            $parent_id = $item_id;
            while($this->tree->tree_data[$parent_id]['parent_id'] != $this->start_item_id){
                $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];
                if ($this->tree->isLastKid($parent_id))
                    $level_output = "<td class=\"blank\" background=\"" . Assets::image_path('forumleer.gif') . "\">"
                                  . Assets::img('forumleer.gif', ['size' => '10@20'])
                                  . "</td>" . $level_output; //nothing
                else
                    $level_output = "<td class=\"blank\" background=\"" . Assets::image_path('forumstrich.gif') . "\">"
                                  . Assets::img('forumleer.gif', ['size' => '10@20'])
                                  . "</td>" . $level_output; //vertical line
            }
        }
        //$level_output = "<td class=\"blank\" background=\"".$GLOBALS['ASSETS_URL']."images/forumleer.gif\" ><img src=\"".$GLOBALS['ASSETS_URL']."images/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" ></td>" . $level_output;

        echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>$level_output";
        echo "<td class=\"printcontent\" width=\"100%\"><br>";
        echo $this->getItemContent($item_id);
        echo "<br></td></tr></table>";
        return;
    }

    /**
    * returns html for the icons in front of the name of the item
    *
    * @access   private
    * @param    string  $item_id
    * @return   string
    */
    function getItemHeadPics($item_id)
    {
        $head = $this->getItemHeadFrontPic($item_id);
        $head .= "\n<td  class=\"printhead\" nowrap align=\"left\" valign=\"bottom\">";
        if ($this->tree->hasKids($item_id)){
            $head .= "<a href=\"";
            $head .= ($this->open_ranges[$item_id]) ? URLHelper::getLink($this->getSelf("close_range={$item_id}")) : URLHelper::getLink($this->getSelf("open_range={$item_id}"));
            $head .= "\">";
            $head .= Icon::create('folder-full', 'clickable', ['title' => $this->open_ranges[$item_id]?_('Alle Unterelemente schließen'):_('Alle Unterelemente öffnen')])->asImg(16, ['class' => 'text-top']);
            $head .= "</a>";
        } else {
            $head .= Icon::create('folder-empty', 'clickable', ['title' => _('Dieses Element hat keine Unterelemente')])->asImg();
        }
    return $head . "</td>";
    }

    function getItemHeadFrontPic($item_id)
    {
        if ($this->use_aging){
            $head = "<td bgcolor=\"" . $this->getAgingColor($item_id) . "\" class=\""
            . (($this->open_items[$item_id]) ? 'printhead3' : 'printhead2')
            . "\" nowrap width=\"1%\"  align=\"left\" valign=\"top\">";
        } else {
            $head = "<td class=\"printhead\" nowrap align=\"left\" valign=\"bottom\">";
        }
        $head .= "<a href=\"";
        $head .= ($this->open_items[$item_id])? URLHelper::getLink($this->getSelf("close_item={$item_id}")) . "\"" . tooltip(_("Dieses Element schließen"),true) . ">"
                                            : URLHelper::getLink($this->getSelf("open_item={$item_id}")) . "\"" . tooltip(_("Dieses Element öffnen"),true) . ">";
        $head .= Assets::img($this->open_items[$item_id] ? $this->pic_open : $this->pic_close);
        #$head .= (!$this->open_items[$item_id]) ? "<img  src=\"".$GLOBALS['ASSETS_URL']."images/forumleer.gif\" width=\"5\" border=\"0\">" : "";
        $head .= "</a>";
        $head .= '</td>';
        return $head;
    }

    /**
    * returns html for the name of the item
    *
    * @access   private
    * @param    string  $item_id
    * @return   string
    */
    function getItemHead($item_id){
        $head = "";
        $head .= "&nbsp;<a class=\"tree\" href=\"";
        $head .= ($this->open_items[$item_id])? URLHelper::getLink($this->getSelf("close_item={$item_id}")) . "\"" . tooltip(_("Dieses Element schließen"),true) . "><b>"
                                            : URLHelper::getLink($this->getSelf("open_item={$item_id}")) . "\"" . tooltip(_("Dieses Element öffnen"),true) . ">";
        $head .= htmlReady(my_substr($this->tree->tree_data[$item_id]['name'],0,$this->max_cols));
        $head .= ($this->open_items[$item_id]) ? "</b></a>" : "</a>";
        return $head;
    }

    /**
    * returns html for the content body of the item
    *
    * @access   private
    * @param    string  $item_id
    * @return   string
    */
    function getItemContent($item_id){
        $content = "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
        if ($item_id == "root"){
            $content .= "\n<tr><td class=\"table_header_bold\" align=\"left\">" . htmlReady($this->tree->root_name) ." </td></tr>";
            $content .= "\n<tr><td class=\"blank\" align=\"left\">" . $this->root_content ." </td></tr>";
            $content .= "\n</table>";
            return $content;
        }
        $content .= "\n<tr><td class=\"blank\" align=\"left\">Inhalt für Element <b>{$this->tree->tree_data[$item_id]['name']} ($item_id)</b><br>".formatReady($this->tree->tree_data[$item_id]['description'])."</td></tr>";
        $content .= "</table>";
        return $content;
    }

    function getAgingColor($item_id){
        $the_time = time();
        $chdate = $this->tree->tree_data[$item_id]['chdate'];
        if ($chdate == 0){
            $timecolor = "#BBBBBB";
        } else {
            if (($the_time - $chdate) < 86400){
                $timecolor = "#FF0000";
            } else {
                $timediff = (int) log(($the_time - $chdate) / 86400 + 1) * 15;
                if ($timediff >= 68){
                    $timediff = 68;
                }
                $red = dechex(255 - $timediff);
                $other = dechex(119 + $timediff);
                $timecolor = "#" . $red . $other . $other;
            }
        }
        return $timecolor;
    }

    /**
    * returns script name
    *
    * @access   private
    * @param    string  $param
    * @return   string
    */
    function getSelf($param = ""){
        return "?" . $param . "#anchor";
    }
}
