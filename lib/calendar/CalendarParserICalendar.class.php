<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarParserICalendar.class.php
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

class CalendarParserICalendar extends CalendarParser
{
    public $type = '';
    protected $count = null;

    public function __construct()
    {
        parent::__construct();
        $this->type = 'iCalendar';
        // initialize error handler
        $GLOBALS['_calendar_error'] = new ErrorHandler();
    }

    public function getCount($data)
    {
        $matches = [];
        if (is_null($this->count)) {
            // Unfold any folded lines
            $data = preg_replace('/\x0D?\x0A[\x20\x09]/', '', $data);
            preg_match_all('/(BEGIN:VEVENT(\r\n|\r|\n)[\W\w]*?END:VEVENT\r?\n?)/', $data, $matches);
            $this->count = sizeof($matches[1]);
        }

        return $this->count;
    }

    /**
     * Parse a string containing vCalendar data.
     *
     * @access private
     * @param String $data  The data to parse
     *
     */
    public function parse($data, $ignore = null)
    {
        global $_calendar_error, $PERS_TERMIN_KAT;

        // match categories
        $studip_categories = [];
        $i = 1;
        foreach ($PERS_TERMIN_KAT as $cat) {
            $studip_categories[mb_strtolower($cat['name'])] = $i++;
        }

        // Unfold any folded lines
        // the CR is optional for files imported from Korganizer (non-standard)
        $data = preg_replace('/\x0D?\x0A[\x20\x09]/', '', $data);

        if (!preg_match('/BEGIN:VCALENDAR(\r\n|\r|\n)([\W\w]*)END:VCALENDAR\r?\n?/', $data, $matches)) {
            $_calendar_error->throwError(ErrorHandler::ERROR_CRITICAL, _("Die Import-Datei ist keine gültige iCalendar-Datei!"));
            return false;
        }

        // client identifier
        if (!$this->_parseClientIdentifier($matches[2])) {
            return false;
        }

        // All sub components
        if (!preg_match_all('/BEGIN:VEVENT(\r\n|\r|\n)([\w\W]*?)END:VEVENT(\r\n|\r|\n)/', $matches[2], $v_events)) {
            $_calendar_error->throwError(ErrorHandler::ERROR_MESSAGE, _("Die importierte Datei enthält keine Termine."));
            return true;
        }

        if ($this->count) {
            $this->count = 0;
        }
        foreach ($v_events[2] as $v_event) {
            $properties['CLASS'] = 'PRIVATE';
            // Parse the remain attributes

            if (preg_match_all('/(.*):(.*)(\r|\n)+/', $v_event, $matches)) {
                $properties = [];
                $check = [];
                foreach ($matches[0] as $property) {
                    preg_match('/([^;^:]*)((;[^:]*)?):(.*)/', $property, $parts);
                    $tag = $parts[1];
                    $value = $parts[4];
                    $params = [];

                    // skip seminar events
                    if ((!$this->import_sem) && $tag == 'UID') {
                        if (mb_strpos($value, 'Stud.IP-SEM') === 0) {
                            continue 2;
                        }
                    }

                    if (!empty($parts[2])) {
                        preg_match_all('/;(([^;=]*)(=([^;]*))?)/', $parts[2], $param_parts);
                        foreach ($param_parts[2] as $key => $param_name)
                            $params[mb_strtoupper($param_name)] = mb_strtoupper($param_parts[4][$key]);

                        if ($params['ENCODING']) {
                            switch ($params['ENCODING']) {
                                case 'QUOTED-PRINTABLE':
                                    $value = $this->_qp_decode($value);
                                    break;

                                case 'BASE64':
                                    $value = base64_decode($value);
                                    break;
                            }
                        }
                    }

                    switch ($tag) {
                        // text fields
                        case 'DESCRIPTION':
                        case 'SUMMARY':
                        case 'LOCATION':
                            $value = preg_replace('/\\\\,/', ',', $value);
                            $value = preg_replace('/\\\\n/', "\n", $value);
                            $properties[$tag] = trim($value);
                            break;

                        case 'CATEGORIES':
                            $categories = [];
                            $properties['STUDIP_CATEGORY'] = null;
                            foreach (explode(',', $value) as $category) {
                                if (!$studip_categories[mb_strtolower($category)]) {
                                    $categories[] = $category;
                                } else if (!$properties['STUDIP_CATEGORY']) {
                                    $properties['STUDIP_CATEGORY']
                                            = $studip_categories[mb_strtolower($category)];
                                }
                            }
                            $properties[$tag] = implode(',', $categories);
                            break;

                        // Date fields
                        case 'DCREATED': // vCalendar property name for "CREATED"
                            $tag = "CREATED";
                        case 'DTSTAMP':
                        case 'COMPLETED':
                        case 'CREATED':
                        case 'LAST-MODIFIED':
                            $properties[$tag] = $this->_parseDateTime($value);
                            break;

                        case 'DTSTART':
                        case 'DTEND':
                            // checking for day events
                            if ($params['VALUE'] == 'DATE')
                                $check['DAY_EVENT'] = true;
                        case 'DUE':
                        case 'RECURRENCE-ID':
                            $properties[$tag] = $this->_parseDateTime($value);
                            break;

                        case 'RDATE':
                            if (array_key_exists('VALUE', $params)) {
                                if ($params['VALUE'] == 'PERIOD') {
                                    $properties[$tag] = $this->_parsePeriod($value);
                                } else {
                                    $properties[$tag] = $this->_parseDateTime($value);
                                }
                            } else {
                                $properties[$tag] = $this->_parseDateTime($value);
                            }
                            break;

                        case 'TRIGGER':
                            if (array_key_exists('VALUE', $params)) {
                                if ($params['VALUE'] == 'DATE-TIME') {
                                    $properties[$tag] = $this->_parseDateTime($value);
                                } else {
                                    $properties[$tag] = $this->_parseDuration($value);
                                }
                            } else {
                                $properties[$tag] = $this->_parseDuration($value);
                            }
                            break;

                        case 'EXDATE':
                            $properties[$tag] = [];
                            // comma seperated dates
                            $values = [];
                            $dates = [];
                            preg_match_all('/,([^,]*)/', ',' . $value, $values);
                            foreach ($values[1] as $value) {
                                if (array_key_exists('VALUE', $params)) {
                                    if ($params['VALUE'] == 'DATE-TIME') {
                                        $dates[] = $this->_parseDateTime($value);
                                    } else if ($params['VALUE'] == 'DATE') {
                                        $dates[] = $this->_parseDate($value);
                                    }
                                } else {
                                    $dates[] = $this->_parseDateTime($value);
                                }
                            }
                            // some iCalendar exports (e.g. KOrganizer) use an EXDATE-entry for every
                            // exception, so we have to merge them
                            array_merge($properties[$tag], $dates);
                            break;

                        // Duration fields
                        case 'DURATION':
                            $attibutes[$tag] = $this->_parseDuration($value);
                            break;

                        // Period of time fields
                        case 'FREEBUSY':
                            $values = [];
                            $periods = [];
                            preg_match_all('/,([^,]*)/', ',' . $value, $values);
                            foreach ($values[1] as $value) {
                                $periods[] = $this->_parsePeriod($value);
                            }

                            $properties[$tag] = $periods;
                            break;

                        // UTC offset fields
                        case 'TZOFFSETFROM':
                        case 'TZOFFSETTO':
                            $properties[$tag] = $this->_parseUtcOffset($value);
                            break;

                        case 'PRIORITY':
                            $properties[$tag] = $this->_parsePriority($value);
                            break;

                        case 'CLASS':
                            switch (trim($value)) {
                                case 'PUBLIC':
                                    $properties[$tag] = 'PUBLIC';
                                    break;
                                case 'CONFIDENTIAL':
                                    $properties[$tag] = 'CONFIDENTIAL';
                                    break;
                                default:
                                    $properties[$tag] = 'PRIVATE';
                            }
                            break;

                        // Integer fields
                        case 'PERCENT-COMPLETE':
                        case 'REPEAT':
                        case 'SEQUENCE':
                            $properties[$tag] = intval($value);
                            break;

                        // Geo fields
                        case 'GEO':
                            $floats = explode(';', $value);
                            $value['latitude'] = floatval($floats[0]);
                            $value['longitude'] = floatval($floats[1]);
                            $properties[$tag] = $value;
                            break;

                        // Recursion fields
                        case 'EXRULE':
                        case 'RRULE':
                            $properties[$tag] = $this->_parseRecurrence($value);
                            break;

                        default:
                            // string fields
                            $properties[$tag] = trim($value);
                            break;
                    }
                }

                if (!$properties['RRULE']['rtype'])
                    $properties['RRULE'] = ['rtype' => 'SINGLE'];

                if (!$properties['LAST-MODIFIED'])
                    $properties['LAST-MODIFIED'] = $properties['CREATED'];

                if (!$properties['DTSTART'] || ($properties['EXDATE'] && !$properties['RRULE'])) {
                    $_calendar_error->throwError(ErrorHandler::ERROR_CRITICAL, _("Die Datei ist keine gültige iCalendar-Datei!"));
                    $this->count = 0;
                    return false;
                }

                if (!$properties['DTEND'])
                    $properties['DTEND'] = $properties['DTSTART'];

                // day events starts at 00:00:00 and ends at 23:59:59
                if ($check['DAY_EVENT'])
                    $properties['DTEND']--;

                // default: all imported events are set to private
                if (!$properties['CLASS']
                        || ($this->public_to_private && $properties['CLASS'] == 'PUBLIC')) {
                    $properties['CLASS'] = 'PRIVATE';
                }

                /*
                if (isset($studip_categories[$properties['CATEGORIES']])) {
                    $properties['STUDIP_CATEGORY'] = $studip_categories[$properties['CATEGORIES']];
                    $properties['CATEGORIES'] = '';
                }
                 *
                 */

                $this->components[] = $properties;
            } else {
                $_calendar_error->throwError(ErrorHandler::ERROR_CRITICAL, _("Die Datei ist keine gültige iCalendar-Datei!"));
                $this->count = 0;
                return false;
            }
            $this->count++;
        }

        return true;
    }

