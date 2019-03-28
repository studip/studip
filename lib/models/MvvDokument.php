<?php
/**
 * MvvDokument.php
 * Model class for Dokumente (table mvv_dokument)
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

class MvvDokument extends ModuleManagementModel
{

    /**
     * The number of assignments of this document.
     * @var int
     */
    private $count_zuordnungen;

    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_dokument';

        $config['has_many']['assignments'] = array(
            'class_name' => 'MvvDokumentZuord',
            'assoc_foreign_key' => 'dokument_id',
            'on_delete' => 'delete'
        );

        $config['additional_fields']['count_zuordnungen']['get'] =
            function($dokument) { return $dokument->count_zuordnungen; };
        
        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['linktext'] = true;
        $config['i18n_fields']['beschreibung'] = true;
        
        parent::configure($config);
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Dokument');
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Dokument');
    }

    /**
     * Finds all documents related to the given object.
     *
     * @param string $object A MVV object
     * @return array Array of documents.
     */
    public static function findByObject(SimpleORMap $object)
    {
        return parent::getEnrichedByQuery('
            SELECT md.*, mdz.position, 
                mdz.kommentar, mdz.mkdate, mdz.chdate 
                FROM mvv_dokument md 
                INNER JOIN mvv_dokument_zuord mdz USING(dokument_id) 
                WHERE mdz.range_id = ? 
                ' . self::getFilterSql(array('mdz.object_type' => get_class($object))) . '
            ORDER BY mdz.position',
            array($object->id)
        );
    }

    /**
     * Finds all documents related to objects of given type.
     *
     * @param string $object_type The type of the objects.
     * @return array Array of documents.
     */
    public static function findByObjectType($object_type)
    {
        return parent::getEnrichedByQuery('
            SELECT md.* 
            FROM mvv_dokument md 
                LEFT JOIN mvv_dokument_zuord mdz USING (dokument_id)
            WHERE mdz.object_type = ?',
            array($object_type)
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
        $sortby = self::createSortStatement($sortby, $order, 'chdate',
                array('count_zuordnungen'));
        return parent::getEnrichedByQuery('
            SELECT mvv_dokument.*, 
                COUNT(mvv_dokument_zuord.range_id) AS `count_zuordnungen` 
            FROM mvv_dokument 
                LEFT JOIN mvv_dokument_zuord USING(dokument_id) 
                ' . self::getFilterSql($filter, true) . '
                GROUP BY mvv_dokument.dokument_id 
                ORDER BY ' . $sortby,
            array(),
            $row_count,
            $offset
        );
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
        return parent::getCountBySql('
            SELECT COUNT(DISTINCT mvv_dokument.dokument_id) 
            FROM mvv_dokument 
                LEFT JOIN mvv_dokument_zuord USING (dokument_id) ',
            $filter
        );
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
        $quoted_term = DBManager::get()->quote('%' . $term . '%');
        return parent::getEnrichedByQuery('
            SELECT dokument_id 
            FROM mvv_dokument 
                LEFT JOIN mvv_dokument_zuord USING(dokument_id) 
            WHERE (name LIKE ' . $quoted_term . '
                OR url LIKE ' . $quoted_term . ') 
                ' . self::getFilterSql($filter)
        );
    }

    /**
     * Returns all relations of this document grouped by object types.
     *
     * @return Array Relations ordered by object types
     */
    public function getRelations()
    {
        $zuordnungen = array();
        $stmt = DBManager::get()->prepare('
            SELECT mdz.* 
            FROM mvv_dokument_zuord AS mdz 
            WHERE dokument_id = ? 
            ORDER BY object_type ASC
        ');
        $stmt->execute(array($this->getID()));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $zuord) {
            $zuordnungen[$zuord['object_type']][$zuord['range_id']] = $zuord;
        }
        return $zuordnungen;
    }

    /**
     * Returns the number of assignments to other MVV objects.
     *
     * @return int Number of assignments.
     */
    public function getCountRelations()
    {
        $stmt = DBManager::get()->prepare('
            SELECT COUNT(range_id) 
            FROM mvv_dokument_zuord 
            WHERE dokument_id = ? 
            GROUP BY dokument_id
        ');
        $stmt->execute(array($this->getId()));
        $result = $stmt->fetchColumn();
        return ($result ? $result : 0);
    }

    /**
     * Returns all assignemnt to objects for this document.
     *
     * @param string $object_id The id of the object.
     * @param string $object_type The type (class name by get_class())
     * of the object.
     * @return array Array of document assignments.
     */
    public function getRelationByObject($object_id, $object_type)
    {
        return MvvDokumentZuord::get(array(
            $this->getId(),
            $object_id,
            $object_type
        ));
    }

    /**
     * Returns all relations of the documents specified by the given ids.
     * The returned array is ordered by the types of the referenced objects.
     *
     * @param array $dokument_ids Ids of the documents.
     * @return array References ordered by object types.
     */
    public static function getAllRelations($dokument_ids = array())
    {
        $zuordnungen = array();
        if (empty($dokument_ids)) {
            $where = '';
            $params = null;
        } else {
            $where = 'WHERE mdz.dokument_id IN(?) ';
            $params = array($dokument_ids);
        }
        $stmt = DBManager::get()->prepare('
            SELECT mdz.* 
            FROM mvv_dokument_zuord mdz 
            ' . $where . '
            ORDER BY object_type ASC
        ');
        $stmt->execute($params);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $zuord) {
            $zuordnungen[$zuord['object_type']][$zuord['range_id']] = $zuord;
        }
        return $zuordnungen;
    }

    /**
     * Updates the assignment of documents to the given object.
     *
     * @param Object $object Assigns the documents to this object.
     * @param array $dokument_ids Array of document object ids.
     * @param array $annotations Array of annotations to the assignment.
     */
    public static function updateDocuments($object, $dokument_ids, $annotations = array()) {
        $assigned_documents = $object->document_assignments->pluck('dokument_id');
        $removed_documents = array_diff($assigned_documents, $dokument_ids);
        $pos = 1;
        foreach ((array) $dokument_ids as $dokument_id) {
            $dokument = $object->document_assignments->findOneBy('dokument_id', $dokument_id);
            if ($dokument) {
                $dokument->kommentar = trim($annotations[$dokument_id]['kommentar']);
                $dokument->position = $pos++;
            } else {
                $dokument = new MvvDokumentZuord();
                $dokument->dokument_id = $dokument_id;
                $dokument->range_id = $object->id;
                $dokument->object_type = get_class($object);
                $dokument->kommentar = trim($annotations[$dokument_id]['kommentar']);
                $dokument->position = $pos++;
                $object->document_assignments->append($dokument);
            }
        }
        $object->document_assignments->unsetBy('dokument_id', $removed_documents);
        $object->document_assignments->orderBy('position', SORT_NUMERIC);
    }

    /**
     * Removes all assignments of this Dokument.
     */
    public static function unassignAllDocuments($object)
    {
        $stmt = DBManager::get()->prepare('
            DELETE FROM mvv_dokument_zuord 
            WHERE range_id = ? 
                AND object_type = ?
        ');
        $stmt->execute(array($object->getId(), get_class($object)));
        if ($stmt->rowCount()) {
            $object->is_dirty = true;
        }
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
            SELECT dokument_id, name FROM mvv_dokument 
            WHERE (name LIKE :input OR url LIKE :input) 
                AND dokument_id NOT IN ('"
            . implode("','", $exclude) . "') 
            ORDER BY name ASC";
        $search = new SQLSearch($query, _('Dokument/Material suchen'));
        $qs_id = md5(serialize($search));
        $qs_html = QuickSearch::get('dokumente', $search)
                    ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                    ->noSelectbox();
        return ['id' => $qs_id, 'html' => $qs_html];
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $rejected = false;
            $messages = array();

            // The name of the Dokument must be longer than 4 characters
            if (mb_strlen($this->name) < 4) {
                $ret['name'] = true;
                $messages[] = _('Der Name des Dokuments ist zu kurz (mindestens 4 Zeichen).');
                $rejected = true;
            }
            if (!preg_match('%^(https?|ftp)://%', $this->url) || $this->url == '') {
                $ret['url'] = true;
                $messages[] = _('Die URL ist ungültig.');
                $rejected = true;
            } else if ($this->isNew()) {
                $dokument = $this->findBySql('url = ' . DBManager::get()->quote($this->url));
                if ($dokument) {
                    $ret['url'] = true;
                    $messages[] = _('Die URL wird bereits von einem anderen Dokument verwendet.');
                    $rejected = true;
                }
            }
            if (mb_strlen($this->linktext) < 3) {
                $ret['linktext'] = true;
                $messages[] = _('Geben Sie einen Text ein, der mit dem Dokument verlinkt wird (min. 3 Zeichen).');
                $rejected = true;
            }

            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }

}
