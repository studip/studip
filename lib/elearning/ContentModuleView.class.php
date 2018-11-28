<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* class to show content-module data
*
* This class contains methods for output of connected module data.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ContentModuleView
* @package  ELearning-Interface
*/
class ContentModuleView
{
    var $view_mode;
    var $change_date;
    var $module_new;
    var $cms_type;
    /**
    * constructor
    *
    * init class. don't call directly, class is loaded by ContentModule.
    * @access public
    * @param string $cms system-type
    */
    function __construct($cms)
    {
        global $connected_cms;

        $this->change_date = 0;
        $this->module_new = false;
        $this->cms_type = $cms;
        $this->setViewMode("closed");
    }

    /**
    * show module-data
    *
    * show module-data in printhead/printcontent-style. user-mode
    * @access public
    */
    function show($mode = "")
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module, $anker_target;

        $content_module = $connected_cms[$this->cms_type]->content_module[$current_module];

        if ( (! $content_module->isDummy()) AND ($connected_cms[$this->cms_type]->isAuthNecessary() == true) AND ($connected_cms[$this->cms_type]->user->isConnected() == true)) {
            if (! $content_module->isAllowed(OPERATION_VISIBLE)) {
                return false;
            }
        }

        if ($_SESSION['elearning_open_close'][$content_module->getReferenceString()] == true)
            $this->setViewMode("open");
        $module_title = $content_module->getTitle();
/*/
        if ($mode == "searchresult") {
            $module_title = $module_title . " (ID " . $content_module->getId() . ", ";
            if ($content_module->isAllowed(OPERATION_WRITE))
                $module_title = $module_title . " " . _("Schreibzugriff") . ")";
            else
                $module_title = $module_title . " " . _("Lesezugriff") . ")";
        }/**/
        if ($this->isOpen() == true)
            $module_link = URLHelper::getLink('?do_close='. $content_module->getReferenceString() . '&view='.$view.'&search_key='.$search_key.'&cms_select='.$cms_select.'#anker');
        else
            $module_link = URLHelper::getLink('?do_open='. $content_module->getReferenceString() . '&view='.$view.'&search_key='.$search_key.'&cms_select='.$cms_select.'#anker');
        if (! $content_module->isDummy())
            $module_buttons = $connected_cms[$this->cms_type]->link->getUserModuleLinks();
        $template = $GLOBALS['template_factory']->open('elearning/_content_module.php');
        $template->set_attribute('module_anker_target', ($anker_target == $content_module->getReferenceString()));
        $template->set_attribute('module_link', $module_link);
        $template->set_attribute('module_buttons', $module_buttons);
        $template->set_attribute('module_authors', $content_module->getAuthors());
        $template->set_attribute('module_title', $module_title);
        $template->set_attribute('module_description', $content_module->getDescription());
        $template->set_attribute('module_is_new', $this->module_new);
        $template->set_attribute('module_chdate', $this->change_date);
        $template->set_attribute('module_reference', $content_module->getReferenceString());
        $template->set_attribute('module_source', $content_module->getCMSName() . " / " . $content_module->getModuleTypeName());
        $template->set_attribute('module_icon', $content_module->getIcon());
        $template->set_attribute('module_is_open', $this->isOpen());
        return $template->render();
    }

    /**
    * show module-data to admin
    *
    * show module-data in printhead/printcontent-style. admin-mode
    * @access public
    */
    function showAdmin($mode = "")
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module, $anker_target;

        $content_module = $connected_cms[$this->cms_type]->content_module[$current_module];

        if ( (! $content_module->isDummy()) AND ($connected_cms[$this->cms_type]->isAuthNecessary() == true) AND ($connected_cms[$this->cms_type]->user->isConnected() == true)) {
            if (! $content_module->isAllowed(OPERATION_VISIBLE)) {
                return false;
            }
        }

        if ($_SESSION['elearning_open_close'][$content_module->getReferenceString()] == true)
            $this->setViewMode("open");

        $module_title = $content_module->getTitle();
        $module_title = $module_title . " (ID " . $content_module->getId();
        if ($content_module->isAllowed(OPERATION_WRITE))
            $module_title = $module_title . ", " . _("Schreibzugriff");
        elseif ($content_module->isAllowed(OPERATION_READ))
            $module_title = $module_title . ", " . _("Lesezugriff");
        else
            $module_title = $module_title . ", " . _("kein Lesezugriff");
        $module_title = $module_title . ")";

        if ($this->isOpen() == true)
            $module_link = URLHelper::getLink('?do_close='. $content_module->getReferenceString() . '&view='.$view.'&search_key='.$search_key.'&cms_select='.$cms_select.'#anker');
        else
            $module_link = URLHelper::getLink('?do_open='. $content_module->getReferenceString() . '&view='.$view.'&search_key='.$search_key.'&cms_select='.$cms_select.'#anker');

        $template = $GLOBALS['template_factory']->open('elearning/_content_module.php');
        $template->set_attribute('module_anker_target', ($anker_target == $content_module->getReferenceString()));
        $template->set_attribute('module_link', $module_link);
        if ($content_module->isAllowed(OPERATION_READ))
            $module_buttons = $connected_cms[$this->cms_type]->link->getAdminModuleLinks();
        $template->set_attribute('module_buttons', $module_buttons);
        $template->set_attribute('module_authors', $content_module->getAuthors());
        $template->set_attribute('module_title', $module_title);
        $template->set_attribute('module_description', $content_module->getDescription());
        $template->set_attribute('module_is_new', $this->module_new);
        $template->set_attribute('module_chdate', $this->change_date);
        $template->set_attribute('module_reference', $content_module->getReferenceString());
        $template->set_attribute('module_source', $content_module->getCMSName() . " / " . $content_module->getModuleTypeName());
        $template->set_attribute('module_icon', $content_module->getIcon());
        $template->set_attribute('module_is_open', $this->isOpen());
        return $template->render();
    }

    /**
    * get open-status
    *
    * returns true, if module is opened
    * @access public
    * @return boolean open-status
    */
    function isOpen()
    {
        if ($this->view_mode == "open")
            return true;
        else
            return false;
    }

    /**
    * set view-mode
    *
    * sets view-mode
    * @access public
    * @param boolean $module_mode view-mode
    */
    function setViewMode($module_mode)
    {
        $this->view_mode = $module_mode;
    }

    /**
    * set changedate
    *
    * sets changedate for view
    * @access public
    * @param string $module_chdate changedate
    */
    function setChangeDate($module_chdate)
    {
        $this->change_date = $module_chdate;

        if (object_get_visit(Context::getId(), "elearning_interface") < $this->change_date)
            $this->module_new = true;
        else
            $this->module_new = false;
    }
}
?>
