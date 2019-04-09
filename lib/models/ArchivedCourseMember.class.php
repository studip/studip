<?php
/**
 * ArchivedCourseMember.class.php
 * model class for table seminar_user
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
 * @property string user_id database column
 * @property string status database column
 * @property string id computed column read/write
 * @property User user belongs_to User
 * @property ArchivedCourse course belongs_to ArchivedCourse
 */
class ArchivedCourseMember extends SimpleORMap implements PrivacyObject
{

    public static function findByCourse($course_id)
    {
        return self::findBySeminar_id($course_id);
    }

    public static function findByUser($user_id)
    {
        return self::findByUser_id($user_id);
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'archiv_user';
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'ArchivedCourse',
            'foreign_key' => 'seminar_id',
        ];
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
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('archivierte SeminareUser'), 'archiv_user', $field_data);
            }
        }
    }
}
