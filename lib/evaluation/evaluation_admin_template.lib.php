<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Library for template gui
 *
 * @author      JPWowra
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

class EvalTemplateGUI {

  /* Define functions ------------------------------------------------------ */

  function __construct( ) {
    $this->BR = new HTMpty( "br" );
    $this->command = $this->getPageCommand();
  }

  /**
   * Creates the two (?) template selection lists
   * @param
   */
  function createSelections( $polTemplates, $skalaTemplates,
              $normalTemplates, $freeTemplates, $myuserid) {

     global $evalID;

     $form = new HTM( "form" );

     $form->attr( "action", URLHelper::getLink("?page=edit&evalID=".$evalID) );
     $form->attr( "method", "post" );
     $form->html(CSRFProtection::tokenTag());

     $table = new HTML("table");
     $table->addAttr("border","0");
     $table->addAttr("cellpadding","2");
     $table->addAttr("cellspacing","0");

     $table->addAttr("width","100%");
     $tr = new HTML ("tr");
     $td = new HTML ("td");
     $td->addAttr("align","right");

     /* Polskalen ------------------------------------------------ */
     $b = new HTM( "b" );
     $b->cont( _("Polskala") );
     $td->addContent($b);
     $tr->addContent($td);

     $td = new HTML ("td");

     /* create button ------------------------------------ */
     $input = new HTMpty( "input" );
     $input->attr( "type", "image" );
     $input->attr( "name", "template_createpol_scale_button" );
     $input->attr( "border", "0" );
     $input->attr( "style", "vertical-align:middle;" );
     $input->stri( tooltip( _("Neue Vorlage erstellen." ), TRUE ) );
     $input->attr ("src", EVAL_PIC_ADD_TEMPLATE);

     $td->addContent($input);
     $tr->addContent($td);
     $table->addContent($tr);

     if(!empty($polTemplates)) {
    $tr = new HTML ("tr");
    $td = new HTML ("td");
    $td->addAttr("align","right");

    $select = new HTM( "select" );
    $select->attr( "name", "template" );
    $select->attr( "size", "1" );
    foreach($polTemplates as $templatearray) {
        $option = new HTM( "option" );
        $select->attr( "name", "template_editpol_scale" );
        $option->attr( "value", $templatearray[0]);
        $option->cont("$templatearray[1]");
        $select->cont( $option );
    }
    $td->addContent($select);
    $tr->addContent($td);
    $td = new HTML ("td");

    /* edit button ----------------------------------- */
    $input = new HTMpty( "input" );
    $input->attr( "type", "image" );
    $input->attr( "name", "template_editpol_scale_button" );
    $input->attr( "border", "0" );
    $input->attr( "style", "vertical-align:middle;" );
    $input->stri( tooltip( _("Ausgewählte Vorlage bearbeiten." ), TRUE ) );
    $input->attr ("src", EVAL_PIC_EDIT);

    $td->addContent($input);
    $tr->addContent($td);
    $table->addContent($tr);
     }
     /* end: Polskalen ----------------------------------------------- */


     /* Likertskalen ------------------------------------------------- */
     $td = new HTML ("td");
     $td->addAttr("align","right");
     $td->addAttr("class","content_body");
     $tr = new HTML ("tr");

     $b = new HTM( "b" );
     $b->cont( _("Likertskala") );
     $td->addContent($b);
     $tr->addContent($td);

     $td = new HTML ("td");
     $td->addAttr("class","content_body");

     /* create button ------------------------------------ */
     $input = new HTMpty( "input" );
     $input->attr( "type", "image" );
     $input->attr( "name", "template_createlikert_scale_button" );
     $input->attr( "border", "0" );
     $input->attr( "style", "vertical-align:middle;" );
     $input->stri( tooltip( _("Neue Vorlage erstellen." ), TRUE ) );
     $input->attr ("src", EVAL_PIC_ADD_TEMPLATE);

     $td->addContent($input);
     $tr->addContent($td);
     $table->addContent($tr);

     if(!empty($skalaTemplates)) {
    $td = new HTML ("td");
    $tr = new HTML ("tr");
    $td->addAttr("align","right");
    $select = new HTM( "select" );
    $select->attr( "name", "template" );
    $select->attr( "size", "1" );

    foreach($skalaTemplates as $templatearray) {
        $option = new HTM( "option" );
        $select->attr( "name", "template_editlikert_scale" );
        $option->attr( "value", $templatearray[0]);
        $option->cont("$templatearray[1]");
        $select->cont( $option );
    }
    $td->addContent($select);
    $tr->addContent($td);

    $td = new HTML ("td");
    $input = new HTMpty( "input" );
    $input->attr( "type", "image" );
    $input->attr( "name", "template_editlikert_scale_button" );
    $input->attr( "border", "0" );
    $input->attr( "style", "vertical-align:middle;" );
    $input->stri( tooltip( _("Ausgewählte Vorlage bearbeiten." ), TRUE ) );
    $input->attr ("src", EVAL_PIC_EDIT);

    $td->addContent($input);
    $tr->addContent($td);
    $table->addContent($tr);
     }
     /* end: Likertskalen ----------------------------------------------- */


     /* Normale / Multiplechoice ---------------------------------------- */
     $td = new HTML ("td");
     $td->addAttr("class","content_body");
     $tr = new HTML ("tr");
     $td->addAttr("align","right");

     $b = new HTM( "b" );
     $b->cont( _("Multiple Choice") );
     $td->addContent($b);
     $tr->addContent($td);

     $td = new HTML ("td");
     $td->addAttr("class","content_body");

     /* create button ------------------------------------ */
     $input = new HTMpty( "input" );
     $input->attr( "type", "image" );
     $input->attr( "name", "template_createnormal_scale_button" );
     $input->attr( "border", "0" );
     $input->attr( "style", "vertical-align:middle;" );
     $input->stri( tooltip( _("Neue Vorlage erstellen." ), TRUE ) );
     $input->attr ("src", EVAL_PIC_ADD_TEMPLATE);

     $td->addContent($input);
     $tr->addContent($td);
     $table->addContent($tr);

     if(!empty($normalTemplates)) {
    $td = new HTML ("td");
    $tr = new HTML ("tr");
    $td->addAttr("align","right");
    $select = new HTM( "select" );
    $select->attr( "name", "template" );
    $select->attr( "size", "1" );

    foreach($normalTemplates as $templatearray) {
        $option = new HTM( "option" );
        $select->attr( "name", "template_editnormal_scale" );
        $option->attr( "value", $templatearray[0]);
        $option->cont("$templatearray[1]");
        $select->cont( $option );
    }
    $td->addContent($select);
    $tr->addContent($td);
    $td = new HTML ("td");

    /* edit button */
    $input = new HTMpty( "input" );
    $input->attr( "type", "image" );
    $input->attr( "name", "template_editnormal_scale_button" );
    $input->attr( "border", "0" );
    $input->attr( "style", "vertical-align:middle;" );
    $input->stri( tooltip( _("Ausgewählte Vorlage bearbeiten." ), TRUE ) );
    $input->attr ("src", EVAL_PIC_EDIT);

    $td->addContent($input);
    $tr->addContent($td);
    $table->addContent($tr);
     }
     /* end: Normale / Multiplechoice-------------------------------------- */


     /* Freitext ----------------------------------------------------- */

    $td = new HTML ("td");
    $td->addAttr("class","content_body");
    $tr = new HTML ("tr");
    $td->addAttr("align","right");

    $b = new HTM( "b" );
    $b->cont( _("Freitext-Antwort") );
    $td->addContent($b);
    $tr->addContent($td);

    $td = new HTML ("td");
    $td->addAttr("class","content_body");
    $input = new HTMpty( "input" );
    $input->attr( "type", "image" );
    $input->attr( "name", "template_createfree_scale_button" );
    $input->attr( "border", "0" );
    $input->attr( "style", "vertical-align:middle;" );
    $input->stri( tooltip( _("Neue Vorlage erstellen." ), TRUE ) );
    $input->attr ("src", EVAL_PIC_ADD_TEMPLATE);
    $td->addContent($input);
    $tr->addContent($td);
    $table->addContent($tr);

    if(!empty($freeTemplates)) {
        $td = new HTML ("td");
        $tr = new HTML ("tr");
        $td->addAttr("align","right");

        $select = new HTM( "select" );
        $select->attr( "name", "template" );
        $select->attr( "size", "1" );

        foreach($freeTemplates as $templatearray) {
       $option = new HTM( "option" );
       $select->attr( "name", "template_editfree_scale" );
       $option->attr( "value", $templatearray[0]);
       $option->cont("$templatearray[1]");
       $select->cont( $option );
        }
        $td->addContent($select);
        $tr->addContent($td);
        $td = new HTML ("td");

        $input = new HTMpty( "input" );
        $input->attr( "type", "image" );
        $input->attr( "name", "template_editfree_scale_button" );
        $input->attr( "border", "0" );
        $input->attr( "style", "vertical-align:middle;" );
        $input->stri( tooltip( _("Ausgewählte Vorlage bearbeiten." ), TRUE ) );
        $input->attr ("src", EVAL_PIC_EDIT);
        $td->addContent($input);
        $tr->addContent($td);
        $table->addContent($tr);
    }
     /* end: Freitext -------------------------------------- */

     $form->cont($table);

     return $form;
  }
/**
   * Creates the form for the template
   * @param
   */
  function createTemplateForm( &$question, $onthefly = "" ) {
      global $evalID,$itemID;

     $type=$question->getType();
     $tableA = new HTM( "table" );
     $tableA->attr("border", "0");
     $tableA->attr("cellpadding", "2");
     $tableA->attr("cellspacing", "0");
     $tableA->attr("width","100%");

     $trA = new HTM( "tr" );
     $tdA = new HTM( "td" );
     $tdA->attr( "class", "table_header_bold" );
     $tdA->attr ("align","left");
     if( $onthefly ) {
    $tdA->html( _("<b>Freie Antworten definieren</b>") );
     } else {
    $isCreate = mb_strstr($this->getPageCommand(), "create");
    $tdA->html("<b>");
    switch ($type){
      case EVALQUESTION_TYPE_MC:
       //$answer = $question->getChild();
       //if ($answer->isFreetext ()) {}
       //else
       $tdA->html( $isCreate
               ? _("Multiple Choice erstellen")
               : _("Multiple Choice bearbeiten") );
       break;
      case EVALQUESTION_TYPE_LIKERT:
       $tdA->html( $isCreate
               ? _("Likertskala erstellen")
               : _("Likertskala bearbeiten") );
       break;
      case EVALQUESTION_TYPE_POL:
       $tdA->html( $isCreate
               ? _("Polskala erstellen")
               : _("Polskala bearbeiten") );
       break;
    }
    $tdA->html("</b>");
     }
     $trA->cont( $tdA );
     $tableA->cont( $trA );

     $trA = new HTM( "tr" );
     $tdA = new HTM( "td" );

     $form = new HTM( "form" );
     $form->attr( "action", URLHelper::getLink('', ['page' => 'edit', 'evalID' => $evalID, 'itemID' => $itemID] ));

    $form->attr( "method", "post" );
    $form->html(CSRFProtection::tokenTag());
    /* template name --------------------------------- */
    if($onthefly!=1){
       $b = new HTM( "b" );
       $b->cont( _("Name").": " );
       $form->cont( $b );
       $input = new HTMpty( "input" );
       $input->attr( "type", "text" );
       $input->attr( "name", "template_name" );
       $input->attr( "value", $question->getText() );
       $input->attr( "style", "vertical-align:middle;" );
       $input->attr( "size", 22 );
       $input->attr( "maxlength", 22 );
       $input->attr( "tabindex", 1 );
       $form->cont( $input );
    }
    else{
    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "template_name" );
    $input->attr( "value", $question->getText() );
    $form->cont( $input );

    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "onthefly" );
    $input->attr( "value", $onthefly );
    $form->cont( $input );
    }

    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "template_id" );
    $input->attr( "value", $question->getObjectID() );
    $form->cont( $input );

    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "template_type" );
    $input->attr( "value", $question->getType() );
    $form->cont( $input );

    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "template_residual" );
    $input->attr( "value", NO);
    $form->cont( $input );

    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "template_position" );
    $input->attr( "value", $question->getPosition());
    $form->cont( $input );

    $input = new HTMpty( "input" );
    $input->attr( "type", "hidden" );
    $input->attr( "name", "parentID" );
    $input->attr( "value", $question->getParentID());
    $form->cont( $input );

    if($onthefly!=1){
       $img = new HTMpty( "img" );
       $img->attr( "src", Icon::create('info-circle', 'inactive')->asImagePath(16));
       $img->attr( "class", "middle" );
       $img->stri( tooltip( _("Geben Sie hier einen Namen für Ihre Vorlage ein. Wenn Sie eine systemweite Vorlage bearbeiten, und speichern, wird eine neue Vorlage für Sie persönlich angelegt."),
                FALSE, TRUE ) );
       $form->cont( $img );
       $form->cont( $this->BR );
    }
    if($type == EVALQUESTION_TYPE_MC){
    /* multiple - radiobuttons ----------------------- */
       $form->cont( $this->createSubHeadline
            ( _("Mehrfachantwort erlaubt").": " ) );
       $radio = new HTMpty( "input" );
       $radio->attr( "type", "radio" );
       $radio->attr( "name", "template_multiple" );
       $radio->attr( "value", YES );
       $question->isMultiplechoice()
      ? $radio->attr( "checked" ) : 0;
       $form->cont( $radio );
       $form->cont( _("ja") );

       $radio = new HTMpty( "input" );
       $radio->attr( "type", "radio" );
       $radio->attr( "name", "template_multiple" );
       $radio->attr( "value", NO );
       $question->isMultiplechoice()
      ? 0 : $radio->attr( "checked" );
       $form->cont( $radio );
       $form->cont( _("nein") );
       $form->cont( $this->BR );
       /*end:  multiple - radiobuttons -------------------- */

       /* show multiple choice checkboxes & answers------------------------- */
       $form->cont( $this->createSubHeadline( _("Antworten").": " ) );
       for( $i=0; $answer = $question->getNextChild(); $i++ ) {
          $form->cont( ($i<9?"0":"").($i+1).". " );
      $input = new HTMpty( "input" );
      $input->attr( "type", "text" );
      $input->attr( "name", "template_answers[".$i."][text]" );
      $input->attr( "value", $answer->getText() );
      $input->attr( "size", 23 );
      $input->attr( "tabindex", $i+2 );
      $form->cont( $input );
      $input = new HTMpty( "input" );
      $input->attr( "type", "checkbox" );
      $input->attr( "name", "template_delete_answers[".$i."]" );
      $input->attr( "value", $answer->getObjectID () );
      $form->cont( $input );

      $input = new HTMpty( "input" );
      $input->attr( "type", "hidden" );
      $input->attr( "name", "template_answers[".$i."][answer_id]" );
      $input->attr( "value", $answer->getObjectID() );
      $form->cont( $input );
      $form->cont( $this->BR );
       }
       /* ------------------------- end: multiple choice checkboxes &answers */

       /* add button ------------------------------------ */
       $input = new HTMpty( "input" );
       $input->attr( "type", "image" );
       $input->attr( "name", "template_add_answers_button" );
       $input->addAttr ("src", EVAL_PIC_ADD);
       $input->attr( "border", "0" );
       $input->attr( "style", "vertical-align:middle;" );
       $form->html("&nbsp;");
       $form->cont( $input );

       /* add number of answers - list ------------------ */
       $select = new HTM( "select" );
       $select->attr( "name", "template_add_num_answers" );
       $select->attr( "size", "1" );
       $select->attr( "style", "vertical-align:middle;" );
       for( $i = 1 ; $i <= 10 ; $i++ ) {
      $option = new HTM( "option" );
      $option->attr( "value", $i );
      $option->cont( $i );
      $select->cont( $option );
       }
       $form->cont( $select );

       /* delete button --------------------------------- */
       $input = new HTMpty( "input" );
       $input->attr( "type", "image" );
       $input->attr( "name", "template_delete_answers_button" );
       $input->addAttr ("src", EVAL_PIC_REMOVE);
       $input->attr( "border", "0" );
       $input->attr( "style", "vertical-align:middle;" );
       $form->html("&nbsp;");
       $form->cont( $input );
       $form->cont( $this->BR );
    }else{
       if($type == EVALQUESTION_TYPE_POL){
      $form->cont( $this->createSubHeadline( _("Antworten").": " ) );
      /* answers --------------------------------------- */
      $isResidual = NO;
      for( $i=0; $answer = $question->getNextChild(); $i++ ) {
         /*Einbau der Residualkategorie hier komplizierter*/
         $residualAnswer = $answer;
         if(!$answer->isResidual()){
        if($i == 0 || $i >= ($question->getNumberChildren()-2)){
           if($i==0){
              $form->cont( _("Beschriftung erste Antwort") );
              $input = new HTMpty( "input" );
              $input->attr( "type", "text" );
              $input->attr( "name", "template_answers[0][text]" );
              $input->attr( "value", $answer->getText() );
              $input->attr( "size", 29 );
              $form->cont( $input );
              $input = new HTMpty( "input" );
              $input->attr( "type", "hidden" );
              $input->attr( "name", "template_answers[0][answer_id]" );
              $input->attr( "value", $answer->getObjectID() );
              $form->cont( $input );
              $form->cont( $this->BR );
           }else{

              if($answer->getText() == "" ){
             $oldid=$answer->getObjectID();
             //continue;
              }
              else{
              $form->cont( _("Beschriftung letzte Antwort") );
              $lastone=-1;
              $input = new HTMpty( "input" );
              $input->attr( "type", "text" );
              $input->attr( "name", "template_answers[1][text]" );
              $input->attr( "value", $answer->getText() );
              $input->attr( "size", 29 );
              $form->cont( $input );
              $input = new HTMpty( "input" );
              $input->attr( "type", "hidden" );
              $input->attr( "name", "template_answers[1][answer_id]" );
              $input->attr( "value", $answer->getObjectID() );
              $form->cont( $input );
              }

           }

        }

         }
         else{
        $isResidual = YES;

         }
         if($lastone!=-1 && $i== ($question->getNumberChildren()-1)){
        $form->cont( _("Beschriftung letzte Antwort") );
        $lastone=YES;
        $input = new HTMpty( "input" );
        $input->attr( "type", "text" );
        $input->attr( "name", "template_answers[1][text]" );
        $input->attr( "value", "" );
        $input->attr( "size", 29 );
        $form->cont( $input );
        $input = new HTMpty( "input" );
        $input->attr( "type", "hidden" );
        $input->attr( "name", "template_answers[1][answer_id]" );
        $input->attr( "value", $oldid );
        $form->cont( $input );
         }
      }
      $form->cont( $this->BR );
      $form->cont( $this->
               createSubHeadline(_("Anzahl Abstufungen").": " ) );
      /* NUMBER OF ANSWERS------------------------------------------ */
      $select = new HTM( "select" );
      $select->attr( "name", "template_add_num_answers" );
      $select->attr( "size", "1" );
      $select->attr( "style", "vertical-align:middle;" );
      if($isResidual==YES){
         $res=1;
      }
      for( $i=4; $i<=20; $i++ ) {
         $option = new HTM( "option" );
         $option->attr( "value", $i );
         $option->cont( $i );
         if($i == $question->getNumberChildren()-$res)
        $option->addAttr("selected","selected");
         $select->cont( $option );
      }
      $form->cont( $select );
      $form->cont( $this->BR );


       }
       if($type == EVALQUESTION_TYPE_LIKERT){
      $form->cont( $this->createSubHeadline( _("Antworten").": " ) );
      $residualAnswer = NULL;
      $isResidual = NO;
      for( $i=0; $answer = $question->getNextChild(); $i++ ) {
         if(!$answer->isResidual()){
        $form->cont( ($i<9?"0":"").($i+1).". " );
        $input = new HTMpty( "input" );
        $input->attr( "type", "text" );
        $input->attr( "name", "template_answers[".$i."][text]" );
        $input->attr( "value", $answer->getText(  ) );
        $input->attr( "size", 23 );
        $input->attr( "tabindex", $i+2 );
        $form->cont( $input );
        $input = new HTMpty( "input" );
        $input->attr( "type", "checkbox" );
        $input->attr( "name", "template_delete_answers[".$i."]" );
        $input->attr( "value", $answer->getObjectID () );
        $form->cont( $input );
        $input = new HTMpty( "input" );
        $input->attr( "type", "hidden" );
        $input->attr( "name", "template_answers[".$i."][answer_id]" );
        $input->attr( "value", $answer->getObjectID() );
        $form->cont( $input );
        $form->cont( $this->BR );
        if(!$residualAnswer)
           $residualAnswer = $answer;
         } else {
        $i--;
        $isResidual = YES;
        $residualAnswer = $answer;
         }
      }

      /* add button ------------------------------------ */
      $input = new HTMpty( "input" );
      $input->attr( "type", "image" );
      $input->attr( "name", "template_add_answers_button" );
      $input->addAttr ("src", EVAL_PIC_ADD);

      $input->attr( "border", "0" );
      $input->attr( "style", "vertical-align:middle;" );
      $form->html("&nbsp;");
      $form->cont( $input );

      /* add number of answers - list ------------------ */
      $select = new HTM( "select" );
      $select->attr( "name", "template_add_num_answers" );
      $select->attr( "size", "1" );
      $select->attr( "style", "vertical-align:middle;" );
      for( $i = 1; $i <= 10 ; $i++ ) {
         $option = new HTM( "option" );
         $option->attr( "value", $i );
         $option->cont( $i );
         $select->cont( $option );
      }
      $form->cont( $select );

      /* delete answers button --------------------------------- */
      $input = new HTMpty( "input" );
      $input->attr( "type", "image" );
      $input->attr( "name", "template_delete_answers_button" );
      $input->addAttr ("src", EVAL_PIC_REMOVE);
      $input->attr( "border", "0" );
      $input->attr( "style", "vertical-align:middle;" );
      $form->html("&nbsp;");
      $form->cont( $input );
      $form->cont( $this->BR );


       }

    }
    if($type == EVALQUESTION_TYPE_LIKERT || $type == EVALQUESTION_TYPE_POL){
       /* residual category ------------------------------------ */
       $form->cont( $this->createSubHeadline( _("Ausweichantwort").": " ) );

       /* residual - radiobuttons ------------------------------ */
       $radio = new HTMpty( "input" );
       $radio->attr( "type", "radio" );
       $radio->attr( "name", "template_residual" );
       $radio->attr( "value", YES );

       $value = $isResidual ? "checked" : "unchecked";
       $radio->attr( $value );

       $form->cont( $radio );
       $form->cont( _("ja").":" );

       /* residual text field -------------> */
       $input = new HTMpty( "input" );
       $input->attr( "type", "text" );
       $input->attr( "name", "template_residual_text" );
       if ($isResidual)
      $input->attr( "value", $residualAnswer->getText() );
       else
      $input->attr( "value", "" );
       $input->attr( "size", 22 );
       $form->cont( $input );
       /* <------------- residual text field */
       $form->cont( $this->BR );
       $radio = new HTMpty( "input" );
       $radio->attr( "type", "radio" );
       $radio->attr( "name", "template_residual" );
       $radio->attr( "value", NO );

       $value = $value == "unchecked" ? "checked" : "unchecked";
       $radio->attr( $value );

       $form->cont( $radio );
       $form->cont( _("nein") );
       /*end:  residual - radiobuttons -------------------- */

       $input = new HTMpty( "input" );
       $input->attr( "type", "hidden" );
       $input->attr( "name", "template_residual_id" );
       $input->attr( "value", $residualAnswer->getObjectID );
       $form->cont( $input );
       /*end:  residual - kategorie -------------------- */
    }
    /* uebernehmen button ---------------------------- */
    if($onthefly==1){
       $input = new HTMpty( "input" );
       $input->attr( "type", "hidden" );
       $input->attr( "name", "cmd" );
       $input->attr( "value", "QuestionAnswersCreated");
       $form->cont( $input );

       $input = Button::create(_('Übernehmen'),
                'template_save2_button');
    }
    else{
        $input = Button::create(_('Übernehmen'),
                'template_save_button');
    }

    if( !mb_strstr($this->command, "create") ) {
       $showDelete = YES;
       $input2 = Button::createAccept(_('Löschen'),
                'template_delete_button');
    }

    $table = new HTM( "table" );
    $table->attr ("border","0");
    $table->attr ("align", "center");
    $table->attr ("cellspacing", "0");
    $table->attr ("cellpadding", "3");
    $table->attr ("width", "100%");
    $tr = new HTM( "tr" );
    $td = new HTM( "td" );
    $td->attr( "class", "content_body" );
    $td->attr( "align", "center" );
    $td->cont( $input );
    $tr->cont( $td );

    if( $showDelete ) {
       $td = new HTM( "td" );
       $td->attr( "class", "content_body" );
       $td->attr( "align", "center" );
       $td->cont( $input2 );
       $tr->cont( $td );
    }

    $table->cont( $tr );
    $form->cont( $table );

    /* ----------------------------------------------- */
    $tdA->cont( $form );
    $trA->cont( $tdA );
    $tableA->cont( $trA );
    return $tableA;

  }



