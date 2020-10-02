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
     * Return the index number of the passed semester in the array return by
     * SemesterData::getAllSemesterData()
     *
     * @param string $semester_id
     * @return int semester index
     * @deprecated use semester class instead
     *
     */
    public static function getSemesterIndexById($semester_id)
    {
        $index = false;

        foreach (Semester::findAllVisible() as $i => $sem) {
            if (@$sem['semester_id'] == $semester_id) {
                $index = $i;
            }
        }

        return $index;
    }

    /**
     * Returns array of all semesters
     *
     * @return array semester-list
     * @deprecated use semester class instead
     *
     */
    public static function getAllSemesterData()
    {
        $ret = [];
        foreach (Semester::getAll() as $semester) {
            if ($GLOBALS['perm']->have_perm('admin') || $semester->visible) {
                $ret[] = $semester->toArray();
            }
        }
        return $ret;
    }
}
