<?php
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


function object_set_visit_module($plugin_id)
{
    $plugin_id = object_type_to_id($plugin_id);
    if (object_get_visit(Context::getId(), $plugin_id, false, false)
            < object_get_visit(Context::getId(), 0, false, false)){
        object_set_visit(Context::getId(), $plugin_id);
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
function object_set_visit($object_id, $plugin_id, $user_id = '')
{
    global $user;
    $plugin_id = object_type_to_id($plugin_id);
    if (!$user_id) {
        $user_id = $user->id;
    }

    $last_visit = object_get_visit($object_id, $plugin_id, FALSE, false , $user_id);

    if ($last_visit === false) {
        $last_visit = object_get_visit_threshold();
    }

    $query = "INSERT INTO object_user_visits (object_id, user_id, plugin_id, visitdate, last_visitdate)
              VALUES (?, ?, ?, UNIX_TIMESTAMP(), ?) ON DUPLICATE KEY UPDATE visitdate=UNIX_TIMESTAMP(), last_visitdate=?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id, $user_id, $plugin_id, $last_visit, $last_visit]);

    return object_get_visit($object_id, $plugin_id, FALSE, false, $user_id, true);
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
function object_get_visit($object_id, $plugin_id, $mode = "last", $open_object_id = '', $user_id = '', $refresh_cache = false)
{
    global $user;
    static $cache;

    $plugin_id = object_type_to_id($plugin_id);
    if (!$user_id) {
        $user_id = $user->id;
    }
    if (!$open_object_id && $open_object_id !== false) {
        $open_object_id = $object_id;
    }
    if ($refresh_cache) {
        $cache[$object_id][$plugin_id][$user_id] = null;
    }

    if ($cache[$object_id][$plugin_id][$user_id]) {
        return $mode == 'last'
             ? $cache[$object_id][$plugin_id][$user_id]['last_visitdate']
             : $cache[$object_id][$plugin_id][$user_id]['visitdate'];
    }

    $query = "SELECT visitdate, last_visitdate
              FROM object_user_visits
              WHERE object_id = ? AND user_id = ? AND plugin_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$object_id, $user_id, $plugin_id]);
    $temp = $statement->fetch(PDO::FETCH_ASSOC);

    if ($temp) {
        $cache[$object_id][$plugin_id][$user_id] = $temp;

        return $mode == 'last'
             ? $temp['last_visitdate']
             : $temp['visitdate'];
    //no visitdate for the object or modul - we have to gather the information from the studip-object (seminar or institute)
    } elseif ($open_object_id) {
        $query = "SELECT visitdate, last_visitdate
                  FROM object_user_visits
                  WHERE object_id = ? AND user_id = ? AND plugin_id = 0";
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
 * This function gets the (last) visit time for an array of objects.
 * If no information is found, the last visit of the open-object can bes used
 *
 * @param  array       $object_ids       The ids of the objects (i.e. seminar_id, news_id, vote_id)
 * @param  string      $type             The type of visited objects or module (i.e. news, documents, wiki)
 * @param  string|null $mode             The return-mode: 'last' for the last visit, other for actual-visit;
 *                                       pass null to get an array of visit date and last visit date
 * @param  mixed       $user_id          User id to gather the data for, pass null for current user
 * @param  array       $additional_types Additional types to get data for. The returned array is then enlarged
 *                                       by one dimension
 * @return array       associate array with the object id as key and the according data as value
 *
 * @note This function will respect the visit threshold defined in NEW_INDICATOR_THRESHOLD config.
 */
function get_objects_visits(array $object_ids, $plugin_id, $mode = 'last', $user_id = null, $additional_plugins = [])
{
    $plugin_id = object_type_to_id($plugin_id);
    // Combine types
    $plugin_ids = array_merge([$plugin_id], $additional_plugins);

    // Create result array with predefined values / defined threshold
    $threshold  = object_get_visit_threshold();
    $thresholds = array_combine($plugin_ids, array_fill(
        0,
        count($plugin_ids),
        $mode === null ? ['last_visitdate' => $threshold, 'visitdate' => $threshold] : $threshold
    ));
    $result = array_combine(
        $object_ids,
        array_fill(0, count($object_ids), $thresholds)
    );

    // Read data from database
    $query = "SELECT `object_id`, `plugin_id`, `visitdate`, `last_visitdate`
              FROM `object_user_visits`
              WHERE `object_id` IN (:ids)
                AND `plugin_id` IN (:plugin_ids)
                AND `user_id` = :user_id";
    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':ids', $object_ids);
    $statement->bindValue(':plugin_ids', $plugin_ids);
    $statement->bindValue(':user_id', $user_id ?? $GLOBALS['user']->id);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);

    // Spread data from database into result array
    foreach ($statement as $row) {
        if ($mode === null) {
            $return = [
                'visitdate'      => max($threshold, (int) $row['visitdate']),
                'last_visitdate' => max($threshold, (int) $row['last_visitdate']),
            ];
        } elseif ($mode === 'last') {
            $return = max($threshold, (int) $row['last_visitdate']);
        } else {
            $return = max($threshold, (int) $row['visitdate']);
        }
        $result[$row['object_id']][$row['plugin_id']] = $return;
    }

    // Reduce array if not additional types were passed
    if (func_num_args() < 5) {
        // Unfortunately array_column() will dispose the array key
        $result = array_map(function ($row) use ($plugin_id) {
            return $row[$plugin_id];
        }, $result);
    }

    return $result;
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

/**
 * converts a ouv type to an id
 * @param $type string former used type of visited objects or module (i.e. news, documents, wiki)
 * @return int
 */
function object_type_to_id($type)
{
    if (is_numeric($type)) {
        return $type;
    }
    $ouv_mapping = [
        'sem' => 0,
        'inst'=> 0,
        'basicdata' => 0,
        'vote' => -1,
        'eval' => -2,
        'news' => 'CoreOverview',
        'documents' => 'CoreDocuments',
        'schedule' => 'CoreSchedule',
        'scm' =>  'CoreScm',
        'wiki' => 'CoreWiki',
        'elearning_interface' => 'CoreElearningInterface',
        'ilias_interface' => 'IliasInterfaceModule',
        'participants' => 'CoreParticipants'
    ];
    if (isset($ouv_mapping[$type])) {
        $id = $ouv_mapping[$type];
        if (is_numeric($id)) {
            return $id;
        }
        $plugin = PluginEngine::getPlugin($id);
        if ($plugin) {
            return $plugin->getPluginId();
        }
    }

}
