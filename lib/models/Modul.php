<?php
/**
 * Modul.php
 * Model class for Module (table mvv_modul)
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

class Modul extends ModuleManagementModelTreeItem
{
    /**
     * The default language of the deskriptor (defined in config).
     *
     * @var string
     */
    private $default_language;

    /**
     * The number of modulteile.
     *
     * @var int
     */
    private $count_modulteile;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modul';

        $config['has_one']['deskriptoren'] = [
            'class_name' => 'ModulDeskriptor',
            'assoc_foreign_key' => 'modul_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['modulteile'] = [
            'class_name' => 'Modulteil',
            'assoc_foreign_key' => 'modul_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY position'
        ];
        // Ist Novellierung von (quelle)
        $config['has_one']['modul_quelle'] = [
            'class_name' => 'Modul',
            'foreign_key' => 'quelle'
        ];
        // Ist Variante von (variante)
        $config['has_one']['modul_variante'] = [
            'class_name' => 'Modul',
            'foreign_key' => 'variante'
        ];
        // hauptverantwortliche Einrichtung
        $config['has_one']['responsible_institute'] = [
            'class_name' => 'ModulInst',
            'assoc_func' => 'findPrimarilyResponsibleInstitute',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        // beteiligte Einrichtungen
        $config['has_many']['assigned_institutes'] = [
            'class_name' => 'ModulInst',
            'assoc_func' => 'findOtherResponsibleInstitutes',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['assigned_users'] = [
            'class_name' => 'ModulUser',
            'assoc_foreign_key' => 'modul_id',
            'order_by' => 'ORDER BY gruppe,position',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['abschnitte_modul'] = [
            'class_name' => 'StgteilabschnittModul',
            'assoc_foreign_key' => 'modul_id',
            'order_by' => 'ORDER BY position,mkdate',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_and_belongs_to_many']['abschnitte'] = [
            'class_name' => 'StgteilAbschnitt',
            'thru_table' => 'mvv_stgteilabschnitt_modul',
            'thru_key' => 'modul_id',
            'thru_assoc_key' => 'abschnitt_id',
            'order_by' => 'ORDER BY position,mkdate'
        ];
        // Assigned languages of instruction
        $config['has_many']['languages'] = [
            'class_name' => 'ModulLanguage',
            'assoc_foreign_key' => 'modul_id',
            'order_by' => 'ORDER BY position,mkdate',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['additional_fields']['count_modulteile']['get'] =
                function ($modul) { return $modul->count_modulteile; };
        $config['additional_fields']['count_modulteile']['set'] = false;
        $config['additional_fields']['languagesofinstruction']['get'] =
                function ($modul) { return $modul->languages; };
        $config['additional_fields']['languagesofinstruction']['set'] = false;

        $config['alias_fields']['flexnow_id'] = 'flexnow_modul';

        $config['default_values']['stat'] = $GLOBALS['MVV_MODUL']['STATUS']['default'];

        parent::configure($config);
    }

    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Modul');
        $this->setDefaultLanguage();
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Modul');
    }

    /**
     * Retrieves the module and all related data and some additional fields.
     *
     * @param string $modul_id The id of the module.
     * @return object The module with additional data or a new module.
     */
    public static function getEnriched($modul_id)
    {
        $modul = parent::getEnrichedByQuery('
            SELECT mvv_modul.*, mvv_modul_deskriptor.bezeichnung AS bezeichnung, 
                COUNT(DISTINCT(mvv_modulteil.modulteil_id)) AS count_modulteile 
            FROM mvv_modul 
                LEFT JOIN mvv_modul_deskriptor 
                    ON mvv_modul.modul_id = mvv_modul_deskriptor.modul_id 
                LEFT JOIN mvv_modulteil 
                    ON mvv_modul.modul_id = mvv_modulteil.modul_id 
                LEFT JOIN mvv_modul_inst 
                    ON (mvv_modul.modul_id = mvv_modul_inst.modul_id 
                        AND mvv_modul_inst.gruppe = ?) 
            WHERE mvv_modul.modul_id = ?',
            [
                'hauptverantwortlich',
                $modul_id
            ]
        );

        if (sizeof($modul)) {
            return $modul[$modul_id];
        }
        return self::get();
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Module sorted and filtered by given parameters and enriched with some
     * additional fields. This function is mainly used in the list view.
     *
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of module objects.
     */
    public static function getAllEnriched($sortby = 'chdate', $order = 'ASC',
            $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'bezeichnung,chdate',
                words('bezeichnung count_modulteile chdate'));
        return parent::getEnrichedByQuery('
                SELECT mvv_modul.*, mvv_modul_deskriptor.bezeichnung AS bezeichnung, 
                    COUNT(DISTINCT(mvv_modulteil.modulteil_id)) AS count_modulteile 
                FROM mvv_modul 
                    LEFT JOIN mvv_modul_deskriptor 
                        ON mvv_modul.modul_id = mvv_modul_deskriptor.modul_id 
                LEFT JOIN mvv_modulteil 
                    ON mvv_modul.modul_id = mvv_modulteil.modul_id 
                LEFT JOIN mvv_modul_inst 
                    ON (mvv_modul.modul_id = mvv_modul_inst.modul_id 
                        AND mvv_modul_inst.gruppe = ?) 
                LEFT JOIN semester_data start_sem 
                    ON (mvv_modul.start = start_sem.semester_id) 
                LEFT JOIN semester_data end_sem 
                    ON (mvv_modul.end = end_sem.semester_id) '
                . self::getFilterSql($filter, true) . '
                GROUP BY modul_id 
                ORDER BY ' . $sortby,
            ['hauptverantwortlich'],
            $row_count,
            $offset
        );
    }

    /**
     * Returns the number of modules optional filtered by $filter.
     *
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of modules
     */
    public static function getCount($filter = null)
    {
        $query = '
            SELECT COUNT(DISTINCT(mvv_modul.modul_id)) 
            FROM mvv_modul 
                LEFT JOIN mvv_modul_inst 
                    ON (mvv_modul.modul_id = mvv_modul_inst.modul_id 
                        AND mvv_modul_inst.gruppe = ?)
                LEFT JOIN semester_data as start_sem 
                    ON (mvv_modul.start = start_sem.semester_id)  
                LEFT JOIN semester_data as end_sem 
                    ON (mvv_modul.end = end_sem.semester_id) '
                . self::getFilterSql($filter, true);
        $db = DBManager::get()->prepare($query);
        $db->execute(['hauptverantwortlich']);
        return $db->fetchColumn(0);
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/Modul/trail_parent_id']);
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Abschluss::get($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/Lvgruppe/trail_parent_id'] =  $this->getId();
        return Lvgruppe::getEnrichedByQuery('
            SELECT ml.* 
            FROM mvv_lvgruppe ml 
                LEFT JOIN mvv_lvgruppe_modulteil USING (lvgruppe_id) 
                LEFT JOIN mvv_modulteil USING (modulteil_id) 
            WHERE modul_id = ? ',
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
        return StgteilabschnittModul::findBySQL('modul_id = ?', [$this->id]);
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT) {
        $options = ($options !== self::DISPLAY_DEFAULT)
                ? $options : self::DISPLAY_CODE;
        $with_code = $options & self::DISPLAY_CODE;
        if ($this->isNew()) {
            return parent::getDisplayName($options);
        }
        $name = ($with_code && trim($this->code)) ? $this->code . ' - ' : '';
        $name .= $this->deskriptoren->bezeichnung;
        if ($options & self::DISPLAY_SEMESTER) {
            $sem_validity = $this->getDisplaySemesterValidity();
            if ($sem_validity) {
                $name .= ', ' . $sem_validity;
            }
        }
        return trim($name);
    }

    /**
     * Returns a string representation of this module's validity by semesters.
     *
     * @return string The string with the validity by semesters.
     */
    public function getDisplaySemesterValidity()
    {
        $ret = '';
        $start_sem = Semester::find($this->start);
        $end_sem = Semester::find($this->end);
        if ($end_sem || $start_sem) {
            if ($end_sem) {
                if ($start_sem->name == $end_sem->name) {
                    $ret .= sprintf(_('gültig im %s'),
                            $start_sem->name);
                } else {
                    $ret .= sprintf(_('gültig %s bis %s'),
                            $start_sem->name, $end_sem->name);
                }
            } else {
                $ret .= sprintf(_('gültig ab %s'), $start_sem->name);
            }
        }
        return $ret;
    }

    /**
     * Sets the default language for the module descriptor. Takes the language
     * previously set by ApplicationSimpleORMap::setLanguage() or the one
     * defined as default in mvv_config.php.
     */
    private function setDefaultLanguage()
    {
        if (isset($GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values']
                [ModuleManagementModel::getLanguage()])) {
            $this->default_language = ModuleManagementModel::getLanguage();
        } else {
            $this->default_language =
                    $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default'];
        }
    }

    /**
     * Returns the default language for the module descriptor.
     *
     * @return string Short name of language (see mvv_config.php)
     */
    public function getDefaultLanguage()
    {
        return $this->default_language;
    }

    /**
     * Returns the Deskriptor in the given language. A Modul has always a
     * Deskriptor in the default language. If the given language is unknown, the
     * method returns the deskriptor in the default language.
     *
     * @param string $language The id of the language
     * @param bool If true returns always a new descriptor
     * @return object The Deskriptor.
     */
    public function getDeskriptor($language = null, $force_new = false) {
        if (!isset($GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language])) {
            $language = $this->default_language;
        }
        if (!$this->deskriptoren) {
            // the module is new and has no descriptor
            // return a new descriptor in the default language
            $deskriptor = new ModulDeskriptor();
            $deskriptor->setNewId();
            $deskriptor->modul_id = $this->getId();
            $this->deskriptoren = $deskriptor;
        }
        
        return $this->deskriptoren;
    }

    /**
     * Assigns the responsible institute to this Modul.
     * A Modul has only one (but always one) responsible institute.
     *
     * @param string $institut_id The id of the institute to assign.
     * @return boolean True if institute was successfully assigned.
     */
    public function assignResponsibleInstitute($institut_id) {

        $institute = Fachbereich::find($institut_id);
        if (!$institute) {
            return false;
        }
        if ($this->responsible_institute->institut_id != $institut_id) {
            $this->responsible_institute && $this->responsible_institute->delete();
            $resp_institute = new ModulInst();
            $resp_institute->institut_id = $institute->id;
            $resp_institute->modul_id = $this->id;
            $resp_institute->gruppe = 'hauptverantwortlich';
            $this->responsible_institute = $resp_institute;
        }
        $this->assigned_institutes->unsetBy('institut_id', $institute->id);
        return true;
    }

    /**
     * Assigns other institutes (by id) to this module.
     *
     * @param array $institut_ids Array of institute ids.
     */
    public function assignInstitutes($institut_ids) {
        $institutes = [];
        foreach ($institut_ids as $pos => $institut_id) {
            $modul_inst = new ModulInst();
            $modul_inst->modul_id = $this->id;
            $modul_inst->institut_id = $institut_id;
            $modul_inst->gruppe = 'verantwortlich';
            $modul_inst->position = $pos;
            $institutes[] = $modul_inst;
        }
        $this->assigned_institutes = SimpleORMapCollection::createFromArray($institutes);
    }

    /**
     * Assigns users in their groups to this module.
     *
     * @param array $grouped_user_ids Array of user ids grouped by usergroup.
     */
    public function assignUsers($grouped_user_ids) {
        $assigned_users = [];

        foreach (array_keys($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values']) as $group) {
            $position = 1;
            foreach ((array) $grouped_user_ids[$group] as $user_id) {
                $user = User::find($user_id);
                if ($user) {
                    $modul_user = ModulUser::find([$this->id, $user_id, $group]);
                    if (!$modul_user) {
                        $modul_user = new ModulUser();
                        $modul_user->modul_id = $this->id;
                        $modul_user->user_id = $user_id;
                        $modul_user->gruppe = $group;
                    }
                    $modul_user->position = $position++;
                    $assigned_users[] = $modul_user;
                } else {
                    throw new Exception(_('Unbekannter Nutzer'));
                }
            }
        }
        $this->assigned_users = SimpleOrMapCollection::createFromArray($assigned_users);
    }

    /**
     * Returns an associative array with all assigned users grouped by
     * their functions.
     *
     * @return array Array with group name as key and array of users as value.
     */
    public function getGroupedAssignedUsers()
    {
        $grouped_users = [];
        foreach ($this->assigned_users as $user) {
            if ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'][$user->gruppe]) {
                $grouped_users[$user->gruppe][] = $user;
            } else {
                $grouped_users['unknown'][] = $user;
            }
        }
        return $grouped_users;
    }

    /**
     * Assignes languages of instruction to this part-module.
     *
     * @param type $languages An array of language keys defined in mvv_config.php.
     */
    public function assignLanguagesOfInstruction($languages)
    {
        $assigned_languages = [];
        $languages_flipped = array_flip($languages);
        foreach ($GLOBALS['MVV_MODUL']['SPRACHE']['values'] as $key => $language) {
            if (isset($languages_flipped[$key])) {
                $language = ModulLanguage::find([$this->id, $key]);
                if (!$language) {
                    $language = new ModulLanguage();
                    $language->modul_id = $this->id;
                    $language->lang = $key;
                }
                $language->position = $languages_flipped[$key];
                $assigned_languages[] = $language;
            }
        }

        $this->languages = SimpleORMapCollection::createFromArray(
                $assigned_languages);
    }

    public function getResponsibleInstitutes()
    {
        if ($this->responsible_institute) {
            $inst = Institute::find($this->responsible_institute->institut_id);
            if ($inst) {
                return [$inst];
            }
        }
        return parent::getResponsibleInstitutes();
    }

    /**
     * Returns a "deep" copy of this object.
     *
     * @param boolean $deep Copy all assigned modulteile if true
     * @return Modul A copy of this module.
     */
    public function copy($deep = true, $with_assignments = false)
    {
        $copy = clone $this;
        $copy->setNew(true);
        $copy->setNewId();

        // reset flexnow id because it's a unique foreign key.
        $copy->flexnow_modul = '';
        if ($this->responsible_institute) {
            $copy->responsible_institute = clone $this->responsible_institute;
            $copy->responsible_institute->modul_id = $copy->id;
            $copy->responsible_institute->setNew(true);
        }
        
        $copy->deskriptoren = clone $this->deskriptoren;
        $copy->deskriptoren->modul_id = $copy->id;
        $copy->deskriptoren->setNewId();
        $copy->deskriptoren->setNew(true);
        
        $institutes = [];
        foreach ($this->assigned_institutes as $assigned_institute) {
            $cloned_inst = clone $assigned_institute;
            $cloned_inst->modul_id = $copy->id;
            $cloned_inst->setNew(true);
            $institutes[] = $cloned_inst;
        }
        $copy->assigned_institutes = SimpleORMapCollection::createFromArray($institutes);

        $users = [];
        foreach ($this->assigned_users as $user) {
            $position = 1;
            $cloned_user = clone $user;
            $cloned_user->position = $position++;
            $cloned_user->setNew(true);
            $users[] = $cloned_user;
        }
        $copy->assigned_users = SimpleORMapCollection::createFromArray($users);

        $languages = [];
        foreach ($this->languages as $assigned_language) {
            $cloned_language = clone $assigned_language;
            $cloned_language->setNew(true);
            $languages[] = $cloned_language;
        }
        $copy->languages = SimpleORMapCollection::createFromArray($languages);

        if ($deep) {
            $modulteile = [];
            $position = 1;
            foreach ($this->modulteile as $modulteil) {
                $modulteil_copy = $modulteil->copy(true, $with_assignments);
                $modulteil_copy->position = $position++;
                $modulteile[] = $modulteil_copy;
            }
            $copy->modulteile = SimpleORMapCollection::createFromArray($modulteile);

            if ($with_assignments) {
                $abschnitte_modul = [];
                foreach ($this->abschnitte_modul as $abschnitt_modul) {
                    $cloned_abschnitt_modul = clone $abschnitt_modul;
                    $cloned_abschnitt_modul->setNew(true);
                    $abschnitte_modul[] = $cloned_abschnitt_modul;
                }
                $copy->abschnitte_modul = SimpleORMapCollection::createFromArray($abschnitte_modul);
            }
        }
        return $copy;
    }

    public static function findBySearchTerm($term, $filter = null)
    {
        $term = '%' . $term . '%';
        return parent::getEnrichedByQuery("
                SELECT mvv_modul.*,
                    CONCAT(mvv_modul_deskriptor.bezeichnung, ' (', code, ')') AS name
                FROM mvv_modul
                    LEFT JOIN mvv_modul_deskriptor USING(modul_id)
                    LEFT JOIN mvv_modul_inst
                        ON (mvv_modul.modul_id = mvv_modul_inst.modul_id)
                    LEFT JOIN semester_data as start_sem
                        ON (mvv_modul.start = start_sem.semester_id)
                    LEFT JOIN semester_data as end_sem
                        ON (mvv_modul.end = end_sem.semester_id)
                WHERE (code LIKE :term OR mvv_modul_deskriptor.bezeichnung LIKE :term) "
                . self::getFilterSql($filter) . "
                ORDER BY name",
                [':term' => $term]
        );
    }

    /**
     * Returns all modules assigned to the given Studiengangteil-Abschnitt.
     *
     * @param string $abschnitt_id The id of a Studiengangteil-Abschnitt
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return object A SimpleORMapCollection of modules.
     */
    public static function findByStgteilAbschnitt($abschnitt_id, $filter)
    {
        return parent::getEnrichedByQuery('
                SELECT mvv_modul.* FROM mvv_modul 
                LEFT JOIN mvv_stgteilabschnitt_modul USING(modul_id) 
                LEFT JOIN semester_data start_sem 
                ON (mvv_modul.start = start_sem.semester_id) 
                LEFT JOIN semester_data end_sem 
                ON (mvv_modul.end = end_sem.semester_id) 
                WHERE mvv_stgteilabschnitt_modul.abschnitt_id = ? '
                . self::getFilterSql($filter) . '
                ORDER BY position, mkdate',
            [$abschnitt_id]
        );
    }

    /**
     * Primarily to find Module by Institute. Possible filters are all fields
     * of the tables mvv_modul, mvv_modulteil, mvv_modul_inst and
     * mvv_modul_deskriptor.
     *
     * Possible fileds to sort by are count_modulteile, bezeichnung (the name
     * of the modul dereived from the descriptor in the default language) and
     * all fields of table mvv_modul.
     *
     * @param string $sortby
     * @param string $order
     * @param array $filter
     * @param int $row_count
     * @param int $offset
     * @return array Array of Module.
     */
    public static function findByInstitut($sortby = 'chdate', $order = 'ASC',
            $filter = [], $row_count = null, $offset = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'chdate',
                ['count_modulteile', 'bezeichnung']);
        return parent::getEnrichedByQuery('
                SELECT mvv_modul.*, mvv_modul_deskriptor.bezeichnung, 
                    COUNT(DISTINCT(mvv_modulteil.modulteil_id)) AS count_modulteile 
                FROM mvv_modul 
                    LEFT JOIN mvv_modulteil 
                        ON mvv_modul.modul_id = mvv_modulteil.modul_id 
                    INNER JOIN mvv_modul_inst 
                        ON mvv_modul.modul_id = mvv_modul_inst.modul_id 
                    LEFT JOIN mvv_modul_deskriptor 
                        ON mvv_modul_deskriptor.modul_id =  mvv_modul.modul_id 
                    LEFT JOIN semester_data start_sem 
                        ON (mvv_modul.start = start_sem.semester_id) 
                    LEFT JOIN semester_data end_sem 
                        ON (mvv_modul.end = end_sem.semester_id) '
                . self::getFilterSql($filter, true) .'
                GROUP BY modul_id 
                ORDER BY ' . $sortby,
            [],
            $row_count,
            $offset
        );
    }

    /**
     * Returns all modules the given LV-Gruppe is assigned to at least
     * one Modulteile.
     *
     * @param string $lvgruppe_id The id of a LV-Gruppe.
     * @return object A SimpleORMapCollection of modules.
     */
    public static function findByLvgruppe($lvgruppe_id)
    {
        return parent::getEnrichedByQuery('
            SELECT mm.* 
            FROM mvv_modul mm 
                LEFT JOIN mvv_modulteil mmt USING(modul_id) 
                LEFT JOIN mvv_lvgruppe_modulteil mlm USING(modulteil_id) 
            WHERE mlm.lvgruppe_id = ? ',
            [$lvgruppe_id]
        );
    }

    /**
     * Returns all Institutes assigned to the given modules.
     *
     * @param string $sortby Field to sort by.
     * @param string $order Order of sorting (ASC or DESC).
     * @param array $modul_ids Ids of modules.
     * @return object a SimpleORMapColection of institutes.
     */
    public static function getAssignedInstitutes($sortby = 'name',
            $order = 'ASC', $modul_ids = [])
    {
        return self::getAllAssignedInstitutes($sortby, $order,
                ['mvv_modul.modul_id' => $modul_ids]);
    }

    /**
     * Returns all institutes assigned to Module. Sorted and filtered by
     * optional parameters.
     *
     * @param string $sortby DB field to sort by.
     * @param string $order ASC or DESC
     * @param array $filter Array of filter.
     * @return array Array of found Fachbereiche.
     */
    public static function getAllAssignedInstitutes($sortby = 'name',
            $order = 'ASC', $filter = null, $row_count = null, $offset = null)
    {
        $sortby = Fachbereich::createSortStatement($sortby, $order, 'name',
                ['count_objects']);
        return Fachbereich::getEnrichedByQuery('
                SELECT Institute.*, 
                    Institute.Name as `name`, 
                    Institute.Institut_id AS institut_id, 
                    COUNT(DISTINCT modul_id) as count_objects 
                FROM Institute 
                    INNER JOIN mvv_modul_inst 
                        ON Institute.Institut_id = mvv_modul_inst.institut_id 
                    INNER JOIN mvv_modul USING(modul_id) 
                    LEFT JOIN semester_data start_sem 
                        ON (mvv_modul.start = start_sem.semester_id) 
                    LEFT JOIN semester_data end_sem 
                        ON (mvv_modul.end = end_sem.semester_id) 
                '.Fachbereich::getFilterSql($filter, true).'
                GROUP BY institut_id ORDER BY ' . $sortby,
                [],
            $row_count,
            $offset
        );
    }

    /**
     * Returns an array with all types of status found by given
     * modul ids as key and the number of associated module as
     * value.
     *
     * @see mvv_config.php for defined status.
     * @param array $modul_ids
     * @return array An array with status key as key and an array of name of
     * status and number of Module with this status.
     */
    public static function findStatusByIds($modul_ids = null)
    {
        if (is_array($modul_ids)) {
            $stmt = DBManager::get()->prepare("
                SELECT IFNULL(stat, '__undefined__') AS stat, 
                COUNT(modul_id) AS count_module 
                FROM mvv_modul WHERE modul_id IN (?) 
                GROUP BY stat
            ");
            $stmt->execute([$modul_ids]);
        } else {
            $stmt = DBManager::get()->prepare("
                SELECT IFNULL(stat, '__undefined__') AS stat, 
                COUNT(modul_id) AS count_module 
                FROM mvv_modul GROUP BY stat
            ");
            $stmt->execute();
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $status) {
            $result[$status['stat']] = [
                'name' => $GLOBALS['MVV_MODUL']['STATUS']['values'][$status['stat']]['name'],
                'count_objects' => $status['count_module']
            ];
        }
        return $result;
    }

    /**
     * Returns an array with ids of all modules found by the given filter.
     * The fields from tables mvv_modul and mvv_modul_inst are possible filter
     * options.
     * If no filter is set an empty array will be returned.
     *
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return array An array of Modul ids.
     */
    public static function findByFilter($filter)
    {
        $filter_sql = self::getFilterSql($filter, true);
        if ($filter_sql == '') {
            return [];
        }
        $stmt = DBManager::get()->prepare('
            SELECT DISTINCT mvv_modul.modul_id 
            FROM mvv_modul 
                LEFT JOIN mvv_modulteil 
                    ON mvv_modul.modul_id = mvv_modulteil.modul_id 
                LEFT JOIN mvv_modul_inst 
                    ON (mvv_modul.modul_id = mvv_modul_inst.modul_id) 
                LEFT JOIN semester_data start_sem 
                    ON (mvv_modul.start = start_sem.semester_id) 
                LEFT JOIN semester_data end_sem 
                    ON (mvv_modul.end = end_sem.semester_id) '
            . $filter_sql
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Retrieves all modules this module ia a variant of.
     *
     * @return array An array of all variants.
     */
    public function getVariants()
    {
        $variants = [];
        foreach (Modul::findBySql('variante = '
                . DBManager::get()->quote($this->getId())) as $variant) {
            $variants[$variant->getId()] = $variant;
        }
        return $variants;
    }

    /**
     * Search modules by search term. This function is used in the
     * search frontend for modules.
     *
     * @param string $search_term
     * @param boolean $only_public If true search only for modules
     * with public status.
     */
    public static function search($search_term, $only_public = true)
    {
        $term = '%' . $search_term . '%';
        if ($only_public) {
            $public_status = ModuleManagementModel::getPublicStatus('Modul');
            if (count($public_status)) {
                $stmt = DBManager::get()->prepare('
                    SELECT mm.modul_id 
                    FROM mvv_modul mm 
                        INNER JOIN mvv_modul_deskriptor mmd USING(modul_id) 
                        LEFT JOIN mvv_stgteilabschnitt_modul msm ON mmd.modul_id = msm.modul_id 
                        LEFT JOIN mvv_stgteilabschnitt USING(abschnitt_id) 
                        LEFT JOIN mvv_stgteilversion msv USING(version_id) 
                    WHERE (
                            mm.code LIKE :term 
                            OR mmd.bezeichnung LIKE :term 
                            OR msm.bezeichnung LIKE :term
                        )
                        AND mm.stat IN (:stat) 
                        AND msv.stat IN (:stat) 
                    GROUP BY mm.modul_id
                ');
                $stmt->execute([':term' => $term, ':stat' => $public_status]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } else {
            $stmt = DBManager::get()->prepare('
                SELECT mm.modul_id 
                FROM mvv_modul mm 
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id) 
                WHERE code LIKE :term OR mmd.bezeichnung LIKE :term 
                GROUP BY modul_id
            ');
            $stmt->execute([':term' => $term]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return [];
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = [];
            $rejected = false;
            if ($this->variante) {
                $variante = Modul::find($this->variante);
                if (is_null($variante)) {
                    $ret['variante'] = true;
                    $messages[] = _('Unbekanntes Modul als Vorlage.');
                    $rejected = true;
                }
            }
            if (!$this->responsible_institute || !$this->responsible_institute->institut_id) {
                $ret['rsponsible_institute'] = true;
                $messages[] = _('Es muss mindestens eine verantwortliche Einrichtung zugewiesen werden.');
                $rejected = true;
            } else {
                $this->responsible_institute->validate();
            }
            if ($this->start) {
                $start_sem = Semester::find($this->start);
                if (!$start_sem) {
                    $ret['start'] = true;
                    $messages[] = _('Ungültiges Semester.');
                    $rejected = true;
                } else if ($this->end) {
                    $end_sem = Semester::find($this->end);
                    if ($end_sem) {
                        if ($start_sem->beginn > $end_sem->beginn) {
                            $ret['start'] = true;
                            $messages[] = _('Das Endsemester muss nach dem Startsemester liegen.');
                            $rejected = true;
                        }
                    } else {
                        $ret['end'] = true;
                        $messages[] = _('Ungültiges Endsemester.');
                        $rejected = true;
                    }
                }
            }  else {
                $ret['start'] = true;
                $messages[] = _('Bitte ein Startsemester angeben.');
                $rejected = true;
            }
            if (mb_strlen($this->code) < 3) {
                $ret['code'] = true;
                $messages[] = _('Der Modulcode ist zu kurz (mindestens 3 Zeichen).');
                $rejected = true;
            } else {
                if ($this->isNew()) {
                    // The code of the Modul has to be unique
                    $existing = $this->findBySql('code = ' . DBManager::get()->quote($this->code));
                    if (sizeof($existing)) {
                        $ret['code'] = true;
                        $messages[] = sprintf(_('Es existiert bereits ein Modul mit dem Code "%s"!'),
                                $this->code);
                        $rejected = true;
                    }
                }
            }
            if (!(preg_match('/\d{0,2}/', $this->dauer) && $this->dauer >= 1)) {
                $ret['dauer'] = true;
                $messages[] = _('Die Dauer (in Semestern) des Moduls muss angegeben werden.');
                $rejected = true;
            }
            if (!((preg_match('/\d{0,4}/', $this->kapazitaet)
                    && $this->kapazitaet > 0) || $this->kapazitaet === '')) {
                $ret['kapazitaet'] = true;
                $messages[] = _('Die Kapazität/Teilnehmendenzahl des Moduls muss angegeben werden.');
                $rejected = true;
            }
            if (!(preg_match('/\d{1,3}/', $this->kp) && $this->kp >= 1)) {
                $ret['kp'] = true;
                $messages[] = _('Die Kreditpunkte müssen angegeben werden.');
                $rejected = true;
            }
            if (!(is_float($this->faktor_note * 1.0) && $this->faktor_note >= 0.1)) {
                $ret['faktor_note'] = true;
                $messages[] = _('Der Notenfaktor für die Endnote des Studiengangs muss angegeben werden.');
                $rejected = true;
            }
            if ($this->fassung_nr) {
                if (!is_numeric($this->fassung_nr)) {
                    $ret['fassung_nr'] = true;
                    $messages[] = _('Für Fassung bitte eine Zahl angeben.');
                    $rejected = true;
                }
                if (!$GLOBALS['MVV_MODUL']['FASSUNG_TYP'][$this->fassung_typ]) {
                    $ret['fassung_typ'] = true;
                    $messages[] = _('Bitte einen Typ der Fassung angeben.');
                    $rejected = true;
                }
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
            $this->deskriptoren->validate();
            foreach ($this->assigned_institutes as $assigned_institute) {
                $assigned_institute->validate();
            }
            foreach ($this->assigned_users as $assigned_user) {
                $assigned_user->validate();
            }
        }
        return $ret;
    }

    /**
    * Checks if modules with public status are available.
    *
    * @return boolean true if modules with public status available
    */
    public static function publicModulesAvailable()
    {
        $public_status = ModuleManagementModel::getPublicStatus('Modul');
        if (count($public_status)) {
            $stmt = DBManager::get()->prepare('
                SELECT 1 
                FROM mvv_modul mm 
                    INNER JOIN mvv_modul_deskriptor mmd USING(modul_id) 
                    INNER JOIN mvv_stgteilabschnitt_modul msm ON mmd.modul_id = msm.modul_id 
                    INNER JOIN mvv_stgteilabschnitt USING(abschnitt_id) 
                    INNER JOIN mvv_stgteilversion msv USING(version_id) 
                WHERE mm.stat IN (:stat) 
                    AND msv.stat IN (:stat) LIMIT 1
            ');
            $stmt->execute([':term' => $term, ':stat' => $public_status]);
            return (bool)$stmt->fetchColumn();
        }
        return false;
    }
    
    /**
     * Retrieves all courses this Modul is assigned by its parts and assigned
     * LV-Gruppen.
     * Filtered by a given semester considering the global visibility or the
     * the visibility for a given user.
     * 
     * @param string $semester_id The id of a semester.
     * @param mixed $only_visible Boolean true retrieves only visible courses, false
     * retrieves all courses. If $only_visible is an user id it depends on the users
     * status which courses will be retrieved.
     * @return array An array of course data.
     */
    public function getAssignedCoursesBySemester($semester_id, $only_visible = true)
    {
        $courses = [];
        foreach ($this->modulteile as $modulteil) {
            $mt_courses = $modulteil->getAssignedCoursesBySemester($semester_id, $only_visible);
            foreach ($mt_courses as $course) {
                $courses[$course->id] = $course;
            }
        }
        return $courses;
    }
}
