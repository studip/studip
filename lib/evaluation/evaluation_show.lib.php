<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Library for evaluation participation page
 *
 * @author      mcohrs <michael A7 cohrs D07 de>
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

use Studip\Button, Studip\LinkButton;

class EvalShow
{

  /**
   * createEvaluationHeader: generate the head of an evaluation (title and base text)
   * @param   the evaluation
   * @returns a table row
   */
  function createEvaluationHeader( $eval, $votedNow, $votedEarlier )
  {
      $br = new HTMpty( "br" );

      $tr = new HTM( "tr" );
      $td = new HTM( "td" );
      $td->attr( "class", "table_row_even" );

      $table2 = new HTM( "table" );
      $table2->attr( "width", "100%" );
      $tr2 = new HTM( "tr" );
      $td2 = new HTM( "td" );
      $td2->attr( "width", "90%" );
      $td2->attr( "valign", "top" );

      if( $eval->isError() ) {
      $td2->html( EvalCommon::createErrorReport ($eval, _("Fehler")) );
      $td2->html( $br );
      }

      $span = new HTM( "span" );
      $span->attr( "class", "eval_title" );
      $span->html( htmlReady($eval->getTitle()) );
      $td2->cont( $span );
      $td2->cont( $br );

      $td2->cont( $br );
      if( $votedNow ) {
          $message = new HTML('div');
          $message->_content = [(string) MessageBox::success(_("Vielen Dank für Ihre Teilnahme."))];
          $td2->cont($message);
      } elseif( $votedEarlier ) {
          $message = new HTML('div');
          $message->_content = [(string) MessageBox::info(_("Sie haben an dieser Evaluation bereits teilgenommen."))];
          $td2->cont($message);
      } else {
          $td2->html( formatReady($eval->getText()) );
          $td2->cont( $br );
      }
      $tr2->cont( $td2 );
      $voted = $votedNow || $votedEarlier;
      $message = new HTML('div');
      $message->_content = [(string)MessageBox::info(EvalShow::getAnonymousText($eval, $voted))];
      $table2->cont($message);
      $message = new HTML('div');
      $message->_content = [(string)EvalShow::getStopdateText( $eval, $voted)];
      $table2->cont($message);
    
      if(!$voted && $GLOBALS["mandatories"] != 0) {
          $message = new HTML('div');
          $message->_content = [(string)sprintf(_("Mit %s gekennzeichnete Fragen müssen beantwortet werden."),
              "<b><span class=\"eval_error\">**</span></b>")];
          $table2->cont($message);
      }
    
    

      $td->cont( $table2 );
      $tr->cont( $td );

      return $tr;
  }
    

  /**
   * createEvaluation: generate the evaluation itself (questions and answers)
   * @param   the evaluation
   * @returns a table row
   */
  function createEvaluation( $tree ) {
      $tr = new HTM( "tr" );
      $td = new HTM( "td" );
      $td->attr( "class", "table_row_even" );
      $td->html( "<hr noshade=\"noshade\" size=\"1\">\n" );

      ob_start();
        $tree->showTree();
        $html = ob_get_contents();
      ob_end_clean();

      $td->html( $html );
      $td->setTextareaCheck();
      $tr->cont( $td );

      return $tr;
  }



  /**
   * create html for the meta-information about an evaluation.
   * @param    Object $eval          The evaluation
   * @param    bool   $isAssociated  whether the current user has used the eval
   * @returns  String                a table row
   */
  function createEvalMetaInfo( $eval, $votedNow = NO, $votedEarlier = NO ) {
      $html     = "";
      $stopdate = $eval->getRealStopdate();
      $number   = EvaluationDB::getNumberOfVotes( $eval->getObjectID() );
      $voted    = $votedNow || $votedEarlier;

      $html .= "<div align=\"left\" style=\"margin-left:3px; margin-right:3px;\">\n";
      $html .= "<hr noshade=\"noshade\" size=\"1\">\n";

#      $html .= $votedEarlier ? _("Sie haben an dieser Evaluation bereits teilgenommen.") : "";
#      $html .= $votedNow ? _("Vielen Dank für Ihre Teilnahme.") : "";
#      $html .= $voted ? "<hr noshade=\"noshade\" size=\"1\">\n" : "";

      /* multiple choice? ----------------------------------------------------- */
#      if ($eval->isMultipleChoice()) {
#     $html .= ($voted || $eval->isStopped())
#         ? _("Sie konnten mehrere Antworten auswählen.")
#         : _("Sie können mehrere Antworten auswählen.");
#     $html .= " \n";
#      }
      /* ---------------------------------------------------------------------- */

      $html .= EvalShow::getNumberOfVotesText( $eval, $voted );
      $html .= "<br>";
      $html .= EvalShow::getAnonymousText( $eval, $voted );
      $html .= "<br>";
      $html .= EvalShow::getStopdateText( $eval, $voted );

      $html .= "<br>\n";
      $html .= "</div>\n";
      /* ---------------------------------------------------------------------- */

      /* create html tr object ------------------------------------------------ */
      $tr = new HTM( "tr" );
      $td = new HTM( "td" );
      $td->attr( "align", "left" );
      $td->attr( "style", "font-size:0.8em;" );
      $td->html( $html );
      $tr->cont( $td );
      return $tr;
  }


