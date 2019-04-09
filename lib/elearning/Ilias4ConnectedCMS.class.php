<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * main-class for connection to ILIAS 4
 *
 * This class contains the main methods of the elearning-interface to connect to ILIAS 4. Extends Ilias3ConnectedCMS.
 *
 * @author   Arne Schröder <schroeder@data-quest.de>
 * @access   public
 * @modulegroup  elearning_interface_modules
 * @module       Ilias4ConnectedCMS
 * @package  ELearning-Interface
 */
class Ilias4ConnectedCMS extends Ilias3ConnectedCMS
{
    var $user_category_node_id;
    var $ldap_enable;
    /**
     * constructor
     *
     * init class.
     * @access public
     * @param string $cms system-type
     */
    function __construct($cms)
    {
        global $messages;
        parent::__construct($cms);
        if (ELearningUtils::getConfigValue("user_category_id", $cms)) {
            $this->user_category_node_id = ELearningUtils::getConfigValue("user_category_id", $cms);
        } else {
            $this->user_category_node_id = $this->main_category_node_id;
        }
        if (ELearningUtils::getConfigValue("ldap_enable", $cms)) {
            $this->ldap_enable = ELearningUtils::getConfigValue("ldap_enable", $cms);
        }
    }

    /**
     * Helper function to fetch childs including objects in folders
     *
     * @access public
     * @param string $parent_id
     * @return array result
     */
    function getChilds($parent_id) {
        $types[] = 'fold';
        foreach ($this->types as $type => $name) {
            $types[] = $type;
        }

        $result = $this->soap_client->getTreeChilds($parent_id, $types, $this->user->getId());
        if ($result) {
            $parent_path = $this->soap_client->getRawPath($parent_id) . '_' . $parent_id;
            foreach($result as $ref_id => $data) {
                // Workaround: getTreeChilds() liefert ALLE Referenzen der beteiligten Objekte, hier sollen aber nur die aus dem Kurs geprüft werden. Deshalb Abgleich der Pfade aller gefundenen Objekt-Referenzen.
                if (($data["accessInfo"] != "granted") OR ($this->soap_client->getRawPath($ref_id) != $parent_path))
                    unset($result[$ref_id]);
                elseif ($data['type'] == 'fold') {
                    unset($result[$ref_id]);
                    $result = $result + $this->getChilds($ref_id);
                }
            }
        }

        if (is_array($result))
            return $result;
        else
            return [];
    }

    /**
     * check connected modules and update connections
     *
     * checks if there are modules in the course that are not connected to the seminar
     * @access public
     * @param string $course_id course-id
     * @return boolean successful
     */
    function updateConnections($course_id)
    {
        global $connected_cms, $messages, $object_connections;

        $db = DBManager::get();

        $result = $this->soap_client->getObjectByReference($course_id);
        if ($result) {
            $course_path = $this->soap_client->getRawPath($course_id) . '_' . $result["ref_id"];
        }
        $this->soap_client->setCachingStatus(false);
        // fetch childs
        $result = $this->getChilds($course_id);

        if (is_array($result)) {
            $check = $db->prepare("SELECT 1 FROM object_contentmodules WHERE object_id = ? AND module_id = ? AND system_type = ? AND module_type = ?");
            $found = [];
            $added = 0;
            $deleted = 0;
            $messages["info"] .= "<b>".sprintf(_("Aktualisierung der Zuordnungen zum System \"%s\":"), $this->getName()) . "</b><br>";
            foreach($result as $ref_id => $data) {
                $check->execute([Context::getId(), $ref_id, $this->cms_type, $data["type"]]);
                if (!$check->fetch()) {
                    $messages["info"] .= sprintf(_("Zuordnung zur Lerneinheit \"%s\" wurde hinzugefügt."), ($data["title"])) . "<br>";
                    ObjectConnections::setConnection(Context::getId(), $ref_id, $data["type"], $this->cms_type);
                    $added++;
                }
                $found[] = $ref_id . '_' . $data["type"];
            }
            $to_delete = $db->prepare("SELECT module_id,module_type FROM object_contentmodules WHERE module_type <> 'crs' AND object_id = ? AND system_type = ? AND CONCAT_WS('_', module_id,module_type) NOT IN (?)");
            $to_delete->execute([Context::getId(), $this->cms_type, count($found) ? $found : ['']]);
            while ($row = $to_delete->fetch(PDO::FETCH_ASSOC)) {
                ObjectConnections::unsetConnection(Context::getId(), $row["module_id"], $row["module_type"], $this->cms_type);
                $deleted++;
                $messages["info"] .= sprintf(_("Zuordnung zu \"%s\" wurde entfernt."), $row["module_id"]  . '_' . $row["module_type"]) . "<br>";
            }
            if (($added + $deleted) < 1) {
                $messages["info"] .= _("Die Zuordnungen sind bereits auf dem aktuellen Stand.") . "<br>";
            }
        }
        ELearningUtils::bench("update connections");
    }

