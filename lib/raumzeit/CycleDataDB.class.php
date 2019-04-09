<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// CycleDataDB.class.php
//
// Datenbank-Abfragen für CycleData.class.php
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
 * CycleDataDB.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */
class CycleDataDB
{
    /**
     * Returns sorted array of all dates belonging to the passed metadate,
     * optionally filtered by start- and end-date
     *
     * @param  string  $metadate_id
     * @param  integer $start
     * @param  integer $end
     *
     * @return array
     */
    public static function getTermine($metadate_id, $start = 0, $end = 0)
    {
        if (($start != 0) || ($end != 0)) {
            $query = "SELECT termine.*, r.resource_id, GROUP_CONCAT(DISTINCT trp.user_id) AS related_persons, GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                      FROM termine
                      LEFT JOIN termin_related_persons AS trp ON (termine.termin_id = trp.range_id)
                      LEFT JOIN termin_related_groups AS trg ON (termine.termin_id = trg.termin_id)
                      LEFT JOIN resources_assign AS r ON (termine.termin_id = assign_user_id)
                      WHERE metadate_id = ? AND termine.date BETWEEN ? AND ?
                      GROUP BY termine.termin_id
                      ORDER BY NULL";
            $parameters = [$metadate_id, $start, $end];
        } else {
            $query = "SELECT termine.*, r.resource_id, GROUP_CONCAT(DISTINCT trp.user_id) AS related_persons, GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                      FROM termine
                        LEFT JOIN termin_related_persons AS trp ON (termine.termin_id = trp.range_id)
                        LEFT JOIN termin_related_groups AS trg ON (termine.termin_id = trg.termin_id)
                        LEFT JOIN resources_assign AS r ON (termine.termin_id = assign_user_id)
                      WHERE metadate_id = ?
                      GROUP BY termine.termin_id
                      ORDER BY NULL";
            $parameters = [$metadate_id];
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        $ret = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $data = $row;
            $data['related_persons'] = array_filter(explode(',', $data['related_persons']));
            $data['related_groups'] = array_filter(explode(',', $data['related_groups']));
            $ret[] = $data;
        }

        if (($start != 0) || ($end != 0)) {
            $query = "SELECT ex_termine.*, GROUP_CONCAT(DISTINCT trp.user_id) AS related_persons, GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                      FROM ex_termine
                        LEFT JOIN termin_related_persons AS trp ON (ex_termine.termin_id = trp.range_id)
                        LEFT JOIN termin_related_groups AS trg ON (ex_termine.termin_id = trg.termin_id)
                      WHERE metadate_id = ? AND `date` BETWEEN ? AND ?
                      GROUP BY ex_termine.termin_id
                      ORDER BY NULL";
            $parameters = [$metadate_id, $start, $end];
        } else {
            $query = "SELECT ex_termine.*, GROUP_CONCAT(DISTINCT trp.user_id) AS related_persons, GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                      FROM ex_termine
                        LEFT JOIN termin_related_persons AS trp ON (ex_termine.termin_id = trp.range_id)
                        LEFT JOIN termin_related_groups AS trg ON (ex_termine.termin_id = trg.termin_id)
                      WHERE metadate_id = ?
                      GROUP BY ex_termine.termin_id
                      ORDER BY NULL";
            $parameters = [$metadate_id];
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $zw = $row;
            $zw['ex_termin'] = TRUE;
            $zw['related_persons'] = array_filter(explode(',', $zw['related_persons']));
            $zw['related_groups'] = array_filter(explode(',', $zw['related_groups']));
            $ret[] = $zw;
        }

        if ($ret) {
            usort($ret, 'CycleDataDB::sort_dates');
            return $ret;
        }

        return FALSE;
    }

    public static function sort_dates($a, $b)
    {
        if ($a['date'] == $b['date']) return 0;
        return ($a['date'] < $b['date']) ? -1 : 1;
    }

