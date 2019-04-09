<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// range_tree.view.class.php
// Database views used with "range_tree"
//
// Copyright (c) 2002 André Noack <noack@data-quest.de>
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

foreach (SemesterData::GetSemesterArray() as $key => $value){
    if (isset($value['beginn']) && $value['beginn'])
      $sem_start_times[] = $value['beginn'];
}
$_views['sem_number_sql'] = "INTERVAL(start_time," . join(",",$sem_start_times) .")";
$_views['sem_number_end_sql'] = "IF(duration_time=-1,-1,INTERVAL(start_time+duration_time," . join(",",$sem_start_times) ."))";

$_views["TREE_KIDS"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT item_id FROM range_tree WHERE parent_id=? ORDER BY priority"];
$_views["TREE_GET_DATA"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT a.*, b.Name AS studip_object_name, b.fakultaets_id FROM range_tree a
                                    LEFT JOIN Institute b ON (a.studip_object_id = b.Institut_id) ORDER BY priority"];
$_views["TREE_GET_SEM_ENTRIES"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT item_id,count(d.Seminar_id) AS entries FROM range_tree a
                                    INNER JOIN seminar_inst c ON (a.studip_object_id = c.institut_id)
                                     INNER JOIN seminare d ON(c.seminar_id=d.Seminar_id  §) § GROUP BY a.item_id"];

$_views["TREE_OBJECT_NAME"] = ["pk"=>"","temp_table_type"=>"HEAP",
                            "query"=>"SELECT Name FROM § WHERE § LIKE ? "];
$_views["TREE_OBJECT_DETAIL"] = ["pk"=>"","temp_table_type"=>"HEAP",
                            "query"=>"SELECT * FROM § WHERE § LIKE ? "];
$_views["TREE_OBJECT_CAT"] = ["pk"=>"kategorie_id","temp_table_type"=>"MyISAM",
                            "query"=>"SELECT * FROM kategorien WHERE range_id LIKE ? ORDER BY priority"];
$_views["TREE_INST_STATUS"] = ["pk"=>"","temp_table_type"=>"HEAP",
                            "query"=>"SELECT Institut_id FROM user_inst WHERE Institut_id IN(&) AND user_id=? AND inst_perms='admin'"];
$_views["TREE_FAK_STATUS"] = ["pk"=>"","temp_table_type"=>"HEAP",
                            "query"=>"SELECT b.fakultaets_id,a.Institut_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id = b.Institut_id AND b.Institut_id=b.fakultaets_id) WHERE a.Institut_id IN(&) AND NOT ISNULL(b.Institut_id) AND user_id=? AND inst_perms='admin'"];
$_views["TREE_ITEMS_OBJECT"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT item_id FROM range_tree WHERE studip_object_id LIKE ?"];

$_views["TREE_UPD_PRIO"] = ["query" => "UPDATE range_tree SET priority=§ WHERE item_id=?"];
$_views["TREE_INS_ITEM"] = ["query" => "INSERT INTO range_tree (item_id,parent_id,name,priority,studip_object,studip_object_id) VALUES (?,?,?,§,?,?)"];
$_views["TREE_UPD_ITEM"] = ["query" => "UPDATE range_tree SET name=?, studip_object=?, studip_object_id=? WHERE item_id=?"];
$_views["TREE_MOVE_ITEM"] = ["query" => "UPDATE range_tree SET parent_id=?, priority=§ WHERE item_id=?"];
$_views["TREE_DEL_ITEM"] = ["query" => "DELETE FROM range_tree WHERE item_id IN (&)"];

$_views["TREE_SEARCH_INST"] = ["query" => "SELECT Name,Institut_id FROM Institute WHERE fakultaets_id!=Institut_id AND Name LIKE '%§%'"];
$_views["TREE_SEARCH_FAK"] = ["query" => "SELECT Name,Institut_id AS Fakultaets_id FROM Institute WHERE fakultaets_id=Institut_id AND Name LIKE '%§%'"];
$_views["TREE_SEARCH_ITEM"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT a.item_id FROM range_tree a LEFT JOIN Institute b ON (a.studip_object_id = b.Institut_id) WHERE a.name LIKE ? OR b.Name LIKE ? "];
$_views["TREE_SEARCH_USER"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT rt.item_id FROM auth_user_md5 a LEFT JOIN user_inst b ON (a.user_id=b.user_id AND b.inst_perms!='user')
LEFT JOIN range_tree rt ON (rt.studip_object_id=b.Institut_id ) WHERE NOT ISNULL(rt.item_id) AND (CONCAT(a.username,' ',a.Vorname,' ',a.Nachname) LIKE ? OR CONCAT(a.Nachname, ', ', a.Vorname) LIKE ?)"];
$_views["TREE_SEARCH_SEM"] = ["pk"=>"item_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT rt.item_id FROM seminare a LEFT JOIN seminar_inst b USING (Seminar_id)LEFT JOIN range_tree rt ON (rt.studip_object_id=b.institut_id)
                            WHERE NOT ISNULL(rt.item_id) AND a.Name LIKE ?"];


$_views["CAT_UPD_PRIO"] = ["query" => "UPDATE kategorien SET priority=§,chdate=UNIX_TIMESTAMP() WHERE kategorie_id=?"];
$_views["CAT_UPD_CONTENT"] = ["query" => "UPDATE kategorien SET name=?, content=?, chdate=UNIX_TIMESTAMP() WHERE kategorie_id=?"];
$_views["CAT_INS_ALL"] = ["query" => "INSERT INTO kategorien (kategorie_id,range_id,name,content,priority,mkdate,chdate)VALUES (?,?,?,?,§,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"];
$_views["CAT_DEL"] = ["query" => "DELETE FROM kategorien WHERE kategorie_id IN (&)"];
$_views["CAT_DEL_RANGE"] = ["query" => "DELETE FROM kategorien WHERE range_id IN (&)"];

$_views["STATUS_COUNT"] = ["query"=>"SELECT count(DISTINCT user_id) AS anzahl FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id=?"];
?>
