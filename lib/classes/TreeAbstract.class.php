<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// TreeAbstract.class.php
// Abstract Base Class to handle in-memory tree structures
//
// Copyright (c) 2002 André Noack <noack@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


/**
* Abstract Base Class to handle in-memory tree structures
*
* This class provides an interface to basic handling of structure of tree structures
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class TreeAbstract {

    /**
    * the name of the root element
    *
    * @access private
    * @var string $root_name
    */
    var $root_name;
    /**
    * object to handle database queries
    *
    * @access private
    * @var object DbView $view
    */
    var $view;
    /**
    * array containing all tree items
    *
    * associative array, key is an unique identifier (eg primary key from DB table)
    * value is another assoc. array containing the other fieldname/fieldvalue pairs
    * these fieldnames must be used :
    * parent_id, name, priority
    * @access public
    * @var array    $tree_data
    */
    var $tree_data = [];
    /**
    * array containing the direct childs of all items
    *
    * assoc. array, key is one from $tree_data, value is numeric array with keys from childs
    * @access private
    * @var array    $tree_childs
    */
    var $tree_childs = [];

    /**
    * array containing the number of direct childs of all items
    *
    * assoc. array, key is one from $tree_data
    * @access private
    * @var array    $tree_num_childs
    */
    var $tree_num_childs = [];

    var $index_offset = 0;

    /**
    * static method used to ensure that only one instance exists
    *
    * use this method if you need a reference to the tree object <br>
    * usage: <pre>$my_tree = StudipRangeTree::GetInstance("name_of_tree_class")</pre>
    *
    * @param    string  $class_name     the name of the used tree_class
    * @param    mixed   $args           argumentlist passed to the constructor in the tree_class (if needed)
    * @return   mixed   always an object, type is one of AbstractTree s childclasses
    */
    public static function GetInstance($class_name, $args = null, $invalidate_cache = false)
    {
        static $tree_instance;

        if ($args){
            $class_hash = $class_name . "_" . md5(serialize($args));
        } elseif ($args === false && is_array($tree_instance)){
            foreach ($tree_instance as $key => $value){
                $tmp_name = explode("_",$key);
                if ($tmp_name[0] == $class_name){
                    $class_hash = $key;
                    break;
                }
            }
            if (!$class_hash){
                $class_hash = $class_name;
            }
        } else {
            $class_hash = $class_name;
        }
        if (!is_object($tree_instance[$class_hash]) || $invalidate_cache){
            $tree_instance[$class_hash] = new $class_name($args);
        }

        return $tree_instance[$class_hash];
    }

    /**
    * constructor
    *
    * do not use directly, call &GetInstance()
    */
    protected function __construct()
    {
        $this->view = new DbView();
        $this->init();
    }

    /**
    * initializes the tree
    *
    * stores all tree items in array $tree_data
    * must be overriden
    */
    public function init()
    {
        $this->tree_childs = [];
        $this->tree_num_childs = [];
        $this->tree_data = [];
        $this->index_offset = 0;
        $this->tree_data['root'] = ['parent_id' => null, 'name' => &$this->root_name, 'index' => 0];
    }

    /**
    * store one item in tree_data array
    *
    * store one item in tree_data array
    *
    * @param    string  $item_id
    * @param    string  $parent_id
    * @param    string  $name
    * @param    integer $priority
    *
    */
    public function storeItem($item_id,$parent_id,$name,$priority)
    {
        $this->tree_data[$item_id]["parent_id"] = $parent_id;
        $this->tree_data[$item_id]["priority"] = $priority;
        $this->tree_data[$item_id]["name"] = $name;
        $this->tree_childs[$parent_id][] = $item_id;
        ++$this->tree_num_childs[$parent_id];
        return;
    }

    /**
    * build an index for sorting purpose
    *
    * build an index for sorting purpose
    *
    * @param    string  $item_id
    *
    */
    public function buildIndex($item_id = false)
    {
        if ($item_id === false && $this->index_offset > 0) {
            return;
        }
        if (!$item_id) {
            $item_id = "root";
        }
        $this->tree_data[$item_id]['index'] = $this->index_offset;
        ++$this->index_offset;
        if (($num_kids = $this->getNumKids($item_id))) {
            for($i = 0; $i < $num_kids; ++$i){
                $this->buildIndex($this->tree_childs[$item_id][$i]);
            }
        }
        return;
    }

    /**
    * returns all direct kids
    *
    * @param    string  $item_id
    * @return   array
    */
    public function getKids($item_id)
    {
        return (is_array($this->tree_childs[$item_id])) ? $this->tree_childs[$item_id] : null;
    }

    /**
    * returns the number of all direct kids
    *
    * @param    string  $item_id
    * @param    bool    $in_recursion
    * @return   int
    */
    public function getNumKids($item_id)
    {
        if(!isset($this->tree_num_childs[$item_id])){
            $this->tree_num_childs[$item_id] = (is_array($this->tree_childs[$item_id])) ? count($this->tree_childs[$item_id]) : 0;
        }
        return $this->tree_num_childs[$item_id];
    }

    /**
    * returns all direct kids and kids of kids and so on...
    *
    * @param    string  $item_id
    * @param    bool    $in_recursion   only used in recursion
    * @return   array
    */
    public function getKidsKids($item_id, $in_recursion = false)
    {
        static $kidskids;
        if (!$kidskids || !$in_recursion){
            $kidskids = [];
        }
        $num_kids = $this->getNumKids($item_id);
        if ($num_kids){
            $kids = $this->getKids($item_id);
            $kidskids = array_merge((array)$kidskids, (array)$kids);
            for ($i = 0; $i < $num_kids; ++$i){
                $this->getKidsKids($kids[$i],true);
            }
        }
        return (!$in_recursion) ? $kidskids : null;
    }

    /**
    * returns the number of all kids and kidskids...
    *
    * @param    string  $item_id
    * @param    bool    $in_recursion
    * @return   int
    */
    public function getNumKidsKids($item_id, $in_recursion = false)
    {
        static $num_kidskids;
        if (!$num_kidskids || !$in_recursion){
            $num_kidskids = 0;
        }
        $num_kids = $this->getNumKids($item_id);
        if ($num_kids){
            $kids = $this->getKids($item_id);
            $num_kidskids += $num_kids;
            for ($i = 0; $i < $num_kids; ++$i){
                $this->getNumKidsKids($kids[$i],true);
            }
        }
        return (!$in_recursion) ? $num_kidskids : null;
    }

    /**
    * checks if item is the last kid
    *
    * @param    string  $item_id
    * @return   boolean
    */
    public function isLastKid($item_id)
    {
        $parent_id = $this->tree_data[$item_id]['parent_id'];
        $num_kids = $this->getNumKids($parent_id);
        if (!$parent_id || !$num_kids) {
            return false;
        }
        return $this->tree_childs[$parent_id][$num_kids-1] == $item_id;
    }

    /**
    * checks if item is the first kid
    *
    * @param    string  $item_id
    * @return   boolean
    */
    public function isFirstKid($item_id)
    {
        $parent_id = $this->tree_data[$item_id]['parent_id'];
        $num_kids = $this->getNumKids($parent_id);
        if (!$parent_id || !$num_kids) {
            return false;
        }
        return $this->tree_childs[$parent_id][0] == $item_id;
    }

    /**
    * checks if given item is a kid or kidkid...of given ancestor
    *
    * checks if given item is a kid or kidkid...of given ancestor
    *
    * @param    string  $ancestor_id
    * @param    string  $item_id
    * @return   boolean
    */
    public function isChildOf($ancestor_id,$item_id)
    {
        return in_array($item_id,$this->getKidsKids($ancestor_id));
    }

    /**
    * checks if item has one or more kids
    *
    * @param    string  $item_id
    * @return   boolean
    */
    public function hasKids($item_id)
    {
        return $this->getNumKids($item_id) > 0;
    }

    /**
    * Returns tree path
    *
    * returns a string with the item and all parents separated with a slash
    *
    * @param    string  $item_id
    * @return   string
    */
    public function getItemPath($item_id)
    {
        if (!$this->tree_data[$item_id]) {
            return false;
        }

        $path = $this->tree_data[$item_id]['name'];
        while($item_id && $item_id !== 'root') {
            $item_id = $this->tree_data[$item_id]['parent_id'];
            $path = $this->tree_data[$item_id]['name'] . " / " . $path;
        }
        return $path;
    }

    /**
    * Returns tree path as array of item_id s
    *
    * returns an array containing all parents of given item
    *
    * @param    string  $item_id
    * @return   array
    */
    public function getParents($item_id)
    {
        if (!$this->tree_data[$item_id]) {
            return [];
        }

        $result = [];
        while ($item_id && $item_id !== 'root') {
            $item_id   = $this->tree_data[$item_id]['parent_id'];
            $result[] = $item_id;
        }
        return $result;
    }

    public function getShortPath($item_id, $length = null, $delimeter = ">", $offset = 0)
    {
        if (!$this->tree_data[$item_id] || $item_id === 'root') {
            return false;
        }
        $parents = array_reverse($this->getParents($item_id));
        array_shift($parents);
        array_push($parents, $item_id);
        $that = $this;
        $parents_names = array_map(function($i) use ($that) {return $that->tree_data[$i]['name'];}, array_slice($parents, $offset, $length ? $length : null));
        return join(" $delimeter ", $parents_names);
    }

    /**
    * Returns the maximum priority value from a parents child
    *
    * @param    string  $parent_id
    * @return   int
    */
    public function getMaxPriority($parent_id)
    {
        $children = $this->getKids($parent_id);
        $last = $this->getNumKids($parent_id) - 1;
        return (int) $this->tree_data[$children[$last]]['priority'];
    }

    public function getNumEntries($item_id, $num_entries_from_kids = false)
    {
        if (!$num_entries_from_kids || !$this->hasKids($item_id)){
                return $this->tree_data[$item_id]["entries"];
        } else {
            return $this->getNumEntriesKids($item_id);
        }
    }

    public function getNumEntriesKids($item_id, $in_recursion = false)
    {
        static $num_entries;
        if (!$in_recursion){
            $num_entries = 0;
        }
        $num_entries += $this->tree_data[$item_id]["entries"];
        $num_kids = $this->getNumKids($item_id);
        if ($num_kids){
            $kids = $this->getKids($item_id);
            for ($i = 0; $i < $num_kids; ++$i){
                $this->getNumEntriesKids($kids[$i],true);
            }
        }
        return (!$in_recursion) ? $num_entries : null;
    }

    public function getValue($item_id, $field)
    {
        return isset($this->tree_data[$item_id][$field])
             ? $this->tree_data[$item_id][$field]
             : null;
    }
}
