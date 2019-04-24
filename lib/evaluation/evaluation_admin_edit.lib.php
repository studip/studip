<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * @author      Christian Bauer <alfredhitchcock@gmx.net>
 * @license     GPL2 or any later version
 *
 */

require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;

class EvalEdit
{
    /**
     * creates the main-table
     * @param string $title the title
     * @param string $left the left site of the table
     * @param string $rigt the right site of the table
     * @return  string  the html-table
     */
    public static function createSite($left = "", $right = "")
    {
        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("class", "blank");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "2");
        $table->addAttr("width", "100%");
        
        $tr = new HTML("tr");
        
        $td = new HTML("td");
        $td->addAttr("class", "blank");
        $td->addAttr("width", "100%");
        $td->addAttr("align", "left");
        $td->addAttr("valign", "top");
        $td->setTextareaCheck(YES);
        $td->addHTMLContent($left);
        
        $tr->addContent($td);
        
        $td = new HTML("td");
        $td->addAttr("class", "blank");
        $td->addAttr("align", "right");
        $td->addAttr("valign", "top");
        $td->addHTMLContent($right);
        
        $tr->addContent($td);
        $table->addContent($tr);
        
        return $table->createContent();
    }
    
    public function createHiddenIDs()
    {
        $input = new HTML ("input");
        $input->addAttr("type", "hidden");
        $input->addAttr("evalID", Request::option('evalID'));
        
        $input = new HTML ("input");
        $input->addAttr("type", "hidden");
        $input->addAttr("itemID", Request::option('itemID'));
        
        $input = new HTML ("input");
        $input->addAttr("type", "hidden");
        $input->addAttr("rangeID", Request::option("rangeID"));
        
        return;
    }
}
