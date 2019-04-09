<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// liteartur.view.class.php
// Database views used with "Literatruverwaltung"
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
$_views['element_name_short_sql'] = " CONCAT(IF ( RIGHT( SUBSTRING_INDEX( TRIM(dc_creator),  ' ', 1  ) , 1  )
                                    =  ',', LEFT( SUBSTRING_INDEX( TRIM(dc_creator),  ' ', 1  ) , 
                                    LENGTH( SUBSTRING_INDEX( TRIM(dc_creator),  ' ', 1  )  )  - 1  ) , 
                                    SUBSTRING_INDEX( TRIM(dc_creator),  ',', 1  )  ) ,'(', YEAR( dc_date ),')-',
                                    dc_title) "; 
$_views["LIT_GET_ELEMENT"] = ["query" => "SELECT * FROM lit_catalog WHERE catalog_id=?"];
$_views["LIT_CHECK_ELEMENT"] = ["query" => "SELECT catalog_id FROM lit_catalog WHERE accession_number=? AND user_id='studip'"];
$_views["LIT_LIST_GET_ELEMENTS"] = ["query" => "SELECT list_element_id, b.* FROM lit_list_content LEFT JOIN lit_catalog b USING (catalog_id) WHERE list_id=? ORDER BY priority"];
$_views["LIT_GET_REFERENCE_COUNT"] = ["query" => "SELECT count(*) AS anzahl FROM lit_list_content WHERE catalog_id=?"];
$_views["LIT_GET_CATALOG_COUNT"] = ["query" => "SELECT count(*) AS anzahl FROM lit_catalog"];
$_views["LIT_SEARCH_CATALOG"] = ["query" => "SELECT catalog_id FROM lit_catalog WHERE § ORDER BY dc_date DESC"];
$_views["LIT_DEL_ELEMENT"] = ["query" => "DELETE FROM lit_catalog WHERE catalog_id=?"];
$_views["LIT_GET_CLIP_ELEMENTS"] = ["query" => "SELECT catalog_id, " . $_views['element_name_short_sql'] . " as short_name 
                                                    FROM  lit_catalog WHERE catalog_id IN(&) ORDER BY short_name"];
$_views["LIT_GET_LIST_BY_RANGE"] =
                                                    ["query" => "SELECT a.*," . $GLOBALS['_fullname_sql']['no_title_short'] .
                                                    " AS fullname,username FROM lit_list a 
                                                    LEFT JOIN auth_user_md5  USING(user_id) LEFT JOIN user_info ON ( auth_user_md5.user_id = user_info.user_id )  WHERE range_id=? 
                                                    ORDER BY priority"];
$_views["LIT_GET_LIST_COUNT_BY_RANGE"] = ["query" => "SELECT COUNT(IF(visibility=1,list_id,NULL)) AS visible_list, COUNT(IF(visibility=0,list_id,NULL)) AS invisible_list 
                                                    FROM lit_list WHERE range_id=? GROUP BY range_id
                                                    "];
$_views["LIT_GET_LIST"] = ["query" => "SELECT * FROM lit_list WHERE list_id=?"];

$_views["LIT_GET_LIST_CONTENT"] = ["query" => "SELECT a.*," . $_views['element_name_short_sql'] . " as short_name ,"
                                                    . $GLOBALS['_fullname_sql']['no_title_short'] . " AS fullname, username  FROM lit_list_content a 
                                                    LEFT JOIN lit_catalog USING(catalog_id) LEFT JOIN auth_user_md5 ON(auth_user_md5.user_id=a.user_id) 
                                                    LEFT JOIN user_info ON ( auth_user_md5.user_id = user_info.user_id ) 
                                                    WHERE list_id IN(&) ORDER BY list_id,priority"];
$_views["LIT_UPD_LIST_CONTENT"] = ["query" => "UPDATE lit_list_content SET list_id=?, catalog_id=?, user_id=?,note=?,priority=?, chdate=UNIX_TIMESTAMP() WHERE list_element_id=?"];
$_views["LIT_INS_LIST_CONTENT"] = ["query" => "INSERT INTO lit_list_content (list_id,catalog_id,user_id,note,priority,chdate,mkdate,list_element_id) VALUES (?,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),?)"];
$_views["LIT_UPD_LIST"] = ["query" => "UPDATE lit_list SET range_id=?, name=?, user_id=?,format=?,priority=?,visibility=§, chdate=UNIX_TIMESTAMP() WHERE list_id=?"];
$_views["LIT_INS_LIST"] = ["query" => "INSERT INTO lit_list (range_id,name,user_id,format,priority,visibility,chdate,mkdate,list_id) VALUES (?,?,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),?)"];
$_views["LIT_DEL_LIST"] = ["query" => "DELETE FROM lit_list WHERE list_id IN(&)"];
$_views["LIT_DEL_LIST_CONTENT_ALL"] = ["query" => "DELETE FROM lit_list_content WHERE list_id IN(&)"];
$_views["LIT_DEL_LIST_CONTENT"] = ["query" => "DELETE FROM lit_list_content WHERE list_element_id=?"];
$_views["LIT_INS_HELPER"] = ["pk" => "list_element_id", "query" => "
                                                        SELECT MD5(CONCAT(list_element_id,?)) AS list_element_id,? AS list_id,catalog_id,
                                                        user_id,mkdate,chdate,note,priority FROM lit_list_content  WHERE list_id=?"];
$_views["LIT_INS_LIST_CONTENT_COPY"] = ["query" => ["INSERT INTO lit_list_content (list_element_id,list_id,catalog_id,user_id,mkdate,chdate,note,priority)
                                                        SELECT * FROM {1}","view:LIT_INS_HELPER"]];
$_views["LIT_LIST_TOGGLE_VISIBILITY"] = ["query" => "UPDATE lit_list SET visibility=IF(visibility=0,1,0) WHERE list_id=?"];
$_views["LIT_LIST_TRIGGER_UPDATE"] = ["query" => "UPDATE lit_list SET chdate=UNIX_TIMESTAMP(),user_id=? WHERE list_id=?"];

$_views["LIT_GET_FAK_LIT_PLUGIN"] = ["query" => "SELECT f.fakultaets_id, f.lit_plugin_name FROM user_inst
                                                                INNER JOIN Institute i
                                                                USING ( institut_id )
                                                                INNER JOIN Institute f ON ( f.institut_id = i.fakultaets_id )
                                                                WHERE user_id = ? AND inst_perms IN ('admin','dozent','tutor') ORDER BY inst_perms ASC"];

?>
