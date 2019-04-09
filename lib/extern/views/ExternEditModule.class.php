<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternEditModule.class.php
*
* basic functions for the extern interfaces
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternEditModule
* @package  studip_extern
*/

use Studip\Button, Studip\LinkButton;

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternEditModule.class.php
//
// Copyright (C) 2003 Peter Thienel <thienel@data-quest.de>,
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


require_once "lib/extern/views/ExternEditHtml.class.php";

class ExternEditModule extends ExternEditHtml {

    function __construct (&$config, $form_values = "", $faulty_values = "",
             $edit_element = "") {
        parent::__construct($config, $form_values, $faulty_values, $edit_element);
    }

    function editMainSettings ($field_names, $hide_fields = "", $hide = "") {
        // these two values are always necessary, even there is an error in the users inputs, so
        // there arent transfered via $_POST
        $this->form_values[$this->element_name . "_order"]
                = $this->config->getValue($this->element_name, "order");
        $this->form_values[$this->element_name . "_visible"]
                = $this->config->getValue($this->element_name, "visible");

        $order = $this->getValue("order");
        $aliases = $this->getValue("aliases");
        $visible = $this->getValue("visible");
        $widths = $this->getValue("width");
        $sort = $this->getValue("sort");
        if (!is_array($hide_fields["sort"]))
            $hide_fields["sort"] = [];
        if (!is_array($hide_fields["aliases"]))
            $hide_fields["aliases"] = [];
        if (!is_array($hide))
            $hide = [];

        $out = "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
        $out .= "<tr>\n";
        $out .= "<td><font size=\"2\"><b>" . _("Datenfeld") . "</b></font></td>\n";
        if (!in_array('aliases', $hide))
            $out .= "<td><font size=\"2\"><b>" . _("Überschrift") . "</b></font></td>\n";
        if (!in_array("width", $hide))
            $out .= "<td><font size=\"2\"><b>" . _("Breite") . "</b></font></td>\n";
        if (!in_array("sort", $hide))
            $out .= "<td><font size=\"2\"><b>" . _("Sortierung") . "</b></font></td>\n";
        if (!in_array("visible", $hide))
            $out .= "<td><font size=\"2\"><b>" . _("Reihenfolge/<br>Sichtbarkeit") . "</b></font></td>\n";
        $out .= "</tr>\n";

        for ($i = 0; $i < sizeof($field_names); $i++) {

            // name of column
            $out .= "<tr valign=\"middle\">\n";
            $out .= '<td>&nbsp;' . htmlReady($field_names[$order[$i]]) . '</td>';

            // column headline
            if (!in_array('aliases', $hide)) {
                if (!in_array($order[$i], $hide_fields["aliases"])) {
                    $out .= "<td><input type=\"text\" name=\"{$this->element_name}_aliases[$order[$i]]\"";
                    $out .= "\" size=\"12\" maxlength=\"50\" value=\"";
                    $out .= $aliases[$order[$i]] . "\">";
                    if ($this->faulty_values[$this->element_name . "_aliases"][$order[$i]])
                        $out .= $this->error_sign;
                    $out .= "</td>\n";
                }
                else {
                    $out .= "<td>&nbsp;</td>\n";
                    $out .= "<input type=\"hidden\" name=\"{$this->element_name}_aliases[$order[$i]]\" ";
                    $out .= "value=\"\">";
                }
            }

            // width
            if (!in_array("width", $hide)) {
                $width = str_replace("%", "", $widths[$order[$i]]);
                $out .= "<td><input type=\"text\" name=\"{$this->element_name}_width[$order[$i]]";
                $out .= "\" size=\"3\" maxlength=\"3\" value=\"$width\">";
                if ($this->faulty_values[$this->element_name . "_width"][$order[$i]])
                    $out .= $this->error_sign;
                $out .= "</td>\n";
            }

            // sort
            if (!in_array("sort", $hide)) {
                if (!in_array($order[$i], $hide_fields["sort"])) {
                    $out .= "<td><select name=\"{$this->element_name}_sort[$order[$i]]\" ";
                    $out .= "size=\"1\">\n";
                    $out .= "<option value=\"0\"" . ($sort[$order[$i]] == 1 ? " selected" : "")
                            . ">" . _("keine") . "</option>";
                    for ($j = 1; $j <= (sizeof($order) - sizeof($hide_fields["sort"])); $j++) {
                        if ($sort[$order[$i]] == $j)
                            $selected = " selected";
                        else
                            $selected = "";
                        $out .= "<option value=\"$j\"$selected>$j</option>";
                    }
                    $out .= "\n</select>\n</td>\n";
                }
                else {
                    $out .= "<td>&nbsp;</td>\n";
                    $out .= "<input type=\"hidden\" name=\"{$this->element_name}_sort[$order[$i]]\" ";
                    $out .= "value=\"0\">\n";
                }
            }

            if (!in_array("visible", $hide)) {
                // move left
                $out .= "<td valign=\"middle\" nowrap=\"nowrap\">";
                $out .= Icon::create('arr_2up', 'sort', ['title' => _('Datenfeld verschieben')])->asInput(["type" => "image", "class" => "middle", "name" => $this->element_name."_move_left[".$i."]"]);

                // move right
                $out .= "\n";
                $out .= Icon::create('arr_2down', 'sort', ['title' => _('Datenfeld verschieben')])->asInput(["type" => "image", "class" => "middle", "name" => $this->element_name."_move_right[".$i."]"]);

                // visible
                if ($visible[$order[$i]]) {
                $out .= "\n";
                $out .= Icon::create('checkbox-checked', 'clickable', ['title' => _('Datenfeld ausblenden')])->asInput(["type" => "image", "class" => "middle", "name" => $this->element_name."_hide[".$order[$i]."]"]);
                } else {
                    $out .= "\n";
                    $out .= Icon::create('checkbox-unchecked', 'clickable', ['title' => _('Datenfeld einblenden')])->asInput(["type" => "image", "class" => "middle", "name" => $this->element_name."_show[".$order[$i]."]"]);
                    $out .= "</td>\n";
                }
           }

        }

        // width in pixels or percent
        if (!in_array("widthpp", $hide) && !in_array('width', $hide)) {
            $colspan = 4 - sizeof($hide);
            $title = _("Breite in:");
            $info = _("Wählen Sie hier, ob die Breiten der Tabellenspalten als Prozentwerte oder Pixel interpretiert werden sollen.");
            $width_values = ["%", ""];
            $width_names = [_("Prozent"), _("Pixel")];
            $out .= "<tr>\n";
            $out .= "<td><font size=\"2\">&nbsp;$title</font></td>";
            $out .= "<td colspan=\"$colspan\"><input type=\"radio\" name=\"{$this->element_name}_widthpp\" value=\"%\"";
            if (mb_substr($widths[0], -1) == "%")
                $out .= " checked=\"checked\"";
            $out .= "><font size=\"2\">" . _("Prozent") . "&nbsp; &nbsp;</font><input type=\"radio\" name=\"";
            $out .= "{$this->element_name}_widthpp\" value=\"\"";
            if (mb_substr($widths[0], -1) != "%")
                $out .= " checked=\"checked\"";
            $out .= "><font size=\"2\">" . _("Pixel") . "&nbsp; &nbsp;</font>\n";
            $out .= tooltipIcon($info);
            $out .= "$error_sign</td></tr>\n";
        }

        $out .= "</table>\n";

        return $out;
    }

