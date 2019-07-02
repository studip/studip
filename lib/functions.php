<?php
# Lifter002: DONE - not applicable
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE - not applicable
/**
 * functions.php
 *
 * The Stud.IP-Core functions. Look to the descriptions to get further details
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @author      Ralf Stockmann <rstockm@gwdg.de>
 * @author      André Noack <andre.noack@gmx.net>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @access      public
 * @package     studip_cores
 * @modulegroup library
 * @module      functions.php
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>,
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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


require_once 'lib/object.inc.php';
require_once 'lib/user_visible.inc.php';

/**
 * returns an array containing name and type of the passed objeact
 * denoted by $range_id
 *
 * @global array $SEM_TYPE
 * @global array $INST_TYPE
 * @global array $SEM_TYPE_MISC_NAME
 *
 * @param string $range_id    the id of the object
 * @param string $object_type the type of the object
 *
 * @return array  an array containing name and type of the object
 */
function get_object_name($range_id, $object_type)
{
    global $SEM_TYPE,$INST_TYPE, $SEM_TYPE_MISC_NAME;

    if ($object_type == "sem") {
        $query = "SELECT status, Name FROM seminare WHERE Seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($SEM_TYPE[$row['status']]['name'] == $SEM_TYPE_MISC_NAME) {
            $type = _('Veranstaltung');
        } else {
            $type = $SEM_TYPE[$row['status']]['name'];
        }
        if (!$type) {
            $type = _('Veranstaltung');
        }
        $name = $row['Name'];
    } else if ($object_type == 'inst' || $object_type == 'fak') {
        $query = "SELECT type, Name FROM Institute WHERE Institut_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $type = $INST_TYPE[$row['type']]['name'];
        if (!$type) {
            $type = _('Einrichtung');
        }
        $name = $row['Name'];
    }

    return compact('name', 'type');
}

/**
 * Returns a sorm object for a given range_id
 *
 * @param string the range_id
 * @return SimpleORMap Course/Institute/User/Statusgruppen/
 */
function get_object_by_range_id($range_id) {
    $possible_sorms = "Course Institute User";
    foreach(words($possible_sorms) as $sorm) {
        if ($object = $sorm::find($range_id)) {
            return $object;
        }
    }
    return false;
}

/**
 * This function checks, if there is an open Veranstaltung or Einrichtung
 *
 * @throws CheckObjectException
 *
 * @return void
 */
function checkObject()
{
    if (!Context::get()) {
        throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
    }
}


/**
 * This function checks, if given old style module "wiki","scm" (not "CoreWiki") etc.
 * is allowed in this stud.ip-object.
 *
 * @param string $module the module to check for
 *
 * @return void
 */
function checkObjectModule($module)
{
    if ($context = Context::get()) {
        $modules = new Modules();
        $local_modules = $modules->getLocalModules($context['id'], Context::getClass());
        $checkslot = $module;
        if (Context::isCourse() && $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][Context::getArtNum()]['class']]) {
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][Context::getArtNum()]['class']];
            $new_module_name = "Core".ucfirst($module);
            $mandatory = false;
            foreach (SemClass::getSlots() as $slot) {
                if ($sem_class->getSlotModule($slot) === $new_module_name) {
                    $checkslot = $slot;
                    if ($sem_class->isModuleMandatory($new_module_name)) {
                        $mandatory = true;
                    }
                }
            }
        }

        if (!$local_modules[$checkslot] && !$mandatory) {
            throw new CheckObjectException(sprintf(_('Das Inhaltselement "%s" ist für dieses Objekt leider nicht verfügbar.'), ucfirst($module)));
        }
    }
}

/**
 * This function closes a opened Veranstaltung or Einrichtung
 *
 * @return void
 */
function closeObject()
{
    Context::close();
}

/**
 * This function determines the type of the passed id
 *
 * The function recognizes the following types at the moment:
 * Einrichtungen, Veranstaltungen, Statusgruppen and Fakultaeten
 *
 * @staticvar array $object_type_cache
 *
 * @param string $id         the id of the object
 * @param array  $check_only an array to narrow the search, may contain
 *                            'sem', 'inst', 'fak', 'group' or 'dokument' (optional)
 *
 * @return string  return "inst" (Einrichtung), "sem" (Veranstaltung),
 *                 "fak" (Fakultaeten), "group" (Statusgruppe), "dokument" (Dateien)
 *
 */
function get_object_type($id, $check_only = [])
{
    static $object_type_cache;

    // Nothing to check
    if (!$id) {
        return false;
    }

    // Id is global
    if ($id == 'studip') {
        return 'global';
    }

    // Read from cache if available
    if (isset($object_type_cache[$id])) {
        return $object_type_cache[$id];
    }

    // Tests for specific types
    $tests = [
        'sem'        => "SELECT 1 FROM seminare WHERE Seminar_id = ?",
        'date'       => "SELECT 1 FROM termine WHERE termin_id = ?",
        'user'       => "SELECT 1 FROM auth_user_md5 WHERE user_id = ?",
        'group'      => "SELECT 1 FROM statusgruppen WHERE statusgruppe_id = ?",
        'dokument'   => "SELECT 1 FROM file_refs WHERE id = ?",
        'range_tree' => "SELECT 1 FROM range_tree WHERE item_id = ?",
    ];

    // Test for every type if no specific types are provided
    $check_all = !count($check_only);

    // Loop through tests
    foreach ($tests as $key => $query) {
        if ($check_all || in_array($key, $check_only)) {
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$id]);

            if ($statement->fetchColumn()) {
                return $object_type_cache[$id] = $key;
            }
        }
    }

    // Institute or faculty?
    if ($check_all || in_array('inst', $check_only) || in_array('fak', $check_only)) {
        $query = "SELECT Institut_id = fakultaets_id FROM Institute WHERE Institut_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);

        $is_fak = $statement->fetchColumn();
        if ($is_fak !== false) {
            return $object_type_cache[$id] = ($is_fak ? 'fak' : 'inst');
        }
    }
    if ($check_all) {
        // None of the above
        return $object_type_cache[$id] = false;
    } else {
        return false;
    }
}

/**
 * This function calculates one of the group colors unique for the semester of
 * the passed timestamp
 *
 * It calculates a unique color number to create the initial entry for a new user in a seminar.
 * It will create a unique number for every semester and will start over, if the max. number
 * (7) is reached.
 *
 * @param integer $sem_start_time the timestamp of the start time from the Semester
 *
 * @return integer  the color number
 *
 */
function select_group($sem_start_time)
{
    //Farben Algorhytmus, erzeugt eindeutige Farbe fuer jedes Semester. Funktioniert ab 2001 die naechsten 1000 Jahre.....
    $year_of_millenium=date ("Y", $sem_start_time) % 1000;
    $index=$year_of_millenium * 2;
    if (date ("n", $sem_start_time) > 6)
        $index++;
    $group=($index % 7) + 1;

    return $group;
}

