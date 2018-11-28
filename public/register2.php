<?php 
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
register2.php - Benutzerregistrierung in Stud.IP, Part II
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

$my_auth = (Config::get()->ENABLE_SELF_REGISTRATION ? "Seminar_Register_Auth" : "Seminar_Default_Auth");

page_open(array("sess" => "Seminar_Session", "auth" => $my_auth, "perm" => "Seminar_Perm", "user" => "Seminar_User"));

if (!Config::get()->ENABLE_SELF_REGISTRATION){
    ob_start();
    PageLayout::postError(_("Registrierung ausgeschaltet"),
            [_("In dieser Installation ist die Möglichkeit zur Registrierung ausgeschaltet."),
             '<a href="index.php">' . _("Hier geht es zur Startseite."). '</a>']);

    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox = $infobox ? array('content' => $infobox) : null;
    echo $template->render();
    page_close();
    die;
}
if ($auth->auth["uid"] == "nobody") {
    $auth->logout();
    header("Location: register2.php");
    page_close();
    die;
}

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

page_close();

ob_start();
PageLayout::setHelpKeyword('Basis.AnmeldungRegistrierung');
PageLayout::setTitle(_('Registrierung erfolgreich'));
$template                     = $GLOBALS['template_factory']->open('register/success.php');
$template->content_for_layout = ob_get_clean();
$template->infobox            = $infobox ? ['content' => $infobox] : null;
$content                      = $template->render();

$template                     = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = $content;
echo $template->render();
$auth->logout();
page_close();
die;