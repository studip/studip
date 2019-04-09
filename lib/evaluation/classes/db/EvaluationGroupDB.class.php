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
require_once EVAL_FILE_ANSWERDB;
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALGROUPDB Instance of an evaluationGroupDB object
 * @access public
 */
define ("INSTANCEOF_EVALGROUPDB", "EvalGroupDB");
# =========================================================================== #


/**
 * Databaseclass for all evaluationgroups
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationGroupDB extends EvaluationObjectDB {

# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function __construct() {
    /* Set default values -------------------------------------------------- */
    parent::__construct();
    $this->instanceof = INSTANCEOF_EVALGROUPDB;
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Loads an evaluationgroup from DB into an object
   *
   * @access private
   * @param  object  EvaluationGroup &$groupObject  The group to load
   * @throws error
   */
  function load (&$groupObject) {
    /* load group ---------------------------------------------------------- */
    $row = DBManager::get()->fetchOne("
        SELECT * FROM evalgroup
        WHERE evalgroup_id = ?
        ORDER BY position ", [$groupObject->getObjectID()]);

    if (count($row)==0)
      return $this->throwError (1,
            _("Keine Gruppe mit dieser ID gefunden."));

    $groupObject->setParentID   ($row['parent_id']);
    $groupObject->setTitle      ($row['title']);
    $groupObject->setText       ($row['text']);
    $groupObject->setPosition   ($row['position']);
    $groupObject->setChildType  ($row['child_type']);
    $groupObject->setMandatory  ($row['mandatory']);
    $groupObject->setTemplateID ($row['template_id']);

    /* ----------------------------------------------------------- end: load */


    /* load children ------------------------------------------------------- */
    if ($groupObject->loadChildren != EVAL_LOAD_NO_CHILDREN) {
       if ($groupObject->loadChildren == EVAL_LOAD_ONLY_EVALGROUP) {
          EvaluationGroupDB::addChildren ($groupObject);
       } else {
          EvaluationGroupDB::addChildren ($groupObject);
          EvaluationQuestionDB::addChildren ($groupObject);
       }
    }
    /* ------------------------------------------------------ end: questions */
  }


  /**
   * Saves a group
   * @access public
   * @param  object   EvaluationGroup  &$groupObject  The group to save
   * @throws  error
   */
  function save (&$groupObject)
  {
    /* save group ---------------------------------------------------------- */
    if ($this->exists ($groupObject->getObjectID ())) {
        DBManager::get()->execute("
            UPDATE evalgroup SET
                title           = ?,
                text            = ?,
                child_type      = ?,
                position        = ?,
                template_id     = ?,
                mandatory       = ?
            WHERE
                evalgroup_id    = ?
            ", [(string)$groupObject->getTitle(),
                     (string)$groupObject->getText(),
                     (string)$groupObject->getChildType(),
                     (int)$groupObject->getPosition (),
                     (string)$groupObject->getTemplateID(),
                     (int)$groupObject->isMandatory(),
                     (string)$groupObject->getObjectID()
             ]);
    } else {
        DBManager::get()->execute("
            INSERT INTO evalgroup SET
                evalgroup_id    = ?,
                parent_id       = ?,
                title           = ?,
                text            = ?,
                child_type      = ?,
                mandatory       = ?,
                template_id     = ?,
                position        = ?
            ", [
                     (string)$groupObject->getObjectID(),
                     (string)$groupObject->getParentID(),
                     (string)$groupObject->getTitle(),
                     (string)$groupObject->getText(),
                     (string)$groupObject->getChildType(),
                     (int)$groupObject->isMandatory(),
                     (string)$groupObject->getTemplateID(),
                     (int)$groupObject->getPosition()
            ]);
    }
    /* ------------------------------------------------------ end: groupsave */
  } // saved


 /**
   * Deletes a group
   * @access public
   * @param  object   EvaluationGroup  &$groupObject  The group to delete
   * @throws  error
   */
  function delete (&$groupObject) {
    DBManager::get()->execute("DELETE FROM evalgroup WHERE evalgroup_id = ?", [$groupObject->getObjectID()]);
  } // deleted

  /**
   * Checks if group with this ID exists
   * @access  public
   * @param   string   $groupID   The groupID
   * @return  bool     YES if exists
   */
  function exists ($groupID) {
    $result = DBManager::get()->fetchColumn("SELECT 1 FROM evalgroup WHERE evalgroup_id = ?", [$groupID]);
    return (bool)$result;
  }

  /**
   * Adds the children to a parent object
   * @access  public
   * @param   EvaluationObject  &$parentObject  The parent object
   */
   function addChildren (&$parentObject) {
      $result = DBManager::get()->fetchFirst("
        SELECT evalgroup_id FROM evalgroup
        WHERE parent_id = ?
        ORDER BY position", [$parentObject->getObjectID()]);

      if (($loadChildren = $parentObject->loadChildren) == EVAL_LOAD_NO_CHILDREN)
         $loadChildren = EVAL_LOAD_NO_CHILDREN;

      foreach ($result as $groupID) {
         $parentObject->addChild (
            new EvaluationGroup ($groupID, $parentObject, $loadChildren)
         );
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
      return INSTANCEOF_EVALGROUP;
    } else {
      $dbObject = new EvaluationQuestionDB ();
      return $dbObject->getType ($objectID);
    }
  }


   /**
    * Returns whether the childs are groups or questions
    * @access   public
    * @param    string   $objectID   The object id
    */
    function getChildType ($objectID) {
        $result = DBManager::get()->fetchColumn("
            SELECT child_type FROM evalgroup WHERE evalgroup_id = ?", [$objectID]);
        if ($result) return $result;
        return NULL;
    }

  /**
   * Returns the id from the parent object
   * @access public
   * @param  string  $objectID  The object id
   * @return string  The id from the parent object
   */
  function getParentID ($objectID) {
    return DBManager::get()->fetchColumn("
            SELECT parent_id FROM evalgroup WHERE evalgroup_id = ?", [$objectID]);
  }
# ===================================================== end: public functions #


# Define private functions ================================================== #

# ==================================================== end: private functions #

}

?>
