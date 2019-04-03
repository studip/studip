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


require '../lib/bootstrap.php';

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

$auth->login_if(!$perm->have_perm('root'));
$perm->check('root');

if (empty($_SESSION['_language'])) {
    $_SESSION['_language'] = get_accepted_languages();
}

$_language_path = init_i18n($_SESSION['_language']);

$path = $GLOBALS['STUDIP_BASE_PATH'] . '/db/migrations';
$verbose = true;
$target = NULL;

FileLock::setDirectory($GLOBALS['TMP_PATH']);
$lock = new FileLock('web-migrate');
if ($lock->isLocked() && Request::int('release_lock')) {
    $lock->release();
}

if (Request::int('target')) {
    $target = (int) Request::int('target');
}

$version = new DBSchemaVersion('studip');
$migrator = new Migrator($path, $version, $verbose);

if (Request::submitted('start')) {
    ob_start();
    set_time_limit(0);

    $lock->lock(['timestamp' => time(), 'user_id' => $GLOBALS['user']->id]);

    $migrator->migrate_to($target);

    $lock->release();

    $announcements = ob_get_clean();
    PageLayout::postSuccess(
        _('Die Datenbank wurde erfolgreich migriert.'),
        explode("\n", $announcements)
    );
}

$current = $version->get();
$migrations = $migrator->relevant_migrations($target);

PageLayout::setTitle(_('Stud.IP Web-Migrator'));
$widget = Sidebar::get()->addWidget(new SidebarWidget());
$widget->setTitle(_('Aktueller Versionsstand'));
$widget->addElement(new WidgetElement($current));

$template = $template_factory->open('web_migrate');
$template->current_page = _('Datenbank-Migration');
$template->target       = $target;
$template->migrations   = $migrations;
$template->lock         = $lock;
$template->set_layout($template_factory->open('layouts/base.php'));
echo $template->render();
