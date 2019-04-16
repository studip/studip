<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO


global $_fullname_sql;

$attr_subheadline_td = preg_replace('/width\="[^"]+"/i',
        $this->config->getAttributes("TableParagraphSubHeadline", "td"),
        $this->config->getValue("TableParagraph", "margin"));


$aliases_content = $this->config->getValue("Main", "aliases");
$visible_content = $this->config->getValue("Main", "visible");

if ($margin = $this->config->getValue("TableParagraphText", "margin")) {
    $text_div = "<div style=\"margin-left:$margin;\">";
    $text_div_end = "</div>";
}
else {
    $text_div = "";
    $text_div_end = "";
}

$first_loop = TRUE;
$order = $this->config->getValue("Main", "order");
foreach ($order as $position) {

    $data_field = $this->data_fields["content"][$position];

    if ($visible_content[$position]) {
        $data = NULL;
        switch ($data_field) {
            case "lebenslauf" :
                $data["content"] = str_repeat(_("Das ist mein Lebenslauf.") . " &nbsp;", 15);
                break;
            case "schwerp" :
                $data["content"] = str_repeat(_("Das sind meine Arbeitsschwerpunkte.") . " &nbsp;", 15);
                break;
            case "publi" :
                $data["content"] = str_repeat(_("Das sind meine Publikationen.") . " &nbsp;", 15);
                break;
            case "news" :
                $data[0]["topic"] = sprintf(_('Das ist News Nr. %u'), 1);
                $data[0]["body"] = str_repeat(sprintf(_('News Nr. %u'), 1) . " &nbsp;", 10);
                $data[1]["topic"] = sprintf(_('Das ist News Nr. %u'), 2);
                $data[1]["body"] = str_repeat(sprintf(_('News Nr. %u'), 2) . " &nbsp;", 10);
                $data[2]["topic"] = sprintf(_('Das ist News Nr. %u'), 3);
                $data[2]["body"] = str_repeat(sprintf(_('News Nr. %u'), 3) . " &nbsp;", 10);
                break;
            case "termine" :
                $now = time();
                for ($i = 0; $i < 3; $i++) {
                    $data[$i]["start"] = $now + 19710329 * ($i + 1);
                    $data[$i]["end"] = $data[$i]["start"] + 1000 * ($i + 1);
                }
                $data[0]["title"] = _("Das ist der erste Termin");
                $data[1]["title"] = _("Das ist der zweite Termin");
                $data[2]["title"] = _("Das ist der dritte Termin");
                $data[0]["content"] = str_repeat(_("Erster Termin") . " &nbsp;", 10);
                $data[1]["content"] = str_repeat(_("Zweiter Termin") . " &nbsp;", 10);
                $data[2]["content"] = str_repeat(_("dritter Termin ") . " &nbsp;", 10);
                break;
            case "kategorien" :
                $data["headline"] = _("Eigene Kategorie");
                $data["content"] = str_repeat(_("Eigene Kategorie") . " &nbsp;", 10);
                break;
            case "lehre" :
                $now = time();
                $data[0]["start_time"] = $now - 164160000;
                $data[1]["start_time"] = $now;
                $data[2]["start_time"] = $now + 164160000;
                $data[0]["name"] = sprintf(_('Veranstaltung %u'), 1);
                $data[1]["name"] = sprintf(_('Veranstaltung %u'), 2);
                $data[2]["name"] = sprintf(_('Veranstaltung %u'), 3);
                $data[0]["untertitel"] = sprintf(_('Untertitel der Veranstaltung %u'), 1);
                $data[1]["untertitel"] = sprintf(_('Untertitel der Veranstaltung %u'), 2);
                $data[2]["untertitel"] = sprintf(_('Untertitel der Veranstaltung %u'), 3);
                break;
            case "head" :
                $nameformat = $this->config->getValue("Main", "nameformat");
                $user = User::build([
                    'vorname'     => 'Peter',
                    'nachname'    => 'Meyer',
                    'title_front' => 'Dr.',
                ]);

                $data['fullname'] = $user->getFullName($nameformat);
                $data['instfunction'] = _("HochschullehrerIn");
                $data["Name"] = _("Mustereinrichtung");
                $data["Strasse"] = _("Musterstra&szlig;e 23");
                $data["Plz"] = _("12345 Musterstadt");
                $data["raum"] = "A 123";
                $data["Telefon"] = "213 - 237 192";
                $data["Fax"] = "213 - 237 191";
                $data["Email"] = "email@email.org";
                $data["Home"] = "http://www.studip.de";
                $data["sprechzeiten"] = _("Mo. und Do. 12.00 - 13.00");
                break;
        }

        if ($first_loop) {
            echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
            if ($this->config->getValue("Main", "studiplink") == "top") {
                $args = ["width" => "100%", "height" => "40", "link" => ""];
                echo "<tr><td width=\"100%\">\n";
                $this->elements["StudipLink"]->printout($args);
                echo "</td></tr>";
            }
            $first_loop = FALSE;
        }

        switch ($data_field) {
            case "lebenslauf" :
            case "schwerp" :
            case "publi" :
                echo "\n<tr><td width=\"100%\">\n";
                echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
                echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
                echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
                echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
                echo $aliases_content[$position] . "</font></td></tr>\n";
                echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
                echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
                echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
                echo $data["content"];
                echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
                break;
            case "news" :
            case "termine" :
            case "kategorien" :
            case "lehre" :
            case "head" :
                $data_field($this, $data, $aliases_content[$position], $text_div, $text_div_end);
        }
    }
}