    function editSort ($field_names, $hide_fields = NULL) {
        if (!is_array($hide_fields)) {
            $hide_fields = [];
        }
        $sort = $this->getValue("sort");


        $out = "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
        $out .= "<tr>\n";
        $out .= "<td><font size=\"2\"><b>" . _("Datenfeld") . "</b></font></td>\n";
        $out .= "<td><font size=\"2\"><b>" . _("Sortierung") . "</b></font></td>\n";

        for ($i = 0; $i < sizeof($field_names); $i++) {
            $out .= "<tr valign=\"middle\">\n";
            $out .= "<td><font size=\"2\">&nbsp;{$field_names[$i]}</font></td>";
            if (!in_array($i, $hide_fields)) {
                $out .= "<td><select name=\"{$this->element_name}_sort[$i]\" ";
                $out .= "size=\"1\">\n";
                $out .= "<option value=\"0\"" . ($sort[$i] == 1 ? " selected" : "")
                        . ">" . _("keine") . "</option>";
                for ($j = 1; $j <= (sizeof($field_names) - sizeof($hide_fields["sort"])); $j++) {
                    if ($sort[$i] == $j)
                        $selected = " selected";
                    else
                        $selected = "";
                    $out .= "<option value=\"$j\"$selected>$j</option>";
                }
                $out .= "\n</select>\n</td>\n";
            }
            else {
                $out .= "<td>&nbsp;</td>\n";
                $out .= "<input type=\"hidden\" name=\"{$this->element_name}_sort[$i]\" ";
                $out .= "value=\"0\">\n";
            }
        }

        $out .= "</table>\n";

        return $out;
    }