/**
 * The function shortens a string, but it uses the first 2/3 and the last 1/3
 *
 * The parts will be divided by a "[...]". The functions is to use like php's
 * mb_substr function.
 *
 * @param string  $what  the original string
 * @param integer $start start pos, 0 is the first pos
 * @param integer $end   end pos
 *
 * @return string
 *
 *
 */
function my_substr($what, $start, $end)
{
    $length=$end-$start;
    $what_length = mb_strlen($what);
    // adding 5 because: mb_strlen("[...]") == 5
    if ($what_length > $length + 5) {
        $what = mb_substr($what, $start, round(($length / 3) * 2))
              . "[...]" . mb_substr($what, $what_length - round($length / 3), $what_length);
    }
    return $what;
}

/**
 * Retrieves the fullname for a given user_id
 *
 * @param string $user_id   if omitted, current user_id is used
 * @param string $format    output format
 * @param bool   $htmlready if true, htmlReady is applied to all output-strings
 *
 * @return string
 */
function get_fullname($user_id = "", $format = "full" , $htmlready = false)
{
    static $cache;
    global $user, $_fullname_sql;

    if (!$user_id) {
        $user_id = $user->id;
    }

    if (User::findCurrent()->id === $user_id) {
        $fullname = User::findCurrent()->getFullName($format);
        return $htmlready ? htmlReady($fullname) : $fullname;
    }

    $hash = md5($user_id . $format);
    if (!isset($cache[$hash])) {
        $query = "SELECT {$_fullname_sql[$format]}
                  FROM auth_user_md5
                  LEFT JOIN user_info USING (user_id)
                  WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $cache[$hash] = $statement->fetchColumn() ?: _('unbekannt');
    }

    return $htmlready ? htmlReady($cache[$hash]) : $cache[$hash];
}

/**
 * Retrieves the fullname for a given username
 *
 * @param string $uname     if omitted, current user_id is used
 * @param string $format    output format
 * @param bool   $htmlready if true, htmlReady is applied to all output-strings
 *
 * @return       string
 */
function get_fullname_from_uname($uname = "", $format = "full", $htmlready = false)
{
    static $cache;
    global $auth, $_fullname_sql;

    if (!$uname) {
        $uname = $auth->auth['uname'];
    }

    $hash = md5($uname . $format);
    if (!isset($cache[$hash])) {
        $query = "SELECT {$_fullname_sql[$format]}
                  FROM auth_user_md5
                  LEFT JOIN user_info USING (user_id)
                  WHERE username = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$uname]);
        $cache[$hash] = $statement->fetchColumn() ?: _('unbekannt');
    }

    return $htmlready ? htmlReady($cache[$hash]) : $cache[$hash];
}

/**
 * Retrieves the username for a given user_id
 *
 * @global object $auth
 * @staticvar array $cache
 *
 * @param string $user_id if omitted, current username will be returned
 *
 * @return string
 *
 */
function get_username($user_id = "")
{
    static $cache = [];
    global $auth;

    if (!$user_id || $user_id == $auth->auth['uid']) {
        return $auth->auth['uname'];
    }

    if (!isset($cache[$user_id])) {
        $query = "SELECT username FROM auth_user_md5 WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $cache[$user_id] = $statement->fetchColumn();
    }

    return $cache[$user_id];
}

/**
 * Retrieves the userid for a given username
 *
 * uses global $online array if user is online
 *
 * @global object $auth
 * @staticvar array $cache
 *
 * @param string $username if omitted, current user_id will be returned
 *
 * @return string
 */
function get_userid($username = "")
{
    static $cache = [];
    global $auth;

    if (!$username || $username == $auth->auth['uname']) {
        return $auth->auth['uid'];
    }

    // Read id from database if no cached version is available
    if (!isset($cache[$username])) {
        $query = "SELECT user_id FROM auth_user_md5 WHERE username = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$username]);
        $cache[$username] = $statement->fetchColumn();
    }

    return $cache[$username];
}


/**
 * Return an array containing the nodes of the sem-tree-path
 *
 * @param string $seminar_id the seminar to get the path for
 * @param int    $depth      the depth
 * @param string $delimeter  a string to separate the path parts
 *
 * @return array
 */
function get_sem_tree_path($seminar_id, $depth = false, $delimeter = ">")
{
    $the_tree = TreeAbstract::GetInstance("StudipSemTree");
    $view = DbView::getView('sem_tree');
    $ret = null;
    $view->params[0] = $seminar_id;
    $rs = $view->get_query("view:SEMINAR_SEM_TREE_GET_IDS");
    while ($rs->next_record()){
        $ret[$rs->f('sem_tree_id')] = $the_tree->getShortPath($rs->f('sem_tree_id'), NULL, $delimeter, $depth ? $depth - 1 : 0);
    }
    return $ret;
}

/**
 * check_and_set_date
 *
 * Checks if given date is valid and sets field in array accordingly.
 * (E.g. $admin_admission_data['admission_enddate'])
 *
 * @param mixed $tag    day or placeholder for day
 * @param mixed $monat  month or placeholder for month
 * @param mixed $jahr   year or placeholder for year
 * @param mixed $stunde hours or placeholder for hours
 * @param mixed $minute minutes or placeholder for minutes
 * @param array &$arr   Reference to array to update. If NULL, only check is performed
 * @param mixed $field  Name of field in array to be set
 *
 * @return bool  true if date was valid, false else
 */
function check_and_set_date($tag, $monat, $jahr, $stunde, $minute, &$arr, $field)
{

    $check=TRUE; // everything ok?
    if (($jahr>0) && ($jahr<100))
        $jahr=$jahr+2000;

    if ($monat == _("mm")) $monat=0;
    if ($tag == _("tt")) $tag=0;
    if ($jahr == _("jjjj")) $jahr=0;
    //if ($stunde == _("hh")) $stunde=0;
    if ($minute == _("mm")) $minute=0;

    if (($monat) && ($tag) && ($jahr)) {
        if ($stunde==_("hh")) {
            $check=FALSE;
        }

        if ((!checkdate((int)$monat, (int)$tag, (int)$jahr) && ((int)$monat) && ((int)$tag) && ((int)$jahr))) {
            $check=FALSE;
        }

        if (($stunde > 24) || ($minute > 59)
            || ($stunde == 24 && $minute > 0) ) {
            $check=FALSE;
        }

        if ($stunde == 24) {
            $stunde = 23;
            $minute = 59;
        }

        if ($arr) {
            if ($check) {
                $arr[$field] = mktime((int)$stunde,(int)$minute, 0,$monat,$tag,$jahr);
            } else {
                $arr[$field] = -1;
            }
        }
    }
    return $check;
}


