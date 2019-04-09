<?php
/**
 * StudiengangTeil.php
 * Model class for Studiengangteile (table mvv_stgteil)
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

class StudiengangTeil extends ModuleManagementModelTreeItem
{

    private $count_versionen;
    private $fach_name;
    private $stgteil_name;
    private $count_fachberater;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_stgteil';

        $config['has_many']['versionen'] = [
            'class_name' => 'StgteilVersion',
            'assoc_foreign_key' => 'stgteil_id',
            'on_delete' => 'delete'
        ];
        // All assigned Studienfachberater
        $config['has_and_belongs_to_many']['fachberater'] = [
            'class_name' => 'User',
            'thru_table' => 'mvv_fachberater',
            'thru_key' => 'stgteil_id',
            'thru_assoc_key' => 'user_id',
            'order_by' => 'ORDER BY position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['fachberater_assignments'] = [
            'class_name' => 'Fachberater',
            'assoc_foreign_key' => 'stgteil_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        // The assigned Fach
        $config['belongs_to']['fach'] = [
            'class_name' => 'Fach',
            'foreign_key' => 'fach_id'
        ];
        $config['has_and_belongs_to_many']['studiengang'] = [
            'class_name' => 'Studiengang',
            'thru_table' => 'mvv_stg_stgteil',
            'thru_key' => 'stgteil_id',
            'thru_assoc_key' => 'studiengang_id'
        ];
        $config['has_many']['studiengang_assignments'] = [
            'class_name' => 'StudiengangStgteil',
            'assoc_foreign_key' => 'stgteil_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];


        $config['additional_fields']['count_versionen']['get'] =
            function($stgteil) { return $stgteil->count_versionen; };
        $config['additional_fields']['fach_name']['get'] =
            function($stgteil) { return $stgteil->fach_name; };
        $config['additional_fields']['count_fachberater']['get'] =
            function($stgteil) { return $stgteil->count_fachberater; };
        $config['additional_fields']['stgteil_name']['get'] =
            function($stgteil) { return $stgteil->stgteil_name; };

        $config['i18n_fields']['zusatz'] = true;

        parent::configure($config);
    }

    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Studiengangteil');
    }

    /**
     * Assignes fachberater to this Studiengangteil.
     * Returns true only if all given user ids are valid.
     *
     * @param String[]/Object[] Array of user ids or user objects.
     * @return boolean True if fachbereiche was successfully assigned.
     */
    public function assignFachberater($users)
    {
        $all_fachberater = [];
        if (count($users)) {
            $position = 1;
            foreach ($users as $user) {
                if (is_object($user)) {
                    $assigned_user = $user;
                } else {
                    $assigned_user = User::find($user);
                }
                if (!$assigned_user->isNew()) {
                    $fachberater = Fachberater::find([$this->id, $assigned_user->id]);
                    if (!$fachberater) {
                        $fachberater = new Fachberater();
                        $fachberater->setId([$this->id, $assigned_user->id]);
                    }
                    $fachberater->position = $position++;
                    $all_fachberater[] = $fachberater;
                } else {
                    return false;
                }
            }
        }
        $this->fachberater_assignments =
                    SimpleORMapCollection::createFromArray($all_fachberater);
        return true;
    }

    /**
     * Assignes a Fach to this Studiengangteil.
     * Returns true only if the given fach id is valid.
     *
     * @param String Id of the Fach to assign.
     * @return boolean True if the fach was successfully assigned.
     */
    public function assignFach($fach_id)
    {
        $fach = Fach::find($fach_id);
        if ($fach) {
            if (is_object($this->fach)
                    && $this->fach->getId() == $fach->getId()) {
                $this->is_dirty = false;
                return true;
            } else {
                $this->is_dirty = true;
                $this->fach = $fach;
                $this->fach_id = $fach->getId();
                return true;
            }
        }
        return false;
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        $options = $options !== self::DISPLAY_DEFAULT
                ? $options : self::DISPLAY_FACH;
        $with_fach = $options & self::DISPLAY_FACH;
        if ($this->isNew()) {
            return '';
        }
        if ($this->fach) {
            $name = $with_fach ? $this->fach->name . ' ' : '';
        } else {
            $name = '';
        }
        $name .= $this->kp ?  $this->kp . ' CP ' : '';
        $name .= $this->zusatz ? $this->zusatz  : '';
        return trim($name);
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->fach_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Fach::get($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        return StgteilVersion::findByStgteil($this->getId());
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return Studiengang::findByStgTeil($this->getId());
    }

    /**
     * Retrieves the Studiengangteil and all related data and some
     * additional fields.
     *
     * @param string $stgteil_id The id of the Studiengangteil.
     * @return StudiengangTeil The Studiengangteil with additional data or a
     * new StudiengangTeil.
     */
    public static function getEnriched($stgteil_id)
    {
        $stgteil = parent::getEnrichedByQuery("
            SELECT `mvv_stgteil`.*,
                CONCAT(`fach`.`name`, ': ', `mvv_stgteil`.`zusatz`, ' (', `mvv_stgteil`.`kp`, ' CP)') AS stgteil_name,
                `fach`.`name` AS `fach_name`,
                `fach`.`fach_id`
            FROM `mvv_stgteil`
                LEFT JOIN `fach` USING(`fach_id`)
            WHERE `mvv_stgteil`.`stgteil_id` = ?",
                [$stgteil_id]);
        if (sizeof($stgteil)) {
            return $stgteil->find($stgteil_id);
        }
        return self::get();
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Studiengangteile sorted and filtered by given parameters and enriched
     * with some additional fields.
     * This function is mainly used in the list view.
     *
     * @param string $sortby Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function getAllEnriched($sortby = 'fach_name',
            $order = 'ASC', $filter = null, $row_count = null, $offset = null)
    {
        $sortby = self::createSortStatement($sortby, $order,
                'fach_name',
                words('fach_name stgteil_name count_fachberater count_versionen'));
        return parent::getEnrichedByQuery(
                'SELECT mvv_stgteil.*, CONCAT(fach.name, ": ", '
                . 'mvv_stgteil.zusatz, " (", mvv_stgteil.kp, " KP)") AS stgteil_name, '
                . 'fach.name AS fach_name, '
                . 'COUNT(DISTINCT mvv_fachberater.user_id) AS count_fachberater, '
                . 'COUNT(DISTINCT mvv_stgteilversion.version_id) AS count_versionen '
                . 'FROM mvv_stgteil '
                . 'LEFT JOIN fach USING(fach_id) '
                . 'LEFT JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN mvv_fachberater USING(stgteil_id) '
                . 'LEFT JOIN mvv_stgteilversion USING(stgteil_id) '
                . self::getFilterSql($filter, true)
                . 'GROUP BY mvv_stgteil.stgteil_id '
                . 'ORDER BY ' . $sortby, [], $row_count, $offset);
    }

    /**
     * Returns the number of Studienagngteile optional filtered by $filter.
     *
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Studiengangteile
     */
    public static function getCount($filter = null)
    {
        $query = 'SELECT COUNT(DISTINCT(mvv_stgteilversion.stgteil_id)) '
                . 'FROM mvv_stgteil '
                . 'LEFT JOIN fach USING(fach_id) '
                . 'LEFT JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN mvv_fachberater USING(stgteil_id) '
                . 'LEFT JOIN mvv_stgteilversion USING(stgteil_id) '
                . self::getFilterSql($filter, true);
        $db = DBManager::get()->query($query);
        return $db->fetchColumn(0);
    }

    /**
     * Retrieves all Studienganteile assigned to the given Studiengang.
     *
     * @param string $studiengang_id The id of a Studiengang.
     * @param string $sort Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function findByStudiengang($studiengang_id,
            $sort = 'stgteil_position, stgteil_chdate', $order = 'ASC')
    {
        $sort = self::createSortStatement($sort, $order, 'chdate',
                ['stgteil_position', 'stgteil_chdate']);
        return parent::getEnrichedByQuery(
                'SELECT mst.*, msb.*, mss.position AS `stgteil_position`, '
                . 'mss.chdate AS `stgteil_chdate`'
                . 'FROM mvv_stg_stgteil mss '
                . 'LEFT JOIN mvv_stgteil_bez msb USING(stgteil_bez_id) '
                . 'LEFT JOIN mvv_stgteil mst USING(stgteil_id) '
                . 'LEFT JOIN fach mf USING(fach_id) '
                . 'WHERE studiengang_id = ? '
                . 'ORDER BY ' . $sort, [$studiengang_id]);
    }

    /**
     * Retrieves all Studiengangteile by Fach. Optionally filtered by given
     * filter parameter.
     *
     * @param string $fach_id The id of a Fach.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param string $sort Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function findByFach($fach_id, $filter = null,
            $sort = 'chdate', $order = 'DESC')
    {
        $sort = self::createSortStatement($sort, $order, 'chdate');
        $params = [$fach_id];
        return parent::getEnrichedByQuery(
            'SELECT `mvv_stgteil`.*
                FROM `mvv_stgteil`
                    LEFT JOIN `fach` USING(`fach_id`)
                WHERE `fach`.`fach_id` = ? ' .
                self::getFilterSql($filter) .
                'ORDER BY ' . $sort, $params);
    }

    /**
     * Retrieves all Studiengangteile by given Fachbereich. The Fachbereich is
     * the responsible institute of a Fach. The Fach is assigned to
     * Studiengangteile.
     *
     * @param string $fachbereich_id The id of an institute.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param string $sort Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function findByFachbereich($fachbereich_id, $filter = null,
            $sort = 'chdate', $order = 'DESC')
    {
        $sort = self::createSortStatement($sort, $order, 'chdate',
                ['fach_name']);
        $params = [$fachbereich_id];
        return parent::getEnrichedByQuery('
            SELECT `mvv_stgteil`.*, `fach`.`name` AS `fach_name`
                FROM `mvv_stgteil`
                    LEFT JOIN `fach` USING(`fach_id`)
                    LEFT JOIN `mvv_fach_inst` USING(`fach_id`)
                WHERE `mvv_fach_inst`.`institut_id` = ? ' .
                self::getFilterSql($filter) .
                'ORDER BY ' . $sort, $params);
    }

    /**
     * Returns an array of all Fachbereiche assigned through Fächer to
     * Studiengangteile.
     *
     * @param string $sortby Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function getAssignedFachbereiche($sortby = 'name', $order = 'ASC',
            $filter = null)
    {
        $sortby = (in_array($sortby,
                words('name stgteile'))
                ? $sortby : 'name');
        $order = ($order != 'DESC' ? ' ASC' : ' DESC');
        $fachbereiche = [];
        $db = DBManager::get();
        $stmt = $db->prepare('
            SELECT
                `mvv_fach_inst`.`institut_id`,
                `Institute`.`Name` as `name`,
                COUNT(`stgteil_id`) as `stgteile`
            FROM `mvv_stgteil`
                INNER JOIN `mvv_fach_inst`
                    USING(fach_id)
                INNER JOIN `Institute`
                    ON `mvv_fach_inst`.`institut_id` = `Institute`.`Institut_id` ' .
                self::getFilterSql($filter, true) . '
            GROUP BY `institut_id` ORDER BY ' . $sortby . $order);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fachbereich) {
            $fachbereiche[$fachbereich['institut_id']] = $fachbereich;
        }
        return $fachbereiche;
    }

    /**
     * Retrieves all Studiengangteile by Studiengang and Studiengangteil-
     * Bezeichnung in the case of Mehrfach-Studiengaenge.
     *
     * @param string $studiengang_id The id of a Studiengang.
     * @param string $stgteil_bez_id The id of a Studiengangteil-Bezeichnung.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function findByStudiengangStgteilBez($studiengang_id,
            $stgteil_bez_id)
    {
        return parent::getEnrichedByQuery(
            'SELECT `mvv_stgteil`.* 
                FROM `mvv_stgteil`
                    INNER JOIN `mvv_stg_stgteil` USING(`stgteil_id`)
                    INNER JOIN `fach` USING(`fach_id`)
                WHERE `mvv_stg_stgteil`.`studiengang_id` = ?
                    AND `mvv_stg_stgteil`.`stgteil_bez_id` = ?
                ORDER BY `position`, `chdate`',
                [$studiengang_id, $stgteil_bez_id]);
    }

    /**
     * Returns the number of Studiengangteile optional filtered by $filter.
     *
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Studiengangteile.
     */
    public static function findBySearchTerm($term, $filter = null)
    {
        $term = '%' . $term . '%';
        return parent::getEnrichedByQuery(
            'SELECT `mvv_stgteil`.*
                FROM fach
                    INNER JOIN `mvv_stgteil` USING(`fach_id`)
                    LEFT JOIN `mvv_fach_inst` USING(`fach_id`)
                    LEFT JOIN `mvv_stgteilversion` USING(`stgteil_id`)
                    LEFT JOIN `semester_data` AS `start_sem`
                        ON (`mvv_stgteilversion`.`start_sem` = `start_sem`.`semester_id`)
                    LEFT JOIN `semester_data` AS `end_sem`
                        ON (`mvv_stgteilversion`.`end_sem` = `end_sem`.`semester_id`)
                WHERE (`mvv_stgteil`.`zusatz` LIKE ?
                    OR `fach`.`name` LIKE ?) ' .
                self::getFilterSql($filter) .
                'GROUP BY `stgteil_id` ORDER BY `fach`.`name`', [$term, $term]);
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Studiengangteil');
    }

    /**
     * Returns the number of Faecher which are assigned to Studiengangteile.
     *
     * @return int the number of assigned Faecher
     */
    public static function getCountAssignedFaecher($filter = null)
    {
        $result = DBManager::get()->query(
            'SELECT COUNT(DISTINCT `fach_id`)
                FROM `mvv_stgteil`
                    INNER JOIN `mvv_fach_inst` USING(`fach_id`) ' .
            self::getFilterSql($filter, true));
        return $result->fetchColumn();
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = [];
            $rejected = false;
            if (!is_object($this->fach)) {
                $ret['fach'] = true;
                $messages[] = _('Es muss ein Fach zugeordnet werden.');
                $rejected = true;
            }
            if ($this->semester < 1) {
                $ret['semester'] = true;
                $messages[] = _('Es muss die Anzahl der Semester angegeben werden.');
                $rejected = true;
            }
            if (mb_strlen($this->isI18nField('zusatz')
                    ? $this->zusatz->original()
                    : $this->zusatz) < 2) {
                $ret['zusatz'] = true;
                $messages[] = _('Der Titelzusatz ist zu kurz (mindestens 2 Zeichen).');
                $rejected = true;
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }

    public function getResponsibleInstitutes()
    {
        return array_map(function ($fb) {
            return new Institute($fb['institut_id']);
        }, self::getAssignedFachbereiche('name', 'ASC', ['mvv_stgteil.stgteil_id' => $this->getId()]));
    }

}
