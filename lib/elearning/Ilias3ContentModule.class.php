<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* class to handle ILIAS 3 learning modules and tests
*
* This class contains methods to handle ILIAS 3 learning modules and tests.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       Ilias3ContentModule
* @package  ELearning-Interface
*/
class Ilias3ContentModule extends ContentModule
{
    var $object_id;

    /**
    * constructor
    *
    * init class.
    * @access public
    * @param string $module_id module-id
    * @param string $module_type module-type
    * @param string $cms_type system-type
    */
    function __construct($module_id = "", $module_type, $cms_type)
    {
        parent::__construct($module_id, $module_type, $cms_type);
        if ($module_id != "")
            $this->readData();
    }

    /**
    * read data
    *
    * get module data from database.
    * @access public
    */
    function readData()
    {
        global $connected_cms;

        $object_data = $connected_cms[$this->cms_type]->soap_client->getObjectByReference($this->id, $connected_cms[$this->cms_type]->user->getId());
        if ( (! ($object_data == false)) AND ($connected_cms[$this->cms_type]->types[$object_data["type"]] != "") )
        {
            // If User has no external Account, show module and link to user-assignment
            if (! $connected_cms[$this->cms_type]->user->isConnected())
                $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ] );

            //set module data
            $this->setObjectId($object_data["obj_id"]);
            $this->setTitle($object_data["title"]);
            $this->setDescription($object_data["description"]);
            if ($object_data["owner"] != "")
            {
                $user_data = $connected_cms[$this->cms_type]->soap_client->getUser($object_data["owner"]);
                $user_name = trim($user_data["title"] . " " . $user_data["firstname"] . " " . $user_data["lastname"]);
                $this->setAuthors($user_name);
            }
//          echo $object_data["accessInfo"] . ": " . implode($object_data["operations"], ".");
            $this->setPermissions($object_data["accessInfo"], $object_data["operations"]);
        }
        else
        {
            // If module doesn't exist, show errormessage
            $this->createDummyForErrormessage("not found");
            $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ, OPERATION_DELETE] );
        }
    }

    /**
    * set permissions
    *
    * sets permissions for content-module
    * @access public
    * @param string $acces_info access-status
    * @param array $operations array of operations
    * @return boolean successful
    */
    function setPermissions($access_info, $operations)
    {
        global $connected_cms;

        switch ($access_info)
        {
            case "granted":
                $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray($operations );
                break;
            case "no_permission":
                $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray($operations );
                $this->setDescription($object_data["description"] . "<br><br><i>" . _("Sie haben keine Leseberechtigung für dieses Modul.") . "</i>");
                return false;
                break;
            case "missing_precondition":
                $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray($operations );
                $this->setDescription($object_data["description"] . "<br><br><i>" . _("Sie haben zur Zeit noch keinen Zugriff auf deses Modul (fehlende Vorbedingungen).") . "</i>");
                break;
            case "no_object_access":
                $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray($operations );
                $this->setDescription($object_data["description"] . "<br><br><i>" . _("Dieses Modul ist momentan offline oder durch Payment-Regeln gesperrt.") . "</i>");
                break;
            case "no_parent_access":
                $this->allowed_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray($operations );
                $this->setDescription($object_data["description"] . "<br><br><i>" . _("Sie haben keinen Zugriff auf die übergeordneten Objekte dieses Moduls.") . "</i>");
                return false;
                break;
            case "object_deleted":
                $this->createDummyForErrormessage("deleted");
                return false;
                break;
        }
        if ($connected_cms[$this->cms_type]->isAuthNecessary() AND ($connected_cms[$this->cms_type]->user->isConnected()))
        {
            // If User has no permission, don't show module data
            if ((! $this->isAllowed(OPERATION_VISIBLE) ) AND (! $this->isDummy()) AND ($connected_cms[$this->cms_type]->user->isConnected()))
                $this->createDummyForErrormessage("no permission");
        }

//      echo "PERM".implode($this->allowed_operations,"-");
    }

    /**
    * set connection
    *
    * sets connection with seminar
    * @access public
    * @param string $seminar_id seminar-id
    * @return boolean successful
    */
    function setConnection($seminar_id)
    {
        global $connected_cms, $messages;

        $write_permission = Request::option("write_permission");
        $write_permission_autor = Request::option("write_permission_autor");

        $crs_id = ObjectConnections::getConnectionModuleId($seminar_id, "crs", $this->cms_type);
//      echo "SET?".$this->cms_type;
        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);
        $connected_cms[$this->cms_type]->soap_client->clearCache();

        // Check, ob Kurs in ILIAS gelöscht wurde
        if (($crs_id != false) AND ($connected_cms[$this->cms_type]->soap_client->getObjectByReference($crs_id) == false))
        {
            ObjectConnections::unsetConnection($seminar_id, $crs_id, "crs", $this->cms_type);
//          echo "deleted: ".ObjectConnections::getConnectionModuleId($seminar_id, "crs", $this->cms_type);
//          echo "Der zugeordnete ILIAS-Kurs (ID $crs_id) existiert nicht mehr. Ein neuer Kurs wird angelegt.";
            $messages["info"] .= _("Der zugeordnete ILIAS-Kurs (ID $crs_id) existiert nicht mehr. Ein neuer Kurs wird angelegt.") . "<br>";
            $crs_id = false;
        }

        if ($crs_id == false)
        {

            $lang_array = explode("_",Config::get()->DEFAULT_LANGUAGE);
            $course_data["language"] = $lang_array[0];
            $course_data["title"] = "Stud.IP-Kurs " . Context::get()->Name;
            $course_data["description"] = "";
            $ref_id = $connected_cms[$this->cms_type]->main_category_node_id;
            $crs_id = $connected_cms[$this->cms_type]->soap_client->addCourse($course_data, $ref_id);

            if ($crs_id == false)
            {
                $messages["error"] .= _("Zuordnungs-Fehler: Kurs konnte nicht angelegt werden.");
                return false;
             }
            ObjectConnections::setConnection($seminar_id, $crs_id, "crs", $this->cms_type);

            // Rollen zuordnen
            $connected_cms[$this->cms_type]->permissions->CheckUserPermissions($crs_id);
//          $messages["info"] .= "Neue Kurs-ID: $crs_id. <br>";
        }


        $ref_id = $this->getId();
        $ref_id = $connected_cms[$this->cms_type]->soap_client->addReference($this->id, $crs_id);
        $local_roles = $connected_cms[$this->cms_type]->soap_client->getLocalRoles($crs_id);
        $member_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ]);
        $admin_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ, OPERATION_WRITE]);
        foreach ($local_roles as $key => $role_data){
            // check only if local role is il_crs_member, -tutor or -admin
            if (mb_strpos($role_data["title"], "il_crs_") === 0) {
                if(mb_strpos($role_data["title"], 'il_crs_member') === 0){
                    $operations = $write_permission_autor ? $admin_operations : $member_operations;
                } else if(mb_strpos($role_data["title"], 'il_crs_tutor') === 0){
                    $operations = $write_permission_autor || $write_permission ? $admin_operations : $member_operations;
                } else {
                    continue;
                }
                $connected_cms[$this->cms_type]->soap_client->revokePermissions($role_data["obj_id"], $ref_id);
                $connected_cms[$this->cms_type]->soap_client->grantPermissions($operations, $role_data["obj_id"], $ref_id);
            }
        }
        if ($ref_id)
        {
            $this->setId($ref_id);
            return parent::setConnection($seminar_id);
        }
        else
            $messages["error"] .= _("Die Zuordnung konnte nicht gespeichert werden.");
        return false;
    }

    /**
    * unset connection
    *
    * unsets connection with seminar
    * @access public
    * @param string $seminar_id seminar-id
    * @return boolean successful
    */
    function unsetConnection($seminar_id)
    {
        global $connected_cms, $messages;

        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);
        {
            if ( $this->getObjectId() != false)
                $connected_cms[$this->cms_type]->soap_client->deleteObject($this->getId());
            return parent::unsetConnection($seminar_id);
        }
        $messages["error"] .= _("Die Zuordnung konnte nicht entfernt werden.");
        return false;
    }

    /**
    * set object id
    *
    * sets object id
    * @access public
    * @param string $module_object_id object id
    */
    function setObjectId($module_object_id)
    {
        $this->object_id = $module_object_id;
    }

    /**
    * get object id
    *
    * returns object id
    * @access public
    * @return string object id
    */
    function getObjectId()
    {
        return $this->object_id;
    }

    /**
    * set allowed operations
    *
    * sets allowed operations
    * @access public
    * @param array $operation_array operation-ids
    */
    function setAllowedOperations( $operation_array )
    {
        global $connected_cms;

        $this->allowed_operations = [];
        foreach($operation_array as $key => $operation)
        {
//          echo "O$operation = I".$connected_cms[$this->cms_type]->permissions->getOperation[$operation]."<br>";
            $this->allowed_operations[] = $connected_cms[$this->cms_type]->permissions->getOperation[$operation];
        }
    }

    /**
    * get permission-status
    *
    * returns true, if operation is allowed
    * @access public
    * @param string $operation operation
    * @return boolean allowed
    */
    function isAllowed($operation)
    {
        global $connected_cms;

        if (is_array($this->allowed_operations))
        {
            if (in_array($connected_cms[$this->cms_type]->permissions->getOperation($operation), $this->allowed_operations))
                return true;
            else
                return false;
        }
        else
            return false;
    }
}
?>