    function editName ($attribute) {
        $info = _("Geben Sie den Namen der Konfiguration an.");

        return $this->editTextfieldGeneric($attribute, "", $info, 40, 40);
    }

    function editGroups () {
        $groups_db = get_all_statusgruppen($this->config->range_id);

        if (!$groups_db)
            return FALSE;

        $title = _("Gruppen auswählen:");
        $info = _("Wählen Sie die Statusgruppen aus, die ausgegeben werden sollen.");
        $groups_config = $this->getValue("groups");

        // this value is always necessary, even there is an error in the users inputs, so
        // it isn't transfered via $_POST
        $this->form_values[$this->element_name . "_groupsvisible"]
                = $this->config->getValue($this->element_name, "groupsvisible");

        // initialize groups if this value isn't set in the config file
        if (!$groups_config) {
            $groups_config = array_keys($groups_db);
        }

        $groups_aliases = $this->getValue("groupsalias");
        $groups_visible = $this->getValue("groupsvisible");
        if (!is_array($groups_visible))
            $groups_visible = [];
        if (!is_array($groups_aliases))
            $groups_aliases = [];
        if (sizeof(array_intersect(array_keys($groups_aliases),
                array_keys($groups_db)))) {
            foreach ($groups_config as $group_config) {
                $groups[$group_config] = $groups_aliases[$group_config];
            }
        } else {
            for ($i = 0; $i < sizeof($groups_config); $i++) {
                $groups[$groups_config[$i]] = $groups_aliases[$i];
            }
        }

        $out = "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
        $out .= "<tr>\n";
        $out .= "<td width=\"42%\"><font size=\"2\"><b>" . _("Gruppenname") . "</b></font></td>\n";
        $out .= "<td width=\"48%\"><font size=\"2\"><b>" . _("alternativer Gruppenname") . "</b></font></td>\n";
        $out .= "<td width=\"1%\"><font size=\"2\"><b>" . _("Sichtbarkeit") . "</b></font></td>\n";
        $out .= "<td width=\"9%\"><font size=\"2\">&nbsp;</font></td>\n";
        $out .= "</tr>\n";
        $i = 0;
        foreach ($groups_db as $id => $name) {

            // name of group
            $out .= "<tr>\n";
            $out .= "<td nowrap=\"nowrap\" style=\"max-width: 40em; overflow: hidden; text-overflow: ellipsis;\" title=\"" . htmlReady($name) . "\"><font size=\"2\">&nbsp;" . htmlReady($name) . "</font></td>";

            // column headline
            $out .= '<td nowrap="nowrap"><input type="text" name="'
                    . $this->element_name . '_groupsalias[' . $id . ']"';
            $out .= " size=\"25\" maxlength=\"150\" value=\"";
            $out .= $groups[$id] . "\">";
            if ($this->faulty_values[$this->element_name . "_groupsalias"][$id])
                    $out .= $this->error_sign;
            $out .= "</td>\n";

            // visible
            $out .= '<td align="center">'
                    . '<input type="hidden" name="'
                    . $this->element_name . '_show_group[' . $id . ']" value="0">'
                    . '<input type="checkbox" name="'
                    . $this->element_name . '_show_group[' . $id . ']" value="1"';
            if (in_array($id, $groups_visible)) {
                $out .= ' checked';
            }
            $out .= '></td>';
            $out .= "<td>&nbsp;</td></tr>\n";
            $i++;
        }

        $out .= "</table>\n";

        return $out;
    }

