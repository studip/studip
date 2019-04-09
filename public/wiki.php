<?php
# Lifter001: DONE
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
wiki.php - (No longer so) Simple WikiWikiWeb in Stud.IP

@module wiki
@author Tobias Thelen <tthelen@uos.de>

Copyright (C) 2003 Tobias Thelen <tthelen@uni-osnabrueck.de>
Contains code (regex for WikiWord detection) from Blast Wiki http://www.roboticboy.com/wiki/ (GPL'd)
Contains code (diff routine) from PukiWiki http://www.pukiwiki.org (GPL'd)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


require '../lib/bootstrap.php';

page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);
$auth->login_if(Request::get('again') && ($auth->auth["uid"] == "nobody"));

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once 'lib/wiki.inc.php';

$view = Request::get('view');
$keyword = Request::get('keyword');
$version = Request::int('version');
$cmd = Request::option('cmd');

if ($view === 'wikiprint') {
    printWikiPage($keyword, $version);
    page_close();
    die();
} elseif ($view === 'wikiprintall') {
    printAllWikiPages(Context::getId(), Context::getHeaderLine());
    page_close();
    die();
} elseif ($view === 'export_pdf') {
    exportWikiPagePDF($keyword, $version);
} elseif ($view === 'exportall_pdf') {
    exportAllWikiPagesPDF('all', Request::option('sortby'));
}

checkObject(); // do we have an open object?
checkObjectModule("wiki"); //are we allowed to use this module here?
object_set_visit_module("wiki");

PageLayout::setHelpKeyword("Basis.Wiki"); // Hilfeseite im Hilfewiki
PageLayout::setTitle(Context::getHeaderLine() . " - " . _("Wiki"));

if (in_array(Request::get('view'), words('listnew listall export'))) {
    Navigation::activateItem('/course/wiki/'.$view);
} else {
    Navigation::activateItem('/course/wiki/show');
}

if (Request::option('wiki_comments') === 'all') {         // show all comments
    $show_wiki_comments = 'all';
} elseif (Request::option('wiki_comments') === 'none') {  // don't show comments
    $show_wiki_comments = 'none';
} else {                             // show comments as icons
    $show_wiki_comments = 'icon';
}

URLHelper::addLinkParam('wiki_comments', $show_wiki_comments);

ob_start();

// ---------- Start of main WikiLogic

