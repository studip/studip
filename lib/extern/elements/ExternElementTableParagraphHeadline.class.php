<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTableParagraphHeadline.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTableParagraphHeadline
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTableParagraphHeadline.class.php
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

class ExternElementTableParagraphHeadline extends ExternElement {

    var $attributes = ["tr_class", "tr_style", "td_height", "td_align",
            "td_valign", "td_bgcolor", "td_class", "td_style", "font_face",
            "font_size", "font_color", "font_class", "font_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "TableParagraphHeadline";
        $this->real_name = _("Absatzüberschrift");
        $this->description = _("Angaben zur Formatierung einer Absatzüberschrift.");
    }

    function toString ($args = null) {
        $out = "\n" . $this->config->getTag($this->name, "tr") . "\n";
        $out .= $this->config->getTag($this->name, "td");
        if ($attributes_font = $this->config->getAttributes($this->name, "font"))
            $out .= "<font$attributes_font>{$args['content']}</font>";
        else
            $out .= $args["content"];
        $out .= "</td>\n</tr>";

        return $out;
    }

}
