<?php

/**
 * request.php - contains Resources_StatisticsController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2018-2019
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_StatisticsController contains actions to display statistics
 * related to the room and resource management system.
 */
class Resources_StatisticsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Navigation::hasItem('/room_management/statistics')) {
            Navigation::activateItem('/room_management/statistics');
        }
    }

    public function room_load_action()
    {
        if (Navigation::hasItem('/room_management/statistics/room_load')) {
            Navigation::activateItem('/room_management/statistics/room_load');
        }

        $year_begin = new DateTime();
        $year_begin->setDate(intval(date('Y')), 1, 1);
        $year_begin->setTime(0,0,0);
        $year_end = clone $year_begin;
        $year_end = $year_end->add(
            new DateInterval('P1Y')
        )->sub(
            new DateInterval('PT1S')
        );
        $db = DBManager::get();

        $sum_stmt = $db->prepare(
            "SELECT SUM(rbi.end - rbi.begin) FROM resource_booking_intervals rbi
            INNER JOIN resource_bookings rb
            ON rbi.booking_id = rb.id
            WHERE
            rb.booking_type IN ( :types )
            AND
            (rbi.begin BETWEEN :begin AND :end)
            OR
            (rbi.end BETWEEN :begin AND :end);"
        );

        $sum_stmt->execute(
            [
                'types' => [0, 1],
                'begin' => $year_begin->getTimestamp(),
                'end' => $year_end->getTimestamp()
            ]
        );
        $total_booked_time = $sum_stmt->fetchColumn();

        $sum_stmt->execute(
            [
                'types' => [2],
                'begin' => $year_begin->getTimestamp(),
                'end' => $year_end->getTimestamp()
            ]
        );
        $total_locked_time = $sum_stmt->fetchColumn();

        $total_seconds = intval($year_end->getTimestamp()) - intval($year_begin->getTimestamp());

        if ($total_seconds > 0) {
            $this->booked_share = $total_booked_time / $total_seconds;
            $this->locked_share = $total_locked_time / $total_seconds;

            $this->unused_share = 1 - $this->booked_share - $this->locked_share;
        }
    }
}
