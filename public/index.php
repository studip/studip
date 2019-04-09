<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
 * index.php - Startseite von Stud.IP (anhaengig vom Status)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Stefan Suchi <suchi@gmx.de>
 * @author      Ralf Stockmann <rstockm@gwdg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require '../lib/bootstrap.php';

page_open(['sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User']);

$auth->login_if(Request::get('again') && ($auth->auth['uid'] == 'nobody'));

// evaluate language clicks
// has to be done before seminar_open to get switching back to german (no init of i18n at all))
if (Request::get('set_language')) {
    if(array_key_exists(Request::get('set_language'), $GLOBALS['INSTALLED_LANGUAGES'])) {
        $_SESSION['forced_language'] = Request::get('set_language');
        $_SESSION['_language'] = Request::get('set_language');
    }
}

// store user-specific language preference
if ($auth->is_authenticated() && $user->id != 'nobody') {
    // store last language click
    if (mb_strlen($_SESSION['forced_language'])) {
        $query = "UPDATE user_info SET preferred_language = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$_SESSION['forced_language'], $user->id]);

        $_SESSION['_language'] = $_SESSION['forced_language'];
    }
    $_SESSION['forced_language'] = null;
}

// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

include 'lib/seminar_open.php'; // initialise Stud.IP-Session

// if new start page is in use, redirect there (if logged in)
if ($auth->is_authenticated() && $user->id != 'nobody') {
    header('Location: ' . URLHelper::getURL('dispatch.php/start'));
    die;
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * *   L O G I N - P A G E   ( N O B O D Y - U S E R )   * *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

PageLayout::setHelpKeyword("Basis.Startseite"); // set keyword for new help
PageLayout::setTitle(_("Startseite"));
Navigation::activateItem('/start');
PageLayout::setTabNavigation(NULL); // disable display of tabs

// Start of Output
include 'lib/include/html_head.inc.php'; // Output of html head
include 'lib/include/header.php';

$index_nobody_template = $GLOBALS['template_factory']->open('index_nobody');
$cache = StudipCacheFactory::getCache();
$stat = $cache->read('LOGINFORM_STATISTICS');
if (!is_array($stat)) {
    $stat = [];
    $stat['num_active_courses'] = Course::countBySQL();
    $stat['num_registered_users'] = User::countBySQL();
    $cache->write('LOGINFORM_STATISTICS', $stat, 3600);
}
$index_nobody_template->set_attributes(array_merge($stat, [
    'num_online_users' => get_users_online_count(),
]));

if (Request::get('logout'))
{
    $index_nobody_template->set_attribute('logout', true);
}

echo '<div><div class="index_container">';
echo $index_nobody_template->render();

$layout = $GLOBALS['template_factory']->open('shared/index_box');

// Prüfen, ob PortalPlugins vorhanden sind.
$portalplugins = PluginEngine::getPlugins('PortalPlugin');

foreach ($portalplugins as $portalplugin) {
    $template = $portalplugin->getPortalTemplate();

    if ($template) {
        echo $template->render(NULL, $layout);
        $layout->clear_attributes();
    }
}

page_close();

include 'lib/include/html_end.inc.php';
