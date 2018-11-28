<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * Error.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     calendar
 */


class CalendarError
{

    var $status;
    var $message;
    var $file;
    var $line;

    public function __construct($status, $message, $file = '', $line = '')
    {
        $this->status = $status;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getLine()
    {
        return $this->line;
    }

}
