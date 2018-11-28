<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* class to handle access controls
*
* This class contains methods to handle permissions on connected objects.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ConnectedPermission
* @package  ELearning-Interface
*/
class ConnectedPermissions
{
    var $cms_type;

    var $db_class;
    /**
    * constructor
    *
    * init class. don't call directly, class is loaded by ConnectedCMS.
    * @access public
    * @param string $cms system-type
    */
    function __construct($cms)
    {
        global $connected_cms, $ELEARNING_INTERFACE_MODULES;

        $this->cms_type = $cms;
        if ($ELEARNING_INTERFACE_MODULES[$this->cms_type]["RELATIVE_PATH_DB_CLASSES"] != false)
        {
            require_once('lib/elearning/' . $ELEARNING_INTERFACE_MODULES[$this->cms_type]["RELATIVE_PATH_DB_CLASSES"]
                . "/" . $ELEARNING_INTERFACE_MODULES[$this->cms_type]["db_classes"]["permissions"]["file"] );
            $classname = $ELEARNING_INTERFACE_MODULES[$this->cms_type]["db_classes"]["permissions"]["classname"];
            $this->db_class = new $classname();
        }

    }

    /**
    * get module-permissions
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @param string $module_id module-id
    * @return boolean returns false
    */
    function getContentModulePerms($module_id)
    {
        return false;
    }
}
?>