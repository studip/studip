<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* ResourceObject.class.php
*
* class for a resource-object
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ResourceObject.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourceObject.class.php
// Klasse fuer ein Ressourcen-Object
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
ResourceObject, zentrale Klasse der Ressourcen Objekte
/*****************************************************************************/
class ResourceObject
{
    public static function Factory()
    {
        static $ressource_object_pool;
        $argn = func_num_args();
        if ($argn == 1) {
            if ($id = func_get_arg(0)) {
                if (is_object($ressource_object_pool[$id]) && $ressource_object_pool[$id]->getId() == $id) {
                    return $ressource_object_pool[$id];
                } else {
                    $ressource_object_pool[$id] = new ResourceObject($id);
                    return $ressource_object_pool[$id];
                }
            }
        }
        return new ResourceObject(func_get_args());
    }

    var $id;                //resource_id des Objects;
    var $name;              //Name des Objects
    var $description;           //Beschreibung des Objects;
    var $owner_id;              //Owner_id;
    var $category_id;           //Die Kategorie des Objects
    var $category_name;         //name of the assigned catgory
    var $category_iconnr;           //iconnumber of the assigned catgory
    var $is_room = null;
    var $is_parent = null;
    var $my_state = null;

    //Konstruktor
    public function __construct($argv)
    {
        global $user;

        $this->user_id = $user->id;

        if ($argv && !is_array($argv)) {
            $id = $argv;
            $this->restore($id);
        } elseif (count($argv) == 7) {
            $this->name = $argv[0];
            $this->description = $argv[1];
            $this->parent_bind = $argv[2];
            $this->root_id = $argv[3];
            $this->parent_id = $argv[4];
            $this->category_id = $argv[5];
            $this->owner_id = $argv[6];
            if (!$this->id) {
                $this->id = $this->createId();
            }
            if (!$this->root_id) {
                $this->root_id = $this->id;
                $this->parent_id = "0";
            }
            if (!$this->requestable) {
                $this->requestable = "0";
            }
            $this->chng_flag=FALSE;
        }
    }

    public function createId()
    {
        return md5(uniqid("DuschDas",1));
    }

    public function create()
    {
        $query = "SELECT resource_id FROM resources_objects WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        $check = $statement->fetchColumn();

        if ($check) {
            $this->chng_flag = true;
            return $this->store();
        }

        return $this->store(true);
    }

