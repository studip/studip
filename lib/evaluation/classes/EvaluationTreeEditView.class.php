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

use Studip\Button, Studip\LinkButton;

# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_LIB_COMMON;
require_once EVAL_FILE_EVALTREE;
require_once EVAL_FILE_EVAL;
# ====================================================== end: including files #

/**
 * Class to print out the an evaluation's admin-tree
 *
 * @author  Christian Bauer <alfredhitchcock@gmx.net>
 * @copyright   2004 Stud.IP-Project
 * @access  public
 * @package     evaluation
 * @modulegroup evaluation_modules
 */


# defines ==================================================================== #

/**
 * @const NO_TEMPLATE_GROUP  title of the template without temtplateID
 * @access private
 */
define ('NO_TEMPLATE_GROUP', _('keine Vorlage'));

/**
 * @const NO_TEMPLATE_GROUP_TITLE  title of questiongroup without title
 * @access private
 */
define ('NO_QUESTION_GROUP_TITLE', _('*Fragenblock*'));

/**
 * @const NO_TEMPLATE  title of a template without title
 * @access private
 */
define ('NO_TEMPLATE', _('*unbekannt*'));

/**
 * @const NEW_EVALUATION_TITLE  title of a new question block
 * @access public
 */
define ('NEW_EVALUATION_TITLE', _('Neue Evaluation'));

/**
 * @const FIRST_ARRANGMENT_BLOCK_TITLE  title of a new arrangment block
 * @access public
 */
define ('FIRST_ARRANGMENT_BLOCK_TITLE', _('Erster Gruppierungsblock'));

/**
 * @const NEW_ARRANGMENT_BLOCK_TITLE  title of a new arrangment block
 * @access private
 */
define ('NEW_ARRANGMENT_BLOCK_TITLE', _('Neuer Gruppierungsblock'));

/**
 * @const NEW_QUESTION_BLOCK_BLOCK_TITLE  title of a new question block
 * @access private
 */
define ('NEW_QUESTION_BLOCK_BLOCK_TITLE', _('Neuer Fragenblock'));

/**
 * @const ROOT_BLOCK  the root item
 * @access private
 */
define ('ROOT_BLOCK', 'root');

/**
 * @const ARRANGMENT_BLOCK  the arrangment block item
 * @access private
 */
define ('ARRANGMENT_BLOCK', 'ARRANGMENT_BLOCK');

/**
 * @const QUESTION_BLOCK  the question block item
 * @access private
 */
define ('QUESTION_BLOCK', 'QUESTION_BLOCK');

# =============================================================== end: defines #


# classes ==================================================================== #

class EvaluationTreeEditView {

/**
* Reference to the tree structure
*
* @access   public
* @var      object EvaluationTree  $tree
*/
var $tree;

/**
* contains the item with the current html anchor
*
* @access   public
* @var      string  $anchor
*/
var $anchor;

/**
* the item to start with
*
* @access   public
* @var      string  $startItemID
*/
var $startItemID;

/**
* true if changedate should be set
*
* @access   private
* @var      boolean  $changed
*/
var $changed;

/**
 * Holds the Evaluation object
 * @access   private
 * @var      object Evaluation  $eval
 */
var $eval;

/**
 * Holds the current Item-ID
 * @access   private
 * @var      string $itemID
 */
var $itemID;

/**
 * Holds the current evalID
 * @access   private
 * @var      integer  $evalID
 */
var $evalID;

/**
 * The itemID instance
 * @access   private
 * @var      string  $itemInstance
 */
var $itemInstance;

/**
 * constructor
 *
 * @access public
 * @param  string  $itemID the item to display
 * @param  string  $evalID the evaluation of the item
 */
function __construct( $itemID = ROOT_BLOCK, $evalID = NULL ){
    global $sess;

    $this->itemID = ($itemID) ? $itemID : ROOT_BLOCK;
    $this->startItemID = ($itemID) ? $itemID : ROOT_BLOCK;
    $this->evalID = $evalID;
    $this->itemInstance = $this->getInstance ($this->itemID);
    $this->changed = false;

    $this->tree = TreeAbstract::GetInstance ( "EvaluationTree", ['evalID' => $this->evalID,
                                                                        'load_mode' => EVAL_LOAD_ALL_CHILDREN]);

    # filter out an old session itemID ======================================= #
    if (is_array($this->tree->tree_data) && !is_null($itemID) ){
        if (!array_key_exists($itemID,$this->tree->tree_data)){
            $this->itemID = ROOT_BLOCK;
            $this->startItemID = ROOT_BLOCK;
            $this->tree->init ();
        }
    } else {
        $this->itemID = ROOT_BLOCK;
        $this->startItemID = ROOT_BLOCK;
        $this->tree->init ();
    }

    # handling the moveItemID =============================================== #
    if ( Request::submitted('create_moveItemID') )
        $this->moveItemID = Request::option("itemID");
    elseif ( Request::option("moveItemID") )
        $this->moveItemID = Request::get("moveItemID");

    if (Request::submitted("abbort_move"))
        $this->moveItemID = NULL;

    if ($this->moveItemID != NULL){
     if (is_array($this->tree->tree_data)){
        if (!array_key_exists($this->moveItemID,$this->tree->tree_data)){
            $this->moveItemID = NULL;
            }
     } else {
        $this->moveItemID = NULL;
     }
    }


    # execute the comand ==================================================== #
    $this->parseCommand ();

    # set the new changedate ================================================ #
    if ( $this->changed ){
        $this->tree->eval->setChangedate ( time() );
        $this->tree->eval->save ();
    }

}


################################################################################
#                                                                              #
# public functions                                                             #
#                                                                              #
################################################################################

/**
 * displays the EvaluationTree
 *
 * @access  public
 * @return  string the eval-tree (html)
*/
function showEvalTree(){

    $html = "<table width=\"99%\" border=\"0\" cellpadding=\"0\" "
        . "cellspacing=\"0\">\n";

    if ( $this->startItemID != ROOT_BLOCK ){

    $html .= " <tr>\n"
        . "  <td class=\"table_row_odd\" align=\"left\" valign=\"top\" "
        . "colspan=\"";
    $html .= ($this->moveItemID) ? "1" : "1";
    $html .="\""
        . ">\n"
        . $this->getEvalPath()
#       . "<img src=\"".
#       . "/forumleer.gif\"  border=\"0\" height=\"20\" width=\"1\">\n"
        . "   </td>\n"
        . " </tr>\n";
    }
   # display the infos when moving a block =================================== #

   if ($this->moveItemID){

    $html .= " <tr>\n";
#       . "   <td width=\"10\"class=\"blank tree-indent\" "
#       . "background=\"".."forumstrich.gif\">"
#       . "<img src=\""
#       . ."forumstrich.gif\" width=\"10\" border=\"0\" >"
#       . "</td>\n"
    $html .= "  <td class=\"graulight\" align=\"left\" valign=\"top\" width=\"100%\">\n";


    $mode = $this->getInstance ($this->moveItemID);

    switch ($mode){

        case ARRANGMENT_BLOCK:
            $group =& $this->tree->getGroupObject($this->moveItemID);
            $title = htmlready ($group->getTitle());
            $msg = sprintf(_("Sie haben den Gruppierungsblock <b>%s</b> zum Verschieben ausgewählt. Sie können ihn nun in einen leeren Gruppierungsblock, einen Gruppierungsblock ohne Frageblöcke oder in die oberste Ebene verschieben."),$title);

            break;

        case QUESTION_BLOCK:
            $group = &$this->tree->getGroupObject ($this->moveItemID);
            $title = htmlready ($group->getTitle());
            if (!$title)
                $title = NO_QUESTION_GROUP_TITLE;
            $msg = sprintf(_("Sie haben den Fragenblock <b>%s</b> zum Verschieben ausgewählt. Sie können ihn nun in einen leeren Gruppierungsblock oder einen Gruppierungsblock mit Frageblöcke verschieben."),$title);
            break;

        default:

            $msg = _("Es wurde ein ungültiger Block zum verschieben ausgewählt.");
            break;
    }


    $table = new HTML ("table");
    $table->addAttr ("border","0");
    $table->addAttr ("cellspacing","0");
    $table->addAttr ("cellpadding","2");
    $table->addAttr ("width","100%");

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("align","center");
    $td->addAttr ("class","graulight");
    $td->addAttr ("width","25");

    $img = new HTMLempty ("img");
    $img->addAttr ("width","32");
    $img->addAttr ("height","32");
    $img->addAttr ("src",EVAL_PIC_INFO);

    $td->addContent ($img);
    $tr->addContent ($td);

    $td = new HTML ("td");
    $td->addAttr ("align","left");

    $font = new HTML ("font");
    $font->addAttr ("color","black");
    $font->addHTMLContent ( $msg );
    $font->addHTMLContent ( " " . sprintf(
        _("Benutzen Sie dieses %s Symbol, um den Block zu verschieben."),
        $this->createImage(EVAL_PIC_MOVE_GROUP,_("Block verschieben Symbol")) ));
    $font->addHTMLContent ("<br><br>"
        . _("Oder wollen Sie die Aktion abbrechen?")
        . " "
        . LinkButton::createCancel(_('Abbrechen'),
                $this->getSelf('abbort_move=1')));

    $td->addContent ($font);
    $tr->addContent ($td);
    $table->addContent ($tr);

    $html .= "<br>" . $table->createContent () . "<br>";

    $html .= "</td></tr>\n";
   }
   # ============================= END: display the infos when moving a block #

    $html .= " <tr>\n"
        . "  <td class=\"blank\"  align=\"left\" valign=\"top\" "
        . "colspan=\"";
    $html .= ($this->moveItemID) ? "1" : "1";
    $html .= "\""
        . ">\n";

    if ( !$this->startItemID != ROOT_BLOCK ){
        $html .= "<a name=\"anchor\"></a>\n";
    }

    $html .=  $this->showTree($this->startItemID, 1)
        . "  </td>\n"
        . " </tr>\n"
        . "</table>\n";

    return $html;
}

# ###################################################### end: public functions #


################################################################################
#                                                                              #
# show tree functions                                                          #
#                                                                              #
################################################################################

/**
* prints out the tree beginning at the parent-item
*
* @access  public
* @param   string   $itemID  the item to display
* @param   string   $start   YES if its the basecall
* @return  string   the tree (html)
*/
function showTree($itemID = ROOT_BLOCK, $start = NULL){

    $items = [];
    if (!is_array($itemID)){
        $items[0] = $itemID;

        $mode = $this->getInstance ($itemID);

        switch ($mode){

            case ROOT_BLOCK:
                $this->startItemID = $itemID;
                break;

            case ARRANGMENT_BLOCK:

            case QUESTION_BLOCK:
                $parentgroup = &$this->tree->getGroupObject ($itemID);
                $this->startItemID = $parentgroup->getObjectID ();
                break;
        }

        $this->startItemID = $itemID;
    } else {
        $items = $itemID;
    }
    $num_items = count($items);

    $html ="";

    // this is the first / the opened item
    if ($start){

        $mode = $this->getInstance ($itemID);

        switch ($mode){

            case ROOT_BLOCK:

                break;

            case ARRANGMENT_BLOCK:

            case QUESTION_BLOCK:

                $group      = &$this->tree->getGroupObject ($itemID);
                $parentID   = $group->getParentID ();

                $mode = $this->getInstance ($parentID);

                if ($mode == ROOT_BLOCK){

                    $eval = new Evaluation ($this->evalID, NULL, EVAL_LOAD_FIRST_CHILDREN);
                    while ($child = $eval->getNextChild ())
                        $items2[] = $child->getObjectID ();
                }
                else {

                    $parentgroup = &$this->tree->getGroupObject ($parentID, NULL, EVAL_LOAD_FIRST_CHILDREN);
                    while ($child = $parentgroup->getNextChild ())
                        $items2[] = $child->getObjectID ();
                }

                $num_items2 = count($items2);

                $num_items = $num_items2;
                $items = $items2;
                break;

        }

    }

    for ($j = 0; $j < $num_items; ++$j){

        $html .= $this->createTreeLevelOutput($items[$j]);
        $html .= $this->createTreeItemOutput($items[$j]);

        if ( $this->tree->hasKids($items[$j]) &&
             $this->itemID == $items[$j] )
            $html .= $this->showTree($this->tree->tree_childs[$items[$j]]);
    }

    return $html;
}


/**
* creates the parentslinks
*
* @access  private
* @return  string  the eval path as html-links
*/
function getEvalPath(){

    $path = "<a name=\"anchor\">&nbsp;</a>\n"
        . _("Sie sind hier:")
        . "&nbsp;";
    if ( 0 && $this->startItemID != ROOT_ITEM ){


    $path .= "<a class=\"tree\" href=\""
        . URLHelper::getLink($this->getSelf("itemID=root"))
        . "\">"
#       . Icon::create('arr_1right', 'attention')->asImg()
#       . "width=\"10\" hight=\"20\">"
#       . "&nbsp;"
        . _("Evaluation")
        . "</a>";
    }
    $path .=  "<a class=\"tree\" href=\""
        . URLHelper::getLink($this->getSelf("itemID=" . ROOT_BLOCK, false))
        . "\">"
        . htmlready( my_substr (
            $this->tree->tree_data[ROOT_BLOCK]["name"],0,60))
        . "</a>";

    # collecting the parent blocks =========================================== #

    if ($parents = $this->tree->getParents($this->startItemID)){
        for($i = count($parents)-1; $i >= 0; --$i){
           if ($parents[$i] != ROOT_BLOCK)
            $path .= "&nbsp;&gt;&nbsp;"
                . "<a class=\"tree\" href=\""
                . URLHelper::getLink($this->getSelf("itemID={$parents[$i]}", false))
                . "\">"
                . htmlready( my_substr (
                    $this->tree->tree_data[$parents[$i]]["name"],0,60))
                . "</a>";
        }
    }
    # ====================================== END: collecting the parent blocks #
    return $path;
}


/**
* returns html for the icons in front of the name of the item
*
* @access  private
* @param   string   $itemID the item-heas id
* @return  string   the item head (html)
*/
function getItemHeadPics ( $itemID ){

    $mode = $this->getInstance( $itemID );

   if ($this->itemID == $itemID){

    $img = new HTMLempty ("img");
    $img->addAttr ("src",EVAL_PIC_TREE_ARROW_ACTIVE);
    $img->addAttr ("border","0");
    $img->addAttr ("align","baseline");
    $img->addAttr ("hspace","2");
    $img->addString (tooltip (_("Dieser Block ist geöffnet."),true));
    $head = $img->createContent();

   } else {

    $a = new HTML ("a");
    $a->addAttr ("href",URLHelper::getLink($this->getSelf("itemID={$itemID}")));

    $img = new HTMLempty ("img");
    $img->addAttr ("src",EVAL_PIC_TREE_ARROW);
    $img->addAttr ("border","0");
    $img->addAttr ("align","baseline");
    $img->addAttr ("hspace","2");
    $img->addString (tooltip (_("Diesen Block öffnen."),true));

    $a->addContent ($img);

    $head = $a->createContent ();

   }

   # collecting the image and tooltip for this item ========================== #

   switch ($mode){

    case ROOT_BLOCK:

        $tooltip = _("Dies ist Ihre Evaluation.");
        $image = EVAL_PIC_ICON;
        break;

    case ARRANGMENT_BLOCK:

        $group = &$this->tree->getGroupObject($itemID);

        $tooltip = ($group->getNumberChildren () == 0)
            ? _("Dieser Gruppierungsblock enthält keine Blöcke.")
            : sprintf(_("Dieser Grupppierungsblock enthält %s Blöcke."),
                $group->getNumberChildren ());

        $image = ($group->getNumberChildren () == 0)
            ? EVAL_PIC_TREE_GROUP
            : EVAL_PIC_TREE_GROUP_FILLED;

        break;

    case QUESTION_BLOCK:

        $group = &$this->tree->getGroupObject($itemID);

        $tooltip = ($group->getNumberChildren () == 0)
            ? _("Dieser Fragenblock enthält keine Fragen.")
            : sprintf(_("Dieser Fragenblock enthält %s Fragen."),
                $group->getNumberChildren ());

        $image = ($group->getNumberChildren () == 0)
            ? EVAL_PIC_TREE_QUESTIONGROUP
            : EVAL_PIC_TREE_QUESTIONGROUP_FILLED;

        break;

    default:

        $tooltip = _("Kein Blocktyp.");
        $image = EVAL_PIC_TREE_GROUP;

        break;
   }

   # ===================== END: collecting the image and toolpi for this item #

    $img = new HTMLempty ("img");
    $img->addAttr ("border","0");
    $img->addAttr ("align","baseline");
    $img->addAttr ("src",$image);
    $img->addString (tooltip ($tooltip,true));

    $head .= $img->createContent ();

    return $head;
}


/**
* creates the content for all item-types
*
* @access  private
* @param   string   $itemID the item-heas id
* @return  string   the item content (html)
*/
function getItemContent($itemID){

    $content = "";

    if ($this->getItemMessage($itemID)){

        $table = new HTML ("table");
        $table->addAttr ("width","99%");
        $table->addAttr ("cellpadding","2");
        $table->addAttr ("cellspacing","2");
        $table->addAttr ("style","font-size:10pt;");

        $tr = new HTML ("tr");

        $td = new HTML ("td");
        $td->addHTMLContent ($this->getItemMessage($itemID));

        $tr->addContent ($td);
        $table->addContent ($tr);

        $content .= "<br>" . $table->createContent ();
    }


    $content .= "<form class=\"default\" action=\"".URLHelper::getLink($this->getSelf("item_id={$itemID}",1))
            . "\" method=\"POST\" style=\"display:inline;\">\n";
    $content .= CSRFProtection::tokenTag();

    $content .= "<br>";

    $mode = $this->getInstance($itemID);

    switch ($mode){
     case ROOT_BLOCK:

        $content .= $this->createTitleInput(ROOT_BLOCK)
            . $this->createGlobalFeatures()

            . $this->createButtonbar(ROOT_BLOCK);
        break;

     case ARRANGMENT_BLOCK:

        $content .= $this->createTitleInput(ARRANGMENT_BLOCK);

            $group = &$this->tree->getGroupObject($itemID);
            if ($children = $group->getChildren()){
                if ($this->getInstance( $children[0]->getObjectID()) == ARRANGMENT_BLOCK)
                    $show = ARRANGMENT_BLOCK;
                else
                    $show = QUESTION_BLOCK;
            } else
                $show = "both";
        $content .=  $this->createButtonbar($show);
        break;

     case QUESTION_BLOCK:

        $content .= $this->createTitleInput(QUESTION_BLOCK)
            . $this->createQuestionFeatures()
            . $this->createQuestionForm()
            . $this->createButtonbar(NULL);
        break;
    }

    $content .= "</form>\n";

    return $content;
}


/**
* prints out the lines before an item ("Strichlogik" (c) rstockm)
*
* @access  private
* @param   string   $item_id       the current item
* @param   string   $start_itemID  the start item
* @return  string   the level output (html)
*/
function createTreeLevelOutput($item_id, $start_itemID = NULL){

    $level_output = "";

    // without the first strichcode
    $item_parent = $this->tree->tree_data[$item_id]['parent_id'];
    $startitem_parent = $this->tree->tree_data[$this->startItemID]['parent_id'];

    if ( ($item_parent != $startitem_parent) && ( $item_parent != NULL )
        && (
        ($item_id != ROOT_BLOCK) ||
        ($item_id != $this->tree->tree_data[$this->startItemID]['parent_id']))){
        if ($this->tree->isLastKid($item_id) || $item_id == ROOT_BLOCK)
            $level_output = "<td class=\"blank tree-indent\" valign=\"top\"  "
                . "nowrap>"
                . Assets::img('forumstrich2.gif')
                . "</td>"; //last
        else
            $level_output = "   <td class=\"blank tree-indent\" valign=\"top\" "
                . "nowrap>"
                . Assets::img('forumstrich3.gif')
                . "</td>"; //crossing

        $parent_id = $item_id;
        $counter=0;
        while(
            ( 0 ) &&
            ($this->tree->tree_data[$parent_id]['parent_id'] != $this->tree->tree_data[$this->startItemID]['parent_id'] ) &&
            ($this->tree->tree_data[$parent_id]['parent_id'] != $start_itemID) &&
            ($this->tree->tree_data[$parent_id]['parent_id'] != ROOT_BLOCK)){
            $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];
            $counter++;

            if ($this->tree->isLastKid($parent_id)){
                $level_output = "<td class=\"blank\" valign=\"top\" "
                    . "width=\"10\" nowrap>"
                    . Assets::img('forumleer.gif')
                    . "</td>"
                    . $level_output; //nothing
            } else {
                $level_output = "   <td class=\"blank tree-indent\" valign=\"top\"  "
                    . "nowrap>"
                    . Assets::img('forumstrich.gif')
                    . "</td>"
                    . $level_output; //vertical line
            }

        }

        // the root-item
        if ((0) &&
            ($this->startItemID == ROOT_BLOCK) &&
            ($this->tree->tree_data[$item_id]['parent_id'] == ROOT_BLOCK)){
                $level_output = "<td class=\"blank\" valign=\"top\" "
                    . "width=\"10\" nowrap>"
                    . Assets::img('forumleer.gif')
                    . "</td>"
                    . $level_output; //nothing
        }

    }

