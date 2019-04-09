<?php
/**
 * CalendarUser.class.php - Model for users with access to other users calendar.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.2
 */

class CalendarUser extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'calendar_user';

        $config['has_one']['owner'] = [
            'class_name' => 'User',
            'foreign_key' => 'owner_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id'
        ];

        $config['additional_fields']['nachname']['get'] = function ($cu) {
            return $cu->user->nachname;
        };
        $config['additional_fields']['vorname']['get'] = function ($cu) {
            return $cu->user->vorname;
        };

        parent::configure($config);

    }

    public function setPerm($permission)
    {
        if ($permission == Calendar::PERMISSION_READABLE) {
            $this->permission = Calendar::PERMISSION_READABLE;
        } else if ($permission == Calendar::PERMISSION_WRITABLE) {
            $this->permission = Calendar::PERMISSION_WRITABLE;
        } else {
            throw new InvalidArgumentException(
                'Calendar permission must be of type PERMISSION_READABLE or PERMISSION_WRITABLE.');
        }
    }

    public static function getUsers($user_id, $permission = null)
    {
        $permission_array = [Calendar::PERMISSION_READABLE,
                Calendar::PERMISSION_WRITABLE];
        if (!$permission) {
            $permission = $permission_array;
        } else if (!in_array($permission, $permission_array)) {
            throw new InvalidArgumentException(
                'Calendar permission must be of type PERMISSION_READABLE or PERMISSION_WRITABLE.');
        } else {
            $permission = [$permission];
        }
        return SimpleORMapCollection::createFromArray(CalendarUser::findBySQL(
                'owner_id = ? AND permission IN(?)',
                [$user_id, $permission]));

    }

    public static function getOwners($user_id, $permission = null)
    {
        $permission_array = [Calendar::PERMISSION_READABLE,
                Calendar::PERMISSION_WRITABLE];
        if (!$permission) {
            $permission = $permission_array;
        } else if (!in_array($permission, $permission_array)) {
            throw new InvalidArgumentException(
                'Calendar permission must be of type PERMISSION_READABLE or PERMISSION_WRITABLE.');
        } else {
            $permission = [$permission];
        }
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM calendar_user
                INNER JOIN auth_user_md5 ON (auth_user_md5.user_id = calendar_user.owner_id)
            WHERE calendar_user.user_id = :user_id
                AND calendar_user.permission IN (:permission)
            ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname
        ");
        $statement->execute([
            'user_id' => $user_id,
            'permission' => $permission
        ]);
        $calendar_users = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $calendar_users[] = CalendarUser::buildExisting($data);
        }
        return SimpleORMapCollection::createFromArray($calendar_users);

    }
}
