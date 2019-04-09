<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO

require_once 'lib/statusgruppe.inc.php';

if (Config::get()->CALENDAR_ENABLE) {
    require_once 'app/models/calendar/SingleCalendar.php';
}

$instituts_id = $this->config->range_id;
$username = $args['username'];
$sem_id = $args['seminar_id'];

// Mitarbeiter/in am Institut
$ext_vis_query = get_ext_vis_query();
$query = "SELECT 1
          FROM Institute
          LEFT JOIN user_inst AS ui USING (Institut_id)
          LEFT JOIN auth_user_md5 AS aum USING (user_id)
          WHERE Institut_id = ? AND aum.username = ?
            AND ui.inst_perms IN ('autor', 'tutor', 'dozent') AND {$ext_vis_query}";
$statement = DBManager::get()->prepare($query);
$statement->execute([$instituts_id, $username]);
$temp = $statement->fetchColumn();

// Mitarbeiter/in am Heimatinstitut des Seminars
if (!$temp && $sem_id) {
    $query = "SELECT Institut_id
              FROM seminare AS s
              LEFT JOIN user_inst AS ui USING (Institut_id)
              LEFT JOIN auth_user_md5 AS aum USING (user_id)
              WHERE s.Seminar_id = ? AND aum.username = ?
                AND ui.inst_perms = 'dozent' AND {$ext_vis_query}";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id, $username]);
    $temp = $statement->fetchColumn();

    if ($temp) {
        $instituts_id = $temp;
    }
}

// an beteiligtem Institut Dozent(in)
if (!$temp && $sem_id) {
    $query = "SELECT si.institut_id
              FROM seminare AS s
              LEFT JOIN seminar_inst AS si ON (s.Seminar_id = si.seminar_id)
              LEFT JOIN user_inst AS ui ON (si.institut_id = ui.Institut_id)
              LEFT JOIN auth_user_md5 AS aum USING (user_id)
              WHERE s.Seminar_id = ? AND si.institut_id != ?
                AND ui.inst_perms = 'dozent' AND aum.username = ? AND {$ext_vis_query}";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id, $instituts_id, $username]);
    $temp = $statement->fetchColumn();

    if ($temp) {
        $instituts_id = $temp;
    }
}

if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
    $nameformat = 'full';
}

// ist zwar global Dozent, aber an keinem Institut eingetragen
if (!$temp && $sem_id) {
    $query = "SELECT aum.*, {$GLOBALS['_fullname_sql'][$nameformat]} AS fullname
              FROM auth_user_md5 AS aum
              LEFT JOIN user_info USING (user_id)
              LEFT JOIN seminar_user su USING (user_id)
              WHERE username = ? AND perms = 'dozent' AND su.seminar_id = ?
                AND su.status = 'dozent' AND {$ext_vis_query}";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$username, $sem_id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
} else {
    $base_query = "SELECT i.Institut_id, i.Name, i.Strasse, i.Plz, i.url, ui.*, aum.*,
                          {$GLOBALS['_fullname_sql'][$nameformat]} AS fullname,
                          uin.user_id, uin.lebenslauf, uin.publi, uin.schwerp, uin.Home
                   FROM Institute AS i
                   LEFT JOIN user_inst AS ui USING (Institut_id)
                   LEFT JOIN auth_user_md5 AS aum USING (user_id)
                   LEFT JOIN user_info AS uin USING (user_id)
                   WHERE ui.inst_perms IN ('autor', 'tutor', 'dozent')
                     AND {$ext_vis_query} AND ";

    if ($this->config->getValue('Contact', 'defaultadr')) {
        $query  = $base_query;
        $query .= "aum.username = ? AND ui.externdefault = 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$username]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $query  = $base_query;
            $query .= "aum.username = ? AND i.Institut_id = ? AND ui.inst_perms IN ('autor', 'tutor', 'dozent')";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$username, $instituts_id]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        $query  = $base_query;
        $query .= "aum.username = ? AND i.Institut_id = ? AND ui.inst_perms IN ('autor', 'tutor', 'dozent')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$username, $instituts_id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
    }
}

if (!$row) {
    die;
}

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

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";

