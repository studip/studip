<?php
/**
 * Fach.php
 * Model class for Faecher (table fach)
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

class Fach extends ModuleManagementModelTreeItem implements PrivacyObject
{

    private $count_abschluesse;
    private $count_user;
    private $count_sem;
    private $count_stgteile;
    private $count_module;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'fach';

        $config['has_many']['abschluesse'] = [
            'class_name' => 'Abschluss',
            'assoc_func' => 'findByFach'
        ];
        $config['has_many']['studiengangteile'] = [
            'class_name' => 'StudiengangTeil',
            'assoc_foreign_key' => 'fach_id'
        ];
        $config['has_and_belongs_to_many']['departments'] = [
            'class_name' => 'Fachbereich',
            'thru_table' => 'mvv_fach_inst',
            'thru_key' => 'fach_id',
            'thru_assoc_key' => 'institut_id',
            'order_by' => 'ORDER BY position'
        ];
        $config['has_and_belongs_to_many']['degrees'] = [
            'class_name' => 'Abschluss',
            'thru_table' => 'user_studiengang',
            'thru_key' => 'fach_id',
            'thru_assoc_key' => 'abschluss_id',
            'order_by' => 'GROUP BY abschluss_id ORDER BY name'
        ];
        $config['has_many']['department_assignments'] = [
            'class_name' => 'FachFachbereich',
            'assoc_foreign_key' => 'fach_id',
            'order_by' => 'ORDER BY position',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        $config['additional_fields']['count_abschluesse']['get'] =
            function($fach) { return $fach->count_abschluesse; };
        $config['additional_fields']['count_abschluesse']['set'] = false;
        $config['additional_fields']['count_user']['get'] =
            function($fach) { return $fach->count_user; };
        $config['additional_fields']['count_user']['set'] = false;
        $config['additional_fields']['count_sem']['get'] =
            function($fach) { return $fach->count_sem; };
        $config['additional_fields']['count_sem']['set'] = false;
        $config['additional_fields']['count_stgteile']['get'] =
            function($fach) { return $fach->count_stgteile; };
        $config['additional_fields']['count_stgteile']['set'] = false;
        $config['additional_fields']['count_module']['get'] =
            function($fach) { return $fach->count_module; };

        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['name_kurz'] = true;
        $config['i18n_fields']['beschreibung'] = true;

        parent::configure($config);
    }

    /**
     *
     * @param string $id primary key of table
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Fach');
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Abschluesse sorted and filtered by given parameters and enriched with
     * some additional fields. This function is mainly used in the list view.
     *
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return object A SimpleORMapCollection of Abschluss objects.
     */
    public static function getAllEnriched($sortby = 'name', $order = 'ASC',
            $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'name',
                ['count_abschluesse']);
        return parent::getEnrichedByQuery('
            SELECT fach.*,
              COUNT(DISTINCT abschluss_id) AS `count_abschluesse`
            FROM fach
                LEFT JOIN mvv_fach_inst USING (fach_id)
                LEFT JOIN mvv_stgteil USING (fach_id)
                LEFT JOIN mvv_stg_stgteil USING (stgteil_id)
                LEFT JOIN mvv_studiengang USING (studiengang_id)
            ' . self::getFilterSql($filter, true) . '
            GROUP BY fach_id
            ORDER BY ' . $sortby,
            [],
            $row_count,
            $offset
        );
    }

    /**
     * Returns the number of Fächer optional filtered by $filter.
     *
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Fächer
     */
    public static function getCount($filter = null)
    {
        $query = '
            SELECT COUNT(DISTINCT(fach.fach_id))
            FROM fach
                LEFT JOIN mvv_fach_inst ON (fach.fach_id = mvv_fach_inst.fach_id)
            ' . self::getFilterSql($filter, true);
        $db = DBManager::get()->prepare($query);
        $db->execute();
        return $db->fetchColumn(0);
    }

    /**
     * Returns all Faecher which are assigned to Studiengangteile. Sorted and
     * filtered by optional parameters.
     *
     * @param string $sortby Column names to sort by.
     * @param strind $order Direction of sorting ASC|DESC.
     * @param int $row_count Number of rows to return.
     * @param int $offset Offset of first row to return.
     * @param array $filter Array of filter parameters (name of column as key,
     * @return object Collection of Faecher.
     */
    public static function getAllEnrichedByStgteile($sortby = 'name',
            $order = 'ASC', $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement(
            $sortby,
            $order,
            'name',
            ['count_stgteile']
        );
        return parent::getEnrichedByQuery('
            SELECT fach.*,
            COUNT(stgteil_id) as `count_stgteile`
            FROM fach
                INNER JOIN mvv_fach_inst USING (fach_id)
                INNER JOIN mvv_stgteil USING (fach_id)
                ' . self::getFilterSql($filter, true) . '
            GROUP BY fach_id
            ORDER BY ' . $sortby,
            [],
            $row_count,
            $offset
        );
    }

    /**
     * Retrieves the Fach assigned to the given Studiengangteil.
     *
     * @param string $stgteil_id The id of the Studiengangteil.
     * @return object Fach assigned to the given Studiengangteil contained in a
     * collection.
     */
    public static function findByStudiengangTeil($stgteil_id)
    {
        return parent::getEnrichedByQuery('
            SELECT mf.*
            FROM fach mf
                LEFT JOIN mvv_stgteil AS ms USING (fach_id)
            WHERE ms.stgteil_id = ?
            ORDER BY name',
            [$stgteil_id]
        );
    }

    /**
     * Retrieves all Faecher assigned to the given Studiengang.
     *
     * @param string $studiengang_id The id of the Studiengang.
     * @return object Collection of Faecher assigned to the given Studiengang.
     */
    public static function findByStudiengang($studiengang_id)
    {
        return parent::getEnrichedByQuery('
            SELECT mf.*
            FROM fach mf
                INNER JOIN mvv_stgteil USING (fach_id)
                INNER JOIN mvv_stg_stgteil AS mss USING (stgteil_id)
                LEFT JOIN mvv_stgteil_bez USING (stgteil_bez_id)
            WHERE mss.studiengang_id = ?
            ORDER BY name', [$studiengang_id]);
    }

    /**
     * Retrieves all Faecher implicitly assigned to the given Abschluss.
     *
     * @param string $abschluss_id The id of the Abschluss.
     * @return object Collection of Faecher assigned to the given Abschluss.
     */
    public static function findByAbschluss($abschluss_id)
    {
        return parent::getEnrichedByQuery('
            SELECT mf.*
            FROM fach mf
                LEFT JOIN mvv_stgteil USING (fach_id)
                LEFT JOIN mvv_stg_stgteil USING (stgteil_id)
                LEFT JOIN mvv_studiengang ms USING (studiengang_id)
            WHERE ms.abschluss_id = ?
            ORDER BY name',
            [$abschluss_id]
        );
    }

    /**
     * Retrieves all Faecher the giveb Fachbereich is assigned to. If the 2nd
     * parameter is true, only Faecher assigned to Studiengangteile will be
     * returned.
     *
     * @param string $abschluss_id The id of the Abschluss.
     * @return object Collection of Faecher assigned to the given Abschluss.
     */
    public static function findByFachbereich($fachbereich_id, $has_stgteile = false)
    {
        $has_stgteile_sql = $has_stgteile
                ? 'INNER JOIN mvv_stgteil USING (fach_id) '
                : '';
        return parent::getEnrichedByQuery('
            SELECT mf.*
            FROM fach mf
                ' . $has_stgteile_sql . '
                LEFT JOIN mvv_fach_inst mfi USING(fach_id)
            WHERE mfi.institut_id = ?
            ORDER BY name',
            [$fachbereich_id]
        );
    }

    /**
     * Retrieves all Faecher implicitly assigned by public Studiengangteile to
     * the given modules.
     *
     * @param array $modul_ids Ids of modules.
     * @return object Collection of Faecher.
     */
    public static function findPublicByModule($modul_ids)
    {
        return parent::getEnrichedByQuery('
            SELECT mf.*,
                COUNT(DISTINCT msm.modul_id) AS count_module
                FROM mvv_stgteilabschnitt_modul AS msm
                INNER JOIN mvv_stgteilabschnitt USING (abschnitt_id)
                INNER JOIN mvv_stgteilversion msv USING (version_id)
                INNER JOIN mvv_stgteil USING (stgteil_id)
                INNER JOIN mvv_stg_stgteil USING (stgteil_id)
                INNER JOIN mvv_studiengang ms USING (studiengang_id)
                INNER join fach mf USING (fach_id)
                WHERE msm.modul_id IN (?)
                    AND ms.stat IN (?)
                    AND msv.stat IN (?)
                GROUP BY mf.fach_id
                ORDER BY count_module DESC',
            [
                $modul_ids,
                ModuleManagementModel::getPublicStatus('Studiengang'),
                ModuleManagementModel::getPublicStatus('StgteilVersion')
            ]
        );
    }

    /**
     * Retrieves all Faecher by given search term. The term is compared to
     * name of the Fach and the column "zusatz" of the Studiengangteil the
     * Fach ist assigned to.
     *
     * @param string $term The search term.
     * @return object Collection of Faecher.
     */
    public static function findBySearchTermStgteile($term)
    {
        $quoted_term = DBManager::get()->quote('%' . $term . '%');
        return parent::getEnrichedByQuery("
            SELECT mf.*, COUNT(ms.stgteil_id) AS `count_stgteile`
            FROM fach AS mf
                LEFT JOIN mvv_stgteil AS ms USING (fach_id)
            WHERE ms.zusatz LIKE " . $quoted_term . "
                OR mf.name LIKE " . $quoted_term . "
            GROUP BY stgteil_id ORDER BY `name`
        ");
    }

    /**
     * Returns all Faecher which are assigned to the given Studiengangteile.
     * Sorted and filtered by optional parameters.
     *
     * @param array Array of Studiengangteil ids.
     * @param string $sortby Column names to sort by.
     * @param strind $order Direction of sorting ASC|DESC.
     * @param int $row_count Number of rows to return.
     * @param int $offset Offset of first row to return.
     * @param array $filter Array of filter parameters (name of column as key,
     * @return object Collection of Faecher.
     */
    public static function findByIdsStgteile($stgteil_ids, $sortby = 'name',
            $order = 'ASC', $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement(
            $sortby,
            $order,
            'name',
            ['count_stgteile']
        );
        return parent::getEnrichedByQuery('
            SELECT fach.*,
                COUNT(DISTINCT stgteil_id) as `count_stgteile`
                FROM fach
                INNER JOIN mvv_fach_inst USING(fach_id)
                INNER JOIN mvv_stgteil USING(fach_id)
            WHERE mvv_stgteil.stgteil_id IN(?)
                ' . self::getFilterSql($filter) . '
            GROUP BY fach_id
            ORDER BY ' . $sortby,
            [(array) $stgteil_ids],
            $row_count,
            $offset
        );
    }

    /**
     * Returns an associative array of all Fachbereiche assigned to Faecher.
     *
     * @param string $order Direction of sorting ASC|DESC.
     * @return array An associative array of Faecher.
     */
    public static function getAssignedFachbereiche($order = 'ASC')
    {
        $order = ($order == 'DESC' ? $order : 'ASC');
        $fachbereiche = [];
        $stmt = DBManager::get()->prepare('
            SELECT mfi.institut_id, i.Name AS `name`,
                COUNT(fach_id) AS faecher
            FROM mvv_fach_inst AS mfi
                LEFT JOIN Institute AS i ON (mfi.institut_id = i.Institut_id)
            GROUP BY institut_id
            ORDER BY `name` ' . $order
        );
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fachbereich) {
            $fachbereiche[$fachbereich['institut_id']] = $fachbereich;
        }
        return $fachbereiche;
    }

    /**
     * Returns an associative array of institutes (name and id) assigned to
     * studiengaenge. Can be filtered by ids of studiengaenge.
     *
     * @param array $studiengang_ids Limits the result to these studiengaenge.
     * @return array Associative array (id and name) of institutes.
     */
    public static function getAllAssignedInstitutes($studiengang_ids = [])
    {
        return Fachbereich::getEnrichedByQuery('
            SELECT Institute.*,
                COUNT(DISTINCT(mvv_stg_stgteil.studiengang_id)) AS `count_objects`,
                Institute.Institut_id AS `institut_id`,
                Institute.Name AS `name`,
                fak.Name AS `fak_name`,
                IF(Institute.Institut_id = Institute.fakultaets_id, 1, 0) AS `is_fak`
            FROM mvv_stg_stgteil
                INNER JOIN mvv_stgteil USING (stgteil_id)
                INNER JOIN mvv_fach_inst USING (fach_id)
                INNER JOIN Institute ON (mvv_fach_inst.institut_id = Institute.Institut_id)
                INNER JOIN Institute AS fak ON (Institute.fakultaets_id = fak.Institut_id)
            WHERE fak.Institut_id = fak.fakultaets_id
                ' . Fachbereich::getFilterSql(['mvv_stg_stgteil.studiengang_id' => $studiengang_ids]) . '
            GROUP BY Institute.Institut_id
            ORDER BY name ASC
        ');
    }

    /**
     * Finds all Fachbereiche assigned to Faecher. The result can be filtered
     * by a Abschluss-Kategorie or an Abschluss.
     *
     * @param string $kategorie_id The id of the Abschluss-Kategorie.
     * @param string $abschluss_id The id of the Abschluss.
     * @return array Found Fachbereiche as array. Empty array if none was found.
     */
    public static function findUsedFachbereiche($kategorie_id = null,
            $abschluss_id = null)
    {
        $fachbereiche = [];
        if (!is_null($kategorie_id) && is_null($abschluss_id)) {
            $stmt = DBManager::get()->prepare('
                SELECT i.Name AS `name`,
                    i.Institut_id AS institut_id
                FROM mvv_fach_inst AS mfi
                    INNER JOIN mvv_stgteil AS mst USING(fach_id)
                    LEFT JOIN Institute AS i ON (mfi.institut_id = i.Institut_id)
                    INNER JOIN mvv_stg_stgteil AS mss ON (mss.stgteil_id = mss.stgteil_id)
                    INNER JOIN mvv_studiengang AS ms USING (studiengang_id)
                    INNER JOIN mvv_abschl_zuord AS maz USING (abschluss_id)
                WHERE maz.kategorie_id = ?
                GROUP BY i.Institut_id ORDER BY `name`
            ');
            $stmt->execute([$kategorie_id]);
        } else if (!is_null($abschluss_id)) {
                $stmt = DBManager::get()->prepare('
                    SELECT i.Name AS `name`,
                        i.Institut_id AS institut_id
                    FROM mvv_fach_inst AS mfi
                        INNER JOIN mvv_stgteil AS mst USING(fach_id)
                        LEFT JOIN Institute i ON (mfi.institut_id = i.Institut_id)
                        INNER JOIN mvv_stg_stgteil AS mss ON (mst.stgteil_id = mss.stgteil_id)
                        INNER JOIN mvv_studiengang AS ms USING (studiengang_id)
                    WHERE ms.abschluss_id = ?
                    GROUP BY i.Institut_id ORDER BY `name`
                ');
                $stmt->execute([$abschluss_id]);
        } else {
            $stmt = DBManager::get()->prepare('
                SELECT i.Name AS `name`,
                    i.Institut_id AS institut_id
                FROM mvv_fach_inst AS mfi
                    INNER JOIN mvv_stgteil AS mst USING (fach_id)
                    LEFT JOIN Institute AS i ON (mfi.institut_id = i.Institut_id)
                GROUP BY i.Institut_id
                ORDER BY `name`
            ');
            $stmt->execute();
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fachbereich) {
            $fachbereiche[$fachbereich['institut_id']] = $fachbereich;
        }
        return $fachbereiche;
    }


    /**
     * Returns names ans ids of all Fachbereiche (institutes) with number of
     * related Faecher. Sorted and filtered by optional parameters.
     *
     * @param string $sortby Column names to sort by.
     * @param strind $order Direction of sorting ASC|DESC.
     * @param array $filter Array of filter parameters (name of column as key,
     * see ModulManagementModel::getFilterSql()).
     * @return array Associative array of Fachbereiche (id as key).
     */
    public static function getAllFachbereiche($sortby = 'name', $order = 'ASC',
            $filter = null)
    {
        $sortby = ($sortby == 'name' ? 'name' : 'faecher');
        $order = ($order == 'ASC' ? 'ASC' : 'DESC');
        $fachbereiche = [];
        $stmt = DBManager::get()->prepare('
            SELECT Institute.Name AS `name`,
                Institute.Institut_id AS `institut_id`,
                COUNT(DISTINCT fach_id) AS `faecher`
            FROM Institute
                LEFT JOIN mvv_fach_inst ON Institute.Institut_id = mvv_fach_inst.institut_id
                ' . self::getFilterSql($filter, true) . '
            GROUP BY Institute.Institut_id
            ORDER BY ' . $sortby . ' ' . $order
        );
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fachbereich) {
            $fachbereiche[$fachbereich['institut_id']] = $fachbereich;
        }
        return $fachbereiche;
    }

    /**
     * Returns an array with all faecher which are used by given Fachbereich
     * and the given Studiengangteil.
     *
     * @param string $fachbereich_id The id of a Fachbereich (institute)
     * @param string $stgteil_id The id oa a Studiengangteil.
     * @return array Associative array of Faecher with id as key.
     */
    public static function toArrayByFachbereichStgteil($fachbereich_id,
            $stgteil_id)
    {
        $faecher = [];
        $query = '
            SELECT mf.fach_id, mf.name, msf.position
            FROM mvv_fach_inst
                INNER JOIN fach mf USING (fach_id)
                INNER JOIN mvv_stgteil AS mst USING (fach_id)
                INNER JOIN mvv_stg_stgteil AS mss USING (stgteil_id)
            WHERE mfi.institut_id = ?
                AND mss.stgteil_id = ?
            ORDER BY position, name';
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$fachbereich_id, $stgteil_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fach) {
            $faecher[$fach['fach_id']] = $fach;
        }
        return $faecher;
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Fach');
    }

    /**
     * Assignes fachbereiche to this fach.
     * Returns true only if all given fachbereich ids are valid.
     *
     * @param string[]|object[] Array of $fachbereich_ids or
     * fachbereich objects.
     * @return boolean True if fachbereiche was successfully assigned.
     */
    public function assignFachbereiche($fachbereiche)
    {
        $all_fachbereiche = [];
        if (count($fachbereiche)) {
            $position = 1;
            foreach ($fachbereiche as $fachbereich) {
                if (is_object($fachbereich)) {
                    $assigned_fachbereich = $fachbereich;
                } else {
                    $assigned_fachbereich = Fachbereich::find($fachbereich);
                }
                if (!$assigned_fachbereich->isNew()) {
                    $bereich = FachFachbereich::find([$this->id, $assigned_fachbereich->id]);
                    if (!$bereich) {
                        $bereich = new FachFachbereich();
                        $bereich->setId([$this->id, $assigned_fachbereich->id]);
                    }
                    $bereich->position = $position++;
                    $all_fachbereiche[] = $bereich;
                } else {
                    return false;
                }
            }
        }
        $this->department_assignments =
                    SimpleORMapCollection::createFromArray($all_fachbereiche);
        return true;
    }

    /**
     * Returns all Fachbereiche assigned to this Fach.
     *
     * @return array All assigned Fachbereiche.
     */
    public function getFachbereiche()
    {
        return $this->departments;
    }

    /**
     * Returns all Abschluesse this Fach is implicitly assigned to.
     *
     * @return A collection of Faecher.
     */
    public function getAbschluesse()
    {
        return Abschluss::findByFach($this->getId());
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/' . get_class() . '/trail_parent_id']);
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Fachbereich::get($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/StudiengangTeil/trail_parent_id'] =  $this->getId();
        return StudiengangTeil::findByFach($this->getId());
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [];
    }

    /**
     * Returns ids of all modules which are related to this Fach. The relation
     * is done by assigning a Fach to a Studiengangteil and the modules to a
     * version of that Studiengangteil.
     *
     * @param boolean $only_public If true only modules with a public state
     * will be returned.
     * @return array
     */
    public function getRelatedModules($only_public = true, $modul_ids = null)
    {
        if ($only_public) {
            $query = '
                SELECT DISTINCT mm.modul_id
                FROM mvv_stgteil
                    INNER JOIN mvv_stgteilversion msv USING (stgteil_id)
                    INNER JOIN mvv_stgteilabschnitt USING (version_id)
                    INNER JOIN mvv_stgteilabschnitt_modul USING (abschnitt_id)
                    INNER JOIN mvv_modul mm USING (modul_id)
                WHERE fach_id = ?
                    AND msv.stat IN (?)
                    AND mm.stat IN(?)
            ';
            $params = [$this->getId(), StgteilVersion::getPublicStatus(),
                Modul::getPublicStatus()];
        } else {
            $query = '
                SELECT DISTINCT modul_id FROM mvv_stgteil
                    INNER JOIN mvv_stgteilversion AS msv USING (stgteil_id)
                    INNER JOIN mvv_stgteilabschnitt USING (version_id)
                    INNER JOIN mvv_stgteilabschnitt_modul USING (abschnitt_id)
                    INNER JOIN mvv_modul AS mm USING (modul_id)
                WHERE fach_id = ?
            ';
            $params = [$this->getId()];
        }
        if (is_array($modul_ids)) {
            $query .= ' AND mm.modul_id IN (?)';
            $params[] = $modul_ids;
        }
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function validate()
    {
        $ret = parent::validate();
        $messages = [];
        $rejected = false;
        if (sizeof($this->department_assignments) < 1) {
            $ret['fachbereiche'] = true;
            $messages[] = _('Es muss mindestens eine verantwortliche Einrichtung zugewiesen werden.');
            $rejected = true;
        }
        // The name of the Fach must be longer than 4 characters
        if (mb_strlen($this->name) < 4) {
            $ret['name'] = true;
            $messages[] = _('Der Name des Faches ist zu kurz (mindestens 4 Zeichen).');
            $rejected = true;
        } else {
            if ($this->isNew()) {
                // The name of the Fach has to be unique
                $existing = $this->findBySql('name = ' . DBManager::get()->quote($this->name));
                if (sizeof($existing)) {
                    $ret['name'] = true;
                    $messages[] = sprintf(_('Es existiert bereits ein Fach mit dem Namen "%s"!'),
                            $this->name);
                    $rejected = true;
                }
            }
        }
        if ($rejected) {
            throw new InvalidValuesException(join("\n", $messages), $ret);
        }
        return $ret;
    }

    /**
     * Returns all responsible institutes.
     *
     * @return array An array of Fachbereich objects.
     */
    public function getResponsibleInstitutes()
    {
        $institutes = [];
        foreach ($this->departments as $department) {
            $institutes[] = $department;
        }
        return $institutes;
    }

    /**
     * The number of users this Fach was selected by.
     *
     * @return int The number of users.
     */
    public function countUser()
    {
        $stmt = DBManager::get()->prepare('
            SELECT COUNT(DISTINCT user_id)
            FROM user_studiengang WHERE fach_id = ?
        ');
        $stmt->execute([$this->id]);
        return $stmt->fetchColumn();
    }

    /**
     * The number of users this Fach was selected by. Filtered by given
     * degree (Abschluss).
     *
     * @return int The number of users.
     */
    public function countUserByDegree($degree_id)
    {
        $stmt = DBManager::get()->prepare('
            SELECT COUNT(DISTINCT user_id)
            FROM user_studiengang
            WHERE fach_id = ?
                AND abschluss_id = ?
        ');
        $stmt->execute([$this->id, $degree_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findThru($storage->user_id, [
            'thru_table'        => 'user_studiengang',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'fach_id',
            'assoc_foreign_key' => 'fach_id',
        ]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Fächer/Studiengänge'), 'fach', $field_data);
            }
        }
    }

}
