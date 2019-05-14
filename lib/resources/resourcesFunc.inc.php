<?php
# Lifter002: TEST - maybe getFormattedResult() should get a template as well?
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE - no longer applicable
/**
* resourcesFunc.php
*
* functions for resources
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       resourcesFunc.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// resourcesFunc.php
// Funktionen der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

/*
* allowCreateRooms
*
* gets the status, if an user is allowed to create new rooms(objects)
*
* @param    string  the user_id, if not set, the actual user's id is used
* @return   boolean
*
**/
function allowCreateRooms ($user_id = '')
{
    if (!$user_id) {
        $user_id = $GLOBALS['user']->id;
    }

    switch (Config::get()->RESOURCES_ALLOW_CREATE_ROOMS) {
        case 1:
            return $GLOBALS['perm']->have_perm('tutor');
        break;
        case 2:
            return $GLOBALS['perm']->have_perm('admin');
        break;
        case 3:
            return getGlobalPerms($user_id) == 'admin';
        break;
    }
}

/*
* getLockPeriod
*
* gets a lock-period, if one is active (only the first lock period that matches will be returned)
*
* @param    int the timestamp, if left, the actual time
* @return   array   the start- and end-timestamp
*
**/
function getLockPeriod($type, $timestamp1='', $timestamp2='')
{
    static $cache;

    if ($cache[$type][$timestamp1 / 60][$timestamp2 / 60]) {
        return $cache[$type][$timestamp1 / 60][$timestamp2 / 60];
    }

    if (!$timestamp1) {
        $timestamp1 = time();
    }
    if (!$timestamp2) {
        $timestamp2 = time();
    }

    if ((!Config::get()->RESOURCES_LOCKING_ACTIVE && $type === 'edit') || (!Config::get()->RESOURCES_ASSIGN_LOCKING_ACTIVE && $type === 'assign')) {
        $cache[$type][$timestamp1 / 60][$timestamp2 / 60] = FALSE;
        return FALSE;
    } else {
        if ($timestamp1 && $timestamp2) { // This is always true, isn't it? See line 88ff
            $query = "SELECT lock_begin, lock_end, lock_id
                      FROM resources_locks
                      WHERE type = :type AND :timestamp1 BETWEEN lock_begin AND lock_end";
        } else {
            $query = "SELECT lock_begin, lock_end, lock_id
                      FROM resources_locks
                      WHERE type = :type
                        AND ((lock_begin <= :timestamp1 AND lock_end > :timestamp1)
                             OR (lock_begin >= :timestamp1 AND lock_end <= :timestamp2)
                             OR (lock_begin <= :timestamp1 AND lock_end >= :timestamp2)
                             OR (lock_begin < :timestamp2 AND lock_end >= :timestamp2))";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':type', $type);
        $statement->bindValue(':timestamp1', $timestamp1);
        if (!$timestamp1 || !$timestamp2) {
            $statement->bindValue(':timestamp2', $timestamp2);
        }
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $arr = [
                $row['lock_begin'],
                $row['lock_end'],
                $row['lock_id']
            ];

            $cache[$type][$timestamp1 / 60][$timestamp2 / 60] = $arr;
            return $arr;
        } else {
            $cache[$type][$timestamp1 / 60][$timestamp2 / 60] = FALSE;
            return FALSE;
        }
    }
}

/**
* isLockPeriod
*
* determines, if a lock period could be found in resources_locks and locking is active
*
* @param    int the timestamp, if left, the actual time
* @return   boolean true or false
*
**/
function isLockPeriod($type, $timestamp = '')
{
    static $cache;

    if (!$timestamp) {
        $timestamp = time();
    }

    if ($cache[$type][$timestamp / 60]) {
        return $cache[$type][$timestamp / 60];
    }

    if ((!Config::get()->RESOURCES_LOCKING_ACTIVE && $type === 'edit') || (!Config::get()->RESOURCES_ASSIGN_LOCKING_ACTIVE && $type === 'assign')) {
        $cache[$type][$timestamp % 60] = FALSE;
        return FALSE;
    } else {
        $query = "SELECT 1
                  FROM resources_locks
                  WHERE type = ?
                    AND ? NOT BETWEEN lock_begin AND lock_end";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$type, $timestamp]);
        $check = (bool)$statement->fetchColumn();

        $cache[$type][$timestamp % 60] = $check;
        return $check;
    }
}