$studip_link = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/settings/account';
$studip_link .= "?username=$username&login=yes";
if ($this->config->getValue("Main", "studiplink") == "top") {
    $args = ["width" => "100%", "height" => "40", "link" => $studip_link];
    echo "<tr><td width=\"100%\">\n";
    $this->elements["StudipLink"]->printout($args);
    echo "</td></tr>";
}

// generic data fields
if ($generic_datafields = $this->config->getValue("Main", "genericdatafields")) {
//  $datafields_obj =& new DataFields($row['user_id']);
    $fieldEntries = DataFieldEntry::getDataFieldEntries($row['user_id']);
//  $datafields = $datafields_obj->getLocalFields($row['user_id']);
}

$order = $this->config->getValue("Main", "order");
foreach ($order as $position) {

    $data_field = $this->data_fields["content"][$position];

    if ($visible_content[$position]) {
        switch ($data_field) {
            case 'lebenslauf' :
            case 'schwerp' :
            case 'publi' :
                if ($row[$data_field] != '' && Visibility::verify($data_field, $row['user_id'])) {
                    echo "<tr><td width=\"100%\">\n";
                    echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
                    echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
                    echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
                    echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
                    echo $aliases_content[$position] . "</font></td></tr>\n";
                    echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
                    echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
                    echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
                    echo formatReady($row[$data_field], TRUE, TRUE);
                    echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
                }
                break;
            case "news" :
            case "termine" :
                if (Visibility::verify($data_field, $row['user_id'])) {
                    $data_field($this, $row, $aliases_content[$position], $text_div, $text_div_end);
                }
                break;
            case "kategorien" :
            case "lehre" :
            case "head" :
                $data_field($this, $row, $aliases_content[$position], $text_div, $text_div_end);
                break;
            /*
            case 'literature' :
                $literature_content = $this->elements['LitList']->getContent(NULL);
                literature($this, $literature_content, $aliases_content[$position], $text_div, $text_div_end);
                break;
            */
            // generic data fields
            default :
                // include generic datafields
                if (isset($fieldEntries[$data_field]) && is_object($fieldEntries[$data_field]) && $fieldEntries[$data_field]->getDisplayValue()) {
                    echo "<tr><td width=\"100%\">\n";
                    echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
                    echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
                    echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
                    echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
                    echo $aliases_content[$position] . "</font></td></tr>\n";
                    echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
                    echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
                    echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
                    echo $fieldEntries[$data_field]->getDisplayValue();
                    echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
                }
        }
    }
}

if ($this->config->getValue("Main", "studiplink") == "bottom") {
    $args = ["width" => "100%", "height" => "40", "link" => $studip_link];
    echo "<tr><td width=\"100%\">\n";
    $this->elements["StudipLink"]->printout($args);
    echo "</td></tr>";
}

echo "</table>\n";

function news (&$module, $row, $alias_content, $text_div, $text_div_end)
{
    if (Visibility::verify('news', $row['user_id'])) {
        if ($margin = $module->config->getValue("TableParagraphSubHeadline", "margin")) {
            $subheadline_div = "<div style=\"margin-left:$margin;\">";
            $subheadline_div_end = "</div>";
        }
        else {
            $subheadline_div = "";
            $subheadline_div_end = "";
        }

        $query = "SELECT topic, body
                  FROM news_range AS nr
                  LEFT JOIN news AS n USING (news_id)
                  WHERE nr.range_id = ? AND user_id = nr.range_id
                    AND UNIX_TIMESTAMP() BETWEEN date AND date + expire";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$row['user_id']]);
        $news = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($news) > 0) {
            echo "<tr><td width=\"100%\">\n";
            echo "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
            echo "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
            echo "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
            echo "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
            echo "$alias_content</font></td></tr>\n";

            foreach ($news as $item) {
                echo "<tr" . $module->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
                echo "<td" . $module->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
                echo $subheadline_div;
                echo "<font" . $module->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
                echo htmlReady($item['topic']);
                echo "</font>$subheadline_div_end</td></tr>\n";
                echo "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
                list ($content, $admin_msg) = explode("<admin_msg>", $item['body']);
                echo "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";
                echo "$text_div<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
                echo formatReady($content, TRUE, TRUE);
                echo "</font>$text_div_end</td></tr>\n";
            }
            echo "</table>\n</td></tr>\n";
        }
    }
}

