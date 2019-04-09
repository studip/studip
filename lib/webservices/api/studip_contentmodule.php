<?php
/*
 * studip_contentmodule.php - base class for content modules
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class StudipContentmoduleHelper
{
    function find_seminars_using_contentmodule($system_type, $module_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT s.Seminar_id FROM seminare s
                              LEFT JOIN object_contentmodules oc
                              ON (s.Seminar_id = oc.object_id)
                              WHERE oc.module_id = ? AND oc.system_type = ?');
        $stmt->execute([$module_id, $system_type]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function find_institutes_using_contentmodule($system_type, $module_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT i.Institut_id FROM Institute i
                              LEFT JOIN object_contentmodules oc
                              ON (i.Institut_id = oc.object_id)
                              WHERE oc.module_id = ? AND oc.system_type = ?');
        $stmt->execute([$module_id, $system_type]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
