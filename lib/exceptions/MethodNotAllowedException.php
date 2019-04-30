<?php
/**
 * MethodNotAllowedException.php
 *
 * This exceptions is thrown when a request's method is not allowed.
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

class MethodNotAllowedException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message ?: _('Unzulässige Request-Methode beim Aufruf.'), $code, $previous);
    }
}
