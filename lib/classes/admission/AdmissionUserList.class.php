<?php

/**
 * AdmissionUserList.class.php
 *
 * Contains users that get different probabilities than others in seat
 * distribution algorithm.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class AdmissionUserList
{
    // --- ATTRIBUTES ---

    /**
     * Unique identifier of this list.
     */
    public $id = '';

    /**
     * Conditions for automatic user selection.
     */
    public $conditions = [];

    /**
     * A factor for seat distribution algorithm ("1" means normal algorithm,
     * everything between 0 and 1 decreases the chance to get a seat,
     * everything above 1 increases it.)
     */
    public $factor = 1;

    /**
     * Some name to display for this list.
     */
    public $name = '';

    /**
     * ID of the user who created this list.
     */
    public $ownerId = '';

    /**
     * All user IDs that are on this list.
     */
    public $users = [];

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param String id If this is an existing list, here is its ID.
     * @return This object.
     */
    public function __construct($id='') {
        if ($id) {
            $this->id = $id;
            $this->load();
        }
        return $this;
    }

    /**
     * Adds the given condition to the list.
     *
     * @param  UserFilter condition
     * @return AdmissionUserList
     */
    public function addCondition($condition)
    {
        $this->conditions[$condition->getId()] = $condition;
        return $this;
    }

    /**
     * Adds the given user to the list.
     *
     * @param  String userId
     * @return AdmissionUserList
     */
    public function addUser($userId)
    {
        $this->users[$userId] = true;
        return $this;
    }

    /**
     * Deletes this list.
     */
    public function delete() {
        // Remove user assignments to this list.
        DBManager::get()->exec("DELETE FROM `user_factorlist` WHERE `list_id`='".
            $this->id."'");
        // Remove assigned conditions.
        foreach ($this->conditions as $condition) {
            $condition->delete();
        }
        DBManager::get()->exec("DELETE FROM `user_factorlist` WHERE `list_id`='".
            $this->id."'");
        // Delete list data.
        DBManager::get()->exec("DELETE FROM `admissionfactor` WHERE `list_id`='".
            $this->id."'");
    }

    /**
     * Gets the currently set conditions for automatic user selection.
     *
     * @return Integer
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Gets the currently set manipulation factor for this list.
     *
     * @return Float
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * Gets the list ID.
     *
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the list name.
     *
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the owner ID.
     *
     * @return String
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Gets all user lists the given user has created.
     *
     * @param  String userId
     * @return array
     */
    public static function getUserLists($userId) {
        $result = [];
        $stmt = DBManager::get()->prepare("SELECT `list_id` FROM `admissionfactor` WHERE ".
            "`owner_id`=? ORDER BY `name` ASC");
        $stmt->execute([$userId]);
        $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lists as $list) {
            $result[$list['list_id']] = new AdmissionUserList($list['list_id']);
        }
        return $result;
    }

    /**
     * Gets all assigned user IDs.
     *
     * @return String
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Helper function for loading data from DB.
     */
    public function load()
    {
        // Load basic data.
        $stmt = DBManager::get()->prepare("SELECT `list_id`, `name`,
                CAST(`factor` AS INT) AS factor, `owner_id`, `mkdate`, `chdate` 
            FROM `admissionfactor` WHERE `list_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->factor = $current['factor'];
            $this->name = $current['name'];
            $this->ownerId = $current['owner_id'];
            // Load user IDs.
            $stmt2 = DBManager::get()->prepare("SELECT uf.*
                FROM `user_factorlist` AS uf
                JOIN `auth_user_md5` AS a ON (uf.`user_id`=a.`user_id`)
                WHERE uf.`list_id`=?
                ORDER BY a.`Nachname` ASC, a.`Vorname` ASC, a.`username` ASC");
            $stmt2->execute([$this->id]);
            while ($user = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $this->users[$user['user_id']] = true;
            }

            // Load selection conditions, if applicable.
            // $stmt2 = DBManager::get()->prepare("SELECT `condition_id` FROM ".
            //     "`condition_factorlist` WHERE `list_id`=? ORDER BY `mkdate` ASC");
            //$stmt2->execute(array($this->id));
            //while ($current = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            //    $this->conditions[$current['condition_id']] =
            //        new UserFilter($current['condition_id']);
            //}
        }
    }

    /**
     * Removes the given condition from the list.
     *
     * @param  String conditionId
     * @return AdmissionUserList
     */
    public function removeCondition($conditonId)
    {
        unset($this->conditions[$conditionId]);
        return $this;
    }

    /**
     * Removes the given user from the list.
     *
     * @param  String userId
     * @return AdmissionUserList
     */
    public function removeUser($userId)
    {
        unset($this->users[$userId]);
        return $this;
    }

    /**
     * Set the conditions to the given set.
     *
     * @param  Array conditions
     * @return AdmissionUserList
     */
    public function setConditions($conditions) {
        $this->conditions = [];
        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }
        return $this;
    }

    /**
     * Sets a factor.
     *
     * @param float $newFactor The new factor to be set.
     * @return AdmissionUserList
     */
    public function setFactor($newFactor)
    {
        $this->factor = $newFactor;
        return $this;
    }

    /**
     * Sets a name.
     *
     * @param  String $newName New list name.
     * @return AdmissionUserList
     */
    public function setName($newName)
    {
        $this->name = $newName;
        return $this;
    }

    /**
     * Sets a new owner.
     *
     * @param  String $newOwnerId New owner Id.
     * @return AdmissionUserList
     */
    public function setOwnerId($newOwnerId)
    {
        $this->ownerId = $newOwnerId;
        return $this;
    }

    /**
     * Sets a set of new list members, replacing previous entries.
     *
     * @param  Array $newUsers New member list.
     * @return AdmissionUserList
     */
    public function setUsers($newUsers)
    {
        $this->users = [];
        foreach ($newUsers as $userId) {
            $this->addUser($userId);
        }
        return $this;
    }

    /**
     * Function for storing the data to DB. Is not called automatically on
     * changing object values.
     */
    public function store() {
        // Generate new ID if list doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid('AdmissionUserList', true));
                $db = DBManager::get()->query("SELECT `list_id`
                    FROM `admissionfactor` WHERE `list_id`='.$newid.'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        // Store basic list data.
        $stmt = DBManager::get()->prepare("INSERT INTO `admissionfactor`
            (`list_id`, `name`, `factor`, `owner_id`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `name`=VALUES(`name`), `factor`=VALUES(`factor`),
            `owner_id`=VALUES(`owner_id`), `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->name, $this->factor,
            $this->ownerId, time(), time()]);
        // Clear all old user assignments to this list.
        DBManager::get()->exec("DELETE FROM `user_factorlist` WHERE `list_id`='".
            $this->id."' AND `user_id` NOT IN ('".
            implode("', '", array_keys($this->users))."')");
        // Store assigned users.
        foreach ($this->users as $userId => $assigned) {
            $stmt = DBManager::get()->prepare("INSERT INTO `user_factorlist`
                (`list_id`, `user_id`, `mkdate`)
                VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE
                `user_id`=VALUES(`user_id`)");
            $stmt->execute([$this->id, $userId, time()]);
        }
        return $this;
    }

    /**
     * String representation of this object.
     */
    public function toString() {
        $tpl = $GLOBALS['template_factory']->open('admission/userlist');
        $tpl->set_attribute('userlist', $this);
        return $tpl->render();
    }

    /**
     * Standard string representation of this object.
     *
     * @return String
     */
    public function __toString() {
        return $this->toString();
    }

} /* end of class AdmissionUserList */
?>
