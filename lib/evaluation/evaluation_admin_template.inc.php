<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * the form to create/edit templates for answers
 *
 * @author  JPWowra
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

use Studip\Button, Studip\LinkButton;

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once 'lib/evaluation/classes/db/EvaluationQuestionDB.class.php';
require_once 'lib/evaluation/classes/EvaluationQuestion.class.php';
require_once EVAL_LIB_COMMON;
require_once EVAL_LIB_OVERVIEW;
require_once EVAL_LIB_TEMPLATE;
require_once EVAL_FILE_EVAL;
require_once EVAL_FILE_EVALDB;
require_once EVAL_FILE_QUESTION;
require_once EVAL_FILE_QUESTIONDB;
require_once EVAL_FILE_OBJECTDB;
# ====================================================== end: including files #


/* Create objects ---------------------------------------------------------- */
$db  = new EvaluationQuestionDB();
$lib = new EvalTemplateGUI();
/* ------------------------------------------------------------ end: objects */

#error_reporting( E_ALL );

/* Set variables ----------------------------------------------------------- */
$rangeID = ($rangeID) ? $rangeID : Context::getId();

if (empty ($rangeID)) {
    $rangeID = $user->id;
}

$command = $lib->getPageCommand();
$odb = new EvaluationObjectDB();
if($odb->getGlobalPerm()=="root"){
   $myuserid = 0;
}
else{
   $myuserid = $user->id;

}
if(!$parentID){
   $parentID = Request::option('parentID');
}
$template_name = Request::get('template_name',$template_name);
$template_type = Request::get('template_type') ? Request::get('template_type') : $template_type;
$template_multiple = Request::get('template_multiple',$template_multiple) ;
$template_answers = Request::getArray('template_answers');
$template_add_num_answers = Request::get('template_add_num_answers') ? Request::get('template_add_num_answers') : $template_add_num_answers;
if( empty($template_answers) ) {
    if( mb_strstr( $command, "edit" ))
   for( $i=0; $i<5; $i++ )
       $template_answers[$i] = $lib->makeNewAnswer();
    else
   $template_answers = [];
}
if(!$template_id){
      $template_id = Request::option('template_id');
}

/* ---------------------------------------------------------- end: variables */

if( Request::option('onthefly') && ($command == "delete" || $command == "add_answers"
       || $command == "delete_answers" || $command == "save2")){
   $question = new EvaluationQuestion ($template_id,
                    NULL, EVAL_LOAD_ALL_CHILDREN);
   $question->setMultiplechoice($template_multiple);
   $question->setText(trim($template_name), YES);
   $question->setType($template_type);
   $question->setParentID($parentID);
   $question->setPosition($template_position);
   while ($answerrem = $question->getChild ()){
      $id=$answerrem->getObjectID();
      $answerrem->delete ();
      $question->removeChildID($id);
   }
   $controlnumber = count($template_answers);
   for( $i=0; $i < $controlnumber; $i++ ) {
      $text = $template_answers[$i]['text'];
    $answerID = $template_answers[$i]['answer_id'];
    $answer =  new EvaluationAnswer();
    $answer->setObjectID($answerID);
    $answer->setText(trim($text), YES);
    $answer->setParentID($template_id);
    $question->addChild($answer);
   }

}