/**
* changeLockableRecursiv
*
* sets the lockale option for all childs to state
*
* @param    string  the key for the resource object
* @param    boolean if set, all childs will be lockable
*
**/
function changeLockableRecursiv ($resource_id, $state)
{
    $query = "UPDATE resources_objects
              SET lockable = ?
              WHERE resource_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$state, $resource_id]);

    $query = "SELECT resource_id
              FROM resources_objects
              WHERE parent_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$resource_id]);
    while ($id = $statement->fetchColumn()) {
        changeLockableRecursiv($id, $state);
    }
}


/*
* getGlobalPerms
*
* this Funktion get the globals perms, the given user has in the
* resources-management
*
* @param    string  the user_id
* @return   string  the perms-string
*
**/
function getGlobalPerms($user_id)
{
    static $cache;
    global $perm;

    if (!$user_id){
        $user_id = $GLOBALS['user']->id;
    }

    if ($cache[$user_id]) {
        return $cache[$user_id];
    }
    
    if ($perm->have_perm('root', $user_id)) {
        $res_perm = 'admin';
    } else {
        $query = "SELECT perms
                  FROM resources_user_resources
                  WHERE user_id = ? AND resource_id = 'all'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $res_perm = $statement->fetchColumn() ?: 'autor';
    }

    $cache[$user_id] = $res_perm;
    return $res_perm;
}

/*
* getGlobalPerms
*
* this Funktion creates an fully formatted output after changing/updating assigns
*
* @param    array   the result array, contains all informations about the last operation(s)
* @param    string  the mode of returning: "good" (all booked resources), "bad" (all not booked resources) or "both"
* @return   string  the formatted message, ready for using it in msg.inc.php
*
**/
function getFormattedResult($result, $mode="bad", $bad_message_text = '', $good_message_text = '') {
    //extract the overlaps (bad results) and locks
    if (is_array($result)) {
        $overlaps=FALSE;
        foreach ($result as $key=>$val)
            if ($val["overlap_assigns"] == TRUE) {
                $overlaps[] = ["resource_id"=>$val["resource_id"], "overlap_assigns"=>$val["overlap_assigns"]];
                foreach ($val["overlap_assigns"] as $val2)
                    if ($val2["lock_id"])
                        $locks[$val2["lock_id"]] =  ["begin" => $val2["lock_begin"], "end" => $val2["lock_end"]];
            }
        if ($locks)
            sort ($locks);
    } else
        return FALSE;

    //extract the succesfully booked roomes
    foreach ($result as $key=>$val)
        if (!is_array($val["overlap_assigns"]))
            $rooms_id[$val["resource_id"]]=TRUE;

    //create bad message
    if ((is_array($overlaps)) && (($mode == "bad") || ($mode == "booth"))) {
        $i=0;
        $bad_message = "error§"._("Folgende gewünschte Raumbelegungen überschneiden sich mit bereits vorhandenen Belegungen. Bitte ändern Sie die Räume oder Zeiten!");
        //the overlaps (show only the earliest here, plus a message when more)
        foreach ($overlaps as $val) {
            $resObj = ResourceObject::Factory($val["resource_id"]);
            $bad_message.="<br>".htmlReady($resObj->getName()).": ";
            //show the first overlap
            list(, $val2) = each($val["overlap_assigns"]);
            $bad_message.=date("d.m, H:i",$val2["begin"])." - ".date("H:i",$val2["end"]);
            if (sizeof($val["overlap_assigns"]) >1)
                $bad_message.=", ... (".sprintf (_("und %s weitere Überschneidungen"), (sizeof($val["overlap_assigns"])-1)).")";
            $bad_message.= ", ".$resObj->getFormattedLink($val2["begin"], _("Raumplan anzeigen"));
            $i++;
        }

        if ($locks) {
            $bad_message.="<br><span style=\"color: red\">"._("Die gewünschten Belegungen kollidieren mit folgenden Sperrzeiten:")."</span>";
            $bad_message.="<br>";
            foreach ($locks as $val) {
                $bad_message.=date("d.m.Y, H:i",$val["begin"])." - ".date("d.m.Y, H:i",$val["end"])."<br>";
            }
        }
        $bad_message.="§";
    }


    //create good message
    if ((is_array($rooms_id)) && (($mode == "good") || ($mode == "booth"))) {
        $i=0;
        foreach ($rooms_id as $key=>$val) {
            if ($key) {
                $resObj = ResourceObject::Factory($key);
                if ($i)
                    $rooms_booked.=", ";
                $rooms_booked.= $resObj->getFormattedLink();
                $i++;
            }
        }

    if ($rooms_booked)
        if ($i == 1)
            $good_message.= sprintf ("msg§"._("Die Belegung des Raumes %s wurde in die Ressourcenverwaltung übernommen.")."§", $rooms_booked);
        elseif ($i)
            $good_message.= sprintf ("msg§"._("Die Belegung der Räume %s wurden in die Ressourcenverwaltung übernommen.")."§", $rooms_booked);
    }

    if ($mode == "bad")
        return $bad_message;
    if ($mode == "good")
        return $good_message;
    if ($mode == "booth")
        return $bad_message.$good_message;
}

