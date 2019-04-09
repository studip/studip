<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * UserDomain.php - class representing a user domain in Stud.IP
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Simple class representing a user domain in Stud.IP.
 */
class UserDomain
{
    /**
     * ID of this user domain
     */
    private $id;

    /**
     * name of this user domain
     */
    private $name;

    /**
     * Initialize a new UserDomain instance.
     */
    public function __construct ($id, $name = NULL)
    {
        if (preg_match('/[^\\w.-]/', $id)) {
            throw new Exception(_('Ungültige ID für Nutzerdomäne').': '.$id);
        }

        $this->id = $id;

        if (isset($name)) {
            $this->name = $name;
        } else {
            $sql = "SELECT name FROM userdomains WHERE userdomain_id = :id";
            $statement = DBManager::get()->prepare($sql);
            $statement->execute([':id' => $id]);
            if (($row = $statement->fetch())) {
                $this->name = $row['name'];
            }
        }
    }

    /**
     * Get a string representation of this user domain.
     */
    public function __toString ()
    {
        return $this->id;
    }

    /**
     * Get the ID of this user domain.
     */
    public function getID ()
    {
        return $this->id;
    }

    /**
     * Get the display name of this user domain.
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Set the display name of this user domain.
     */
    public function setName ($name)
    {
        $this->name = $name;
    }

    /**
     * Store changes to the user domain to the database.
     * This currently only saves the display name.
     */
    public function store ()
    {
        $db = DBManager::get();
        
        $query = "SELECT * FROM userdomains WHERE userdomain_id = :id ";
        $statement = $db->prepare($query);
        $statement->execute([':id' => $this->id]);
        
        if (($row = $statement->fetch())) {
            $query = "UPDATE userdomains SET name = :name "
                    . "WHERE userdomain_id = :id ";
            $statement = $db->prepare($query);
            $statement->execute([':name' => $this->name, ':id' => $this->id]);
            NotificationCenter::postNotification('UserDomainDidUpdate', $this->id, $GLOBALS['user']->id); 
        } else {
                $query = "INSERT INTO userdomains (userdomain_id, name) "
                        . "VALUES (:id, :name)";
            $statement = $db->prepare($query);
            $statement->execute([':id' => $this->id, ':name' => $this->name]);
            NotificationCenter::postNotification('UserDomainDidCreate', $this->id, $GLOBALS['user']->id); 
        }
    }

    /**
     * Delete this user domain from the database.
     */
    public function delete ()
    {
        $query = "DELETE FROM userdomains WHERE userdomain_id = :id ";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':id' => $this->id]);
        NotificationCenter::postNotification('UserDomainDidDelete', $this->id, $GLOBALS['user']->id);

    }

    /**
     * Get an array of all defined user domains.
     * Returns an array of UserDomain objects.
     */
    public static function getUserDomains ()
    {
        $domains = [];
        $statement = DBManager::get()->prepare("SELECT * FROM userdomains ORDER BY name");
        $statement->execute();
        foreach ($statement->fetchAll() as $row) {
            $domains[] = new UserDomain($row['userdomain_id'], $row['name']);
        }

        return $domains;
    }

    /**
     * Add a user to this user domain.
     */
    public function addUser ($user_id)
    {
        $query = "INSERT IGNORE INTO user_userdomains (user_id, userdomain_id) "
                . "VALUES (:user_id, :id)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':user_id' => $user_id, ':id' => $this->id]);
        NotificationCenter::postNotification('UserDomainUserDidCreate', $this->id, $user_id);
    }

    /**
     * Remove a user from this user domain.
     */
    public function removeUser ($user_id)
    {
        $query = "DELETE FROM user_userdomains "
                . "WHERE user_id = :user_id "
                . "AND userdomain_id = :id";
                
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':user_id' => $user_id, ':id' => $this->id]);
        NotificationCenter::postNotification('UserDomainUserDidDelete', $this->id, $user_id);
    }

    /**
     * Get an array of all users in this user domain.
     * Returns an array of user IDs.
     */
    public function getUsers ()
    {
        $query = "SELECT user_id FROM user_userdomains WHERE userdomain_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':id' => $this->id]);
        
        $users = [];
        foreach ($statement->fetchAll() as $row) {
            $users[] = $row['user_id'];
        }

        return $users;
    }

    /**
     * Get an array of all user domains for a specific user.
     * Returns an array of UserDomain objects.
     */
    public static function getUserDomainsForUser ($user_id)
    {
        $query = "SELECT * FROM userdomains JOIN user_userdomains USING (userdomain_id) "
                . "WHERE user_userdomains.user_id = :user_id ORDER BY name";
        
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':user_id' => $user_id]);
        
        $domains = [];
        foreach ($statement->fetchAll() as $row) {
            $domains[] = new UserDomain($row['userdomain_id'], $row['name']);
        }

        return $domains;
    }

    /**
     * Remove all user domains for a specific user.
     */
    public static function removeUserDomainsForUser ($user_id)
    {
        $statement = DBManager::get()->prepare("DELETE FROM user_userdomains WHERE user_id = :user_id");
        $statement->execute([':user_id' => $user_id]);
        NotificationCenter::postNotification('UserDomainUserDidDelete', 'all', $user_id);
    }

    /**
     * Add a seminar to this user domain.
     */
    public function addSeminar ($seminar_id)
    {
        $query = "INSERT IGNORE INTO seminar_userdomains (seminar_id, userdomain_id) "
                . "VALUES (:seminar_id, :id)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':seminar_id' => $seminar_id, ':id' => $this->id]);
        NotificationCenter::postNotification('UserDomainSeminarDidCreate', $this->id, $seminar_id);
    }

    /**
     * Remove a seminar from this user domain.
     */
    public function removeSeminar ($seminar_id)
    {
        $query = "DELETE FROM seminar_userdomains "
                . "WHERE seminar_id = :seminar_id AND "
                . "userdomain_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':seminar_id' => $seminar_id, ':id' => $this->id]);
        NotificationCenter::postNotification('UserDomainSeminarDidDelete', $this->id, $seminar_id);
    }

    /**
     * Get an array of all seminars in this user domain.
     * Returns an array of seminar IDs.
     */
    public function getSeminars ()
    {
        $query = "SELECT seminar_id FROM seminar_userdomains WHERE userdomain_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':id' => $this->id]);
    
        $seminars = [];
        foreach ($statement->fetchAll() as $row) {
            $seminars[] = $row['seminar_id'];
        }

        return $seminars;
    }

    /**
     * Get an array of all user domains for a specific seminar.
     * Returns an array of UserDomain objects.
     */
    public static function getUserDomainsForSeminar ($seminar_id)
    {
        $query = "SELECT * FROM userdomains JOIN seminar_userdomains USING (userdomain_id) "
                . "WHERE seminar_userdomains.seminar_id = :seminar_id ORDER BY name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':seminar_id' => $seminar_id]);
        
        $domains = [];
        foreach ($statement->fetchAll() as $row) {
            $domains[] = new UserDomain($row['userdomain_id'], $row['name']);
        }

        return $domains;
    }

    /**
     * Remove all user domains for a specific seminar.
     */
    public static function removeUserDomainsForSeminar ($seminar_id)
    {
        $query = "DELETE FROM seminar_userdomains WHERE seminar_id = :seminar_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':seminar_id' => $seminar_id]);
        NotificationCenter::postNotification('UserDomainSeminarDidDelete', 'all', $seminar_id);
    }
}