<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * class to handle ILIAS 4 learning modules and tests
 *
 * This class contains methods to handle ILIAS 4 learning modules and tests.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias4ContentModule
 * @package    ELearning-Interface
 */
class Ilias4ContentModule extends Ilias3ContentModule
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

        $crs_id = ObjectConnections::getConnectionModuleId($seminar_id, "crs", $this->cms_type);
        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);
        $connected_cms[$this->cms_type]->soap_client->clearCache();

        // Check, ob Kurs in ILIAS gelöscht wurde
        if (($crs_id != false) AND ($connected_cms[$this->cms_type]->soap_client->getObjectByReference($crs_id) == false)) {
            ObjectConnections::unsetConnection($seminar_id, $crs_id, "crs", $this->cms_type);
            $messages["info"] .= _("Der zugeordnete ILIAS-Kurs (ID $crs_id) existiert nicht mehr. Ein neuer Kurs wird angelegt.") . "<br>";
            $crs_id = false;
        }

        $crs_id == $connected_cms[$this->cms_type]->createCourse($seminar_id);

        if ($crs_id == false) return false;

        $ref_id = $this->getId();
        if (Request::get("copy_object") == "1") {
            $connected_cms[$this->cms_type]->soap_client->user_type = 'user';
            $ref_id = $connected_cms[$this->cms_type]->soap_client->copyObject($this->id, $crs_id);
            $connected_cms[$this->cms_type]->soap_client->user_type = 'admin';
        } else {
            $ref_id = $connected_cms[$this->cms_type]->soap_client->addReference($this->id, $crs_id);
        }
        if (!$ref_id) {
            $messages["error"] .= _("Zuordnungs-Fehler: Objekt konnte nicht angelegt werden.");
            return false;
        }
        $local_roles = $connected_cms[$this->cms_type]->soap_client->getLocalRoles($crs_id);
        $member_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ]);
        $admin_operations = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ, OPERATION_WRITE, OPERATION_DELETE]);
        $admin_operations_no_delete = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ, OPERATION_WRITE]);
        $admin_operations_readonly = $connected_cms[$this->cms_type]->permissions->getOperationArray([OPERATION_VISIBLE, OPERATION_READ, OPERATION_DELETE]);
        foreach ($local_roles as $key => $role_data) {
            // check only if local role is il_crs_member, -tutor or -admin
            if (mb_strpos($role_data["title"], "il_crs_") === 0) {
                if(mb_strpos($role_data["title"], 'il_crs_member') === 0){
                    $operations = ($write_permission == "autor") ? $admin_operations_no_delete : $member_operations;
                } elseif(mb_strpos($role_data["title"], 'il_crs_tutor') === 0){
                    $operations = (($write_permission == "tutor") || ($write_permission == "autor")) ? $admin_operations : $admin_operations_readonly;
                } elseif(mb_strpos($role_data["title"], 'il_crs_admin') === 0){
                    $operations = (($write_permission == "dozent") || ($write_permission == "tutor") || ($write_permission == "autor")) ? $admin_operations : $admin_operations_readonly;
                } else {
                    continue;
                }
                $connected_cms[$this->cms_type]->soap_client->revokePermissions($role_data["obj_id"], $ref_id);
                $connected_cms[$this->cms_type]->soap_client->grantPermissions($operations, $role_data["obj_id"], $ref_id);
            }
        }
        if ($ref_id) {
            $this->setId($ref_id);
            return ContentModule::setConnection($seminar_id);
        } else {
            $messages["error"] .= _("Die Zuordnung konnte nicht gespeichert werden.");
        }
        return false;
    }
}