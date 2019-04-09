<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Overview of all existing evaluations
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
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

# PHP-LIB: open session ===================================================== #
// page_open (array ("sess" => "Seminar_Session",
//         "auth" => "Seminar_Auth",
//         "perm" => "Seminar_Perm",
//         "user" => "Seminar_User"));
// $auth->login_if ($auth->auth["uid"] == "nobody");
// $perm->check ("autor");
# ============================================================== end: PHP-LIB #


# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_LIB_COMMON;
require_once EVAL_LIB_OVERVIEW;
require_once EVAL_FILE_EVAL;
require_once EVAL_FILE_EVALDB;
# ====================================================== end: including files #

define ("DISCARD_OPENID", "discard_openid");

/* Create objects ---------------------------------------------------------- */
$db  = new EvaluationObjectDB ();
if ($db->isError)
  return EvalCommon::showErrorReport ($db, _("Datenbankfehler"));
$lib = new EvalOverview ($db, $perm, $user);
/* ------------------------------------------------------------ end: objects */


/* Set variables ----------------------------------------------------------- */
if( $_SESSION['evalID'] )   unset($_SESSION['evalID']);
if( $_SESSION['rangeID'] )  unset($_SESSION['rangeID']);

if (!empty($the_range))
     $rangeID = $the_range;

$rangeID = ($rangeID) ? $rangeID : Context::getId();

if (empty ($rangeID) || ($rangeID == get_username ($user->id)))
     $rangeID = $user->id;
$_SESSION['rangeID'] = $rangeID;
$debug = 0;

$evalAction = $lib->getPageCommand();

$openID = Request::option("openID");
$evalID = Request::option("evalID");
$search = Request::get("search"); // range
$templates_search = Request::get("templates_search");
$search = $templates_search;
/* ---------------------------------------------------------- end: variables */

/* Javascript function ----------------------------------------------------- */
$js = EvalCommon::createEvalShowJS( YES );
echo $js->createContent();

/* Maintable with white border --------------------------------------------- */
$table = $lib->createMainTable ();
/* -----------------------------------------------------------end: maintable */

/* Check permissions and call safeguard ------------------------------------ */
if (! ($perm->have_studip_perm ("tutor", $rangeID)) &&
       $user->id != $rangeID ) {
    $safeguard = $lib->createSafeguard("ausruf", sprintf(_("Sie haben keinen Zugriff auf diesen Bereich.")));
    $table->addContent ($lib->createHeader ($safeguard));
    echo $table->createContent ();
    return;
}

$safeguard = $lib->callSafeguard( $evalAction, $evalID, $rangeID,
              $search, $referer );
/* ---------------------------------------------------------- end: safeguard */

/* found public templates -------------------------------------------------- */
if ( $templates_search ) {
   $search = trim ($search);
   $evalIDArray = $db->getPublicTemplateIDs ($search);
   if (mb_strlen ($search) >= EVAL_MIN_SEARCHLEN && !empty ($evalIDArray) ) {
      $foundTable = new HTML ("table");
      $foundTable->addAttr ("border","0");
      $foundTable->addAttr ("align", "center");
      $foundTable->addAttr ("cellspacing", "0");
      $foundTable->addAttr ("cellpadding", "0");
      $foundTable->addAttr ("width", "100%");
      $foundTr = new HTML ("tr");
      $foundTd = new HTML ("td");
      $foundTd->addAttr ("align", "left");
      $foundTd->addAttr ("colspan", "10");
      $foundTd->addContent (new HTMLempty ("br"));

      $b = new HTML ("b");
      $b->addContent(_("Gefundene öffentliche Evaluationsvorlagen:"));
      $foundTd->addContent ($b);
      $foundTr->addContent ($foundTd);
      $foundTable->addContent ($foundTr);

      $foundTable->addContent ($lib->createGroupTitle ( [
                         " ",
                         _("Titel"),
#                        " ",
                         _("Autor"),
                         _("Letzte Änderung"),
                         _("Anonym"),
                         "",
                         _("Ansehen"),
                         _("Kopieren"),
                        " "
                         ], YES, "public_template"));
      foreach ($evalIDArray as $number => $evalID) {
         $eval = new Evaluation ($evalID);
         $foundTable->addContent ($lib->createEvalRow ($eval, $number, "public_template", NO, YES));
      }
   }
}
/* --------------------------------------------- end: found public templates */

/* Own templates ----------------------------------------------------------- */
$evalIDArray = $db->getEvaluationIDs ();

