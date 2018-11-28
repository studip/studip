<?php
/**
 * ArchivedCourse.class.php
 * model class for table archiv
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string seminar_id database column
 * @property string id alias column for seminar_id
 * @property string name database column
 * @property string untertitel database column
 * @property string beschreibung database column
 * @property string start_time database column
 * @property string semester database column
 * @property string heimat_inst_id database column
 * @property string institute database column
 * @property string dozenten database column
 * @property string fakultaet database column
 * @property string dump database column
 * @property string archiv_file_id database column
 * @property string archiv_protected_file_id database column
 * @property string mkdate database column
 * @property string forumdump database column
 * @property string wikidump database column
 * @property string studienbereiche database column
 * @property string veranstaltungsnummer database column
 * @property SimpleORMapCollection members has_many ArchivedCourseMember
 * @property Institute home_institut belongs_to Institute
 */

class ArchivedCourse extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'archiv';
        $config['has_many']['members'] = array(
            'class_name' => 'ArchivedCourseMember',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['belongs_to']['home_institut'] = array(
            'class_name' => 'Institute',
            'foreign_key' => 'heimat_inst_id',
        );
        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return array of StoredUserData objects
     */
    public static function getUserdata(User $user)
    {
        $storage = new StoredUserData($user);
        $sorm = self::findThru($user->user_id, [
            'thru_table'        => 'archiv_user',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'Seminar_id',
            'assoc_foreign_key' => 'Seminar_id',
        ]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData('archiv', $field_data, $user);
            }
        }
        return [_('archivierte Seminare') => $storage];
    }
}
