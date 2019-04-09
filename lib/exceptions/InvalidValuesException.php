<?php
/**
 * InvalidValuesException.php
 * Exception class used by validation of forms.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class InvalidValuesException extends Exception
{
    private $checked = [];
    
    /**
     * Constructor
     * 
     * @param string $message The error message
     * @param array $checked Associative array
     */
    public function __construct($message, $checked)
    {
        $this->checked = $checked;
        parent::__construct($message);
    }
    
    public function getChecked()
    {
        return $this->checked;
    }
}