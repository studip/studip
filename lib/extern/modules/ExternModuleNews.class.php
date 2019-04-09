<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleNews.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleNews
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleNews.class.php
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

class ExternModuleNews extends ExternModule {

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->registered_elements = [
                                'Body',
                                'TableHeader',
                                'TableHeadrow',
                                'TableRow',
                                'ContentNews',
                                'LinkInternSimple' => 'LinkIntern',
                                'StudipLink'];
        $this->data_fields = ['date', 'topic'];
        $this->field_names = 
        [
                _("Datum/Autor"),
                _("Nachricht")
        ];
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }
    
    function setup () {
        $this->elements["LinkInternSimple"]->link_module_type = 2;
        $this->elements["LinkInternSimple"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
        $this->elements["TableRow"]->real_name = _("Datenzeilen, Schrift von Name und Datum");
    }
    
    function printout ($args) {
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);
        
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        
        include "lib/extern/modules/views/news.inc.php";
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }
    
    function printoutPreview () {
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);
        
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        
        include "lib/extern/modules/views/news_preview.inc.php";
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }
    
}

?>
