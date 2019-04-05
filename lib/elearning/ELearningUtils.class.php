<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* class with several forms and tools for the elearning interface
*
* This class contains Utilities for the elearning-interface.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @package  ELearning-Interface
*/

use Studip\Button, Studip\LinkButton;

class ELearningUtils
{
    /**
     * loads class ConnectedCMS for given system-type and creates an instance
     *
     * @param string $cms system-type
     */
    public static function loadClass($cms)
    {
        global $connected_cms, $ELEARNING_INTERFACE_MODULES;

        if (!is_object($connected_cms[$cms])) {
            require_once "lib/elearning/{$ELEARNING_INTERFACE_MODULES[$cms]['CLASS_PREFIX']}ConnectedCMS.class.php";
            $classname = "{$ELEARNING_INTERFACE_MODULES[$cms]['CLASS_PREFIX']}ConnectedCMS";
            $connected_cms[$cms] = new $classname($cms);
            $connected_cms[$cms]->initSubclasses();
        }
    }

    public static function initElearningInterfaces()
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        if (is_array($ELEARNING_INTERFACE_MODULES)) {
            foreach (array_keys($ELEARNING_INTERFACE_MODULES) as $cms) {
                if (self::isCMSActive($cms)) {
                    self::loadClass($cms);
                }
            }
        }
        return is_array($connected_cms) ? count($connected_cms) : false;
    }

    /**
     * gets config-value with given name from globals
     *
     * @param string $name entry-name
     * @param string $cms system-type
     * @return boolean returns false if no cms is given
     */
    public static function getConfigValue($name, $cms)
    {
        if (!$cms) {
            return false;
        }
        return Config::get()->getValue("ELEARNING_INTERFACE_{$cms}_{$name}");
    }

    /**
     * set config-value
     *
     * writes config-value with given name and value to database
     *
     * @param string $name entry-name
     * @param string $value value
     * @param string $cms system-type
     */
    public static function setConfigValue($name, $value, $cms)
    {
        if (!$cms) {
            return;
        }

        try {
            Config::get()->store("ELEARNING_INTERFACE_{$cms}_{$name}", $value);
        } catch (InvalidArgumentException $e) {
            Config::get()->create("ELEARNING_INTERFACE_{$cms}_{$name}", [
                'value' => $value,
                'type'  => 'string',
            ]);
        }
    }

    /**
     * check cms-status
     *
     * checks if connected content-management-system is activated
     *
     * @param string $cms system-type
     */
    public static function isCMSActive($cms)
    {
        return self::getConfigValue('ACTIVE', $cms);
    }

    /**
     * get cms-selectbox
     *
     * returns a form to select a cms
     *
     * @param string $message description-text
     * @param boolean $check_active show only activated systems
     * @return string returns html-code
     */
    public static function getCMSSelectbox($message, $check_active = true)
    {
        global $ELEARNING_INTERFACE_MODULES, $cms_select, $search_key, $view;
        if (!is_array($ELEARNING_INTERFACE_MODULES)) {
            $msg = sprintf(_("Die ELearning-Schnittstelle ist nicht korrekt konfiguriert. Die Variable \"%s\" "
                            ."muss in der Konfigurationsdatei von Stud.IP erst mit den Verbindungsdaten angebundener "
                            ."Learning-Content-Management-Systeme aufgefüllt werden. Solange dies nicht geschehen "
                            ."ist, setzen Sie die Variable \"%s\" auf FALSE!"), "\$ELEARNING_INTERFACE_MODULES", "\$ELEARNING_INTERFACE_ENABLE");
            PageLayout::postError($msg);
            return false;
        }
        $options = [];
        foreach ($ELEARNING_INTERFACE_MODULES as $cms => $cms_preferences) {
            if (!$check_active || self::isCMSActive($cms)) {
                $options[$cms] = $cms_preferences['name'];
            }
        }
        $template = $GLOBALS['template_factory']->open('elearning/_cms_selectbox.php');
        $template->cms_select = $cms_select;
        $template->options    = $options;
        $template->search_key = $search_key;
        $template->view       = $view;
        $template->message    = $message;
        return $template->render();
    }

     /**
     * get moduletype-selectbox
     *
     * returns a form to select type for new contentmodule
     *
     * @param string $cms system-type
     * @return string returns html-code
     */
    public static function getTypeSelectbox($cms)
    {
        global $ELEARNING_INTERFACE_MODULES;
        $options = array();
        foreach ($ELEARNING_INTERFACE_MODULES[$cms]['types'] as $type => $info) {
            $options[$type] = $info['name'];
            if (Request::get("module_type_{$cms}") === $type) {
                $selected = $type;
            }
        }
        $template = $GLOBALS['template_factory']->open('elearning/_type_selectbox.php');
        $template->options  = $options;
        $template->selected = $selected;
        $template->cms      = $cms;
        return $template->render();
    }

    /**
    * get searchfield
    *
    * returns a form to search for modules
    *
    * @param string $message description-text
    * @return string returns html-code
    */
    public static function getSearchfield($message)
    {
        global $cms_select, $search_key, $view;
        $template = $GLOBALS['template_factory']->open('elearning/_searchfield.php');
        $template->cms_select = $cms_select;
        $template->search_key = $search_key;
        $template->view       = $view;
        $template->message    = $message;
        return $template->render();
    }

    /**
    * get form for new content-module
    *
    * returns a form to choose module-type and to create a new content-module
    *
    * @param string $cms system-type
    * @return string returns html-code
    */
    public static function getNewModuleForm($cms)
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        if (sizeof($ELEARNING_INTERFACE_MODULES[$cms]["types"]) == 1)
            foreach($ELEARNING_INTERFACE_MODULES[$cms]["types"] as $type => $info)
                Request::set("module_type_" . $cms, $type);
        $link = $connected_cms[$cms]->link->getNewModuleLink();

        if ($link == false)
            return false;
        $types = array();
        $cms_types = array();
        foreach ($ELEARNING_INTERFACE_MODULES as $cms_type => $cms_data)
            $cms_types["module_type_" . $cms_type] = Request::option("module_type_" . $cms_type);
        $template = $GLOBALS['template_factory']->open('elearning/_new_module_form.php');
        $template->set_attribute('link', $link);
        $template->set_attribute('cms', $cms);
        $template->set_attribute('cms_types', $cms_types);
        $template->set_attribute('types', $ELEARNING_INTERFACE_MODULES[$cms]["types"]);
        return $template->render();
    }

    /**
    * get form for external user-account
    *
    * returns a form for administration of external user-account
    *
    * @param string message message-string
    * @param string my_account_cms cms-type
    * @return string returns html-code
    */
    public static function getMyAccountForm($message, $my_account_cms)
    {
        global $connected_cms;
        $template = $GLOBALS['template_factory']->open('elearning/_my_account_form.php');
        if ($connected_cms[$my_account_cms]->user->isConnected()) {
            $template->set_attribute('login', $connected_cms[$my_account_cms]->user->getUsername());
            $template->set_attribute('is_connected', 1);
        }
        $template->set_attribute('my_account_cms', $my_account_cms);
        $template->set_attribute('search_key', $search_key);
        $template->set_attribute('view', $view);
        $template->set_attribute('message', $message);
        return $template->render();
    }

    /**
    * get form for new user
    *
    * returns a form to add a user-account to connected cms
    *
    * @param string $new_account_cms system-type
    * @return string returns html-code
    */
    public static function getNewAccountForm(&$new_account_cms)
    {
        global $connected_cms, $cms_select, $view, $current_module, $messages,
             $ELEARNING_INTERFACE_MODULES;

        $new_account_step = Request::int('new_account_step');
        $ext_password = Request::get('ext_password');
        $ext_password_2 = Request::get('ext_password_2');
        $ext_username = Request::get('ext_username');
        $ref_id = Request::get('ref_id');
        $module_type = Request::get('module_type');

        self::loadClass($new_account_cms);

        //Password was sent, but is to short
        if (isset($ext_password_2) && !Request::submitted('go_back') && Request::submitted('next') && mb_strlen($ext_password_2) < 6) {
            PageLayout::postError(_('Das Passwort muss mindestens 6 Zeichen lang sein!'));
            $new_account_step--;
        } elseif (isset($ext_password_2) && ! Request::submitted('go_back') && Request::submitted('next') && $ext_password != $ext_password_2) {
            //Passwords doesn't match password repeat
            PageLayout::postError(_('Das Passwort entspricht nicht der Passwort-Wiederholung!'));
            $new_account_step--;
        }

        // Benutzername was sent
        if ($ext_username && !Request::submitted('go_back') && Request::submitted('assign')) {
            $caching_status = $connected_cms[$new_account_cms]->soap_client->getCachingStatus();
            $connected_cms[$new_account_cms]->soap_client->setCachingStatus(false);
            if ($connected_cms[$new_account_cms]->user->verifyLogin($ext_username, $ext_password)) {
                $is_verified = true;
                PageLayout::postInfo(_('Der Account wurde zugeordnet.'));
                $connected_cms[$new_account_cms]->user->setCategory("");
                $connected_cms[$new_account_cms]->user->setUsername($ext_username);
                $connected_cms[$new_account_cms]->user->setPassword($ext_password);
                $connected_cms[$new_account_cms]->user->setConnection(USER_TYPE_ORIGINAL);
                if ($ref_id) {
                    $connected_cms[$new_account_cms]->newContentModule($ref_id, $module_type, true);
                    $module_title = $connected_cms[$new_account_cms]->content_module[$current_module]->getTitle();
                    $module_links = $connected_cms[$new_account_cms]->link->getUserModuleLinks();
                }
            } else {
                $new_account_step = 1;
                PageLayout::postError(_('Die eingegebenen Login-Daten sind nicht korrekt.'));
            }
            $connected_cms[$new_account_cms]->soap_client->setCachingStatus($caching_status);
        }

        if (Request::submitted('start')) {
            $new_account_step = 1;
        }
        if (Request::submitted('go_back')) {
            $new_account_step--;
            if ($new_account_step < 1) {
                $new_account_cms = '';
                return false;
            }
        } elseif (Request::submitted('next') || Request::submitted('assign')) {
            $new_account_step++;
        }

        if ($new_account_step == 2 && Request::submitted('assign')) {
            // Assign existing Account
            $step = 'assign';
        } elseif ($new_account_step == 2 && Request::submitted('next')) {
            // Create new Account: ask for new password
            $step = 'new_account';
        } elseif ($new_account_step == 3 && Request::submitted('next')) {
            // Create new Account
            $connected_cms[$new_account_cms]->user->setPassword($ext_password);
            if ($connected_cms[$new_account_cms]->user->newUser()) {
                $is_verified = true;
                PageLayout::postInfo(sprintf(_("Der Account wurde erzeugt und zugeordnet. Ihr Loginname ist %s."), "<b>" . htmlReady($connected_cms[$new_account_cms]->user->getUsername()) . "</b>"));
                if ($ref_id) {
                    $connected_cms[$new_account_cms]->newContentModule($ref_id, $module_type, true);
                    $module_title = $connected_cms[$new_account_cms]->content_module[$current_module]->getTitle();
                    $module_links = $connected_cms[$new_account_cms]->link->getUserModuleLinks();
                }
            }
        } elseif (!$is_verified) {
            $output .= '<font size="-1">';
            if (Request::submitted('start')) {
                $messages["info"] = sprintf(_("Sie versuchen zum erstem Mal ein Lernmodul des angebundenen Systems %s zu starten. Bevor Sie das Modul nutzen können, muss Ihrem Stud.IP-Benutzeraccount ein Account im angebundenen System zugeordnet werden."), htmlReady($connected_cms[$new_account_cms]->getName())) . "<br><br>\n\n";
            }
        }
        $template = $GLOBALS['template_factory']->open('elearning/_new_account_form.php');
        $template->cms_title        = htmlReady($connected_cms[$new_account_cms]->getName());
        $template->cms_select       = $cms_select;
        $template->module_title     = $module_title;
        $template->module_links     = $module_links;
        $template->module_type      = $module_type;
        $template->ref_id           = $ref_id;
        $template->ext_username     = $ext_username;
        $template->new_account_cms  = $new_account_cms;
        $template->new_account_step = $new_account_step;
        $template->is_connected     = $connected_cms[$new_account_cms]->user->isConnected();
        $template->is_verified      = $is_verified;
        $template->step             = $step;

        // TODO: Should this really be below the assignment?
        if ($is_verified) {
            $new_account_cms = "";
        }

        return $template->render();
    }

    /**
     * get table-header for connected cms
     *
     * returns a table-header for connected cms
     *
     * @param string $title table-title
     * @return string returns html-code
     */
    public static function getCMSHeader($title)
    {
        $template = $GLOBALS['template_factory']->open('elearning/_cms_header.php');
        $template->title = $title;
        return $template->render();
    }

    /**
     * get table-footer for connected cms
     *
     * returns a table-footer for connected cms
     *
     * @param string $logo system-logo
     * @return string returns html-code
     */
    public static function getCMSFooter($logo)
    {
        $template = $GLOBALS['template_factory']->open('elearning/_cms_footer.php');
        $template->logo = $logo;
        return $template->render();
    }

    /**
     * get headline for modules
     *
     * returns a table with a headline
     *
     * @param string $title headline
     * @return string returns html-code
     */
    public static function getModuleHeader($title)
    {
        global $view, $cms_select, $search_key;
        $template = $GLOBALS['template_factory']->open('elearning/_module_header.php');
        $template->title      = $title;
        $template->view       = $view;
        $template->cms_select = $cms_select;
        $template->search_key = $search_key;
        $template->all_open   = $_SESSION['elearning_open_close']['all open'];
        return $template->render();
    }

    /**
    * get Headline
    *
    * returns a table with a headline
    *
    * @param string $title headline
    * @return string returns html-code
    */
    public static function getHeader($title)
    {
        $template = $GLOBALS['template_factory']->open('elearning/_header.php');
        $template->title = $title;
        return $template->render();
    }

    /**
     * save timestamp
     *
     * saves a timestamp for debugging and performance-check
     *
     * @param string $stri description
     */
    public static function bench($stri)
    {
        $GLOBALS['timearray'][] = [
            'zeit' => microtime(true),
            'name' => $stri,
        ];
    }

    /**
     * show benchmark
     *
     * shows saved timestamps with descriptions
     *
     * @param string $stri description
     */
    public static function showbench()
    {
        global $timearray;
        echo "<table><tr><td>Zeit (".$timearray[0]["name"].")</td><td align=\"right\"></td></tr>";
        for ($i = 0; $i < count($timearray); $i++) {
            echo "<tr><td>".$timearray[$i]["name"].": </td><td align=\"right\">" . number_format(($timearray[$i]["zeit"]-$timearray[$i-1]["zeit"])*1000,2) . " msek</td></tr>";
        }
        echo "<tr><td>Gesamtzeit: </td><td align=\"right\">" . number_format(($timearray[$i-1]["zeit"]-$timearray[0]["zeit"])*1000,2)." msek</td></tr></table>";
    }

    /**
     * delete cms-data
     *
     * deletes all data belonging to the specified cms from stud.ip database
     *
     * @return boolean successful
     */
    public static function deleteCMSData($cms_type)
    {
        $db = DBManager::get();
        $db->execute("DELETE FROM auth_extern WHERE external_user_system_type = ?", [$cms_type]);
        $db->execute("DELETE FROM object_contentmodules WHERE system_type = ?", [$cms_type]);

        $config = Config::get();
        foreach ($config->getFields('global' ,null , "ELEARNING_INTERFACE_{$cms_type}") as $key) {
            $config->delete($key);
        }
    }

    /**
     * get ilias courses
     *
     * creates output of ilias courses linked to the chosen seminar. also updates object-connections.
     *
     * @return boolean successful
     */
    public static function getIliasCourses($sem_id)
    {
        global  $connected_cms, $messages, $view, $cms_select;
        $db = DBManager::get();

        $rs = $db->query("SELECT DISTINCT system_type, module_id
                          FROM object_contentmodules
                          WHERE module_type = 'crs' AND object_id = " . $db->quote($sem_id))
                        ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rs as $row) $courses[$row['system_type']] = $row['module_id'];
        if (is_array($courses))
            foreach($courses as $system_type => $crs_id)
                if (self::isCMSActive($system_type)) {
                    self::loadClass($system_type);
                    $connected_courses['courses'][$system_type] = array(
                        'url' => UrlHelper::getLink($connected_cms[$system_type]->link->cms_link . '?client_id=' . $connected_cms[$system_type]->getClientId() . '&cms_select=' . $system_type . '&ref_id=' . $crs_id . '&type=crs&target=start'),
                        'cms_name' => $connected_cms[$system_type]->getName());
                    $course_output[] = "<a href=\"" . UrlHelper::getLink($connected_cms[$system_type]->link->cms_link . "?" . "client_id=" . $connected_cms[$system_type]->getClientId() . "&cms_select=" . $system_type . "&ref_id=" . $crs_id . "&type=crs&target=start") . "\" target=\"_blank\" rel=\"noopener noreferrer\">".sprintf(_("Kurs in %s"), htmlReady($connected_cms[$system_type]->getName()))."</a>";
                    // gegebenenfalls zugeordnete Module aktualisieren
                    if (Request::option('update')) {
                        if ((method_exists($connected_cms[$system_type], "updateConnections"))) {
                            $connected_cms[$system_type]->updateConnections( $crs_id );
                        }
                    }
                    if (method_exists($connected_cms[$system_type]->permissions, 'CheckUserPermissions')) {
                        $connected_cms[$system_type]->permissions->CheckUserPermissions($crs_id);
                    }
                }

        if ($connected_courses['courses']) {
            if (count($connected_courses['courses']) > 1)
                $connected_courses['text'] = _("Diese Veranstaltung ist mit folgenden Ilias-Kursen verknüpft. Hier gelangen Sie direkt in den jeweiligen Kurs: ");
            else
                $connected_courses['text'] = _("Diese Veranstaltung ist mit einem Ilias-Kurs verknüpft. Hier gelangen Sie direkt in den Kurs: ");
            $output["update"] .= "<font style=\"font-size: -1\">" . _("Hier können Sie die Zuordnungen zu den verknüpften Kursen aktualisieren."). "<br></font>";
            $output["update"] .= "<form method=\"POST\" action=\"" . URLHelper::getLink() . "#anker\">\n";
            $output["update"] .= CSRFProtection::tokenTag();
            $output["update"] .= "<input type=\"HIDDEN\" name=\"view\" value=\"" . htmlReady($view) . "\">\n";
            $output["update"] .= "<input type=\"HIDDEN\" name=\"cms_select\" value=\"" . htmlReady($cms_select) . "\">\n";
            $output["update"] .= Button::create(_('Aktualisieren'), 'update');
            $output["update"] .= "</form>";
        }

        return $connected_courses;
    }

    /**
    * check db-integrity
    *
    * checks if there are broken links in the database
    *
    * @return boolean successful
    */
    public static function checkIntegrity()
    {
        global $ELEARNING_INTERFACE_MODULES, $messages;
        $db = DBManager::get();

        foreach ($ELEARNING_INTERFACE_MODULES as $cms_type =>$data) $cmsystems[$cms_type] = array();

        $config = Config::get();
        foreach ($config->getFields('global' ,null , 'ELEARNING_INTERFACE_') as $key) {
            $parts = explode("_", $key);
            $cmsystems[$parts[2]]["config"]++;
        }

        $rs = $db->query("SELECT external_user_system_type, COUNT(*) as c FROM auth_extern GROUP BY external_user_system_type");
        while ($row = $rs->fetch())
            $cmsystems[$row["external_user_system_type"]]["accounts"] = $row['c'];
        $rs = $db->query("SELECT system_type, COUNT(*) FROM object_contentmodules GROUP BY system_type");
        while ($row = $rs->fetch())
            $cmsystems[$row["system_type"]]["modules"] = $row['c'];

        if (Request::submitted('delete')) {
            $messages["info"] .= "<form method=\"POST\" action=\"" . URLHelper::getLink() . "\">";
            $messages["info"] .= CSRFProtection::tokenTag();
            $messages["info"] .= "<table>";
            $messages["info"] .= "<tr><td>&nbsp;</td></tr>";
            $messages["info"] .= "<tr><td>" . sprintf(_("Durch das Löschen der Daten zum System mit dem Index \"%s\" werden %s Konfigurationseinträge und Verknüpfungen von Stud.IP-Veranstaltungen und -User-Accounts unwiederbringlich aus der Stud.IP-Datenbank entfernt. Wollen Sie diese Daten jetzt löschen?"), Request::quoted('delete_cms'), $cmsystems[Request::quoted('delete_cms')]["accounts"]+$cmsystems[Request::quoted('delete_cms')]["modules"]+$cmsystems[Request::quoted('delete_cms')]["config"] ) . "</td></tr>";
            $messages["info"] .= "<tr><td align=\"center\"><input type=\"hidden\" name=\"delete_cms\" value=\"".Request::quoted('delete_cms')."\">";
            $messages["info"] .= '<div class="button-group">' . Button::create(_('Alle löschen'), 'confirm_delete') . Button::createCancel(_('Abbrechen'), 'abbruch') . '<div></td></tr>';
            $messages["info"] .= "<tr><td align=\"center\"></td></tr>";
            $messages["info"] .= "</table>";
            $messages["info"] .= "</form>";
        }

        if (Request::submitted('confirm_delete')) {
            unset($cmsystems[Request::quoted('delete_cms')]);
//          deleteCMSData(Request::quoted('delete_cms'));
            $messages["info"] .= _("Daten wurden gelöscht.");
        }

        foreach ($cmsystems as $cms_type =>$data) {
            if ($ELEARNING_INTERFACE_MODULES[$cms_type]) {
                $output .= self::getCMSHeader($ELEARNING_INTERFACE_MODULES[$cms_type]["name"]);
                $output .= "<table>";
                $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                if (self::isCMSActive($cms_type)) {
                    $output .= "<tr><td>" .  Icon::create('checkbox-checked', 'clickable')->asImg(['class' => 'text-top']) . "</td><td><b>". sprintf(_("Die Schnittstelle zum System %s ist aktiv."), $ELEARNING_INTERFACE_MODULES[$cms_type]["name"]) . "</b></td></tr>";
                    $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                }
                elseif ($data["config"] < 1)
                    $output .= "<tr><td>" .  Icon::create('checkbox-unchecked', 'clickable')->asImg(['class' => 'text-top']) . "</td><td><i>". sprintf(_("Die Schnittstelle für das System %s wurde noch nicht eingerichtet."), $ELEARNING_INTERFACE_MODULES[$cms_type]["name"]) . "</i></td></tr>";
                elseif ($data["config"] < 1)
                    $output .= "<tr><td>" .  Icon::create('checkbox-unchecked', 'clickable')->asImg(['class' => 'text-top']) . "</td><td><i>". sprintf(_("Die Schnittstelle wurde noch nicht aktiviert."), $ELEARNING_INTERFACE_MODULES[$cms_type]["name"]) . "</i></td></tr>";

                if ($data["accounts"])
                    $output .= "<tr><td colspan=\"2\">". sprintf(_("%s Stud.IP-User-Accounts sind mit Accounts im System %s verknüpft."), $data["accounts"], $ELEARNING_INTERFACE_MODULES[$cms_type]["name"]) . "</td></tr>";
                if ($data["modules"])
                    $output .= "<tr><td colspan=\"2\">". sprintf(_("%s Objekte sind Stud.IP-Veranstaltungen oder -Einrichtungen zugeordnet."), $data["modules"]) . "</td></tr>";
                if ($data["config"])
                    $output .= "<tr><td colspan=\"2\">". sprintf(_("%s Einträge in der config-Tabelle der Stud.IP-Datenbank."), $data["config"]) . "</td></tr>";
                $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                $output .= "</table>";
                $output .= self::getCMSFooter(($ELEARNING_INTERFACE_MODULES[$cms_type]["logo_file"] ? "<img src=\"".$ELEARNING_INTERFACE_MODULES[$cms_type]["logo_file"]."\" border=\"0\">" : $cms_type));
            }
            else {
                $output .= self::getCMSHeader("<font color=FF0000> Unbekanntes System: " . $cms_type . "</font>");
                $output .= "<form method=\"POST\" action=\"" . URLHelper::getLink() . "\">";
                $output .= CSRFProtection::tokenTag();
                $output .= "<table>";
                $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                $output .= "<tr><td>" . Icon::create('decline', 'attention')->asImg(['class' => 'text-top']) . "</td><td><i>".sprintf(_("Für das System mit dem Index \"%s\" existieren keine Voreinstellungen in den Konfigurationsdateien mehr."), $cms_type) . "</i></td></tr>";
                $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                $output .= "<tr><td colspan=\"2\"><b>". _("In der Stud.IP-Datenbank sind noch folgende Informationen zu diesem System gespeichert:") . "</b></td></tr>";
                if ($data["accounts"])
                    $output .= "<tr><td colspan=\"2\">". sprintf(_("%s Stud.IP-User-Accounts sind mit externen Accounts mit dem Index \"%s\" verknüpft."), $data["accounts"], $cms_type) . "</td></tr>";
                if ($data["modules"])
                    $output .= "<tr><td colspan=\"2\">". sprintf(_("%s Objekte sind Stud.IP-Veranstaltungen oder -Einrichtungen zugeordnet."), $data["modules"]) . "</td></tr>";
                if ($data["config"])
                    $output .= "<tr><td colspan=\"2\">". sprintf(_("%s Einträge in der config-Tabelle der Stud.IP-Datenbank."), $data["config"]) . "</td></tr>";
                $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                $output .= "<tr><td align=\"center\" colspan=\"2\"><input type=\"hidden\" name=\"delete_cms\" value=\"".$cms_type."\">" . Button::create(_('Löschen'), 'delete') . "</td></tr>";
                $output .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                $output .= "</table>";
                $output .= "</form>";
                $output .= self::getCMSFooter('');
            }
            $output .= "<br>";
        }

        return $output;
    }
}
