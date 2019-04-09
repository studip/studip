<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTreeBackLink.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTreeBackLink
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTreeBackLink.class.php
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

class ExternElementTreeBackLink extends ExternElement {

    var $attributes = ["linktext", "image", "table_bgcolor",
                "table_cellpadding", "table_cellspacing", "table_class",
                "table_style", "td_height", "td_align", "td_valign", "td_bgcolor",
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
        
        $this->name = "TreeBackLink";
        $this->real_name = _("Link auf übergeordnete Ebene");
        $this->description = _("Formatierung des Link auf die übergeordnete Ebene in einer Baum-Navigation.");
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
        
        $headline = $edit_form->editHeadline(_("Allgemeine Angaben"));
        $title = _("Link-Text:");
        $info = _("Geben Sie den Text ein, der auf die übergeordnete Ebene verweist (Link zurück).");
        $content = $edit_form->editTextfieldGeneric("linktext", $title, $info, 25, 60);
        
        $title = _("Link-Bild (URL):");
        $info = _("Geben Sie die URL eines Bildes ein, das als Link auf die übergeordnete Ebene verweist (z.B. Back-Button).");
        $content .= $edit_form->editTextfieldGeneric("image", $title, $info, 40, 200);
        
        $content_table = $edit_form->editContentTable($headline, $content);
        $content_table .= $edit_form->editBlankContent();
        
        $out = $content_table . $edit_form->getEditFormContent($this->attributes);
        
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($out, $submit);
        $out .= $edit_form->editBlank();
        
        return  $element_headline . $out;
    }
    
}

?>
