<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarWriterICalendar.class.php
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

define('CALENDAR_WEEKSTART', 'MO');

class CalendarWriteriCalendar extends CalendarWriter
{
    var $newline = "\r\n";

    public function __construct()
    {

        parent::__construct();
        $this->default_filename_suffix = "ics";
        $this->format = "iCalendar";
    }

    public function writeHeader()
    {

        // Default values
        $header = "BEGIN:VCALENDAR" . $this->newline;
        $header .= "VERSION:2.0" . $this->newline;
        if ($this->client_identifier) {
            $header .= "PRODID:" . $this->client_identifier . $this->newline;
        } else {
            $header .= "PRODID:-//Stud.IP@{$_SERVER['SERVER_NAME']}//Stud.IP_iCalendar Library";
            $header .= " //EN" . $this->newline;
        }
        $header .= "METHOD:PUBLISH" . $this->newline;

        // time zone definition CET/CEST
        $header .= 'CALSCALE:GREGORIAN' . $this->newline
                   . 'BEGIN:VTIMEZONE' . $this->newline
                   . 'TZID:Europe/Berlin' . $this->newline
                   . 'BEGIN:DAYLIGHT' . $this->newline
                   . 'TZOFFSETFROM:+0100' . $this->newline
                   . 'TZOFFSETTO:+0200' . $this->newline
                   . 'TZNAME:CEST' . $this->newline
                   . 'DTSTART:19700329T020000' . $this->newline
                   . 'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3' . $this->newline
                   . 'END:DAYLIGHT' . $this->newline
                   . 'BEGIN:STANDARD' . $this->newline
                   . 'TZOFFSETFROM:+0200' . $this->newline
                   . 'TZOFFSETTO:+0100' . $this->newline
                   . 'TZNAME:CET' . $this->newline
                   . 'DTSTART:19701025T030000' . $this->newline
                   . 'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10' . $this->newline
                   . 'END:STANDARD' . $this->newline
                   . 'END:VTIMEZONE' . $this->newline;

        return $header;
    }

    public function writeFooter()
    {
        return "END:VCALENDAR" . $this->newline;
    }