/**
 * gets an entry from the studip configuration table
 *
 * @param string $key the key for the config entry
 *
 * @return string  the value
 *
 */
function get_config($key)
{
    return Config::get()->$key;
}

/**
 * reset the order-positions for the lecturers in the passed seminar,
 * starting at the passed position
 *
 * @param string $s_id     the seminar to work on
 * @param int    $position the position to start with
 *
 * @return void
 */
function re_sort_dozenten($s_id, $position)
{
    $query = "UPDATE seminar_user
              SET position = position - 1
              WHERE Seminar_id = ? AND status = 'dozent' AND position > ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$s_id, $position]);
}

/**
 * reset the order-positions for the tutors in the passed seminar,
 * starting at the passed position
 *
 * @param string $s_id     the seminar to work on
 * @param int    $position the position to start with
 *
 * @return void
 */
function re_sort_tutoren($s_id, $position)
{
    $query = "UPDATE seminar_user
              SET position = position - 1
              WHERE Seminar_id = ? AND status = 'tutor' AND position > ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$s_id, $position]);
}

/**
 * return the highest position-number increased by one for the
 * passed user-group in the passed seminar
 *
 * @param string $status     can be on of 'tutor', 'dozent', ...
 * @param string $seminar_id the seminar to work on
 *
 * @return int  the next available position
 */
function get_next_position($status, $seminar_id)
{
    $query = "SELECT MAX(position) + 1
              FROM seminar_user
              WHERE Seminar_id = ? AND status = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id, $status]);

   return $statement->fetchColumn() ?: 0;
}

/**
 * converts a string to a float, depending on the locale
 *
 * @param string $str the string to convert to float
 *
 * @return float the string casted to float
 */
function StringToFloat($str)
{
    $str = mb_substr((string)$str,0,13);
    $locale = localeconv();
    $from = ($locale["thousands_sep"] ? $locale["thousands_sep"] : ',');
    $to = ($locale["decimal_point"] ? $locale["decimal_point"] : '.');
    if(mb_strstr($str, $from)){
        $conv_str = str_replace($from, $to, $str);
        $my_float = (float)$conv_str;
        if ($conv_str === (string)$my_float) return $my_float;
    }
    return (float)$str;
}

/**
 * check which perms the currently logged in user had in the
 * passed archived seminar
 *
 * @global array $perm
 * @global object $auth
 * @staticvar array $archiv_perms
 *
 * @param string $seminar_id the seminar in the archive
 *
 * @return string the perm the user had
 */
function archiv_check_perm($seminar_id)
{
    static $archiv_perms;
    global $perm, $user;

    $u_id = $user->id;

    // root darf sowieso ueberall dran
    if ($perm->have_perm('root')) {
        return 'admin';
    }

    if (!is_array($archiv_perms)){
        $query = "SELECT seminar_id, status FROM archiv_user WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$u_id]);
        $archiv_perms = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        if ($perm->have_perm("admin")){
            $query = "SELECT archiv.seminar_id, 'admin'
                      FROM user_inst
                      INNER JOIN archiv ON (heimat_inst_id = institut_id)
                      WHERE user_inst.user_id = ? AND user_inst.inst_perms = 'admin'";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$u_id]);
            $temp_perms = $statement->fetchGrouped(PDO::FETCH_COLUMN);

            $archiv_perms = array_merge($archiv_perms, $temp_perms);
        }
        if ($perm->is_fak_admin()){
            $query = "SELECT archiv.seminar_id, 'admin'
                      FROM user_inst
                      INNER JOIN Institute ON (user_inst.institut_id = Institute.fakultaets_id)
                      INNER JOIN archiv ON (archiv.heimat_inst_id = Institute.institut_id)
                      WHERE user_inst.user_id = ? AND user_inst.inst_perms = 'admin'";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$u_id]);
            $temp_perms = $statement->fetchGrouped(PDO::FETCH_COLUMN);

            $archiv_perms = array_merge($archiv_perms, $temp_perms);
        }
    }
    return $archiv_perms[$seminar_id];
}

/**
 * retrieve a list of all online users
 *
 * @global object $user
 * @global array  $_fullname_sql
 *
 * @param int    $active_time filter: the time in minutes until last life-sign
 * @param string $name_format format the fullname shall have
 *
 * @return array
 */
function get_users_online($active_time = 5, $name_format = 'full_rev')
{
    if (!isset($GLOBALS['_fullname_sql'][$name_format])) {
        $name_format = reset(array_keys($GLOBALS['_fullname_sql']));
    }

    $query = "SELECT a.username AS temp, a.username, {$GLOBALS['_fullname_sql'][$name_format]} AS name,
                     ABS(CAST(UNIX_TIMESTAMP() AS SIGNED) - CAST(last_lifesign AS SIGNED)) AS last_action,
                     a.user_id, IF(owner_id IS NOT NULL, 1, 0) AS is_buddy, " . get_vis_query('a', 'online') . " AS is_visible,
                     a.visible
              FROM user_online uo
              JOIN auth_user_md5 a ON (a.user_id = uo.user_id)
              LEFT JOIN user_info ON (user_info.user_id = uo.user_id)
              LEFT JOIN user_visibility ON (user_visibility.user_id = uo.user_id)
              LEFT JOIN contact ON (owner_id = ? AND contact.user_id = a.user_id)
              WHERE last_lifesign > ? AND uo.user_id <> ?
              ORDER BY {$GLOBALS['_fullname_sql'][$name_format]} ASC";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([
        $GLOBALS['user']->id,
        time() - $active_time * 60,
        $GLOBALS['user']->id,
    ]);
    $online = $statement->fetchGrouped();

    // measure users online
    if ($active_time === 10) {
        Metrics::gauge('core.users_online', sizeof($online));
    }

    return $online;
}

/**
 * get the number of currently online users
 *
 * @param int $active_time filter: the time in minutes until last life-sign
 *
 * @return int
 */
function get_users_online_count($active_time = 10)
{
    $cache = StudipCacheFactory::getCache();
    $online_count = $cache->read("online_count/{$active_time}");
    if ($online_count === false) {
        $query = "SELECT COUNT(*) FROM user_online
                  WHERE last_lifesign > ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([time() - $active_time * 60]);
        $online_count = $statement->fetchColumn();
        $cache->write("online_count/{$active_time}", $online_count, 180);
    }
    if ($GLOBALS['user']->id && $GLOBALS['user']->id !== 'nobody') {
        --$online_count;
    }
    return $online_count > 0 ? $online_count : 0;
}

/**
 * return a studip-ticket
 *
 * @return string a unique id referring to a newly created ticket
 */
function get_ticket()
{
    return Seminar_Session::get_ticket();
}

