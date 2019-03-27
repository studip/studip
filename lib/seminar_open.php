<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
seminar_open.php - Initialises a Stud.IP sesssion
Copyright (C) 2000 Stefan Suchi <suchi@data-quest.de>

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

/**
 * @addtogroup notifications
 *
 * Logging in triggers a UserDidLogin notification. The user's ID is
 * transmitted as subject of the notification.
 */

//redirect the user where he want to go today....
function startpage_redirect($page_code) {
    switch ($page_code) {
        case 1:
        case 2:
            $jump_page = "dispatch.php/my_courses";
        break;
        case 3:
            $jump_page = "dispatch.php/calendar/schedule";
        break;
        case 4:
            $jump_page = "dispatch.php/contact";
        break;
        case 5:
            $jump_page = "dispatch.php/calendar/single";
        break;
        case 6:
            // redirect to global blubberstream
            // or no redirection if blubber isn't active
            if (PluginEngine::getPlugin("Blubber")) {
                $jump_page = "plugins.php/blubber/streams/global";
            }
            break;
    }

    page_close();
    header ('Location: ' . URLHelper::getURL($jump_page));
    exit;
}

global $i_page,
       $DEFAULT_LANGUAGE, $SessionSeminar,
       $sess, $auth, $user, $perm, $_language_path;

//get the name of the current page in $i_page
$i_page = basename($_SERVER['PHP_SELF']);

//INITS
$seminar_open_redirected = false;
$user_did_login = false;

// session init starts here
if ($_SESSION['SessionStart'] == 0) {
    $_SESSION['SessionStart'] = time();
    $_SESSION['object_cache'] = array();

    // try to get accepted languages from browser
    if (!isset($_SESSION['_language']))
        $_SESSION['_language'] = get_accepted_languages();
    if (!$_SESSION['_language'])
        $_SESSION['_language'] = $DEFAULT_LANGUAGE; // else use system default
}

// user init starts here
if ($auth->is_authenticated() && is_object($user) && $user->id != "nobody") {
    if ($_SESSION['SessionStart'] > UserConfig::get($user->id)->CURRENT_LOGIN_TIMESTAMP) {      // just logged in
        // store old CURRENT_LOGIN in LAST_LOGIN and set CURRENT_LOGIN to start of session
        UserConfig::get($user->id)->store('LAST_LOGIN_TIMESTAMP', UserConfig::get($user->id)->CURRENT_LOGIN_TIMESTAMP);
        UserConfig::get($user->id)->store('CURRENT_LOGIN_TIMESTAMP', $_SESSION['SessionStart']);
        //find current semester and store it in $_SESSION['_default_sem']
        $current_sem = Semester::findByTimestamp(time() + get_config('SEMESTER_TIME_SWITCH') * 7 * 24 * 60 * 60);
        if (!$current_sem ) $current_sem = Semester::findCurrent();
        $_SESSION['_default_sem'] = $current_sem->semester_id;
        //redirect user to another page if he want to, redirect is deferred to allow plugins to catch the UserDidLogin notification
        if (UserConfig::get($user->id)->PERSONAL_STARTPAGE > 0 && $i_page == "index.php" && !$perm->have_perm("root")) {
            $seminar_open_redirected = TRUE;
        }
        $user_did_login = true;
    }
}

// init of output via I18N
$_language_path = init_i18n($_SESSION['_language']);
//force reload of config to get translated data
include 'config.inc.php';

// Try to select the course or institute given by the parameter 'cid'
// in the current request.

$course_id = (Request::int('cancel_login') && (!is_object($user) || $user->id === 'nobody'))
           ? null
           : Request::option('cid');

// Select the current course or institute if we got one from 'cid' or session.
// This also binds Context::getId()
// to the URL parameter 'cid' for all generated links.
if (isset($course_id)) {
    Context::set($course_id);
    unset($course_id);
}

if (Request::int('disable_plugins') !== null && ($user->id === 'nobody' || $perm->have_perm('root'))) {
    // deactivate non-core plugins
    PluginManager::getInstance()->setPluginsDisabled(Request::int('disable_plugins'));
}

// load the default set of plugins
PluginEngine::loadPlugins();

// add navigation item: add modules
if ((Navigation::hasItem('/course/admin') || $GLOBALS['perm']->have_perm('admin'))
    && ($perm->have_studip_perm('tutor', Context::getId()) && Context::isCourse())
    && (!Context::isCourse() || !$GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][Context::getArtNum()]['class']]['studygroup_mode'])) {
    $plus_nav = new Navigation(_('Mehr …'), 'dispatch.php/course/plus/index');
    $plus_nav->setDescription(_("Mehr Stud.IP-Funktionen für Ihre Veranstaltung"));
    Navigation::addItem('/course/modules', $plus_nav);
}

// add navigation item for profile: add modules
if (Navigation::hasItem('/profile/edit')) {
    $plus_nav = new Navigation(_('Mehr …'), 'dispatch.php/profilemodules/index');
    $plus_nav->setDescription(_("Mehr Stud.IP-Funktionen für Ihr Profil"));
    Navigation::addItem('/profile/modules', $plus_nav);
}

if ($user_did_login) {
    NotificationCenter::postNotification('UserDidLogin', $user->id);
}

if ($seminar_open_redirected) {
    startpage_redirect(UserConfig::get($user->id)->PERSONAL_STARTPAGE);
}

// Show terms on first login
if (is_object($GLOBALS['user'])
    && $GLOBALS['user']->needsToAcceptTerms()
    && !match_route('dispatch.php/terms'))
{
    if (!Request::isXhr()) {
        header('Location: ' . URLHelper::getURL('dispatch.php/terms', ['return_to' => $_SERVER['REQUEST_URI'], 'redirect_token' => Token::create(600)], true));
    } else {
        throw new Trails_Exception(400);
    }
    page_close();
    die;
}

if (Config::get()->USER_VISIBILITY_CHECK && is_object($GLOBALS['user']) && $GLOBALS['user']->id !== 'nobody') {
    require_once('lib/user_visible.inc.php');
    first_decision($GLOBALS['user']->id);
}
