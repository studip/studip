<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Export-functions to create links to the export-module.
*
* In this file there are three functions which help to include the export-module into Stud.IP-pages.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_linking_functions
* @package      Export
*/
require_once 'lib/export/export_config.inc.php';

use Studip\Button, Studip\LinkButton;

/**
* Generates a form that can be put into Stud.IP-pages to link to the export-module.
*
* This function returns a string with a HTML-form that links to the export-module.
* It passes the given parameters in order to allow to jump to a specific part of the export-module.
*
* @access   public
* @param        string  $range_id   export-range
* @param        string  $ex_type    type of data to be exported
* @param        string  $filename   filename for data-file
* @param        string  $format file-format for export
* @param        string  $filter grouping-category for export
* @return       string
*/
function export_form($range_id, $ex_type = "", $filename = "", $format = "", $filter = "")
{
    global $output_formats, $xslt_filename;
    $filename = $xslt_filename;
    require_once ("lib/export/export_xslt_vars.inc.php");
    $export_string .= "<form action=\"" . "export.php\" method=\"post\">";
    $export_string .= CSRFProtection::tokenTag();
    $export_string .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"table_row_even\"> &nbsp; &nbsp; &nbsp; ";

    $export_string .= "<font size=\"-1\"><b> "._("Diese Daten exportieren: ") .  "</b></font>";
    $export_string .= "</td><td align=\"center\" class=\"table_row_even\">";
    $export_string .= "<select name=\"format\">";
    while (list($key, $val) = each($output_formats))
    {
        $export_string .= "<option value=\"" . $key . "\"";
        if ($format==$key) $export_string .= " selected";
        $export_string .= ">" . $val;
    }
    $export_string .= "</select>";

    $export_string .= "</td><td align=\"right\" class=\"table_row_even\">";
    $export_string .= Button::create(_('Export'), 'export', ['title' => _('Diese Daten Exportieren')]);

    $export_string .= "<input type=\"hidden\" name=\"range_id\" value=\"$range_id\">";
    $export_string .= "<input type=\"hidden\" name=\"o_mode\" value=\"choose\">";
    $export_string .= "<input type=\"hidden\" name=\"page\" value=\"1\">";
    $export_string .= "<input type=\"hidden\" name=\"ex_type\" value=\"$ex_type\">";
    $export_string .= "<input type=\"hidden\" name=\"filter\" value=\"$filter\">";
    $export_string .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"$filename\">";
    $export_string .= "</td></tr></table>";
    $export_string .= "</form>";
    return $export_string;
}

/**
* Generates a form that can be put into the sidebar to link to the export-module.
*
* This function returns a string with a HTML-form that links to the export-module.
* It passes the given parameters in order to allow to jump to a specific part of the export-module.
*
* @access   public
* @param        string  $range_id   export-range
* @param        string  $ex_type    type of data to be exported
* @param        string  $filename   filename for data-file
* @param        string  $format file-format for export
* @param        string  $filter grouping-category for export
* @return       string
*/
function export_form_sidebar($range_id, $ex_type = "", $filename = "", $format = "", $filter = "")
{
    global $output_formats, $xslt_filename;
    $filename = $xslt_filename;
    require_once ("lib/export/export_xslt_vars.inc.php");
    $export_string .= "<form class=\"default\" action=\"" .$GLOBALS['ABSOLUTE_URI_STUDIP']. "export.php\" method=\"post\">";
    $export_string .= CSRFProtection::tokenTag();
    $export_string .= "<select name=\"format\">";
    while (list($key, $val) = each($output_formats))
    {
        $export_string .= "<option value=\"" . $key . "\"";
        if ($format==$key) $export_string .= " selected";
        $export_string .= ">" . my_substr($val, 0, 20) . "</option>";
    }
    $export_string .= "</select>";

    $export_string .= Button::create(_('Export'), 'export', ['title' => _('Daten Exportieren')]);
    $export_string .= "<input type=\"hidden\" name=\"range_id\" value=\"$range_id\">";
    $export_string .= "<input type=\"hidden\" name=\"o_mode\" value=\"choose\">";
    $export_string .= "<input type=\"hidden\" name=\"page\" value=\"1\">";
    $export_string .= "<input type=\"hidden\" name=\"ex_type\" value=\"$ex_type\">";
    $export_string .= "<input type=\"hidden\" name=\"filter\" value=\"$filter\">";
    $export_string .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"$filename\">";
    $export_string .= "</form>";
    return $export_string;
}

/**
* Generates a link to the export-module that can be put into Stud.IP-pages.
*
* This function returns a string with a  link to the export-module.
* It passes the given parameters in order to allow to jump to a specific part of the export-module.
*
* @access   public
* @param        string  $range_id   export-range
* @param        string  $ex_type    type of data to be exported
* @param        string  $filename   filename for data-file
* @param        string  $format file-format for export
* @param        string  $choose xslt-Script for transformation
* @param        string  $filter grouping-category for export
* @return       string
*/
function export_link($range_id, $ex_type = "", $filename = "", $format = "", $choose = "", $filter = "", $content = "", $o_mode = 'processor')
{
    global $xslt_filename, $i_page;

    $export_string = '<a href="';
    if ($choose != "")
        $export_string .= URLHelper::getLink('export.php', ['range_id' => $range_id, 'ex_type' => $ex_type, 'xslt_filename' => $filename, 'format' => $format, 'choose' => $choose, 'o_mode' => $o_mode, 'filter' => $filter, 'jump' => $i_page]);
    elseif ($ex_type != "")
        $export_string .= URLHelper::getLink('export.php', ['range_id' => $range_id, 'ex_type' => $ex_type, 'xslt_filename' =>  $filename, 'o_mode' => 'choose', 'filter' => $filter]);
    else
        $export_string .= URLHelper::getLink('export.php', ['range_id' => $range_id, 'o_mode' => 'start']);

    $export_string .= '">' . ($content ? $content : _("Diese Daten exportieren"));
    $export_string .= '</a>';
    return $export_string;
}

/**
* Generates a Button with a link to the export-module that can be put into Stud.IP-pages.
*
* This function returns a string containing an export-button with a link to the export-module.
* It passes the given parameters in order to allow to jump to a specific part of the export-module.
*
* @access   public
* @param        string  $range_id   export-range
* @param        string  $ex_type    type of data to be exported
* @param        string  $filename   filename for data-file
* @param        string  $format file-format for export
* @param        string  $choose xslt-Script for transformation
* @param        string  $filter grouping-category for export
* @return       string
*/
function export_button($range_id, $ex_type = "", $filename = "", $format = "", $choose = "", $filter = "")
{
    global $xslt_filename, $i_page;
    $export_link = '';
    if ($choose != "")
        $export_link .= "export.php?range_id=$range_id&ex_type=$ex_type&xslt_filename=$filename&format=$format&choose=$choose&o_mode=processor&filter=$filter&jump=$i_page";
    elseif ($ex_type != "")
        $export_link .= "export.php?range_id=$range_id&ex_type=$ex_type&xslt_filename=$filename&o_mode=choose&filter=$filter";
    else
        $export_link .= "export.php?range_id=$range_id&o_mode=start";
    $export_string = Studip\LinkButton::create(_('Export'), URLHelper::getURL($export_link));
    return $export_string;
}

?>
