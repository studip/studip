<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainPersons.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainPersons
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainPersons.class.php
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

class ExternElementMainPersons extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'genericdatafields', 'order', 'visible', 'aliases', 'width',
                'width_pp', 'sort', 'groupsalias', 'groupsvisible', 'grouping', 'wholesite',
                'nameformat', 'repeatheadrow', 'urlcss', 'title', 'bodystyle', 'bodyclass',
                'copyright', 'author', 'defaultadr'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
    }

    /**
    *
    */
    function getDefaultConfig () {
        if ($groups = get_all_statusgruppen($this->config->range_id))
            $groups = "|" . implode("|", array_keys($groups));
        else
            $groups = "";

        $config = [
            "name" => "",
            "order" => "|0|1|2|3|4",
            "visible" => "|1|1|1|1|1",
            "aliases" => "|"._("Name")."|"._("Telefon")."|"._("Raum")."|"._("E-Mail")."|"._("Sprechzeiten"),
            "width" => "|30%|15%|15%|20%|20%",
            "widthpp" => "",
            "sort" => "|1|0|0|0|0",
            "groupsalias" => "",
            "groupsvisible" => $groups,
            "grouping" => "1",
            "wholesite" => "",
            "nameformat" => "",
            "repeatheadrow" => "",
            "urlcss" => "",
            "title" => _("MitarbeiterInnen"),
            "nodatatext" => "",
            "config" => "",
            "srilink" => "",
            "copyright" => htmlReady(Config::get()->UNI_NAME_CLEAN
                    . " ({$GLOBALS['UNI_CONTACT']})"),
            "author" => "",
            "defaultadr" => ''
        ];

        get_default_generic_datafields($config, "user");

        return $config;
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        update_generic_datafields($this->config, $this->data_fields, $this->field_names, "user");
        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName("name");
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $content_table .= $this->getSRIFormContent($edit_form);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));

        $edit_function = $this->edit_function;
        for ($i = 5; $i < sizeof($this->field_names); $i++)
            $hide_sort[] = $i;
        $table = $edit_form->$edit_function($this->field_names, ['sort' => $hide_sort]);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Anzeige von Gruppen"));

        $table = $edit_form->editGroups();
        if ($table) {
            $title = _('Gruppierung') . ':';
            $info = _("Personen nach Gruppen/Funktionen gruppieren.");
            $values = "1";
            $table .= $edit_form->editCheckboxGeneric("grouping", $title, $info, $values, "");
        }
        else {
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

        $title = _('Spaltenüberschriften wiederholen') . ':';
        $info = _("Wiederholung der Spaltenüberschriften über oder unter der Gruppierungszeile.");
        $values = ["above", "beneath", ""];
        $names = [_("über"), _("unter Gruppenname"), _("keine")];
        $table .= $edit_form->editRadioGeneric("repeatheadrow", $title, $info, $values, $names);

        $title = _('Standard-Adresse') . ':';
        $info = _("Wenn Sie diese Option wählen, wird die Standard-Adresse ausgegeben, die jede(r) Mitarbeiter(in) bei seinen universitären Daten auswählen kann. Wählen Sie diese Option nicht, wenn immer die Adresse der Einrichtung ausgegeben werden soll.");
        $table .= $edit_form->editCheckboxGeneric('defaultadr', $title, $info, '1', '0');

        $title = _('HTML-Header/Footer') . ':';
        $info = _("Anwählen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
        $values = "1";
        $names = "";
        $table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $values, $names);

        $title = _('Stylesheet-Datei') . ':';
        $info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
        $table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);

        $title = _('Seitentitel') . ':';
        $info = _("Geben Sie hier den Titel der Seite ein. Der Titel wird bei der Anzeige im Web-Browser in der Titelzeile des Anzeigefensters angezeigt.");
        $table .= $edit_form->editTextfieldGeneric("title", $title, $info, 50, 200);

        $title = _('Copyright') . ':';
        $info = _("Geben Sie hier einen Copyright-Vermerk an. Dieser wird im Meta-Tag \"copyright\" ausgegeben, wenn Sie die Option \"HTML-Header/Footer\" angewählt haben.");
        $table .= $edit_form->editTextfieldGeneric("copyright", $title, $info, 50, 200);

        $title = _('Autor') . ':';
        $info = _("Geben Sie hier den Namen des Seitenautors an. Dieser wird im Meta-Tag \"author\" ausgegeben, wenn Sie die Option \"HTML-Header/Footer\" angewählt haben.");
        $table .= $edit_form->editTextfieldGeneric("author", $title, $info, 50, 200);

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
