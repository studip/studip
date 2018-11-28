<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
register1.php - Benutzerregistrierung in Stud.IP, Part I
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Oliver Brakel <obrakel@gwdg.de>

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

page_open(['sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User']);

include('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
if (!Config::get()->ENABLE_SELF_REGISTRATION) {
    ob_start();
    PageLayout::postError(_("Registrierung ausgeschaltet"),
        [_('In dieser Installation ist die Möglichkeit zur Registrierung ausgeschaltet.'),
            '<a href="index.php">' . _('Hier geht es zur Startseite.') . '</a>']);

    $template                     = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox            = $infobox ? ['content' => $infobox] : null;
    echo $template->render();
    page_close();
    die;
}


if ($auth->is_authenticated() && $user->id != "nobody") {
    ob_start();
    PageLayout::postError(_('Sie sind schon als BenutzerIn am System angemeldet!'), ['<a href="index.php">' . _('Hier geht es zur Startseite.') . '</a>']);
    $template                     = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox            = $infobox ? ['content' => $infobox] : null;
    echo $template->render();
    page_close();
    die;
} else {
    ob_start();
    PageLayout::setHelpKeyword('Basis.AnmeldungRegistrierung');
    PageLayout::setTitle(_('Nutzungsbedingungen'));
    $template                     = $GLOBALS['template_factory']->open('register/step1.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox            = $infobox ? ['content' => $infobox] : null;
    $content                      = $template->render();

    $template                     = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = $content;
    echo $template->render();
    $auth->logout();
    page_close();
    die;
}