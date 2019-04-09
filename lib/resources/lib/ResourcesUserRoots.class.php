<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* ResourcesUserRoots.class.php
*
* provides all the individual resources-roots for an user
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ResourcesUserRoots.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesUserRoots.class.php
// Stellt die individuellen Wurzeln innerhalb der Hierarchie fuer einen Nutzer zuer Verfuegung
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
ResourcesUserRoots, stellt Stamm-Ressourcen zur Verfuegung
/*****************************************************************************/

class ResourcesUserRoots {
    var $user_global_perm;          //Globaler Status des Benutzers, fuer den Klasse initiert wird
    var $range_id;                  //the id of the User (could be a Person, Einrichtung oder Veranstaltung)
    var $my_roots;                  //Alle meine Ressourcen-Staemme

    //Konstruktor
    function __construct($range_id='') {
        global $user, $perm, $auth;

        if($range_id){
            $this->range_id = $range_id;
        }

        if (!$this->range_id)
            $this->range_id=$user->id;

        if (get_object_type($this->range_id) == "user") {
            //load the global perms in the resources-system (check if the user ist resources-root)
            $this->resources_global_perm=getGlobalPerms($this->range_id);
            //load the global studip perms (check, if user id root)
            $this->user_global_perm = $GLOBALS['perm']->get_perm($this->range_id);

            if ($this->resources_global_perm == "admin")
                $global_perm="root";
            else
                $global_perm=$this->user_global_perm;
        }

        //root or resoures root are able to see all resources (roots in tree)
        if ($global_perm == "root") {
            $query = "SELECT resource_id FROM resources_objects WHERE resource_id = root_id ORDER BY name";
            $statement = DBManager::get()->query($query);
            while ($resource_id = $statement->fetchColumn()) {
                $this->my_roots[$resource_id] = $resource_id;
            }
        } else {
            $my_objects            = search_administrable_objects();
            $my_objects[$user->id] = TRUE;
            $my_objects["global"]  = TRUE;

            //create the clause with all my id's
            $i=0;
            $clause = " (";
            foreach ($my_objects as $key=>$val) {
                if ($i)
                    $clause .= ", ";
                $clause .= "'$key'";
                $i++;
            }
            $clause .= ") ";

            //all objects where I have owner perms...
            $query = "SELECT resource_id, parent_id, root_id, level
                      FROM resources_objects
                      WHERE owner_id IN (?)
                      ORDER BY level DESC, name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                array_keys($my_objects)
            ]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $my_resources[$row['resource_id']] = [
                    'root_id'   => $row['root_id'],
                    'parent_id' => $row['parent_id'],
                    'level'     => $row['level']
                ];
                $roots[$row['root_id']][] = $row['resource_id'];
            }

            //...and all objects where I have add perms...
            $query = "SELECT resource_id, parent_id, root_id, level
                      FROM resources_user_resources
                      LEFT JOIN resources_objects USING (resource_id)
                      WHERE user_id IN ('all', ?)
                      ORDER BY level DESC, name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                array_keys($my_objects)
            ]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $my_resources[$row['resource_id']] = [
                    'root_id'   => $row['root_id'],
                    'parent_id' => $row['parent_id'],
                    'level'     => $row['level']
                ];
                $roots[$row['root_id']][] = $row['resource_id'];
            }

            if (is_array($my_resources)) {
                $query = "SELECT parent_id FROM resources_objects WHERE resource_id = ?";
                $statement = DBManager::get()->prepare($query);

                foreach ($my_resources as $key => $val) {
                    if (!$this->checked[$key]) {
                        if (sizeof($roots[$val["root_id"]]) == 1) {
                            $this->my_roots[$key] = $key;
                        } else {
                            //there are more than 2 resources in one thread...
                            $statement->execute([$key]);
                            $superordinated_id = $statement->fetchColumn();
                            $statement->closeCursor();

                            $top        = FALSE;
                            $last_found = $key;
                            while (!$top && $superordinated_id) {
                                $statement->execute([$superordinated_id]);
                                $parent_id = $statement->fetchColumn();
                                $statement->closeCursor();

                                if ($my_resources[$superordinated_id]) {
                                    $checked[$last_found] = TRUE;
                                    $last_found           = $superordinated_id;
                                }

                                $superordinated_id = $parent_id;
                                if ($parent_id == "0") {
                                    $top = TRUE;
                                }
                            }
                            $this->my_roots[$last_found] = $last_found;
                        }
                    }
                }
            }
            if (is_array($this->my_roots)) {
                $this->my_roots = DBManager::get()->fetchPairs("SELECT resource_id a, resource_id b FROM resources_objects WHERE resource_id IN (?) ORDER BY name", [$this->my_roots]);
            }
        }
    }

    //public
    function getRoots() {
        return $this->my_roots;
    }
}
