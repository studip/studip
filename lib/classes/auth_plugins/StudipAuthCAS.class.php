<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Stud.IP authentication against CAS Server
*
* @access   public
* @author   Dennis Reil <dennis.reil@offis.de>
* @package
*/

require_once 'vendor/phpCAS/CAS.php';
require_once 'lib/classes/cas/CAS_PGTStorage_Cache.php';

class StudipAuthCAS extends StudipAuthSSO {

    var $host;
    var $port;
    var $uri;
    var $cacert;

    var $cas;
    var $userdata;

    /**
    * Constructor
    *
    *
    * @access public
    *
    */
    function __construct() {
        parent::__construct();

        if (Request::option('sso')) {
            $this->cas = new CAS_Client(CAS_VERSION_2_0, $this->proxy, $this->host, $this->port, $this->uri, false);

            if ($this->proxy) {
                URLHelper::setBaseUrl($GLOBALS['ABSOLUTE_URI_STUDIP']);
                $this->cas->setPGTStorage(new CAS_PGTStorage_Cache($this->cas));
                $this->cas->setCallbackURL(URLHelper::getURL('dispatch.php/cas/proxy'));
            }

            if (isset($this->cacert)) {
                $this->cas->setCasServerCACert($this->cacert);
            } else {
                $this->cas->setNoCasServerValidation();
            }
        }
    }

    /**
     * Return the current username.
     */
    function getUser()
    {
        return $this->cas->getUser();
    }

    /**
     * Validate the username passed to the auth plugin.
     * Note: This triggers authentication if needed.
     */
    function verifyUsername($username)
    {
        $this->cas->forceAuthentication();
        return $this->getUser();
    }

    function getUserData($key){
        $userdataclassname = $GLOBALS["STUDIP_AUTH_CONFIG_CAS"]["user_data_mapping_class"];
        if (empty($userdataclassname)){
            echo ("ERROR: no userdataclassname specified.");
            return;
        }
        require_once($userdataclassname . ".class.php");
        // get the userdata
        if (empty($this->userdata)){
            $this->userdata = new $userdataclassname();
        }
        $result = $this->userdata->getUserData($key, $this->cas->getUser());
        return $result;
    }

    function logout(){
        // do a global cas logout
        $this->cas = new CAS_Client(CAS_VERSION_2_0, false, $this->host, $this->port, $this->uri, false);
        $this->cas->logout();
    }
}
