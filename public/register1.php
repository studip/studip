<?php
/**
 * register1.php - Benutzerregistrierung in Stud.IP, Part I
 *
 * @author    Stefan Suchi <suchi@gmx.de>
 * @author    Oliver Brakel <obrakel@gwdg.de>
 * @copyright 2000 authors
 * @license   GPL2 or any later version
 */

require '../lib/bootstrap.php';

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

include 'lib/seminar_open.php'; // initialise Stud.IP-Session

if (!Config::get()->ENABLE_SELF_REGISTRATION) {
    PageLayout::postError(_('Registrierung ausgeschaltet'), [
        _('In dieser Installation ist die Möglichkeit zur Registrierung ausgeschaltet.'),
        sprintf(
            '<a href="%s">%s</a>',
            URLHelper::getLink('index.php'),
            _('Hier geht es zur Startseite.')
        )
    ]);

    echo $GLOBALS['template_factory']->render('layouts/base.php', [
        'content_for_layout' => '',
    ]);
} elseif (Config::get()->SHOW_TERMS_ON_FIRST_LOGIN) {
    header('Location: ' . URLHelper::getURL('register2.php'));
} elseif ($GLOBALS['auth']->is_authenticated() && $GLOBALS['user']->id !== 'nobody') {
    PageLayout::postError(_('Sie sind schon als BenutzerIn am System angemeldet!'), [
        sprintf(
            '<a href="%s">%s</a>',
            URLHelper::getLink('index.php'),
            _('Hier geht es zur Startseite.')
        )
    ]);
    echo $GLOBALS['template_factory']->render('layouts/base.php', [
        'content_for_layout' => '',
    ]);
} else {
    PageLayout::setHelpKeyword('Basis.AnmeldungRegistrierung');
    PageLayout::setTitle(_('Nutzungsbedingungen'));
    echo $GLOBALS['template_factory']->render(
        'register/step1.php',
        [],
        $GLOBALS['template_factory']->open('layouts/base.php')
    );
    $auth->logout();
}

page_close();