/**
 * check if the passed ticket is valid
 *
 * @param string $studipticket the ticket-id to check
 *
 * @return bool
 */
function check_ticket($studipticket)
{
    return Seminar_Session::check_ticket($studipticket);
}

/**
 * searches
 *
 * @global array $perm
 * @global object $user
 * @global array $_fullname_sql
 *
 * @param string $search_str  optional search-string
 * @param string $search_user optional user to search for
 * @param bool   $show_sem    if true, the seminar is added to the result
 *
 * @return array
 */
function search_range($search_str = false, $search_user = false, $show_sem = true)
{
    global $perm, $user, $_fullname_sql;

    // Helper function that obtains the correct name for an entity taking
    // in account whether the semesters should be displayed or not
    $formatName = function ($row) use ($show_sem) {
        $name = $row['Name'];
        if ($show_sem) {
            $name = sprintf('%s (%s%s)',
                            $name,
                            $row['startsem'],
                            $row['startsem'] != $row['endsem'] ? ' - ' . $row['endsem'] : '');
        }
        return $name;
    };

    $search_result = [];
    $show_sem_sql1 = ",s.start_time,sd1.name AS startsem,IF(s.duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem ";
    $show_sem_sql2 = "LEFT JOIN semester_data sd1 ON (start_time BETWEEN sd1.beginn AND sd1.ende)
                      LEFT JOIN semester_data sd2 ON (start_time + duration_time BETWEEN sd2.beginn AND sd2.ende)";


    if ($search_str && $perm->have_perm('root')) {
        if ($search_user) {
            $query = "SELECT user_id, CONCAT({$_fullname_sql['full']}, ' (', username, ')') AS name
                      FROM auth_user_md5 AS a
                      LEFT JOIN user_info USING (user_id)
                      WHERE CONCAT(Vorname, ' ', Nachname, ' ', username) LIKE CONCAT('%', ?, '%')
                      ORDER BY Nachname, Vorname";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$search_str]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $search_result[$row['user_id']] = [
                    'type' => 'user',
                    'name' => $row['name'],
                ];
            }
        }

        $_hidden = _('(versteckt)');
        $query = "SELECT Seminar_id, IF(s.visible = 0, CONCAT(s.Name, ' {$_hidden}'), s.Name) AS Name %s
                  FROM seminare AS s %s
                  WHERE s.Name LIKE CONCAT('%%', ?, '%%')
                  ORDER BY start_time DESC, Name";
        $query = $show_sem
               ? sprintf($query, $show_sem_sql1, $show_sem_sql2)
               : sprintf($query, '', '');
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$search_str]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Seminar_id']] = [
                'type'      => 'sem',
                'name'      => $formatName($row),
                'starttime' => $row['start_time'],
                'startsem'  => $row['startsem'],
            ];
        }

        $query = "SELECT Institut_id, Name, IF(Institut_id = fakultaets_id, 'fak', 'inst') AS type
                  FROM Institute
                  WHERE Name LIKE CONCAT('%', ?, '%')
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$search_str]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Institut_id']] = [
                'type' => $row['type'],
                'name' => $row['Name'],
            ];
        }
    } elseif ($search_str && $perm->have_perm('admin')) {
        $_hidden = _('(versteckt)');
        $query = "SELECT s.Seminar_id, IF(s.visible = 0, CONCAT(s.Name, ' {$_hidden}'), s.Name) AS Name %s
                  FROM user_inst AS a
                  JOIN seminare AS s USING (Institut_id) %s
                  WHERE a.user_id = ? AND a.inst_perms = 'admin' AND s.Name LIKE CONCAT('%%', ?, '%%')
                  ORDER BY start_time";
        $query = $show_sem
               ? sprintf($query, $show_sem_sql1, $show_sem_sql2)
               : sprintf($query, '', '');
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user->id, $search_str]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Seminar_id']] = [
                'type'      => 'sem',
                'name'      => $formatName($row),
                'starttime' => $row['start_time'],
                'startsem'  => $row['startsem'],
            ];
        }

        $query = "SELECT b.Institut_id, b.Name
                  FROM user_inst AS a
                  JOIN Institute AS b USING (Institut_id)
                  WHERE a.user_id = ? AND a.inst_perms = 'admin'
                    AND a.institut_id != b.fakultaets_id AND b.Name LIKE CONCAT('%', ?, '%')
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user->id, $search_str]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Institut_id']] = [
                'type' => 'inst',
                'name' => $row['Name'],
            ];
        }
        if ($perm->is_fak_admin()) {
            $_hidden = _('(versteckt)');
            $query = "SELECT s.Seminar_id, IF(s.visible = 0, CONCAT(s.Name, ' {$_hidden}'), s.Name) AS Name %s
                      FROM user_inst AS a
                      JOIN Institute AS b ON (a.Institut_id = b.Institut_id AND b.Institut_id = b.fakultaets_id)
                      JOIN Institute AS c ON (c.fakultaets_id = b.Institut_id AND c.fakultaets_id != c.Institut_id)
                      JOIN seminare AS s ON (s.Institut_id = c.Institut_id) %s
                      WHERE a.user_id = ? AND a.inst_perms = 'admin'
                        AND s.Name LIKE CONCAT('%%', ?, '%%')
                      ORDER BY start_time DESC, Name";
            $query = $show_sem
                   ? sprintf($query, $show_sem_sql1, $show_sem_sql2)
                   : sprintf($query, '', '');
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$user->id, $search_str]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $search_result[$row['Seminar_id']] = [
                    'type'      => 'sem',
                    'name'      => $formatName($row),
                    'starttime' => $row['start_time'],
                    'startsem'  => $row['startsem'],
                ];
            }

            $query = "SELECT c.Institut_id, c.Name
                      FROM user_inst AS a
                      JOIN Institute AS b ON (a.Institut_id = b.Institut_id AND b.Institut_id = b.fakultaets_id)
                      JOIN Institute AS c ON (c.fakultaets_id = b.institut_id AND c.fakultaets_id != c.institut_id)
                      WHERE a.user_id = ? AND a.inst_perms = 'admin'
                        AND c.Name LIKE CONCAT('%', ?, '%')
                      ORDER BY Name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$user->id, $search_str]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $search_result[$row['Institut_id']] = [
                    'type' => 'inst',
                    'name' => $row['Name'],
                ];
            }

            $query = "SELECT b.Institut_id, b.Name
                      FROM user_inst AS a
                      JOIN Institute AS b ON (a.Institut_id = b.Institut_id AND b.Institut_id = b.fakultaets_id)
                      WHERE a.user_id = ? AND a.inst_perms = 'admin'
                        AND b.Name LIKE CONCAT('%', ?, '%')
                      ORDER BY Name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$user->id, $search_str]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $search_result[$row['Institut_id']] = [
                    'type' => 'inst',
                    'name' => $row['Name'],
                ];
            }
        }
    } elseif ($perm->have_perm('tutor') || $perm->have_perm('autor')) {
        // autors my also have evaluations and news in studygroups with proper rights
        $_hidden = _('(versteckt)');
        $query = "SELECT s.Seminar_id, IF(s.visible = 0, CONCAT(s.Name, ' {$_hidden}'), s.Name) AS Name %s
                  FROM seminar_user AS a
                  JOIN seminare AS s USING (Seminar_id) %s
                  WHERE a.user_id = ? AND a.status IN ('tutor', 'dozent')
                  ORDER BY start_time DESC, Name";
        $query = $show_sem
               ? sprintf($query, $show_sem_sql1, $show_sem_sql2)
               : sprintf($query, '', '');
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user->id]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Seminar_id']] = [
                'type'      => 'sem',
                'name'      => $formatName($row),
                'starttime' => $row['start_time'],
                'startsem'  => $row['startsem'],
            ];
        }

        $query = "SELECT Institut_id, b.Name,
                         IF (Institut_id = fakultaets_id, 'fak', 'inst') AS type
                  FROM user_inst AS a
                  JOIN Institute AS b USING (Institut_id)
                  WHERE a.user_id = ? AND a.inst_perms IN ('dozent','tutor')
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user->id]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Institut_id']] = [
                'name' => $row['Name'],
                'type' => $row['type'],
            ];
        }
    }

    if (get_config('DEPUTIES_ENABLE')) {
        $_hidden = _('(versteckt)');
        $_deputy = _('Vertretung');
        $query = "SELECT s.Seminar_id,
                         CONCAT(IF(s.visible = 0, CONCAT(s.Name, ' {$_hidden}'), s.Name), ' [{$_deputy}]') AS Name %s
                  FROM seminare AS s
                  JOIN deputies AS d ON (s.Seminar_id = d.range_id) %s
                  WHERE d.user_id = ?
                  ORDER BY s.start_time DESC, Name";
        $query = $show_sem
               ? sprintf($query, $show_sem_sql1, $show_sem_sql2)
               : sprintf($query, '', '');
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user->id]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $search_result[$row['Seminar_id']] = [
                'type'      => 'sem',
                'name'      => $formatName($row),
                'starttime' => $row['start_time'],
                'startsem'  => $row['startsem'],
            ];
        }
        if (isDeputyEditAboutActivated()) {
            $query = "SELECT a.user_id, a.username, 'user' AS type,
                             CONCAT({$_fullname_sql['full']}, ' (', username, ')') AS name
                      FROM auth_user_md5 AS a
                      JOIN user_info USING (user_id)
                      JOIN deputies AS d ON (a.user_id = d.range_id)
                      WHERE d.user_id = ?
                      ORDER BY name ASC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $user->id
            ]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $search_result[$row['user_id']] = $row;
            }
        }
    }

    return $search_result ?: null;
}

