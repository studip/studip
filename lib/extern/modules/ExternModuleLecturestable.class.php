<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleLecturestable.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleLecturestable
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLecturestable.class.php
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
require_once 'lib/extern/modules/views/ExternSemBrowseTable.class.php';

class ExternModuleLecturestable extends ExternModule {

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->data_fields = ['VeranstaltungsNummer', 'Name', 'Untertitel', 'status', 'Ort',
            'art', 'zeiten', 'dozent'];
        $this->registered_elements = [
            'ReplaceTextSemType',
            'SelectSubjectAreas',
            'Body',
            'TableHeader',
            'InfoCountSem' => 'TableGroup',
            'Grouping' => 'TableGroup',
            'TableHeadrow',
            'TableRow',
            'SemLink' => 'LinkIntern',
            'LecturerLink' => 'LinkIntern'];
        $this->field_names = [
                _("Veranstaltungsnummer"),
                _("Name"),
                _("Untertitel"),
                _("Status"),
                _("Ort"),
                _("Art"),
                _("Zeiten"),
                _("Lehrende")
        ];
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }
    
    function setup () {
        // extend $data_fields if generic datafields are set
        $config_datafields = $this->config->getValue("Main", "genericdatafields");
        $this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);
        
        // setup module properties
        $this->elements["InfoCountSem"]->real_name = _("Anzahl Veranstaltungen/Gruppierung");
        $this->elements["SemLink"]->link_module_type = 4;
        $this->elements["SemLink"]->real_name = _("Link zum Modul Veranstaltungsdetails");
        $this->elements["LecturerLink"]->link_module_type = 2;
        $this->elements["LecturerLink"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
    }
    
    function printout ($args) {
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);
        
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        
        $start_item_id = get_start_item_id($this->config->range_id);
        $browser = new ExternSemBrowseTable($this, $start_item_id);
        $browser->print_result();
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }
    
    function printoutPreview () {
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);
        
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        
        include "lib/extern/modules/views/lecturestable_preview.inc.php";
        
        if ($this->config->getValue("Main", "wholesite"))   
            echo html_footer();
    }
    
}

?>
