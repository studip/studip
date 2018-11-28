<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Beschreibung
 *
 * @author      Christian Bauer <alfredhitchcock@gmx.net>
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
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

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;
//require_once (HTMLempty);
# ====================================================== end: including files #
class EvalEdit{

    /**
     * creates the main-table
     * @access  public
     * @param   string  $title  the title
     * @param   string  $left  the left site of the table
     * @param   string  $rigt  the right site of the table
     * @return  string  the html-table
    */
    function createSite($left = "", $right = ""){

        $table = new HTML("table");
        $table->addAttr ("border","0");
        $table->addAttr ("class","blank");
        $table->addAttr ("align","center");
        $table->addAttr ("cellspacing","0");
        $table->addAttr ("cellpadding","2");
        $table->addAttr ("width","100%");
            
        $tr = new HTML("tr");
        
        $td = new HTML("td");
        $td->addAttr ("class","blank");
        $td->addAttr ("width","100%");
        $td->addAttr ("align","left");
        $td->addAttr ("valign","top");
        $td->setTextareaCheck(YES);
        $td->addHTMLContent ($left);
        
        $tr->addContent ($td);
            
        $td = new HTML("td");
        $td->addAttr ("class","blank");
        $td->addAttr ("align","right");
        $td->addAttr ("valign","top");
        $td->addHTMLContent ($right);
        
        $tr->addContent ($td);
        $table->addContent ($tr);

        return $table->createContent();
    }
    
    function createHiddenIDs(){
        $input = new HTML ("input");
        $input->addAttr ("type","hidden");
        $input->addAttr ("evalID",Request::option('evalID'));
        
        $input = new HTML ("input");
        $input->addAttr ("type","hidden");
        $input->addAttr ("itemID",Request::option('itemID'));
        
        $input = new HTML ("input");
        $input->addAttr ("type","hidden");
        $input->addAttr ("rangeID",Request::option("rangeID"));
        
        return ;
    }
}
?>
