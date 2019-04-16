<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO


global $SEM_TYPE,$SEM_CLASS ;
$all_semester = SemesterData::getAllSemesterData();

// reorganize the $SEM_TYPE-array
foreach ($SEM_CLASS as $key_class => $class) {
    $i = 0;
    foreach ($SEM_TYPE as $key_type => $type) {
        if ($type["class"] == $key_class) {
            $i++;
            $sem_types_position[$key_type] = $i;
        }
    }
}

// current semester
$now = time();
foreach ($all_semester as $key => $sem) {
    if ($sem["beginn"] >= $now)
        break;
}

$data_sem[0]["group"] = 1;
$data_sem[1]["group"] = 1;
$data_sem[2]["group"] = 2;
$data_sem[0]["name"] = sprintf(_('Name der Veranstaltung %u'), 1);
$data_sem[1]["name"] = sprintf(_('Name der Veranstaltung %u'), 2);
$data_sem[2]["name"] = sprintf(_('Name der Veranstaltung %u'), 3);
$data_sem[0]["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
$data_sem[1]["time"] = _("Termine am 31.7. 14:00 - 16:00, 17.8. 11:00 - 14:30, 6.9. 14:00 - 16:00,...");
$data_sem[2]["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");

switch ($this->config->getValue("Main", "nameformat")) {
    case "no_title_short" :
        $data_sem[0]["lecturer"] = _("Meyer, P.");
        break;
    case "no_title" :
        $data_sem[0]["lecturer"] = _("Peter Meyer");
        break;
    case "no_title_rev" :
        $data_sem[0]["lecturer"] = _("Meyer Peter");
        break;
    case "full" :
        $data_sem[0]["lecturer"] = _("Dr. Peter Meyer");
        break;
    case "full_rev" :
        $data_sem[0]["lecturer"] = _("Meyer, Peter, Dr.");
        break;
    default :
        $data_sem[0]["lecturer"] = _("Meyer, P.");
        break;
}
$data_sem[1]["lecturer"] = $data_sem[0]["lecturer"];
$data_sem[2]["lecturer"] = $data_sem[0]["lecturer"];

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

echo "\n<table" . $this->config->getAttributes("TableHeader", "table") . ">";
if ($this->config->getValue("Main", "addinfo")) {
    echo "\n<tr" . $this->config->getAttributes("InfoCountSem", "tr") . ">";
    echo "<td" . $this->config->getAttributes("InfoCountSem", "td") . ">";
    echo "<font" . $this->config->getAttributes("InfoCountSem", "font") . ">&nbsp;";
    echo "2";
    echo $this->config->getValue("Main", "textlectures");
    echo ", " . $this->config->getValue("Main", "textgrouping");
    $group_by_name = $this->config->getValue("Main", "aliasesgrouping");
    echo $group_by_name[3];
    echo "</font></td></tr>";
}
$i = 0;
$group = "";
foreach ($data_sem as $dat) {
    $aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
            "class_{$SEM_TYPE[$dat['group']]['class']}");
    if ($aliases_sem_type[$sem_types_position[$dat['group']] - 1])
        $group2 = $aliases_sem_type[$sem_types_position[$dat['group']] - 1];
    else {
        $group2 = htmlReady($SEM_TYPE[$dat['group']]["name"]
                ." (". $SEM_CLASS[$SEM_TYPE[$dat['group']]["class"]]["name"].")");
    }

    if ($group != $group2) {
        echo "\n<tr" . $this->config->getAttributes("Grouping", "tr") . ">";
        echo "<td" . $this->config->getAttributes("Grouping", "td") . ">";
        echo "<font" . $this->config->getAttributes("Grouping", "font") . ">";
        echo $group2;
        echo "\n</td></tr>\n";
        $group = $group2;
    }

    echo "<tr" . $this->config->getAttributes("LecturesInnerTable", "tr").">";
    if ($i % 2 && $this->config->getValue("LecturesInnerTable", "td_bgcolor2_"))
        echo "<td width=\"100%\"".$this->config->getAttributes("LecturesInnerTable", "td", TRUE)."\">\n";
    else
        echo "<td width=\"100%\"".$this->config->getAttributes("LecturesInnerTable", "td")."\">\n";
    $i++;
    echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
    echo "<tr" . $this->config->getAttributes("LecturesInnerTable", "tr1") . ">";
    echo "<td$colspan" . $this->config->getAttributes("LecturesInnerTable", "td1") . ">";
    echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font1") . ">";
    echo "<a href=\"\"";
    echo $this->config->getAttributes("SemLink", "a") . ">";
    echo $dat["name"] . "</a></font></td></tr>";

    if ($show_time || $show_lecturer) {
        echo "\n<tr" . $this->config->getAttributes("LecturesInnerTable", "tr2") . ">";
        if ($show_time) {
            echo "<td$td_time>";
            echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font2") . ">";
            echo $dat["time"] . "</font>\n";
        }
        if ($show_lecturer) {
            echo "<td$td_lecturer>";
            echo "<font" . $this->config->getAttributes("LecturesInnerTable", "font2") . ">(";
            echo "<a href=\"\"";
            echo $this->config->getAttributes("LecturerLink", "a") . ">";
            echo $dat["lecturer"] . "</a>";
            echo ") </font></td>";
        }
        echo "</tr>\n";
    }
    echo "</table></td></tr>\n";
}
echo "</table>";

?>
