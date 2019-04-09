<?php
# Lifter002: TEST
# Lifter003: TEST
# Lifter007: TEST
# Lifter010: DONE - not applicable
/*
email_validation.php - Hochstufung eines user auf Status autor, wenn erfolgreich per Mail zurueckgemeldet
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

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

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User'
]);
$auth->login_if($auth->auth['uid'] == 'nobody');
$perm->check('user');
// nobody hat hier nix zu suchen...

include 'lib/seminar_open.php'; // initialise Stud.IP-Session

// hier wird noch mal berechnet, welches secret in der Bestaetigungsmail uebergeben wurde
$secret = Request::option('secret');
PageLayout::setHelpKeyword('Basis.AnmeldungMail');
PageLayout::setTitle(_('Bestätigung der E-Mail-Adresse'));

//user bereits vorhanden
if ($perm->have_perm('autor')) {
    $info = sprintf(_('Sie haben schon den Status <b>%s</b> im System.
                       Eine Aktivierung des Accounts ist nicht mehr nötig, um Schreibrechte zu bekommen'), $auth->auth['perm']);
    $details = [];
    $details[] = sprintf('<a href="%s">%s</a>', URLHelper::getLink('index.php'), _('zurück zur Startseite'));
    $message = MessageBox::info($info, $details);
}

//  So, wer bis hier hin gekommen ist gehoert zur Zielgruppe...
// Volltrottel (oder abuse)
else if (empty($secret)) {
    $message = MessageBox::error(_('Sie müssen den vollständigen Link aus der Bestätigungsmail in die Adresszeile Ihres Browsers kopieren.'));
}

// abuse (oder Volltrottel)
else if (!Seminar_Register_Auth::validateSecret($secret, $user->id)) {
    $error = _('Der übergebene <em>Secret-Code</em> ist nicht korrekt.');
    $details = [];
    $details[] = _('Sie müssen unter dem Benutzernamen eingeloggt sein, für den Sie die Bestätigungsmail erhalten haben.');
    $details[] = _('Und Sie müssen den vollständigen Link aus der Bestätigungsmail in die Adresszeile Ihres Browsers kopieren.');
    $message = MessageBox::error($error, $details);

    // Mail an abuse
    $REMOTE_ADDR=getenv("REMOTE_ADDR");
    $Zeit=date("H:i:s, d.m.Y",time());
    $username = $auth->auth["uname"];
    StudipMail::sendAbuseMessage("Validation", "Secret falsch\n\nUser: $username\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
}

// alles paletti, Status ändern
else {
    $query = "UPDATE auth_user_md5 SET perms = 'autor' WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$user->id]);
    if ($statement->rowCount() == 0) {
        $error = _('Fehler! Bitte wenden Sie sich an den Systemadministrator.');
        $details = [$query];
        $message = MessageBox::error($error, $details);
    } else {
        $success = _('Ihr Status wurde erfolgreich auf <em>autor</em> gesetzt.<br>
                      Damit dürfen Sie in den meisten Veranstaltungen schreiben, für die Sie sich anmelden.');
        $details = [];
        $details[] = _('Einige Veranstaltungen erfordern allerdings bei der Anmeldung die Eingabe eines Passwortes.
                        Dieses Passwort erfahren Sie von den Lehrenden der Veranstaltung.');
        $message = MessageBox::success($success, $details);

        // Auto-Inserts
        AutoInsert::instance()->saveUser($user->id, "autor");

        $auth->logout();    // einen Logout durchführen, um erneuten Login zu erzwingen

        $info = sprintf(_('Die Statusänderung wird erst nach einem erneuten %sLogin%s wirksam!<br>
                          Deshalb wurden Sie jetzt automatisch ausgeloggt.'),
                        '<a href="index.php?again=yes"><em>',
                        '</em></a>');
        $message .= MessageBox::info($info);
    }
}

$template = $GLOBALS['template_factory']->open('email-validation');
$template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
$template->message = $message;
echo $template->render();

page_close();
