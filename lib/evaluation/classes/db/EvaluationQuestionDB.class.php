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
require_once EVAL_FILE_ANSWERDB;
# ====================================================== end: including files #

# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALQUESTIONDB Instance of an evaluationQuestionDB object
 * @access public
 */
define ("INSTANCEOF_EVALQUESTIONDB", "EvalQuestionDB");
# =========================================================================== #


class EvaluationQuestionDB extends EvaluationObjectDB {

# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function __construct() {

    /* Set default values ------------------------------------------------ */
    parent::__construct();
    $this->instanceof = INSTANCEOF_EVALGROUPDB;
    /* ------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #

# Define public functions =================================================== #
  /**
   * Loads a question from the DB
   * @access  public
   * @param   EvaluationQuestion   &$questionObject   The question object
   */
  function load (&$questionObject) {
    $db = DBManager::get();

    /* load question ------------------------------------------------------- */
    $query =
      "SELECT".
      " * ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = ? ".
      "ORDER BY".
      " position ";
    $row = $db->fetchOne($query, [$questionObject->getObjectID ()]);

    if (!count($row))
      return $this->throwError (1,
            _("Keine Frage mit dieser ID gefunden."));

    $questionObject->setParentID       ($row['parent_id']);
    $questionObject->setType           ($row['type']);
    $questionObject->setPosition       ($row['position']);
    $questionObject->setText           ($row['text']);
    $questionObject->setMultiplechoice ($row['multiplechoice']);
    /* --------------------------------------------------------------------- */


    /* load children ------------------------------------------------------- */
    if ($questionObject->loadChildren != EVAL_LOAD_NO_CHILDREN)
       EvaluationAnswerDB::addChildren ($questionObject);
    /* ------------------------------------------------------ end: questions */
  } //loaded



  /**
   * Writes or updates a question into the DB
   * @access  public
   * @param   EvaluationQuestion   &$questionObject   The question object
   */
  function save (&$questionObject)
  {
    $db = DBManager::get();
    
    if ($this->exists ($questionObject->getObjectID ())) {
      $sql =
   "UPDATE".
   " evalquestion ".
   "SET".
   " parent_id       = ?,".
   " type            = ?,".
   " position        = ?,".
   " text            = ?,".
   " multiplechoice  = ? ".
   "WHERE".
   " evalquestion_id = ?";
    } else {
      $sql =
   "INSERT INTO".
   " evalquestion ".
   "SET".
   " parent_id       = ?,".
   " type            = ?,".
   " position        = ?,".
   " text            = ?,".
   " multiplechoice  = ?,".
   " evalquestion_id = ?";
;
    }
    $db->execute($sql, [
        (string)$questionObject->getParentID(),
        (string)$questionObject->getType(),
        (int)$questionObject->getPosition(),
        (string)$questionObject->getText(),
        (int)$questionObject->isMultiplechoice(),
        $questionObject->getObjectID()
        ]);
  } // saved


  /**
   * Deletes a question
   * @access public
   * @param  object EvaluationQuestion &$questionObject The question to delete
   * @throws  error
   */
  function delete (&$questionObject) {
    $db = DBManager::get();

    /* delete question ----------------------------------------------------- */
    $sql =
      "DELETE FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = ?";
    $db->execute($sql, [$questionObject->getObjectID ()]);
    /* ------------------------------------------------------- end: deleting */
  } // deleted


  /**
   * Checks if question with this ID exists
   * @access  public
   * @param   string   $questionID   The questionID
   * @return  bool     YES if exists
   */
  function exists ($questionID) {
    $db = DBManager::get();

    $sql =
      "SELECT".
      " 1 ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = ?";
    $result = $db->fetchColumn($sql, [$questionID]);

    return (bool)$result;
  }

/**
   * Checks if a template exists with this title
   * @access  public
   * @param   string   $questionTitle   The title of the question
   * @param   string   $userID          The user id
   * @return  bool     YES if exists
   */
  function titleExists ($questionTitle, $userID) {
    $db = DBManager::get();

    $sql =
       "SELECT".
       " 1 ".
       "FROM".
       " evalquestion ".
       "WHERE".
       " text = ? ".
       " AND ".
       " parent_id = ?";

    $result = $db->fetchColumn($sql, [$questionTitle,$userID]);

    return (bool)$result;
  }


  /**
   * Adds the children to a parent object
   * @access  public
   * @param   EvaluationObject  &$parentObject  The parent object
   */
  function addChildren (&$parentObject) {
    $db = DBManager::get();

    $sql =
      "SELECT".
      " evalquestion_id ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " parent_id = ? ".
      "ORDER BY".
      " position";
    $result = $db->fetchFirst($sql, [$parentObject->getObjectID ()]);

    $loadChildren = $parentObject->loadChildren == EVAL_LOAD_ALL_CHILDREN
         ? EVAL_LOAD_ALL_CHILDREN
         : EVAL_LOAD_NO_CHILDREN;

    foreach ($result as $evalquestion_id) {
      $parentObject->addChild (new EvaluationQuestion
                ($evalquestion_id,
                $parentObject, $loadChildren));
    }
  }

  /**
   * Returns the type of an objectID
   * @access public
   * @param  string  $objectID  The objectID
   * @return string  INSTANCEOF_x, else NO
   */
  function getType ($objectID) {
    if ($this->exists ($objectID)) {
      return INSTANCEOF_EVALQUESTION;
    } else {
      $dbObject = new EvaluationAnswerDB ();
      return $dbObject->getType ($objectID);
    }
  }

  /**
   * Returns the id from the parent object
   * @access public
   * @param  string  $objectID  The object id
   * @return string  The id from the parent object
   */
  function getParentID ($objectID) {
    $db = DBManager::get();

    $sql =
      "SELECT".
      " parent_id ".
      "FROM".
      " evalquestion ".
      "WHERE".
      " evalquestion_id = ?";
    $result = $db->fetchColumn($sql, [$objectID]);
    return $result;
  }

  /**
   * Returns the ids of the Answertemplates of a user
   * @access public
   * @param  string  $userID  The user id
   * @return array  The ids of the answertemplates
   */
  function getTemplateID ($userID) {
    $db = DBManager::get();

     if (EvaluationObjectDB::getGlobalPerm()=="root") {
         $sql =
            "SELECT".
            " evalquestion_id ".
            "FROM".
            " evalquestion ".
            "WHERE".
            " parent_id = '0' ORDER BY text";
            return $db->fetchFirst($sql);
      } else {
         $sql =
            "SELECT".
            " evalquestion_id ".
            "FROM".
            " evalquestion ".
            "WHERE".
            " parent_id = '".$userID."' ".
            "OR ".
            " parent_id = '0' ORDER BY text";
      $sql .= " ";
      return $db->fetchFirst($sql, [$userID]);
      }
  }


}
