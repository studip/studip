<?php
/**
 * CronjobScheduler - Scheduler for the cronjobs.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CronjobScheduler.class.php
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

class CronjobScheduler
{
    protected static $instance = null;

    /**
     * Returns the scheduler object. Implements the singleton pattern to
     * ensure that only one scheduler exists.
     *
     * @return CronjobScheduler The scheduler object
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected $lock;

    /**
     * Private constructor to ensure the singleton pattern is used correctly.
     */
    private function __construct()
    {
        FileLock::setDirectory($GLOBALS['TMP_PATH']);
        $this->lock = new FileLock('studip-cronjob');
    }

    /**
     * Registers a new executable task.
     *
     * @param mixed $task Either path of the task class filename (relative
     *                    to Stud.IP root) or an instance of CronJob
     * @param bool   $active Indicates whether the task should be set active
     *                       or not
     * @return String Id of the created task
     * @throws InvalidArgumentException when the task class file does not
     *         exist
     * @throws RuntimeException when task has already been registered
     */
    public function registerTask($task, $active = true)
    {
        if (is_object($task)) {
            $reflection = new ReflectionClass($task);
            $class = $reflection->getName();
            $class_filename = studip_relative_path($reflection->getFileName());
        } else {
            $filename = $GLOBALS['STUDIP_BASE_PATH'] . '/' . $task;
            if (!file_exists($filename)) {
                $message = sprintf('Task class file "%s" does not exist.', $task);
                throw new InvalidArgumentException($message);
            }
            $class_filename = $task;

            $classes = get_declared_classes();
            require_once $filename;
            $class = end(array_diff(get_declared_classes(), $classes));

            if (empty($class)) {
                throw new RuntimeException('No class was defined in file.');
            }

            $reflection = new ReflectionClass($class);
        }

        if (!$reflection->isSubclassOf('CronJob')) {
            $message = sprintf('Job class "%s" (defined in %s) does not extend the abstract CronJob class.', $class, $filename);
            throw new RuntimeException($message);
        }

        if ($task = CronjobTask::findOneByClass($class)) {
            return $task->task_id;
        }

        $task = new CronjobTask();
        $task->filename = $class_filename;
        $task->class    = $class;
        $task->active   = (int)$active;
        $task->store();

        return $task->task_id;
    }

    /**
     * Unregisters a previously registered task.
     *
     * @param String $task_id Id of the task to be unregistered
     * @return CronjobScheduler to allow chaining
     * @throws InvalidArgumentException when no task with the given id exists
     */
    public function unregisterTask($task_id)
    {
        $task = CronjobTask::find($task_id);
        if ($task === null) {
            $message = sprintf('A task with the id "%s" does not exist.', $task_id);
            throw new InvalidArgumentException($message);
        }
        $task->delete();

        return $this;
    }

    /**
     * Schedules a task for a single execution at the provided time.
     *
     * @param String $task_id    The id of the task to be executed
     * @param int    $timestamp  When the task should be executed
     * @param String $priority   Priority of the execution (low, normal, high),
     *                           defaults to normal
     * @param Array  $parameters Optional parameters passed to the task
     * @return CronjobSchedule The generated schedule object.
     */
    public function scheduleOnce($task_id, $timestamp, $priority = CronjobSchedule::PRIORITY_NORMAL,
                                 $parameters = [])
    {
        $schedule = new CronjobSchedule();
        $schedule->type           = 'once';
        $schedule->task_id        = $task_id;
        $schedule->parameters     = $parameters;
        $schedule->priority       = $priority;
        $schedule->next_execution = $timestamp;

        $schedule->store();

        $task = $schedule->task;
        $task->assigned_count += 1;
        $task->store();

        return $schedule;
    }

    /**
     * Schedules a task for periodic execution with the provided schedule.
     *
     * @param String $task_id     The id of the task to be executed
     * @param mixed  $minute      Minute part of the schedule:
     *                            - null for "every minute" a.k.a. "don't care"
     *                            - x < 0 for "every x minutes"
     *                            - x >= 0 for "only at minute x"
     * @param mixed  $hour        Hour part of the schedule:
     *                            - null for "every hour" a.k.a. "don't care"
     *                            - x < 0 for "every x hours"
     *                            - x >= 0 for "only at hour x"
     * @param mixed  $day         Day part of the schedule:
     *                            - null for "every day" a.k.a. "don't care"
     *                            - x < 0 for "every x days"
     *                            - x > 0 for "only at day x"
     * @param mixed  $month       Month part of the schedule:
     *                            - null for "every month" a.k.a. "don't care"
     *                            - x < 0 for "every x months"
     *                            - x > 0 for "only at month x"
     * @param mixed  $day_of_week Day of week part of the schedule:
     *                            - null for "every day" a.k.a. "don't care"
     *                            - 1 >= x >= 7 for "exactly at day of week x"
     *                              (x starts with monday at 1 and ends with
     *                               sunday at 7)
     * @param String $priority   Priority of the execution (low, normal, high),
     *                           defaults to normal
     * @param Array  $parameters Optional parameters passed to the task
     * @return CronjobSchedule The generated schedule object.
     */
    public function schedulePeriodic($task_id, $minute = null, $hour = null,
                                     $day = null, $month = null, $day_of_week = null,
                                     $priority = CronjobSchedule::PRIORITY_NORMAL,
                                     $parameters = [])
    {
        $schedule = new CronjobSchedule();
        $schedule->type       = 'periodic';
        $schedule->task_id    = $task_id;
        $schedule->parameters = $parameters;
        $schedule->priority   = $priority;

        $schedule->minute = $minute;
        $schedule->hour = $hour;
        $schedule->day = $day;
        $schedule->month = $month;
        $schedule->day_of_week = $day_of_week;

        $schedule->store();

        $task = $schedule->task;
        $task->assigned_count += 1;
        $task->store();

        return $schedule;
    }

    /**
     * Cancels the provided schedule.
     *
     * @param String $schedule_id Id of the schedule to be canceled
     */
    public function cancel($schedule_id)
    {
        CronjobSchedule::find($schedule_id)->delete();
    }

    /**
     * Cancels all schedules of the provided task.
     *
     * @param String $task_id Id of the task which schedules shall be canceled
     */
    public function cancelByTask($task_id)
    {
        $schedules = CronjobSchedule::findByTask_id($task_id);
        foreach ($schedules as $schedule) {
            $schedule->delete();
        }
    }

    /**
     * Executes the available schedules if they are to be executed.
     * This method can only be run once - even if one execution takes more
     * than planned. This is ensured by a locking mechanism.
     */
    public function run()
    {
        if (!Config::get()->CRONJOBS_ENABLE) {
            return;
        }

        $escalation_time = Config::get()->CRONJOBS_ESCALATION;

        // Check whether a previous cronjob worker is still running.
        if ($this->lock->isLocked($data)) {
            // Running but not yet escalated -> let it run
            if ($data['timestamp'] + $escalation_time > time()) {
                return;
            }

            // Load locked schedule
            $schedule = CronjobSchedule::find($data['schedule_id']);

            // If we discovered a deadlock release it
            if ($schedule) {
                // Deactivate schedule
                $schedule->deactivate();

                // Adjust log
                $log = CronjobLog::find($data['log_id']);
                if ($log) {
                    $log->duration  = time() - $data['timestamp'];
                    $log->exception = new Exception('Cronjob has escalated');
                    $log->store();
                }

                // Inform roots about the escalated cronjob
                $subject = sprintf('[Cronjobs] %s: %s',
                                   _('Eskalierte Ausführung'),
                                   $schedule->title);

                $message = sprintf(_('Der Cronjob "%s" wurde deaktiviert, da '
                                    .'seine Ausführungsdauer die maximale '
                                    .'Ausführungszeit von %u Sekunden '
                                    .'überschritten hat.') . "\n",
                                   $schedule->title,
                                   $escalation_time);

                $this->sendMailToRoots($subject, $message);
            }

            // Release lock
            $this->lock->release();
        }

        // Find all schedules that are due to execute and which task is active
        $temp = CronjobSchedule::findBySQL('active = 1 AND next_execution <= UNIX_TIMESTAMP() '
                                          .'ORDER BY priority DESC, next_execution ASC');
#        $temp = SimpleORMapCollection::createFromArray($temp);
        $schedules = array_filter($temp, function ($schedule) { return $schedule->task->active; });

        if (count($schedules) === 0) {
            return;
        }

        foreach ($schedules as $schedule) {
            $log = new CronjobLog();
            $log->schedule_id = $schedule->schedule_id;
            $log->scheduled   = $schedule->next_execution;
            $log->executed    = time();
            $log->exception   = null;
            $log->duration    = -1;
            $log->store();

            set_time_limit($escalation_time);

            // Activate the file lock and store the current timestamp,
            // schedule id and according log id in it
            $this->lock->lock([
                'schedule_id' => $schedule->schedule_id,
                'log_id'      => $log->log_id,
            ]);

            // Start capturing output and measuring duration
            ob_start();
            $start_time = microtime(true);

            try {
                $schedule->execute();
            } catch (Exception $e) {
                $log->exception = $e;

                // Deactivate schedule
                $schedule->deactivate();

                // Send mail to root accounts
                $subject = sprintf('[Cronjobs] %s: %s',
                                   _('Fehlerhafte Ausführung'),
                                   $schedule->title);

                $message = sprintf(_('Der Cronjob "%s" wurde deaktiviert, da bei der Ausführung ein Fehler aufgetreten ist.'), $schedule->title) . "\n";
                $message .= "\n";
                $message .= display_exception($e) . "\n";

                $message .= _('Für weiterführende Informationen klicken Sie bitten den folgenden Link:') . "\n";

                $old = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
                $message .= URLHelper::getURL('dispatch.php/admin/cronjobs/logs/schedule/' . $schedule->schedule_id);
                URLHelper::setBaseURL($old);

                $this->sendMailToRoots($subject, $message);
            }

            // Actually capture output and duration
            $end_time = microtime(true);
            $output = ob_get_clean();

            // Complete log
            $log->output    = $output;
            $log->duration  = $end_time - $start_time;
            $log->store();
        }

        // Release lock
        $this->lock->release();
    }

    /**
     * Sends an internal mail with the provided subject and message to all
     * users with a global permission of "root".
     *
     * @param String $subject The subject of the message
     * @param String $message The message itself
     */
    private function sendMailToRoots($subject, $message)
    {
        $temp  = User::findByPerms('root');
        $roots = SimpleORMapCollection::createFromArray($temp)
            ->filter(function($r) { return $r->locked == 0; })
            ->pluck('username');

        $msging = new messaging;
        $msging->insert_message($message, $roots, '____%system%____', null, null, null, null, $subject, false, 'high');
    }
}
