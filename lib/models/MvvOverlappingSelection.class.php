<?php
/**
 * MvvOverlappingSelection.class.php - model class for table mvv_ovl_selections
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2018 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 */

class MvvOverlappingSelection extends SimpleORMap
{

    /**
     * Configures the model.
     * 
     * @param array  $config Configuration
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_ovl_selections';
        $config['belongs_to']['semester'] = [
            'class_name'  => 'Semester',
            'foreign_key' => 'semester_id'
        ];
        $config['belongs_to']['base_version'] = [
            'class_name'  => 'StgteilVersion',
            'foreign_key' => 'base_version_id'
        ];
        $config['belongs_to']['comp_version'] = [
            'class_name'  => 'StgteilVersion',
            'foreign_key' => 'comp_version_id'
        ];
        $config['has_many']['conflicts'] = [
            'class_name'        => 'MvvOverlappingConflict',
            'foreign_key'       => 'id',
            'assoc_foreign_key' => 'selection_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];
        $config['belongs_to']['user'] =  [
            'class_name'  => 'User',
            'foreign_key' => 'user_id'
        ];
        $config['has_many']['excludes'] = [
            'class_name'        => 'MvvOverlappingExclude',
            'foreign_key'       => 'selection_id',
            'assoc_foreign_key' => 'selection_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];
        parent::configure($config);
    }
    
    /**
     * Creates a selection id and stores the selection.
     *
     * @throws UnexpectedValueException if there are forbidden NULL values
     * @return number|boolean
     */
    public function store()
    {
        if ($this->isNew() && $this->selection_id == '') {
            $this->selection_id = self::createSelectionId(
                $this->base_version,
                $this->comp_version,
                $this->fachsems,
                $this->semtypes
            );
        }
        return parent::store();
    }
    
    /**
     * Sets the given Fachsemester. Expects an array or a comma
     * separated list of Fachsemester.
     * 
     * @param array|string $semtypes 
     */
    public function setFachsemester($fachsems)
    {
        if (is_array($fachsems)) {
            sort($fachsems, SORT_NUMERIC);
            $fachsems = implode(',', $fachsems);
        }
        $this->fachsems = $fachsems;
    }
    
    /**
     * Sets the given course types (semtypes). Expects an array or a comma
     * separated list of course types.
     * 
     * @param array|string $semtypes 
     */
    public function setCoursetypes($semtypes)
    {
        if (is_array($semtypes)) {
            sort($semtypes, SORT_NUMERIC);
            $semtypes = implode(',', $semtypes);
        }
        $this->semtypes = $semtypes;
    }
    