/*****************************************************************************
a quick function to get the resource_id (only rooms!) for a assigned date
/*****************************************************************************/

function getDateAssigenedRoom($date_id)
{
    $query = "SELECT resources_assign.resource_id
              FROM resources_assign
              LEFT JOIN resources_objects USING (resource_id)
              LEFT JOIN resources_categories USING (category_id)
              WHERE assign_user_id = ?
                AND resources_categories.is_room = 1";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$date_id]);
    return $statement->fetchColumn() ?: false;
}

/*****************************************************************************
a quick function to get a name from a resources object
/*****************************************************************************/

function getResourceObjectName($id)
{
    $query = "SELECT name FROM resources_objects WHERE resource_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$id]);
    return $statement->fetchColumn() ?: false;
}

/*****************************************************************************
a quick function to get a category from a resources object
/*****************************************************************************/

function getResourceObjectCategory($id)
{
    $query = "SELECT category_id FROM resources_objects WHERE resource_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$id]);
    return $statement->fetchColumn() ?: false;
}

function getMyRoomRequests($user_id = '', $semester_id = null, $only_not_closed = true, $single_request = null, $sem_type = null, $faculty = null, $tagged = null, $regular = null)
{
    global $user;

    if (!$user_id) {
        $user_id = $user->id;
    }

    $parameters = [];

    if ($only_not_closed) {
        $criteria = ' closed = 0 ';
    } else {
        $criteria = ' 1 ';
    }
    if ($single_request) {
        $criteria .= " AND rr.request_id = :request_id";
        $parameters[':request_id'] = $single_request;
    } elseif ($semester_id){
        $semester = Semester::find($semester_id);
        $sem_criteria = ' AND t.date BETWEEN ' . (int)$semester['beginn'] . ' AND ' . (int)$semester['ende'];
        if ($sem_type) {
            $criteria .= " AND EXISTS (SELECT * FROM seminare WHERE seminare.Seminar_id=rr.seminar_id AND seminare.status=".(int)$sem_type.") ";
        }
        if ($faculty) {
            $criteria .= " AND EXISTS (SELECT * FROM seminare INNER JOIN Institute USING(Institut_id) WHERE seminare.Seminar_id=rr.seminar_id AND fakultaets_id=".DBManager::get()->quote($faculty).") ";
        }
        if ($tagged) {
            $criteria .= " AND NOT EXISTS (SELECT * FROM resources_requests_user_status WHERE resources_requests_user_status.request_id=rr.request_id AND resources_requests_user_status.user_id=".DBManager::get()->quote($user_id).") ";
        }
        if ($regular) {
            $criteria .= " AND rr.termin_id = '' AND EXISTS (SELECT * FROM termine WHERE range_id=rr.seminar_id AND date > UNIX_TIMESTAMP() AND metadate_id IS NOT NULL AND metadate_id != '') ";
        }
    }

    $query0  = "SELECT request_id, closed, rr.resource_id
               FROM resources_requests AS rr
               WHERE %s ";

    $queries = [];
    $queries[] = "SELECT request_id
                  FROM resources_requests AS rr
                  INNER JOIN termine t
                     ON (t.termin_id = rr.termin_id AND t.date > UNIX_TIMESTAMP() {$sem_criteria})
                  WHERE rr.termin_id <> '' AND %s";
    $presence_type_clause = getPresenceTypeClause();
    $queries[] = "SELECT DISTINCT request_id
                  FROM resources_requests AS rr
                  INNER JOIN termine AS t
                     ON (rr.seminar_id = t.range_id AND
                         t.date_typ IN {$presence_type_clause} AND
                         t.date > UNIX_TIMESTAMP() {$sem_criteria})
                  WHERE rr.termin_id = '' AND rr.metadate_id = '' AND %s ";
    $queries[] = "SELECT DISTINCT request_id
                  FROM resources_requests AS rr
                  INNER JOIN termine AS t ON (t.metadate_id = rr.metadate_id AND t.date > UNIX_TIMESTAMP() {$sem_criteria})
                  WHERE rr.metadate_id <> '' AND %s ";

    $requests = [];
    if ((getGlobalPerms($user_id) == 'admin')) {
        $query = sprintf($query0, $criteria);
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $requests[$row['request_id']] = [
                'my_sem'      => true,
                'my_res'      => mb_strlen($row['resource_id']) > 0,
                'closed'      => $row['closed'],
                'resource_id' => $row['resource_id'],
            ];
        }

        foreach ($queries as $q) {
            $query = sprintf($q, $criteria);
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            while ($request_id = $statement->fetchColumn()) {
                $requests[$request_id]['have_times'] = 1;
            }
        }
    } else {
        //load all my resources
        $resList = new ResourcesUserRoomsList($user_id, FALSE, FALSE);
        $my_res = $resList->getRooms();

        if (count($my_res) > 0) {
            foreach (array_keys($my_res) as $res_id) {
                $object_perms = ResourceObjectPerms::Factory($res_id, $user_id);
                if (!$object_perms->havePerm('tutor')) {
                    unset($my_res[$res_id]);
                }
            }
        }

        if (count($my_res) > 0) {
            $res_criteria = $criteria . " AND rr.resource_id IN (:resource_ids)";
            $params = $parameters;
            $params[':resource_ids'] = array_keys($my_res);

            $query = sprintf($query0, $res_criteria);
            $statement = DBManager::get()->prepare($query);
            $statement->execute($params);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $requests[$row['request_id']] = [
                    'my_res'      => true,
                    'closed'      => $row['closed'],
                    'resource_id' => $row['resource_id'],
                ];
            }

            foreach ($queries as $q) {
                $query = sprintf($q, $res_criteria);
                $statement = DBManager::get()->prepare($query);
                $statement->execute($params);
                while ($request_id = $statement->fetchColumn()) {
                    $requests[$request_id]['have_times'] = 1;
                }
            }

            //load all my seminars
            $my_sems = search_administrable_seminars();
            if (count($my_sems) > 0) {
                $sem_criteria = $criteria . " AND rr.seminar_id IN (:seminar_ids)";
                $params = $parameters;
                $params[':seminar_ids'] = array_keys($my_sems);

                $query = sprintf($query0, $sem_criteria);
                $statement = DBManager::get()->prepare($query);
                $statement->execute($params);
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $request_id = $row['request_id'];
                    $requests[$request_id]['my_sem']      = true;
                    $requests[$request_id]['closed']      = $row['closed'];
                    $requests[$request_id]['resource_id'] = $row['resource_id'];
                }

                foreach ($queries as $q) {
                    $query = sprintf($q, $sem_criteria);
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute($params);
                    while ($request_id = $statement->fetchColumn()) {
                        $requests[$request_id]['have_times'] = 1;
                    }
                }
            }
        }
    }

    return $requests;
}