    function editSemTypes () {
        global $SEM_TYPE, $SEM_CLASS;
        // these two values are always necessary, even there is an error in the users inputs, so
        // there aren't transfered via $_POST
        $this->form_values[$this->element_name . "_order"]
                = $this->config->getValue($this->element_name, "order");
        $order = $this->getValue("order");

        $this->form_values[$this->element_name . '_visibility']
                = $this->config->getValue($this->element_name, 'visibility');
        $visibility = $this->getValue('visibility');

        // compat <1.3: new attribute visibility (all SemTypes are visible)
        if (!is_array($visibility) || !count($visibility)) {
            $visibility = array_fill(0, sizeof($order), 1);
        }

        if (!is_array($order)) {
            $order = array_keys($SEM_TYPE);
        }


        $out = "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
        $out .= "<tr>\n";
        $out .= "<td><font size=\"2\"><b>" . _("Datenfeld") . "</b></font></td>\n";
        $out .= "<td><font size=\"2\"><b>" . _("Überschrift") . "</b></font></td>\n";
        $out .= "<td align=\"center\"><font size=\"2\"><b>" . _("Reihenfolge") . "</b></font></td>\n";
        $out .= "<td align=\"center\"><font size=\"2\"><b>" . _("Sichtbarkeit") . "</b></font></td>\n";
        $out .= "</tr>\n";

        foreach ($SEM_CLASS as $class_index => $foo) {
            $i = 0;
            foreach ($SEM_TYPE as $type_index => $type) {
                if ($type["class"] == $class_index) {
                    $mapping[$type_index] = $i++;
                }
            }
            $classes[$class_index] = $this->getValue("class_$class_index");
        }

        for ($i = 0; $i < sizeof($order); $i++) {
            if ($SEM_TYPE[$order[$i]]) {
                $new_order[] = $order[$i];
                // name of column
                $out .= "<tr>\n";
                $out .= "<td><font size=\"2\">&nbsp;";
                if (mb_strlen($SEM_TYPE[$order[$i]]["name"]) > 25) {
                    $out .= htmlReady(mb_substr($SEM_TYPE[$order[$i]]["name"], 0, 22)
                            . "... ({$SEM_CLASS[$SEM_TYPE[$order[$i]]['class']]['name']})");
                } else {
                    $out .= htmlReady($SEM_TYPE[$order[$i]]["name"]
                            . " ({$SEM_CLASS[$SEM_TYPE[$order[$i]]['class']]['name']})");
                }
                $out .= "</font></td>";

                // column headline
                $out .= "<td><input type=\"text\" name=\"{$this->element_name}_class_";
                $out .= $SEM_TYPE[$order[$i]]['class'] . "[{$mapping[$order[$i]]}]\"";
                $out .= "\" size=\"20\" maxlength=\"100\" value=\"";
                if (isset($classes[$SEM_TYPE[$order[$i]]['class']][$mapping[$order[$i]]])) {
                    $out .= $classes[$SEM_TYPE[$order[$i]]['class']][$mapping[$order[$i]]] . "\">";
                } else {
                    $out .= $SEM_TYPE[$order[$i]]["name"]
                            . " ({$SEM_CLASS[$SEM_TYPE[$order[$i]]['class']]['name']})\">";
                }
                if ($this->faulty_values[$this->element_name
                        . "_class_{$SEM_TYPE[$order[$i]]['class']}"][$mapping[$order[$i]]]) {
                    $out .= $this->error_sign;
                }
                $out .= "</td>\n";

                    // move up
                $out .= "<td valign=\"top\" align=\"center\" nowrap=\"nowrap\">";
                $out .= Icon::create('arr_2up', 'sort', ['title' => _('Datenfeld verschieben')])->asInput(['name'=>$this->element_name.'_move_left['.$i.']','align'=>'middle',]);

                // move down
                $out .= Icon::create('arr_2down', 'sort', ['title' => _('Datenfeld verschieben')])->asInput(['name'=>$this->element_name.'_move_right['.$i.']','align'=>'middle',]);
                $out .= "</td>\n";

                // visibility
                $out .= "<td valign=\"top\" align=\"center\" nowrap=\"nowrap\">";
                $out .= "<input type=\"checkbox\" name=\"{$this->element_name}_visibility";
                $out .= '[' . ($order[$i] - 1) . "]\" value=\"1\"";
                if ($visibility[$order[$i] - 1] == 1) {
                    $out .= ' checked="checked"';
                }
                $out .= '>';

                $out .= "</td>\n</tr>\n";
            }
        }

        $out .= "</table>\n";
        $out .= "<input type=\"hidden\" name=\"count_semtypes\" value=\"$i\">\n";

        // update order
        if (sizeof(array_diff($order, $new_order))) {
            $this->config->setValue($this->element_name, 'order', $new_order);
            $this->config->store();
        }
        return $out;
    }

