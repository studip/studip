<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ResourceObjectPerms.class.php
*
* perm-class for a resource-object
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ResourceObjectPerms.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourceObjectPerms.class.php
// Rechteklasse die Rechte fuer ein Ressourcem-Objekt zur Verfuegung stellt
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
ResourceObjectPerms, stellt Perms zum Ressourcen Object zur
Verfuegung
/*****************************************************************************/

class ResourceObjectPerms {

    function Factory($resource_id, $user_id = false){

        static $object_pool;

        if (!$user_id){
            $user_id = $GLOBALS['auth']->auth['uid'];
        }
        if (is_object($object_pool[$user_id][$resource_id])){
            return $object_pool[$user_id][$resource_id];
        } else {
            $object_pool[$user_id][$resource_id] = new ResourceObjectPerms($resource_id, $user_id);
            return $object_pool[$user_id][$resource_id];
        }
    }

    var $user_id;
    var $db;
    var $db2;
    var $resource_id;
    var $perm_weight= ["admin" => 4, "tutor" => 2, "autor" => 1];


    function __construct($resource_id, $user_id='') {
        global $user, $perm;

        if ($user_id)
            $this->user_id=$user_id;
        else
            $this->user_id=$user->id;

        $this->resource_id=$resource_id;
        if (!$this->resource_id){
            $this->perm = false;
            return;
        }

        $resObject = ResourceObject::Factory($this->resource_id);
        $is_room = $resObject->isRoom();

        if ($is_room) {
            $inheritance = Config::get()->RESOURCES_INHERITANCE_PERMS_ROOMS;
        } else {
            $inheritance = Config::get()->RESOURCES_INHERITANCE_PERMS;
        }

        //check if user is root
        if ($perm->have_perm('root')) {
            $this->changePerm('admin');
        }
        //check if resources admin
        elseif (getGlobalPerms($this->user_id) === 'admin') {
            $this->changePerm('admin');
        }

        //check, if the resource is locked at the moment (only rooms!)
        if (($this->perm != "admin") && ($resObject->isLocked())) {
            $this->perm = FALSE;
            return;
        }

        //check if the user is owner of the object
        if ($this->perm != "admin") {
            $result = DBManager::get()->query("SELECT owner_id FROM resources_objects WHERE owner_id='$this->user_id' AND resource_id = '$this->resource_id' ");
            if ($result->fetch()) {
                $this->owner=TRUE;
                $this->changePerm("admin");
            } else {
                $this->owner=FALSE;
            }
        }

        //else check all the other possibilities
        if ($this->perm != "admin") {
            $my_administrable_objects = search_administrable_objects(); //the administrative ones....
            $my_objects=search_my_objects();                //...and the other, where the user is autor.
            $my_objects["all"] = TRUE;
            $my_objects = array_merge((array)$my_administrable_objects, (array)$my_objects);
            //check if one of my administrable (system) objects owner of the resourcen object, so that I am too...

            if (is_array($my_objects) && count($my_objects)){
                $objects_sql = " ('" . join("','", array_keys($my_objects)) . "') ";

                $superordinated_id = $this->resource_id;
                $top=FALSE;

                while ((!$top) && ($k<10000) && ($superordinated_id)) {
                    $result = DBManager::get()->query("SELECT owner_id, resource_id
                        FROM resources_objects
                        WHERE owner_id IN $objects_sql AND resource_id = '$superordinated_id' ");

                    while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
                        if ($my_objects[$data['owner_id']]["perms"] == "admin"){
                            $this->changePerm("admin");
                        } else {
                            switch ($inheritance) {
                                case "1":
                                    $this->changePerm($my_objects[$data['owner_id']]["perms"]);
                                break;
                                default:
                                case "2":
                                    $this->changePerm("autor");
                                break;
                            }
                        }
                        if ($this->perm == "admin")
                        break;
                    }
                    ++$k;
                    if ($this->perm == "admin")
                        break;

                    //also check the additional perms...
                    $result = DBManager::get()->query("SELECT user_id,perms
                        FROM resources_user_resources
                        WHERE user_id IN $objects_sql AND resource_id = '$superordinated_id' ");

                    while ($data = $result->fetch(PDO::FETCH_ASSOC)){
                        $this->changePerm($data['perms']);
                        if ($this->perm == "admin")
                            break;
                    }
                    if ($this->perm == "admin")
                        break;

                    //select the next superordinated object
                    $result = DBManager::get()->query("SELECT parent_id FROM resources_objects WHERE resource_id = '$superordinated_id'");
                    $data = $result->fetch(PDO::FETCH_ASSOC);
                    $superordinated_id = $data['parent_id'];
                    if ($data['parent_id'] == "0")
                        $top = TRUE;
                }

            }
        }
    }

    //private
    function changePerm($new_perm) {
        if ($new_perm == "dozent")
            $new_perm = "tutor";
        if ($this->perm_weight[$new_perm] > $this->perm_weight[$this->perm])
            $this->perm = $new_perm;
    }

    function havePerm ($perm) {
        if ($perm == "admin") {
            if ($this->getUserPerm () == "admin")
                return TRUE;
        } elseif ($perm == "autor") {
            if (($this->getUserPerm () == "admin") || ($this->getUserPerm () == "autor") || ($this->getUserPerm () == "tutor"))
                return TRUE;
        } elseif ($perm == "tutor") {
            if (($this->getUserPerm () == "admin") || ($this->getUserPerm () == "tutor"))
                return TRUE;
        } else
            return FALSE;
    }

    function getUserPerm () {
        return $this->perm;
    }

    function getUserIsOwner () {
        return $this->owner;
    }

    function getId () {
        return $this->resource_id;
    }

    function getUserId () {
        return $this->user_id;
    }

}
