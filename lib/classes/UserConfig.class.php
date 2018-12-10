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
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $usr_conf = [[]];
        foreach (new UserConfig($storage->user_id) as $key => $val) {
            $usr_conf[0][$key] = is_array($val) ? print_r($val, true) : $val;
        }
        if ($usr_conf) {
            $storage->addTabularData(_('Benutzer Konfigurationen'), 'user_config', $usr_conf);
        }
    }
}
