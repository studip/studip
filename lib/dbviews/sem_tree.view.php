<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sem_tree.view.class.php
// Database views used with "sem_tree"
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

$_views["SEM_TREE_GET_DATA_NO_ENTRIES"] = ["pk"=>"sem_tree_id","temp_table_type"=>"HEAP",
                            "query"=>"SELECT a.*, c.Name AS studip_object_name
                             FROM sem_tree a LEFT JOIN Institute c ON (a.studip_object_id = c.Institut_id)
                            ORDER BY priority"];
$_views["SEM_TREE_GET_ENTRIES"] = ["pk"=>"sem_tree_id","temp_table_type"=>"HEAP",
                            "query" => "SELECT st.sem_tree_id, count(b.Seminar_id) AS entries
                                FROM seminare b INNER JOIN seminar_sem_tree st ON(st.seminar_id = b.Seminar_id)
                                    WHERE b.status IN(&) AND §
                                    GROUP BY st.sem_tree_id ORDER BY NULL"];
$_views["SEM_TREE_GET_SEMIDS"] = ["pk"=>"seminar_id","temp_table_type"=>"HEAP",
                            "query" => "SELECT  b.seminar_id, " . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminar_sem_tree b INNER JOIN seminare c ON(b.seminar_id=c.Seminar_id AND c.status IN(&) AND §) WHERE sem_tree_id IN(&) §"];
$_views["SEM_TREE_GET_SEMIDS_ROOT"] = ["pk"=>"seminar_id","temp_table_type"=>"HEAP",
                            "query" => "SELECT  b.seminar_id, " . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminar_sem_tree b INNER JOIN seminare c ON(b.seminar_id=c.Seminar_id AND c.status IN(&) AND §) WHERE 1 §"];

$_views["SEM_TREE_GET_SEMDATA"] = ["query" => "SELECT a.seminar_id,IF(b.visible=0,CONCAT(Name, ' "._("(versteckt)")."'), Name) AS Name,username AS doz_uname, Nachname AS doz_name, " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end
                                        FROM seminar_sem_tree a INNER JOIN seminare b ON(a.seminar_id=b.Seminar_id AND b.status IN(&) AND §) LEFT JOIN seminar_user c ON (b.seminar_id=c.seminar_id AND c.status='dozent' )
                                        LEFT JOIN auth_user_md5 USING(user_id) WHERE sem_tree_id IN(&)  § ORDER BY sem_number DESC,Name ASC"];
$_views["SEM_TREE_GET_SEMDATA_ROOT"] = ["query" => "SELECT a.seminar_id,IF(b.visible=0,CONCAT(Name, ' "._("(versteckt)")."'), Name) AS Name,username AS doz_uname, Nachname AS doz_name, " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end
                                        FROM seminar_sem_tree a INNER JOIN seminare b ON(a.seminar_id=b.Seminar_id AND b.status IN(&) AND §) LEFT JOIN seminar_user c ON (b.seminar_id=c.seminar_id AND c.status='dozent' )
                                        LEFT JOIN auth_user_md5 USING(user_id) WHERE 1 § ORDER BY sem_number DESC,Name ASC"];

$_views["SEM_TREE_GET_NUM_SEM"] = ["query" => "SELECT count(DISTINCT(seminar_id)) , " . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminar_sem_tree
                                                    LEFT JOIN seminare USING (seminar_id) WHERE sem_tree_id IN(&) §"];

$_views["SEM_TREE_GET_LONELY_SEM_DATA"] = ["query" => "SELECT d.Seminar_id AS seminar_id,IF(d.visible=0,CONCAT(d.Name, ' "._("(versteckt)")."'), d.Name) AS Name, " . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end ,username AS doz_uname, Nachname AS doz_name
                                        FROM Institute a LEFT JOIN seminar_inst b USING(Institut_id)  INNER JOIN seminare d ON(b.seminar_id=d.Seminar_id AND d.status IN(&) AND §) LEFT JOIN seminar_user e ON (d.Seminar_id = e.seminar_id AND e.status='dozent')
                                        LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN seminar_sem_tree c ON (c.seminar_id=b.seminar_id)
                                        WHERE ISNULL(c.sem_tree_id)
                                        AND a.fakultaets_id LIKE ? AND NOT ISNULL(b.seminar_id)  GROUP BY d.Seminar_id § ORDER BY sem_number DESC,d.Name ASC"];
$_views["SEM_TREE_GET_NUM_LONELY_SEM"] = ["query" => "SELECT COUNT(DISTINCT(b.seminar_id)) AS num_sem , " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM Institute a LEFT JOIN seminar_inst b USING(Institut_id)
                                        INNER JOIN seminare d ON(b.seminar_id=d.Seminar_id AND d.status IN(&) AND §)  LEFT JOIN seminar_sem_tree c ON(c.seminar_id=d.Seminar_id)
                                        WHERE ISNULL(c.sem_tree_id)
                                        AND a.fakultaets_id LIKE ? AND NOT ISNULL(b.seminar_id) GROUP BY sem_number,sem_number_end § "];
$_views["SEM_TREE_GET_LONELY_FAK"] = ["query" => "SELECT Institut_id,a.Name FROM Institute a LEFT JOIN sem_tree b ON(studip_object_id=Institut_id) WHERE Institut_id = fakultaets_id AND ISNULL(studip_object_id) ORDER BY a.Name"];
$_views["SEM_TREE_UPD_PRIO"] = ["query" => "UPDATE sem_tree SET priority=§ WHERE sem_tree_id=?"];
$_views["SEM_TREE_INS_ITEM"] = ["query" => "INSERT INTO sem_tree (sem_tree_id,parent_id,name,priority,info,studip_object_id,type) VALUES (?,?,?,?,?,?,?)"];
$_views["SEM_TREE_UPD_ITEM"] = ["query" => "UPDATE sem_tree SET name=?, info=?, type=? WHERE sem_tree_id=?"];
$_views["SEM_TREE_DEL_ITEM"] = ["query" => "DELETE FROM sem_tree WHERE sem_tree_id IN (&)"];
$_views["SEM_TREE_MOVE_ITEM"] = ["query" => "UPDATE sem_tree SET parent_id=?, priority=§ WHERE sem_tree_id=?"];
$_views["SEM_TREE_SEARCH_SEM"] = ["query" => "SELECT b.seminar_id, " . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM sem_tree a LEFT JOIN seminar_sem_tree b USING(sem_tree_id)
                                                    LEFT JOIN seminare c USING(seminar_id)
                                                    WHERE § AND NOT ISNULL(b.seminar_id) AND a.name LIKE ? §"];
$_views["SEM_TREE_CHECK_PERM"] = ["query" => "SELECT inst_perms FROM user_inst WHERE inst_perms='admin' AND user_id=? AND Institut_id=?"];
$_views["SEM_TREE_SEARCH_ITEM"] = ["query" => "SELECT a.sem_tree_id,a.parent_id FROM sem_tree a LEFT JOIN sem_tree b ON(a.sem_tree_id=b.parent_id) WHERE a.name LIKE ? AND ISNULL(b.sem_tree_id) AND a.sem_tree_id NOT IN(&)"];

$_views["SEMINAR_SEM_TREE_DEL_RANGE"] = ["query" => "DELETE FROM seminar_sem_tree WHERE sem_tree_id IN (&)"];
$_views["SEMINAR_SEM_TREE_DEL_SEM_RANGE"] = ["query" => "DELETE FROM seminar_sem_tree WHERE sem_tree_id IN (&) AND seminar_id IN (&)"];
$_views["SEMINAR_SEM_TREE_DEL_SEMID_RANGE"] = ["query" => "DELETE FROM seminar_sem_tree WHERE seminar_id IN (&)"];
$_views["SEMINAR_SEM_TREE_INS_ITEM"] = ["query" => "INSERT IGNORE INTO seminar_sem_tree (seminar_id,sem_tree_id) VALUES (?,?)"];
$_views["SEMINAR_SEM_TREE_GET_IDS"] = ["query" => "SELECT DISTINCT a.sem_tree_id,b.parent_id FROM seminar_sem_tree a INNER JOIN sem_tree b USING(sem_tree_id) WHERE seminar_id=? ORDER BY parent_id,priority"];
$_views["SEMINAR_SEM_TREE_GET_EXP_IDS"] = ["query" => "SELECT DISTINCT b.sem_tree_id,c.parent_id FROM seminare a LEFT JOIN seminar_sem_tree b USING(seminar_id) LEFT JOIN sem_tree c USING(sem_tree_id) WHERE a.Institut_id=? AND b.sem_tree_id NOT IN(&)"];


$_views["SEMINAR_GET_SEMDATA"] = ["query" => "SELECT a.seminar_id,IF(a.visible=0,CONCAT(Name, ' "._("(versteckt)")."'), Name) AS Name,username AS doz_uname, Nachname AS doz_name, " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end
                                        FROM seminare a LEFT JOIN seminar_user b ON (a.seminar_id=b.seminar_id AND b.status='dozent' )
                                        LEFT JOIN auth_user_md5 USING(user_id) WHERE a.seminar_id IN (&) ORDER BY sem_number DESC,Name ASC"];
$_views["SEM_SEARCH_LECTURER"] = ["query" => "SELECT user_id FROM auth_user_md5 WHERE perms = 'dozent' AND (username LIKE ? OR Vorname LIKE ? OR Nachname LIKE ? OR CONCAT(Vorname, ' ', Nachname) LIKE ? OR CONCAT(Nachname, ', ', Vorname) LIKE ?)"];
$_views["SEM_SEARCH_LECTURER_ID"] = ["query" => "SELECT b.seminar_id, " . $_views['sem_number_sql'] . " AS sem_number, " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminar_user b
                                                    INNER JOIN seminare c USING (seminar_id) WHERE § AND b.status='dozent' AND b.user_id IN (&) §"];
$_views["SEM_SEARCH_SEM"] = ["query" =>"SELECT c.seminar_id, " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminare c WHERE § §"];
$_views["SEM_GET_FAKS"] = ["query" => "SELECT DISTINCT b.fakultaets_id,d.sem_tree_id FROM seminar_inst a LEFT JOIN  Institute b USING(Institut_id) LEFT JOIN sem_tree d ON (b.fakultaets_id=d.studip_object_id) WHERE a.seminar_id=?"];
$_views["SEM_GET_INST"] = ["query" => "SELECT institut_id FROM seminar_inst WHERE seminar_id=?"];
$_views["SEM_TREE_GET_FAK"] = ["query" => "SELECT DISTINCT sem_tree_id FROM Institute LEFT JOIN sem_tree ON (fakultaets_id=studip_object_id) WHERE Institut_id IN (&) AND NOT ISNULL(sem_tree_id)"];


$_views["SEM_INST_GET_SEM"] = ["query" => "SELECT c.Seminar_id, " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminar_inst a LEFT JOIN seminare c USING (seminar_id) WHERE a.Institut_id IN (&) AND c.Seminar_id IS NOT NULL
                                                § § "];

$_views["SEM_USER_GET_SEM"] = ["query" =>"SELECT b.Seminar_id,b.Name, " . $_views['sem_number_sql'] . " AS sem_number , " . $_views['sem_number_end_sql'] . " AS sem_number_end FROM seminar_user a LEFT JOIN seminare b USING(Seminar_id)
                                            WHERE b.visible=1 AND a.user_id=? AND a.status=?  §"];
?>