switch( $command ) {
 /* -------------------------------------------------------------------- */
 case "savefree":
  $qdb = new EvaluationQuestionDB();
  if( $qdb->exists($template_id) ){
     $question=  new EvaluationQuestion ($template_id, NULL,
                 EVAL_LOAD_ALL_CHILDREN);
     if($question->getParentID()!=$myuserid){
    $question = new EvaluationQuestion();
    $question->setParentID($myuserid);
     }else{
    $question->delete();
    $question = new EvaluationQuestion();
     }
  }else{
     $question = new EvaluationQuestion();
  }
  /*wenn root, dann id = 0 setzen ---und Text Brandmarken!!--------------*/
  $question->setParentID($myuserid);
  $question->setText(trim($template_name), YES);
  while ($answerrem = $question->getChild ()){
     $id=$answerrem->getObjectID();
     $answerrem->delete ();
     $question->removeChildID($id);
  }
  $answer = new EvaluationAnswer();
  $answer->setRows(Request::option('template_add_num_answers'));
  $question->addChild ($answer);
  $lib->setUniqueName ($question, $db, $myuserid);
  $question->save();
  $command = "";

  break;
 /* -------------------------------------------------------------------- */
 case "delete":
  $question =  new EvaluationQuestion ($template_id, NULL,
               EVAL_LOAD_ALL_CHILDREN);

  if ($question->getParentID() == $myuserid) {
     /* Delete if it is my template */
     $question->delete();
  } elseif (get_username ($question->getParentID ()) == "") {
     /* Remove all answers if it is not a template */
     while ($answer = $question->getChild ())
        $answer->delete ();
  } else {
     /* Cannot delete templates of other users */
     $report = EvalCommon::createReportMessage(_("Keine Berechtigung zum Löschen."),
                      EVAL_PIC_ERROR, EVAL_CSS_ERROR);
  }
  $command="";
  break;

 /* -------------------------------------------------------------------- */
 case "add_answers":
  // Bevor etwas hinzugefügt wird nochmal die Speicherungsroutine laufen lassen
  if(!Request::option('onthefly')){
     $question=save1($myuserid);
  }else{
     $question->save();
  }
  //$question->setMultiplechoice($template_multiple);
  //$question->setText(trim($template_name), YES);
  //$question->setType($template_type);
  $command = "continue_edit";
  if ($question->getType () == EVALQUESTION_TYPE_MC ||
      $question->getType () == EVALQUESTION_TYPE_LIKERT) {
     while ($template_add_num_answers--) {
    $answer = new EvaluationAnswer();
    $answer->setText("");
    $question->addChild ($answer);
     }
     #echo "Nummer: ".$question->getNumberChildren()."<br>";
     break;

   } elseif ($question->getType () == EVALQUESTION_TYPE_POL) {
      echo (_("Diese Option gibt es nicht"));
   } else {
      echo (_("Unbekanntes Objekt"));
   }
  $command = "continue_edit";

     break;


 /* delete answers ----------------------------------------------------- */
 case "delete_answers":
  if(!Request::option('onthefly')){
     $question=save1($myuserid);
     $question->setParentID($myuserid);
  }
  else
     $question->save();
     $template_delete_answers = Request::getArray("template_delete_answers");
     if(!empty($template_delete_answers))
          foreach ($template_delete_answers as $answerID) {
             $question->removeChildID ($answerID);
             $answer = new EvaluationAnswer ($answerID);
             $answer->delete ();
          }
     $command = "continue_edit";

     break;
   /* ------------------------------------------------ end: delete answers */


   /* -------------------------------------------------------------------- */
  case "save":
   $question=save1($myuserid);

    /* Check userinput ----------------------------------------------------- */
   if ($question->getType () == EVALQUESTION_TYPE_MC ||
       $question->getType () == EVALQUESTION_TYPE_LIKERT) {
       $nummer=$question->getNumberChildren();
     for ( $i=0; $i < count($template_answers); $i++ ) {
     $text     = $template_answers[$i]['text'];
     if($text==""){
        $question->removeChildID ($template_answers[$i]['answer_id']);
     $nummer--;
     }
     }

      if($nummer==0){
     $report =
        EvalCommon::createReportMessage(
        _("Dem Template wurden keine Antworten zugewiesen oder keine der Antworten  enthielt einen Text. Fügen Sie Antworten an, oder löschen Sie das Template."),
                        EVAL_PIC_ERROR,
                        EVAL_CSS_ERROR);
     $command = "continue_edit";
     break;
      }
   }

   if($question->getType() == EVALQUESTION_TYPE_POL ){

      for ( $i=0; $i < count($template_answers); $i++ ) {
     $text     = $template_answers[$i]['text'];
     if($text==""){
        $report =
           EvalCommon::createReportMessage(
           _("Leere Antworten sind nicht zulässig, löschen Sie betreffende Felder oder geben Sie einen Text ein."),
                           EVAL_PIC_ERROR,
                           EVAL_CSS_ERROR);
        $command = "continue_edit";
        break;
     }
      }

   if($command == "continue_edit")
      break;
   }


   if ($template_residual && empty ($template_residual_text)) {
      $report = EvalCommon::createReportMessage(
      _("Geben Sie eine Ausweichantwort ein oder deaktivieren Sie diese."),
                        EVAL_PIC_ERROR,EVAL_CSS_ERROR);
      $command = "continue_edit";
      break;
   }

   if (!Request::option('onthefly') && ! $question->getText()) {
       $report = EvalCommon::createReportMessage(_("Geben Sie einen Namen für die Vorlage ein."),
                         EVAL_PIC_ERROR,
                         EVAL_CSS_ERROR);
       $command = "continue_edit";
       break;
   }
   /*POSITION DER NÄCHSTEN ZEILE ÜBERDENKEN ---------------------------------*/
   // $lib->setUniqueName ($question, $db, $myuserid, YES);
   /* ------------------------------------------------- end: check userinput */


   /* save template -------------------------------------------------------- */
   $question->save();
   if($question->isError())
       $report = EvalCommon::createReportMessage(_("Fehler beim Speichern."),
                         EVAL_PIC_ERROR,
                         EVAL_CSS_ERROR);
   /* --------------------------------------------------- end: save template */
   $command = "";
   $template_answers = "";
   break;

  case "save2":

   $question->save();
   if($question->isError())
      $report = EvalCommon::createReportMessage(_("Fehler beim Speichern."),
                   EVAL_PIC_ERROR, EVAL_CSS_ERROR);
   $command = "";
   $template_answers = "";

   break;
}

