<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipAuthLdapReadAndBind.class.php
// Stud.IP authentication against LDAP Server using read-only account and
// user bind
//
// Copyright (c) 2006 André Noack <noack@data-quest.de>
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
* Stud.IP authentication against LDAP Server using read-only account and
* following user bind
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipAuthLdapReadAndBind extends StudipAuthLdap {

    var $anonymous_bind = false;

    var $reader_dn;
    var $reader_password;

    /**
    * Constructor
    *
    *
    * @access public
    *
    */
    function __construct() {
        //calling the baseclass constructor
        parent::__construct();
    }


    function getUserDn($username){
        $user_dn = "";
        if (!($r = @ldap_bind($this->conn, $this->reader_dn, $this->reader_password))){
            $this->error_msg = sprintf(_("Anmeldung von %s fehlgeschlagen."),$this->reader_dn) . $this->getLdapError();
            return false;
        }
        if (!($result = @ldap_search($this->conn, $this->base_dn, $this->getLdapFilter($username), ['dn']))){
            $this->error_msg = _("Durchsuchen des LDAP Baumes fehlgeschlagen.") .$this->getLdapError();
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
        return $user_dn;
    }

    function isUsedUsername($username){
        if (!$this->doLdapConnect()) {
            return false;
        }
        $ret = (bool)$this->getUserDn($username);
        ldap_unbind($this->conn);
        return $ret;
    }
}
