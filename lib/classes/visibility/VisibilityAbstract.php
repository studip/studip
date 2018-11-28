<?php
/**
 * VisibilityAbstract.php - Abstract class to define a visibilitySetting
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Defines basic attributes and functions a visibilitySetting needs
 * 
 * @abstract
 */
abstract class VisibilityAbstract
{

    protected $activated;
    protected $int_representation;
    protected $display_name;
    protected $description;

    /**
     * verify method to determine if 2 users satisfy the criteria
     * @param string $user_id the owner of the visibility
     * @param string $other_id the user who gets checked if he can see the
     * selected object
     * @abstract
     */
    abstract function verify($user_id, $other_id);

    /**
     * Returns if a visibiltySetting is activated
     * 
     * @return boolean true if the visibilitySetting is activated 
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * Returns the int representation of the visibilitySetting in the database
     * 
     * @return int the visibilitySetting in the database 
     */
    public function getIntRepresentation()
    {
        return $this->int_representation;
    }

    /**
     * Returns the displayname of a visibilitySetting
     * 
     * @return string the displayname 
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Returns the description of a visibilitySetting
     * 
     * @return string  description of the visibilitySetting
     */
    public function getDescription()
    {
        return $this->description;
    }

}

?>
