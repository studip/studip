<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* persons.inc.php
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       persons
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// persons.inc.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

require_once 'lib/user_visible.inc.php';

global $_fullname_sql;

// [tlx] We are not inside a class definition, so where does $this refer to?
$range_id = $this->config->range_id;

//$all_groups = $this->config->getValue("Main", "groups");
if (!$all_groups = get_all_statusgruppen($range_id)) {
    die($GLOBALS['EXTERN_ERROR_MESSAGE']);
} else {
    $all_groups = array_keys($all_groups);
}

if (Request::get('visible_groups')) {
    $group_ids = explode(',', Request::get('visible_groups'));
} else {
    $group_ids = $this->config->getValue('Main', 'groupsvisible');
}
if (!$group_ids) {
    die($GLOBALS['EXTERN_ERROR_MESSAGE']);
} else {
    $group_ids = array_intersect($all_groups, $group_ids);
}

if (!is_array($group_ids)) {
    die($GLOBALS['EXTERN_ERROR_MESSAGE']);
}

if (!$visible_groups = get_statusgruppen_by_id($range_id, $group_ids)) {
    die($GLOBALS['EXTERN_ERROR_MESSAGE']);
}

$aliases_groups = $this->config->getValue('Main', 'groupsalias');
$order          = $this->config->getValue('Main', 'order');
$sort           = $this->config->getValue('Main', 'sort');

$query_order = [];
foreach ($sort as $key => $position) {
    if ($position > 0) {
        $query_order[$position] = $this->data_fields[$key];
    }
}
if (count($query_order) > 0) {
    ksort($query_order, SORT_NUMERIC);
    $query_order = ' ORDER BY ' . implode(', ', $query_order);
} else {
    $query_order = '';
}

if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
    $nameformat = 'full_rev';
}

$grouping = $this->config->getValue('Main', 'grouping');
if (!$grouping) {
    if (Request::get('visible_groups')) {
        $groups_ids = [Request::get('visible_groups')];
    } else {
        $groups_ids = $this->config->getValue('Main', 'groupsvisible');
    }
    $ext_vis_query = get_ext_vis_query();

    $range_ids = [$range_id];
    if (Request::option('aggregation')) {
        $i = Institute::find($range_id);
        $children = $i->sub_institutes->pluck('institut_id');
        $range_ids = array_merge($range_ids, $children);
    }

    $query = "SELECT DISTINCT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,
                     Email, aum.user_id, username, aum.Nachname,
                     {$_fullname_sql[$nameformat]} AS fullname ";
    if ($query_order) {
        $query .= "FROM statusgruppe_user
                   LEFT JOIN auth_user_md5 AS aum USING(user_id)
                   LEFT JOIN user_info USING (user_id)
                   LEFT JOIN user_inst AS ui USING (user_id) 
                   WHERE statusgruppe_id IN (?) AND Institut_id IN (?)
                     AND {$ext_vis_query}
                   {$query_order}";
    } else {
        $query .= "FROM statusgruppen AS s
                   LEFT JOIN statusgruppe_user AS su USING (statusgruppe_id)
                   LEFT JOIN auth_user_md5 AS aum USING (user_id)
                   LEFT JOIN user_info USING (user_id)
                   LEFT JOIN user_inst AS ui USING (user_id) 
                   WHERE su.statusgruppe_id IN (?) AND Institut_id IN (?)
                     AND {$ext_vis_query}
                   ORDER BY s.position ASC, su.position ASC";
    }
    $statement = DBManager::get()->prepare($query);
    $statement->execute([
        $groups_ids ?: '',
        $range_ids
    ]);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Ensure the main loop will only get executed once since we already
    // have all the neccessary data
    $visible_groups = [''];
}

// generic data fields
$generic_datafields = $this->config->getValue("Main", "genericdatafields"); 
//  $datafields_obj = new DataFields();

$repeat_headrow = $this->config->getValue('Main', 'repeatheadrow');
$link_persondetails = $this->getModuleLink('Persondetails',
                                           $this->config->getValue('LinkIntern', 'config'),
                                           $this->config->getValue('LinkIntern', 'srilink'));
$data['data_fields'] = $this->data_fields;
$defaultadr = $this->config->getValue('Main', 'defaultadr');

