<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainTemplatePersBrowse.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainTemplatePersBrowse
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainTemplatePersBrowse.class.php
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


class ExternElementMainTemplatePersBrowse extends ExternElementMain {

    /**
    * Constructor
    *
    */
    public function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'sort', 'groupsalias', 'groupsvisible', 'grouping',
                'nameformat', 'defaultadr', 'genericdatafields', 'onlylecturers', 'onlygrouped',
                'instperms'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
        $this->edit_function = 'editSort';
    }

    /**
    *
    */
    public function getDefaultConfig () {
        $config = [
            'name' => '',
            'sort' => '|1|0|0|0|0',
            'nameformat' => '',
            'defaultadr' => '',
            'instperms' => '|dozent',
            'onlylecturers' => '1'
        ];

        return $config;
    }

    /**
    *
    */
    public function toStringEdit ($post_vars = "", $faulty_values = "",
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

        $headline = $edit_form->editHeadline(_("Sortierung der Personenliste"));
        $edit_function = $this->edit_function;
        $table = $edit_form->$edit_function($this->field_names);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        if (in_array(get_object_type($this->config->range_id), ['fak', 'global'])) {
            $headline = $edit_form->editHeadline(_("Filter"));

            $title = _('Rechtestufe in Einrichtung') . ':';
            $info = _("Es werden nur Personen angezeigt, die in einer Einrichtung die angegebenen Rechtestufen besitzen");
            $values = ['tutor', 'dozent', 'admin'];
            $names = [_("Tutor"), _("Dozent"), _("Administrator")];
            $table = $edit_form->editCheckboxGeneric('instperms', $title, $info, $values, $names);

            $title = _('Nur Lehrende') . ':';
            $info = _("Es werden nur Personen angezeigt, die in einer sichtbaren Veranstaltung des aktuellen Semesters Dozent sind.");
            $values = '1';
            $table .= $edit_form->editCheckboxGeneric('onlylecturers', $title, $info, $values, '');

            $table .= $edit_form->editTextblock('<span style="font-weight: bold">'
                . _("Das Modul zeigt nur Personen an, die eine Standardadresse angegeben haben.")
                . '</span>');

            $content_table .= $edit_form->editContentTable($headline, $table);
            $content_table .= $edit_form->editBlankContent();
        }

        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $title = _('Namensformat') . ':';
        $info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
        $values = ["", "no_title_short", "no_title", "no_title_rev", "full", "full_rev"];
        $names = [_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer, Peter"),
                _("Dr. Peter Meyer"), _("Meyer, Peter, Dr.")];
        $table = $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    public function checkValue ($attribute, $value) {
        if (in_array($attribute, ['grouping', 'defaultadr', 'onlylecturers'])) {
            // This is especially for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to "0".
            if (!isset($_POST[$this->name . "_" . $attribute])) {
                $_POST[$this->name . "_" . $attribute] = "";
                return false;
            }
            return !($value == '1' || $value == '');
        }

        if ($attribute == 'instperms') {
            if (!isset($_POST[$this->name . '_instperms'])) {
                $_POST[$this->name . '_instperms'] = [];
                return false;
            }
        }

        return false;
    }


}
