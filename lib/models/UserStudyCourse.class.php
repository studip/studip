<?php
/**
 * UserStudyCourse.class.php
 * model class for table user_studiengang
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string user_id database column
 * @property string studiengang_id database column
 * @property string semester database column
 * @property string abschluss_id database column
 * @property string degree_name computed column read/write
 * @property string studycourse_name computed column read/write
 * @property string id computed column read/write
 * @property User user belongs_to User
 * @property Degree degree belongs_to Degree
 * @property StudyCourse studycourse belongs_to StudyCourse
 */
class UserStudyCourse extends SimpleORMap implements PrivacyObject
{

    public static function findByUser($user_id)
    {
        $db = DbManager::get();
        $st = $db->prepare("SELECT user_studiengang.*, abschluss.name as degree_name,
                            fach.name as studycourse_name
                            FROM user_studiengang
                            LEFT JOIN abschluss USING (abschluss_id)
                            LEFT JOIN fach USING (fach_id)
                            WHERE user_id = ? ORDER BY studycourse_name,degree_name");
        $st->execute(array($user_id));
        $ret = array();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $ret[] = self::buildExisting($row);
        }
        return $ret;
    }

    public static function findByStudyCourseAndDegree($study_course_id, $degree_id)
    {
        return self::findBySql("fach_id = ? AND abschluss_id = ?", array($study_course_id, $degree_id));
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'user_studiengang';
        $config['belongs_to']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        );
        $config['belongs_to']['degree'] = array(
            'class_name' => 'Abschluss',
            'foreign_key' => 'abschluss_id',
        );
        $config['belongs_to']['studycourse'] = array(
            'class_name' => 'Fach',
            'foreign_key' => 'fach_id',
        );
        $config['additional_fields']['degree_name'] = array();
        $config['additional_fields']['studycourse_name'] = array();
        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return StoredUserData object
     */
    public static function getUserdata(User $user)
    {
        $storage = new StoredUserData($user->id);
        $sorm = self::findBySQL("user_id = ?", [$user->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('UserStudiengang'), 'user_studiengang', $field_data);
            }
        }
        return $storage;
    }
}
