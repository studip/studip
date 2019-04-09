<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemTreeViewSimple.class.php
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
* class to print out the seminar tree
*
* This class prints out a html representation a part of the tree
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipSemTreeViewSimple
{
    var $tree;
    var $show_entries;

    /**
    * constructor
    *
    */
    public function __construct($start_item_id = "root", $sem_number = false, $sem_status, $visible_only = false)
    {
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
        $this->tree = TreeAbstract::GetInstance("StudipSemTree",$args);
        $this->tree->enable_lonely_sem = false;
        if (!$this->tree->tree_data[$this->start_item_id]){
            $this->start_item_id = "root";
        }
    }

    public function showSemTree($start_id = null)
    {
        echo '
            <table style="width:100%;">
                <tr>
                    <td style="text-align:left; vertical-align:top; font-size:10pt;">
                        <div style="font-size:10pt; margin-left:10px">
                            <b>' .
                                _('Studienbereiche') . '
                            </b><br>' .
                $this->getSemPath($start_id);
        if ($this->tree->getValue($this->start_item_id, 'info')) {
            echo '
                            <div class="sem_path_info">' .
                        formatReady($this->tree->getValue($this->start_item_id, 'info')) .
                            '</div>';
        }
        echo '
                        </div>
                    </td>
                    <td nowrap style="text-align:right; vertical-align:top; padding-top: 1em;">';
        if ($this->start_item_id != 'root') {
            echo '
                        <a href="' .
                        URLHelper::getLink($this->getSelf('start_item_id=' .
                                $this->tree->tree_data[$this->start_item_id]['parent_id'], false)) .
                        '>' .
                        Icon::create('arr_2left', 'clickable')->asImg(['class' => 'text-top', 'title' =>_('eine Ebene zurück')]) .
                        '</a>';
        } else {
            echo '&nbsp;';
        }
        echo '
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center; vertical-align:center;">';
        $num_all_entries = $this->showKids($this->start_item_id);
        echo '
                    </td>
                </tr>
                <tr>
                    <td colspan=\"2\" style="text-align:left; vertical-align:center;">';
        $this->showContent($this->start_item_id, $num_all_entries);
        echo '
                    </td>
                </tr>
            </table>';
    }

    public function showKids($item_id)
    {
        $num_kids = $this->tree->getNumKids($item_id);
        $all_kids = $this->tree->getKids($item_id);
        $kids = [];
        if(!$GLOBALS['perm']->have_perm(Config::GetInstance()->getValue('SEM_TREE_SHOW_EMPTY_AREAS_PERM')) && $num_kids){
            foreach($all_kids as $kid){
                if($this->tree->getNumKids($kid) || $this->tree->getNumEntries($kid,true)) $kids[] = $kid;
            }
            $num_kids = count($kids);
        } else {
            $kids = $all_kids;
        }
        $num_all_entries = 0;
        echo "\n<table width=\"95%\" border=\"0\" cellpadding=\"0\" cellspacing=\"10\"><tr>\n<td class=\"table_row_even\" width=\"50%\" align=\"left\" valign=\"top\"><ul class=\"semtree\">";
        for ($i = 0; $i < $num_kids; ++$i){
            if ($this->start_item_id != 'root') {
            $num_entries = $this->tree->getNumEntries($kids[$i],true);
                $num_all_entries += $num_entries;
            }
            echo "<li><a " . ($num_entries ? tooltip(sprintf(_("%s Einträge in allen Unterebenen vorhanden"), $num_entries), false) : '') . " href=\"" .URLHelper::getLink($this->getSelf("start_item_id={$kids[$i]}", false)) . "\">";
            echo htmlReady($this->tree->getValue($kids[$i], 'name'));
            if ($num_entries) echo " ($num_entries)";
            echo "</a></li>";
            if ($i == ceil($num_kids / 2)-1){
                echo "</ul></td>\n<td class=\"table_row_even\" align=\"left\" valign=\"top\"><ul class=\"semtree\">";
            }
        }
        if (!$num_kids){
            echo "<li>";
            echo _("Auf dieser Ebene existieren keine weiteren Unterebenen.");
            echo "</li>";
        }

        echo "\n</ul></td></tr></table>";
        return $num_all_entries;
    }

    public function getInfoIcon($item_id)
    {
        if ($item_id === 'root') {
            $info = $this->root_content;
        } else {
            $info = $this->tree->getValue($item_id, 'info');
        }
        $ret = $info ? tooltipicon(kill_format($info)) : '';
        return $ret;
    }

    public function showContent($item_id, $num_all_entries)
    {
        echo "\n<div align=\"center\" style=\"margin-left:10px;margin-top:10px;margin-bottom:10px;font-size:10pt\">";
        if ($item_id != "root"){
            if ($this->tree->hasKids($item_id) && $num_all_entries){
                if ($this->show_entries != "sublevels"){
                    if ($num_all_entries <= 100) echo "<a " . tooltip(_("alle Einträge in allen Unterebenen anzeigen"), false) ." href=\"" . URLHelper::getLink($this->getSelf("cmd=show_sem_range&item_id={$this->start_item_id}_withkids")) ."\">";
                    echo Icon::create('arr_1right', 'clickable')->asImg();
                    echo '&nbsp;';
                } else {
                    echo Icon::create('arr_1down', 'clickable')->asImg();
                    echo '&nbsp;';
                }
                printf(_("<b>%s</b> Einträge in allen Unterebenen vorhanden"), $num_all_entries);
                if ($this->show_entries != "sublevels"){
                    echo "</a>";
                }
                echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
            }
            if ($num_entries = $this->tree->getNumEntries($item_id)){
                if ($this->show_entries != "level"){
                    echo "<a " . tooltip(_("alle Einträge auf dieser Ebene anzeigen"), false) ." href=\"" . URLHelper::getLink($this->getSelf("cmd=show_sem_range&item_id=$item_id")) ."\">";
                    echo Icon::create('arr_1right', 'clickable')->asImg();
                    echo '&nbsp;';
                } else {
                    echo Icon::create('arr_1down', 'clickable')->asImg();
                    echo '&nbsp;';
                }
                printf(_("<b>%s</b> Einträge auf dieser Ebene.&nbsp;"),$num_entries);
                if ($this->show_entries != "level"){
                    echo "</a>";
                }
            } else {
                    echo _("Keine Einträge auf dieser Ebene vorhanden!");
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
            for($i = count($parents) - 1; $i >= 0; --$i){
                if ($add_item || $start_id == $parents[$i]) {
                    $ret .= "&nbsp;&gt;&nbsp;<a href=\""
                            . URLHelper::getLink($this->getSelf("start_item_id={$parents[$i]}", false))
                            . "\">"
                            . htmlReady($this->tree->getValue($parents[$i], "name"))
                            . "</a>";
                    $add_item = true;
                }
            }
        }
        if ($this->start_item_id == "root") {
            $ret = "&nbsp;&gt;&nbsp;<a href=\"" . URLHelper::getLink($this->getSelf("start_item_id=root",false)) . "\">" .htmlReady($this->tree->root_name) . "</a>";
        } else {
            $ret .= "&nbsp;&gt;&nbsp;<a href=\"" . URLHelper::getLink($this->getSelf("start_item_id={$this->start_item_id}",false)) . "\">" . htmlReady($this->tree->getValue($this->start_item_id, "name")) . "</a>";

        }
        $ret .= "&nbsp;";
        if (!$this->tree->getValue($this->start_item_id, 'info')) {
            $ret .= $this->getInfoIcon($this->start_item_id);
        }
        return $ret;
    }

    /**
     * @return string url NOT escaped
     */
    public function getSelf($param = "", $with_start_item = true)
    {
        $url_params = (($with_start_item) ? "start_item_id=" . $this->start_item_id . "&" : "") . $param ;
        return URLHelper::getURL('?' . $url_params);
    }
}
?>
