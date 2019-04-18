<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTableRowTwoColumns.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTableRowTwoColumns
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTableRowTwoColumns.class.php
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

class ExternElementTableRowTwoColumns extends ExternElement {

    var $attributes = ["tr_height", "tr_class", "tr_style", "td1_align",
            "td1_valign", "td1_bgcolor", "td1_class", "td1_style", "font1_face",
            "font1_size", "td1width", "font1_color", "font1_class", "font1_style",
            "td2_align", "td2_valign", "td2_bgcolor", "td2_class", "td2_style",
            "font2_face", "font2_size", "font2_color", "font2_class", "font2_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "TableRowTwoColumns";
        $this->real_name = _("Zeile mit zwei Spalten");
        $this->description = _("Angaben zur Formatierung einer Tabellenzeile mit zwei Spalten.");

        $this->headlines = [_("Angaben zum HTML-Tag &lt;tr&gt;"), _("Linke Spalte &lt;td&gt;"),
            _("Linke Spalte &lt;font&gt;"), _("Rechte Spalte &lt;td&gt;"),
            _("Rechte Spalte &lt;font&gt;")];
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

        $attributes = ["tr_height", "tr_class", "tr_style"];
        $headline = ["tr" => $this->headlines[0]];
        $content_table = $edit_form->getEditFormContent($attributes, $headline);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline($this->headlines[1]);

        $title = _('Spaltenbreite') . ':';
        $info = _("Breite der Spalte in Prozent.");
        $table = $edit_form->editTextfieldGeneric("td1width", $title, $info, 2, 2);

        $table .= $edit_form->editAlign("td1_align");
        $table .= $edit_form->editValign("td1_valign");
        $table .= $edit_form->editBgcolor("td1_bgcolor");
        $table .= $edit_form->editClass("td1_class");
        $table .= $edit_form->editStyle("td1_style");

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $attributes = ["font1_face",   "font1_size","font1_color", "font1_class",
                "font1_style", "td2_align", "td2_valign", "td2_bgcolor", "td2_class",
                "td2_style", "font2_face", "font2_size", "font2_color", "font2_class", "font2_style"];
        $headline = ["font1" => $this->headlines[2], "td2" => $this->headlines[3],
                "font2" => $this->headlines[4]];
        $content_table .= $edit_form->getEditFormContent($attributes, $headline);

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;

        return $out;
    }

}
