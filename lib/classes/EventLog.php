<?php
/**
 * event_log.php - event logging admin model
 *
 * @author     Elmar Ludwig <ludwig@uos.de>
 * @copyright  2009 Authors
 * @license    GPL2 or any later version
 */
class EventLog
{
    /**
     * clean up old log events
     */
    public function cleanup_log_events()
    {
        return LogEvent::deleteExpired();
    }

    /**
     * get object types available for query
     */
    public function get_object_types()
    {
        return [
            'course'    => _('Veranstaltung'),
            'institute' => _('Einrichtung'),
            'user'      => _('Nutzer/-in'),
            'resource'  => _('Ressource'),
            'other'     => _('Sonstige (von Aktion abhängig)')
        ];
    }

    /**
     * find objects matching the given string
     */
    public function find_objects($type, $string, $action_name = null)
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
    private function sql_event_filter($action_id, $object_id, &$parameters = [])
    {
        $filter = [];
        if (isset($action_id) && $action_id != 'all') {
            $filter[] = "action_id = :action_id";
            $parameters[':action_id'] = $action_id;
        }

        if (isset($object_id)) {
            $filter[] = "(:object_id IN (affected_range_id, coaffected_range_id, user_id))";
            $parameters[':object_id'] = $object_id;
        }

        return count($filter) > 0 ? implode(' AND ', $filter) : '';
    }

    /**
     * count number of log events for selected action
     */
    public function count_log_events($action_id, $object_id)
    {
        $filter = $this->sql_event_filter($action_id, $object_id, $parameters);
        return LogEvent::countBySql($filter ?: '1', $parameters);
    }

    /**
     * get log events (max. 50) for selected action, starting at offset
     */
    public function get_log_events($action_id, $object_id, $offset)
    {
        $offset = (int) $offset;

        $filter  = $this->sql_event_filter($action_id, $object_id, $parameters) ?: '1';
        $filter .= " ORDER BY mkdate DESC, event_id DESC LIMIT {$offset}, 50";
        $log_events = LogEvent::findBySQL($filter, $parameters);

        foreach ($log_events as $log_event) {
            $events[] = [
                'time'   => $log_event->mkdate,
                'info'   => $log_event->formatEvent(),
                'detail' => $log_event->info,
                'debug'  => $log_event->dbg_info
            ];
        }

        return $events;
    }

    /**
     * get list of all available log actions
     */
    public function get_log_actions()
    {
        $log_count = LogEvent::countByActions();
        $actions = LogAction::findBySQL('1 ORDER BY name');
        $log_actions = [];
        foreach ($actions as $action) {
            $log_actions[$action->getId()] = $action->toArray();
            $log_actions[$action->getId()]['log_count']
                    = (int) $log_count[$action->getId()];
        }

        return $log_actions;
    }
}
