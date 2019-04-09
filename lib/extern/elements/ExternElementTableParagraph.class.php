<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTableParagraph.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementParagraph
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTableParagraph.class.php
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

class ExternElementTableParagraph extends ExternElement {

    var $attributes = ["table_width", "table_align", "table_valign", "table_border",
            "table_bgcolor", "table_bordercolor", "table_cellpadding", "table_cellspacing",
            "table_class", "table_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;
        
        $this->name = "TableParagraph";
        $this->real_name = _("Allgemeine Angaben zum Absatz");
        $this->description = _("Der Absatz wird mit Hilfe einer Tabelle aufgebaut.");
    }
    
    function toString ($args = null) {
        return "\n" . $this->config->getTag($this->name, "table") . $args["content"]
                . "</table>";
    }
    
}

?>
