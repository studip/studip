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
    //private $last_online_time = null;

    public function __construct($user = null)
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
        //$this->last_online_time = $this->get_last_action();
    }

    public function getAuthenticatedUser()
    {
        return $this->user->id !== 'nobody' ? $this->user : null;
    }

    private function get_last_action()
    {
        if ($this->id && $this->id != 'nobody') {
            $stmt = DBManager::get()->prepare("SELECT last_lifesign FROM user_online WHERE user_id = ?");
            $stmt->execute([$this->id]);
            return $stmt->fetchColumn();
        }
    }

    public function set_last_action($timestamp = 0)
    {
        if ($this->id && $this->id != 'nobody') {
            if ($timestamp <= 0) {
                if ((time() - $_SESSION['USER_LAST_LIFESIGN']) < 180) {
                    return 0;
                }
                $timestamp = time();
            }
            $query = "INSERT INTO user_online (user_id, last_lifesign)
                      VALUES (:user_id, UNIX_TIMESTAMP() - :time_delta)
                      ON DUPLICATE KEY UPDATE last_lifesign = UNIX_TIMESTAMP() - :time_delta";
            $stmt = DBManager::get()->prepare($query);
            $stmt->bindValue(':user_id', $this->id);
            $stmt->bindValue(':time_delta', time() - $timestamp, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['USER_LAST_LIFESIGN'] = time() - $timestamp;
            return $stmt->rowCount();
        }
    }

    public function delete()
    {
        if ($this->id && $this->id != 'nobody') {
            $stmt = DBManager::get()->prepare("DELETE FROM user_online WHERE user_id = ?");
            $stmt->execute([$this->id]);
            return $stmt->rowCount();
        }
    }

    public function __get($field)
    {
        if ($field == 'id') {
            return $this->user->user_id;
        }
        return $this->user->$field;
    }

    public function __set($field, $value)
    {
        return null;
    }

    public function __isset($field)
    {
        return isset($this->user->$field);
    }

    public function getFullName($format = 'full')
    {
        return $this->user->getFullName($format);
    }

    /**
     * Returns whether the current needs to accept the terms of use.
     * @return bool
     */
    public function needsToAcceptTerms()
    {
        return $this->id !== 'nobody'
            && Config::get()->SHOW_TERMS_ON_FIRST_LOGIN
            && !$this->cfg->TERMS_ACCEPTED;
    }
}
