<?php
# Lifter002: TODO
# Lifter010: TODO
/**
* ExternElementMainTemplateLecturedetails.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainTemplateLecturedetails
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainLecturedetails.class.php
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


class ExternElementMainTemplateSemBrowse extends ExternElementMain {

    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
            'name', 'includeurl', 'grouping', 'semstart', 'semrange', 'semswitch', 'allseminars', 'rangepathlevel',
            'time', 'lecturer', 'semclasses', 'aliasesgrouping', 'nameformat',
            'language', 'genericdatafields', 'mode', 'countshowsublevels', 'startitem',
            'disableemptylevels', 'selectedeventtypes', 'resultorderby', 'maxnumberofhits', 'maxpagesresultbrowser'
        ];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
    }

    function getDefaultConfig () {
        $config = [
            'name' => '',
            'grouping' => '3',
            'semstart' => '',
    //      'semrange' => '',
            'semswitch' => '',
            'allseminars' => '',
            'rangepathlevel' => '1',
            'time' => '1',
            'lecturer' => '1',
            'semclasses' => '|1',
            "aliasesgrouping" => "|"._("Semester")."|"._("Bereich")."|"._("Lehrende")."|"
                    ._("Typ")."|"._("Einrichtung"),
            "nameformat" => '',
            "language" => '',
            'mode' => 'show_sem_range',
            'countshowsublevels' => '0',
            'startitem' => '',
            'disableemptylevels' => '',
            'selectedeventtypes' => '|all',
            'resultorderby' => 'VeranstaltungsNummer',
            'maxnumberofhits' => '10',
            'maxpagesresultbrowser' => ''
        ];

        get_default_generic_datafields($config, 'sem');

        return $config;
    }

    function toStringEdit ($post_vars = '', $faulty_values = '', $edit_form = '', $anker = '') {
        // get semester data
        $semester_data = SemesterData::getAllSemesterData();

        update_generic_datafields($this->config, $this->data_fields, $this->field_names, "sem");

        $out = '';
        $table = '';
        if ($edit_form == '') {
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
        }

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        if ($faulty_values == '') {
            $faulty_values = [];
        }

        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName('name');
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $content_table .= $this->getSRIFormContent($edit_form, true);

        $headline = $edit_form->editHeadline(_("Konfiguration des Moduls"));

        $title = _('Anzeigemodus') . ':';
        $info = _("Auswahl zwischen Einrichtungsbaum und Bereichsbaum");
        $values = ['show_sem_range', 'show_sem_range_tree'];
        $names = [_("Vorlesungsverzeichnis"), _("Einrichtungen")];
        $table = $edit_form->editRadioGeneric('mode', $title, $info, $values, $names);

        $title = _('Gruppierung') . ':';
        $info = _("Wählen Sie, wie die Veranstaltungen gruppiert werden sollen.");
        $values = ['0', '1', '2', '3', '4'];
        $names = [_("Semester"), _("Bereich"), _("Lehrende"),
                _("Typ"), _("Einrichtung")];
        $table .= $edit_form->editOptionGeneric('grouping', $title, $info, $values, $names);

        $title = _('Startsemester') . ':';
        $info = _("Geben Sie das erste anzuzeigende Semester an. Die Angaben \"vorheriges\", \"aktuelles\" und \"nächstes\" beziehen sich immer auf das laufende Semester und werden automatisch angepasst.");
        $current_sem = get_sem_num_sem_browse();
        if ($current_sem === FALSE) {
            $names = [_("keine Auswahl"), _("aktuelles"), _("nächstes")];
            $values = ['', 'current', 'next'];
        }
        else if ($current_sem === TRUE) {
            $names = [_("keine Auswahl"), _("vorheriges"), _("aktuelles")];
            $values = ['', 'previous', 'current'];
        }
        else {
            $names = [_("keine Auswahl"), _("vorheriges"), _("aktuelles"), "nächstes"];
            $values = ['', 'previous', 'current', 'next'];
        }
        foreach ($semester_data as $sem_num => $sem) {
            $names[] = $sem['name'];
            $values[] = $sem_num + 1;
        }
        $table .= $edit_form->editOptionGeneric("semstart", $title, $info, $values, $names);
        /*
        $title = _('Anzahl der anzuzeigenden Semester') . ':';
        $info = _("Geben Sie an, wieviele Semester (ab o.a. Startsemester) angezeigt werden sollen.");
        $names = array(_("keine Auswahl"));
        $values = array('');
        $i = 1;
        foreach ($semester_data as $sem_num => $sem) {
            $names[] = $i++;
            $values[] = $sem_num + 1;
        }
        $table .= $edit_form->editOptionGeneric('semrange', $title, $info, $values, $names);
        */
        $title = _('Umschalten des aktuellen Semesters') . ':';
        $info = _("Geben Sie an, wieviele Wochen vor Semesterende automatisch auf das nächste Semester umgeschaltet werden soll.");
        $names = [_("keine Auswahl"), _("am Semesterende"), _("1 Woche vor Semesterende")];
        for ($i = 2; $i < 13; $i++) {
            $names[] = sprintf(_("%s Wochen vor Semesterende"), $i);
        }
        $values = ['', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
        $table .= $edit_form->editOptionGeneric('semswitch', $title, $info, $values, $names);

        /*
        $title = _('Veranstaltungen beteiligter Institute anzeigen') . ':';
        $info = _("Wählen Sie diese Option, um Veranstaltungen anzuzeigen, bei denen diese Einrichtung als beteiligtes Institut eingetragen ist.");
        $values = '1';
        $names = '';
        $table .= $edit_form->editCheckboxGeneric('allseminars', $title, $info, $values, $names);
        */

        $title = _('Bereichspfad ab Ebene') . ':';
        $info = _("Wählen Sie, ab welcher Ebene der Bereichspfad ausgegeben werden soll.");
        $values = ['1', '2', '3', '4', '5', '6'];
        $names = ['1', '2', '3', '4', '5', '6'];
        $table .= $edit_form->editOptionGeneric('rangepathlevel', $title, $info, $values, $names);

        $title = _('Anzeige von Unterebenen') . ':';
        $info = _("Anzahl der Unterebenen des Baumes, die angezeigt werden sollen.");
        $values = ['0', '1', '2', '3', '4', '5', '6'];
        $names = ['0', '1', '2', '3', '4', '5', '6'];
        $table .= $edit_form->editOptionGeneric('countshowsublevels', $title, $info, $values, $names);
        $cid = Request::option('cid') ;
        if ($GLOBALS['perm']->have_perm('root') && $cid = 'studip') {
            $title = _('Start bei Root-Ebene') . ':';
            $info = _("Wird das Modul ohne weitere Parameter aufgerufen startet die Anzeige beim Root-Level (alle Fakultäten).");
            $table .= $edit_form->editCheckboxGeneric('startitem', $title, $info, 'root', '0');
        }

        $title = _('Leere Ebenen ausblenden') . ':';
        $info = _("Ebenen ohne Veranstaltungen und ohne Veranstaltungen in ihren Unterebenen werden nicht angezeigt.");
        $table .= $edit_form->editCheckboxGeneric('disableemptylevels', $title, $info, '1', '0');

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Konfiguration der Suche"));

        $title = _('Sortierung des Treffersets') . ':';
        $info = _("Nach welchem Tabellenfeld soll das Trefferset sortiert werden?");
        $values = ['VeranstaltungsNummer', 'Name'];
        $names = ['Veranstaltungsnummer', 'Name'];
        $table = $edit_form->editOptionGeneric('resultorderby', $title, $info, $values, $names);

        $title = _('Anzahl der Treffer bei Suche') . ':';
        $info = _("Maximale Anzahl der Veranstaltungen im Trefferset. Angabe 0, um alle anzuzeigen.");
        $table .= $edit_form->editTextfieldGeneric('maxnumberofhits', $title, $info, 3, 3);

        $title = _('Anzahl der Seiten im Result Browser') . ':';
        $info = _("Maximale Anzahl der Seiten, die der Result Browser anzeigen soll.");
        $table .= $edit_form->editTextfieldGeneric('maxpagesresultbrowser', $title, $info, 3, 3);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Ausgabe bestimmter Veranstaltungsklassen"));

        $names = [];
        $values = [];
        $info = _("Wählen Sie die anzuzeigenden Veranstaltungsklassen aus.");

        foreach ($GLOBALS['SEM_CLASS'] as $key => $class) {
            $values[] = $key;
            $names[] = $class['name'];
        }
        $table = $edit_form->editCheckboxGeneric('semclasses', $names, $info, $values, "");

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Textersetzungen"));

        $titles = [_("Semester"), _("Bereich"), _("Lehrende"), _("Typ"), _("Einrichtung")];
        $info = _("Geben Sie eine Bezeichnung für die entsprechende Gruppierungsart ein.");
        $table = $edit_form->editTextfieldGeneric('aliasesgrouping', $titles, $info, 40, 150);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Termine"));

        $title = _('Termintypen') . ':';
        $info = _("Wählen Sie aus, welche Termintypen angezeigt werden sollen.");
        $values = ['all', 'meeting', 'other', ''];
        $names = [_("alle Termine"), _("nur Sitzungstermine"), _("nur andere Termine"), '-----------'];
        foreach ($GLOBALS['TERMIN_TYP'] as $termin_key => $termin_typ) {
            $values[] = $termin_key;
            $names[] = $termin_typ['name'] . ($termin_typ['sitzung'] ? ' ('._("Sitzung").')' : '');
        }
        $table = $edit_form->editOptionGeneric('selectedeventtypes', $title, $info, $values, $names, 5, true);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $title = _('Namensformat') . ':';
        $info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
        $values = ["", "no_title_short", "no_title", "no_title_rev", "full", "full_rev"];
        $names = [_("keine Auswahl"), _("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
                _("Dr. Peter Meyer"), _("Meyer, Peter, Dr.")];
        $table = $edit_form->editOptionGeneric('nameformat', $title, $info, $values, $names);

        $title = _('Sprache') . ':';
        $info = _("Wählen Sie eine Sprache für die Datumsangaben aus.");
        $values = ["", "de_DE", "en_GB"];
        $names = [_("keine Auswahl"), _("Deutsch"), _("Englisch")];
        $table .= $edit_form->editOptionGeneric('language', $title, $info, $values, $names);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == 'mode') {
            if (!sizeof($_POST[$this->getName() . '_mode'])) {
                return true;
            }
        }
        if ($attribute == 'startitem') {
            if (!($GLOBALS['perm']->have_perm('root') && Request::option('cid') == 'studip')) {
                return false;
            }
            if (!isset($_POST[$this->name . '_' . $attribute])) {
                $_POST[$this->name . '_' . $attribute] = '';
                return FALSE;
            }
            return !($value == 'root' || $value == '');
        }
        if ($attribute == 'disableemptylevels') {
            if (!isset($_POST[$this->name . '_' . $attribute])) {
                $_POST[$this->name . '_' . $attribute] = '';
                return FALSE;
            }
            return !($value == '1' || $value == '');
        }
        if ($attribute == 'resultorderby') {
            $_POST[$this->getName() . '_resultorderby'] = (in_array($_POST[$this->getName() . '_resultorderby'], ['Name', 'VeranstaltungsNummer']) ? $_POST[$this->getName() . '_resultorderby'] : 'VeranstaltungsNummer');
        }
        if ($attribute == 'maxpagesresultbrowser') {
            $_POST[$this->getName() . '_maxpagesresultbrowser'] = intval($_POST[$this->getName() . '_maxpagesresultbrowser']);
        }
        if ($attribute == 'maxnumberofhits') {
            $_POST[$this->getName() . '_maxnumberofhits'] = intval($_POST[$this->getName() . '_maxnumberofhits']);
        }
        return false;
    }

}