$templateTable = new HTML ("table");
$templateTable->addAttr ("border","0");
#$templateTable->addAttr ("style","border:1px solid black");
$templateTable->addAttr ("align", "center");
$templateTable->addAttr ("cellspacing", "0");
$templateTable->addAttr ("cellpadding", "2");
$templateTable->addAttr ("width", "100%");
$templateTr = new HTML ("tr");
$templateTd = new HTML ("td");
$templateTd->addAttr ("colspan", "7");

$b = new HTML ("h2");
$b->addContent(_("Eigene Evaluationsvorlagen:"));
$templateTd->addContent ($b);
$templateTr->addContent ($templateTd);
$templateTable->addContent ($templateTr);

if (!empty ($evalIDArray)) {
   $templateTable->addContent ($lib->createGroupTitle ( [
                  " ",
                  _("Titel"),
                  _("Freigeben"),
                  " ",
                  " ",
                  " ",
                  _("Bearbeiten"),
                  _("Löschen")], YES, "user_template" ));
   foreach ($evalIDArray as $number => $evalID) {
      $eval = new Evaluation ($evalID);
      $open = ($openID == $evalID);
      $templateTable->addContent ($lib->createEvalRow ($eval, $number, "user_template", $open, YES));
      if ($open) {
         $tr = new HTML ("tr");
         $td = new HTML ("td");
         $td->addAttr ("colspan", "10");
         $td->addContent ($lib->createEvalContent ($eval, $number, "user_template", $safeguard));
         $tr->addContent($td);
         $templateTable->addContent ($tr);
      }
    }
} else {
      $tr = new HTML ("tr");
      $td = new HTML ("td");
      $td->addAttr ("colspan", "10");
      $td->addContent ($lib->createInfoCol (_("Keine eigenen Evaluationsvorlagen vorhanden.")));
      $tr->addContent($td);
      $templateTable->addContent ($tr);
}

/* ------------------------------------------------------ end: own templates */



/* Create header with logo and safeguard messages -------------------------- */
if ( is_array($safeguard) ){
   if ($safeguard["option"] == DISCARD_OPENID)
      $openID = NULL;
   $safeguard = $safeguard["msg"];
}

if( empty($openID) ) {
    $table->addContent ($lib->createHeader ($safeguard, $templateTable, $foundTable));
} else {
    $table->addContent ($lib->createHeader (" ", $templateTable, $foundTable));
}
/* ------------------------------------------------------------- end: header */

$table->addContent ($lib->createClosingRow());
/* ---------------------------------------------------------- end: templates */


/* Create line with informations ------------------------------------------- */
$tr = new HTML ("tr");
$td = new HTML ("td");
$td->addAttr ("class", "blank");




if ($lib->db->getGlobalPerm() != "autor") {
   $td->addContent ($lib->createShowRangeForm ());
} else {
   $td->addHTMLContent ("Evaluationen aus dem Bereich \"".
         htmlReady($db->getRangename ($rangeID))."\":");
   $td->addContent (new HTMLempty ("br"));
}
$td->addContent (new HTMLempty ("br"));


$tr->addContent ($td);
$table->addContent ($tr);
/* ----------------------------------------------------------- end: infoline */

/* Show showrange search results ------------------------------------------- */
if( $evalAction == "search_showrange" && Request::get("search") ) {
    $tr = new HTML ("tr");
    $td = new HTML ("td");
    $td->addAttr ("class", "blank");
    $td->addAttr ("align", "left");
    $td->addContent (new HTMLempty ("br"));
    $b = new HTML ("b");
    $line = new HTMLempty ("hr");
    $line->addAttr ("size", "1");
    $line->addAttr ("noshade", "noshade");
#$td->addContent ($line);
    $b->addContent(_("Suchergebnisse:"));
    $td->addContent ($b);

    $td->addHTMLContent ($lib->createDomainLinks (Request::get("search")));
    $tr->addContent ($td);
    $table->addContent ($tr);
    $table->addContent ($lib->createClosingRow());
    echo $table->createContent();
    return;
}
/* -------------------------------------- end: Show showrange search results */

/* Show not started evaluations -------------------------------------------- */
$evalIDArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_NEW);

$tr = new HTML ("tr");
$td = new HTML ("td");
$td->addAttr ("class", "blank");
$b = new HTML ("b");
$b->addContent(_("Noch nicht gestartete Evaluationen: "));
$td->addContent ($b);