    $html = "<table border=\"0\" width=\"100%\" cellspacing=\"0\" "
        . "cellpadding=\"0\">"
        . " <tr>$level_output";
    return $html;
}


/**
* prints out one item
*
* @access  private
* @param   string   $item_id  the items id
* @return  string             one item (html)
*/
function createTreeItemOutput($item_id){

    $html = "  <td  class=\"printhead\" nowrap  align=\"left\" "
        . "valign=\"bottom\">\n"
        . $this->getItemHeadPics($item_id) ."\n"
        . "  </td>\n"
        . "  <td class=\"printhead\" nowrap width=\"1\" valign=\"middle\">\n";
    if ($this->anchor == $item_id)
        $html .= "<a name=\"anchor\">";
    $html .= Assets::img('forumleer.gif');
    if ($this->anchor == $item_id)
        $html .= "</a>";
    $html .= "\n"
        . "  </td>\n"
        . "  <td class=\"printhead\" align=\"left\" width=\"99%\" "
        . "nowrap valign=\"bottom\">"
        . $this->getItemHead($item_id)
        . "  </td>\n"
        . " </tr>\n"
        . "</table>\n";
    if ($this->itemID == $item_id)
        $html .= $this->createTreeItemDetails($item_id);
    return $html;
}


/**
* prints out the item details
*
* @access  private
* @param   string   $item_id the current item
* @return  string   the item details (html)
*/
function createTreeItemDetails($item_id){

    $mode = $this->getInstance ($item_id);

    switch ($mode){

        case ROOT_BLOCK:
            $eval = new Evaluation ($this->evalID, NULL, EVAL_LOAD_FIRST_CHILDREN);
            $hasKids = ($eval->getNumberChildren() == 0)
                ? NO
                : YES;
            $isLastKid = YES;
            break;
            break;

        case ARRANGMENT_BLOCK:
            $group = &$this->tree->getGroupObject($item_id);
            $hasKids = ($group->getNumberChildren() == 0)
                ? NO
                : YES;

            $par = $this->getInstance ($group->getParentID ());

             if ($par == ROOT_BLOCK)
                $parent = new Evaluation ($group->getParentID (), NULL, EVAL_LOAD_FIRST_CHILDREN);
            else
                $parent =& $this->tree->getGroupObject($group->getParentID ());

            $isLastKid = ($parent->getNumberChildren()
                == $group->getPosition () + 1)
                ? YES
                : NO;
            break;

        case QUESTION_BLOCK:

            $hasKids = NO;

            $group =& $this->tree->getGroupObject($item_id);
            $par = $this->getInstance ($group->getParentID ());

             if ($par == ROOT_BLOCK) $parent = new Evaluation ($group->getParentID (), NULL, EVAL_LOAD_FIRST_CHILDREN);
             else $parent = &$this->tree->getGroupObject($group->getParentID);
            $isLastKid = ($parent->getNumberChildren()
                == $group->getPosition () + 1)
                ? YES
                : NO;
            break;

        default:
            $hasKids = NO;
            $isLastKid = NO;
            break;
    }

    if (!$hasKids || (!$this->itemID == $item_id))
        $level_output = $this->createLevelOutputTD ("forumleer.gif") . $level_output;
    else
        $level_output = $this->createLevelOutputTD ("forumstrich.gif") . $level_output;#

#   if (($isLastKid))
#       $level_output = $this->createLevelOutputTD ("forumleer.gif") . $level_output;
#   else
#       $level_output = $this->createLevelOutputTD ("forumstrich.gif") . $level_output;

    if ($item_id != $this->startItemID){
        $parent_id = $item_id;

        while(($this->tree->tree_data[$parent_id]['parent_id'] != $this->tree->tree_data[$this->startItemID]['parent_id'] ) &&
            ($this->tree->tree_data[$parent_id]['parent_id'] != $start_itemID) &&
            ($this->tree->tree_data[$parent_id]['parent_id'] != ROOT_BLOCK)){

            $parent_id = $this->tree->tree_data[$parent_id]['parent_id'];

#           if (($this->tree->isLastKid($parent_id)) || (!$hasKids))
#               $level_output = $this->createLevelOutputTD ("forumleer.gif") . $level_output;
#           else
#               $level_output = $this->createLevelOutputTD ("forumstrich.gif") . $level_output;
            }
    }

    $table = new HTML ("table");
    $table->addAttr ("border","0");
    $table->addAttr ("cellspacing","0");
    $table->addAttr ("cellpadding","0");
    $table->addAttr ("width","100%");

    $tr = new HTML ("tr");

    if ($level_output);
        $tr->addHTMLContent ($level_output);

    $td = new HTML ("td");
    $td->addAttr ("class","printcontent");
    $td->addAttr ("width","100%");

    $div = new HTML ("div");
    $div->addAttr ("align","center");
    $div->setTextareaCheck ();
    $div->addHTMLContent ($this->getItemContent($item_id));

    $td->addContent ($div);
    $tr->addContent ($td);
    $table->addContent ($tr);

    return $table->createContent ();
}


