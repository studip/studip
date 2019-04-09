<?php
/**
 * AbschlussZuord.php
 * Model class for assignments of Abshluss-Kategorien to Abschluesse
 * (table mvv_abschl_zuord)
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

class AbschlussZuord extends ModuleManagementModel
{
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_abschl_zuord';
        
        $config['belongs_to']['abschluss'] = [
            'class_name' => 'Abschluss',
            'foreign_key' => 'abschluss_id'
        ];
        $config['belongs_to']['kategorie'] = [
            'class_name' => 'AbschlussKategorie',
            'foreign_key' => 'kategorie_id'
        ];
        
        parent::configure($config);
    }

}