// fit size of image
if ($pic_max_width && $pic_max_height) {
    $pic_size = @getimagesize($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . "user/"
            . $db->f("user_id") . ".jpg");

    if ($pic_size[0] > $pic_max_width || $pic_size[1] > $pic_max_height) {
        $fak_width = $pic_size[0] / $pic_max_width;
        $fak_height = $pic_size[1] / $pic_max_height;
        if ($fak_width > $fak_height) {
            $pic_width = (int) ($pic_size[0] / $fak_width);
            $pic_height = (int) ($pic_size[1] / $fak_width);
        }
        else {
            $pic_height = (int) ($pic_size[1] / $fak_height);
            $pic_width = (int) ($pic_size[0] / $fak_height);
        }
    }
    else {
        $pic_width = $pic_size[0];
        $pic_height = $pic_size[1];
    }
    $pic_max_width = $pic_width;
    $pic_max_height = $pic_height;
}
else {
    $pic_max_width = "";
    $pic_max_height = "";
}

if ($this->config->getValue("Main", "studiplink") == "bottom") {
    $args = ["width" => "100%", "height" => "40", "link" => ""];
    echo "<tr><td width=\"100%\">\n";
    $this->elements["StudipLink"]->printout($args);
    echo "</td></tr>";
}

echo "</table>\n";

function news (&$module, $data, $alias_content, $text_div, $text_div_end) {
    if ($margin = $module->config->getValue("TableParagraphSubHeadline", "margin")) {
        $subheadline_div = "<div style=\"margin-left:$margin;\">";
        $subheadline_div_end = "</div>";
    }
    else {
        $subheadline_div = "";
        $subheadline_div_end = "";
    }

    echo "<tr><td width=\"100%\">\n";
    echo "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
    echo "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
    echo "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
    echo "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
    echo "$alias_content</font></td></tr>\n";
    foreach ($data as $dat) {
        echo "<tr" . $module->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
        echo "<td" . $module->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
        echo $subheadline_div;
        echo "<font" . $module->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
        echo $dat["topic"];
        echo "</font>$subheadline_div_end</td></tr>\n";
        echo "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
        list ($content, $admin_msg) = explode("<admin_msg>", $dat["body"]);
        echo "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";
        echo "$text_div<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
        echo $content;
        echo "</font>$text_div_end</td></tr>\n";
    }
    echo "</table>\n</td></tr>\n";
}

