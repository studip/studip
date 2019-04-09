<?php
namespace RESTAPI\Routes;

use Calendar;
use DbCalendarEventList;
use SingleCalendar;
use SingleDate;
use Seminar;
use Issue;
use CalendarExport;
use CalendarWriterICalendar;
use SemesterData;

/**
 * @author  André Klaßen <andre.klassen@elan-ev.de>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @condition course_id ^[a-f0-9]{32}$
 * @condition user_id ^[a-f0-9]{32}$
 * @condition semester_id ^[a-f0-9]{32}$
 */
class Events extends \RESTAPI\RouteMap
{
    public function before($router, &$handler, &$parameters)
    {
        require_once 'lib/calendar/CalendarExportFile.class.php';
        require_once 'lib/calendar/CalendarWriterICalendar.class.php';
    }

    /**
     * returns all upcoming events within the next two weeks for a given user
     *
     * @get /user/:user_id/events
     */
    public function getEvents($user_id)
    {
        if ($user_id !== $GLOBALS['user']->id) {
            $this->error(401);
        }

        $start = time();
        $end   = strtotime('+2 weeks', $start);
        $list = SingleCalendar::getEventList($user_id, $start, $end, null, [], [
            'CourseEvent',
            'CourseCancelledEvent',
            'CourseMarkedEvent',
        ]);

        $json = [];
        $events = array_slice($list, $this->offset, $this->limit); ;
        foreach ($events as $event) {
            $singledate = new SingleDate($event->id);

            $course_uri = $this->urlf('/course/%s', [htmlReady($event->getSeminarId())]);

            $json[] = [
                'event_id'    => $event->id,
                'course'      => $course_uri,
                'start'       => $event->getStart(),
                'end'         => $event->getEnd(),
                'title'       => $event->getTitle(),
                'description' => $event->getDescription() ?: '',
                'categories'  => $event->toStringCategories() ?: '',
                'room'        => html_entity_decode(strip_tags($singledate->getRoom() ?: $singledate->getFreeRoomText() ?: '')),
                'canceled'    => $singledate->isHoliday() ?: false,
            ];
        }

        $this->etag(md5(serialize($json)));

        return $this->paginated($json, count($list), compact('user_id'));
    }

    /**
     *  returns an iCAL Export of all events for a given user
     *
     * @get /user/:user_id/events.ics
     */
    public function getEventsICAL($user_id)
    {
        if ($user_id !== $GLOBALS['user']->id) {
            $this->error(401);
        }

        $export = new CalendarExport(new CalendarWriterICalendar());
        $export->exportFromDatabase($user_id, 0, 2114377200, 'ALL_EVENTS');

        if ($GLOBALS['_calendar_error']->getMaxStatus(\ErrorHandler::ERROR_CRITICAL)) {
            $this->halt(500);
        }

        $content = implode($export->getExport());

        $this->contentType('text/calendar');
        $this->headers([
            'Content-Length'      => strlen($content),
            'Content-Disposition' => 'attachment; ' . encode_header_parameter('filename', 'studip.ics'),
        ]);
        $this->halt(200, $this->response->headers, function () use ($content) {
            echo $content;
        });
    }


    /**
     * returns events for a given course
     *
     * @get /course/:course_id/events
     */
    public function getEventsForCourse($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('user', $course_id, $GLOBALS['user']->id)) {
            $this->error(401);
        }

        $seminar = new Seminar($course_id);
        $dates = getAllSortedSingleDates($seminar);
        $total = sizeof($dates);

        $events = [];
        foreach (array_slice($dates, $this->offset, $this->limit) as $date) {

            // get issue titles
            $issue_titles = [];
            if (is_array($issues = $date->getIssueIDs())) {
                foreach ($issues as $is) {
                    $issue = new Issue(['issue_id' => $is]);
                    $issue_titles[] = $issue->getTitle();
                }
            }

            $room = self::getRoomForSingleDate($date);
            $events[] = [
                'event_id'    => $date->getSingleDateID(),
                'start'       => $date->getStartTime(),
                'end'         => $date->getEndTime(),
                'title'       => $date->toString(),
                'description' => implode(', ', $issue_titles),
                'categories'  => $date->getTypeName() ?: '',
                'room'        => $room ?: '',
                'deleted'     => $date->isExTermin(),
                'canceled'    => $date->isHoliday() ?: false,
            ];
        }

        $this->etag(md5(serialize($events)));

        return $this->paginated($events, $total, compact('course_id'));
    }

    private static function getRoomForSingleDate($val) {

        /* css-Klasse auswählen, sowie Template-Feld für den Raum mit Text füllen */
        if (\Config::get()->RESOURCES_ENABLE) {

            if ($val->getResourceID()) {
                $resObj = \ResourceObject::Factory($val->getResourceID());
                $room = _("Raum: ");
                $room .= $resObj->getFormattedLink(TRUE, TRUE, TRUE, 'view_schedule', 'no_nav', $val->getStartTime());
            }

            else {

                if (\Config::get()->RESOURCES_SHOW_ROOM_NOT_BOOKED_HINT) {
                    $room = '('._("kein gebuchter Raum").')';
                } else {
                    $room = _("keine Raumangabe");
                }

                if ($val->isExTermin()) {
                    if ($name = $val->isHoliday()) {
                        $room = '('.$name.')';
                    } else {
                        $room = '('._('fällt aus').')';
                    }
                }

                else {
                    if ($val->getFreeRoomText()) {
                        $room = '('.htmlReady($val->getFreeRoomText()).')';
                    }
                }
            }
        } else {
            $room = '';
            if ($val->getFreeRoomText()) {
                $room = '('.htmlReady($val->getFreeRoomText()).')';
            }
        }

        return html_entity_decode(strip_tags($room));
    }

}
