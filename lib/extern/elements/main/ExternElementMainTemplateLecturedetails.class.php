<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainTemplateLecturedetails.class.php
* 
*  
* 
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainTemplateLecturedetails
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainLecturedetails.class.php
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

class ExternElementMainTemplateLecturedetails extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'genericdatafields', 'rangepathlevel',
                'nameformat', 'dateformat', 'language'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
    }
    
    /**
    * 
    */
    function getDefaultConfig () {
        $config = [
            "name" => "",
            "rangepathlevel" => "1",
            "nameformat" => "",
            "dateformat" => '%d. %b. %Y',
            "language" => ""
        ];
        
        get_default_generic_datafields($config, "sem");
        
        return $config;
    }
    
    /**
    * 
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {
        
        update_generic_datafields($this->config, $this->data_fields, $this->field_names, "sem");
        $out = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
        
        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);
        
        if ($faulty_values == '')
            $faulty_values = [];
        
        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName("name");
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();
        
        $content_table .= $this->getSRIFormContent($edit_form);
        
        $headline = $edit_form->editHeadline(_("Weitere Angaben"));
        
        $title = _("Bereichspfad ab Ebene:");
        $info = _("Wählen Sie, ab welcher Ebene der Bereichspfad ausgegeben werden soll.");
        $values = ["1", "2", "3", "4", "5"];
        $names = ["1", "2", "3", "4", "5"];
        $table = $edit_form->editOptionGeneric("rangepathlevel", $title, $info, $values, $names);
        
        $title = _("Namensformat:");
        $info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
        $values = ["", "no_title_short", "no_title", "no_title_rev", "full", "full_rev"];
        $names = [_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
                _("Dr. Peter Meyer"), _("Meyer, Peter, Dr.")];
        $table .= $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);
        
        $title = _("Datumsformat:");
        $info = _("Wählen Sie, wie Datumsangaben formatiert werden sollen.");
        $values = ["%d. %b. %Y", "%d.%m.%Y", "%d.%m.%y", "%d. %B %Y", "%m/%d/%y"];
        $names = [_("25. Nov. 2003"), _("25.11.2003"), _("25.11.03"),
                _("25. November 2003"), _("11/25/03")];
        $table .= $edit_form->editOptionGeneric("dateformat", $title, $info, $values, $names);

        $title = _("Sprache:");
        $info = _("Wählen Sie eine Sprache für die Datumsangaben aus.");
        $values = ["", "de_DE", "en_GB"];
        $names = [_("keine Auswahl"), _("Deutsch"), _("Englisch")];
        $table .= $edit_form->editOptionGeneric("language", $title, $info, $values, $names);
        
        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();
        
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();
        
        return $element_headline . $out;
    }
    
    /*
    function checkValue ($attribute, $value) {
        if ($attribute == "studipinfo" || $attribute == "headlinerow") {
            // This is especially for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to "".
            if (!isset($_POST[$this->name . "_" . $attribute])) {
                $_POST[$this->name . "_" . $attribute] = "";
                return FALSE;
            }
            return !($value == "1" || $value == "");
        }
        
        return FALSE;
    }
    */
}

?>
