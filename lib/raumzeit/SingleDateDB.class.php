<?php
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: DONE
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// SingleDateDB.class.php
//
// Datenbank-Abfragen für SingleDate.class.php
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
 * SingleDateDB.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */

class SingleDateDB
{
    static function storeSingleDate($termin)
    {
        //NOTE: If you modify this method make sure the changes
        //are also inserted in CourseDate::cancelDate and
        //CourseExDate::unCancelDate to keep the behavior consistent
        //across Stud.IP!

        $table = 'termine';

        if ($termin->isExTermin()) {
            $table = 'ex_termine';

            $query = "SELECT assign_id FROM resources_assign WHERE assign_user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$termin->getTerminID()]);
            $assign_id = $statement->fetchColumn();

            if ($assign_id) {
                // delete resource-request, if any
                if ($request_id = self::getRequestID($termin->getTerminID())) {
                    $rr = new RoomRequest($request_id);
                    $rr->delete();
                }

                // delete resource assignment, if any
                AssignObject::Factory($assign_id)->delete();
            }
        }

        $issueIDs = $termin->getIssueIDs();
        if (is_array($issueIDs)) {
            $query = "REPLACE INTO themen_termine (termin_id, issue_id)
                      VALUES (?, ?)";
            $statement = DBManager::get()->prepare($query);

            foreach ($issueIDs as $val) {
                $statement->execute([
                    $termin->getTerminID(),
                    $val
                ]);
            }
        }

        if ($termin->isUpdate()) {
            $query = "UPDATE :table
                      SET metadate_id = :metadate_id, date_typ = :date_typ,
                          date = :date, end_time = :end_time,
                          range_id = :range_id, autor_id = :autor_id,
                          raum = :raum, content = :content
                      WHERE termin_id = :termin_id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':table', $table, StudipPDO::PARAM_COLUMN);
            $statement->bindValue(':metadate_id', $termin->getMetaDateID() ?: null);
            $statement->bindValue(':date_typ', $termin->getDateType());
            $statement->bindValue(':date', $termin->getStartTime());
            $statement->bindValue(':end_time', $termin->getEndTime());
            $statement->bindValue(':range_id', $termin->getRangeID());
            $statement->bindValue(':autor_id', $termin->getAuthorID());
            $statement->bindValue(':raum', $termin->getFreeRoomText());
            $statement->bindValue(':content', $termin->getComment());
            $statement->bindValue(':termin_id',$termin->getTerminID());
            $statement->execute();

            if ($statement->rowCount() > 0) {
                $query = "UPDATE :table SET chdate = :chdate WHERE termin_id = :termin_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':table', $table, StudipPDO::PARAM_COLUMN);
                $statement->bindValue(':chdate', $termin->getChDate());
                $statement->bindValue(':termin_id', $termin->getTerminID());
                $statement->execute();
            }
        } else {
            $query = "REPLACE INTO :table
                        (metadate_id, date_typ, date, end_time, mkdate, chdate,
                         termin_id, range_id, autor_id, raum, content)
                      VALUES
                        (:metadate_id, :date_typ, :date, :end_time, :mkdate, :chdate,
                         :termin_id, :range_id, :autor_id, :raum, :content)";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':table', $table, StudipPDO::PARAM_COLUMN);
            $statement->bindValue(':metadate_id', $termin->getMetaDateID());
            $statement->bindValue(':date_typ', $termin->getDateType());
            $statement->bindValue(':date', $termin->getStartTime());
            $statement->bindValue(':end_time', $termin->getEndTime());
            $statement->bindValue(':mkdate', $termin->getMkDate());
            $statement->bindValue(':chdate', $termin->getChDate());
            $statement->bindValue(':termin_id', $termin->getTerminID());
            $statement->bindValue(':range_id', $termin->getRangeID());
            $statement->bindValue(':autor_id', $termin->getAuthorID());
            $statement->bindValue(':raum', $termin->getFreeRoomText());
            $statement->bindValue(':content', $termin->getComment());
            $statement->execute();
        }

        $query = "DELETE FROM termin_related_persons WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin->getTerminId()]);

        if (count($termin->related_persons)
                && (count($termin->related_persons) < CourseMember::countBySQL("Seminar_id = ? AND status = 'dozent'", [$termin->range_id]))) {
            $query = "INSERT IGNORE INTO termin_related_persons (range_id, user_id) VALUES (?, ?)";
            $statement = DBManager::get()->prepare($query);

            foreach ($termin->getRelatedPersons() as $user_id) {
                $statement->execute([
                    $termin->getTerminId(),
                    $user_id
                ]);
            }
        }

