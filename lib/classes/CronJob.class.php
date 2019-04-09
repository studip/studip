<?php
/**
 * CronJob - Abstract cronjob class. All cronjobs derive from this.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Cronjob.class.php
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

abstract class CronJob
{
    /**
     * Return the name of the cronjob.
     */
    abstract public static function getName();

    /**
     * Return the description of the cronjob.
     */
    abstract public static function getDescription();

    /**
     * Execute the cronjob.
     *
     * @param mixed $last_result What the last execution of this cronjob
     *                           returned.
     * @param Array $parameters Parameters for this cronjob instance which
     *                          were defined during scheduling.
     */
    abstract public function execute($last_result, $parameters = []);

    /**
     * Returns a list of available parameters for this cronjob.
     *
     * Each parameter is an entry in the resulting with a unique identifier
     * with the following array fields:
     *
     * - "type" which is one of the following:
     *   - boolean, a simple binary option
     *   - string, a single line of text
     *   - text, a multiline chunk of text
     *   - integer, a number
     *   - select, a defined set of values (define in the field "values" as
     *     an array)
     * - "default" provides a default value for this field (optional)
     * - "status" is either "optional" or "mandatory" (optional, defaults to
     *   optional)
     * - "description" provides a decription for this parameter
     *
     * Example:
     *
     * <code>
     * return array(
     *   'area' => array(
     *      'type'        => 'select',
     *      'values'      => array('seminar', 'institute', 'user'),
     *      'description' => 'Example parameter #1',
     *    ),
     *   'verbose' => array(
     *     'type'        => 'boolean',
     *     'default'     => false,
     *     'status'      => 'optional',
     *     'description' => 'Example parameter #2',
     *   ),
     * );
     * </code>
     *
     * @param Array List of paramters in the format described above.
     */
    public static function getParameters()
    {
        return [];
    }

    /**
     * Setup method.
     */
    public function setUp()
    {
    }

    /**
     * Teardown method.
     */
    public function tearDown()
    {
    }

// Convenience methods to ease the usage

    /**
     * Registers the cronjob and/or returns the corresponding task.
     *
     * @return CronjobTask Task for this cronjob
     */
    public static function register()
    {
        $class_name = get_called_class();
        $reflection = new ReflectionClass($class_name);

        $task_id = CronjobScheduler::getInstance()->registerTask($reflection->newInstance());

        return CronjobTask::find($task_id);
    }

    /**
     * Unregisters a previously registered task.
     *
     * @param String $task_id Id of the task to be unregistered
     * @return CronjobScheduler to allow chaining
     * @throws InvalidArgumentException when no task with the given id exists
     */
    public static function unregister()
    {
        $class_name = get_called_class();
        $task       = CronjobTask::findOneByClass($class_name);

        if ($task !== null) {
            CronjobScheduler::getInstance()->unregisterTask($task->id);
        }
    }

}
