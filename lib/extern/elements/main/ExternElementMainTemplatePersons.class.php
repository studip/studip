<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainTemplatePersons.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainTemplatePersons
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainTemplatePersons.class.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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


class ExternElementMainTemplatePersons extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'sort', 'groupsalias', 'groupsvisible', 'grouping',
                'nameformat', 'defaultadr', 'genericdatafields'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
        $this->edit_function = 'editSort';
    }

    /**
    *
    */
    function getDefaultConfig () {
        if ($groups = get_all_statusgruppen($this->config->range_id))
            $groups = '|' . implode('|', array_keys($groups));
        else
            $groups = '';

        $config = [
            'name' => '',
            'sort' => '|1|0|0|0|0',
            'groupsalias' => '',
            'groupsvisible' => $groups,
            'nameformat' => '',
        //  'nodatatext' => '',
            'defaultadr' => ''
        ];

        return $config;
    }




    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        $out = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName('name');
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $content_table .= $this->getSRIFormContent($edit_form);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));
        $edit_function = $this->edit_function;
        $table = $edit_form->$edit_function($this->field_names);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Anzeige von Gruppen"));

        $table = $edit_form->editGroups();
        if ($table) {
            $title = _('Gruppierung') . ':';
            $info = _("Personen nach Gruppen/Funktionen gruppieren.");
            $values = "1";
            $table .= $edit_form->editCheckboxGeneric("grouping", $title, $info, $values, "");
        } else {
            $text = _("An dieser Einrichtung wurden noch keine Gruppen/Funktionen angelegt, oder es wurden diesen noch keine Personen zugeordnet.");
            $text .= _("Das Modul gibt nur Daten von Personen aus, die einer Gruppe/Funktion zugeordnet sind.");
            $table = $edit_form->editTextblock('<font size="2"><b>' . $text . '</b></font>');
        }

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();


        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $title = _('Namensformat') . ':';
        $info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
        $values = ["", "no_title_short", "no_title", "no_title_rev", "full", "full_rev"];
        $names = [_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer, Peter"),
                _("Dr. Peter Meyer"), _("Meyer, Peter, Dr.")];
        $table = $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);

        $title = _('Standard-Adresse') . ':';
        $info = _("Wenn Sie diese Option wählen, wird die Standard-Adresse ausgegeben, die jede(r) Mitarbeiter(in) bei seinen universitären Daten auswählen kann. Wählen Sie diese Option nicht, wenn immer die Adresse der Einrichtung ausgegeben werden soll.");
        $table .= $edit_form->editCheckboxGeneric('defaultadr', $title, $info, '1', '0');

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == "grouping" || $attribute == "defaultadr") {
            // This is especially for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to "0".
            if (!isset($_POST[$this->name . "_" . $attribute])) {
                $_POST[$this->name . "_" . $attribute] = "";
                return FALSE;
            }
            return !($value == '1' || $value == '');
        }

        return FALSE;
    }


}