    function editSelectSubjectAreas ($selector) {
        $info = _("Wählen Sie die Studienbereiche aus, deren Veranstaltungen angezeigt werden sollen.");
        $info2 = _("Sie können beliebig viele Studienbereiche auswählen.");
        $form_name = $this->element_name . "_" . 'subjectareasselected';

        if ($this->faulty_values[$form_name][0]) {
            $error_sign = $this->error_sign;
        } else {
            $error_sign = '';
        }

        $selected = $this->config->getValue($this->element_name, 'subjectareasselected');
        $selector->selected = [];
        $selector->sem_tree_ranges = [];
        $selector->sem_tree_ids = [];
        if (is_array($selected) && count($selected)) {
            foreach ($selected as $selected_id) {
                $selector->selected[$selected_id] = TRUE;
                $selector->sem_tree_ranges[$selector->tree->tree_data[$selected_id]['parent_id']][] = $selected_id;
                $selector->sem_tree_ids[] = $selected_id;
            }
        }

        $form_name_tmp = $selector->form_name;
        $selector->form_name = 'SelectSubjectAreas';
        $selector->doSearch();
        $out .= "<table width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\">\n";
        $out .= '<tr><td align="left" style="font-size: smaller;" width="100%" nowrap="nowrap" colspan="2">' . _("Suche") . ': ';
        $out .= $selector->getSearchField(['size' => 30 ,'style' => 'vertical-align:middle;']);
        $out .= $selector->getSearchButton(['style' => 'vertical-align:middle;']);
        $out .= '<br><span style="font-size: 0.9em;"> (' . sprintf(_("Geben Sie '%s' ein, um alle Studienbereiche zu finden."), '%%%') . ')</span>';
        if ($selector->num_search_result !== false){
            $out .= "<br><span style=\"font-size:smaller;\"><a name=\"anker\">&nbsp;&nbsp;</a>"
                    . sprintf(_("Ihre Suche ergab %s Treffer."),$selector->num_search_result)
                    . (($selector->num_search_result) ? _(" (Suchergebnisse werden blau angezeigt)") : '')
                    . '</span>';
        }
        $out .= '</td></tr>';
        $selector->form_name = $form_name_tmp;
        $out .= '<td nowrap="nowrap" width="80%">';
        $out .= $selector->getChooserField(['style' => 'width:98%;','size' => 15],
                70, 'subjectareasselected');
        $out .= '</td><td width="20%" style="vertical-align: top;">';
        $out .= tooltipIcon($info);
        $out .= "<span style=\"vertical-align:top;\">$error_sign</span>";
        $out .= "</td></tr></table>\n";

        return $out;
    }

