<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* ResourcesUserRoomsList.class.php
* 
* container for a list of rooms a user has rights for
* 
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       AssignEvent.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesUserRoomsList.class.php
// Containerklasse, die eine Liste der Raeume, auf die ein User Zugriff hat, enthaelt
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

/*****************************************************************************
ResourcesUserRoomsList, creates a list for all resources for one user
/*****************************************************************************/

class ResourcesUserRoomsList {
    var $user_id;       // userId from PhpLib (String)
    var $resources = [];       // the results
    var $return_objects;    // should the complete objects be returned?
    var $only_rooms;    // we can do this stuff for rooms ar for all resources
    
    function CheckUserResource($resource_id, $user_id = false){
        static $resources_list;
        if (!$user_id){
            $user_id = $GLOBALS['auth']->auth['uid'];
        }
        if($GLOBALS['perm']->have_perm('root', $user_id) || getGlobalPerms($user_id) == 'admin'){
            return true;
        }
        if (!isset($resources_list[$user_id])){
            $resources_list[$user_id] = new ResourcesUserRoomsList($user_id, false, false, false);
        }
        return $resources_list[$user_id]->checkResource($resource_id);
    }
    
    // Konstruktor
    function __construct($user_id ='', $sort= TRUE, $return_objects = TRUE, $only_rooms = TRUE) {
        $this->user_id = $user_id;
        if (!$this->user_id)
            $this->user_id = $GLOBALS['user']->id;

        $this->global_perms = getGlobalPerms($this->user_id);
        $this->return_objects = $return_objects;
        $this->only_rooms = $only_rooms;
        $this->restore();
        
        if ($sort) {
            $this->sort();
        }
    }
    
    static function getInstance($user_id, $sort = true, $return_objects = true, $only_rooms = true)
    {
        static $resources_users_rooms_list;
        
        if (!$resources_users_rooms_list[$user_id]) {
            $resources_users_rooms_list[$user_id] = 
                new ResourcesUserRoomsList($user_id, $sort, $return_objects, $only_rooms);
        }
        
        return $resources_users_rooms_list[$user_id];
    }
    
    //public
    function setReturnObjects ($value) {
        $this->return_objects = $value;
    }
    
    //private
    function walkThread ($resource_list)
    {
        if (!count($resource_list)) {
            return;
        }

        $query = "SELECT is_room, resource_id, lockable, resources_objects.name
                  FROM resources_objects
                  LEFT JOIN resources_categories USING (category_id)
                  WHERE parent_id IN (?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$resource_list]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (!$this->only_rooms || ($this->only_rooms && $row['is_room'])) {
                $this->insertResource($row['resource_id'], $row['name'], $row['lockable']);
            }
            $check_childs[] = $row['resource_id'];
        }
        if (is_array($check_childs)){
            $this->walkThread($check_childs);
        }
    }
    
    function insertResource($resource_id, $name, $lockable = false){
        if  (!$lockable || ($lockable && !isLockPeriod(time()))) {
            if ($this->return_objects) {
                $this->resources[$resource_id] = ResourceObject::Factory($resource_id);
            } else {
                $this->resources[$resource_id] = $name;
            }
        }
    }
    
    // private
    function restore()
    {
        global $perm, $user;

        //if perm is root or resources admin, load all rooms/objects
        if (($perm->have_perm ("root", $this->user_id)) || ($this->global_perms == "admin")) { //hier muss auch admin rein!! {
            if ($this->only_rooms) {
                $query = "SELECT resource_id, resources_objects.name
                          FROM resources_categories
                          LEFT JOIN resources_objects USING (category_id)
                          WHERE resources_categories.is_room = 1
                            AND resource_id IS NOT NULL
                          ORDER BY resources_objects.name";
            } else {
                $query = "SELECT resource_id, resources_objects.name
                          FROM resources_objects
                          ORDER BY resources_objects.name";
            }
            $statement = DBManager::get()->query($query);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $this->insertResource($row['resource_id'], $row['name']);
            }
        //if tutor, dozent or admin, load all the rooms of all his administrable objects
        } elseif  ($perm->have_perm ("tutor", $this->user_id)) {
            $my_objects=search_administrable_objects('', $this->user_id);
            $my_objects[$this->user_id]=TRUE;
            $my_objects["all"]=TRUE;
            if (is_array($my_objects) && count($my_objects)){
                $query = "SELECT is_room, resource_id, resources_objects.name, lockable
                          FROM resources_objects
                          LEFT JOIN resources_categories USING (category_id)
                          WHERE owner_id IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    array_keys($my_objects)
                ]);
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    if (!$this->only_rooms || ($this->only_rooms && $row['is_room'])) {
                        $this->insertResource($row['resource_id'], $row['name'], $row['lockable']);
                    }
                    $my_resources[$row['resource_id']] = true;
                }

                $query = "SELECT is_room, resources_user_resources.resource_id, resources_objects.name, lockable
                          FROM resources_user_resources
                          INNER JOIN resources_objects USING (resource_id)
                          LEFT JOIN resources_categories USING (category_id)
                          WHERE resources_user_resources.user_id IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    array_keys($my_objects)
                ]);
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    if (!isset($my_resources[$row['resource_id']])){
                        if (!$this->only_rooms || ($this->only_rooms && $row['is_room'])) {
                            $this->insertResource($row['resource_id'], $row['name'], $row['lockable']);
                        }
                        $my_resources[$row['resource_id']] = true;
                    }
                }
                if (is_array($my_resources)){
                    $this->walkThread(array_keys($my_resources));
                }
            }
        }
        /*
        if (!$perm->have_perm("admin")) {
            $query = "SELECT resource_id FROM resources_objects WHERE owner_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($this->user_id));
            while ($resource_id = $statement->fetchColumn()) {
                $this->walkThread($resource_id);
            }

            $query = "SELECT resource_id FROM resources_user_resources WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($this->user_id));
            while ($resource_id = $statement->fetchColumn()) {
                $this->walkThread($resource_id);
            }
        }
        */
    }
    
    function getRooms() {
        return ($this->resources);
    }
    
    //public
    function numberOfRooms() {
        return sizeof($this->resources);
    }
    
    //public
    function roomsExist() {
        return sizeof($this->resources) > 0;
    }
    
    function checkResource($resource_id){
        return ($resource_id && is_array($this->resources) && isset($this->resources[$resource_id]));
    }
    
    //public
    function next() {
        if (is_array($this->resources))
            if(list($id,$name) = each($this->resources))
                return ["name" => $name, "resource_id" => $id];
        return FALSE;
    }

    //public
    function reset() {
        if (is_array($this->resources))
            reset($this->resources);
    }
    
    
    function sort(){
        if ($this->resources) 
            if ($this->return_objects)
                uasort($this->resources,"cmp_resources");
            else
                asort ($this->resources, SORT_STRING);
    }
} 
