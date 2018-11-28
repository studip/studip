<?php
# Lifter010: TODO
/**
 * SemesterData.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Mark Sievers <msievers@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


class SemesterData
{
    /**
     * get an instance of this class (does not make any sense anymore,
     * all methods are static).
     *
     * @deprecated use semester class instead
     *
     * @param boolean $refresh_cache
     * @return object SemesterData
     */
    public static function getInstance($refresh_cache = false)
    {
        static $semester_object;

        if ($refresh_cache) {
            $semester_object = null;
        }
        if (is_object($semester_object)) {
            return $semester_object;
        } else {
            $semester_object = new SemesterData();
            return $semester_object;
        }
    }

    /**
     * Return a specially orderd array of all semesters
     *
     * @deprecated use semester class instead
     *
     * @return array [name => '...', past => true oder false]
     */
    public static function getSemesterArray()
    {
        static $all_semester;

        if (is_null($all_semester)) {
            $all_semester = SemesterData::getAllSemesterData();
            array_unshift($all_semester, 0);
            $all_semester[0] = [
                'name' => sprintf(_("vor dem %s"), $all_semester[1]['name']),
                'past' => true
            ];
        }

        return $all_semester;
    }

    /**
     * Return the index number of the passed semester in the array return by
     * SemesterData::getAllSemesterData()
     *
     * @deprecated use semester class instead
     *
     * @param  string $semester_id
     * @return int semester index
     */
    public static function getSemesterIndexById($semester_id)
    {
        $index = false;

        foreach(SemesterData::getAllSemesterData() as $i => $sem){
            if($sem['semester_id'] == $semester_id) {
                $index = $i + 1;
            }
        }

        return $index;
    }

    /**
     * Return the id of the semester with the passed index in the array
     * generated by SemesterData::getSemesterArray()
     *
     * @deprecated use semester class instead
     *
     * @param  int $semester_index
     * @return string  semester_id
     */
    public static function getSemesterIdByIndex($semester_index)
    {
        $old_style_semester = SemesterData::getSemesterArray();
        return isset($old_style_semester[$semester_index]['semester_id'])
            ? $old_style_semester[$semester_index]['semester_id']
            : null;
    }

    /**
     * Return the semester_id of the semester with the passed start date
     *
     * @deprecated use semester class instead
     *
     * @param  int $timestamp
     * @return string  semester_id
     */
    public static function getSemesterIdByDate($timestamp)
    {
        $one_semester = SemesterData::getSemesterDataByDate($timestamp);
        return isset($one_semester['semester_id'])
            ? $one_semester['semester_id']
            : null;
    }

    /**
     * Returns an html fragment with a semester select-box
     *
     * @deprecated use semester class instead
     *
     * @param array  $select_attributes
     * @param integer $default
     * @param string  $option_value
     * @param boolean $include_all
     */
    public static function getSemesterSelector($select_attributes = null, $default = 0, $option_value = 'semester_id', $include_all = true)
    {
        $semester = SemesterData::getSemesterArray();

        unset($semester[0]);

        if ($include_all) {
            $semester['all'] = [
                'name' => _("alle"),
                'semester_id' => 0
            ];
        }

        $semester = array_reverse($semester, true);

        if (!$select_attributes['name']) {
            $select_attributes['name'] = 'sem_select';
        }

        $out = chr(10) . '<select ';
        foreach ($select_attributes as $key => $value) {
            $out .= ' ' . $key .'="'.$value.'" ';
        }
        $out .= '>';

        foreach($semester as $sem_key => $one_sem) {
            $one_sem['key'] = $sem_key;
            $out .= "\n<option value=\"{$one_sem[$option_value]}\" "
                . ($one_sem[$option_value] == $default ? "selected" : "")
                . ">" . htmlReady($one_sem['name']) . "</option>";
        }
        $out .= chr(10) . '</select>';

        return $out;
    }

    /**
     * Returns array of all semesters
     *
     * @deprecated use semester class instead
     *
     * @return array semester-list
     */
    public static function getAllSemesterData()
    {
        $ret = array();
        foreach (Semester::getAll() as $semester) {
            $ret[] = $semester->toArray();
        }
        return $ret;
    }

    /**
     * Delete semester with the passed id
     *
     * @deprecated use semester class instead
     *
     * @param  string $semester_id
     * @return array deleted semester
     */
    public static function deleteSemester($semester_id)
    {
        $ret = Semester::find($semester_id)->delete();
        Semester::getAll(true);
        return $ret;
    }

    /**
     * Get semester array for the passed id or false, if none is found
     *
     * @deprecated use semester class instead
     *
     * @param  string $semester_id
     * @return array found semester as array or false
     */
    public static function getSemesterData($semester_id)
    {
        $ret = Semester::find($semester_id);
        return $ret ? $ret->toArray() : false;
    }

    /**
     * Get semester array for the passed timestamp or false, if none is found
     *
     * @deprecated use semester class instead
     *
     * @param  int $timestamp
     * @return array found semester as array or false
     */
    public static function getSemesterDataByDate($timestamp)
    {
        $ret = Semester::findByTimestamp($timestamp);
        return $ret ? $ret->toArray() : false;
    }

    /**
     * Get semester array for the current semester or false, if none is found
     *
     * @deprecated use semester class instead
     *
     * @return array found semester as array or false
     */
    public static function getCurrentSemesterData()
    {
        $ret = Semester::findCurrent();
        return $ret ? $ret->toArray() : false;
    }

    /**
     * Get semester array for the next semester or false, if none is found
     *
     * @deprecated use semester class instead
     *
     * @param  boolean $timestamp
     * @return array found semester as array or false
     */
    public static function getNextSemesterData($timestamp = false)
    {
        $ret = Semester::findNext($timestamp);
        return $ret ? $ret->toArray() : false;
    }
}
