<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* DeleteResourcesUser.class.php
* 
* kills the to the user (range_id)  linked resources
* 
*
* @author       Cornelis Kater <ckater@gwdg.de>
* @access       public
* @package      resources
* @modulegroup      resources_modules
* @module       VeranstaltungResourcesAssign.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// DeleteResourcesUser.class.php
// Klasse zum Loeschen aller verknuepften Ressourcen mit einem Objekt
// (Nutzer, Veranstaltung oder Einrichtung)
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>
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

class DeleteResourcesUser
{
    var $range_id;
    var $object_type;
    
    //Konstruktor
    function __construct($range_id)
    {
        $this->range_id = $range_id;
        $this->object_type = get_object_type($this->range_id);
    }
    
    //private
    function deleteForeignAssigns() {
        //all assigns linked to resource
        if ($this->range_id) {
            $query = "SELECT assign_id FROM resources_assign WHERE assign_user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->range_id]);
            while ($assign_id = $statement->fetchColumn()) {
                AssignObject::Factory($assign_id)->delete();
            }
        }
        if ($this->object_type == 'sem') {
            $query = "SELECT assign_id
                      FROM termine AS t
                      LEFT JOIN resources_assign AS ra ON (ra.assign_user_id = t.termin_id)
                      WHERE range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->range_id]);
            while ($assign_id = $statement->fetchColumn()) {
                AssignObject::Factory($assign_id)->delete();
            }
        }
    }
    
    //private
    function deleteRequests()
    {
        $column = $this->object_type == 'sem'
                ? 'seminar_id'
                : 'termin_id';

        $query = "SELECT request_id FROM resources_requests WHERE :column = :value";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':column', $column, StudipPDO::PARAM_COLUMN);
        $statement->bindValue(':value', $this->range_id);
        $statement->execute();

        while ($request_id = $statement->fetchColumn()) {
            $killRequest = new RoomRequest($request_id);
            $killRequest->delete();
        }
    }

    //private
    function deleteForeignPerms()
    {
        $query = "DELETE FROM resources_user_resources WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->range_id]);
    }

    //private
    function deleteOwnerResources()
    {
        $query = "SELECT resource_id FROM resources_objects WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->range_id]);
        while ($resource_id = $statement->fetchColumn()) {
            ResourceObject::Factory($resource_id)->delete();
        }
    }
    
    function delete()
    {
        if ($this->range_id) {
            $this->deleteForeignAssigns();
            $this->deleteRequests();
            $this->deleteForeignPerms();
            $this->deleteOwnerResources();
        }
    }
}
?>
