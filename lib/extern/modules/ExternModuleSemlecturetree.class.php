<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleSemlecturetree.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleSemlecturetree
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleSemlecturetree.class.php
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

class ExternModuleSemLectureTree extends ExternModule {

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->registered_elements = [
                'Body', 'TreePath', 'TreeLevelName', 'TreeLevelContent', 'TreeKids',
                'TreeBackLink'
        ];
        $this->args = ['sem', 'start_item_id'];
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }
    
    function printout ($args) {
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);
        
        require_once "lib/extern/modules/views/ExternSemLectureTree.class.php";
        
        $tree = new ExternSemLectureTree($this->config, $args["start_item_id"]);
        $tree->showSemTree();
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }
    
    function printoutPreview ($args) {
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);
        
        require_once "lib/extern/modules/views/ExternSemLectureTree.class.php";
        
        $tree = new ExternSemLectureTree($this->config, $args["start_item_id"]);
        $tree->showSemTree();
        
        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }
    
}

?>
