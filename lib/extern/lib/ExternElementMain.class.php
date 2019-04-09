<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMain.class.php
* 
*  
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMain
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMain.class.php
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

class ExternElementMain extends ExternElement {

    var $attributes = [];
    var $edit_function;
    
    
    /**
    *
    */
    function GetInstance (&$config, $module_name, &$data_fields = null, &$field_names = null) {
        if ($module_name != '') {
            $main_class_name = 'ExternElementMain' . ucfirst($module_name);
            require_once "lib/extern/elements/main/$main_class_name.class.php";
            $main_module = new $main_class_name($module_name, $data_fields, $field_names, $config);
            
            return $main_module;
        }
        
        return NULL;
    }
    
    /**
    * Constructor
    *
    */
    function __construct ($module_name, &$data_fields, &$field_names, &$config) { 
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Elements ändern.");
        $this->name = 'Main';
        $this->edit_function = 'editMainSettings';
        $this->config =& $config;
        $this->data_fields =& $data_fields;
        $this->field_names =& $field_names;
        if (Config::get()->EXTERN_SRI_ENABLE && (!Config::get()->EXTERN_SRI_ENABLE_BY_ROOT ||
                (sri_is_enabled($this->config->range_id) && Config::get()->EXTERN_SRI_ENABLE_BY_ROOT))) {
            $this->attributes[] = 'sriurl';
        }
    }
    
    /**
    * 
    */
    function getDefaultConfig () {}
    
    /**
    * 
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {
        
        $out = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEdit($this->config, $post_vars, $faulty_values, $anker);
        
        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE);
        
        if ($faulty_values == '')
            $faulty_values = [];
        
        $edit_function = $this->edit_function;
        $table = $edit_form->$edit_function($this->field_names);

        $content_table = $edit_form->editContentTable($tag_headline, $table);
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();
        
        return $element_headline . $out;
    }
    
    function getSRIFormContent (&$edit_form, $include_url = false) {
        $content = '';
        $sri_info = _("Nur bei Benutzung der SRI-Schnittstelle für dieses Modul: Geben Sie hier die vollständige URL der Seite an, in die die Ausgabe des Moduls eingefügt werden soll.");
        if (!$include_url && Config::get()->EXTERN_SRI_ENABLE && (!Config::get()->EXTERN_SRI_ENABLE_BY_ROOT ||
                (sri_is_enabled($this->config->range_id) && Config::get()->EXTERN_SRI_ENABLE_BY_ROOT))) {
            $headline = $edit_form->editHeadline(_("URL des SRI-Templates"));
            $table = $edit_form->editTextfieldGeneric("sriurl", '', $sri_info, 70, 350);
            $content = $edit_form->editContentTable($headline, $table);
            $content .= $edit_form->editBlankContent();
        }
        if ($include_url) {
            $table = '';
            $headline = $edit_form->editHeadline(_("Einbindung des Moduls"));
            if (Config::get()->EXTERN_SRI_ENABLE && (!Config::get()->EXTERN_SRI_ENABLE_BY_ROOT ||(sri_is_enabled($this->config->range_id) && Config::get()->EXTERN_SRI_ENABLE_BY_ROOT))) {
                $table = $edit_form->editTextfieldGeneric('sriurl', 'SRI-URL', $sri_info, 50, 350);
            }
            $table .= $edit_form->editTextfieldGeneric('includeurl', 'include-URL', _("URL der Seite, in der die Ausgabe des Moduls per Include (z.B. durch eine Script-Sprache) eingebunden wird."), 50, 350);
            $content = $edit_form->editContentTable($headline, $table);
            $content .= $edit_form->editBlankContent();
        }
        return $content;
    }
        
}

?>
