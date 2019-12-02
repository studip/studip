<?php
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE - no html

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipAuthAbstract.class.php
// Abstract class, used as a template for authentication plugins
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

/**
* abstract base class for authentication plugins
*
* abstract base class for authentication plugins
* to write your own authentication plugin, derive it from this class and
* implement the following abstract methods: isUsedUsername($username) and
* isAuthenticated($username, $password, $jscript)
* don't forget to call the parents constructor if you implement your own, php
* won't do that for you !
*
* @abstract
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipAuthAbstract {

    /**
    * contains error message, if authentication fails
    *
    *
    * @access   public
    * @var      string
    */
    var $error_msg;

    /**
    * indicates whether the authenticated user logs in for the first time
    *
    *
    * @access   public
    * @var      bool
    */
    var $is_new_user = false;

    /**
     * array of user domains to assign to each user, can be set in local.inc
     *
     * @access  public
     * @var     array $user_domains
     */
    var $user_domains;

    /**
    * associative array with mapping for database fields
    *
    * associative array with mapping for database fields,
    * should be set in local.inc
    * structure :
    * array("<table name>.<field name>" => array(   "callback" => "<name of callback method used for data retrieval>",
    *                                               "map_args" => "<arguments passed to callback method>"))
    * @access   public
    * @var      array $user_data_mapping
    */
    var $user_data_mapping = null;

    /**
    * name of the plugin
    *
    * name of the plugin (last part of class name) is set in the constructor
    * @access   public
    * @var      string
    */
    var $plugin_name;

    /**
    * text, which precedes error message for the plugin
    *
    *
    * @access   public
    * @var      string
    */
    var $error_head;

    private static $plugin_instances;

    /**
    * static method to instantiate and retrieve a reference to an object (singleton)
    *
    * use always this method to instantiate a plugin object, it will ensure that only one object of each
    * plugin will exist
    * @access public
    * @static
    * @param    string  name of plugin, if omitted an array with all plugin objects will be returned
    * @return   mixed   either a reference to the plugin with the passed name, or an array with references to all plugins
    */
    static function getInstance($plugin_name = false)
    {
        if (!is_array(self::$plugin_instances)) {
            foreach($GLOBALS['STUDIP_AUTH_PLUGIN'] as $plugin) {
                $plugin = "StudipAuth" . $plugin;
                include_once "lib/classes/auth_plugins/" . $plugin . ".class.php";
                self::$plugin_instances[mb_strtoupper($plugin)] = new $plugin;
            }
        }
        return ($plugin_name) ? self::$plugin_instances[mb_strtoupper("StudipAuth" . $plugin_name)] : self::$plugin_instances;
    }

    /**
    * static method to check authentication in all plugins
    *
    * if authentication fails in one plugin, the error message is stored and the next plugin is used
    * if authentication succeeds, the uid element in the returned array will contain the Stud.IP user id
    *
    * @access public
    * @static
    * @param    string  the username to check
    * @param    string  the password to check
    * @return   array   structure: array('uid'=>'string <Stud.IP user id>','error'=>'string <error message>','is_new_user'=>'bool')
    */
    static function CheckAuthentication($username, $password)
    {

        $plugins = StudipAuthAbstract::GetInstance();
        $error = false;
        $uid = false;
        foreach ($plugins as $object) {
            // SSO plugins can't be used
            if ($object instanceof StudipAuthSSO) {
                continue;
            }
            if ($user = $object->authenticateUser($username, $password)) {
                if ($user) {
                    $uid = $user->id;
                    $locked = $user['locked'];
                    $key = $user['validation_key'];
                    $checkIPRange = ($GLOBALS['ENABLE_ADMIN_IP_CHECK'] && $user['perms'] === 'admin')
                        || ($GLOBALS['ENABLE_ROOT_IP_CHECK'] && $user['perms'] === 'root');

                    $exp_d = UserConfig::get($user['user_id'])->EXPIRATION_DATE;

                    if ($exp_d > 0 && $exp_d < time()) {
                        $error .= _("Dieses Benutzerkonto ist abgelaufen.<br> Wenden Sie sich bitte an die Administration.") . "<BR>";
                        return ['uid' => false, 'error' => $error];
                    } else if ($locked == "1") {
                        $error .= _("Dieser Benutzer ist gesperrt! Wenden Sie sich bitte an die Administration.") . "<BR>";
                        return ['uid' => false, 'error' => $error];
                    } else if ($key != '') {
                        return ['uid' => $uid, 'user' => $user, 'error' => $error, 'need_email_activation' => $uid];
                    } else if ($checkIPRange && !self::CheckIPRange()) {
                        $error .= _("Der Login in Ihren Account ist aus diesem Netzwerk nicht erlaubt.") . "<BR>";
                        return ['uid' => false, 'error' => $error];
                    }
                }
                return ['uid' => $uid, 'user' => $user, 'error' => $error, 'is_new_user' => $object->is_new_user];
            } else {
                $error .= (($object->error_head) ? ("<b>" . $object->error_head . ":</b> ") : "") . $object->error_msg . "<br>";
            }
        }
        return ['uid' => $uid, 'error' => $error];
    }

    /**
    * static method to check if passed username is used in external data sources
    *
    * all plugins are checked, the error messages are stored and returned
    *
    * @access public
    * @static
    * @param    string the username
    * @return   array
    */
    static function CheckUsername($username)
    {
        $plugins = StudipAuthAbstract::GetInstance();
        $error = false;
        $found = false;
        foreach ($plugins as $object) {
            if ($found = $object->isUsedUsername($username)) {
                return ['found' => $found,'error' => $error];
            } else {
                $error .= (($object->error_head) ? ("<b>" . $object->error_head . ":</b> ") : "") . $object->error_msg . "<br>";
            }
        }
        return ['found' => $found,'error' => $error];
    }
    /**
    * static method to check for a mapped field
    *
    * this method checks in the plugin with the passed name, if the passed
    * Stud.IP DB field is mapped to an external data source
    *
    * @access public
    * @static
    * @param    string  the name of the db field must be in form '<table name>.<field name>'
    * @param    string  the name of the plugin to check
    * @return   bool    true if the field is mapped, else false
    */
    static function CheckField($field_name,$plugin_name)
    {
        if (!$plugin_name) {
            return false;
        }
        $plugin = StudipAuthAbstract::GetInstance($plugin_name);
        return (is_object($plugin) ? $plugin->isMappedField($field_name) : false);
    }

    /**
     * static method to check if ip address belongs to allowed range
     *
     * @return   bool    true if the client ip address is within the valid range
     */
    public static function CheckIPRange()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $version = mb_substr_count($ip, ':') > 1 ? 'V6' : 'V4'; // valid ip v6 addresses have atleast two colons
        $method = 'CheckIPRange' . $version;
        if (is_array($GLOBALS['LOGIN_IP_RANGES'][$version])) {
            foreach ($GLOBALS['LOGIN_IP_RANGES'][$version] as $range) {
                if (self::$method($ip, $range)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $ip string IPv4 adress
     * @param $range array assoc array with [start] & [end]
     * @return bool
     */
    public static function CheckIPRangeV4($ip, $range)
    {
        $ipv4 = ip2long($ip);
        if ($ipv4 === false) {
            return false; // invalid ip address
        }

        $start = ip2long($range['start']);
        $end = ip2long($range['end']);

        return $ipv4 >= $start && $ipv4 <= $end;
    }

    /**
     * @param $ip string IPv6 address
     * @param $range array assoc array with [start] & [end]
     * @return bool
     */
    public static function CheckIPRangeV6($ip, $range)
    {
        $ipv6 = inet_pton($ip);
        if ($ipv6 === false) {
            return false; // invalid ip address
        }

        $start = inet_pton($range['start']);
        $end = inet_pton($range['end']);

        return mb_strlen($ipv6) === mb_strlen($start)
        && $ipv6 >= $start && $ipv6 <= $end;
    }

    /**
    * Constructor
    *
    * the constructor is private, you should use StudipAuthAbstract::GetInstance($plugin_name)
    * to get a reference to a plugin object. Make sure the constructor in the base class is called
    * when deriving your own plugin class, it assigns the settings from local.inc as members of the plugin
    * each key of the $STUDIP_AUTH_CONFIG_<plugin name> array will become a member of the object
    *
    * @access private
    *
    */
    function __construct()
    {
        $this->plugin_name = mb_strtolower(mb_substr(get_class($this),10));
        //get configuration array set in local inc
        $config_var = $GLOBALS["STUDIP_AUTH_CONFIG_" . mb_strtoupper($this->plugin_name)];
        //assign each key in the config array as a member of the plugin object
        if (isset($config_var)) {
            foreach ($config_var as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
    * authentication method
    *
    * this method authenticates the passed username, it is used by StudipAuthAbstract::CheckAuthentication()
    * if authentication succeeds it calls StudipAuthAbstract::doDataMapping() to map data fields
    * if the authenticated user logs in for the first time it calls StudipAuthAbstract::doNewUserInit() to
    * initialize the new user
    * @access private
    * @param    string  the username to check
    * @param    string  the password to check
    * @return   string  if authentication succeeds the Stud.IP user , else false
    */
    function authenticateUser($username, $password)
    {
        $username = $this->verifyUsername($username);
        if ($this->isAuthenticated($username, $password)) {
            if ($user = $this->getStudipUser($username)) {
                $this->doDataMapping($user);
                if ($this->is_new_user){
                    $this->doNewUserInit($user);
                }
                $this->setUserDomains($user);
            }
            return $user;
        } else {
            return false;
        }
    }

    /**
    * method to retrieve the Stud.IP user id to a given username
    *
    *
    * @access   private
    * @param    string  the username
    * @return   User  the Stud.IP or false if an error occurs
    */
    function getStudipUser($username)
    {
        $user = User::findByUsername($username);
        if ($user) {
            $auth_plugin = $user->auth_plugin;
            if ($auth_plugin === null) {
                $this->error_msg = _("Dies ist ein vorläufiger Benutzer.") . "<br>";
                return false;
            }
            if ($auth_plugin != $this->plugin_name){
                $this->error_msg = sprintf(_("Dieser Benutzername wird bereits über %s authentifiziert!"),$auth_plugin) . "<br>";
                return false;
            }
            return $user;
        }
        $new_user = new User();
        $new_user->username = $username;
        $new_user->perms = 'autor';
        $new_user->auth_plugin = $this->plugin_name;
        $new_user->preferred_language = $_SESSION['_language'];
        if ($new_user->store()) {
            $this->is_new_user = true;
            return $new_user;
        }
    }

    /**
     * initialize a new user
     *
     * this method is invoked for one time, if a new user logs in ($this->is_new_user is true)
     * place special treatment of new users here
     *
     * @access private
     * @param
     *            User the user object
     * @return bool
     */
    function doNewUserInit($user)
    {
        // auto insertion of new users, according to $AUTO_INSERT_SEM[] (defined in local.inc)
        AutoInsert::instance()->saveUser($user->id, $user->perms);
    }

    /**
     * This method sets the user domains for the current user.
     *
     * @access  private
     * @param   User  the user object
     */
    function setUserDomains ($user) {
        $user_domains = $this->getUserDomains();
        $uid = $user->id;
        if (isset($user_domains)) {
            $old_domains = UserDomain::getUserDomainsForUser($uid);

            foreach ($old_domains as $domain) {
                if (!in_array($domain->id, $user_domains)) {
                    $domain->removeUser($uid);
                }
            }

            foreach ($user_domains as $user_domain) {
                $domain = new UserDomain($user_domain);

                if ($domain->isNew()) {
                    $domain->name = $user_domain;
                    $domain->store();
                }

                if (!in_array($domain, $old_domains)) {
                    $domain->addUser($uid);
                }
            }
        }
    }

    /**
     * Get the user domains to assign to the current user.
     */
    function getUserDomains ()
    {
        return $this->user_domains;
    }

    /**
    * this method handles the data mapping
    *
    * for each entry in $this->user_data_mapping the according callback will be invoked
    * the return value of the callback method is then written to the db field, which is specified
    * in the key of the array
    *
    * @access   private
    * @param    User  the user object
    * @return   bool
    */
    function doDataMapping($user)
    {
        if ($user && is_array($this->user_data_mapping)) {
            foreach($this->user_data_mapping as $key => $value){
                if (method_exists($this, $value['callback'])) {
                    $split = explode(".",$key);
                    $table = $split[0];
                    $field = $split[1];
                    if ($table == 'auth_user_md5' || $table == 'user_info') {
                        $mapped_value = call_user_func([$this, $value['callback']],$value['map_args']);
                        if (isset($mapped_value)) {
                            $user->setValue($field, $mapped_value);
                        }
                    } else {
                        call_user_func([$this, $value['callback']],[$table,$field,$user,$value['map_args']]);
                    }
                }
            }
            return $user->store();
        }
        return false;
    }

    /**
    * method to check, if a given db field is mapped by the plugin
    *
    *
    * @access   private
    * @param    string  the name of the db field (<table_name>.<field_name>)
    * @return   bool    true if the field is mapped
    */
    function isMappedField($name)
    {
        return isset($this->user_data_mapping[$name]);
    }

    /**
    * method to eliminate bad characters in the given username
    *
    *
    * @access   private
    * @param    string  the username
    * @return   string  the username
    */
    function verifyUsername($username)
    {
        if($this->username_case_insensitiv) $username = mb_strtolower($username);
        if ($this->bad_char_regex){
            return preg_replace($this->bad_char_regex, '', $username);
        } else {
            return trim($username);
        }
    }

    /**
    * method to check, if username is used
    *
    * abstract MUST be realized
    *
    * @access   private
    * @param    string  the username
    * @return   bool    true if the username exists
    */
    function isUsedUsername($username)
    {
        $this->error_msg = sprintf(_("Methode %s nicht implementiert!"),get_class($this) . "::isUsedUsername()");
        return false;
    }

    /**
    * method to check the authentication of a given username and a given password
    *
    * abstract, MUST be realized
    *
    * @access private
    * @param    string  the username
    * @param    string  the password
    * @return   bool    true if authentication succeeds
    */
    function isAuthenticated($username, $password) {
        $this->error = sprintf(_("Methode %s nicht implementiert!"),get_class($this) . "::isAuthenticated()");
        return false;
    }
}
