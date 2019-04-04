<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleTemplateLectures.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplateLectures
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplateLectures.class.php
//
// Copyright (C) 2007 Peter Thienel <thienel@data-quest.de>,
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
require_once 'lib/dates.inc.php';


class ExternModuleTemplateLectures extends ExternModule {

    var $markers = array();
    var $args = array('seminar_id');

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        $this->data_fields = array('VeranstaltungsNummer', 'Name', 'Untertitel', 'status', 'Ort',
            'art', 'zeiten', 'dozent');
        $this->registered_elements = array(
                'ReplaceTextSemType',
                'SelectSubjectAreas',
                'LinkInternLecturedetails' => 'LinkInternTemplate',
                'LinkInternPersondetails' => 'LinkInternTemplate',
                'TemplateGeneric'
        );
        $this->field_names = array
        (
                _("Veranstaltungsnummer"),
                _("Name"),
                _("Untertitel"),
                _("Status"),
                _("Ort"),
                _("Art"),
                _("Zeiten"),
                _("Lehrende")
        );

        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    function setup () {
        // extend $data_fields if generic datafields are set
    //  $config_datafields = $this->config->getValue("Main", "genericdatafields");
    //  $this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);

        // setup module properties
    //  $this->elements["LinkIntern"]->link_module_type = 2;
    //  $this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");

        $this->elements['LinkInternLecturedetails']->real_name = _("Link zum Modul Veranstaltungsdetails");
        $this->elements['LinkInternLecturedetails']->link_module_type = array(4, 13);
        $this->elements['LinkInternPersondetails']->real_name = _("Verlinkung zum Modul MitarbeiterInnendetails");
        $this->elements['LinkInternPersondetails']->link_module_type = array(2, 14);
        $this->elements['TemplateGeneric']->real_name = _("Template");
        $this->elements['TemplateGeneric']->link_module_type = 2;

    }

    function toStringEdit ($open_elements = '', $post_vars = '',
            $faulty_values = '', $anker = '') {

        $this->updateGenericDatafields('TemplateGeneric', 'sem');
        $this->elements['TemplateGeneric']->markers = $this->getMarkerDescription('TemplateGeneric');

        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }

