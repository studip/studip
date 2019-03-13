<?php
/**
 * Role.class.php
 *
 * @author      Dennis Reil <dennis.reil@offis.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     pluginengine
 * @subpackage  core
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 */
class Role
{
    const UNKNOWN_ROLE_ID = null;

    public $roleid;
    public $rolename;
    public $systemtype;

    /**
     * Constructor
     */
    public function __construct($id = self::UNKNOWN_ROLE_ID, $name = '', $system = false)
    {
        $this->setRoleid($id);
        $this->setRolename($name);
        $this->setSystemtype($system);
    }

    /**
     * Returns the role's id.
     *
     * @return int
     */
    public function getRoleid()
    {
        return $this->roleid;
    }

    /**
     * Set the role's id.
     *
     * @param int $newid
     */
    public function setRoleid($newid)
    {
        $this->roleid = $newid;
    }

    /**
     * Returns the role's name.
     *
     * @return string
     */
    public function getRolename()
    {
        return $this->rolename;
    }

    /**
     * Set the role's name.
     *
     * @param string $newrole
     */
    public function setRolename($newrole)
    {
        $this->rolename = $newrole;
    }

    /**
     * Returns whether the role is a system role.
     *
     * @return boolean
     */
    public function getSystemtype()
    {
        return $this->systemtype;
    }

    /**
     * Sets whether the role is a system role.
     *
     * @param boolean $newtype
     */
    public function setSystemtype($newtype)
    {
        $this->systemtype = (bool) $newtype;
    }
}
