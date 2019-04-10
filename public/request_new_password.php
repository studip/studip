<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * request_new_password.php
 *
 * Handles password requests and sends a new password to the users email address.
 *
 *
 * @author       Mike Barthel <m.barthel.goe@gmx.de>, Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @access       public
 * @module       request_new_password.php
 * @modulegroup  public
 * @package      studip_core
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// request_new_password.php
// Handles password requests and sends a new password to the users email address
//
// Copyright (C) 2007 Mike Barthel <m.barthel.goe@gmx.de>,
// Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
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

page_open(['sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User']);
$step = Request::int('step');
// set up user session
include 'lib/seminar_open.php';

if (!(Config::get()->ENABLE_REQUEST_NEW_PASSWORD_BY_USER && in_array('Standard', $GLOBALS['STUDIP_AUTH_PLUGIN'])) || $auth->auth["uid"] != "nobody") {
    ob_start();
    if ($auth->auth["uid"] != "nobody") {
        $message = _("Sie können kein neues Passwort anfordern, wenn Sie bereits eingeloggt sind.");
    } else {
        $message = _("Das Anfordern eines neuen Passwortes durch den Benutzer ist in dieser Stud.IP-Installation nicht möglich.");
    }
    PageLayout::postError(_("Passwortanforderung nicht möglich!"), [$message]);

    $template                     = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox            = $infobox ? ['content' => $infobox] : null;
    echo $template->render();
    die();
}
$msg        = [];
$email      = '';
$admin_link = sprintf(
    _("Leider ist ein Fehler aufgetreten. Bitte fordern Sie gegebenenfalls %sper E-Mail%s ein neues Passwort an."),
    "<a href=\"mailto:{$GLOBALS['UNI_CONTACT']}?subject=" . rawurlencode("Stud.IP Passwort vergessen - ".Config::get()->UNI_NAME_CLEAN) . "&amp;body=" . rawurlencode("Ich habe mein Passwort vergessen. Bitte senden Sie mir ein Neues.\nMein Nutzername:\n") . "\">",
    "</a>"
);


/*
    ######################################################
    ### Formularauswertung: Eingabe der E-Mail-Adresse ###
    ######################################################
*/
$email = trim(Request::get('email'));
if ($email != "") {
    $validator = new email_validation_class();
    if (!$validator->ValidateEmailAddress($email)) {
        // E-Mail ungültig
        $msg['error'][] = _("Die E-Mail-Adresse ist ungültig!");
    } else {
        // Suche Benutzer über E-Mail-Adresse
        $userlist = User::findByEmail($email);

        if (count($userlist) === 0) {
            // kein Benutzer mit eingegebener E-Mail
            $msg['error'][] = _("Es konnte kein Benutzer mit dieser E-Mail-Adresse<br>gefunden werden!");
            $msg['info'][]  = $admin_link;
        } elseif (count($userlist) === 1) {
            $one_user = current($userlist);
            if ($one_user->auth_plugin !== 'standard' || in_array($one_user->perms, ['root', 'admin'])) {
                $msg['error'][] = sprintf(
                    _("Ihr Passwort kann nur durch einen Adminstrator geändert werden. Bitte fordern Sie gegebenenfalls %sper E-Mail%s ein neues Passwort an."),
                    "<a href=\"mailto:{$GLOBALS['UNI_CONTACT']}?subject=" . rawurlencode("Stud.IP Passwort vergessen - ".Config::get()->UNI_NAME_CLEAN) . "&amp;body=" . rawurlencode("Ich habe mein Passwort vergessen. Bitte senden Sie mir ein Neues.\nMein Nutzername: " . htmlReady($one_user->username) . "\n") . "\">",
                    "</a>"
                );
            } else {
                // Bestätigungslink senden
                $step          = 2;
                $msg['info'][] = sprintf(_("In Kürze wird Ihnen eine E-Mail an die Adresse %s mit einem Bestätigungslink geschickt. Bitte beachten Sie die Hinweise in dieser E-Mail. Sollten Sie keine E-Mail erhalten haben, vergewissern Sie sich, ob diese evtl. in einem Spam-Ordner abgelegt wurde."), $one_user['Email']);
                $username      = $one_user->username;
                $vorname       = $one_user->vorname;
                $nachname      = $one_user->nachname;
                $id            = Token::create(24 * 60 * 60, $one_user->id);
                // include language-specific subject and mailbody
                $user_language = getUserLanguagePath($one_user->id);
                include("locale/$user_language/LC_MAILS/request_new_password_mail.inc.php");

                StudipMail::sendMessage($one_user->email, $subject, $mailbody);
            }
        } else {
            // Mehrere Benutzer für E-Mail
            $msg['error'][] = _("Diese E-Mail-Adresse wird von mehreren Benutzern genutzt!");
            $msg['info'][]  = $admin_link;
        }
    }
} else {
    // E-Mail leer
    if (Request::int('step')) {
        $msg['error'][] = _("Sie haben keine E-Mail-Adresse eingegeben!");
    }
}

/*
    #################################################
    ### Auswerten des Bestätigungslinks           ###
    #################################################
*/

if (Request::submitted('id')) {
    $token           = Request::option('id');
    $step            = 4;
    $requesting_user = User::find(Token::isValid($token));

    if ($requesting_user) {
        $user_management = new UserManagement($requesting_user['user_id']);
        if ($user_management->changePassword($user_management->generate_password(8))) {
            StudipLog::USER_NEWPWD($requesting_user['user_id'], null, 'Passwort neu setzen angefordert', null, $requesting_user['user_id']);
            $msg['success'][] = sprintf(_("Ihnen wird in Kürze eine E-Mail an die Adresse %s mit Ihrem neuen Passwort geschickt. Bitte beachten Sie die Hinweise in dieser E-Mail."), $requesting_user['Email']);
        } else {
            $msg['error'][] = _("Das Passwort konnte nicht gesetzt werden. Bitte wiederholen Sie den Vorgang oder fordern Sie ein neues Passwort per E-Mail an.");
            $msg['info'][]  = $admin_link;
        }
    } else {
        $msg['error'][] = _("Fehler beim Aufruf dieser Seite. Stellen Sie sicher, dass Sie den gesamten Link in die Adressleiste eingetragen haben. Bitte wiederholen Sie den Vorgang oder fordern Sie ein neues Passwort per E-Mail an.");
        $msg['info'][]  = $admin_link;
    }
}

if (!Request::int('step') && empty($step)) {
    $step = 1;
}

$request_template = $GLOBALS['template_factory']->open('request_password');
$request_template->set_attribute('step', intval($step));
$request_template->set_attribute('messages', $msg);
$request_template->set_attribute('link_startpage', sprintf(_("Zurück zur %sStartseite%s."), '<a href="./index.php?cancel_login=1">', '</a>'));
$request_template->set_attribute('email', $email);

PageLayout::setHelpKeyword('Basis.AnmeldungPasswortAnfrage');
$header_template               = $GLOBALS['template_factory']->open('header');
$header_template->current_page = _('Passwort anfordern');

include('lib/include/html_head.inc.php');
echo $header_template->render();
echo $request_template->render();
include 'lib/include/html_end.inc.php';

page_close();
