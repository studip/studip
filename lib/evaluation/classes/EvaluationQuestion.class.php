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
require_once EVAL_FILE_QUESTIONDB;
require_once EVAL_FILE_OBJECT;
require_once EVAL_FILE_ANSWER;
# ====================================================== end: including files #


# Define constants ========================================================== #
/**
 * @const INSTANCEOF_EVALGROUP Is instance of an evaluationquestion object
 * @access public
 */
define ("INSTANCEOF_EVALQUESTION", "EvaluationQuestion");

/**
 * @const EVALQUESTION_TYPE_LIKERT Type of question is skala
 * @access public
 */
define ("EVALQUESTION_TYPE_LIKERT", "likertskala");

/**
 * @const EVALQUESTION_TYPE_MC Type of question is normal
 * @access public
 */
define ("EVALQUESTION_TYPE_MC", "multiplechoice");

/**
 * @const EVALQUESTION_TYPE_POL Type of question is pol
 * @access public
 */
define ("EVALQUESTION_TYPE_POL", "polskala");
# ===================================================== end: define constants #


class EvaluationQuestion extends EvaluationObject {

# Define all required variables ============================================= #
  /**
   * Type of question (skala/normal) => see EVALQUESTION_TYPE_*
   * @access private
   * @var    string   $type
   */
  var $type;

  /**
   * If set YES it is allowed to choose more than one answer
   * @access private
   * @var    string   $isMultiplechoice
   */
  var $isMultiplechoice;
# ============================================================ end: variables #

# Define constructor and destructor ========================================= #
   /**
    * Constructor
    * @access   public
    * @param    string   $objectID       The ID of an existing question
    * @param    object   $parentObject   The parent object if exists
    * @param    integer  $loadChildren   See const EVAL_LOAD_*_CHILDREN
    */
   function __construct($objectID = "", $parentObject = NULL,
                                $loadChildren = EVAL_LOAD_NO_CHILDREN) {
    /* Set default values ------------------------------------------------- */
    parent::__construct($objectID, $parentObject, $loadChildren);
    $this->instanceof       = INSTANCEOF_EVALQUESTION;

    $this->type             = EVALQUESTION_TYPE_MC;
    $this->isMultiplechoice = NO;
    $this->templateID       = YES;
    /* ------------------------------------------------------------------- */

    /* Connect to database ------------------------------------------------- */
    $this->db = new EvaluationQuestionDB ();
    if ($this->db->isError ())
      return $this->throwErrorFromClass ($this->db);
    $this->init ($objectID);
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #
  

# Define public functions =================================================== #
  /**
   * Sets the type of a question
   * @access  public
   * @param   string  $type  The type of the question. ('skala','normal','pol')
   */
  function setType ($type) {
    $this->type = $type;
  }
  
  /**
   * Sets the type of a question
   * @access  public
   * @return  string  The type of the question.('likert','multiplechoice','pol')
   */
  function getType () {
    return  $this->type;
  }
  

  /**
   * Sets multiplechoice value of a question
   * @access  public
   * @param   $tinyint  The multiplechoice Value.
   */
  function setMultiplechoice ($multiplechoice) {
    $this->multiplechoice = $multiplechoice == YES ? YES : NO;
  }
  
  /**
   * Checks for multiplechoice
   * @access   public
   * @return   boolean   YES if it is an multiplechoice question
   */
  function isMultiplechoice () {
    return $this->multiplechoice == YES ? YES : NO;
  }
  
  
  /**
   * Adds a child and sets the value to pos+1
   * @access  public
   * @param   object  EvaluationObject &$child  The child object
   * @throws  error
   */
  function addChild (&$child) {
    parent::addChild ($child);
    if ($child->getValue () == 0)
        $child->setValue ($child->getPosition () + 1);
  }
# ===================================================== end: public functions #


# Define private functions ================================================== #
# ==================================================== end: private functions #

}

?>
