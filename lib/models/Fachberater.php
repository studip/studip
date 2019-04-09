<?php
/**
 * Fachberater.php
 * Model class for the assignment of users to Studiengangteile
 * (table mvv_fachberater)
 *
* This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class Fachberater extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_fachberater';
    
        $config['belongs_to']['studiengangteil'] = [
            'class_name' => 'Studiengangteil',
            'foreign_key' => 'stgteil_id'
        ];
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id'
        ];
        
        parent::configure($config);
    }
    
}