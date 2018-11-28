<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* function to delete old files in the export tmp-directory.
* 
* This file checks the tmp-directory for old files an deletes them.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @package      Export
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// oscar.inc.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
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

function export_tmp_gc(){
    $tmp_export = $GLOBALS['TMP_PATH']. '/export/';
    if(!is_dir($tmp_export)){
        if(!mkdir($tmp_export)){
            trigger_error("tmp directory for export ($tmp_export) could not be created",E_USER_WARNING);
            return false;
        } else {
            chmod($tmp_export, 0700);
        }
    }
    if (mt_rand() % 100 < 10){
        $dir = @dir($tmp_export);
        if ($dir){
            while (false !== ($entry = $dir->read())) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $file = $tmp_export . '/' . $entry;
                $now = time();
                if (@is_file($file) && (($now - filemtime($file)) > 86400)){
                    @unlink($file);
                }
            }
            $dir->close();
        }
    }
}
?>