function termine (&$module, $data, $alias_content, $text_div, $text_div_end) {
    if (Config::get()->CALENDAR_ENABLE) {
        if ($margin = $module->config->getValue("TableParagraphSubHeadline", "margin")) {
            $subheadline_div = "<div style=\"margin-left:$margin;\">";
            $subheadline_div_end = "</div>";
        }
        else {
            $subheadline_div = "";
            $subheadline_div_end = "";
        }

        echo "<tr><td width=\"100%\">\n";
        echo "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
        echo "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
        echo "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
        echo "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
        echo "$alias_content</font></td></tr>\n";

        foreach ($data as $dat) {
            echo "<tr" . $module->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
            echo "<td" . $module->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
            echo $subheadline_div;
            echo "<font" . $module->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
            echo strftime($module->config->getValue("Main", "dateformat") . " %H.%m", $dat["start"]);
            if (date("dmY", $dat["start"]) == date("dmY", $dat["end"]))
                echo strftime(" - %H.%m", $dat["end"]);
            else
                echo strftime(" - " . $module->config->getValue("Main", "dateformat") . " %H.%m", $dat["end"]);
            echo " &nbsp;" . $dat["title"];
            echo "</font>$subheadline_div_end</td></tr>\n";
            echo "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
            echo "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";
            echo "$text_div<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
            echo $dat["content"];
            echo "</font>$text_div_end</td></tr>\n";
        }
        echo "</table>\n</td></tr>\n";
    }
}

function kategorien (&$module, $data, $alias_content, $text_div, $text_div_end) {
    echo "<tr><td width=\"100%\">\n";
    echo "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
    echo "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
    echo "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
    echo "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
    echo $data["headline"];
    echo "</font></td></tr>\n";
    echo "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
    echo "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";
    echo "$text_div<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
    echo $data["content"];
    echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
}

