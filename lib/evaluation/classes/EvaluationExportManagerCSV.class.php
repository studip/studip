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
require_once EVAL_FILE_EXPORTMANAGER;
# ====================================================== end: including files #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_EVALEXPORTMANAGER Is instance of an export manager
 * @access public
 */
define ("INSTANCEOF_EVALEXPORTMANAGERCSV", "EvaluationExportManagerCSV");

/**
 * @const EVALEXPORT_SEPERATOR The seperator for values
 * @access public
 */
define ("EVALEXPORT_SEPERATOR", ";");

/**
 * @const EVALEXPORT_DELIMITER The delimiter for values
 * @access public
 */
define ("EVALEXPORT_DELIMITER", "\"");

/**
 * @const EVALEXPORT_NODELIMITER Character to substitute the delimiter in a text
 * @access public
 */
define ("EVALEXPORT_NODELIMITER", "'");

/**
 * @const EVALEXPORT_ENDROW The characters to end a row
 * @access public
 */
define ("EVALEXPORT_ENDROW", EVALEXPORT_DELIMITER.EVALEXPORT_DELIMITER."\n");

/**
 * @const EVALEXPORT_EXTENSION The extension for the filenames
 * @access public
 */
define ("EVALEXPORT_EXTENSION", "csv");
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
class EvaluationExportManagerCSV extends EvaluationExportManager {
# Define all required variables ============================================= #

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
    * Constructor
    * @access   public
    * @param    string   $evalID   The ID of the evaluation for export
    */
   function __construct($evalID) {
    /* Set default values ------------------------------------------------- */
    register_shutdown_function([&$this, "_EvaluationExportManagerCSV"]);
    ini_set('memory_limit', '256M');
    parent::__construct($evalID);
    $this->setAuthorEmail ("mail@AlexanderWillner.de");
    $this->setAuthorName ("Alexander Willner");
    $this->instanceof = INSTANCEOF_EVALEXPORTMANAGERCSV;

    $this->extension     = EVALEXPORT_EXTENSION;
    /* -------------------------------------------------------------------- */
  }