/**
   * Creates the form for the Polskala templates
   * @param
   */
 function createTemplateFormFree( &$question ) {
     global $evalID;
     $answer = $question->getNextChild ();

     $tableA = new HTM( "table" );
     $tableA->attr("border", "0");
     $tableA->attr("cellpadding", "2");
     $tableA->attr("cellspacing", "0");
     $tableA->attr("width","100%");

     $trA = new HTM( "tr" );
     $tdA = new HTM( "td" );
     $tdA->attr( "class", "table_header_bold" );
     $tdA->attr( "align","left" );
     $tdA->html( "<b>" . ( mb_strstr($this->getPageCommand(), "create")
               ? _("Freitextvorlage erstellen")
               : _("Freitextvorlage bearbeiten") ) . "</b>" );
     $trA->cont( $tdA );
     $tableA->cont( $trA );

     $trA = new HTM( "tr" );
     $tdA = new HTM( "td" );
     $form = new HTM( "form" );
     $form->attr( "action", URLHelper::getLink("?page=edit&evalID=".$evalID) );
     $form->attr( "method", "post" );
     $form->html(CSRFProtection::tokenTag());

     $b = new HTM( "b" );
     $b->cont( _("Name").": " );
     $form->cont( $b );

     $input = new HTMpty( "input" );
     $input->attr( "type", "text" );
     $input->attr( "name", "template_name" );
     $name = $question->getText();
     $input->attr( "value", $question->getText());
     //    $input->attr( "value", $name );
     $input->attr( "style", "vertical-align:middle;" );
     $input->attr( "size", 22 );
     $input->attr( "maxlength", 22 );
     $form->cont( $input );

     $input = new HTMpty( "input" );
     $input->attr( "type", "hidden" );
     $input->attr( "name", "template_id" );
     $input->attr( "value", $question->getObjectID() );
     $form->cont( $input );

     $input = new HTMpty( "input" );
     $input->attr( "type", "hidden" );
     $input->attr( "name", "template_type" );
     $input->attr( "value", $question->getType() );
     $form->cont( $input );

     $input = new HTMpty( "input" );
     $input->attr( "type", "hidden" );
     $input->attr( "name", "template_multiple" );
     $input->attr( "value", NO );
     $form->cont( $input );

     $img = new HTMpty( "img" );
     $img->attr( "src", Icon::create('info-circle', 'inactive')->asImagePath(16));
     $img->attr( "class", "middle" );
     $img->stri( tooltip( _("Geben Sie hier einen Namen für Ihre Vorlage ein. Ändern Sie den Namen, um eine neue Vorlage anzulegen." ),
           FALSE, TRUE ) );
     $form->cont( $img );
     $form->cont( $this->BR );

     //$answer = $question->getNextChild();
     //$answer->toString();
    /* Anzahl Zeilen------------------------------------------------------ */
    $form->cont( $this->createSubHeadline( _("Anzahl Zeilen").": " ) );

    $select = new HTM( "select" );
    $select->attr( "name", "template_add_num_answers" );
    $select->attr( "size", "1" );
    $select->attr( "style", "vertical-align:middle;" );
    for( $i=1; $i<=25; $i++ ) {
   $option = new HTM( "option" );
   $option->attr( "value", $i );
   $option->cont( $i );
   if($i == $answer->getRows())
      $option->addAttr("selected","selected");
   $select->cont( $option );
    }
    $form->cont( $select );
    $form->cont( $this->BR );

    /* uebernehmen / loeschen Button ---------------------------- */
    $input = Button::create(_('Übernehmen'),
                'template_savefree_button');
    $odb = new EvaluationObjectDB();
    //if($odb->getGlobalPerm()=="root"){
    //  $myuserid = 0;
    //}
    //else{
    //   $myuserid = $user->id;
    //}
    //if($question->getParentID()==$myuserid){
    //   $loesch=1;
    if( !mb_strstr($this->command, "create") ) {
        $showDelete = YES;
        $input2 = Button::createAccept(_('Löschen'),
                'template_delete_button');
    }

    $table = new HTM( "table" );
    $table->attr ("border","0");
    $table->attr ("align", "center");
    $table->attr ("cellspacing", "0");
    $table->attr ("cellpadding", "3");
    $table->attr ("width", "100%");
    $tr = new HTM( "tr" );
    $td = new HTM( "td" );
    $td->attr( "class", "content_body" );
    $td->attr( "align", "center" );
    $td->cont( $input );
    $tr->cont( $td );

    if( $showDelete ) {
   $td = new HTM( "td" );
   $td->attr( "class", "content_body" );
   $td->attr( "align", "center" );
   $td->cont( $input2 );
   $tr->cont( $td );
    }
    $table->cont( $tr );
    $form->cont( $table );

    $tdA->cont( $form );
    $trA->cont( $tdA );
    $tableA->cont( $trA );
    return $tableA;
  }


 /**
  * create a blue headline
  */
 function createHeadline( $text ) {
     $div = new HTM( "div" );
     $div->attr( "class", "eval_title" );
     $div->attr( "style", "margin-bottom:4px; margin-top:4px;" );
     $div->cont( $text );
     return $div;
 }

 /**
  * create a fat-printed sub headline with some space
  */
 function createSubHeadline( $text ) {
     $div = new HTM( "div" );
     $div->attr( "style", "margin-bottom:4px; margin-top:4px;" );
     $b = new HTM( "b" );
     $b->cont( $text );
     $div->cont( $b );
     return $div;
 }




  /**
   * creates the infobox
   *
   */
  function createInfoBox () {
      global $evalID, $rangeID;

      if (get_Username($rangeID)) {
          $rangeID = get_Username($rangeID);
      }

      if (empty($rangeID)) {
          $rangeID = get_Username($user->id);
      }

      $actions = new ActionsWidget();
      $actions->addLink(_('Vorschau der Evaluation'),
          URLHelper::getURL('show_evaluation.php',
              ['evalID' => $evalID,
               'isPreview' => YES]), Icon::create('question-circle', 'clickable'),
          ['target' => $evalID,
          'onClick' => "openEval('".$evalID."'); return false;"]);
      $actions->addLink(_('Zurück zur Evaluations-Verwaltung'), URLHelper::getURL('admin_evaluation.php',
          ['page' => 'overview',
           'check_abort_creation_button' => '1',
          'evalID' => $evalID,
          'rangeID' => $rangeID]),
          Icon::create('link-intern', 'clickable'));
      Sidebar::Get()->addWidget($actions);
  }