/**
 * format_help_url($keyword)
 * returns URL for given help keyword
 *
 * @param string $keyword the help-keyword
 *
 * @return string the help-url
 */
function format_help_url($keyword)
{
    $helppage = $keyword;

    // $loc is only set if special help view for installation is known
    $loc = "";

    $locationid = Config::get()->EXTERNAL_HELP_LOCATIONID;
    if ($locationid && $locationid !== 'default') {
        $loc = $locationid . '/';
    }

    // all help urls need short language tag (de, en)
    $lang = 'de';
    if ($_SESSION['_language']) {
        list($lang) = explode('_', $_SESSION['_language']);
    }

    // determine Stud.IP version as of MAJOR.MINOR
    // from SOFTWARE_VERSION. That variable MUST match pattern MAJOR.MINOR.*
    preg_match('/^(\d+\.\d+)/', $GLOBALS['SOFTWARE_VERSION'], $v);
    $version = $v[0];

    $help_query = sprintf('https://hilfe.studip.de/help/%s/%s/%s%s',
                          $version, $lang, $loc, $helppage);
    return $help_query;
}

/**
 * Splits a string by space characters and returns these words as an array.
 *
 * @param string $string the string to split
 *
 * @return array  the words of the string as array
 */
function words($string)
{
  return preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
}

/**
 * Does not encode anything anymore and just returns the data it received.
 *
 * @deprecated
 *
 * @param mixed $data
 *
 * @return mixed unaltered input $data
 */
function studip_utf8encode($data)
{
    return $data;
}

/**
 * Does not decode anything anymore and just returns the data it received.
 *
 * @deprecated
 *
 * @param mixed $data
 *
 * @return mixed unaltered input $data
 */
function studip_utf8decode($data)
{
    return $data;
}

/**
 * Encodes a string or array from UTF-8 to Stud.IP encoding (WINDOWS-1252/ISO-8859-1 with numeric HTML-ENTITIES)
 *
 * @param mixed $data a string in UTF-8 or an array with all strings encoded in utf-8
 *
 * @return string  the string in WINDOWS-1252/HTML-ENTITIES
 */
function legacy_studip_utf8decode($data)
{
    if (is_array($data)) {
        $new_data = [];
        foreach ($data as $key => $value) {
            $key = legacy_studip_utf8decode($key);
            $new_data[$key] = legacy_studip_utf8decode($value);
        }
        return $new_data;
    }

    if (!preg_match('/[\200-\377]/', $data)) {
        return $data;
    } else {
        $windows1252 = [
            "\x80" => '&#8364;',
            "\x81" => '&#65533;',
            "\x82" => '&#8218;',
            "\x83" => '&#402;',
            "\x84" => '&#8222;',
            "\x85" => '&#8230;',
            "\x86" => '&#8224;',
            "\x87" => '&#8225;',
            "\x88" => '&#710;',
            "\x89" => '&#8240;',
            "\x8A" => '&#352;',
            "\x8B" => '&#8249;',
            "\x8C" => '&#338;',
            "\x8D" => '&#65533;',
            "\x8E" => '&#381;',
            "\x8F" => '&#65533;',
            "\x90" => '&#65533;',
            "\x91" => '&#8216;',
            "\x92" => '&#8217;',
            "\x93" => '&#8220;',
            "\x94" => '&#8221;',
            "\x95" => '&#8226;',
            "\x96" => '&#8211;',
            "\x97" => '&#8212;',
            "\x98" => '&#732;',
            "\x99" => '&#8482;',
            "\x9A" => '&#353;',
            "\x9B" => '&#8250;',
            "\x9C" => '&#339;',
            "\x9D" => '&#65533;',
            "\x9E" => '&#382;',
            "\x9F" => '&#376;'];
        return str_replace(
            array_values($windows1252),
            array_keys($windows1252),
            utf8_decode(mb_encode_numericentity(
                $data,
                [0x100, 0xffff, 0, 0xffff],
                'UTF-8'
            ))
        );
    }
}

