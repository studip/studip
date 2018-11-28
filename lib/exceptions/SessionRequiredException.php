<?php
/**
 * SessionRequiredException.php
 *
 * This exception is thrown when a token should have been stored in a
 * non-existant session.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      mlunzena@uos.de
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class SessionRequiredException extends Exception
{
    /**
     * @param string this parameter is ignored but required by PHP
     */
    function __construct($message = NULL) {
        parent::__construct(_("Fehlende Session."));
    }
}
