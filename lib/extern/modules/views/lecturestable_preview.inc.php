<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

global $SEM_TYPE, $SEM_CLASS;

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

// get semester data
$semester_data = SemesterData::getAllSemesterData();
// current semester
$now = time();
foreach ($semester_data as $key => $sem) {
    if ($sem["beginn"] >= $now)
        break;
}
$i = 1;
$data_group[] = 1;
$data_group[] = 1;
$data_group[] = 2;
$data_sem[0]["Name"] = sprintf(_("Name der Veranstaltung %s"), $i++);
$data_sem[0]["Untertitel"] = sprintf(_("Untertitel der Veranstaltung %s"), $i);
$data_sem[1]["Name"] = sprintf(_("Name der Veranstaltung %s"), $i++);
$data_sem[1]["Untertitel"] = sprintf(_("Untertitel der Veranstaltung %s"), $i);
$data_sem[2]["Name"] = sprintf(_("Name der Veranstaltung %s"), $i);
$data_sem[2]["Untertitel"] = sprintf(_("Untertitel der Veranstaltung %s"), $i);
$data_sem[0]["zeiten"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
$data_sem[1]["zeiten"] = _("Termine am 31.7. 14:00 - 16:00, 17.8. 11:00 - 14:30, 6.9. 14:00 - 16:00,...");
$data_sem[2]["zeiten"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
$data_sem[0]["VeranstaltungsNummer"] = "1592";
$data_sem[1]["VeranstaltungsNummer"] = "1258";
$data_sem[2]["VeranstaltungsNummer"] = "4732";
$data_sem[0]["status"] = _("Seminar");
$data_sem[1]["status"] = _("Vorlesung");
$data_sem[2]["status"] = _("Praktikum");
$data_sem[0]["art"] = _("Vorlesung im Hauptstudium");
$data_sem[1]["art"] = _("Vorlesung im Grundstudium");
$data_sem[2]["art"] = _("Praktikum im Haupstudium");
$data_sem[0]["Ort"] = "MN13";
$data_sem[1]["Ort"] = "ZHG107";
$data_sem[2]["Ort"] = "R124";

switch ($this->config->getValue("Main", "nameformat")) {
    case "no_title_short" :
        $data_sem[0]["dozent"] = _("Meyer, P.");
        break;
    case "no_title" :
        $data_sem[0]["dozent"] = _("Peter Meyer");
        break;
    case "no_title_rev" :
        $data_sem[0]["dozent"] = _("Meyer Peter");
        break;
    case "full" :
        $data_sem[0]["dozent"] = _("Dr. Peter Meyer");
        break;
    case "full_rev" :
        $data_sem[0]["dozent"] = _("Meyer, Peter, Dr.");
        break;
    default :
        $data_sem[0]["dozent"] = _("Meyer, P.");
        break;
}
$data_sem[1]["dozent"] = $data_sem[0]["dozent"];
$data_sem[2]["dozent"] = $data_sem[0]["dozent"];

$repeat_headrow = $this->config->getValue("Main", "repeatheadrow");

$out = "";
if ($this->config->getValue("Main", "addinfo")) {
    $group_by_name = $this->config->getValue("Main", "aliasesgrouping");
    $out = $this->elements["InfoCountSem"]->toString(["content" => "&nbsp;2" .
            $this->config->getValue("Main", "textlectures") . ", " .
            $this->config->getValue("Main", "textgrouping") .
            $group_by_name[3]]);
}

$i = 0;
$group2 = "";
$first_loop = TRUE;
foreach ($data_group as $group) {
    $aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
            "class_{$SEM_TYPE[$group]['class']}");
    if ($aliases_sem_type[$sem_types_position[$group] - 1])
        $group1 = $aliases_sem_type[$sem_types_position[$group] - 1];
    else {
        $group1 = htmlReady($SEM_TYPE[$group]["name"]
                ." (". $SEM_CLASS[$SEM_TYPE[$group]["class"]]["name"].")");
    }

    if ($repeat_headrow == "beneath" && ($group1 != $group2)) {
        $out .= $this->elements["Grouping"]->toString(["content" => $group1]);
        $out .= $this->elements["TableHeadrow"]->toString();
        $group2 = $group1;
    }

    if($first_loop && $repeat_headrow != "beneath")
        $out .= $this->elements["TableHeadrow"]->toString();

    if ($repeat_headrow != "beneath" && ($group1 != $group2)) {
        if ($repeat_headrow && !$first_loop)
            $out .= $this->elements["TableHeadrow"]->toString();
        $out .= $this->elements["Grouping"]->toString(["content" => $group1]);
        $group2 = $group1;
    }

    $out .= $this->elements["TableRow"]->toString(["content" => $data_sem[$i++],
            "data_fields" => $this->data_fields]);

    $first_loop = FALSE;
}
$this->elements["TableHeader"]->printout(["content" => $out]);

?>