    /**
     * Export this component as iCalendar format
     *
     * @param object $event The event to export.
     * @return String iCalendar formatted data
     */
    public function write(Event &$event)
    {

        $match_pattern_1 = ['\\', '\n', ';', ','];
        $replace_pattern_1 = ['\\\\', '\\n', '\;', '\,'];
        $match_pattern_2 = ['\\', '\n', ';'];
        $replace_pattern_2 = ['\\\\', '\\n', '\;'];

        $result = "BEGIN:VEVENT" . $this->newline;

        foreach ($event->getProperties() as $name => $value) {
            $params = [];
            $params_str = '';

            if ($name === 'SUMMARY') {
                $value = $event->getTitle();
            }
            if ($value === '' || is_null($value)) {
                continue;
            }

            switch ($name) {
                // not supported event properties
                case 'SEMNAME':
                case 'EXPIRE':
                case 'STUDIP_AUTHOR_ID':
                case 'STUDIP_EDITOR_ID':
                case 'STUDIP_ID':
                case 'BEGIN':
                case 'END':
                case 'EVENT_TYPE':
                case 'SEM_ID':
                case 'STUDIP_GROUP_STATUS':
                case 'STUDIP_CATEGORY':
                    continue 2;

                // text fields
                case 'SUMMARY':
                    $value = str_replace($match_pattern_1, $replace_pattern_1, $value);
                    break;
                case 'DESCRIPTION':
                    $value = str_replace($match_pattern_1, $replace_pattern_1, $event->getDescription());
                    break;
                case 'LOCATION':
                    $value = str_replace($match_pattern_1, $replace_pattern_1, $event->getLocation());
                    break;

                case 'CATEGORIES':
                    $value = $this->_exportCategories($event);
                    break;

                // Date fields
                case 'LAST-MODIFIED':
                case 'CREATED':
                case 'COMPLETED':
                    $value = $this->_exportDateTime($value, true);
                    break;

                case 'DTSTAMP':
                    $value = $this->_exportDateTime(time(), true);
                    break;

                case 'DTEND':
                case 'DTSTART':
                    if ($event->isDayEvent()) {
                        $params['VALUE'] = 'DATE';
                        $params_str = ';VALUE=DATE';
                        $value++;
                    }
                case 'DUE':
                case 'RECURRENCE-ID':
                    if (array_key_exists('VALUE', $params)) {
                        if ($params['VALUE'] == 'DATE') {
                            $value = $this->_exportDate($value);
                        } else {
                            $value = $this->_exportDateTime($value);
                            $params_str = ';TZID=Europe/Berlin';
                        }
                    } else {
                        $value = $this->_exportDateTime($value);
                        $params_str = ';TZID=Europe/Berlin';
                    }
                    break;

                case 'EXDATE':
                    if (array_key_exists('VALUE', $params)) {
                        $value = $this->_exportExdate($value, $params['VALUE']);
                    } else {
                        $value = $this->_exportExdate($value, 'DATE-TIME');
                    }
                    $params_str = ';TZID=Europe/Berlin';
                    break;

                case 'RDATE':
                    if (array_key_exists('VALUE', $params)) {
                        if ($params['VALUE'] == 'DATE') {
                            $value = $this->_exportDate($value);
                        } else if ($params['VALUE'] == 'PERIOD') {
                            $value = $this->_exportPeriod($value);
                        } else {
                            $value = $this->_exportDateTime($value);
                        }
                    } else {
                        $value = $this->_exportDateTime($value);
                    }
                    break;

                case 'TRIGGER':
                    if (array_key_exists('VALUE', $params)) {
                        if ($params['VALUE'] == 'DATE-TIME') {
                            $value = $this->_exportDateTime($value);
                        } else if ($params['VALUE'] == 'DURATION') {
                            $value = $this->_exportDuration($value);
                        }
                    } else {
                        $value = $this->_exportDuration($value);
                    }
                    break;

                // Duration fields
                case 'DURATION':
                    $value = $this->_exportDuration($value);
                    break;

                // Period of time fields
                case 'FREEBUSY':
                    $value_str = '';
                    foreach ($value as $period) {
                        $value_str .= empty($value_str) ? '' : ',';
                        $value_str .= $this->_exportPeriod($period);
                    }
                    $value = $value_str;
                    break;


                // UTC offset fields
                case 'TZOFFSETFROM':
                case 'TZOFFSETTO':
                    $value = $this->_exportUtcOffset($value);
                    break;

                // Integer fields
                case 'PERCENT-COMPLETE':
                    if ($event->getPermission() == Event::PERMISSION_CONFIDENTIAL)
                        $value = '';
                case 'REPEAT':
                case 'SEQUENCE':
                    $value = "$value";
                    break;

                case 'PRIORITY':
                    if ($event->getPermission() == Event::PERMISSION_CONFIDENTIAL)
                        $value = '0';
                    else {
                        switch ($value) {
                            case 1:
                                $value = '1';
                                break;
                            case 2:
                                $value = '5';
                                break;
                            case 3:
                                $value = '9';
                                break;
                            default:
                                $value = '0';
                        }
                    }
                    break;

                // Geo fields
                case 'GEO':
                    if ($event->getPermission() == Event::PERMISSION_CONFIDENTIAL)
                        $value = '';
                    else
                        $value = $value['latitude'] . ',' . $value['longitude'];
                    break;

                // Recursion fields
                case 'EXRULE':
                case 'RRULE':
                    if ($event->getRecurrence('rtype') != 'SINGLE')
                        $value = $this->_exportRecurrence($value);
                    else
                        continue 2;
                    break;

                case "UID":
                    $value = "$value";
            }
            if ($name) {
                $attr_string = "$name$params_str:$value";
                $result .= $this->_foldLine($attr_string) . $this->newline;
            }
        }
    //    if ($event->isGroupEvent()) {
        if ($event instanceof CalendarEvent && $event->attendees->count() > 1) {
            $result .= $this->_exportGroupEventProperties($event);
        }
        //  $result .= 'DTSTAMP:' . $this->_exportDateTime(time()) . $this->newline;
        $result .= "END:VEVENT" . $this->newline;

        return $result;
    }

    /**
     * Export a UTC Offset field
     *
     * @param array $value
     * @return String UTC offset field iCalendar formatted
     */
    public function _exportUtcOffset($value)
    {
        $offset = $value['ahead'] ? '+' : '-';
        $offset .= sprintf('%02d%02d', $value['hour'], $value['minute']);
        if (array_key_exists('second', $value)) {
            $offset .= sprintf('%02d', $value['second']);
        }

        return $offset;
    }

    /**
     * Export a Time Period field
     *
     * @param array $value
     * @return String Period field iCalendar formatted
     */
    public function _exportPeriod($value)
    {
        $period = $this->_exportDateTime($value['start']);
        $period .= '/';
        if (array_key_exists('duration', $value)) {
            $period .= $this->_exportDuration($value['duration']);
        } else {
            $period .= $this->_exportDateTime($value['end']);
        }
        return $period;
    }

