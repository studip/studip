<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternSemBrowseTable.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternSemBrowseTable
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternSemBrowseTable.class.php
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

class ExternSemBrowseTable extends SemBrowse {

    var $module;
    var $sem_types_position;

    function __construct(&$module, $start_item_id) {

        global $SEM_TYPE,$SEM_CLASS;
        ob_start();
        $all_semester = SemesterData::getAllSemesterData();
        array_unshift($all_semester, 0);

        $this->group_by_fields = [ ['name' => _("Semester"), 'group_field' => 'sem_number'],
                                        ['name' => _("Bereich"), 'group_field' => 'bereich'],
                                        ['name' => _("Lehrende"), 'group_field' => 'fullname', 'unique_field' => 'username'],
                                        ['name' => _("Typ"), 'group_field' => 'status'],
                                        ['name' => _("Einrichtung"), 'group_field' => 'Institut', 'unique_field' => 'Institut_id']];

        $this->module = $module;
        $this->sem_browse_data["group_by"] = $this->module->config->getValue("Main", "grouping");
        $this->sem_dates = $all_semester;
        $this->sem_dates[0] = ["name" => sprintf(_("vor dem %s"),$this->sem_dates[1]['name'])];

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
                if (isset($all_semester[$current_sem - 1]))
                    $current_sem--;
                break;
            case "next" :
                if (isset($all_semester[$current_sem + 1]))
                    $current_sem++;
                break;
            case "current" :
                break;
            default :
                if (isset($all_semester[$this->module->config->getValue("Main", "semstart")]))
                    $current_sem = $this->module->config->getValue("Main", "semstart");
        }

        $last_sem = $current_sem + $this->module->config->getValue("Main", "semrange");
        if ($last_sem < $current_sem)
            $last_sem = $current_sem;
        if (!isset($all_semester[$last_sem]))
            $last_sem = sizeof($all_semester);

        for (;$last_sem > $current_sem; $last_sem--)
            $this->sem_number[] = $last_sem - 1;

        $semclasses = $this->module->config->getValue("Main", "semclasses");
        foreach ($SEM_TYPE as $key => $type) {
            if (in_array($type["class"], $semclasses))
                $this->sem_browse_data['sem_status'][] = $key;
        }