/**
 * Special stud.ip version of json_decode() that also converts the data
 * from utf8 and creates an associative array by default (this differs
 * from the default behavior of json_decode() !).
 *
 * @param String $json
 * @param bool   $assoc
 * @param int    $depth
 * @param int    $options
 */
function studip_json_decode($json, $assoc = true, $depth = 512, $options = 0)
{
    $data = json_decode($json, $assoc, $depth, $options);

    return $data;
}

/**
 * Special stud.ip version of json_decode() that also converts the data
 * to utf8.
 *
 * @param mixed $data
 * @param int   $options
 * @param int   $depth
 */
function studip_json_encode($data, $options = 0)
{
    $json = json_encode($data, $options);

    return $json;
}

/**
 * Encode an HTTP header parameter (e.g. filename for 'Content-Disposition').
 *
 * @param string $name  parameter name
 * @param string $value parameter value
 *
 * @return string encoded header text (using RFC 2616 or 5987 encoding)
 */
function encode_header_parameter($name, $value)
{
    if (preg_match('/[\200-\377]/', $value)) {
        // use RFC 5987 encoding (ext-parameter)
        return $name . "*=UTF-8''" . rawurlencode($value);
    } else {
        // use RFC 2616 encoding (quoted-string)
        return $name . '="' . addslashes($value) . '"';
    }
}

/**
 * Get the title used for the given status ('dozent', 'tutor' etc.) for the
 * specified SEM_TYPE. Alternative titles can be defined in the config.inc.php.
 *
 * @global array $SEM_TYPE
 * @global array $DEFAULT_TITLE_FOR_STATUS
 *
 * @param string $type     status ('dozent', 'tutor', 'autor', 'user' or 'accepted')
 * @param int    $count    count, this determines singular or plural form of title
 * @param int    $sem_type sem_type of course (defaults to type of current course)
 *
 * @return string  translated title for status
 */
function get_title_for_status($type, $count, $sem_type = NULL)
{
    global $SEM_TYPE, $DEFAULT_TITLE_FOR_STATUS;

    if (is_null($sem_type)) {
        $sem_type = Context::getArtNum();
    }

    $atype = 'title_'.$type;

    if (is_array($SEM_TYPE[$sem_type][$atype])) {
        $title = $SEM_TYPE[$sem_type][$atype];
    } else if (isset($DEFAULT_TITLE_FOR_STATUS[$type])) {
        $title = $DEFAULT_TITLE_FOR_STATUS[$type];
    } else {
        $title = ['unbekannt', 'unbekannt'];
    }

    return ngettext($title[0], $title[1], $count);
}

/**
 * Test whether the given URL refers to some page or resource of
 * this Stud.IP installation.
 *
 * @param string $url url to check
 *
 * @return mixed
 */
function is_internal_url($url)
{
    if (preg_match('%^[a-z]+:%', $url)) {
        return mb_strpos($url, $GLOBALS['ABSOLUTE_URI_STUDIP']) === 0;
    }

    if ($url[0] === '/') {
        return mb_strpos($url, $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) === 0;
    }

    return true;
}

/**
 * Return the list of SEM_TYPES that represent study groups in this
 * Stud.IP installation.
 *
 * @return array  list of SEM_TYPES used for study groups
 */
function studygroup_sem_types()
{
    $result = [];

    foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type) {
        if ($GLOBALS['SEM_CLASS'][$sem_type['class']]['studygroup_mode']) {
            $result[] = $id;
        }
    }

    return $result;
}

/**
 * generates form fields for the submitted multidimensional array
 *
 * @param string $variable the name of the array, which is filled with the data
 * @param mixed  $data     the data-array
 * @param mixed  $parent   leave this entry as is
 *
 * @return string the inputs of type hidden as html
 */
function addHiddenFields($variable, $data, $parent = [])
{
    if (is_array($data)) {
        foreach($data as $key => $value) {
            if (is_array($value)) {
                $ret .= addHiddenFields($variable, $value, array_merge($parent, [$key]));
            } else {
                $ret.= '<input type="hidden" name="'. htmlReady($variable .'['. implode('][', array_merge($parent, [$key])) .']').'" value="'. htmlReady($value) .'">' ."\n";
            }
        }
    } else {
        $ret.= '<input type="hidden" name="'. htmlReady($variable) .'" value="'. htmlReady($data) .'">' ."\n";
    }

    return $ret;
}

/**
 * Returns a new array that is a one-dimensional flattening of this
 * array (recursively). That is, for every element that is an array,
 * extract its elements into the new array.
 *
 * @param array $ary the array to be flattened
 * @return array the flattened array
 */
function array_flatten($ary)
{
    $i = 0;
    while ($i < sizeof($ary)) {
        if (is_array($ary[$i])) {
            array_splice($ary, $i, 1, $ary[$i]);
        } else {
            $i++;
        }
    }
    return $ary;
}

/**
 * Displays "relative time" - a textual representation between now and a
 * certain timestamp, e.g. "3 hours ago".
 *
 * @param int  $timestamp        Timestamp to relate to.
 * @param bool $verbose          Display long or short texts (optional)
 * @param int  $displayed_levels How many levels shall be displayed
 * @param int  $tolerance        Defines a tolerance area of seconds around
 *                               now (How many seconds must have passed until
 *                               the function won't return "now")
 * @return String Textual representation of the difference between the passed
 *                timestamp and now
 */
function reltime($timestamp, $verbose = true, $displayed_levels = 1, $tolerance = 5)
{
    if ($verbose) {
        $glue = [', ', _(' und ')];
        $levels = [
            [60, _('%u Sekunde'), _('%u Sekunden')],
            [60, _('%u Minute'),  _('%u Minuten')],
            [24, _('%u Stunde'),  _('%u Stunden')],
            [30, _('%u Tag'),     _('%u Tagen')],
            [12, _('%u Monat'),   _('%u Monaten')],
            [99, _('%u Jahr'),    _('%u Jahren')],
        ];
    } else {
        $glue = ['', ''];
        $levels = [
            [60, _('%us'),   _('%us')],
            [60, _('%umin'), _('%umin')],
            [24, _('%uh'),   _('%uh')],
            [30, _('%ud'),   _('%ud')],
            [12, _('%uM'),   _('%uM')],
            [99, _('%uy'),   _('%uy')],
        ];
    }

    $now   = time();
    $diff  = abs($timestamp - $now);

    if ($diff < $tolerance) {
        return _('jetzt');
    }

    $chunks = [];
    for ($i = 0; $i < count($levels) && $diff > 0; $i++) {
        $remainder = $diff % $levels[$i][0];
        if ($remainder > 0) {
            $chunks[] = sprintf(ngettext($levels[$i][1], $levels[$i][2], $remainder), $remainder);
        }
        $diff = floor($diff / $levels[$i][0]);
        if ($diff === 0) {
            break;
        }
    }

    $chunks = array_reverse($chunks);
    $chunks = array_slice($chunks, 0, $displayed_levels);
    if (count($chunks) == 1) {
        $result = $chunks[0];
    } else {
        $result = $chunks[0] . $glue[1] . implode($glue[0], array_slice($chunks, 1));
    }
    if ($verbose) {
        $result = sprintf($timestamp < $now ? _('vor %s') : _('in %s'), $result);
    }
    return $result;
}

