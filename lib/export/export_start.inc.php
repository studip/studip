<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* Export-Subfile that contains the first page of the export-module.
*
* This file shows the first page of the export-module where the type of export and the institute can be chosen.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_start
* @package      Export
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_run_xslt.inc.php
// Integration of xslt-processor
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

use Studip\Button, Studip\LinkButton;

require_once ("lib/export/export_xslt_vars.inc.php");   // XSLT-Variablen

$semester = new SemesterData;

$export_pagename = _("Datenexport - Startseite");

$export_info = null;

$export_pagecontent .= "<form class=\"default\" method=\"POST\" action=\"" . URLHelper::getURL() . "\">";

$export_pagecontent .= CSRFProtection::tokenTag();
$export_pagecontent .= "<fieldset><legend>"._("Bitte wählen Sie Datenart und Einrichtung.")."</legend>";
$export_pagecontent .="<label>". _("Bitte wählen Sie eine Einrichtung: ") .  "<br><select name=\"range_id\" class=\"nested-select\">";

// Prepare institutes statement for faculty
$query = "SELECT Institut_id, Name
          FROM Institute
          WHERE fakultaets_id = ? AND institut_id != fakultaets_id
          ORDER BY Name";
$inst_statement = DBManager::get()->prepare($query);

// Prepare and execute faculties statement
$query = "SELECT Institut_id, Name, fakultaets_id
          FROM Institute
          WHERE fakultaets_id = Institut_id
          ORDER BY Name";
$statement = DBManager::get()->query($query);
$faculties = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($faculties as $faculty) {
    $export_pagecontent .= "<option class=\"nested-item-header\" ";

    if ($range_id == $faculty['fakultaets_id']) {
        $export_pagecontent .= " selected";
    }

    $export_pagecontent .= " value=\"" . $faculty['Institut_id'] . "\">" . htmlReady(my_substr($faculty['Name'], 0, 60)) . "</option>";

    $inst_statement->execute([$faculty['Institut_id']]);
    while ($institute = $inst_statement->fetch(PDO::FETCH_ASSOC)) {
        $export_pagecontent .= sprintf("<option value=\"%s\" class=\"nested-item\"", $institute['Institut_id']);
        if ($range_id == $institute['Institut_id'] && $range_id != $faculty['Institut_id']) {
            $export_pagecontent .= " selected";
        }
        $export_pagecontent .= sprintf(">%s </option>\n", htmlReady(my_substr($institute['Name'], 0, 60)));
    }
    $inst_statement->closeCursor();
}

if ($perm->have_perm("root")) {
    $export_pagecontent .= "<option class=\"nested-item-header\" value=\"root\">Alle Einrichtungen";
}

$export_pagecontent .= "</select></label>";

$export_pagecontent .= "<label>"._("Art der auszugebenden Daten: ") .  "<select name=\"ex_type\">";

$export_pagecontent .= "<option";
if ($ex_type=="veranstaltung")
    $export_pagecontent .= " selected";
$export_pagecontent .= " value=\"veranstaltung\">" . _("Veranstaltungsdaten") .  "</option>";

$export_pagecontent .= "<option";
if ($ex_type=="person") {
    $export_pagecontent .= " selected";
}
$export_pagecontent .= " value=\"person\">" . _("MitarbeiterInnendaten") .  "</option>";

$export_pagecontent .= "</select></label>";

$export_pagecontent .="<label>". _("Aus welchem Semester sollen die Daten exportiert werden (für Veranstaltungsexport): ");
if (!isset($ex_sem)) {
    $ex_sem = (Semester::findCurrent() ? Semester::findCurrent()->getId() : null);
}
$export_pagecontent .= SemesterData::GetSemesterSelector(['name' => 'ex_sem'], $ex_sem, 'semester_id', true);
$export_pagecontent .="</label>";

$export_pagecontent .="<label>". _("Welche Arten von Veranstaltungen sollen exportiert werden? ") . "<br>";

if (!count($ex_sem_class)) {
    $ex_sem_class[1] = 1;
}

foreach (SeminarCategories::getAll() as $sem_class) {
    if(!$sem_class->studygroup_mode){
        $export_pagecontent .= "<input type=\"checkbox\" name=\"ex_sem_class[$sem_class->id]\" value=\"1\"";
        if (isset($ex_sem_class[$sem_class->id])) $export_pagecontent .= " checked";
        $export_pagecontent .= ">&nbsp;" . htmlready($sem_class->name) . "&nbsp;&nbsp;";
    }
}
$export_pagecontent .= "</label>";

$export_pagecontent .= "<input type=\"hidden\" name=\"o_mode\" value=\"choose\">";
$export_pagecontent .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"" . htmlReady($xslt_filename) . "\">";
$export_pagecontent .= "<input type=\"hidden\" name=\"choose\" value=\"" . htmlReady($choose) . "\">";
$export_pagecontent .= "<input type=\"hidden\" name=\"format\" value=\"" . htmlReady($format) . "\">";
$export_pagecontent .= "</fieldset>";

$export_weiter_button = "<footer>" . Button::create(_('Weiter') . ' >>' ) . "</footer></form>";
