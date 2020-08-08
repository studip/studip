<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

/**
* class to generate links to ILIAS 3
*
* This class contains methods to generate links to ILIAS 3.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       Ilias3ConnectedLink
* @package  ELearning-Interface
*/
class Ilias3ConnectedLink extends ConnectedLink
{
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
        $this->cms_link = "ilias3_referrer.php";
    }

    /**
    * get user module links
    *
    * returns content module links for user
    * @access public
    * @return string html-code
    */
    function getUserModuleLinks()
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;

        if ($connected_cms[$this->cms_type]->isAuthNecessary() AND (! $connected_cms[$this->cms_type]->user->isConnected()))
        {
            $output .= $this->getNewAccountLink();
        }
        else
        {
            if (! $connected_cms[$this->cms_type]->content_module[$current_module]->isDummy() )
            {
                if ($connected_cms[$this->cms_type]->content_module[$current_module]->isAllowed(OPERATION_READ))
                {

                    $output .= LinkButton::create(_('Starten'), URLHelper::getURL($this->cms_link . "?"
                        . "client_id=" . $connected_cms[$this->cms_type]->getClientId()
                        . "&cms_select=" . $this->cms_type
//                      . "&sess_id=" . $connected_cms[$this->cms_type]->user->getSessionId()
                        . "&ref_id=" . $connected_cms[$this->cms_type]->content_module[$current_module]->getId()
                        . "&type=" . $connected_cms[$this->cms_type]->content_module[$current_module]->getModuleType()
                        . $auth_data
                        . "&target=start"), [
                            'target' => '_blank',
                            'rel'    => 'noopener noreferrer',
                        ]);
                    $output .= "&nbsp;";
                }
                if ($connected_cms[$this->cms_type]->content_module[$current_module]->isAllowed(OPERATION_WRITE))
                {
                    $output .= LinkButton::create(_('Bearbeiten'), URLHelper::getURL($this->cms_link . "?"
                        . "client_id=" . $connected_cms[$this->cms_type]->getClientId()
                        . "&cms_select=" . $this->cms_type
//                      . "&sess_id=" . $connected_cms[$this->cms_type]->user->getSessionId()
                        . "&ref_id=" . $connected_cms[$this->cms_type]->content_module[$current_module]->getId()
                        . "&type=" . $connected_cms[$this->cms_type]->content_module[$current_module]->getModuleType()
                        . $auth_data
                        . "&target=edit"), [
                            'target' => '_blank',
                            'rel'    => 'noopener noreferrer',
                        ]);
                    $output .= "&nbsp;";

                }
            }
        }

        return $output;
    }

    /**
    * get admin module links
    *
    * returns links add or remove a module from course
    * @access public
    * @return string returns html-code
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
        elseif ($connected_cms[$this->cms_type]->content_module[$current_module]->isAllowed(OPERATION_WRITE))
        {
            $output .= "<div align=\"left\"><input type=\"CHECKBOX\" value=\"1\" name=\"write_permission\" style=\"vertical-align:middle\">";
            $output .= _("Mit Schreibrechten für alle Lehrenden/Tutoren und Tutorinnen dieser Veranstaltung") . "<br>";
            $output .= "<input type=\"CHECKBOX\" value=\"1\" style=\"vertical-align:middle\" name=\"write_permission_autor\">";
            $output .= _("Mit Schreibrechten für alle Teilnehmenden dieser Veranstaltung") . "</div>";
            $output .=  Button::create(_('Hinzufügen'), 'add') . "<br>";
        }
        else
            $output .= "&nbsp;" . Button::create(_('Hinzufügen'), 'add');
        $output .= "</form>";

        return $output;
//      $output .= parent::getAdminModuleLinks();
    }

    /**
    * get new module link
    *
    * returns link to create a new module if allowed
    * @access public
    * @return string returns html-code or false
    */
    function getNewModuleLink()
    {
        global $connected_cms, $auth;
        $output = "\n";
//      echo "NML.";
        if ((Request::get("module_type_" . $this->cms_type) != ""))
        {
//          echo "TYPE.";
            if ($connected_cms[$this->cms_type]->user->category == "")
            {
//              echo "NoCat.";
                $connected_cms[$this->cms_type]->user->newUserCategory();
                if ($connected_cms[$this->cms_type]->user->category == false)
                    return $output;
            }
            $output = "&nbsp;" . LinkButton::create(_('Neu anlegen'), URLHelper::getURL($this->cms_link . "?"
                . "client_id=" . $connected_cms[$this->cms_type]->getClientId()
                . "&cms_select=" . $this->cms_type
//              . "&sess_id=" . $connected_cms[$this->cms_type]->user->getSessionId()
                . "&ref_id=" . $connected_cms[$this->cms_type]->user->category
                . $auth_data
                . "&type=" . Request::option("module_type_" . $this->cms_type) . "&target=new"), [
                    'target' => '_blank',
                    'rel'    => 'noopener noreferrer',
                ]);
//          echo $output . ".";
        }
        $user_crs_role = $connected_cms[$this->cms_type]->crs_roles[$auth->auth["perm"]];
        if ($user_crs_role=="admin")
            return $output;
        else
            return false;
    }

    /**
    * get start page link
    *
    * returns link to ilias start-page
    * @access public
    * @return string returns url or false
    */
    function getStartpageLink()
    {
        global $connected_cms;

        if ($connected_cms[$this->cms_type]->user->isConnected()) {
            $output = $this->cms_link . "?"
                . "client_id=" . $connected_cms[$this->cms_type]->getClientId()
                . "&cms_select=" . $this->cms_type
                . "&target=login";
        }
        return $output;
    }
}
?>
