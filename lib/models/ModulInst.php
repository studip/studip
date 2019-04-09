<?php
/**
 * ModulInst.php
 * Model class for assignments of institutes to modules
 * (table mvv_modul_inst)
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

class ModulInst extends ModuleManagementModel
{
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modul_inst';
    
        $config['belongs_to']['institute'] = [
            'class_name' => 'Fachbereich',
            'foreign_key' => 'institut_id'
        ];
        $config['belongs_to']['modul'] = [
            'class_name' => 'Modul',
            'foreign_key' => 'modul_id'
        ];
        
        parent::configure($config);
    }
    
    /**
     * Returns all asignments of institutes by given modul_id filteres by
     * optional parameter group.
     * 
     * @param string $modul_id The id of the Modul the institutes are assigned to
     * @param string $group Optional group
     * @return array Array of objects
     */
    public static function findByModul($modul_id, $group = null)
    {
        $params = is_null($group) ? [$modul_id]
                : [$modul_id, $group];
        $ret = [];
        $modul_insts = parent::getEnrichedByQuery('
            SELECT mmi.* 
            FROM mvv_modul_inst mmi 
            WHERE mmi.modul_id = ? '
                . (is_null($group) ? '' : 'AND gruppe = ? ') .
            ' ORDER BY `position`',
            $params
        );
        foreach ($modul_insts as $modul_inst) {
            $ret[$modul_inst->institut_id] = $modul_inst;
        }
        return $ret;
    }
    
    /**
     * Retrieves the primarily responsible institute of the given module.
     * 
     * @param string $modul_id The id of a module.
     * @return ModulInst
     */
    public static function findPrimarilyResponsibleInstitute($modul_id)
    {
        return self::findOneBySql(
            "modul_id = ? AND `gruppe` = 'hauptverantwortlich'",
            [$modul_id]
        );
    }
    
    /**
     * Retrieves other responsible institutes of the given module.
     * 
     * @param string $modul_id The id of a module.
     * @return array An array of ModulInst objects.
     */
    public static function findOtherResponsibleInstitutes($modul_id)
    {
        return self::findBySql(
            "modul_id = ? AND `gruppe` != 'hauptverantwortlich' ORDER BY `position`",
            [$modul_id]
        );
    }
    
    /**
     * Inherits the status of the parent module.
     * 
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        $modul = Modul::find($this->modul_id);
        if ($modul) {
            return $modul->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
}