<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * event_log.php - event logging admin model
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class EventLog
{
    /*
     * clean up old log events
     */
    function cleanup_log_events ()
    {
        return LogEvent::deleteExpired();
    }

    /**
     * get object types available for query
     */
    function get_object_types ()
    {
        return array(
            'course'    => _('Veranstaltung'),
            'institute' => _('Einrichtung'),
            'user'      => _('Nutzer/-in'),
            'resource'  => _('Ressource'),
            'other'     => _('Sonstige (von Aktion abhängig)')
        );
    }

    /**
     * find objects matching the given string
     */
    function find_objects ($type, $string, $action_name = null)
    {
        switch ($type) {
            case 'course':
                return StudipLog::searchSeminar(addslashes($string));
            case 'institute':
                return StudipLog::searchInstitute(addslashes($string));
            case 'user':
                return StudipLog::searchUser(addslashes($string));
            case 'resource':
                return StudipLog::searchResource(addslashes($string));
            case 'other':
                return StudipLog::searchObjectByAction($string, $action_name);
        }

        return NULL;
    }

    /**
     * build SQL query filter for selected action and object
     */
    private function sql_event_filter ($action_id, $object_id, &$parameters = array())
    {
        if (isset($action_id) && $action_id != 'all') {
            $filter[] = "action_id = :action_id";
            $parameters[':action_id'] = $action_id;
        }

        if (isset($object_id)) {
            $filter[] = "(:object_id IN (affected_range_id, coaffected_range_id, user_id))";
            $parameters[':object_id'] = $object_id;
        }

        return count($filter) ? join(' AND ', $filter) : '';
    }

    /**
     * count number of log events for selected action
     */
    function count_log_events ($action_id, $object_id)
    {
        $filter = $this->sql_event_filter($action_id, $object_id, $parameters);
        return LogEvent::countBySql($filter ?: '1', $parameters);
    }

    /**
     * get log events (max. 50) for selected action, starting at offset
     */
    function get_log_events ($action_id, $object_id, $offset)
    {
        $offset = (int)$offset;
        $filter = $this->sql_event_filter($action_id, $object_id, $parameters) ?: '1';

        $log_events = LogEvent::findBySQL($filter . " ORDER BY mkdate DESC LIMIT {$offset}, 50",
                $parameters);

        foreach ($log_events as $log_event) {
            $events[] = array(
                'time'   => $log_event->mkdate,
                'info'   => $log_event->formatEvent(),
                'detail' => $log_event->info,
                'debug'  => $log_event->dbg_info
            );
        }

        return $events;
    }

    /**
     * get list of all available log actions
     */
    function get_log_actions ()
    {
        $log_count = LogEvent::countByActions();
        $actions = LogAction::findBySQL('1 ORDER BY name');
        $log_actions = array();
        foreach ($actions as $action) {
            $log_actions[$action->getId()] = $action->toArray();
            $log_actions[$action->getId()]['log_count']
                    = (int) $log_count[$action->getId()];
        }

        return $log_actions;
    }

    /**
     * get all log actions with recorded events
     */
    function get_used_log_actions ()
    {
        return LogAction::getUsed();
    }

    /**
     * update log action in the database
     */
    function update_log_action ($action_id, $description, $info_template, $active, $expires)
    {
        if ($description === '') {
            throw new InvalidArgumentException(_('Keine Beschreibung angegeben.'));
        } else if ($info_template === '') {
            throw new InvalidArgumentException(_('Kein Info-Template angegeben.'));
        } else if ($expires < 0) {
            throw new InvalidArgumentException(_('Ablaufzeit darf nicht negativ sein.'));
        }
        
        $action = LogAction::find($action_id);
        if (!$action) {
            throw new InvalidArgumentException(_('Unbekannte Aktion.'));
        }
        
        $action->description = $description;
        $action->info_template = $info_template;
        $action->active = $active;
        $action->expires = $expires;
        $action->store();
    }
}
