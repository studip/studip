<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
lit_overview_print_view.php
Copyright (C) 2004 André Noack <noack@data-quest.de>
Suchi & Berg GmbH <info@data-quest.de>
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require '../lib/bootstrap.php';
ob_start();
page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);
$perm->check('admin');

include 'lib/seminar_open.php'; // initialise Stud.IP-Session

$element = new StudipLitCatElement();
if(Request::quotedArray('_lit_data'))
     $_SESSION['_lit_data'] = Request::quotedArray('_lit_data');

$header = get_object_name($_inst_id, 'inst');
?>
<h1>
<?=htmlReady(sprintf(_("Literaturliste %s"), $header['type'] . ": " . $header['name']))?>
</h1>
<?
if (is_array($_SESSION['_lit_data'])){
    foreach ($_SESSION['_lit_data'] as $cid => $data){
        $element->setValues($data);
        if ($element->getValue('catalog_id')){
            $titel = htmlReady($element->getShortName());
            echo "\n<table width=\"99%\" cellpadding=\"0\" cellspacing=\"4\" border=\"0\" align=\"center\"><tr>";
            echo '<td><b>' . $titel . '</b></td>';
            echo "\n</tr></table>";
            $content = "";
            $estimated_p = 0;
            $participants = 0;
            echo "\n<table width=\"97%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">";
            $content .= "<b>" . _("Titel:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("dc_title"),true,true) . "<br>";
            $content .= "<b>" . _("Autor; weitere Beteiligte:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("authors"),true,true) . "<br>";
            $content .= "<b>" . _("Erschienen:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("published"),true,true) . "<br>";
            $content .= "<b>" . _("Identifikation:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("dc_identifier")) . "<br>";

            $content .= "<b>" . _("Veranstaltungen:") . "</b>&nbsp;&nbsp;";
            foreach ($_SESSION['_lit_data'][$cid]['sem_data'] as $sem_data){
                $content .= htmlReady(my_substr($sem_data["Name"],0,50)) .', ';
                $estimated_p += $sem_data['admission_turnout'];
                $participants += $sem_data['participants'];
            }
            $content = mb_substr($content,0,-2);
            $content .= "<br>";
            $content .= "<b>" . _("Dozenten:") . "</b>&nbsp;&nbsp;";
            foreach ($_SESSION['_lit_data'][$cid]['doz_data'] as $doz_data){
                $content .= htmlReady($doz_data["Nachname"]) . ", ";
            }
            $content = mb_substr($content,0,-2);
            $content .= "<br>";
            $content .= "<b>" . _("Teilnehmendenanzahl (erwartet/angemeldet):") . "</b>&nbsp;&nbsp;";
            $content .= ($estimated_p ? $estimated_p : _("unbekannt"));
            $content .= ' / ' . (int)$participants;
            $content .= "<br>";
            if (is_array($_SESSION['_lit_data'][$cid]['check_accession'])){
                $content .= "<div style=\"margin-top: 10px;border: 1px solid black;padding: 5px; width:96%;\"<b>" ._("Verfügbarkeit in externen Katalogen:") . "</b><br>";
                foreach ( $_SESSION['_lit_data'][$cid]['check_accession'] as $plugin_name => $ret){
                    $content .= "<b>&nbsp;{$plugin_name}&nbsp;</b>";
                    if ($ret['found']){
                        $content .= _("gefunden") . "&nbsp;";

                    } elseif (count($ret['error'])){
                        $content .= '<span style="color:red;">' . htmlReady($ret['error'][0]['msg']) . '</span>';
                    } else {
                        $content .= _("<u>nicht</u> gefunden") . "&nbsp;";
                    }
                    $content .= "<br>";
                }
                $content .= "</div>";
            }
            echo '<tr><td style="font-size:75%;">' . $content . '</td></tr>';
            echo "\n</table><br>";
        }
    }
}

PageLayout::removeStylesheet('studip-base.css');
PageLayout::addStylesheet('print.css'); // use special stylesheet for printing
$layout = $GLOBALS['template_factory']->open('layouts/base');

$layout->content_for_layout = ob_get_clean();

echo $layout->render();
page_close();
?>
