<?php
/**
 * StudiengangStgteil.php
 * Model class for the relation between Studiengang, Studiengangteil-Bezeichnung
 * and Studiengangteil (table mvv_stg_stgteil)
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

class StudiengangStgteil extends ModuleManagementModel
{
    
    private $stgteil_name;
    private $stgbez_name;
    private $stgbez_id;
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_stg_stgteil';
        
        $config['belongs_to']['studiengang'] = [
            'class_name' => 'Studiengang',
            'foreign_key' => 'studiengang_id'
        ];
        $config['has_one']['stgteil_bezeichnung'] = [
            'class_name' => 'StgteilBezeichnung',
            'foreign_key' => 'stgteil_bez_id'
        ];
        $config['belongs_to']['studiengangteil'] = [
            'class_name' => 'StudiengangTeil',
            'foreign_key' => 'stgteil_id'
        ];
        
        $config['additional_fields']['stgteil_name']['get'] =
            function($st) { return $st->stgteil_name; };
        $config['additional_fields']['stgbez_id']['get'] =
            function($st) { return $st->stgbez_id; };
        $config['additional_fields']['stgbez_name']['get'] =
            function($st) { return $st->stgbez_name; };
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        $this->default_values['position'] = 10000;
        parent::__construct($id);
    }
    
    /**
     * Retrieves the StudiengangStgteil and all related data and some
     * additional fields.
     * 
     * @param string $modul_id The id of the module.
     * @return object The module with additional data or a new module.
     */
    public static function getEnriched($id)
    {
        if (!is_array($id) && sizeof($id) != 2) {
            return false;
        }
        $stg_stgteil = parent::getEnrichedByQuery(
                'SELECT mss.*, CONCAT(mf.name, ": ", '
                . 'mst.zusatz, " (", mst.kp, " CP)") AS `stgteil_name`, '
                . 'msb.name AS `stgbez_name` '
                . 'FROM mvv_stg_stgteil mss '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'LEFT JOIN fach mf USING(fach_id) '
                . 'LEFT JOIN mvv_stgteil_bez msb USING(stgteil_bez_id) '
                . 'WHERE mss.studiengang_id = ? AND mss.stgteil_id = ?',
                [$id[0], $id[1]]);
        if (sizeof($stg_stgteil)) {
            return $stg_stgteil->find(join('_', $id));
        }
        return self::get();
    }
    
    /**
     * Retrieves all StudiengangStgteile by given Studiengangteil.
     * 
     * @param string $studiengang_id The id of the Studiengang.
     * @param string $sortby Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @return SimpleORMapCollection A collection of StudiengangStgteile.
     */
    public static function findByStudiengang($studiengang_id,
            $sort = 'position', $order = 'ASC')
    {
        $sortby = self::createSortStatement($sortby, $order, 'position',
                ['count_faecher']);
        return parent::getEnrichedByQuery('SELECT mst.*, msb.*, '
                . 'COUNT(fach_id) as `count_faecher` '
                . 'FROM mvv_stgteil mst '
                . 'LEFT JOIN mvv_stg_stgteil mss USING(stgteil_id) '
                . 'LEFT JOIN mvv_stgteil_bez msb USING(stgteil_bez_id) '
                . 'WHERE studiengang_id = ? '
                . 'GROUP BY mss.stgteil_id '
                . 'ORDER BY ' . $sort, [$studiengang_id]);
    }
    
    /**
     * Retrieves all StudiengangStgteile by given Studiengang and an optional
     * Studiengangteil-Bezeichnung if the Studiengang is a Mehrfach-Studiengang.
     * 
     * @param string $studiengang_id The id of a Studiengang.
     * @param string $bez_id The id of a Studiengangteil-Bezeichnung.
     * @return SimpleORMapCollection A collection of StudiengangStgteile.
     */
    public static function findByStudiengangStgteilBez($studiengang_id,
            $bez_id = null)
    {
        if ($bez_id) {
            return parent::getEnrichedByQuery('SELECT * FROM mvv_stg_stgteil WHERE '
                    . 'studiengang_id = ? AND stgteil_bez_id = ? '
                    . 'ORDER BY position, mkdate',
                    [$studiengang_id, $bez_id]);
        } else {
            return parent::getEnrichedByQuery('SELECT * FROM mvv_stg_stgteil WHERE '
                    . "studiengang_id = ? AND stgteil_bez_id = '' "
                    . 'ORDER BY position, mkdate',
                    [$studiengang_id]);
        }
    }

}
