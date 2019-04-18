<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTableGroup.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTableGroup
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTableGroup.class.php
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


class ExternElementTableGroup extends ExternElement {

    var $attributes = ["tr_class", "tr_style", "td_height", "td_align",
            "td_valign", "td_bgcolor", "td_bgcolor_2", "td_class", "td_style",
            "font_face", "font_size", "font_color", "font_class", "font_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct ($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "TableGroup";
        $this->real_name = _("Gruppenüberschriften");
        $this->description = _("Gruppenüberschriften sind Tabellenzeilen, die eine neue Gruppe einleiten.");
    }

    function toString ($args = null) {
        if (!$args["main_module"])
            $args["main_module"] = "Main";
        if (isset($args["colspan"]))
            $visible["1"] = $args["colspan"];
        else
            $visible = array_count_values($this->config->getValue($args["main_module"], "visible"));

        if ($tag = $this->config->getTag($this->name, "font", FALSE, TRUE))
            $content = $tag . $args["content"] . "</font>";
        else
            $content = $args["content"];
        $out = "<tr" . $this->config->getAttributes($this->name, "tr") . ">";
        if ($visible["1"] > 1)
            $out .= "<td colspan=\"{$visible['1']}\" ";
        else
            $out .= "<td";
        $out .= $this->config->getAttributes($this->name, "td") . ">";
        $out .= "$content</td></tr>\n";

        return $out;
    }

}