/*****************************************************************************
sort function to sort the AssignEvents by date
/*****************************************************************************/

function cmp_assign_events($a, $b){
    $start_a = $a->getBegin();
    $start_b = $b->getBegin();
    if($start_a == $start_b)
        return 0;
    if($start_a < $start_b)
        return -1;
    return 1;
}

/*****************************************************************************
sort function to sort the ResourceObject by name
/*****************************************************************************/
function cmp_resources($a, $b){
    $name_a = $a->getName();
    $name_b = $b->getName();
    if($name_a == $name_b)
        return 0;
    if($name_a < $name_b)
        return -1;
    return 1;
}

/*
* checkAvailableResources
*
* This Funktion searches for available resources for studip-objects (and users, too),
* but it only work's properly with studip-objects, because it didn't pay attention for
* inheritance of perms for a studip-user.
*
* @param    string  the obejct id
* @return   boolean true, if resources are found, otherwise false
*
**/
function checkAvailableResources($id)
{
    //check if owner
    $query = "SELECT 1 FROM resources_objects WHERE owner_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$id]);
    if ($statement->fetchColumn()) {
        return true;
    }

    //or additional perms avaiable
    $query = "SELECT 1 FROM resources_user_resources WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$id]);
    if ($statement->fetchColumn()) {
        return true;
    }

    return false;
}

/*****************************************************************************
checkObjektAdminstrablePerms checks, if I have the chance to change
the owner of the given object
/*****************************************************************************/

