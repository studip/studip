<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

/**
* main-class for connection to ILIAS 3
*
* This class contains the main methods of the elearning-interface to connect to ILIAS 3. Extends ConnectedCMS.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       Ilias3ConnectedCMS
* @package  ELearning-Interface
*/
class Ilias3ConnectedCMS extends ConnectedCMS
{
    var $client_id;
//  var $root_user_id;
    var $root_user_sid;
    var $main_category_node_id;
    var $user_role_template_id;
    var $user_skin;
    var $user_style;
    var $crs_roles;
    var $global_roles;

    var $db_class_object;
    var $db_class_tree;
    var $db_class_course;

    var $soap_client;
    /**
    * constructor
    *
    * init class.
    * @access public
    * @param string $cms system-type
    */
    function __construct($cms)
    {
        global $ELEARNING_INTERFACE_MODULES;

        parent::__construct($cms);

        require_once($this->CLASS_PREFIX . "Soap.class.php");
        $classname = $this->CLASS_PREFIX . "Soap";
        $this->soap_client = new $classname($this->cms_type);
        $this->soap_client->setCachingStatus(true);

        $this->main_category_node_id = ELearningUtils::getConfigValue("category_id", $cms);

        if ((ELearningUtils::getConfigValue("user_role_template_id", $cms) == "") AND ($GLOBALS["role_template_name"] == ""))
            $GLOBALS["role_template_name"] = "Author";
        $this->user_role_template_id = ELearningUtils::getConfigValue("user_role_template_id", $cms);
        $this->user_skin = ELearningUtils::getConfigValue("user_skin", $cms);
        $this->user_style = ELearningUtils::getConfigValue("user_style", $cms);
        $this->encrypt_passwords = ELearningUtils::getConfigValue("encrypt_passwords", $cms);

        $this->crs_roles = $ELEARNING_INTERFACE_MODULES[$cms]["crs_roles"];
        $this->client_id = $ELEARNING_INTERFACE_MODULES[$cms]["soap_data"]["client"];
        $this->global_roles = $ELEARNING_INTERFACE_MODULES[$cms]["global_roles"];
//      $this->root_user_sid = $this->soap_client->login();
        $this->is_first_call = true;
    }

