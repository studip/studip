<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginAbstract.class.php
//
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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
*
*
*
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
**/
class StudipLitSearchPluginAbstract {

    var $error_msg = [];
    var $description;
    var $search_result = [];
    var $class_name;
    var $sess_var_name;

    function __construct(){
        global $sess;
        $this->class_name = mb_strtolower(get_class($this));
        $this->sess_var_name = "_search_result_" . $this->class_name;
        $this->search_result =& $_SESSION[$this->sess_var_name];
    }

    function doSearch($search_values){
        return false;
    }

    function parseSearchValues(){
        return false;
    }

    function getSearchFields(){
        return false;
    }

    function getSearchResult($num_hit){
        return false;
    }

    function doResetSearch(){
        $this->search_result = [];
    }

    function getNumHits(){
        return (is_array($this->search_result)) ? count($this->search_result) : false;
    }

    function getError($format = "clear"){
        if ($format == "clear"){
            return $this->error_msg;
        } else {
            $ret = '';
            for ($i = 0; $i < count($this->error_msg); ++$i){
                $ret .= $this->error_msg[$i]['type'] . "§" . htmlReady($this->error_msg[$i]['msg']) . "§";
            }
            return $ret;
        }
    }

    function getNumError(){
        return count($this->error_msg);
    }

    function addError($type, $msg){
        $this->error_msg[] = ['type' => $type, 'msg' => $msg];
        return true;
    }

    function getPluginName(){
        global $_lit_search_plugins;
        $ret = false;
        for ($i = 0; $i < count($_lit_search_plugins); ++$i){
            if (mb_substr(strtolower($this->class_name),21) == mb_strtolower($_lit_search_plugins[$i]['name'])){
                $ret = $_lit_search_plugins[$i]['name'];
                break;
            }
        }
        return $ret;
    }
}
?>