$out = '';
$first_loop = TRUE;
foreach ($visible_groups as $group_id => $group) {
    if ($grouping) {
        if (!$query_order) {
            $query_order = ' ORDER BY su.position';
        }

        $ext_vis_query = get_ext_vis_query();
        $query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,
                         Email, aum.user_id, username, aum.Nachname,
                         {$_fullname_sql[$nameformat]} AS fullname 
                  FROM statusgruppe_user AS su
                  LEFT JOIN auth_user_md5 AS aum USING (user_id)
                  LEFT JOIN user_info USING (user_id)
                  LEFT JOIN user_inst AS ui USING (user_id)
                  WHERE su.statusgruppe_id = ? AND Institut_id = ?
                    AND {$ext_vis_query}
                  {$query_order}";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$group_id, $range_id]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $position = array_search($group_id, $all_groups);
        if ($aliases_groups[$group_id]) {
            $group = $aliases_groups[$group_id];
        }
    }

    if (count($rows) > 0) {

        if ($grouping && $repeat_headrow == 'beneath') {
            $out .= $this->elements['TableGroup']->toString(['content' => htmlReady($group)]);
        }

        if ($repeat_headrow || $first_loop) {
            $out .= $this->elements['TableHeadrow']->toString();
        }

        if ($grouping && $repeat_headrow != 'beneath') {
            $out .= $this->elements['TableGroup']->toString(['content' => htmlReady($group)]);
        }

        $range_ids = [$range_id];
        if (Request::option('aggregation')) {
            $i = Institute::find($range_id);
            $children = $i->sub_institutes->pluck('institut_id');
            $range_ids = array_merge($range_ids, $children);
        }

        foreach ($rows as $row) {
            if ($defaultadr) {
                $ext_vis_query = get_ext_vis_query();
                $query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
                                 inst_perms, Email, aum.user_id, username,
                                 {$_fullname_sql[$nameformat]} AS fullname,
                                 aum.Nachname
                          FROM auth_user_md5 AS aum
                          LEFT JOIN user_info USING (user_id)
                          LEFT JOIN user_inst AS ui USING (user_id)
                          WHERE aum.user_id = ? AND externdefault = 1
                            AND {$ext_vis_query}";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$row['user_id']]);
                $temp = $statement->fetch(PDO::FETCH_ASSOC);
                
                if ($temp) {
                    $row = $temp;
                } else {
                    // No default
                    $query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
                                     inst_perms,  Email, aum.user_id, username,
                                     {$_fullname_sql[$nameformat]} AS fullname,
                                     aum.Nachname
                              FROM auth_user_md5 AS aum
                              LEFT JOIN user_info USING (user_id)
                              LEFT JOIN user_inst AS ui USING (user_id)
                              WHERE aum.user_id = ? AND Institut_id IN (?)
                                AND {$ext_vis_query}";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$row['user_id'], $range_ids]);
                    $row = $statement->fetch(PDO::FETCH_ASSOC);
                }
            }

            $email = get_visible_email($row['user_id']);
            $data['content'] = [
                'Nachname'     => $this->elements['LinkIntern']->toString([
                                      'content'   => htmlReady($row['fullname']),
                                      'module'    => 'Persondetails',
                                      'link_args' => 'username=' . $row['username']
                                  ]),
                'Telefon'      => htmlReady($row['Telefon']),
                'sprechzeiten' => htmlReady($row['sprechzeiten']),
                'raum'         => htmlReady($row['raum']),
                'Email'        => $this->elements['Link']->toString([
                                      'content' => htmlReady($email),
                                      'link'    => 'mailto:' . htmlReady($email)
                                  ])
            ];

            // generic data fields
            if (is_array($generic_datafields)) {
                $localEntries = DataFieldEntry::getDataFieldEntries($row['user_id']);
                foreach ($generic_datafields as $id) {
                    $data['content'][$id] = is_object($localEntries[$id]) ? $localEntries[$id]->getDisplayValue() : '';
                }
            }
            $out .= $this->elements['TableRow']->toString($data);
        }
        $first_loop = FALSE;
    }
}

$this->elements['TableHeader']->printout(['content' => $out]);