function checkObjektAdministrablePerms ($resource_object_owner_id, $user_id='') {
    global $user, $perm, $my_perms;

    if (!$user_id)
        $user_id = $user->id;

    //for root, it's quick!
    if ($perm->have_perm("root"))
        return TRUE;

    //for the resources admin too
    if (getGlobalPerms($user_id) == "admin")
        return TRUE;

    //load all my administrable objects
    $my_objects=search_administrable_objects ();

    //ok, we as a user aren't interesting...
    unset ($my_objects[$user_id]);
    if (sizeof ($my_objects)) {
        if (($my_objects[$resource_object_owner_id]["perms"] == "admin") || ($resource_object_owner_id == $user_id)) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else
        return FALSE;
}
/*
* search_administrable_seminars
*
* this Funktion searches all my aministrable seminars
*
* @param    string  a search string, that could be used
* @param    string  the user_id
* @return   array   result
*
**/
function search_administrable_seminars ($search_string = '', $user_id = '')
{
    global $user, $perm, $auth;

    if (!$user_id) {
        $user_id = $user->id;
    }

    if (!$search_string) {
        $search_sql = '1';
        $parameters = [];
    } else {
        $search_sql = " Name LIKE CONCAT('%', :needle, '%') OR
                        Untertitel LIKE CONCAT('%', :needle, '%') OR
                        Seminar_id = :needle ";
        $parameters[':needle'] = $search_string;
    }

    $user_global_perm = $perm->get_perm($user_id);
    switch ($user_global_perm) {
        case 'root':
            // Alle Seminare...
            $type = _('Veranstaltungen');
            $query = "SELECT Seminar_id AS id, Name AS name,
                             '{$type}' AS art, 'admin' AS perms
                      FROM seminare
                      WHERE {$search_sql}
                      ORDER BY Name";
        break;
        case 'admin':
            //Alle meine Institute (unabhaengig von Suche fuer Rechte)...
            if ($perm->is_fak_admin($user_id)) {
                $query = "SELECT DISTINCT IFNULL(b.Institut_id, a.Institut_id) AS Institut_id
                          FROM user_inst a
                          LEFT JOIN Institute b ON ( a.Institut_id = b.fakultaets_id )
                          WHERE a.inst_perms = 'admin' AND a.user_id = :user_id";
            } else {
                $query = "SELECT Institut_id
                          FROM user_inst
                          WHERE inst_perms = 'admin' AND user_id = :user_id";
            }
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $user_id);
            $statement->execute();
            $institute_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

            // Return empty array if no institutes were found
            if (count($institute_ids) == 0) {
                return [];
            }

            //...alle Seminare meiner Institute, in denen ich Admin bin....
            $type = _('Veranstaltungen');
            $query = "SELECT Seminar_id AS id, Name AS name,
                             '{$type}' AS art, 'admin' AS perms
                      FROM seminare
                      WHERE Institut_id IN (:inst_ids) AND ({$search_sql})
                      ORDER BY Name";
            $parameters[':inst_ids'] = $institute_ids;
        break;
        case 'dozent':
        case 'tutor':
            //Alle meine Seminare
            $type = _('Veranstaltungen');
            $query = "SELECT Seminar_id AS id, Name AS name,
                             '{$type}' AS art, 'admin' AS perms
                      FROM seminar_user
                      LEFT JOIN seminare USING (seminar_id)
                      WHERE seminar_user.status IN ('tutor', 'dozent')
                        AND seminar_user.user_id = :user_id
                        AND ({$search_sql})
                      ORDER BY Name";
            $parameters[':user_id'] = $user_id;
        break;
    }

    $statement = DBManager::get()->prepare($query);
    $statement->execute($parameters);
    return $statement->fetchGrouped(PDO::FETCH_ASSOC);
}