  function getNumberOfVotesText( $eval, $voted ) {
      $stopdate = $eval->getRealStopdate();
      $number   = EvaluationDB::getNumberOfVotes( $eval->getObjectID() );
      $html = "";

      /* Get number of participants ------------------------------------------- */
      if( $stopdate < time() && $stopdate > 0 ) {
      if ($number != 1)
          $html .= sprintf (_("Es haben insgesamt <b>%s</b> Personen teilgenommen"), $number);
      else
          $html .= $voted
          ? sprintf (_("Sie waren die einzige Person die teilgenommen hat"))
          : sprintf (_("Es hat insgesamt <b>eine</b> Person teilgenommen"));
      }
      else {
      if ($number != 1)
          $html .= sprintf (_("Es haben bisher <b>%s</b> Personen teilgenommen"), $number);
      else
          $html .= $voted
          ? sprintf (_("Sie waren bisher der/die einzige Person die teilgenommen hat"))
          : sprintf (_("Es hat bisher <b>eine</b> Person teilgenommen"));
      }
      /* ---------------------------------------------------------------------- */

      if ($voted && $number > 1)
      $html .= _(", Sie ebenfalls");

      $html .= ".\n";
      return $html;
  }

  function getStopdateText( $eval, $voted ) {
      $stopdate = $eval->getRealStopdate();
      $html = "";

      /* stopdate ------------------------------------------------------------- */
      if (!empty ($stopdate)) {
      if( $stopdate < time() ) {
          $html .=  sprintf (_("Die Evaluation wurde beendet am <b>%s</b> um <b>%s</b> Uhr."),
                 date ("d.m.Y", $stopdate),
                 date ("H:i", $stopdate));
      }
      else {
          if( $voted ) {
          $html .= sprintf (_("Die Evaluation wird voraussichtlich beendet am <b>%s</b> um <b>%s</b> Uhr."),
                    date ("d.m.Y", $stopdate),
                    date ("H:i", $stopdate));
          }
          else {
          $html .= sprintf (_("Sie können teilnehmen bis zum <b>%s</b> um <b>%s</b> Uhr."),
                    date ("d.m.Y", $stopdate),
                    date ("H:i", $stopdate));
          }
      }
      }
      else {
      $html .= _("Der Endzeitpunkt dieser Evaluation steht noch nicht fest.");
      }
      $html .= " \n";

      return $html;
  }

  function getAnonymousText( $eval, $voted ) {
      $stopdate = $eval->getRealStopdate();
      $html = "";

      /* Is anonymous --------------------------------------------------------- */
      if( ($stopdate < time() && $stopdate > 0) ||
      $voted )
      $html .= ($eval->isAnonymous())
          ? _("Die Teilnahme war anonym.")
          : _("Die Teilnahme war <b>nicht</b> anonym.");
      else
      $html .= ($eval->isAnonymous())
          ? _("Die Teilnahme ist anonym.")
#         : _("Die Teilnahme ist <b>nicht</b> anonym.");
          : ("<span style=\"color:red;\">" .
         _("Dies ist eine personalisierte Evaluation. Ihre Angaben werden verknüpft mit Ihrem Namen gespeichert.") .
         "</span>");

      return $html;
  }


  /**
   * createEvaluationFooter: generate the foot of an evaluation (buttons etc.)
   * @param   the evaluation
   * @returns a table row
   */
  function createEvaluationFooter( $eval, $voted, $isPreview ) {
      global $auth;
      if( $isPreview )
      $voted = YES;

      $br = new HTMpty( "br" );

      $tr = new HTM( "tr" );
      $td = new HTM( "td" );
      $td->attr( "class", "content_body" );
      $td->attr( "align", "center" );
      $td->attr( "data-dialog-button","");
      $td->cont( $br );

      /* vote button */
      if( ! $voted ) {
          $button = Button::createAccept(_('Abschicken'),
                'voteButton',
                ['title' => _('Senden Sie Ihre Antworten hiermit ab.'), 'data-dialog' => '']);
         $td->cont( $button );
      }

      /* close button */
      if (!Request::isXHR()) {
         $button = new HTM( "p" );
         $button->cont( _("Sie können dieses Fenster jetzt schließen.") );
         $td->cont( $button );
      }


      /* reload button */
      if( $isPreview ) {
         $button = LinkButton::create(_('Aktualisieren'),
                URLHelper::getURL('show_evaluation.php?evalID='.$eval->getObjectID().'&isPreview=1'),
                ['title' => _('Vorschau aktualisieren.')]);
         $td->cont( $button );
      }

      $td->cont( $br );
      $td->cont( $br );

      $tr->cont( $td );

      return $tr;
  }

