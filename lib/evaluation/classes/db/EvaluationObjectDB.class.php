<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
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

require_once 'lib/evaluation/evaluation.config.php';

/**
 * Databaseclass for all evaluationobjects
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationObjectDB extends DatabaseObject
{
    /**
     * Constructor
     * @access   public
     */
    public function __construct()
    {
        parent::__construct();
        $this->instanceof = 'EvalDBObject';
    }

    /**
     * Gets the name of the range. Copied somewhere from Stud.IP...
     * @access  public
     * @param string $rangeID the rangeID
     * @param boolean $html_decode (optional)
     * @return  string                    The name of the range
     */
    public function getRangename($rangeID, $html_decode = true)
    {
        if ($rangeID == "studip") {
            return _('Systemweite Evaluationen');
        }
        $o_type = get_object_type($rangeID, ['sem', 'user', 'inst']);
        if (in_array($o_type, ['sem', 'inst', 'fak'])) {
            $name = Context::getHeaderLine();
            if ($name != NULL) {
                if ($html_decode)
                    $rangename = decodeHTML($name);
                else
                    $rangename = $name;
            } else {
                $rangename = _('Kein Titel gefunden.');
            }
            return $rangename;
        }
        if ($o_type != 'user') {
            $user_id = get_userid($rangeID);
        } else {
            $user_id = $rangeID;
        }

        if ($user_id != $GLOBALS['user']->id) {
            $rangename = _('Profil') . ': '
                . get_fullname($user_id, 'full', true)
                . ' (' . get_username($user_id) . ')';
        } else {
            $rangename = _('Profil');
        }
        return $rangename;
    }

    /**
     * Gets the global Studi.IP perm
     * @access  public
     * @param boolean $as_value If YES return as value
     * @return  string   the perm or NULL
     */
    public function getGlobalPerm($as_value = false)
    {
        if ($GLOBALS['perm']->have_perm("root")) {
            return ($as_value) ? 63 : "root";
        } elseif ($GLOBALS['perm']->have_perm("admin")) {
            return ($as_value) ? 31 : "admin";
        } elseif ($GLOBALS['perm']->have_perm("dozent")) {
            return ($as_value) ? 15 : "dozent";
        } elseif ($GLOBALS['perm']->have_perm("tutor")) {
            return ($as_value) ? 7 : "dozent";
        } elseif ($GLOBALS['perm']->have_perm("autor")) {
            return ($as_value) ? 3 : "autor";
        } elseif ($GLOBALS['perm']->have_perm("user")) {
            return ($as_value) ? 1 : "user";
        } else {
            return ($as_value) ? 0 : NULL;
        }
    }

    /**
     * Get the Stud.IP-Perm for a range
     * @param string $rangeID The range id
     * @param string $userID The user id
     * @param boolean $as_value If YES return as value
     * @access   public
     * @return   string
     */
    public function getRangePerm($rangeID, $userID = NULL, $as_value = false)
    {
        if (!$rangeID) {
            print "no rangeID!<br>";
            return NULL;
        }
        $userID = ($userID) ? $userID : $GLOBALS['user']->id;
        $range_perm = $GLOBALS['perm']->get_studip_perm($rangeID, $userID);

        if ($rangeID == $userID) {
            return ($as_value) ? 63 : "root";
        }

        if (($rangeID == "studip") && ($GLOBALS['perm']->have_perm("root"))) {
            return ($as_value) ? 63 : "root";
        }

        switch ($range_perm) {
            case "root":
                return ($as_value) ? 63 : "root";
            case "admin":
                return ($as_value) ? 31 : "admin";
            case "dozent":
                return ($as_value) ? 15 : "dozent";
            case "tutor":
                return ($as_value) ? 7 : "dozent";
            case "autor":
                return ($as_value) ? 3 : "autor";
            case "user":
                return ($as_value) ? 1 : "user";
            default:
                return 0;
        }

    }

    /**
     * Look for all rangeIDs for my permissions
     * @param object  Perm &$permObj  PHP-LIB-Perm-Object
     * @param object  User &$userObj  PHP-LIB-User-Object
     * @param string $rangeID RangeID of actual page
     */
    public function getValidRangeIDs(&$permObj, &$userObj, $rangeID)
    {
        $range_ids = [];
        $username = $userObj->username;

        $range_ids += [
            $username => ["name" => _("Profil")]
        ];

        if ($permObj->have_perm("root")) {
            $range_ids += ["studip" => ["name" => _("Stud.IP-System")]];
            if (($adminRange = $this->getRangename($rangeID)) &&
                $rangeID != $userObj->id)
                $range_ids += [$rangeID => ["name" =>
                    $adminRange]];
        } else if ($permObj->have_perm("admin")) {
            if (($adminRange = $this->getRangename($rangeID)) &&
                $rangeID != $userObj->id) {
                $range_ids += [$rangeID => ["name" =>
                    $adminRange]];
            }
        } else if ($permObj->have_perm("dozent") || $permObj->have_perm("tutor")) {
            if ($ranges = search_range("")) {
                $range_ids += $ranges;
            }
        }
        return $range_ids;
    }

    /**
     * Returns the number of ranges with no permission
     * @access   public
     * @param EvaluationObject   &$eval The evaluation
     * @param boolean $return_ids If YES return the ids
     * @return   integer            Number of ranges with no permission
     */
    public function getEvalUserRangesWithNoPermission(&$eval, $return_ids = false)
    {
        $no_permisson = 0;
        $rangeIDs = $eval->getRangeIDs();

        if (!is_array($rangeIDs)) {
            $rangeIDs = [$rangeIDs];
        }

        foreach ($eval->getRangeIDs() as $rangeID) {
            $user_perm = EvaluationObjectDB::getRangePerm($rangeID, $GLOBALS['user']->id, YES);
            // every range with a lower perm than Tutor
            if ($user_perm < 7) {
                $no_permisson++;
                $no_permisson_ranges[] = $rangeID;
            }
        }
        if ($return_ids == YES) {
            return $no_permisson_ranges;
        }
        return ($no_permisson > 0) ? $no_permisson : NO;
    }

    /**
     * Gets the public template ids
     * @access   public
     * @param string $searchString The name of the template
     * @return   array    The public template ids
     */
    public function getPublicTemplateIDs($searchString)
    {
        $sql = "
            SELECT eval_id FROM eval
            LEFT JOIN auth_user_md5 ON user_id = author_id
            WHERE shared = 1
              AND author_id <> :current_user
              AND (title LIKE :search_string
                   OR text LIKE :search_string
                   OR Vorname LIKE :search_string
                   OR Nachname LIKE :search_string
                   OR username LIKE :search_string
                  )
            ORDER BY title";

        return DBManager::get()->fetchFirst(
            $sql, [':current_user' => $GLOBALS['user']->id, ':search_string' => '%' . $searchString . '%']
        );
    }

    /**
     * Return all evaluationIDs in a specific range
     *
     * @access  public
     * @param string $rangeID Specific rangeID or it is a template
     * @param string $state Specific state
     * @return  array   All evaluations in this range and this state
     */
    public function getEvaluationIDs($rangeID = "", $state = "")
    {
        if (!empty ($rangeID) && !is_scalar($rangeID)) {
            return $this->throwError(1, _("Übergebene RangeID ist ungültig."));
        }
        if ($state != "" &&
            $state != EVAL_STATE_NEW &&
            $state != EVAL_STATE_ACTIVE &&
            $state != EVAL_STATE_STOPPED) {
            return $this->throwError(2, _("Übergebener Status ist ungültig."));
        }

        if (get_userid($rangeID) != NULL && $rangeID != NULL) {
            $rangeID = get_userid($rangeID);
        }

        if (!empty ($rangeID)) {
            $sql =
                "SELECT" .
                " a.eval_id " .
                "FROM" .
                " eval_range a, eval b " .
                "WHERE" .
                " a.eval_id = b.eval_id" .
                " AND " .
                " a.range_id = ?";
            $param = $rangeID;
        } else {
            $sql =
                "SELECT" .
                " b.eval_id " .
                "FROM" .
                " eval b " .
                "LEFT JOIN" .
                " eval_range " .
                "ON" .
                " b.eval_id = eval_range.eval_id " .
                "WHERE" .
                " eval_range.eval_id IS NULL" .
                " AND" .
                " b.author_id = ?";
            $param = $GLOBALS['user']->id;
        }

        if ($state == EVAL_STATE_NEW)
            $sql .= " AND (b.startdate IS NULL OR b.startdate > " . time() . ")";

        elseif ($state == EVAL_STATE_ACTIVE)
            $sql .=
                " AND b.startdate < " . time() . "" .
                " AND (" .
                "      (b.timespan IS NULL AND b.stopdate > " . time() . ")" .
                "       OR" .
                "      (b.stopdate IS NULL AND (b.startdate+b.timespan) > " . time() . ")" .
                "       OR" .
                "      (b.timespan IS NULL AND b.stopdate IS NULL)" .
                "     )";

        elseif ($state == EVAL_STATE_STOPPED)
            $sql .=
                " AND b.startdate < " . time() . "" .
                " AND (" .
                "      (b.timespan IS NULL AND b.stopdate <= " . time() . ")" .
                "       OR" .
                "      (b.stopdate IS NULL AND (b.startdate+b.timespan) <= " . time() . ")" .
                "     )";

        $sql .= " ORDER BY chdate DESC";

        return DBManager::get()->fetchFirst($sql, [$param]);
    }

    /**
     * Gets the evaluation id for a object id
     * @access  public
     * @param string $objectID The object id
     * @return  string   The evaluation id or nothing
     */
    public function getEvalID($objectID)
    {
        if (empty ($objectID)) {
            throw new Exception("FATAL ERROR in getEvalID ;)");
        }

        $type = EvaluationObjectDB::getType($objectID);

        switch ($type) {
            case INSTANCEOF_EVALANSWER:
                $parentID = EvaluationAnswerDB::getParentID($objectID);
                break;
            case INSTANCEOF_EVALQUESTION:
                $parentID = EvaluationQuestionDB::getParentID($objectID);
                break;
            case INSTANCEOF_EVALGROUP:
                $parentID = EvaluationGroupDB::getParentID($objectID);
                break;
            default:
                return $objectID;
        }
        return EvaluationObjectDB::getEvalID($parentID);
    }

    /**
     * Returns the type of an objectID
     * @access public
     * @param string $objectID The objectID
     * @return string  INSTANCEOF_x, else NO
     */
    public function getType($objectID)
    {
        return (new EvaluationDB ())->getType($objectID);
    }
}