    /**
     * Store this selection with its all conflicts.
     *
     * @throws UnexpectedValueException if there are forbidden NULL values
     * @return number|boolean
     */
    public function storeConflicts()
    {
        
        $query = "
            SELECT DISTINCT `cbase`.`metadate_id` AS `cbase_metadate_id`,
                `cbase`.`seminar_id` AS `cbase_seminar_id`,
                `sembase`.`abschnitt_id` AS `sembase_abschnitt_id`,
                `sembase`.`modulteil_id` AS `sembase_modulteil_id`,
                `ccomp`.`metadate_id` AS `ccomp_metadate_id`,
                `ccomp`.`seminar_id` AS `ccomp_seminar_id`,
                `semcomp`.`abschnitt_id` AS `semcomp_abschnitt_id`,
                `semcomp`.`modulteil_id` AS `semcomp_modulteil_id`
            FROM `seminar_cycle_dates` AS `cbase`
                INNER JOIN (
                    SELECT `mvv_lvgruppe_seminar`.`seminar_id`,
                        `mvv_stgteilabschnitt_modul`.`abschnitt_id`,
                        `mvv_modulteil`.`modulteil_id`
                    FROM `mvv_stgteilabschnitt`
                        INNER JOIN `mvv_stgteilabschnitt_modul` USING (`abschnitt_id`)
                        INNER JOIN `mvv_modul` USING (`modul_id`)
                        INNER JOIN `mvv_modulteil` USING (`modul_id`)
                        INNER JOIN `mvv_lvgruppe_modulteil` USING (`modulteil_id`)
                        INNER JOIN `mvv_lvgruppe_seminar` USING (`lvgruppe_id`)
                        INNER JOIN `seminare` USING (`seminar_id`)
                        INNER JOIN `mvv_modulteil_stgteilabschnitt`
                            ON (`mvv_stgteilabschnitt_modul`.`abschnitt_id` =
                                    `mvv_modulteil_stgteilabschnitt`.`abschnitt_id`
                                AND `mvv_modulteil`.`modulteil_id` =
                                    `mvv_modulteil_stgteilabschnitt`.`modulteil_id`)
                        LEFT JOIN `semester_data` AS `start_sem`
                            ON (`mvv_modul`.`start` = `start_sem`.`semester_id`)
                        LEFT JOIN `semester_data` AS `end_sem`
                            ON (`mvv_modul`.`end` = `end_sem`.`semester_id`)
                    WHERE `mvv_stgteilabschnitt`.`version_id` = :base_version
                        AND `mvv_modulteil_stgteilabschnitt`.`fachsemester` IN (:fachsem)
                        AND ((`start_sem`.`beginn` < :sem_end OR ISNULL(`start_sem`.`beginn`))
                            AND (`end_sem`.`ende` > :sem_start OR ISNULL(`end_sem`.`ende`)))
                        AND `seminare`.`status` IN (:typ)
                        AND `seminare`.`start_time` <= :sem_end
                        AND `seminare`.`start_time` + `seminare`.`duration_time` >= :sem_start
                ) AS `sembase` ON (`sembase`.`seminar_id` = `cbase`.`seminar_id`)
                INNER JOIN `seminar_cycle_dates` AS `ccomp`
                    ON (`cbase`.`seminar_id` != `ccomp`.`seminar_id`
                        AND `cbase`.`weekday` = `ccomp`.`weekday`
                        AND `cbase`.`start_time` < `ccomp`.`end_time`
                        AND `cbase`.`end_time` > `ccomp`.`start_time`
                        AND `cbase`.`metadate_id` = (
                            SELECT DISTINCT `metadate_id`
                                FROM `termine`
                                WHERE `termine`.`metadate_id` = `cbase`.`metadate_id`
                                LIMIT 1)
                        AND `ccomp`.`metadate_id` = (
                            SELECT DISTINCT `metadate_id`
                                FROM `termine`
                                WHERE `termine`.`metadate_id` = `ccomp`.`metadate_id`
                                LIMIT 1)
                    )
                INNER JOIN (
                    SELECT `mvv_lvgruppe_seminar`.`seminar_id`,
                        `mvv_stgteilabschnitt_modul`.`abschnitt_id`,
                        `mvv_modulteil`.`modulteil_id`
                    FROM `mvv_stgteilabschnitt`
                        INNER JOIN `mvv_stgteilabschnitt_modul` USING (`abschnitt_id`)
                        INNER JOIN `mvv_modul` USING (`modul_id`)
                        INNER JOIN `mvv_modulteil` USING (`modul_id`)
                        INNER JOIN `mvv_lvgruppe_modulteil` USING (`modulteil_id`)
                        INNER JOIN `mvv_lvgruppe_seminar` USING (`lvgruppe_id`)
                        INNER JOIN `seminare` USING (`seminar_id`)
                        INNER JOIN `mvv_modulteil_stgteilabschnitt`
                            ON (`mvv_stgteilabschnitt_modul`.`abschnitt_id` =
                                    `mvv_modulteil_stgteilabschnitt`.`abschnitt_id`
                                AND `mvv_modulteil`.`modulteil_id` =
                                    `mvv_modulteil_stgteilabschnitt`.`modulteil_id`)
                        LEFT JOIN `semester_data` AS `start_sem`
                            ON (`mvv_modul`.`start` = `start_sem`.`semester_id`)
                        LEFT JOIN `semester_data` AS `end_sem`
                            ON (`mvv_modul`.`end` = `end_sem`.`semester_id`)
                    WHERE `mvv_stgteilabschnitt`.`version_id` = :comp_version
                        AND `mvv_modulteil_stgteilabschnitt`.`fachsemester` IN (:fachsem)
                        AND ((`start_sem`.`beginn` < :sem_end OR ISNULL(`start_sem`.`beginn`))
                            AND (`end_sem`.`ende` > :sem_start OR ISNULL(`end_sem`.`ende`)))
                        AND `seminare`.`status` IN (:typ)
                        AND `seminare`.`start_time` <= :sem_end
                        AND `seminare`.`start_time` + `seminare`.`duration_time` >= :sem_start
                ) AS `semcomp` ON (`semcomp`.`seminar_id` = `ccomp`.`seminar_id`)
                INNER JOIN `mvv_modulteil_stgteilabschnitt` AS `mms1`
                    ON (`mms1`.`abschnitt_id` = `semcomp`.`abschnitt_id` AND `mms1`.`modulteil_id` = `semcomp`.`modulteil_id`)
                    WHERE `mms1`.`fachsemester` IN (
                        SELECT `fachsemester`
                        FROM `mvv_modulteil_stgteilabschnitt` AS `mms2`
                        WHERE `mms2`.`abschnitt_id` = `sembase`.`abschnitt_id`
                            AND `mms2`.`modulteil_id` = `sembase`.`modulteil_id`)
            ORDER BY `cbase_seminar_id`";
        
        // if no filter is set use all types and fachsems
        $fachsems = $this->fachsems ? $this->fachsems : implode(',', range(1, 6));
        $semtypes = $this->semtypes ? $this->semtypes : implode(',', array_keys(SemType::getTypes()));
        
        $db = DBManager::get();
        $conflicts = $db->fetchAll($query, [
            ':base_version' => $this->base_version_id,
            ':comp_version' => $this->comp_version_id,
            ':fachsem'      => explode(',', $fachsems),
            ':typ'          => explode(',', $semtypes),
            ':sem_start'    => $this->semester->beginn,
            ':sem_end'      => $this->semester->ende
        ]);
        
        $conlicts = [];
        foreach ($conflicts as $conflict) {
            $ovl_conflict = new MvvOverlappingConflict();
            $ovl_conflict->selection_id = $this->id;
            $ovl_conflict->base_abschnitt_id = $conflict['sembase_abschnitt_id'];
            $ovl_conflict->base_modulteil_id = $conflict['sembase_modulteil_id'];
            $ovl_conflict->base_course_id    = $conflict['cbase_seminar_id'];
            $ovl_conflict->base_metadate_id  = $conflict['cbase_metadate_id'];
            $ovl_conflict->comp_abschnitt_id = $conflict['semcomp_abschnitt_id'];
            $ovl_conflict->comp_modulteil_id = $conflict['semcomp_modulteil_id'];
            $ovl_conflict->comp_course_id    = $conflict['ccomp_seminar_id'];
            $ovl_conflict->comp_metadate_id  = $conflict['ccomp_metadate_id'];
            $this->conflicts[] = $ovl_conflict;
        }
        return $this->store();
    }
    