function termine (&$module, $row, $alias_content, $text_div, $text_div_end)
{
    if (Config::get()->CALENDAR_ENABLE && Visibility::verify('dates', $row['user_id']) || 1) {
        if ($margin = $module->config->getValue("TableParagraphSubHeadline", "margin")) {
            $subheadline_div = "<div style=\"margin-left:$margin;\">";
            $subheadline_div_end = "</div>";
        }
        else {
            $subheadline_div = "";
            $subheadline_div_end = "";
        }

        $event_list = SingleCalendar::getEventList($row['user_id'], time(),
                time() + 60 * 60 * 24 * 7, null, ['class' => 'PUBLIC'],
                ['CalendarEvent']);
        if (sizeof($event_list)) {
            echo "<tr><td width=\"100%\">\n";
            echo "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
            echo "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
            echo "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
            echo "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
            echo "$alias_content</font></td></tr>\n";

            foreach ($event_list as $event) {
                echo "<tr" . $module->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
                echo "<td" . $module->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
                echo $subheadline_div;
                echo "<font" . $module->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
                echo strftime($module->config->getValue("Main", "dateformat") . " %H:%M", $event->getStart());
                if (date("dmY", $event->getStart()) == date("dmY", $event->getEnd()))
                    echo strftime(" - %H:%M", $event->getEnd());
                else
                    echo strftime(" - " . $module->config->getValue("Main", "dateformat") . " %H:%M", $event->getEnd());
                echo " &nbsp;" . htmlReady($event->getTitle());
                echo "</font>$subheadline_div_end</td></tr>\n";
                if ($event->getDescription()) {
                    echo "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
                    echo "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";
                    echo "$text_div<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
                    echo htmlReady($event->getDescription());
                    echo "</font>$text_div_end</td></tr>\n";
                }
            }
            echo "</table>\n</td></tr>\n";
        }
    }
}

function kategorien (&$module, $row, $alias_content, $text_div, $text_div_end)
{
    $query = "SELECT kategorie_id, name, content
              FROM auth_user_md5 AS aum
              LEFT JOIN kategorien AS k ON (k.range_id = user_id)
              WHERE username = ?
              ORDER BY priority";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$row['username']]);
    while ($category = $statement->fetch(PDO::FETCH_ASSOC)) {
        if (Visibility::verify('kat_'.$category['kategorie_id'], $row['user_id'])) {
            echo "<tr><td width=\"100%\">\n";
            echo "<table" . $module->config->getAttributes("TableParagraph", "table") . ">\n";
            echo "<tr" . $module->config->getAttributes("TableParagraphHeadline", "tr") . ">";
            echo "<td" . $module->config->getAttributes("TableParagraphHeadline", "td") . ">";
            echo "<font" . $module->config->getAttributes("TableParagraphHeadline", "font") . ">";
            echo htmlReady($category['name'], TRUE);
            echo "</font></td></tr>\n";
            echo "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
            echo "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";
            echo "$text_div<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
            echo formatReady($category['content'], TRUE, TRUE);
            echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
        }
    }
}

