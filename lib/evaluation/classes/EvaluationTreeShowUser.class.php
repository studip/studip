<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
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

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;
# ====================================================== end: including files #

# Define constants ========================================================== #

/**
 * The number of pixels by which each sub-group is indented.
 * @const INDENT_PIXELS
 * @access private
 */
define( "INDENT_PIXELS", 5 );

# ===================================================== end: define constants #


/**
 * Class to print out html representation of an evaluation's tree
 * for the participation page
 * (based on /lib/classes/TreeView.class)
 *
 * @author  mcohrs <michael A7 cohrs D07 de>
 * @copyright   2004 Stud.IP-Project
 * @access  public
 * @package     evaluation
 * @modulegroup evaluation_modules
 */
class EvaluationTreeShowUser {

    /**
     * Reference to the tree structure
     *
     * @access  private
     * @var object EvaluationTree $tree
     */
    var $tree;

    /**
     * contains the item with the current html anchor (currently unused)
     *
     * @access  public
     * @var string  $anchor
     */
    var $anchor;

    /**
     * the item to start with
     *
     * @access  private
     * @var string  $start_item_id
     */
    var $start_item_id;


    /**
     * constructor
     * @access public
     * @param string  the eval's ID
     */
    function __construct( $evalID ) {

    $this->tree = TreeAbstract::GetInstance( "EvaluationTree", ['evalID' => $evalID,
                                                                    'load_mode' => EVAL_LOAD_ALL_CHILDREN]);

    }


    /**
     * prints out the tree beginning with a given item
     *
     * @access  public
     * @param   string  ID of the start item, shouldnt be needed.
     */
    function showTree( $item_id = "root" ) {
    $items = [];

    if( ! is_array($item_id) ) {
        $items[0] = $item_id;
        $this->start_item_id = $item_id;
    } else {
        $items = $item_id;
    }

    $num_items = count($items);
    for( $j = 0; $j < $num_items; ++$j ) {

        $this->printLevelOutput( $items[$j] );
        $this->printItemOutput( $items[$j] );

        if( $this->tree->hasKids( $items[$j] ) ) {
        $this->showTree( $this->tree->tree_childs[$items[$j]] );
        }
    }
    return;
    }


    /**
     * prints out ... hmm ... the group's level indentation space, and a table start
     *
     * @access  private
     * @param   string  ID of the item (which is an EvaluationGroup) to print the space for.
     */
    function printLevelOutput( $group_id ) {
    if( $group_id == "root" )
        return;

    $level_output = "";
#   echo "<td nowrap width=\"1\" valign=\"middle\">\n";
#   echo ($this->anchor == $group_id ? "<a name=\"anchor\">" : "");
#   echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/forumleer.gif\" border=\"0\" height=\"1\" width=\"1\" alt=\"\">";
#   echo ($this->anchor == $group_id ? "</a>\n" : "\n");

    $parent_id = $group_id;
    while( $this->tree->tree_data[$parent_id]['parent_id'] != $this->start_item_id ) {
        $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];

        /* a little space to indent subgroups */
        $level_output .=
        "<td valign=\"top\" width=\"".INDENT_PIXELS."\" height=\"1\" nowrap>".
        Assets::img('forumleer.gif', ['size' => INDENT_PIXELS . '@1']).
        "</td>";
    }

