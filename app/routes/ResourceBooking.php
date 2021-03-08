<?php
namespace RESTAPI\Routes;

/**
 * This file contains the REST class for the
 * room and resource management system.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class ResourceBooking extends \RESTAPI\RouteMap
{

    /**
     * Helper method that either returns the specified data
     * or simply an empty string in case that no request result
     * is requested.
     */
    protected function sendReturnData($data)
    {
        if (\Request::submitted('quiet')) {
            //Return nothing.
            return '';
        }

        //Return data.
        return $data;
    }


    /**
     * Moves a resource booking, if permitted.
     *
     * @post /resources/booking/:booking_id/move
     */
    public function move($booking_id)
    {
        $booking = \ResourceBooking::find($booking_id);
        if (!$booking) {
            $this->notFound('Resource booking object not found!');
        }

        $current_user = \User::findCurrent();

        if ($booking->isReadOnlyForUser($current_user)) {
            throw new \AccessDeniedException();
        }

        $resource_id = \Request::get('resource_id');
        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $interval_id = \Request::get('interval_id');

        //Try the ISO format first: YYYY-MM-DDTHH:MM:SS±ZZ:ZZ
        $begin = \DateTime::createFromFormat(\DateTime::RFC3339, $begin_str);
        $end = \DateTime::createFromFormat(\DateTime::RFC3339, $end_str);
        if (!($begin instanceof \DateTime) || !($end instanceof \DateTime)) {
            $tz = new \DateTime();
            $tz = $tz->getTimezone();
            //Try the ISO format without timezone:
            $begin = \DateTime::createFromFormat('Y-m-d\TH:i:s', $begin_str, $tz);
            $end = \DateTime::createFromFormat('Y-m-d\TH:i:s', $end_str, $tz);
        }

        //Check if a specific interval has been moved:
        if ($interval_id) {
            $interval = \ResourceBookingInterval::findOneBySql(
                'interval_id = :interval_id AND booking_id = :booking_id',
                [
                    'interval_id' => $interval_id,
                    'booking_id' => $booking->id
                ]
            );
            if (!$interval) {
                $this->notFound('Resource booking interval not found!');
            }
            $interval_begin = new \DateTime();
            $interval_begin->setTimestamp($interval->begin);
            $interval_end = new \DateTime();
            $interval_end->setTimestamp($interval->end);

            //Calculate the difference from the interval time range
            //to the time range from the request. That difference
            //is then applied to the booking.
            $begin_diff = $interval_begin->diff($begin);
            $end_diff = $interval_end->diff($end);

            $new_booking_begin = new \DateTime();
            $new_booking_begin->setTimestamp($booking->begin);
            $new_booking_end = new \DateTime();
            $new_booking_end->setTimestamp($booking->end);

            $new_booking_begin = $new_booking_begin->add($begin_diff);
            $new_booking_end = $new_booking_end->add($end_diff);
            //We must substract the preparation time to the begin timestamp
            //to get the real begin:
            $real_begin = clone $new_booking_begin;
            if ($booking->preparation_time > 0) {
                $real_begin->sub(new \DateInterval('PT' . ($booking->preparation_time / 60 ) . 'M'));
            }
            $booking->begin = $real_begin->getTimestamp();
            $booking->end = $new_booking_end->getTimestamp();
        } else {
            //We must substract the preparation time to the begin timestamp
            //to get the real begin:
            $real_begin = clone $begin;
            if ($booking->preparation_time > 0) {
                $real_begin->sub(new \DateInterval('PT' . ($booking->preparation_time / 60 ) . 'M'));
            }
            $booking->begin = $real_begin->getTimestamp();
            $booking->end = $end->getTimestamp();
        }
        if ($resource_id) {
            //The resource-ID has changed:
            //The booking was moved from one resource to another.
            $booking->resource_id = $resource_id;
        }

        //Update the booking_user_id field:
        $booking->booking_user_id = \User::findCurrent()->id;

        try {
            $booking->store();
            return $this->sendReturnData($booking->toRawArray());
        } catch (Exception $e) {
            $this->halt(500, $e->getMessage());
        }
    }


    /**
     * Retrieves the intervals of the resource booking.
     * These can be filtered by a time range.
     *
     * @get /resources/booking/:booking_id/intervals
     */
    public function getIntervals($booking_id)
    {
        $booking = \ResourceBooking::find($booking_id);
        if (!$booking) {
            $this->notFound('Resource booking object not found!');
        }

        $current_user = \User::findCurrent();

        $resource = $booking->resource->getDerivedClassInstance();
        if (!$resource->bookingPlanVisibleForUser($current_user)) {
            throw new \AccessDeniedException();
        }

        //Get begin and end:
        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');
        $begin = null;
        $end = null;
        if ($begin_str && $end_str) {
            //Try the ISO format first: YYYY-MM-DDTHH:MM:SS±ZZ:ZZ
            $begin = \DateTime::createFromFormat(\DateTime::RFC3339, $begin_str);
            $end = \DateTime::createFromFormat(\DateTime::RFC3339, $end_str);
            if (!($begin instanceof \DateTime) || !($end instanceof \DateTime)) {
                $tz = new \DateTime();
                $tz = $tz->getTimezone();
                //Try the ISO format without timezone:
                $begin = \DateTime::createFromFormat('Y-m-d\TH:i:s', $begin_str, $tz);
                $end = \DateTime::createFromFormat('Y-m-d\TH:i:s', $end_str, $tz);
            }
        }

        $sql = "booking_id = :booking_id ";
        $sql_data = ['booking_id' => $booking->id];
        if (($begin instanceof \DateTime) && ($end instanceof \DateTime)) {
            $sql .= "AND begin >= :begin AND end <= :end ";
            $sql_data['begin'] = $begin->getTimestamp();
            $sql_data['end'] = $end->getTimestamp();
        }
        if (\Request::submitted('exclude_cancelled_intervals')) {
            $sql .= "AND takes_place = '1' ";
        }
        $sql .= "ORDER BY begin ASC, end ASC";
        $intervals = \ResourceBookingInterval::findBySql($sql, $sql_data);

        $result = [];
        foreach ($intervals as $interval) {
            $result[] = $interval->toRawArray();
        }

        return $result;
    }
}