/* Surrounding Table ------------------------------------------------------- */
$br = new HTMpty( "br" );

$tableA = new HTM ("table");
$tableA->attr ("border","0");
$tableA->attr ("align", "center");
$tableA->attr ("cellspacing", "0");
$tableA->attr ("cellpadding", "2");
$tableA->attr ("width", "250");

$lib->createInfoBox();

$trA = new HTM( "tr" );
$tdA = new HTM( "td" );
$tdA->cont( EvalCommon::createTitle( _("Antwortenvorlagen"), NULL, 2 ) );
$trA->cont( $tdA );
$tableA->cont( $trA );

$trA = new HTM( "tr" );
$tdA = new HTM( "td" );

$table = new HTM ("table");
$table->attr ("border","0");
$table->attr ("align", "center");
$table->attr ("cellspacing", "0");
$table->attr ("cellpadding", "3");
$table->attr ("width", "100%");

$tr = new HTM( "tr" );
$td = new HTM( "td" );
$td->attr( "class", "table_row_even" );

if( !$command || $command == "back" ) {
    /* the template selection lists --------------------------------------- */

    $question_show = new EvaluationQuestionDB();
    $arrayOfTemplateIDs = $question_show->getTemplateID ($myuserid);
    $arrayOfPolTemplates = [];
    $arrayOfSkalaTemplates = [];
    $arrayOfNormalTemplates = [];
    $arrayOfFreeTemplates = [];

    foreach($arrayOfTemplateIDs as $templateID) {
   $questionload = new EvaluationQuestion ($templateID,
                        NULL, EVAL_LOAD_FIRST_CHILDREN);
   $typ=$questionload->getType();
   $text=my_substr ($questionload->getText(), 0, EVAL_MAX_TEMPLATENAMELEN);
   /*Root kennzeichnung hier entfernen!!*/
   //if($questionload->getParentID()==0)
   //  $text="<b>".$text."</b>";
   if($questionload->getParentID()=='0') {
      $text=$questionload->getText()." ".EVAL_ROOT_TAG;
   }
   if (($answer = $questionload->getChild()) == NULL)
      $answer = new EvaluationAnswer ();
     /* --------------------------------------------------------------- */
       switch( $typ ) {

       case EVALQUESTION_TYPE_POL:
      array_push($arrayOfPolTemplates,
            [$questionload->getObjectID(), $text]);
      break;
       case EVALQUESTION_TYPE_LIKERT:
      array_push($arrayOfSkalaTemplates,
            [$questionload->getObjectID(),$text]);
      break;
       case EVALQUESTION_TYPE_MC:
      if ($answer->isFreetext ()) {
          array_push($arrayOfFreeTemplates,
                [$questionload->getObjectID(), $text]);
      } else {
          array_push($arrayOfNormalTemplates,
                [$questionload->getObjectID(),$text]);
      }
      break;
       }
       /* -------------------------------------------------------- */
    }

    /* report messages ---------------------------------------------------- */
    $td->cont( $report );

    $td->cont( $lib->createSelections($arrayOfPolTemplates,
                  $arrayOfSkalaTemplates,
                  $arrayOfNormalTemplates,
                  $arrayOfFreeTemplates,
                  $myuserid) );

} else {
    /* NO template selection lists ---------------------------------------- */

    /* a back button */
    $form = new HTM( "form" );

    $form->attr( "action", URLHelper::getLink("?page=edit&evalID=".$evalID));
    $form->attr( "method", "post" );
    $form->html(CSRFProtection::tokenTag());
    $form->cont( Button::create(_('zurück'), 'template_back_button', ['title' => _('Zurück zur Auswahl')]) );
    $td->cont( $form );

    /* on the fly info message -------------------------------------------- */
    if( $command == "create_question_answers" || Request::option('onthefly') ) {
   $report = EvalCommon::createReportMessage(
           sprintf(_("Weisen Sie der links %sausgewählten%s Frage hier Antworten zu:"),
              "<span class=\"eval_highlight\">", "</span>"),
           EVAL_PIC_INFO, EVAL_CSS_INFO );
    }
    /* report messages ---------------------------------------------------- */
    $td->cont( $report );
}


