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
require_once EVAL_FILE_EVALDB;
require_once EVAL_FILE_OBJECT;
require_once EVAL_FILE_GROUP;
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVAL Is instance of an evaluation object
 * @access public
 */
define ("INSTANCEOF_EVAL", "Evaluation");
# ===================================================== end: define constants #


/**
 * The mainclass for an evaluation for the Stud.IP-project.
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class Evaluation extends EvaluationObject implements PrivacyObject
{
# Define all required variables ============================================= #
  /**
   * Startdate
   * @access   private
   * @var      integer $startdate
   */
  var $startdate;

  /**
   * Stopdate
   * @access   private
   * @var      integer $stopdate
   */
  var $stopdate;

  /**
   * Timespan
   * @access   private
   * @var      integer $timespan
   */
  var $timespan;

  /**
   * Time of creation. Is set automatically.
   * @access   private
   * @var      integer $mkdate
   */
  var $mkdate;

  /**
   * Time of last change. Is set automatically.
   * @access   private
   * @var      integer $chdate
   */
  var $chdate;

  /**
   * Defines wheter the evaluation is anonymous
   * @access   private
   * @var      boolean    $anonymous
   */
  var $anonymous;

  /**
   * Defines whether the evaluation is visible
   * @access   private
   * @var      boolean   $visible
   */
  var $visible;

  /**
   * Defines whether the evaluation template is shared
   * @access   private
   * @var      boolean   $shared
   */
  var $shared;

  /**
   * Counts the number of connected ranges
   * @access   private
   * @var      integer  $numberRanges
   */
  var $numberRanges;
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
   /**
    * Constructor
    * @access   public
    * @param    string   $objectID       The ID of an existing evaluation
    * @param    object   $parentObject   The parent object if exists
    * @param    integer  $loadChildren   See const EVAL_LOAD_*_CHILDREN
    */
   function __construct($objectID = "", $parentObject = null,
                        $loadChildren = EVAL_LOAD_NO_CHILDREN) {
    /* Set default values ------------------------------------------------- */
    parent::__construct($objectID, $parentObject, $loadChildren);
    $this->setAuthorEmail ("mail@AlexanderWillner.de");
    $this->setAuthorName ("Alexander Willner");
    $this->instanceof = INSTANCEOF_EVAL;

    $this->rangeID      =  [];
    $this->startdate    = NULL;
    $this->stopdate     = NULL;
    $this->timespan     = NULL;
    $this->mkdate       = time ();
    $this->chdate       = time ();
    $this->anonymous    = NO;
    $this->visible      = NO;
    $this->shared       = NO;
    $this->isUsed       = NO;
    $this->rangeNum     = 0;
    /* -------------------------------------------------------------------- */

    /* Connect to database ------------------------------------------------ */
    $this->db = new EvaluationDB ();
    if ($this->db->isError ())
      return $this->throwErrorFromClass ($this->db);
    $this->init ($objectID);
    /* -------------------------------------------------------------------- */

  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
   /**
    * Sets the startdate
    * @access  public
    * @param   integer  $startdate  The startdate.
    * @throws  error
    */
   function setStartdate ($startdate) {
     if (!empty ($startdate)) {
       if (!empty ($this->stopdate) && $startdate > $this->stopdate)
     return $this->throwError
       (1, _("Das Startdatum ist nach dem Stoppdatum."));
       if ($startdate <= 0)
     return $this->throwError (1, _("Das Startdatum ist leider ungültig."));
     }
     $this->startdate = $startdate;
   }

   /**
    * Gets the startdate
    * @access  public
    * @return  integer  The startdate
    */
   function getStartdate () {
      return $this->startdate;
   }

   /**
    * Sets the stopdate
    * @access  public
    * @param   integer  $stopdate  The stopdate.
    * @throws  error
    */
   function setStopdate ($stopdate) {
     if (!empty ($stopdate)) {
       if ($stopdate <= 0)
         return $this->throwError (1, _("Das Stoppdatum ist leider ungültig."));
       if ($stopdate < $this->startdate)
         return $this->throwError (1, _("Das Stoppdatum ist vor dem Startdatum."));
       if (!empty ($this->timespan))
         $this->timespan = NULL;
     }
     $this->stopdate = $stopdate;
   }

   /**
    * Gets the stopdate
    * @access  public
    * @return  string  The stopdate
    */
   function getStopdate () {
      return $this->stopdate;
   }

   /**
    * Gets the real stop date as a UNIX-timestamp (e.g. startdate + timespan)
    * @access  public
    * @return  integer The UNIX-timestamp with the real stopdate
    */
   function getRealStopdate () {
      $stopdate = $this->getStopdate ();

      if ($this->getTimespan () != NULL)
         $stopdate = $this->getStartdate () + $this->getTimespan ();

      return $stopdate;
   }

   /**
    * Sets the timespan
    * @access  public
    * @param   string  $timespan  The timespan.
    * @throws  error
    */
   function setTimespan ($timespan) {
     if (!empty ($timespan) && !empty ($this->stopdate))
       $this->stopdate = NULL;
     $this->timespan = $timespan;
   }

   /**
    * Gets the timespan
    * @access  public
    * @return  string  The timespan
    */
   function getTimespan () {
      return $this->timespan;
   }

   /**
    * Gets the creationdate
    * @access  public
    * @return  integer  The creationdate
    */
   function getCreationdate () {
     return $this->mkdate;
   }

   /**
    * Gets the changedate
    * @access  public
    * @return  integer  The changedate
    */
   function getChangedate () {
      return $this->chdate;
   }

   /**
    * Sets anonymous
    * @access  public
    * @param   string  $anonymous  The anonymous.
    * @throws  error
    */
   function setAnonymous ($anonymous) {
     $this->anonymous = $anonymous == YES ? YES : NO;
   }

   /**
    * Gets anonymous
    * @access  public
    * @return  string  The anonymous
    */
   function isAnonymous () {
      return $this->anonymous == YES ? YES : NO;
   }

   /**
    * Sets visible
    * @access  public
    * @param   string  $visible  The visible.
    * @throws  error
    */
   function setVisible ($visible) {
     $this->visible = $visible == YES ? YES : NO;
   }

   /**
    * Gets visible
    * @access  public
    * @return  string  The visible
    */
   function isVisible () {
      return $this->visible == YES ? YES : NO;
   }

   /**
    * Set shared for a public search
    * @access  public
    * @param   boolean  $shared  if true it is shared
    */
   function setShared ($shared) {
     if ($shared == YES && $this->isTemplate () == NO)
         return $this->throwError (1, _("Nur ein Template kann freigegeben werden"));
     $this->shared = $shared == YES ? YES : NO;
   }

   /**
    * Is shared for a public search?
    * @access  public
    * @return  boolen  true if it is shared template
    */
   function isShared () {
      return $this->shared == YES ? YES : NO;
   }

   /**
    * Is this evaluation a template?
    * @access  public
    * @return  boolen  true if it is a template
    */
   function isTemplate () {
      return empty ($this->rangeID) ? YES : NO;
   }

   /**
    * Has a user used this evaluation?
    * @access  public
    * @param   string  $userID  Optional an user id
    * @return  string  YES if a user used this evaluation
    */
   function hasVoted ($userID = "") {
     return $this->db->hasVoted ($this->getObjectID (), $userID);
   }

   /**
    * Removes a range from the object (not from the DB!)
    * @access  public
    * @param   string   $rangeID   The range id
    */
   function removeRangeID ($rangeID) {
      $temp =  [];
      while ($oldRangeID = $this->getNextRangeID ()) {
         if ($oldRangeID != $rangeID) {
            array_push ($temp, $oldRangeID);
         }
      }
      $this->rangeID = $temp;
      $this->numberRanges = count ($temp);
   }

   /**
    * Removes all rangeIDs
    * @access   public
    */
   function removeRangeIDs () {
      while ($this->getRangeID ());
   }

   /**
    * Adds a rangeID
    * @access  public
    * @param   string  $rangeID  The rangeID
    * @throws  error
    */
   function addRangeID ($rangeID) {
     array_push ($this->rangeID, $rangeID);
     $this->numberRanges++;
   }

   /**
    * Gets the first rangeID and removes it
    * @access  public
    * @return  string  The first object
    */
   function getRangeID () {
      if ($this->numberRanges)
        $this->numberRanges--;
     return array_pop ($this->rangeID);
   }

   /**
    * Gets the next rangeID
    * @access  public
    * @return  string   The rangeID
    */
   function getNextRangeID () {
     if ($this->rangeNum >= $this->numberRanges) {
       $this->rangeNum = 0;
       return NULL;
     }
     return $this->rangeID[$this->rangeNum++];
   }

   /**
    * Gets all the rangeIDs from the evaluation
    * @access  public
    * @return  array  An array full of rangeIDs
    */
   function getRangeIDs () {
     return $this->rangeID;
   }

   /**
    * Gets the number of ranges
    * @access  public
    * @return  integer  Number of ranges
    */
   function getNumberRanges () {
     return $this->numberRanges;
   }

   /**
    * Resets all answers for this evaluation
    * @access public
    */
   function resetAnswers () {
      // Für diesen Mist habe ich jetzt ca. 3 Stunden gebraucht :(
      $answers = $this->getSpecialChildobjects ($this, INSTANCEOF_EVALANSWER);

      $number = count ($answers);
      for ($i = 0; $i < $number; $i++) {
         $answer = &$answers[$i];
#while ($answer->getUserID ()); // delete users...
         $answer->db->resetVotes ($answer);
      }

   }

   /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $field_data = DBManager::get()->fetchAll("SELECT * FROM eval WHERE author_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('Evaluation'), 'eval', $field_data);
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM evalanswer_user WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('EvaluationAnswerUser'), 'evalanswer_user', $field_data);
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM eval_group_template WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('EvaluationGroupTemplate'), 'eval_group_template', $field_data);
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM eval_templates WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('EvaluationTemplates'), 'eval_templates', $field_data);
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM eval_templates_user WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('EvaluationTemplatesUser'), 'eval_templates_user', $field_data);
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM eval_user WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('EvaluationUser'), 'eval_user', $field_data);
        }

    }
# ===================================================== end: public functions #

# Define private functions ================================================== #
   /**
    * Sets the creationdate
    * @access  private
    * @param   integer  $creationdate  The creationdate.
    * @throws  error
    */
   function setCreationdate ($creationdate) {
     $this->mkdate = $creationdate;
   }

   /**
    * Sets the changedate
    * @access  private
    * @param   integer  $changedate  The changedate.
    * @throws  error
    */
   function setChangedate ($changedate) {
     $this->chdate = $changedate;
   }

   /**
    * Checks if object is in a valid state
    * @access private
    */
   function check () {
     parent::check ();
     if (empty ($this->title))
       $this->throwError (1, _("Der Titel darf nicht leer sein."));

     if ($this->isTemplate () && $this->hasVoted ())
        $this->throwError (2, _("Ungültiges Objekt: Bei einer Vorlage wurde abgestimmt."));

     if (!$this->isTemplate () && $this->isShared ())
        $this->throwError (3, _("Ungültiges Objekt: Eine aktive Evaluation wurde freigegeben."));

   }
# ==================================================== end: private functions #

}

?>
