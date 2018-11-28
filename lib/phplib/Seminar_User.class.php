<?php
/**
 * Seminar_User.class.php
 * global object representing current user
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2000 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
*/

class Seminar_User
{
    public $cfg = null; //UserConfig object
    private $user = null; //User object
    private $last_online_time = null;

    function __construct($user = null)
    {
        if ($user instanceOf User) {
            $this->user = $user;
        } else {
            $this->user = User::findFull($user);
        }
        if (!isset($this->user)) {
            $this->user = new User();
            $this->user->user_id = 'nobody';
            $this->user->perms = null;
        }
        $this->cfg = UserConfig::get($this->user->user_id);
        $this->last_online_time = $this->get_last_action();
    }

    function getAuthenticatedUser()
    {
        return $this->user->id !== 'nobody' ? $this->user : null;
    }

    function get_last_action()
    {
        if ($this->id && $this->id != 'nobody') {
            try {
                $stmt = DBManager::get()->prepare("SELECT last_lifesign FROM user_online WHERE user_id = ?");
                $stmt->execute(array($this->id));
                return $stmt->fetchColumn();
            } catch (PDOException $e) {
                $version = new DBSchemaVersion('studip');
                if ($version->get() < 98) {
                    Log::ALERT('Seminar_User::set_last_action() failed. Check migration no. 98!');
                } else {
                    throw $e;
                }
            }
        }
    }

    function set_last_action($timestamp = 0)
    {
        if ($this->id && $this->id != 'nobody') {
            if ($timestamp <= 0) {
                if ((time() - $this->last_online_time) < 180) {
                    return 0;
                }
                $timestamp = time();
            }
            try {
                $query = "INSERT INTO user_online (user_id, last_lifesign)
                          VALUES (:user_id, UNIX_TIMESTAMP() - :time_delta)
                          ON DUPLICATE KEY UPDATE last_lifesign = UNIX_TIMESTAMP() - :time_delta";
                $stmt = DBManager::get()->prepare($query);
                $stmt->bindValue(':user_id', $this->id);
                $stmt->bindValue(':time_delta', time() - $timestamp, PDO::PARAM_INT);
                $stmt->execute();
            } catch (PDOException $e) {
                $version = new DBSchemaVersion('studip');
                if ($version->get() < 98) {
                    Log::ALERT('Seminar_User::set_last_action() failed. Check migration no. 98!');
                } else {
                    throw $e;
                }
            }
            return $stmt->rowCount();
        }
    }

    function delete()
    {
        if ($this->id && $this->id != 'nobody') {
            $stmt = DBManager::get()->prepare("DELETE FROM user_online WHERE user_id = ?");
            $stmt->execute(array($this->id));
            return $stmt->rowCount();
        }
    }

    function __get($field)
    {
        if ($field == 'id') {
            return $this->user->user_id;
        }
        return $this->user->$field;
    }

    function __set($field, $value)
    {
        return null;
    }

    function __isset($field)
    {
        return isset($this->user->$field);
    }

    function getFullName($format = 'full')
    {
        return $this->user->getFullName($format);
    }
}

