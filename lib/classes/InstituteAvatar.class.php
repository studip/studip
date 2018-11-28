<?php
# Lifter010: TODO

/*
 * Copyright (C) 2009 - Marcus Lunzenauer (mlunzena@uos)
 * André Noack <noack@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * This class represents the avatar of a institute.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright (c) Authors
 * @since     1.10
 */
class InstituteAvatar extends CourseAvatar
{

    /**
     * Returns an avatar object of the appropriate class.
     *
     * @param  string  the course's id
     *
     * @return mixed   the course's avatar.
     */
    static function getAvatar($id)
    {
        return new InstituteAvatar($id);
    }

    /**
     * Returns an avatar object for "nobody".
     *
     * @return mixed   the course's avatar.
     */
    static function getNobody()
    {
        return new InstituteAvatar('nobody');
    }

    /**
     * Returns the URL to the institute' avatars.
     *
     * @return string     the URL to the avatars
     */
    function getAvatarDirectoryUrl()
    {
        return $GLOBALS['DYNAMIC_CONTENT_URL'] . "/institute";
    }


    /**
     * Returns the file system path to the institute' avatars
     *
     * @return string      the file system path to the avatars
     */
    function getAvatarDirectoryPath()
    {
        return $GLOBALS['DYNAMIC_CONTENT_PATH'] . "/institute";
    }

    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    function getDefaultTitle()
    {
        $institute = Institute::find($this->user_id);
        return $institute
               ? $institute->name
               : Avatar::NOBODY;
    }
    
    /**
     * Return if avatar is visible to the current user.
     * @return boolean: true if visible
     */
    protected function checkAvatarVisibility() {
        //no special conditions for visibility of course-avatars yet
        return true;
    }
}
