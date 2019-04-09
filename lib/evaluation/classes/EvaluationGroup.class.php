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
require_once EVAL_FILE_GROUPDB;
require_once EVAL_FILE_OBJECT;
require_once EVAL_FILE_QUESTION;
# ====================================================== end: including files #


# Define constants ========================================================== #
/**
 * @const INSTANCEOF_EVALGROUP Is instance of an evaluationgroup object
 * @access public
 */
define ("INSTANCEOF_EVALGROUP", "EvaluationGroup");
# ===================================================== end: define constants #


/**
 * This class provides a group for an evaluation for the Stud.IP-project.
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */

class EvaluationGroup extends EvaluationObject {

#Define all required variables ============================================= #
  /**
   * Possible Type of thechildren
   * @access   private
   * @var      string   $childType
   */
   var $childType;

  /**
   *  Is it mandatory to answer sub questions
   *  @access  private
   *  @var     boolean  $mandatory
   */
   var $mandatory;

  /**
   * ID of the templateID for the childs
   * @access private
   * @var    string   $templateID
   */
  var $templateID;
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
   /**
    * Constructor
    * @access   public
    * @param    string            $objectID       The ID of an existing group
    * @param    EvaluationObject  $parentObject   The parent object if exists
    * @param    string            $loadChildren   See const EVAL_LOAD_*_CHILDREN
    */
   function __construct($objectID = "", $parentObject = null,
                              $loadChildren = EVAL_LOAD_NO_CHILDREN) {
    /* Set default values ------------------------------------------------- */
    parent::__construct($objectID, $parentObject, $loadChildren);
    $this->setAuthorEmail ("mail@AlexanderWillner.de");
    $this->setAuthorName ("Alexander Willner");
    $this->instanceof = INSTANCEOF_EVALGROUP;

    $this->childType = NULL;
    $this->mandatory = NO;
    /* --------------------------------------------------------------------- */

    /* Connect to database ------------------------------------------------- */
    $this->db = new EvaluationGroupDB ();
     if ($this->db->isError ())
       return $this->throwErrorFromClass ($this->db);
     $this->init ($objectID);
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #

# Define public functions =================================================== #
   /**
    * Returns wheter the childs are groups or questions
    * @access public
    */
    function getChildType () {
        return $this->childType;
    }

   /**
    * Adds a child
    * @access  public
    * @param   object  EvaluationObject &$child  The child object
    */
   function addChild (&$child) {
        parent::addChild ($child);
        $this->childType = $child->x_instanceof ();
   }

   /**
    * Defines which type of childs the group have
    * @access public
    * @param   string   $childType   The child type
    */
  function setChildType ($childType) {
    $this->childType = $childType;
  }

  /**
   * Is it mandatory to answer sub questions
   * @access public
   * @param  boolean  $boolean  true if it is mandatory
   */
  function setMandatory ($boolean) {
     $this->mandatory = $boolean == YES ? YES : NO;
  }

  /**
   * Is it mandatory to answer sub questions?
   * @access  public
   * @return  boolean  YES if it is true, else NO
   */
  function isMandatory () {
     return $this->mandatory == YES ? YES : NO;
  }

  /**
   * Gets the template id
   * @access   public
   * @return   string   The template id
   */
   function getTemplateID () {
      return $this->templateID;
   }

  /**
   * Sets the template id
   * @access   public
   * @param    string   $templateID   The template id
   */
   function setTemplateID ($templateID) {
      $newQuestionTexts =  [];

#      if ($templateID == $this->templateID)
#         return; // for performance reasons

      $this->templateID = $templateID;

      while ($child = &$this->getChild ()) {
         array_push ($newQuestionTexts, $child->getText ());
         $child->delete ();
      }

      while ($text = array_pop ($newQuestionTexts)) {
         $template = new EvaluationQuestion ($templateID, NULL,
          EVAL_LOAD_ALL_CHILDREN);
         $child = &$template->duplicate ();
         $child->setText ($text);
         $this->addChild ($child);
      }
   }
# ======================================================= end: public nctions #


# Define private functions ================================================== #
# ==================================================== end: private functions #
}

?>
