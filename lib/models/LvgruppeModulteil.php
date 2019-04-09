<?php
/**
 * lvgruppe_modulteil.php
 * Model class for the relation between Lehrveranstaltungsgruppen and
 * Modulteilen (table mvv_lvgruppe_modulteil)
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

class LvgruppeModulteil extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_lvgruppe_modulteil';
    
        $config['belongs_to']['lvgruppe'] = [
            'class_name' => 'Lvgruppe',
            'foreign_key' => 'lvgruppe_id'
        ];
        $config['belongs_to']['modulteil'] = [
            'class_name' => 'Modulteil',
            'foreign_key' => 'modulteil_id'
        ];
        
        $config['alias_fields']['flexnow_id'] = 'fn_id';
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name =
                _('Zuordnung Lehrveranstaltungsgruppe zu Modulteil');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Zuordnung Lehrveranstaltungsgruppe zu Modulteil');
    }
    
    /**
     * Inherits the status of the parent modulteil.
     * 
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        $modulteil = Modulteil::find($this->modulteil_id);
        if ($modulteil) {
            return $modulteil->getStatus();
        }
        return parent::getStatus();
    }

}