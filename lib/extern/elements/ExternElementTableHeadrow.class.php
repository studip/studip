<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTableHeadrow.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTableHeadrow
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTableHeadrow.class.php
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


class ExternElementTableHeadrow extends ExternElement {

    var $attributes = ["tr_class", "tr_style", "th_height", "th_align",
            "th_valign", "th_bgcolor", "th_bgcolor2_", "th_zebrath_", "th_class", "th_style",
            "font_face", "font_size", "font_color", "font_class", "font_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "TableHeadrow";
        $this->real_name = _("Kopfzeile");
        $this->description = _("Angaben, die die Kopfzeile einer Tabelle betreffen.");
    }

    function toString ($args = NULL) {
        if (!$args["main_module"])
            $args["main_module"] = "Main";

        $alias = $this->config->getValue($args["main_module"], "aliases");
        $visible = $this->config->getValue($args["main_module"], "visible");
        // if all visible aliases are empty return empty string
        $al_empty = TRUE;
        for ($i = 0; $i < sizeof($alias); $i++) {
            if ($alias[$i] != '' && $visible[$i]) {
                $al_empty = FALSE;
                break;
            }
        }
        if ($al_empty)
            return '';

        $out = "<tr" . $this->config->getAttributes($this->name, "tr") . ">\n";
        $i = 0;
        $zebra = $this->config->getValue($this->name, "th_zebrath_");
        $order = $this->config->getValue($args["main_module"], "order");
        $width = $this->config->getValue($args["main_module"], "width");
        $attributes[0] = $this->config->getAttributes($this->name, "th", TRUE);
        $attributes[1] = $this->config->getAttributes($this->name, "th", FALSE);
        $font = $this->config->getTag($this->name, "font", FALSE, TRUE);

        foreach ($order as $column) {

            // "zebra-effect" in head-row
            if ($zebra)
                $set = $attributes[++$i % 2];
            else
                $set = $attributes[1];

            if ($visible[$column]) {
            $out .= "<th$set width=\"" . $width[$column] . "\">";
                if ($font)
                    $out .= $font;
                if ($alias[$column])
                    $out .= $alias[$column];
                else
                    $out .= "&nbsp;";
                if ($font)
                    $out .= "</font>";
                $out .= "</th>\n";
            }
        }
        $out .= "</tr>\n";

        return $out;
    }

}
