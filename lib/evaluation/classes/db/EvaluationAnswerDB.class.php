<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Beschreibung
 *
 * @author      Alexander Willner <mail@AlexanderWillner.de>
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

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
# ====================================================== end: including files #

# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALANSWERDB Instance of an evaluationanswerDB object
 * @access public
 */
define ("INSTANCEOF_EVALANSWERDB", "EvalANSWERDB");
# =========================================================================== #


class EvaluationAnswerDB extends EvaluationObjectDB {


# Define all required variables ============================================= #

# ============================================================ end: variables #

# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function __construct () {

    /* Set default values ------------------------------------------------ */
    parent::__construct();
    $this->instanceof = INSTANCEOF_EVALANSWERDB;
    /* ------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Loads answers of a group from the DB
   * @access  public
   * @param   EvaluationAnswer   &&$answerObject   The answer object
   */
  function load (&$answerObject) {
    /* load answer --------------------------------------------------------- */
    $row = DBManager::get()->fetchOne("SELECT * FROM evalanswer
                                            WHERE evalanswer_id= ?", [$answerObject->getObjectID()]);
    if (!count($row))
      return $this->throwError (2,
            _("Keine Antwort mit dieser ID gefunden."));

    $answerObject->setObjectID ($row['evalanswer_id']);
    $answerObject->setParentID ($row['parent_id']);
    $answerObject->setPosition ($row['position']);
    $answerObject->setText     ($row['text']);
    $answerObject->setValue    ($row['value']);
    $answerObject->setRows     ($row['rows']);
    $answerObject->setResidual ($row['residual']);
    /* --------------------------------------------------------------------- */

  } //loaded


  /**
   * Loads the votes from the users for this answer
   * @access   public
   * @param    EvaluationAnswer   &$answerObject   The answer object
   */
   function loadVotes (&$answerObject) {
        /* load users -------------------------------------------------------- */
        $result = DBManager::get()->fetchFirst("SELECT user_id FROM evalanswer_user
                                            WHERE evalanswer_id= ?", [$answerObject->getObjectID()]);
        foreach ($result as $row) {
            $answerObject->addUserID($row, NO);
        }
   }
   /* ----------------------------------------------------------- end: users */

  /**
   * Writes answers into the DB
   * @access    public
   * @param     EvaluationAnswer   &$answerObject       The answerobject
   * @throws    error
   */
  function save (&$answerObject)
  {
    /* save answers -------------------------------------------------------- */
    DBManager::get()->execute(
            "REPLACE INTO evalanswer SET
                `evalanswer_id`   = ?,
                `parent_id`       = ?,
                `position`        = ?,
                `text`            = ?,
                `value`           = ?,
                `rows`            = ?,
                `residual`        = ?
                ",
            [$answerObject->getObjectID(),
                    $answerObject->getParentID(),
                    $answerObject->getPosition(),
                    $answerObject->getText(),
                    $answerObject->getValue(),
                    $answerObject->getRows(),
                    $answerObject->isResidual()]);
    /* ----------------------------------------------------- end: answersave */

    /* connect answer to users --------------------------------------------- */
    while ($userID = $answerObject->getNextUserID ()) {
            DBManager::get()->execute(
                "INSERT INTO evalanswer_user SET
                    evalanswer_id   = ?,
                    user_id         = ?,
                    evaldate        = UNIX_TIMESTAMP()",
                [$answerObject->getObjectID(), $userID]);
    }
    /* ----------------------------------------------------- end: connecting */

  } // saved

  /**
   * Deletes all votes from the users for this answers
   * @access   public
   * @param    EvaluationAnswer   &$answerObject   The answer object
   */
  function resetVotes (&$answerObject) {
   /* delete userconnects ------------------------------------------------- */
    DBManager::get()->execute("
        DELETE FROM evalanswer_user
            WHERE evalanswer_id   = ?",
        [$answerObject->getObjectID()]);
    /* ------------------------------------------------------- end: deleting */
  }

  /**
   * Deletes a answer
   * @access public
   * @param  EvaluationAnswer   &$answerObject   The answer to delete
   * @throws  error
   */
  function delete (&$answerObject) {
    /* delete answer ----------------------------------------------------- */
    DBManager::get()->execute("
        DELETE FROM evalanswer
            WHERE evalanswer_id   = ?",
        [$answerObject->getObjectID()]);
    /* ------------------------------------------------------- end: deleting */
    $this->resetVotes($answerObject);
  } // deleted


  /**
   * Checks if answer with this ID exists
   * @access  public
   * @param   string   $answerID   The answerID
   * @return  bool     YES if exists
   */
  function exists ($answerID) {
    $result = DBManager::get()->fetchOne("SELECT 1 FROM evalanswer
                                            WHERE evalanswer_id= ?", [$answerID]);
    if (count($result)>0)
        return true;
    return false;
  }


  /**
   * Adds the children to a parent object
   * @access  public
   * @param   EvaluationObject  &$parentObject  The parent object
   */
  function addChildren (&$parentObject) {
    $result = DBManager::get()->fetchFirst("SELECT evalanswer_id FROM evalanswer
                                            WHERE parent_id= ? ORDER by position",
                                            [$parentObject->getObjectID()]);

    $loadChildren =
        $parentObject->loadChildren == EVAL_LOAD_ALL_CHILDREN ? EVAL_LOAD_ALL_CHILDREN : EVAL_LOAD_NO_CHILDREN;

    foreach ($result as $row) {
      $parentObject->addChild (new EvaluationAnswer
                ($row, $parentObject, $loadChildren));
    }
  }

  /**
   * Returns the type of an objectID
   * @access public
   * @param  string  $objectID  The objectID
   * @return string  INSTANCEOF_x, else NO
   */
  function getType ($objectID) {
    if ($this->exists($objectID)) {
      return INSTANCEOF_EVALANSWER;
    } else {
      return NO;
    }
  }

  /**
   * Returns the id from the parent object
   * @access public
   * @param  string  $objectID  The object id
   * @return string  The id from the parent object
   */
  function getParentID ($objectID) {
    return DBManager::get()->fetchColumn("SELECT parent_id FROM evalanswer
                                            WHERE evalanswer_id = ?",
                                            [$objectID()]);
  }

   /**
    * Give all textanswers for a user and question for the export
    * @access  public
    * @param   string   $questionID   The question id
    * @param   string   $userID       The user id
    */
   function getUserAnwerIDs ($questionID, $userID) {
      /* ask database ------------------------------------------------------- */
      $sql = "SELECT a.evalanswer_id as ttt FROM evalanswer a, evalanswer_user b
                    WHERE a.parent_id = ? AND a.evalanswer_id = b.evalanswer_id";
      if (empty ($userID))
        $answer_ids = DBManager::get()->fetchFirst($sql, [$questionID]);
      else
        $answer_ids = DBManager::get()->fetchFirst($sql." AND b.user_id = ?", [$questionID, $userID]);
      /* -------------------------------------------------------- end: asking */
      return $answer_ids;
  }

  /**
   * Checks whether a user has voted for an answer
   * @access   public
   * @param    string   $answerID   The answer id
   * @param    string   $userID     The user id
   * @return   boolean  YES if user has voted for the answer
   */
  function hasVoted ($answerID, $userID) {
    $result = DBManager::get()->fetchOne("SELECT 1 FROM evalanswer_user
                                            WHERE evalanswer_id= ? AND user_id", [$answerID, $userID]);
    if (count($result)>0)
        return true;
    return false;
  }

  function getAllAnswers ($question_id, $userID, $only_user_answered = false) {
        if ($only_user_answered)
            return DBManager::get()->fetchAll("
                SELECT evalanswer.*, COUNT(IF(user_id=?,1,NULL)) AS has_voted
                FROM evalanswer LEFT JOIN evalanswer_user USING(evalanswer_id)
                WHERE parent_id = ? AND user_id = ?
                GROUP BY evalanswer.evalanswer_id ORDER BY position",
                [$userID, $question_id, $userID]);
        else
            return DBManager::get()->fetchAll("
                SELECT evalanswer.*, COUNT(IF(user_id=?,1,NULL)) AS has_voted
                FROM evalanswer LEFT JOIN evalanswer_user USING(evalanswer_id)
                WHERE parent_id = ?
                GROUP BY evalanswer.evalanswer_id ORDER BY position",
                [$userID, $question_id]);
  }
}
?>
