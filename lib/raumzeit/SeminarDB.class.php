<?php
# Lifter002: DONE
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// SeminarDB.class.php
//
// Datenbank-Abfragen für Seminar.class.php
//
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


/**
 * SeminarDB.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 * @deprecated
 */

class SeminarDB
{
    public static function getIssues($seminar_id)
    {
        $query = "SELECT *
                  FROM themen
                  WHERE themen.seminar_id = ?
                  ORDER BY priority";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSingleDates($seminar_id, $start = 0, $end = 0)
    {
        $query = "SELECT termine.*, resources_assign.resource_id, GROUP_CONCAT(DISTINCT trp.user_id) AS related_persons,  GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                  FROM termine
                  LEFT JOIN termin_related_persons AS trp ON (termine.termin_id = trp.range_id)
                  LEFT JOIN termin_related_groups AS trg ON (termine.termin_id = trg.termin_id)
                  LEFT JOIN resources_assign ON (assign_user_id = termine.termin_id)
                  WHERE termine.range_id = ?
                    AND (metadate_id IS NULL OR metadate_id = '')";
        $parameters = [$seminar_id];

        if ($start != 0 || $end != 0) {
            $query .= " AND termine.date BETWEEN ? AND ?";
            array_push($parameters, $start, $end);
        }

        $query .= " GROUP BY termine.termin_id ORDER BY date";

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        $ret = [];
        while ($data = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($data['related_persons']) {
                $data['related_persons'] = explode(',', $data['related_persons']);
            }
            if ($data['related_groups']) {
                $data['related_groups'] = explode(',', $data['related_groups']);
            }

            $ret[] = $data;
        }

        return $ret;
    }

    public static function getStatOfNotBookedRooms($cycle_id, $seminar_id, $filterStart = 0, $filterEnd = 0)
    {
        $stat = [
            'booked'         => 0,
            'open'           => 0,
            'open_rooms'     => [],
            'declined'       => 0,
            'declined_dates' => [],
        ];

        $query = "SELECT termine.*, resources_assign.resource_id
                  FROM termine
                  LEFT JOIN resources_assign ON (assign_user_id = termin_id)
                  WHERE range_id = ? AND metadate_id = ?";
        $parameters = [$seminar_id, $cycle_id];

        if ($filterStart != 0 || $filterEnd != 0) {
            $query .= " AND date >= ? AND end_time <= ?";
            array_push($parameters, $filterStart, $filterEnd);
        }
        $query .= " ORDER BY date";

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $stat['all'] += 1;
            if ($row['resource_id']) {
                $stat['booked'] += 1;
            } else {
                $stat['open'] += 1;
                $stat['open_rooms'][] = $row;
            }
        }

        // count how many singledates have a declined room-request
        $query = "SELECT *
                  FROM termine t
                  LEFT JOIN resources_requests AS rr ON (t.termin_id = rr.termin_id)
                  WHERE range_id = ? AND t.metadate_id = ? AND closed = 3";
        $parameters = [$seminar_id, $cycle_id];

        if ($filterStart != 0 && $filterEnd != 0) {
            $query .= " AND date >= ? AND end_time <= ?";
            array_push($parameters, $filterStart, $filterEnd);
        }
        $query .= " ORDER BY date";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($parameters);

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stat['declined'] += 1;
            $stat['declined_dates'][] = $data;
        }

        return $stat;
    }

    public static function countRequestsForSingleDates($cycle_id, $seminar_id, $filterStart = 0, $filterEnd = 0)
    {
        $query = "SELECT COUNT(*)
                  FROM termine AS t
                  LEFT JOIN resources_requests AS rr ON (t.termin_id = rr.termin_id)
                  WHERE seminar_id = ? AND t.metadate_id = ? AND closed = 0";
        $parameters = [$seminar_id, $cycle_id];

        if ($filterStart > 0 || $filterEnd > 0) {
            $query .= " AND `date` >= ? AND end_time <= ?";
            array_push($parameters, $filterStart, $filterEnd);
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchColumn();
    }

    public static function hasDatesOutOfDuration($start, $end, $seminar_id)
    {
        $query = "SELECT COUNT(*)
                  FROM termine
                  WHERE range_id = ? AND `date` NOT BETWEEN ? AND ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_id, $start, $end]);
        return $statement->fetchColumn();
    }

    public static function getFirstDate($seminar_id)
    {
        $termine = [];

        $query = "SELECT termin_id, date, end_time
                    FROM termine
                    WHERE range_id = ?
                    ORDER BY date";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_id]);

        $start = 0;
        $end = 0;

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (($start == 0 && $end == 0) || ($start == $row['date'] && $end == $row['end_time'])) {
                $termine[] = $row['termin_id'];
                $start     = $row['date'];
                $end       = $row['end_time'];
            }
        }

        return $termine ?: false;
    }

    public static function getNextDate($seminar_id)
    {
        $termin = [];

        $query = "SELECT termin_id, date, end_time
                  FROM termine
                  WHERE range_id = ? AND date > UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR)
                  ORDER BY date, end_time";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$seminar_id]);

        $start = 0;
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($start == 0 || $start == $data['date']) {
                $termin[] = $data['termin_id'];
                $start = $data['date'];
            }
        }

        $ex_termin = [];

        $query = "SELECT termin_id
                  FROM ex_termine
                  WHERE range_id = ? AND date > UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR)
                    AND content != '' AND content IS NOT NULL
                  ORDER BY date
                  LIMIT 1";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$seminar_id]);

        while ($termin_id = $stmt->fetchColumn()) {
            $ex_termin[] = $termin_id;
        }

        return compact('termin', 'ex_termin');
    }

    /**
     * vergisst die Einträge in resources_requests_properties
     * @deprecated
     * @param unknown_type $id
     * @return boolean
     */
    public static function deleteRequest($id)
    {
        $query = "DELETE FROM resources_requests
                  WHERE seminar_id = ?
                    AND (termin_id = '' OR termin_id IS NULL)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);

        return true;
    }

    public static function getDeletedSingleDates($seminar_id, $start = 0, $end = 0)
    {
        $ret = [];
        if (($start != 0) || ($end != 0)) {
            $query = "SELECT ex_termine.*, GROUP_CONCAT(trp.user_id) AS related_persons, GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                      FROM ex_termine
                        LEFT JOIN termin_related_persons AS trp ON (ex_termine.termin_id = trp.range_id)
                        LEFT JOIN termin_related_groups AS trg ON (ex_termine.termin_id = trg.termin_id)
                      WHERE ex_termine.range_id = ?
                        AND (metadate_id IS NULL OR metadate_id = '')
                      AND `date` BETWEEN ? AND ?
                      GROUP BY ex_termine.termin_id
                      ORDER BY date";
            $parameters = [$seminar_id, $start, $end];
        } else {
            $query = "SELECT ex_termine.*, GROUP_CONCAT(trp.user_id) AS related_persons, GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                      FROM ex_termine
                        LEFT JOIN termin_related_persons AS trp ON (ex_termine.termin_id = trp.range_id)
                        LEFT JOIN termin_related_groups AS trg ON (ex_termine.termin_id = trg.termin_id)
                      WHERE ex_termine.range_id = ?
                        AND (metadate_id IS NULL OR metadate_id = '')
                      GROUP BY ex_termine.termin_id
                      ORDER BY date";
            $parameters = [$seminar_id];
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $zw = $row;
            $zw['ex_termin'] = TRUE;
            $zw['related_persons'] = explode(',', $zw['related_persons']);
            $zw['related_groups'] = explode(',', $zw['related_groups']);
            $ret[] = $zw;
        }
        return $ret;
    }

}