/**
* creates the items head
*
* @access  private
* @param   string   $itemID the current item
* @return  string   the item head (html)
*/
function getItemHead($itemID){

    $mode = $this->getInstance($itemID);

    if ($this->itemID == $itemID){

#       $group = new EvaluationGroup($itemID);
        $head = "&nbsp;";
        if ($this->tree->tree_data[$itemID]['name'] == "" && $mode == QUESTION_BLOCK)
            $head .= NO_QUESTION_GROUP_TITLE;
        else
            $head .= htmlready(my_substr (
                $this->tree->tree_data[$itemID]['name'],0,60));

    } else {

        if ($mode == QUESTION_BLOCK){

            $group = &$this->tree->getGroupObject($itemID);
            $templateID = $group->getTemplateID();
            if ($templateID){
                $template = new EvaluationQuestion($templateID);
                $templateTitle = htmlReady ($template->getText());
            } else
                $templateTitle = NO_TEMPLATE_GROUP;

            if ( $templateTitle == "" )
                $templateTitle = NO_TEMPLATE;

            $template = "   </td>\n"
                . "   <td align=\"right\" valign=\"bottom\" "
                . "class=\"printhead\" nowrap=\"nowrap\">\n"
                . "<b>"
                . _("Vorlage") . ": "
                . $templateTitle
                . "</b>&nbsp;";

        }

        $head = "&nbsp;<a class=\"tree\" href=\""
            . URLHelper::getLink($this->getSelf("itemID={$itemID}",false)) . "\"" . tooltip(_("Diesen Block öffnen"),true) . ">";

        if ($this->tree->tree_data[$itemID]['name'] == "" && $mode == QUESTION_BLOCK)
            $head .= NO_QUESTION_GROUP_TITLE;
        else
            $head .= htmlready(my_substr (
                $this->tree->tree_data[$itemID]['name'],0,60));
        $head .= "</a>";

        if ($template)
            $head .= $template;
    }

    if ($itemID == ROOT_BLOCK)
        $itemID2 = $this->evalID;
    else
        $itemID2 = $itemID;

    // the "verschiebäfinger"
    if ($this->moveItemID &&
        ($this->tree->tree_data[$itemID]['parent_id'] != $this->moveItemID) &&
        ($mode == ARRANGMENT_BLOCK || $itemID == ROOT_BLOCK) &&
        $this->moveItemID != $itemID2){

        $parentID = $this->tree->tree_data[$itemID]['parent_id'];
        if (!$parentID) $parentID = ROOT_BLOCK;
        while ($parentID != ROOT_BLOCK && $parentID != $this->moveItemID){
            $parentID = $this->tree->tree_data[$parentID]['parent_id'];
            if ($parentID == $this->moveItemID)
                $moveItemIsParent = 1;
        }

        $moveItem = "   </td>\n"
            . "   <td align=\"right\" valign=\"middle\" class=\"printhead\" nowrap=\"nowrap\">\n"
            . $this->createLinkImage(EVAL_PIC_MOVE_GROUP,
                _("Den ausgwählten Block in diesen Block verschieben"),
                "&itemID=$itemID&cmd=MoveGroup",
                NO,NULL,NO)
            . "&nbsp;";
    }

    if ($moveItem && !$moveItemIsParent){
        $move_mode = $this->getInstance ($this->moveItemID);

        if ($mode == ARRANGMENT_BLOCK){
            $group = &$this->tree->getGroupObject ($itemID);
            if ($children = $group->getChildren()){
                if ($this->getInstance( $children[0]->getObjectID()) == ARRANGMENT_BLOCK)
                    $move_type = ARRANGMENT_BLOCK;
                else
                    $move_type = QUESTION_BLOCK;
            } else
                $move_type = "both";
        } elseif ($mode == ROOT_BLOCK)
            $move_type = ARRANGMENT_BLOCK;
        else
            $move_type = "no";



        if (($move_type == "both") ||
            ($move_mode == $move_type)){
            $head .= $moveItem;
        }
    }

    if (!($this->tree->isFirstKid($itemID) && $this->tree->isLastKid($itemID)) &&
        ($itemID != $this->startItemID) &&
        ($this->tree->tree_data[$itemID]['parent_id'] == $this->startItemID)){
        $head .= "   </td>\n"
            . "   <td align=\"right\" valign=\"bottom\" class=\"printhead\" nowrap=\"nowrap\">\n"
            . $this->createLinkImage(EVAL_PIC_MOVE_UP,
                _("Block nach oben verschieben"),
                "cmd=Move&direction=up&groupID=$itemID",
                NO)
            . $this->createLinkImage(EVAL_PIC_MOVE_DOWN,
                _("Block nach unten verschieben"),
                "cmd=Move&direction=down&groupID=$itemID",
                NO)
            . "&nbsp;";
    }
    return $head;
}


/**
* creates a table and calls the ItemMessages
*
* @access  private
* @param   string   $itemID   the current item
* @param   integer  $colspan  the needed colspan (optional)
* @return  string             the item message (html)
*/
function getItemMessage($itemID, $colspan = 1)
{
    if ($this->msg[$itemID]) {
        $msg = explode("§", $this->msg[$itemID]);

        if ($msg[0] == 'msg') {
            $msg[0] = 'success';
        }
        if (mb_strpos($msg[1], '<br>')) {
            $details = explode("<br>", $msg[1]);
            $msg[1] = array_shift($details);
        }

        return (string) MessageBox::{$msg[0]}($msg[1], $details);
    } else {
        return NULL;
    }
}


/**
* creates a self-url with add. items
*
* @access  private
* @param   string  $param            params (optional)
* @param   boolean $with_start_item  startItem needed? (optional)
* @return  string                    the self url
*/
function getSelf ( $param = "", $with_start_item = true ){

    $url = "?page=edit";

    if ($this->evalID)
        $url .= "&evalID=".$this->evalID;
    else
        $url .= "&evalID=".Request::option("evalID");

    if ($param){
        $url .= (($with_start_item)
            ? "&itemID=" . $this->startItemID . "&"
            : "&") . $param;
    } else {
        $url .= (($with_start_item)
            ? "&itemID=" . $this->startItemID
            : "");
    }

    if ($this->moveItemID)
        $url .= "&moveItemID=" . $this->moveItemID;

    $url .= "#anchor";

    return $url;
}

# ################################################### end: show tree functions #


################################################################################
#                                                                              #
# command functions                                                            #
#                                                                              #
################################################################################

/**
* parses the _Request-commands and calls the avaible functions
*
* @access  private
*/
function parseCommand(){

    if (Request::option('cmd') || Request::optionArray('cmd')){
        # extract the command from Request (array) =========================== #

        if (Request::optionArray('cmd'))
            $exec_func = "execCommand" . key(Request::optionArray('cmd'));
        else
            $exec_func = "execCommand" . Request::option('cmd');

    } else {

        # extract the command from the template-site ========================= #
        foreach( $_REQUEST as $key => $value ) {
            if( preg_match( "/template_(.*)_#(.*)_button?/", $key, $command ) ){
                $found = 1;
                break;
            }
        }

       if (!$found){
        foreach( $_REQUEST as $key => $value ) {
            if( preg_match( "/cmd_(.*)_#(.*)_§(.*)_button?/", $key, $command ) )
                break;
        }
       }


        if ($command[1] == "create_question_answers")
            $exec_func = "execCommandQuestionAnswersCreate";
        else
            $exec_func = "execCommand" . $command[1];
        # ==================== END: extract the command from the template-site #
    }

    if (method_exists($this,$exec_func)){
        if ($this->$exec_func()){
            $this->tree->init();
            $this->tree->eval->save ();
        }
    }
}


/**
 * Creates cancel-message
 * @access   public
 * @return   boolean  true (reinits the tree)
 */
function execCommandCancel(){


    $itemID = Request::option('startItemID');

    $this->anchor = $itemID;
    $this->msg[$this->startItemID] .= "info§"
        . sprintf(_("Die Aktion wurde abgebrochen."));
    return false;
}

/**
* Updates the item content of any kind
*
* @access  private
* @param   boolean  $no_delete  YES/NO (optional)
* @return  boolean  true (reinits the tree)
*/
function execCommandUpdateItem ( $no_delete = false ){


    $mode = $this->getInstance($this->itemID);

    $title = Request::get('title');
    if ($title == "" && $mode != QUESTION_BLOCK)
        $title = _("Kein Titel angegeben.");
    $text = Studip\Markup::purifyHtml(trim(Request::get('text')));

    switch ($mode){
     case ROOT_BLOCK:

        $this->tree->eval->setTitle($title);
        $this->tree->eval->setText($text);

        //global features
        $this->tree->eval->setAnonymous(Request::get('anonymous'));

        $this->tree->eval->save();

        if ($this->tree->eval->isError)
            return EvalCommon::showErrorReport ($this->tree->eval,
                _("Fehler beim Einlesen (root-item)"));
        $this->msg[$this->itemID] = "msg§"
            . _("Veränderungen wurden gespeichert.");

        break;
     case ARRANGMENT_BLOCK:

        $group = &$this->tree->getGroupObject($this->itemID, true);

        $group->setTitle($title);
        $group->setText($text);
        $group->save();
        if ($group->isError)
            return EvalCommon::showErrorReport ($this->tree->eval,
                _("Fehler beim Einlesen (Block)"));
        $this->msg[$this->itemID] = "msg§"
            . _("Veränderungen wurden gespeichert.");
        $group = null;
        break;
     case QUESTION_BLOCK:

        $group = &$this->tree->getGroupObject($this->itemID, true );
        $group->setTitle($title);
        $group->setText($text);
        $group->setMandatory(Request::get('mandatory'));
        $group->save();

        // update the questions
        $msg = $this->execCommandUpdateQuestions();

        $no_answers = 0;
        $group = &$this->tree->getGroupObject($this->itemID, true);
        // info about missing answers
        if ($group->getChildren() && $group->getTemplateID() == NULL){
            foreach ($group->getChildren() as $question){
                if ($question->getChildren() == NULL)
                    $no_answers++;
            }
            if ($no_answers == 1){
                if ($this->msg[$this->itemID])
                    $this->msg[$this->itemID] .= "<br>"._("Einer Frage wurden noch keine Antwortenmöglichkeiten zugewiesen.");
                else
                    $this->msg[$this->itemID] .= "info§"._("Einer Frage  wurden noch keine Antwortenmöglichkeiten zugewiesen.");
            } elseif ($no_answers > 1){
                if ($this->msg[$this->itemID])
                    $this->msg[$this->itemID] .= "<br>".sprintf(_("%s Fragen wurden noch keine Antwortenmöglichkeiten zugewiesen."),$no_answers);
                else
                    $this->msg[$this->itemID] .= "info§".sprintf(_("%s Fragen wurden noch keine Antwortenmöglichkeiten zugewiesen."),$no_answers);
            }

        }

        if ($group->isError)
            return EvalCommon::showErrorReport ($this->tree->eval,
                _("Fehler beim Einlesen (Fragenblock)"));
        if ($this->msg[$this->itemID])
            $this->msg[$this->itemID] .= "<br>"._("Veränderungen wurden gespeichert.");
        else
            $this->msg[$this->itemID] .= "msg§"
                . _("Veränderungen wurden gespeichert.");

        if ($msg)
            $this->msg[$this->itemID] = $this->msg[$this->itemID]."<br>".$msg;

#   }
        break;
     default:
        $this->msg[$this->itemID] .= "info§"
            . _("Falscher Blocktyp. Es wurden keine Veränderungen vorgenommen.");
        break;
    }

    $this->changed = true;

    return true;
}


