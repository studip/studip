<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

$error_message = "";

$nameformat = $this->config->getValue("Main", "nameformat");

if ($nameformat == "last") {
    $query = "SELECT n.*, aum.Nachname AS name, aum.username FROM news_range nr LEFT JOIN ";
    $query .= "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) ";
    $query .= "WHERE range_id='{$this->config->range_id}'";
}
else {
    global $_fullname_sql;
    $query = "SELECT n.*, {$_fullname_sql[$nameformat]} AS name, ";
    $query .= "aum.username FROM news_range nr LEFT JOIN ";
    $query .= "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) ";
    $query .= "LEFT JOIN user_info USING(user_id) ";
    $query .= "WHERE range_id='{$this->config->range_id}'";
}

$now = time();
$data = NULL;
for ($n = 0; $n < 3; $n++) {
    $content_data[$n]["date"] = $now - 600000 * ($n + 1);
    $content_data[$n]["topic"] = sprintf(_("Aktuelle Nachricht Nr. %s"), $n + 1);
    $content_data[$n]["body"] = str_repeat(sprintf(_("Beschreibung der Nachricht Nr. %s"), $n + 1) . " ", 10);
    switch ($nameformat) {
        case "no_title_short" :
            $content_data[$n]["fullname"] = _("Meyer, P.");
            break;
        case "no_title" :
            $content_data[$n]["fullname"] = _("Peter Meyer");
            break;
        case "no_title_rev" :
            $content_data[$n]["fullname"] = _("Meyer Peter");
            break;
        case "full" :
            $content_data[$n]["fullname"] = _("Dr. Peter Meyer");
            break;
        case "full_rev" :
            $content_data[$n]["fullname"] = _("Meyer, Peter, Dr.");
            break;
        case "last" :
            $content_data[$n]["fullname"] = _("Meyer");
            break;
        default :
            $content_data[$n]["fullname"] = _("Peter Meyer");
            break;
    }
}

if ($this->config->getValue("Main", "studiplink")) {
    echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ";
    echo "width=\"" . $this->config->getValue("TableHeader", "table_width");
    echo "\" align=\"" . $this->config->getValue("TableHeader", "table_align") . "\">\n";

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

echo "<tr" . $this->config->getAttributes("TableHeadRow", "tr") . ">\n";

$rf_news = $this->config->getValue("Main", "order");
$width = $this->config->getValue("Main", "width");
if ($this->config->getValue("TableHeader", "width_pp") == "PERCENT")
    $percent = "%";
$aliases = $this->config->getValue("Main", "aliases");
$visible = $this->config->getValue("Main", "visible");

$set_1 = $this->config->getAttributes("TableHeadrow", "th");
$set_2 = $this->config->getAttributes("TableHeadrow", "th", TRUE);
$zebra = $this->config->getValue("TableHeadrow", "th_zebrath_");

$i = 0;
foreach($rf_news as $spalte){
    if ($visible[$spalte]) {
    
        // "zebra-effect" in head-row
        if ($zebra) {
            if ($i % 2)
                $set = $set_2;
            else
                $set = $set_1;
        }
        else
            $set = $set_1;
        
        echo "<th$set width=\"" . $width[$spalte] . "$percent\">";
        
        if($aliases[$spalte] == "")
            echo "<b>&nbsp;</b>\n";
        else 
            echo "<font" . $this->config->getAttributes("TableHeadrow", "font") . ">" . $aliases[$spalte] . "</font>\n";
    
        echo "</th>\n";
        $i++;
    }
}
echo "</tr>\n";

$dateform = $this->config->getValue("Main", "dateformat");
$attr_a = $this->config->getAttributes("LinkInternSimple", "a");
$attr_font = $this->config->getAttributes("TableRow", "font");
$attr_div_topic = $this->config->getAttributes("ContentNews", "divtopic");
$attr_div_body = $this->config->getAttributes("ContentNews", "divbody");
$attr_font_topic = $this->config->getAttributes("ContentNews", "fonttopic");
$attr_font_body = $this->config->getAttributes("ContentNews", "fontbody");

$set_1 = $this->config->getAttributes("TableRow", "td");
$set_2 = $this->config->getAttributes("TableRow", "td", TRUE);
$zebra = $this->config->getValue("TableRow", "td_zebratd_");
$show_date_author = $this->config->getValue("Main", "showdateauthor");
$not_author_link = $this->config->getValue("Main", "notauthorlink");

foreach ($content_data as $dat) {
    list ($content,$admin_msg) = explode("<admin_msg>",$dat["body"]);
    if ($admin_msg) 
        $content.="\n--%%{$admin_msg}%%--";
    
    $data['date'] = $attr_font ? "<font$attr_font>" : '';
    
    if ($show_date_author != 'date') {
        if ($not_author_link)
            $author_name = $dat["fullname"];
        else
            $author_name = sprintf("<a href=\"\"%s>%s</a>", $attr_a, $dat["fullname"]);
    }
    
    switch ($show_date_author) {
        case 'date' :
            $data['date'] .= strftime($dateform, $dat["date"]);
            break;
        case 'author' :
            $data['date'] .= $author_name;
            break;
        default:
            $data['date'] .= strftime($dateform, $dat["date"]) . '<br>' . $author_name;
    }
    
    $data['date'] .= $attr_font ? '</font>' : '';
    
    $data['topic'] = sprintf("<div%s><font%s>%s</font></div><div%s><font%s>%s</font></div>",
                                                $attr_div_topic, $attr_font_topic,
                                                $dat["topic"], $attr_div_body,
                                                $attr_font_body, $content);
    
    // "horizontal zebra"
    if ($zebra == "HORIZONTAL") {
        if ($i % 2)
            $set = $set_2;
        else
            $set = $set_1;
    }
    else
        $set = $set_1;
    
    echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
    
    $j = 0;
    foreach($rf_news as $spalte){
        
        // "vertical zebra"
        if ($zebra == "VERTICAL") {
            if ($j % 2)
                $set = $set_2;
            else
                $set = $set_1;
        }
    
        if ($visible[$spalte]) {
            if($data[$this->data_fields[$spalte]] == "")
                echo "<td$set>&nbsp;</td>\n";
            else
                echo "<td$set>" . $data[$this->data_fields[$spalte]] . "</td>\n";
            $j++;
        }
    }
    
    echo "</tr>\n";
    $i++;
}

echo "\n</table>";

if ($this->config->getValue("Main", "studiplink")) {
    if ($this->config->getValue("Main", "studiplink") == "bottom") {
        $args = ["width" => "100%", "height" => "40", "link" => ""];
        echo "</td></tr>\n<tr><td width=\"100%\">\n";
        $this->elements["StudipLink"]->printout($args);
    }
    echo "</td></tr></table>\n";
}

?>
