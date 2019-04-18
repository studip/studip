<?php
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainDownload.class.php
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
// ExternElementMainDownload.class.php
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


class ExternElementMainDownload extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct ($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'order', 'visible', 'aliases', 'width', 'sort',
                'wholesite', 'lengthdesc', 'nameformat', 'urlcss', 'title',
                'nodatatext', 'dateformat', 'language', 'iconpic', 'icontxt',
                'iconpdf', 'iconppt', 'iconxls', 'iconrtf', 'iconzip',
                'icondefault', 'copyright', 'author'
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
            "order" => "|0|1|2|3|4|5",
            "visible" => "|1|1|1|1|1|1",
            "aliases" => "||"._("Name")."|"._("Beschreibung")."|"._("Upload am")."|"
                    ._("Größe")."|"._("Upload durch"),
            "width" => "|1%|20%|25%|15%|15%|24%",
            "sort" => "|0|0|0|1|0|0",
            "wholesite" => "",
            "lengthdesc" => "",
            "nameformat" => "",
            "urlcss" => "",
            "title" => _("Download"),
            "nodatatext" => _("Keine Dateien vorhanden"),
            "dateformat" => "%d. %b. %Y",
            "language" => "",
            "config" => "",
            "srilink" => "",
            "iconpic" => "",
            "icontxt" => "",
            "iconpdf" => "",
            "iconppt" => "",
            "iconxls" => "",
            "iconrtf" => "",
            "iconzip" => "",
            "icondefault" => "",
            "copyright" => htmlReady(Config::get()->UNI_NAME_CLEAN
                    . " ({$GLOBALS['UNI_CONTACT']})"),
            "author" => ""
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

        if ($faulty_values == '')
            $faulty_values = [];

        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName("name");
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $content_table .= $this->getSRIFormContent($edit_form);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));

        $edit_function = $this->edit_function;
        $table = $edit_form->$edit_function($this->field_names, ["sort" => [0]]);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $title = _('HTML-Header/Footer') . ':';
        $info = _("Anwählen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
        $values = "1";
        $names = "";
        $table = $edit_form->editCheckboxGeneric("wholesite", $title, $info, $values, $names);

        $title = _('Max. Länge der Beschreibung') . ':';
        $info = _("Geben Sie an, wieviele Zeichen der Beschreibung der Datei ausgegeben werden sollen.");
        $table .= $edit_form->editTextfieldGeneric("lengthdesc", $title, $info, 3, 3);

        $title = _('Namensformat') . ':';
        $info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
        $values = ["", "no_title_short", "no_title", "no_title_rev", "full", "full_rev"];
        $names = [_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
                _("Dr. Peter Meyer"), _("Meyer, Peter, Dr.")];
        $table .= $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);

        $title = _('Datumsformat') . ':';
        $info = _("Wählen Sie, wie Datumsangaben formatiert werden sollen.");
        $values = ["%d. %b. %Y", "%d.%m.%Y", "%d.%m.%y", "%d. %B %Y", "%m/%d/%y"];
        $names = [_("25. Nov. 2003"), _("25.11.2003"), _("25.11.03"),
                _("25. November 2003"), _("11/25/03")];
        $table .= $edit_form->editOptionGeneric("dateformat", $title, $info, $values, $names);

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

        $title = _('Keine Dateien') . ':';
        $info = _("Dieser Text wird an Stelle der Tabelle ausgegeben, wenn keine Dateien zum Download verfügbar sind.");
        $table .= $edit_form->editTextareaGeneric("nodatatext", $title, $info, 3, 50);

        $title = _('Copyright') . ':';
        $info = _("Geben Sie hier einen Copyright-Vermerk an. Dieser wird im Meta-Tag \"copyright\" ausgegeben, wenn Sie die Option \"HTML-Header/Footer\" angewählt haben.");
        $table .= $edit_form->editTextfieldGeneric("copyright", $title, $info, 50, 200);

        $title = _('Autor') . ':';
        $info = _("Geben Sie hier den Namen des Seitenautors an. Dieser wird im Meta-Tag \"author\" ausgegeben, wenn Sie die Option \"HTML-Header/Footer\" angewählt haben.");
        $table .= $edit_form->editTextfieldGeneric("author", $title, $info, 50, 200);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Eigene Icons"));
        $icon_attributes = ["iconpic", "icontxt", "iconpdf", "iconppt",
                "iconxls", "iconrtf", "iconzip", "icondefault"];
        $icon_titles = [
            _('Bilder') . ':',
            _('Text') . ':',
            _('Adobe pdf') . ':',
            _('Powerpoint (ppt)') . ':',
            _('Excel (xls)') . ':',
            _('Rich Text (rtf)') . ':',
            _('ZIP-Dateien') . ':',
            _('sonstige Dateien') . ':',
        ];
        $icon_infos = [
            _("Geben Sie die URL eines Bildes ein, dass als Icon für Bild-Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für Text-Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für PDF-Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für Powerpoint-Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für Excel-Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für RTF-Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für komprimierte Dateien dienen soll. Erlaubte Formate: jpg, png, gif. "),
            _("Geben Sie die URL eines Bildes ein, dass als Icon für alle anderen Dateiformate dienen soll. ")
        ];
        $info_add = _("Wenn Sie keine URL angeben, wird ein Standard-Icon ausgegeben.");

        $table = "";
        for ($i = 0; $i < sizeof($icon_attributes); $i++) {
            $table .= $edit_form->editTextfieldGeneric($icon_attributes[$i],
                    $icon_titles[$i], $icon_infos[$i] . $info_add, 50, 200);
        }

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        switch ($attribute) {
            case "lengthdesc" :
                return !preg_match("|^\d{0,3}$|", $value);
            case "timelocale" :
                return ($value != "de_DE" || $value != "en_US");
            case "iconpic" :
            case "icontxt" :
            case "iconpdf" :
            case "iconppt" :
            case "iconxls" :
            case "iconrtf" :
            case "iconzip" :
            case "icondefault" :
                return ($value[$i] != ""
                        && (preg_match("/(<|>|\"|<script|<php)/i", $value[$i])
                        || !preg_match("/^[^.\/\\\].*\.(png|jpg|jpeg|gif)$/i", $value[$i])));
        }

        return FALSE;
    }

}
