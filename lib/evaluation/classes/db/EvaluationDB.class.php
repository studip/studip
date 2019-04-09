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
// +--------------------------------------------------------------------------+


# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_FILE_OBJECTDB;
require_once EVAL_FILE_GROUPDB;
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALDB Is instance of an evaluationDB object
 * @access public
 */
define ("INSTANCEOF_EVALDB", "EvalDB");

/**
 * @const EVAL_STATE_NEW Beschreibung
 * @access public
 */
define ("EVAL_STATE_NEW", "new");

/**
 * @const EVAL_STATE_ACTIVE Beschreibung
 * @access public
 */
define ("EVAL_STATE_ACTIVE", "active");

/**
 * @const EVAL_STATE_STOPPED Beschreibung
 * @access public
 */
define ("EVAL_STATE_STOPPED", "stopped");
# =========================================================================== #


/**
 * Databaseclass for all evaluations
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationDB extends EvaluationObjectDB {

# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function __construct () {
    /* Set default values -------------------------------------------------- */
    parent::__construct();
    $this->instanceof = INSTANCEOF_EVALDBOBJECT;
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Loads an evaluation from DB into an object
   *
   * @access public
   * @param  object EvaluationObject &$evalObject  The evaluation to load
   * @throws error
   */
  function load (&$evalObject) {
    /* load evaluation basics ---------------------------------------------- */

    $row = DBManager::get()->fetchOne("SELECT * FROM eval WHERE eval_id = ?", [$evalObject->getObjectID()]);

    if (!count($row))
      return $this->throwError (1,
            _("Keine Evaluation mit dieser ID gefunden."));

    $evalObject->setAuthorID     ($row['author_id']);
    $evalObject->setTitle        ($row['title']);
    $evalObject->setText         ($row['text']);
    $evalObject->setStartdate    ($row['startdate']);
    $evalObject->setStopdate     ($row['stopdate']);
    $evalObject->setTimespan     ($row['timespan']);
    $evalObject->setCreationdate ($row['mkdate']);
    $evalObject->setChangedate   ($row['chdate']);
    $evalObject->setAnonymous    ($row['anonymous']);
    $evalObject->setVisible      ($row['visible']);
    $evalObject->setShared       ($row['shared']);
    /* --------------------------------------------------------- end: values */


    /* load ranges --------------------------------------------------------- */
    $range_ids = DBManager::get()->fetchFirst("SELECT range_id FROM eval_range WHERE eval_id = ?",
            [$evalObject->getObjectID()]);

    foreach ($range_ids as $range_id) {
      $evalObject->addRangeID($range_id);
    }
    /* --------------------------------------------------------- end: ranges */


    /* load groups --------------------------------------------------------- */
    if ($evalObject->loadChildren != EVAL_LOAD_NO_CHILDREN) {
        EvaluationGroupDB::addChildren ($evalObject);
     }
    /* ---------------------------------------------------------- end: group */

  } // loaded


  /**
   * Saves an evaluation
   * @access public
   * @param  object   Evaluation  &$evalObject  The evaluation to save
   * @throws  error
   */
  function save (&$evalObject)
  {
    $startdate = $evalObject->getStartdate();
    $stopdate  = $evalObject->getStopdate();
    $timespan  = $evalObject->getTimespan();

    $evalObject->setChangedate(time());

    /* save evaluation ----------------------------------------------------- */
    if ($this->exists ($evalObject->getObjectID ())) {

        DBManager::get()->execute(
            "UPDATE eval SET title = ?, text = ?, startdate = ?,
                stopdate = ?, timespan = ?, mkdate = ?,
                chdate = ?, anonymous = ?, visible = ?, shared = ?
             WHERE eval_id = ?",
                [$evalObject->getTitle(), $evalObject->getText(),
                    $startdate, $stopdate, $timespan, $evalObject->getCreationdate(),
                    $evalObject->getChangedate(), $evalObject->isAnonymous(),
                    $evalObject->isVisible(), $evalObject->isShared(), $evalObject->getObjectID()]);
    } else {
        DBManager::get()->execute(
            "INSERT INTO eval SET eval_id = ?,
                author_id = ?, title = ?, text = ?, startdate = ?,
                stopdate = ?, timespan = ?, mkdate = ?, chdate = ?,
                anonymous = ?, visible = ?, shared = ?",
                [$evalObject->getObjectID(), $evalObject->getAuthorID(),
                    $evalObject->getTitle(), $evalObject->getText(),
                    $startdate, $stopdate, $timespan, $evalObject->getCreationdate(),
                    $evalObject->getChangedate(), $evalObject->isAnonymous(),
                    $evalObject->isVisible(), $evalObject->isShared()]);
    }

    /* ------------------------------------------------------- end: evalsave */

    /* connect to ranges --------------------------------------------------- */
      DBManager::get()->execute("DELETE FROM eval_range WHERE eval_id  = ?", [$evalObject->getObjectID()]);

      while ($rangeID = $evalObject->getNextRangeID ()) {
            DBManager::get()->execute("INSERT INTO eval_range SET eval_id  = ?, range_id = ?",
                [$evalObject->getObjectID(), $rangeID]);
      }
    /* ----------------------------------------------------- end: connecting */
  } //...saved


  /**
   * Deletes an evaluation
   * @access public
   * @param  object   Evaluation  &$evalObject  The evaluation to delete
   * @throws  error
   */
  function delete (&$evalObject) {
    /* delete evaluation --------------------------------------------------- */
    DBManager::get()->execute("DELETE FROM eval WHERE eval_id  = ?", [$evalObject->getObjectID()]);
    /* ------------------------------------------------------- end: deleting */

    /* delete rangeconnects ------------------------------------------------ */
    DBManager::get()->execute("DELETE FROM eval_range WHERE eval_id  = ?", [$evalObject->getObjectID()]);
    /* ------------------------------------------------------- end: deleting */

    /* delete userconnects ------------------------------------------------- */
    DBManager::get()->execute("DELETE FROM eval_user WHERE eval_id  = ?", [$evalObject->getObjectID()]);
    /* ------------------------------------------------------- end: deleting */
  } // deleted

  /**
   * Checks if evaluation with this ID exists
   * @access  public
   * @param   string   $evalID   The evalID
   * @return  bool     YES if exists
   */
  function exists ($evalID) {
    $entry = DBManager::get()->fetchOne("SELECT 1 FROM eval WHERE eval_id = ?", [$evalID]);
    if (count($entry) > 0)
        return true;
    return false;
  }

  /**
   * Checks if someone used the evaluation
   * @access  public
   * @param   string   $evalID   The eval id
   * @param   string   $userID   The user id
   * @return  bool     YES if evaluation was used
   */
  function hasVoted ($evalID, $userID = "") {
    /* ask database ------------------------------------------------------- */
    $sql= "SELECT 1 FROM eval_user WHERE eval_id = ?";
    if (empty($userID))
        $entry = DBManager::get()->fetchOne($sql, [$evalID]);
    else
        $entry = DBManager::get()->fetchOne($sql." AND user_id = ?", [$evalID, $userID]);
    /* --------------------------------------------------------- end: asking */
    if (count($entry) > 0)
        return true;
    return false;
  }

  /**
   * Returns the type of an objectID
   * @access public
   * @param  string  $objectID  The objectID
   * @return string  INSTANCEOF_x, else NO
   */
  function getType ($objectID) {
    if ($this->exists ($objectID)) {
      return INSTANCEOF_EVAL;
    } else {
      $dbObject = new EvaluationGroupDB();
      return $dbObject->getType ($objectID);
    }
  }
