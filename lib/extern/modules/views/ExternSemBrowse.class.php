<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternSemBrowse.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternSemBrowse
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternSemBrowse.class.php
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

class ExternSemBrowse extends SemBrowse {

    var $module;
    var $config;
    var $sem_types_position;

    function __construct(&$module, $start_item_id) {

        global $SEM_TYPE,$SEM_CLASS;
        // prevent warnings if snapshot of database is empty
        ob_start();
        $all_semester = SemesterData::getAllSemesterData();
        array_unshift($all_semester,0);

        $this->group_by_fields = [ ['name' => _("Semester"), 'group_field' => 'sem_number'],
                                        ['name' => _("Bereich"), 'group_field' => 'bereich'],
                                        ['name' => _("Lehrende"), 'group_field' => 'fullname', 'unique_field' => 'username'],
                                        ['name' => _("Typ"), 'group_field' => 'status'],
                                        ['name' => _("Einrichtung"), 'group_field' => 'Institut', 'unique_field' => 'Institut_id']];

        $this->module = $module;
        $this->config = $this->module->config;
        $this->sem_browse_data["group_by"] = $this->config->getValue("Main", "grouping");
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
                date("d") + 7 * $this->config->getValue("Main", "semswitch"), date("Y"));
        // get current semester
        $current_sem = get_sem_num($switch_time) + 1;

        switch ($this->config->getValue("Main", "semstart")) {
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
                if (isset($all_semester[$this->config->getValue("Main", "semstart")]))
                    $current_sem = $this->config->getValue("Main", "semstart");
        }

        $last_sem = $current_sem + $this->config->getValue("Main", "semrange");
        if ($last_sem < $current_sem)
            $last_sem = $current_sem;
        if (!isset($all_semester[$last_sem]))
            $last_sem = sizeof($all_semester);

        for ($i = $last_sem; $i > $current_sem; $i--)
            $this->sem_number[] = $i - 1;

        $semclasses = $this->config->getValue("Main", "semclasses");
        foreach ($SEM_TYPE as $key => $type) {
            if (in_array($type["class"], $semclasses))
                $this->sem_browse_data['sem_status'][] = $key;
        }

