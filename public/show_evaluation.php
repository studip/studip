<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * the evaluation participation page :)
 *
 * @author      mcohrs <michael A7 cohrs D07 de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


//TODO: auf TemplateFactory umstellen

# PHP-LIB: open session ===================================================== #

require '../lib/bootstrap.php';

page_open ( ["sess" => "Seminar_Session",
          "auth" => "Seminar_Auth",
          "perm" => "Seminar_Perm",
          "user" => "Seminar_User"]);
$auth->login_if ($auth->auth["uid"] == "nobody");
$perm->check ("autor");
# ============================================================== end: PHP-LIB #

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once 'lib/seminar_open.php';

require_once EVAL_FILE_EVAL;
require_once EVAL_FILE_EVALDB;
require_once EVAL_FILE_SHOW_TREEVIEW;
require_once EVAL_FILE_EVALTREE;

require_once EVAL_LIB_COMMON;
require_once EVAL_LIB_SHOW;
# ====================================================== end: including files #

/* Create objects ---------------------------------------------------------- */
$db  = new EvaluationDB();
$lib = new EvalShow();
/* ------------------------------------------------------------ end: objects */

#error_reporting( E_ALL & ~E_NOTICE );

/* Set variables ----------------------------------------------------------- */
$rangeID = Request::option('rangeID',Context::getId());
if (empty ($rangeID)) {
    $rangeID = $user->id; }

$evalID = Request::option('evalID');
$tree = new EvaluationTreeShowUser( $evalID );

$eval = $tree->tree->eval;
$evalDB = new EvaluationDB();

$isPreview = Request::option('isPreview') ? YES : NO;

$votedEarlier = $eval->hasVoted( $auth->auth["uid"] ) && $isPreview == NO;
$votedNow = Request::submitted('voteButton') && $votedEarlier == NO;

if ( $eval->isAnonymous() )
   $userID = StudipObject::createNewID ();
else
   $userID = $auth->auth["uid"];
/* ---------------------------------------------------------- end: variables */

$br = new HTMpty( "br" );

/* Surrounding Form -------------------------------------------------------- */
$form = new HTM( "form" );
$form->attr( "action", URLHelper::getLink(Request::url()) );
$form->attr( "method", "post" );
$form->html(CSRFProtection::tokenTag());

if (Request::isXHR()) {
    PageLayout::setTitle(_("Stud.IP Online-Evaluation"));
} else {
    // TODO: This should use Assets::img() but on the other hand it should also use templates
    $titlebar = EvalCommon::createTitle( _("Stud.IP Online-Evaluation"), Icon::create('test', 'info_alt')->asImagePath() );
    $form->cont( $titlebar );
}
/* Surrounding Table ------------------------------------------------------- */
$table = new HTM( "table" );
$table->attr( "border","0" );
$table->attr( "align", "center" );
$table->attr( "cellspacing", "0" );
$table->attr( "cellpadding", "3" );
$table->attr( "width", "100%" );
$table->attr( "class", "table_row_even" );

/* count mandatory items */
$mandatories = checkMandatoryItems( $eval );
$answers = Request::quotedArray('answers');
/* ------------------------------------------------------------------------- */
if( $votedNow ) {
    $answers = Request::quotedArray('answers');
    $freetexts = Request::quotedArray('freetexts');
    if( ! ( is_array($answers) ||
        /* clicked no answer */
        (is_array($freetexts) && implode("", $freetexts) != "")
        /* typed no freetext */
        )
    ) {

    $eval->throwError( 1, _("Sie haben keine Antworten gewählt.") );
    $votedNow = NO;

    }

    /* check if mandatory answers are missing */
    if( count($mandatories) > 0 ) {
    $eval->throwError( 1, sprintf(_("Sie haben %s erforderliche Fragen nicht beantwortet. Diese wurden gesondert markiert."),
                      count($mandatories)) );
    $votedNow = NO;
    }
}

if( $votedNow ) {
    /* the vote was OK */

    /* process the user's selected answers --------------------------------- */
    if( is_array($answers) ) {
    foreach( $answers as $question_id => $answer ) {
        if( is_array($answer) )
        /* multiple choice question */
        foreach( $answer as $nr => $answer_id )
            voteFor( $answer_id );
        else
        /* answer = answer_id */
        voteFor( $answer );
    }
    }

    /* process the user's typed-in answers --------------------------------- */
    $freetexts = Request::quotedArray('freetexts');
    if( is_array($freetexts) ) {
    foreach( $freetexts as $question_id => $text ) {
        if( trim($text) != '' ) {
        $question = new EvaluationQuestion( $question_id );
        $answer = new EvaluationAnswer();
        $answer->setText( $text );
        $answer->setRows( 1 );
        $answer->vote( $GLOBALS["userID"] );
        $question->addChild( $answer );
        $question->save();
        $debug .= "added answer text <b>".$answer->getText().
            "</b> for question <b>".$question->getText()."</b>\n";
        }
    }
    }

    /* connect user with eval */
    $evalDB->connectWithUser( $evalID, $auth->auth["uid"] );

    /* header ------ */
    $table->cont( $lib->createEvaluationHeader( $eval, $votedNow, $votedEarlier ) );

} elseif( $votedEarlier ) {
    /* header ------ */
    $table->cont( $lib->createEvaluationHeader( $eval, $votedNow, $votedEarlier ) );

} else {
    /* header ------ */
    $table->cont( $lib->createEvaluationHeader( $eval, $votedNow, $votedEarlier ) );

    /* the whole evaluation ------ */
    $table->cont( $lib->createEvaluation( $tree ) );
}

/* footer ------ */
$table->cont( $lib->createEvaluationFooter( $eval, $votedNow || $votedEarlier, $isPreview ) );

$form->cont( $table );

/* Ausgabe erzeugen---------------------------------------------------------- */
//Content (TODO: besser mit TemplateFactory)
if (Request::isXHR()) {
    echo $form->createContent();
} else {
    $layout = $GLOBALS['template_factory']->open('layouts/base.php');
    $layout->content_for_layout = $form->createContent();
    echo $layout->render();
}

page_close();


 /**
  * checkMandatoryItems:
  * put IDs of mandatory questions into global array $mandatories
  *  (or, if the user has voted, the IDs of the mandatory questions, which he did not answer to)
  *
  * @param object  the Evaluation object (when called externally).
  */
 function checkMandatoryItems( $item )
 {
     global $mandatories;

     if( $children = $item->getChildren() )
     {
        foreach( $children as $child )
        {
         checkMandatoryItems( $child );
        }
     }

     if( $item->x_instanceof() == INSTANCEOF_EVALQUESTION )
     {
        $group = $item->getParentObject();
        $answers = Request::quotedArray('answers');
        $freetexts = Request::quotedArray('freetexts');
        if( $group->isMandatory() &&
         ( ! is_array($answers) ||
           ( is_array($answers) &&
         ! in_array($item->getObjectID(), array_keys($answers)) )
           ) &&
         trim($freetexts[$item->getObjectID()]) == ''
         )
         {
             $mandatories[] = $item->getObjectID();
         }
     }
     return $mandatories ?: [];

 }


 /**
  * vote for an answer of given ID
  * @param string  the ID.
  */
 function voteFor( $answer_id )
 {
    global $debug;
    global $userID;

    $answer = new EvaluationAnswer( $answer_id );
    $answer->vote($userID);

    $answer->save();

    $debug .= "voted for answer <b>".$answer->getText()."</b> (".
    $answer->getObjectID().")\n";
}

?>
