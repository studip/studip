<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

DEFINE (CRS_NOTIFICATION, "1");
DEFINE (CRS_NO_NOTIFICATION, "2");
DEFINE (CRS_ADMIN_ROLE, "1");
DEFINE (CRS_MEMBER_ROLE, "2");
DEFINE (CRS_TUTOR_ROLE, "3");
DEFINE (CRS_PASSED_VALUE, "0");

DEFINE (OPERATION_VISIBLE, "visible");
DEFINE (OPERATION_READ, "read");
DEFINE (OPERATION_WRITE, "write");
DEFINE (OPERATION_DELETE, "delete");
DEFINE (OPERATION_CREATE_LM, "create_lm");
DEFINE (OPERATION_CREATE_TEST, "create_tst");
DEFINE (OPERATION_CREATE_QUESTIONS, "create_qps");
DEFINE (OPERATION_CREATE_FILE, "create_file");

/**
* class to handle ILIAS 3 access controls
*
* This class contains methods to handle permissions on connected objects.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       Ilias3ConnectedPermission
* @package  ELearning-Interface
*/
class Ilias3ConnectedPermissions extends ConnectedPermissions
{
    var $operations;
    var $allowed_operations;
    var $tree_allowed_operations;

    var $USER_OPERATIONS;
    var $AUTHOR_OPERATIONS;
    /**
    * constructor
    *
    * init class.
    * @access
    * @param string $cms system-type
    */
    function __construct($cms)
    {
        global $connected_cms;

        parent::__construct($cms);
        $this->readData();

        if ($connected_cms[$this->cms_type]->user->isConnected())
        {
            $roles = $this->getUserRoles();
            $connected_cms[$this->cms_type]->user->setRoles( $roles );
        }
        $this->USER_OPERATIONS = [OPERATION_VISIBLE, OPERATION_READ];
//      $this->AUTHOR_OPERATIONS = array(OPERATION_VISIBLE, OPERATION_READ, OPERATION_CREATE_LM, OPERATION_CREATE_TEST, OPERATION_CREATE_QUESTIONS, OPERATION_CREATE_FILE);
        $this->permissions_changed = false;
    }

    /**
    * read data
    *
    * reads acces control data from database
    * @access public
    */
    function readData()
    {
        global $connected_cms;

//      $this->operations = $this->db_class->getOperations($connected_cms[$this->cms_type]->db);

        $this->operations = $connected_cms[$this->cms_type]->soap_client->getOperations();
    }

