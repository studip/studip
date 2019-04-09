<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * SeminarCategories.class.php
 *
 * encapsulates configuration settings for courses from config.inc.php
 * aka $SEM_CLASS, $SEM_TYPE, $UPLOAD_TYPES
 *
 * @author      André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
 * @access      public
 * @package     core
 */
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// SeminarCategories.class.php
//
// Copyright (C) 2008 André Noack <noack@data-quest>, data-quest GmbH <info@data-quest.de>
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

class SeminarCategories {

    private static $seminar_categories = [];

    private $sem_class_data = [];
    private $sem_type_data = [];

    /**
     * Enter description here...
     *
     * @param unknown_type $id
     * @return unknown
     */
    public static function Get($id){
        if(is_null(self::$seminar_categories[$id])){
            $cat = new SeminarCategories($id);
            if($cat->id !== false) {
                self::$seminar_categories[$id] = $cat;
            } else {
                self::$seminar_categories[$id] = false;
            }
        }
        return self::$seminar_categories[$id];
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public static function GetAll(){
        $ret = [];
        foreach($GLOBALS['SEM_CLASS'] as $id => $sem_class){
            $ret[] = self::get($id);
        }
        return $ret;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $id
     * @return unknown
     */
    public static function GetByTypeId($id){
        return self::Get($GLOBALS['SEM_TYPE'][$id]['class']);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $seminar_id
     * @return unknown
     */
    public static function GetBySeminarId($seminar_id){
        return self::GetByTypeId(Seminar::GetInstance($seminar_id)->status);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $sem_class_id
     */
    private function __construct($sem_class_id) {
        if(isset($GLOBALS['SEM_CLASS'][$sem_class_id])){
            $this->sem_class_data = $GLOBALS['SEM_CLASS'][$sem_class_id];
            $this->sem_class_data['id'] = $sem_class_id;
            foreach($GLOBALS['SEM_TYPE'] as $type_id => $type_data){
                if($type_data['class'] == $sem_class_id) {
                    $this->sem_type_data[$type_id] = $type_data['name'];
                }
            }
        }
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $type_id
     * @return unknown
     */
    public function getNameOfType($type_id){
        return isset($this->sem_type_data[$type_id]) ? $this->sem_type_data[$type_id] : '';
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getTypes(){
        return $this->sem_type_data;
    }

    public function __get($attribute){
        if(isset($this->sem_class_data[$attribute])){
            return $this->sem_class_data[$attribute];
        } else {
            return false;
        }
    }
}
?>