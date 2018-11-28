<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* extern_functions_templates.inc.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       extern_functions_templates
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_functions_templates.inc.php
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


/**
*
*
*/
function table_header ($element) {
    $out = "<table" . $element->getAttributes("table") . ">\n";

    return $out;
}

/**
*
*
*/
function table_headrow ($element, $fields) {
    $font_attributes = $element->getAttributes("font");

    $out = "<tr" . $element->getAttributes("tr") . ">\n";
    foreach ($fields as $field) {
        if ($font_attributes)
            $field = "<font$font_attributes>$field</font>";
        $out .= "<td" . $element->getAttributes("td") . ">" . $field . "</td>\n";
    }
    $out .= "<tr>\n";

    return $out;
}

/**
*
*
*/
function table_row ($element, $fields) {
    $font_attributes = $element->getAttributes("font");

    $out = "<tr" . $element->getAttributes("tr") . ">\n";
    foreach ($fields as $field) {
        if ($font_attributes)
            $field = "<font$font_attributes>$field</font>";
        $out .= "<td" . $element->getAttributes("td") . ">" . $field . "</td>\n";
    }
    $out .= "</tr>\n";

    return $out;
}

/**
*
*
*/
function table_group ($element, $group_name) {
    $colspan = " colspan=\"";
    $colspan .= sizeof($element->config->getValue("main", "order")) . "\"";

    if ($font_attributes = $element->getAttributes("font"))
        $group_name = "<font$font_attributes>$group_name</font>";

    $out = "<tr" . $element->getAttributes("tr") . ">\n";
    $out .= "<td" . $element->getAttributes("td") . $colspan . ">";
    $out .= $group_name;
    $out .= "</td>\n</tr>\n";

    return $out;
}

/**
*
*
*/
function table_footer () {
    $out = "</table>";

    return $out;
}

/**
*
*
*/
function html_header (&$config) {
    $out = "<!DOCTYPE html>\n";
    $out .= "<html>\n<head>\n";
    $out .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
    if ($copyright = $config->getValue('Main', 'copyright'))
        $out .= "<meta name=\"copyright\" content=\"$copyright\">\n";
    if ($author = $config->getValue('Main', 'author'))
        $out .= "<meta name=\"author\" content=\"$author\">\n";
    $out .= '<title>' . $config->getValue('Main', 'title') . "</title>\n";
    if ($urlcss = $config->getValue('Main', 'urlcss'))
        $out .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$urlcss\">\n";
    $out .= "</head>\n" . $config->getTag('Body', 'body') . "\n";

    return $out;
}

/**
*
*
*/
function html_footer () {

    return "</body>\n</html>";
}

?>