    echo "<!-- printLevelOutput ----------------- -->\n";
    echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\n";
    echo "<tr>\n";
    echo $level_output;
    return;
    }


    /**
     * prints out one group
     *
     * @access  private
     * @param   string  ID of the item to print (which is an EvaluationGroup).
     */
    function printItemOutput( $group_id ) {
    if( $group_id == "root" )
        return;

#   $group = new EvaluationGroup( $group_id, NULL, EVAL_LOAD_ALL_CHILDREN );
    $group = &$this->tree->getGroupObject($group_id);

#   echo "<td>";
#   echo ">";
#   echo "</td>\n";

    echo "<td width=\"1\">\n";
#   echo "<td nowrap width=\"1\" valign=\"middle\">\n";
#   echo ($this->anchor == $group_id ? "<a name=\"anchor\">" : "");
#   echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/forumleer.gif\" border=\"0\" height=\"1\" width=\"1\" alt=\"\">";
#   echo ($this->anchor == $group_id ? "</a>\n" : "\n");
    echo "</td>\n";

    /* show group headline, if it's not a question group */
    if( $group->getChildType() != "EvaluationQuestion" ) {

        /* add space after a top-level group */
        $parent = $group->getParentObject();
        if( $parent->x_instanceof() == "Evaluation" && $group->getPosition() != 0 )
        echo "<td colspan=\"2\" width=\"100%\"><br></td><tr>";

        echo "<td align=\"left\" width=\"100%\" valign=\"bottom\" class=\"content_body\" style=\"padding:1px;\">\n";
        $parent_id = $group_id;
        while( $parent_id != "root" ) {
        $chapter_num = ($this->tree->tree_data[$parent_id]['priority'] + 1) .".". $chapter_num;
        $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];
        }
        echo "&nbsp;".$chapter_num." ";
        echo "<b>";
        echo htmlReady($this->tree->tree_data[$group_id]['name']);
        echo "</b>";
        echo "</td>";

        echo "<td width=\"1\">\n";
        echo Assets::img('forumleer.gif', ['size' => '2@1']);
        echo "</td>\n";

    } else {
        echo "<td width=\"100%\"></td>";
    }

    echo "</tr>\n";
    echo "</table>\n";

    /* item details */
    $this->printItemDetails($group);

    return;
    }


    /**
     * prints out the details for a group
     *
     * @access  private
     * @param   object EvaluationGroup  the group object.
     */
    function printItemDetails( $group ) {
    $group_id = $group->getObjectID();

        $parent_id = $group_id;
    while( $this->tree->tree_data[$parent_id]['parent_id'] != $this->start_item_id ) {
        $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];

        /* a little space to indent subgroups */
        $level_output = "<td width=\"".INDENT_PIXELS."\">".
        Assets::img('forumleer.gif', ['size' => INDENT_PIXELS . '@1']).
        "</td>".
        $level_output;
    }

    /* print table */
    echo "<!-- printItemDetails ----------------- -->\n";
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
    echo "<tr>\n".$level_output;
    echo "<td class=\"printcontent\" width=\"100%\" ".
        ($group->getChildType() == "EvaluationQuestion"
#        ? "style=\"border-left:1px solid #d0d0d0; border-right:1px solid #d0d0d0;\">"
         ? ">"
         : ">");
    echo $this->getGroupContent($group);
    echo "</td></tr>\n";
    echo "</table>\n";
    return;
    }


    /**
     * returns html for the content of a group
     *
     * @access  private
     * @param   object EvaluationGroup  the group object.
     * @return  string
     */
    function getGroupContent( $group ) {
    $closeTable = NO;
    $html = "";
    $content = "";

    /* get title */
    $content .= $group->getChildType() == "EvaluationQuestion" && $group->getTitle()
        ? "<b>".formatReady( $group->getTitle() )."</b><br>\n"
        : "";

    /* get text */
    $content .= $group->getText()
        ? formatReady( $group->getText() )."<br>\n"
        : "";

    /* get the content of questions under this group, if any */
    foreach( $group->getChildren() as $question ) {
        if( $question->x_instanceof() == INSTANCEOF_EVALQUESTION ) {

        if( $question->getPosition() == 0 ) {
            $content .= "\n<table width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" ".
            "align=\"center\" style=\"margin-top:3px;\">\n";
        }

        $content .= $this->getQuestionContent( $question, $group );
        $closeTable = YES;
        }
    }
    if( $closeTable )
        $content .= "</table>\n";

    /* return if there is nothing to show */
    if( empty($content) )
        return "";

    /* build table of content */
    $style = $group->getChildType() != "EvaluationQuestion"
#       ? "style=\"border:1px solid #d0d0d0;\""
        ? ""
        : "";

    $class = $group->getChildType() != "EvaluationQuestion"
        ? "eval_gray"
        : "steelgroup7";
    $html .= "\n<!-- getGroupContent ----------------- -->\n";
    $html .= "<table width=\"100%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" ".$style.">\n";
###
#   if( $group->getChildType() == "EvaluationGroup" )
#       $html .= "<tr><td class=\"steelgroup4\" width=\"100%\" height=\"3\"></td></tr>\n";
###
    $html .= "<tr>\n";
    $html .= "<td align=\"left\" class=\"".$class."\">\n";
    $html .= $content;
    $html .= "</td></tr>\n";
    $html .= "</table>\n";

    return $html;
    }


    /**
     * returns html for a question and its answers
     *
     * @access  private
     * @param   object EvaluationQuestion  the question object.
     * @param   object EvaluationGroup     the question's parent-group object.
     * @return  string
     */
    function getQuestionContent( $question, $group ) {

    $type = $question->isMultipleChoice() ? "checkbox" : "radio";
#   $answerBorder = "1px dotted #c0c0c0";
    $answerBorder = "1px dotted #909090";
#   $residualBorder = "1px dotted #c0c0c0";
    $residualBorder = "1px dotted #909090";
    $answerArray = $question->getChildren();
    $hasResidual = NO;
    $leftOutStyle = ( $group->isMandatory() &&
              Request::submitted('voteButton') &&
              is_array( $GLOBALS["mandatories"] ) &&
              in_array( $question->getObjectID(), $GLOBALS["mandatories"] ) )
        ? "background-image:url(".Assets::image_path("steelgroup1.gif")."); border-left:3px solid red; border-right:3px solid red;"
        : "";

    /* Skala (one row question) ---------------------------------------- */
    if( $question->getType() == EVALQUESTION_TYPE_LIKERT || $question->getType() == EVALQUESTION_TYPE_POL ) {

        if( ($numAnswers = $question->getNumberChildren()) > 0 )
        $cellWidth = (int)( 40 / $numAnswers );

        if( $numAnswers > 0 && $answerArray[ $numAnswers - 1 ]->isResidual() )
        $hasResidual = YES;

        $lastTextAnswer = $hasResidual ? ($numAnswers - 3) : ($numAnswers - 2);

        /* Headline, only shown for first question */
        if( $question->getPosition() == 0 ) {
        $html .= " <tr>\n";
        $html .= "  <td width=\"60%\" style=\"border-bottom: $answerBorder; border-top: $answerBorder;\">";
#       $html .= mb_strlen( $group->getText() ) < 100 ? formatReady( $group->getText() ) : "&nbsp;";
        $html .= "&nbsp;";
        $html .= "</td>\n";
        foreach( $answerArray as $answer ) {
            $noWrap = NO;

            if( $answer->x_instanceof() == INSTANCEOF_EVALANSWER ) {
            if( ! $answer->getText() ) {
                /* answer has NO text ------------ */
                if( $answer->getPosition() <= $lastTextAnswer/2 ) //&& $numAnswers > 4 )
                $headCell = "&lt;--";
                elseif( $answer->getPosition() >= round($lastTextAnswer/2) + $lastTextAnswer % 2 ) //&& $numAnswers > 4 )
                $headCell = "--&gt;";
                else
                $headCell = "&lt;- -&gt;";

                $noWrap = YES;
            } else {
                /* answer has its own text ------ */
                $headCell = formatReady( $answer->getText() );
            }

            $extraStyle = "";
            if( $answer->isResidual() ) {
                $extraStyle = "border-left: $residualBorder;";
                $html .=
#               "<td align=\"center\" class=\"content_seperator\" ".
#               "style=\"border-left: 1px solid black; border-top: 1px solid black;\" ".
#               "style=\"border-left: $answerBorder; border-top: $answerBorder;\" ".
#               "width=\"2\">x</td>";
                "<td align=\"center\" style=\"$extraStyle\" ".
                "width=\"1\">&nbsp;</td>";
            }

            $html .=
                "  <td align=\"center\" class=\"steelgroup6\" ".
                "style=\"border-bottom: $answerBorder; ".
                "border-left: $answerBorder; border-top: $answerBorder; $extraStyle;\" ".
                "width=\"".$cellWidth."%\" ".($noWrap ? "nowrap" : "").">";
            $html .= $headCell;
            $html .= "</td>\n";
            }
        }
        $html .= " </tr>\n";
        }
        /* ------------------------------- Headline end */


        /* Question and Answer Widgets ---------------- */
        $class = $question->getPosition() % 2 ? "table_row_even" : "table_row_odd";
        $extraStyle = ($question->getPosition() == $group->getNumberChildren() - 1
               ? "border-bottom: $answerBorder"
               : "");
        $html .= " <tr class=\"".$class."\">\n";
        $html .= "  <td align=\"left\" width=\"60%\" style=\"$extraStyle; $leftOutStyle;\">";
        $html .= formatReady( $question->getText() );
        $html .= ($group->isMandatory() ? "<span class=\"eval_error\"><b>**</b></span>" : "");
        $html .= "</td>\n";

        foreach( $answerArray as $answer ) {
        $number = $question->isMultipleChoice() ? "[".$answer->getPosition()."]" : "";

        if( $answer->x_instanceof() == INSTANCEOF_EVALANSWER ) {
            $extraStyle = "";
            if( $answer->isResidual() ) {
            $extraStyle = "border-left: $residualBorder;";
            $html .=
#               "<td align=\"center\" class=\"content_seperator\" ".
#               "style=\"border-left: $answerBorder; border-top: $answerBorder;\" ".
#               "width=\"2\"></td>";
                "<td align=\"center\" class=\"steelgroup7\" style=\"$extraStyle\" ".
                "width=\"1%\">&nbsp;</td>";
            }

            $extraStyle .= ($question->getPosition() == $group->getNumberChildren() - 1
                    ? " border-bottom: $answerBorder;"
                    : "");
            $answers = Request::getArray('answers');
            $checked = $answers[$question->getObjectID()] == $answer->getObjectID() ? "checked" : "";

            $html .= "  <td align=\"center\" style=\"border-left: $answerBorder; $extraStyle;\" ".
            "width=\"".$cellWidth."%\">";
            $html .= "<input type=\"".$type."\" name=\"answers[".$question->getObjectID()."]".$number."\" ".
            "value=\"".$answer->getObjectID()."\" ".$checked.">";
            $html .= "</td>\n";
        }
        }
        $html .= " </tr>\n";
        /* -------------------------------------------- */
    }


    /* Normal (question with long answers) ----------------------------- */
    else {
        $class = $question->getPosition() % 2 ? "table_row_even" : "table_row_odd";

        /* Question ----------------------------------- */
        $html .=
        "<tr class=\"".$class."\">".
        "<td align=\"left\" style=\"$leftOutStyle;\">".
        formatReady( $question->getText() ).
        ($group->isMandatory() ? "<span class=\"eval_error\"><b>**</b></span>" : "").
        "</td>".
        "</tr>\n";

        $html .= "<tr class=\"".$class."\">";
        $html .= "<td>";
        $html .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
        /* -------------------------------------------- */

        $numberOfVisibleAnswers = 0;
        foreach( $answerArray as $answer )
        if( ! ($answer->isFreetext() && $answer->getText() != '') )
            $numberOfVisibleAnswers++;

        if( $numberOfVisibleAnswers == 0 ) {
        $html .= "<tr valign=\"middle\">\n";
        $html .=
            "<td class=\"eval_error\">".
            _("Dieser Frage wurden keine Antworten zugeordnet!").
            "</td>\n";
        $html .= "</tr>\n";
        }

        /* Answers ------------------------------------ */
        foreach( $answerArray as $answer ) {
        if( $answer->x_instanceof() == INSTANCEOF_EVALANSWER ) {
            $number = $question->isMultipleChoice() ? "[".$answer->getPosition()."]" : "";

            /* if not a user's answer */
            if( ! ($answer->isFreetext() && $answer->getText() != '') )  {
            $html .= "<tr valign=\"middle\">\n";

            /* show text input field ---------- */
            if( $answer->isFreetext() ) {

                // not really needed anymore
                if( $numberOfVisibleAnswers > 1 )
                /* show a check/radio-box */
                $html .=
                    "<td width=\"2%\">".
                    "<input type=\"".$type."\"".
                    " name=\"answers[".$question->getObjectID()."]".$number."\"".
                    " value=\"".$answer->getObjectID()."\">".
                    "</td>\n";

                /* one row input field */
                $freetexts = Request::getArray('freetexts');
                if( $answer->getRows() == 1)
                $html .=
                    "<td colspan=\"2\">".
                    "<input type=\"text\"".
                    " name=\"freetexts[".$question->getObjectID()."]\"".
                    " value=\"".htmlReady($freetexts[$question->getObjectID()])."\" size=\"60\">".
                    "</td>\n";

                /* multiple row input field (textarea) */
                else
                $html .=
                    "<td colspan=\"2\">".
                    "<textarea".
                    " name=\"freetexts[".$question->getObjectID()."]\"".
                    " cols=\"60\" rows=\"".$answer->getRows()."\">".
                    htmlReady($freetexts[$question->getObjectID()]).
                    "</textarea>".
                    "</td>\n";
            }

            /* show normal answer ------------- */
            else {
                $answers = Request::getArray('answers');
                /* see if it must be checked  */
                if( $type == "radio" )
                $checked = $answers[$question->getObjectID()] == $answer->getObjectID()
                    ? "checked"
                    : "";
                else
                $checked = ( is_array($answers[$question->getObjectID()]) &&
                         in_array( $answer->getObjectID(), $answers[$question->getObjectID()] ) )
                    ? "checked"
                    : "";

                /* show a check/radio-box */
                $html .=
                "<td width=\"2%\">".
                "<input type=\"".$type."\"".
                " name=\"answers[".$question->getObjectID()."]".$number."\"".
                " value=\"".$answer->getObjectID()."\" ".$checked.">".
                "</td>\n";
                $html .=
                "<td align=\"left\" width=\"98%\">".
                formatReady( $answer->getText() ).
                "</td>\n";
            }
            $html .= "</tr>\n";
            }
        }
        /* ------------------------------- End: Answers */
        }

        $html .= "</table>\n";
        $html .= "</td></tr>";
    }

    return $html;
    }

}
# ================================================================ end #
?>
