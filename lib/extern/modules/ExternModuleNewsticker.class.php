<?
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleNewsticker.class.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleNewsticker
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

class ExternModuleNewsticker extends ExternModule {

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }
    
    function setup () {}
    
    function printout ($args) {
        echo $this->toString();
    }
    
    function printoutPreview () {
        echo html_header($this->config);
                
        echo $this->toStringPreview();
        
        echo html_footer();
    }
    
    function toString ($args = NULL) {
        $js_only = $this->config->getValue("Main", "jsonly");
        if (!$js_only)
            $out = "<script type=\"text/javascript\">\n<!--\n";
        $out .= "var newsticker_max = 0;\n\n";
        $out .= "function textlist() {\n\tnewsticker_max = textlist.arguments.length;\n\t";
        $out .= "for (i = 0; i < newsticker_max; i++)\n\t\tthis[i] = textlist.arguments[i];\n}\n\n";
        $out .= "newsticker_tl = new textlist(";
        
        $topics = [];
        foreach(StudipNews::GetNewsByRange($this->config->range_id, true) as $news_content){
            $topics[] = "'" . addslashes($news_content["topic"]) . "'";
        }
        if (!count($topics))
            $topics[] = "'" . $this->config->getValue("Main", "nodatatext") . "'";
        if ($this->config->getValue("Main", "endtext"))
            $topics[] = "'" . $this->config->getValue("Main", "endtext") . "'";
        
        $out .= implode(", ", $topics) . ");\n\n";
        
        $out .= "var newsticker_x = 0; newsticker_pos = 0;\n";
        $out .= "var newsticker_l = newsticker_tl[0].length;\n\n";
        $out .= "function newsticker() {\n\t";
        $out .= "document.tickform.tickfield.value = newsticker_tl[newsticker_x].substring(0, newsticker_pos) + \"_\";\n";
        $out .= "\tif (newsticker_pos++ == newsticker_l) {\n";
        $out .= "\t\tnewsticker_pos = 0;\n\t\tsetTimeout(\"newsticker()\", ";
        
        $out .= $this->config->getValue("Main", "pause");
        
        $out .= ");\n\t\tif (++newsticker_x == newsticker_max)\n\t\t\tnewsticker_x = 0;\n"; 
        $out .= "\t\tnewsticker_l = newsticker_tl[newsticker_x].length;\n\t}\n";
        $out .= "\telse\n\t\tsetTimeout(\"newsticker()\", ";
        
        $out .= ceil(1000 / $this->config->getValue("Main", "frequency"));
        
        $out .= ");\n}\n";
        if (!$js_only) {
            $out .= "//-->\n</script>\n";
            $out .= "<form name=\"tickform\">\t\n<textarea name=\"tickfield\" rows=\"";
        
            $out .= $this->config->getValue("Main", "rows") . "\" cols=\"";
            $out .= $this->config->getValue("Main", "length") . "\" style=\"";
            $out .= $this->config->getValue("Main", "style") . "\" wrap=\"virtual\">";
            $out .= $this->config->getValue("Main", "starttext");
            $out .= "</textarea>\n</form>\n";
        
            if ($this->config->getValue("Main", "automaticstart"))
                $out .= "<script type=\"text/javascript\">\n\tnewsticker();\n</script>\n";
        }
        
        return $out;
    }
    
    function toStringPreview () {
        $out = "<script type=\"text/javascript\">\n<!--\nvar newsticker_max = 0;\n";
        $out .= "function textlist() {\n\tnewsticker_max = textlist.arguments.length;\n\t";
        $out .= "for (i = 0; i < newsticker_max; i++)\n\t\tthis[i] = textlist.arguments[i];\n}\n\n";
        $out .= "newsticker_tl = new textlist(";
        
        for ($i = 1; $i < 5; $i++)
            $topics[] = sprintf("'" . _("Das ist News Nummer %s!") . "'", $i);
        if ($this->config->getValue("Main", "endtext"))
            $topics[] = "'" . $this->config->getValue("Main", "endtext") . "'";     
        
        $out .= implode(", ", $topics) . ")\n\n";
        
        $out .= "var newsticker_x = 0; newsticker_pos = 0;\n";
        $out .= "var newsticker_l = newsticker_tl[0].length;\n\n";
        $out .= "function newsticker() {\n\t";
        $out .= "document.tickform.tickfield.value = newsticker_tl[newsticker_x].substring(0, newsticker_pos) + \"_\";\n";
        $out .= "\tif (newsticker_pos++ == newsticker_l) {\n";
        $out .= "\t\tnewsticker_pos = 0;\n\t\tsetTimeout(\"newsticker()\", ";
        
        $out .= $this->config->getValue("Main", "pause");
        
        $out .= ");\n\t\tif (++newsticker_x == newsticker_max)\n\t\t\tnewsticker_x = 0;\n"; 
        $out .= "\t\tnewsticker_l = newsticker_tl[newsticker_x].length;\n\t}\n";
        $out .= "\telse\n\t\tsetTimeout(\"newsticker()\", ";
        
        $out .= ceil(1000 / $this->config->getValue("Main", "frequency"));
        
        $out .= ");\n}\n//-->\n</script>\n";
        $out .= "<form name=\"tickform\">\t\n<textarea name=\"tickfield\" rows=\"";
        
        $out .= $this->config->getValue("Main", "rows") . "\" cols=\"";
        $out .= $this->config->getValue("Main", "length") . "\" style=\"";
        $out .= $this->config->getValue("Main", "style") . "\" wrap=\"virtual\">";
        $out .= $this->config->getValue("Main", "starttext");
        $out .= "</textarea>\n</form>\n";
        
        if ($this->config->getValue("Main", "automaticstart"))
            $out .= "<script type=\"text/javascript\">\n\tnewsticker();\n</script>\n";
        
        return $out;
    }
    
}

?>
