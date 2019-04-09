<?php
/*
 * studip_institute.php - base class for institutes
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/webservices/api/studip_user.php';

class StudipInstituteHelper
{
    function get_users_by_status($institute_id, $status)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT au.username FROM user_inst ui
                              JOIN auth_user_md5 au USING(user_id)
                              WHERE ui.inst_perms = ? AND ui.Institut_id = ?');
        $stmt->execute([$status, $institute_id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function get_user_status($username, $institute_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT ui.inst_perms FROM user_inst ui
                              JOIN auth_user_md5 au USING(user_id)
                              WHERE au.username = ? AND ui.Institut_id = ?');
        $stmt->execute([$username, $institute_id]);

        return $stmt->fetchColumn();
    }

    function get_admins($institute_id)
    {
        return StudipInstituteHelper::get_users_by_status($institute_id, 'admin');
    }

    function get_lecturers($institute_id)
    {
        return StudipInstituteHelper::get_users_by_status($institute_id, 'dozent');
    }

    function get_authors($institute_id)
    {
        return StudipInstituteHelper::get_users_by_status($institute_id, 'autor');
    }

    function get_users($institute_id)
    {
        return StudipInstituteHelper::get_users_by_status($institute_id, 'user');
    }

    function get_higher_level_institute($institute_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT i2.Institut_id FROM Institute i
                              JOIN Institute i2 ON (i.fakultaets_id = i2.Institut_id)
                              WHERE i.Institut_id = ? AND i2.fakultaets_id != ?');
        $stmt->execute([$institute_id, $institute_id]);

        return $stmt->fetchColumn();
    }

    function get_admins_upward_recursive($institute_id)
    {
        $institute_fak = StudipInstituteHelper::get_higher_level_institute($institute_id);

        return array_merge(StudipInstituteHelper::get_admins($institute_fak),
                           StudipInstituteHelper::get_admins($institute_id));
    }
}