# ===================================================== end: public functions #


# Define private functions ================================================== #

# ==================================================== end: private functions #


# Define static functions =================================================== #
  /**
   * Connect a user with an evaluation
   * @access   public
   * @param    string   $evalID   The evaluation id
   * @param    string   $userID   The user id
   */
  function connectWithUser ($evalID, $userID) {
    if (empty ($userID))
      die ("EvaluationDB::connectWithUser: UserID leer!!");
    DBManager::get()->execute("INSERT IGNORE INTO eval_user SET eval_id = ?, user_id = ?", [$evalID, $userID]);
  }

   /**
    * Removes the connection of an evaluation with a user or all users
    * @access   public
    * @param    string   $evalID   The evaluation id
    * @param    string   $userID   The user id
    */
   function removeUser ($evalID, $userID = "") {
      $sql = "DELETE FROM eval_user WHERE eval_id  = ?";

      if (empty($userID))
         DBManager::get()->execute($sql, [$evalID]);
      else
         DBManager::get()->execute($sql." AND user_id = ?", [$evalID, $userID]);
   }

  /**
   * Get number of users who participated in the eval
   * @access public
   * @param  string   $evalID  The eval id
   * @return integer  The number of users
   */
   function getNumberOfVotes ($evalID) {
        return DBManager::get()->fetchColumn("SELECT count(DISTINCT user_id) AS number FROM eval_user WHERE eval_id = ?", [$evalID]);
   }

   /**
   * Get users who participated in the eval
   * @access public
   * @param  string   $evalID     The eval id
   * @param  array    $answerIDs  The answerIDs to get the pseudonym users
   * @return integer  The number of users
   */
   function getUserVoted ($evalID, $answerIDs =  [], $questionIDs =  []) {
      $sql = "SELECT DISTINCT user_id FROM ";

      /* ask database ------------------------------------------------------- */
      if (empty($answerIDs) && empty($questionIDs)) {
        $sql .= "eval_user WHERE eval_id = ?";
        $search_criteria = $evalID;
      } elseif (empty ($questionIDs)) {
        $sql .= "evalanswer_user WHERE evalanswer_id IN (?)";
        $search_criteria = $answerIDs;
      } else {
        $sql .= "evalanswer INNER JOIN evalanswer_user USING(evalanswer_id) WHERE parent_id IN (?)";
        $search_criteria = $questionIDs;
      }

      return DBManager::get()->fetchFirst($sql, [$search_criteria]);
      /* ------------------------------------------------ end: asking database */
  }


  /**
   *
   * @access public
   * @param  string   $search_str
   * @return array
   */
   function search_range($search_str) {
      return search_range($search_str, true);
   }
# ===================================================== end: static functions #

}

?>
