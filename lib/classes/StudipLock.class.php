<?php
/**
 * StudipLock.class.php
 * class with methods to perform cooperative advisory locking
 * using the GET_LOCK feature from Mysql 
 * https://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
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
*/
class StudipLock
{
    /**
     * name of active lock
     * @var string
     */
    private static $current;

    /**
     * returns the name of the current active lock
     * @return string name of active lock
     */
    public static function getCurrent()
    {
        return self::$current;
    }

    /**
     * Tries to obtain a lock with a name given by the string $lockname, 
     * using a timeout of $timeout seconds. Returns 1 if the lock was obtained 
     * successfully, 0 if the attempt timed out 
     * (for example, because another client has previously locked the name),
     * or NULL if an error occurred
     * If a name has been locked by one client, any request by another client
     * for a lock with the same name is blocked.
     * 
     * @param string $lockname
     * @param number $timeout in seconds
     * @throws UnexpectedValueException if there is already an active lock
     * @return integer 1 if the lock was obtained successfully, 0 if the attempt timed out 
     */
    public static function get($lockname, $timeout = 10)
    {
        if (self::$current !== null) {
            throw new UnexpectedValueException(sprintf('could not acquire new lock, %s still active'));
        }
        $ok = DBManager::get()->fetchColumn("SELECT GET_LOCK(?,?)", [self::lockname($lockname), $timeout]);
        if ($ok) {
            self::$current = $lockname;
        }
        return $ok;
    }

    /**
     * check if lock with given name is available
     * 
     * @param string $lockname
     * @return integer 1 if lock is available
     */
    public static function isFree($lockname)
    {
        return DBManager::get()->fetchColumn("SELECT IS_FREE_LOCK(?)", [self::lockname($lockname)]);
    }
    
    /**
     * release the current lock
     * 
     * @return integer 1 if the lock could be released
     */
    public static function release()
    {
        if (self::$current) {
            return DBManager::get()->fetchColumn("SELECT RELEASE_LOCK(?)", [self::lockname(self::$current)]);
        }
    }
    
    /**
     * prepends the name of current database to lockname
     * because locks are server-wide
     * 
     * @param string $lockname
     * @return string
     */
    public static function lockname($lockname)
    {
        return $GLOBALS['DB_STUDIP_DATABASE'] . '_' . $lockname;
    }
}