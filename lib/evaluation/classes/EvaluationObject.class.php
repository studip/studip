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
# ====================================================== end: including files #


# Define constants ========================================================== #
/**
 * @const INSTANCEOF_EVALOBJECT Is instance of an abstrakt evaluation object
 * @access public
 */
define ("INSTANCEOF_EVALOBJECT", "EvaluationObject");

/**
 * @const EVAL_LOAD_NO_CHILDREN Load no children from DB
 * @access public
 */
define ("EVAL_LOAD_NO_CHILDREN", 0);

/**
 * @const EVAL_LOAD_FIRST_CHILDREN Load just the direct children from DB
 * @access public
 */
define ("EVAL_LOAD_FIRST_CHILDREN", 1);

/**
 * @const EVAL_LOAD_ALL_CHILDREN Load all children from DB
 * @access public
 */
define ("EVAL_LOAD_ALL_CHILDREN", 2);

/**
 * @const EVAL_LOAD_ONLY_EVALGROUP Load just the groups for performance reasons
 * @access public
 */
define ("EVAL_LOAD_ONLY_EVALGROUP", 3);
# ===================================================== end: define constants #


/**
 * This abstract class provides functions for evaluation objects
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationObject extends StudipObject {
# Define all required variables ============================================= #
  /**
   * The parent object
   * @access   private
   * @var      string $parentObject
   */
  var $parentObject;

  /**
   * The parent object id
   * @access   private
   * @var      string $parentObjectCD
   */
  var $parentObjectID;

  /**
   * Array with all linked childobjects
   * @access   private
   * @var      array $childObjects
   */
  var $childObjects;

  /**
   * Counts the number of childs
   * @access   private
   * @var      integer  $numberChildren
   */
  var $numberChildren;

  /**
   * Title of the group
   * @access   private
   * @var      integer $title
   */
  var $title;

  /**
   * Text of the group
   * @access   private
   * @var      integer $text
   */
  var $text;
  
  /**
   * Position of this group in parent object
   * @access   private
   * @var      integer $position
   */
  var $position;

  /**
   * Holds the DB object
   * @access   private
   * @var      object DatabaseObject $db
   */
  var $db;

  /**
   * Is used as a counter for getNextChild )
   * @access   private
   * @var      integer $childNum
   */
  var $childNum;
  
  /**
   * Defines how many children to load. See EVAL_LOAD_*_CHILDREN
   * @access   private
   * @var      integer  $loadChildren
   */
  var $loadChildren;
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @param    string            $objectID       The ID of an existing object
   * @param    EvaluationObject  $parentObject   The parent object
   * @param    integer           $loadChildren   See const EVAL_LOAD_*_CHILDREN
   * @access   public
   */
  function __construct($objectID = "", $parentObject = NULL, 
                              $loadChildren = EVAL_LOAD_NO_CHILDREN) {

    /* Set default values -------------------------------------------------- */
    parent::__construct($objectID);
    $this->setAuthorEmail ("mail@AlexanderWillner.de");
    $this->setAuthorName ("Alexander Willner");
    $this->instanceof = INSTANCEOF_EVALOBJECT;

    $this->parentObject   = $parentObject;
    $this->loadChildren   = $loadChildren;
    $this->db             = NULL;
    $this->childObjects   =  [];
    $this->numberChildren = 0;
    $this->title          = "";
    $this->text           = "";
    $this->position       = 0;
    $this->childNum       = 0;
    /* --------------------------------------------------------------------- */
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
   /**
    * Sets the title
    * @access  public
    * @param   string  $title  The title.
    * @throws  error
    */
   function setTitle ($title) {
         $this->title = $title;
   }

   /**
    * Gets the title
    * @access  public
    * @return  string  The title
    */
   function getTitle () {
         return $this->title;
   }

   /**
    * Sets the text
    * @access  public
    * @param   string  $text        The text.
    * @throws  error
    */
   function setText ($text) {
         $this->text = $text;
   }

   /**
    * Gets the text
    * @access  public
    * @return  string  The text
    */
   function getText () {
         return $this->text;
   }
   

   /**
    * Sets the position
    * @access  public
    * @param   string  $position  The position.
    */
   function setPosition ($position) {
     $this->position = $position;
   }

   /**
    * Gets the position
    * @access  public
    * @return  string  The position
    */
   function getPosition () {
      return $this->position;
   }

   /**
    * Sets the parentObject
    * @access  public
    * @param   string  &$parentObject  The parentObject.
    */
   function setParentObject (&$parentObject) {
     $this->parentObject = &$parentObject;
     $this->parentObjectID = $this->parentObject->getObjectID ();
   }
   
   /**
    * Gets the parentObject
    * @access  public
    * @returns object  The parentObject.
    */
   function getParentObject () {
     return $this->parentObject;
   }

   /**
    * Gets the parentObjectID
    * @access  public
    * @returns string  The parentObjectID
    */
   function getParentID () {
     return $this->parentObjectID;
   }

   
   /**
    * Sets the parentObjectID
    * @access  public
    * @param   string  $parentID  The parent id
    */
   function setParentID ($parentID) {
     $this->parentObjectID = $parentID;
   }

   /**
    * Removes a child from the object (not from the DB!)
    * @access  public
    * @param   string   $childID   The child id
    */
   function removeChildID ($childID) {
      $temp         =  [];
      $childRemoved = NO;
      
      while ($child = &$this->getNextChild ()) {
         if ($childRemoved)
            $child->setPosition ($child->getPosition () - 1);
         
         if ($child->getObjectID () != $childID) {
            array_push ($temp, $child);
         } else {
            $childRemoved = YES;
         }
      }
      
      $this->childObjects = $temp;
      
      if ($childRemoved)
         $this->numberChildren--;
   }
   
   /**
    * Adds a child
    * @access  public
    * @param   object  EvaluationObject &$child  The child object
    */
   function addChild (&$child) {
     $child->setPosition ($this->numberChildren++);
     $child->setParentObject ($this);
     array_push ($this->childObjects, $child);
   }

   /**
    * Gets the first child and removes it (if no id is given)
    * @access  public
    * @param   string   $childID   The child id
    * @return  object   The first object
    */
   function &getChild ($childID = "") {
      if (!empty ($childID)) {
         while ($child = $this->getNextChild ())
         if ($child->getObjectID () == $childID) {
            $this->childNum = 0;
            return $child;
         }
         return NULL;
      } else {
         if ($this->numberChildren > 0)
            $this->numberChildren--;
         return array_pop ($this->childObjects);
      }
   }

   /**
    * Gets the next child
    * @access  public
    * @return  object   The next object, otherwise NULL
    */
   function &getNextChild () {
      if ($this->childNum >= $this->numberChildren) {
         $this->childNum = 0;
         return NULL;
      }     
      return $this->childObjects[$this->childNum++];
   }

   /**
    * Gets all the childs in the object
    * @access  public
    * @return  array  An array full of childObjects
    */
   function getChildren () {
     return $this->childObjects;
   }

   /**
    * Gets the number of children
    * @access  public
    * @return  integer  Number of children
    */
   function getNumberChildren () {
     return $this->numberChildren;
   }

   /**
    * Saves the object into the database
    * @access public
    */
   function save () {
     /* Check own object --------------------------------------------------- */
     $this->check ();
     if ($this->isError ())
       return;
     /* --------------------------------------------------------- end: check */

     /* save own object ---------------------------------------------------- */
     $this->db->save ($this);
     if ($this->db->isError ())
       return $this->throwErrorFromClass ($this->db);
     /* ----------------------------------------------- end: save own object */

     /* save children ------------------------------------------------------ */
     while ($childObject = $this->getNextChild ()) {
       $childObject->save ();
       if ($childObject->isError ())
     return $this->throwErrorFromClass ($childObject);      
     }
     /* ------------------------------------------------- end: save children */
   }

   /**
    * Deletes the object from the database
    * @access public
    */
   function delete () {   
     /* remove id from parentobject if exists ------------------------------ */
      if (!empty ($this->parentObject)) {
         $this->parentObject->removeChildID ($this->getObjectID ());
      }
     /* ----------------------------------------- end: remove id from parent */
      
     /* delete own object -------------------------------------------------- */
     $this->db->delete ($this);
     /* --------------------------------------------- end: delete own object */

     /* delete children ---------------------------------------------------- */
     while ($childObject = $this->getChild ()) {
       $childObject->delete ();
       if ($childObject->isError ())
         $this->throwErrorFromClass ($childObject);
     }
     /* ----------------------------------------------- end: delete children */
   }

   /**
    * Duplicates the evaluation object. WARNING: Stored childs will be
    * modified :(
    * @access public
    */
   function &duplicate () {
     $newObject = $this;
     $newObject->duplicate_init ();
     return $newObject;
   }
# ===================================================== end: public functions #


# Define private functions ================================================== #
   /**
    * Initialisation for duplicated objects
    * @access   private
    */
   function duplicate_init () {
     $this->init ();
     while ($childObject =& $this->getNextChild ()) {
       $childObject->setParentID ($this->getObjectID ());
       $childObject->duplicate_init ();
     }
   }
   
   /**
    * Initialisation for objects
    * @access   private
    * @param    string   $objectID   The object id
    */
   function init ($objectID = "") {
     /* Load an evaluationobject or create a new one ----------------------- */
     if (empty ($objectID)) {
       $this->setObjectID ($this->createNewID ());
     } else {
       $this->setObjectID ($objectID);
       $this->load ();
       if ($this->db->isError ())
     return $this->throwErrorFromClass ($this->db);
     }
    /* -------------------------------------------------------------------- */

   }

   /**
    * Loads the Object from the database
    * @access private
    */
   function load () {
     $this->db->load ($this);
     if ($this->db->isError ())
       return $this->throwErrorFromClass ($this->db);
   }
   
   /**
    * Checks if object is in a valid state
    * @access private
    */
   function check () {
     if (empty ($this->db))
       $this->throwError (1, _("Es existiert kein DB-Objekt"));
   }
    
    /**
     * Gets all children of a special kind
     * @param   EvaluationObject  &$object      the parent object
     * @param   string            $instanceof  instance of the searched child
     * @param   boolean           $reset       for internal use
     * @access  public
     */
   function getSpecialChildobjects (&$object, $instanceof, $reset = false) {
      static $specialchildobjects =  [];
      if ($reset == YES) {
         $specialchildobjects =  [];
      }
      
      if ($object->x_instanceof () == $instanceof) {
         array_push ($specialchildobjects, $object);
      } else {
         while ($child = &$object->getNextChild ()) {
            $this->getSpecialChildobjects ($child, $instanceof, NO);
         }
      }
      return $specialchildobjects;
   }

   /**
    * Debugfunction
    * @access public
    */
   function toString () {
     echo "<table border=1 cellpadding=5><tr><td>";
     echo "Typ: ".$this->x_instanceof ()."<br>";
     echo "ObjectID: ".$this->getObjectID ()."<br>";
     echo "ParentID: ".$this->getParentID ()."<br>";
     echo "ParentObject: ".$this->getParentObject ()."<br>";
     echo "Author: ".$this->getAuthorID ()."<br>";
     echo "Titel: ".$this->getTitle ()."<br>";
     echo "Text: ".$this->getText ()."<br>";
     echo "Position: ".$this->getPosition ()."<br>";
     echo "Untergruppen: ".$this->getNumberChildren ()."<br>";
     echo "</td></tr>";
     while ($child = $this->getNextChild ()) {
       echo "<tr><td>";
       $i++;
       echo "<b>Kind $i</b>"."<br>";
       $child->toString ();
       echo "</td></tr>";
     }
     echo "</table>";
   }
# ==================================================== end: private functions #

}

?>
