<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * web_migrate.php - web-based migrator for Stud.IP
 * Copyright (C) 2007  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require __DIR__ . '/../lib/bootstrap.php';

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

URLHelper::setBaseUrl($GLOBALS['ABSOLUTE_URI_STUDIP']);

if (empty($_SESSION['_language'])) {
    $_SESSION['_language'] = get_accepted_languages();
}

$_language_path = init_i18n($_SESSION['_language']);

$GLOBALS['template_factory'] = new Flexi_TemplateFactory('../templates/');

# get plugin class from request
$dispatch_to = $_SERVER['PATH_INFO'] ?: '';

$dispatcher = new Trails_Dispatcher( '../app', $_SERVER['SCRIPT_NAME'], 'web_migrate');
$dispatcher->dispatch("web_migrate/{$dispatch_to}");
