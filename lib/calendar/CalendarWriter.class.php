<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarWriter.class.php
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

class CalendarWriter
{
    var $default_filename_suffix;
    var $format;
    var $client_identifier;

    public function __construct()
    {
        // initialize error handler
        $GLOBALS['_calendar_error'] = new ErrorHandler();
    }

    public function write(Event &$event)
    {
        return $event->properties;
    }

    public function writeHeader()
    {
    }

    public function writeFooter()
    {
    }

    public function getDefaultFilenameSuffix()
    {
        return $this->default_filename_suffix;
    }

    public function getFormat()
    {
        return $this->format;
    }
}
