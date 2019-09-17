<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO

$error_message = "";

// stimmt die übergebene range_id?
$query = "SELECT 1 FROM Institute WHERE Institut_id = ?";
$statement = DBManager::get()->prepare($query);
$statement->execute([$this->config->range_id]);
if (!$statement->fetchColumn()) {
    $error_message = $GLOBALS["EXTERN_ERROR_MESSAGE"];
}

if (!$nameformat = $this->config->getValue("Main", "nameformat"))
    $nameformat = "no_title";
if ($nameformat == 'last') $GLOBALS['_fullname_sql']['last'] = ' Nachname ';

$news =& StudipNews::GetNewsByRange($this->config->range_id, true);
if (!count($news))
    $error_message = $this->config->getValue("Main", "nodatatext");

if ($this->config->getValue("Main", "studiplink")) {
    echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ";
    echo "width=\"" . $this->config->getValue("TableHeader", "table_width");
    echo "\" align=\"" . $this->config->getValue("TableHeader", "table_align") . "\">\n";

    $studip_link = URLHelper::getLink('dispatch.php/institute/overview?again=yes&cid='. $this->config->range_id);
    if ($this->config->getValue("Main", "studiplink") == "top") {
        $args = ["width" => "100%",
        "height" => "40", "link" => $studip_link];
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

$i = 0;
$this->elements["TableHeadrow"]->printout();

// no data to print
if ($error_message) {
    echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
    echo "<td" . $this->config->getAttributes("TableRow", "td") . " colspan=\"$i\">\n";
    echo $error_message;
    echo "</td></tr>\n</table>\n";
}
else {
    $data["data_fields"] = $this->data_fields;
    $dateform = $this->config->getValue("Main", "dateformat");
    $show_date_author = $this->config->getValue("Main", "showdateauthor");
    $not_author_link = $this->config->getValue("Main", "notauthorlink");

    $query = "SELECT COUNT(*)
              FROM Institute AS i
              LEFT JOIN user_inst AS ui USING(Institut_id)
              LEFT JOIN auth_user_md5 AS aum USING(user_id)
              WHERE Institut_id = ? AND user_id = ? AND ui.inst_perms IN ('autor','tutor','dozent')";
    $statement = DBManager::get()->prepare($query);

    foreach($news as $news_id => $news_detail){
        list ($content, $admin_msg) = explode("<admin_msg>", $news_detail["body"]);
        if ($admin_msg) {
            $admin_msg = preg_replace('# \(.*?\)#', '', $admin_msg);
            $content .= "\n--%%{$admin_msg}%%--";
        }

        // Mitarbeiter/in am Institut
        $statement->execute([
            $this->config->range_id,
            $news_detail['user_id']
        ]);
        $institute_user = $statement->fetchColumn() ?: 0;
        $statement->closeCursor();

        // !!! LinkInternSimple is not the type of this element,
        // the type of this element is LinkIntern !!!
        // this is for compatibiliy reasons only
        if ($show_date_author != 'date') {
            if ($not_author_link || !$institute_user)
                $author_name = htmlReady(get_fullname($news_detail["user_id"], $nameformat));
            else
                $author_name = $this->elements["LinkInternSimple"]->toString([
                                        "content" => htmlReady(get_fullname($news_detail["user_id"], $nameformat)),
                                        "link_args" => "username=" . get_username($news_detail['user_id']),
                                        "module" => "Persondetails"]);
        }

        switch ($show_date_author) {
            case 'date' :
                $data["content"]["date"] = strftime($dateform, $news_detail["date"]);
                break;
            case 'author' :
                $data["content"]["date"] = $author_name;
                break;
            default :
                $data["content"]["date"] = strftime($dateform, $news_detail["date"]) . "<br>" . $author_name;
        }

        $data["content"]["topic"] = $this->elements["ContentNews"]->toString(["content" =>
                                    ["topic" => htmlReady($news_detail["topic"]),
                                    "body" => formatReady($content, TRUE, TRUE)]]);

        $this->elements["TableRow"]->printout($data);
    }

    echo "\n</table>";
}
if ($this->config->getValue("Main", "studiplink")) {
    if ($this->config->getValue("Main", "studiplink") == "bottom") {
        $args = ["width" => "100%",
        "height" => "40", "link" => $studip_link];
        echo "</td></tr>\n<tr><td width=\"100%\">\n";
        $this->elements["StudipLink"]->printout($args);
    }
    echo "</td></tr></table>\n";
}
