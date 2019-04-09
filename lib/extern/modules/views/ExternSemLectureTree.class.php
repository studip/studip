<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO

class ExternSemLectureTree extends StudipSemTreeViewSimple {
    
    var $config;
    var $param;
    var $root_id;
    
    function __construct(&$config, $start_item_id = "", $sem_number = FALSE)
    {
        $this->config = $config;

        $query = "SELECT sem_tree_id FROM sem_tree WHERE studip_object_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->config->range_id]);
        $this->root_id = $statement->fetchColumn();

        $this->start_item_id = ($start_item_id) ? $start_item_id : $this->root_id;
        $this->param = "range_id={$this->config->range_id}&module=Semlecturetree&config_id={$this->config->id}&";
        
        parent::__construct($this->start_item_id, $sem_number);
    }

    function showSemTree ($start_id = null) {
        echo "\n<table" . $this->config->getAttributes("Main", "table") . ">";
        echo "\n<tr><td>". $this->getSemPath() . "</td></tr>\n<tr><td>";
        $this->showContent($this->start_item_id);
        echo "\n</td></tr>";
        if ($this->tree->getNumKids($this->start_item_id)) {
            echo "\n<tr><td>";
            echo $this->showKids($this->start_item_id);
            echo "\n</td></tr>";
        }
        if ($this->config->getAttributes("TreeBackLink", "image")
                || $this->config->getAttributes("TreeBackLink", "linktext")) {
            echo "\n<tr><td>";
            echo $this->backLink($this->start_item_id) . "\n</td></tr>";
        }
        echo "\n</table>";
    }

    function showKids ($item_id) {
        $num_kids = $this->tree->getNumKids($item_id);
        $kids = $this->tree->getKids($item_id);
        $attributes_a = $this->config->getAttributes("TreeKids", "a");
        $attributes_font = $this->config->getAttributes("TreeKids", "font");
        $attributes_td = $this->config->getAttributes("TreeKids", "td");
        echo "\n<table width=\"100%\" border=\"0\"";
        echo $this->config->getAttributes("TreeKids", "table") . ">";
        for ($i = 0; $i < $num_kids; ++$i){
            $num_entries = $this->tree->getNumEntries($kids[$i],true);
            echo "<tr>\n<td$attributes_td>";
            echo "<a href=\"" .URLHelper::getLink($this->getSelf("{$this->param}start_item_id={$kids[$i]}", false));
            echo "\"$attributes_a><font$attributes_font>";
            echo htmlReady($this->tree->tree_data[$kids[$i]]['name']);
            echo "&nbsp;($num_entries)";
            echo "</font></a>";
            echo "</td></tr>\n";
        }
        echo "</table>\n";
    }
    
    function backLink ($item_id) {
        if ($item_id != $this->root_id){
            echo "<table width=\"100%\" border=\"0\"";
            echo $this->config->getAttributes("TreeBackLink", "table") . ">\n";
            echo "<tr><td" . $this->config->getAttributes("TreeBackLink", "td") . ">";
            if ($image = $this->config->getValue("TreeBackLink", "image")) {
                echo "<a href=\"" .URLHelper::getLink($this->getSelf("{$this->param}start_item_id={$this->tree->tree_data[$item_id]['parent_id']}", false)) . "\">";
                echo "<img src=\"$image\" border=\"0\"></a>&nbsp;";
            }
            if ($link_text = $this->config->getValue("TreeBackLink", "linktext")) {
                echo "<a href=\"" .URLHelper::getLink($this->getSelf("{$this->param}start_item_id={$this->tree->tree_data[$item_id]['parent_id']}", false)) . "\">";
                echo "<font" . $this->config->getAttributes("TreeBackLink", "font");
                echo ">$link_text</font></a>";
            }
            echo "</td></tr></table>\n";
        }
    }

    function showContent ($item_id) {
        echo "<table" . $this->config->getAttributes("TreeLevelName", "table");
        echo ">\n<tr><td" . $this->config->getAttributes("TreeLevelName", "td") . ">";
        echo "<font" . $this->config->getAttributes("TreeLevelName", "font") . ">";
        echo htmlReady($this->tree->tree_data[$item_id]['name']) ."</font></td></tr>\n</table>";
        echo "</td></tr><tr><td>\n";
        echo "<table" . $this->config->getAttributes("TreeLevelContent", "table");
        echo ">\n<tr><td" . $this->config->getAttributes("TreeLevelContent", "td") . ">";
        echo "<font" . $this->config->getAttributes("TreeLevelContent", "font") . ">";
        echo formatReady($this->tree->tree_data[$item_id]['info'], TRUE, TRUE) ."</font></td></tr>\n</table>";
    }

    function getSemPath ($start_id = null) {
        $delimiter = $this->config->getValue("TreePath", "delimiter");
        $attributes_a = $this->config->getAttributes("TreePath", "a");
        $ret = "<table width=\"100%\"";
        $ret .= $this->config->getAttributes("TreePath", "table") . "><tr>\n";
        $ret .= "<td". $this->config->getAttributes("TreePath", "td") . ">";
        $ret .= "<font". $this->config->getAttributes("TreePath", "td") . ">";
        $parents = $this->tree->getParents($this->start_item_id);
        $parents_root[] = $this->tree->getParents($this->root_id);
        if (is_array($parents)) {
            $parents = array_diff($parents, $parents_root);
            while ($parent = array_pop($parents)) {
                $ret .= $delimiter;
                $ret .= "<a href=\"" . URLHelper::getLink($this->getSelf("{$this->param}start_item_id=".$parent,false));
                $ret .= "\"$attributes_a>" . htmlReady($this->tree->tree_data[$parent]["name"]) . "</a>";
            }
        }
        if ($this->start_item_id != $this->root_id)
            $ret .= $delimiter;
        $ret .= htmlReady($this->tree->tree_data[$this->start_item_id]["name"]);
        $ret .= "</font></td></tr></table>\n";
        
        return $ret;
    }
    
}

?>
