<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainRangelecturetree.class.php
* 
* This class defines 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementRangelecturetree
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainRangelecturetree.class.php
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

class ExternElementMainRangeLectureTree extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'table_width', 'table_align', 'table_border', 'table_bgcolor',
                'table_bordercolor', 'table_cellpadding', 'table_cellspacing', 'table_class',
                'table_style', 'wholesite', 'urlcss', 'title', 'bodystyle',
                'bodyclass'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
    }
    
    /**
    * 
    */
    function getDefaultConfig () {
        
        $config = [];
        
        return $config;
    }
    
    /**
    * 
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {
        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
        
        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);
        
        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName("name");
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();
        
        $content_table .= $this->getSRIFormContent($edit_form);
        
        $attributes = ["table_width", "table_align", "table_border", "table_bgcolor",
                "table_bordercolor", "table_cellpadding", "table_cellspacing", "table_class",
                "table_style"];
        $headline = ["table" => _("Umschließende Tabelle")];
        $content_table .= $edit_form->getEditFormContent($attributes, $headline);
        $content_table .= $edit_form->editBlankContent();
        
        $headline = $edit_form->editHeadline(_("Weitere Angaben"));
        
        $title = _("HTML-Header/Footer:");
        $info = _("Anwählen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
        $wholesite_values = "1";
        $wholesite_names = "";
        $table = $edit_form->editCheckboxGeneric("wholesite", $title, $info, $wholesite_values, $wholesite_names);
        
        $title = _("Stylesheet-Datei:");
        $info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
        $table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);
        
        $title = _("Seitentitel:");
        $info = _("Geben Sie hier den Titel der Seite ein. Der Titel wird bei der Anzeige im Web-Browser in der Titelzeile des Anzeigefensters angezeigt.");
        $table .= $edit_form->editTextfieldGeneric("title", $title, $info, 50, 200);
        
        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();
        
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();
        
        return $element_headline . $out;
        
        return $out;
    }
    
}

?>
