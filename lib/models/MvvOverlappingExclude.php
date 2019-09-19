<?php

/**
 * MvvOverlappingExclude.class.php - model class for table mvv_ovl_excludes
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2018 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 */

class MvvOverlappingExclude extends SimpleORMap
{

    /**
     * Configures the model.
     * 
     * @param array  $config Configuration
     */
    protected static function configure($config = array()) {

        $config['db_table'] = 'mvv_ovl_excludes';
        $config['belongs_to']['selection'] = [
            'class_name'  => 'MvvOverlappingSelection',
            'foreign_key' => 'selection_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'course_id'
        ];
        
        parent::configure($config);
    }
    
}

