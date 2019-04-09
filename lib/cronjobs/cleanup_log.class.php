<?php
/**
 * cleanup_log.php - Clean up event and cronjob logs
 *
 * @author Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @access public
 * @since  2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// cleanup_log.class.php
//
// Copyright (C) 2013 Jan-Hendrik Willms <tleilax+studip@gmail.com>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once 'lib/classes/CronJob.class.php';

class CleanupLogJob extends CronJob
{
    /**
     * Returns the name of the cronjob.
     */
    public static function getName()
    {
        return _('Logs aufräumen');
    }

    /**
     * Returns the description of the cronjob.
     */
    public static function getDescription()
    {
        return _('Entfernt abgelaufene Log-Einträge sowohl für das '
                .'Eventsystem als auch für die Cronjobs');
    }

    /**
     * Return the paremeters for this cronjob.
     *
     * @return Array Parameters.
     */
    public static function getParameters()
    {
        return [
            'cronjobs' => [
                'type'        => 'boolean',
                'default'     => true,
                'status'      => 'optional',
                'description' => _('Sollen die Logeinträge für Cronjobs auch gelöscht werden'),
            ],
            'cronjobs-success' => [
                'type'        => 'integer',
                'default'     => 7,
                'status'      => 'optional',
                'description' => _('Nach wievielen Tagen sollen Logeinträge für '
                                  .'erfolgreiche Cronjobs gelöscht werden (0 für nie)'),
            ],
            'cronjobs-error' => [
                'type'        => 'integer',
                'default'     => 28,
                'status'      => 'optional',
                'description' => _('Nach wievielen Tagen sollen Logeinträge für '
                                  .'fehlgeschlagene Cronjobs gelöscht werden (0 für nie)'),
            ],
        ];
    }

    /**
     * Setup method. Loads the neccessary classes.
     */
    public function setUp()
    {
        require_once 'app/models/event_log.php';
    }

    /**
     * Executes the cronjob.
     *
     * @param mixed $last_result What the last execution of this cronjob
     *                           returned.
     * @param Array $parameters Parameters for this cronjob instance which
     *                          were defined during scheduling.
     */
    public function execute($last_result, $parameters = [])
    {
        $event_log = new EventLog();
        $event_log->cleanup_log_events();

        if (!empty($parameters['cronjobs'])) {
            $delete = function($l) {$l->delete();};
            if ($parameters['cronjobs-error'] > 0) {
                CronjobLog::findEachBySql(
                    $delete,
                    "exception IS NOT NULL AND executed + ? < UNIX_TIMESTAMP()",
                    [$parameters['cronjobs-error'] * 24 * 60 * 60]
                );
            }
            if ($parameters['cronjobs-success'] > 0) {
                CronjobLog::findEachBySql(
                    $delete,
                    "exception IS NULL AND executed + ? < UNIX_TIMESTAMP()",
                    [$parameters['cronjobs-success'] * 24 * 60 * 60]
                );
            }
        }
    }
}

