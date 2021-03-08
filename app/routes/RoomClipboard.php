<?php
namespace RESTAPI\Routes;

/**
 * This file contains the REST class for room clipboards
 * (clipboards containing room resources).
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class RoomClipboard extends \RESTAPI\RouteMap
{
    //Room clipboard routes:

    /**
     * Returns the request/booking plan for a room clipboard.
     *
     * @get /room_clipboard/:clipboard_id/booking_plan
     */
    public function getPlan($clipboard_id = null)
    {
        if (!$clipboard_id) {
            $this->notFound('ID of clipboard has not been provided!');
        }

        $clipboard = \Clipboard::find($clipboard_id);
        if (!$clipboard) {
            $this->notFound('Clipboard object not found!');
        }

        $current_user = \User::findCurrent();

        //Permission check:
        if ($clipboard->user_id != $current_user->id) {
            throw new AccessDeniedException();
        }

        $display_requests = \Request::get('display_requests');
        $display_all_requests = \Request::get('display_all_requests');

        $begin_date = \Request::get('start');
        $end_date = \Request::get('end');
        if (!$begin_date || !$end_date) {
            //No time range specified.
            $this->halt(400, 'The parameters "start" and "end" are missing!');
            return;
        }

        //Try the ISO format first: YYYY-MM-DDTHH:MM:SS±ZZ:ZZ
        $begin = \DateTime::createFromFormat(\DateTime::RFC3339, $begin_date);
        $end = \DateTime::createFromFormat(\DateTime::RFC3339, $end_date);

        if (!($begin instanceof \DateTime) || !($end instanceof \DateTime)) {
            $begin = new \DateTime();
            $end = new \DateTime();
            //Assume the local timezone and use the Y-m-d format:
            $date_regex = '/[0-9]{4}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/';
            if (preg_match($date_regex, $begin_date)) {
                //$begin is specified in the date formay YYYY-MM-DD:
                $begin_str = explode('-', $begin_date);
                $begin->setDate(
                    intval($begin_str[0]),
                    intval($begin_str[1]),
                    intval($begin_str[2])
                );
                $begin->setTime(0,0,0);
            } else {
                $begin->setTimestamp($begin_date);
            }
            //Now we do the same for $end_timestamp:
            if (preg_match($date_regex, $end_date)) {
                //$begin is specified in the date formay YYYY-MM-DD:
                $end_str = explode('-', $end_date);
                $end->setDate(
                    intval($end_str[0]),
                    intval($end_str[1]),
                    intval($end_str[2])
                );
                $end->setTime(23,59,59);
            } else {
                $end->setTimestamp($end_date);
            }
        }

        //Check if a clipboard is selected:
        $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        $rooms = [];
        if ($clipboard_id) {
            $clipboard = \Clipboard::find($clipboard_id);
        } elseif ($selected_clipboard_id) {
            $clipboard = \Clipboard::find($selected_clipboard_id);
        } else {
            $this->halt(400, 'No clipboard selected!');
        }
        if ($clipboard) {
            $room_ids = [];
            if ($selected_clipboard_id && $selected_clipboard_item_ids) {
                //The array of current clipboard item IDs is not empty.
                //This means that at least one but not necessarily all
                //clipboard items are selected.
                $room_ids = $clipboard->getSomeRangeIds(
                    'Room',
                    $selected_clipboard_item_ids
                );
            } else {
                //Use all items from the clipboard:
                $room_ids = $clipboard->getAllRangeIds('Room');
            }

            $rooms = \Room::findMany($room_ids);
        } else {
            $this->halt(404, 'Clipboard not found!');
        }

        $booking_types = \Request::getArray('booking_types');

        //Room permission check:
        $plan_objects = [];
        foreach ($rooms as $room) {
            if ($room->bookingPlanVisibleForuser($current_user)) {
                $plan_objects = array_merge(
                    $plan_objects,
                    \ResourceManager::getBookingPlanObjects(
                        $room,
                        [
                            [
                                'begin' => $begin->getTimestamp(),
                                'end' => $end->getTimestamp()
                            ]
                        ],
                        $booking_types,
                        (
                            $display_all_requests
                            ? 'all'
                            : (
                                $display_requests
                                ? true
                                : false
                            )
                        )
                    )
                );
            }
        }

        $data = \Studip\Fullcalendar::createData($plan_objects, $begin, $end);

        return $data;
    }


    /**
     * Returns the semester plan for a room clipboard.
     *
     * @get /room_clipboard/:clipboard_id/semester_plan
     */
    public function getSemeterPlan($clipboard_id = null)
    {
        if (!$clipboard_id) {
            $this->notFound('ID of clipboard has not been provided!');
        }

        $clipboard = \Clipboard::find($clipboard_id);
        if (!$clipboard) {
            $this->notFound('Clipboard object not found!');
        }

        $current_user = \User::findCurrent();

        //Permission check:
        if ($clipboard->user_id != $current_user->id) {
            throw new AccessDeniedException();
        }

        $display_requests = \Request::get('display_requests');
        $display_all_requests = \Request::get('display_all_requests');

        $begin = new \DateTime();
        $end = new \DateTime();

        $semester_id = \Request::get('semester_id');
        $semester = null;

        if ($semester_id) {
            $semester = \Semester::find($semester_id);
            if (!$semester) {
                $this->halt(404, 'Specified semester not found!');
            }
        } else {
            $semester = \Semester::findCurrent();
            if (!$semester) {
                $this->halt(500, 'Current semester not available!');
            }
        }

        if (\Request::get('semester_timerange') == 'vorles') {
            $begin->setTimestamp($semester->vorles_beginn);
            $end->setTimestamp($semester->vorles_ende);
        } else {
            $begin->setTimestamp($semester->beginn);
            $end->setTimestamp($semester->ende);
        }

        //Check if a clipboard is selected:
        $selected_clipboard_id = $_SESSION['selected_clipboard_id'];
        $selected_clipboard_item_ids = $_SESSION['selected_clipboard_items'];

        $rooms = [];
        if ($clipboard_id) {
            $clipboard = \Clipboard::find($clipboard_id);
        } elseif ($selected_clipboard_id) {
            $clipboard = \Clipboard::find($selected_clipboard_id);
        } else {
            $this->halt(400, 'No clipboard selected!');
        }
        if ($clipboard) {
            $room_ids = [];
            if ($selected_clipboard_id && $selected_clipboard_item_ids) {
                //The array of current clipboard item IDs is not empty.
                //This means that at least one but not necessarily all
                //clipboard items are selected.
                $room_ids = $clipboard->getSomeRangeIds(
                    'Room',
                    $selected_clipboard_item_ids
                );
            } else {
                //Use all items from the clipboard:
                $room_ids = $clipboard->getAllRangeIds('Room');
            }

            $rooms = \Room::findMany($room_ids);
        } else {
            $this->halt(404, 'Clipboard not found!');
        }

        //Get parameters:
        $booking_types = \Request::getArray('booking_types');

        //Get the event data sources:
        $plan_objects = [];

        foreach ($rooms as $room) {
            if ($room->bookingPlanVisibleForuser($current_user)) {
                $plan_objects = array_merge(
                    $plan_objects,
                    \ResourceManager::getBookingPlanObjects(
                        $room,
                        [
                            [
                                'begin' => $begin->getTimestamp(),
                                'end' => $end->getTimestamp()
                            ]
                        ],
                        $booking_types,
                        (
                            $display_all_requests
                            ? 'all'
                            : (
                                $display_requests
                                ? true
                                : false
                            )
                        )
                    )
                );
            }
        }

        $merged_objects = [];
        $metadates = [];
        foreach ($plan_objects as $plan_object) {
            if ($plan_object instanceof \ResourceBooking) {
                $irrelevant_booking =
                    $plan_object->getRepetitionType() != 'weekly' ||
                    ($plan_object->getAssignedUserType() === 'course' && in_array($plan_object->assigned_course_date->metadate_id, $metadates));
                if ($irrelevant_booking) {
                    continue;
                }

                //It is a booking with repetitions that has to be included
                //in the semester plan.

                $real_begin = $plan_object->begin;
                if ($plan_object->preparation_time > 0) {
                    $real_begin -= $plan_object->preparation_time;
                }
                $event_data = $plan_object->convertToEventData([\ResourceBookingInterval::build(['interval_id' => md5(uniqid()), 'begin' => $real_begin, 'end' => $plan_object->end])], $current_user);

                //Merge event data from the same booking that have the
                //same weekday and begin and end time into one event.
                //If no repetition interval is set and the booking belongs
                //to a course date, use the corresponding metadate ID or the
                //course date ID in the index. Otherwise use the booking's
                //ID (specified by event_data->object_id).
                foreach ($event_data as $event) {
                    if ($plan_object->getAssignedUserType() === 'course') {
                        $index = sprintf(
                            '%1$s_%2$s_%3$s',
                            $plan_object->assigned_course_date->metadate_id,
                            $event->begin->format('NHis'),
                            $event->end->format('NHis')
                        );
                        $metadates[] = $plan_object->assigned_course_date->metadate_id;
                    } else {
                        $index = sprintf(
                            '%1$s_%2$s_%3$s',
                            $plan_object->id,
                            $event->begin->format('NHis'),
                            $event->end->format('NHis')
                        );
                    }

                    //Strip some data that cannot be used effectively in here:
                    $event->api_urls = [];

                    $merged_objects[$index] = $event;
                }
            } elseif ($plan_object instanceof \ResourceRequest) {
                if ($plan_object->cycle instanceof \SeminarCycleDate) {
                    $cycle_dates = $plan_object->cycle->getAllDates();
                    foreach ($cycle_dates as $cycle_date) {
                        $relevant_request = $semester->beginn <= $cycle_date->date
                                         && $semester->ende >= $cycle_date->date;
                        if ($relevant_request) {
                            //We have found a date for the current semester
                            //that makes the request relevant.
                            break;
                        }
                    }
                    if (!$relevant_request) {
                        continue;
                    }
                    $event_data_list = $plan_object->getFilteredEventData(
                        $current_user->id
                    );

                    foreach ($event_data_list as $event_data) {
                        $index = sprintf(
                            '%1$s_%2$s_%3$s',
                            $plan_object->metadate_id,
                            $event_data->begin->format('NHis'),
                            $event_data->end->format('NHis')
                        );

                        //Strip some data that cannot be used effectively in here:
                        $event_data->view_urls = [];
                        $event_data->api_urls = [];

                        $merged_objects[$index] = $event_data;
                    }
                }
            }
        }

        //Convert the merged events to Fullcalendar events:
        $data = [];
        foreach ($merged_objects as $obj) {
            $data[] = $obj->toFullCalendarEvent();
        }

        return $data;
    }
}
