<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementLecturesInnerTable.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementLecturesInnerTable
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLecturesInnerTable.class.php
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


class ExternElementLecturesInnerTable extends ExternElement {

    var $attributes = ["tr_class", "tr_style", "td_bgcolor", "td_bgcolor2_",
            "td_class", "td_style", "td1_height", "td1_align", "td1_valign",
            "td2_height", "td2_align", "td2_valign", "td2width", "font2_face",
            "font2_size", "font2_color", "font2_class", "font2_style", "td3_align"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "LecturesInnerTable";
        $this->real_name = _("Veranstaltungsname/Zeiten(Termine)/Lehrende");
        $this->description = _("Formatierung von Veranstaltungsname/Zeiten(Termine)/Lehrenden in der Veranstaltungsübersicht.");

        $this->headlines = [_("Angaben zum HTML-Tag &lt;tr&gt;"), _("Angaben zum HTML-Tag &lt;td&gt;"),
            _("Ausrichtung Veranstaltungsname"), _("Ausrichtung Zeiten(Termine)/Lehrenden"),
            _("Schrift Zeiten(Termine)/Lehrende (HTML-Tag &lt;font&gt;)")];
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

        $attributes = ["tr_class", "tr_style", "td_bgcolor", "td_bgcolor2_",
                "td_class", "td_style"];
        $headline = ["tr" => $this->headlines[0], "td" => $this->headlines[1]];
        $content_table = $edit_form->getEditFormContent($attributes, $headline);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline($this->headlines[2]);

        $table = $edit_form->editHeight("td1_height");
        $table .= $edit_form->editAlign("td1_align");
        $table .= $edit_form->editValign("td1_valign");

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline($this->headlines[3]);

        $table = $edit_form->editHeight("td2_height");

        $title = _('Spaltenbreite Zeiten') . ':';
        $info = _("Breite der Spalte \"Zeiten(Termine)\" in Prozent.");
        $table .= $edit_form->editTextfieldGeneric("td2width", $title, $info, 2, 2);

        $title = _('Horizontale Ausrichtung Zeiten') . ':';
        $info = _("Wählen Sie aus der Auswahlliste die Art der horizontalen Ausrichtung.");
        $values = ["left", "right", "center"];
        $names = [_("linksbündig"), _("rechtsbündig"), _("zentriert")];
        $table .= $edit_form->editOptionGeneric("td2_align", $title, $info, $values, $names);

        $title = _('Horizontale Ausrichtung Lehrende') . ':';
        $info = _("Wählen Sie aus der Auswahlliste die Art der horizontalen Ausrichtung.");
        $values = ["left", "right", "center"];
        $names = [_("linksbündig"), _("rechtsbündig"), _("zentriert")];
        $table .= $edit_form->editOptionGeneric("td3_align", $title, $info, $values, $names);

        $table .= $edit_form->editValign("td2_valign");

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $attributes = ["font2_face", "font2_size", "font2_color", "font2_class", "font2_style"];
        $headline = ["font2" => $this->headlines[4]];
        $content_table .= $edit_form->getEditFormContent($attributes, $headline);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;

        return $out;
    }

    function toString ($args = null) {
        static $zebra = 0;

        $show_time = $this->config->getValue("Main", "time");
        $show_lecturer = $this->config->getValue("Main", "lecturer");
        if ($show_time && $show_lecturer) {
          if (!$td2width = $this->config->getValue($this->name, "td2width"))
            $td2width = 50;
          $colspan = " colspan=\"2\"";
          $td_time = $this->config->getAttributes($this->name, "td2");
          $td_time .= " width=\"$td2width%\"";
          $td_lecturer = " align=\"" . $this->config->getValue($this->name, "td3_align");
          $td_lecturer .= "\" valign=\"" . $this->config->getValue($this->name, "td2_valign");
          $td_lecturer .= "\" width=\"" . (100 - $td2width) . "%\"";
        }
        else {
          $colspan = "";
          $td_time = $this->config->getAttributes($this->name, "td2") . " width=\"100%\"";
          $td_lecturer = " align=\"" . $this->config->getValue($this->name, "td3_align");
          $td_lecturer .= "\" valign=\"" . $this->config->getValue($this->name, "td2_valign");
          $td_lecturer .= " width=\"100%\"";
        }

        $out = "<tr" . $this->config->getAttributes($this->name, "tr").">";
        if ($zebra % 2 && $this->config->getValue($this->name, "td_bgcolor2_"))
            $out .= "<td width=\"100%\"".$this->config->getAttributes($this->name, "td", TRUE)."\">\n";
        else
            $out .= "<td width=\"100%\"".$this->config->getAttributes($this->name, "td", FALSE)."\">\n";
        $zebra++;
        $out .= "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
        $out .= "<tr" . $this->config->getAttributes($this->name, "tr1") . ">";
        $out .= "<td$colspan" . $this->config->getAttributes($this->name, "td1") . ">";
        $out .= "{$args['content']['sem_name']}</td></tr>\n";

        if ($show_time || $show_lecturer) {
            $out .= "\n<tr" . $this->config->getAttributes($this->name, "tr2") . ">";
            if ($show_time) {
                $out .= "<td$td_time>";
                $out .= "<font" . $this->config->getAttributes($this->name, "font2") . ">";
                $out .= "{$args['content']['turnus']}</font></td>\n";
            }
            if ($show_lecturer) {
                $out .= "<td$td_lecturer>";
                $out .= "<font" . $this->config->getAttributes($this->name, "font2") . ">";

                $out .= "{$args['content']['lecturers']}</font></td>";
            }
            $out .= "</tr>";
        }
        $out .= "</table></td></tr>\n";

        return $out;
    }

}