/**
 * Displays a filesize in a (shortened) human readable form including the
 * according units. For instance, 1234567 would be displayed as "1 MB" or
 * 12345 would be displayed as "12 kB".
 * The function can display the units in a short or a long form ("1 b" vs.
 * "1 Byte").
 * Optionally, more than one unit part can be displayed. For instance, 1234567
 * could also be displayed as "1 MB, 234 kB, 567 b".
 *
 * @param int    $size             The raw filesize as integer
 * @param bool   $verbose          Use short or long unit names
 * @param int    $displayed_levels How many unit parts should be displayed
 * @param String $glue             Text used to glue the different unit parts
 *                                 together
 * @return String The filesize in human readable form.
 * @todo Allow "1,3 MB"
 */
function relsize($size, $verbose = true, $displayed_levels = 1, $glue = ', ', $truncate = false)
{
    $units = [
        'B' => 'Byte',
        'kB' => 'Kilobyte',
        'MB' => 'Megabyte',
        'GB' => 'Gigabyte',
        'TB' => 'Terabyte',
        'PB' => 'Petabyte',
        'EB' => 'Exabyte',
        'ZB' => 'Zettabyte',
        'YB' => 'Yottabyte',
    ];

    $result = [];
    foreach ($units as $short => $long) {
        $remainder = $size % 1024;

        $template = sprintf('%%.1f %s%%s', $verbose ? $long : $short);
        $result[$template] = $remainder;

        $size = floor($size / 1024);
        if ($size == 0) {
            break;
        }
    }

    if ($displayed_levels == 1 && count($result) >=2 && !$truncate) {
        $result = array_slice($result, -2);

        $fraction = array_shift($result);
        $template = key($result);
        $size     = array_pop($result);

        $result = [
            $template => $size + $fraction / 1024,
        ];
    } elseif ($displayed_levels > 0) {
        $result = array_slice($result, -$displayed_levels);
    }

    $display = [];
    foreach ($result as $template => $size) {
        if ($truncate || $size - floor($size) < 0.1) {
            $template = str_replace('%.1f', '%u', $template);
            $size     = (int)$size;
        }
        $display[] = sprintf($template, $size, ($verbose && $size !== 1) ? 's' : '');
    }
    return implode($glue, array_reverse($display));
}

/**
 * extracts route
 *
 * @param string $route           route (optional, uses REQUEST_URI otherwise)
 *
 * @return  string  route
 */
