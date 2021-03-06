<?php
/**
 * CourseMember.class.php
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
 * @property string position database column
 * @property string gruppe database column
 * @property string notification database column
 * @property string mkdate database column
 * @property string comment database column
 * @property string visible database column
 * @property string label database column
 * @property string bind_calendar database column
 * @property string vorname computed column read/write
 * @property string nachname computed column read/write
 * @property string username computed column read/write
 * @property string email computed column read/write
 * @property string title_front computed column read/write
 * @property string title_rear computed column read/write
 * @property string course_name computed column read/write
 * @property string id computed column read/write
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property User user belongs_to User
 * @property Course course belongs_to Course
 */
class CourseMember extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'seminar_user';
        $config['belongs_to']['user'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'seminar_id',
        ];
        $config['has_many']['datafields'] = [
            'class_name' => 'DatafieldEntryModel',
            'assoc_foreign_key' =>
                function($model, $params) {
                    list($sec_range_id, $range_id) = (array)$params[0]->getId();
                    $model->setValue('range_id', $range_id);
                    $model->setValue('sec_range_id', $sec_range_id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($course_member) {
                    return [$course_member];
                }
        ];

        $config['additional_fields']['vorname']     = ['user', 'vorname'];
        $config['additional_fields']['nachname']    = ['user', 'nachname'];
        $config['additional_fields']['username']    = ['user', 'username'];
        $config['additional_fields']['email']       = ['user', 'email'];
        $config['additional_fields']['title_front'] = ['user', 'title_front'];
        $config['additional_fields']['title_rear']  = ['user', 'title_rear'];

        $config['additional_fields']['course_name'] = [];

        parent::configure($config);
    }

    public static function findByCourse($course_id)
    {
        $query = "SELECT seminar_user.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                         FROM seminar_user
                         LEFT JOIN auth_user_md5 aum USING (user_id)
                         LEFT JOIN user_info ui USING (user_id)
                         WHERE seminar_id = ?
                         ORDER BY position, Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$course_id],
            __CLASS__ . '::buildExisting'
        );
    }

    public static function findByCourseAndStatus($course_id, $status)
    {
        $query = "SELECT seminar_user.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                  FROM seminar_user
                  LEFT JOIN auth_user_md5 aum USING (user_id)
                  LEFT JOIN user_info ui USING (user_id)
                  WHERE seminar_id = ?
                    AND seminar_user.status IN (?)
                  ORDER BY status, position, Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$course_id, is_array($status) ? $status : words($status)],
            __CLASS__ . '::buildExisting'
        );
    }

    public static function findByUser($user_id)
    {
        $query = "SELECT seminar_user.*, seminare.Name AS course_name
                  FROM seminar_user
                  LEFT JOIN seminare USING (seminar_id)
                  WHERE user_id = ?
                  ORDER BY seminare.Name";
        return DBManager::get()->fetchAll(
            $query,
            [$user_id],
            __CLASS__ . '::buildExisting'
        );
    }

    /**
     * Retrieves the number of all members of a status
     *
     * @param String|Array $status  the status to filter with
     *
     * @return int the number of all those members.
     */
    public static function countByCourseAndStatus($course_id, $status)
    {
        return self::countBySql(
            'seminar_id = ? AND status IN(?)',
            [$course_id, is_array($status) ? $status : words($status)]
        );
    }

    public function getUserFullname($format = 'full')
    {
        return User::build(array_merge(
            ['motto' => ''],
            $this->toArray('vorname nachname username title_front title_rear')
        ))->getFullname($format);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL('user_id = ?', [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('SeminareUser'), 'seminar_user', $field_data);
            }
        }
    }
}