if ($view === 'listall') {
    //
    // list all pages, default sorting = alphabetically
    //
    SkipLinks::addIndex(_('Alle Seiten'), 'main_content', 100);
    listPages('all', Request::option('sortby'));

} else if ($view === 'listnew') {
    //
    // list new pages, default sorting = newest first
    //
    SkipLinks::addIndex(_('Neue Seiten'), 'main_content', 100);
    listPages('new', Request::option('sortby'));

} else if ($view === 'diff') {
    //
    // show one large diff-file containing all changes
    //
    SkipLinks::addIndex(_('Seite mit Änderungen'), 'main_content', 100);
    showDiffs($keyword, Request::option('versionssince'));

} else if ($view === 'combodiff') {
    //
    // show one large diff-file containing all changes
    //
    SkipLinks::addIndex(_('Seite mit Änderungen'), 'main_content', 100);
    showComboDiff($keyword);

} else if ($view === 'export') {
    //
    // show export dialog
    //
    SkipLinks::addIndex(_('Seiten exportieren'), 'wiki_export', 100);
    exportWiki();

} else if ($view === 'search') {
    searchWiki(Request::get('searchfor'), Request::get('searchcurrentversions'), Request::get('keyword'), Request::get('localsearch'));

} else if ($view === 'edit') {
    //
    // show page for editing
    //

    // prevent malformed urls: keyword must be set
    if (!$keyword) {
        throw new InvalidArgumentException(_('Es wurde keine zu editierende Seite übergeben!'));
    }

    $wikiData = getWikiPage($keyword, 0); // always get newest page

    if ($wikiData && !$wikiData->isEditableBy($GLOBALS['user'])) {
        throw new AccessDeniedException(_('Sie haben keine Berechtigung, Seiten zu editieren!'));
    }

    SkipLinks::addIndex(_('Seite bearbeiten'), 'main_content', 100);

    // set lock
    setWikiLock(null, $user->id, Context::getId(), $keyword);

    // show form
    wikiEdit($keyword, $wikiData, $user->id);

} else if ($view === 'editnew') {
    //
    // edit a new page
    //

    $range_id = Context::getId();
    $edit_perms = CourseConfig::get($range_id)->WIKI_COURSE_EDIT_RESTRICTED ? 'tutor' : 'autor';
    if (!$perm->have_studip_perm($edit_perms, $range_id)) {
        throw new AccessDeniedException(_('Sie haben keine Berechtigung, in dieser Veranstaltung Seiten zu editieren!'));
    }

    // prevent malformed urls: keyword must be set
    if (!$keyword) {
        throw new InvalidArgumentException(_('Es wurde keine zu editierende Seite übergeben!'));
    }

    $wikiData = getWikiPage($keyword, 0); // always get newest page

    // warning in the case of an existing wiki page
    if ($wikiData && !$wikiData->isNew()) {
        PageLayout::postInfo(sprintf(
            _('Die Wiki-Seite "%s" existiert bereits. Änderungen hier überschreiben diese Seite!'),
            htmlReady($keyword)
        ));
    }

    // set lock
    setWikiLock(null, $user->id, Context::getId(), $keyword);

    //show form
    wikiEdit($keyword, $wikiData, $user->id, Request::get('lastpage'));

} else {
    // Default action: Display WikiPage (+ logic for submission)
    //
    if (empty($keyword)) {
        $keyword = 'WikiWikiWeb'; // display Start page as default
    }
    releaseLocks($keyword); // kill old locks
    $special = '';

    if (Request::submitted('submit')) {
        //
        // Page was edited and submitted
        //
        submitWikiPage($keyword, $version, Studip\Markup::purifyHtml(Request::get('body')), $user->id, Context::getId());
        $version = ''; // $version="" means: get latest

    } else if ($cmd === 'abortedit') { // Editieren abgebrochen
        //
        // Editing page was aborted
        //

        // kill lock (set when starting to edit)
        releasePageLocks($keyword, $user->id);

        // if editing new page was aborted, display last page again
        $keyword = Request::get('lastpage', $keyword);

    } else if ($cmd === 'delete') {
        //
        // Delete request sent -> confirmdialog and current page
        //
        $special = 'delete';

    } else if ($cmd === 'really_delete') {
        //
        // Delete was confirmed -> really delete
        //

        $keyword = deleteWikiPage($keyword, $version, Context::getId());
        $version = ''; // show latest version
    } else if ($cmd === 'delete_all') {
        //
        // Delete all request sent -> confirmdialog and current page
        //
        $special = 'delete_all';

    } else if ($cmd === 'really_delete_all') {
        //
        // Delete all was confirmed -> delete entire page
        //
        $keyword = deleteAllWikiPage($keyword, Context::getId());
        $version = ''; // show latest version
    }

    //
    // Show Page
    //
    SkipLinks::addIndex(_('Aktuelle Seite'), 'main_content', 100);

    $page = WikiPage::findLatestPage(Context::getId(), $keyword);
    if (!$page || $page->isVisibleTo($GLOBALS['user']->id)) {
        showWikiPage($keyword, $version, $special, $show_wiki_comments, Request::get('hilight'));
    } else {
        throw new AccessDeniedException(sprintf(
            _('Sie haben keine Berechtigung, die Seite %s zu lesen!'),
            $keyword
        ));
    }


} // end default action

$layout = $GLOBALS['template_factory']->open('layouts/base');
$layout->content_for_layout = ob_get_clean();

if (in_array($cmd, words('show abortedit really_delete really_delete_all'))) {
    // redirect to normal view to avoid duplicate edits on reload or back/forward
    header('Location: ' . URLHelper::getURL('', compact('keyword')));
} else {
    Sidebar::get()->setImage('sidebar/wiki-sidebar.png');
    echo $layout->render();
}

// Save data back to database.
page_close();