function lehre (&$module, $row, $alias_content, $text_div, $text_div_end)
{
    global $attr_text_td, $end, $start;

    $all_semester = SemesterData::getAllSemesterData();
    // old hard coded $SEMESTER-array starts with index 1
    array_unshift($all_semester, 0);

    if ($margin = $module->config->getValue('TableParagraphSubHeadline', 'margin')) {
        $subheadline_div     = '<div style="margin-left:' . $margin . ';">';
        $subheadline_div_end = '</div>';
    } else {
        $subheadline_div     = '';
        $subheadline_div_end = '';
    }
    if ($margin = $module->config->getValue('List', 'margin')) {
        $list_div     = '<div style="margin-left:' . $margin . ';">';
        $list_div_end = '</div>';
    } else {
        $list_div     = '';
        $list_div_end = '';
    }

    $types = [];
    $semclass = $module->config->getValue('PersondetailsLectures', 'semclass');
    if (is_null($semclass)) {
        $semclass = [1];
    }
    foreach ($GLOBALS['SEM_TYPE'] as $key => $type) {
        if (in_array($type['class'], $semclass)) {
            $types[] = $key;
        }
    }

    $switch_time = mktime(0, 0, 0, date('m'),
            date('d') + 7 * $module->config->getValue('PersondetailsLectures', 'semswitch'), date('Y'));
    // get current semester
    $current_sem = get_sem_num($switch_time) + 1;

    switch ($module->config->getValue('PersondetailsLectures', 'semstart')) {
        case 'previous':
            if (isset($all_semester[$current_sem - 1])) {
                $current_sem -= 1;
            }
            break;
        case 'next':
            if (isset($all_semester[$current_sem + 1])) {
                $current_sem += 1;
            }
            break;
        case 'current':
            break;
        default:
            if (isset($all_semester[$module->config->getValue('PersondetailsLectures', 'semstart')])) {
                $current_sem = $module->config->getValue('PersondetailsLectures', 'semstart');
            }
    }

    $last_sem = $current_sem + $module->config->getValue('PersondetailsLectures', 'semrange') - 1;
    if ($last_sem < $current_sem) {
        $last_sem = $current_sem;
    }
    if (!isset($all_semester[$last_sem])) {
        $last_sem = count($all_semester) - 1;
    }

    $query = "SELECT *
              FROM seminar_user AS su
              LEFT JOIN seminare AS s USING (seminar_id)
              WHERE user_id = :user_id AND su.status = 'dozent'
                AND start_time <= :beginn AND (:beginn <= start_time + duration_time OR duration_time = -1)
                AND s.status IN (:types) AND s.visible = 1";
    if (Config::get()->IMPORTANT_SEMNUMBER) {
        $query .= " ORDER BY s.`VeranstaltungsNummer`, s.`Name`";
    } else {
        $query .= " ORDER BY s.`Name`";
    }
    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':user_id', $row['user_id']);
    $statement->bindValue(':types', $types ?: '');

    $out = '';
    for (;$current_sem <= $last_sem; $last_sem--) {
        $statement->bindValue(':beginn', $all_semester[$last_sem]['beginn']);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($data) > 0) {
            if (!($module->config->getValue("PersondetailsLectures", "semstart") == "current"
                    && $module->config->getValue("PersondetailsLectures", "semrange") == 1)) {
                $out .= "<tr" . $module->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
                $out .= "<td" . $module->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
                $out .= $subheadline_div;
                $out .= "<font" . $module->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
                $month = date("n", $all_semester[$last_sem]['beginn']);
                if($month > 9) {
                    $out .= $module->config->getValue("PersondetailsLectures", "aliaswise");
                    $out .= date(" Y/", $all_semester[$last_sem]['beginn']) . date("y", $all_semester[$last_sem]['ende']);
                }
                else if($month > 3 && $month < 10) {
                    $out .= $module->config->getValue("PersondetailsLectures", "aliassose");
                    $out .= date(" Y", $all_semester[$last_sem]['beginn']);
                }
                $out .= "</font>$subheadline_div_end</td></tr>\n";
            }

            $out .= "<tr" . $module->config->getAttributes("TableParagraphText", "tr") . ">";
            $out .= "<td" . $module->config->getAttributes("TableParagraphText", "td") . ">";

            if ($module->config->getValue("PersondetailsLectures", "aslist")) {
                $out .= "$list_div<ul" . $module->config->getAttributes("List", "ul") . ">\n";
                foreach ($data as $item) {
                    $out .= "<li" . $module->config->getAttributes("List", "li") . ">";
                    $name = $item['Name'];
                    if (Config::get()->IMPORTANT_SEMNUMBER && $item['VeranstaltungsNummer']) {
                        $name = $item['VeranstaltungsNummer'].' '.$name;
                    }
                    $out .= $module->elements["LinkIntern"]->toString(["module" => "Lecturedetails",
                            "link_args" => "seminar_id=" . $item['Seminar_id'],
                            "content" => htmlReady($name, TRUE)]);
                    if ($item['Untertitel'] != '') {
                        $out .= "<font" . $module->config->getAttributes("TableParagraphText", "font") . "><br>";
                        $out .= htmlReady($item['Untertitel'], TRUE) . "</font>\n";
                    }
                }
                $out .= "</ul>$list_div_end";
            }
            else {
                $out .= $text_div;
                $j = 0;
                foreach ($data as $item) {
                    if ($j) {
                        $out .= '<br>';
                    }
                    $out .= $module->elements['LinkIntern']->toString(['module' => 'Lecturedetails',
                            'link_args' => 'seminar_id=' . $item['Seminar_id'],
                            'content' => htmlReady($item['Name'], TRUE)]);
                    if ($item['Untertitel'] != '') {
                        $out .= "<font" . $module->config->getAttributes("TableParagraphText", "font") . ">";
                        $out .= "<br>" . htmlReady($item['Untertitel'], TRUE) . "</font>\n";
                    }
                    $j = 1;
                }
                $out .= $text_div_end;
            }
            $out .= "</td></tr>\n";
        }
    }

    if ($out) {
        $out_title = '<tr><td width="100%">' . "\n";
        $out_title .= '<table' . $module->config->getAttributes('TableParagraph', 'table') . '>' . "\n";
        $out_title .= '<tr' . $module->config->getAttributes('TableParagraphHeadline', 'tr') . '>';
        $out_title .= '<td' . $module->config->getAttributes('TableParagraphHeadline', 'td') . '>';
        $out_title .= '<font' . $module->config->getAttributes('TableParagraphHeadline', 'font') . '>';
        $out_title .= $alias_content . '</font></td></tr>' . "\n";
        echo $out_title . $out;
        echo '</table>' . "\n";
        echo '</td></tr>' . "\n";
    }
}

