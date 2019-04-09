<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* RoomGroups.class.php
*
* class for a grouping of rooms
*
*
* @author       André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module        RoomGroups.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RoomGroups.class.php
//
// Copyright (C) 2005 André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
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

class RoomGroups {

    private static $room_group_object;
    private $groups = [];

    public static function GetInstance($refresh_cache = false){
        if ($refresh_cache){
            self::$room_group_object = null;
        }
        if (is_object(self::$room_group_object)){
            return self::$room_group_object;
        } else {
            self::$room_group_object = new RoomGroups();
            return self::$room_group_object;
        }
    }

    function __construct(){
        $this->createConfigGroups();
        if (get_config('RESOURCES_ENABLE_VIRTUAL_ROOM_GROUPS')){
            $this->createVirtualGroups();
        }
    }

    function createConfigGroups(){
        @include "config_room_groups.inc.php";
        if (is_array($room_groups)){
            $room_list = new ResourcesUserRoomsList($GLOBALS['user']->id, false, false, true);
            if ($room_list->numberOfRooms()){
                $my_rooms = array_keys($room_list->getRooms());
                foreach ($room_groups as $key => $value){
                    $rooms = array_intersect($value['rooms'], $my_rooms);
                    if (count($rooms)){
                        $this->groups[] = ['name' => $value['name'], 'resources' => $rooms];
                    }
                }
            }
        }
    }

    function createVirtualGroups(){
        $db = DBManager::get();
        $room_list = new ResourcesUserRoomsList($GLOBALS['user']->id, false, false, true);
        $res_obj = ResourceObject::Factory();
        $offset = count($this->groups);
        if ($room_list->numberOfRooms()){
            $rs = $db->query("SELECT parent_id,resource_id 
                FROM resources_objects 
                WHERE resource_id IN('"
                . join("','", array_keys($room_list->getRooms()))."') ORDER BY name");
            foreach($rs->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP) as $parent_id => $resource_ids){
                if (is_array($resource_ids) && count($resource_ids)){
                    $res_obj->restore($parent_id);
                    $this->groups[$offset]['name'] = $res_obj->getPathToString(true);
                    foreach ($resource_ids as $resource_id){
                        $this->groups[$offset]['resources'][] = $resource_id;
                    }
                    ++$offset;
                }
            }
            if (count($this->groups)) {
                usort($this->groups, function($a, $b) { return strnatcasecmp($a["name"], $b["name"]);});
            }
        }
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
}
?>