function lehre (&$module, $data, $alias_content, $text_div, $text_div_end) {
    global $attr_text_td;

    $all_semester = SemesterData::getAllSemesterData();
    // old hard coded $SEMESTER-array starts with index 1
    array_unshift($all_semester, 0);

    if ($margin = $module->config->getValue("TableParagraphSubHeadline", "margin")) {
        $subheadline_div = "<div style=\"margin-left:$margin;\">";
        $subheadline_div_end = "</div>";
    }
    else {
        $subheadline_div = "";
        $subheadline_div_end = "";
    }
    if ($margin = $module->config->getValue("List", "margin")) {
        $list_div = "<div style=\"margin-left:$margin;\">";
        $list_div_end = "</div>";
    }
    else {
        $list_div = "";
        $list_div_end = "";
    }
    // sem-types in class 1 (Lehre)
    foreach ($GLOBALS["SEM_TYPE"] as $key => $type) {
        if ($type["class"] == 1)
            $types[] = $key;
    }
    $types = implode("','", $types);


    $switch_time = mktime(0, 0, 0, date("m"),
            date("d") + 7 * $module->config->getValue("PersondetailsLectures", "semswitch"), date("Y"));
    // get current semester
    $current_sem = get_sem_num($switch_time) + 1;

    switch ($module->config->getValue("PersondetailsLectures", "semstart")) {
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
            if (isset($all_semester[$module->config->getValue("PersondetailsLectures", "semstart")]))
                $current_sem = $module->config->getValue("PersondetailsLectures", "semstart");
    }

    $last_sem = $current_sem + $module->config->getValue("PersondetailsLectures", "semrange") - 1;
    if ($last_sem < $current_sem)
        $last_sem = $current_sem;
    if (!isset($all_semester[$last_sem]))
        $last_sem = sizeof($all_semester) - 1;

    $out = "";
    for (;$current_sem <= $last_sem; $last_sem--) {
        if (!($module->config->getValue("PersondetailsLectures", "semstart") == "current"
                && $module->config->getValue("PersondetailsLectures", "semrange") == 1)) {
            $out .= "<tr" . $module->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
            $out .= "<td" . $module->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
            $out .= $subheadline_div;
            $out .= "<font" . $module->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
            $month = date("n", $all_semester[$last_sem]["beginn"]);
            if($month > 9) {
                $out .= $module->config->getValue("PersondetailsLectures", "aliaswise");
                $out .= date(" Y/", $all_semester[$last_sem]["beginn"]) . date("y", $all_semester[$last_sem]["ende"]);
            }
            else if($month > 3 && $month < 10) {
                $out .= $module->config->getValue("PersondetailsLectures", "aliassose");
                $out .= date(" Y", $all_semester[$last_sem]["beginn"]);
            }
            $out .= "</font>$subheadline_div_end</td></tr>\n";
        }

        $out .= "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
        $out .= "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";

        if ($module->config->getValue("PersondetailsLectures", "aslist")) {
            $out .= "$list_div<ul" . $module->config->getAttributes("List", "ul") . ">\n";
            foreach ($data as $dat) {
                $out .= "<li" . $module->config->getAttributes("List", "li") . ">";
                $out .= "<font" . $module->config->getAttributes("LinkIntern", "font") . ">";
                $out .= "<a href=\"\"" . $module->config->getAttributes("LinkIntern", "a") . ">";
                $out .= $dat["name"] . "</a></font>\n";
                $out .= "<font" . $module->config->getAttributes("TableParagraphText", "font") . "><br>";
                $out .= $dat["untertitel"] . "</font>\n";
            }
            $out .= "</ul>$list_div_end";
        }
        else {
            $out .= $text_div;
            $j = 0;
            foreach ($data as $dat) {
                if ($j) $out .= "<br><br>";
                $out .= "<font" . $module->config->getAttributes("LinkIntern", "font") . ">";
                $out .= "<a href=\"$lnk\"" . $module->config->getAttributes("LinkIntern", "a") . ">";
                $out .= $dat["name"] . "</a></font>\n";
                $out .= "<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
                $out .= "<br>" . $dat["untertitel"] . "</font>\n";
                $j = 1;
            }
            $out .= $text_div_end;
        }
        $out .= "</td></tr>\n";
    }

    if ($out) {
        $out_title = "<tr><td width=\"100%\">\n";
        $out_title .= "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
        $out_title .= "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
        $out_title .= "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
        $out_title .= "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
        $out_title .= $alias_content . "</font></td></tr>\n";
        echo $out_title . $out . "</table>\n</td></tr>\n";
    }
}

function head (&$module, $data, $a) {
    if ($module->config->getValue("Main", "showcontact")
            && $module->config->getValue("Main", "showimage"))
        $colspan = " colspan=\"2\"";
    else
        $colspan = "";

    echo "<tr><td width=\"100%\">\n";
    echo "<table" . $module->config->getAttributes("PersondetailsHeader", "table") . ">\n";
    if (!$module->config->getValue('PersondetailsHeader', 'hidename')) {
        echo "<tr" . $module->config->getAttributes("PersondetailsHeader", "tr") . ">";
        echo "<td$colspan width=\"100%\"";
        echo $module->config->getAttributes("PersondetailsHeader", "headlinetd") . ">";
        echo "<font" . $module->config->getAttributes("PersondetailsHeader", "font") . ">";
        echo $data["fullname"];
        echo "</font></td></tr>\n";
    }

    if ($module->config->getValue("Main", "showimage")
            || $module->config->getValue("Main", "showcontact")) {
        echo "<tr>";
        if ($module->config->getValue("Main", "showcontact")
                && ($module->config->getValue("Main", "showimage") == "right"
                || !$module->config->getValue("Main", "showimage"))) {
                echo "<td" . $module->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
                echo kontakt($module, $data) . "</td>\n";
        }

        if ($module->config->getValue("Main", "showimage")) {
            echo "<td" . $module->config->getAttributes("PersondetailsHeader", "picturetd") . ">";
            echo "<img src=\"".Avatar::getNobody()->getUrl(Avatar::NORMAL)."\" alt=\"Foto " . $data["fullname"] . "\"";
            echo $module->config->getAttributes("PersondetailsHeader", "img") . ">";
        }

        if ($module->config->getValue("Main", "showcontact")
                && $module->config->getValue("Main", "showimage") == "left") {
            echo "<td" . $module->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
            echo kontakt($module, $data) . "</td>\n";
        }

        echo "</tr>\n";
        if ($module->config->getValue('Main', 'showcontact')
                && $module->config->getValue('Contact', 'separatelinks')) {
            echo "<tr><td";
            if ($module->config->getValue('Main', 'showimage'))
                echo ' colspan="2"';
            echo $module->config->getAttributes('PersondetailsHeader', 'contacttd') . ">\n";
            echo kontakt($module, $data, TRUE);
            echo "</td></tr>\n";
        }
    }

    echo "</table>\n</td></tr>\n";
}

