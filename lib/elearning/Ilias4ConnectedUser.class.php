<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * class to handle ILIAS 4 user-accounts
 *
 * This class contains methods to handle connected ILIAS 4 user-accounts.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias4ConnectedUser
 * @package    ELearning-Interface
 */
class Ilias4ConnectedUser extends Ilias3ConnectedUser
{
    var $roles;
    var $user_sid;
    /**
     * constructor
     *
     * init class.
     * @access
     * @param string $cms system-type
     */
    function __construct($cms, $user_id = false)
    {
        // get auth_plugin
        $user_id = $user_id ? $user_id : $GLOBALS['user']->id;
        $this->auth_plugin = DBManager::get()->query("SELECT IFNULL(auth_plugin, 'standard') FROM auth_user_md5 WHERE user_id = '" . $user_id . "'")->fetchColumn();
        parent::__construct($cms, $user_id);
    }

    /**
     * new user
     *
     * save new user
     * @access public
     * @return boolean returns false on error
     */
    function newUser($ignore_encrypt_passwords = false)
    {
        global $connected_cms, $auth, $messages;

        if ($this->getLoginData($this->login)) {
            //automatische Zuordnung von bestehenden Ilias Accounts
            //nur wenn ldap Modus benutzt wird und Stud.IP Nutzer passendes ldap plugin hat
            if ($connected_cms[$this->cms_type]->USER_AUTO_CREATE == true &&
            $connected_cms[$this->cms_type]->USER_PREFIX == '' &&
            $this->auth_plugin &&
            $this->auth_plugin != "standard" &&
            $this->auth_plugin  == $connected_cms[$this->cms_type]->ldap_enable) {
                if (!$this->external_password) {
                    $this->setPassword(md5(uniqid("4dfmjsnll")));
                }
                $ok = $connected_cms[$this->cms_type]->soap_client->updatePassword($this->id, $this->external_password);
                $this->setConnection($this->getUserType(), true);
                if ($ok) {
                    $messages["info"] .= sprintf(_("Verbindung mit Nutzer ID %s wiederhergestellt."), $this->id);
                }
                return true;
            }
            $messages["error"] .= sprintf(_("Es existiert bereits ein Account mit dem Benutzernamen \"%s\" (Account ID %s)."), $this->login, $this->id) . "<br>\n";
            return false;
        }

        // data for user-account in ILIAS 4
        $user_data["login"] = $this->login;
        $user_data["passwd"] = $this->external_password;
        $user_data["firstname"] = $this->firstname;
        $user_data["lastname"] = $this->lastname;
        $user_data["title"] = $this->title;
        $user_data["gender"] = $this->gender;
        $user_data["email"] = $this->email;
        $user_data["street"] = $this->street;
        $user_data["phone_home"] = $this->phone_home;
        $user_data["time_limit_unlimited"] = 1;
        $user_data["active"] = 1;
        $user_data["approve_date"] = date('Y-m-d H:i:s');
        $user_data["accepted_agreement"] = true;
        // new values for ILIAS 4
        $user_data["agree_date"] = date('Y-m-d H:i:s');
        $user_data["external_account"] = $this->login;
        if ($this->auth_plugin && $this->auth_plugin != "standard" &&  ($this->auth_plugin  == $connected_cms[$this->cms_type]->ldap_enable)) {
            $user_data["auth_mode"] = "ldap";
        } else {
            $user_data["auth_mode"] = "default";
        }
        if ($connected_cms[$this->cms_type]->user_style != "") {
            $user_data["user_style"] = $connected_cms[$this->cms_type]->user_style;
        }
        if ($connected_cms[$this->cms_type]->user_skin != "") {
            $user_data["user_skin"] = $connected_cms[$this->cms_type]->user_skin;
        }

        $role_id = $connected_cms[$this->cms_type]->roles[$auth->auth["perm"]];

        $user_id = $connected_cms[$this->cms_type]->soap_client->addUser($user_data, $role_id);

        if ($user_id != false) {
            $this->id = $user_id;

            //            $connected_cms[$this->cms_type]->soap_client->updatePassword($user_id, $user_data["passwd"]);

            //            $this->newUserCategory();

            $this->setConnection(USER_TYPE_CREATED);
            return true;
        }
        return false;
    }

    /**
     * create new user category
     *
     * create new user category
     * @access public
     * @return boolean returns false on error
     */
    function newUserCategory()
    {
        global $connected_cms, $messages;

        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);

        // data for user-category in ILIAS 4
        $object_data["title"] = sprintf(_("Eigene Daten von %s (%s)."), $this->getName(), $this->getId());
        $object_data["description"] = sprintf(_("Hier befinden sich die persönlichen Lernmodule des Benutzers %s."), $this->getName());
        $object_data["type"] = "cat";
        $object_data["owner"] = $this->getId();

        $cat = $connected_cms[$this->cms_type]->soap_client->getReferenceByTitle($object_data["title"]);
        if ($cat != false && $connected_cms[$this->cms_type]->soap_client->checkReferenceById($cat) ) {
            $messages["info"] .= sprintf(_("Ihre persönliche Kategorie wurde bereits angelegt."), $this->login) . "<br>\n";
            $this->category = $cat;
        } else {
            $this->category = $connected_cms[$this->cms_type]->soap_client->addObject($object_data, $connected_cms[$this->cms_type]->user_category_node_id);
        }
        if ($this->category != false) {
            parent::setConnection($this->getUserType(), true);
        } else {
            echo "CATEGORY_ERROR".$connected_cms[$this->cms_type]->user_category_node_id ."-";
            return false;
        }
        // data for personal user-role in ILIAS 4
        $role_data["title"] = "studip_usr" . $this->getId() . "_cat" . $this->category;
        $role_data["description"] = sprintf(_("User-Rolle von %s. Diese Rolle wurde von Stud.IP generiert."), $this->getName());
        $role_id = $connected_cms[$this->cms_type]->soap_client->getObjectByTitle($role_data["title"], "role");
        if ($role_id != false) {
            $messages["info"] .= sprintf(_("Ihre persönliche Userrolle wurde bereits angelegt."), $this->login) . "<br>\n";
        } else {
            $role_id = $connected_cms[$this->cms_type]->soap_client->addRoleFromTemplate($role_data, $this->category, $connected_cms[$this->cms_type]->user_role_template_id);
        }
        $connected_cms[$this->cms_type]->soap_client->addUserRoleEntry($this->getId(), $role_id);
        // delete permissions for all global roles for this category
        foreach ($connected_cms[$this->cms_type]->global_roles as $key => $role) {
            $connected_cms[$this->cms_type]->soap_client->revokePermissions($role, $this->category);
        }
        return true;
    }
}