<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Role.class.php
 *
 * PHP version 5
 *
 * @author      Dennis Reil <dennis.reil@offis.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     pluginengine
 * @subpackage  core
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 */

/**
 * Rolle
 *
 */
class Role
{
    public $roleid;
    public $rolename;
    public $systemtype;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->roleid = UNKNOWN_ROLE_ID;
        $this->rolename = "";
        $this->systemtype = false;
    }

    /**
     * Enter description here...
     *
     * @return int
     */
    public function getRoleid()
    {
        return $this->roleid;
    }

    /**
     * Enter description here...
     *
     * @param int $newid
     */
    public function setRoleid($newid)
    {
        $this->roleid = $newid;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getRolename()
    {
        return $this->rolename;
    }

    /**
     * Enter description here...
     *
     * @param string $newrole
     */
    public function setRolename($newrole)
    {
        $this->rolename = $newrole;
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function getSystemtype()
    {
        return $this->systemtype;
    }

    /**
     * Enter description here...
     *
     * @param boolean $newtype
     */
    public function setSystemtype($newtype)
    {
        $this->systemtype = $newtype;
    }
}

?>