/*
* search_administrable_objects
*
* this Funktion searches all my aministrable objects (the object i've got tutor
* or better perms, so I'am able to administrate most of the things).
*
* @param    string  a search string, that could be used
* @param    string  the user_id
* @param    boolean should seminars searched`?
* @return   array
*
**/
function search_administrable_objects($search_string='', $user_id='', $sem=TRUE)
{
    static $my_object_cache;
    global $user, $perm, $auth, $_fullname_sql;

    if (!$user_id) {
        $user_id = $user->id;
    }

    $user_global_perm = $perm->get_perm($user_id);
    $my_objects = [];
    $my_inst_ids = [];

    $search_sql = [];
    if (!$search_string) {
        $caching = true;
        $search_sql['user']     = '1';
        $search_sql['institut'] = '1';
        $search_sql['seminar']  = '1';
    } else {
        $search_sql['user']     = "username LIKE CONCAT('%', :needle, '%') OR
                                   Vorname LIKE CONCAT('%', :needle, '%') OR
                                   Nachname LIKE CONCAT('%', :needle, '%') OR
                                   CONCAT(Nachname, ', ', Vorname) LIKE CONCAT('%', :needle, '%') OR
                                   CONCAT(Vorname, ' ', Nachname) LIKE CONCAT('%', :needle, '%') OR
                                   auth_user_md5.user_id = :needle";
        $search_sql['institut'] = "Name LIKE CONCAT('%', :needle, '%') OR
                                   Institute.Institut_id = :needle";
        $search_sql['seminar']  = "Name LIKE CONCAT('%', :needle, '%') OR
                                   Untertitel LIKE CONCAT('%', :needle, '%') OR
                                   seminare.Seminar_id = :needle";
       if ($user_global_perm == 'admin') {
           $tmp_objects = search_administrable_objects(false, $user_id, false);
           if (is_array($tmp_objects)) {
               foreach ($tmp_objects as $id => $detail){
                   if ($detail['inst_perms']) {
                       $my_inst_ids[$id] = $detail['inst_perms'];
                   }
               }
           }
        }
    }

    if ($caching && isset($my_object_cache[$user_id][$sem])) {
        return $my_object_cache[$user_id][$sem];
    }

    if (getGlobalPerms($user_id) == 'admin') {
        $my_objects['global'] = [
            'name'  => _('Global'),
            'perms' => 'admin'
        ];
    }

    $my_objects[$user_id] = [
        'name'  => 'aktueller Account (' . get_username($user_id) . ')',
        'art'   => _('Personen'),
        'perms' => 'admin'
    ];

    if ($user_global_perm == 'admin') {
        //Alle meine Institute (Suche)...
        $type = _('Einrichtungen');
        $query = "SELECT Institute.Institut_id AS id, Name AS name,
                         '{$type}' AS art, 'admin' AS perms
                  FROM user_inst
                  LEFT JOIN Institute USING (institut_id)
                  WHERE inst_perms = 'admin' AND user_inst.user_id = :user_id
                    AND ({$search_sql['institut']})
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        if ($search_string) {
            $statement->bindValue(':needle', $search_string);
        }
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $my_objects[$row['id']]  = $row;
            $my_inst_ids[$row['id']] = 'admin';
        }

        if ($perm->is_fak_admin($user_id) && count($my_inst_ids) > 0) {
            $type = _('Einrichtungen');
            $query = "SELECT Institut_id AS id, Name AS name,
                             '{$type}' AS art, 'admin' AS perms
                      FROM Institute
                      WHERE Institut_id != fakultaets_id AND fakultaets_id IN (:inst_ids)
                        AND ({$search_sql['institut']})
                      ORDER BY Name";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':inst_ids', array_keys($my_inst_ids));
            if ($search_string) {
                $statement->bindValue(':needle', $search_string);
            }
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $my_objects[$row['id']]  = $row;
                $my_inst_ids[$row['id']] = 'fak_admin';
            }
        }

        if ($sem && count($my_inst_ids) > 0) {
            $type = _('Veranstaltungen');
            $query = "SELECT Seminar_id AS id, Name AS name,
                             '{$type}' AS art, 'admin' AS perms
                      FROM seminare
                      WHERE Institut_id IN (:inst_ids)
                        AND ({$search_sql['seminar']})
                      ORDER BY Name";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':inst_ids', array_keys($my_inst_ids));
            if ($search_string) {
                $statement->bindValue(':needle', $search_string);
            }
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $my_objects[$row['id']] = $row;
            }
        }
    } else {
        $queries = [];
        $parameters = [];
        if ($search_string) {
            $parameters[':needle'] = $search_string;
        }

        switch ($user_global_perm) {
            case 'root':
                //Alle Personen...
                $type = _('Personen');
                $queries[] = "SELECT auth_user_md5.user_id AS id,
                                     CONCAT({$_fullname_sql['full_rev']}, ' (', username, ')') AS name,
                                     '{$type}' AS art, 'admin' AS perms
                              FROM auth_user_md5
                              LEFT JOIN user_info USING (user_id)
                              WHERE {$search_sql['user']}
                              ORDER BY Nachname, Vorname, username";

                //Alle Seminare...
                if ($sem) {
                    $type = _('Veranstaltungen');
                    $queries[] = "SELECT Seminar_id AS id, Name AS name,
                                         '{$type}' AS art, 'admin' AS perms
                                  FROM seminare
                                  WHERE {$search_sql['seminar']}
                                  ORDER BY Name";
                }

                //Alle Institute...
                $type = _('Einrichtungen');
                $queries[] = "SELECT Institut_id AS id, Name AS name,
                                     '{$type}' AS art, 'admin' AS perms
                              FROM Institute
                              WHERE {$search_sql['institut']}
                              ORDER BY Name";
            break;
            case 'dozent':
            case 'tutor':
                $parameters[':user_id']     = $user_id;
                $parameters[':user_status'] = ($user_global_perm == 'tutor')
                                            ? 'tutor'
                                            : words('tutor dozent');

                //Alle meine Seminare
                if ($sem) {
                    $type = _('Veranstaltungen');
                    $queries[] = "SELECT seminare.Seminar_id AS id, Name AS name,
                                         '{$type}' AS art, 'admin' AS perms
                                  FROM seminar_user
                                  LEFT JOIN seminare USING (seminar_id)
                                  WHERE seminar_user.status IN (:user_status) AND seminar_user.user_id = :user_id
                                    AND ({$search_sql['seminar']})
                                  ORDER BY Name";
                }

                //Alle meine Institute...
                $type = _('Einrichtungen');
                $queries[] = "SELECT Institute.Institut_id AS id, Name AS name,
                                     '{$type}' AS art, inst_perms AS perms
                              FROM user_inst
                              LEFT JOIN Institute USING (institut_id)
                              WHERE inst_perms IN (:user_status)  AND user_inst.user_id = :user_id
                                AND ({$search_sql['institut']})
                              ORDER BY Name";
            break;
        }

        foreach ($queries as $query) {
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($my_objects[$row['id']])) {
                    $my_objects[$row['id']] = $row;
                }
            }
        }
    }

    if ($caching) {
        $my_object_cache[$user_id][$sem] = $my_objects;
    }
    return $my_objects;
}

