<?php

# Lifter002: TODO
# Lifter005: TODO
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

use Studip\Button,
    Studip\LinkButton;

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;
require_once EVAL_LIB_COMMON;
require_once EVAL_LIB_SHOW;
require_once EVAL_FILE_EXPORTMANAGERCSV;
# ====================================================== end: including files #
# Define constants ========================================================== #
/**
 * @const EVAL_TITLE Blah...
 */
define("EVAL_TITLE", _("Evaluations-Verwaltung"));
# ===================================================== end: define constants #

/**
 * Library for the overview of all existing evaluations
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvalOverview {
# Define all required variables ============================================= #
    /**
     * Databaseobject
     * @access   private
     * @var      object DatabaseObject $db
     */

    var $db;

    /**
     * Permobject
     * @access   private
     * @var      object Perm $perm
     */
    var $perm;

    /**
     * Userobject
     * @access   private
     * @var      object User $user
     */
    var $user;
# ============================================================ end: variables #
# Define constructor and destructor ========================================= #
    /**
     * Constructor
     * @access   public
     * @param    object  DatabaseObject $db    The database object
     * @param    object  Perm $perm  The permission object
     * @param    object  User $user  The user object
     */

    function __construct($db, $perm, $user) {
        /* Set default values ------------------------------------------------- */
        $this->db = $db;
        $this->perm = $perm;
        $this->user = $user;
        /* -------------------------------------------------------------------- */
    }

# =========================================== end: constructor and destructor #
# Define public functions =================================================== #
    /**
     *
     */

    function createMainTable() {
        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "0");
        $table->addAttr("width", "100%");
        $table->addAttr("style", "border:5px solid white;");
        return $table;
    }

    /**
     * Creates the funny blue small titlerows
     * @access public
     * @param  array   $rowTitles  An array with all col-titles
     * @param  boolean $returnRow  If YES it returns the row not the table
     * @param  string  $state
     */
    function createGroupTitle($rowTitles, $returnRow = false, $state = false) {
        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "2");
        $table->addAttr("width", "100%");

        $tr = new HTML("tr");

        if ($state == "user_template")
            $style = "steel_with_table_row_even_bg";
        elseif ($state == "public_template")
            $style = "eval_grey_border";
        else
            $style = "table_header";

        for ($i = 0; $rowTitles != NULL; $i++) {

            $td = new HTML("td");
            $td->addAttr("style", "vertical-align:bottom; font-weight:bold; white-space:nowrap");
            $td->addAttr("nowrap", "nowrap");
            $td->addAttr("height", "22");
            $td->addAttr("class", $style);

            if ($i == 0) {
                $td->addAttr("width", $state == "public_template" ? "1" : "10");
                $td->addHTMLContent("&nbsp;");
            } else {
                if ($i == 2 && $state == "user_template") {
                    // the title
                    $td->addAttr("width", "100%");
                    $td->addAttr("align", "left");
                } elseif ($i == 2 && $state == "public_template") {
                    // the title
                    $td->addAttr("width", "100%");
                    $td->addAttr("align", "left");
                } elseif ($i > 1) {
                    $td->addAttr("width", "96");
                    $td->addAttr("align", "center");
                } elseif ($i == 1 && $state == "public_template") {
                    // the preview
                    $td->addAttr("width", "20");
                    $td->addAttr("align", "left");
                    $td->addHTMLContent("&nbsp;");
                } else {
                    $td->addAttr("align", "left");
                }
                $title = array_shift($rowTitles);
                $title = empty($title) ? "&nbsp;" : $title;
                $td->addHTMLContent($title);
            }

            # filter out not needed headlines
            if ($state == "user_template" &&
                    ( ($i == 4) || ($i == 5) )) {
                //nothing
            } elseif ($state == "public_template" &&
                    ( ($i == 6) || ($i == 7) || ($i == 9))) {
                //nothing
            } else
                $tr->addContent($td);
        } // for

        $table->addContent($tr);

        return $returnRow ? $tr : $table;
    }

    /**
     * Test...
     * @access public
     * @param  object  Evaluation  $eval  The evaluation
     * @param  string  $number
     * @param  string  $state
     * @param  string  $open
     * @param  boolean $returnRow
     */
    function createEvalRow($eval, $number, $state, $open, $returnRow = false) {

        /* initialize variables -------- */
        $evalID = $eval->getObjectID();
        $numberOfVotes = EvaluationDB::getNumberOfVotes($evalID);

        $no_permissons = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);

        if ($eval->getAuthor() != $user->id && $no_permissons)
            $no_buttons = 1;

        $style = ($number % 2) ? "table_row_odd" : ($number == 0 ? "content_body" : "table_row_even");

        $startDate = $eval->getStartdate() == NULL ? " " : date("d.m.Y", $eval->getStartdate());

        $stopDate = $eval->getRealStopdate() == NULL ? " " : date("d.m.Y", $eval->getRealStopdate());

        $link = "?rangeID=" . $_SESSION["rangeID"];
        if ($open == NO)
            $link .= '&openID=' . $evalID . '#open';

        $titleLink = new HTML('a');
        $titleLink->addAttr('href', URLHelper::getLink($link));
        $arrowLink = new HTML('a');
        $arrowLink->addAttr('href', URLHelper::getLink($link));

        $titleLink->addContent(($eval->getTitle()) ? $eval->getTitle() : ' ' );

        switch ($state) {

            case "public_template":
                $arrowLink = "&nbsp;";
                $titleLink = $eval->getTitle() ? $eval->getTitle() : " ";
                $content[0] = $eval->getFullname() ? $eval->getFullname() : " ";
                $content[1] = $eval->getChangedate() == NULL ? " " : date("d.m.Y", $eval->getChangedate());

                $button = LinkButton::create(_('Vorschau'), URLHelper::getURL('show_evaluation.php?evalID=' . $evalID . '&isPreview=' . YES), ['title' => _('Vorschau dieser öffentlichen Evaluationsvorlage.'),
                            'onClick' => 'openEval(\'' . $evalID . '\'); return false;']);
                $div = new HTML("div");
                $div->addHTMLContent($button);
                $content[4] = $div;

                $content[2] = $eval->isAnonymous() ? EvalCommon::createImage(EVAL_PIC_YES, _("ja")) : EvalCommon::createImage(EVAL_PIC_NO, _("nein"));

                $copyButton = new HTMLempty("input");
                $copyButton->addAttr("style", "vertical-align:middle;");
                $copyButton->addAttr("type", "image");
                $copyButton->addAttr("name", "copy_public_template_button");
                $copyButton->addAttr("src", Icon::create('arr_2down', 'sort')->asImagePath());
                $copyButton->addAttr("border", "0");
                $copyButton->addAttr("alt", _("Kopieren"));
                $copyButton->addAttr("title", _("Diese öffentliche Evaluationsvorlagen zu den eigenen Evaluationsvorlagen kopieren"));
                $content[5] = $copyButton;

                break;

            case "user_template":
                $arrowLink->addContent(EvalCommon::createImage(($open ? EVAL_PIC_ARROW_TEMPLATE_OPEN : EVAL_PIC_ARROW_TEMPLATE), _("Aufklappen")));
                $isShared = $eval->isShared() ? YES : NO;
                $shareButton = new HTMLempty("input");
                $shareButton->addAttr("style", "vertical-align:middle;");
                $shareButton->addAttr("type", "image");
                $shareButton->addAttr("name", "share_template_button");
                $shareButton->addAttr("src", $isShared ? EVAL_PIC_SHARED : EVAL_PIC_NOTSHARED );
                $shareButton->addAttr("border", "0");
                $shareButton->addAttr("alt", $isShared ? _("als öffentliche Evaluationsvorlage Freigeben") : _("Freigabe entziehen"));
                $shareButton->addAttr("title", $isShared ? _("Die Freigabe für diese Evaluationsvorlage entziehen") : _("Diese Evaluationsvorlage öffentlich freigeben"));

                $content[0] = $shareButton;
                $content[3] = Button::create(_('Kopie erstellen'), 'copy_own_template_button', ['title' => _('Evaluationsvorlage kopieren')]);

                $content[4] = LinkButton::create(_('Bearbeiten'), URLHelper::getURL("admin_evaluation.php?page=edit&evalID=" . $evalID), ['title' => _('Evaluation bearbeiten')]);

                $content[5] = Button::create(_('Löschen'), 'delete_request_button', ['title' => _('Evaluation löschen')]);
                break;

            case EVAL_STATE_NEW:
                $arrowLink->addContent(EvalCommon::createImage(($open ? EVAL_PIC_ARROW_NEW_OPEN : EVAL_PIC_ARROW_NEW), _("Aufklappen")));
                $content[0] = $eval->getFullname() ? $eval->getFullname() : " ";
                $content[1] = $startDate;
                if (!$no_buttons) {
                    $content[2] = Button::create(_('Start'), 'start_button', ['title' => _('Evaluation starten')]);

                    $content[4] = LinkButton::create(_('Bearbeiten'), URLHelper::getURL("admin_evaluation.php?page=edit&evalID=" . $evalID), ['title' => _('Evaluation bearbeiten')]);

                    $content[5] = Button::create(_('Löschen'), 'delete_request_button', ['title' => _('Evaluation löschen')]);
                }
                break;

            case EVAL_STATE_ACTIVE:
                $arrowLink->addContent(EvalCommon::createImage(($open ? EVAL_PIC_ARROW_RUNNING_OPEN : EVAL_PIC_ARROW_RUNNING), _("Aufklappen")));
                $content[0] = $eval->getFullname() ? $eval->getFullname() : " ";
                $content[1] = $stopDate;
                if (!$no_buttons) {
                    $content[2] = Button::createCancel(_('Stop'), 'stop_button', ['title' => _('Evaluation stoppen')]);
                    ;
                    // Kann hier noch optimiert werden, da hasVoted () immer einen DB-Aufruf startet
                    $content[3] = ($eval->hasVoted()) ? Button::create(_('Zurücksetzen'), 'restart_request_button', ['title' => _('Evaluation zurücksetzen')]) : Button::create(_('Zurücksetzen'), 'restart_confirmed_button', ['title' => _('Evaluation zurücksetzen')]);
                    $content[4] = Button::create(_('Export'), 'export_request_button', ['title' => _('Evaluation exportieren')]);
                    $content[5] = Button::create(_('Löschen'), 'delete_request_button', ['title' => _('Evaluation löschen')]);
                    //$content[6] = EvalCommon::createSubmitButton ("auswertung", _("Auswertung"), "export_gfx_request_button");
                    $content[6] = LinkButton::create(_('Auswertung'), URLHelper::getURL("eval_summary.php?eval_id=" . $evalID), ['title' => _('Auswertung')]);
                }
                break;

            case EVAL_STATE_STOPPED:
                $arrowLink->addContent(EvalCommon::createImage(($open ? EVAL_PIC_ARROW_STOPPED_OPEN : EVAL_PIC_ARROW_STOPPED), _("Aufklappen")));
                $content[0] = $eval->getFullname() ? $eval->getFullname() : " ";
                //$content[1] = $eval->isVisible() ? "yes" : "no";
                if (!$no_buttons) {
                    $content[2] = Button::create(_('Fortsetzen'), 'continue_button', ['title' => _('Evaluation fortsetzen')]);
                    $content[3] = ($eval->hasVoted()) ? Button::create(_('Zurücksetzen'), 'restart_request_button', ['title' => _('Evaluation zurücksetzen')]) : Button::create(_('Zurücksetzen'), 'restart_confirmed_button', ['title' => _('Evaluation zurücksetzen')]);
                    $content[4] = Button::create(_('Export'), 'export_request_button', ['title' => _('Evaluation exportieren')]);
                    $content[5] = Button::create(_('Löschen'), 'delete_request_button', ['title' => _('Evaluation löschen')]);
                    //$content[6] = EvalCommon::createSubmitButton ("auswertung", _("Auswertung"), "export_gfx_request_button");
                    $content[6] = LinkButton::create(_('Auswertung'), URLHelper::getURL("eval_summary.php?eval_id=" . $evalID), ['title' => _('Auswertung')]);
                }
                break;
        }

        $form = new HTML("form");
        $form->addAttr("action", URLHelper::getLink("?rangeID=" . $_SESSION["rangeID"]));
        $form->addAttr("method", "post");
        $form->addAttr("style", "display:inline;");
        $form->addHTMLContent(CSRFProtection::tokenTag());

        $input = new HTMLEmpty("input");
        $input->addAttr("type", "hidden");
        $input->addAttr("name", "evalID");
        $input->addAttr("value", $evalID);

        $form->addContent($input);


        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "2");
        $table->addAttr("width", "100%");

        $tr = new HTML("tr");
        $tr->addAttr("align", "center");

        /* opening arrow */
        $td = new HTML("td");
        $td->addAttr("class", $style);
        $td->addAttr("width", "10");
        if ($open) {
            $anchor = new HTML("a");
            $anchor->addAttr("name", "open");
            $anchor->addContent($arrowLink);
            $td->addContent($anchor);
        } else {
            $td->addHTMLContent($arrowLink);
        }

        if ($state != "public_template")
            $tr->addContent($td);
        else {
            $td = new HTML("td");
            $td->addAttr("class", $style);
            $td->addAttr("width", "1");

            // create a blindgif
            $blingif = new HTMLempty("img");
            $blingif->addAttr("border", "0");
            $blingif->addAttr("valign", "middle");
            $blingif->addAttr("width", "1");
            $blingif->addAttr("height", "24");
            $blingif->addAttr("alt", " ");
            $blingif->addAttr("src", Assets::image_path('forumleer.gif'));
            $td->addContent($blingif);
            $tr->addContent($td);
        }

        /* preview icon */
        $td = new HTML("td");
        $td->addAttr("width", $state == "public_template" ? "20" : "1%");
        $td->addAttr("class", $style);
        $td->addAttr("align", "left");
        $icon = EvalCommon::createImage(EVAL_PIC_PREVIEW, _("Vorschau"));
        $td->addContent(EvalCommon::createEvalShowLink($evalID, $icon, YES));
        $tr->addContent($td);

        /* title */
        $td = new HTML("td");
        $td->addAttr("class", $style);
        $td->addAttr("align", "left");
        if ($returnRow)
            $td->addAttr("width", "100%");

        $td->addContent($titleLink);

        if ($state == EVAL_STATE_ACTIVE || $state == EVAL_STATE_STOPPED) {
            $td->addContent("|");
            $font = new HTML("font");
            $font->addAttr("color", "#005500");
            $font->addContent($numberOfVotes);
            $td->addContent($font);
            $td->addContent("|");
        }
        $tr->addContent($td);
        /* the content fields */
        //for( $i = 0; $i < 6; $i++ ) {
        for ($i = 0; $i < 7; $i++) {
            $td = new HTML("td");
            $td->addAttr("width", "96");
            $td->addAttr("class", $style);
            $td->addAttr("nowrap", "nowrap");
            $td->addAttr("style", "white-space:nowrap");
            #if (is_object($content[$i]))
            $td->addContent(($content[$i] ? $content[$i] : " "));
            #$td->addHTMLContent ( ($content[$i] ? $content[$i] : "-") );
            # filter out not needed datacells
            if ($state == "user_template" &&
                    ( ($i == 1) || ($i == 2) )) {
                //nothing
            } elseif ($state == "public_template" &&
                    ( ($i == 3) || ($i == 4) )) {
                //nothing
            } else {
                $tr->addContent($td);
            }
        } // end: for

        $table->addContent($tr);
        if ($returnRow)
            $form->addContent($tr);
        else
            $form->addContent($table);

        return $form;
    }

    /**
     * Test...
     * @access public
     * @param  object  Evaluation  $eval  The evaluation
     */
    function createEvalContent($eval, $number, $state, $safeguard) {

        /* initialize variables -------- */
        $evalID = $eval->getObjectID();

        $style = ($number % 2) ? "table_row_odd" : "table_row_even";

        $startDate = $eval->getStartdate() == NULL ? " " : date("d.m.Y", $eval->getStartdate());

        $stopDate = $eval->getRealStopdate() == NULL ? " " : date("d.m.Y", $eval->getRealStopdate());

        switch ($state) {

            case EVAL_STATE_NEW:
                break;

            case EVAL_STATE_ACTIVE:
                break;

            case EVAL_STATE_STOPPED:
                break;
        }

        $form = new HTML("form");
        $form->addAttr("name", "settingsForm");
        $form->addAttr("action", URLHelper::getLink("?rangeID=" .
                        $_SESSION["rangeID"] . "&openID=" . $evalID . "#open"));
        $form->addAttr("method", "post");
        $form->addAttr("style", "display:inline;");
        $form->addHTMLContent(CSRFProtection::tokenTag());

        $input = new HTMLEmpty("input");
        $input->addAttr("type", "hidden");
        $input->addAttr("name", "evalID");
        $input->addAttr("value", $evalID);

        $form->addContent($input);

        $a = new HTMLEmpty("a");
        $a->addAttr("name", "open");

        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "2");
        $table->addAttr("width", "100%");

        $tr = new HTML("tr");
        $tr->addAttr("align", "center");

        $td = new HTML("td");
        $td->addAttr("class", $style);


        $table2 = new HTML("table");
        $table2->addAttr("align", "center");
        $table2->addAttr("cellspacing", "0");
        $table2->addAttr("cellpadding", "3");
        $table2->addAttr("width", "90%");

        $tr2 = new HTML("tr");
        $td2 = new HTML("td");
        $td2->addAttr("colspan", "2");
