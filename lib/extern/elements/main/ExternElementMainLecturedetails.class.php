<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainLecturedetails.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainDownload
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainLecturedetails.class.php
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

class ExternElementMainLecturedetails extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'genericdatafields', 'order', 'visible', 'aliases',
                'aliaspredisc', 'aliasfirstmeeting', 'headlinerow', 'rangepathlevel',
                'studipinfo',   'studiplink', 'studiplinktarget', 'wholesite',
                'nameformat', 'urlcss', 'title', 'language', 'copyright', 'author'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
    }

    /**
    *
    */
    function getDefaultConfig () {
        $config = [
            "name" => "",
            "order" => "|0|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15",
            "visible" => "|1|1|1|1|1|1|1|1|1|1|1|1|1|1|1|1",
            "aliases" => "|"._('Untertitel').": |"._("Lehrende").": |"._('Veranstaltungsart')
                .": |"._('Veranstaltungstyp').": |"._('Beschreibung').": |"._('Ort').": |"._('Semester')
                .": |"._('Zeiten').": |"._('Veranstaltungsnummer').": |"._('Teilnehmende')
                .": |"._('Voraussetzungen').": |"._('Lernorganisation').": |"._('Leistungsnachweis')
                .": |"._('Bereichseinordnung').": |"._('Sonstiges').": |"._('ECTS-Punkte'),
            "aliaspredisc" => _('Vorbesprechung') . ": ",
            "aliasfirstmeeting" => _('Erster Termin') . ": ",
            "headlinerow" => "1",
            "rangepathlevel" => "1",
            "studipinfo" => "1",
            "studiplink" => "top",
            "studiplinktarget" => "admin",
            "wholesite" => "",
            "nameformat" => "",
            "urlcss" => "",
            "title" => _("Veranstaltungsdaten"),
            "language" => "",
            "copyright" => htmlReady(Config::get()->UNI_NAME_CLEAN
                    . " ({$GLOBALS['UNI_CONTACT']})"),
            "author" => ""
        ];

        get_default_generic_datafields($config, "sem");

        return $config;
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        update_generic_datafields($this->config, $this->data_fields, $this->field_names, "sem");
        $out = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        if ($faulty_values == '')
            $faulty_values = [];

        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName("name");
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $content_table .= $this->getSRIFormContent($edit_form);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));

        $table = $edit_form->editMainSettings($this->field_names, "", ["sort", "width", "widthpp"]);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Textersetzungen"));

        $titles = _('Vorbesprechung') . ':';
        $info = _("Geben Sie eine alternative Bezeichnung ein.");
        $table = $edit_form->editTextfieldGeneric("aliaspredisc", $titles, $info, 40, 150);

        $titles = _('Erster Termin') . ':';
        $info = _("Geben Sie eine alternative Bezeichnung ein.");
        $table .= $edit_form->editTextfieldGeneric("aliasfirstmeeting", $titles, $info, 40, 150);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $title = _('Absatzüberschrift in eigener Zeile') . ':';
        $info = _("Diese Option bewirkt, dass die Überschrift eines Absatzes in einer eigenen Zeile ausgegeben wird. Ist diese Option nicht ausgewählt, wird die Überschrift dem Text des Absatzes direkt vorangestellt.");
        $values = "1";
        $names = "";
        $table = $edit_form->editCheckboxGeneric("headlinerow", $title, $info, $values, $names);

        $title = _('Bereichspfad ab Ebene') . ':';
        $info = _("Wählen Sie, ab welcher Ebene der Bereichspfad ausgegeben werden soll.");
        $values = ["1", "2", "3", "4", "5"];
        $names = ["1", "2", "3", "4", "5"];
        $table .= $edit_form->editOptionGeneric("rangepathlevel", $title, $info, $values, $names);

        $title = _('Stud.IP-Info') . ':';
        $info = _("Diese Option zeigt weitere Informationen aus der Stud.IP-Datenbank an (Anzahl Teilnehmender, Posting, Dokumente usw.).");
        $values = "1";
        $names = "";
        $table .= $edit_form->editCheckboxGeneric("studipinfo", $title, $info, $values, $names);

        $title = _('Stud.IP-Link') . ':';
        $info = _("Ausgabe eines Links, der direkt zum Stud.IP-Administrationsbereich verweist.");
        $value = ["top", "bottom", "0"];
        $names = [_("oberhalb"), _("unterhalb der Tabelle"), _("ausblenden")];
        $table .= $edit_form->editRadioGeneric("studiplink", $title, $info, $value, $names);

        $title = _('Stud.IP-Link-Ziel') . ':';
        $info = _("Ziel des Stud.IP-Links. Entweder direkter Einsprung zur Anmeldeseite oder in den Administrationsbereich (nur für berechtigte Nutzer) der Veranstaltung");
        $value = ["signin", "admin"];
        $names = [_("Anmeldung"), _("Administrationsbereich")];
        $table .= $edit_form->editRadioGeneric("studiplinktarget", $title, $info, $value, $names);

        $title = _('HTML-Header/Footer') . ':';
        $info = _("Anwählen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
        $values = "1";
        $names = "";
        $table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $values, $names);

        $title = _('Namensformat') . ':';
        $info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
        $values = ["", "no_title_short", "no_title", "no_title_rev", "full", "full_rev"];
        $names = [_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
                _("Dr. Peter Meyer"), _("Meyer, Peter, Dr.")];
        $table .= $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);

        $title = _('Sprache') . ':';
        $info = _("Wählen Sie eine Sprache für die Datumsangaben aus.");
        $values = ["", "de_DE", "en_GB"];
        $names = [_("keine Auswahl"), _("Deutsch"), _("Englisch")];
        $table .= $edit_form->editOptionGeneric("language", $title, $info, $values, $names);

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
        if ($attribute == "studipinfo" || $attribute == "headlinerow") {
            // This is especially for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to "".
            if (!isset($_POST[$this->name . "_" . $attribute])) {
                $_POST[$this->name . "_" . $attribute] = "";
                return FALSE;
            }
            return !($value == "1" || $value == "");
        }

        return FALSE;
    }

}