    /**
    * get preferences
    *
    * shows additional settings.
    * @access public
    */
    function getPreferences()
    {
        global $connected_cms;

        $role_template_name = Request::get('role_template_name');
        $cat_name = Request::get('cat_name');
        $style_setting = Request::option('style_setting');
        $encrypt_passwords = Request::option('encrypt_passwords');

        $this->soap_client->setCachingStatus(false);

        if ($cat_name != "")
        {
            $cat = $this->soap_client->getReferenceByTitle( trim( $cat_name ), "cat");
            if ($cat == false)
                $messages["error"] .= sprintf(_("Das Objekt mit dem Namen \"%s\" wurde im System %s nicht gefunden."), htmlReady($cat_name), htmlReady($this->getName())) . "<br>\n";
            if ($cat != "")
            {
                ELearningUtils::setConfigValue("category_id", $cat, $this->cms_type);
                $this->main_category_node_id = $cat;
            }
        }

        if ($role_template_name != "")
        {
            $role_template = $this->soap_client->getObjectByTitle( trim( $role_template_name ), "rolt" );
            if ($role_template == false)
                $messages["error"] .= sprintf(_("Das Rollen-Template mit dem Namen \"%s\" wurde im System %s nicht gefunden."), htmlReady($role_template_name), htmlReady($this->getName())) . "<br>\n";
            if (is_array($role_template))
            {
                ELearningUtils::setConfigValue("user_role_template_id", $role_template["obj_id"], $this->cms_type);
                ELearningUtils::setConfigValue("user_role_template_name", $role_template["title"], $this->cms_type);
                $this->user_role_template_id = $role_template["obj_id"];
            }
        }

        if (Request::submitted('submit'))
        {
            ELearningUtils::setConfigValue("user_style", $style_setting, $this->cms_type);
            ELearningUtils::setConfigValue("user_skin", $style_setting, $this->cms_type);
            ELearningUtils::setConfigValue("encrypt_passwords", $encrypt_passwords, $this->cms_type);
        }
        else
        {
            if (ELearningUtils::getConfigValue("user_style", $this->cms_type) != "")
                $style_setting = ELearningUtils::getConfigValue("user_style", $this->cms_type);
            if (ELearningUtils::getConfigValue("encrypt_passwords", $this->cms_type) != "")
                $encrypt_passwords = ELearningUtils::getConfigValue("encrypt_passwords", $this->cms_type);
        }


        if ($messages["error"] != "")
            echo "<b>" . Icon::create('decline', 'attention')->asImg(['class' => 'text-top', 'title' => _('Fehler')]) . " " . $messages["error"] . "</b><br><br>";

        echo "<table>";
        echo "<tr valign=\"top\"><td width=30% align=\"left\"><font size=\"-1\">";
        echo "<b>" . _("SOAP-Verbindung: ") . "</b>";
        echo "</td><td><font size=\"-1\">";
        $error = $this->soap_client->getError();
        if ($error != false)
            echo sprintf(_("Beim Herstellen der SOAP-Verbindung trat folgender Fehler auf:")) . "<br><br>" . $error;
        else
            echo sprintf(_("Die SOAP-Verbindung zum Klienten \"%s\" wurde hergestellt, der Name des Administrator-Accounts ist \"%s\"."), htmlReady($this->soap_data["client"]), htmlReady($this->soap_data["username"]));
        echo "<br>\n";
        echo "<br>\n";
        echo "</td></tr><tr><td  width=30% align=\"left\"><font size=\"-1\">";

        $cat = $this->soap_client->getObjectByReference( $this->main_category_node_id );
        echo "<b>" . _("Kategorie: ") . "</b>";
        echo "</td><td>";
        echo "<input type=\"text\" size=\"20\" border=0 value=\"" . $cat["title"] . "\" name=\"cat_name\">&nbsp;";
        echo Icon::create('info-circle', 'inactive', ['title' => _('Geben Sie hier den Namen einer bestehenden ILIAS 3 - Kategorie ein, in der die Lernmodule und User-Kategorien abgelegt werden sollen.')])->asImg();
        echo "</td></tr><tr><td></td><td><font size=\"-1\">";
        echo " (ID " . $this->main_category_node_id;
        if ($cat["description"] != "")
            echo ", " . _("Beschreibung: ") . htmlReady($cat["description"]);
        echo ")";
        echo "<br>\n";
        echo "<br>\n";
        echo "</td></tr><tr><td  width=30% align=\"left\"><font size=\"-1\">";


        echo "<b>" . _("Rollen-Template für die persönliche Kategorie: ") . "</b>";
        echo "</td><td>";
        echo "<input type=\"text\" size=\"20\" border=0 value=\"" . ELearningUtils::getConfigValue("user_role_template_name", $this->cms_type) . "\" name=\"role_template_name\">&nbsp;";
        echo Icon::create('info-circle', 'inactive', ['title' => _('Geben Sie den Namen des Rollen-Templates ein, das für die persönliche Kategorie von Lehrenden verwendet werden soll (z.B. \"Author\").')])->asImg();
        echo "</td></tr><tr><td></td><td><font size=\"-1\">";
        echo " (ID " . $this->user_role_template_id;
        echo ")";
        echo "<br>\n";
        echo "<br>\n";
        echo "</td></tr><tr><td  width=30% align=\"left\"><font size=\"-1\">";

        echo "<b>" . _("Passwörter: ") . "</b>";
        echo "</td><td><font size=\"-1\">";
        echo "<input type=\"checkbox\" border=0 value=\"md5\" name=\"encrypt_passwords\"";
        if ($encrypt_passwords == "md5")
            echo " checked";
        echo ">&nbsp;" . _("ILIAS-Passwörter verschlüsselt speichern.");
        echo Icon::create('info-circle', 'inactive', ['title' => _('Wählen Sie diese Option, wenn die ILIAS-Passwörter der zugeordneten Accounts verschlüsselt in der Stud.IP-Datenbank abgelegt werden sollen.')])->asImg();
        echo "</td></tr><tr><td></td><td><font size=\"-1\">";
        echo "<br>\n";
        echo "<br>\n";
        echo "</td></tr><tr><td  width=30% align=\"left\"><font size=\"-1\">";

        echo "<b>" . _("Style / Skin: ") . "</b>";
        echo "</td><td><font size=\"-1\">";
        echo "<input type=\"checkbox\" border=0 value=\"studip\" name=\"style_setting\"";
        if ($style_setting == "studip")
            echo " checked";
        echo ">&nbsp;" . _("Stud.IP-Style für neue Nutzer-Accounts voreinstellen.");
        echo Icon::create('info-circle', 'inactive', ['title' => _('Wählen Sie diese Option, wenn für alle von Stud.IP angelegten ILIAS-Accounts das Stud.IP-Layout als System-Style eingetragen werden soll. ILIAS-seitig angelegte Accounts erhalten weiterhin den Standard-Style.')])->asImg();
        echo "</td></tr><tr><td></td><td><font size=\"-1\">";
        echo "<br>\n";
        echo "<br>\n";




        echo "</td></tr>";
        echo "</table>";
        echo "<center>" . Button::create(_('übernehmen'), 'submit') . "</center><br>";
        echo "<br>\n";

        parent::getPreferences();

        echo "<br>\n";
    }