#$td2->addAttr ("style", "padding-bottom:0; border-top:1px solid black;");
        $td2->addAttr("align", "center");
        $td2->addAttr("class", ($number % 2 ? "table_row_odd" : "table_row_even"));

        $td2->addHTMLContent($safeguard);

        $globalperm = EvaluationObjectDB::getGlobalPerm();

        $no_permission = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);

        if (($globalperm == "root" || $globalperm == "admin") &&
                !Request::get("search") && $eval->isTemplate()) {
            // no RuntimeSettings and Save-Button for Template if there are no ranges
            $td2->addHTMLContent($this->createDomainSettings($eval, $state, $number % 2 ? "eval_grey_border" : "eval_light_border" ));
        } elseif ($no_permission) {
            // no RuntimeSettings if there are ranges with no permission
            $td2->addHTMLContent($this->createDomainSettings($eval, $state, $number % 2 ? "eval_grey_border" : "eval_light_border" ));

            $td2->addContent(new HTMLEmpty("br"));

            $saveButton = Button::create(_('Übernehmen'), 'save_button', ['title' => _('Einstellungen speichern')]);
            $td2->addContent($saveButton);
        } else {
            $td2->addHTMLContent($this->createRuntimeSettings($eval, $state, $number % 2 ? "eval_grey_border" : "eval_light_border" ));

            $td2->addHTMLContent($this->createDomainSettings($eval, $state, $number % 2 ? "eval_grey_border" : "eval_light_border" ));
            $td2->addContent(new HTMLEmpty("br"));

            $saveButton = Button::create(_('Übernehmen'), 'save_button', ['title' => _('Einstellungen speichern')]);

            $td2->addContent($saveButton);
        }

        if (!$eval->isTemplate()) {
            /* No Infotext for templates, it makes no sense */
            $show = new EvalShow ();
            $td2->addContent($show->createEvalMetaInfo($eval, NO, NO));
        }

        $tr2->addContent($td2);
        $table2->addContent($tr2);

        $td->addContent($table2);
        $tr->addContent($td);
        $table->addContent($tr);

