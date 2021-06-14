<?php
/**
 * MvvContact.php
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

class MvvContact extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_contacts';

        $config['has_many']['ranges'] = [
            'class_name'  => 'MvvContactRange',
            'foreign_key' => 'contact_id',
            'on_delete'   => 'delete'
        ];

        $config['additional_fields']['name']['get'] = 'getContactName';
        $config['additional_fields']['count_relations']['get'] = function ($contact) {
            return $contact->count_relations;
        };

        parent::configure($config);
    }

    /**
     * Returns the name of the object to display in a specific context..
     *
     * @return string The name for
     */
    public function getDisplayName($options = null)
    {
        return $this->name;
    }

    /**
     * Returns the name of the contact based on contact type.
     *
     * @return bool|string Returns false on failure, otherwise the name of contact.
     */
    public function getContactName()
    {
        switch ($this->contact_status) {
            case 'extern':
                $contact = MvvExternContact::findCached($this->contact_id);
                return $contact->vorname ? $contact->name.', '.$contact->vorname : $contact->name;
            case 'intern':
                $contact = User::find($this->contact_id);
                return $contact->Nachname . ', ' . $contact->Vorname;
            case 'institution':
                $contact = Institute::find($this->contact_id);
                return $contact->Name;
            default:
                return false;
        }
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * contacts sorted and filtered by given parameters and enriched with
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

        $sortby = self::createSortStatement($sortby, $order, 'name',
                ['count_zuordnungen']);

        $ids = self::getIdsFiltered($filter);
        return parent::getEnrichedByQuery("
            SELECT *, COUNT(CONCAT(`range_id`,`category`)) AS `count_relations`
            FROM (
                SELECT `mvv_contacts`.*, `mvv_contacts_ranges`.`range_id`,
                    `mvv_contacts_ranges`.`category`,
                    `mvv_extern_contacts`.`name` AS `name`
                FROM `mvv_contacts`
                    INNER JOIN `mvv_contacts_ranges` USING (`contact_id`)
                    INNER JOIN `mvv_extern_contacts` ON (`mvv_contacts`.`contact_id` = `extern_contact_id`)
                WHERE `mvv_contacts_ranges`.`contact_id` IN (:contacts)
                    AND `mvv_contacts_ranges`.`range_id` IN (:ranges)
                    AND `mvv_contacts_ranges`.`category` IN (:categories)
            UNION
                SELECT `mvv_contacts`.*, `mvv_contacts_ranges`.`range_id`,
                    `mvv_contacts_ranges`.`category`,
                    CONCAT(`auth_user_md5`.`Nachname`, ', ', `auth_user_md5`.`Vorname`) AS `name`
                FROM `mvv_contacts`
                    INNER JOIN `mvv_contacts_ranges` USING (`contact_id`)
                    INNER JOIN `auth_user_md5` ON (`mvv_contacts`.`contact_id` = `user_id`)
                WHERE `mvv_contacts_ranges`.`contact_id` IN (:contacts)
                    AND `mvv_contacts_ranges`.`range_id` IN (:ranges)
                    AND `mvv_contacts_ranges`.`category` IN (:categories)
            UNION
                SELECT `mvv_contacts`.*, `mvv_contacts_ranges`.`range_id`,
                    `mvv_contacts_ranges`.`category`,
                    `Institute`.`Name` AS `name`
                FROM `mvv_contacts`
                    INNER JOIN `mvv_contacts_ranges` USING (`contact_id`)
                    INNER JOIN `Institute` ON (`mvv_contacts`.`contact_id` = `Institut_id`)
                WHERE `mvv_contacts_ranges`.`contact_id` IN (:contacts)
                    AND `mvv_contacts_ranges`.`range_id` IN (:ranges)
                    AND `mvv_contacts_ranges`.`category` IN (:categories)
            ) tab1
            GROUP BY contact_id
            ORDER BY $sortby ", $ids, $row_count, $offset);
    }

    /**
     * Returns range_ids (ids of Modul/Studiengang) of assigned contacts.
     *
     * @staticvar array $ids The range_ids of assigned contacts after filtration.
     * @param array $filter An array with keys  (table_name.column_name) and
     * values (skalar or array) used in where clause.
     * @param boolean $refresh Refresh ids if true.
     * @return array An array with range_ids of assigned contacts.
     */
    public static function getIdsFiltered($filter, $refresh = true)
    {
        static $ids = null;

        if (is_array($ids) && !$refresh) {
            return $ids;
        }

        // split filter for different table joins
        $filter_modul = [];
        $filter_studiengang = [];
        foreach ($filter as $column => $values) {
            $table = explode('.', $column)[0];
            switch ($table) {
                case 'mvv_modul':
                case 'mvv_modul_inst':
                    $filter_modul[$column] = $values;
                    break;
                case 'mvv_studiengang':
                    $filter_studiengang[$column] = $values;
                    break;
                case 'mvv_stgteil':
                    $filter_stgteil[$column] = $values;
                    break;
                default:
                    $filter_modul[$column] = $values;
                    $filter_studiengang[$column] = $values;
                    $filter_stgteil[$column] = $values;
            }
        }

        $sql = "SELECT DISTINCT `mvv_contacts`.`contact_id`,
                    `mvv_contacts_ranges`.`contact_range_id`,
                    `mvv_contacts_ranges`.`category`, `mvv_contacts_ranges`.`range_id`
                FROM `mvv_contacts`
                    INNER JOIN `mvv_contacts_ranges` USING (`contact_id`)
                    INNER JOIN `mvv_modul_inst`
                        ON (`mvv_contacts_ranges`.`range_id` = `mvv_modul_inst`.`modul_id`
                            AND `mvv_modul_inst`.`gruppe` = 'hauptverantwortlich')
                    INNER JOIN `mvv_modul` USING(`modul_id`)
                    LEFT JOIN `semester_data` `start_sem`
                        ON (`mvv_modul`.`start` = `start_sem`.`semester_id`)
                    LEFT JOIN `semester_data` `end_sem`
                        ON (`mvv_modul`.`end` = `end_sem`.`semester_id`)"
                    . self::getFilterSql($filter_modul, true) . "
            UNION
                SELECT DISTINCT `mvv_contacts`.`contact_id`,
                    `mvv_contacts_ranges`.`contact_range_id`,
                    `mvv_contacts_ranges`.`category`, `mvv_contacts_ranges`.`range_id`
                FROM `mvv_contacts`
                    INNER JOIN `mvv_contacts_ranges` USING (`contact_id`)
                    INNER JOIN `mvv_studiengang` ON (`mvv_contacts_ranges`.`range_id` = `mvv_studiengang`.`studiengang_id`)
                    LEFT JOIN `mvv_stg_stgteil` USING (`studiengang_id`)
                    LEFT JOIN `semester_data` `start_sem`
                        ON (`mvv_studiengang`.`start` = `start_sem`.`semester_id`)
                    LEFT JOIN `semester_data` `end_sem`
                        ON (`mvv_studiengang`.`end` = `end_sem`.`semester_id`)"
                    . self::getFilterSql($filter_studiengang, true) . "
            UNION
                SELECT DISTINCT `mvv_contacts`.`contact_id`,
                    `mvv_contacts_ranges`.`contact_range_id`,
                    `mvv_contacts_ranges`.`category`, `mvv_contacts_ranges`.`range_id`
                FROM `mvv_contacts`
                    INNER JOIN `mvv_contacts_ranges` USING (`contact_id`)
                    INNER JOIN `mvv_stg_stgteil` ON (`mvv_contacts_ranges`.`range_id` = `mvv_stg_stgteil`.`stgteil_id`)
                    LEFT JOIN `mvv_studiengang` USING (`studiengang_id`)
                    LEFT JOIN `semester_data` `start_sem`
                        ON (`mvv_studiengang`.`start` = `start_sem`.`semester_id`)
                    LEFT JOIN `semester_data` `end_sem`
                        ON (`mvv_studiengang`.`end` = `end_sem`.`semester_id`)"
                    . self::getFilterSql($filter_stgteil, true);

        $stm = DBManager::get()->prepare($sql);
        $stm->execute();
        $contacts = $contacts_ranges = $ranges = $categories = [];
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $contacts[$row['contact_id']] = 1;
            $contacts_ranges[$row['contact_range_id']] = 1;
            $ranges[$row['range_id']] = 1;
            $categories[$row['category']] = 1;
        }
        $ids['contacts'] = array_keys($contacts);
        $ids['contacts_ranges'] = array_keys($contacts_ranges);
        $ids['ranges'] = array_keys($ranges);
        $ids['categories'] = array_keys($categories);
        return $ids;
    }

    /**
     * Returns all relations of the contacts specified by the given ids.
     * The returned array is ordered by the types of the referenced objects.
     *
     * @param array $contact_ids Ids of the contacts.
     * @return array References ordered by object types.
     */
    public static function getAllRelations($contact_ids = array())
    {
        $contacts = [];
        if ($contact_ids) {
            foreach ($contact_ids as $contact_id) {
                foreach (self::findBySQL('contact_id = ?', [$contact_id]) as $mvv_contact) {
                    $contacts[] = $mvv_contact;
                }
            }
        } else {
            $contacts = self::findBySQL('1');
        }

        $zuordnungen = [];
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                foreach ($contact->ranges as $contact_range) {
                    if($contact_range['range_type'] != null) {
                        $zuordnungen[$contact_range['range_type']][$contact_range['range_id']] = $contact;
                    }
                }
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
        if (empty($filter)) {
            return parent::getCount();
        }

        $ids = self::getIdsFiltered($filter);
        return parent::getCountBySql("
            SELECT COUNT(DISTINCT `mvv_contacts`.`contact_id`)
            FROM `mvv_contacts_ranges`
                INNER JOIN `mvv_contacts` USING(`contact_id`)
            WHERE `mvv_contacts_ranges`.`contact_id` IN ('" . implode("','", $ids['contacts']) . "')
                AND `mvv_contacts_ranges`.`category` IN ('" . implode("','", $ids['categories']) . "')
                AND `mvv_contacts_ranges`.`range_id` IN ('" . implode("','", $ids['ranges']) . "')");
    }

    /**
     * Find contacts by given search term.
     * Used as search function in list view.
     *
     * @param type $term The search term.
     * @param type $filter Optional filter parameters.
     * @return array An array of contacts ids.
     */
    public static function findBySearchTerm($term, $filter = null)
    {
        $ids = self::getIdsFiltered($filter, true);
        $quoted_term = DBManager::get()->quote('%' . $term . '%');
        return parent::getEnrichedByQuery("
            SELECT contact_id
                FROM mvv_contacts_ranges
                    INNER JOIN auth_user_md5 ON (contact_id = user_id)
                WHERE (auth_user_md5.username LIKE $quoted_term
                    OR auth_user_md5.Vorname LIKE $quoted_term
                    OR auth_user_md5.Nachname LIKE $quoted_term)
                    AND mvv_contacts_ranges.contact_id IN (:contacts)
                    AND mvv_contacts_ranges.category IN (:categories)
                    AND mvv_contacts_ranges.range_id IN (:ranges)
            UNION SELECT contact_id
                FROM mvv_contacts_ranges
                    INNER JOIN Institute ON (contact_id = Institut_id)
                WHERE Institute.Name LIKE $quoted_term
                    AND mvv_contacts_ranges.category IN (:contacts)
                    AND mvv_contacts_ranges.category IN (:categories)
                    AND mvv_contacts_ranges.range_id IN (:ranges)
            UNION SELECT contact_id
                FROM mvv_contacts_ranges
                    INNER JOIN mvv_extern_contacts ON (contact_id = extern_contact_id)
                WHERE mvv_extern_contacts.name LIKE $quoted_term
                    AND mvv_contacts_ranges.category IN (:contacts)
                    AND mvv_contacts_ranges.category IN (:categories)
                    AND mvv_contacts_ranges.range_id IN (:ranges)", $ids);
    }

    /**
     * Returns all institutes assigned to contacts. Sorted and filtered by
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
        $ids = self::getIdsFiltered($filter);
        $sortby = Fachbereich::createSortStatement($sortby, $order, 'name',
                ['count_objects']);

        return Fachbereich::getEnrichedByQuery("
            SELECT *, `Institut_id` AS `institut_id`, `Name` AS `name`, COUNT(`object_id`) AS `count_objects`
            FROM
                (SELECT Institute.*,
                    `mvv_contacts_ranges`.`contact_id` AS `object_id`
                FROM `mvv_contacts_ranges`
                    INNER JOIN `mvv_modul_inst`
                        ON (`mvv_contacts_ranges`.`range_id` = `mvv_modul_inst`.`modul_id`
                            AND `mvv_modul_inst`.`gruppe` = 'hauptverantwortlich')
                    INNER JOIN Institute
                        ON (Institute.Institut_id = mvv_modul_inst.institut_id)
                WHERE `mvv_contacts_ranges`.`contact_id` IN (:contacts)
                    AND `mvv_contacts_ranges`.`category` IN (:categories)
                    AND `mvv_contacts_ranges`.`range_id` IN (:ranges)
            UNION SELECT Institute.*,
                    `mvv_contacts_ranges`.`contact_id` AS `object_id`
                FROM mvv_contacts_ranges
                    INNER JOIN mvv_studiengang
                        ON (mvv_contacts_ranges.range_id = mvv_studiengang.studiengang_id)
                    INNER JOIN Institute
                        ON (Institute.Institut_id = mvv_studiengang.institut_id)
                WHERE `mvv_contacts_ranges`.`contact_id` IN (:contacts)
                    AND `mvv_contacts_ranges`.`category` IN (:categories)
                    AND `mvv_contacts_ranges`.`range_id` IN (:ranges)) tab1
            GROUP BY institut_id ORDER BY " . $sortby,
                $ids, $row_count, $offset
        );
    }

    /**
     * adds a new range to the contact.
     *
     * @param string $range_id
     * @param string $range_type
     * @param string $contact_type
     * @param string $category
     * @return boolean success of adding
     */
    public function addRange($range_id, $range_type, $contact_type, $category)
    {
        if (!MvvContactRange::findOneBySQL("contact_id =? AND range_id =? AND category=?",[$this->contact_id, $range_id, $category])){
            $mvv_cr = new MvvContactRange();
            $mvv_cr->contact_id = $this->contact_id;
            $mvv_cr->range_id = $range_id;
            $mvv_cr->category = $category;
            $mvv_cr->range_type = $range_type;
            $mvv_cr->type = $contact_type;
            $mvv_cr->position = MvvContactRange::getMaxSortingPos($range_id)+1;
            return $mvv_cr->store();
        } else {
            return false;
        }
    }

    /**
     * Removes a range from the contact.
     *
     * @param string $range The range object (assignment of a contact
     * to a mvv object)
     * @return boolean success of removing
     */
    public function deleteRange($range)
    {
        if ($range) {
            $vacant = $range->position;
            $category = $range->category;
            if ($range->delete()) {
                $other_ranges = $this->ranges->findBy('category', $category)->orderBy('position asc');
                foreach ($other_ranges as $other_range) {
                    if ($other_range->position > $vacant) {
                        $tmp = $other_range->position;
                        $other_range->position = $vacant;
                        $other_range->store();
                        $vacant = $tmp;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the name of the status.
     *
     * @return string The name of the status
     */
    public function getStatusName()
    {
        return self::getStatusNames()[$this->status];
    }

    /**
     * Return an associative array with all possible status names. The key is
     * used by the field "status".
     *
     * @return array Array with alle possible status names
     */
    public static function getStatusNames()
    {
        return [
            'intern' => _('Interne Person'),
            'extern' => _('Externe Person'),
            'institution' => _('Einrichtung')
        ];
    }

}
