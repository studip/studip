<?php
# Lifter007: TEST

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require '../lib/bootstrap.php';

// set base url for URLHelper class
URLHelper::setBaseUrl($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']);

// initialize Stud.IP-Session
page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

try {
    require_once 'lib/seminar_open.php';

    // get plugin class from request
    $dispatch_to = isset($_SERVER['PATH_INFO']) ?$_SERVER['PATH_INFO'] : '';
    list($plugin_class, $unconsumed) = PluginEngine::routeRequest($dispatch_to);

    // retrieve corresponding plugin info
    $plugin_manager = PluginManager::getInstance();
    $plugin_info = $plugin_manager->getPluginInfo($plugin_class);

    // create an instance of the queried plugin
    $plugin = PluginEngine::getPlugin($plugin_class);

    // user is not permitted, show login screen
    if (is_null($plugin)) {
        // TODO (mlunzena) should not getPlugin throw this exception?
        throw new AccessDeniedException(_('Sie besitzen keine Rechte zum Aufruf dieses Plugins.'));
    }

    // set default page title
    PageLayout::setTitle($plugin->getPluginName());

    if (is_callable([$plugin, 'initialize'])) {
      $plugin->initialize();
    }

    // let the show begin
    $plugin->perform($unconsumed);
} catch (AccessDeniedException $ade) {
    global $auth;

    $auth->login_if($auth->auth['uid'] == 'nobody');
    throw $ade;
}

// close the page
page_close();
