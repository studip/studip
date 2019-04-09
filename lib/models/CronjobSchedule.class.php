<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CronjobSchedule.class.php
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

/**
 * CronjobSchedule - Model for the database table "cronjobs_schedules"
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 *
 * @property string schedule_id database column
 * @property string id alias column for schedule_id
 * @property string task_id database column
 * @property string active database column
 * @property string title database column
 * @property string description database column
 * @property string parameters database column
 * @property string priority database column
 * @property string type database column
 * @property string minute database column
 * @property string hour database column
 * @property string day database column
 * @property string month database column
 * @property string day_of_week database column
 * @property string next_execution database column
 * @property string last_execution database column
 * @property string last_result database column
 * @property string execution_count database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection logs has_many CronjobLog
 * @property CronjobTask task belongs_to CronjobTask
 */

class CronjobSchedule extends SimpleORMap
{
    const PRIORITY_LOW    = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';

    protected static function configure($config = [])
    {
        $config['db_table'] = 'cronjobs_schedules';

        $config['belongs_to']['task'] = [
            'class_name'  => 'CronjobTask',
            'foreign_key' => 'task_id',
        ];
        $config['has_many']['logs'] = [
            'class_name' => 'CronjobLog',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];

        $config['registered_callbacks']['before_store'][]     = 'cbJsonifyParameters';
        $config['registered_callbacks']['after_store'][]      = 'cbJsonifyParameters';
        $config['registered_callbacks']['after_initialize'][] = 'cbJsonifyParameters';

        parent::configure($config);
    }

    /**
     * Returns a mapped version of the priorities (key = priority value,
     * value = localized priority label).
     *
     * @return Array The mapped priorities
     */
    public static function getPriorities()
    {
        $mapping = [];
        $mapping[self::PRIORITY_LOW]    = _('niedrig');
        $mapping[self::PRIORITY_NORMAL] = _('normal');
        $mapping[self::PRIORITY_HIGH]   = _('hoch');

        return $mapping;
    }

    /**
     * Maps a priority value to it's localized label.
     *
     * @param  String $priority Priority value
     * @return String The localized label
     * @throws RuntimeException when an unknown priority value is passed
     */
    public static function describePriority($priority)
    {
        $mapping = self::getPriorities();

        if (!isset($mapping[$priority])) {
            throw new RuntimeException('Access to unknown priority "' . $priority . '"');
        }

        return $mapping[$priority];
    }

    /**
     * replaces title with task name if title is empty.
     *
     * @return string the title or the task name
     */
    public function getTitle()
    {
        return $this->content['title'] ?: $this->task->name;
    }

    protected function cbJsonifyParameters($type)
    {
        if ($type === 'before_store' && !is_string($this->parameters)) {
            $this->parameters = json_encode($this->parameters ?: null);
        }
        if (in_array($type, ['after_initialize', 'after_store']) && is_string($this->parameters)) {
            $this->parameters = json_decode($this->parameters, true) ?: [];
        }
    }

    /**
     * Stores the schedule in database. Will bail out with an exception if
     * the provided task does not exists. Will also nullify the title if it
     * matches the task name (see CronjobSchedule::getTitle()).
     *
     * @return CronjobSchedule Returns itself to allow chaining
     */
    public function store()
    {
        if ($this->task === null) {
            $message = sprintf('A task with the id "%s" does not exist.', $this->task_id);
            throw new InvalidArgumentException($message);
        }

        // Remove title if it is the default (task's name)
        if ($this->title === $this->task->name) {
            $this->title = null;
        }

        parent::store();

        return $this;
    }

    /**
     * Activates this schedule.
     *
     * @return CronjobSchedule Returns itself to allow chaining
     */
    public function activate()
    {
        $this->active         = 1;
        $this->next_execution = $this->calculateNextExecution();
        $this->store();

        return $this;
    }

    /**
     * Deactivates this schedule.
     *
     * @return CronjobSchedule Returns itself to allow chaining
     */
    public function deactivate()
    {
        $this->active = 0;
        $this->store();

        return $this;
    }

    /**
     * Executes this schedule.
     *
     * @param bool $force Pass true to force execution of  the schedule even
     *                    if it's not activated
     * @return mixed The result of the execution
     * @throws RuntimeException When either the schedule or the according is
     *                          not activated
     */
    public function execute($force = false)
    {
        if (!$force && !$this->active) {
            throw new RuntimeException('Execution aborted. Schedule is not active');
        }
        if (!$this->task->active) {
            throw new RuntimeException('Execution aborted. Associated task is not active');
        }

        $this->last_execution   = time();
        $this->execution_count += 1;
        $this->next_execution   = $this->calculateNextExecution();
        $this->store();

        $this->task->execution_count += 1;
        $this->task->store();

        $result = $this->task->engage($this->last_result, $this->parameters);

        if ($this->type === 'once') {
            $this->active = 0;
        }
        $this->last_result = $result;
        $this->store();

        return $result;
    }

    /**
     * Determines whether the schedule should execute given the provided
     * timestamp.
     *
     * @param mixed $now Defines the temporal fix point
     * @return bool Whether the schedule should execute or not.
     */
    public function shouldExecute($now = null)
    {
        return ($now ?: time()) >= $this->next_execution;
    }

    /**
     * Calculates the next execution for this schedule.
     *
     * For schedules of type 'once' the check solely tests whether the
     * timestamp has already passed and will return false in that case.
     * Otherwise the defined timestamp will be returned.
     *
     * For schedules of type 'periodic' the next execution
     * is calculated by increasing the current timestamp and testing
     * whether all conditions match. This is not the best method to test
     * and should be optimized sooner or later.
     *
     * @param mixed $now Defines the temporal fix point
     * @return int Timestamp of calculated next execution
     * @throws RuntimeException When calculation takes too long (you should
     *                          check the conditions for validity in that case)
     */
    public function calculateNextExecution($now = null)
    {
        $now = $now ?: time();

        if ($this->type === 'once') {
            return $now <= $this->next_execution
                ? $this->next_execution
                : false;
        }

        $result  = $now;
        $result -= $result % 60;

        $i = 366 * 24 * 60; // Maximum: A year
        $offset = 60;

        do {
            $result += $offset;

            // TODO: Performance - Adjust result according to conditions
            // See http://coderzone.org/library/PHP-PHP-Cron-Parser-Class_1084.htm
            $valid  = $this->testTimestamp($result, $this->minute, 'i')
                   && $this->testTimestamp($result, $this->hour, 'H')
                   && $this->testTimestamp($result, $this->day, 'd')
                   && $this->testTimestamp($result, $this->month, 'm')
                   && $this->testTimestamp($result, $this->day_of_week, 'N');

        } while (!$valid && $i-- > 0);

        if ($i <= 0) {
            throw new RuntimeException('No result, current: ' . date('d.m.Y H:i', $result));
        }

        $this->next_execution = $result;
        return $result;
    }

    /**
     * Tests a timestamp against the passed condition.
     *
     * @param int $timestamp The timestamp to test
     * @param mixed $condition Can be either null for "don't care", a positive
     *                         number for an exact moment or a negative number
     *                         for a repeating moment
     * @param String $format Format for date() to extract a portion of the
     *                       timestamp
     */
    protected function testTimestamp($timestamp, $condition, $format)
    {
        if ($condition === null) {
            return true;
        }

        $probe     = (int) date($format, $timestamp);
        $condition = (int) $condition;

        if ($condition < 0) {
            return ($probe % abs($condition)) === 0;
        }

        return $probe === $condition;
    }
}
