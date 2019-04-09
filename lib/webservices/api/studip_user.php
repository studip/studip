<?php
/*
 * studip_user.php - Basisklasse für Stud.IP User
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * <ClassDescription>
 *
 * @package    studip
 * @subpackage base
 *
 * @author    mlunzena
 * @copyright (c) Authors
 *
 * @todo using UserManagement class for now, shall be removed afterwards
 */

class Studip_User {
    // internal variables
    var $id;
    var $user_name;
    var $first_name;
    var $last_name;
    var $email;
    var $permission;
    var $fullname;
    var $auth_plugin;
    var $visibility;


    // Constructor
    function __construct($user) {
        $fields = self::get_fields();

        foreach ($fields as $field) {
            if (isset($user[$field])) {
                $this->$field = $user[$field];
            }
        }

        if (isset($this->id)) {
            $this->fullname = get_fullname($this->id);
        }
    }


    /**
     * <MethodDescription>
     *
     * @return type <description>
     */
    function save()
    {
        foreach (self::get_fields() as $v => $k) {
            if (isset($this->$k)) {
                $user[$v] = $this->$k;
            }
        }

        // no id, create
        if (!$this->id) {
            $user_management = new UserManagement();
            if (!$user_management->createNewUser($user)) {
                $this->error = $user_management->msg; // TODO
                return FALSE;
            }

            // set id
            $this->id = $user_management->user_data['auth_user_md5.user_id'];
        } else {
            // update
            $user_management = new UserManagement($this->id);
            if (!$user_management->changeUser($user)) {
                $this->error = $user_management->msg; // TODO
                return FALSE;
            }
        }

        return TRUE;
    }


    /**
     * <MethodDescription>
     *
     * @return type <description>
     */
    function destroy()
    {
        $user_management = new UserManagement($this->id);

        if (!$user_management->deleteUser()) {
            $this->error = $user_management->msg; // TODO
            return FALSE;
        }

        return TRUE;
    }


    /**
     * <MethodDescription>
     *
     * @param type <description>
     *
     * @return mixed <description>
     */
    function find_by_user_name($user_name)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT * FROM auth_user_md5 WHERE username = ?');
        $stmt->execute([$user_name]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = NULL;

        if ($row) {
            $user = [];
            foreach (self::get_fields() as $old => $new) {
                $user[$new] = $row[array_pop(explode('.', $old))];
            }
            $result = new Studip_User($user);
        }

        return $result;
    }

    /**
     * <MethodDescription>
     *
     * @param type <description>
     *
     * @return mixed <description>
     */
    function find_by_user_id($user_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT * FROM auth_user_md5 WHERE user_id = ?');
        $stmt->execute([$user_id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = NULL;

        if ($row) {
            $user = [];
            foreach (self::get_fields() as $old => $new) {
                $user[$new] = $row[array_pop(explode('.', $old))];
            }
            $result = new Studip_User($user);
        }

        return $result;
    }

    /**
     * <MethodDescription>
     *
     * @param type <description>
     *
     * @return mixed <description>
     */
    function find_by_status($status)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT username FROM auth_user_md5 WHERE perms = ?');
        $stmt->execute([$status]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * <MethodDescription>
     *
     * @return array <description>
     */
    function get_fields()
    {
        $fields = ['auth_user_md5.user_id'  => 'id',
                        'auth_user_md5.username' => 'user_name',
                        'auth_user_md5.Vorname'  => 'first_name',
                        'auth_user_md5.Nachname' => 'last_name',
                        'auth_user_md5.Email'    => 'email',
                        'auth_user_md5.perms'    => 'permission',
                        'auth_user_md5.auth_plugin' => 'auth_plugin',
                        'auth_user_md5.visible' => 'visibility'];
        return $fields;
    }
}
