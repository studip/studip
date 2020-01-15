<?php

/**
 * ResourceRequestAppointment.class.php - Contains a model class for
 * the resource_request_appointments table.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 */


/**
 * ResourceRequestAppointment is a model class to connect
 * resource requests to CourseDate objects.
 *
 * @property string id database column
 * @property string request_id database column
 * @property string appointment_id database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property ResourceRequest resource_request belongs_to ResourceRequest
 * @property CourseDate appointment belongs_to CourseDate
 *
 */
class ResourceRequestAppointment extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_request_appointments';
        
        $config['belongs_to']['resource_request'] = [
            'class_name'  => 'ResourceRequest',
            'foreign_key' => 'request_id',
            'assoc_func'  => 'find'
        ];
        $config['belongs_to']['appointment']      = [
            'class_name'  => 'CourseDate',
            'foreign_key' => 'appointment_id',
            'assoc_func'  => 'find'
        ];
        
        parent::configure($config);
    }
}