    /**
     * create course
     *
     * creates new ilias course
     * @access public
     * @param string $seminar_id seminar-id
     * @return boolean successful
     */
    function createCourse($seminar_id)
    {
        global $messages, $DEFAULT_LANGUAGE, $ELEARNING_INTERFACE_MODULES;

        $crs_id = ObjectConnections::getConnectionModuleId($seminar_id, "crs", $this->cms_type);
        $this->soap_client->setCachingStatus(false);
        $this->soap_client->clearCache();

        if ($crs_id == false) {
            $seminar = Seminar::getInstance($seminar_id);
            $home_institute = Institute::find($seminar->getInstitutId());
            if ($home_institute) {
                $ref_id = ObjectConnections::getConnectionModuleId($home_institute->getId(), "cat", $this->cms_type);
            }
            if ($ref_id < 1) {
                // Kategorie für Heimateinrichtung anlegen
                $object_data["title"] = sprintf("%s", $home_institute->name);
                $object_data["description"] = sprintf(_("Hier befinden sich die Veranstaltungsdaten zur Stud.IP-Einrichtung \"%s\"."), $home_institute->name);
                $object_data["type"] = "cat";
                $object_data["owner"] =  $this->soap_client->LookupUser($ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["username"]);
                $ref_id = $this->soap_client->addObject($object_data, $this->main_category_node_id);
                ObjectConnections::setConnection($home_institute->getId(), $ref_id, "cat", $this->cms_type);
            }
            if ($ref_id < 1) {
                $ref_id = $this->main_category_node_id;
            }

            // Kurs anlegen
            $lang_array = explode("_",$DEFAULT_LANGUAGE);
            $course_data["language"] = $lang_array[0];
            $course_data["title"] = "Stud.IP-Kurs " . $seminar->getName();
            $course_data["description"] = "";
            $crs_id = $this->soap_client->addCourse($course_data, $ref_id);
            if ($crs_id == false) {
                $messages["error"] .= _("Zuordnungs-Fehler: Kurs konnte nicht angelegt werden.");
                return false;
            }
            ObjectConnections::setConnection($seminar_id, $crs_id, "crs", $this->cms_type);

            // Rollen zuordnen
            $this->permissions->CheckUserPermissions($crs_id);
        }
        return $crs_id;
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

        $this->soap_client->setCachingStatus(false);

        if ($cat_name != "") {
            $cat = $this->soap_client->getReferenceByTitle( trim( $cat_name ), "cat");
            if ($cat == false) {
                $messages["error"] .= sprintf(_("Das Objekt mit dem Namen \"%s\" wurde im System %s nicht gefunden."), htmlReady($cat_name), htmlReady($this->getName())) . "<br>\n";
            } elseif ($cat != "") {
                ELearningUtils::setConfigValue("category_id", $cat, $this->cms_type);
                $this->main_category_node_id = $cat;
            }
        }

        if (($this->main_category_node_id != false) AND (ELearningUtils::getConfigValue("user_category_id", $this->cms_type) == "")) {
            $object_data["title"] = sprintf(_("User-Daten"));
            $object_data["description"] = _("Hier befinden sich die persönlichen Ordner der Stud.IP-User.");
            $object_data["type"] = "cat";
            $object_data["owner"] = $this->user->getId();
            $user_cat = $connected_cms[$this->cms_type]->soap_client->addObject($object_data, $connected_cms[$this->cms_type]->main_category_node_id);
            if ($user_cat != false) {
                $this->user_category_node_id = $user_cat;
                ELearningUtils::setConfigValue("user_category_id", $user_cat, $this->cms_type);
            } else {
                $messages["error"] .= _("Die Kategorie für User-Daten konnte nicht angelegt werden.") . "<br>\n";
            }
        }

        if ($role_template_name != "") {
            $role_template = $this->soap_client->getObjectByTitle( trim( $role_template_name ), "rolt" );
            if ($role_template == false) {
                $messages["error"] .= sprintf(_("Das Rollen-Template mit dem Namen \"%s\" wurde im System %s nicht gefunden."), htmlReady($role_template_name), htmlReady($this->getName())) . "<br>\n";
            }
            if (is_array($role_template)) {
                ELearningUtils::setConfigValue("user_role_template_id", $role_template["obj_id"], $this->cms_type);
                ELearningUtils::setConfigValue("user_role_template_name", $role_template["title"], $this->cms_type);
                $this->user_role_template_id = $role_template["obj_id"];
            }
        }

        if (Request::submitted('submit')) {
            ELearningUtils::setConfigValue("encrypt_passwords", Request::option("encrypt_passwords"), $this->cms_type);
            $encrypt_passwords = Request::option("encrypt_passwords");
            ELearningUtils::setConfigValue("ldap_enable", Request::option("ldap_enable"), $this->cms_type);
            $this->ldap_enable = Request::option("ldap_enable");
        } else {
            if (ELearningUtils::getConfigValue("encrypt_passwords", $this->cms_type) != "")
            $encrypt_passwords = ELearningUtils::getConfigValue("encrypt_passwords", $this->cms_type);
        }

        $cat = $this->soap_client->getObjectByReference( $this->main_category_node_id );
        $user_cat = $this->soap_client->getObjectByReference( $this->user_category_node_id );
        $title = $this->link->getModuleLink($user_cat["title"], $this->user_category_node_id, "cat");
        $ldap_options = [];
        foreach (StudipAuthAbstract::GetInstance() as $plugin) {
            if ($plugin instanceof StudipAuthLdap) {
                $ldap_options[] = '<option '.($plugin->plugin_name == $this->ldap_enable ? 'selected' : '').'>' . $plugin->plugin_name . '</option>';
            }
        }
        ob_start();
        ConnectedCMS::getPreferences();
        $module_types = ob_get_clean();

        $template = $GLOBALS['template_factory']->open('elearning/ilias4_connected_cms_preferences.php');
        $template->set_attribute('messages', $messages);
        $template->set_attribute('soap_error', $this->soap_client->getError());
        $template->set_attribute('soap_data', $this->soap_data);
        $template->set_attribute('main_category_node_id',  $this->main_category_node_id);
        $template->set_attribute('main_category_node_id_title', $cat['title']);
        $template->set_attribute('user_category_node_id',  $this->user_category_node_id);
        $template->set_attribute('user_category_node_id_title', $title);
        $template->set_attribute('user_role_template_name', ELearningUtils::getConfigValue("user_role_template_name", $this->cms_type));
        $template->set_attribute('user_role_template_id', $this->user_role_template_id);
        $template->set_attribute('encrypt_passwords', $encrypt_passwords);
        $template->set_attribute('ldap_options', count($ldap_options) ? join("\n", array_merge(['<option></option>'], $ldap_options)) : '');
        $template->set_attribute('module_types', $module_types);
        echo $template->render();
    }

}
