<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Beschreibung
 *
 * @author      Christian Bauer <alfredhitchcock@gmx.net>
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

# PHP-LIB: open session ===================================================== #
/*page_open (array ("sess" => "Seminar_Session",
          "auth" => "Seminar_Auth",
          "perm" => "Seminar_Perm",
          "user" => "Seminar_User"));
$auth->login_if ($auth->auth["uid"] == "nobody");
$perm->check ("autor");*/
# ============================================================== end: PHP-LIB #

# Include all required files ================================================ #

require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_LIB_EDIT;
require_once EVAL_FILE_EDIT_TREEVIEW;

# ====================================================== end: including files #

# define constancs ========================================================== #

/**
 * @const NEW_EVALUATION_TITLE  title of a new question block
 * @access public
 */
define("NEW_EVALUATION_TITLE", _("Neue Evaluation"));

/**
 * @const FIRST_ARRANGMENT_BLOCK_TITLE  title of a new arrangment block
 * @access public
 */
define("FIRST_ARRANGMENT_BLOCK_TITLE", _("Erster Gruppierungsblock"));

# ====================================================== end: define constancs #

$debug = "<pre class=\"steelgroup6\" style=\"font-size:10pt\">"
    . "<pre class=\"steelgroup3\" style=\"font-size:10pt\">"
    . "Welcome to BugReport 1.02 "
    . "[Sharewareversion]"
    . "</pre>";

# check the evalID ========================================================= #

global $user;

if (Request::submitted('newButton')) {
    $debug .= "neue Eval!<br>";
    // create the first group
    $group = new EvaluationGroup();
    $group->setTitle(FIRST_ARRANGMENT_BLOCK_TITLE, QUOTED);
    $group->setText("");
    if ($group->isError()) {
        return MessageBox::error(_("Fehler beim Anlegen einer Gruppe"));
    }
    // create a new eval
    $eval = new Evaluation ();
    $rangeID = Request::option("rangeID");
    if ($rangeID == $user->username) {
        $rangeID = $user->id;
    }

    $eval->setAuthorID($user->id);
    $eval->setTitle(NEW_EVALUATION_TITLE);
    $eval->setAnonymous(YES);
    $evalID = $eval->getObjectID();
    $eval->addChild($group);
    $eval->save();

    if ($eval->isError()) {
        return MessageBox::error(_("Fehler beim Anlegen einer Evaluation"));
    }

    $groupID = $group->getObjectID();
    $evalID = $eval->getObjectID();

} elseif (Request::option("evalID") && (Request::option("evalID") != NULL)) {
    $debug .= "isset _REQUTEST[evalID]!<br>";
    $evalID = Request::option("evalID");
    $eval = new Evaluation ($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
    if ($eval->isError()) {
        PageLayout::postError(_("Es wurde eine ungültige Evaluations-ID übergeben."));
    } elseif ($evalID == NULL) {
        PageLayout::postError(_("Es wurde keine Evaluations-ID übergeben"));
    }

} else {
    PageLayout::postError(_("Es wurde keine Evaluations-ID übergeben"));
}

# ===================================================== END: check the evalID #

# check the itemID =========================================================  #
$itemID = Request::option('itemID');
if ($itemID) {
    $_SESSION['itemID'] = $itemID;
} elseif (Request::submitted('newButton')) {
    $_SESSION['itemID'] = "root";
}
# ===================================================== END: check the itemID #

# check the rangeID ========================================================  #

if (Request::option("rangeID")) {
    $_SESSION['rangeID'] = Request::option("rangeID");

}

# ==================================================== END: check the rangeID #

# EVTAU: employees of the vote-team against urlhacking ====================== #

$eval = new Evaluation($evalID, NULL, EVAL_LOAD_NO_CHILDREN);

// someone has voted
if ($eval->hasVoted()) {
    $error = MessageBox::error(
        _("An dieser Evaluation hat bereits jemand teilgenommen. Sie darf nicht mehr verändert werden.")
    );
}


// only the author or user with tutor perm in all evalRangeIDs should edit an eval
$authorID = $eval->getAuthorID();
$db = new EvaluationObjectDB();

if ($authorID != $user->id) {

    $no_permisson = 0;

    if (is_array($eval->getRangeIDs())) {

        foreach ($eval->getRangeIDs() as $rangeID) {

            $user_perm = $db->getRangePerm($rangeID, $user->id, YES);

            // every range with a lower perm than Tutor
            if ($user_perm < 7)
                $no_permisson++;
        }

        if ($no_permisson > 0) {
            if ($no_permisson == 1) {
                $no_permisson_msg = _("Sie haben in einem Bereich, in welchem diese Evaluation hängt, nicht aussreichene Rechte, um diese Eval zu bearbeiten.");
            } else {
                $no_permisson_msg = sprintf(_("Sie haben in %s Bereichen, in denen diese Evaluation hängt, nicht aussreichene Rechte, um diese Eval zu bearbeiten."), $no_permisson);
            }
            $error_msgs[] = MessageBox::error($no_permisson_msg);
        }
    }
}


# ============================================ end: Collection post/get-vars #

# Print Error MSG and end Site ============================================= #

if ($error_msgs) {

    $back_button = ("&nbsp;&nbsp;&nbsp;")
        . "<a href=\"" . URLHelper::getLink('admin_evaluation.php?page=overview&rangeID=' . Request::option('rangeID')) . "\">"
        . _("Zur Evaluations-Verwaltung")
        . "</a>";

    if (is_array($error_msgs)) {
        foreach ($error_msgs as $error_msg) {
            $errors .= $error_msg . "<br>";
        }

    }
    echo EvalEdit::createSite($errors . $back_button, " ");
    include_once('lib/include/html_end.inc.php');
    page_close();
    exit ();

}


# ======================================== end: Print Error MSG and end Site #

/* Do first all actions for templates -------------------------------------- */
$templateSite = include(EVAL_FILE_TEMPLATE);
/* --------------------------------- end: do first all actions for templates */


# Creating the Tree ======================================================== #
$EditTree = new EvaluationTreeEditView($itemID, $evalID);

# Send messages to the tree ================================================ #

if (Request::submitted('newButton')) {
    $EditTree->msg["root"] = "msg§"
        . _("Erstellen Sie nun eine Evaluation.<br> Der erste Gruppierungsblock ist bereits angelegt worden. Wenn Sie ihn öffnen, können Sie dort weitere Gruppierungsblöcke oder Fragenblöcke anlegen.");
}
$editSite = $EditTree->showEvalTree($itemID, 1);
echo EvalEdit::createSite($editSite, $templateSite);

?>
