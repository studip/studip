<?php
/**
 * SemesterHoliday.class.php
 * model class for table semester_holiday
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string holiday_id database column
 * @property string id alias column for holiday_id
 * @property string semester_id database column
 * @property string name database column
 * @property string description database column
 * @property string beginn database column
 * @property string ende database column
 */

class SemesterHoliday extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param Array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'semester_holiday';
        $config['additional_fields']['current'] = true;
        parent::configure($config);
    }

    /**
     * cache
     */
    private static $holiday_cache;

    /**
     * returns SemesterHoliday object for given id or null
     * @param string $id
     * @return NULL|SemesterHoliday
     */
    public static function find($id)
    {
        $holiday_cache = self::getAll();
        return $holiday_cache[$id] ?: null;
    }

     /**
     * returns all SemesterHoliday between given timestamps (starting AND ending within given timestamps)
     * @param integer $timestamp_start
     * @param integer $timestamp_end
     * @return array of SemesterHoliday
     */
    public static function findByTimestampRange($timestamp_start, $timestamp_end)
    {
        $ret = [];
        if ($timestamp_start < $timestamp_end) {
            foreach(self::getAll() as $holiday) {
               if ($holiday->beginn >= $timestamp_start && $holiday->ende <= $timestamp_end) {
                    $ret[] = $holiday;
                }
            }
        }
        return $ret;
    }

    /**
     * returns all SemesterHolidays for given semester
     *
     * @param mixed semester object or id as string or assoc array
     * @return array of SemesterHoliday
     */
    public static function findBySemester($semester)
    {
        $semester = Semester::toObject($semester);
        return self::findByTimestampRange($semester->beginn, $semester->ende);
    }

    /**
     * returns array of all existing SemesterHoliday objects
     * orderd by begin
     * @param boolean $force_reload
     * @return array
     */
    public static function getAll($force_reload = false)
    {
        if (!is_array(self::$holiday_cache) || $force_reload) {
            self::$holiday_cache = [];
            foreach(self::findBySql('1 ORDER BY beginn') as $holiday){
                self::$holiday_cache[$holiday->getId()] = $holiday;
            }
        }
        return self::$holiday_cache;
    }

    /**
     * Returns whether we currently have this holidays (yay).
     *
     * @return bool
     */
    public function getcurrent()
    {
        return $this->beginn < time() && $this->ende > time();
    }

    /**
     * Returns if a given date is a holiday.
     *
     * @param int  $time                Timestamp to check
     * @param bool $check_vacation_only Defines whether to check only vacation
     *                                  times or against all holidays
     * @return mixed false if no holiday was found, an array with the name and
     *               the "col" value of the holiday otherwise
     */
    public static function isHoliday($time, $check_vacation_only = true)
    {
        // Check all defined vaciation times
        foreach (SemesterHoliday::getAll() as $val) {
            if ($val->beginn <= $time && $val->ende >= $time) {
                return [
                    'name' => $val->name,
                    'col' => 3,
                ];
            }
        }

        // Check all other holidays
        if (!$check_vacation_only) {
            return holiday($time);
            $holiday_entry = holiday($time);
        }

        // Nothing found
        return false;
    }

}
