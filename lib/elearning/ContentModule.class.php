<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* class to handle content module data
*
* This class contains methods to handle connected content module data.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ContentModule
* @package  ELearning-Interface
*/
class ContentModule
{
    var $id;
    var $title;
    var $module_type;
    var $module_type_name;
    var $icon_file;
    var $cms_type;
    var $cms_name;
    var $description;
    var $authors;
    var $is_connected;
    var $is_dummy;
    var $allowed_operations;

    var $db_class;
    var $view;
    /**
    * constructor
    *
    * init class. don't call directly, class is loaded by ConnectedCMS.
    * @access public
    * @param string $module_id module-id
    * @param string $module_type module-type
    * @param string $cms_type system-type
    */
    function __construct($module_id = "", $module_type, $cms_type)
    {
        global $connected_cms;

        $this->is_dummy = false;
        $this->setCMSType($cms_type);
        $this->setModuleType($module_type);
        if ($module_id != "")
        {
            $this->setId($module_id);

            $this->readData();
        }
        $this->view = new ContentModuleView($this->cms_type);

/**/    }

/*  // Dummy-method. Must be overwritten by subclass.
    function readData()
    {
        return false;
    }
*/

    /**
    * set id
    *
    * sets id
    * @access public
    * @param string $module_id id
    */
    function setId($module_id)
    {
        $this->id = $module_id;
    }

    /**
    * get id
    *
    * returns id
    * @access public
    * @return string id
    */
    function getId()
    {
        return $this->id;
    }

    /**
    * set cms-type
    *
    * sets cms-type
    * @access public
    * @param string $module_cms_type cms-type
    */
    function setCMSType($module_cms_type)
    {
        global $ELEARNING_INTERFACE_MODULES;
        $this->cms_type = $module_cms_type;
        $this->cms_name = $ELEARNING_INTERFACE_MODULES[$module_cms_type]["name"];
    }

    /**
    * get cms-type
    *
    * returns cms-type
    * @access public
    * @return string cms-type
    */
    function getCMSType()
    {
        return $this->cms_type;
    }

    /**
    * get cms name
    *
    * returns cms name
    * @access public
    * @return string cms name
    */
    function getCMSName()
    {
        return $this->cms_name;
    }

    /**
    * set module-type
    *
    * sets module-type
    * @access public
    * @param string $module_type module-type
    */
    function setModuleType($module_type)
    {
        global $ELEARNING_INTERFACE_MODULES;
        $this->module_type = $module_type;
        $this->module_type_name = $ELEARNING_INTERFACE_MODULES[$this->cms_type]["types"][$module_type]["name"];
        $this->icon_file = $ELEARNING_INTERFACE_MODULES[$this->cms_type]["types"][$module_type]["icon"];
    }

    /**
    * get module-type
    *
    * returns module-type
    * @access public
    * @return string module-type
    */
    function getModuleType()
    {
        return $this->module_type;
    }

    /**
    * get module-type name
    *
    * returns module-type name
    * @access public
    * @return string module-type name
    */
    function getModuleTypeName()
    {
        return $this->module_type_name;
    }

    /**
    * set title
    *
    * sets title
    * @access public
    * @param string $module_title title
    */
    function setTitle($module_title)
    {
        $this->title = $module_title;
    }

    /**
    * get title
    *
    * returns title
    * @access public
    * @return string title
    */
    function getTitle()
    {
        return $this->title;
    }

    /**
    * set description
    *
    * sets description
    * @access public
    * @param string $module_description description
    */
    function setDescription($module_description)
    {
        $this->description = $module_description;
    }

    /**
    * get description
    *
    * returns description
    * @access public
    * @return string description
    */
    function getDescription()
    {
        return $this->description;
    }

    /**
    * set authors
    *
    * sets authors
    * @access public
    * @param array $module_authors authors
    */
    function setAuthors($module_authors)
    {
        $this->authors = $module_authors;
    }

    /**
    * get authors
    *
    * returns authors
    * @access public
    * @return array authors
    */
    function getAuthors()
    {
        return $this->authors;
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
        $this->is_connected = true;
//      echo "$this->id, $this->module_type, $this->cms_type";
        return ObjectConnections::setConnection($seminar_id, $this->id, $this->module_type, $this->cms_type);
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
        $this->is_connected = false;
        return ObjectConnections::unsetConnection($seminar_id, $this->id, $this->module_type, $this->cms_type);
    }

    /**
    * set connection-status
    *
    * sets connection-status
    * @access public
    * @param boolean $is_connected connection-status
    */
    function setConnectionType($is_connected)
    {
        $this->is_connected = $is_connected;
    }

    /**
    * get connection-status
    *
    * returns true, if module is connected to seminar
    * @access public
    * @return boolean connection-status
    */
    function isConnected()
    {
        return $this->is_connected;
    }

    /**
    * get reference string
    *
    * returns reference string for content-module
    * @access public
    * @return string reference string
    */
    function getReferenceString()
    {
        return $this->cms_type."_".$this->module_type."_".$this->id;
    }

    /**
    * get icon-image
    *
    * returns icon-image
    * @access public
    * @return string icon-image
    */
    function getIcon()
    {
        if (!$this->icon_file) {
            $this->icon_file = 'learnmodule';
        }
        if (mb_strpos('http', $this->icon_file) === 0) {
            return "<img src=\"" . $this->icon_file . "\">";
        } else {
            return Icon::create($this->icon_file, 'inactive')->asImg();
        }
    }

    /**
    * get module-status
    *
    * returns true, if module is a dummy
    * @access public
    * @return boolean module-status
    */
    function isDummy()
    {
        return $this->is_dummy;
    }

    /**
    * create module-dummy
    *
    * sets title and description of module to display error-message
    * @access public
    * @param string $error error-type
    */
    function createDummyForErrormessage($error = "unknown")
    {
        global $connected_cms;

        switch($error)
        {
            case "no permission":
                $this->setTitle(_("--- Keine Lese-Berechtigung! ---"));
                $this->setDescription(sprintf(_("Sie haben im System \"%s\" keine Lese-Berechtigung für das Lernmodul, das dieser Veranstaltung / Einrichtung an dieser Stelle zugeordnet ist."), $this->getCMSName()));
                break;
            case "not found":
                $this->setTitle(_("--- Dieses Content-Modul existiert nicht mehr im angebundenen System! ---"));
                $this->setDescription(sprintf(_("Das Lernmodul, das dieser Veranstaltung / Einrichtung an dieser Stelle zugeordnet war, existiert nicht mehr. Dieser Fehler tritt auf, wenn das angebundene LCMS \"%s\" nicht erreichbar ist oder wenn das Lernmodul innerhalb des angebundenen Systems gelöscht wurde."), $this->getCMSName()));
                break;
            case "deleted":
                $this->setTitle(_("--- Dieses Content-Modul wurde im angebundenen System gelöscht! ---"));
                $this->setDescription(sprintf(_("Das Lernmodul, das dieser Veranstaltung / Einrichtung an dieser Stelle zugeordnet war, wurde gelöscht."), $this->getCMSName()));
                break;
            default:
                $this->setTitle(_("--- Es ist ein unbekannter Fehler aufgetreten! ---"));
                $this->setDescription(sprintf(_("Unbekannter Fehler beim Lernmodul mit der Referenz-ID \"%s\" im LCMS \"%s\""), $this->getId(), $this->getCMSName()));
        }

        $this->is_dummy = true;
    }

    /**
    * ask for permission for given operation
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @param string $operation operation
    * @return boolean returns false
    */
    function isAllowed($operation)
    {
        return false;
    }
}
?>
