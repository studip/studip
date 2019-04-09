<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipAuthLdap.class.php
// Stud.IP authentication against LDAP Server
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
* Stud.IP authentication against LDAP Server
*
* Stud.IP authentication against LDAP Server
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipAuthLdap extends StudipAuthAbstract {

    var $anonymous_bind = true;

    var $host;
    var $base_dn;
    var $username_attribute = 'uid';
    var $ldap_filter;
    var $bad_char_regex =  '/[^0-9_a-zA-Z]/';

    var $conn = null;
    var $user_data = null;

    /**
    * Constructor
    *
    *
    * @access public
    *
    */
    function __construct()
    {
        //calling the baseclass constructor
        parent::__construct();
    }


    function getLdapFilter($username)
    {
        if (isset($this->ldap_filter)) {
            list($user, $domain) = explode('@', $username);
            $search = ['%u', '%U', '%d', '%%'];
            $replace = [$username, $user, $domain, '%'];

            return str_replace($search, $replace, $this->ldap_filter);
        }

        return $this->username_attribute . '=' . $username;
    }

    function doLdapConnect()
    {
        if (!($this->conn = ldap_connect($this->host))) {
            $this->error_msg = _("Keine Verbindung zum LDAP Server möglich.");
            return false;
        }
        if (!($r = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3))){
            $this->error_msg = _("Setzen der LDAP Protokolversion fehlgeschlagen.");
            return false;
        }
        if ($this->start_tls) {
            if (!ldap_start_tls($this->conn)) {
                $this->error_msg = _("\"Start TLS\" fehlgeschlagen.");
                return false;
            }
        }
        return true;
    }

    function getUserDn($username)
    {
        $user_dn = "";

        if ($this->anonymous_bind){
            if (!($r = @ldap_bind($this->conn))){
                $this->error_msg =_("Anonymer Bind fehlgeschlagen.") . $this->getLdapError();
                return false;
            }
            if (!($result = @ldap_search($this->conn, $this->base_dn, $this->getLdapFilter($username), ['dn']))){
                $this->error_msg = _("Anonymes Durchsuchen des LDAP Baumes fehlgeschlagen.") .$this->getLdapError();
                return false;
            }
            if (!ldap_count_entries($this->conn, $result)){
                $this->error_msg = sprintf(_("%s wurde nicht unterhalb von %s gefunden."), $username, $this->base_dn);
                return false;
            }
            if (!($entry = @ldap_first_entry($this->conn, $result))){
                $this->error_msg = $this->getLdapError();
                return false;
            }
            if (!($user_dn = @ldap_get_dn($this->conn, $entry))){
                $this->error_msg = $this->getLdapError();
                return false;
            }
        } else {
            $user_dn = $this->username_attribute . "=" . $username . "," . $this->base_dn;
        }
        return $user_dn;
    }

    function doLdapBind($username, $password)
    {
        if (!$this->doLdapConnect()){
            return false;
        }
        if (!($user_dn = $this->getUserDn($username))){
            return false;
        }
        if (!$password){
            $this->error_msg = _("Kein Passwort eingegeben."); //some ldap servers seem to allow binding with a user dn and  without a password, if anonymous bind is enabled
            return false;
        }
        if (!($r = @ldap_bind($this->conn, $user_dn, $password))){
            if(ldap_errno($this->conn) == 49) {
                $this->error_msg = _("Bitte überprüfen Sie ihre Zugangsdaten.");
            }
            $this->error_msg = _("Anmeldung fehlgeschlagen.") . $this->getLdapError();
            return false;
        }
        if (!($result = @ldap_search($this->conn, $user_dn, "objectclass=*"))){
            $this->error_msg = _("Abholen der Benutzer Attribute fehlgeschlagen.") .$this->getLdapError();
            return false;
        }
        if (@ldap_count_entries($this->conn, $result)){
            if (!($info = @ldap_get_entries($this->conn, $result))){
                $this->error_msg = $this->getLdapError();
                return false;
            }
        }
        $this->user_data = $info[0];
        return true;
    }

    /**
    *
    *
    *
    * @access private
    *
    */
    function isAuthenticated($username, $password)
    {
        if (!$this->doLdapBind($username,$password)){
            ldap_unbind($this->conn);
            return false;
        }
        ldap_unbind($this->conn);
        return true;
    }



    function doLdapMap($map_params)
    {
        if (isset($this->user_data[$map_params][0])) {
            $ret = $this->user_data[$map_params][0];
            if ($ret[0] == ':') {
                $ret = base64_decode($ret);
            }
        }
        return $ret;
    }

    function doLdapMapDatafield($params)
    {
        $datafield_id = $params[1];
        $user = $params[2];
        $ldap_field = $this->doLdapMap($params[3]);
        if (isset($ldap_field)) {
            $df = $user->datafields->findOneBy('datafield_id', $datafield_id);
            if ($df) {
                $df->content = $ldap_field;
                return true;
            }
        }
    }

    function isUsedUsername($username)
    {
        if (!$this->anonymous_bind){
            $this->error = _("Kann den Benutzernamen nicht überprüfen, anonymous_bind ist ausgeschaltet!");
            return false;
        }
        if (!$this->doLdapConnect()){
            return false;
        }
        if (!($r = @ldap_bind($this->conn))){
            $this->error = _("Anonymer Bind fehlgeschlagen.") . $this->getLdapError();
            return false;
        }
        if (!($result = @ldap_search($this->conn, $this->base_dn, $this->getLdapFilter($username), ['dn']))){
            $this->error =  _("Anonymes Durchsuchen des LDAP Baumes fehlgeschlagen.") .$this->getLdapError();
            return false;
        }
        if (!ldap_count_entries($this->conn, $result)){
            $this->error_msg = _("Der Benutzername wurde nicht gefunden.");
            return false;
        }
        return true;
    }

    function getLdapError()
    {
            return _("<br>LDAP Fehler: ") . ldap_error($this->conn) ." (#" . ldap_errno($this->conn) . ")";
    }
}
