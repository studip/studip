<?php
/**
 * SemesterCourse.class.php
 * Contains the SemesterCourse model.
 *
 * This class represents entries in the mapping table
 * seminare_semester.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string semester_id The ID of a semester.
 * @property string course_id The ID of a course.
 * @property string mkdate The database entry's creation date.
 * @property string chdate The database entry's last modification date.
 *
 * The combination of semester_id and course_id form the primary key.
 */
class SemesterCourse extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'semester_courses';

        $config['belongs_to']['semester'] = [
            'class_name' => Semester::class
        ];

        parent::configure($config);
    }
}