/**
 * Creates a delete-request
 *
 * @access   public
* @return    boolean  false
 */
function execCommandAssertDeleteItem(){


    $group = &$this->tree->getGroupObject($this->itemID);
    if ($group->getChildType() == "EvaluationQuestion")
        $numberofchildren = $group->getNumberChildren();
    else
        $numberofchildren = $this->tree->getNumKidsKids($this->itemID);

    $title = htmlready ($group->getTitle ());

    // constructing the message
    $this->msg[$this->itemID] = "info§";

    if ($group->getChildType() == "EvaluationQuestion"){
        if ($numberofchildren){
            $this->msg[$this->itemID] .= ""
            . sprintf(
                _("Sie beabsichtigen den Fragenblock <b>%s</b> inklusive aller Fragen zu löschen. "),
                $title)
            . sprintf(_("Es werden insgesamt %s Fragen gelöscht!") ,$numberofchildren);
        } else {
            $this->msg[$this->itemID] .= ""
            . sprintf(
                _("Sie beabsichtigen den Fragenblock <b>%s</b> inklusive aller Fragen zu löschen. "),
                $title);
        }
        $this->msg[$this->itemID] .= "<br>"
            . _("Wollen Sie diesen Fragenblock wirklich löschen?");
    } else {
        if ($numberofchildren){
            $this->msg[$this->itemID] .= ""
            . sprintf(
                _("Sie beabsichtigen den Gruppierungsblock <b>%s</b> inklusive aller Unterblöcke zu löschen. "),
                $title)
            . sprintf(_("Es werden insgesamt %s Unterblöcke gelöscht!"),$numberofchildren);
        } else {
            $this->msg[$this->itemID] .= ""
            . sprintf(
                _("Sie beabsichtigen den Gruppierungsblock <b>%s</b> inklusive aller Unterblöcke zu löschen. "),
                $title);
        }
        $this->msg[$this->itemID] .= "<br>"
        . _("Wollen Sie diesen Gruppierungsblock wirklich löschen?");
    }

    $this->msg[$this->itemID] .= "<br><br>"
        . LinkButton::createAccept(_('JA!'),
                $this->getSelf('cmd[DeleteItem]=1'),
                ['title' => _('Löschen')])
        . "&nbsp;"
        . LinkButton::createCancel(_('NEIN!'),
                $this->getSelf('cmd[Cancel]=1'))
        . "\n";

    return false;
}

/**
 * Deletes an Item and its kids
 * @access   public
 * @return   boolean  true (reinits the tree)
 */
function execCommandDeleteItem(){

    $title = $this->tree->tree_data[$this->itemID]['name'];
    $parentID = $this->tree->tree_data[$this->itemID]['parent_id'];

    $group = &$this->tree->getGroupObject($this->startItemID);
    if ($group->getChildType() == "EvaluationQuestion")
        $numberofchildren = $group->getNumberChildren();
    else
        $numberofchildren = $this->tree->getNumKidsKids($this->itemID);

    $group->delete();

    if ($group->isError)
        return EvalCommon::showErrorReport ($group,
            _("Fehler beim Löschen eines Block."));

    if ($group->getChildType() == "EvaluationQuestion"){
        if ($numberofchildren){
            $this->msg[$parentID] = "msg§" . sprintf(_("Der Fragenblock <b>%s</b> und alle darin enthaltenen Fragen (insgesamt %s) wurden gelöscht. "),$title,$numberofchildren);
        } else {
            $this->msg[$parentID] = "msg§" . sprintf(_("Der Fragenblock <b>%s</b> wurde gelöscht. "), $title);
        }
    } else {
        if ($numberofchildren){
            $this->msg[$parentID] = "msg§" . sprintf(_("Der Gruppierungsblock <b>%s</b> und alle Unterblöcke (insgesamt %s) wurden gelöscht. "),$title,$numberofchildren);
        } else {
            $this->msg[$parentID] = "msg§" . sprintf(_("Der Gruppierungsblock <b>%s</b> wurde gelöscht. "), $title);
        }
    }

    $this->changed = true;

    $this->startItemID = $parentID;
    $this->itemID = $parentID;

    return true;
}

/**
 * Creates a new Group and adds it to the tree
 *
 * @access   public
 * @return   boolean  true (reinits the tree)
 */
function execCommandAddGroup(){


    $group = new EvaluationGroup();
    $group->setTitle( NEW_ARRANGMENT_BLOCK_TITLE);
    $group->setText("");

    $mode = $this->getInstance($this->itemID);

    if ($mode == ROOT_BLOCK){
        $this->tree->eval->addChild($group);
        $this->tree->eval->save();
        if ($this->tree->eval->isError)
            return EvalCommon::showErrorReport ($this->tree->eval,
                _("Fehler beim Anlegen eines neuen Blocks."));
        $this->msg[$this->itemID] = "msg§"
            . _("Ein neuer Gruppierungsblock wurde angelegt.");
    }// group
    elseif ($mode == ARRANGMENT_BLOCK){
        $parentgroup = &$this->tree->getGroupObject($this->itemID);
        $parentgroup->addChild($group);
        $parentgroup->save();
        if ($parentgroup->isError)
            return EvalCommon::showErrorReport ($parentgroup,
                _("Fehler beim Anlegen eines neuen Blocks."));
        $this->msg[$this->itemID] = "msg§"
            . _("Ein neuer Gruppierungsblock wurde angelegt.");
    }

    $this->execCommandUpdateItem();

    return true;
}

/**
 * adds a questions-group
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandAddQGroup(){


    $group = new EvaluationGroup();
    $group->setTitle( NEW_QUESTION_BLOCK_BLOCK_TITLE);
    $group->setText("");
    $group->setChildType("EvaluationQuestion");
    $group->setTemplateID(Request::option("templateID"));
    $template = new EvaluationQuestion (Request::option("templateID"),
        NULL, EVAL_LOAD_FIRST_CHILDREN);

    $mode = $this->getInstance($this->itemID);

    if ($mode == ROOT_BLOCK){
        $this->tree->eval->addChild($group);
        $this->tree->eval->save();
        if ($this->tree->eval->isError)
            return EvalCommon::showErrorReport ($this->tree->eval,
                _("Fehler beim Anlegen eines neuen Blocks."));
        $this->msg[$this->itemID] = "msg§"
            . _("Ein neuer Fragenblock wurde angelegt.");
    }// group
    elseif ($mode == ARRANGMENT_BLOCK){
        $parentgroup =& $this->tree->getGroupObject($this->itemID);
        $parentgroup->addChild($group);
        $parentgroup->save();
        if ($parentgroup->isError)
            return EvalCommon::showErrorReport ($parentgroup,
                _("Fehler beim Anlegen eines neuen Blocks."));
        if (Request::option("templateID") != "")
            $this->msg[$this->itemID] = "msg§"
                . sprintf(_("Ein neuer Fragenblock mit der Antwortenvorlage <b>%s</b> wurde angelegt."),
                    htmlReady ($template->getText()));
        else
            $this->msg[$this->itemID] = "msg§"
                . sprintf(_("Ein neuer Fragenblock mit keiner Antwortenvorlage wurde angelegt."),
                    1);
    }
    $this->execCommandUpdateItem();

    return true;
}

/**
 * Updates the templateID of a group
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandChangeTemplate(){


    $this->execCommandUpdateItem();

    $group = &$this->tree->getGroupObject($this->itemID);
    $group->setTemplateID(Request::option("templateID"));
    $group->save();

    if ($group->isError)
        return EvalCommon::showErrorReport ($group,
            _("Fehler beim Zuordnen eines Templates."));

    $templateID = $group->getTemplateID();
    if ($templateID){

        $template = new EvaluationQuestion($templateID);
        $templateTitle = htmlReady ($template->getText());

    } else
        $templateTitle = NO_TEMPLATE_GROUP;

    $this->msg[$this->itemID] = "msg§"
            . sprintf(_("Die Vorlage <b>%s</b> wurde dem Fragenblock zugeordnet."),
                $templateTitle);

    return true;
}

/**
 * Update the Question content
 *
 * @access  private
 * @param   boolean  $no_delete  YES/NO (optional)
 * @return  string   the udpatemessage
 */
function execCommandUpdateQuestions ( $no_delete = false ){

    $questions = Request::getArray('questions');
    $deleteQuestions = Request::getArray('DeleteQuestions');

    // remove any empty questions
    $deletecount = 0;

    $qgroup = &$this->tree->getGroupObject($this->itemID);
    $questionsDB = $qgroup->getChildren();
    $cmd = Request::optionArray('cmd');
    if (!empty($cmd))
        if (key($cmd) == "UpdateItem")
            $delete_empty_questions = 1;

    for( $i=0; $i<count($questions); $i++ ) {

        if (!isset($deleteQuestions[$i])){
            $question = new EvaluationQuestion($questions[$i]['questionID'], NULL,
            EVAL_LOAD_FIRST_CHILDREN);

            // remove any empty questions
            if( (empty( $questions[$i]['text'] )) && $delete_empty_questions ) {

                $question->delete();
                $deletecount++;

                // upadate the questiontext to the db
            } else {

                $question->setText($questions[$i]['text']);
                $question->save();
            }
        }
    }
    $msg = NULL;
    if ($deletecount == 1)
        $msg = _("Es wurde eine leere Frage entfernt.");
    elseif ($deletecount > 1)
        $msg = sprintf(_("Es wurden %s leere Fragen entfernt."),$deletecount);

    return $msg;
}

/**
 * Adds Questions
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandAddQuestions(){

    $addquestions = Request::get('newQuestionFields');

    $qgroup = &$this->tree->getGroupObject($this->itemID);
    $templateID = $qgroup->getTemplateID();

    for ($i=1;$i<=$addquestions;$i++){
        $template = new EvaluationQuestion ($templateID, NULL, EVAL_LOAD_FIRST_CHILDREN);
        $newquestion = $template->duplicate ();
        $newquestion->setText("");
        $qgroup->addChild ($newquestion);
        $qgroup->save();
        if ($qgroup->isError)
            return EvalCommon::showErrorReport ($this->tree->eval,
                _("Fehler beim Anlegen neuer Fragen."));
    }

    if ($addquestions == "1")
        $this->msg[$this->itemID] = "msg§"
            . _("Es wurde eine neue Frage hinzugefügt.");
    else
        $this->msg[$this->itemID] = "msg§"
            . sprintf(_("Es wurden %s neue Fragen hinzugefügt."),$addquestions);

    $this->execCommandUpdateItem( NO );

    return true;
}

/**
 * deletes questions
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandDeleteQuestions(){

    $questions = Request::getArray('questions');
    $deleteQuestions = Request::getArray('DeleteQuestions');

    $deletecount = 0;
    for( $i=0; $i<count($questions); $i++ ) {

        $question = new EvaluationQuestion($questions[$i]['questionID'], NULL,
            EVAL_LOAD_ALL_CHILDREN);

        // remove any empty questions
        if( $deleteQuestions[$i] ) {
            $question->delete();
            $deletecount++;
        }
    }

    if ($deletecount == "1")
        $this->msg[$this->itemID] = "msg§"
            . _("Es wurde eine Frage gelöscht.");
    elseif ($deletecount > 1)
        $this->msg[$this->itemID] = "msg§"
            . sprintf(_("Es wurden %s Fragen gelöscht."),$deletecount);
    else
        $this->msg[$this->itemID] = "msg§"
            . _("Es wurde keine Frage gelöscht.");

    $this->execCommandUpdateItem();

    return true;
}

/**
 * creates an info-message and updates the item
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandQuestionAnswersCreate(){

    $this->execCommandUpdateItem();

     // extract the questionID from the command
     foreach( $_REQUEST as $key => $value ) {
         if( preg_match( "/template_(.*)_button?/", $key, $command ) )
             break;
         }
      if ( preg_match( "/(.*)_#(.*)/", $command[1], $command_parts ) )
        $questionID = $command_parts[2];

    $question = new EvaluationQuestion($questionID);
    $questiontitle = htmlReady($question->getText());

    $this->msg[$this->itemID] = "msg§"
#           . sprintf(_("Sie können nun der Frage <b>%s</b> im rechten Bereich Antworten zuweisen.")
#               , $questiontitle)
#           . "<br>"
            . _("Veränderungen wurden gespeichert.");

    return true;
}

/**
 * creates an confirm-message if answers were created
 *
 * @access   private
 * @return   boolean  false
 */