        $query = "DELETE FROM termin_related_groups WHERE termin_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin->getTerminId()]);
        if (count($termin->related_groups)
                && (count($termin->related_groups) < Statusgruppen::countBySQL("range_id = ?", [$termin->range_id]))) {
            $query = "INSERT IGNORE INTO termin_related_groups (termin_id, statusgruppe_id) VALUES (?, ?)";
            $statement = DBManager::get()->prepare($query);
            foreach ($termin->getRelatedGroups() as $statusgruppe_id) {
                $statement->execute([
                    $termin->getTerminId(),
                    $statusgruppe_id
                ]);
            }
        }

        return true;
    }

    static function restoreSingleDate($termin_id)
    {
        $query = "SELECT termine.*, resource_id, 0 AS ex_termin,
                        GROUP_CONCAT(trp.user_id) AS related_persons,
                        GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                  FROM termine
                        LEFT JOIN termin_related_persons AS trp ON (termine.termin_id = trp.range_id)
                        LEFT JOIN termin_related_groups AS trg ON (termine.termin_id = trg.termin_id)
                        LEFT JOIN resources_assign ON (assign_user_id = termine.termin_id)
                  WHERE termine.termin_id = ?
                  GROUP BY termine.termin_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
            $result['related_persons'] = array_filter(explode(',', $result['related_persons']));
            $result['related_groups'] = array_filter(explode(',', $result['related_groups']));
            return $result;
        }

        $query = "SELECT ex_termine.*, 1 AS ex_termin,
                         GROUP_CONCAT(trp.user_id) AS related_persons,
                         GROUP_CONCAT(DISTINCT trg.statusgruppe_id) AS related_groups
                  FROM ex_termine
                    LEFT JOIN termin_related_persons AS trp ON (ex_termine.termin_id = trp.range_id)
                    LEFT JOIN termin_related_groups AS trg ON (ex_termine.termin_id = trg.termin_id)
                  WHERE ex_termine.termin_id = ?
                  GROUP BY termin_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
            $result['related_persons'] = array_filter(explode(',', $result['related_persons']));
            $result['related_groups'] = array_filter(explode(',', $result['related_groups']));
            return $result;
        }

        return false;
    }

    static function deleteSingleDate($id, $ex_termin)
    {
        if (Config::get()->RESOURCES_ENABLE) {
            // delete resource assignment, if any
            $killAssign = AssignObject::Factory(self::getAssignID($id));
            $killAssign->delete();

            if ($request_id = self::getRequestID($id)) {
                $rr = new RoomRequest($request_id);
                $rr->delete();
            }
        }

        // Prepare query that deletes all entries for a given termin id
        // from a given table
        $query = "DELETE FROM :table WHERE termin_id = :termin_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':termin_id', $id);

        // Execute statement for the termin itself (ex_termin if neccessary)
        $statement->bindValue(':table', $ex_termin ? 'ex_termine' : 'termine', StudipPDO::PARAM_COLUMN);
        $statement->execute();

        // Execute statement for themen_termine
        $statement->bindValue(':table', 'themen_termine', StudipPDO::PARAM_COLUMN);
        $statement->execute();

        // Execute statement for termin_related_persons
        $query = "DELETE FROM termin_related_persons WHERE range_id = :termin_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':termin_id', $id);
        $statement->execute();

        return true;
    }

    static function getAssignID($termin_id)
    {
        $query = "SELECT assign_id
                  FROM termine
                  LEFT JOIN resources_assign ON (assign_user_id = termin_id)
                  WHERE termin_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        return $statement->fetchColumn() ?: false;
    }

    static function getRequestID($termin_id)
    {
        $query = "SELECT request_id FROM resources_requests WHERE termin_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        return $statement->fetchColumn() ?: false;
    }

    static function getIssueIDs($termin_id)
    {
        $query = "SELECT tt.*
                  FROM themen_termine AS tt
                  LEFT JOIN themen AS t USING (issue_id)
                  WHERE termin_id = ?
                    AND issue_id IS NOT NULL AND issue_id != '' ORDER BY t.priority, t.title";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    static function deleteIssueID($issue_id, $termin_id)
    {
        $query = "DELETE FROM themen_termine WHERE termin_id = ? AND issue_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id, $issue_id]);

        return true;
    }

    static function deleteRequest($termin_id)
    {
        $query = "DELETE FROM resources_requests WHERE termin_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);

        return true;
    }

    static function deleteAllDates($course_id)
    {
        $query = "DELETE FROM ex_termine WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$course_id]);

        $query = "SELECT termin_id FROM termine WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$course_id]);

        $termine = 0;
        while ($termin_id = $statement->fetchColumn()) {
            self::deleteSingleDate($termin_id, false);
            $termine += 1;
        }

        return $termine;
    }
}