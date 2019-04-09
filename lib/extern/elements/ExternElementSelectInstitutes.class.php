<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementSelectInstitutes.class.php
* 
* 
* 
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementSelectInstitutes
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementSelectInstitutes.class.php
// 
// Copyright (C) 2006 Peter Thienel <thienel@data-quest.de>,
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

class ExternElementSelectInstitutes extends ExternElement {

    public $attributes = [];

    /**
    * Constructor
    *
    * @param array config
    */
    public function __construct ($config = '') {
        if ($config != '')
            $this->config = $config;
        
        $this->name = "SelectInstitutes";
        $this->real_name = _("Auswahl der anzuzeigenden Institute/Einrichtungen");
        $this->description = _("Sie können hier die Institute/Einrichtungen auswählen, die auf der externen Seite ausgegeben werden sollen.");
        $this->attributes = ['institutesselected'];
    }
    
    /**
    * 
    */
    public function getDefaultConfig () {
        $config = ['institutesselected' => '|'];
        
        return $config;
    }
    
    public function toStringEdit ($post_vars = '', $faulty_values = '', $edit_form = '', $anker = '') {
                            
        if ($faulty_values == '') {
            $faulty_values = [];
        }
        $out = '';
        $table = '';
        if ($edit_form == '') {
            $edit_form = new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);
        }
        
        $edit_form->setElementName($this->getName());
        $element_headline = $this->getEditFormHeadline($edit_form);
        
        $table = $edit_form->editSelectInstitutes();
        
        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();
        
        $submit = $edit_form->editSubmit($this->config->getName(), $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();
        
        return  $element_headline . $out;
    }
    
    public function checkValue ($attribute, $value) {
        if ($attribute == 'institutesselected') {
            if (!is_array($_POST[$this->name . '_' . $attribute])) {
                $_POST[$this->name . '_' . $attribute] = '';
                return FALSE;
            }
        }

        return FALSE;
    }
    
}

?>