    /**
     * Export a DateTime field
     *
     * @param int $value Unix timestamp
     * @return String Date and time (UTC) iCalendar formatted
     */
    public function _exportDateTime($value, $utc = false)
    {

//      $TZOffset  = 3600 * mb_substr(date('O', $value), 0, 3);
//      $TZOffset += 60 * mb_substr(date('O', $value), 3, 2);
        //transform local time in UTC
        if ($utc) {
            $value -= date('Z', $value);
        }

        return $this->_exportDate($value) . 'T' . $this->_exportTime($value, $utc);
    }

    /**
     * Export a Time field
     *
     * @param int $value Unix timestamp
     * @return String Time (UTC) iCalendar formatted
     */
    public function _exportTime($value, $utc = false)
    {
        $time = date("His", $value);
        if ($utc) {
            $time .= 'Z';
        }

        return $time;
    }

    /**
     * Export a Date field
     */
    public function _exportDate($value)
    {
        return date("Ymd", $value);
    }

    /**
     * Export a duration value
     */
    public function _exportDuration($value)
    {
        $duration = '';
        if ($value < 0) {
            $value *= - 1;
            $duration .= '-';
        }
        $duration .= 'P';

        $weeks = floor($value / (7 * 86400));
        $value = $value % (7 * 86400);
        if ($weeks) {
            $duration .= $weeks . 'W';
        }

        $days = floor($value / (86400));
        $value = $value % (86400);
        if ($days) {
            $duration .= $days . 'D';
        }

        if ($value) {
            $duration .= 'T';

            $hours = floor($value / 3600);
            $value = $value % 3600;
            if ($hours) {
                $duration .= $hours . 'H';
            }

            $mins = floor($value / 60);
            $value = $value % 60;
            if ($mins) {
                $duration .= $mins . 'M';
            }

            if ($value) {
                $duration .= $value . 'S';
            }
        }

        return $duration;
    }

    /**
     * Export a recurrence rule
     */
    public function _exportRecurrence($value)
    {
        $rrule = [];
        // the last day of week in a MONTHLY or YEARLY recurrence in the
        // Stud.IP calendar is 5, in iCalendar it is -1
        if ($value['sinterval'] == '5')
            $value['sinterval'] = '-1';

        if ($value['count'])
            unset($value['expire']);

        foreach ($value as $r_param => $r_value) {
            if ($r_value) {
                switch ($r_param) {
                    case 'rtype':
                        $rrule[] = 'FREQ=' . $r_value;
                        break;
                    case 'expire':
                        // end of unix epoche (this is also the end of Stud.IP epoche ;-) )
                        if ($r_value < Calendar::CALENDAR_END)
                            $rrule[] = 'UNTIL=' . $this->_exportDateTime($r_value, true);
                        break;
                    case 'linterval':
                        $rrule[] = 'INTERVAL=' . $r_value;
                        break;
                    case 'wdays':
                        switch ($value['rtype']) {
                            case 'WEEKLY':
                                $rrule[] = 'BYDAY=' . $this->_exportWdays($r_value);
                                break;
                            // Some CUAs (e.g. Outlook) don't understand the nWDAY syntax
                            // (where n is the nth ocurrence of the day in a given period of
                            // time and WDAY is the day of week) the RRULE uses the BYSETPOS
                            // rule.
                            case 'MONTHLY':
                            case 'YEARLY':
                                $rrule[] = 'BYDAY=' . $value['sinterval'] . $this->_exportWdays($r_value);
                                $rrule[] = 'BYDAY=' . $this->_exportWdays($r_value);
                                // The Stud.IP calendar don't support multiple values in a
                                // comma separated list.

                                if ($value['sinterval'])
                                    $rrule[] = 'BYSETPOS=' . $value['sinterval'];

                                break;
                        }
                        break;
                    case 'day':
                        $rrule[] = 'BYMONTHDAY=' . $r_value;
                        break;
                    case 'month':
                        $rrule[] = 'BYMONTH=' . $r_value;
                        break;
                    case 'count':
                        $rrule[] = 'COUNT=' . $r_value;
                        break;
                }
            }
        }

        if ($value['rtype'] == 'WEEKLY' && CALENDAR_WEEKSTART != 'MO') {
            $rrule[] = 'WKST=' . CALENDAR_WEEKSTART;
        }

        return implode(';', $rrule);
    }

