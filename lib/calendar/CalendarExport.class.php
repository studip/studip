<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarExport.class.php
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

require_once('app/models/calendar/SingleCalendar.php');

class CalendarExport
{
    protected $_writer;
    protected $export;
    private $count;

    public function __construct(&$writer)
    {
        $this->_writer = $writer;
    }

    public function exportFromDatabase($range_id = null, $start = 0, $end = Calendar::CALENDAR_END, $event_types = null, $except = NULL)
    {
        global $_calendar_error, $user;

        if (!$range_id) {
            $range_id = $user->id;
        }
        $calendar = new SingleCalendar($range_id);

        $this->_export($this->_writer->writeHeader());
        $calendar->getEvents($event_types, $start, $end);

        foreach ($calendar->events as $event) {
            $this->_export($this->_writer->write($event));
        }
        $this->count = sizeof($calendar->events);

        $this->_export($this->_writer->writeFooter());
    }

    public function exportFromObjects($events)
    {
        global $_calendar_error;

        $this->_export($this->_writer->writeHeader());

        $this->count = 0;
        foreach ($events as $event) {
            $this->_export($this->_writer->write($event));
            $this->count++;
        }

        if (!sizeof($events)) {
            $message = _('Es wurden keine Termine exportiert.');
        } else {
            $message = sprintf(ngettext('Es wurde 1 Termin exportiert', 'Es wurden %s Termine exportiert', sizeof($events)), sizeof($events));
        }

        $this->_export($this->_writer->writeFooter());
    }

    public function _export($exp)
    {
        if (!empty($exp)) {
            $this->export[] = $exp;
        }
    }

    public function getExport()
    {
        return $this->export;
    }

    public function getCount()
    {
        return $this->count;
    }
}