   function createVoteButton ($eval) {
      $button = LinkButton::create(_('Anzeigen'),
              URLHelper::getURL('show_evaluation.php?evalID=' .$eval->getObjectID().'&isPreview=' . NO),
              ['title' => _('Evaluation anzeigen.'),
                  'onClick' => 'openEval(\''.$eval->getObjectID().'\'); return false;']);
      $div = new HTML ("div");
      $div->addHTMLContent( $button );
      return $div;

      // keine Ahnung warum das hier nicht funktioniert, bekomme eine JS-Fehlermeldung :(

      // <grusel> das da oben reicht ja auch :)

      /*
      $script = new HTML ("script");
      $script->addAttr ("type", "text/javascript");
      $script->addAttr ("language", "JavaScript");

      $aScript = new HTML ("a");
      $aScript->addAttr ("href", "javascript:void();");
      $aScript->addAttr ("onClick",
        "window.open(\'show_evaluation.php?evalID=".$eval->getObjectID ()."\', ".
        "\'_blank\', ".
        "\'width=790,height=500,scrollbars=yes,resizable=yes\');");
      $aScript->addContent ("Teilnehmen"); // Eigentlich kommt hier ein button hin
      $script->addContent ("document.write ('");
      $script->addContent ($aScript);
      $script->addContent ("');");

      $noscript = new HTML ("noscript");
      $aNoScript = new HTML ("a");
      $aNoScript->addAttr ("href", "show_evaluation.php?evalID=".$eval->getObjectID ());
      $aNoScript->addAttr ("target", "_blank");
      $aNoScript->addContent ("Teilnehmen"); // Eigentlich kommt hier ein button hin
      $noscript->addContent ($aNoScript);

      $div = new HTML ("div");
      $div->addContent ($script);
      $div->addContent ($noscript);
      $tr = new HTML ("tr");
      $td = new HTML ("td");
      $td->addContent($script);
      $td->addContent($noscript);
      $tr->addContent($td);
      return $tr;
      */
   }

   function createEditButton ($eval) {
         $button = LinkButton::create(_('Bearbeiten'),
                 URLHelper::getURL(EVAL_FILE_ADMIN."?page=edit&evalID=".$eval->getObjectID()),
                 ['title' => _('Evaluation bearbeiten.')]);
         return $button;
   }

   function createOverviewButton ($rangeID, $evalID) {
         $button = LinkButton::create(_('Bearbeiten'),
                 URLHelper::getURL(EVAL_FILE_ADMIN."?rangeID=".$rangeID."&openID=".$evalID."#open"),
                 ['title' => _('Evaluationsverwaltung.')]);
         return $button;
   }

   function createDeleteButton ($eval) {
         $button = LinkButton::create(_('Löschen'),
                 URLHelper::getURL(EVAL_FILE_ADMIN."?evalAction=delete_request&evalID=".$eval->getObjectID ()),
                 ['title' => _('Evaluation löschen.')]);
         return $button;
   }

   function createStopButton ($eval) {
         $button = LinkButton::createCancel(_('Stop'),
                 URLHelper::getURL(EVAL_FILE_ADMIN."?evalAction=stop&evalID=".$eval->getObjectID ()),
                 ['title' => _('Evaluation stoppen.')]);
         return $button;
   }

   function createContinueButton ($eval) {
         $button = LinkButton::create(_('Fortsetzen'),
                 URLHelper::getURL(EVAL_FILE_ADMIN."?evalAction=continue&evalID=".$eval->getObjectID ()),
                 ['title' => _('Evaluation fortsetzen')]);
         return $button;
   }

   function createExportButton ($eval) {
         $button = LinkButton::create(_('Export'),
                 URLHelper::getURL(EVAL_FILE_ADMIN."?evalAction=export_request&evalID=".$eval->getObjectID ()),
                 ['title' => _('Evaluation exportieren.')]);
         return $button;
   }

   function createReportButton($eval) {
       return LinkButton::create(_('Auswertung'), URLHelper::getURL("eval_summary.php?eval_id=" . $eval->getObjectID()), ['title' => _('Auswertung')]);
   }

  /* ----------------------------------------------------------------------- */
}

# Define constants ========================================================== #
# ===================================================== end: define constants #


# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;
require_once EVAL_LIB_COMMON;
# ====================================================== end: including files #
