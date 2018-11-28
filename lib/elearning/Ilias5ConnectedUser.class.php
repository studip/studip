<?php
/**
 * class to handle ILIAS 5.2 user-accounts
 *
 * This class contains methods to handle connected ILIAS 5 user-accounts.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias5ConnectedUser
 * @package    ELearning-Interface
 */
class Ilias5ConnectedUser extends Ilias4ConnectedUser
{

    /**
    * verify login data
    *
    * returns true, if login-data is valid
    * @access public
    * @param string $username username
    * @param string $password password
    * @return boolean login-validation
    */
    function verifyLogin($username, $password)
    {
        global $connected_cms, $messages;
        $result = $connected_cms[$this->cms_type]->soap_client->checkPassword($username, $password);
        if (strpos($result, '::') > 0) {
            return $this->getLoginData($username);
        }
        return false;
    }

    function setConnection($user_type, $ignore_encrypted_passwords = false)
    {
        $this->external_password = '';
        parent::setConnection($user_type, true);
    }
}