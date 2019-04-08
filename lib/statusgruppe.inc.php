<?php
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
function GetAllStatusgruppen($parent, $check_user = null, $exclude = false) {
    $query = "SELECT * FROM statusgruppen WHERE range_id = ? ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent]);
    $groups = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$groups) {
        return false;
    }

    $query = "SELECT visible FROM statusgruppe_user WHERE user_id = ? AND statusgruppe_id = ?";
    $presence = DBManager::get()->prepare($query);

    $childs = [];
    foreach ($groups as $group) {
        $user_there = $visible = $user_in_child = false;

        $kids = GetAllStatusgruppen($group['statusgruppe_id'], $check_user, $exclude);

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
            $childs[$group['statusgruppe_id']] = [
                'role'          => Statusgruppen::build($group),
                'visible'       => $visible,
                'user_there'    => $user_there,
                'user_in_child' => $user_in_child,
                'child'         => $kids
            ];
        }
    }

    return $childs ?: false;
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
 * @param $user_or_id
 * @param $default_entries
 * @param int $level
 * @param string $pred
 * @return array
 */
function get_role_data_recursive($roles, $user_or_id, $default_entries, $level = 0, $pred = '') {
    if (!is_array($roles)) {
        return '';
    }

    $user = $user_or_id instanceof User
          ? $user_or_id
          : User::find($user_or_id);

    $out = '';

    foreach ($roles as $role_id => $role) {
        $role['name'] = $role['role']->getGenderedName($user);

        if ($pred != '') {
            $new_pred = "{$pred} > {$role['name']}";
        } else {
            $new_pred = $role['name'];
        }

        if ($role['user_there'] && $role['visible']) {
            $out .= '<tr><td>'
                 . Assets::img('forumgrau2.png')
                 . '</td><td colspan="2"><b>'. htmlReady($new_pred) .'</b></td></tr>';

            $entries = DataFieldEntry::getDataFieldEntries([$user->id, $role_id]);
            foreach ($entries as $id => $entry) {
                if ($entry->getValue() == 'default_value') {
                    $value = $default_entries[$id]->getDisplayValue();
                    $default = true;
                } else {
                    $value = $entry->getDisplayValue();
                    $default = false;
                }

                $name = $entry->getName();

                if ($entry->isVisible()) {
                    if (trim($value) && !$default) {
                        $out .= '<tr><td></td><td>'. htmlReady($name) .':</td><td>'.trim($value);
                        $out .= '</td></tr>';
                    }
                }
            }
        }

        if ($role['child'] && $role['user_in_child']) {
            $out .= get_role_data_recursive($role['child'], $user, $default_entries, $level + 1, $new_pred);
        }
    }

    return $out;
}

/**
 * @deprecated
 * @param $roles
 * @param int $level
 * @param bool $parent_name
 * @return array
 */
function getFlattenedRoles($roles, $level = 0, $parent_name = false) {
    if (!is_array($roles)) {
        return [];
    }

    $ret = [];
    foreach ($roles as $id => $role) {
        if (!isset($role['name'])) {
            $role['name'] = $role['role']->getName();
        }
        $spaces = '';
        for ($i = 0; $i < $level; $i++) {
            $spaces .= '&nbsp;&nbsp;';
        }

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
