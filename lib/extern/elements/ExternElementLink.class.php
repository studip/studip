<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementLink.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElement
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLink.class.php
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

class ExternElementLink extends ExternElement {

    var $attributes = ["font_size", "font_face", "font_color", "font_class", "font_style",
            "a_class", "a_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;
        
        $this->name = "Link";
        $this->real_name = _("Links");
        $this->description = _("Eigenschaften der Schrift für Links.");
    }
    
    function toString ($args = null) {
        // to set the color of the font in the style-attribute of the a-tag
        if ($color = $this->config->getValue($this->name, "font_color")) {
            $style = $this->config->getValue($this->name, "a_style");
            $style = "color:$color;$style";
            $this->config->setValue($this->name, "a_style", $style);
        }
        
        $out = $args["content"];
        if ($tag = $this->config->getTag($this->name, "font", FALSE, TRUE))
            $out = $tag . $out . "</font>";
        $out = "<a href=\"{$args['link']}\"" . $this->config->getAttributes($this->name, "a")
                . ">$out</a>";
        
        return $out;
    }
    
}

?>
