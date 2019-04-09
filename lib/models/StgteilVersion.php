<?php
/**
 * StgteilVersion.php
 * Model class for Studiengangteil-Versionen (table mvv_stgteilversion)
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

class StgteilVersion extends ModuleManagementModelTreeItem
{

    private $count_abschnitte;
    private $count_dokumente;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_stgteilversion';

        $config['belongs_to']['studiengangteil'] = [
            'class_name' => 'StudiengangTeil',
            'foreign_key' => 'stgteil_id'
        ];
        $config['has_many']['abschnitte'] = [
            'class_name' => 'StgteilAbschnitt',
            'assoc_foreign_key' => 'version_id',
            'order_by' => 'ORDER BY position,mkdate',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['documents'] = [
            'class_name' => 'MvvDokument',
            'assoc_func' => 'findByObject',
            'assoc_func_params_func' => function ($version) {
                return $version;
            }
        ];
        $config['has_many']['document_assignments'] = [
            'class_name' => 'MvvDokumentZuord',
            'assoc_foreign_key' => 'range_id',
            'order_by' => 'ORDER BY position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['additional_fields']['count_abschnitte']['get'] =
            function($version) { return $version->count_abschnitte; };
        $config['additional_fields']['count_dokumente']['get'] =
            function($version) { return $version->count_dokumente; };

        $config['i18n_fields']['beschreibung'] = true;
        
        parent::configure($config);
    }

    public function __construct($id = null)
    {
        $this->object_real_name = _('Studiengangteil-Version');
        parent::__construct($id);
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return ($long ? _('Studiengangteil-Version')
            : _('Version'));
    }

    /**
     * Retrieves the Studiengangteil-Version and all related data and some
     * additional fields.
     *
     * @param string $version_id The id of the Studiengangteil-Version.
     * @return object The Studiengangteil-Version with additional data or a new
     * Studiengangteil-Version.
     */
    public static function getEnriched($version_id)
    {
        $version = parent::getEnrichedByQuery('
            SELECT msv.*, COUNT(msa.abschnitt_id) AS count_abschnitte 
            FROM mvv_stgteilversion AS msv 
                LEFT JOIN mvv_stgteilabschnitt AS msa USING(version_id) 
            WHERE msv.version_id = ? 
            GROUP BY version_id 
            ORDER BY mkdate',
            [$version_id]
        );
        if (count($version)) {
            return $version->find($version_id);
        }
        return self::get();
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Studiengangteil-Versionen sorted and filtered by given parameters and
     * enriched with some additional fields.
     * This function is mainly used in the list view.
     *
     * @param string $sortby Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return SimpleORMapCollection A collection of Studiengangteil-Versionen.
     */
    public static function getAllEnriched($sortby = 'start', $order = 'ASC',
            $filter = null, $row_count = null, $offset = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'start',
                ['start', 'count_abschnitte', 'count_dokumente']);
        return parent::getEnrichedByQuery("
            SELECT mvv_stgteilversion.*, 
                start_sem.beginn AS start, 
                COUNT(abschnitt_id) AS count_abschnitte, 
                COUNT(DISTINCT dokument_id) AS count_dokumente 
            FROM mvv_stgteilversion 
                LEFT JOIN mvv_stgteilabschnitt USING(version_id) 
                LEFT JOIN mvv_dokument_zuord ON (
                        mvv_dokument_zuord.range_id = mvv_stgteilversion.version_id 
                        AND mvv_dokument_zuord.object_type = 'StgteilVersion'
                    ) 
                LEFT JOIN semester_data start_sem ON (mvv_stgteilversion.start_sem = start_sem.semester_id) 
                LEFT JOIN semester_data end_sem ON (mvv_stgteilversion.end_sem = end_sem.semester_id) 
                " . self::getFilterSql($filter, true) . "
            GROUP BY version_id 
            ORDER BY " . $sortby,
            [],
            $row_count,
            $offset
        );
    }

    /**
     * Returns the number of Studiengangteil-Versionen optional filtered
     * by $filter.
     *
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Studiengangteil-Versionen.
     */
    public static function getCount($filter = null)
    {
        $query = '
            SELECT COUNT(DISTINCT(mvv_stgteilversion.version_id)) 
            FROM mvv_stgteilversion 
                LEFT JOIN semester_data start_sem 
                    ON (mvv_stgteilversion.start_sem = start_sem.semester_id) 
                LEFT JOIN semester_data end_sem 
                    ON (mvv_stgteilversion.end_sem = end_sem.semester_id) 
            ' . self::getFilterSql($filter, true);
        $db = DBManager::get()->query($query);
        return $db->fetchColumn(0);
    }

    /**
     * Retrieves all Studiengangteil-Versionen of the given Studiengangteil.
     * sorted and filtered by given parameters and enriched with some
     * additional fields.
     *
     * @param string $stgteil_id The id of a Studiengangteil.
     * @param string $sortby Field names to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of Studiengangteil-Versionen.
     */
    public static function findByStgteil($stgteil_id,
            $sortby = 'start', $order = 'ASC', $filter = null)
    {
        $filter = array_merge((array) $filter,
                    ['mvv_stgteilversion.stgteil_id' => $stgteil_id]);
        return self::getAllEnriched($sortby, $order, $filter);
    }

    /**
     * Returns an array with ids of all Studiengangteil-Versionen found by the
     * given filter.
     *
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return type
     */
    public static function findByFilter($filter)
    {
        $stmt = DBManager::get()->prepare('
            SELECT DISTINCT version_id 
            FROM mvv_stgteilversion 
                LEFT JOIN semester_data start_sem 
                    ON (mvv_stgteilversion.start_sem = start_sem.semester_id) 
                LEFT JOIN semester_data end_sem 
                    ON (mvv_stgteilversion.end_sem = end_sem.semester_id) 
            ' . self::getFilterSql($filter, true));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Returns Version by Studiengangteilabschnitt.
     *
     * @param string $abschnitt_id
     * @return null|object
     */
    public static function findByStgteilAbschnitt($abschnitt_id)
    {
        $versions = parent::getEnrichedByQuery('
            SELECT msv.*, sd.beginn AS start 
            FROM mvv_stgteilversion msv 
                LEFT JOIN semester_data sd ON msv.start_sem = sd.semester_id 
                LEFT JOIN mvv_stgteilabschnitt msa USING(version_id) 
            WHERE abschnitt_id = ? ',
            [$abschnitt_id]
        );
        foreach ($versions as $version) {
            return $version;
        }
        return null;
    }

    /**
     * Returns Versions by given Fach and Abschluss  ordered by cp and start semester.
     *
     * @param string $fach_id Id of Fach.
     * @param string $abschluss_id Id of Abschluss.
     * @param string $version_id Only this version.
     * @return array Array of versions.
     */
    public static function findByFachAbschluss($fach_id, $abschluss_id, $version_id = null)
    {
        $stmt = '
            SELECT DISTINCT msv.* 
            FROM mvv_stgteilversion msv 
                INNER JOIN mvv_stg_stgteil AS mss ON msv.stgteil_id = mss.stgteil_id 
                INNER JOIN mvv_stgteil AS mst ON mss.stgteil_id = mst.stgteil_id 
                INNER JOIN mvv_studiengang AS msg ON mss.studiengang_id = msg.studiengang_id 
                LEFT JOIN semester_data AS sem_start ON msv.start_sem = sem_start.semester_id 
                ' . ($version_id ? 'WHERE msv.version_id = ? AND mst.fach_id = ? AND msg.abschluss_id = ? '
                               : 'WHERE mst.fach_id = ? AND msg.abschluss_id = ? ') . '
            ORDER BY mst.kp DESC, sem_start.beginn';

        return DBManager::get()->fetchAll($stmt,
                ($version_id
                    ? [$version_id, $fach_id, $abschluss_id]
                    : [$fach_id, $abschluss_id]),
                function ($row) {
                    $version = StgteilVersion::buildExisting($row);
                    return $version;
                });
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        if ($this->isNew()) {
            return '';
        }
        
        $options = ($options !== self::DISPLAY_DEFAULT)
                ? $options : (self::DISPLAY_STGTEIL | self::DISPLAY_FACH);
        $with_stgteil = $options & self::DISPLAY_STGTEIL;
        $with_fach = $options & self::DISPLAY_FACH;
        $start_sem = Semester::find($this->start_sem);
        $end_sem = Semester::find($this->end_sem);
        $fassung_nr = $this->fassung_nr
            ? $this->fassung_nr . ModuleManagementModel::getLocaleOrdinalNumberSuffix($this->fassung_nr). ' '
            : '';
        $fassung_typ = $this->fassung_typ
            ? $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'][$this->fassung_typ]['name'] . ' '
            : '';
        if (!$end_sem) {
            if (!$start_sem) {
                $name = $fassung_nr . trim($fassung_typ);
            } else {
                $name = $fassung_nr . $fassung_typ . sprintf(_('gültig ab %s'), $start_sem->name);
            }
        } else {
            if ($start_sem->name == $end_sem->name) {
                $name = $fassung_nr . $fassung_typ . '(' . $start_sem->name . ')';
            } else {
                $name = $fassung_nr . $fassung_typ
                        . sprintf('(%s - %s)', $start_sem->name, $end_sem->name);
            }
        }
        if ($with_stgteil) {
            return StudiengangTeil::get($this->stgteil_id)->getDisplayName($with_fach)
                    . (trim($name) ? ', ' . $name : '');
        } else {
            return $name;
        }
    }

    /**
     * Returns a string representation of this version's validity by semesters.
     *
     * @return string The string with the validity by semesters.
     */
    public function getDisplaySemesterValidity()
    {
        $ret = '';
        $start_sem = Semester::find($this->start_sem);
        $end_sem = Semester::find($this->end_sem);
        if ($end_sem || $start_sem) {
            if ($end_sem) {
                if ($start_sem->name == $end_sem->name) {
                    $ret .= sprintf(_('gültig im %s'), $start_sem->name);
                } else {
                    $ret .= sprintf(_('gültig %s bis %s'), $start_sem->name, $end_sem->name);
                }
            } else {
                $ret .= sprintf(_('gültig ab %s'), $start_sem->name);
            }
        }
        return $ret;
    }

    /**
     * Makes a deep copy of this version.
     *
     * @return bool|object Returns the new version or false on failure;
     */
    public function copy()
    {
        $new_mvv_objects = [];
        $new_version = clone $this;
        $new_version->setNew(true);
        $new_version->setNewId();
        // TODO set default value
        $new_version->stat = 'planung';
        $new_mvv_objects[] = $new_version;
        foreach (StgteilAbschnitt::findByStgteilVersion($this->getId()) as $abschnitt) {
            $new_abschnitt = clone $abschnitt;
            $new_abschnitt->setNew(true);
            $new_abschnitt->setNewId();
            $new_abschnitt->version_id = $new_version->version_id;
            $new_mvv_objects[] = $new_abschnitt;
            $modul_assignments = $abschnitt->getModulAssignments();
            foreach ($modul_assignments as $assignment) {
                $new_modul_assignment = clone $assignment;
                $new_modul_assignment->setNew(true);
                $new_modul_assignment->setNewId();
                $new_modul_assignment->abschnitt_id = $new_abschnitt->abschnitt_id;
                $new_mvv_objects[] = $new_modul_assignment;
            }
            $modulteil_assignments = ModulteilStgteilabschnitt::findBySql(
                    'abschnitt_id = ' . DBManager::get()->quote($abschnitt->getId()));
            foreach ($modulteil_assignments as $assignment) {
                $new_modulteil_assignment = clone $assignment;
                $new_modulteil_assignment->setNew(true);
                $new_modulteil_assignment->abschnitt_id = $new_abschnitt->abschnitt_id;
                $new_mvv_objects[] = $new_modulteil_assignment;
            }
        }
        $success = array_walk($new_mvv_objects, function ($mvv_object) {
                return $mvv_object->store(false);
            });
        return ($success ? $new_version : false);
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->stgteil_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return StudiengangTeil::get($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/StgteilAbschnitt/trail_parent_id'] =  $this->getId(); //RAS what's that?
        return StgteilAbschnitt::findByStgteilVersion($this->getId());
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [StudiengangTeil::get($this->getValue('stgteil_id'))];
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = [];
            $rejected = false;
            if ($this->start_sem) {
                $start_sem = Semester::find($this->start_sem);
                if (!$start_sem) {
                    $ret['start_sem'] = true;
                    $messages[] = _('Ungültiges Semester.');
                    $rejected = true;
                } else if ($this->end_sem) {
                    $end_sem = Semester::find($this->end_sem);
                    if ($end_sem) {
                        if ($start_sem->beginn > $end_sem->beginn) {
                            $ret['start_sem'] = true;
                            $messages[] = _('Das Endsemester muss nach dem Startsemester liegen.');
                            $rejected = true;
                        }
                    } else {
                        $ret['end_sem'] = true;
                        $messages[] = _('Ungültiges Endsemester.');
                        $rejected = true;
                    }
                }
            }  else {
                $ret['start_sem'] = true;
                $messages[] = _('Bitte ein Startsemester angeben.');
                $rejected = true;
            }
            if ($this->stgteil_id) {
                if (!StudiengangTeil::find($this->stgteil_id)) {
                    $ret['stgteil'] = true;
                    $messages[] = _('Der angegebene Studiengangteil ist ungültig.');
                    $rejected = true;
                }
            } else {
                $ret['stgteil'] = true;
                $messages[] = _('Bitte einen Studiengangteil angeben.');
                $rejected = true;
            }
            if ($this->fassung_nr) {
                if (!is_int($this->fassung_nr)) {
                    $ret['fassung_nr'] = true;
                    $messages[] = _('Für Fassung bitte eine Zahl angeben.');
                    $rejected = true;
                }
                if (!$GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'][$this->fassung_typ]) {
                    $ret['fassung_typ'] = true;
                    $messages[] = _('Bitte einen Typ der Fassung angeben.');
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
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        if ($this->isNew()) {
            return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['default'];
        }
        return parent::getStatus();
    }
    
    /**
     * Returns the responsible institutes.
     * Inherits the responsible institutes from Studiengangteil
     * 
     * @return array Array of institute objects.
     */
    public function getResponsibleInstitutes()
    {
        $parent = reset($this->getParents());
        if ($parent) {
            return $parent->getResponsibleInstitutes();
        }
        return parent::getResponsibleInstitutes();
    }
    
}
