<?php
/**
 * UserConfig.class.php
 * provides access to user preferences
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class UserConfig extends ObjectConfig implements PrivacyObject
{
    /**
     * range type ('user' or 'course')
     * @var string
     */
    protected $range_type = 'user';

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
        $usr_conf = [[]];
        foreach (new UserConfig($user->user_id) as $key => $val) {
            $usr_conf[0][$key] = is_array($val) ? print_r($val, true) : $val;
        }
        if ($usr_conf) {
            $storage->addTabularData('user_config', $usr_conf, $user);
        }
        return [_('Benutzer Konfigurationen') => $storage];
    }
}
