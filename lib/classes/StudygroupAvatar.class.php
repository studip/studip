<?php
# Lifter010: TODO

/*
 * Copyright (C) 2009 - Marcus Lunzenauer (mlunzena@uos)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * This class represents the avatar of a course.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marcus Lunzenauer (mlunzena@uos), Till Glöggler (tgloeggl@uos)
 * @copyright (c) Authors
 * @since     1.10
 */
class StudygroupAvatar extends CourseAvatar
{
    /**
     * Returns an avatar object of the appropriate class.
     *
     * @param  string  the studygroup's id
     *
     * @return mixed   the studygroup's avatar.
     */
    static function getAvatar($course_id)
    {
        return new StudygroupAvatar($course_id);
    }


    /**
     * Returns an avatar object for "nobody".
     *
     * @return mixed   the studygroup's avatar.
     */
    static function getNobody()
    {
        return new StudygroupAvatar('studygroup');
    }

}
