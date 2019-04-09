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
require_once EVAL_FILE_ANSWERDB;
require_once EVAL_FILE_OBJECT;
# ====================================================== end: including files #

# Define constants ========================================================== #
/**
 * @const INSTANCEOF_EVALANSWER Is instance of an evaluationanswer object
 * @access public
 */
define ("INSTANCEOF_EVALANSWER", "EvaluationAnswer");
# ===================================================== end: define constants #


class EvaluationAnswer extends EvaluationObject {


# Define all required variables ============================================= #
  /**
   * The value for an answer
   * @access   private
   * @var      integer $value;
   */
  var $value;

  /**
   * If >0 the answer is a freetext with $rows rows
   * @access   private
   * @var      integer $rows
   */
  var $rows;

  /**
   * The userIDs of users who voted for this answer
   * @access   private
   * @var      array $users
   */
  var $users;

  /**
   * The number of users voted for this answer
   * @access   private
   * @var      integer $userNum
   */
  var $userNum;

  /**
   * For internal use (getNextUserID)
   * @access   private
   * @var      integer $userNumIterator
   */
  var $userNumIterator;
  
  /**
   * If true this is the residual answer for a question
   * @access   private
   * @var      boolean   $residual
   */
  var $residual;
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
   /**
    * Constructor
    * @access   public
    * @param    string   $objectID       The ID of an existing answer
    * @param    object   $parentObject   The parent object if exists
    * @param    integer  $loadChildren   See const EVAL_LOAD_*_CHILDREN
    */
   function __construct($objectID = "", $parentObject = NULL,
                              $loadChildren = EVAL_LOAD_NO_CHILDREN) {
    /* Set default values ------------------------------------------------- */
    parent::__construct($objectID, $parentObject, $loadChildren);
    $this->instanceof      = INSTANCEOF_EVALANSWER;
    
    $this->value           = 0;
    $this->rows            = 0;
    $this->users           =  [];
    $this->userNum         = 0;
    $this->userNumIterator = 0;
    $this->residual        = NO;
    /* --------------------------------------------------------------------- */
    
    /* Connect to database ------------------------------------------------- */
    $this->db = new EvaluationAnswerDB ();
    if ($this->db->isError ())
      return $this->throwErrorFromClass ($this->db);
    $this->init ($objectID);
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   * Gets the number of votes for this answer
   * @access  public
   * @return  string  The counter of the answer
   */
  function getNumberOfVotes () {
      return $this->userNum;
  }
 
  /**
   * Gets the number of rows from freetext answers
   * @access   public
   * @return   integer   The number of rows
   */
  function getRows () {
    return $this->rows;
  }
  
  /**
   * Gets the number of rows for freetext answers
   * @access   public
   * @param    integer   $rows   The number of rows
   */
  function setRows ($rows) {
    $this->rows = $rows;
    return;
  }

  /**
   * Gets the value of an answer
   * @access   public  
   * @return   integer   The value
   */
  function getValue () {
    return $this->value;;
  }

  /**
   * Sets the value of an answer
   * @access   public
   * @param    integer   $value   The value
   */
  function setValue ($value) {
    $this->value = $value;
  }

  /**
   * Checks whether the answer is a residual answer
   * @access   public
   * @return   boolean   YES if it is a residual answer
   */
  function isResidual () {
    return $this->residual == YES ? YES : NO;
  }

  /**
   * Sets the answers as an residual answer
   * @access   public
   * @param    boolean   $boolean   YES to set it as a residual answer
   */
  function setResidual ($boolean) {
    $this->residual = $boolean == YES ? YES : NO;
  }

  
  /**
   * Vote for this answer 
   * @access  public
   * @param   string   $userID   The user id
   */
   function vote ($userID) {
     $this->addUserID ($userID);
  }

  /**
   * Non-Anonymous vote for this answer
   * @access  public
   * @param   string  $userID  The user id
   */
   function addUserID ($userID) {
      if (empty ($userID))
         return $this->throwError (1,
              _("Nur pseudonyme Abstimmung erlaubt! Neue ID mit StudipObject::createNewID () erzeugen"));

      $this->userNum++;
      array_push ($this->users, $userID);
  }
   
  /**
   * Gets the first user and removes it
   * @access  public
   * @return  string  The first user id
   */
  function getUserID () {
    if ($this->userNum > 0)
      $this->userNum--;
    return array_pop ($this->users);
  }

  /**
   * Gets the next user
   * @access  public
   * @return  string  The next user id, otherwise NULL
   */
  function getNextUserID () {
    if ($this->userNumIterator >= $this->userNum) {
      $this->userNumIterator = 0;
      return NULL;
    }
    return $this->users[$this->userNumIterator++];
  }

  /**
   * Gets all the user ids
   * @access  public
   * @return  array  An array full of user ids
   */
  function getUserIDs () {
    return $this->users;
  }

  /**
   * @access public
   * @return integer  YES, if the Answer is a textfield
   */
  function isFreetext () {
    return ($this->rows == 0) ? NO : YES;
  }
# ===================================================== end: public functions #

# Define private functions ================================================== #
  /**
   * Checks if object is in a valid state
   * @access private
   */
  function check () {
    parent::check ();
#    if (!empty ($this->users) && !empty ($this->counter))
#$this->throwError (1, _("Zähler hat einen ungültigen Wert"));
  }
  
  /**
   * Debugfunction
   * @access   private
   */
  function toString () {
     parent::toString ();
     echo "Anzahl der Stimmen: ".$this->getNumberOfVotes ()."<br>\n";
  }
# ==================================================== end: private functions #
}
?>
