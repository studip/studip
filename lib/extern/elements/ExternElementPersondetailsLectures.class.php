<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementPersondetailsLectures.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementPersondetailsLectures
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementPersondetailsLectures.class.php
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

require_once 'lib/dates.inc.php';

class ExternElementPersondetailsLectures extends ExternElement {

    var $attributes = ["semstart", "semrange", "semswitch", "aliaswise",
            "aliassose", "aslist", "semclass"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "PersondetailsLectures";
        $this->real_name = _("Lehrveranstaltungen");
        $this->description = _("Angaben zur Ausgabe von Lehrveranstaltungen.");
    }

    /**
    *
    */
    function getDefaultConfig () {

        $config = [
            "semstart" => "",
            "semrange" => "",
            "semswitch" => "",
            "aliaswise" => _("Wintersemester"),
            "aliassose" => _("Sommersemester"),
            "aslist" => "1",
            'semclass' => '|1'

        ];

        return $config;
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        // get semester data
        $semester_data = SemesterData::getAllSemesterData();

        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben"));

        $title = _('Startsemester') . ':';
        $info = _("Geben Sie das erste anzuzeigende Semester an. Die Angaben \"vorheriges\", \"aktuelles\" und \"nächstes\" beziehen sich immer auf das laufende Semester und werden automatisch angepasst.");
        $current_sem = get_sem_num_sem_browse();
        if ($current_sem === FALSE) {
            $names = [_("keine Auswahl"), _("aktuelles"), _("nächstes")];
            $values = ["", "current", "next"];
        }
        else if ($current_sem === TRUE) {
            $names = [_("keine Auswahl"), _("vorheriges"), _("aktuelles")];
            $values = ["", "previous", "current"];
        }
        else {
            $names = [_("keine Auswahl"), _("vorheriges"), _("aktuelles"), "nächstes"];
            $values = ["", "previous", "current", "next"];
        }
        foreach ($semester_data as $sem_num => $sem) {
            $names[] = $sem["name"];
            $values[] = $sem_num + 1;
        }
        $table = $edit_form->editOptionGeneric("semstart", $title, $info, $values, $names);

        $title = _('Anzahl der anzuzeigenden Semester') . ':';
        $info = _("Geben Sie an, wieviele Semester (ab o.a. Startsemester) angezeigt werden sollen.");
        $names = [_("keine Auswahl")];
        $values = [""];
        $i = 1;
        foreach ($semester_data as $sem_num => $sem) {
            $names[] = $i++;
            $values[] = $sem_num + 1;
        }
        $table .= $edit_form->editOptionGeneric("semrange", $title, $info, $values, $names);

        $title = _('Umschalten des aktuellen Semesters') . ':';
        $info = _("Geben Sie an, wieviele Wochen vor Semesterende automatisch auf das nächste Semester umgeschaltet werden soll.");
        $names = [_("keine Auswahl"), _("am Semesterende"), _("1 Woche vor Semesterende")];
        for ($i = 2; $i < 13; $i++)
            $names[] = sprintf(_("%s Wochen vor Semesterende"), $i);
        $values = ["", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];
        $table .= $edit_form->editOptionGeneric("semswitch", $title, $info, $values, $names);

        $title = _('Bezeichnung Sommersemester') . ':';
        $info = _("Alternative Bezeichnung für den Begriff \"Sommersemester\".");
        $table .= $edit_form->editTextfieldGeneric("aliassose", $title, $info, 40, 80);

        $title = _('Bezeichnung Wintersemester') . ':';
        $info = _("Alternative Bezeichnung für den Begriff \"Wintersemester\".");
        $table .= $edit_form->editTextfieldGeneric("aliaswise", $title, $info, 40, 80);

        $title = _('Darstellungsart') . ':';
        $info = _("Wählen Sie zwischen Listendarstellung und reiner Textdarstellung.");
        $names = [_("Liste"), _("nur Text")];
        $values = ["1", "0"];
        $table .= $edit_form->editRadioGeneric("aslist", $title, $info, $values, $names);

        $title = _('Veranstaltungsklassen') . ':';
        $info = _("Wählen Sie aus, welche Veranstaltungsklassen angezeigt werden sollen.");
        foreach ($GLOBALS['SEM_CLASS'] as $key => $lecture_class) {
            $class_names[] = $lecture_class['name'];
            $class_values[] = $key;
        }
        $table .= $edit_form->editCheckboxGeneric("semclass", $title, $info, $class_values, $class_names);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == 'semclass') {
            if (!sizeof($_POST[$this->getName() . '_semclass'])) {
                return true;
            }
        }

        return FALSE;
    }

}

?>
