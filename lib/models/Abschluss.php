<?php
/**
 * abschluss.php
 * Model class for Abschluesse (table abschluss)
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

class Abschluss extends ModuleManagementModelTreeItem implements PrivacyObject
{
    /**
     * Number of assigned Faecher.
     * @var type int
     */
    private $count_faecher;

    /**
     * Number of Studiengaenge this Abschluss is assigned to.
     * @var type int
     */
    private $count_studiengaenge;

    /**
     * The name of the assigned Kategorie.
     * @var type string
     */
    private $kategorie_name;

    /**
     * The id of the assigned Kategorie.
     * @var type
     */
    private $kategorie_id;

    /**
     * Alias for $count_studiengaenge
     * @var type
     */
    private $count_objects;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'abschluss';

        $config['belongs_to']['category'] = [
            'class_name' => 'AbschlussKategorie',
            'assoc_func' => 'findByAbschluss'
        ];

        $config['has_one']['category_assignment'] = [
            'class_name' => 'AbschlussZuord',
            'assoc_foreign_key' => 'abschluss_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];
        $config['has_many']['faecher'] = [
            'class_name' => 'Fach',
            'assoc_func' => 'findByAbschluss'
        ];
        $config['has_many']['studiengaenge'] = [
            'class_name' => 'Studiengang',
            'assoc_foreign_key' => 'abschluss_id'
        ];
        $config['has_and_belongs_to_many']['professions'] = [
            'class_name' => 'Fach',
            'thru_table' => 'user_studiengang',
            'thru_key' => 'abschluss_id',
            'thru_assoc_key' => 'fach_id',
            'order_by' => 'GROUP BY fach_id ORDER BY name'
        ];

        $config['additional_fields']['count_faecher']['get'] =
                function($abschluss) { return $abschluss->count_faecher; };
        $config['additional_fields']['kategorie_name']['get'] =
                function($abschluss) { return $abschluss->kategorie_name; };
        $config['additional_fields']['kategorie_id']['get'] =
                function($abschluss) { return $abschluss->category_assignment->kategorie_id; };
        $config['additional_fields']['count_studiengaenge']['get'] =
                function($abschluss) { return $abschluss->count_studiengaenge; };
        $config['additional_fields']['count_objects']['get'] =
            function($abschluss) { return $abschluss->count_objects; };
        $config['additional_fields']['count_user']['get'] = 'countUser';

        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['name_kurz'] = true;
        $config['i18n_fields']['beschreibung'] = true;

        parent::configure($config);
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Abschluss');
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Abschluesse sorted and filtered by given parameters and enriched with
     * some additional fields. This function is mainly used in the list view.
     *
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return object A SimpleORMapCollection of Abschluss objects.
     */
    public static function getAllEnriched($sortby = 'name', $order = 'ASC',
            $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'chdate',
                ['kategorie_name', 'count_faecher', 'count_studiengaenge']);
        return parent::getEnrichedByQuery('
                SELECT abschluss.*, mvv_abschl_kategorie.name AS `kategorie_name`,
                    COUNT(DISTINCT mvv_stgteil.fach_id) AS `count_faecher`,
                    COUNT(DISTINCT mvv_studiengang.studiengang_id) AS `count_studiengaenge`
                FROM abschluss
                    LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                    LEFT JOIN mvv_abschl_kategorie USING (kategorie_id)
                    LEFT JOIN mvv_studiengang USING (abschluss_id)
                    LEFT JOIN mvv_stg_stgteil USING (studiengang_id)
                    LEFT JOIN mvv_stgteil USING (stgteil_id)
                    LEFT JOIN mvv_fach_inst USING (fach_id)
                ' . self::getFilterSql($filter, true) . '
                GROUP BY abschluss_id
                ORDER BY ' . $sortby,
        [], $row_count, $offset);
    }

    /**
     * Returns the number of Abschlüsse optional filtered by $filter.
     *
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Abschluesse
     */
    public static function getCount($filter = null)
    {
        $query = '
            SELECT COUNT(DISTINCT(abschluss_id))
            FROM abschluss
                LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie USING (kategorie_id)
                LEFT JOIN mvv_studiengang USING (abschluss_id)
                LEFT JOIN mvv_stg_stgteil USING (studiengang_id)
                LEFT JOIN mvv_stgteil USING (stgteil_id)
                LEFT JOIN mvv_fach_inst USING (fach_id)
                ' . self::getFilterSql($filter, true);
        $db = DBManager::get()->prepare($query);
        $db->execute();
        return $db->fetchColumn(0);
    }

    /**
     * Returns all Abschluesse assigned to a given Fach.
     *
     * @param string $fach_id The id of the fach.
     * @return array An array of abschluss objects.
     */
    public static function findByFach($fach_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ma.*,
                COUNT(DISTINCT mss.studiengang_id) AS count_studiengaenge
            FROM mvv_stgteil AS mst
                INNER JOIN mvv_stg_stgteil AS mss USING (stgteil_id)
                LEFT JOIN mvv_studiengang USING (studiengang_id)
                LEFT JOIN abschluss AS ma USING (abschluss_id)
            WHERE mst.fach_id = ?
            GROUP BY ma.abschluss_id
            ORDER BY name',
            [$fach_id]
        );
    }

    /**
     * Returns all Abschluesse assigned to Studiengaenge.
     *
     * @return array An array of Abschluesse.
     */
    public static function findUsed()
    {
        return parent::getEnrichedByQuery('
            SELECT a.*, maz.kategorie_id
            FROM abschluss AS a
                INNER JOIN mvv_studiengang AS ms USING(abschluss_id)
                LEFT JOIN mvv_abschl_zuord AS maz USING(abschluss_id)
            ORDER BY name
        ');
    }

    /**
     * Returns all Abschluesse assigned to the given Studiengaenge.
     *
     * @param string|array $studiengang_ids One or more ids (as array) of
     * Studiengaenge.
     * @return array An array of Abschluesse with number of assigned
     * Studiengaenge.
     */
    public static function findByStudiengaenge($studiengang_ids = [])
    {
        return parent::getEnrichedByQuery('
            SELECT ma.*,
                COUNT(studiengang_id) AS count_objects
            FROM abschluss AS ma
                INNER JOIN mvv_studiengang USING (abschluss_id)
            ' . self::getFilterSql(['mvv_studiengang.studiengang_id' => $studiengang_ids], true) . '
            GROUP BY ma.abschluss_id
            ORDER BY ma.name');
    }

    /**
     * Returns all Abschluesse assigned to the given Fachbereich.
     *
     * @param string $fachbereich_id The id of a Fachbereich.
     * @return array An array of Abschluesse.
     */
    public static function findByFachbereich($fachbereich_id)
    {
        return parent::getEnrichedByQuery('
            SELECT a.*, maz.kategorie_id
            FROM abschluss AS a
                INNER JOIN mvv_studiengang AS ms USING (abschluss_id)
                LEFT JOIN mvv_abschl_zuord AS maz USING (abschluss_id)
                INNER JOIN mvv_stg_stgteil AS mss ON (ms.studiengang_id = mss.studiengang_id)
                INNER JOIN mvv_stgteil USING (stgteil_id)
                INNER JOIN mvv_fach_inst AS mfi USING (fach_id)
            WHERE mfi.institut_id = ?
            ORDER BY name',
            [$fachbereich_id]
        );
    }

    /**
     * Returns all Abschluesse assigned to the given module.
     *
     * @param string $modul_id The id of a module.
     * @return array An array of Abschluesse.
     */
    public static function findByModul($modul_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ma.*
            FROM abschluss ma
                INNER JOIN mvv_studiengang USING (abschluss_id)
                INNER JOIN mvv_stg_stgteil USING (studiengang_id)
                INNER JOIN mvv_stgteilversion USING (stgteil_id)
                INNER JOIN mvv_stgteilabschnitt USING (version_id)
                INNER JOIN mvv_stgteilabschnitt_modul AS msm USING (abschnitt_id)
            WHERE msm.modul_id = ?
            ORDER BY msm.position',
            [$modul_id]
        );
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Abschluss');
    }

    /**
     * Assigns an Abschluss-Kategorie to this Abschluss.
     *
     * @param string $kategorie_id The id of the Abschluss-Kategorie
     * @param int Position of this Abschluss in the given Kategorie.
     * @return object|null The assigned Kategorie. Null if assigned
     * Abschluss-Kategorie is unknown
     */
    public function assignKategorie($kategorie_id, $position = null)
    {
        $kategorie = AbschlussKategorie::find($kategorie_id);
        if ($kategorie) {
            $category_assignment = new AbschlussZuord($this->id);
            $category_assignment->kategorie_id = $kategorie->id;
            if (!is_null($position)) {
                $this->category_assignment->position = $position;
            }
            $this->category_assignment = $category_assignment;
        }

        return $kategorie;
    }

    /**
     * Returns all Faecher this Abschluss is assigned to.
     *
     * @return array All Faecher this Abschluss is assigned to.
     */
    public function getFaecher()
    {
        return Fach::findByAbschluss($this->getId());
    }

    /**
     * Returns all assigned institutes of this Abschluss.
     *
     * @return array An array of institutes.
     */
    public function getAssignedInstitutes()
    {
        $institute = [];

        $stmt = DBManager::get()->prepare('
            SELECT inst.*
            FROM mvv_studiengang ms
                INNER JOIN Institute inst ON (inst.Institut_id = ms.institut_id)
            WHERE ms.abschluss_id = ? '
        );

        $stmt->execute([$this->getId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $institut) {
            $institute[$institut['Institut_id']] =
                    new Fachbereich($institut['Institut_id']);
        }
        return $institute;
    }

    /**
     * Returns all Studiengaenge assigned to this Abschluss.
     *
     * @return object A SimpleORMapCollection of Studiengaenge.
     */
    public function getStudiengaenge()
    {
        return Studiengang::findByAbschluss($this->getId());
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        if ($this->name_kurz) {
            return sprintf('%s (%s)', $this->name, $this->name_kurz);
        } else {
            return $this->name;
        }
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/Abschluss/trail_parent_id']);
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
        $_SESSION['MVV/Modul/trail_parent_id'] =  $this->getId();
        // return Modulteil::findByModul($this->getId());
        return Modul::getEnrichedByQuery('
            SELECT mm.*
            FROM mvv_modul mm
                LEFT JOIN mvv_stgteilabschnitt_modul USING (modul_id)
                LEFT JOIN mvv_stgteilabschnitt USING (abschnitt_id)
                LEFT JOIN mvv_stgteilversion USING (version_id)
                LEFT JOIN mvv_stg_stgteil USING (stgteil_id)
                LEFT JOIN mvv_studiengang USING (studiengang_id)
            WHERE abschluss_id = ? ',
            [$this->getId()]
        );
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
        $abschluss_kategorie = AbschlussKategorie::findByAbschluss($this->getId());
        return $abschluss_kategorie ? [$abschluss_kategorie] : [];

    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $rejected = false;
            $messages = [];

            if (!$this->category_assignment) {
                $ret['category_assignment'] = true;
                $messages[] = _('Es muss eine Abschluss-Kategorie ausgewählt werden.');
                $rejected = true;
            } else {
                if (!AbschlussKategorie::find($this->category_assignment->kategorie_id)) {
                    $ret['category_assignment'] = true;
                    $messages[] = _('Unbekannte Abschluss-Kategorie.');
                    $rejected = true;
                }
            }
            // The name of the Abschluss must be longer than 4 characters
            if (mb_strlen($this->name) < 4) {
                $ret['name'] = true;
                $messages[] = _('Der Name des Abschlusses ist zu kurz (mindestens 4 Zeichen).');
                $rejected = true;
            } else {
                if ($this->isNew()) {
                    // The name of the Abschluss has to be unique
                    $existing = $this->findBySql('name = '
                            . DBManager::get()->quote($this->name));
                    if (sizeof($existing)) {
                        $ret['name'] = true;
                        $messages[] = sprintf(_('Es existiert bereits ein Abschluss mit dem Namen "%s"!'),
                                $this->name);
                        $rejected = true;
                    }
                }
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }

    public function countUser()
    {
        $stmt = DBManager::get()->prepare('SELECT COUNT(DISTINCT user_id) '
                . 'FROM user_studiengang WHERE abschluss_id = ?');
        $stmt->execute([$this->id]);
        return $stmt->fetchColumn();
    }

    public function countUserByStudycourse($studycourse_id)
    {
        $stmt = DBManager::get()->prepare('
            SELECT COUNT(DISTINCT user_id)
            FROM user_studiengang
            WHERE fach_id = ?
                AND abschluss_id = ?'
        );
        $stmt->execute([$studycourse_id, $this->id]);
        return $stmt->fetchColumn();
    }

    public function store($validate = true)
    {
        if ($this->isNew() || $this->isDirty()) {
            $this->editor_id = $GLOBALS['user']->id;
            if (!$this->getPristineValue('author_id')) {
                $this->author_id = $GLOBALS['user']->id;
            }
        }

        return parent::store($validate);
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
            'thru_assoc_key'    => 'abschluss_id',
            'assoc_foreign_key' => 'abschluss_id',
        ]);

        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Abschlüsse'), 'abschluss', $field_data);
            }
        }

    }
}