    /**
    * check user permissions
    *
    * checks user permissions for connected course and changes setting if necessary
    * @access public
    * @param string $course_id course-id
    * @return boolean returns false on error
    */
    function checkUserPermissions($course_id = "")
    {
        global $connected_cms, $messages;

        if ($course_id == "")
            return false;
        if ($connected_cms[$this->cms_type]->user->getId() == "")
            return false;

        // get course role folder and local roles
        $local_roles = $connected_cms[$this->cms_type]->soap_client->getLocalRoles($course_id);
        $active_role = "";
        $proper_role = "";
        $user_crs_role = $connected_cms[$this->cms_type]->crs_roles[$perm->get_studip_perm(Context::getId())];
        if (is_array($local_roles))
            foreach ($local_roles as $key => $role_data)
                // check only if local role is il_crs_member, -tutor or -admin
                if (! (mb_strpos($role_data["title"], "_crs_") === false))
                {
                    if ( in_array( $role_data["obj_id"], $connected_cms[$this->cms_type]->user->getRoles() ) )
                        $active_role = $role_data["obj_id"];
                    if ( mb_strpos( $role_data["title"], $user_crs_role) > 0 )
                        $proper_role = $role_data["obj_id"];
                }
    //          if ($GLOBALS["debug"] == true)
    //              echo "P$proper_role A$active_role U" . $user_crs_role . " R" . implode($connected_cms[$this->cms_type]->user->getRoles(), ".")."<br>";

        // is user already course-member? otherwise add member with proper role
        $is_member = $connected_cms[$this->cms_type]->soap_client->isMember( $connected_cms[$this->cms_type]->user->getId(), $course_id);
        if (! $is_member)
        {
            $member_data["usr_id"] = $connected_cms[$this->cms_type]->user->getId();
            $member_data["ref_id"] = $course_id;
            $member_data["status"] = CRS_NO_NOTIFICATION;
            $type = "";
            switch ($user_crs_role)
            {
                case "admin":
                    $member_data["role"] = CRS_ADMIN_ROLE;
                    $type = "Admin";
                    break;
                case "tutor":
                    $member_data["role"] = CRS_TUTOR_ROLE;
                    $type = "Tutor";
                    break;
                case "member":
                    $member_data["role"] = CRS_MEMBER_ROLE;
                    $type = "Member";
                    break;
                default:
            }
            $member_data["passed"] = CRS_PASSED_VALUE;
            if ($type != "")
            {
                $connected_cms[$this->cms_type]->soap_client->addMember( $connected_cms[$this->cms_type]->user->getId(), $type, $course_id );
                if ($GLOBALS["debug"] == true)
                    echo "addMember";
                $this->permissions_changed = true;
            }
        }

        // check if user has proper local role
        // if not, change it
        if ($active_role != $proper_role)
        {
            if ($active_role != "")
            {
                $connected_cms[$this->cms_type]->soap_client->deleteUserRoleEntry( $connected_cms[$this->cms_type]->user->getId(), $active_role);
                if ($GLOBALS["debug"] == true)
                    echo "Role $active_role deleted.";
            }

            if ($proper_role != "")
            {
                $connected_cms[$this->cms_type]->soap_client->addUserRoleEntry( $connected_cms[$this->cms_type]->user->getId(), $proper_role);
                if ($GLOBALS["debug"] == true)
                    echo "Role $proper_role added.";
            }
            $this->permissions_changed = true;

        }
        if (!$this->getContentModulePerms($course_id)) {
            $messages["info"] .= _("Für den zugeordneten ILIAS-Kurs konnten keine Berechtigungen ermittelt werden.") . "<br>";
        }
    }

    /**
    * get user roles
    *
    * returns roles for current user
    * @access public
    * @return array role-ids
    */
    function getUserRoles()
    {
        global $connected_cms;

        return $connected_cms[$this->cms_type]->soap_client->getUserRoles($connected_cms[$this->cms_type]->user->getId());
    }

    /**
    * get permissions for content module
    *
    * returns allowed operations for given user and module
    * @access public
    * @param string $module_id module-id
    * @return boolean returns false on error
    */
    function getContentModulePerms($module_id)
    {
        global $connected_cms, $current_module;

        if (is_array($connected_cms[$this->cms_type]->content_module[$current_module]->allowed_operations))
            return true;
        $this->allowed_operations = [];
        $this->tree_allowed_operations = $connected_cms[$this->cms_type]->soap_client->getObjectTreeOperations(
                    $module_id,
                    $connected_cms[$this->cms_type]->user->getId()
                    );
//      echo "MID".$module_id."UID".$connected_cms[$this->cms_type]->user->getId()."OPS".implode($this->tree_allowed_operations,"-") ;
        if (! is_array($this->tree_allowed_operations))
            return false;

        $no_permission = false;
        if (isset($current_module)) { //TODO: fixes Warning:Creating default object from empty value - possible side effects
            if ((! in_array($this->operations[OPERATION_READ], $this->tree_allowed_operations)) OR (! in_array($this->operations[OPERATION_VISIBLE], $this->tree_allowed_operations)))
                $no_permission = true;

            if ($no_permission == false)
                $connected_cms[$this->cms_type]->content_module[$current_module]->allowed_operations = $this->tree_allowed_operations;
            else
                $connected_cms[$this->cms_type]->content_module[$current_module]->allowed_operations = false;
        }
        return true;
    }

    /**
    * get operation
    *
    * returns id for given operation-string
    * @access public
    * @param string $operation operation
    * @return integer operation-id
    */
    function getOperation($operation)
    {
        return $this->operations[$operation];
    }

    /**
    * get operation-ids
    *
    * returns an array of operation-ids
    * @access public
    * @param string $operation operation
    * @return array operation-ids
    */
    function getOperationArray($operation)
    {
        if (is_array($operation))
        {
            foreach ($operation as $key => $operation_name)
            {
                $ops_array[] = $this->operations[$operation_name];
            }
        }
        else
            return false;
        return $ops_array;
    }
}
?>