    function editMarkerDescription ($markers, $new_datafields = FALSE) {
        $out = "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" style=\"font-size: 0.7em\">\n";
        $out .= "<tr>\n";
        $out .= '<td><font size="2"><b>' . _("Marker") . "</b></font></td>\n";
        $out .= '<td><font size="2"><b>' . _("Beschreibung") . "</b></font></td>\n";
        $out .= "</tr>\n";

        $spacer = 0;
        $global_vars = FALSE;
        foreach ((array) $markers as $marker) {
            $mark = $marker[0];
            $description = $marker[1];
            if ($mark == '__GLOBAL__') {
                $out .= "<tr>\n";
                $out .= '<td colspan="2"><strong>' . htmlReady(_("Globale Variablen")) . '</strong></td>';
                $spacer++;
                $global_vars = TRUE;
            } else if ($mark{0} == '<') {
                if ($global_vars) {
                    $out .= "<tr>\n";
                    $out .= '<td colspan="2">&nbsp;</td>';
                    $spacer--;
                    $global_vars = FALSE;
                }
                if (mb_substr($mark, 0, 8) == '<!-- END') {
                    $spacer--;
                    $out .= "<tr>\n";
                    $out .= '<td colspan="2">&nbsp;</td>';
                    $out .= "<tr>\n";
                    $out .= '<td nowrap="nowrap">' . str_repeat('&nbsp;', $spacer * 4);
                    $out .= htmlReady($mark) . '</td><td>' . htmlReady($description);
                    $out .= '</td>';
                } else {
                    if ($spacer > 0 && mb_substr($mark, 0, 10) != '<!-- BEGIN') {
                        $out .= "<tr>\n";
                        $out .= '<td colspan="2">&nbsp;</td>';
                    }
                    $out .= "<tr>\n";
                    $out .= '<td nowrap="nowrap">' . str_repeat('&nbsp;', $spacer * 4);
                    $out .= htmlReady($mark) . '</td><td>' . htmlReady($description);
                    $out .= '</td>';
                    $spacer++;
                    $out .= "<tr>\n";
                    $out .= '<td colspan="2">&nbsp;</td>';
                }
            } else {
                $out .= "<tr>\n";
                $out .= '<td>' . str_repeat('&nbsp;', $spacer * 4);
                $out .= $mark . '</td><td>' . htmlReady($description);
                $out .= '</td>';
            }
            $out .= "</tr>\n";
        }
        if ($new_datafields) {
            $out .= "<tr>\n";
            $out .= "<td colspan=\"2\">&nbsp;</td></tr>\n";
            $out .= "<tr>\n";
            $out .= '<td colspan="2" align="center">' . Button::create(_('Aktualisieren')). "</td></tr>\n";
        }
        $out .= "</table>\n";

        return $out;
    }

    function editSelectInstitutes () {
        // get all faculties
        $stm_fak = DBManager::get()->prepare(
            "SELECT Institut_id, Name "
            . "FROM Institute "
            . "WHERE fakultaets_id = Institut_id "
            . "ORDER BY Name");
        $stm_fak->execute();
        $stm_inst = DBManager::get()->prepare(
            "SELECT Institut_id, Name "
            . "FROM Institute "
            . "WHERE fakultaets_id = ? AND fakultaets_id != Institut_id "
            . "ORDER BY Name");
        $selected = $this->config->getValue($this->element_name, 'institutesselected');
        if (!is_array($selected)) {
            $selected = [];
        }

        $out = '<div class="selectbox" style="width: 98%;" size="15">';
        while ($row_fak = $stm_fak->fetch(PDO::FETCH_ASSOC)) {
            $stm_inst->execute([$row_fak['Institut_id']]);
            $out .= sprintf('<div style="margin-top: 5px; font-weight: bold; color: red;">%s</div>', htmlReady(my_substr($row_fak['Name'], 0, 70)));
            $out .= '<div style="font-weight: bold; color: red;">';
            $out .= str_repeat("¯", 70);
            $out .= '</div>';
            while ($row_inst = $stm_inst->fetch(PDO::FETCH_ASSOC)) {
                $is_selected = in_array($row_inst['Institut_id'], $selected);
                $out .= sprintf('<div><label for="SelectInstitutes_institutesselected_%s"><input style="vertical-align: middle;" id="SelectInstitutes_institutesselected_%s" type="checkbox" name="SelectInstitutes_institutesselected[]" value="%s"%s>', $row_inst['Institut_id'], $row_inst['Institut_id'], $row_inst['Institut_id'], ($is_selected ? ' checked="checked"' : ''));
                $out .= sprintf('&nbsp;<span%s>%s</span></div>', ($is_selected ? '' : ' style="color: blue;"'), htmlReady(my_substr($row_inst['Name'], 0, 70)));
            }
        }
        $out .= '</div>';
        return $out;
    }

}
