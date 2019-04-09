<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementList.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementList
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementList.class.php
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

class ExternElementList extends ExternElement {

    var $attributes = ["ul_class", "ul_style", "li_class", "li_style", "margin"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;
        
        $this->name = "List";
        $this->real_name = _("Aufzählungsliste");
        $this->description = _("Eigenschaften einer Aufzählungsliste.");
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
        
        $content_table = $edit_form->getEditFormContent($this->attributes);
        $content_table .= $edit_form->editBlankContent();
        
        $headline = $edit_form->editHeadline(_("Einzug"));
        $title = _("Linker Einzug:");
        $info = _("Geben Sie an, wie weit (Pixel) die Aufzählungsliste im Absatz links eingerückt werden soll.");
        $content = $edit_form->editTextfieldGeneric("margin", $title, $info, 3, 3);
        
        $content_table .= $edit_form->editContentTable($headline, $content);
        $content_table .= $edit_form->editBlankContent();
                
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();
        
        return  $element_headline . $out;
    }
    
    function checkValue ($attribute, $value) {
        if ($attribute == "margin")
            return !preg_match("|^\d{0,3}$|", $value);
    }
    
    function toString ($args = null) {
        if ($args["level"] == "list") {
            if ($this->config->getValue($this->name, "margin")) {
                $out = "\n<div style=\"margin-left:" . $this->config->getValue($this->name, "margin");
                $out .= "\">" . $this->config->getTag($this->name, "ul") . $args["content"];
                $out .= "</ul></div>";
            }
            else
                $out = "\n" . $this->config->getTag($this->name, "ul") . $args["content"] . "</ul>";
        }
        else if ($args["level"] == "item")
            $out = "\n" . $this->config->getTag($this->name, "li") . $args["content"] . "</li>";
        else
            $out = "";
        
        return $out;
    }
    
}

?>
