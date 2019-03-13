<?php
/**
 * register2.php - Benutzerregistrierung in Stud.IP, Part II
 *
 * @author    Stefan Suchi <suchi@gmx.de>
 * @author    Oliver Brakel <obrakel@gwdg.de>
 * @copyright 2000 authors
 * @license   GPL2 or any later version
 */

require '../lib/bootstrap.php';

page_open([
    'sess' => 'Seminar_Session',
    'auth' => Config::get()->ENABLE_SELF_REGISTRATION ? 'Seminar_Register_Auth' : 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

if (!Config::get()->ENABLE_SELF_REGISTRATION){
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
} elseif ($GLOBALS['auth']->auth['uid'] === 'nobody') {
    $GLOBALS['auth']->logout();
    header('Location: ' . URLHelper::getURL('register2.php'));
} else {
    include 'lib/seminar_open.php'; // initialise Stud.IP-Session

    PageLayout::setHelpKeyword('Basis.AnmeldungRegistrierung');
    PageLayout::setTitle(_('Registrierung erfolgreich'));
    echo $GLOBALS['template_factory']->render(
        'register/success.php',
        [],
        $GLOBALS['template_factory']->open('layouts/base.php')
    );
    $GLOBALS['auth']->logout();
}

page_close();
