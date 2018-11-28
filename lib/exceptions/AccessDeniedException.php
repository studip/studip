<?php
/**
 * AccessDeniedException.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class AccessDeniedException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (func_num_args() === 0) {
            $message = _('Sie haben nicht die Berechtigung, diese Aktion '
                       . 'auszuführen bzw. diesen Teil des Systems zu betreten.');
        }

        parent::__construct($message, $code, $previous);
    }
}