#      $form->addContent ($a);
        $form->addContent($table);

        return $form;
    }


    /**
     *
     */
    function createHeader($safeguard, $templates = NULL, $foundTable = "") {
        Helpbar::Get()->addPlainText(_('Übersicht'), _('Auf dieser Seite haben Sie eine Übersicht aller in dem ausgewählten Bereich existierenden Evaluationen sowie Ihrer eigenen Evaluationsvorlagen.'));
        Helpbar::Get()->addPlainText(_('Ansicht'), _("Sie können eine Evaluation aufklappen und dann Bereichen zuordnen und ihre Laufzeit bestimmen."));
        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "5");
        $table->addAttr("width", "100%");

        /* create new ---------------------------------------------------------- */
        $actions = new ActionsWidget();
        $actions->addLink(_('Neue Evaluationsvorlage'),
                          URLHelper::getURL('?rangeID=' . $_SESSION['rangeID'] .'&page=edit&newButton=1'),
                          Icon::create('add', 'clickable'));
        Sidebar::get()->addWidget($actions);
        /* ----------------------------------------------------- end: create new */

        /* search template ----------------------------------------------------- */
        $tr = new HTML("tr");
        $td = new HTML("td");
        $td->addAttr("class", "table_row_odd");
        $td->addAttr("valign", "top");
        $td->addContent(EvalOverview::createSearchTemplateForm());
        $tr->addContent($td);
        $table->addContent($tr);
        /* --------------------------------------------------------- end: search */

        /* Show found templates ------------------------------------------------ */
        if ($foundTable) {
            $tr = new HTML("tr");
            $td = new HTML("td");
            $td->addAttr("class", "table_row_odd");
            $td->addContent($foundTable);
            $tr->addContent($td);
            $table->addContent($tr);
        }
        /* ------------------------------------------------- end: show templates */

        /* Show templates ------------------------------------------------------ */
        $tr = new HTML("tr");
        $td = new HTML("td");
        $td->addAttr("class", "content_body");
        $td->addContent(" ");
        $tr->addContent($td);
        $table->addContent($tr);
        $tr = new HTML("tr");
        $td = new HTML("td");
        $td->addAttr("valign", "top");
        $td->addAttr("class", "table_row_even");
        $td->addContent($templates ? $templates : " ");
        $tr->addContent($td);
        $table->addContent($tr);
        /* -------------------------------------------------- end: show templates */

        /* Create result ------------------------------------------------------- */
        $tr = new HTML("tr");
        $td = new HTML("td");
        $td->addAttr("class", "blank");

        $tr->addHTMLContent($safeguard);

        $td->addContent($table);
        $tr->addContent($td);
        /* --------------------------------------------------------- end: result */

        return $tr;
    }

    /**
     *
     */
    function createShowRangeForm() {

        $currentRangeID = $_SESSION['rangeID'];

        $form = new HTML("form");
        $form->addAttr('class', 'default');
        $form->addAttr("method", "post");
        $form->addAttr("action", URLHelper::getLink());
        $form->addHTMLContent(CSRFProtection::tokenTag());

        $form->addHTMLContent('<fieldset>');

        $headline = new HTML ("legend");
        $headline->addAttr("class","eval");
        $headline->addContent(_("Evaluationen"));
        $form->addContent($headline);

        $select = new HTML("select");
        $select->addAttr("name", "rangeID");
        $select->addAttr("style", "vertical-align:middle;");

        /* get allowed range id's for user */
        $rangeIDs = $this->db->getValidRangeIDs($this->perm, $this->user, $currentRangeID);
        /* add the currently shown range if neccessary */
        if (is_array($rangeIDs) && !in_array($currentRangeID, array_keys($rangeIDs))) {
            $rangeIDs[$currentRangeID] = ["name" => $this->db->getRangename($currentRangeID)];
        }

        foreach ($rangeIDs as $rangeID => $object) {
            $option = new HTML("option");
            if ($currentRangeID == $rangeID)
                $option->addAttr("selected", "selected");

            $option->addAttr("value", $rangeID);
            if (empty($object["name"]))
                $object["name"] = " ";
            $option->addHTMLContent(htmlReady($object["name"]));
            $select->addContent($option);
        }
        /* --------------------------------------------------------------------- */

        $form->addHTMLContent('<label>');
        $form->addContent(_("Evaluationen aus folgendem Bereich anzeigen"));
        $form->addContent($select);
        $form->addHTMLContent('</label>');
        $form->addContent(" ");
        $form->addContent(Button::create(_('Anzeigen'), ['title' => _('Evaluationen aus gewähltem Bereich anzeigen')]));
        $form->addContent(new HTMLempty("br"));

        /* search field for showing ranges (admin/root) */
        if ($GLOBALS["perm"]->have_perm("admin")) {
            $form->addHTMLContent('<label>');
            $form->addContent(_("Nach weiteren Bereichen suchen"));
            $input = new HTMLEmpty("input");
            $input->addAttr("type", "text");
            $input->addAttr("name", "search");
            $input->addAttr("size", "30");
            $form->addContent($input);
            $form->addHTMLContent('</label>');
            $form->addContent(Button::create(_('Suchen'), 'search_showrange_button', ['title' => _('Weitere Bereiche suchen')]));
        }

        $form->addHTMLContent('</fieldset>');

        return $form;
    }

    /**
     *
     */
    function createSearchTemplateForm() {
        $form = new HTML("form");
        $form->addAttr('class', 'default');
        $form->addAttr("method", "post");
        $form->addAttr("action", URLHelper::getLink("?rangeID=" . $_SESSION["rangeID"]));
        $form->addHTMLContent(CSRFProtection::tokenTag());

        $form->addHTMLContent('<fieldset><legend>');
        $form->addContent(_("Öffentliche Evaluationsvorlage suchen"));
        $form->addHTMLContent('</legend>');

        $form->addHTMLContent('<label>');
        $form->addContent(_("Name der Vorlage"));

        $input = new HTMLempty("input");
        $input->addAttr("type", "text");
        $input->addAttr("name", "templates_search");
        $input->addAttr("value", stripslashes($GLOBALS['templates_search']));
        $input->addAttr("style", "vertical-align:middle;");

        $form->addContent($input);
        $form->addHTMLContent('</label>');

        $form->addHTMLContent('</fieldset><footer>');
        $form->addContent(Button::create(_('Suchen'), 'search_template_button', ['title' => _('Öffentliche Vorlage suchen')]));
        $form->addHTMLContent('</footer>');

        return $form;
    }

    /**
     * Creates a gray col with text
     * @access public
     * @param  string  $text  The information
     */
    function createInfoCol($text) {
        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "2");
        $table->addAttr("width", "100%");

        $tr = new HTML("tr");

        $td = new HTML("td");
        $td->addAttr("class", "content_body");
        $td->addContent($text);

        $tr->addContent($td);

        $table->addContent($tr);
        return $table;
    }

    /**
     * Creates a row with black line above (and "open all evals" link...?)
     * @access public
     */
    function createClosingRow() {
        $tr = new HTML("tr");
        $tr->addAttr("height", "2");
        $td = new HTML("td");
        $td->addAttr("class", "content_body");
        $td->addContent("");
        $tr->addContent($td);

        return $tr;
    }

    /*
     * modifies the eval and calls createSafeguard
     *
     * @access private
     * @param evalAction   string comprised the action
     */

    function callSafeguard($evalAction, $evalID = "", $showrangeID = NULL, $search = NULL, $referer = NULL) {
        global $perm, $auth, $user;

        if (!($evalAction || $evalAction == "search"))
            return " ";

        if (!($perm->have_studip_perm("tutor", $showrangeID)) &&
                $user->id != $showrangeID &&
                !(isDeputyEditAboutActivated() && isDeputy($user->id, $showrangeID, true))) {
            return $this->createSafeguard("ausruf", sprintf(_("Sie haben keinen Zugriff auf diesen Bereich.")));
        }

        $evalDB = new EvaluationDB;
        $evalChanged = NULL;
        $safeguard = " ";

        /* Actions without any permissions ---------------------------------- */
        switch ($evalAction) {

            case "search_template":
                $search = trim($search);
                $templates = $evalDB->getPublicTemplateIDs($search);

                if (mb_strlen($search) < EVAL_MIN_SEARCHLEN) {
                    $report = EvalCommon::createReportMessage(sprintf(_("Bitte einen Suchbegriff mit mindestens %d Buchstaben eingeben."), EVAL_MIN_SEARCHLEN), EVAL_PIC_ERROR, EVAL_CSS_ERROR);
                } elseif (count($templates) == 0) {
                    $report = EvalCommon::createReportMessage(_("Es wurden keine passenden öffentlichen Evaluationsvorlagen gefunden."), EVAL_PIC_ERROR, EVAL_CSS_ERROR);
                } else {
                    $report = EvalCommon::createReportMessage(sprintf(_("Es wurde(n) %d passende öffentliche Evaluationsvorlagen gefunden."), count($templates)), EVAL_PIC_SUCCESS, EVAL_CSS_SUCCESS);
                }
                $safeguard .= $report->createContent();
                return $safeguard;

            case "export_request":
                /* Check permissions ------------------------------------------- */
                $haveNoPerm = YES;
                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
                $haveNoPerm = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);
                if ($haveNoPerm == YES) {
                    $report = EvalCommon::createReportMessage(_("Sie haben nicht die Berechtigung diese Evaluation zu exportieren."), EVAL_PIC_ERROR, EVAL_CSS_ERROR);
                    return $report->createContent();
                }
                /* -------------------------------------- end: check permissions */
            
                /* Export evaluation ------------------------------------------- */
                $exportManager = new EvaluationExportManagerCSV($evalID);
                $exportManager->export();
                /* -------------------------------------- end: export evaluation */


                /* Create report ----------------------------------------------- */
                if ($exportManager->isError()) {
                    $report = EvalCommon::createErrorReport($exportManager, _("Fehler beim Exportieren"));
                } else {
                    $report = EvalCommon::createReportMessage(_("Die Daten wurden erfolgreich exportiert. Sie können die Ausgabedatei jetzt herunterladen."), EVAL_PIC_SUCCESS, EVAL_CSS_SUCCESS);
                    $report = $report->createContent();
                    $report .= sprintf(_("Bitte klicken Sie %s um die Datei herunter zu laden.") . "<br><br>", '<a href="' .FileManager::getDownloadLinkForTemporaryFile($exportManager->getTempFilename(), $exportManager->getFilename()) . '">' . _("auf diese Verknüpfung") . '</a>');
                }
                $safeguard .= $report;
                /* ------------------------------------------ end: create report */

                return $safeguard;
        }
        /* ----------------------------------- end: actions without permissions */

        $eval = new Evaluation($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
        $evalName = htmlready($eval->getTitle());

        /* Check for errors while loading ------------------------------------- */
        if ($eval->isError()) {
            EvalCommon::createErrorReport($eval);
            return $this->createSafeguard("", EvalCommon::createErrorReport($eval));
        }
        /* -------------------------------------- end: errorcheck while loading */

        /* Check for permissions in all ranges of the evaluation -------------- */
        if (!$eval->isTemplate() && ($user->id != $eval->getAuthorID())) {

            $no_permisson = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);

            if ($no_permisson > 0) {
                if ($no_permisson == 1)
                    $no_permission_msg .= sprintf(_("Die Evaluation <b>%s</b> ist einem Bereich zugeordnet, für den Sie keine Veränderungsrechte besitzen."), $evalName);
                else
                    $no_permission_msg .= sprintf(_("Die Evaluation <b>%s</b> ist %s Bereichen zugeordnet, für die Sie keine Veränderungsrechte besitzen."), $evalName, $no_permisson);

                if ($evalAction != "save") {

                    $no_permission_msg .= " " . _("Der Besitzer wurde durch eine systeminterne Nachricht informiert.");

                    $sms = new messaging();
                    $sms->insert_message(
                            sprintf(_("Benutzer **%s** hat versucht eine unzulässige Änderung an Ihrer Evaluation **%s** vorzunehmen."), get_username($auth->auth["uid"]), $eval->getTitle()), get_username($eval->getAuthorID()), "____%system%____", FALSE, FALSE, "1", FALSE, _("Versuchte Änderung an Ihrer Evaluation"));
                }
            }
        } else if ($eval->isTemplate() &&
                $user->id != $eval->getAuthorID() &&
                $evalAction != "copy_public_template" &&
                $evalAction != "search_showrange") {

            $sms = new messaging();
            $sms->insert_message(
                    sprintf(_("Benutzer **%s** hat versucht eine unzulässige Änderung an Ihrem Template **%s** vorzunehmen."), get_username($auth->auth["uid"]), $eval->getTitle()), get_username($eval->getAuthorID()), "____%system%____", FALSE, FALSE, "1", FALSE, _("Versuchte Änderung an Ihrem Template"));
            return $this->createSafeguard("ausruf", sprintf(_("Sie besitzen keine Rechte für das Tempate <b>%s</b>. Der Besitzer wurde durch eine systeminterne Nachricht informiert."), $evalName));
        }
        /* ----------------------------------------- end: check for permissions */

        switch ($evalAction) {

            case "share_template":
                if ($eval->isShared()) {
                    $eval->setShared(NO);
                    $eval->save();
                    if ($eval->isError()) {
                        $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                        return $safeguard;
                    }
                    $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluationsvorlage <b>%s</b> kann jetzt nicht mehr von anderen Benutzern gefunden werden."), $evalName));
                } else {
                    $eval->setShared(YES);
                    $eval->save();
                    if ($eval->isError()) {
                        $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                        return $safeguard;
                    }
                    $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluationsvorlage <b>%s</b> kann jetzt von anderen Benutzern gefunden werden."), $evalName));
                }
                break;

            case "copy_public_template":
                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $newEval = $eval->duplicate();
                $newEval->setAuthorID($auth->auth["uid"]);
                $newEval->setShared(NO);
                $newEval->setStartdate(NULL);
                $newEval->setStopdate(NULL);
                $newEval->setTimespan(NULL);
                $newEval->removeRangeIDs();
                $newEval->save();
                if ($newEval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($newEval));
                    return $safeguard;
                }
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die öffentliche Evaluationsvorlage <b>%s</b> wurde zu den eigenen Evaluationsvorlagen kopiert."), $evalName));
                break;

            case "start":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht gestartet."));

                $eval->setStartdate(time() - 500);
                $eval->save();
                if ($eval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluation <b>%s</b> wurde gestartet."), $evalName));
                $evalChanged = YES;
                break;

            case "stop":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht beendet."));

                $eval->setStopdate(time());
                $eval->save();
                if ($eval->isError()) {
                    EvalCommon::createErrorReport($eval);
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluation <b>%s</b> wurde beendet."), $evalName));
                $evalChanged = YES;
                break;

            case "continue":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht fortgesetzt."));

                $eval->setStopdate(NULL);
                $eval->setStartdate(time() - 500);
                $eval->save();
                if ($eval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluation <b>%s</b> wurde fortgesetzt."), $evalName));
                $evalChanged = YES;
                break;

            case "restart_request":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht zurücksetzen."));

                $safeguard .= $this->createSafeguard("ausruf", sprintf(_("Die Evaluation <b>%s</b> wirklich zurücksetzen? Dabei werden alle bisher abgegebenen Antworten gelöscht!"), $evalName), "restart_request", $evalID, $showrangeID, $referer);
                break;

            case "restart_confirmed":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht zurücksetzen."));

                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $eval->resetAnswers();

                $evalDB->removeUser($eval->getObjectID());
                $eval->setStartdate(NULL);
                $eval->setStopdate(NULL);
                $eval->save();
                if ($eval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluation <b>%s</b> wurde zurückgesetzt."), $evalName));
                $evalChanged = YES;
                break;

            case "restart_aborted":
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluation <b>%s</b> wurde nicht zurückgesetzt."), $evalName), "", "", "", $referer);
                break;

            case "copy_own_template":
                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $newEval = $eval->duplicate();
                $newEval->setShared(NO);
                $newEval->save();
                if ($newEval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($newEval));
                    return $safeguard;
                }
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluationsvorlage <b>%s</b> wurde kopiert."), $evalName));
                break;

            case "delete_request":

                if ($no_permission_msg) {
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht gelöscht."));
                }

                $text = $eval->isTemplate() ? sprintf(_("Die Evaluationsvorlage <b>%s </b>wirklich löschen?"), $evalName) : sprintf(_("Die Evaluation <b>%s </b>wirklich löschen?"), $evalName);
                $safeguard .= $this->createSafeguard("ausruf", $text, "delete_request", $evalID, $showrangeID, $referer);
                break;

            case "delete_confirmed":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht gelöscht."));

                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $eval->delete();
                if ($eval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }

                $text = $eval->isTemplate() ? _("Die Evaluationsvorlage <b>%s</b> wurde gelöscht.") : _("Die Evaluation <b>%s</b> wurde gelöscht.");
                $safeguard .= $this->createSafeguard("ok", sprintf($text, $evalName), "", "", "", $referer);
                $evalChanged = YES;
                break;

            case "delete_aborted":
                $text = $eval->isTemplate() ? _("Die Evaluationsvorlage <b>%s</b> wurde nicht gelöscht.") : _("Die Evaluation <b>%s</b> wurde nicht gelöscht.");
                $safeguard .= $this->createSafeguard("ok", sprintf($text, $evalName), "", "", "", $referer);
                break;

            case "unlink_delete_aborted":
                $text = _("Die Evaluation <b>%s</b> wurde nicht verändert.");
                $safeguard .= $this->createSafeguard("ok", sprintf($text, $evalName), "", "", "", $referer);
                break;

            case "unlink_and_move":

                if ($no_permission_msg)
                    return $this->createSafeguard("ausruf", $no_permission_msg . "<br>" . _("Die Evaluation wurde nicht ausgehängt und zu den eigenen Evaluationsvorlagen verschoben."));

                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $eval->removeRangeIDs();
                $eval->setAuthorID($auth->auth["uid"]);
                $eval->resetAnswers();
                $evalDB->removeUser($eval->getObjectID());
                $eval->setStartdate(NULL);
                $eval->setStopdate(NULL);
                $eval->save();
                if ($eval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }
                $text = _("Die Evaluation <b>%s</b> wurde aus allen Bereichen ausgehängt und zu den eigenen Evaluationsvorlagen verschoben.");
                $safeguard .= $this->createSafeguard("ok", sprintf($text, $evalName), "", "", "", $referer);
                break;

            case "created":
                $safeguard .= $this->createSafeguard("ok", sprintf(_("Die Evaluation <b>%s</b> wurde angelegt."), $evalName));
                break;


            case "save2":
            case "save":
                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $update_message = sprintf(_("Die Evaluation <b>%s</b> wurde mit den Veränderungen gespeichert."), $evalName);


                /* Timesettings ---------------------------------------------------- */
                if (Request::option("startMode")) {


                    switch (Request::option("startMode")) {

                        case "manual":
                            $startDate = NULL;
                            break;

                        case "timeBased":
                            $startDate = EvalCommon::date2timestamp(Request::int("startDay"), Request::int("startMonth"), Request::int("startYear"), Request::int("startHour"), Request::int("startMinute"));
                            break;

                        case "immediate":
                            $startDate = time() - 1;
                            break;
                    }

                    if ($no_permission_msg &&
                            ($eval->getStartdate != $startDate)) {
                        $time_msg = $no_permission_msg . "<br>" . _("Die Einstellungen zur Startzeit wurden nicht verändert.");
                    }
                }

                if (Request::option("stopMode")) {

                    switch (Request::option("stopMode")) {
                        case "manual":
                            $stopDate = NULL;
                            $timeSpan = NULL;
                            break;

                        case "timeBased":
                            $stopDate = EvalCommon::date2timestamp(Request::int("stopDay"), Request::int("stopMonth"), Request::int("stopYear"), Request::int("stopHour"), Request::int("stopMinute"));
                            $timeSpan = NULL;
                            break;

                        case "timeSpanBased":
                            $stopDate = NULL;
                            $timeSpan = Request::get("timeSpan");
                            break;
                    }

                    if ($no_permission_msg &&
                            ($eval->getStopdate != $stopDate &&
                            $eval->getTimespan != $timeSpan )) {
                        $time_msg = ($time_msg) ? $time_msg . "<br>" : $no_permission_msg;
                        $time_msg .= _("Die Einstellungen zur Endzeit wurden nicht verändert.");
                    }
                }
                /* ----------------------------------------------- end: timesettings */


                /* link eval to ranges --------------------------------------------- */
                $link_range_Array = Request::optionArray("link_range");
                if ($link_range_Array) {
                    $isTemplate = $eval->isTemplate();
                    if ($isTemplate) {
                        $newEval = $eval->duplicate();
                        if ($newEval->isError()) {
                            $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($newEval));
                            return $safeguard;
                        }
                        $update_message = sprintf(_("Die Evaluationsvorlage <b>%s</b> wurde als Evaluation angelegt."), $evalName);
                        $newEval->setStartdate($startDate);
                        $newEval->setStopdate($stopDate);
                        $newEval->setTimespan($timeSpan);
                        $newEval->setShared(NO);
                    } else {
                        $newEval = &$eval;
                    }

                    $counter_linked = 0;
                    foreach ($link_range_Array as $link_rangeID => $v) {
                        if ($userid = get_userid($link_rangeID))
                            $link_rangeID = $userid;
                        $newEval->addRangeID($link_rangeID);
                        $counter_linked++;
                    }

                    if ($isTemplate)
                        $newEval->save();

                    if ($newEval->isError()) {
                        $safeguard .= $this->createSafeguard("ausruf", _("Fehler beim Einhängen von Bereichen.") . EvalCommon::createErrorReport($newEval));
                        return $safeguard;
                    }

                    $message .= $message ? "<br>" : " ";
                    $message .= ($counter_linked > 1) ? sprintf(_("Die Evaluation wurde in %s Bereiche eingehängt."), $counter_linked) : sprintf(_("Die Evaluation wurde in einen Bereich eingehängt."), $counter_linked);
                }
                /* ---------------------------------------- end: link eval to ranges */


                /* copy eval to ranges --------------------------------------------- */
                $copy_range_Array = Request::optionArray("copy_range");
                if (!empty($copy_range_Array)) {
                    $counter_copy = 0;
                    foreach ($copy_range_Array as $copy_rangeID => $v) {
                        if ($userid = get_userid($copy_rangeID))
                            $copy_rangeID = $userid;
                        $newEval = $eval->duplicate();
                        if (Request::option("startMode"))
                            $newEval->setStartdate($startDate);
                        if (Request::get("stopMode")) {
                            $newEval->setStopdate($stopDate);
                            $newEval->setTimespan($timeSpan);
                        }
                        $newEval->setShared(NO);
                        $newEval->removeRangeIDs();
                        $evalDB->removeUser($newEval->getObjectID());
                        $newEval->addRangeID($copy_rangeID);
                        $newEval->save();
                        $counter_copy++;

                        if ($newEval->isError()) {
                            $safeguard .= $this->createSafeguard("ausruf", _("Fehler beim Kopieren von Evaluationen in Bereiche.") . EvalCommon::createErrorReport($newEval));
                            return $safeguard;
                        }
                    }

                    $message .= $message ? "<br>" : " ";
                    $message .= ($counter_copy > 1) ? sprintf(_("Die Evaluation wurde in %s Bereiche kopiert."), $counter_copy) : sprintf(_("Die Evaluation wurde in einen Bereich kopiert."), $counter_copy);
                }
                /* ------------------------------------------- end: copy eval to ranges */

                /* unlink ranges ------------------------------------------------------- */
                $remove_range_Array = Request::optionArray("remove_range");
                if (!empty($remove_range_Array)) {

                    /* if all rangeIDs will be removed, so ask if it should be deleted -- */
                    if (sizeof($remove_range_Array) == $eval->getNumberRanges()) {
                        $text = _("Sie wollen die Evaluation <b>%s</b> aus allen ihr zugeordneten Bereichen aushängen.<br>Soll die Evaluation gelöscht oder zu Ihren eigenen Evaluationsvorlagen verschoben werden?");
                        $safeguard .= $this->createSafeguard("ausruf", sprintf($text, $evalName), "unlink_delete_request", $evalID, $showrangeID, $referer);
                        $update_message = NULL;
                        return $safeguard;
                    }
                    /* -------------------------------- end: ask if it should be deleted */

                    $no_permission_ranges = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval, YES);


                    $counter_no_permisson = 0;

                    if (is_array($no_permission_ranges)) {
                        foreach ($remove_range_Array as $remove_rangeID => $v) {

                            if ($userid = get_userid($remove_rangeID))
                                $remove_rangeID = $userid;

                            // no permisson to unlink this range
                            if (in_array($remove_rangeID, $no_permission_ranges))
                                $counter_no_permisson++;
                        }
                    }

                    // if there are no_permisson_ranges to unlink, return
                    if ($counter_no_permisson > 0) {

                        if ($counter_no_permisson == 1)
                            $safeguard .= $this->createSafeguard("ausruf", _("Sie wollen die Evaluation aus einem Bereich aushängen, für den Sie keine Berechtigung besitzten.<br> Die Aktion wurde nicht ausgeführt."));
                        else
                            $safeguard .= $this->createSafeguard("ausruf", sprintf(_("Sie wollen die Evaluation aus %d Bereichen aushängen, für die Sie keine Berechtigung besitzten.<br> Die Aktion wurde nicht ausgeführt."), $counter_no_permisson));
                        return $safeguard;
                    }

                    reset($remove_range_Array);
                    $counter_copy = 0;
                    foreach ($remove_range_Array as $remove_rangeID => $v) {

                        if ($userid = get_userid($remove_rangeID))
                            $remove_rangeID = $userid;

                        // the current range will be removed
                        if ($showrangeID == $remove_rangeID)
                            $current_range_removed = 1;

                        $eval->removeRangeID($remove_rangeID);
                        $counter_copy++;
                    }


                    if ($eval->isError()) {
                        $safeguard .= $this->createSafeguard("ausruf", _("Fehler beim Aushängen von Bereichen.") . EvalCommon::createErrorReport($eval));
                        return $safeguard;
                    }

                    $message .= $message ? "<br>" : " ";
                    $message .= ($counter_copy > 1) ? sprintf(_("Die Evaluation wurde aus %s Bereichen ausgehängt."), $counter_copy) : sprintf(_("Die Evaluation wurde aus einem Bereich ausgehängt."), $counter_copy);

                    if ($eval->getNumberRanges() == 0) {
                        $message .= $message ? "<br>" : "";
                        $message .= _("Sie ist nun keinem Bereich mehr zugeordnet und wurde zu den eigenen Evaluationsvorlagen verschoben.");
                        $eval->setStartdate(NULL);
                        $eval->setStopdate(NULL);
                        $evalDB->removeUser($eval->getObjectID());

                        if ($eval->isError()) {
                            $safeguard .= $this->createSafeguard("ausruf", _("Fehler beim Kopieren von Evaluationen in Bereiche.") . EvalCommon::createErrorReport($newEval));
                            return $safeguard;
                        }
                    } else {

                        $no_permission_ranges = EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval);
                        $number_of_ranges = $eval->getNumberRanges();

                        if ($number_of_ranges == $no_permission_ranges) {
                            $return["msg"] = $this->createSafeguard("ausruf", $message . "<br>" . sprintf(_("Sie haben die Evaluation <b>%s</b> aus allen ihren Bereichen ausgehängt."), $evalName));
                            $return["option"] = DISCARD_OPENID;
                            $eval->save();
                            if ($eval->isError()) {
                                $safeguard = $this->createSafeguard("ausruf", _("Fehler beim Aushängen einer Evaluationen aus allen Bereichen auf die Sie Zugriff haben.") . EvalCommon::createErrorReport($newEval));
                                return $safeguard;
                            }
                            return $return;
                        }
                    }
                }

                if ($eval->isTemplate()) {
                    if (empty($link_range) && empty($copy_range) && empty($remove_range)) {
                        $update_message = sprintf(_("Es wurden keine Veränderungen an der Evaluationsvorlage <b>%s</b> gespeichert."), $evalName);
                    }
                } else {
                    // nothing changed
                    if (! Request::option('startMode') && ! Request::option('stopMode') &&
                            empty($link_range) && empty($copy_range) && empty($remove_range))
                        $update_message = _("Es wurden keine Veränderungen gespeichert.");

                    // set new start date
                    if (Request::option("startMode") && !$time_msg) {
                        $eval->setStartDate($startDate);

                        if ($startDate != NULL && $startDate <= time() - 1) {
                            $message .= $message ? "<br>" : " ";
                            $message .= _("Die Evaluation wurde gestartet.");
                        }
                    }

                    // set new stop date
                    if (Request::get("stopMode") && !$time_msg) {
                        $eval->setStopDate($stopDate);
                        $eval->setTimeSpan($timeSpan);

                        if (($stopDate != NULL && $stopDate <= time() - 1) ||
                                ($timeSpan != NULL && $eval->getStartdate() != NULL && $eval->getStartdate() + $timeSpan <= time() - 1)) {
                            $message .= $message ? "<br>" : " ";
                            $message .= _("Die Evaluation wurde beendet.");
                        }
                    }

                    if ($eval->isError()) {
                        $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                        return $safeguard;
                    }

                    $eval->save();
                }

                $evalChanged = YES;

                // start/endtime aren't saved, because of ranges with no permisson
                if ($time_msg)
                    $safeguard .= $this->createSafeguard(
                            "ausruf", $time_msg);

                // everything is just fine so print the all messages
                if ($update_message && !$time_msg)
                    $safeguard .= $this->createSafeguard(
                            "ok", $update_message . "<br>" . $message);
                // messages from un/linking an making copys
                elseif ($time_msg && $message)
                    $safeguard .= $this->createSafeguard(
                            "ok", $message);

                break;

            case "search_showrange":
            case "search_range":
                $search = Request::get("search");

                if (EvaluationObjectDB::getGlobalPerm(YES) < 31) {
                    $safeguard = $this->createSafeguard("ausruf", _("Sie besitzen keine Berechtigung eine Suche durchzuführen."));
                    return $safeguard;
                }

                $results = $evalDB->search_range($search);
                if (empty($search))
                    $safeguard .= $this->createSafeguard("ausruf", _("Bitte einen Suchbegriff eingeben."), $search);
                elseif (sizeof($results) == 0)
                    $safeguard .= $this->createSafeguard("ausruf", sprintf(_("Es wurde kein Bereich gefunden, der den Suchbegriff <b>%s</b> enthält."), htmlReady($search)), $search);
                else
                    $safeguard .= $this->createSafeguard("ok", sprintf(_("Es wurden %s Bereiche gefunden, die den Suchbegriff <b>%s</b> enthalten."), sizeof($results), htmlReady($search)), $search);
                break;

            case "check_abort_creation":

                # check if the evaluation is new and not yet edited
                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
                $abort_creation = false;
                if ($eval->getTitle() == _("Neue Evaluation") &&
                        $eval->getText() == "") {
                    # the evaluationen may be not edited yet ... so continue checking
                    $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                    $number_of_childs = $eval->getNumberChildren();
                    $child = $eval->getNextChild();
                    if ($number_of_childs == 1 &&
                            $child &&
                            $child->getTitle() == _("Erster Gruppierungsblock") &&
                            $child->getChildren() == NULL &&
                            $child->getText() == "") {
                        $abort_creation = true;
                    }
                }

                if ($abort_creation != true)
                    break;
            # continue abort_creation

            case "abort_creation":
                $eval = new Evaluation($evalID, NULL, EVAL_LOAD_ALL_CHILDREN);
                $eval->delete();
                // error_ausgabe
                if ($eval->isError()) {
                    $safeguard .= $this->createSafeguard("", EvalCommon::createErrorReport($eval));
                    return $safeguard;
                }

                $safeguard .= $this->createSafeguard("ok", _("Die Erstellung einer Evaluation wurde abgebrochen."), "", "", "", $referer);
                break;

            case "nothing":
                break;

            default:
                $safeguard .= $this->createSafeguard("ausruf", _("Fehler! Es wurde versucht, eine nicht vorhandene Aktion auszuführen."));
                break;
        }

        /* Send SMS when eval has been modified by admin/root ----------------- */
        if (($evalChanged) && ($eval->getAuthorID() != $auth->auth["uid"])) {

            $sms = new messaging();
            $sms->insert_message(sprintf(_("An Ihrer Evaluation \"%s\" wurden von %s Änderungen vorgenommen."), $eval->getTitle(), get_username($auth->auth["uid"])), get_username($eval->getAuthorID()), "____%system%____", FALSE, FALSE, "1");
        }
        /* ------------------------------------------------------ end: send SMS */

        // the current range has been removed from the eval
        if ($current_range_removed) {
            $return["msg"] = $safeguard;
            $return["option"] = DISCARD_OPENID;
            return $return;
        } else {
            return $safeguard;
        }
    }