    function setContentModule($data, $is_connected = false)
    {
        parent::setContentModule($data, $is_connected);

        if ($data["owner"] != "")
        {
            $user_data = $this->soap_client->getUser($data["owner"]);
            $user_name = trim($user_data["title"] . " " . $user_data["firstname"] . " " . $user_data["lastname"]);
            $this->content_module[$data["ref_id"]]->setAuthors($user_name);
        }
        $this->content_module[$data["ref_id"]]->setPermissions($data["accessInfo"], $data["operations"]);
    }

    /**
    * create new instance of subclass content-module
    *
    * creates new instance of subclass content-module and gets permissions
    * @access public
    * @param string $module_id module-id
    * @param string $module_type module-type
    * @param string $is_connected is module connected to seminar?
    */
    function newContentModule($module_id, $module_type, $is_connected = false)
    {
        global $seminar_id, $current_module, $caching_active;

        $current_module = $module_id;
//      echo "call module $module_id";

        if ($this->is_first_call  AND ($seminar_id != "") AND ($is_connected == true))
        {
            $id = ObjectConnections::getConnectionModuleId( $seminar_id, "crs", $this->cms_type );
            if ($id != false)
            {
                if ($this->user->isConnected())
                    $this->permissions->checkUserPermissions($id);
                $this->is_first_call = false;
            }
//          echo "first call, ref_id $id";
        }

        parent::newContentModule($module_id, $module_type, $is_connected);
    }

    /**
    * get user modules
    *
    * returns user content modules
    * @access public
    * @return array list of content modules
    */
    function getUserContentModules()
    {
        global $connected_cms;

        $types = [];
        foreach ($this->types as $type => $name)
        {
            $types[] = $type;
        }
        if ($this->user->getCategory() == false)
            return false;
        $result = $this->soap_client->getTreeChilds($this->user->getCategory(), $types, $connected_cms[$this->cms_type]->user->getId());
        $obj_ids = [];
        if (is_array($result))
            foreach($result as $key => $object_data)
                if (is_array($object_data["operations"]))
                    if ((!in_array($object_data["obj_id"], $obj_ids) && in_array(OPERATION_READ, $object_data["operations"]))
                    || in_array(OPERATION_WRITE, $object_data["operations"]))
                    {
                    if (is_array($user_modules[$object_data["obj_id"]]["operations"]))
                        if (in_array(OPERATION_WRITE, $user_modules[$object_data["obj_id"]]["operations"]))
                            continue;
                    $user_modules[$object_data["obj_id"]] = $object_data;
                    $obj_ids[] = $result[$key]["obj_id"];
                }
        return $user_modules;
    }

    /**
    * search for content modules
    *
    * returns found content modules
    * @access public
    * @param string $key keyword
    * @return array list of content modules
    */
    function searchContentModules($key)
    {
        global $connected_cms;

        $types = [];
        foreach ($this->types as $type => $name)
        {
            $types[] = $type;
        }

        $result = $this->soap_client->searchObjects($types, $key,"and", $connected_cms[$this->cms_type]->user->getId());
        return $result;
    }


    /**
    * get client-id
    *
    * returns client-id
    * @access public
    * @return string client-id
    */
    function getClientId()
    {
        return $this->client_id;
    }

    /**
    * get session-id
    *
    * returns soap-session-id
    * @access public
    * @return string session-id
    */
    function getSID()
    {
        return $this->root_user_sid;
    }

    /**
    * terminate
    *
    * terminates connection.
    * @access public
    * @return boolean returns false
    */
    function terminate()
    {
//      $this->soap_client->logout();
        $this->soap_client->saveCacheData();
    }

    //we have to delete the course only
    function deleteConnectedModules($object_id){
        global $connected_cms;
        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);
        $connected_cms[$this->cms_type]->soap_client->clearCache();
        $connected_cms[$this->cms_type]->soap_client->user_type == "admin";
        $crs_id = ObjectConnections::getConnectionModuleId($object_id, "crs", $this->cms_type);
        if($crs_id && $connected_cms[$this->cms_type]->soap_client->checkReferenceById($crs_id)){
            $connected_cms[$this->cms_type]->soap_client->deleteObject($crs_id);
        }
        return parent::deleteConnectedModules($object_id);
    }
}
?>