    public function setName($name)
    {
        $this->name      = $name;
        $this->chng_flag = true;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        $this->chng_flag   = true;
    }

    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        $this->chng_flag   = true;
    }

    public function setMultipleAssign($value)
    {
        if ($value) {
            $this->multiple_assign = true;
        } else {
            // multiple assigns where allowed and are not allowed anymore - update
            if ($this->multiple_assign) {
                // update the table resources_temporary_events or bad things will happen
                $this->updateAllAssigns();
            }

            $this->multiple_assign = false;
        }

        $this->chng_flag = true;
    }


    /**
     * Set wether the ResourceObject is requestable or not
     *
     * @param bool $value
     */
    public function setRequestable($value)
    {
        if ($value) {
            $this->requestable = true;
        } else {
            // requests where allowed and are not allowed anymore - update
            if ($this->requestable) {
                // update the table resources_temporary_events or bad things will happen
                $this->updateAllAssigns();
            }

            $this->requestable = false;
        }

        $this->chng_flag = true;
    }

    public function setParentBind($parent_bind)
    {
        $this->parent_bind = $parent_bind === 'on';
        $this->chng_flag   = true;
    }

    public function setLockable($lockable)
    {
        $this->lockable  = $lockable === 'on';
        $this->chng_flag = true;
    }

    public function setOwnerId($owner_id)
    {
        $old_value = $this->owner_id;

        $this->owner_id  = $owner_id;
        $this->chng_flag = true;

        return $old_value !== $owner_id;
    }

    public function setInstitutId($institut_id)
    {
        $this->institut_id = $institut_id;
        $this->chng_flag   = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRootId()
    {
        return $this->root_id;
    }

    public function getParentId()
    {
        return $this->parent_id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCategoryName()
    {
        return $this->category_name;
    }

    public function getCategoryIconnr()
    {
        return $this->category_iconnr;
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getOwnerId()
    {
        return $this->owner_id;
    }

    public function getInstitutId()
    {
        return $this->institut_id;
    }

    public function getMultipleAssign()
    {
        return $this->multiple_assign;
    }

    /**
     * Get wether the ResourceObject is requestable or not
     *
     * @return bool
     */
    public function getRequestable()
    {
        return $this->requestable;
    }

    public function getParentBind()
    {
        return $this->parent_bind;
    }

    public function getOwnerType($id = '')
    {
        if (!$id) {
            $id = $this->owner_id;
        }

        if ($id === 'global') {
            return 'global';
        }
        if ($id === 'all'){
            return 'all';
        }

        $type = get_object_type($id);
        return $type === 'fak' ? 'inst' : $type;
    }

    public function getOrgaName ($explain = false, $id = '')
    {
        if (!$id) {
            $id = $this->institut_id;
        }

        $query = "SELECT Name FROM Institute WHERE Institut_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        $name = $statement->fetchColumn();

        if ($name) {
            return $explain
                ? sprintf('%s (%s)', $name, _('Einrichtung'))
                : $name;
        }
    }

    public function getOwnerName($explain = false, $id = '')
    {
        if (!$id) {
            $id = $this->owner_id;
        }

        switch ($this->getOwnerType($id)) {
            case 'all':
                if ($explain) {
                    return _('jedeR (alle Nutzenden)');
                }
                return _('jederR');
            break;
            case 'global':
                if ($explain) {
                    return _('Global (zentral verwaltet)');
                }
                return _('Global');
            break;
            case 'user':
                if ($explain) {
                    return get_fullname($id, 'full') . ' (' . _('NutzerIn') . ')';
                }
                return get_fullname($id, 'full');
            break;
            case 'inst':
                return $this->getOrgaName($explain, $id);
            break;
            case 'sem':
                $query = "SELECT Name FROM seminare WHERE Seminar_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$id]);
                $name = $statement->fetchColumn();

                if ($name) {
                    return $explain
                        ? sprintf('%s (%s)', $name, _('Veranstaltung'))
                        : $name;
                }
            break;
        }
    }

    /**
     * This function creates a link to show an room in a new window/tab/popup. This function should not be used from outside of this class anymore
     *
     * @param bool   $quick_view
     * @param string $view
     * @param string $view_mode
     * @param int    $timestamp jump to this date in the room-assignment-plan
     *
     * @return string href-part of a link
     */
    private function getLink($quick_view = false, $view ="view_schedule", $view_mode = "no_nav", $timestamp = false)
    {
        if (func_num_args() == 1) {
            $timestamp = func_get_arg(0);
        }
        return URLHelper::getLink(sprintf ('resources.php?actual_object=%s&%sview=%s&%sview_mode=%s%s', $this->id, ($quick_view) ? 'quick_' : '', $view, ($quick_view) ? 'quick_' : '', $view_mode, ($timestamp > 0) ? '&start_time='.$timestamp : ''));
    }

    public function getFormattedLink($javaScript = true, $target_new = true, $quick_view = true, $view = 'view_schedule', $view_mode = 'no_nav', $timestamp = false, $link_text = false)
    {
        global $auth;

        if (func_num_args() == 1) {
            $timestamp = func_get_arg(0);
            $javaScript = true;
        }

        if (func_num_args() == 2) {
            $timestamp = func_get_arg(0);
            $link_text = func_get_arg(1);
            $javaScript = true;
        }


        if ($this->id) {
            if (self::isScheduleViewAllowed($this->id)) {
                if (!$javaScript || !$auth->auth["jscript"]) {
                    return "<a " . ($target_new ? "target=\"_blank\" rel=\"noopener noreferrer\"" : "") . " href=\"" . $this->getLink($quick_view, $view, $view_mode, ($timestamp > 0) ? $timestamp : FALSE) . "\">" . (($link_text) ? $link_text : $this->getName()) . "</a>";
                } else {
                    return "<a href=\"javascript:void(null)\" onClick=\"window.open('" . $this->getLink($quick_view, $view, $view_mode, ($timestamp > 0) ? $timestamp : FALSE) . "','','scrollbars=yes,left=10,top=10,width=1000,height=680,resizable=yes').opener = null;\" >" . ($link_text ?: $this->getName()) . "</a>";
                }
            } else {
                return $link_text ?: $this->getName();
            }
        }
        return false;
    }

    public function getOrgaLink ($id = '')
    {
        if (!$id) {
            $id = $this->institut_id;
        }

        return sprintf('dispatch.php/institute/overview?auswahl=%s',$id);
    }

    public function getOwnerLink($id  ='')
    {
        if (!$id) {
            $id = $this->owner_id;
        }
        switch ($this->getOwnerType($id)) {
            case 'global':
                return '#a';
            case 'all':
                return '#a';
            break;
            case 'user':
                return sprintf('dispatch.php/profile?username=%s', get_username($id));
            break;
            case 'inst':
                return sprintf('dispatch.php/institute/overview?auswahl=%s', $id);
            break;
            case 'sem':
                return sprintf('seminar_main.php?auswahl=%s', $id);
            break;
        }
    }

    public function getPlainProperties($only_requestable = false, $only_info_label_visible = false)
    {
        $query = "SELECT b.name, a.state, b.type, b.options
                  FROM resources_objects_properties AS a
                  LEFT JOIN resources_properties AS b USING (property_id)
                  LEFT JOIN resources_categories_properties AS c USING (property_id)
                  WHERE resource_id = ? AND c.category_id = ?";
        if ($only_requestable) {
            $query .= " AND requestable = 1";
        }
        if ($only_info_label_visible) {
            $query .= " AND b.info_label = 1";
        }
        $query .= " ORDER BY b.name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->id,
            $this->category_id
        ]);

        $temp = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $temp[] = sprintf('%s: %s',
                              $row['name'],
                              $row['type'] == 'bool'
                                  ? ($row['state'] ? $row['options'] : '-')
                                  : $row['state']);
        }

        return implode(" \n", $temp);
    }

    public function getSeats()
    {
        if (is_null($this->my_state)) {
            $query = "SELECT a.state
                      FROM resources_objects_properties AS a
                      LEFT JOIN resources_properties AS b USING (property_id)
                      LEFT JOIN resources_categories_properties AS c USING (property_id)
                      WHERE resource_id = ? AND c.category_id = ? AND b.system = 2
                      ORDER BY b.name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->id,
                $this->category_id
            ]);
            $this->my_state = $statement->fetchColumn() ?: null;
        }
        return $this->my_state ?: false;
    }

    public function isUnchanged()
    {
        return $this->mkdate == $this->chdate;
    }

    public function isDeletable()
    {
        return $GLOBALS['perm']->have_perm('root') || (!$this->isParent() && !$this->isAssigned());
    }

    public function isParent()
    {
        if (is_null($this->is_parent)) {
            $query = "SELECT 1
                      FROM resources_objects
                      WHERE parent_id = ?
                      LIMIT 1";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->id]);
            $this->is_parent = ($statement->fetchColumn() > 0) ?: null;
        }
        return (!is_null($this->is_parent));
    }

    public function isAssigned()
    {
        if (is_null($this->is_assigned)) {
            $query = "SELECT 1
                      FROM resources_assign
                      WHERE resource_id = ?
                      LIMIT 1";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->id]);
            $this->is_assigned = ($statement->fetchColumn() > 0) ?: null;
        }
        return (!is_null($this->is_assigned));
    }

    public function isRoom()
    {
        if (is_null($this->is_room)) {
            $query = "SELECT is_room
                      FROM resources_objects
                      LEFT JOIN resources_categories USING (category_id)
                      WHERE resource_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->id]);
            $this->is_room = ($statement->fetchColumn() > 0) ?: null;
        }
        return (!is_null($this->is_room));
    }

    public function isLocked()
    {
        return $this->isRoom() && $this->isLockable() && isLockPeriod('edit');
    }

    public function isLockable()
    {
        return $this->lockable;
    }

    public function flushProperties($id = '')
    {
        if (!$id) {
            $id = $this->id;
        }

        $query = "DELETE FROM resources_objects_properties
                  WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        return $statement->rowCount() > 0;
    }

    public function storeProperty($property_id, $state)
    {
        $query = "INSERT INTO resources_objects_properties
                    (resource_id, property_id, state)
                  VALUES (?, ?, ?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->id,
            $property_id,
            $state ?: ''
        ]);
        return $statement->rowCount() > 0;
    }

    public function deletePerms($user_id)
    {
        $query = "DELETE FROM resources_user_resources
                  WHERE user_id = ? AND resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $user_id,
            $this->id
        ]);
        return $statement->rowCount() > 0;
    }

    public function storePerms ($user_id, $perms = '')
    {
        //User_id zwingend notwendig
        if (!$user_id) {
            return false;
        }

        $query = "SELECT 1
                  FROM resources_user_resources
                  WHERE user_id = ? AND resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $user_id,
            $this->id
        ]);
        $check = $statement->fetchColumn();

        //neuer Eintrag
        if (!$check) {
            if (!$perms) {
                $perms = 'autor';
            }
            $query = "INSERT INTO resources_user_resources
                        (perms, user_id, resource_id)
                      VALUES (?, ?, ?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $perms,
                $user_id,
                $this->id
            ]);
            return $statement->rowCount() > 0;
        }

        //alter Eintrag wird veraendert
        if ($perms) {
            $query = "UPDATE resources_user_resources
                      SET perms = ?
                      WHERE user_id = ? AND resource_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $perms,
                $user_id,
                $this->id
            ]);
            return $statement->rowCount() > 0;
        }

        return false;
    }

    public function restore($id='')
    {
        if (func_num_args() == 0) {
            $id = $this->id;
        }

        $query = "SELECT ro.*, rc.name AS category_name, rc.iconnr
                  FROM resources_objects AS ro
                  LEFT JOIN resources_categories AS rc USING (category_id)
                  WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $this->id = $id;
        $this->name            = $row['name'];
        $this->description     = $row['description'];
        $this->owner_id        = $row['owner_id'];
        $this->institut_id     = $row['institut_id'];
        $this->category_id     = $row['category_id'];
        $this->category_name   = $row['category_name'];
        $this->category_iconnr = $row['iconnr'];
        $this->parent_id       = $row['parent_id'];
        $this->lockable        = $row['lockable'];
        $this->multiple_assign = $row['multiple_assign'];
        $this->requestable     = $row['requestable'];
        $this->root_id         = $row['root_id'];
        $this->mkdate          = $row['mkdate'];
        $this->chdate          = $row['chdate'];
        $this->parent_bind     = !empty($row['parent_bind']);

        return true;
    }

    public function store($create = '')
    {
        // Natuerlich nur Speichern, wenn sich was gaendert hat oder das Object neu angelegt wird
        if ($this->chng_flag || $create) {
            $chdate = time();
            $mkdate = time();

            if ($create) {
                //create level value
                if (!$this->parent_id) {
                    $level = 0;
                } else {
                    $query = "SELECT level FROM resources_objects WHERE resource_id = ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$this->parent_id]);
                    $level = $statement->fetchColumn() + 1;
                }

                $query = "INSERT INTO resources_objects
                            (resource_id, root_id, parent_id, category_id,
                             owner_id, institut_id, level, name, description,
                             lockable, multiple_assign, requestable, mkdate, chdate)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                  UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    $this->id,
                    $this->root_id,
                    $this->parent_id,
                    $this->category_id,
                    $this->owner_id,
                    $this->institut_id ?: '',
                    $level,
                    $this->name,
                    $this->description,
                    $this->lockable,
                    $this->multiple_assign,
                    $this->requestable
                ]);
                $affected_rows = $statement->rowCount();
            } else {
                $query = "UPDATE resources_objects
                          SET root_id = ?, parent_id = ?, category_id = ?,
                              owner_id = ?, institut_id = ?, name = ?,
                              description = ?, lockable = ?, multiple_assign = ?,
                              requestable = ?
                          WHERE resource_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    $this->root_id,
                    $this->parent_id,
                    $this->category_id,
                    $this->owner_id,
                    $this->institut_id,
                    $this->name,
                    $this->description,
                    $this->lockable,
                    $this->multiple_assign,
                    $this->requestable,
                    $this->id
                ]);
                $affected_rows = $statement->rowCount();

                if ($affected_rows) {
                    $query = "UPDATE resources_objects
                              SET chdate = UNIX_TIMESTAMP()
                              WHERE resource_id = ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$this->id]);
                }
            }

            return $affected_rows > 0;
        }
        return false;
    }

    public function delete()
    {
        $this->deleteResourceRecursive ($this->id);
    }

    //delete section, very privat :)

    private function deleteAllAssigns($id = '')
    {
        if (!$id) {
            $id = $this->id;
        }

        $query = "SELECT assign_id FROM resources_assign WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        while ($assign_id = $statement->fetchColumn()) {
            AssignObject::Factory($assign_id)->delete();
        }
    }

    /**
     * update all assigns for this resource
     *
     * @throws Exception
     */
    public function updateAllAssigns()
    {
        if (!$this->id) {
            throw new Exception('Missing resource-ID!');
        }

        $query = "SELECT assign_id FROM resources_assign WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);

        while ($assign_id = $statement->fetchColumn()) {
            AssignObject::Factory($assign_id)->updateResourcesTemporaryEvents();
        }
    }

    private function deleteAllPerms($id='')
    {
        if (!$id) {
            $id = $this->id;
        }
        $query = "DELETE FROM resources_user_resources
                  WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
    }

    public function deleteResourceRecursive($id)
    {
        //subcurse to subordinated resource-levels
        $query = "SELECT resource_id FROM resources_objects WHERE parent_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);

        while ($resource_id = $statement->fetchColumn()) {
            $this->deleteResourceRecursive($resource_id);
        }

        $this->deleteAllAssigns($id);
        $this->deleteAllPerms($id);
        $this->flushProperties($id);

        $query = "DELETE FROM resources_objects WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
    }

    public function getPathArray($include_self = false)
    {
        $result_arr = [];

        $id = $this->getId();
        if ($include_self) {
            $result_arr[$id] = $this->getName();
        }

        $query = "SELECT name, parent_id, resource_id
                  FROM resources_objects
                  WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);

        while ($id) {
            $statement->execute([$id]);
            $temp = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();

            if (!$temp) {
                break;
            }

            $id = $temp['parent_id'];
            $result_arr[$temp['resource_id']] = $temp['name'];
        }
        return $result_arr;
    }

    public function getPathToString($include_self = false, $delimiter = '/')
    {
        return join($delimiter, array_reverse(array_values($this->getPathArray($include_self))));
    }

    /**
     * Checks if the resource occupation may be seen by current user.
     */
    public static function isScheduleViewAllowed($object_id) {
        // Check if room occupation may be seen.
        $allowed = false;

        // Globally allowed via config, for admins, roots and resource admins.
        if (hasGlobalOccupationAccess()) {
            $allowed = true;

        // View occupation only if own room.
        } else {
            $list = ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, false, false, false);
            if (in_array($object_id, array_keys($list->getRooms()))) {
                $allowed = true;
            }
        }

        return $allowed;
    }

}
