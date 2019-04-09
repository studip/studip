<?
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Export-mainfile. Calls the submodules.
*
* This file checks the given parameters and calls the requested
* submodules for export in formats xml, rtf, html, pdf...
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export
* @package      Export
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de>
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

require '../lib/bootstrap.php';

ob_start();
page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);

$o_mode = Request::option('o_mode');
$xml_file_id = Request::option('xml_file_id',"");
$xslt_filename = Request::quoted('xslt_filename');
$page = Request::option('page');
$filter = Request::option('filter');
$ex_type = Request::quoted('ex_type');
$ex_sem = Request::option('ex_sem');
$format = Request::option('format');
$choose = Request::quoted('choose');
$range_id = Request::option('range_id');

$sidebar = Sidebar::Get();
$sidebar->setImage('sidebar/export-sidebar.png');

if (($o_mode != "direct") AND ($o_mode != "passthrough"))
{
    $perm->check("tutor");
    include ('lib/seminar_open.php'); // initialise Stud.IP-Session
}

require_once  'lib/export/export_config.inc.php';

PageLayout::setHelpKeyword("Basis.Export");
ob_start();
if (Config::get()->EXPORT_ENABLE)
{
    $ex_sem_class = Request::intArray('ex_sem_class');

    // Zurueckbutton benutzt?
    if (Request::submitted('back'))
    {
        if ($o_mode == "choose")
        {
            if ($page == 4)
            {
                if (get_config('skip_page_3'))
                    $page = 1;
                else
                    $page = 2;
            }
            elseif ($page>1)
                $page = $page-2;
            else
            {
                unset($xml_file_id);
                unset($page);
                $o_mode= "start";
            }
        }
    }

    if ( (empty($range_id) AND empty($xml_file_id) AND empty($o_mode) AND empty($ex_type)) OR ($o_mode == "start"))
    {
        include("lib/export/export_start.inc.php");
        $start_done = true;
    }

    if (($page==2) AND Config::get()->XSLT_ENABLE AND get_config('skip_page_3'))
        $page=3;

    //Exportmodul einbinden
    if (($page != 3) AND ($o_mode == "choose") AND ($export_error_num < 1))
    {
        include("lib/export/export_choose_xslt.inc.php");
        if ($export_error_num < 1)
            $xslt_choose_done = true;
    }

    if ( ($range_id != "") AND ($xml_file_id == "") AND ($o_mode != "start") AND (($o_mode != "choose") OR ($page == 3)))
    {
        include("lib/export/export_xml.inc.php");
        if ($export_error_num < 1)
            $xml_output_done = true;
    }

    if ( ($choose != "") AND ($format != "") AND ($format != "xml") AND (Config::get()->XSLT_ENABLE) AND ($export_error_num==0) AND
        ( ($o_mode == "processor") OR ($o_mode == "passthrough") OR ($page == 3) ) )
    {
        include("lib/export/export_run_xslt.inc.php");
        if ($export_error_num < 1)
            $xslt_process_done = true;
    }

    if (($export_error_num < 1) AND ($xslt_process_done) AND ($format == "fo"))
        include("lib/export/export_run_fop.inc.php");

    if (($export_error_num < 1) AND (!$start_done) AND ((!$xml_output_done) OR ($o_mode != "file")) AND (!$xslt_choose_done) AND (!$xslt_process_done))
    {
        $export_pagename = "Exportmodul - Fehler!";
        $export_error = _("Fehlerhafter Seitenaufruf");

    }

    include("lib/export/export_view.inc.php");
}
else
{
    PageLayout::postError(_("Das Exportmodul ist nicht eingebunden. Damit Daten im XML-Format exportiert werden können, muss das Exportmodul in den Systemeinstellungen freigeschaltet werden. 
    Wenden Sie sich bitte an die Administratoren."), [_("Exportmodul nicht eingebunden")]);

}
if (!in_array($o_mode, ['direct', 'passthrough'])) {
    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();


    echo $template->render();
    page_close();
}
