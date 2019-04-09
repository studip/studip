<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
* object.inc.php
*
* functions for object operations (Stud.IP-ojects/modules) as get/set viewdate, rates, favourites and more
*
*
* @author       Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <kater@data-quest.de>, data-quest GmbH <info@data-quest.de>
* @access       public
* @modulegroup      functions
* @module       object.inc.php
* @package      studip_core
*/

//object.inc.php - Verwaltung von Objektoperationen
//Copyright (C) 2004 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <kater@data-quest.de>, data-quest GmbH <info@data-quest.de>
// This file is part of Stud.IP
// object.inc.php
// Funktionen fuer generische Objekt-Behandlungen (Stud.IP-Objekte/Module)
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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


function object_set_visit_module($type){
    if (object_get_visit(Context::getId(), $type, false, false)
            < object_get_visit(Context::getId(), Context::getClass(), false, false)){
        object_set_visit(Context::getId(), $type);
    }
}

/**
* This function saves the actual time as last visitdate for the given object, user and type
*
* @param    string  the id of the object (i.e. seminar_id, news_id, vote_id)
* @param    string  the type of visited object or module (i.e. news, documents, wiki - see /lib/classes/Modules.class.php for definitions)
* @param    string  the user who visited the object - if not given, the actual user is used
*
*/
function object_set_visit($object_id, $type, $user_id = '')
{
    global $user;
    if (!$user_id) {
        $user_id = $user->id;
    }

    $last_visit = object_get_visit($object_id, $type, FALSE, false , $user_id);

    if ($last_visit === false) {
        $last_visit = object_get_visit_threshold();
    }

    $query = "INSERT INTO object_user_visits (object_id, user_id, type, visitdate, last_visitdate)
              VALUES (?, ?, ?, UNIX_TIMESTAMP(), ?) ON DUPLICATE KEY UPDATE visitdate=UNIX_TIMESTAMP(), last_visitdate=?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id, $user_id, $type, $last_visit, $last_visit]);

    return object_get_visit($object_id, $type, FALSE, false, $user_id, true);
}

/**
* This function gets the (last) visit time for an object or module. If no information is found, the last visit of the open-object can bes used
*
* @param    string  the id of the object (i.e. seminar_id, news_id, vote_id)
* @param    string  the type of visited object or module (i.e. news, documents, wiki - see /lib/classes/Modules.class.php for definitions OR sem/inst, if the visit for the whole seminar was saved)
* @param    string  the return-mode: 'last' for the last visit, other for actual-visit
* @param    string  the user who visited the object - if not given, the actual user is used
* @param    string  the id of an open-object (seminar or inst), to gather information for last visit from the visit of the whole open-object
* @return   int the timestamp of the last visit or FALSE
*
*/
function object_get_visit($object_id, $type, $mode = "last", $open_object_id = '', $user_id = '', $refresh_cache = false)
{
    global $user;
    static $cache;

    if (!$user_id) {
        $user_id = $user->id;
    }
    if (!$open_object_id && $open_object_id !== false) {
        $open_object_id = $object_id;
    }
    if ($refresh_cache) {
        $cache[$object_id][$type][$user_id] = null;
    }

    if ($cache[$object_id][$type][$user_id]) {
        return $mode == 'last'
             ? $cache[$object_id][$type][$user_id]['last_visitdate']
             : $cache[$object_id][$type][$user_id]['visitdate'];
    }

    $query = "SELECT visitdate, last_visitdate
              FROM object_user_visits
              WHERE object_id = ? AND user_id = ? AND type = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id, $user_id, $type]);
    $temp = $statement->fetch(PDO::FETCH_ASSOC);

    if ($temp) {
        $cache[$object_id][$type][$user_id] = $temp;

        return $mode == 'last'
             ? $temp['last_visitdate']
             : $temp['visitdate'];
    //no visitdate for the object or modul - we have to gather the information from the studip-object (seminar or institute)
    } elseif ($open_object_id) {
        $query = "SELECT visitdate, last_visitdate
                  FROM object_user_visits
                  WHERE object_id = ? AND user_id = ? AND type IN ('sem', 'inst')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$open_object_id, $user_id]);
        $temp = $statement->fetch(PDO::FETCH_ASSOC);

        if ($temp) {
            return $mode == 'last'
                 ? $temp['last_visitdate']
                 : $temp['visitdate'];
        } else {
            return object_get_visit_threshold();
        }

    } else {
        return object_get_visit_threshold();
    }
}

/**
 * This function gets the cutoff value for object visit dates as defined by the NEW_INDICATOR_THRESHOLD setting.
 *
 * @return   int the timestamp of the oldest possible visit or 0
 */
function object_get_visit_threshold()
{
    $threshold = Config::get()->NEW_INDICATOR_THRESHOLD;

    return $threshold ? strtotime("-{$threshold} days 0:00:00") : 0;
}

function object_kill_visits($user_id, $object_ids = false)
{
    if (!$user_id && !$object_ids) {
        return false;
    }

    $query      = "DELETE FROM object_user_visits WHERE ";
    $parameters = [];

    if ($user_id) {
        $query       .= "user_id = ?";
        $parameters[] = $user_id;
    } else {
        $query .= "1";
    }

    if ($object_ids) {
        if (!is_array($object_ids)) {
            $object_ids = [$object_ids];
        }
        $query       .= " AND object_id IN (?)";
        $parameters[] = $object_ids;
    }

    $statement = DBManager::get()->prepare($query);
    $statement->execute($parameters);
    return $statement->rowCount();
}

function object_add_view ($object_id)
{
    $count_view = !in_array($object_id, $_SESSION['object_cache']);
    if (!$count_view) {
        return;
    }

    $_SESSION['object_cache'][] = $object_id;

    $query = "INSERT INTO object_views (object_id, views, chdate)
              VALUES (?, 1, UNIX_TIMESTAMP())
              ON DUPLICATE KEY UPDATE views = views + 1,
                                      chdate = UNIX_TIMESTAMP()";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id]);

    $query = "SELECT views FROM object_views WHERE object_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id]);
    return $statement->fetchColumn();
}

function object_kill_views($object_id)
{
    if (!empty($object_id)) {
        $query = "DELETE FROM object_views WHERE object_id IN (?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$object_id]);
        return $statement->rowCount();
    } else {
        return 0;
    }
}

function object_return_views ($object_id)
{
    $query = "SELECT views FROM object_views WHERE object_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id]);
    return $statement->fetchColumn() ?: 0;
}
