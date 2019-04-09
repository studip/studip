<?php
/**
 * AbschlussKategorie.php
 * Model class for Abschluss-Kategorien (table mvv_abschl_kategorie)
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

class AbschlussKategorie extends ModuleManagementModelTreeItem
{
    
    /**
     * Number of Abschluesse this Kategorie is assigned to.
     * @var int
     */
    private $count_abschluesse;
    
    /**
     * Number of Studiengaenge this Kategorie is assigned to through the used
     * Studiengangteile
     * @var int
     */
    private $count_studiengaenge;
    
    /**
     * Number of assigned Documents
     * @var int
     */
    private $count_dokumente;
    
    /**
     * Number of Studiengaenge (used in the search functions)
     * @var int
     */
    private $count_objects;
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_abschl_kategorie';
        
        $config['has_many']['documents'] = [
            'class_name' => 'MvvDokument',
            'assoc_func' => 'findByObject',
            'assoc_func_params_func' => function ($ak) { return $ak; }
        ];
        $config['has_many']['document_assignments'] = [
            'class_name' => 'MvvDokumentZuord',
            'assoc_foreign_key' => 'range_id',
            'order_by' => 'ORDER BY position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_and_belongs_to_many']['abschluesse'] = [
            'class_name' => 'Abschluss',
            'thru_table' => 'mvv_abschl_zuord',
            'thru_key' => 'kategorie_id',
            'thru_assoc_key' => 'abschluss_id',
            'order_by' => 'ORDER BY position'
        ];
        $config['has_many']['abschluss_assignments'] = [
            'class_name' => 'AbschlussZuord',
            'assoc_foreign_key' => 'kategorie_id',
            'on_delete' => 'delete'
        ];
        
        $config['additional_fields']['count_abschluesse']['get'] =
            function($ak) { return $ak->count_abschluesse; };
        $config['additional_fields']['count_studiengaenge']['get'] =
            function($ak) { return $ak->count_studiengaenge; };
        $config['additional_fields']['count_dokumente']['get'] =
            function($ak) { return $ak->count_dokumente; };
        $config['additional_fields']['count_objects']['get'] =
            function($ak) { return $ak->count_objects; };
        
        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['name_kurz'] = true;
        $config['i18n_fields']['beschreibung'] = true;
            
        parent::configure($config);
    }
    
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Abschluss-Kategorie');
    }
    
    /**
     * Returns the Abschluss-Kategorie for a given Abschluss.
     *
     * @param string $abschluss_id 
     */
    public static function findByAbschluss($abschluss_id)
    {
        $db = DBManager::get();
        $stmt = $db->prepare('SELECT kategorie_id FROM mvv_abschl_zuord '
                . 'WHERE abschluss_id = ?');
        $stmt->execute([$abschluss_id]);
        $kategorie_id = $stmt->fetch(PDO::FETCH_COLUMN, 0);
        return new AbschlussKategorie($kategorie_id);
    }
    
    /**
     * Returns all or a specified (by row count and offset) number of
     * Abschluss-Kategorien sorted and filtered by given parameters and enriched
     * with some additional fields. This function is mainly used in the list view.
     * 
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return object A SimpleORMapCollection of Abschluss objects.
     */
    public static function getAllEnriched($sortby = 'position', $order = 'ASC',
            $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement(
            $sortby,
            $order,
            'position',
            ['count_abschluesse', 'count_dokumente', 'count_studiengaenge']
        );
        return parent::getEnrichedByQuery("
            SELECT mvv_abschl_kategorie.*, 
                COUNT(DISTINCT mvv_abschl_zuord.abschluss_id) AS `count_abschluesse`, 
                COUNT(DISTINCT mvv_dokument_zuord.dokument_id) AS `count_dokumente`, 
                COUNT(DISTINCT mvv_studiengang.studiengang_id) AS `count_studiengaenge` 
            FROM mvv_abschl_kategorie 
                LEFT JOIN mvv_abschl_zuord USING (kategorie_id) 
                LEFT JOIN mvv_dokument_zuord ON (mvv_dokument_zuord.range_id = mvv_abschl_kategorie.kategorie_id 
                    AND mvv_dokument_zuord.object_type = '" . get_class() . "') 
                LEFT JOIN mvv_studiengang ON mvv_studiengang.abschluss_id = mvv_abschl_zuord.abschluss_id 
            " . self::getFilterSql($filter, true) . "
            GROUP BY kategorie_id 
            ORDER BY " . $sortby,
            [], 
            $row_count, 
            $offset
        );
    }
    
    /**
     * Finds all kategorien assigned to abschluesse used by studiengaenge.
     * 
     * @return array Array of objects or empty array 
     */
    public static function findUsed()
    {
        return parent::getEnrichedByQuery('
            SELECT mak.* 
            FROM mvv_abschl_kategorie AS mak 
                INNER JOIN mvv_abschl_zuord USING (kategorie_id) 
                INNER JOIN mvv_studiengang USING (abschluss_id) 
            ORDER BY name
        ');
    }

    /**
     * Finds all kategorien assigned to abschluesse used by studiengaenge.
     * Can be filtered by fachbereich.
     * 
     * @param string $fachbereich_id The id of the fachbereich
     * @return array Array of objects or empty array 
     */
    public static function findByFachbereich($fachbereich_id)
    {
        return parent::getEnrichedByQuery('
            SELECT mak.* 
            FROM mvv_abschl_kategorie mak 
                INNER JOIN mvv_abschl_zuord USING (kategorie_id) 
                INNER JOIN mvv_studiengang USING (abschluss_id) 
                INNER JOIN mvv_stg_stgteil USING (studiengang_id) 
                INNER JOIN mvv_stgteil USING (stgteil_id) 
                INNER JOIN mvv_fach_inst mfi USING (fach_id) 
            WHERE mfi.institut_id = ? 
            ORDER BY mak.position',
            [$fachbereich_id]
        );
    }
    
    /**
     * Returns all Kategorien implicitly assigned (through
     * Studiengangteile) to given Studiengange.
     * 
     * @param array $studiengang_ids Array of Studiengang ids.
     * @return object SimpleORMapCollection of Kategorien.
     */
    public static function findByStudiengaenge($studiengang_ids = [])
    {
        return parent::getEnrichedByQuery('
            SELECT mak.*, 
                COUNT(studiengang_id) AS count_objects 
            FROM mvv_abschl_kategorie AS mak 
                INNER JOIN mvv_abschl_zuord USING (kategorie_id) 
                INNER JOIN mvv_studiengang USING (abschluss_id) 
                ' . self::getFilterSql(['mvv_studiengang.studiengang_id' => $studiengang_ids], true) . '
            GROUP BY mak.kategorie_id 
            ORDER BY mak.name ASC
        ');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Abschluss-Kategorie');
    }
    
    /**
     * Assigns this Abschluss-Kategorie to an Abschluss
     * 
     * @param type $abschluss_id The ID of the Abschluss
     */
    public function assign($abschluss_id)
    {
        if (!$this->isNew()) {
            $stmt = DBManager::get()->prepare('
                INSERT INTO mvv_abschl_zuord (abschluss_id, kategorie_id) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE kategorie_id = ?
            ');
            $stmt->execute([$abschluss_id, $this->getId(), $this->getId()]);
        }
    }
    
    /**
     * Returns all Studiengaenge assigned to this Kategorie.
     * 
     * @return object A SimpleORMapCollection of Studiengaenge.
     */
    public function getStudiengaenge()
    {
        return Studiengang::findByAbschlussKategorie($this->getId());
    }
    
    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = [];
            $rejected = false;
            if ($this->isNew() || $this->isDirty()) {
                // The name of the Abschluss-Kategorie must be longer than 4 characters
                if (mb_strlen($this->name) < 4) {
                    $ret['name'] = true;
                    $messages[] = _('Der Name der Abschluss-Kategorie ist zu kurz (mindestens 4 Zeichen).');
                    $rejected = true;
                }
                // The name of the Abschluss-Kategorie has to be unique
                $existing = AbschlussKategorie::findOneBySQL('name = ?', [trim($this->name)]);
                if ($existing && $existing->getId() != $this->getId()) {
                    $ret['name'] = true;
                    $messages[] = sprintf(_('Es existiert bereits eine Abschluss-Kategorie mit dem Namen "%s"!'),
                            $this->name);
                    $rejected = true;
                }
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return 'root';
    }
    
    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent() 
    {
        return new MvvTreeRoot();
    }
    
    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [];
    }
    
    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/StgteilVersion/trail_parent_id'] =  $this->getId();
        $trail_parent_id = $_SESSION['MVV/AbschlussKategorie/trail_parent_id'];
        
        $start_sem = self::$object_filter['StgteilVersion']['start_semester'];
        $end_sem = self::$object_filter['StgteilVersion']['end_semester'];
        return StgteilVersion::getEnrichedByQuery("
            SELECT msv.* 
            FROM mvv_abschl_zuord maz 
                INNER JOIN mvv_studiengang ms ON (
                    maz.abschluss_id = ms.abschluss_id 
                    AND maz.kategorie_id = :kategorie_id ) 
                INNER JOIN Institute AS `ins` ON ms.institut_id = `ins`.Institut_id 
                INNER JOIN mvv_stg_stgteil USING(studiengang_id) 
                INNER JOIN mvv_stgteilversion AS msv USING(stgteil_id)
                LEFT JOIN semester_data AS startsem ON msv.start_sem = startsem.semester_id 
                LEFT JOIN semester_data AS endsem ON msv.end_sem = endsem.semester_id 
            WHERE (`ins`.Institut_id = :parent_id OR `ins`.fakultaets_id = :parent_id) 
                AND (
                    (ISNULL(NULLIF(msv.end_sem, '')) AND (startsem.beginn <= :sem_end)) 
                    OR (:sem_begin BETWEEN startsem.beginn AND endsem.ende) 
                    OR (startsem.beginn BETWEEN :sem_begin AND :sem_end)
                ) 
            ORDER BY ms.name, startsem.beginn",
            [':kategorie_id' => $this->getId(),
                ':parent_id' => $trail_parent_id,
                ':sem_begin' => ($start_sem ? $start_sem->beginn : 0),
                ':sem_end' => ($end_sem ? $end_sem->ende : PHP_INT_MAX)]);
        
    }
    
}