    /**
     * Parse a UTC Offset field
     */
    private function _parseUtcOffset($text)
    {
        $offset = 0;
        if (preg_match('/(\+|-)([0-9]{2})([0-9]{2})([0-9]{2})?/', $text, $matches)) {
            $offset += 3600 * intval($matches[2]);
            $offset += 60 * intval($matches[3]);
            $offset *= ( $matches[1] == '+' ? 1 : -1);
            if (array_key_exists(4, $matches)) {
                $offset += intval($matches[4]);
            }
            return $offset;
        } else {
            return false;
        }
    }

    /**
     * Parse a Time Period field
     */
    private function _parsePeriod($text)
    {
        $matches = explode('/', $text);

        $start = $this->_parseDateTime($matches[0]);

        if ($duration = $this->_parseDuration($matches[1])) {
            return ['start' => $start, 'duration' => $duration];
        } else if ($end = $this->_parseDateTime($matches[1])) {
            return ['start' => $start, 'end' => $end];
        }
    }

    /**
     * Parse a DateTime field
     */
    private function _parseDateTime($text)
    {
        $dateParts = explode('T', $text);
        if (count($dateParts) != 2 && !empty($text)) {
            // not a date time field but may be just a date field
            if (!$date = $this->_parseDate($text)) {
                return $date;
            }
            $date = $this->_parseDate($text);
            return mktime(0, 0, 0, $date['month'], $date['mday'], $date['year']);
        }

        if (!$date = $this->_parseDate($dateParts[0])) {
            return $date;
        }
        if (!$time = $this->_parseTime($dateParts[1])) {
            return $time;
        }

        if ($time['zone'] == 'UTC') {
            return gmmktime($time['hour'], $time['minute'], $time['second'], $date['month'], $date['mday'], $date['year']);
        } else {
            return mktime($time['hour'], $time['minute'], $time['second'], $date['month'], $date['mday'], $date['year']);
        }
    }

