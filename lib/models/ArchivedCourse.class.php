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
    protected static function configure($config = [])
    {
        $config['db_table'] = 'archiv';

        $config['has_many']['members'] = [
            'class_name' => ArchivedCourseMember::class,
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['belongs_to']['home_institut'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'heimat_inst_id',
        ];

        $config['registered_callbacks']['after_delete'][] = 'deleteFiles';

        parent::configure($config);
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
            'thru_table'        => 'archiv_user',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'Seminar_id',
            'assoc_foreign_key' => 'Seminar_id',
        ]);
        if ($sorm) {
            $limit = 'seminar_id name untertitel beschreibung start_time '
                   . 'semester heimat_inst_id institute dozenten fakultaet '
                   . 'archiv_file_id archiv_protected_file_id mkdate '
                   . 'studienbereiche VeranstaltungsNummer';
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray($limit);
            }
            if ($field_data) {
                $storage->addTabularData(_('archivierte Seminare'), 'archiv', $field_data);
            }
        }
    }

    /**
     * delete data files belonging to this archived course
     *
     * @return int number of deleted files
     */
    public function deleteFiles()
    {
        $ok = 0;
        if ($this->archiv_file_id) {
            $ok += unlink($GLOBALS['ARCHIV_PATH'] . '/' . basename($this->archiv_file_id));
        }
        if ($this->archiv_protected_file_id) {
            $ok += unlink($GLOBALS['ARCHIV_PATH'] . '/' . basename($this->archiv_protected_file_id));
        }
        return $ok;
    }
}