        $this->get_sem_range_tree($start_item_id, true);
    }

    function print_result () {
        global $_fullname_sql,$SEM_TYPE,$SEM_CLASS;

        if (is_array($this->sem_browse_data['search_result']) && count($this->sem_browse_data['search_result'])) {

            // show only selected subject areas
            $selected_ranges = $this->config->getValue('SelectSubjectAreas', 'subjectareasselected');
            if ($stid = Request::option('sem_tree_id')) {
                if (!is_object($this->sem_tree)){
                    $the_tree = TreeAbstract::GetInstance("StudipSemTree");
                } else {
                    $the_tree =& $this->sem_tree->tree;
                }
                $the_tree->buildIndex();
                $selected_ranges = array_merge([$stid], $the_tree->getKidsKids($stid));
            }
            if (!$this->config->getValue('SelectSubjectAreas', 'selectallsubjectareas')
                    && count($selected_ranges)) {
                if ($this->config->getValue('SelectSubjectAreas', 'reverseselection')) {
                    $sem_range_query =  "AND seminar_sem_tree.sem_tree_id NOT IN ('".implode("','", $selected_ranges)."')";
                } else {
                    $sem_range_query =  "AND seminar_sem_tree.sem_tree_id IN ('".implode("','", $selected_ranges)."')";
                }
            } else {
                $sem_range_query = '';
            }

            // show only selected SemTypes
            $selected_semtypes = $this->config->getValue('ReplaceTextSemType', 'visibility');
            if (Request::get('semstatus')) {
                $selected_semtypes = [Request::get('semstatus')];
            }
            $sem_types_array = [];
            if (count($selected_semtypes)) {
                //for ($i = 0; $i < count($selected_semtypes); $i++) {
                foreach ($selected_semtypes as $i => $active) {
                    if ($active == '1') {
                        $sem_types_array[] = $i + 1;
                    }
                }
                $sem_types_query = "AND seminare.status IN ('" . implode("','", $sem_types_array) . "')";
            } else {
                $sem_types_query = '';
            }

            if ($this->sem_browse_data['group_by'] == 1){
                if (!is_object($this->sem_tree)){
                    $the_tree = TreeAbstract::GetInstance("StudipSemTree");
                } else {
                    $the_tree =& $this->sem_tree->tree;
                }
                $the_tree->buildIndex();
            }

            if (!$this->config->getValue("Main", "allseminars") && !Request::get('allseminars')) {
                $sem_inst_query = " AND seminare.Institut_id='{$this->config->range_id}' ";
            }
            if (Request::option('aggregation')) {
                $i = Institute::find($this->config->range_id);
                $children = $i->sub_institutes->pluck('institut_id');
                $children[] = $i->institut_id;
                $sem_inst_query = " AND seminare.Institut_id IN ('".(implode("', '", $children))."')";
            }

            $dbv = DbView::getView('sem_tree');

            if (!$nameformat = $this->config->getValue("Main", "nameformat"))
                $nameformat = "no_title_short";
            $query = "SELECT seminare.Seminar_id, seminare.status, seminare.Name
                , seminare.Institut_id, Institute.Name AS Institut,Institute.Institut_id,
                seminar_sem_tree.sem_tree_id AS bereich, "
                . $_fullname_sql[$nameformat]
                . " AS fullname, auth_user_md5.username,
                " . $dbv->sem_number_sql . " AS sem_number, " . $dbv->sem_number_end_sql . " AS sem_number_end, " .
            " seminar_user.position AS position, seminare.parent_course, seminare.visible " .
            " FROM seminare
                LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent')
                LEFT JOIN auth_user_md5 USING (user_id)
                LEFT JOIN user_info USING (user_id)
                LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id)
                LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id)
                LEFT JOIN Institute ON (seminar_inst.institut_id = Institute.Institut_id)
                WHERE (seminare.Seminar_id IN('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "')
                    OR seminare.parent_course IN ('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "'))
                 $sem_inst_query $sem_range_query $sem_types_query";

            $db = new DB_Seminar($query);
            $snap = new DbSnapshot($db);
            $group = Request::int('group');
            if (isset($group) && $group >= 0 && $group < 5) {
                $this->sem_browse_data['group_by'] = $group;
            }
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
                                    $sem_number_end = count($this->sem_dates) - 1;
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

            $show_time = $this->config->getValue("Main", "time");
            $show_lecturer = $this->config->getValue("Main", "lecturer");
            if ($show_time && $show_lecturer) {
                if (!$td2width = $this->config->getValue("LecturesInnerTable", "td2width"))
                    $td2width = 50;
                $colspan = " colspan=\"2\"";
                $td_time = $this->config->getAttributes("LecturesInnerTable", "td2");
                $td_time .= " width=\"$td2width%\"";
                $td_lecturer = " align=\"" . $this->config->getValue("LecturesInnerTable", "td3_align");
                $td_lecturer .= "\" valign=\"" . $this->config->getValue("LecturesInnerTable", "td2_valign");
                $td_lecturer .= "\" width=\"" . (100 - $td2width) . "%\"";
            }
            else {
                $colspan = "";
                $td_time = $this->config->getAttributes("LecturesInnerTable", "td2") . " width=\"100%\"";
                $td_lecturer = " align=\"" . $this->config->getValue("LecturesInnerTable", "td3_align");
                $td_lecturer .= "\" valign=\"" . $this->config->getValue("LecturesInnerTable", "td2_valign");
                $td_lecturer .= " width=\"100%\"";
            }
            // erase output buffer with warnings and start unbuffered output
            ob_end_clean();
            echo "\n<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
            if ($this->config->getValue("Main", "addinfo")) {
                echo "\n<tr" . $this->config->getAttributes("InfoCountSem", "tr") . ">";
                echo "<td" . $this->config->getAttributes("InfoCountSem", "td") . ">";
                echo "<font" . $this->config->getAttributes("InfoCountSem", "font") . ">&nbsp;";
                $visibles = array_filter($sem_data, function ($c) { return key($c['visible']) == 1; });
                echo count($visibles);
                echo $this->config->getValue("Main", "textlectures");
                echo ", " . $this->config->getValue("Main", "textgrouping");
                $group_by_name = $this->config->getValue("Main", "aliasesgrouping");
                echo $group_by_name[$this->sem_browse_data['group_by']];
                echo "</font></td></tr>";
            }
            if (count($group_by_data)) {
                foreach ($group_by_data as $group_field => $sem_ids) {
                    echo "\n<tr" . $this->config->getAttributes("Grouping", "tr") . ">";
                    echo "<td" . $this->config->getAttributes("Grouping", "td") . ">";
                    echo "<font" . $this->config->getAttributes("Grouping", "font") . ">";
                    switch ($this->sem_browse_data["group_by"]){
                        case 0:
                        echo $this->sem_dates[$group_field]['name'];
                        break;

                        case 1:
                        if ($the_tree->tree_data[$group_field]) {
                            $range_path_level = $this->config->getValue("Main", "rangepathlevel");
                            echo htmlReady($the_tree->getShortPath($group_field, NULL, '>', $range_path_level ? $range_path_level - 1 : 0));
                        } else {
                            echo $this->config->getValue("Main", "textnogroups");
                        }
                        /*
                        $range_path_new = NULL;
                        if ($the_tree->tree_data[$group_field]) {
                            $range_path = explode(" ^ ", $the_tree->getShortPath($group_field, "^"));
                            $range_path_level = $this->config->getValue("Main", "rangepathlevel");
                            if ($range_path_level > sizeof($range_path))
                                $range_path_level = sizeof($range_path);
                            for ($i = $range_path_level - 1; $i < sizeof($range_path); $i++)
                                $range_path_new[] = $range_path[$i];
                            echo htmlReady(implode(" > ", $range_path_new));
                        }
                        else
                            echo $this->config->getValue("Main", "textnogroups");
                        */
                        break;

                        case 2:
                        echo htmlReady($group_field);
                        break;

                        case 3:
                        $aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
                                "class_{$SEM_TYPE[$group_field]['class']}");
                        if ($aliases_sem_type[$this->sem_types_position[$group_field] - 1])
                            echo $aliases_sem_type[$this->sem_types_position[$group_field] - 1];
                        else {
                            echo htmlReady($SEM_TYPE[$group_field]["name"]
                                    ." (". $SEM_CLASS[$SEM_TYPE[$group_field]["class"]]["name"].")");
                        }
                        break;

                        case 4:
                        echo htmlReady($group_field);
                        break;

                    }
                    echo "</font></td></tr>";
                    if (is_array($sem_ids['Seminar_id'])) {
                        $zebra = 0;

                        $group_sem_types = SemType::getGroupingSemTypes();

                        $table_data = compact('zebra', 'colspan', 'show_time',
                            'show_lecturer', 'td_time', 'td_lecturer', 'group_sem_types');

                        while (list($seminar_id,) = each($sem_ids['Seminar_id'])) {
                            $this->printCourseRow($seminar_id, $sem_data, $table_data);
                        }

                        echo '<script type="text/javascript">
                            function toggleChildren(showhide) {
                                var children = document.getElementsByName("child-course");

                                var display = "none";
                                if (showhide == "show") {
                                    display = "block";
                                }

                                for (var i = 0 ; i < children.length ; i++) {
                                    if (children[i].style.display == "") {
                                        children[i].style.display = "none";
                                    } else {
                                        children[i].style.display = "";
                                    }
                                }
                                return false;
                            }
                            window.onload = function() { toggleChildren(); };
                            </script>';
                    }
                }
            }
            echo "</table>";
        }
    }

    /**
     * Generate output for a single course (including children, if applicable)
     * @param string $seminar_id the course ID to show
     * @param mixed $sem_data global data with all found courses
     * @param bool $table_data some variables needed for displaying
     * @param bool $child are we showing a child or a normal or parent course?
     */
    private function printCourseRow($seminar_id, &$sem_data, $table_data, $child = false)
    {
        /*
         * Generate output only if parent course is not already present
         * because the current course will be sorted there as a child.
         */
        if (!key($sem_data[$seminar_id]['parent_course']) || !$sem_data[key($sem_data[$seminar_id]['parent_course'])] || $child) {
            extract($table_data);

            $sem_name = key($sem_data[$seminar_id]["Name"]);
            $sem_number_start = key($sem_data[$seminar_id]["sem_number"]);
            $sem_number_end = key($sem_data[$seminar_id]["sem_number_end"]);
            if ($sem_number_start != $sem_number_end) {
                $sem_name .= " (" . $this->sem_dates[$sem_number_start]['name'] . " - ";
                $sem_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $this->sem_dates[$sem_number_end]['name']) . ")";
            }
            echo "\n<tr" . $this->config->getAttributes("LecturesInnerTable", "tr") . ($child ? ' name="child-course"' : '') . ">";
            if ($zebra % 2 && $this->config->getValue("LecturesInnerTable", "td_bgcolor2_"))
                echo "<td width=\"100%\"" . $this->config->getAttributes("LecturesInnerTable", "td", TRUE) . "\">\n";
            else
                echo "<td width=\"100%\"" . $this->config->getAttributes("LecturesInnerTable", "td") . "\">\n";
            $zebra++;
            echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
            echo "<tr" . $this->config->getAttributes("LecturesInnerTable", "tr1") . ">";
            echo "<td$colspan" . $this->config->getAttributes("LecturesInnerTable", "td1") . ">";
            echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font1") . ">";
            $sem_link["module"] = "Lecturedetails";
            $sem_link["link_args"] = "seminar_id=$seminar_id";
            $sem_link["content"] = htmlReady($sem_name);
            $this->module->elements["SemLink"]->printout($sem_link);
            echo "</font>";
            $children = [];
            if (in_array(key($sem_data[$seminar_id]['status']), $table_data['group_sem_types'])) {
                $children = array_filter(Course::findByParent_Course($seminar_id), function ($c) {
                    return $c->visible == 1;
                });
                if (count($children) > 0) {
                    echo '<br><a href="" onclick="return toggleChildren()">' .
                        sprintf(
                            ngettext('%s Unterveranstaltung', '%s Unterveranstaltungen', count($children)),
                            count($children)) . '</a>';
                }
            }
            echo "</td></tr>\n";
            //create Turnus field
            $temp_turnus_string = Seminar::GetInstance($seminar_id)->getDatesExport(['show_room' => true]);
            //Shorten, if string too long (add link for details.php)
            if (mb_strlen($temp_turnus_string) > 70) {
                $temp_turnus_string = mb_substr($temp_turnus_string, 0,
                    mb_strpos(mb_substr($temp_turnus_string, 70,
                        mb_strlen($temp_turnus_string)), ',') + 71);
                $temp_turnus_string .= '...';
            }
            if ($show_time || $show_lecturer) {
                echo "\n<tr" . $this->config->getAttributes('LecturesInnerTable', 'tr2') . '>';
                if ($show_time) {
                    echo "<td$td_time>";
                    echo '<font' . $this->config->getAttributes('LecturesInnerTable', 'font2') . '>';
                    echo $temp_turnus_string . "</font></td>\n";
                }
                if ($show_lecturer) {
                    echo "<td$td_lecturer>";
                    echo '<font' . $this->config->getAttributes('LecturesInnerTable', 'font2') . '>(';
                    $doz_position = array_keys($sem_data[$seminar_id]['position']);
                    $doz_name = array_keys($sem_data[$seminar_id]['fullname']);
                    $doz_uname = array_keys($sem_data[$seminar_id]['username']);
                    if (is_array($doz_name)) {
                        $lecturer_link['module'] = 'Persondetails';
                        if (count($doz_position) != count($doz_uname)) $doz_position = range(1, count($doz_uname));
                        array_multisort($doz_position, $doz_name, $doz_uname);
                        $i = 0;
                        foreach ($doz_name as $index => $value) {
                            if ($i == 4) {
                                echo '...';
                                break;
                            }
                            $lecturer_link['link_args'] = "username={$doz_uname[$index]}&seminar_id=$seminar_id";
                            $lecturer_link['content'] = htmlReady($value);
                            $this->module->elements['LecturerLink']->printout($lecturer_link);
                            if ($i != count($doz_name) - 1) {
                                echo ', ';
                            }
                            ++$i;
                        }
                        echo ') ';
                    }
                    echo '</font></td>';
                }
                echo '</tr>';
            }
            echo "</table>";
            echo "</td></tr>\n";

            if (count($children) > 0) {
                foreach ($children as $child) {
                    $this->printCourseRow($child->id, $sem_data, $table_data, true);
                }
            }
        }
    }

}
?>
