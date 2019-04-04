<?php
# Lifter002: DONE - not applicable
# Lifter007: TEST
# Lifter003: TEST
# Lifter010: DONE - not applicable

// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// MetaDateDB.class.php
//
// Datenbank-Abfragen für MetaDate.class.php
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
 * MetaDateDB.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */
class MetaDateDB
{
    public static function has_dates($metadate_id, $seminar_id, $filterStart = 0, $filterEnd = 0)
    {
        $query = "SELECT 1 FROM termine WHERE range_id = ? AND metadate_id = ?";
        $parameters = array($seminar_id, $metadate_id);

        if ($filterStart != 0) {
            $query .= " AND date >= ? AND end_time <= ?";
            array_push($parameters, $filterStart, $filterEnd);
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        return (bool)$statement->fetchColumn();
    }
}
