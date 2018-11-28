<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter005: TODO
# Lifter007: TODO
# Lifter010: TODO
$js_only = $this->config->getValue("Main", "jsonly");
if (!$js_only)
echo "
<script type=\"text/javascript\">
<!--\n";
echo "var newsticker_max = 0;

function textlist() {
    newsticker_max = textlist.arguments.length;
    for (i = 0; i < newsticker_max; i++)
        this[i] = textlist.arguments[i];
}

newsticker_tl = new textlist(";

$topics = array();
$query = "SELECT news.topic
          FROM news_range
          LEFT JOIN news USING (news_id)
          WHERE range_id LIKE ? AND UNIX_TIMESTAMP() BETWEEN date AND date + expire
          ORDER BY date DESC";
$statement = DBManager::get()->prepare($query);
$statement->execute(array($this->config->range_id));
while ($topic = $statement->fetchColumn()) {
    $topics[] = sprintf("'%s'", addslashes($topic));
}

if (count($topics) == 0) {
    $topics[] = "'" . $this->config->getValue("Main", "nodatatext") . "'";
}
if ($this->config->getValue("Main", "endtext"))
    $topics[] = "'" . $this->config->getValue("Main", "endtext") . "'";

echo implode(", ", $topics) . ")";

echo "
var newsticker_x = 0; newsticker_pos = 0;
var newsticker_l = newsticker_tl[0].length;

function newsticker() {
    document.tickform.tickfield.value = newsticker_tl[newsticker_x].substring(0, newsticker_pos) + \"_\";
    if (newsticker_pos++ == newsticker_l) {
        newsticker_pos = 0; 
        setTimeout(\"newsticker()\", ";
echo $this->config->getValue("Main", "pause");
echo "); 
        if (++newsticker_x == newsticker_max)
            newsticker_x = 0; 
        newsticker_l = newsticker_tl[newsticker_x].length;
    }
    else
        setTimeout(\"newsticker()\", ";
echo ceil(1000 / $this->config->getValue("Main", "frequency"));
echo ");
}\n";
if (!$js_only) {
    echo "//-->
</script>
<form name=\"tickform\">
    <textarea name=\"tickfield\" rows=\"";
    echo $this->config->getValue("Main", "rows") . "\" cols=\"";
    echo $this->config->getValue("Main", "length") . "\" style=\"";
    echo $this->config->getValue("Main", "style") . "\" wrap=\"virtual\">";
    echo $this->config->getValue("Main", "starttext");
    echo "</textarea>\n</form>\n";

    if ($this->config->getValue("Main", "automaticstart"))
        echo "<script type=\"text/javascript\">\n\tnewsticker();\n</script>\n";
}   
?>
