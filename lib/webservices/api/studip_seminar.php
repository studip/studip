<?php
/*
 * studip_seminar.php - Seminar API for Stud.IP webservice
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/webservices/api/studip_user.php';
require_once 'lib/webservices/api/studip_institute.php';
require_once 'lib/webservices/api/studip_session.php';

class StudipSeminarHelper
{
    function get_title($seminar_id)
    {
        $seminar_id = preg_replace('/\W/', '', $seminar_id);

        return Seminar::getInstance($seminar_id)->getName();
    }

    function validate_seminar_permission($ticket, $seminar_id, $permission)
    {
        $username = StudipSessionHelper::get_session_username($ticket);

        if (in_array($username, StudipSeminarHelper::get_participants($seminar_id, $permission)) ||
            in_array($username, StudipSeminarHelper::get_admins_for_seminar($seminar_id))) {
            return $username;
        } else {
            return false;
        }
    }

    function get_user_status($username, $seminar_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT status FROM seminar_user
                              JOIN auth_user_md5 USING(user_id)
                              WHERE Seminar_id = ? AND username = ?');
        $stmt->execute([$seminar_id, $username]);

        $status = $stmt->fetchColumn();

        if (!$status) {
            $admin_list = StudipSeminarHelper::get_admins_for_seminar($seminar_id);

            if (in_array($username, $admin_list)) {
                $status = 'admin';
            }
        }

        return $status;
    }

    function get_participants($seminar_id, $status = 'all')
    {
        $db = DBManager::get();

        if ($status == 'all') {
            $query = 'SELECT username FROM auth_user_md5
                      JOIN seminar_user USING(user_id)
                      WHERE Seminar_id = ?';
            $params = [$seminar_id];
        } else {
            $query = 'SELECT username FROM auth_user_md5
                      JOIN seminar_user USING(user_id)
                      WHERE Seminar_id = ? AND status = ?';
            $params = [$seminar_id, $status];
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function get_main_institute($seminar_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT Institut_id FROM seminare WHERE Seminar_id = ?');
        $stmt->execute([$seminar_id]);

        return $stmt->fetchColumn();
    }

    function get_additional_institutes($seminar_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT institut_id FROM seminar_inst WHERE seminar_id = ?');
        $stmt->execute([$seminar_id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function get_all_institutes($seminar_id)
    {
        $institute_list = array_unique(array_merge([StudipSeminarHelper::get_main_institute($seminar_id)],
                                                   StudipSeminarHelper::get_additional_institutes($seminar_id)));
        return $institute_list;
    }

    function get_admins_for_seminar($seminar_id)
    {
        $all_institutes = StudipSeminarHelper::get_all_institutes($seminar_id);
        $admins = [];

        foreach ($all_institutes as $institute) {
            $admins = array_merge($admins, StudipInstituteHelper::get_admins_upward_recursive($institute));
        }

        $admins = array_merge($admins, Studip_User::find_by_status('root'));

        return $admins;
    }

    function get_seminar_groups($seminar_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT st.name FROM statusgruppen st
                              JOIN seminare s ON (st.range_id = s.Seminar_id)
                              WHERE s.Seminar_id = ?');
        $stmt->execute([$seminar_id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function get_seminar_group_members($seminar_id, $group_name)
    {
        $db = DBManager::get();
        $result = [];

        $stmt = $db->prepare('SELECT au.username FROM statusgruppen st
                              JOIN seminare s ON (st.range_id = s.Seminar_id)
                              JOIN statusgruppe_user su USING(statusgruppe_id)
                              JOIN auth_user_md5 au USING(user_id)
                              WHERE s.Seminar_id = ? AND st.name = ?');
        $stmt->execute([$seminar_id, $group_name]);

        foreach ($stmt as $row) {
            $result[] = Studip_User::find_by_user_name($row['username']);
        }

        return $result;
    }
}
