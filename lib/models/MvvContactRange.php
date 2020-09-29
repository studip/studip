<?php
/**
 * MvvContactRange.php
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

class MvvContactRange extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_contacts_ranges';

        $config['belongs_to']['contact'] = array(
            'class_name' => 'MvvContact',
            'foreign_key' => 'contact_id',
            'assoc_func' => 'findCached',
        );

        $config['additional_fields']['count_relations']['get'] = 'countRelations';
        $config['additional_fields']['name']['get'] = 'getDisplayName';
        $config['additional_fields']['contact_status']['get'] = 'getContactStatus';

        parent::configure($config);
    }

    /**
     * Returns the name of the object to display in a specific context..
     *
     * @return string The name for
     */
    public function getDisplayName($options = null)
    {
        return $this->contact->name;
    }

        /**
     * Returns the status of the contact.
     *
     * @return string contact status
     */
    public function getContactStatus()
    {
        return $this->contact->contact_status;
    }

    /**
     * Returns the number of assignments to other MVV objects.
     *
     * @return int Number of assignments.
     */
    public function countRelations()
    {
        return self::countBySql("contact_id = ?", [$this->contact_id]);
    }

    /**
     * Returns all relations of this contact grouped by object types.
     *
     * @return Array Relations ordered by object types
     */
    public function getRelations($filter = null)
    {
        if (is_array($filter)) {
            $ids = MvvContact::getIdsFiltered($filter, true);
            $ids['contact_id'] = $this->contact_id;
            $contacts = self::findBySQL('`contact_id` = :contact_id AND '
                    . '`category` IN (:categories) AND `range_id` IN (:ranges)', $ids);
        } else {
            $contacts = self::findBySQL('contact_id = ?', [$this->contact_id]);
        }
        $zuordnungen = [];
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                $zuordnungen[$contact['range_type']][$contact['range_id']][] = $contact;
            }
        }
        return $zuordnungen;
    }

    /**
     * Returns the number of contacts comply with the given filter parameters.
     *
     * @param array $filter Array of filter parameters
     * @see ModuleManagementModel::getFilterSql()
     * @return int The number of contacts.
     */
    public static function getCount($filter = null)
    {

        $sql = "SELECT COUNT(DISTINCT contact_id) AS 'anz' FROM mvv_contacts
                INNER JOIN auth_user_md5 ON (contact_id = user_id)
                LEFT JOIN Institute ON (contact_id = Institut_id)
                LEFT JOIN mvv_extern_contacts ON (contact_id = extern_contact_id)";

        if ($filter) {
            foreach ($filter as $column => $val) {
                if ($column === null || $val === null) {
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
                    $params[] = $val;
                }
            }
        }

        $ret = DBManager::get()->fetchOne($sql, $params);
        return $ret['anz'];
    }

    /**
     * Returns the rangetype of the contact.
     *
     * @return string Returns the name of the range.
     */
    public function getRangeType()
    {
        return $this->range_type;
    }

        /**
     * Returns the highest current sorting position.
     *
     * @param sting $range_id Id of the mvv object.
     * @return int Number of the highest current sorting position.
     */
    public static function getMaxSortingPos($range_id)
    {
        $ret = DBManager::get()->fetchOne("SELECT MAX(`position`) FROM `mvv_contacts_ranges` WHERE `range_id` = ?", [$range_id]);
        return $ret['MAX(`position`)'];
    }

    /**
     * Returns the range_type of given range.
     *
     * @param sting $range_id Id of the mvv object.
     * @return string range_type
     */
    public static function getRangeTypeByRangeId($range_id)
    {
        $one_contact = self::findOneBySQL('range_id =?', [$range_id]);
        return $one_contact ? $one_contact->range_type : null;
    }

    /**
     * Returns the 'PERSONEN_GRUPPEN' from mvv config of given range type.
     *
     * @param sting $range_type type of the mvv object.
     * @return array PERSONEN_GRUPPEN
     */
    public static function getCategoriesByRangetype($range_type)
    {
        switch ($range_type) {
            case 'Modul':
                return $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'];
            case 'Studiengang':
                return $GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'];
            case 'StudiengangTeil':
                return $GLOBALS['MVV_STGTEIL']['PERSONEN_GRUPPEN']['values'];
            default:
                return array_merge(
                    $GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'],
                    $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values']
                );
        }
    }

    /**
     * Returns the displayname of selected category.
     *
     * @return string displayname
     */
    public function getCategoryDisplayname()
    {
        $cats = self::getCategoriesByRangetype($this->range_type);
        return $cats[$this->category]['name'];
    }

}