/*
* search_my_objects
*
* this Funktion searches all my objects (only them with autor perms).
* the function works as an addition to the search administrable objects
* function above
*
* @param    string  a search string, that could be used
* @param    string  the user_id
* @param    boolean should seminars searched`?
* @return   array
*
**/
function search_my_objects ($search_string = '', $user_id = '', $sem = TRUE)
{
    global $user, $perm;

    if ($perm->have_perm('admin')){
        return [];
    }

    $queries = [];

    //Alle meine Seminare
    if ($sem) {
        $type = _('Veranstaltungen');
        $queries[] = "SELECT seminare.Seminar_id AS id, Name AS name,
                             '{$type}' AS art, 'autor' AS perms
                      FROM seminar_user
                      LEFT JOIN seminare USING (seminar_id)
                      WHERE seminar_user.status = 'autor' AND seminar_user.user_id = :user_id
                        AND (Name LIKE CONCAT('%', :needle, '%') OR
                             Untertitel LIKE CONCAT('%', :needle, '%') OR
                             seminare.Seminar_id = :needle)
                      ORDER BY Name";
    }

    //Alle meine Institute...
    $type = _('Einrichtungen');
    $queries[] = "SELECT Institute.Institut_id AS id, Name AS name,
                         '{$type}' AS art, 'autor' AS perms
                  FROM user_inst
                  LEFT JOIN Institute USING (institut_id)
                  WHERE inst_perms = 'autor' AND user_inst.user_id = :user_id AND
                        (Name LIKE CONCAT('%', :needle, '%') OR
                         Institute.Institut_id = :needle)
                  ORDER BY Name";

    $my_objects = [];
    foreach ($queries as $query) {
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':needle', $search_string ?: '_');
        $statement->bindValue(':user_id', $user_id ?: $user->id);
        $statement->execute();
        $my_objects = array_merge($my_objects , $statement->fetchGrouped(PDO::FETCH_ASSOC));
    }

    return $my_objects;
}


