<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitImportPluginStudipLitList.class.php
// 
// 
// Copyright (c) 2006 Jan Kulmann <jankul@zmml.uni-bremen.de>
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

require_once 'StudipLitImportPluginAbstract.class.php';

/**
*
*
* 
*
* @access   public  
* @author   Jan Kulmann <jankul@zmml.uni-bremen.de> 
* @package  
**/
class StudipLitImportPluginStudipLitList extends StudipLitImportPluginAbstract {
    
    function __construct(){
        // immer erst den parent-contructor aufrufen!
        parent::__construct();
    }

    function parse($data){
        return $data;
    }
    
    function import($data) {
        global $auth, $_msg, $_lit_search_plugins;
            $msg = &$_msg;
            if ($data) {

            $fields_arr = [];
            $lines = explode("\n",$data);
            $count = 0;
            foreach ($lines as $line) {
                if (mb_strlen($line)>0) {
                    $count++;
                    if ($count<3) continue;
                    $parts = explode("\t",$line);

                                $fields = [];
                                $fields["catalog_id"] = "new_entry";
                                $fields["user_id"] = $auth->auth["uid"];

                    $fields["dc_type"]          = $parts[0];
                    $fields["dc_title"]         = $parts[1];
                    $fields["dc_creator"]       = $parts[2];
                    $fields["dc_date"]          = $parts[3]."-01-01";
                    $fields["dc_contributor"]   = $parts[4];
                    $fields["dc_publisher"]     = $parts[5];
                    $fields["dc_identifier"]    = $parts[6];                
                    $fields["dc_source"]        = $parts[7];                
                    $fields["dc_subject"]       = $parts[8];
                    $fields["dc_description"]   = $parts[9];
                    $fields["accession_number"] = $parts[10];

                    $fields["lit_plugin"]       = "Studip";
                    foreach ($_lit_search_plugins as $p) {
                        if ($p["link"]!="") {
                            $l = str_replace("{accession_number}","",$p["link"]);
                            $pos = mb_strpos ($parts[12], $l);
                            if (!($pos === false)) {
                                $fields["lit_plugin"] = $p["name"];
                                break;
                            }
                        }
                    }

                                if (!trim($fields["dc_creator"])) $fields["dc_creator"] = "Unbekannt";
                                if (!trim($fields["dc_title"])) $fields["dc_title"] = "";

                    if ( $fields["dc_title"] != "") array_push($fields_arr, $fields);
                    
                }
            }

            return (count($fields_arr)>0 ? $fields_arr : FALSE);
            }

    }
}
?>
