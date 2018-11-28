<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* class to generate links to connected systems
*
* This class contains methods to generate links to connected content-management-systems.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ConnectedLink
* @package  ELearning-Interface
*/

use Studip\Button, Studip\LinkButton;

class ConnectedLink
{
    var $cms_type;
    var $cms_link;
    /**
    * constructor
    *
    * init class. don't call directly, class is loaded by ConnectedCMS.
    * @access public
    * @param string $cms system-type
    */
    function __construct($cms)
    {
        global $ELEARNING_INTERFACE_MODULES;

        $this->cms_type = $cms;
        $this->cms_link = $ELEARNING_INTERFACE_MODULES[$cms]["ABSOLUTE_PATH_ELEARNINGMODULES"] . $ELEARNING_INTERFACE_MODULES[$cms]["target_file"];
    }

    /**
    * get link to create new account
    *
    * returns link to create new user-account
    * @access public
    * @return string html-code
    */
    function getNewAccountLink()
    {
        global $connected_cms, $cms_select, $current_module;

        $output .= "<form method=\"POST\" action=\"" . URLHelper::getLink() . "\">\n";
        $output .= CSRFProtection::tokenTag();
        $output .= "<input type=\"HIDDEN\" name=\"view\" value=\"" . Request::option('view') . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"ref_id\" value=\"" . htmlReady($connected_cms[$this->cms_type]->content_module[$current_module]->getId()) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_type\" value=\"" . htmlReady($connected_cms[$this->cms_type]->content_module[$current_module]->getModuleType()) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"search_key\" value=\"" . htmlReady(Request::get('search_key')) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"cms_select\" value=\"" . htmlReady($cms_select) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"new_account_cms\" value=\"" . htmlReady($this->cms_type) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"new_account_step\" value=\"0\">\n";
        $output .= Button::createAccept(_('Starten'), 'start');
        $output .= "</form>";
        return $output;
    }

    /**
    * get module-links for user
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @return boolean returns false
    */
    function getUserModuleLinks()
    {
        return false;
    }

    /**
    * get module-links for admin
    *
    * returns links to remove or add module to object
    * @access public
    * @return string html-code
    */
    function getAdminModuleLinks()
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;

        $output .= "<form method=\"POST\" action=\"" . URLHelper::getLink() . "\">\n";
        $output .= CSRFProtection::tokenTag();
        $output .= "<input type=\"HIDDEN\" name=\"view\" value=\"" . htmlReady($view) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"search_key\" value=\"" . htmlReady($search_key) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"cms_select\" value=\"" . htmlReady($cms_select) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_type\" value=\"" . htmlReady($connected_cms[$this->cms_type]->content_module[$current_module]->getModuleType()) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_id\" value=\"" . htmlReady($connected_cms[$this->cms_type]->content_module[$current_module]->getId()) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_system_type\" value=\"" . htmlReady($this->cms_type) . "\">\n";

        if ($connected_cms[$this->cms_type]->content_module[$current_module]->isConnected())
            $output .= "&nbsp;" . Button::create(_('Entfernen'), 'remove');
        else
            $output .= "&nbsp;" . Button::create(_('Hinzufügen'), 'add');
        $output .= "</form>";

        return $output;
    }

    /**
    * get new module link
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @return boolean returns false
    */
    function getNewModuleLink()
    {
        return false;
    }

    /**
    * get start page link
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @return boolean returns false
    */
    function getStartpageLink()
    {
        return false;
    }
}
?>