/*****************************************************************************
search_admin_user searches in all the admins
/*****************************************************************************/
function search_admin_user ($search_string='')
{
    global $_fullname_sql;

    //In allen Admins suchen...
    $query = "SELECT a.user_id, CONCAT({$_fullname_sql['full_rev']}, ' (', username, ')') AS name, :art AS art
              FROM auth_user_md5 AS a
              LEFT JOIN user_info USING (user_id)
              WHERE username LIKE CONCAT('%', :needle, '%')
                 OR Vorname LIKE CONCAT('%', :needle, '%')
                 OR Nachname LIKE CONCAT('%', :needle, '%')
                 OR CONCAT(Nachname, ', ', Vorname) LIKE CONCAT('%', :needle, '%')
                 OR CONCAT(Vorname, ' ', Nachname) LIKE CONCAT('%', :needle, '%')
                 OR a.user_id = :needle
              ORDER BY Nachname, Vorname";
    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':needle', $search_string);
    $statement->bindValue(':art', _('Personen'));
    $statement->execute();
    return $statement->fetchGrouped(PDO::FETCH_ASSOC);
}


/*****************************************************************************
search_objects searches in all objects
/*****************************************************************************/

function search_objects($search_string = '', $user_id = '', $sem = TRUE)
{
    global $_fullname_sql;
    $queries = [];

    //Alle Personen...
    $type = _('Personen');
    $queries[] = "SELECT user_id AS id,
                         CONCAT({$_fullname_sql['full_rev']}, '(', username, ')') AS name,
                         '{$type}' AS art
                  FROM auth_user_md5 AS a
                  LEFT JOIN user_info USING (user_id)
                  WHERE username LIKE CONCAT('%', :needle, '%')
                     OR Vorname LIKE CONCAT('%', :needle, '%')
                     OR Nachname LIKE CONCAT('%', :needle, '%')
                     OR CONCAT(Nachname, ', ', Vorname) LIKE CONCAT('%', :needle, '%')
                     OR CONCAT(Vorname, ' ', Nachname) LIKE CONCAT('%', :needle, '%')
                     OR user_id = :needle
                  ORDER BY Nachname, Vorname";

    //Alle Seminare...
    if ($sem) {
        $type = _('Veranstaltungen');
        $queries[] = "SELECT Seminar_id AS id, name, '{$type}' AS art
                      FROM seminare
                      WHERE Name LIKE CONCAT('%', :needle, '%')
                         OR Untertitel LIKE CONCAT('%', :needle, '%')
                         OR Seminar_id = :needle
                      ORDER BY Name";
    }

    //Alle Institute...
    $type = _('Einrichtungen');
    $queries[] = "SELECT Institut_id AS id, Name AS name, '{$type}' AS art
                  FROM Institute
                  WHERE Name LIKE CONCAT('%', :needle, '%')
                     OR Institut_id = :needle
                  ORDER BY Name";

    $result = [];
    foreach ($queries as $query) {
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':needle', $search_string);
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['id']] = $row;
        }
    }

    return $result;
}


/*****************************************************************************
Searchform, zur Erzeugung der oft gebrauchten Personen-Auswahl
u.a. Felder
/*****************************************************************************/

function showSearchForm($name, $search_string='', $user_only=FALSE, $administrable_objects_only=FALSE, $admins=FALSE, $allow_all=FALSE, $sem=TRUE, $img_dir="left")
{
    $template = $GLOBALS['template_factory']->open('resources/search_form');
    $template->set_attributes(compact(words('name search_string img_dir allow_all')));

    if ($search_string) {
        if ($user_only) { //Nur in Personen suchen
            if ($admins) { //nur admins anzeigen
                $my_objects = search_admin_user($search_string);
            } else {//auch andere...
            }
        } else if ($administrable_objects_only) {
            $my_objects = search_administrable_objects($search_string, FALSE, $sem);
        } else { //komplett in allen Objekten suchen
            $my_objects = search_objects($search_string, FALSE, $sem);
        }

        // We need the results grouped by 'art'
        $temp = $my_objects ?: [];
        $results = [];

        foreach ($temp as $key => $val) {
            $art = $val['art'] ?: $val['name'];
            if (!isset($results[$art])) {
                $results[$art] = [];
            }
            $results[$art][$key] = $val;
        }

        $template->results = $results;
    }

    echo $template->render();
}

function getResourcesCategories()
{
    $query = "SELECT * FROM resources_categories ORDER BY name";
    return DBManager::get()->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Checks if current user has global access to resource occupation plans.
 * @return bool
 */
function hasGlobalOccupationAccess()
{
    return (Config::get()-> RESOURCES_ALLOW_VIEW_RESOURCE_OCCUPATION ||
        $GLOBALS['perm']->have_perm('admin') ||
        getGlobalPerms($GLOBALS['user']->id) == 'admin');
}
