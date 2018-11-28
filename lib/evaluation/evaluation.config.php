<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Configurationfile for the evaluation module
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


# Include all required files ================================================ #

# ====================================================== end: including files #


# Define public constants =================================================== #

/* General constants ------------------------------------------------------- */
define ("YES", 1);
define ("NO", 0);
define ("DEBUG", 1);
define ("QUOTED", 1);
define ("UNQUOTED", 0);
define ("EVAL_MIN_SEARCHLEN", 3);
define ("EVAL_MAX_TEMPLATENAMELEN", 22);
/* -------------------------------------------------- end: general constants */

/* Path constants ---------------------------------------------------------- */
define ("EVAL_PATH_RELATIV", "lib/evaluation/");
define ("EVAL_PATH", EVAL_PATH_RELATIV);
define ("EVAL_PATH_CLASSES", EVAL_PATH."classes/");
define ("EVAL_PATH_DBCLASSES", EVAL_PATH_CLASSES."db/");
/* ----------------------------------------------------- end: path constatns */

/* Class constants --------------------------------------------------------- */
define ("EVAL_FILE_EDIT", "evaluation_admin_edit.inc.php");
define ("EVAL_FILE_TEMPLATE", "evaluation_admin_template.inc.php");
define ("EVAL_FILE_OVERVIEW", "evaluation_admin_overview.inc.php");
define ("EVAL_FILE_SHOW", "show_evaluation.php");
define ("EVAL_FILE_ADMIN", "admin_evaluation.php");

define ("EVAL_FILE_OBJECT", EVAL_PATH_CLASSES."EvaluationObject.class.php");
define ("EVAL_FILE_OBJECTDB", EVAL_PATH_DBCLASSES."EvaluationObjectDB.class.php");
define ("EVAL_FILE_EVAL", EVAL_PATH_CLASSES."Evaluation.class.php");
define ("EVAL_FILE_EVALDB", EVAL_PATH_DBCLASSES."EvaluationDB.class.php");
define ("EVAL_FILE_GROUP", EVAL_PATH_CLASSES."EvaluationGroup.class.php");
define ("EVAL_FILE_GROUPDB", EVAL_PATH_DBCLASSES."EvaluationGroupDB.class.php");
define ("EVAL_FILE_QUESTION", EVAL_PATH_CLASSES."EvaluationQuestion.class.php");
define ("EVAL_FILE_QUESTIONDB", EVAL_PATH_DBCLASSES."EvaluationQuestionDB.class.php");
define ("EVAL_FILE_ANSWER", EVAL_PATH_CLASSES."EvaluationAnswer.class.php");
define ("EVAL_FILE_ANSWERDB", EVAL_PATH_DBCLASSES."EvaluationAnswerDB.class.php");
define ("EVAL_FILE_EXPORTMANAGER", EVAL_PATH_CLASSES."EvaluationExportManager.class.php");
define ("EVAL_FILE_EXPORTMANAGERCSV", EVAL_PATH_CLASSES."EvaluationExportManagerCSV.class.php");
define ("EVAL_FILE_EVALTREE", EVAL_PATH_CLASSES."EvaluationTree.class.php");
define ("EVAL_FILE_EDIT_TREEVIEW", EVAL_PATH_CLASSES."EvaluationTreeEditView.class.php");
define ("EVAL_FILE_SHOW_TREEVIEW", EVAL_PATH_CLASSES."EvaluationTreeShowUser.class.php");

define ("HTML", EVAL_PATH_CLASSES."HTML.class.php");
define ("HTMLempty", EVAL_PATH_CLASSES."HTMLempty.class.php");
/* --------------------------------------------------- end:  class constants */

/* Library constants ------------------------------------------------------- */
define ("EVAL_LIB_COMMON", EVAL_PATH."evaluation.lib.php");
define ("EVAL_LIB_OVERVIEW", EVAL_PATH."evaluation_admin_overview.lib.php");
define ("EVAL_LIB_EDIT", EVAL_PATH."evaluation_admin_edit.lib.php");
define ("EVAL_LIB_TEMPLATE", EVAL_PATH."evaluation_admin_template.lib.php");
define ("EVAL_LIB_SHOW", EVAL_PATH."evaluation_show.lib.php");
/* -------------------------------------------------- end: library constants */

