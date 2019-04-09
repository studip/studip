<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* class to handle ILIAS 3 user-accounts
*
* This class contains methods to handle connected ILIAS 3 user-accounts.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       Ilias3ConnectedUser
* @package  ELearning-Interface
*/
class Ilias3ConnectedUser extends ConnectedUser
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
        global $connected_cms, $perm;

        parent::__construct($cms, $user_id);
        // create account automatically if it doesn't exist
        if (! $this->isConnected() AND ($connected_cms[$this->cms_type]->USER_AUTO_CREATE == true))
        {
            $this->setPassword(md5(uniqid("4dfmjsnll")));
            $this->newUser(true);
            $this->readData();
        }
        $this->roles = [$connected_cms[$cms]->roles[$perm->get_perm($this->studip_id)]];
    }

    function readData()
    {
        global $connected_cms;
        parent::readData();
        if($this->is_connected){
            $user_id = $connected_cms[$this->cms_type]->soap_client->lookupUser($this->login);
            if (!$user_id) {
                //do not delete in case of error
                if($user_id !== false) {
                    $query = "DELETE FROM auth_extern WHERE studip_user_id = ? LIMIT 1";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$this->studip_id]);
                }
                $this->id = '';
                $this->login = '';
                $this->external_password = '';
                $this->category = '';
                $this->type = '';
                $this->is_connected = false;
            } elseif($this->category != ''){
                $cat = $connected_cms[$this->cms_type]->soap_client->checkReferenceById($this->category);
                if(!$cat){
                    $query = "UPDATE auth_extern SET external_user_category = '' WHERE studip_user_id = ? LIMIT 1";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$this->studip_id]);

                    $this->category = '';
                }
            }
        }
        return $this->is_connected;
    }

    /**
    * get login-data
    *
    * gets login-data from database
    * @access public
    * @param string $username username
    * @return boolean returns false, if no data was found
    */
    function getLoginData($username)
    {
        global $connected_cms;

        if (!$username) {
            return false;
        }
        $user_id = $connected_cms[$this->cms_type]->soap_client->lookupUser($username);

        if ($user_id == false)
            return false;

        $user_data = $connected_cms[$this->cms_type]->soap_client->getUser($user_id);

        if ($user_data == false)
            return false;

        $this->id = $user_data["usr_id"];
        $this->login = $user_data["login"];
        $this->external_password = $user_data["passwd"];
        return true;
    }

    /**
    * get crypted password
    *
    * returns ILIAS 3 password
    * @access public
    * @param string $password password
    * @return string password
    */
    function getCryptedPassword($password)
    {
        return md5($password);
    }

    /**
    * set roles
    *
    * sets roles
    * @access public
    * @param array $role_array role-array
    */
    function setRoles($role_array)
    {
        $this->roles = $role_array;
    }

    /**
    * get roles
    *
    * returns roles
    * @access public
    * @return array roles
    */
    function getRoles()
    {
        return $this->roles;
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

        // data for user-category in ILIAS 3
        $object_data["title"] = sprintf(_("Eigene Daten von %s (%s)."), $this->getName(), $this->getId());
        $object_data["description"] = sprintf(_("Hier befinden sich die persönlichen Lernmodule des Benutzers %s."), $this->getName());
        $object_data["type"] = "cat";
        $object_data["owner"] = $this->getId();

        $cat = $connected_cms[$this->cms_type]->soap_client->getReferenceByTitle($object_data["title"]);
        if ($cat != false && $connected_cms[$this->cms_type]->soap_client->checkReferenceById($cat) )
        {
            $messages["info"] .= sprintf(_("Ihre persönliche Kategorie wurde bereits angelegt."), $this->login) . "<br>\n";
            $this->category = $cat;
        }
        else
        {
            $this->category = $connected_cms[$this->cms_type]->soap_client->addObject($object_data, $connected_cms[$this->cms_type]->main_category_node_id);
        }
        if ($this->category != false)
            parent::setConnection( $this->getUserType() );
        else
        {
            echo "CATEGORY_ERROR".$connected_cms[$this->cms_type]->main_category_node_id ."-";
            return false;
        }
        // data for personal user-role in ILIAS 3
        $role_data["title"] = "studip_usr" . $this->getId() . "_cat" . $this->category;
        $role_data["description"] = sprintf(_("User-Rolle von %s. Diese Rolle wurde von Stud.IP generiert."), $this->getName());
        $role_id = $connected_cms[$this->cms_type]->soap_client->getObjectByTitle($role_data["title"], "role");
        if ($role_id != false)
            $messages["info"] .= sprintf(_("Ihre persönliche Userrolle wurde bereits angelegt."), $this->login) . "<br>\n";
        else
            $role_id = $connected_cms[$this->cms_type]->soap_client->addRoleFromTemplate($role_data, $this->category, $connected_cms[$this->cms_type]->user_role_template_id);
        $connected_cms[$this->cms_type]->soap_client->addUserRoleEntry($this->getId(), $role_id);
        // delete permissions for all global roles for this category
        foreach ($connected_cms[$this->cms_type]->global_roles as $key => $role)
            $connected_cms[$this->cms_type]->soap_client->revokePermissions($role, $this->category);
        return true;
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

        if ($this->getLoginData($this->login))
        {
            $messages["error"] .= sprintf(_("Es existiert bereits ein Account mit dem Benutzernamen \"%s\"."), $this->login) . "<br>\n";
            return false;
        }

        // data for user-account in ILIAS 3
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

        if ($connected_cms[$this->cms_type]->user_style != "")
            $user_data["user_style"] = $connected_cms[$this->cms_type]->user_style;
        if ($connected_cms[$this->cms_type]->user_skin != "")
            $user_data["user_skin"] = $connected_cms[$this->cms_type]->user_skin;

        $role_id = $connected_cms[$this->cms_type]->roles[$auth->auth["perm"]];

        $user_id = $connected_cms[$this->cms_type]->soap_client->addUser($user_data, $role_id);

        if ($user_id != false)
        {
            $this->id = $user_id;

//          $connected_cms[$this->cms_type]->soap_client->updatePassword($user_id, $user_data["passwd"]);

//          $this->newUserCategory();

            $this->setConnection(USER_TYPE_CREATED, $ignore_encrypt_passwords);
            return true;
        }
        echo $connected_cms[$this->cms_type]->soap_client->getError();
        return false;
    }

    /**
    * update user
    *
    * update user-account
    * @access public
    * @return boolean returns false on error
    */
    function updateUser()
    {
    }

    /**
    * delete user
    *
    * delete user-account
    * @access public
    * @return boolean returns false on error
    */
    function deleteUser()
    {
        global $connected_cms;
        $ret = null;
        $connected_cms[$this->cms_type]->soap_client->user_type == "admin";
        $connected_cms[$this->cms_type]->soap_client->caching_active = false;
        if($this->category){
            $ret['cat_deleted'] = $connected_cms[$this->cms_type]->soap_client->deleteObject($this->category);
        }
        if($this->type == 0 && $this->id){
            $ret['iliasuser_deleted'] = $connected_cms[$this->cms_type]->soap_client->deleteUser($this->id);
        }
        $query = "DELETE FROM auth_extern WHERE studip_user_id = ? LIMIT 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->studip_id]);

        $ret['auth_extern_deleted'] = $statement->rowCount();
        return $ret;
    }

    /**
    * set connection
    *
    * set user connection
    * @access public
    * @param string user_type user-type
    * @return boolean returns false on error
    */
    function setConnection($user_type, $ignore_encrypt_passwords = false)
    {
        global $connected_cms;

        if (!$ignore_encrypt_passwords && $connected_cms[$this->cms_type]->encrypt_passwords == "md5")
        {
//          echo "PASSWORD-ENCRYPTION";
            $this->external_password = $this->getCryptedPassword( $this->external_password );
        }

        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);
        parent::setConnection($user_type);
    }

    /**
    * get sid
    *
    * returns soap-sid
    * @access public
    * @return string soap-sid
    */
    function getSID()
    {
        global $connected_cms;

        $caching_status = $connected_cms[$this->cms_type]->soap_client->getCachingStatus();
        $connected_cms[$this->cms_type]->soap_client->setCachingStatus(false);

        $connected_cms[$this->cms_type]->soap_client->setUserType("user");
        $this->user_sid = $connected_cms[$this->cms_type]->soap_client->login();

        $connected_cms[$this->cms_type]->soap_client->setCachingStatus($caching_status);
        $connected_cms[$this->cms_type]->soap_client->setUserType("admin");
        return $this->user_sid;
    }

    /**
    * get session-id
    *
    * returns soap-session-id
    * @access public
    * @return string soap-session-id
    */
    function getSessionId()
    {
        $sid = $this->getSID();
        if ($sid == false)
            return false;
        $arr = explode("::", $sid);
        return $arr[0];
    }
}
?>