function execCommandQuestionAnswersCreated(){

    $id = $this->itemID;

    $question = new EvaluationQuestion(Request::get("questionID"));
    $title = htmlready ($question->getTitle());

    $this->msg[$this->itemID] = "msg§"
        . sprintf(_("Der Frage <b>%s</b> wurden Antwortenmöglichkeiten zugewiesen."),$title);

    $this->changed = true;

    return false;
}

/**
 * Moves a Questions
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandMoveQuestionUp(){

    $this->execCommandUpdateItem();

    foreach( $_REQUEST as $key => $value ) {
        if( preg_match( "/cmd_(.*)_#(.*)_§(.*)_button(_x)?/", $key, $command ) )
            break;
    }

    $questionID = $command[2];
    $oldposition = $command[3];

    $this->swapPosition($this->itemID, $questionID, $oldposition,
        "up");

    if ($oldposition == 0)
        $this->msg[$this->itemID] = "msg§"
            . _("Die Frage wurde von Position 1 an die letzte Stelle verschoben.");
    else
        $this->msg[$this->itemID] = "msg§"
            . sprintf(_("Die Frage wurde von Position %s nach oben verschoben."), $oldposition+1);

    $this->msg[$this->itemID] .= "<br>". _("Veränderungen wurden gespeichert.");
    return true;
}

/**
 * Moves a Questions
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandMoveQuestionDown(){

    $this->execCommandUpdateItem();

    foreach( $_REQUEST as $key => $value ) {
        if( preg_match( "/cmd_(.*)_#(.*)_§(.*)_button(_x)?/", $key, $command ) )
            break;
    }

    $questionID = $command[2];
    $oldposition = $command[3];

    $this->swapPosition($this->itemID, $questionID, $oldposition,
        "down");

    if ($oldposition == $numberchild-1)
    $this->msg[$this->itemID] = "msg§"
            . sprintf(_("Die Frage wurde von Position %s an die erste Stelle verschoben.")
                , $oldposition+1);
    else
    $this->msg[$this->itemID] = "msg§"
            . sprintf(_("Die Frage wurde von Position %s nach oben verschoben."), $oldposition+1);

    $this->msg[$this->itemID] .= "<br>". _("Veränderungen wurden gespeichert.");
    return true;
}

/**
 * Moves a Group up or down
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandMove(){

    $direction = Request::option('direction');

    $group = &$this->tree->getGroupObject(Request::option('groupID'));
    $oldposition = $group->getPosition();

    $this->swapPosition($this->itemID, $group->objectID, $oldposition, $direction);

    $this->msg[$this->itemID] = "msg§ ";
    if (($this->itemID != ROOT_BLOCK)
        && ($group->getChildType() == "EvaluationQuestion"))
        $this->msg[$this->itemID] .= _("Fragenblock");
    else
        $this->msg[$this->itemID] .= _("Gruppierungsblock");

    if (($oldposition == 0) && ($direction == "up"))
            $this->msg[$this->itemID] .=
                _(" wurde von Position 1 an die letzte Stelle verschoben.");
    elseif (($oldposition == $group->getNumberChildren()-1)
        && ($direction == "down"))
        $this->msg[$this->itemID] .=
            sprintf(_(" wurde von Position %s an die erste Stelle verschoben.")
                , $oldposition+1);
    else
        $this->msg[$this->itemID] .= (($direction == "up")
        ? sprintf(_(" wurde von Position %s nach oben verschoben."), $oldposition+1)
        : sprintf(_(" wurde von Position %s nach unten verschoben."), $oldposition+1));

    $this->changed = true;

    return true;
}

/**
 * Moves a Group from one parent to another
 *
 * @access   private
 * @return   boolean  true (reinits the tree)
 */
