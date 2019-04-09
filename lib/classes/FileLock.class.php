<?php
/**
 * file_lock.php
 * Simple lock mechanism on a file basis.
 *
 * With the help of this class you can manage persistent locks. Locks are
 * stored in files and potential additional data is stored as json.
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright 2013 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 * @since     2.4
 */

class FileLock
{
    protected static $directory = '';

    /**
     * Sets a new base path for the locks.
     *
     * @param String $directory
     * @throws RuntimeException if provided directory is either not existant
     *         or is not a directory or is not writable.
     */
    public static function setDirectory($directory)
    {
        if (!file_exists($directory) || !is_dir($directory) || !is_writable($directory)) {
            throw new RuntimeException('Passed directory is not an actual directory or is not writable.');
        }
        self::$directory = rtrim($directory, '/') . '/';
    }

    protected $filename;

    /**
     * Constructs a new lock object with the provided id.
     *
     * @param String $id Identifier of the lock
     */
    public function __construct($id)
    {
        $this->filename = self::$directory . '.' . $id . '.json';
    }
    
    /**
     * Returns the filename of the lock.
     *
     * @return String Filename of the lock
     */
    public function getFilename()
    {
        return $this->filename;
    }
    
    /**
     * Establish or renew the current lock. Provided lock information will
     * be stored with the lock.
     *
     * @param Array $data Additional information to bestore with the lock
     */
    public function lock($data = [])
    {
        $data['timestamp'] = time();
        file_put_contents($this->filename, json_encode($data));
    }

    /**
     * Tests whether the lock is in use. Returns lock information in
     * $lock_data.
     *
     * @param mixed $lock_data Information stored in lock
     * @return bool Indicates whether the lock is active or not
     */
    public function isLocked(&$lock_data = null)
    {
        if (!file_exists($this->filename)) {
            return false;
        }

        $lock_data = json_decode(file_get_contents($this->filename), true);
        return true;
    }

    /**
     * Releases a previously obtained lock
     */
    public function release()
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }
}