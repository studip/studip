<?
# Lifter001: DONE
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
* helper functions for handling statusgruppen
*
* helper functions for handling statusgruppen
*
* @author               Ralf Stockmann <rstockm@gwdg.de>
* @access               public
* @package          studip_core
* @modulegroup  library
* @module               statusgruppe.inc.php
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// statusgruppe.inc.php
// Copyright (c) 2002 Ralf Stockmann <rstockm@gwdg.de>
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

/**
 * Returns all statusgruppen for the given range.
 *
 * If there is no statusgruppe for the given range, it returns FALSE.
 * @deprecated
 * @param    string  $range_id
 * @param    string  $user_id
 * @return   array   (structure statusgruppe_id => name)
 */
function GetAllStatusgruppen($parent, $check_user = null, $exclude = false)
{
    $query = "SELECT * FROM statusgruppen WHERE range_id = ? ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($parent));
    $groups = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (empty($groups)) {
        return false;
    }

    $query = "SELECT visible FROM statusgruppe_user WHERE user_id = ? AND statusgruppe_id = ?";
    $presence = DBManager::get()->prepare($query);

    $childs = array();
    foreach ($groups as $group) {
        $user_there = $visible = $user_in_child = false;

        $kids = getAllStatusgruppen($group['statusgruppe_id'], $check_user, $exclude);

        if ($check_user) {
            $presence->execute(array($check_user, $group['statusgruppe_id']));
            $present = $presence->fetchColumn();
            $presence->closeCursor();

            if ($user_there = ($present !== false)) {
                $visible = $present;
            }

            if (is_array($kids)) {
                foreach ($kids as $kid) {
                    if ($kid['user_there'] || $kid['user_in_child']) {
                        $user_in_child = true;
                    }
                }
            }
        }

        if (!$check_user || !$exclude || $user_in_child || $user_there) {
            $childs[$group['statusgruppe_id']] = array(
                'role'          => Statusgruppen::build($group),
                'visible'       => $visible,
                'user_there'    => $user_there,
                'user_in_child' => $user_in_child,
                'child'         => $kids
            );
        }
    }

    return is_array($childs) ? $childs : false;
}


/**
 * @deprecated
 * @param $roles
 * @param int $level
 * @param string $pred
 * @param bool $all
 * @return array|null
 */
function GetRoleNames($roles, $level = 0, $pred = '', $all = false) {
    $out = array();

    if (is_array($roles))
    foreach ($roles as $role_id => $role) {
        if (!$role['name']) $role['name'] = $role['role']->getName();

        if ($pred != '') {
            $new_pred = $pred.' > '.$role['name'];
        } else {
            $new_pred = $role['name'];
        }

        if ($role['user_there'] || $all) {
            $out[$role_id] = $new_pred;
        }

        if ($role['child']) {
            $out = array_merge((array)$out, (array)GetRoleNames($role['child'], $level+1, $new_pred, $all));
        }
    }

    return (sizeof($out) > 0 ? $out : null);
}

/**
 * @deprecated
 * @param $roles
 * @param $user_id
 * @param $default_entries
 * @param null $filter
 * @param int $level
 * @param string $pred
 * @return array
 */
function get_role_data_recursive($roles, $user_id, &$default_entries, $filter = null, $level = 0, $pred = '') {
    global $auth, $user, $has_denoted_fields;

    $out = '';
    $out_table = array();

    if (is_array($roles))
    foreach ($roles as $role_id => $role) {

        $the_user = User::find($user_id);

        $role['name'] = $role['role']->getGenderedName($the_user);

        $out_zw = '';

        if ($pred != '') {
            $new_pred = $pred.' > '.$role['name'];
        } else {
            $new_pred = $role['name'];
        }

      $entries = DataFieldEntry::getDataFieldEntries(array($user_id, $role_id));

        if ($role['user_there']) {
            $out_zw .= '<tr><td>'
                    .  Assets::img('forumgrau2.png')
                    .  '</td><td colspan="2"><b>'. htmlReady($new_pred) .'</b></td></tr>';
            $zw = '<td %class%></td><td %class%><font size="-1">'. htmlReady($new_pred) .'</font></td>';
        }

        $zw2 = '';
        $has_value = false;

        if (is_array($entries))
        foreach ($entries as $id => $entry) {
            $default = false;
            if ($filter == null || in_array($id, $filter) === TRUE) {
                if ($entry->getValue() == 'default_value') {
                    $value = $default_entries[$id]->getDisplayValue();
                    $default = true;
                } else {
                    $value = $entry->getDisplayValue();
                }

                $name = $entry->getName();
                if ($role['user_there']) {
                    $view = $entry->isVisible();
                    $show_star = false;
                    if (!$view && ($user_id == $user->id)) {
                        $view = true;
                        $show_star = true;
                        $has_denoted_fields = true;
                    }

                    if ($view) { // Sichtbarkeitsberechtigung
                        $zw2 .= '<td %class%><font size="-1">'. trim($value);
                        if ($show_star) $zw2 .= ' *';
                        $zw2 .= '</font></td>';

                        if (trim($value)) {
                            $has_value = true;
                            if (!$default) {
                                $out_zw .= '<tr><td></td><td>'. htmlReady($name) .':&nbsp;&nbsp;</td><td>'.trim($value);
                                if ($show_star) $out_zw .= ' *';
                                $out_zw .= '</td></tr>';
                            }
                        }
                    }   // Ende Sichtbarkeitsberechtigung

                }
            }

        }

        if ($role['user_there'] && $role['visible']) {
            $out_table[] = $zw.$zw2;
            $out .= $out_zw;
        }

        if ($role['child']) {
            $back = get_role_data_recursive($role['child'], $user_id, $default_entries, $filter, $level+1, $new_pred);
            $out .= $back['standard'];
            $out_table = array_merge((array)$out_table, (array)$back['table']);
        }
    }

    return array('standard' => $out, 'table' => $out_table);
}

/**
 * @deprecated
 * @param $roles
 * @param int $level
 * @param bool $parent_name
 * @return array
 */
function getFlattenedRoles($roles, $level = 0, $parent_name = false) {
    if (!is_array($roles)) return array();

    $ret = array();

    foreach ($roles as $id => $role) {
        if (!isset($role['name'])) $role['name'] = $role['role']->getName();
        $spaces = '';
        for ($i = 0; $i < $level; $i++) $spaces .= '&nbsp;&nbsp;';

        // generate an indented version of the role-name
        $role['name'] = $spaces . $role['name'];

        // generate a name with all parent-roles in the name
        if ($parent_name) {
            $role['name_long'] = $parent_name . ' > ' . $role['role']->getName();
        } else {
            $role['name_long'] = $role['role']->getName();
        }

        $ret[$id] = $role;

        if ($role['child']) {
            $ret = array_merge($ret, getFlattenedRoles($role['child'], $level + 1, $role['name_long']));
        }

    }

    return $ret;
}
