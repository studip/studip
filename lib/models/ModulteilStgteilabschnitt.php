<?php
/**
 * ModulteilStgteilabschnitt.php
 * Model class for the relation between Modulteile and
 * Studiengangteil-Abschnitte (table mvv_modulteil_stgteilabschnitt)
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

class ModulteilStgteilabschnitt extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modulteil_stgteilabschnitt';
    
        $config['belongs_to']['modulteil'] = [
            'class_name' => 'Modulteil',
            'foreign_key' => 'modulteil_id'
        ];
        $config['belongs_to']['abschnitt'] = [
            'class_name' => 'StgteilAbschnitt',
            'foreign_key' => 'abschnitt_id'
        ];
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name =
                _('Zuordnung Modulteil zu Studiengangteil-Abschnitt');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Zuordnung Modulteil zu Studiengangteil-Abschnitt');
    }
    
     /**
     * Inherits the status of the parent StgteilAbschnitt.
     * 
     * @return string the status of parent StgteilAbschnitt
     */
    public function getStatus()
    {
        $stgteil_abschnitt = StgteilAbschnitt::find($this->abschnitt_id);
        if ($stgteil_abschnitt) {
            return $stgteil_abschnitt->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }

}