if (!empty ($evalIDArray)) {
   $td->addContent ($lib->createGroupTitle ( [_("Titel"),
                     _("Autor"),
                     _("Startdatum"),
                     _("Status"),
                     "",
                     _("Bearbeiten"),
                     _("Löschen"),
             ""]));
   foreach ($evalIDArray as $number => $evalID) {
      $eval = new Evaluation ($evalID);
      $open = ($openID == $evalID);
      $td->addContent ($lib->createEvalRow ($eval, $number, EVAL_STATE_NEW, $open));
      if ($open)
         $td->addContent ($lib->createEvalContent ($eval, $number, EVAL_STATE_NEW, $safeguard));
   }

} else {
   $td->addContent ($lib->createInfoCol (_("Keine neuen Evaluationen vorhanden.")));
}
$tr->addContent ($td);
$table->addContent ($tr);
$table->addContent ($lib->createClosingRow());
/* -------------------------------------------------------- end: not started */


/* Show running evaluations ------------------------------------------------ */
$evalIDArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_ACTIVE);

$tr = new HTML ("tr");
$td = new HTML ("td");
$td->addAttr ("class", "blank");
$td->addContent (new HTMLEmpty("br"));
$b = new HTML ("b");
$b->addContent(_("Laufende Evaluationen:"));
$td->addContent ($b);
if (!empty ($evalIDArray)) {
   $td->addContent ($lib->createGroupTitle ( [_("Titel"),
                  _("Autor"),
                  _("Ablaufdatum"),
                  _("Status"),
                  "",
                  _("Exportieren"),
                  _("Löschen"),
          _("Auswertung")]));
   foreach ($evalIDArray as $number => $evalID) {
      $eval = new Evaluation ($evalID);
      $open = ($openID == $evalID);
      $td->addContent ($lib->createEvalRow ($eval, $number, EVAL_STATE_ACTIVE, $open));
      if ($open)
         $td->addContent ($lib->createEvalContent ($eval, $number, EVAL_STATE_ACTIVE, $safeguard));
   }
} else {
    $td->addContent ($lib->createInfoCol (_("Keine laufenden Evaluationen vorhanden.")));
}
$tr->addContent ($td);
$table->addContent ($tr);
$table->addContent ($lib->createClosingRow());
/* ------------------------------------------------------------ end: running */


/* Show stopped evaluations ------------------------------------------------ */
$evalIDArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_STOPPED);
$tr = new HTML ("tr");
$td = new HTML ("td");
$td->addAttr ("class", "blank");
$td->addContent (new HTMLEmpty("br"));
$b = new HTML ("b");
$b->addContent(_("Beendete Evaluationen:"));
$td->addContent ($b);

if (!empty ($evalIDArray)) {
   $td->addContent ($lib->createGroupTitle ( [_("Titel"),
                  _("Autor"),
                  "",
                  _("Status"),
                  "",
                  _("Exportieren"),
                  _("Löschen"),
          _("Auswertung")]));
   foreach ($evalIDArray as $number => $evalID) {
      $eval = new Evaluation ($evalID);
      $open = ($openID == $evalID);
      $td->addContent ($lib->createEvalRow ($eval, $number, EVAL_STATE_STOPPED, $open));
      if ($open)
         $td->addContent ($lib->createEvalContent ($eval, $number, EVAL_STATE_STOPPED, $safeguard));
    }
} else {
   $td->addContent ($lib->createInfoCol (_("Keine gestoppten Evaluationen vorhanden.")));
}
$tr->addContent ($td);
$table->addContent ($tr);
$table->addContent ($lib->createClosingRow());
/* ------------------------------------------------------------ end: stopped */

echo $table->createContent ();


if ($debug) {
    echo "<pre>";
    echo "rangeid = $rangeID\n";
    echo "<font color=red>Nach Evaluationen suchen...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID);
    echo "ed(n) ".count($evalArray)." Evaluation(en) gefunden...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_NEW);
    echo "Es wurde(n) ".count($evalArray)." neue Evaluation(en) gefunden...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_ACTIVE);
    echo "Es wurde(n) ".count($evalArray)." laufende Evaluation(en) gefunden...</font><br>";
    $evalArray = $db->getEvaluationIDs ($rangeID, EVAL_STATE_STOPPED);
    echo "Es wurde(n) ".count($evalArray)." gestoppte Evaluation(en) gefunden...</font><br>";

    echo EvalCommon::createErrorReport($db);

    print_r($_POST);
}


?>