    function getMarkerDescription ($element_name) {
        $markers['TemplateGeneric'][] = array('__GLOBAL__', _("Globale Variablen (gültig im gesamten Template)."));
        $markers['TemplateGeneric'][] = array('###LECTURES-COUNT###', '');
        $markers['TemplateGeneric'][] = array('###LECTURES-SUBSTITUTE-GROUPED-BY###', '');
        $markers['TemplateGeneric'][] = array('###START_SEMESTER###', _('Name des Startsemesters'));

        $markers['TemplateGeneric'][] = array('<!-- BEGIN LECTURES -->', '');

        $markers['TemplateGeneric'][] = array('<!-- BEGIN NO-LECTURES -->', '');
        $markers['TemplateGeneric'][] = array('###NO-LECTURES-TEXT###', '');
        $markers['TemplateGeneric'][] = array('<!-- END NO-LECTURES -->', '');

        $markers['TemplateGeneric'][] = array('<!-- BEGIN GROUP -->', '');
        $markers['TemplateGeneric'][] = array('###GROUP###', '');
        $markers['TemplateGeneric'][] = array('###GROUP-NO###', '');

        $markers['TemplateGeneric'][] = array('<!-- BEGIN LECTURE -->', '');
        $markers['TemplateGeneric'][] = array('###TITLE###', '');
        $markers['TemplateGeneric'][] = array('###LECTUREDETAILS-HREF###', '');
        $markers['TemplateGeneric'][] = array('###SUBTITLE###', '');
        $markers['TemplateGeneric'][] = array('###NUMBER###', _("Die Veranstaltungsnummer"));

        $markers['TemplateGeneric'][] = array('<!-- BEGIN LECTURERS -->', '');
        $markers['TemplateGeneric'][] = array('###FULLNAME###', '');
        $markers['TemplateGeneric'][] = array('###LASTNAME###', '');
        $markers['TemplateGeneric'][] = array('###FIRSTNAME###', '');
        $markers['TemplateGeneric'][] = array('###TITLEFRONT###', '');
        $markers['TemplateGeneric'][] = array('###TITLEREAR###', '');
        $markers['TemplateGeneric'][] = array('###PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('###LECTURER-NO###', '');
        $markers['TemplateGeneric'][] = array('###UNAME###', _("Stud.IP-Username"));
        $markers['TemplateGeneric'][] = array('<!-- END LECTURERS -->', '');

        $markers['TemplateGeneric'][] = array('###ROOM###', '');
        $markers['TemplateGeneric'][] = array('###FORM###', _("Die Veranstaltungsart"));
        $markers['TemplateGeneric'][] = array('###SEMTYPE###', '');
        $markers['TemplateGeneric'][] = array('###SEMTYPE-SUBSTITUTE###', '');
        $markers['TemplateGeneric'][] = array('###SEMESTER###', '');
        $markers['TemplateGeneric'][] = array('###CYCLE###', '');
        $this->insertDatafieldMarkers('sem', $markers, 'TemplateGeneric');

        $markers['TemplateGeneric'][] = array('<!-- END LECTURE -->', '');
        $markers['TemplateGeneric'][] = array('<!-- END GROUP -->', '');
        $markers['TemplateGeneric'][] = array('<!-- END LECTURES -->', '');

        return $markers[$element_name];
    }

    function getContent ($args = NULL, $raw = FALSE) {

        $start_item_id = get_start_item_id($this->config->range_id);
        $browser = new ExternSemBrowseTemplate($this, $start_item_id);

        return $browser->getContent();
    }

    function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        ob_start();
        echo $this->elements['TemplateGeneric']->toString(array('content' => $this->getContent(), 'subpart' => 'LECTURES'));
        ob_end_flush();

    }

    function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateGeneric']->toString(array('content' => $this->getContent(), 'subpart' => 'LECTURES', 'hide_markers' => FALSE));

    }

}



class ExternSemBrowseTemplate extends SemBrowse {

    var $module;
    var $sem_types_position;

    function __construct(&$module, $start_item_id) {

        global $SEM_TYPE,$SEM_CLASS;
        $all_semester = SemesterData::getAllSemesterData();
        array_unshift($all_semester, 0);

        $this->group_by_fields = array( array('name' => _("Semester"), 'group_field' => 'sem_number'),
                                        array('name' => _("Bereich"), 'group_field' => 'bereich'),
                                        array('name' => _("Lehrende"), 'group_field' => 'fullname', 'unique_field' => 'username'),
                                        array('name' => _("Typ"), 'group_field' => 'status'),
                                        array('name' => _("Einrichtung"), 'group_field' => 'Institut', 'unique_field' => 'Institut_id'));

        $this->module = $module;
        $this->sem_browse_data["group_by"] = $this->module->config->getValue("Main", "grouping");
        $this->sem_dates = $all_semester;
        $this->sem_dates[0] = array("name" => sprintf(_("vor dem %s"),$this->sem_dates[1]['name']));

        // reorganize the $SEM_TYPE-array
        foreach ($SEM_CLASS as $key_class => $class) {
            $i = 0;
            foreach ($SEM_TYPE as $key_type => $type) {
                if ($type["class"] == $key_class) {
                    $i++;
                    $this->sem_types_position[$key_type] = $i;
                }
            }
        }

        $switch_time = mktime(0, 0, 0, date("m"),
                date("d") + 7 * $this->module->config->getValue("Main", "semswitch"), date("Y"));
        // get current semester
        $current_sem = get_sem_num($switch_time) + 1;

        switch ($this->module->config->getValue("Main", "semstart")) {
            case "previous" :
                if (isset($all_semester[$current_sem - 1])) {
                    $current_sem--;
                }
                break;
            case "next" :
                if (isset($all_semester[$current_sem + 1])) {
                    $current_sem++;
                }
                break;
            case "current" :
                break;
            default :
                if (isset($all_semester[$this->module->config->getValue('Main', 'semstart')])) {
                    $current_sem = $this->module->config->getValue('Main', 'semstart');
                }
        }

        $last_sem = $current_sem + $this->module->config->getValue('Main', 'semrange');
        if ($last_sem < $current_sem)
            $last_sem = $current_sem;
        if (!isset($all_semester[$last_sem]))
            $last_sem = sizeof($all_semester);

        for (;$last_sem > $current_sem; $last_sem--)
            $this->sem_number[] = $last_sem - 1;

        $this->start_sem = $current_sem;

        $semclasses = $this->module->config->getValue('Main', 'semclasses');
        foreach ($SEM_TYPE as $key => $type) {
            if (in_array($type['class'], (array) $semclasses))
                $this->sem_browse_data['sem_status'][] = $key;
        }

        $this->get_sem_range_tree($start_item_id, true);
    }

