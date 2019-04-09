<?
# Lifter010: TODO
/**
* ResourcesOpenObjectGroups.class.php
* 
* class for a grouping of ressources
* 
*
* @author       André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version      
* @access       public
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesOpenObjectGroups.class.php
// 
// Copyright (C) 2008 André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
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

class ResourcesOpenObjectGroups {
    
    private $groups = [];
    private $range_type;
    private $range_name;
    private static $instances;
    
    public static function GetInstance($range_id, $refresh_cache = false){
        if ($refresh_cache){
            self::$instances[$range_id] = null;
        }
        if (is_object(self::$instances[$range_id])){
            return self::$instances[$range_id];
        } else {
            self::$instances[$range_id] = new ResourcesOpenObjectGroups($range_id);
            return self::$instances[$range_id];
        }
    }
    
    function __construct($range_id){
        $this->range_id = $range_id;
        $this->range_type = get_object_type($range_id);
        $object_name = get_object_name($range_id, $this->range_type);
        $this->range_name = $object_name['type'] . ": " . $object_name['name'];
        $this->createGroups();
    }
    
    function createGroups(){
        $resources = [];
        $db = DBManager::get();
        $st = $db->prepare("SELECT resource_id
            FROM resources_objects
            WHERE owner_id = ?
            UNION DISTINCT
            SELECT resource_id
            FROM resources_user_resources 
            WHERE user_id = ?");
        if($st->execute([$this->range_id, $this->range_id])){
            while($resource_id = $st->fetchColumn()){
                $resources[] = $resource_id;
                $resources = array_merge($resources, $this->getResourcesChildren($resource_id));
            }
        }
        $rs = $db->query(sprintf("SELECT parent_id,resource_id FROM resources_objects
            WHERE resource_id IN('%s') ORDER BY name", join("','", $resources)));
        $res_obj = ResourceObject::Factory();
        $offset = 0;
        foreach($rs->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP) as $parent_id => $resource_ids){
            if (is_array($resource_ids) && count($resource_ids)){
                $res_obj->restore($parent_id);
                $this->groups[$offset]['name'] = $res_obj->getPathToString(true);
                foreach ($resource_ids as $resource_id){
                    $res_obj->restore($resource_id);
                    $this->groups[$offset]['resources'][] = $resource_id;  
                }
                ++$offset;
            }
        }
    }
    
    function getResourcesChildren($resource_id, $in_recursion = false){
        static $resources;
        if(!$in_recursion) $resources = [];
        $rs = DBManager::get()->query("SELECT resource_id FROM resources_objects 
            WHERE parent_id='$resource_id'");
        foreach($rs->fetchAll(PDO::FETCH_COLUMN, 0) as $resource_id){
            $resources[] = $resource_id;
            $this->getResourcesChildren($resource_id, true);
        }
        if(!$in_recursion) return $resources;
    }
    
    function getAllResources(){
        $ret = [];
        foreach($this->groups as $group) $ret = array_merge($ret, $group['resources']);
        return $ret;
    }
    
    function getGroupName($id){
        return (isset($this->groups[$id]) ? $this->groups[$id]['name'] : false);
    }
    
    function getGroupContent($id){
        return (isset($this->groups[$id]) ? $this->groups[$id]['resources'] : []);
    }
    
    function getGroupCount($id){
        return count($this->getGroupContent($id));
    }
    
    function getAvailableGroups(){
        return array_keys($this->groups);
    }
    
    function isGroup($id){
        return array_key_exists($id, $this->groups);
    }
    
    function getRangeName(){
        return $this->range_name;
    }
    
    function getRangeType(){
        return $this->range_type;
    }
}
?>