<?php
/**
 * LvgruppeModulteil.php
 * Model class for the relation between Lehrveranstaltungsgruppen and Courses
 * (table mvv_lvgruppe_seminar)
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

class LvgruppeSeminar extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_lvgruppe_seminar';
    
        $config['belongs_to']['lvgruppe'] = [
            'class_name' => 'Lvgruppe',
            'foreign_key' => 'lvgruppe_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'seminar_id'
        ];
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name =
                _('Zuordnung Veranstaltung zu Lehrveranstaltungsgruppe');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Zuordnung Veranstaltung zu Lehrveranstaltungsgruppe');
    }

}