/* Picture constants ------------------------------------------------------- */
define ("EVAL_PIC_ICON",                Icon::create('test', 'inactive')->asImagePath());
define ("EVAL_PIC_PREVIEW",             Icon::create('question-circle', 'clickable')->asImagePath());
define ("EVAL_PIC_ADMIN",               Icon::create('admin', 'clickable')->asImagePath());
define ("EVAL_PIC_LOGO",                Assets::image_path('sidebar/evaluation-sidebar.png'));
define ("EVAL_PIC_ARROW",               Icon::create('arr_1right', 'accept')->asImagePath());
define ("EVAL_PIC_ARROW_ACTIVE",        Icon::create('arr_1down', 'accept')->asImagePath());
define ("EVAL_PIC_SUCCESS",             Icon::create('accept', 'accept')->asImagePath());
define ("EVAL_PIC_ERROR",               Icon::create('decline', 'attention')->asImagePath());
define ("EVAL_PIC_INFO",                Icon::create('exclaim', 'inactive')->asImagePath());
define ("EVAL_PIC_INFO_SMALL",          Icon::create('info', 'info')->asImagePath());
define ("EVAL_PIC_HELP",                Icon::create('info-circle', 'inactive')->asImagePath());
define ("EVAL_PIC_MOVE_GROUP",          Icon::create('arr_2left', 'sort')->asImagePath());
define ("EVAL_PIC_MOVE_UP",             Icon::create('arr_2up', 'sort')->asImagePath());
define ("EVAL_PIC_MOVE_DOWN",           Icon::create('arr_2down', 'sort')->asImagePath());
define ("EVAL_PIC_MOVE_RIGHT",          Icon::create('arr_2right', 'sort')->asImagePath());
define ("EVAL_PIC_MOVE_LEFT",           Icon::create('arr_2left', 'sort')->asImagePath());
define ("EVAL_PIC_CREATE_ANSWERS",      Assets::image_path('eval_create_answers.gif'));
define ("EVAL_PIC_EDIT_ANSWERS",        Assets::image_path('eval_edit_answers.gif'));
define ("EVAL_PIC_TIME",                Icon::create('date', 'info')->asImagePath());
define ("EVAL_PIC_EXCLAIM",             Icon::create('info', 'info')->asImagePath());
define ("EVAL_PIC_DELETE_GROUP",        Icon::create('trash', 'clickable')->asImagePath());
define ("EVAL_PIC_MOVE_BUTTON",         Icon::create('arr_2right', 'sort')->asImagePath());
define ("EVAL_PIC_ADD",                 Icon::create('add', 'clickable')->asImagePath());
define ("EVAL_PIC_ADD_TEMPLATE",        Icon::create('add', 'clickable')->asImagePath());
define ("EVAL_PIC_REMOVE",              Icon::create('trash', 'clickable')->asImagePath());
define ("EVAL_PIC_EDIT",                Icon::create('edit', 'clickable')->asImagePath());
define ("EVAL_PIC_BACK",                Icon::create('link-intern', 'clickable')->asImagePath());
define ("EVAL_PIC_ARROW_TEMPLATE",      Icon::create('arr_1right', 'clickable')->asImagePath());
define ("EVAL_PIC_ARROW_TEMPLATE_OPEN", Icon::create('arr_1down', 'clickable')->asImagePath());
define ("EVAL_PIC_ARROW_NEW",           Icon::create('arr_1right', 'sort')->asImagePath());
define ("EVAL_PIC_ARROW_NEW_OPEN",      Icon::create('arr_1down', 'sort')->asImagePath());
define ("EVAL_PIC_ARROW_RUNNING",       Icon::create('arr_1right', 'accept')->asImagePath());
define ("EVAL_PIC_ARROW_RUNNING_OPEN",  Icon::create('arr_1down', 'accept')->asImagePath());
define ("EVAL_PIC_ARROW_STOPPED",       Icon::create('arr_1right', 'attention')->asImagePath());
define ("EVAL_PIC_ARROW_STOPPED_OPEN",  Icon::create('arr_1down', 'attention')->asImagePath());
define ("EVAL_PIC_TREE_ARROW",          Icon::create('arr_1right', 'clickable')->asImagePath());
define ("EVAL_PIC_TREE_ARROW_ACTIVE",   Icon::create('arr_1down', 'clickable')->asImagePath());
define ("EVAL_PIC_TREE_BLANC",          Assets::image_path('forumleer.gif'));
define ("EVAL_PIC_TREE_ROOT",           Icon::create('vote', 'inactive')->asImagePath());
define ("EVAL_PIC_TREE_GROUP",          Icon::create('test', 'info')->asImagePath());
define ("EVAL_PIC_TREE_GROUP_FILLED",   Assets::image_path('eval_group_filled.gif'));
define ("EVAL_PIC_TREE_QUESTIONGROUP",  Icon::create('test', 'info')->asImagePath());
define ("EVAL_PIC_TREE_QUESTIONGROUP_FILLED", Assets::image_path('eval_qgroup_filled.gif'));
define ("EVAL_PIC_EXPORT_FILE",         Icon::create('file-xls', 'clickable')->asImagePath());
define ("EVAL_PIC_YES",                 Icon::create('accept', 'accept')->asImagePath());
define ("EVAL_PIC_NO",                  Icon::create('decline', 'attention')->asImagePath());
define ("EVAL_PIC_SHARED",              Icon::create('checkbox-checked', 'clickable')->asImagePath());
define ("EVAL_PIC_NOTSHARED",           Icon::create('checkbox-unchecked', 'clickable')->asImagePath());
/* -------------------------------------------------- end: picture constants */

/* CSS constants ----------------------------------------------------------- */
define ("EVAL_CSS_SUCCESS", "eval_success");
define ("EVAL_CSS_ERROR",   "eval_error");
define ("EVAL_CSS_INFO",    "eval_info");
/* ------------------------------------------------------ end: css constants */

# ===================================================== end: define constants #
?>
