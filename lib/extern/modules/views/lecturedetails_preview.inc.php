<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

require_once 'lib/dates.inc.php';

global $SEM_CLASS, $SEM_TYPE;

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

$data_sem["name"] = _("Name der Veranstaltung");
$data_sem["subtitle"] = _("Untertitel der Veranstaltung");
switch ($this->config->getValue("Main", "nameformat")) {
    case "no_title_short" :
        $data_sem["lecturer"] = _("Meyer, P.");
        break;
    case "no_title" :
        $data_sem["lecturer"] = _("Peter Meyer");
        break;
    case "no_title_rev" :
        $data_sem["lecturer"] = _("Meyer Peter");
        break;
    case "full" :
        $data_sem["lecturer"] = _("Dr. Peter Meyer");
        break;
    case "full_rev" :
        $data_sem["lecturer"] = _("Meyer, Peter, Dr.");
        break;
    default :
        $data_sem["lecturer"] = _("Meyer, P.");
}
$data_sem["art"] = _("Testveranstaltung");
$data_sem["semtype"] = 1;
$data_sem["description"] = str_repeat(_("Beschreibung") . " ", 10);
$data_sem["location"] = _("A 123, 1. Stock");
$data_sem["semester"] = "WS 2003/2004";
$data_sem["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
$data_sem["number"] = "1234";
$data_sem["teilnehmer"] = str_repeat(_("Teilnehmende") . " ", 6);
$data_sem["requirements"] = str_repeat(_("Voraussetzungen") . " ", 6);
$data_sem["lernorga"] = str_repeat(_("Lernorganisation") . " ", 6);
$data_sem["leistung"] = str_repeat(_("Leistungsnachweis") . " ", 6);
$data_sem["range_path"] = _("Fakultät &gt; Studiengang &gt; Bereich");
$data_sem["misc"] = str_repeat(_("Sonstiges") . " ", 6);
$data_sem["ects"] = "4";


setlocale(LC_TIME, $this->config->getValue("Main", "timelocale"));
$order = $this->config->getValue("Main", "order");
$visible = $this->config->getValue("Main", "visible");
$aliases = $this->config->getValue("Main", "aliases");
$j = -1;

$data["name"] = $data_sem["name"];

if ($visible[++$j])
    $data["subtitle"] = $data_sem["subtitle"];

if ($visible[++$j]) {
    $data["lecturer"][] = sprintf("<a href=\"\"%s>%s</a>",
            $this->config->getAttributes("LinkInternSimple", "a"),
            $data_sem["lecturer"]);
    if (is_array($data["lecturer"]))
        $data["lecturer"] = implode(", ", $data["lecturer"]);
}

if ($visible[++$j])
    $data["art"] = $data_sem["art"];

if ($visible[++$j]) {
    $aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
            "class_{$SEM_TYPE[$data_sem['semtype']]['class']}");
    if ($aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1])
        $data["status"] = $aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1];
    else {
        $data["status"] = htmlReady($SEM_TYPE[$data_sem['semtype']]["name"]
                ." (". $SEM_CLASS[$SEM_TYPE[$data_sem['semtype']]["class"]]["name"].")");
    }
}

if ($visible[++$j])
    $data["description"] = $data_sem["description"];

if ($visible[++$j])
    $data["location"] = $data_sem["location"];

if ($visible[++$j])
    $data["semester"] = $data_sem["semester"];

if ($visible[++$j])
    $data["time"] = $data_sem["time"];

if ($visible[++$j])
    $data["number"] = $data_sem["number"];

if ($visible[++$j])
    $data["teilnehmer"] = $data_sem["teilnehmer"];

if ($visible[++$j])
    $data["requirements"] = $data_sem["requirements"];

if ($visible[++$j])
    $data["lernorga"] = $data_sem["lernorga"];

if ($visible[++$j])
    $data["leistung"] = $data_sem["leistung"];

if ($visible[++$j]) {
    $pathes = [$data_sem["range_path"]];
    if (is_array($pathes)) {
        $pathes_values = array_values($pathes);
        if ($this->config->getValue("Main", "range") == "long")
            $data["range_path"] = $pathes_values;
        else {
            foreach ($pathes_values as $path)
                $data["range_path"][] = array_pop(explode("&gt;", $path));
        }
        $data["range_path"] = array_filter($data["range_path"], "htmlReady");
        $data["range_path"] = implode("<br>", $data["range_path"]);
    }
}

if ($visible[++$j])
    $data["misc"] = $data_sem["misc"];

if ($visible[++$j])
    $data["ects"] = $data_sem["ects"];

