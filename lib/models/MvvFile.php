<?php
/**
 * MvvFile.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */

class MvvFile extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_files';

        $config['has_many']['file_refs'] = [
            'class_name'  => MvvFileFileref::class,
            'foreign_key' => 'mvvfile_id'
        ];
        $config['has_many']['ranges'] = [
            'class_name'  => MvvFileRange::class,
            'foreign_key' => 'mvvfile_id'
        ];
        $config['additional_fields']['count_relations']['get'] = 'countRelations';

        parent::configure($config);
    }

    /**
     * Finds all documents related to the given object.
     *
     * @param string $object A MVV object
     * @return array Array of documents.
     */
    public static function findByObject(SimpleORMap $object)
    {
        $condition = self::getFilterSql(['mdz.range_type' => get_class($object)]);
        $query = "SELECT md.*, mdz.position, mdz.mkdate, mdz.chdate
                  FROM mvv_files md
                  INNER JOIN mvv_files_ranges mdz USING(mvvfile_id)
                  WHERE mdz.range_id = ? {$condition}
                  ORDER BY mdz.position";
        return parent::getEnrichedByQuery($query, [$object->id]);
    }

    public static function findByRange_id($range_id)
    {
        return self::findBySQL('JOIN mvv_files_ranges USING (mvvfile_id) WHERE range_id =? ORDER BY position ASC', [$range_id]);
    }

    /**
     * Returns the name of the object to display in a specific context..
     *
     * @return string The name for
     */
    public function getDisplayName($options = null)
    {
        if ($this->file_refs) {
            return $this->file_refs[0]->name;
        }
        return '';
    }

    /**
     * Returns the name of the rangetype this mvvfile is bound to.
     *
     * @return string type of range
     */
    public function getRangeType()
    {
        if ($this->ranges) {
            return $this->ranges[0]->range_type;
        }
        return '';
    }

    /**
     * Returns the number of assignments to other MVV objects.
     *
     * @return int Number of assignments.
     */
    public function countRelations()
    {
        return MvvFileRange::countBySql('mvvfile_id = ?', [$this->mvvfile_id]);
    }

    /**
     * Returns the position in given range.
     *
     * @return int position.
     */
    public function getPositionInRange($range_id)
    {
        return MvvFileRange::find([$this->mvvfile_id, $range_id])->position;
    }

    /**
     * Returns the ids of related ranges.
     *
     * @return array Ids of related ranges.
     */
    public function getRangesArray()
    {
        return MvvFileRange::findAndMapBySQL(
            function ($range) {
                return $range->range_id;
            },
            'mvvfile_id = ?',
            [$this->mvvfile_id]
        );
    }

    /**
     * Returns the filenames of related filerefs.
     *
     * @return string available filenames.
     */
    public function getFilenames()
    {
        return MvvFileFileref::findAndMapBySQL(
            function ($ref) {
                return $ref->getFilename();
            },
            'mvvfile_id = ?',
            [$this->mvvfile_id]
        );
    }

    /**
     * Returns the filetypes of related filerefs.
     *
     * @return string available filetypes.
     */
    public function getFiletypes()
    {
        return MvvFileFileref::findAndMapBySQL(
            function ($ref) {
                return $ref->getFileType();
            },
            'mvvfile_id = ?',
            [$this->mvvfile_id]
        );
    }


    /**
     * Returns all or a specified (by row count and offset) number of
     * documents sorted and filtered by given parameters and enriched with
     * some additional fields. This function is mainly used in the list view.
     *
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return object A SimpleORMapCollection of Dokument objects.
     */
    public static function getAllEnriched($sortby = 'chdate', $order = 'DESC',
            $row_count = null, $offset = null, $filter = null)
    {
        $sortby = self::createSortStatement(
            $sortby,
            $order,
            'mvvfile_id',
            ['count_zuordnungen', 'mvv_files_filerefs.name', 'file_refs.name']
        );

        $parameters = [
            ':ranges' => self::getIdsFiltered($filter),
        ];

        $name_filter_sql = '';
        if ($filter['searchnames']) {
            $name_filter_sql = " AND CONCAT_WS(' ', `file_refs`.`name`, `mvv_files_filerefs`.`name`, `mvv_files`.`category`,`mvv_files`.`tags`) LIKE :needle";
            $parameters[':needle'] = "%{$filter['searchnames']}%";
        }
        unset($filter['searchnames']);

        $query = "SELECT `mvv_files`.*,
                         COUNT(`mvv_files_ranges`.`range_id`) AS `count_relations`,
                         `file_refs`.`name` AS `filename`,
                         `mvv_files_ranges`.`range_type` AS `range_type`
                  FROM `mvv_files`
                  LEFT JOIN `mvv_files_ranges` USING (`mvvfile_id`)
                  LEFT JOIN `mvv_files_filerefs` USING (`mvvfile_id`)
                  INNER JOIN `file_refs` ON (`fileref_id` = `file_refs`.`id`)
                  INNER JOIN `folders` ON (file_refs.folder_id = folders.id)
                  WHERE `mvv_files_ranges`.`range_id` IN (:ranges) {$name_filter_sql}
                  GROUP BY `mvvfile_id`
                  ORDER BY {$sortby}";
        return parent::getEnrichedByQuery($query, $parameters, $row_count, $offset);
    }

    /**
     * Returns all relations of the documents specified by the given ids.
     * The returned array is ordered by the types of the referenced objects.
     *
     * @param array $dokument_ids Ids of the documents.
     * @return array References ordered by object types.
     */
    public static function getAllRelations($dokument_ids = [])
    {
        $files = [];
        if ($dokument_ids) {
            foreach ($dokument_ids as $dokument_id) {
                foreach (self::findBySQL('mvvfile_id = ?', [$dokument_id]) as $mvv_file) {
                    $files[] = $mvv_file;
                }
            }
        } else {
            $files = self::findBySQL('1');
        }
        $zuordnungen = [];
        foreach ($files as $file) {
            foreach ($file->ranges as $file_range) {
                $zuordnungen[$file_range['range_type']][$file_range['range_id']] = $file;
            }
        }
        return $zuordnungen;
    }

    /**
     * Returns all relations of this document grouped by object types.
     *
     * @return Array Relations ordered by object types
     */
    public function getRelations()
    {
        $zuordnungen = [];
        foreach (MvvFileRange::findBySQL('mvvfile_id =?', [$this->mvvfile_id]) as $range) {
            $zuordnungen[$range['range_type']][$range['range_id']] = $range;
        }
        return $zuordnungen;
    }

    /**
     * Find Documents by given search term.
     * Used as search function in list view.
     *
     * @param type $term The search term.
     * @param type $filter Optional filter parameters.
     * @return array An array of Dokument ids.
     */
    public static function findBySearchTerm($term, $filter = null)
    {
        $sql = "LEFT JOIN mvv_files_ranges USING (mvvfile_id)
                LEFT JOIN mvv_files_filerefs USING (mvvfile_id)
                LEFT JOIN file_refs ON (fileref_id = file_refs.id)
                LEFT JOIN folders ON (file_refs.folder_id = folders.id)
                WHERE (mvv_files_filerefs.name LIKE CONCAT('%', :term, '%') OR file_refs.name LIKE CONCAT('%', :term, '%')) GROUP BY mvvfile_id";
        $params['term'] = $term;

        /* if ($filter) {
            foreach ($filter as $column => $val) {
                if ($column == null || $val == null) {
                    continue;
                }
                if (is_array($val)) {
                    $sql .= ' AND '. $column . ' IN('
                    . join(',', array_map(
                        function ($val) {
                            return DBManager::get()->quote($val);
                        }, $val))
                    . ') ';
                } else {
                    $sql .= ' AND '.$column.' = ? ';
                    //$params[] = $column;
                    $params[] = $val;
                }
            }
        } */

        return SimpleORMapCollection::createFromArray(self::findBySQL($sql, $params));
    }



    /**
     * Returns the number of Documents comply with the given filter parameters.
     *
     * @param array $filter Array of filter parameters
     * @see ModuleManagementModel::getFilterSql()
     * @return int The number of Documents.
     */
    public static function getCount($filter = null)
    {
        if (empty($filter)) {
            return parent::getCount();
        }
        $searchnames = $filter['searchnames'];
        $name_filter_sql = '';
        if ($searchnames) {
            $name_filter_sql = " AND CONCAT_WS(' ', `file_refs`.`name`, `mvv_files_filerefs`.`name`, `mvv_files`.`category`,`mvv_files`.`tags`) LIKE ". DBManager::get()->quote('%' . $searchnames . '%');
        }
        unset($filter['searchnames']);
        $ids = self::getIdsFiltered($filter);
        return parent::getCountBySql("
            SELECT COUNT(DISTINCT `mvv_files`.`mvvfile_id`)
            FROM `mvv_files_ranges`
            INNER JOIN `mvv_files` USING(`mvvfile_id`)
            INNER JOIN `mvv_files_filerefs` USING(`mvvfile_id`)
            INNER JOIN `file_refs` ON `fileref_id` = `file_refs`.`id`
            WHERE `mvv_files_ranges`.`range_id` IN ('" . implode("','", $ids) . "') $name_filter_sql");
    }

    /**
     * Returns a ready to use quick search widget.
     *
     * @param array $exclude Ids of documents excluded from search.
     * @return array Array with quick search id and quick search html.
     */
    public static function getQuickSearch($exclude = array())
    {
        $query = "
            SELECT mvv_files.mvvfile_id, mvv_files_filerefs.name
            FROM mvv_files
            WHERE (mvv_files_filerefs.name LIKE :input OR file_refs.name LIKE :input)
                AND mvv_files.fileref_id NOT IN ('"
            . implode("','", $exclude) . "')
            ORDER BY name ASC";
        $search = new SQLSearch($query, _('Dokument suchen'));
        $qs_id = md5(serialize($search));
        $qs_html = QuickSearch::get('dokumente', $search)
                    ->fireJSFunctionOnSelect('STUDIP.MVV.Search.addSelected')
                    ->noSelectbox();
        return ['id' => $qs_id, 'html' => $qs_html];
    }

    /**
     * Returns the highest current sorting position.
     *
     * @param sting $range_id Id of the mvv object.
     * @return int Number of the highest current sorting position.
     */
    public static function getMaxSortingPos($range_id)
    {
        $ret = DBManager::get()->fetchOne("
            SELECT MAX(`position`)
            FROM `mvv_files_ranges`
            JOIN mvv_files USING (`mvvfile_id`) WHERE `range_id` = ?;", [$range_id]);
        return $ret['MAX(`position`)'];
    }

    /**
     * Adds this mvvfile to given range.
     *
     * @param sting $range_id Id of the mvv object.
     */
    public function addToRange($range_id, $range_type)
    {
        $mvvfile_range = new MvvFileRange([$this->mvvfile_id, $range_id]);
        $mvvfile_range->range_type = $range_type;
        if ($mvvfile_range->isNew()) {
            $mvvfile_range->position = self::getMaxSortingPos($range_id) + 1;
        }
        $mvvfile_range->store();
    }

    /**
     * Removes this mvvfile from given range.
     *
     * @param sting $range_id Id of the mvv object.
     */
    public function removeFromRange($range_id)
    {
        if ($mvvfile_range = MvvFileRange::find([$this->mvvfile_id, $range_id])) {
            $vacant = $mvvfile_range->position;
            if ($mvvfile_range->delete()) {
                foreach (MvvFileRange::findBySQL('range_id = ? ORDER BY position ASC',[$range_id]) as $other_range) {
                    if ($other_range->position > $vacant) {
                        $tmp = $other_range->position;
                        $other_range->position = $vacant;
                        $other_range->store();
                        $vacant = $tmp;
                    }
                }
            }
        }
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = array();
            $rejected = false;

            /* if (!$this->name) {
                $ret['name'] = true;
                $messages[] = _('Es muss ein Anzeigename für das Dokument angegeben werden.');
                $rejected = true;
            } */

            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }

    /**
     * Returns all institutes assigned to files. Sorted and filtered by
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

        return Fachbereich::getEnrichedByQuery("
            SELECT `Institute`.*,
                `Institute`.`Name` as `name`,
                `Institute`.`Institut_id` AS `institut_id`,
                COUNT(DISTINCT `mvv_files`.`mvvfile_id`) as `count_objects`
            FROM `mvv_files`
                LEFT JOIN `mvv_files_ranges` USING(`mvvfile_id`)
                LEFT JOIN `mvv_abschl_zuord` ON (`mvv_abschl_zuord`.`kategorie_id` = `mvv_files_ranges`.`range_id`)
                INNER JOIN `abschluss` USING(`abschluss_id`)
                LEFT JOIN `mvv_studiengang` ON (`mvv_studiengang`.`abschluss_id` = `abschluss`.`abschluss_id`
                    OR `mvv_studiengang`.`studiengang_id` = `mvv_files_ranges`.`range_id`)
                INNER JOIN `Institute` USING(`institut_id`)
                LEFT JOIN `semester_data` `start_sem`
                    ON (`mvv_studiengang`.`start` = `start_sem`.`semester_id`)
                LEFT JOIN `semester_data` `end_sem`
                    ON (`mvv_studiengang`.`end` = `end_sem`.`semester_id`)"
            . Fachbereich::getFilterSql($filter, true) . "
            GROUP BY `institut_id` ORDER BY " . $sortby, [], $row_count, $offset
        );
    }

    /**
     * Returns range_ids (ids of Studiengang/Abschluss-Kategorie) of assigned files.
     *
     * @staticvar array $ids The range_ids of assigned files after filtration.
     * @param array $filter An array with keys  (table_name.column_name) and
     * values (skalar or array) used in where clause.
     * @param boolean $refresh Refresh ids if true.
     * @return array An array with range_ids of assigned files.
     */
    public static function getIdsFiltered($filter, $refresh = false)
    {
        static $ids = null;

        if (is_array($ids) && !$refresh) {
            return $ids;
        }

        $sql = "SELECT DISTINCT `mvv_files_ranges`.`range_id`
                FROM `mvv_files`
                    LEFT JOIN `mvv_files_ranges` USING (`mvvfile_id`)
                    LEFT JOIN `mvv_abschl_zuord` ON (`mvv_abschl_zuord`.`kategorie_id` = `mvv_files_ranges`.`range_id`)
                    LEFT JOIN `abschluss` USING(`abschluss_id`)
                    LEFT JOIN `mvv_studiengang` ON (`mvv_studiengang`.`abschluss_id` = `abschluss`.`abschluss_id`
                        OR `mvv_studiengang`.`studiengang_id` = `mvv_files_ranges`.`range_id`)
                    LEFT JOIN `semester_data` `start_sem`
                        ON (`mvv_studiengang`.`start` = `start_sem`.`semester_id`)
                    LEFT JOIN `semester_data` `end_sem`
                        ON (`mvv_studiengang`.`end` = `end_sem`.`semester_id`)"
                    . self::getFilterSql($filter, true);

        $stm = DBManager::get()->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_COLUMN, 0);
    }

}
