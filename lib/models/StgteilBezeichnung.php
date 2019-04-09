<?php
/**
 * StgteilBezeichnung.php
 * Model class for Studiengangteil-Bezeichnungen (table mvv_stgteil_bez)
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

class StgteilBezeichnung extends ModuleManagementModel
{
    
    private $count_stgteile;
    private $count_studiengaenge;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_stgteil_bez';
        
        $config['additional_fields']['count_stgteile']['get'] =
            function($stg_bez) { return $stg_bez->count_stgteile; };
        $config['additional_fields']['count_stgteile']['set'] = false;
        $config['additional_fields']['count_studiengaenge']['get'] =
            function($stg_bez) { return $stg_bez->count_studiengaenge; };
        $config['additional_fields']['count_studiengaenge']['set'] = false;
        
        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['name_kurz'] = true;
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Studiengangteil-Bezeichnung');
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Studiengangteil-Bezeichnungen sorted by given parameters and enriched
     * with some additional fields. This function is mainly used
     * in the list view.
     * 
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return SimpleORMapCollection A collection of Studiengangteil-
     * Bezeichnungen.
     */
    public static function getAllEnriched($sortby = 'position', $order = 'ASC',
            $row_count = null, $offset = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'position',
                ['count_studiengaenge', 'count_stgteile']);
        return parent::getEnrichedByQuery('
            SELECT msb.*, 
                COUNT(DISTINCT studiengang_id) AS `count_studiengaenge`, 
                COUNT(DISTINCT stgteil_id) AS `count_stgteile` 
            FROM mvv_stgteil_bez AS msb 
                LEFT JOIN mvv_stg_stgteil USING (stgteil_bez_id) 
                LEFT JOIN mvv_studiengang USING (studiengang_id) 
            GROUP BY stgteil_bez_id 
            ORDER BY ' . $sortby,
            [],
            $row_count,
            $offset
        );
    }
    
    /**
     * Returns all Studiengangteil-Bezeichnungen ordered by position.
     * 
     * @return SimpleORMapCollection A collection of Studiengangteil-
     * Bezeichnungen.
     */
    public static function getAllSorted()
    {
        return parent::getEnrichedByQuery('
            SELECT * FROM mvv_stgteil_bez 
            ORDER BY position
        ');
    }
    
    /**
     * Retrieves all Studienganteil-Bezeichnungen used by given Studiengang.
     * 
     * @param type $studiengang_id The id of a Studiengang.
     * @return SimpleORMapCollection A collection of Studiengangteil-
     * Bezeichnungen.
     */
    public static function findByStudiengang($studiengang_id)
    {
        return parent::getEnrichedByQuery('
            SELECT msb.*, 
                COUNT(DISTINCT stgteil_id) AS `count_stgteile` 
            FROM mvv_stgteil_bez msb 
                LEFT JOIN mvv_stg_stgteil mss USING(stgteil_bez_id) 
            WHERE mss.studiengang_id = ? 
            GROUP BY stgteil_bez_id 
            ORDER BY position, mkdate',
            [$studiengang_id]
        );
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Studiengangteil-Bezeichnung');
    }
    
    public function validate()
    {
        $ret = parent::validate();
        $messages = [];
        $rejected = false;

        // The name must not be empty
        if (!trim($this->name)) {
            $ret['name'] = true;
            $messages[] = _('Der Name der Studiengangteil-Bezeichnung darf nicht leer sein.');
            $rejected = true;
        } else {
            if ($this->isNew()) {
                // The name has to be unique
                $existing = $this->findBySql('name = ' . DBManager::get()->quote($this->name));
                if (sizeof($existing)) {
                    $ret['name'] = true;
                    $messages[] = sprintf(
                        _('Es existiert bereits eine Studiengangteil-Bezeichnung mit dem Namen "%s"!'),
                        $this->name
                    );
                    $rejected = true;
                }
            }
        }
        if ($rejected) {
            throw new InvalidValuesException(join("\n", $messages), $ret);
        }
        return $ret;
    }
    
}
