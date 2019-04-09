<?php
# Lifter002: TEST
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
require_once 'lib/export/export_tmp_gc.inc.php';
require_once EVAL_FILE_EVALDB;
require_once EVAL_FILE_ANSWERDB;
require_once EVAL_FILE_OBJECT;
require_once EVAL_FILE_GROUP;
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALEXPORTMANAGER Is instance of an export manager
 * @access public
 */
define ("INSTANCEOF_EVALEXPORTMANAGER", "EvaluationExportManager");

/**
 * @const EVALEXPORT_PREFIX The prefix for temporary filenames
 * @access public
 */
define ("EVALEXPORT_PREFIX", "evaluation");

/**
 * @const EVALEXPORT_PATH_TEMP The path for temporary files
 * @access public
 */
define ("EVALEXPORT_PATH_TEMP", $GLOBALS['TMP_PATH']);

/**
 * @const EVALEXPORT_LIFETIME The lifetime in seconds for temporary files
 * @access public
 */
define ("EVALEXPORT_LIFETIME", 1800);
# ===================================================== end: define constants #


/**
 * The mainclass for the evaluation export manager
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvaluationExportManager extends AuthorObject {
# Define all required variables ============================================= #
  /**
   * The temporary filename
   * @access   private
   * @var      string $filename
   */
  var $filename;

  /**
   * The filehandle for the temporary filename
   * @access   private
   * @var      integer $filehandle
   */
  var $filehandle;

  /**
   * The ID for the evaluation to export
   * @access   private
   * @var      string $evalID
   */
  var $evalID;

  /**
   * The evaluation to export
   * @access   private
   * @var      object   Evaluation   $eval
   */
  var $eval;

  /**
   * Array full of questionobjects
   * @access   private
   * @var      array $evalquestions
   */
  var $evalquestions;

  /**
   * The extension for the FILENAME
   * @access   private
   * @var      string $extension
   */
  var $extension;

  /**
   * UserIDs of all persons which used the evaluation
   * @access   private
   * @var      array $users
   */
  var $users;
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
    * Constructor
    * @access   public
    * @param    string   $evalID   The ID of the evaluation for export
    */
   function __construct($evalID) {
    /* Set default values ------------------------------------------------- */
    register_shutdown_function([&$this, "_EvaluationExportManager"]);

    parent::__construct();
    $this->setAuthorEmail ("mail@AlexanderWillner.de");
    $this->setAuthorName ("Alexander Willner");
    $this->instanceof = INSTANCEOF_EVALEXPORTMANAGER;

    $this->filename      = "";
    $this->filehandle    = "";
    $this->evalID        = $evalID;
    $this->eval          = new Evaluation ($evalID, NULL, EVAL_LOAD_FIRST_CHILDREN);
    $this->evalquestions =  [];
    $this->extension     = EVALEXPORT_EXTENSION;

    $this->createNewFile ();
    $this->getQuestionobjects ($this->eval);
    /* -------------------------------------------------------------------- */
  }

  /**
    * Destructor. Closes all files and removes old temp files
    * @access   public
    */
  function _EvaluationExportManager () {
      $this->closeFile ();
      $this->cleanUp ();
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
   /**
    * Returns the temnporary filename
    * @access   public
    * @returns  string   The temporary filename
    */
   function getTempFilename () {
       return $this->filename;
   }

   /**
    * Exports the evaluation
    * @access   public
    */
   function export () {
      if (empty ($this->filehandle))
         return $this->throwError (1, _("ExportManager::Konnte temporäre Datei nicht öffnen."));

      if (!$this->eval->isAnonymous ()) {
         $this->users = EvaluationDB::getUserVoted ($this->eval->getObjectID ());
      } else {
         $questions = $this->eval->getSpecialChildobjects ($this->eval, INSTANCEOF_EVALQUESTION);
         $questionIDs =  [];
         foreach ($questions as $question) {
            array_push ($questionIDs , $question->getObjectID ());
         }
         $this->users = EvaluationDB::getUserVoted ($this->eval->getObjectID (), null ,$questionIDs );
      }

      if (empty ($this->users))
         return $this->throwError (1, _("ExportManager::Es haben noch keine Benutzer abgestimmt oder angegebene Evaluation existiert nicht."));
   }

   /**
    * Gets the filname for the user
    * @access   public
    */
   function getFilename () {
      return (rawurlencode($this->eval->getTitle ()).".".$this->extension);
   }
# ===================================================== end: public functions #

# Define private functions ================================================== #
   /**
    * Gets all questionobjects of the evaluation
    * @access   private
    * @param    EvaluationObject   &$object   An evaluationobject object
    */
   function getQuestionobjects (&$object) {
      if ($object->x_instanceof () == INSTANCEOF_EVALQUESTION) {
         array_push ($this->evalquestions, $object);
      } else {
         while ($child = $object->getNextChild ()) {
            $this->getQuestionobjects ($child);
         }
      }
   }

   /**
    * Closes all opened files
    * @access   public
    */
   function closeFile () {
      if (empty ($this->filehandle))
         return $this->throwError (1, _("ExportManager::Konnte temporäre Datei nicht schließen."));

       fclose ($this->filehandle);
   }

   /**
    * Removes old temporary files
    * @access   private
    */
   function cleanUp () {
      if (empty ($this->filehandle))
         return $this->throwError (1, _("ExportManager::Konnte temporäre Datei nicht öffnen."));

      $dirhandle = dir (EVALEXPORT_PATH_TEMP);
      while (($file = $dirhandle->read()) != false) {
         $file = EVALEXPORT_PATH_TEMP."/".$file;
         $part = pathinfo ($file);

         if (filemtime ($file) < (time () - EVALEXPORT_LIFETIME) &&
             $part["extension"] == $this->extension &&
             mb_substr ($part["basename"], 0, mb_strlen (EVALEXPORT_PREFIX)) == EVALEXPORT_PREFIX)
            unlink ($file);
      }
      $dirhandle->close();
   }

   /**
    * Creates a new temporary file
    * @access   public
    */
   function createNewFile () {
      $randomID       = StudipObject::createNewID ();
      $this->filename = $randomID.".".$this->extension;
      export_tmp_gc();
      if (!is_dir (EVALEXPORT_PATH_TEMP))
         return $this->throwError (1, sprintf (_("ExportManager::Das Verzeichnis %s existiert nicht."), EVALEXPORT_PATH_TEMP));
      if (!is_writable (EVALEXPORT_PATH_TEMP))
         return $this->throwError (2, sprintf (_("ExportManager::Das Verzeichnis %s ist nicht schreibbar nicht."), EVALEXPORT_PATH_TEMP));

      $this->filehandle = @fopen (EVALEXPORT_PATH_TEMP."/".$this->filename,"w");

      if (empty ($this->filehandle))
         return $this->throwError (3, _("ExportManager::Konnte temporäre Datei nicht erstellen."));
   }
# ==================================================== end: private functions #

}

?>
