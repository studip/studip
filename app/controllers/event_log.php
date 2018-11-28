<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * event_log.php - event logging admin controller
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'app/models/event_log.php';

class EventLogController extends AuthenticatedController
{
    private $event_log;

    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory;

        parent::before_filter($action, $args);

        // user must have root permission
        $perm->check('root');

        // set page title and navigation
        if ($action === 'show') {
            PageLayout::setTitle(_('Anzeige der Log-Events'));
            Navigation::activateItem('/admin/log/show');
        } else {
            PageLayout::setTitle(_('Konfiguration der Logging-Funktionen'));
            Navigation::activateItem('/admin/log/admin');
        }

        $this->event_log = new EventLog();
    }

    /**
     * show and search log events
     */
    function show_action ()
    {
        $this->action_id = Request::option('action_id');
        $this->object_id = Request::option('object_id');
        $this->format = Request::option('format');
        $this->search = trim(Request::get('search'));
        $this->log_actions = $this->event_log->get_used_log_actions();
        $this->types = $this->event_log->get_object_types();

        // restrict log events to object scope
        if ($this->search !== '') {
            $this->type = Request::option('type');
            $objects = $this->event_log->find_objects($this->type, $this->search, $this->action_id);

            if (count($objects) > 0) {
                $this->objects = $objects;
            } else {
                $this->error_msg = _('Kein passendes Objekt gefunden.');
            }
        }

        // find all matching log events
        if ($this->search === '' || isset($this->object_id)) {
            $this->start = Request::int('start', 0);
            $this->num_entries =
                $this->event_log->count_log_events($this->action_id, $this->object_id);

            if (Request::submitted('back')) {
                $this->start = max(0, $this->start - 50);
            } else if (Request::submitted('forward') ) {
                $this->start = min($this->num_entries, $this->start + 50);
            }

            $this->log_events =
                $this->event_log->get_log_events($this->action_id, $this->object_id, $this->start);
        }
    }

    /**
     * configure log action
     */
    function admin_action ()
    {
        $this->log_actions = $this->event_log->get_log_actions();
    }

    /**
     * edit an existing log action
     */
    function edit_action ($action_id)
    {
        $this->edit_id = $action_id;
        $this->log_actions = $this->event_log->get_log_actions();
        $this->render_action('admin');
    }

    /**
     * save changes to a log action
     */
    function save_action ($action_id)
    {
        $description = Request::get('description');
        $info_template = Request::get('info_template');
        $active = Request::int('active', 0);
        $expires = Request::int('expires') * 86400;

        try {
            $this->event_log->update_log_action($action_id, $description,
                                                $info_template, $active, $expires);
        } catch (InvalidArgumentException $ex) {
            $this->error_msg = $ex->getMessage();
        }

        $this->log_actions = $this->event_log->get_log_actions();
        $this->render_action('admin');
    }
}
?>