    /**
     * Returns all conflicts of all selections with the given selection id.
     * 
     * @param string $selection_id The selection id.
     * @param boolean $only_visible Returns only visible conflicts.
     * @return SimpleORMapCollection All conflicts of appropriate selections.
     */
    public static function getConflictsBySelection($selection_id, $only_visible = false)
    {
        $excluded_courses = [];
        $visible_sql = '';
        if ($only_visible) {
            $excluded_courses = SimpleORMapCollection::createFromArray(
                    MvvOverlappingExclude::findBySelection_id($selection_id))->pluck('course_id');
            if ($excluded_courses) {
                $visible_sql = 'AND `mvv_ovl_conflicts`.`comp_course_id` NOT IN (:course_ids)';
            }
        }
        return SimpleORMapCollection::createFromArray(
            MvvOverlappingConflict::findBySql('LEFT JOIN `mvv_ovl_selections`
                ON (`mvv_ovl_conflicts`.`selection_id` = `mvv_ovl_selections`.`id`)
                WHERE `mvv_ovl_selections`.`selection_id` = :selection_id ' . $visible_sql, [
                ':selection_id' => $selection_id,
                ':course_ids'    => $excluded_courses
            ])
        );
    }
    
    /**
     * Returns a md5 hash over all given parameters.
     * 
     * @param string $base_version The id of the base version.
     * @param string $comp_versions The id of the compared version.
     * @param array|string $fachsems An array or a string with comma separated fachsem numbers.
     * @param array|string $semtypes An array or a string with comma separated course types.
     * @return string The md5 id.
     */
    public static function createSelectionId($base_version, $comp_versions, $fachsems, $semtypes)
    {
        if (is_array($fachsems)) {
            sort($fachsems, SORT_NUMERIC);
            $fachsems = implode(',', $fachsems);
        }
        if (is_array($semtypes)) {
            sort($semtypes, SORT_NUMERIC);
            $semtypes = implode(',', $semtypes);
        }
        if (is_array($comp_versions)) {
            $comp_version_ids = [];
            foreach ($comp_versions as $comp_version) {
                $comp_version_ids[] = $comp_version->id;
            }
            sort($comp_version_ids);
            $comp_versions = implode(',', $comp_version_ids);
        } else {
            $comp_versions = $comp_versions->id;
        }
        return md5(implode('_', [
            $base_version->id,
            $comp_versions,
            trim($fachsems) ? $fachsems : 'x',
            trim($semtypes) ? $semtypes : 'x'
        ]));
    }
    
    /**
     * Returns all excluded (hidden) conflicts of this selection.
     * 
     * @return SimpleORMapCollection The excluded (hidden) conflicts.
     */
    public function getExcludedConflicts()
    {
        return $this->conflicts->findBy(
            'comp_course_id',
            $this->excludes->pluck('course_id')
        );
    }
}