// callSafeguard

    /**
     * creates the 'Safeguard'
     *
     * @param sign   string Sign to draw (must be "ok" or "ausruf")
     * @param text   string        The Text to draw
     * @param evalID string needed if you want to delete an evaluation (not needed)
     */
    public function createSafeguard($sign, $text, $mode = NULL, $evalID = NULL, $showrangeID = NULL, $referer = NULL)
    {
        //TODO: auf messagebox bzw. createQuestion umstellen!!!

        $label = [
            "referer" => _("Zum vorherigen Bereich zurückkehren."),
            "yes" => _("Ja!"),
            "no" => _("Nein!"),
            "delete" => _("Löschen."),
            "template" => _("Zu den eigenen Evaluationsvorlagen verschieben."),
            "cancel" => _("Abbrechen")
        ];

        $html = "   <table align=\"center\" width=\"100%\" border=0 cellpadding=3 cellspacing=0>\n" // class=\"table_row_even\"
                . "   <tr>\n"
                . "    <td width=\"34\" valign=\"middle\" style=\"vertical-align:middle;\">\n";

        if ($sign != "") {
            if ($sign == 'ok') {
                $sign = 'messagebox/info.png';
            } elseif ($sign == 'ausruf') {
                $sign = 'messagebox/question.png';
            }
            $html .= Assets::img($sign, ['class' => 'middle']);
        }

        $html .="    </td>\n";
        $html .="    <td align=\"left\" valign=\"middle\" style=\"vertical-align:middle;\">\n";
        $html .="     <span class=\"" . ($sign == "ausruf" ? "eval_error" : "eval_success") . "\"><br>" . $text . "</span><br><br>\n";

        if ($referer)
            $linkreferer = "&referer=" . $referer;

        if ($mode == "delete_request") {
            $value1 = "delete_confirmed";
            $value2 = "delete_aborted";
            $request = YES;
        }
        if ($mode == "restart_request") {
            $value1 = "restart_confirmed";
            $value2 = "restart_aborted";
            $request = YES;
        }

        if ($referer)
            URLHelper::bindLinkParam('referer', $referer);

        if ($request) {

            $html .= LinkButton::createAccept(_("Ja"), URLHelper::getURL('admin_evaluation.php?evalAction=' . $value1 . '&evalID=' . $evalID . '&rangeID=' . $showrangeID),
                        ['title' => $label["yes"]]) . "\n";
            $html .= LinkButton::createCancel(_("Nein"), URLHelper::getURL('admin_evaluation.php?evalAction=' . $value2 . '&evalID=' . $evalID . '&rangeID=' . $showrangeID),
                        ['title' => $label["no"]]) . "\n";
        }

        if ($mode == "unlink_delete_request") {
            $add_cancel = !$referer ? : "&referer=" . $referer;
            $links = [
                URLHelper::getURL('admin_evaluation.php?evalAction=delete_confirmed&evalID=' . $evalID . '&rangeID=' . $showrangeID),
                URLHelper::getURL('admin_evaluation.php?evalAction=unlink_delete_aborted&evalID=' . $evalID . '&rangeID=' . $showrangeID),
                URLHelper::getURL('admin_evaluation.php?evalAction=unlink_and_move&evalID=' . $evalID . '&rangeID=' . $showrangeID . $add_cancel)
            ];
            $html .= LinkButton::create(_('Löschen'), $links[0], ['title' => $label["delete"]]) . "\n";
            $html .= LinkButton::create(_('Verschieben'), $links[1], ['title' => $label["template"]]) . "\n";
            $html .= LinkButton::createCancel(_('Abbrechen'), $links[2], ['title' => $label["cancel"]]) . "\n";
            $html .= "<br><br>";
        }

        $html .="    </td>\n"
                . "   </tr>\n";

        if ($referer)
            $html .= "    <tr><td>&nbsp;</td><td><a href=\"" . URLHelper::getLink($referer) . "\">" . $label["referer"] . "</a></td></tr>";

        $html .="   </table>\n";

        return $html;
    }

    /**
     * prints the tables for the runtime settings (start date, stop date...)
     *
     * @access  private
     * @param   $eval    the eval object
     * @param   $state   the eval's state (EVAL_STATE_NEW, EVAL_STATE_ACTIVE, ...)
     * @param   $style   the background style
     * @return  string   the runtime settings (html)
     */
    public function createRuntimeSettings($eval, $state, $style)
    {
        $html = "";
        $startDate = $eval->getStartdate();
        $stopDate = $eval->getStopdate();
        $timeSpan = $eval->getTimespan();

        if ($startDate == NULL)
            $startMode = "manual";
        else
            $startMode = "timeBased";

        if ($stopDate == NULL)
            $stopMode = "manual";
        else
            $stopMode = "timeBased";

        if ($timeSpan != NULL)
            $stopMode = "timeSpanBased";

        if (!$startDate || $startDate == -1)
            $startDate = time();

        $startDay = date("d", $startDate);
        $startMonth = date("m", $startDate);
        $startYear = date("Y", $startDate);
        $startHour = date("H", $startDate);
        $startMinute = date("i", $startDate);

        if (!$stopDate || $stopDate == -1)
            $stopDate = mktime(0, 0, 0, date("m") + 1, date("d"), date("Y"));

        $stopDay = date("d", $stopDate);
        $stopMonth = date("m", $stopDate);
        $stopYear = date("Y", $stopDate);
        $stopHour = date("H", $stopDate);
        $stopMinute = date("i", $stopDate);

        if (!$timeSpan)
            $timeSpan = 1209600; // default: 2 weeks

        $html .= "<table border=0 align=center cellspacing=0 cellpadding=4 width=\"100%\">\n";
        $html .= "<tr><td colspan=\"2\">\n";
        $html .= "<b>" . _("Einstellungen zur Start- und Endzeit:") . "</b>";
        $tooltip = $eval->isTemplate()
                 ? _('Legen Sie fest, von wann bis wann alle eingehängten und kopierten Instanzen dieser Evaluationsvorlage in Stud.IP öffentlich sichtbar sein sollen.')
                 : _('Legen Sie fest, von wann bis wann die Evaluation in Stud.IP öffentlich sichtbar sein soll.');
        $html .= " ";
        $html .= Icon::create('info-circle', 'inactive', ['title' => $tooltip])->asImg(['class' => 'middle']);
        $html .= "</td></tr>";
        $html .= "<tr>";

        /* START TIME settings ------------------------------------------------- */
        $html .= "<td width=\"50%\" valign=\"top\">"
                . "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0>\n"
                . "<tr>"
                . "<td class=\"$style\" height=\"22\" align=\"left\" "
                . "style=\"vertical-align:bottom;\">"
                . "<b>"
                . "&nbsp;"
                . _("Anfang")
                . "</b>"
                . "</td>"
                . "</tr>\n";

        /* Eval has NOT started yet --- */
        if ($state == EVAL_STATE_NEW || $eval->isTemplate()) {
            $html .= "<tr><td class=\"table_row_even\">";
            $html .= "<input type=radio name=\"startMode\" value=\"manual\" " . ($startMode == "manual" ? "checked" : "") . ">&nbsp;";
            $html .= _("später manuell starten");
            $html .= "</td></tr>";

            $html .= "<tr><td class=table_row_odd>";
            $html .= "<input type=radio name=\"startMode\" value=\"timeBased\" " . ($startMode == "timeBased" ? "checked" : "") . ">&nbsp;";
            $html .= _("Startzeitpunkt:");
            $html .= "&nbsp;&nbsp;<input type=text name=\"startDay\" size=3 maxlength=2 value=\"" . $startDay . "\">&nbsp;.&nbsp;"
                    . "<input type=text name=\"startMonth\" size=3 maxlength=2 value=\"" . $startMonth . "\">&nbsp;.&nbsp;"
                    . "<input type=text name=\"startYear\" size=5 maxlength=4 value=\"" . $startYear . "\">&nbsp;"
                    . sprintf(_("um %s Uhr"), "&nbsp;<input type=text name=\"startHour\" size=3 maxlength=2 value=\"" . $startHour . "\">&nbsp;:" .
                            "&nbsp;<input type=text name=\"startMinute\" size=3 maxlength=2 value=\"" . $startMinute . "\">&nbsp;");
            $html .= "</td></tr>";

            $html .= "<tr><td class=table_row_even valign=middle>";
            $html .= "<input type=radio name=\"startMode\" value=\"immediate\">&nbsp;";
            $html .= _("sofort");
            $html .= "</td></tr>";
        }

        /* Eval is already running or finished --- */ else {
            $html .= "<tr><td><font size=\"+2\">&nbsp;</font></td></tr>";
            $html .= "<tr><td valign=\"middle\" align=\"center\">";
            $html .= sprintf(_("Startzeitpunkt war der <b>%s</b> um <b>%s</b> Uhr."), date("d.m.Y", $startDate), date("H:i", $startDate));
            $html .= "</td></tr>";
            $html .= "<tr><td><font size=\"+2\">&nbsp;</font></td></tr>";
        }
        $html .= "</table></td>";


        /* END TIME settings --------------------------------------------------- */
        $html .= "<td width=\"50%\" valign=\"top\">"
                . "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0>\n"
                . "<tr>"
                . "<td class=\"$style\" height=\"22\" align=\"left\" "
                . "style=\"vertical-align:bottom;\">"
                . "<b>"
                . "&nbsp;"
                . _("Ende")
                . "</b>"
                . "</td>"
                . "</tr>\n";

        /* Eval has NOT finished yet --- */
        if ($state != EVAL_STATE_STOPPED) {
            $html .= "<tr><td class=table_row_even>\n"
                    . "<input type=radio name=\"stopMode\" value=\"manual\" " . ($stopMode == "manual" ? "checked" : "") . ">&nbsp;"
                    . _("manuell beenden")
                    . "</td></tr>"
                    . "<tr><td class=table_row_odd>\n"
                    . "<input type=radio name=\"stopMode\" value=\"timeBased\" " . ($stopMode == "timeBased" ? "checked" : "") . ">&nbsp;"
                    . _("Endzeitpunkt:");

            $html .= "&nbsp;&nbsp;"
                    . "<input type=text name=\"stopDay\" size=3 maxlength=2 value=\"" . $stopDay . "\">&nbsp;.&nbsp;"
                    . "<input type=text name=\"stopMonth\" size=3 maxlength=2 value=\"" . $stopMonth . "\">&nbsp;.&nbsp;"
                    . "<input type=text name=\"stopYear\" size=5 maxlength=4 value=\"" . $stopYear . "\">&nbsp;"
                    . sprintf(_("um %s Uhr"), "&nbsp;<input type=text name=\"stopHour\" size=3 maxlength=2 value=\"" . $stopHour . "\">&nbsp;:" .
                            "&nbsp;<input type=text name=\"stopMinute\" size=3 maxlength=2 value=\"" . $stopMinute . "\">&nbsp;");
            $html .= "&nbsp;"
#       . "<input type=hidden name=\"stopDate\" value=\"".$stopDate."\">"
                    . "</td></tr>"
                    . "<tr><td class=table_row_even valign=middle>"
                    . "<input type=radio name=\"stopMode\" value=\"timeSpanBased\" " . ($stopMode == "timeSpanBased" ? "checked" : "")
#       . " onClick=\"document.settingsForm.submit()\""
                    . ">&nbsp;"
                    . _("Zeitspanne")
                    . "&nbsp;&nbsp;"
                    . "<select name=\"timeSpan\" style=\"vertical-align:middle\" size=1 "
#       . "onChange=\"document.settingsForm.submit()\""
                    . ">";

            for ($i = 1; $i <= 12; $i++) {
                $secs = $i * 604800;  // == weeks * seconds per week

                $html .= "\n<option value=\"" . $secs . "\" ";
                if ($timeSpan == $secs)
                    $html .= "selected";
                $html .= ">";
                $html .= sprintf(
                    ngettext(
                        '%d Woche',
                        '%d Wochen',
                        $i
                    ),
                    $i
                );
                $html .= "</option>";
            }
            $html .= "</select>";

            if ($stopMode == "timeSpanBased" && $startMode != "manual") {

                $startDate = ($startMode == "immediate") ? time() : $startDate;

                $html .= "&nbsp;";
                $html .= Icon::create('refresh', 'clickable', ['title' => _('Endzeitpunkt neu berechnen')])->asInput(['name'=>'save2_button','align'=>'middle',]);
                $html .= sprintf(_(" (<b>%s</b> um <b>%s</b> Uhr)"), strftime("%d.%m.%Y", $startDate + $timeSpan), strftime("%H:%M", $startDate + $timeSpan));
            }
            $html .= "</td></tr>";
        }

        /* Eval has finished --- */ else {
            $html .= "<tr><td><font size=\"+2\">&nbsp;</font></td></tr>";
            $html .= "<tr><td valign=\"middle\" align=\"center\">";
            $html .= sprintf(_("Endzeitpunkt war der <b>%s</b> um <b>%s</b> Uhr."), date("d.m.Y", $stopDate), date("H:i", $stopDate));
            $html .= "</td></tr>";
            $html .= "<tr><td><font size=\"+2\">&nbsp;</font></td></tr>";
        }
        $html .= "</table>";

        $html .= "</td></tr>";
        $html .= "</table>";

        return $html;
    }

    /**
     *  the current eval-ranges and the options to copy and link ranges
     *
     * @access  private
     * @param   $eval    the eval object
     * @param   $state   the eval's state (EVAL_STATE_NEW, EVAL_STATE_ACTIVE, ...)
     * @param   $style   the background style
     * @return  string   the domain settings (html)
     */
    public function createDomainSettings($eval, $state, $style) {
        global $user;
        $db = new EvaluationObjectDB ();
        $evalDB = new EvaluationDB ();
        $globalperm = $db->getGlobalPerm();

        // linked ranges
        $rangeIDs = $eval->getRangeIDs();

        // search results
        if (Request::get("search")) {
            $results = $evalDB->search_range(Request::get("search"));
        }
        else {
            $results = $evalDB->search_range("");
        }

        if ($globalperm == "root") {
            $results["studip"] = ["type" => "system", "name" => _("Systemweite Evaluationen")];
        } elseif ($globalperm == "dozent" || $globalperm == "autor" || $globalperm == "admin") {
            $results[$GLOBALS['user']->id] = ["type" => "user", "name" => _("Profil")];
        }

        if ($globalperm == "dozent" || $globalperm == "autor" || Request::get("search")) {
            $showsearchresults = 1;
        }
        
        if ($globalperm == "autor") {
            $range_types = [
                "user" => _("Benutzer"),
                "sem" => _("Veranstaltung")
            ];
        }
        elseif ($globalperm == "dozent") {
            $range_types = [
                "user" => _("Benutzer"),
                "sem" => _("Veranstaltung"),
                "inst" => _("Einrichtung")
            ];
        }
        elseif ($globalperm == "admin") {
            $range_types = [
                "user" => _("Benutzer"),
                "sem" => _("Veranstaltung"),
                "inst" => _("Einrichtung"),
                "fak" => _("Fakultät")
            ];
        }
        elseif ($globalperm == "root") {
            $range_types = [
                "user" => _("Benutzer"),
                "sem" => _("Veranstaltung"),
                "inst" => _("Einrichtung"),
                "fak" => _("Fakultät"),
                "system" => _("System")
            ];
        }


        // zugewiesene Bereiche
        $table_r = new HTML("table");
        $table_r->addAttr("border", "0");
        $table_r->addAttr("align", "center");
        $table_r->addAttr("cellspacing", "0");
        $table_r->addAttr("cellpadding", "0");
        $table_r->addAttr("width", "100%");
        $table_r->addAttr('class', 'default');

        // Überschriften
        $tr_r = new HTML("tr");

        $td_r = new HTML("td");
        $td_r->addAttr("class", "$style");
        $td_r->addAttr("style", "vertical-align:bottom;");
        $td_r->addAttr("height", "22");
        $b_r = new HTML("b");
        $b_r->addHTMLContent("&nbsp;");
        $b_r->addContent(_("Bereich"));
        $td_r->addContent($b_r);
        $tr_r->addContent($td_r);

        $td_r = new HTML("td");
        $td_r->addAttr("class", "$style");
        $td_r->addAttr("height", "22");
        $td_r->addAttr("align", "center");
        $td_r->addAttr("style", "vertical-align:bottom;");
        $b_r = new HTML("b");
        $b_r->addContent(_("aushängen"));
        $td_r->addContent($b_r);
        $tr_r->addContent($td_r);

        $table_r->addContent($tr_r);

        if ($rangeIDs) {
            // die verknüpften bereiche
            foreach ($rangeIDs as $k => $assigned_rangeID) {
                $tr_r = new HTML("tr");

                // title
                $td_r = new HTML("td");
                $td_r->addHTMLContent("&nbsp;");
                $td_r->addContent($db->getRangename($assigned_rangeID, NO));
                $tr_r->addContent($td_r);

                if (($this->perm->have_studip_perm("tutor", $assigned_rangeID)) || $assigned_rangeID == $user->id) {
                    // link
                    $td_r = new HTML("td");
                    $td_r->addAttr("align", "center");
                    $input = new HTMLempty("input");
                    $input->addAttr("type", "checkbox");
                    $input->addAttr("name", "remove_range[$assigned_rangeID]");
                    $input->addAttr("value", "1");
                    $td_r->addContent($input);
                } else {
                    // no permission
                    $td_r = new HTML("td");
                    $td_r->addAttr("align", "center");
                    $td_r->addContent(_("Sie haben keine Berechtigung die Evaluation aus diesem Bereich auszuhängen."));
                }
                $tr_r->addContent($td_r);
                $table_r->addContent($tr_r);
            }
        } else {
            $td_r = new HTML("td");
            $td_r->addAttr("class", "content_body");
            $td_r->addAttr("width", "40");
            $td_r->addAttr("align", "center");
            $td_r->addAttr("style", "vertical-align:bottom;");
            $td_r->addAttr("colspan", "2");
            $b_r = new HTML("b");
            $b_r->addHTMLContent("&nbsp;");
            $b_r->addContent(_("Diese Evaluation wurde keinem Bereich zugeordnet."));
            $td_r->addContent($b_r);
            $tr_r->addContent($td_r);

            $table_r->addContent($tr_r);
        }

        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", "4");
        $table->addAttr("width", "100%");

        $tr = new HTML("tr");

        $td = new HTML("td");
        $td->addAttr("colspan", "2");
        $td->addAttr("style", "padding-bottom:0; border-top:1px solid black;");
        $b = new HTML("b");
        $b->addContent(_("Diese Evaluation ist folgenden Bereichen zugeordnet:"));
        $td->addContent($b);
        $td->addContent(EvalCommon::createImage(EVAL_PIC_HELP, "", tooltip(_(" können Sie Ihre Evaluation aus den verknüpften Bereichen entfernen."), TRUE, TRUE)));
        $tr->addContent($td);
        if (!$eval->isTemplate())
            $table->addContent($tr);

        $tr = new HTML("tr");

        $td = new HTML("td");
        $td->addAttr("colspan", "2");
        $td->addContent($table_r);
        $tr->addContent($td);
        if (!$eval->isTemplate())
            $table->addContent($tr);

        // display search_results
        if ($results) {
            foreach ($results as $k => $v) {
                foreach($range_types as $type_key => $type_value) {
                    if ($v["type"] == $type_key) {
                        $ranges[$type_key][] = ["id" => $k, "name" => $v["name"]];
                    }
                }
                reset($range_types);
            }

            $table_s = new HTML("table");
            $table_s->addAttr("border", "0");
            $table_s->addAttr("align", "center");
            $table_s->addAttr("cellspacing", "0");
            $table_s->addAttr("cellpadding", "0");
            $table_s->addAttr("width", "100%");
    
            foreach($range_types as $type_key => $type_value) {
                // Überschriften
                $tr_s = new HTML("tr");

                // Typ
                $td_s = new HTML("td");
                $td_s->addAttr("colspan", "1");
                $td_s->addAttr("class", "$style");
                $td_s->addAttr("height", "22");
                $td_s->addAttr("style", "vertical-align:bottom;");
                $b_s = new HTML("b");
                $b_s->addHTMLContent("&nbsp;");
                $b_s->addContent($type_value . ":");
                $td_s->addContent($b_s);
                $tr_s->addContent($td_s);

                // link
                $td_s = new HTML("td");
                $td_s->addAttr("class", "$style");
                $td_s->addAttr("height", "22");
                $td_s->addAttr("align", "center");
                $td_s->addAttr("style", "vertical-align:bottom;");
                $b_s = new HTML("b");
                $b_s->addContent(_("einhängen"));
                $td_s->addContent($b_s);
                $tr_s->addContent($td_s);

                // kopie
                $td_s = new HTML("td");
                $td_s->addAttr("class", "$style");
                $td_s->addAttr("height", "22");
                $td_s->addAttr("align", "center");
                $td_s->addAttr("style", "vertical-align:bottom;");
                $b_s = new HTML("b");
                $b_s->addContent(_("kopieren"));
                $td_s->addContent($b_s);
                $tr_s->addContent($td_s);

                $table_s->addContent($tr_s);

                $counter = 0;

                if ($ranges[$type_key]) {
                    foreach ($ranges[$type_key] as $range) {
                        $tr_s = new HTML("tr");

                        // name
                        $td_s = new HTML("td");
                        $td_s->addHTMLContent("&nbsp;");
                        $td_s->addHTMLContent(htmlready($range["name"]));
                        $tr_s->addContent($td_s);

                        // if the rangeID is a username, convert it to the userID
                        $new_rangeID = (get_userid($range['id'])) ? get_userid($range['id']) : $range['id'];

                        if (!in_array($new_rangeID, $rangeIDs)) {
                            // link
                            $td_s = new HTML("td");
                            $td_s->addAttr("align", "center");
                            $input = new HTMLempty("input");
                            $input->addAttr("type", "checkbox");
                            $input->addAttr("name", "link_range[{$range['id']}]");
                            $input->addAttr("value", "1");
                            $td_s->addContent($input);
                            $tr_s->addContent($td_s);
                        } else {
                            // no link
                            $td_s = new HTML("td");
                            $td_s->addAttr("align", "center");
                            $td_s->addAttr("colspan", "1");
                            $input = new HTMLempty("input");
                            $td_s->addContent(_("Die Evaluation ist bereits diesem Bereich zugeordnet."));
                            $tr_s->addContent($td_s);
                        }

                        // copy
                        $td_s = new HTML("td");
                        $td_s->addAttr("align", "center");
                        $input = new HTMLempty("input");
                        $input->addAttr("type", "checkbox");
                        $input->addAttr("name", "copy_range[{$range['id']}]");
                        $input->addAttr("value", "1");
                        $td_s->addContent($input);
                        $tr_s->addContent($td_s);

                        $table_s->addContent($tr_s);
                        $counter++;
                    }
                } elseif ($globalperm == "root" || $globalperm == "admin") {
                    $tr_s = new HTML("tr");
                    $td_s = new HTML("td");
                    $td_s->addAttr("class", "content_body");
                    $td_s->addAttr("colspan", "4");
                    $td_s->addHTMLContent("&nbsp;");
                    $td_s->addContent(_("Es wurden keine Ergebnisse aus diesem Bereich gefunden."));
                    $tr_s->addContent($td_s);
                    $table_s->addContent($tr_s);
                }
                reset($ranges);
            }
        }
        if ($showsearchresults) {
            $tr = new HTML("tr");
            $td = new HTML("td");
            $td->addAttr("colspan", "2");
            $b = new HTML("b");
            if (Request::get("search")) {
                $b->addContent(_("Sie können die Evaluation folgenden Bereichen zuordnen (Suchergebnisse):"));
            }
            else {
                $b->addContent(_("Sie können die Evaluation folgenden Bereichen zuordnen:"));
            }
            $td->addContent($b);
            $td->addContent(EvalCommon::createImage(EVAL_PIC_HELP, "", tooltip(_("Hängen Sie die Evaluation in die gewünschten Bereiche ein (abhängige Kopie mit gemeinsamer Auswertung) oder kopieren Sie sie in Bereiche (unabhängige Kopie mit getrennter Auswertung)."), TRUE, TRUE)));
            $td->addContent(($results) ? $table_s : _("Die Suche ergab keine Treffer."));
            $tr->addContent($td);
            $table->addContent($tr);
        }

        // the seach-button
        if ($globalperm == "root" || $globalperm == "admin") {
            $tr = new HTML("tr");

            $td = new HTML("td");
            $td->addAttr("colspan", "2");
            $td->addAttr("style", "padding-bottom:0; border-top:1px solid black;");
            $td->addContent(_("Nach Bereichen suchen:"));
            $td->addContent(" ");

            $input = new HTMLempty("input");
            $input->addAttr("type", "text");
            $input->addAttr("name", "search");
            $input->addAttr("style", "vertical-align:middle;");
            $input->addAttr("value", "" . Request::get("search") . "");
            $td->addContent($input);

            $td->addContent(Button::create(_('Suchen'), 'search_range_button', ['title' => _('Bereiche suchen')]));
            $tr->addContent($td);

            $table->addContent($tr);
        }

        return $table->createContent();
    }

    public function createDomainLinks($search)
    {
        $db = new EvaluationObjectDB ();
        $evalDB = new EvaluationDB ();
        $globalperm = $db->getGlobalPerm();

        // search results
        $results = $evalDB->search_range($search);

        if ($globalperm == "root") {
            $results["studip"] = ["type" => "system", "name" => _("Systemweite Evaluationen")];
        } else {
            $results[$GLOBALS['user']->id] = ["type" => "user", "name" => _("Profil")];
        }
        
        if ($globalperm == "admin") {
            $range_types = [
                "user" => _("Benutzer"),
                "sem" => _("Veranstaltung"),
                "inst" => _("Einrichtung"),
                "fak" => _("Fakultät")
            ];
        }
        elseif ($globalperm == "root") {
            $range_types = [
                "user" => _("Benutzer"),
                "sem" => _("Veranstaltung"),
                "inst" => _("Einrichtung"),
                "fak" => _("Fakultät"),
                "system" => _("System")
            ];
        }

        // display search_results
        if ($results) {
            foreach ($results as $k => $v) {
                foreach($range_types as $type_key => $type_value) {
                    if ($v['type'] === $type_key) {
                        $ranges[$type_key][] = ["id" => $k, "name" => $v["name"]];
                    }
                }
                reset($range_types);
            }

            $table = new HTML("table");
            $table->addAttr("class", "default");
            $table->addAttr("border", "0");
            $table->addAttr("align", "center");
            $table->addAttr("cellspacing", "0");
            $table->addAttr("cellpadding", "0");
            $table->addAttr("width", "100%");

            foreach($range_types as $type_key => $type_value) {
                // Überschriften
                $tr = new HTML("tr");

                // Typ
                $td = new HTML("td");
                $td->addAttr("colspan", "1");
                $td->addAttr("class", "table_header");
                $td->addAttr("height", "22");
                $td->addAttr("width", "50%");
                $td->addAttr("style", "vertical-align:bottom;");
                $b = new HTML("b");
                $b->addHTMLContent("&nbsp;");
                $b->addContent($type_value . ":");
                $td->addContent($b);
                $tr->addContent($td);

                // Typ
                $td = new HTML("td");
                $td->addAttr("class", "table_header");
                $td->addAttr("height", "22");
                $td->addAttr("align", "center");
                $td->addAttr("style", "vertical-align:bottom;");
                $b = new HTML("b");
                $b->addContent(" ");
                $td->addContent($b);
                $tr->addContent($td);

                // Typ
                $td = new HTML("td");
                $td->addAttr("class", "table_header");
                $td->addAttr("height", "22");
                $td->addAttr("align", "center");
                $td->addAttr("style", "vertical-align:bottom;");
                $b = new HTML("b");
                $b->addContent(" ");
                $td->addContent($b);
                $tr->addContent($td);

                $table->addContent($tr);

                $counter = 0;

                if ($ranges["$type_key"]) {
                    foreach ($ranges["$type_key"] as $range) {
                        $tr = new HTML("tr");

                        // name
                        $td = new HTML("td");
                        $td->addHTMLContent("&nbsp;");
                        $td->addContent($range["name"]);
                        $tr->addContent($td);

                        // link
                        $td = new HTML("td");
                        $td->addAttr("align", "center");
                        $link = new HTML("a");
                        $link->addAttr("href", URLHelper::getLink(EVAL_FILE_ADMIN . "?rangeID=" . $range['id']));
                        $link->addContent(_("Diesen Bereich anzeigen."));
                        $td->addContent($link);
                        $tr->addContent($td);

                        // copy
                        $td = new HTML("td");
                        $td->addAttr("align", "center");
                        $td->addContent(" ");
                        $tr->addContent($td);

                        $table->addContent($tr);
                        $counter++;
                    }
                } elseif ($globalperm == "root" || $globalperm == "admin") {
                    $tr = new HTML("tr");
                    $td = new HTML("td");
                    $td->addAttr("class", "content_body");
                    $td->addAttr("colspan", "4");
                    $td->addHTMLContent("&nbsp;");
                    $td->addContent(_("Es wurden keine Ergebnisse aus diesem Bereich gefunden."));
                    $tr->addContent($td);
                    $table->addContent($tr);
                }
                reset($ranges);
            }
        }

        return $table->createContent();
    }

    /**
     * checks which button was pressed
     *
     * @returns string   the command
     *
     */
    public function getPageCommand() {
        if (Request::option('evalAction')) {
            return Request::option('evalAction');
        }
        foreach ($_REQUEST as $key => $value) {
            if (preg_match("/(.*)_button(_x)?/", $key, $command)) {
                break;
            }
        }
        return $command[1];
    }
}
