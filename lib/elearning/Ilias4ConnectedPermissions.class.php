<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

DEFINE ("OPERATION_COPY", "copy");

/**
 * class to handle ILIAS 4 access controls
 *
 * This class contains methods to handle permissions on connected objects.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias4ConnectedPermission
 * @package    ELearning-Interface
 */
class Ilias4ConnectedPermissions extends Ilias3ConnectedPermissions
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
        parent::__construct($cms);
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

        if ($course_id == "") return false;
        if ($connected_cms[$this->cms_type]->user->getId() == "") return false;

        // get course role folder and local roles
        $local_roles = $connected_cms[$this->cms_type]->soap_client->getLocalRoles($course_id);
        $active_role = "";
        $proper_role = "";
        $user_crs_role = $connected_cms[$this->cms_type]->crs_roles[$GLOBALS["perm"]->get_studip_perm(Context::getId())];
        if (is_array($local_roles)) {
            foreach ($local_roles as $key => $role_data) {
                // check only if local role is il_crs_member, -tutor or -admin
                if (! (mb_strpos($role_data["title"], "_crs_") === false)) {
                    if ( in_array( $role_data["obj_id"], $connected_cms[$this->cms_type]->user->getRoles() ) ) {
                        $active_role = $role_data["obj_id"];
                    }
                    if ( mb_strpos( $role_data["title"], $user_crs_role) > 0 ) {
                        $proper_role = $role_data["obj_id"];
                    }
                }
            }
        }

        // is user already course-member? otherwise add member with proper role
        $is_member = $connected_cms[$this->cms_type]->soap_client->isMember( $connected_cms[$this->cms_type]->user->getId(), $course_id);
        if (!$is_member) {
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
            if ($type != "") {
                $connected_cms[$this->cms_type]->soap_client->addMember( $connected_cms[$this->cms_type]->user->getId(), $type, $course_id );
                if ($GLOBALS["debug"] == true) echo "addMember";
                $this->permissions_changed = true;
            }
        }

        // check if user has proper local role
        // if not, change it
        if ($active_role != $proper_role) {
            if ($active_role != "") {
                $connected_cms[$this->cms_type]->soap_client->deleteUserRoleEntry( $connected_cms[$this->cms_type]->user->getId(), $active_role);
                if ($GLOBALS["debug"] == true) echo "Role $active_role deleted.";
            }

            if ($proper_role != "") {
                $connected_cms[$this->cms_type]->soap_client->addUserRoleEntry( $connected_cms[$this->cms_type]->user->getId(), $proper_role);
                if ($GLOBALS["debug"] == true) echo "Role $proper_role added.";
            }
            $this->permissions_changed = true;

        }

        if (! $this->getContentModulePerms( $course_id )) {
            $messages["info"] .= _("Für den zugeordneten ILIAS-Kurs konnten keine Berechtigungen ermittelt werden.") . "<br>";
        }

        return true;
    }
}
?>