  /**
    * Destructor. Closes all files and removes old temp files
    * @access   public
    */
  function _EvaluationExportManagerCSV () {

  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
   /**
    * Exports the evaluation
    * @access   public
    */
   function export () {
      parent::export ();
      if ($this->isError ())
         return;
      $this->exportHeader ();
      $this->exportContent();
   }

   /**
    * Exports the headline
    * @access   public
    */
   function exportHeader () {
      if (empty ($this->filehandle))
         return $this->throwError (1, _("ExportManager::Konnte temporäre Datei nicht öffnen."));
      fputs ($this->filehandle, "\xEF\xBB\xBF");
      fputs ($this->filehandle, EVALEXPORT_DELIMITER . _("Nummer") . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
      fputs ($this->filehandle, EVALEXPORT_DELIMITER . _("Datum") . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
      fputs ($this->filehandle, EVALEXPORT_DELIMITER . _("Benutzername") . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
      fputs ($this->filehandle, EVALEXPORT_DELIMITER . _("Nachname") . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
      fputs ($this->filehandle, EVALEXPORT_DELIMITER . _("Vorname") . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
      fputs ($this->filehandle, EVALEXPORT_DELIMITER . _("Email") . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);

      $db      = new EvaluationAnswerDB ();

      /* for each question -------------------------------------------------- */
      foreach ($this->evalquestions as $evalquestion) {
         $type     = $evalquestion->getType ();
         $residual = "";

         /* Questiontype: likert scale -------------------------------------- */
         if ($type == EVALQUESTION_TYPE_LIKERT) {
             $db->addChildren($evalquestion);
            $header = $evalquestion->getText ().":";
            while ($answer = &$evalquestion->getNextChild ()) {
               if ($answer->isResidual ()) {
                  $residual = $evalquestion->getText ().":".$answer->getText ();
               } else {
                  $header .= $answer->getText ();
                  $header .= "(".$answer->getPosition ().")";
                  $header .= ",";
               }
            }
            $header = mb_substr ($header, 0, mb_strlen ($header) - 1);

            $this->addCol ($header);

            if (!empty ($residual)) {
               $this->addCol ($residual);
               $this->evalquestions_residual[$evalquestion->getObjectID()] = true;
            }
         /* ----------------------------------------------------- end: likert */


         /* Questiontype: pol scale ----------------------------------------- */
         } elseif ($type == EVALQUESTION_TYPE_POL) {
            $db->addChildren($evalquestion);
            $header = $evalquestion->getText ().":";
            $answer = $evalquestion->getNextChild ();
            $header .= $answer->getText ();
            $header .= "(".$answer->getPosition ().")";
            $header .= "-";
            while ($answer = &$evalquestion->getNextChild ()) {
               if ($answer->isResidual ())
                  $residual = $evalquestion->getText ().":".$answer->getText ();
               else
                  $last = $answer->getText ()."(".$answer->getPosition ().")";
            }
            $header .= $last;
            $this->addCol ($header);
            if (!empty ($residual)) {
               $this->addCol ($residual);
               $this->evalquestions_residual[$evalquestion->getObjectID()] = true;
            }
         /* -------------------------------------------------------- end: pol */


         /* Questiontype: multiple chioice ---------------------------------- */
         } elseif ($type == EVALQUESTION_TYPE_MC) {
            if ($evalquestion->isMultiplechoice ()) {
                $db->addChildren($evalquestion);
               while ($answer = &$evalquestion->getNextChild ()) {
                  $header = $evalquestion->getText ();
                  $header .= ":".$answer->getText ();
                  $this->addCol ($header);
               }
            } else {
               $header = $evalquestion->getText ();
               $this->addCol ($header);
            }
         /* --------------------------------------------------------- end: mc */


         /* Questiontype: undefined ----------------------------------------- */
         } else {
            return $this->throwError (2, _("ExportManager::Ungültiger Typ."));
         }
         /* -------------------------------------------------- end: undefined */
      }
      /* ---------------------------------------------- end: foreach question */

      fputs ($this->filehandle, EVALEXPORT_ENDROW);
   }

   /**
    * Exports the content
    * @access   public
    */
   function exportContent () {
      $counter = 0;
      $answers = [];
      $db = DBManager::get();
      $stmt = $db->prepare("SELECT user_id,text,value,position,residual,
                    MAX(evaldate) as evaldate,
                    GROUP_CONCAT(evalanswer_id) as evalanswer_id
                    FROM evalanswer
                    INNER JOIN evalanswer_user
                    USING ( evalanswer_id )
                    WHERE parent_id = ? GROUP BY user_id");
      foreach ($this->evalquestions as $evalquestion) {
          $stmt->execute([$evalquestion->getObjectID()]);
          $answers[$evalquestion->getObjectID()] = $stmt->fetchGrouped();
      }

      /* One row for each user --------------------------------------------- */
      foreach ($this->users as $userID) {

         /* Userinformation if available ----------------------------------- */
         $username = "";
         $name     = "";
         $surname  = "";
         $email    = "";
         $evaldate = "";
         if (!$this->eval->isAnonymous ()) {
             $data = DBManager::get()->query("SELECT username, Vorname, Nachname, Email "
                   . "FROM auth_user_md5 WHERE user_id = "
                   . DBManager::get()->quote($userID))->fetchAll(PDO::FETCH_NUM);
             if (is_array($data[0])) {
                 list($username, $name, $surname, $email) = $data[0];
             }
         }
         if ($timestamp = $answers[$this->evalquestions[0]->getObjectID()][$userID]['evaldate']) {
             $evaldate = date('Y-m-d H:i:s', $timestamp);
         }
         fputs ($this->filehandle, EVALEXPORT_DELIMITER . ++$counter . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
         fputs ($this->filehandle, EVALEXPORT_DELIMITER . $evaldate . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
         fputs ($this->filehandle, EVALEXPORT_DELIMITER . $username . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
         fputs ($this->filehandle, EVALEXPORT_DELIMITER . $surname . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
         fputs ($this->filehandle, EVALEXPORT_DELIMITER . $name . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
         fputs ($this->filehandle, EVALEXPORT_DELIMITER . $email . EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);

         /* ------------------------------------------------- end: user info */

         /* One colum for each question ------------------------------------ */
         foreach ($this->evalquestions as $evalquestion) {
            $type = $evalquestion->getType ();

            /* Questiontype: pol or likert scale --------------------------- */
            if ($type == EVALQUESTION_TYPE_LIKERT ||
                $type == EVALQUESTION_TYPE_POL) {
               $hasResidual = $this->evalquestions_residual[$evalquestion->getObjectID()];
               $entry       = "";
               $residual    = 0;
               if ($answer = $answers[$evalquestion->getObjectID()][$userID]) {
                   if ($answer['residual']) {
                       $residual = 1;
                   } else {
                       $entry = $answer['position'];
                   }
               }
               $this->addCol ($entry);

               if ($hasResidual) {
                  $this->addCol ($residual);
               }
            }
            /* ------------------------------------------------- end: likert */


            /* Questiontype: multiple chioice ------------------------------ */
            elseif ($type == EVALQUESTION_TYPE_MC) {
                if ($evalquestion->isMultiplechoice ()) {
                    $mc_answers = explode(',', $answers[$evalquestion->getObjectID()][$userID]['evalanswer_id']);
                    while ($answer = &$evalquestion->getNextChild ()) {
                        $this->addCol ((int)in_array($answer->getObjectID(), $mc_answers));
                    }
                } else {
                    $entry = "";
                    if ($answer = $answers[$evalquestion->getObjectID()][$userID]) {
                        $entry = preg_replace ("(\r\n|\n|\r)", " ", $answer['text']);
                    }
                    $this->addCol ($entry);
                }
            }
            /* ------------------------------------------------------ end: mc */


            /* Questiontype: undefined -------------------------------------- */
            else {
               return $this->throwError (1, _("ExportManager::Ungültiger Fragetyp."));
            }
            /* ----------------------------------------------- end: undefined */
         }
         /* ------------------------------------------ end: col for question */

         fputs ($this->filehandle, EVALEXPORT_ENDROW);
      }
      /* -------------------------------------------- end: row for each user */
   }
# ===================================================== end: public functions #

# Define private functions ================================================== #
   /**
    * Adds a row for the text
    * @param    string   $text   The text for the row
    * @access   private
    */
   function addCol ($text) {
      $col = str_replace (EVALEXPORT_DELIMITER, EVALEXPORT_NODELIMITER, $text);
      fputs ($this->filehandle, EVALEXPORT_DELIMITER.$col.EVALEXPORT_DELIMITER.EVALEXPORT_SEPERATOR);
   }
# ==================================================== end: private functions #

}

?>
