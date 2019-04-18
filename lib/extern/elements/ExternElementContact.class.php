<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementContact.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementContact
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementContact.class.php
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


class ExternElementContact extends ExternElement {

    var $attributes = ["order", "visible", "aliases", "headline", "adradd", "table_width",
                "table_align", "table_border", "table_bgcolor", "table_bordercolor", "table_cellpadding",
                "table_cellspacing", "table_class", "table_style", "tr_class",
                "tr_style", "td_height", "td_align", "td_valign", "td_bgcolor", "td_class", "td_style",
                "fonttitle_face", "fonttitle_size", "fonttitle_color", "fonttitle_class",
                "fonttitle_style", "fontcontent_face", "fontcontent_size", "fontcontent_color",
                "fontcontent_class", "fontcontent_style", "hidepersname", "hideinstname", "separatelinks",
                "showinstgroup", "defaultadr"];

    /**
    * Constructor
    *
    */
    function __construct() {
        $this->name = "Contact";
        $this->real_name = _("Name, Anschrift, Kontakt");
        $this->description = _("Allgemeine Angaben zum und Formatierung des Kontaktfeldes (Anschrift, E-Mail, Homepage usw.).");
    }

    function getDefaultConfig () {

        $config = [
            "order" => "|0|1|2|3|4|5",
            "visible" => "|1|1|1|1|1|1",
            "aliases" => "|"._("Raum").":|"._("Telefon").":|"._("Fax").":|"._("E-Mail").":|"
                    ._("Homepage").":|"._("Sprechzeiten").":",
            "headline" => _("Kontakt").":",
            "adrradd" => "",
            "hidepersname" => "",
            "hideinstname" => "",
            "separatelinks" => "",
            "showinstgroup" => "",
            "defaultadr" => ""
        ];

        return $config;
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $headline = $edit_form->editHeadline(_("Aufbau der Adress- und Kontakt-Tabelle"));
        $field_names = [_("Raum"), _("Telefon"), _("Fax"), _("E-Mail"), _("Homepage"), _("Sprechzeiten")];
        $table = $edit_form->editMainSettings($field_names, "", ["width", "sort", "widthpp"]);

        $title = _('Überschrift') . ':';
        $info = _("Überschrift der Kontakt-Daten");
        $table .= $edit_form->editTextfieldGeneric("headline", $title, $info, 35, 100);

        $title = _('Standard-Adresse') . ':';
        $info = _("Wenn Sie diese Option wählen, wird die Standard-Adresse ausgegeben, die jede(r) Mitarbeiter(in) bei seinen universitären Daten auswählen kann. Wählen Sie diese Option nicht, wenn immer die Adresse der Einrichtung ausgegeben werden soll.");
        $table .= $edit_form->editCheckboxGeneric('defaultadr', $title, $info, '1', '0');

        $title = _('Personenname ausblenden') . ':';
        $info = _("Unterdrückt die Anzeige des Namens im Adressfeld.");
        $table .= $edit_form->editCheckboxGeneric('hidepersname', $title, $info, '1', '0');

        $title = _('Funktionen anzeigen') . ':';
        $info = _("Ausgabe der Funktionen der Mitarbeiterin/des Mitarbeiters in der Einrichtung.");
        $table .= $edit_form->editCheckboxGeneric('showinstgroup', $title, $info, '1', '0');

        $title = _('Einrichtungsname') . ':';
        $info = _("Anzeige des Einrichtungsnamens. Der Name kann auch als Link auf die in Stud.IP angegebene URL (unter Grunddaten der Einrichtung) angezeigt werden.");
        $values = ['1', '0', 'link'];
        $names = [_("nicht anzeigen"), _("anzeigen"), _("als Link anzeigen")];
        $table .= $edit_form->editRadioGeneric('hideinstname', $title, $info, $values, $names);

        $title = _('E-Mail und Hompage getrennt') . ':';
        $info = _("Sinnvoll ist diese Option bei sehr langen E-Mail-Adressen und Homepage-Links der Mitarbeiter. Diese werden dann unterhalb des Adressfeldes ausgegeben.");
        $table .= $edit_form->editCheckboxGeneric('separatelinks', $title, $info, '1', '0');

        $title = _('Adresszusatz') . ':';
        $info = _("Zusatz zur Adresse der Einrichtung, z.B. Universitätsname.");
        $table .= $edit_form->editTextfieldGeneric("adradd", $title, $info, 35, 100);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $attributes = ["table_width", "table_align",
                "table_border", "table_bgcolor", "table_bordercolor", "table_cellpadding",
                "table_cellspacing", "table_class", "table_style", "tr_class", "tr_style",
                "td_height", "td_align", "td_valign", "td_bgcolor", "td_class", "td_style"];
        $content_table .= $edit_form->getEditFormContent($attributes);
        $content_table .= $edit_form->editBlankContent();

        $attributes = ["fonttitle_face", "fonttitle_size", "fonttitle_color", "fonttitle_class",
                "fonttitle_style", "fontcontent_face", "fontcontent_size", "fontcontent_color",
                "fontcontent_class", "fontcontent_style"];
        $headlines = ["fonttitle" => _("Schriftformatierung der Überschrift"),
                "fontcontent" => _("Schriftformatierung des Inhalts")];
        $content_table .= $edit_form->getEditFormContent($attributes, $headlines);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == 'hidepersname' || $attribute == 'separatelinks'
                || $attribute == 'defaultadr' || $attribute == 'showinstgroup') {
            if (!isset($_POST["Contact_$attribute"])) {
                $_POST["Contact_$attribute"] = 0;
                return FALSE;
            }

            return !($value == '1' || $value == '');
        }

        return FALSE;
    }

}
