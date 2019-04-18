<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTableRow.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTableRow
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTableRow.class.php
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

class ExternElementTableRow extends ExternElement {

    var $attributes = ["tr_class", "tr_style", "td_height", "td_align",
            "td_valign", "td_bgcolor", "td_bgcolor2_", "td_zebratd_",
            "td_class", "td_style", "font_face", "font_size", "font_color",
            "font_class", "font_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "TableRow";
        $this->real_name = _("Datenzeile");
        $this->description = _("Angaben zur Formatierung einer Datenzeile.");
    }

    function toString ($args = null) {
        static $i = 0;
        static $j = 0;

        if ($args["color"] === 0 || $args["color"] === 1)
            $i = $args["color"];
        if (!$args["main_module"])
            $args["main_module"] = "Main";
        $order = $this->config->getValue($args["main_module"], "order");
        $visible = $this->config->getValue($args["main_module"], "visible");
        $attributes[0] = $this->config->getAttributes($this->name, "td", FALSE);
        $attributes[1] = $this->config->getAttributes($this->name, "td", TRUE);
        $font = $this->config->getTag($this->name, "font", FALSE, TRUE);
        $zebra = $this->config->getValue($this->name, "td_zebratd_");
        $width = $this->config->getValue($args["main_module"], "width");

        // "horizontal zebra"
        if ($zebra == "HORIZONTAL")
            $set_td = $attributes[$i % 2];
        else
            $set_td = $attributes[0];

        $out = "<tr" . $this->config->getAttributes($this->name, "tr") . ">";

        foreach ($order as $column) {
            if ($visible[$column]) {
                // "vertical zebra"
                if ($zebra == "VERTICAL")
                    $set_td = $attributes[$j++ % 2];

                if (!$args["content"][$args["data_fields"][$column]])
                    $args["content"][$args["data_fields"][$column]] = "&nbsp;";
                $out .= "<td$set_td";
                if ($i == 0 && $width[$column] != '')
                    $out .= " width=\"$width[$column]\"";
                $out .= '>';
                if ($font)
                    $out .= $font . $args["content"][$args["data_fields"][$column]] . "</font>";
                else
                    $out .= $args["content"][$args["data_fields"][$column]];
                $out .= "</td>\n";
            }
        }
        $out .= "</tr>\n";
        $i++;

        return $out;
    }

}
