<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// IssueDB.class.php
//
// Datenbank-Abfragen für Issue.class.php
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
 * IssueDB.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 * @deprecated
 */

class IssueDB {

    function restoreIssue($issue_id)
    {
        $query = "SELECT *
                  FROM themen
                  WHERE issue_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$issue_id]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    function storeIssue(&$issue)
    {
        global $user;


        if ($issue->new) {
            $query = "INSERT INTO themen
                        (issue_id, seminar_id, author_id, title, description, mkdate, chdate, priority)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $issue->issue_id,
                $issue->seminar_id,
                $issue->author_id,
                $issue->title,
                $issue->description,
                $issue->mkdate,
                $issue->chdate,
                $issue->priority
            ]);
        } else {
            $query = "UPDATE themen
                      SET seminar_id = ?, author_id = ?, title = ?, description = ?, mkdate = ?, priority = ?
                      WHERE issue_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $issue->seminar_id,
                $issue->author_id,
                $issue->title,
                $issue->description,
                $issue->mkdate,
                $issue->priority,
                $issue->issue_id
            ]);

            if ($statement->rowCount()) {
                $query = "UPDATE themen SET chdate = UNIX_TIMESTAMP() WHERE issue_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$issue->issue_id]);

                $query = "SELECT termin_id FROM themen_termine WHERE issue_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$issue->issue_id]);
                $termin_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

                if (count($termin_ids) > 0) {
                    $query = "UPDATE termine SET chdate = UNIX_TIMESTAMP() WHERE termin_id IN (?)";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$termin_ids]);
                }
            }

        }
        return TRUE;
    }

    function deleteIssue($issue_id, $seminar_id, $title = '', $description = '')
    {
        $query = "DELETE FROM themen WHERE issue_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$issue_id]);

        $query = "DELETE FROM themen_termine WHERE issue_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$issue_id]);
    }

    function isIssue($issue_id)
    {
        $query = "SELECT 1 FROM themen WHERE issue_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$issue_id]);
        return (bool)$statement->fetchColumn();
    }

    function getDatesforIssue($issue_id)
    {
        $query = "SELECT termine.*
                  FROM themen_termine
                  INNER JOIN termine USING (termin_id)
                  WHERE issue_id = ?
                  ORDER BY `date` ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$issue_id]);

        $ret = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $ret[$row['termin_id']] = $row;
        }
        return $ret;
    }

    static function deleteAllIssues($course_id)
    {
        $query = "SELECT issue_id FROM themen WHERE seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$course_id]);
        $themen = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($themen as $issue_id) {
            self::deleteIssue($issue_id, $course_id);
        }

        return count($themen);
    }
}
?>
