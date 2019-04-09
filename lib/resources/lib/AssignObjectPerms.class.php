<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
* AssignObjectPerms.class.php
* 
* perm-class for an assign-object
* 
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       AssignObjectPerms.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// AssignObjectPerms.class.php
// Rechteklasse die Rechte fuer ein Assign-Objekt zur Verfuegung stellt
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
AssignObjectPerms, stellt Perms zum Ressourcen Object zur 
Verfuegung
/*****************************************************************************/

class AssignObjectPerms {
    var $user_id;
    var $assign_id;
    
    function __construct ($assign_id, $user_id='') {
        global $user, $perm;
        
        if ($user_id)
            $this->user_id=$user_id;
        else
            $this->user_id=$user->id;
        
        $this->assign_id=$assign_id;
        
        //check if user is root
        if ($perm->have_perm("root")) {
            $this->perm="admin";
        } else //check if resources admin
            if (getGlobalPerms($this->user_id) == "admin")
                $this->perm="admin";

        //check if the user assigns the assign 
        if ($this->perm != "admin") {
            $query = "SELECT 1
                      FROM resources_assign
                      WHERE assign_user_id = ? AND assign_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->user_id,
                $this->assign_id
            ]);
            $this->owner = (bool)$statement->fetchColumn();

            if ($this->owner) {
                $this->perm = 'admin';
            }
        }
        
        //else check if the user is admin of the assigned resource
        if ($this->perm != "admin") {
            $query = "SELECT resource_id FROM resources_assign WHERE assign_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->assign_id]);
            $resource_id = $statement->fetchColumn();

            if ($resource_id) {
                $ObjectPerms = ResourceObjectPerms::Factory($resource_id);
                if ($ObjectPerms->havePerm('tutor')) {
                    $this->perm = 'admin';
                }
            }
        }
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
        return $this->assign_id;    
    }

    function getUserId () {
        return $this->user_id;  
    }
}