function execCommandMoveGroup(){


    $moveGroupeID = Request::option('moveGroupeID');

    if (!$this->moveItemID){
        $this->msg[$this->itemID] = "msg§"
            . _("Fehler beim Verschieben eines Blocks. Es wurde kein Block zum verschieben ausgewählt.");
        return false;
    }

    $mode = $this->getInstance ($this->itemID);

    if (!$mode){
        $this->msg[$this->itemID] = "msg§"
            . _("Fehler beim Verschieben eines Blocks. Der Zielblock besitzt keinen Typ.");
        return false;
    }

    $move_mode = $this->getInstance ($this->moveItemID);

    if (!$move_mode){
        $this->msg[$this->itemID] = "msg§"
            . _("Fehler beim Verschieben eines Blocks. Der Zielblock besitzt keinen Typ.");
        return false;
    }

    $move_group =&$this->tree->getGroupObject($this->moveItemID);
    $move_group_title = htmlready ($move_group->getTitle ());
    $oldparentID = $move_group->getParentID ();

    switch ($mode){

        case ROOT_BLOCK:

            if ($children = $this->tree->eval->getChildren()){
                if ($this->getInstance( $children[0]->getObjectID()) != $move_mode){
                    $this->msg[$this->itemID] = "msg§"
                        . _("Fehler beim Verschieben eines Blocks. Der ausgewählte Block und der Zielblock besitzen verschiedene Typen.");
                    return false;
                }
            }

            $newgroup = $move_group->duplicate ();

            $this->tree->eval->addChild ($newgroup);
            $this->tree->eval->save ();

            if (($oldparentID == $this->evalID) || $oldparentID == "root"){

                $grouptodelete = $this->tree->eval->getChild ($move_group->getObjectID());
                $grouptodelete->delete ();
                $this->tree->eval->save ();
                if ($this->tree->eval->isError)
                    return EvalCommon::showErrorReport ($newgroup,
                        _("Fehler beim Verschieben eines Blocks."));
            } else {

                $oldparentgroup = &$this->tree->getGroupObject($oldparentID);
                $grouptodelete = $oldparentgroup->getChild ($move_group->getObjectID());
                $grouptodelete->delete ();
                $oldparentgroup->save ();
            }

            if ($this->tree->eval->isError)
                return EvalCommon::showErrorReport ($group,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($move_group->isError)
                return EvalCommon::showErrorReport ($move_group,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($newgroup->isError)
                return EvalCommon::showErrorReport ($newgroup,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($grouptodelete->isError)
                return EvalCommon::showErrorReport ($newgroup,
                    _("Fehler beim Verschieben eines Blocks."));

            $this->msg[$this->itemID] = "msg§"
                . sprintf(_("Der Block <b>%s</b> wurde in die Hauptebene verschoben."),
                    $move_group_title);
            break;

        case ARRANGMENT_BLOCK:

            $group = &$this->tree->getGroupObject($this->itemID);
            if ($children = $group->getChildren()){
                if ($this->getInstance( $children[0]->getObjectID()) != $move_mode){
                    $this->msg[$this->itemID] = "msg§"
                        . _("Fehler beim Verschieben eines Blocks. Der ausgewählte Block und der Zielblock besitzen verschiedene Typen.");
                    return false;
                }
            }
            if ($oldparentID == $this->evalID){
                $grouptodelete = $this->tree->eval->getChild ($move_group->getObjectID());
                $grouptodelete->delete ();
                $this->tree->eval->save ();
                if ($this->tree->eval->isError)
                    return EvalCommon::showErrorReport ($newgroup,
                        _("Fehler beim Verschieben eines Blocks."));
            } else {

                $oldparentgroup = &$this->tree->getGroupObject($oldparentID);
                $grouptodelete = $oldparentgroup->getChild ($move_group->getObjectID());
                $grouptodelete->delete ();
                $oldparentgroup->save ();
                if ($oldparentgroup->isError)
                    return EvalCommon::showErrorReport ($newgroup,
                        _("Fehler beim Verschieben eines Blocks."));
            }
            $newgroup = $move_group->duplicate ();

            $group->addChild ($newgroup);
            $group->save ();



            if ($group->isError)
                return EvalCommon::showErrorReport ($group,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($move_group->isError)
                return EvalCommon::showErrorReport ($move_group,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($newgroup->isError)
                return EvalCommon::showErrorReport ($newgroup,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($grouptodelete->isError)
                return EvalCommon::showErrorReport ($newgroup,
                    _("Fehler beim Verschieben eines Blocks."));


            $this->msg[$this->itemID] = "msg§"
                . sprintf(_("Der Block <b>%s</b> wurde in diesen Gruppierungsblock verschoben."),
                    $move_group_title);
            break;

        case QUESTION_BLOCK:

            $group = &$this->tree->getGroupObject($this->itemID);

            if ($children = $group->getChildren()){
                if ($this->getInstance( $children[0]->getObjectID()) != $move_mode){
                    $this->msg[$this->itemID] = "msg§"
                        . _("Fehler beim Verschieben eines Blocks. Der ausgewählte Block und der Zielblock besitzen verschiedene Typen.");
                    return false;
                }
            }

            $oldparentID = $move_group->getParentID ();
            if ($oldparentID == ROOT_BLOCK){

                $this->msg[$this->itemID] = "msg§"
                        . _("Fehler beim Verschieben eines Blocks. Ein Fragenblock kann nicht auf die oberste Ebene verschoben werden.");
                    return false;
            } elseif ($oldparentID == $this->evalID){

                $this->msg[$this->itemID] = "msg§"
                        . _("Fehler beim Verschieben eines Blocks. Ein Fragenblock kann nicht auf die oberste Ebene verschoben werden.");
                    return false;
            } else {

                $oldparent = &$this->tree->getGroupObject($oldparentID);
            }

            $newgroup = $move_group->duplicate ();

            $group->addChild ($newgroup);
            $group->save ();

            $grouptodelete = $oldparent->getChild ($move_group->getObjectID());
            $grouptodelete->delete ();
            $oldparent->save ();


            if ($group->isError)
                return EvalCommon::showErrorReport ($group,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($move_group->isError)
                return EvalCommon::showErrorReport ($move_group,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($newgroup->isError)
                return EvalCommon::showErrorReport ($newgroup,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($grouptodelete->isError)
                return EvalCommon::showErrorReport ($newgroup,
                    _("Fehler beim Verschieben eines Blocks."));
            if ($oldparent->isError)
                return EvalCommon::showErrorReport ($oldparent,
                    _("Fehler beim Verschieben eines Blocks."));

            $this->msg[$this->itemID] = "msg§"
                . sprintf(_("Der Block <b>%s</b> wurde in diesen Fragenblock verschoben."),
                    $move_group_title);

            break;
    }

    $this->moveItemID = NULL;

    $this->changed = true;

    return true;
}

# ##################################################### end: command functions #


################################################################################
#                                                                              #
# HTML functions                                                               #
#                                                                              #
################################################################################

/**
* creates the html for the create new group options
*
* @access  private
*
* @param   string  $show  the blocktyp to display
* @return  string         the buttons (html)
*/
function createButtonbar ( $show = ARRANGMENT_BLOCK ){

    $infotext = _("Sie können ...") . "\n";

    $table = new HTML ("table");
    $table->addAttr ("width","100%");
    $table->addAttr ("class","blank");
    $table->addAttr ("border","0");
    $table->addAttr ("cellpadding","6");
    $table->addAttr ("cellspacing","0");
    $table->addAttr ("div","left");

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("class","steelgrau");
    $td->addAttr ("align","center");

    $seperator = "&nbsp;|&nbsp;&nbsp;";

    // the update-button
    $buttons = "&nbsp;"
        . Button::create(_('Übernehmen'),
                'cmd[UpdateItem]',
                ['title' => _('Die Veränderungen innerhalb des Blockes speichern.')]);

    $infotext .= "\n"
        . _("- die Veränderungen dieses Blocks speichern.");

    // the new group-button
   if ($show == "both" || $show == ARRANGMENT_BLOCK || $show == ROOT_BLOCK){
    $buttons .= $seperator
        . Button::create(_('Erstellen'),
                'cmd[AddGroup]',
                [ 'title' => _('Einen neuen Gruppierungsblock erstellen.')]);
    $infotext .= "\n"
        . _("- einen neuen Gruppierungsblock innerhalb dieses Blockes erstellen, in welchem Sie weitere Gruppierungs- oder Fragenblöcke anlegen können.");
   }

    // the new question-group-button
   if ($show == "both" || $show == QUESTION_BLOCK){

    $buttons .=  $seperator
        . $this->createTemplateSelection()
        . Button::create(_('Erstellen'),
                'cmd[AddQGroup]',
                [ 'title' => _('Einen neuen Fragenblock mit der ausgewählten Antwortenvorlage erstellen.')]);
    $infotext .= "\n"
        . _("- einen neuen Fragenblock innherhalb dieses Blockes erstellen. Geben Sie dazu bitte eine Antwortenvorlage an, welche für alle Fragen des neuen Fragenblockes verwendet wird.");
   }

    // the move-button
   if ($this->itemID != ROOT_BLOCK && !$this->moveItemID){

    $a = new HTML ("a");
    $a->addAttr ("href",
        URLHelper::getLink($this->getSelf ("&moveItemID=" . $this->itemID)));

    $img = new HTMLempty ("img");
    $img->addAttr ("border","0");
    $img->addAttr ("style","vertical-align:middle;");
    $img->addAttr ("src", EVAL_PIC_MOVE_BUTTON);
    $img->addAttr ("style","vertical-align:middle;");
    $img->addString (tooltip (_("Diesen Block verschieben.")));

    $a->addContent ($img);

    $button = new HTMLempty ("input");
    $button->addAttr ("type", "image");
    $button->addAttr ("name", "&moveItemID=" . $this->itemID);
    $button->addAttr ("style", "vertical-align:middle;");
    $button->addAttr ("border", "0");
    $button->addAttr ("src", EVAL_PIC_MOVE_BUTTON);
    $button->addString (Tooltip (_("Diesen Block verschieben.")));

    $buttons .= $seperator
        . Button::create(_('verschieben'),
                'create_moveItemID',
                ['title' => _('Diesen Block verschieben.')]);
#       . $a->createContent ();
    $infotext .= "\n"
        . _("- diesen Block zum Verschieben markieren.");

    $movebutton = 1;
   }


    // the delete-button
   if ($this->itemID != ROOT_BLOCK){
    $button = new HTMLempty ("input");
    $button->addAttr ("type", "image");
    $button->addAttr ("name", "cmd[AssertDeleteItem]");
    $button->addAttr ("style", "vertical-align:middle;");
    $button->addAttr ("border", "0");
    $button->addAttr ("src", EVAL_PIC_DELETE_GROUP);
    $button->addString (Tooltip (_("Diesen Block und alle seine Unterblöcke löschen.")));

    $buttons .= ($movebutton)
        ? "&nbsp;"
        : $seperator;
    $buttons .=  Button::create(_('Löschen'),
            'cmd[AssertDeleteItem]',
            ['title' => _('Diesen Block (und alle seine Unterblöcke) löschen..')]);
#   $buttons .= $button->createContent ();

    $infotext .= "\n"
        . _("- diesen Block und seine Unterblöcke löschen.");
   }

    // the abort-button
    $child = $this->tree->eval->getNextChild();
    $number_of_childs = $this->tree->eval->getNumberChildren();
   if ($number_of_childs == 1 &&
        $this->itemID == ROOT_BLOCK &&
        $this->tree->eval->getTitle() == NEW_EVALUATION_TITLE &&
        $this->tree->eval->getText() == "" &&
        $child &&
        $child->getTitle() == FIRST_ARRANGMENT_BLOCK_TITLE &&
        $child->getChildren() == NULL &&
        $child->getText == ""){

        $a_content = LinkButton::createCancel(_('Abbrechen'),
                URLHelper::getURL(EVAL_FILE_ADMIN. "?evalID=").$this->tree->eval->getObjectID()."&abort_creation_button=1",
                ['title' => _("Erstellung einer Evaluation abbrechen")]);

        $buttons .= $seperator
            . $a_content;
    $infotext .= "\n"
        . _("Die Erstellung dieser Evaluation abbrechen.");
   }

    $td->addHTMLContent (
        $this->createImage (EVAL_PIC_HELP,$infotext));
    $td->addHTMLContent ($buttons);
    $tr->addContent ($td);
    $table->addContent ($tr);


    return $table->createContent ();
}

/**
* creates the html for the create new group options
*
* @access   private
* @param    string $show
* @return   string the html
*/
function createFormNew($show = ARRANGMENT_BLOCK){

    $table = new HTML ("table");
    $table->addAttr ("width","100%");
    $table->addAttr ("class","blank");
    $table->addAttr ("border","0");
    $table->addAttr ("cellpadding","6");
    $table->addAttr ("cellspacing","0");
    $table->addAttr ("div","left");

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("class","blank");
    $td->addAttr ("align","center");
    $td->addContent (new HTMLempty ("br"));

#   $tr->addContent ($td);
#   $table->addContent ($tr);

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("class","content_body");
#   $td->addAttr ("class","steelgrau");
    $td->addAttr ("align","center");

    $img = new HTMLempty ("img");
    $img->addAttr ("src", Assets::image_path("blank.gif"));
    $img->addAttr ("width","30");
    $img->addAttr ("height","1");
    $img->addAttr ("alt","");

#   $td->addContent ($img);
#   $td->addContent (new HTMLempty ("br"));


    $group_selection = _("Gruppierungsblock")
        . "&nbsp;"
        . Button::create(_('Erstellen'),
                'cmd[AddGroup]',
                ['title' => _('Einen neuen Gruppierungsblock erstellen')]);

    $qgroup_selection = _("Fragenblock mit")
        . "&nbsp;"
        . $this->createTemplateSelection()
        . Button::create(_('Erstellen'),
                'cmd[AddQGroup]',
                ['title' => _('Einen neuen Fragenblock erstellen')]);

    $seperator = "&nbsp;|&nbsp;";

    switch ($show){
        case ARRANGMENT_BLOCK:
            $td->addHTMLContent ($group_selection);
            break;
        case QUESTION_BLOCK:
            $td->addHTMLContent ($qgroup_selection);
            break;
        case "both":
            $td->addHTMLContent (
                  $group_selection
                . $seperator
                . $qgroup_selection);
            break;
    }

    // abort-button
    $child = $this->tree->eval->getNextChild();
    $number_of_childs = $this->tree->eval->getNumberChildren();
    if ($number_of_childs == 1 &&
        $this->itemID == ROOT_BLOCK &&
        $this->tree->eval->getTitle() == _("Neue Evaluation") &&
        $this->tree->eval->getText() == "" &&
        $child &&
        $child->getTitle() == _("Erster Gruppierungsblock") &&
        $child->getChildren() == NULL &&
        $child->getText == ""){



        $cancel = $seperator ."&nbsp;";

        $a_content = LinkButton::createCancel(_('Abbrechen'),
                URLHelper::getURL(EVAL_FILE_ADMIN . "?evalID=".$this->tree->eval->getObjectID()."&abort_creation_button=1"),
                ['title' => _("Erstellung einer Evaluation abbrechen")]);

        $cancel .= $a_content;

        $td->addHTMLContent ($cancel);

    }

    $tr->addContent ($td);
    $table->addContent ($tr);


    return $table->createContent ();
}

/**
* creates the html for the title and text input
*
* @access   private
* @param    string  $mode
* @return   string the html
*/
function createTitleInput($mode = ROOT_BLOCK){

    switch ($mode) {

        case ROOT_BLOCK:
            $title_label = _("Titel der Evaluation");
            $title       = htmlReady ($this->tree->eval->getTitle());
            $text_label  = _("Zusätzlicher Text");
            $text        = wysiwygReady ($this->tree->eval->getText());
            break;

        case ARRANGMENT_BLOCK:
            $title_label = _("Titel des Gruppierungsblocks");
            $group       =  &$this->tree->getGroupObject($this->itemID);
            $title       = htmlReady ($group->getTitle());
            $text_label  = _("Zusätzlicher Text");
            $text        = wysiwygReady ($group->getText());
            break;

        case QUESTION_BLOCK:
            $title_label = _("Titel des Fragenblocks");
            $title_info  = _("Die Angabe des Titels ist bei einem Fragenblock optional.");
            $group       =  &$this->tree->getGroupObject($this->itemID);
            $title       = htmlReady ($group->getTitle());
            $text_label  = _("Zusätzlicher Text");
            $text        = wysiwygReady ($group->getText());
            break;
    }
    $text_info = _("Die Angabe des zusätzlichen Textes ist optional.");

    $table = new HTML ("table");
    $table->addAttr ("width","98%");
    $table->addAttr ("border","0");
    $table->addAttr ("cellpadding","2");
    $table->addAttr ("cellpadding","0");

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr('colspan', '2');
    $label = new HTML('label');
    $label->addContent ($title_label);
    if ($mode == QUESTION_BLOCK)
        $label->addHTMLContent ($this->createImage(EVAL_PIC_HELP,$title_info));


    $input = new HTMLempty ("input");
    $input->addAttr ("type","text");
    $input->addAttr ("name","title");
    $input->addString ("value=\"".$title."\"");
    $input->addAttr ("size","60");
    $input->addAttr ("style","vertical-align:middle; width: 100%;");

    $label->addContent($input);

    $td->addContent($label);
    $tr->addContent ($td);
    $table->addContent ($tr);

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr('colspan', '2');

    $label = new HTML('label');
    $label->addContent ($text_label);
    $label->addHTMLContent ($this->createImage(EVAL_PIC_HELP, $text_info));

    $textarea = "<br><textarea class=\"add_toolbar wysiwyg\" name=\"text\" rows=\"4\" "
        . "style=\"vertical-align:top; width: 100%;\">";
    $textarea .=($text)
            ? $text
            : "";
    $textarea .= "</textarea>";

    $label->addHTMLContent($textarea);
    $label->setTextareaCheck();

    $td->addContent($label);
    $tr->addContent ($td);
    $table->addContent ($tr);

    return $table->createContent ();
}

/**
* creates the html for the update button
*
* @access  private
* @param   string  $mode
* @return  string  the html
*/
function createUpdateButton ( $mode = NULL ){

    $button = "<table width=\"100%\" border=\"0\" cellpadding=\"2\" "
        . "cellspacing=\"2\">\n"
        ." <tr>\n"
        . "  <td align=center>\n"
//      . "   <input type=hidden name=\"cmd\" value=\"UpdateItem\">\n"
        . Button::create(_('Übernehmen'),
                'cmd[UpdateItem]',
                ['title' => _('Änderungen übernehmen.')]);

    if($mode == NULL){
        $button .= "&nbsp;&nbsp;|&nbsp;&nbsp;"._("Diesen Block")."&nbsp;"
            . Button::create(_('Löschen'),
                    'cmd[AssertDeleteItem]',
                    ['title', _('Diesen Block und alle seine Unterblöcke löschen.')]);
    }

    $button .= "  </td>\n"
        . " </tr>\n"
//      . " </form></tr>\n"
        . "</table>\n";
    return $button;
}

/**
* creates the html for the global features-input
*
* @access   private
* @return   string the html
*/
function createGlobalFeatures (){

    $table = new HTML ("table");
    $table->addAttr ("width","99%");
    $table->addAttr ("border","0");
    $table->addAttr ("cellpadding","2");
    $table->addAttr ("cellspacing","2");

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("class","table_row_odd");
    $td->addAttr ("colspan","2");

    $b = new HTML ("b");
    $b->addContent (_("Globale Eigenschaften"));

    $td->addContent ($b);
    $tr->addContent ($td);
    $table->addContent ($tr);

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr('colspan', '2');

    $div = new HTML('div');
    $div->addContent (_("Die Auswertung der Evaluation läuft"));

    $td->addContent($div);

    $section = new HTML('section');
    $section->addAttr('class', 'hgroup');

    $l1 = new HTML('label');
    $input = new HTMLempty ("input");
    $input->addAttr ("type","radio");
    $input->addAttr ("value","1");
    $input->addAttr ("name","anonymous");
    if ($this->tree->eval->isAnonymous())
        $input->addAttr ("checked","checked");
    $l1->addContent($input);
    $l1->addContent(_("anonym"));

    $l2 = new HTML('label');
    $input2 = new HTMLempty ("input");
    $input2->addAttr ("type","radio");
    $input2->addAttr ("value","0");
    $input2->addAttr ("name","anonymous");
    if (!$this->tree->eval->isAnonymous())
        $input2->addAttr ("checked","checked");
    $l2->addContent($input2);
    $l2->addContent(_("personalisiert"));

    $section->addContent($l1);
    $section->addContent($l2);

    $td->addContent($section);
    $tr->addContent ($td);
    $table->addContent ($tr);

    return $table->createContent ();
}

/**
* creates the html for the global features-input
*
* @access   private
* @return   string the html
*/
function createQuestionFeatures(){

    $group      = &$this->tree->getGroupObject($this->itemID);
    $templateID = $group->getTemplateID();

    if ($templateID){
        $template = new EvaluationQuestion($templateID);
        $templateTitle = htmlReady ($template->getText());
    } else
        $templateTitle = NO_TEMPLATE_GROUP;//_("keine Vorlage");

    if ( $templateTitle == "" )
                $templateTitle = NO_TEMPLATE;

    $table = new HTML ("table");
    $table->addAttr ("border","0");
    $table->addAttr ("align", "center");
    $table->addAttr ("cellspacing", "0");
    $table->addAttr ("cellpadding", "0");
    $table->addAttr ("width", "98%");
//    $table->addAttr ("style", "border:5px solid white;");

    $tr = new HTML ("tr");

    $td = new HTMl ("td");
    $td->addAttr ("class","table_row_odd");
    $td->addAttr ("colspan","2");

    $b = new HTML ("b");
    $b->addContent (_("Eigenschaften"));
    $b->addContent (":");

    $td->addContent ($b);
    $tr->addContent ($td);
    $table->addContent ($tr);

    $tr = new HTML ("tr");

    $td = new HTMl ("td");
    $td->addAttr ("style","border-bottom:0px dotted black;");
    $td->addContent (_("Die Fragen dieses Blocks müssen beantwortet werden (Pflichtfelder):"));

    $tr->addContent ($td);

    $td = new HTMl ("td");
    $td->addAttr ("style","border-bottom:0px dotted black;");

    $input = new HTMLempty ("input");
    $input->addAttr ("type","radio");
    $input->addAttr ("value","0");
    $input->addAttr ("name","mandatory");
    if (!$group->isMandatory()) $input->addAttr ("checked","checked");

    $td->addContent($input);
    $td->addContent(_("nein"));
    $td->addContent(new HTMLempty ("br"));

    $input = new HTMLempty ("input");
    $input->addAttr ("type","radio");
    $input->addAttr ("value","1");
    $input->addAttr ("name","mandatory");
    if ($group->isMandatory()) $input->addAttr ("checked","checked");

    $td->addContent($input);
    $td->addContent(_("ja"));

    $tr->addContent ($td);
    $table->addContent ($tr);

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("style","border-bottom:0px dotted black;");
    $td->addHTMLContent (sprintf(_("Diesem Fragenblock ist die Antwortenvorlage <b>%s</b> zugewiesen."),
                $templateTitle));
    $text = _("Das Zuweisen einer Antwortenvorlage ändert alle Antwortenmöglichkeiten der Fragen dieses Fragenblocks.");
    if ($templateTitle == NO_TEMPLATE_GROUP)
        $text .= " "._("Da dieser Fragenblock keine Antwortenvorlage benutzt, würde ein Zuweisen einer Antwortenvorlage zum Verlust aller eingegebenen Antworten führen.");

    $td->addHTMLContent ($this->createImage(EVAL_PIC_HELP,
        $text));

    $tr->addContent ($td);

    $td = new HTML ("td");
    $td->addAttr ("style","border-bottom:0px dotted black;");
    $td->addAttr ("nowrap","nowrap");
    $td->addHTMLContent ($this->createTemplateSelection($templateID));
    $td->addContent (" ");
    $td->addHTMLContent (Button::create(_('Zuweisen'),
            'cmd[ChangeTemplate]',
            ['title' => _('Eine andere Antwortenvorlage für diesen Fragenblock auswählen')]));
    $tr->addContent ($td);
    $table->addContent ($tr);

    return $table->createContent ();
}

/**
* creates the html for the question-input
*
* @access   private
* @return   string the html
*/
function createQuestionForm(){

    $qgroup     = &$this->tree->getGroupObject($this->itemID);
    $questions  = $qgroup->getChildren();
    $templateID = $qgroup->getTemplateID();

    $table = new HTML ("table");
    $table->addAttr ("border","0");
    $table->addAttr ("align", "center");
    $table->addAttr ("cellspacing", "0");
    $table->addAttr ("cellpadding", "2");
    $table->addAttr ("width", "98%");

    $tr = new HTML ("tr");

    $td = new HTML ("td");
    $td->addAttr ("align","center");

    $table2 = new HTML ("table");
    $table2->addAttr ("border","0");
    $table2->addAttr ("class", "blank");
    $table2->addAttr ("cellspacing", "0");
    $table2->addAttr ("cellpadding", "0");
    $table2->addAttr ("width", "100%");

    // captions
    $tr2 = new HTML ("tr");

    $showclass = "table_row_odd";

    $td2 = new HTML ("td");
    $td2->addAttr ("class",$showclass);
    $td2->addAttr ("align","center");
    $td2->addAttr ("width","15");

    $b = new HTML ("b");
    $b->addContent ("#");

    $td2->addContent ($b);
    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addAttr ("class",$showclass);

    $b = new HTML ("b");
    $b->addContent (_("Frage"));

    $td2->addContent ($b);
    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addAttr ("class",$showclass);

   if( count($questions) > 1 ){
    $b = new HTML ("b");
    $b->addContent (_("Position"));

    $td2->addContent ($b);

   } else {

    $td2->addContent ("");

   }

    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addAttr ("class",$showclass);

    $b = new HTML ("b");
    $b->addContent (_("Löschen"));

    $td2->addContent ($b);
    $tr2->addContent ($td2);

   // only if template is NO_TEMPLATE_GROUP
   if ($templateID == NULL){
    $td2 = new HTML ("td");
    $td2->addAttr ("class",$showclass);

    $b = new HTML ("b");
    $b->addContent (_("Antworten"));

    $td2->addContent ($b);
    $tr2->addContent ($td2);
   }

    $table2->addContent ($tr2);

    $i = 0;
   foreach ($questions as $question){
    $tr2 = new HTML ("tr");

    // brrr :)
    // extract the questionID from the command
    foreach( $_REQUEST as $key => $value ) {
        if( preg_match( "/template_(.*)_button?/", $key, $command ) )
        break;
    }
    if ( preg_match( "/(.*)_#(.*)/", $command[1], $command_parts ) )
        $questionID = $command_parts[2];
    else
        $questionID = Request::submitted('template_save2_button') ? "" : Request::get("template_id");

    if ($question->getObjectID() == $questionID)
        $tr2->addAttr ("class", "eval_highlight");
    else
        $tr2->addAttr ("class", ($i%2 == 1 ? "table_row_odd" : "table_row_even"));

    $td2 = new HTML ("td");
    $td2->addAttr ("align","center");

    $font = new HTML ("font");
    $font->addAttr ("size","-1");
    $font->addContent (($i+1).".");

    $td2->addContent ($font);
    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addAttr ("align","left");

    $input = new HTMLempty ("input");
    $input->addAttr ("type","tex");
    $input->addAttr ("size","70");
    $input->addAttr ("name","questions[$i][text]");
    $input->addAttr ("value", $question->getText() );
    $input->addAttr ("tabindex",3+$i);

    $td2->addContent ($input);
#   $td2->addHTMLContent ("POST: -".$question->getPosition()."-!");

    $input = new HTMLempty ("input");
    $input->addAttr ("type","hidden");
    $input->addAttr ("name","questions[$i][questionID]");
    $input->addAttr ("value", $question->getObjectID() );

    $td2->addContent ($input);

    $input = new HTMLempty ("input");
    $input->addAttr ("type","hidden");
    $input->addAttr ("name","questions[$i][position]");
    $input->addAttr ("value", $question->getPosition() );

    $td2->addContent ($input);

    $input = new HTMLempty ("input");
    $input->addAttr ("type","hidden");
    $input->addAttr ("name","questions[$i][counter]");
    $input->addAttr ("value", $question->getPosition() );

    $td2->addContent ($input);

    $tr2->addContent ($td2);

    // move-up/down arrows and counter
    if( count($questions) > 1 ) {

     $numberchildren = $qgroup->getNumberChildren();

     if ($question->getPosition() == 0)
        $tooltipup = _("Diese Frage mit der letzten Frage vertauschen.");
     else
        $tooltipup = _("Diese Frage eine Position nach oben verschieben.");

     if ($question->getPosition() == $numberchildren-1)
        $tooltipdown = _("Diese Frage mit der ersten Frage vertauschen.");
     else
        $tooltipdown = _("Diese Frage eine Position nach unten verschieben.");

     $td2 = new HTML ("td");
     $td2->addAttr ("align","center");

     $button = new HTMLempty ("input");
     $button->addAttr ("type", "image");
     $button->addAttr ("name", "cmd_MoveQuestionUp_#".$question->getObjectID()."_§".$question->getPosition()."_button");
     $button->addAttr ("style", "vertical-align:middle;");
     $button->addAttr ("border", "0");
     $button->addAttr ("src", EVAL_PIC_MOVE_UP);
     $button->addString (Tooltip ($tooltipup));

     $td2->addContent ($button);

     $button = new HTMLempty ("input");
     $button->addAttr ("type", "image");
     $button->addAttr ("name", "cmd_MoveQuestionDown_#".$question->getObjectID()."_§".$question->getPosition()."_button");
     $button->addAttr ("style", "vertical-align:middle;");
     $button->addAttr ("border", "0");
     $button->addAttr ("src", EVAL_PIC_MOVE_DOWN);
     $button->addString (Tooltip ($tooltipdown));

     $td2->addContent ($button);

    } else {

     $td2 = new HTML ("td");
     $td2->addAttr ("align","center");
     $td2->addContent (" ");
    }

    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addAttr ("align","center");

    $input = new HTMLempty ("input");
    $input->addAttr ("type","checkbox");
    $input->addAttr ("id","deleteCheckboxes");
    $input->addAttr ("name","DeleteQuestions[".$question->getPosition()."]");

    $td2->addContent ($input);
    $tr2->addContent ($td2);

    // if template is NO_TEMPLATE_GROUP
    if ($templateID == NULL) {

     // hat noch keine antworten
     if ($question->getChildren() == NULL){
        $image = EVAL_PIC_CREATE_ANSWERS;
        $text = _("Dieser Frage wurden noch keine Antwortenmöglichkeiten zugewiesen. Drücken Sie auf den Doppelfpeil, um dies jetzt zu tun.");
        $tooltip = tooltip (_("Dieser Frage Antwortenmöglichkeiten zuweisen."));
    } else {
        $image = EVAL_PIC_EDIT_ANSWERS;
        $text = _("Dieser Frage wurden bereits folgende Antwortenmöglichkeiten zugewiesen:")
            . " ";
        $tooltip = tooltip (_("Die zugewiesenen Antwortenmöglichkeiten bearbeiten."));
        $text .= "\n";
        while ($answer = $question->getNextChild()){
            $text .= "\"".$answer->getText()."\"\n ";
        }
        $text .= "";
    }

     $td2 = new HTML ("td");
     $td2->addAttr ("align","center");
     $td2->addAttr ("valign","middle");
     $td2->addHTMLContent (
        $this->createImage(EVAL_PIC_HELP, $text));

    $questionID = $question->getObjectID();

    $button = new HTMLempty ("input");
    $button->addAttr ("type", "image");
    $button->addAttr ("name", "template_create_question_answers_#".$questionID."_button");
    $button->addAttr ("style", "vertical-align:middle;");
    $button->addAttr ("border", "0");
    $button->addAttr ("src", $image);
    $button->addString ($tooltip);

     $td2->addContent ($button);


     $tr2->addContent ($td2);
    }

    $table2->addContent ($tr2);
    $i++;
   }

   if (sizeof($questions) == 0){

    $tr2 = new HTML ("tr");
    $td2->addAttr ("class","table_row_even");

    $td2 = new HTML ("td");
    $td2->addAttr ("align","center");
    $td2->addContent (" ");

    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addContent (_("Dieser Block besitzt keine Fragen."));

    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addContent (" ");

    $tr2->addContent ($td2);

    $td2 = new HTML ("td");
    $td2->addContent (" ");

    $tr2->addContent ($td2);
    $table2->addContent ($tr2);
   }

    $td->addContent ($table2);

    // the new questions und delete questions buttons
    $table2 = new HTML ("table");
    $table2->addAttr ("width","100%");
    $table2->addAttr ("border","0");
    $table2->addAttr ("class",
        ( $i%2 == 6 )
            ? "content_body"
            : "content_body");
    $table2->addAttr ("cellspacing", "0");
    $table2->addAttr ("cellpadding", "2");

    // buttons
    $tr2 = new HTML ("tr");

    $td2 = new HTML ("td");
    $td2->addAttr ("align","left");

    $select = new HTML ("select");
    $select->addAttr ("style", "vertical-align:middle;");
    $select->addAttr ("name", "newQuestionFields");
    $select->addAttr ("size", "1");

   for ( $i=1; $i<=10; $i++ ) {

        $option = new HTML ("option");
        $option->addAttr ("value",$i);
        $option->addContent ($i);

        $select->addContent ($option);
   }

    $td2->addContent ($select);
    $td2->addContent (_("Frage/en"));
    $td2->addContent (" ");
    $td2->addHTMLContent (
        Button::create(_('Hinzufügen'),
                'cmd[AddQuestions]',
                ['title' => _('Fragen hinzufügen')])
            );

    $tr2->addContent($td2);

    $td2 = new HTML ("td");
    $td2->addAttr ("align","right");

    $font = new HTML ("font");
    $font->addAttr ("size","-1");
    $font->addContent (_("markierte Fragen "));

    $td2->addContent ($font);
    $td2->addHTMLContent (
        Button::create(_('Löschen'),
                'cmd[DeleteQuestions]',
                ['title' => _('Markierte Fragen löschen')])
        );

    $tr2->addContent ($td2);
    $table2->addContent ($tr2);

    $td->addContent ($table2);
    $tr->addContent ($td);
    $table->addContent ($tr);

    return $table->createContent();
}
# ######################################################## end: HTML functions #


################################################################################
#                                                                              #
# additional HTML functions                                                    #
#                                                                              #
################################################################################

/**
* creates a link-image
*
* @access  private
* @param   string   $pic      the image
* @param   string   $alt      the alt-text (optional)
* @param   string   $value    the value (optional)
* @param   boolean  $tooltip  display as tooltip? (optional)
* @param   string   $args     additional options (optional)
* @param   boolean  $self     get self? (optional)
* @return  string             the image with a link (html)
*/
function createLinkImage( $pic,
                          $alt = "",
                          $value = "",
                          $tooltip = true,
                          $args = NULL,
                          $self = true){

    $a = new HTML ("a");
    $a->addAttr ("href",URLHelper::getLink($this->getSelf($value)));

    $img = new HTMLempty ("img");
    $img->addAttr ("src",$pic);
    $img->addAttr ("border","0");
    $img->addAttr ("style","vertical-align:middle;");
    if ($tooltip)
        $img->addString (tooltip($alt,TRUE,TRUE));
    else
        $img->addAttr ("alt",$alt);
    if ($args)
        $img->addString ($args);

    $a->addContent ($img);

    return $a->createContent ();
}


/**
* creates an image
*
* @access  private
* @param   string   $pic   the image
* @param   string   $alt   the alt-text (optional)
* @param   string   $args  additional options (optional)
* @return  string          the image (html)
*/
function createImage ( $pic,
                       $alt = "",
                       $args = NULL){

    if (!isset($args['alt'])) {
        $args['alt'] = $alt;
        $args['title'] = $alt;
        }

    $args['border'] = 0;
    $args['style'] = "vertical-align:middle;";

    $img = new HTMLempty ("img");
    $img->addString (tooltip($alt,TRUE,TRUE));
    $img->addAttr ("src",$pic);
    $img->addAttr ("border","0");
    $img->addAttr ("style","vertical-align:middle;");
    if (empty($args)) {
    $img->addAttr ("alt", $alt);
    $img->addAttr ("title", $alt);
    } else
    $img->addString($alt);
    if ($args);
        $img->addString ($args);

    return $img->createContent ();
}


/**
* creates an td with an image
*
* @access  private
* @param   string  $pic  the image
* @return  string        the image
*/
function createLevelOutputTD ( $pic = "forumleer.gif" )
{
    $td = new HTML ("td");
    $td->addAttr ("class","blank");
    $td->addAttr ("background", Assets::image_path($pic));

    $img = new HTMLempty ("img");
    $img->addAttr ("width","10");
    $img->addAttr ("height","20");
    $img->addAttr ("src", Assets::image_path($pic));

    $td->addContent ($img);

    return $td->createContent ();
}


/**
* creates the template selection
*
* @access  private
* @param   string  $selected  the entry to be preselected (optional)
* @return  string             the html
*/
function createTemplateSelection ( $selected = NULL ){
    global $user;

    $question_show          = new EvaluationQuestionDB();
    $arrayOfTemplateIDs     = $question_show->getTemplateID ($user->id);
    $arrayOfPolTemplates    = [];
    $arrayOfSkalaTemplates  = [];
    $arrayOfNormalTemplates = [];
    $arrayOfFreetextTemplates = [];

   if (is_array ($arrayOfTemplateIDs)){
    foreach($arrayOfTemplateIDs as $templateID){
        $question = new EvaluationQuestion ($templateID, NULL,
            EVAL_LOAD_FIRST_CHILDREN);
        $question->load();
        $questiontyp = $question->getType();

        $questiontext = $question->getText();

        if( $question->getParentID() == '0')
            $questiontext .= " " . EVAL_ROOT_TAG;


       switch( $questiontyp ) {

         case EVALQUESTION_TYPE_POL:
          array_push($arrayOfPolTemplates, [$question->getObjectID(),
            ($questiontext)]);
          break;

         case EVALQUESTION_TYPE_LIKERT:
          array_push($arrayOfSkalaTemplates, [$question->getObjectID(),
            ($questiontext)]);
          break;

         case EVALQUESTION_TYPE_MC:
          $answer = $question->getNextChild ();
          if ( $answer && $answer->isFreetext() )
            array_push($arrayOfFreetextTemplates, [
                $question->getObjectID(),
                ($questiontext)]);
          else
             array_push($arrayOfNormalTemplates, [
                $question->getObjectID(),
                ($questiontext)]);
          break;
        }
    }

   } // End:  if (is_array ($arrayOfTemplateIDs))


    $select = new HTML ("select");
    $select->addAttr ("name","templateID");
    $select->addAttr ("style","vertical-align:middle; max-width: 250px;");

    $option = new HTML ("option");
    $option->addAttr ("value","");
    $option->addContent (NO_TEMPLATE_GROUP);

    $select->addContent ($option);


    if ( !empty($arrayOfPolTemplates) && is_array($arrayOfPolTemplates) ){

        $optgroup = new HTML ("optgroup");
        $optgroup->addAttr ("label",_("Polskalen:"));

        foreach ($arrayOfPolTemplates as $template){
            $option = new HTML ("option");
            $option->addAttr ("value",$template[0]);
            if ($template[0] == $selected)
                $option->addAttr ("selected","selected");
            $option->addHTMLContent ($template[1]);
            $optgroup->addContent ($option);
        }

        $select->addContent ($optgroup);

    }


    if ( !empty($arrayOfSkalaTemplates) && is_array($arrayOfSkalaTemplates) ){

        $optgroup = new HTML ("optgroup");
        $optgroup->addAttr ("label",_("Likertskalen:"));

        foreach ($arrayOfSkalaTemplates as $template){
            $option = new HTML ("option");
            $option->addAttr ("value",$template[0]);
            if ($template[0] == $selected)
                $option->addAttr ("selected","selected");
            $option->addContent ($template[1]);
            $optgroup->addContent ($option);
        }

        $select->addContent ($optgroup);

    }


    if ( !empty($arrayOfNormalTemplates) && is_array($arrayOfNormalTemplates) ){

        $optgroup = new HTML ("optgroup");
        $optgroup->addAttr ("label",_("Multiple Choice:"));

        foreach ($arrayOfNormalTemplates as $template){
            $option = new HTML ("option");
            $option->addAttr ("value",$template[0]);
            if ($template[0] == $selected)
                $option->addAttr ("selected","selected");
            $option->addContent ($template[1]);
            $optgroup->addContent ($option);
        }

        $select->addContent ($optgroup);

    }


    if (!empty($arrayOfFreetextTemplates) && is_array($arrayOfFreetextTemplates)){

        $optgroup = new HTML ("optgroup");
        $optgroup->addAttr ("label",_("Freitextantworten:"));

        foreach ( $arrayOfFreetextTemplates as $template ){
            $option = new HTML ("option");
            $option->addAttr ("value",$template[0]);
            if ($template[0] == $selected)
                $option->addAttr ("selected","selected");
            $option->addContent ($template[1]);
            $optgroup->addContent ($option);
        }

        $select->addContent ($optgroup);

    }

    return $select->createContent ();

}

# ############################################# end: additional HTML functions #


################################################################################
#                                                                              #
# additional functions                                                         #
#                                                                              #
################################################################################

/**
* detects the type of an object by its itemID
*
* @access  private
* @param   string  $itemID
* @return  string  the insctance of an object
*/
function getInstance ( $itemID ){

    if ($itemID == ROOT_BLOCK || $itemID == $this->evalID)
        return ROOT_BLOCK;
    else {
        $tree = TreeAbstract::GetInstance ( "EvaluationTree", ['evalID' => $this->evalID,
                                                                        'load_mode' => EVAL_LOAD_FIRST_CHILDREN]);
        $group = &$tree->getGroupObject($itemID);
        $childtype = $group->getChildType();

        if ($childtype == "EvaluationQuestion")
            return QUESTION_BLOCK;
        else
            return ARRANGMENT_BLOCK;
    }
}


/**
* swaps positions of two objects
*
* @access  private
* @param   string  $parentID     the parentID
* @param   string  $objectID     the object to swap
* @param   string  $oldposition  the old position
* @param   string  $direction    the direction to swap
*/
function swapPosition ( $parentID,
                        $objectID,
                        $oldposition,
                        $direction ){

    if ( $parentID == ROOT_BLOCK ) $group =  $this->tree->eval;
    else $group =  &$this->tree->getGroupObject( $parentID);

    $numberchildren = $group->getNumberChildren();
    $instance = $group->x_instanceof();

    if ($direction == "up"){
        if ($oldposition == 0)
            $newposition = $numberchildren-1;
        else
            $newposition = $oldposition-1;
    } else {
        if ($oldposition == $numberchildren-1)
            $newposition = 0;
        else
            $newposition = $oldposition+1;
    }

    while( $swapitem = $group->getNextChild () ){
        if ( $swapitem->getPosition () == $newposition ){
            $swapitem->setPosition ($oldposition);
            $swapitem->save ();
        }
    }
    if ( ($parentID != ROOT_BLOCK) &&
          $group->getChildType () == "EvaluationQuestion")
        $object = new EvaluationQuestion ( $objectID );
    else
        $object = &$this->tree->getGroupObject( $objectID );
    $object->setPosition ( $newposition );
    $object->save ();

    if ( $swapitem->isError )
        return EvalCommon::showErrorReport ( $swapitem,
                _("Fehler beim verschieben.") );
    if ( $object->isError )
        return EvalCommon::showErrorReport ( $object,
                _("Fehler beim verschieben.") );
}

# ################################################## end: additional functions #

}

?>