function get_route($route = '')
{
    $route = mb_substr(parse_url($route ?: $_SERVER['REQUEST_URI'], PHP_URL_PATH), mb_strlen($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']));
    if (mb_strpos($route, 'plugins.php/') !== false) {
        $trails = explode('plugins.php/', $route);
        $pieces = explode('/', $trails[1]);
        $route = 'plugins.php/' . $pieces[0] . ($pieces[1] ? '/' . $pieces[1] : '') . ($pieces[2] ? '/' . $pieces[2] : '');
    } elseif (mb_strpos($route, 'dispatch.php/') !== false) {
        $trails = explode('dispatch.php/', $route);
        $dispatcher = new StudipDispatcher();
        $pieces = explode('/', $trails[1]);
        foreach ($pieces as $index => $piece) {
            $trail .= ($trail ? '/' : '') . $piece;
            if ($dispatcher->file_exists($trail . '.php')) {
                $route = 'dispatch.php/' . $trail . ($pieces[$index+1] ? '/' . $pieces[$index+1] : '');
            }
        }
    }
    while (mb_substr($route, mb_strlen($route)-6, 6) == '/index') {
        $route = mb_substr($route, 0, mb_strlen($route)-6);
    }
    return $route;
}

/**
 * compares actual route to requested route
 *
 * @param string $requested_route         requested route (for help content or tour)
 * @param string $current_route           current route (optional)
 *
 * @return  boolean  result
 */
function match_route($requested_route, $current_route = '')
{
    if (!$current_route) {
        $current_route = get_route();
    }
    $route_parts = explode('?', $requested_route);
    // if base routes don't match, return false without further checks
    if (!fnmatch($route_parts[0], $current_route)) {
        return false;
    }
    // if no parameters given and base routes do match, return true
    if (!$route_parts[1]) {
        return true;
    }
    // extract vars and check if they are set accordingly
    $vars = [];
    parse_str($route_parts[1], $vars);
    if (!count($vars)) {
        return false;
    }
    foreach ($vars as $name => $value) {
        if (@$_REQUEST[$name] != $value) {
            return false;
        }
    }
    return true;
}

function studip_default_exception_handler($exception) {
    require_once 'lib/visual.inc.php';

    // send exception to metrics backend
    if (class_exists('Metrics')) {
        $exception_class = mb_strtolower(
            preg_replace(
                '/(?<=\w)([A-Z])/',
                '_\\1',
                get_class($exception)));
        Metrics::increment('core.exception.' . $exception_class);
    }

    while (ob_get_level()) {
        ob_end_clean();
    }
    $layout = 'layouts/base.php';
    if ($exception instanceof AccessDeniedException) {
        PageLayout::setTitle(_('Zugriff verweigert'));

        $status = 403;
        $template = 'access_denied_exception';
    } else if ($exception instanceof CheckObjectException) {
        $status = 403;
        $template = 'check_object_exception';
    } elseif ($exception instanceof LoginException) {
        $GLOBALS['auth']->login_if(true);
    } else {
        if ($exception instanceOf Trails_Exception) {
            $status = $exception->getCode();
        } else {
            $status = 500;
        }
        error_log($exception->__toString());
        $template = 'unhandled_exception';
    }

    header('HTTP/1.1 ' . $status . ' ' . $exception->getMessage());

    // ajax requests return JSON instead
    // re-use the http status code determined above
    if (!strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest')) {
        header('Content-Type: application/json; charset=UTF-8');
        $template = 'json_exception';
        $layout = null;
    }


    try {
        $args = compact('exception', 'status');
        ob_start();
        echo $GLOBALS['template_factory']->render($template, $args, $layout);
    } catch (Exception $e) {
        ob_end_clean();
        echo 'Error: ' . htmlReady($e->getMessage());
    }
    exit;
}

/**
 * Converts a string to camelCase.
 *
 * @param String $string  The string that should be converted
 * @param bool   $ucfirst Uppercase the very first character as well
 *                        (optional, defaults to false)
 * @return String containing the converted input string
 */
function strtocamelcase($string, $ucfirst = false) {
    $string = mb_strtolower($string);
    $chunks = preg_split('/\W+/', $string);
    $chunks = array_map('ucfirst', $chunks);

    if (!$ucfirst && count($chunks) > 0) {
        $chunks[0] = mb_strtolower($chunks[0]);
    }

    return implode($chunks);
}

/**
 * Converts a string to snake_case.
 *
 * @param String $string  The string that should be converted
 * @return String containing the converted input string
 */
function strtosnakecase($string) {
    $string = preg_replace('/\W+/', '_', $string);
    $string = preg_replace('/(?<!^)[A-Z]/', '_$0', $string);
    $string = mb_strtolower($string);
    return $string;
}

/**
 * fetch number of rows for a table
 * for innodb this is not exact, but much faster than count(*)
 *
 * @param string $table  name of database table
 * @return int number of rows
 */
function count_table_rows($table) {
    $stat = DbManager::get()->fetchOne("SHOW TABLE STATUS LIKE ?", [$table]);
    return (int)$stat['Rows'];
}

/**
 * get the file path relative to the STUDIP_BASE_PATH
 *
 * @param string path of the file
 * @return string relative path of the file
 */
function studip_relative_path($filepath)
{
    return str_replace($GLOBALS['STUDIP_BASE_PATH'] . '/', '', $filepath);
}


/**
 * converts a given array to a csv format
 *
 * @param array $data the data to convert, each row should be an array
 * @param string $filename full path to a file to write to, if omitted the csv content is returned
 * @param array $caption assoc array with captions, is written to the first line, $data is filtered by keys
 * @param string $delimiter sets the field delimiter (one character only)
 * @param string $enclosure sets the field enclosure (one character only)
 * @param string $eol sets the end of line format
 * @return mixed if $filename is given the number of written bytes, else the csv content as string
 */
function array_to_csv($data, $filename = null, $caption = null, $delimiter = ';' , $enclosure = '"', $eol = "\r\n", $add_bom = true )
{
    $fp = fopen('php://temp', 'r+');
    $fp2 = fopen('php://temp', 'r+');
    if ($add_bom) {
        fwrite($fp2, "\xEF\xBB\xBF");
    }
    if (is_array($caption)) {
        fputcsv($fp, array_values($caption), $delimiter, $enclosure);
        rewind($fp);
        $csv = stream_get_contents($fp);
        if ($eol != PHP_EOL) {
            $csv = trim($csv);
            $csv .= $eol;
        }
        fwrite($fp2, $csv);
        ftruncate($fp, 0);
        rewind($fp);
    }
    foreach ($data as $row) {
        if (is_array($caption)) {
            $fields = [];
            foreach(array_keys($caption) as $fieldname) {
                $fields[] = $row[$fieldname];
            }
        } else {
            $fields = $row;
        }
        fputcsv($fp, $fields, $delimiter, $enclosure);
        rewind($fp);
        $csv = stream_get_contents($fp);
        if ($eol != PHP_EOL) {
            $csv = trim($csv);
            $csv .= $eol;
        }
        fwrite($fp2, $csv);
        ftruncate($fp, 0);
        rewind($fp);
    }
    fclose($fp);
    rewind($fp2);
    if ($filename === null) {
        return stream_get_contents($fp2);
    } else {
        return file_put_contents($filename, $fp2);
    }
}


/**
* Delete a file, or a folder and its contents
*
* @author      Aidan Lister <aidan@php.net>
* @version     1.0
* @param       string   $dirname    The directory to delete
* @return      bool     Returns true on success, false on failure
*/
function rmdirr($dirname){
    // Simple delete for a file
    if (is_file($dirname)) {
        return @unlink($dirname);
    } else if (!is_dir($dirname)) {
        return false;
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== ($entry = $dir->read())) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep delete directories
        if (is_dir("$dirname/$entry") && !is_link("$dirname/$entry")) {
            rmdirr("$dirname/$entry");
        } else {
            @unlink("$dirname/$entry");
        }
    }
    // Clean up
    $dir->close();
    return @rmdir($dirname);
}


/**
 * Determines an appropriate MIME type for a file based on the
 * extension of the file name.
 *
 * @param string $filename      file name to check
 */
function get_mime_type($filename)
{
    static $mime_types = [
        // archive types
        'gz'   => 'application/x-gzip',
        'tgz'  => 'application/x-gzip',
        'bz2'  => 'application/x-bzip2',
        'zip'  => 'application/zip',
        // document types
        'txt'  => 'text/plain',
        'css'  => 'text/css',
        'csv'  => 'text/csv',
        'rtf'  => 'application/rtf',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'xls'  => 'application/ms-excel',
        'ppt'  => 'application/ms-powerpoint',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'swf'  => 'application/x-shockwave-flash',
        'odp'  => 'application/vnd.oasis.opendocument.presentation',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        // image types
        'gif'  => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'png'  => 'image/png',
        'bmp'  => 'image/x-ms-bmp',
        // audio types
        'mp3'  => 'audio/mp3',
        'oga'  => 'audio/ogg',
        'wav'  => 'audio/wave',
        'ra'   => 'application/x-pn-realaudio',
        'ram'  => 'application/x-pn-realaudio',
        // video types
        'mpeg' => 'video/mpeg',
        'mpg'  => 'video/mpeg',
        'mpe'  => 'video/mpeg',
        'qt'   => 'video/quicktime',
        'mov'  => 'video/quicktime',
        'avi'  => 'video/x-msvideo',
        'flv'  => 'video/x-flv',
        'ogg'  => 'application/ogg',
        'ogv'  => 'video/ogg',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (isset($mime_types[$extension])) {
        return $mime_types[$extension];
    } else {
        return 'application/octet-stream';
    }
}


function readfile_chunked($filename, $start = null, $end = null) {
    if (isset($start) && $start < $end) {
        $chunksize = 1024 * 1024; // how many bytes per chunk
        $bytes = 0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        fseek($handle, $start);
        while (!feof($handle) && ($p = ftell($handle)) <= $end) {
            if ($p + $chunksize > $end) {
                $chunksize = $end - $p + 1;
            }
            $buffer = fread($handle, $chunksize);
            $bytes += strlen($buffer);
            echo $buffer;
        }
        fclose($handle);
        return $bytes; // return num. bytes delivered like readfile() does.
    } else {
        return readfile($filename);
    }
}