$tr->cont( $td );
$table->cont( $tr );
$tdA->cont( $table );
$trA->cont( $tdA );
$tableA->cont( $trA );



if( $command ) {
    /* the template editing fields */
    $trA = new HTM( "tr" );
    $tdA = new HTM( "td" );

    $table = new HTM ("table");
    $table->attr ("border","0");
    $table->attr ("align", "center");
    $table->attr ("cellspacing", "0");
    $table->attr ("cellpadding", "0");
    $table->attr ("width", "100%");

    $tr = new HTM( "tr" );
    $td = new HTM( "td" );
    $td->attr( "class", "table_row_odd" );

      /*übergebe an create Form das template, dass verändert werden soll*/

    switch( $command ) {
      case "editpol_scale":
       $question=  new EvaluationQuestion (Request::option('template_editpol_scale'), NULL,EVAL_LOAD_ALL_CHILDREN);
       $td->cont( $lib->createTemplateForm( $question ) );
       break;
      case "createpol_scale":
       $question = new EvaluationQuestion();
       $question->setObjectID(md5(uniqid(rand())));
       $question->setType(EVALQUESTION_TYPE_POL);
       $question->setText("");
        for( $i = 0 ; $i < 2 ; $i++ ){
       $answer = new EvaluationAnswer();
       $answer->setParentID($question->getObjectID());
       if($i==0)
          $answer->setText(_("Anfang"));
       else
          $answer->setText(_("Ende"));
       $question->addChild($answer);
    }
    //  $td->cont( $lib->createTemplateFormPol( $question ) );
    $td->cont( $lib->createTemplateForm( $question ) );
    break;
      case "editlikert_scale":
       $question=  new EvaluationQuestion (Request::option('template_editlikert_scale'),
                        NULL, EVAL_LOAD_ALL_CHILDREN);
      $question->setType(EVALQUESTION_TYPE_LIKERT);
       //$td->cont( $lib->createTemplateFormLikert( $question ) );
       $td->cont( $lib->createTemplateForm( $question ) );
       break;
      case "createlikert_scale":
       $question = new EvaluationQuestion();
       $question->setObjectID(md5(uniqid(rand())));
       $question->setType(EVALQUESTION_TYPE_LIKERT);
       $question->setText("");
       for( $i = 0 ; $i < 4 ; $i++ ){
      $answer = new EvaluationAnswer();
      $answer->setParentID($question->getObjectID());
      $answer->setText("");
      $answer->setPosition(1);
      $question->addChild($answer);
       }
       $td->cont( $lib->createTemplateForm( $question ) );
       break;
      case "editnormal_scale":
       $question=  new EvaluationQuestion (Request::option('template_editnormal_scale'),
                        NULL, EVAL_LOAD_ALL_CHILDREN);
       $question->setType(EVALQUESTION_TYPE_MC);
       $td->cont( $lib->createTemplateForm( $question ) );
       break;
      case "createnormal_scale":
       $question = new EvaluationQuestion();
       $question->setObjectID(md5(uniqid(rand())));
       $question->setType(EVALQUESTION_TYPE_MC);
       $question->setText("");
       for( $i = 0 ; $i < 4 ; $i++ ){
      $answer = new EvaluationAnswer();
      $answer->setParentID($question->getObjectID());
      $answer->setText("");
      $answer->setPosition(1);
      $question->addChild($answer);
       }
       $td->cont( $lib->createTemplateForm( $question ) );
       //$td->cont( $lib->createTemplateFormMul( $question ) );
       break;
      case "continue_edit":
       /*Im Fall direkt question->answers flag mitübergeben*/
       /*$template_type überprüfen------------------------------------------*/
       switch( Request::option('template_type') ) {
      /* --------------------------------------------------------------- */
     case EVALQUESTION_TYPE_POL:
      $td->cont( $lib->createTemplateForm( $question ) );
      break;
     case EVALQUESTION_TYPE_LIKERT:
      $td->cont( $lib->createTemplateForm( $question ) );
      break;
     case EVALQUESTION_TYPE_MC:
      $td->cont( $lib->createTemplateForm( $question, Request::int('onthefly')));
      break;
       }
       break;

      case "create_question_answers":
       $onthefly=1;
       // extract the questionID from the command
       foreach( $_REQUEST as $key => $value ) {
      if( preg_match( "/template_(.*)_button?/", $key, $command ) )
         break;
       }
       if ( preg_match( "/(.*)_#(.*)/", $command[1], $command_parts ) )
      $questionID = $command_parts[2];
       $question=  new EvaluationQuestion ($questionID,
                        NULL, EVAL_LOAD_ALL_CHILDREN);

       if($question->getNumberChildren()==0){
      $question->setType(EVALQUESTION_TYPE_MC);
      for( $i = 0 ; $i < 4 ; $i++ ){
         $answer = new EvaluationAnswer();
         $answer->setParentID($question->getObjectID());
         $answer->setText((""));
         $answer->setPosition(1);
         $question->addChild($answer);
      }
       }
       $td->cont( $lib->createTemplateForm( $question, $onthefly ) );
       break;
      case "createfree_scale":
       $question = new EvaluationQuestion();
       $question->setObjectID(md5(uniqid(rand())));
       $question->setType(EVALQUESTION_TYPE_MC);
       $question->setText(_("Freitext"));
       $answer = new EvaluationAnswer();
       $answer->setParentID($question->getObjectID());
       $answer->setText("");
       $answer->setRows(1);
       $question->addChild($answer);
       $td->cont( $lib->createTemplateFormFree( $question ) );
       break;
      case "editfree_scale":
       $question=  new EvaluationQuestion (Request::option('template_editfree_scale'),
                        NULL, EVAL_LOAD_ALL_CHILDREN);
       $td->cont( $lib->createTemplateFormFree( $question ) );
       break;

      case "back":
       $td->cont(" ");
       break;

    }

    $tr->cont( $td );
    $table->cont( $tr );
    $tdA->cont( $table );
    $trA->cont( $tdA );
    $tableA->cont( $trA );

}

