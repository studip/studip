<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_evaluation.php
//
// Show the admin pages
//
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


/**
 * admin_evaluation.php
 *
 *
 * @author  cb
 * @version 10. Juni 2003
 * @access  public
 * @package evaluation
 */

require '../lib/bootstrap.php';

ob_start(); // start output buffering

page_open ( ["sess" => "Seminar_Session",
                  "auth" => "Seminar_Auth",
                  "perm" => "Seminar_Perm",
                  "user" => "Seminar_User"]);
$perm->check ("autor");

$list = Request::option('list');
$view = Request::option('view');


include_once 'lib/seminar_open.php';

PageLayout::setHelpKeyword("Basis.Evaluationen");
$title = _('Verwaltung von Evaluationen');
if (Context::get()) {
    $title = Context::getHeaderLine() . ' - ' . $title;
}
PageLayout::setTitle($title);

require_once 'lib/evaluation/evaluation.config.php';

if ($view === 'eval_inst') {
    Navigation::activateItem('/admin/institute/evaluation');
    require_once 'lib/admin_search.inc.php';
} else if (Context::getId() && $view == "eval_sem") {
    Navigation::activateItem('/course/admin/evaluation');
} else {
    Navigation::activateItem('/tools/evaluation');
}

if ((Context::getId()) && ($view == "eval_sem") || ($view == "eval_inst")) {
    $the_range = Context::getId();
} else {
    $the_range = Request::option('rangeID');
}

if ($the_range) {
    if (get_Username($the_range)) {
        $the_range = get_Username($the_range);
    }
    if (get_Userid($the_range)) {
        $isUserrange = 1;
    }
} elseif ($view) {
    $the_range = Context::getId();
}

if (empty($the_range)) {
    $the_range = $user->id;
    $isUserrange = 1;
}

if ($the_range != $auth->auth['uname'] && $the_range != 'studip' && !$isUserrange){
    $view_mode = get_object_type($the_range);
    if ($view_mode == "fak"){
        $view_mode = "inst";
    }
}


ob_start();
if (Request::option('page') == "edit"){
    include (EVAL_PATH.EVAL_FILE_EDIT);
}else{
    include (EVAL_PATH.EVAL_FILE_OVERVIEW);
}
$template = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = ob_get_clean();
echo $template->render();
page_close();