        $this->get_sem_range_tree($start_item_id, true);
    }

    function print_result () {
        global $_fullname_sql,$SEM_TYPE,$SEM_CLASS,$sem_type_tmp;

        $sem_link = $this->module->getModuleLink("Lecturedetails",
            $this->module->config->getValue("SemLink", "config"),
            $this->module->config->getValue("SemLink", "srilink"));

        $lecturer_link = $this->module->getModuleLink("Persondetails",
            $this->module->config->getValue("LecturerLink", "config"),
            $this->module->config->getValue("LecturerLink", "srilink"));

        if (is_array($this->sem_browse_data['search_result']) && count($this->sem_browse_data['search_result'])) {

            // show only selected subject areas
            $selected_ranges = $this->module->config->getValue('SelectSubjectAreas', 'subjectareasselected');
            if ($stid = Request::option('sem_tree_id')) {
                if (!is_object($this->sem_tree)){
                    $the_tree = TreeAbstract::GetInstance("StudipSemTree");
                } else {
                    $the_tree =& $this->sem_tree->tree;
                }
                $the_tree->buildIndex();
                $selected_ranges = array_merge([$stid], $the_tree->getKidsKids($stid));
            }
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
            if (Request::get('semstatus')) {
                $selected_semtypes = [Request::get('semstatus')];
            }
            $sem_types_array = [];
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
            $group_colspan = array_count_values($this->module->config->getValue("Main", "visible"));

            if ($this->sem_browse_data['group_by'] == 1){
                if (!is_object($this->sem_tree)){
                    $the_tree = TreeAbstract::GetInstance("StudipSemTree");
                } else {
                    $the_tree =& $this->sem_tree->tree;
                }
            $the_tree->buildIndex();
            }

            if (!$this->module->config->getValue("Main", "allseminars") && !Request::get('allseminars')){
                $sem_inst_query = " AND seminare.Institut_id='{$this->module->config->range_id}' ";
            }
            if (Request::option('aggregation')) {
                $i = Institute::find($this->config->range_id);
                $children = $i->sub_institutes->pluck('institut_id');
                $children[] = $i->institut_id;
                $sem_inst_query = " AND seminare.Institut_id IN ('".(implode("', '", $children))."')";
            }
            if (!$nameformat = $this->module->config->getValue("Main", "nameformat"))
                $nameformat = "no_title_short";

            $dbv = DbView::getView('sem_tree');

            $query = "SELECT seminare.*
                , Institute.Name AS Institut,Institute.Institut_id,
                seminar_sem_tree.sem_tree_id AS bereich, " . $_fullname_sql[$nameformat] ." AS fullname, auth_user_md5.username,
                " . $dbv->sem_number_sql . " AS sem_number, " . $dbv->sem_number_end_sql . " AS sem_number_end, " .
            " seminar_user.position AS position " .
            " FROM seminare
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
                $group_by_duration = $snap->getGroupedResult("sem_number_end", ["sem_number","Seminar_id"]);
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
            //release memory
            unset($snap);
            unset($tmp_group_by_data);

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
                            $the_tree = TreeAbstract::GetInstance('StudipSemTree');
                            return $the_tree->tree_data[$a]['index'] - $the_tree->tree_data[$b]['index'];
                        });
                        break;

                    case 3:
                        if ($order = $this->module->config->getValue("ReplaceTextSemType", "order")) {
                            foreach ($order as $position) {
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
//              $datafields_obj = new DataFields();

            if ($this->module->config->getValue("Main", "addinfo")) {
                $info = "&nbsp;" . count($sem_data);
                $info .= $this->module->config->getValue("Main", "textlectures");
                $info .= ", " . $this->module->config->getValue("Main", "textgrouping");
                $group_by_name = $this->module->config->getValue("Main", "aliasesgrouping");
                $info .= $group_by_name[$this->sem_browse_data['group_by']];
                $out = $this->module->elements["InfoCountSem"]->toString(["content" => $info]);
            }
            else
                $out = "";

            $first_loop = TRUE;
            $repeat_headrow = $this->module->config->getValue("Main", "repeatheadrow");
            foreach ($group_by_data as $group_field => $sem_ids) {

                $group_content = $this->getGroupContent($the_tree, $group_field);

                if ($repeat_headrow == "beneath") {
                    $out .= $this->module->elements["Grouping"]->toString(["content" => $group_content]);
                    $out .= $this->module->elements["TableHeadrow"]->toString();
                }

                if($first_loop && $repeat_headrow != "beneath")
                    $out .= $this->module->elements["TableHeadrow"]->toString();

                if ($repeat_headrow != "beneath") {
                    if ($repeat_headrow && !$first_loop)
                        $out .= $this->module->elements["TableHeadrow"]->toString();
                    $out .= $this->module->elements["Grouping"]->toString(["content" => $group_content]);
                }
                $first_loop = FALSE;

                if (is_array($sem_ids['Seminar_id'])) {
                    $zebra = 0;
                    while (list($seminar_id,) = each($sem_ids['Seminar_id'])) {

                        $sem_name = key($sem_data[$seminar_id]["Name"]);
                        $sem_number_start = key($sem_data[$seminar_id]["sem_number"]);
                        $sem_number_end = key($sem_data[$seminar_id]["sem_number_end"]);
                        if ($sem_number_start != $sem_number_end){
                            $sem_name .= " (" . $this->sem_dates[$sem_number_start]['name'] . " - ";
                            $sem_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $this->sem_dates[$sem_number_end]['name']) . ")";
                        }

                        //create Turnus field
                        $data["content"]["zeiten"] = Seminar::GetInstance($seminar_id)->getDatesExport(['show_room' => true]);
                        //Shorten, if string too long
                        if (mb_strlen($data["content"]["zeiten"]) >70) {
                            $data["content"]["zeiten"] = mb_substr($data["content"]["zeiten"], 0,
                                    mb_strpos(mb_substr($data["content"]["zeiten"], 70, mb_strlen($data["content"]["zeiten"])), ",") +71);
                            $data["content"]["zeiten"] .= "...";
                        }
                        $data["content"]["zeiten"] = htmlReady($data["content"]["zeiten"]);
                        $doz_position = array_keys($sem_data[$seminar_id]['position']);
                        $doz_name = array_keys($sem_data[$seminar_id]['fullname']);
                        $doz_uname = array_keys($sem_data[$seminar_id]['username']);
                        if (is_array($doz_name)){
                            if(count($doz_position) != count($doz_uname)) $doz_position = range(1, count($doz_uname));
                     array_multisort($doz_position, $doz_name, $doz_uname);
                            $data["content"]["dozent"] = "";
                            $i = 0;
                            foreach ($doz_name as $index => $value) {
                                if ($i == 4) {
                                    $data["content"]["dozent"] .= $this->module->elements["LecturerLink"]->toString(
                                        ["module" => "Lecturedetails", "link_args" => "seminar_id=$seminar_id",
                                        "content" => "..."]);
                                    break;
                                }
                                $data["content"]["dozent"] .= $this->module->elements["LecturerLink"]->toString(
                                        ["module" => "Persondetails", "link_args" => "username="
                                        . $doz_uname[$index] . "&seminar_id=$seminar_id",
                                        "content" =>  htmlReady($value)]);
                                if ($i != count($doz_name) - 1) {
                                    $data["content"]["dozent"] .= ", ";
                                }
                                ++$i;
                            }
                        }

                        $data["content"]["Name"] = $this->module->elements["SemLink"]->toString(
                                ["module" => "Lecturedetails", "link_args" => "seminar_id=$seminar_id",
                                "content" => htmlReady($sem_name)]);
                        $data["content"]["VeranstaltungsNummer"] =
                                htmlReady(key($sem_data[$seminar_id]["VeranstaltungsNummer"]));
                        $data["content"]["Untertitel"] = htmlReady(key($sem_data[$seminar_id]["Untertitel"]));

                        $aliases_sem_type = $this->module->config->getValue("ReplaceTextSemType",
                                "class_" . $SEM_TYPE[key($sem_data[$seminar_id]["status"])]['class']);
                        if ($aliases_sem_type[$this->sem_types_position[key($sem_data[$seminar_id]["status"])] - 1]) {
                            $data["content"]["status"] =
                                    $aliases_sem_type[$this->sem_types_position[key($sem_data[$seminar_id]["status"])] - 1];
                        }
                        else {
                            $data["content"]["status"] =
                                    htmlReady($SEM_TYPE[key($sem_data[$seminar_id]["status"])]["name"]
                                    ." (". $SEM_CLASS[$SEM_TYPE[key($sem_data[$seminar_id]["status"])]["class"]]["name"].")");
                        }

                        $data["content"]["Ort"] = Seminar::getInstance($seminar_id)->getDatesTemplate('dates/seminar_export_location');
                        if ($sem_data[$seminar_id]["art"])
                            $data["content"]["art"] = htmlReady(key($sem_data[$seminar_id]["art"]));
                        else
                            $data["content"]["art"] = "";

                        // generic data fields
                        if (is_array($generic_datafields)) {
                            $localEntries = DataFieldEntry::getDataFieldEntries($seminar_id);
                            foreach ($generic_datafields as $id) {
                                if (isset($localEntries[$id]) && is_object($localEntries[$id])) {
                                    $data["content"][$id] = $localEntries[$id]->getDisplayValue();
                                }
                            }
                        }

                        $data["data_fields"] = $this->module->data_fields;
                        $out .= $this->module->elements["TableRow"]->toString($data);
                    }
                }
            }
            ob_end_clean();
            $this->module->elements["TableHeader"]->printout(["content" => $out]);
        }
    }

    // private
    function getGroupContent ($the_tree, $group_field) {
        global $SEM_TYPE, $SEM_CLASS;

        switch ($this->sem_browse_data["group_by"]){
            case 0:
                $content = $this->sem_dates[$group_field]['name'];
                break;

            case 1:
                if ($the_tree->tree_data[$group_field]) {
                    $range_path_level = $this->module->config->getValue("Main", "rangepathlevel");
                    $content = htmlReady($the_tree->getShortPath($group_field, NULL, '>', $range_path_level ? $range_path_level - 1 : 0));
                } else {
                    $content = $this->module->config->getValue("Main", "textnogroups");
                }
                break;

            case 2:
                $content = htmlReady($group_field);
                break;

            case 3:
                $aliases_sem_type = $this->module->config->getValue("ReplaceTextSemType",
                        "class_{$SEM_TYPE[$group_field]['class']}");
                if ($aliases_sem_type[$this->sem_types_position[$group_field] - 1])
                    $content = $aliases_sem_type[$this->sem_types_position[$group_field] - 1];
                else {
                    $content = htmlReady($GLOBALS["SEM_TYPE"][$group_field]["name"]
                            ." (". $SEM_CLASS[$SEM_TYPE[$group_field]["class"]]["name"].")");
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