/* Javascript function for preview-link */
$js = EvalCommon::createEvalShowJS( YES );

/* --------------------------------------------------------------------- */
return $js->createContent() . $tableA->createContent();
/* --------------------------------------------------------------------- */


/* --------------------------------------------------------------------- */
function save1($myuserid){
   $mineexists=0;
   /*Existiert Question/Template schon?*/
   $qdb = new EvaluationQuestionDB();
   if(!$template_id){
      $template_id = Request::option("template_id");
   }
   if( $qdb->exists($template_id) ){
      $question=  new EvaluationQuestion ($template_id,
                       NULL, EVAL_LOAD_ALL_CHILDREN);
      if($question->getParentID() != $myuserid){
     $foreign=TRUE;
     $question = new EvaluationQuestion();
     $question->setParentID($myuserid);
      }
      else{
     $overwrite=1;
     //$question->delete();
     //$question = new EvaluationQuestion();
     //$template_id=$question->getObjectID();
      }
   }
   else{
      $question = new EvaluationQuestion();
   }

   /*Get Vars ----------------------------------------------------*/
   $template_name = Request::get("template_name");
   $template_type = Request::get("template_type");
   $template_multiple = Request::get("template_multiple");
   $template_add_num_answers = Request::option("template_add_num_answers");
   $template_residual = Request::get("template_residual");
   $template_residual_text = Request::get("template_residual_text");
   $template_answers = Request::getArray("template_answers");
   /*end: Get Vars -----------------------------------------------*/

   $question->setParentID($myuserid);
   $question->setMultiplechoice($template_multiple);
   $question->setText(trim($template_name), YES);
   $question->setType($template_type);

   while ($answerrem = $question->getChild ()){
      $id=$answerrem->getObjectID();
      $answerrem->delete ();
      $question->removeChildID($id);
   }

   $controlnumber = count($template_answers);
   $ausgleich = 0;

   for ( $i=0; $i < $controlnumber; $i++ ) {
      $text     = $template_answers[$i]['text'];
      $answerID = $template_answers[$i]['answer_id'];
      $answer = new EvaluationAnswer();
     if(!$foreign)
        $answer->setObjectID($answerID);
     $answer->setText(trim($text), YES);
     $question->addChild($answer);

     /*Anzahl der Antworten bei Polskalen anpassen ------------------------*/
     if ($template_type == EVALQUESTION_TYPE_POL && $i == 0){
    $answerdiff = $controlnumber - $template_add_num_answers ;
    if($template_residual){
       //echo "Hust<br>";
       //$answerdiff;
    }
    if($answerdiff > 0){
       /*differenz abziehen => answers überspringen*/
       $i=$i+$answerdiff;
       $ausgleich=$ausgleich-$answerdiff;
    }
    while($answerdiff < 0){
       $ausgleich = $ausgleich + 1;
       $answer =  new EvaluationAnswer();
       $answer->setText("");
       $answer->setParentID($question->getObjectID());
       $answer->setPosition($i+$ausgleich);
       $answer->setValue($i+1+$ausgleich);
       $question->addChild($answer);
       $answerdiff++;
    }
     }
     /*end: Polskala antworten angleichen ----------------------------------*/
   }
   /*create residual category-----------------------------------------------*/
   if($template_residual){
      $answer =   new EvaluationAnswer();
      $answer->setResidual($template_residual);
      $answer->setText(trim($template_residual_text),QUOTED);
      $answer->setParentID($question->getObjectID());
      $answer->setPosition($i+$ausgleich+1);
      $answer->setValue(-1);
      $question->addChild($answer);
   }
   /*object HIER NOCH NICHT in db speichern!      */
   // $question->save();
   if(!$overwrite){
   $db  = new EvaluationQuestionDB();
   $lib = new EvalTemplateGUI();
   $lib->setUniqueName ($question, $db, $myuserid, YES);
   }

   if($question->isError())
      EvalCommon::showErrorReport($question,_("Fehler beim Speichern."));
   return $question;

}
# Define constants ========================================================== #
/**
 * @const EVAL_ROOT_TAG Specifies the string for taging root templates
 * @access public
 */
define ('EVAL_ROOT_TAG', "[R]");
# ===================================================== end: define constants #


?>