function head (&$module, $row, $a) {
    $pic_max_width = $module->config->getValue("PersondetailsHeader", "img_width");
    $pic_max_height = $module->config->getValue("PersondetailsHeader", "img_height");

    // fit size of image
    if ($pic_max_width && $pic_max_height) {
        $pic_size = @getimagesize(Avatar::getAvatar($row['user_id'])->getFilename(Avatar::NORMAL));

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

    $module->config->config["PersondetailsHeader"]["img_width"] = $pic_max_width;
    $module->config->config["PersondetailsHeader"]["img_height"] = $pic_max_height;

    if ($module->config->getValue("Main", "showcontact")
            && $module->config->getValue("Main", "showimage"))
        $colspan = " colspan=\"2\"";
    else
        $colspan = "";

    echo "<tr><td width=\"100%\">\n";
    echo "<table" . $module->config->getAttributes("PersondetailsHeader", "table") . ">\n";

    // display name as headline
    if (!$module->config->getValue('PersondetailsHeader', 'hidename')) {
        echo "<tr" . $module->config->getAttributes("PersondetailsHeader", "tr") . ">";
        echo "<td$colspan width=\"100%\"";
        echo $module->config->getAttributes("PersondetailsHeader", "headlinetd") . ">";
        echo "<font" . $module->config->getAttributes("PersondetailsHeader", "font") . ">";
        echo htmlReady($row['fullname'], TRUE);
        echo "</font></td></tr>\n";
    }

    if ($module->config->getValue("Main", "showimage")
            || $module->config->getValue("Main", "showcontact")) {
        echo "<tr>";
        if ($module->config->getValue("Main", "showcontact")
                && ($module->config->getValue("Main", "showimage") == "right"
                || !$module->config->getValue("Main", "showimage"))) {
                echo "<td" . $module->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
                echo kontakt($module, $row) . "</td>\n";
        }
        if ($module->config->getValue("Main", "showimage")) {
            echo "<td" . $module->config->getAttributes("PersondetailsHeader", "picturetd") . ">";
            $avatar = Avatar::getAvatar($row['user_id']);
            if ($avatar->is_customized() && Visibility::verify('picture', $row['user_id'])) {
                echo "<img src=\"".$avatar->getURL(Avatar::NORMAL) .
                     "\" alt=\"Foto " . htmlReady(trim($row['fullname'])) . "\"";
                echo $module->config->getAttributes("PersondetailsHeader", "img") . "></td>";
            }
            else
                echo "&nbsp;</td>";
        }

        if ($module->config->getValue("Main", "showcontact")
                && $module->config->getValue("Main", "showimage") == "left") {
            echo "<td" . $module->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
            echo kontakt($module, $row) . "</td>\n";
        }

        echo "</tr>\n";
        if ($module->config->getValue('Main', 'showcontact')
                && $module->config->getValue('Contact', 'separatelinks')) {
            echo "<tr><td";
            if ($module->config->getValue('Main', 'showimage'))
                echo ' colspan="2"';
            echo $module->config->getAttributes('PersondetailsHeader', 'contacttd') . ">\n";
            echo kontakt($module, $row, TRUE);
            echo "</td></tr>\n";
        }
    }

    echo  "</table>\n</td></tr>\n";
}

function kontakt ($module, $row, $separate = FALSE) {
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
            $out .= "<br><br>" . htmlReady($row['fullname'], TRUE) . "\n";
        if ($module->config->getValue('Contact', 'showinstgroup')) {
            $allgroups = GetAllStatusgruppen($module->config->range_id, $row['user_id']);
            array_walk($allgroups, function(&$v, $k, $user_id) {
                $s = Statusgruppen::find($k);
                $v['role']->name = htmlReady($s->getGenderedName($user_id));
            }, $row['user_id']);
            if ($gruppen = GetRoleNames($allgroups))
                $out .= "<br>" . htmlReady(join(", ", array_values($gruppen)));
        }
        // display name of institution (as link)
        if ($row['Name']) {
            $br_out = "";
            if ($module->config->getValue("Contact", "hideinstname") != '1') {
                if ($module->config->getValue("Contact", "hideinstname") == 'link' && $row['url']) {
                    $url = htmlReady(trim($row['url']));
                    if (!mb_stristr($url, "http://"))
                        $url = "http://$url";
                    $out .= "<br><br><a href=\"$url\" target=\"_blank\" rel=\"noopener noreferrer\">";
                    $out .= htmlReady($row['Name'], TRUE) . "</a><br>";
                }
                else
                    $out .= "<br><br>" . htmlReady($row['Name'], TRUE) . "<br>";
            }
            if ($module->config->getValue("Contact", "adradd"))
                $out .= "<br>" . $module->config->getValue("Contact", "adradd");
        }

        $out .= "<br>";
        if ($row['Strasse']) {
            $out .= "<br>" . htmlReady($row['Strasse'], TRUE);
            if($row['Plz'])
            $out .= "<br>" . htmlReady($row['Plz'], TRUE);
        }
      $out .= "<br><br></font></td></tr>\n";
    }
    $order = $module->config->getValue("Contact", "order");
    $visible = $module->config->getValue("Contact", "visible");
    $alias_contact = $module->config->getValue("Contact", "aliases");
    foreach ($order as $position) {
        $data_field = $module->data_fields["contact"][$position];
        if (!$visible[$position] || !$row[$data_field])
            continue;
        switch ($data_field) {
            case 'Email' :
                if ($separate || !$module->config->getValue('Contact', 'separatelinks')) {
                    $email_address = get_visible_email($row['user_id']);
                    $out .= "<tr$attr_tr>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fonttitle>";
                    $out .= $alias_contact[$position] . "</font></td>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fontcontent>";
                    $mail = trim(htmlReady($email_address));
                    $out .= "<a href=\"mailto:$mail\">$mail</a>";
                }
                break;
            case 'Home' :
                if (($separate || !$module->config->getValue('Contact', 'separatelinks')) &&
                       true || Visibility::verify('homepage', $row['user_id'])) {
                    $out .= "<tr$attr_tr>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fonttitle>";
                    $out .= $alias_contact[$position] . "</font></td>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fontcontent>";
                    $out .= formatLinks($row['Home']);
                }
                break;
            default:
                if (!$separate) {
                    $out .= "<tr$attr_tr>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fonttitle>";
                    $out .= $alias_contact[$position] . "</font></td>";
                    $out .= "<td$attr_td>";
                    $out .= "<font$attr_fontcontent>";
                    $out .= htmlReady($row[$data_field], TRUE);
                }
        }
        if ($row[$data_field])
            $out .= "</font></td></tr>\n";
    }
    $out .= "</table>\n";

    return $out;
}
