<?php
/**
 * ical_export.php - provides functions to handle the export of events
 * over a short url
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel, data-quest GmbH <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class IcalExport
{

    private static $id_length = 8;

    /**
     * Sets the lentgh of the key
     *
     * @param int $length
     */
    public static function setKeyLength($length)
    {
        self::$id_length = $length;
    }

    /**
     * Returns a key string.
     *
     * @return string
     */
    public static function makeKey()
    {
        $length = self::$id_length;
        $ret = '';
        $rejected = [
            'A' => 2,
            'E' => 2,
            'I' => 2,
            'O' => 2,
            'U' => 2,
            'a' => 2,
            'e' => 2,
            'i' => 2,
            'o' => 2,
            'u' => 2];
        while ($length--) {
            while (1) {
                $rnd = rand(48, 122);
                if ($rnd < 48)
                    continue;
                if ($rnd > 57 && $rnd < 65)
                    continue;
                if ($rnd > 90 && $rnd < 97)
                    continue;
                if ($rnd > 122)
                    continue;
                $char = chr($rnd);
                if ($rejected[$char] > 1) {
                    continue;
                }
                $rejected[$char]++;
                $ret .= $char;
                break;
            }
        }
        return $ret;
    }

    /**
     * Returns the key by given user_id. Returns false if no valid key was found.
     *
     * @param string $user_id
     * @return mixed
     */
    public static function getKeyByUser($user_id)
    {
        return UserConfig::get($user_id)->getValue('ICAL_EXPORT_KEY');
    }

    /**
     * Returns user_id by given key. Returns false if no valid user_id was found.
     *
     * @param type $short_id
     * @return mixed
     */
    public static function getUserIdByKey($key)
    {
        $where = "field = 'ICAL_EXPORT_KEY' AND value = " . DBManager::get()->quote($key);
        $user_config_entries = ConfigValue::findBySql($where);
        
        if (isset($user_config_entries[0])) {
            return $user_config_entries[0]->getValue('range_id');
        } else {
            return false;
        }
    }

    /**
     * Sets a new key for the user with the given user_id.
     *
     * @param type $user_id
     * @return string the new key
     */
    public static function setKey($user_id)
    {
        // delete old key
        $key = self::getKeyByUser($user_id);
        if ($key) {
            self::deleteKey($user_id);
        }
        // make new unique key
        do {
            $key = self::makeKey();
        } while (self::getUserIdByKey($key));

        UserConfig::get($user_id)->store('ICAL_EXPORT_KEY', $key);

        return $key;
    }

    /**
     * Deletes the key for the user with the given user_id.
     *
     * @param type $user_id
     */
    public static function deleteKey($user_id)
    {
        UserConfig::get($user_id)->delete('ICAL_EXPORT_KEY');
    }

}