function kontakt (&$module, $data, $separate = FALSE) {
    $attr_table = $module->config->getAttributes("Contact", "table");
    $attr_tr = $module->config->getAttributes("Contact", "table");
    $attr_td = $module->config->getAttributes("Contact", "td");
    $attr_fonttitle = $module->config->getAttributes("Contact", "fonttitle");
    $attr_fontcontent = $module->config->getAttributes("Contact", "fontcontent");

    $out = "<table$attr_table>\n";
    if (!$separate) {
        $out .= "<tr$attr_tr>";
        $out .= "<td colspan=\"2\"$attr_td>";
        $out .= "<font$attr_fonttitle>";
        if ($headline = $module->config->getValue("Contact", "headline"))
            $out .= "$headline</font>\n";
        else
            $out .= "</font>\n";
        $out .= "<font$attr_fontcontent>";

        if (!$module->config->getValue("Contact", "hidepersname"))
            $out .= "<br><br>" . $data["fullname"] . "\n";
        if ($module->config->getValue('Contact', 'showinstgroup'))
            $out .= "<br>{$data['instfunction']}\n";

        if ($module->config->getValue("Contact", "hideinstname") != '1') {
            if ($module->config->getValue("Contact", "hideinstname") == 'link')
                $out .= "<br><br><a href=\"\">" . $data["Name"] . "</a><br>";
            else
                $out .= "<br><br>" . $data["Name"] . "<br>";
        }
        if ($module->config->getValue("Contact", "adradd"))
            $out .= "<br>" . $module->config->getValue("Contact", "adradd");

        $out .= "<br><br>" . $data["Strasse"];
        $out .= "<br>" . $data["Plz"];

      $out .= "<br><br></font></td></tr>\n";
    }
    $order = $module->config->getValue("Contact", "order");
    $visible = $module->config->getValue("Contact", "visible");
    $alias_contact = $module->config->getValue("Contact", "aliases");
    foreach ($order as $position) {
        if (!$visible[$position])
            continue;
        $data_field = $module->data_fields["contact"][$position];
        switch ($data_field) {
            case 'Email' :
                if ($separate || !$module->config->getValue('Contact', 'separatelinks')) {
                    $out .= "<tr$attr_tr>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fonttitle>";
                    $out .= $alias_contact[$position] . "</font></td>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fontcontent>";
                    $out .= "<a href=\"mailto:{$data['Email']}\">{$data['Email']}</a>";
                }
                break;
            case 'Home' :
                if ($separate || !$module->config->getValue('Contact', 'separatelinks')) {
                    $out .= "<tr$attr_tr>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fonttitle>";
                    $out .= $alias_contact[$position] . "</font></td>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fontcontent>{$data['Home']}";
                }
                break;
            default:
                if (!$separate) {
                    $out .= "<tr$attr_tr>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fonttitle>";
                    $out .= $alias_contact[$position] . "</font></td>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fontcontent>{$data[$data_field]}";
                }
        }
        $out .= "</font></td></tr>\n";
    }

    $out .= "</table>\n";

    return $out;
}

?>
