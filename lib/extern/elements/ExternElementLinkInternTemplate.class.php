<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementLinkInternTemplate.class.php
* 
* 
* 
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementLinkInternTemplate
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLinkInternTemplate.class.php
// 
// Copyright (C) 2007 Peter Thienel <thienel@data-quest.de>,
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

require_once 'ExternElementLinkIntern.class.php';

class ExternElementLinkInternTemplate extends ExternElementLinkIntern {

    var $attributes = ['config', 'srilink', 'externlink'];
    var $link_module_type;

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = '') {
        if ($config) {
            $this->config = $config;
        }
        
        $this->name = "LinkInternTemplate";
        $this->real_name = _("Links");
        $this->description = _("Eigenschaften der Verlinkung zu anderen Modulen.");
    }
    
    /**
    * 
    */
    function toStringEdit ($post_vars = '', $faulty_values = '',
            $edit_form = '', $anker = '') {
        global $EXTERN_MODULE_TYPES;
        $out = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
        
        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);
        
        $this->toStringConfigSelector($edit_form, $content_table);
                
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();
        
        return $element_headline . $out;
    }
    
    function toString ($args = null) {
        
        return $this->createUrl($args);
    }
    
}

?>