    /**
     * Deletes all dates that are newer then the passed date for metadate
     * with the passed id
     *
     * @param  string  $metadate_id
     * @param  int  $timestamp
     * @param  boolean $keepIssues
     *
     * @return int  number of deleted singledates
     */
    public static function deleteNewerSingleDates($metadate_id, $timestamp, $keepIssues = false)
    {
        $count = 0;

        $query = "SELECT termin_id
                  FROM termine
                  WHERE metadate_id = ? AND `date` > ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$metadate_id, $timestamp]);
        while ($termin_id = $statement->fetchColumn()) {
            $termin = new SingleDate($termin_id);
            $termin->delete($keepIssues);
            unset($termin);

            $count += 1;
        }

        $query = "DELETE FROM termine WHERE metadate_id = ? AND `date` > ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$metadate_id, $timestamp]);

        $query = "DELETE FROM ex_termine WHERE metadate_id = ? AND `date` > ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$metadate_id, $timestamp]);

        return $count;
    }

    /**
     * Returns the list of booked rooms ordered by number of appearance
     * in the metadate with the passed id
     *
     * @param  string  $metadate_id
     * @param  integer $filterStart
     * @param  integer $filterEnd
     *
     * @return array [resource_id, number_of_appearances]
     */
    public static function getPredominantRoomDB($metadate_id, $filterStart = 0, $filterEnd = 0)
    {
        if (($filterStart == 0) && ($filterEnd == 0)) {
            $query = "SELECT resource_id, COUNT(resource_id) AS c
                      FROM termine
                      INNER JOIN resources_assign ON (termin_id = assign_user_id)
                      WHERE termine.metadate_id = ? AND resource_id != ''
                      GROUP BY resource_id
                      ORDER BY c DESC";
            $parameters = [$metadate_id];
        } else {
            $query = "SELECT resource_id, COUNT(resource_id) AS c
                      FROM termine
                      INNER JOIN resources_assign ON (termin_id = assign_user_id)
                      WHERE termine.metadate_id = ? AND termine.date BETWEEN ? AND ?
                      GROUP BY resource_id
                      ORDER BY c DESC";
            $parameters = [$metadate_id, $filterStart, $filterEnd];
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchGrouped(PDO::FETCH_COLUMN) ?: false;
    }

    /**
     * Returns the list of freetext rooms ordered by number of appearance
     * in the metadate with the passed id
     *
     * @param  [type]  $metadate_id
     * @param  integer $filterStart
     * @param  integer $filterEnd
     *
     * @return array [freetex, number_of_appearances]
     */
    public static function getFreeTextPredominantRoomDB($metadate_id, $filterStart = 0, $filterEnd = 0)
    {
        if (($filterStart == 0) && ($filterEnd == 0)) {
            $query = "SELECT raum, COUNT(raum) AS c
                      FROM termine
                      LEFT JOIN resources_assign ON (termin_id = assign_user_id)
                      WHERE termine.metadate_id = ? AND assign_user_id IS NULL
                      GROUP BY raum
                      ORDER BY c DESC";
            $parameters = [$metadate_id];
        } else {
            $query = "SELECT raum, COUNT(raum) AS c
                      FROM termine
                      LEFT JOIN resources_assign ON (termin_id = assign_user_id)
                      WHERE termine.metadate_id = ? AND assign_user_id IS NULL
                        AND termine.date BETWEEN ? AND ?
                      GROUP BY raum
                      ORDER BY c DESC";
            $parameters = [$metadate_id, $filterStart, $filterEnd];
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchGrouped(PDO::FETCH_COLUMN) ?: false;
    }

    /**
     * returns the first date for a given metadate_id as array
     *
     * @param string $metadate_id
     *
     * @return array
     */
    public static function getFirstDate($metadate_id)
    {
        $query = "SELECT *
                  FROM termine
                  WHERE metadate_id = ?
                  ORDER BY `date` ASC
                  LIMIT 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$metadate_id]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