# Define private functions ================================================== #

  /**
   * creates a new answer
   *
   * @access  private
   * @return  array    the created answer as an array with keys 'answer_id' => new md5 id,
   *                                                            'text' => "",
   *                                                            'counter' => 0,
   *                                                            'correct' => NO
   */
  function makeNewAnswer( ) {
      return [ 'answer_id' => md5(uniqid(rand())),
          'text'      => rand()
          ];
  }



  /**
   * deletes the answer at position 'pos' from the array 'answers'
   * and modifies the array 'deleteAnswers' respectively
   *
   * @access  public
   * @param   array  &$answers        the answerarray
   * @param   array  &$deleteAnswers  the array containing the deleteCheckbox-bool-value for each answer
   * @param   int    $pos             the position of the answer to be deleted
   *
   */
  function deleteAnswer( $pos, &$answers, &$deleteAnswers ) {

      unset( $answers[$pos] );
      if( is_array( $deleteAnswers ) )
     unset( $deleteAnswers[$pos] );

      for( $i=$pos; $i<count($answers); $i++ ) {

     if( !isset( $answers[$i] ) ) {
         $answers[$i] = $answers[$i+1];
         unset( $answers[$i+1] );
         if( is_array( $deleteAnswers ) ) {
        $deleteAnswers[$i] = $deleteAnswers[$i+1];
        unset( $deleteAnswers[$i+1] );
         }
     }
      }
      return;
  }

  /**
   * checks which button was pressed
   *
   * @access  public
   * @returns string   the command "add_answers", "delete_answers", etc.
   *
   */
  function getPageCommand() {
      foreach( $_REQUEST as $key => $value ) {
    if( preg_match( "/template_(.*)_button?/", $key, $command ) )
         break;
      }

     $return_command = $command[1];

     // extract the command if theres a questionID in the commandname
     if ( preg_match( "/(.*)_#(.*)/", $return_command, $new_command ) )
        $return_command = $new_command[1];


      return  $return_command;
  }


   /**
    * Checks if a template with the same name already exists and modifies the
    * text of the template if necessary.
    * @param    object   $template   The template
    * @param    object   $db         The EvaluationQuestionDB
    * @param    string   $myuserid   My userid
    * @param    boolean  $rootTag    If yes, add the root tag if necessary
    * @access   private
    */
   function setUniqueName (&$question, $db, $myuserid, $rootTag = NO) {
      $text = $question->getText ();

      /* Add root tag if necessary ----------------------------------------- */
      //if ($rootTag && $myuserid == "0" && !mb_strstr ($text, EVAL_ROOT_TAG))
      //   $question->setText ($text." ".EVAL_ROOT_TAG);
      /* ------------------------------------------------- end: add root tag */

      /* Remove root tag if necessary -------------------------------------- */
      if ($myuserid != "0" && mb_strstr ($text, EVAL_ROOT_TAG)) {
         $question->setText  (trim(implode("", explode(EVAL_ROOT_TAG,$text))));
      }
      /* ---------------------------------------------- end: remove root tag */

      /* Change text if necessary with increasing number ------------------- */
      $originalName = $question->getText ();
      for ($i = 1; $db->titleExists ($question->getText (), $myuserid); $i++) {
         $question->setText ($originalName." (".$i.")");
      }
      /* -------------------------------------------------- end: change text */
   }

# ==================================================== end: private functions #

}

# Define constants ========================================================== #
/**
 * @const EVAL_ROOT_TAG Specifies the string for taging root templates
 * @access public
 */
define ('EVAL_ROOT_TAG', "[R]");
# ===================================================== end: define constants #

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;
# ====================================================== end: including files #
