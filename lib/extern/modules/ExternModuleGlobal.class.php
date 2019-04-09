<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleGlobal.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleGlobal
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModulePersons.class.php
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


require_once 'lib/extern/views/extern_html_templates.inc.php';

class ExternModuleGlobal extends ExternModule {

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->registered_elements = 
            [
                'PageBodyGlobal' => 'Body',
                'MainTableHeaderGlobal' => 'TableHeader',
                'InnerTableHeaderGlobal' => 'TableHeader',
                'MainTableHeadrowGlobal' => 'TableHeadrow',
                'TableGrouprowGlobal' => 'TableGroup',
                'TableRowGlobal' => 'TableRow',
                'TableHeadrowTextGlobal' => 'Link',
                'Headline1TextGlobal' => 'Link',
                'Headline2TextGlobal' => 'Link',
                'TextGlobal' => 'Link',
                'LinksGlobal' => 'Link'
            ];
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }
    
    /**
    *
    */
    function setup () {
        $this->elements["PageBodyGlobal"]->real_name = _("Seitenkörper");
        $this->elements["MainTableHeaderGlobal"]->real_name = _("Tabellenkopf Gesamttabelle");
        $this->elements["InnerTableHeaderGlobal"]->real_name = _("Tabellenkopf innere Tabelle");
        $this->elements["MainTableHeadrowGlobal"]->real_name = _("Kopfzeile");
        $this->elements["TableGrouprowGlobal"]->real_name = _("Gruppenzeile");
        $this->elements["TableRowGlobal"]->real_name = _("Datenzeile");
        $this->elements["TableHeadrowTextGlobal"]->real_name = _("Text in Tabellenkopf");
        $this->elements["Headline1TextGlobal"]->real_name = _("Überschriften erster Ordnung");
        $this->elements["Headline2TextGlobal"]->real_name = _("Überschriften zweiter Ordnung");
        $this->elements["TextGlobal"]->real_name = _("Schrift");
        $this->elements["LinksGlobal"]->real_name = _("Links");
        
        $this->elements["MainTableHeadrowGlobal"]->attributes = ["tr_class", "tr_style",
                "th_height", "th_align", "th_valign", "th_bgcolor", "th_bgcolor2_",
                "th_zebrath_", "th_class", "th_style"];
        $this->elements["TableGrouprowGlobal"]->attributes = ["tr_class", "tr_style",
                "td_height", "td_align", "td_valign", "td_bgcolor", "td_bgcolor_2", "td_class",
                "td_style"];
        $this->elements["TableRowGlobal"]->attributes = ["tr_class", "tr_style",
                "td_height", "td_align", "td_valign", "td_bgcolor", "td_bgcolor2_",
                "td_zebratd_", "td_class", "td_style"];
        $this->elements["TableHeadrowTextGlobal"]->attributes = ["font_size", "font_face",
                "font_color", "font_class", "font_style"];
        $this->elements["Headline1TextGlobal"]->attributes = ["font_size", "font_face",
                "font_color", "font_class", "font_style"];
        $this->elements["Headline2TextGlobal"]->attributes = ["font_size", "font_face",
                "font_color", "font_class", "font_style"];
        $this->elements["TextGlobal"]->attributes = ["font_size", "font_face",
                "font_color", "font_class", "font_style"];
        
    }
    
    /**
    *
    */
    function store ($element_name = '', $values = '') {
        $this->config->restore($this, $element_name, $values);
        $this->globalConfigMapping();
        $this->config->store();
    }
    
    /**
    *
    */
    function globalConfigMapping () {
    
        // mapping entire elements
                
        $elements_map["Body"][] = $this->elements["PageBodyGlobal"];
        $elements_map["TableHeader"][] = $this->elements["MainTableHeaderGlobal"];
        
        $elements_map["TableHeadrow"][] = $this->elements["MainTableHeadrowGlobal"];
        $elements_map["TableHeadrow"][] = $this->elements["TableHeadrowTextGlobal"];
        
        $elements_map["TableRow"][] = $this->elements["TableRowGlobal"];
        $elements_map["TableRow"][] = $this->elements["TextGlobal"];
        
        $elements_map["TableGroup"][] = $this->elements["TableGrouprowGlobal"];
        $elements_map["TableGroup"][] = $this->elements["Headline2TextGlobal"];
        
        $elements_map["Grouping"][] = $this->elements["TableGrouprowGlobal"];
        $elements_map["Grouping"][] = $this->elements["Headline2TextGlobal"];
        
        $elements_map["Link"][] = $this->elements["LinksGlobal"];
        $elements_map["LinkIntern"][] = $this->elements["LinksGlobal"];
        $elements_map["LinkInternSimple"][] = $this->elements["LinksGlobal"];
        $elements_map["LecturerLink"][] = $this->elements["LinksGlobal"];
        
        $elements_map["SemName"][] = $this->elements["Headline1TextGlobal"];
        $elements_map["Headline"][] = $this->elements["Headline2TextGlobal"];
        $elements_map["Headline"][] = $this->elements["TableGrouprowGlobal"];
        $elements_map["Content"][] = $this->elements["TextGlobal"];
        $elements_map["Content"][] = $this->elements["TableRowGlobal"];
        
        $elements_map["StudipLink"][] = $this->elements["LinksGlobal"];
        $elements_map["SemLink"][] = $this->elements["LinksGlobal"];
        
        $elements_map["Contact"][] = $this->elements["InnerTableHeaderGlobal"];
        
        $elements_map["TableParagraph"][] = $this->elements["InnerTableHeaderGlobal"];
        
        $elements_map["TableParagraphHeadline"][] = $this->elements["TableGrouprowGlobal"];
        $elements_map["TableParagraphHeadline"][] = $this->elements["Headline2TextGlobal"];
        
        $elements_map["TableParagraphSubHeadline"][] = $this->elements["TableRowGlobal"];
        $elements_map["TableParagraphSubHeadline"][] = $this->elements["TableHeadrowTextGlobal"];
        
        $elements_map["TableParagraphText"][] = $this->elements["TableRowGlobal"];
        $elements_map["TableParagraphText"][] = $this->elements["TextGlobal"];
                
        $elements_map["PersondetailsHeader"][] = $this->elements["Headline1TextGlobal"];
        
        foreach ($elements_map as $name => $elements) {
            foreach ($elements as $element) {
                foreach ($element->attributes as $attribute) {
                    $this->config->config[$name][$attribute]
                            = $this->config->getValue($element->name, $attribute);
                }
            }
        }
        
        // mapping single attributes
        
        $this->config->config["PersondetailsHeader"]["headlinetd_align"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_align");
        $this->config->config["PersondetailsHeader"]["headlinetd_valign"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_valign");
        $this->config->config["PersondetailsHeader"]["headlinetd_bgcolor"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_bgcolor");
        $this->config->config["PersondetailsHeader"]["headlinetd_class"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_class");
        $this->config->config["PersondetailsHeader"]["headlinetd_style"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_style");
        
        $this->config->config["SemName"]["td_align"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_align");
        $this->config->config["SemName"]["td_valign"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_valign");
        $this->config->config["SemName"]["td_bgcolor"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_bgcolor");
        $this->config->config["SemName"]["td_class"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_class");
        $this->config->config["SemName"]["td_style"]
                = $this->config->getValue("MainTableHeadrowGlobal", "th_style");
        
        $this->config->config["Contact"]["defaultadr"]
                = $this->config->getValue("Main", "defaultadr");
        
        $this->config->config["PersondetailsLectures"]["semstart"]
                = $this->config->getValue("Main", "semstart");
        $this->config->config["PersondetailsLectures"]["semrange"]
                = $this->config->getValue("Main", "semrange");
        $this->config->config["PersondetailsLectures"]["semswitch"]
                = $this->config->getValue("Main", "semswitch");
    }
    
    /**
    *
    */
    function printout ($args) {
    
    // nothing to print
    
    }
    
    /**
    *
    */
    function printoutPreview () {
    
    // nothing to print
    
    }
    
}

?>