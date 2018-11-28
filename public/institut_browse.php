<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// institut_browse.php
//
//
// Copyright (c) 2002 André Noack <noack@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
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

require '../lib/bootstrap.php';

page_open(array(
    "sess" => "Seminar_Session",
    "auth" => "Seminar_Auth",
    "perm" => "Seminar_Perm",
    "user" => "Seminar_User"
));

$perm->check('user');

require_once ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once ('lib/visual.inc.php');

PageLayout::setHelpKeyword("Basis.SuchenEinrichtungen");
PageLayout::setTitle(_("Suche nach Einrichtungen"));
Navigation::activateItem('/search/institutes');

$template = $GLOBALS['template_factory']->open('institute_browse');
$template->set_layout('layouts/base');

// Start of Output
ob_start();
$view = DbView::getView('range_tree');
$the_tree = new StudipRangeTreeView();
$the_tree->open_ranges['root'] = true;
$template->tree_view = $the_tree;

// reset the search and show StudipRangeTree
if (Request::submitted('reset')) {
    $the_tree->open_ranges['root'] = true;
} elseif (Request::submitted('search')) {
    $search_text = Request::get('search_text');
    if ($search_text && mb_strlen($search_text) > 3){
        $view->params[0] = "%" . Request::quoted('search_text') . "%";
        $view->params[1] = "%" . Request::quoted('search_text') . "%";
        $search_results = $view->get_query("view:TREE_SEARCH_ITEM");
        $template->search_results = $search_results;
        while ($search_results->next_record()) {
            $range_tree_object = new RangeTreeObject($search_results->f("item_id"));
            $tree_item_ids[] = $range_tree_object->tree_item_id;
        }
        $template->tree_item_ids = $tree_item_ids;
        $template->tree = $range_tree_object->tree;
    }
    $template->search_text = $search_text;
}

// add sidebar
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/institute-sidebar.png');

// the sidebar needs a dummy widget to be rendered
$widget = new Widget();
$sidebar->addWidget($widget);

// render view
echo $template->render();
page_close();