    /**
     * Return the Stud.IP calendar wdays attribute of a event recurrence
     */
    public function _exportWdays($value)
    {
        $wdays_map = ['1' => 'MO', '2' => 'TU', '3' => 'WE', '4' => 'TH', '5' => 'FR',
            '6' => 'SA', '7' => 'SU'];
        $wdays = [];
        preg_match_all('/(\d)/', $value, $matches);
        foreach ($matches[1] as $match) {
            $wdays[] = $wdays_map[$match];
        }

        return implode(',', $wdays);
    }

    public function _exportExdate($value, $param)
    {
        $exdates = [];
        $date_times = explode(',', $value);
        foreach ($date_times as $date_time) {
            if ($param == 'DATE-TIME')
                $exdates[] = $this->_exportDateTime($date_time);
            else
                $exdates[] = $this->_exportDate($date_time);
        }

        return implode(',', $exdates);
    }

    private function _exportGroupEventProperties(Event $event)
    {
        $organizer = User::find($event->getAuthorId());
        if ($organizer) {
            $properties = $this->_foldLine('ORGANIZER;CN="'
                    . $organizer->getFullName()
                    . '":mailto:' . $organizer->Email)
                    . $this->newline;
        } else {
            $properties = $this->_foldLine('ORGANIZER;CN="'
                    . _('unbekannt')
                    . '":mailto:' . $GLOBALS['user']->email)
                    . $this->newline;
        }
        foreach ($event->attendees as $event_member) {
            if ($event->getAuthorId() == $event_member->user->id) {
                if ($event_member->user) {
                    $properties .= $this->_foldLine('ATTENDEE;'
                            . 'ROLE=REQ-PARTICIPANT;'
                            . 'CN="' . $event_member->user->getFullName()
                            . '":mailto:' . $event_member->user->Email)
                            . $this->newline;
                } else {
                    $properties = '';
                    /*
                    $properties .= $this->_foldLine('ATTENDEE;'
                            . 'ROLE=REQ-PARTICIPANT;'
                            . 'CN="' . _('unbekannt') . '"')
                            . $this->newline;
                     *
                     */
                }
            } else {
                if ($event_member->user) {
                    switch ($event_member->group_status) {
                        case CalendarEvent::PARTSTAT_ACCEPTED :
                            $attendee = 'ATTENDEE;ROLE=REQ-PARTICIPANT'
                                . ';PARTSTAT=ACCEPTED';
                            break;
                        case CalendarEvent::PARTSTAT_DELEGATED :
                            $attendee = 'ATTENDEE;ROLE=NON-PARTICIPANT'
                                . ';PARTSTAT=ACCEPTED'
                                . ';DELEGATED-TO="mailto:'
                                . $this->getFacultyEmail($organizer->getId())
                                . '"';
                            break;
                        case CalendarEvent::PARTSTAT_DECLINED :
                            $attendee = 'ATTENDEE;ROLE=REQ-PARTICIPANT'
                                . ';PARTSTAT=DECLINED';
                            break;
                        default :
                            $attendee = 'ATTENDEE;ROLE=REQ-PARTICIPANT';
                            $attendee .= ';PARTSTAT=TENTATIVE';
                            $attendee .= ';RSVP=TRUE';

                    }
                    $attendee .= ';CN="' . $event_member->user->getFullName()
                            . '":mailto:' . $event_member->user->Email;
                    /*
                } else {
                    $attendee .= ';CN="' . _('unbekannt') . '"';
                }
                     *
                     */
                    $properties .= $this->_foldLine($attendee) . $this->newline;
                }
            }
        }
        return $properties;
    }

    public function getFacultyEmail($user_id)
    {
        $stmt = DBManager::get()->prepare('SELECT email FROM Institute i '
                . 'LEFT JOIN user_inst ui USING(institut_id) '
                . 'WHERE i.Institut_id = fakultaets_id AND user_id = ?');
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function _exportCategories($event)
    {
        return implode(',' ,$event->toStringCategories(true));
    }

    /**
     * Return the folded version of a line
     */
    public function _foldLine($line)
    {
        $line = preg_replace('/(\r\n|\n|\r)/', '\n', $line);
        if (mb_strlen($line) > 75) {
            $foldedline = '';
            while (!empty($line)) {
                $maxLine = mb_substr($line, 0, 75);
                $cutPoint = max(60, max(mb_strrpos($maxLine, ';'), mb_strrpos($maxLine, ':')) + 1);

                $foldedline .= ( empty($foldedline)) ?
                        mb_substr($line, 0, $cutPoint) :
                        $this->newline . ' ' . mb_substr($line, 0, $cutPoint);

                $line = (mb_strlen($line) <= $cutPoint) ? '' : mb_substr($line, $cutPoint);
            }
            return $foldedline;
        }
        return $line;
    }
}