    function getContent () {
        global $SEM_TYPE, $SEM_CLASS, $sem_type_tmp;

        if (is_array($this->sem_browse_data['search_result']) && count($this->sem_browse_data['search_result'])) {

            // show only selected subject areas
            $selected_ranges = (array) $this->module->config->getValue('SelectSubjectAreas', 'subjectareasselected');
            $selected_ranges[] = $this->sem_browse_data['start_item_id'];
            if (!$this->module->config->getValue('SelectSubjectAreas', 'selectallsubjectareas')
                    && count($selected_ranges)) {
                if ($this->module->config->getValue('SelectSubjectAreas', 'reverseselection')) {
                    $sem_range_query =  "AND seminar_sem_tree.sem_tree_id NOT IN ('".implode("','", $selected_ranges)."')";
                } else {
                    $sem_range_query =  "AND seminar_sem_tree.sem_tree_id IN ('".implode("','", $selected_ranges)."')";
                }
            } else {
                $sem_range_query = '';
            }

            // show only selected SemTypes
            $selected_semtypes = $this->module->config->getValue('ReplaceTextSemType', 'visibility');
            $sem_types_array = array();
            if (count($selected_semtypes)) {
                for ($i = 0; $i < count($selected_semtypes); $i++) {
                    if ($selected_semtypes[$i] == '1') {
                        $sem_types_array[] = $i + 1;
                    }
                }
                $sem_types_query = "AND seminare.status IN ('" . implode("','", $sem_types_array) . "')";
            } else {
                $sem_types_query = '';
            }

            // number of visible columns
            $group_colspan = array_count_values((array) $this->module->config->getValue("Main", "visible"));

            if ($this->sem_browse_data['group_by'] == 1){
                if (!is_object($this->sem_tree)){
                    $the_tree = TreeAbstract::GetInstance("StudipSemTree");
                } else {
                    $the_tree =& $this->sem_tree->tree;
                }
            $the_tree->buildIndex();
            }

            if (!$this->module->config->getValue("Main", "allseminars")){
                $sem_inst_query = " AND seminare.Institut_id='{$this->module->config->range_id}' ";
            }
            if (!$nameformat = $this->module->config->getValue("Main", "nameformat"))
                $nameformat = "no_title_short";

            $dbv = DbView::getView('sem_tree');

            $query = "SELECT seminare.Seminar_id, seminare.Name, seminare.Untertitel, seminare.VeranstaltungsNummer, seminare.status, seminare.art
                , Institute.Name AS Institut,Institute.Institut_id,
                seminar_sem_tree.sem_tree_id AS bereich, " . $GLOBALS['_fullname_sql'][$nameformat] ." AS fullname, auth_user_md5.username, Vorname, Nachname, title_front, title_rear,
                " . $dbv->sem_number_sql . " AS sem_number, " . $dbv->sem_number_end_sql . " AS sem_number_end,
                seminar_user.position AS position FROM seminare
                LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent')
                LEFT JOIN auth_user_md5 USING (user_id)
                LEFT JOIN user_info USING (user_id)
                LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id)
                LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id)
                LEFT JOIN Institute ON (seminar_inst.institut_id = Institute.Institut_id)
                WHERE seminare.Seminar_id IN('" . join("','", array_keys($this->sem_browse_data['search_result']))
                 . "') $sem_inst_query $sem_range_query $sem_types_query";

            $db = new DB_Seminar($query);
            $snap = new DbSnapshot($db);
            $group_field = $this->group_by_fields[$this->sem_browse_data['group_by']]['group_field'];
            $data_fields[0] = "Seminar_id";
            if ($this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field']){
                $data_fields[1] = $this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field'];
            }
            $group_by_data = $snap->getGroupedResult($group_field, $data_fields);
            $sem_data = $snap->getGroupedResult("Seminar_id");
            if ($this->sem_browse_data['group_by'] == 0){
                $group_by_duration = $snap->getGroupedResult("sem_number_end", array("sem_number","Seminar_id"));
                foreach ($group_by_duration as $sem_number_end => $detail){
                    if ($sem_number_end != -1 && ($detail['sem_number'][$sem_number_end - 1] && count($detail['sem_number']) == 1)){
                        continue;
                    } else {
                        foreach ($detail['Seminar_id'] as $seminar_id => $foo){
                            $start_sem = key($sem_data[$seminar_id]["sem_number"]);
                            if ($sem_number_end == -1){
                                if (is_array($this->sem_number)){
                                    $sem_number_end = $this->sem_number[0];
                                } else {
                                    $sem_number_end = count($this->sem_dates)-1;
                                }
                            }
                            for ($i = $start_sem; $i <= $sem_number_end; ++$i){
                                if ($this->sem_number === false || (is_array($this->sem_number) && in_array($i,$this->sem_number))){
                                    if ($group_by_data[$i] && !$tmp_group_by_data[$i]){
                                        foreach($group_by_data[$i]['Seminar_id'] as $id => $bar){
                                            $tmp_group_by_data[$i]['Seminar_id'][$id] = true;
                                        }
                                    }
                                    $tmp_group_by_data[$i]['Seminar_id'][$seminar_id] = true;
                                }
                            }
                        }
                    }
                }
                if (is_array($tmp_group_by_data)){
                    if ($this->sem_number !== false){
                        unset($group_by_data);
                    }
                    foreach ($tmp_group_by_data as $start_sem => $detail){
                        $group_by_data[$start_sem] = $detail;
                    }
                }
            }

            foreach ($group_by_data as $group_field => $sem_ids){
                foreach ($sem_ids['Seminar_id'] as $seminar_id => $foo){
                    $name = mb_strtolower(key($sem_data[$seminar_id]["Name"]));
                    $name = str_replace("ä","ae",$name);
                    $name = str_replace("ö","oe",$name);
                    $name = str_replace("ü","ue",$name);
                    $group_by_data[$group_field]['Seminar_id'][$seminar_id] = $name;
                }
                uasort($group_by_data[$group_field]['Seminar_id'], 'strnatcmp');
            }

            switch ($this->sem_browse_data["group_by"]){
                case 0:
                    krsort($group_by_data, SORT_NUMERIC);
                    break;

                case 1:
                    uksort($group_by_data, function ($a, $b) {
                        $the_tree = TreeAbstract::GetInstance('StudipSemTree', false);
                        return $the_tree->tree_data[$a]['index'] - $the_tree->tree_data[$b]['index'];
                    });
                    break;

                case 3:
                    if ($order = $this->module->config->getValue("ReplaceTextSemType", "order")) {
                        foreach ((array) $order as $position) {
                            if (isset($group_by_data[$position]))
                                $group_by_data_tmp[$position] = $group_by_data[$position];
                        }
                        $group_by_data = $group_by_data_tmp;
                        unset($group_by_data_tmp);
                    }
                    else {
                        uksort($group_by_data, function ($a, $b) {
                            global $SEM_CLASS,$SEM_TYPE;
                            return strnatcasecmp(
                                $SEM_TYPE[$a]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$a]['class']]['name'] . ')',
                                $SEM_TYPE[$b]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$b]['class']]['name'] . ')'
                            );
                        });
                    }
                    break;

                default:
                    uksort($group_by_data, 'strnatcasecmp');
                    break;

            }

            // generic datafields
            $generic_datafields = $this->module->config->getValue("Main", "genericdatafields");

            $content['__GLOBAL__']['LECTURES-COUNT'] = count($sem_data);
            $group_by_name = $this->module->config->getValue("Main", "aliasesgrouping");
            $content['__GLOBAL__']['LECTURES-SUBSTITUTE-GROUPED-BY'] = $group_by_name[$this->sem_browse_data['group_by']];
            $content['__GLOBAL__']['START_SEMESTER'] = ExternModule::ExtHtmlReady($this->sem_dates[$this->start_sem]['name']);

            $i = 0;
            foreach ((array) $group_by_data as $group_field => $sem_ids) {
                $content['LECTURES']['GROUP'][$i]['GROUP'] = $this->getGroupContent($the_tree, $group_field);
                $content['LECTURES']['GROUP'][$i]['GROUP-NO'] = $i + 1;

                if (is_array($sem_ids['Seminar_id'])) {
                    $zebra = 0;
                    $j = 0;
                    while (list($seminar_id,) = each($sem_ids['Seminar_id'])) {

                //      $sem_name = key($sem_data[$seminar_id]["Name"]);

                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['TITLE'] = ExternModule::ExtHtmlReady(key($sem_data[$seminar_id]['Name']));

                        $sem_number_start = key($sem_data[$seminar_id]['sem_number']);
                        $sem_number_end = key($sem_data[$seminar_id]['sem_number_end']);
                        $sem_semester = $this->sem_dates[$sem_number_start]['name'];
                        if ($sem_number_start != $sem_number_end){
                            $sem_semester .= ' - ' . ($sem_number_end == -1 ? _("unbegrenzt") : $this->sem_dates[$sem_number_end]['name']);
                        }

                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['SEMESTER'] = $sem_semester;

                        // create turnus field
                        $sem_turnus = Seminar::getInstance($seminar_id)->getDatesExport(array('show_room' => true));

                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['CYCLE'] = ExternModule::ExtHtmlReady($sem_turnus);

                        $doz_name = array_keys($sem_data[$seminar_id]['fullname']);
                        $doz_lastname = array_keys($sem_data[$seminar_id]['Nachname']);
                        $doz_firstname = array_keys($sem_data[$seminar_id]['Vorname']);
                        $doz_titlefront = array_keys($sem_data[$seminar_id]['title_front']);
                        $doz_titlerear = array_keys($sem_data[$seminar_id]['title_rear']);
                        $doz_uname = array_keys($sem_data[$seminar_id]['username']);
                        $doz_position = array_keys($sem_data[$seminar_id]['position']);
                        if (sizeof($doz_position) < $doz_name) $doz_position = array_fill(0, sizeof($doz_name), 0);
                        if (is_array($doz_name)){
                            array_multisort($doz_position, $doz_name, $doz_uname);
                            $k = 0;
                            foreach ($doz_name as $index => $value) {
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['PERSONDETAIL-HREF'] = $this->module->elements['LinkInternPersondetails']->createUrl(array('link_args' => 'username=' . $doz_uname[$index]));
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['FULLNAME'] = ExternModule::ExtHtmlReady($doz_name[$index]);
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['LASTNAME'] = ExternModule::ExtHtmlReady($doz_lastname[$index]);
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['FIRSTNAME'] = ExternModule::ExtHtmlReady($doz_firstname[$index]);
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['TITLEFRONT'] = ExternModule::ExtHtmlReady($doz_titlefront[$index]);
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['TITLEREAR'] = ExternModule::ExtHtmlReady($doz_titlerear[$index]);
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['UNAME'] = $doz_uname[$index];
                                $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTURERS'][$k]['LECTURER-NO'] = $k + 1;
                                $k++;
                            }
                        }

                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['LECTUREDETAILS-HREF'] = $this->module->elements['LinkInternLecturedetails']->createUrl(array('link_args' => 'seminar_id=' . $seminar_id));
                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['NUMBER'] = ExternModule::ExtHtmlReady(key($sem_data[$seminar_id]['VeranstaltungsNummer']));
                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['SUBTITLE'] = ExternModule::ExtHtmlReady(key($sem_data[$seminar_id]['Untertitel']));
                        $aliases_sem_type = $this->module->config->getValue('ReplaceTextSemType', 'class_' . $SEM_TYPE[key($sem_data[$seminar_id]['status'])]['class']);
                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['SEMTYPE-SUBSTITUTE'] = $aliases_sem_type[$this->sem_types_position[key($sem_data[$seminar_id]['status'])] - 1];
                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['SEMTYPE'] = ExternModule::ExtHtmlReady($SEM_TYPE[key($sem_data[$seminar_id]['status'])]['name']
                                    .' ('. $SEM_CLASS[$SEM_TYPE[key($sem_data[$seminar_id]['status'])]['class']]['name'] . ')');
                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['ROOM'] = ExternModule::ExtHtmlReady(Seminar::getInstance($seminar_id)->getDatesTemplate('dates/seminar_export_location'));
                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['FORM'] = ExternModule::ExtHtmlReady(key($sem_data[$seminar_id]['art']));

                        // generic data fields
                        if (is_array($generic_datafields)) {
                            $localEntries = DataFieldEntry::getDataFieldEntries($seminar_id, 'sem');
                            #$datafields = $datafields_obj->getLocalFields($seminar_id);
                            $l = 1;
                            foreach ($generic_datafields as $datafield) {
                                if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
                                    $localEntry = $localEntries[$datafield]->getDisplayValue();
                                    if ($localEntry) {
                                        $content['LECTURES']['GROUP'][$i]['LECTURE'][$j]['DATAFIELD_' . $l] = $localEntry;
                                    }
                                }
                                $l++;
                            }

                        }

                        $j++;
                    }
                }
                $i++;
            }
        } else {
            $content['__GLOBAL__']['LECTURES-COUNT'] = 0;
            $group_by_name = $this->module->config->getValue('Main', 'aliasesgrouping');
            $content['__GLOBAL__']['LECTURES-SUBSTITUTE-GROUPED-BY'] = $group_by_name[$this->sem_browse_data['group_by']];
            $content['LECTURES']['NO-LECTURES']['NO-LECTURES-TEXT'] = ExternModule::ExtHtmlReady($this->module->config->getValue('Main', 'nodatatext'));
        }
        return $content;

    }

    // private
    function getGroupContent ($the_tree, $group_field) {
        global $SEM_TYPE, $SEM_CLASS;

        switch ($this->sem_browse_data['group_by']){
            case 0:
                $content = $this->sem_dates[$group_field]['name'];
                break;

            case 1:
                if ($the_tree->tree_data[$group_field]) {
                    $range_path_level = $this->module->config->getValue('Main', 'rangepathlevel');
                    $content = htmlReady($the_tree->getShortPath($group_field, NULL, '>', $range_path_level ? $range_path_level - 1 : 0));
                } else {
                    $content = $this->module->config->getValue('Main', 'textnogroups');
                }
                break;

            case 2:
                $content = htmlReady($group_field);
                break;

            case 3:
                $aliases_sem_type = $this->module->config->getValue('ReplaceTextSemType', "class_{$SEM_TYPE[$group_field]['class']}");
                if ($aliases_sem_type[$this->sem_types_position[$group_field] - 1]) {
                    $content = $aliases_sem_type[$this->sem_types_position[$group_field] - 1];
                } else {
                    $content = htmlReady($GLOBALS['SEM_TYPE'][$group_field]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$group_field]['class']]['name'] . ')');
                }
                break;
            case 4:
                $content = htmlReady($group_field);
                break;

        }

        return $content;
    }

}
?>