if ($this->config->getValue("Main", "studiplink")) {
    echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ";
    if ($studiplink_width = $this->config->getValue("TableHeader", "table_width"))
        echo " width=\"$studiplink_width\"";
    if ($studiplink_align = $this->config->getValue("TableHeader", "table_align"))
        echo " align=\"$studiplink_align\">\n";


    if ($this->config->getValue("Main", "studiplink") == "top") {
        $args = ["width" => "100%", "height" => "40", "link" => ""];
        echo "<tr><td width=\"100%\">\n";
        $this->elements["StudipLink"]->printout($args);
        echo "</td></tr>";
    }
    $table_attr = $this->config->getAttributes("TableHeader", "table");
    $pattern = ["/width=\"[0-9%]+\"/", "/align=\"[a-z]+\"/"];
    $replace = ["width=\"100%\"", ""];
    $table_attr = preg_replace($pattern, $replace, $table_attr);
    echo "<tr><td width=\"100%\">\n<table$table_attr>\n";
}
else
    echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";

echo "<tr" . $this->config->getAttributes("SemName", "tr") . ">";
echo "<td" . $this->config->getAttributes("SemName", "td") . ">";

if ($margin = $this->config->getValue("SemName", "margin"))
    echo "<div style=\"margin-left:{$margin}px;\">";
else
    echo "<div>";
echo "<font" . $this->config->getAttributes("SemName", "font") . ">";
echo $data["name"] . "</font></div></td></tr>\n";

$headline_tr = $this->config->getAttributes("Headline", "tr");
$headline_td = $this->config->getAttributes("Headline", "td");
$headline_font = $this->config->getAttributes("Headline", "font");
if ($headline_margin = $this->config->getValue("Headline", "margin")) {
    $headline_div = "<div style=\"margin-left:$headline_margin;\">";
    $headline_div_end = "</div>";
}
else {
    $headline_div = "";
    $headline_div_end = "";
}
$content_tr =$this->config->getAttributes("Content", "tr");
$content_td = $this->config->getAttributes("Content", "td");
$content_font = $this->config->getAttributes("Content", "font");
if ($content_margin = $this->config->getValue("Content", "margin")) {
    $content_div = "<div style=\"margin-left:$content_margin;\">";
    $content_div_end = "</div>";
}
else {
    $content_div = "";
    $content_div_end = "";
}

if ($this->config->getValue("Main", "headlinerow")) {
    foreach ($order as $position) {
        if ($visible[$position] && $data[$this->data_fields[$position]]) {
            echo "<tr$headline_tr><td$headline_td>$headline_div";
            echo "<font$headline_font>{$aliases[$position]}</font>$headline_div_end</td></tr>\n";
            echo "<tr$content_tr><td$content_td>$content_div";
            echo "<font$content_font>" . $data[$this->data_fields[$position]];
            echo "</font>$content_div_end</td></tr>\n";
        }
    }
}
else {
    foreach ($order as $position) {
        if ($visible[$position] && $data[$this->data_fields[$position]]) {
            echo "<tr$content_tr><td$content_td>$content_div";
            echo "<font$headline_font>{$aliases[$position]}</font>\n";
            echo "<font$content_font>" . $data[$this->data_fields[$position]];
            echo "</font>$content_div_end</td></tr>\n";
        }
    }
}

if ($this->config->getValue("Main", "studipinfo")) {
    echo "<tr$headline_tr><td$headline_td>$headline_div";
    echo "<font$headline_font>" . $this->config->getValue("StudipInfo", "headline");
    echo "<font>$headline_div_end</td></tr>\n";

    $pre_font = $this->config->getAttributes("StudipInfo", "font");
    echo "<tr$content_tr><td$content_td>$content_div";
    echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "homeinst");
    echo "&nbsp;</font><font$content_font>";
    printf("<a href=\"\"%s>%s</a>",
            $this->config->getAttributes("LinkInternSimple", "a"),
            _("Heimatinstitut"));
    echo "<br></font>\n";

    echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "involvedinst");
    echo "&nbsp;</font><font$content_font>";
    echo str_repeat(_("Beteiligte Institute") . " ", 5) . "<br></font>\n";

    echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countuser");
    echo "&nbsp;</font><font$content_font>";
    echo "23<br></font>\n";

    echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countpostings");
    echo "&nbsp;</font><font$content_font>";
    echo "42<br></font>\n";

    echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countdocuments");
    echo "&nbsp;</font><font$content_font>";
    echo "7<br></font>\n";
    echo "$content_div_end</td></tr>";
}

echo "</table>\n";

if ($this->config->getValue("Main", "studiplink")) {
    if ($this->config->getValue("Main", "studiplink") == "bottom") {
        $args = ["width" => "100%", "height" => "40", "link" => ""];
        echo "</td></tr>\n<tr><td width=\"100%\">\n";
        $this->elements["StudipLink"]->printout($args);
    }
    echo "</td></tr></table>\n";
}

?>
