<?
# Lifter001: TEST
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* EditResourceData.class.php
*
* shows the forms to edit the object
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       EditResourceData.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// EditResourceData.class.php
// stellt die forms zur Bearbeitung eines Ressourcen-Objekts zur Verfuegung
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
EditResourceData, Darstellung der unterschiedlichen Forms zur
Bearbeitung eines Objects
/*****************************************************************************/
class EditResourceData {
    var $resObject;     //Das Oject an dem gearbeitet wird
    var $used_view;     //the used view

    //Konstruktor
    function __construct($resource_id) {
        $this->resObject = ResourceObject::Factory($resource_id);
    }

    function setUsedView ($value) {
        $this->used_view = $value;
    }

    //private
    function selectCategories($select_rooms = TRUE)
    {
        if (!$select_rooms) {
            $query = "SELECT category_id, name FROM resources_categories WHERE is_room = 0 ORDER BY name";
        } else {
            $query = "SELECT category_id, name FROM resources_categories ORDER BY name";
        }
        $statement = DBManager::get()->query($query);
        return $statement->fetchGrouped(PDO::FETCH_COLUMN);
    }

    //private
    function selectProperties()
    {
        $query = "SELECT rp.property_id, rp.name, rp.type, rp.options, rp.system, rop.state, rcp.protected
                  FROM resources_properties AS rp
                  LEFT JOIN resources_categories_properties AS rcp USING (property_id)
                  LEFT JOIN resources_objects AS ro USING (category_id)
                  LEFT JOIN resources_objects_properties AS rop USING (resource_id, property_id)
                  WHERE ro.resource_id = ?
                  ORDER BY rp.name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->resObject->getId()]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    //private
    function selectFaculties($only_fak = TRUE)
    {
        $query = "SELECT Institut_id, Name, fakultaets_id
                  FROM Institute
                  WHERE fakultaets_id = Institut_id
                  ORDER BY name";
        $statement = DBManager::get()->query($query);
        $faculties = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        if (count($faculties) === 0) {
            return $faculties;
        }

        foreach (array_keys($faculties) as $fakultaets_id) {
            $faculties[$fakultaets_id]['institutes'] = [];
        }

        $query = "SELECT fakultaets_id, Institut_id, Name
                  FROM Institute
                  WHERE fakultaets_id IN (?) AND fakultaets_id != Institut_id
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            array_keys($faculties),
        ]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $faculties[$row['fakultaets_id']]['institutes'][$row['Institut_id']] = $row['Name'];
        }

        return $faculties;
    }

    //private
    function selectPerms()
    {
        $query = "SELECT user_id, perms FROM resources_user_resources WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->resObject->getId()]);
        return $statement->fetchGrouped(PDO::FETCH_COLUMN);
    }

    function showScheduleForms($assign_id='') {
        global $perm, $user;

        $resReq = new RoomRequest();

        $killButton = TRUE;
        if ($_SESSION['new_assign_object'])
            $resAssign = unserialize($_SESSION['new_assign_object']);
        else
            $resAssign = AssignObject::Factory($assign_id);

        //workaround anoack: AssignObject::resource_id  must match the actual resource object
        if($resAssign->getResourceId() != $_SESSION['resources_data']['actual_object']) {
            $resAssign = AssignObject::Factory(false);
        }
        //workaround anoack: new AssignObjects need a resource_id !
        if ($resAssign->isNew()){
            $resAssign->setResourceId($_SESSION['resources_data']['actual_object']);
        }

        $add_ts = Request::int('add_ts');
        if (($add_ts) && ($resAssign->isNew())) {
            $resAssign->setBegin($add_ts);
            $resAssign->setEnd($add_ts + (2 * 60 * 60));
        }


        $owner_type = $resAssign->getOwnerType();

        //it is not allowed to edit or kill assigns for rooms here
        if (($owner_type == "sem") || ($owner_type == "date")) {
            $resObject = ResourceObject::Factory($resAssign->getResourceId());
            if ($resObject->isRoom()) {
                $lockedAssign=TRUE;
                $killButton = FALSE;
            }
        }


        //load the object perms
        $ResourceObjectPerms = ResourceObjectPerms::Factory($resAssign->getResourceId());

        //in some case, we load the perms from the assign object, if it has an owner
        if (($ResourceObjectPerms->getUserPerm() != "admin") && (!$resAssign->isNew()) && (!$_SESSION['new_assign_object'])) {
            //load the assign-object perms of a saved object
            $SavedStateAssignObject = AssignObject::Factory($resAssign->getId());
            if ($SavedStateAssignObject->getAssignUserId()){
                unset($ObjectPerms);
                $ObjectPerms = new AssignObjectPerms($resAssign->getId());
            }
        }
        if (!isset($ObjectPerms)){
            $ObjectPerms =& $ResourceObjectPerms;
        }

        if ((!$ObjectPerms->havePerm("autor"))){ // && (!$resAssign->isNew()) && (!$new_assign_object)) {
            $killButton = FALSE;
            $lockedAssign = TRUE;
        }

        if ($resAssign->isNew()){
            $killButton = FALSE;
            if($ObjectPerms->getUserPerm() == 'autor' && !$resAssign->getAssignUserId()) {
                $resAssign->setAssignUserId($user->id);
            }
        }

        if ($resAssign->isNew() && $lockedAssign){
            echo MessageBox::info(_("Sie haben nicht die Berechtigung, für diese Resource eine Belegung zu erstellen."));
            return;
        }

        if ($lockedAssign) {
            if ($owner_type == "sem") {
                $seminarName = Seminar::GetInstance($resAssign->getAssignUserId())->getName();
                $seminarID = $resAssign->getAssignUserId();
            } elseif ($owner_type == "date") {
                $seminarID = Seminar::GetSemIdByDateId($resAssign->getAssignUserId());
                $seminarName = Seminar::GetInstance($seminarID)->getName();
            }
        }
        $search_user = Request::quoted('search_user');
        $search_string_search_user = Request::quoted('search_string_search_user');
        $view_mode = Request::option('view_mode');
        $quick_view = Request::option('quick_view');
        $search_exp_room = Request::quoted('search_exp_room');
        $search_properties = Request::submitted('search_properties');

        /* * * * * * * * * * * * * * * *
         * * * * T E M P L A T E * * * *
         * * * * * * * * * * * * * * * */
        $template = $GLOBALS['template_factory']->open('resources/show_schedule_forms.php');
        $template->set_attribute('used_view', $this->used_view);
        $change_schedule_move_or_copy = Request::option('change_schedule_move_or_copy');
        echo $template->render(compact( 'resAssign', 'resources_data', 'view_mode', 'lockedAssign', 'killButton', 
            'owner_type', 'perm', 'search_string_search_user', 'ResourceObjectPerms', 'ObjectPerms', 'search_exp_room',
            'search_properties_x', 'resReq', 'seminarName', 'seminarID','change_schedule_move_or_copy'));
    }


    function showPropertiesForms() {
        global $user;

        $ObjectPerms = ResourceObjectPerms::Factory($this->resObject->getId());

        /* * * * * * * * * * * * * * * *
         * * * * T E M P L A T E * * * *
         * * * * * * * * * * * * * * * */
        $template = $GLOBALS['template_factory']->open('resources/show_properties_forms.php');
        $template->set_attribute('resObject', $this->resObject);
        $template->set_attribute('EditResourceData', $this);

        echo $template->render(compact( 'ObjectPerms', 'user' ));
    }

    function showPermsForms() {
        $template = $GLOBALS['template_factory']->open('resources/show_perms_forms.php');
        
        if (!Request::submitted('reset_search_perm_user')) {
            $template->set_attribute('search_string_search_perm_user', Request::get('search_string_search_perm_user'));
        }
        
        if (!Request::submitted('reset_search_owner')) {
           $template->set_attribute('search_string_search_owner', Request::get('search_string_search_owner'));
        }
        
        $ObjectPerms = ResourceObjectPerms::Factory($this->resObject->getId());
        $owner_perms = checkObjektAdministrablePerms($this->resObject->getOwnerId());

        if ($owner_perms) {
            $admin_perms = true;
        } else {
            $admin_perms = ($ObjectPerms->havePerm("admin")) ? true : false;
        }

        $selectPerms = $this->selectPerms();

        /* * * * * * * * * * * * * * * *
         * * * * T E M P L A T E * * * *
         * * * * * * * * * * * * * * * */
        
        $template->set_attributes([
            'resObject' => $this->resObject,
            'user'      => $GLOBALS['user']
        ]);

        echo $template->render(compact('admin_perms', 'owner_perms', 'ObjectPerms', 'selectPerms' ));
    }
}
