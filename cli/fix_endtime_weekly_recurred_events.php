#!/usr/bin/env php
<?php
/**
 * fix_endtime_weekly_recurred_events.php
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
require_once __DIR__ . '/studip_cli_env.inc.php';
require_once 'app/models/calendar/Calendar.php';

$events = EventData::findBySQL("rtype = 'WEEKLY' AND IFNULL(count, 0) > 0");
$cal_event = new CalendarEvent();
$i = 0;
foreach ($events as $event) {
    $id = $event->getId();
    $cal_event->event = $event;
    $rrule = $cal_event->getRecurrence();
    $cal_event->setRecurrence($rrule);
    $event->expire = $cal_event->event->expire;
    $event->setId($id);
    $event->store();
    $i++;
}

fwrite(STDOUT, 'Wrong end time of recurrence fixed for ' . $i . ' events.' . chr(10));
exit(1);