    /**
     * Parse a Time field
     */
    private function _parseTime($text)
    {
        if (preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})(Z)?/', $text, $matches)) {
            $time['hour'] = intval($matches[1]);
            $time['minute'] = intval($matches[2]);
            $time['second'] = intval($matches[3]);
            if (array_key_exists(4, $matches)) {
                $time['zone'] = 'UTC';
            } else {
                $time['zone'] = 'LOCAL';
            }
            return $time;
        } else {
            return false;
        }
    }

    /**
     * Parse a Date field
     */
    private function _parseDate($text)
    {
        if (mb_strlen(trim($text)) !== 8) {
            return false;
        }

        $date['year'] = intval(mb_substr($text, 0, 4));
        $date['month'] = intval(mb_substr($text, 4, 2));
        $date['mday'] = intval(mb_substr($text, 6, 2));

        return $date;
    }

    /**
     * Parse a Duration Value field
     */
    private function _parseDuration($text)
    {
        if (preg_match('/([+]?|[-])P(([0-9]+W)|([0-9]+D)|)(T(([0-9]+H)|([0-9]+M)|([0-9]+S))+)?/', trim($text), $matches)) {
            // weeks
            $duration = 7 * 86400 * intval($matches[3]);
            if (count($matches) > 4) {
                // days
                $duration += 86400 * intval($matches[4]);
            }
            if (count($matches) > 5) {
                // hours
                $duration += 3600 * intval($matches[7]);
                // mins
                if (array_key_exists(8, $matches)) {
                    $duration += 60 * intval($matches[8]);
                }
                // secs
                if (array_key_exists(9, $matches)) {
                    $duration += intval($matches[9]);
                }
            }
            // sign
            if ($matches[1] == "-") {
                $duration *= - 1;
            }

            return $duration;
        } else {
            return false;
        }
    }

    private function _parsePriority($value)
    {
        $value = intval($value);
        if ($value > 0 && $value < 5) {
            return 1;
        }

        if ($value == 5) {
            return 2;
        }

        if ($value > 5 && $value < 10) {
            return 3;
        }

        return 0;
    }

    /**
     * Parse a Recurrence field
     */
    private function _parseRecurrence($text)
    {
        global $_calendar_error;

        if (preg_match_all('/([A-Za-z]*?)=([^;]*);?/', $text, $matches, PREG_SET_ORDER)) {
            $r_rule = [];

            foreach ($matches as $match) {
                switch ($match[1]) {
                    case 'FREQ' :
                        switch (trim($match[2])) {
                            case 'DAILY' :
                            case 'WEEKLY' :
                            case 'MONTHLY' :
                            case 'YEARLY' :
                                $r_rule['rtype'] = trim($match[2]);
                                break;
                            default:
                                $_calendar_error->throwSingleError('parse', ErrorHandler::ERROR_WARNING, _("Der Import enthält Kalenderdaten, die Stud.IP nicht korrekt darstellen kann."));
                                break;
                        }
                        break;

                    case 'UNTIL' :
                        $r_rule['expire'] = $this->_parseDateTime($match[2]);
                        break;

                    case 'COUNT' :
                        $r_rule['count'] = intval($match[2]);
                        break;

                    case 'INTERVAL' :
                        $r_rule['linterval'] = intval($match[2]);
                        break;

                    case 'BYSECOND' :
                    case 'BYMINUTE' :
                    case 'BYHOUR' :
                    case 'BYWEEKNO' :
                    case 'BYYEARDAY' :
                        $_calendar_error->throwSingleError('parse', ErrorHandler::ERROR_WARNING, _("Der Import enthält Kalenderdaten, die Stud.IP nicht korrekt darstellen kann."));
                        break;

                    case 'BYDAY' :
                        $byday = $this->_parseByDay($match[2]);
                        $r_rule['wdays'] = $byday['wdays'];
                        if ($byday['sinterval'])
                            $r_rule['sinterval'] = $byday['sinterval'];
                        break;

                    case 'BYMONTH' :
                        $r_rule['month'] = $this->_parseByMonth($match[2]);
                        break;

                    case 'BYMONTHDAY' :
                        $r_rule['day'] = $this->_parseByMonthDay($match[2]);
                        break;

                    case 'BYSETPOS':
                        $r_rule['sinterval'] = intval($match[2]);
                        break;

                    case 'WKST' :
                        break;
                }
            }
        }

        return $r_rule;
    }

    private function _parseByDay($text)
    {
        global $_calendar_error;

        preg_match_all('/(-?\d{1,2})?(MO|TU|WE|TH|FR|SA|SU),?/', $text, $matches, PREG_SET_ORDER);
        $wdays_map = ['MO' => '1', 'TU' => '2', 'WE' => '3', 'TH' => '4', 'FR' => '5',
            'SA' => '6', 'SU' => '7'];
        $wdays = "";
        $sinterval = null;
        foreach ($matches as $match) {
            $wdays .= $wdays_map[$match[2]];
            if ($match[1]) {
                if (!$sinterval && ((int) $match[1]) > 0 || $match[1] == '-1') {
                    if ($match[1] == '-1')
                        $sinterval = '5';
                    else
                        $sinterval = $match[1];
                } else {
                    $_calendar_error->throwSingleError('parse', ErrorHandler::ERROR_WARNING, _("Der Import enthält Kalenderdaten, die Stud.IP nicht korrekt darstellen kann."));
                }
            }
        }

        return $wdays ? ['wdays' => $wdays, 'sinterval' => $sinterval] : false;
    }

    private function _parseByMonthDay($text)
    {
        $days = explode(',', $text);
        if (sizeof($days) > 1 || ((int) $days[0]) < 0) {
            return false;
        }

        return $days[0];
    }

    private function _parseByMonth($text)
    {
        $months = explode(',', $text);
        if (sizeof($months) > 1) {
            return false;
        }

        return $months[0];
    }

    private function _qp_decode($value)
    {
        return preg_replace_callback("/=([0-9A-F]{2})/", function ($m) {return chr(hexdec($m[1]));}, $value);
    }

    private function _parseClientIdentifier(&$data)
    {
        global $_calendar_error;

        if ($this->client_identifier == '') {
            if (!preg_match('/PRODID((;[\W\w]*)*):([\W\w]+?)(\r\n|\r|\n)/', $data, $matches)) {
                $_calendar_error->throwError(ErrorHandler::ERROR_CRITICAL, _("Die Datei ist keine gültige iCalendar-Datei!"));
                return false;
            } elseif (!trim($matches[3])) {
                $_calendar_error->throwError(ErrorHandler::ERROR_CRITICAL, _("Die Datei ist keine gültige iCalendar-Datei!"));
                return false;
            } else {
                $this->client_identifier = trim($matches[3]);
            }
        }
        return true;
    }

    public function getClientIdentifier($data = null)
    {
        if (!is_null($data)) {
            $this->_parseClientIdentifier($data);
        }

        return $this->client_identifier;
    }

}
