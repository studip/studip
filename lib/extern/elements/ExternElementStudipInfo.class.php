<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementStudipInfo.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementStudipInfo
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementStudipInfo.class.php
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


class ExternElementStudipInfo extends ExternElement {

    var $attributes = ["headline", "homeinst", "involvedinst", "countuser",
            "countpostings", "countdocuments", "font_face", "font_size", "font_color",
            "font_class", "font_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "StudipInfo";
        $this->real_name = _("Informationen aus Stud.IP");
        $this->description = _("Anzeige weiterer Informationen aus Stud.IP im Modul &quot;Veranstaltungsdetails&quot;.");
    }

    /**
    *
    */
    function getDefaultConfig () {
        $config = [
            "headline" => _("Weitere Informationen aus Stud.IP zu dieser Veranstaltung"),
            "homeinst" => _("Heimatinstitut:"),
            "involvedinst" => _("beteiligte Institute:"),
            "countuser" => _("In Stud.IP angemeldete Teilnehmende:"),
            "countpostings" => _("Anzahl der Postings im Stud.IP-Forum:"),
            "countdocuments" => _("Anzahl der Dokumente im Stud.IP-Downloadbereich:")
        ];

        return $config;
    }

    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        if ($faulty_values == '')
            $faulty_values = [];
        $out = '';
        $tag_headline = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $this->getEditFormHeadline($edit_form);

        $headline = $edit_form->editHeadline(_("Textersetzungen"));

        $info = _("Geben Sie jeweils einen Text ein, der an der entsprechenden Stelle ausgegeben werden soll.");
        $attributes = ["headline", "homeinst", "involvedinst", "countuser",
                "countpostings", "countdocuments"];
        $titles = [_("Überschrift:"), _("Heimatinstitut:"), _("beteiligte Institute:"),
                _("Teilnehmende:"), _("Forenbeiträge:"), _("Dokumente:")];
        for ($i = 0; $i < sizeof($attributes); $i++)
            $table .= $edit_form->editTextfieldGeneric($attributes[$i], $titles[$i], $info, 40, 150);

        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $attributes = ["font_face", "font_size", "font_color", "font_class", "font_style"];
        $headlines = ["font" => _("Schriftformatierung für Textersetzungen")];
        $content_table .= $edit_form->getEditFormContent($attributes, $headlines);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

    function toString ($args = null) {
        if ($attributes_font = $this->config->getAttributes($this->name, "font"))
            return "\n" . $this->config->getTag($this->name, "font") . $args["content"]
                . "</font>";

        return $args["content"];
    }

}

?>
