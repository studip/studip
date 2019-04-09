<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO

/**
* class to print out the range tree
*
* This class prints out a html representation of the tree for the "extern modules"
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package  
*/
class ExternRangeLectureTree {

    
    var $tree;
    var $config;
    var $param;
    var $root_id;
    
    /**
    * constructor
    *
    * @access public
    */
    function __construct(&$config, $start_item_id, $sem_number = false, $sem_status = false)
    {
        $this->config = $config;

        $query = "SELECT item_id FROM range_tree WHERE studip_object_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->config->range_id]);
        $this->root_id = $statement->fetchColumn();

        $this->start_item_id = $start_item_id ?: $this->root_id;
        $args = NULL;
        if ($sem_number !== false) {
            $args['sem_number'] = $sem_number;
        }
        if ($sem_status !== false) {
            $args['sem_status'] =  $sem_status;
        }
        $this->param = "range_id={$this->config->range_id}&module=Rangelecturetree&config_id={$this->config->id}&";
        
        $this->tree = TreeAbstract::GetInstance("StudipRangeTree",$args);
    }
    
    function showSemRangeTree () {
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
                echo ">" . htmlReady($link_text) . "</font></a>";
            }
            echo "</td></tr></table>\n";
        }
    }
    
    function showContent ($item_id) {
        echo "<table" . $this->config->getAttributes("RangeTreeLevelName", "table");
        echo ">\n<tr><td" . $this->config->getAttributes("RangeTreeLevelName", "td") . ">";
        echo "<font" . $this->config->getAttributes("RangeTreeLevelName", "font") . ">";
        $alias_names = $this->config->getValue("RangeTreeLevelName", "aliases");
        $range_object = RangeTreeObject::GetInstance($item_id);
        if ($range_object->item_data['type'])
            $name = $alias_names[$range_object->item_data['type_num'] - 1] . " ";
        $name .= $range_object->item_data['name'];
        echo htmlReady($name) ."</font></td></tr>\n</table>\n";
        if (is_array($range_object->item_data_mapping)) {
            echo "</td></tr><tr><td>\n";
            echo "<table" . $this->config->getAttributes("RangeTreeLevelContent", "table");
            echo ">\n<tr><td" . $this->config->getAttributes("RangeTreeLevelContent", "td") . ">";
            $alias_mapping = $this->config->getValue("RangeTreeLevelContent", "mapping");
            $aliases = $this->config->getValue("RangeTreeLevelContent", "aliases");
            foreach ($alias_mapping as $position => $key) {
                if ($range_object->item_data[$key]) {
                    echo "<font" . $this->config->getAttributes("RangeTreeLevelContent", "fontalias") . ">";
                    echo htmlReady($aliases[$position]) . "&nbsp;</font>";
                    echo "<font" . $this->config->getAttributes("RangeTreeLevelContent", "fontdata") . ">";
                    echo formatLinks($range_object->item_data[$key]) . "&nbsp; </font>";
                }
            }
            echo "</td></tr></table>\n";
        }
    }
    
    function getSemPath () {
        $delimiter = $this->config->getValue("TreePath", "delimiter");
        $attributes_a = $this->config->getAttributes("TreePath", "a");
        $ret = "<table width=\"100%\"";
        $ret .= $this->config->getAttributes("TreePath", "table") . "><tr>\n";
        $ret .= "<td". $this->config->getAttributes("TreePath", "td") . ">";
        $ret .= "<font". $this->config->getAttributes("TreePath", "td") . ">";
        $parents = $this->tree->getParents($this->start_item_id);
        $parents_root = $this->tree->getParents($this->root_id);
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
            
    
    
    function getSelf ($param = "", $with_start_item = true) {
        if ($param)
            $url = $_SERVER['PHP_SELF'] . (($with_start_item) ? "?start_item_id=" . $this->start_item_id . "&" : "?") . $param ;
        else
            $url = $_SERVER['PHP_SELF'] . (($with_start_item) ? "?start_item_id=" . $this->start_item_id : "") ;
        return $url;
    }
}
