<?php
/**
 * class to use ILIAS-5-Webservices
 *
 * This class contains methods to connect to the ILIAS-5-Soap-Server.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias5Soap
 * @package    ELearning-Interface
 */
class Ilias5Soap extends Ilias4Soap
{
    var $cms_type;
    var $admin_sid;
    var $user_sid;
    var $user_type;
    var $soap_cache;
    var $separator_string;

    /**
     * call soap-function
     *
     * calls soap-function with given parameters
     * @access public
     * @param string method method-name
     * @param string params parameters
     * @return mixed result
     */
    function call($method, $params)
    {
        $index = md5($method . ":" . implode($params, "-"));
        // return false if no session_id is given
        if (($method != "login") AND ($params["sid"] == ""))
            return false;
//      echo $this->caching_active;
        if (($this->caching_active == true) AND (isset($this->soap_cache[$index])))
        {
//          echo $index;
//          echo " from Cache<br>";
            $result = $this->soap_cache[$index];
        }
        else
        {
            $result = $this->_call($method, $params);
            // if Session is expired, re-login and try again
            if (($method != "login") AND $this->soap_client->fault AND in_array(mb_strtolower($this->faultstring), ["session not valid","session invalid", "session idled"]) )
            {
                $caching_status = $this->caching_active;
                $this->caching_active = false;
                $user_type = $this->user_type;
                $this->user_type = 'admin';
                $params["sid"] = $this->login();
                $result = $this->_call($method, $params);
                $this->caching_active = $caching_status;
                $this->user_type = $user_type;
            }
            elseif (! $this->soap_client->fault)
                $this->soap_cache[$index] = $result;
        }
        return $result;
    }

    /**
    * login
    *
    * login to soap-webservice
    * @access public
    * @return string result
    */
    function login()
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        if ($this->user_type == "admin") {
            $param = [
                'client' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["client"],
                'username' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["username"],
                'password' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["password"]
                ];
            $result = $this->call('login', $param);
        } elseif ($this->user_type == "user") {
            $param = [
                'sid' => $this->admin_sid,
                'user_id' => $connected_cms[$this->cms_type]->user->getId()
                ];
            $result = $this->call('loginStudipUser', $param);
        }
        if ($this->user_type == "admin")
            $this->admin_sid = $result;
        if ($this->user_type == "user")
            $this->user_sid = $result;
        return $result;
    }

    /**
    * Check Auth
    *
    * login to soap-webservice
    * @access public
    * @return string result
    */
    function checkPassword($username, $password)
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        $param = [
            'client' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["client"],
            'username' => $username,
            'password' => $password
        ];
        $result = $this->call('login', $param);
        return $result;
    }
}