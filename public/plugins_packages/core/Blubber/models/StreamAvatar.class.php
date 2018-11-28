<?php
/*
 *  Copyright (c) 2013  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * This class represents the avatar of a blubberstream.
 *
 */
class StreamAvatar extends Avatar
{

    /**
     * Returns an avatar object of the appropriate class.
     *
     * @param  string  the stream's id
     *
     * @return mixed   the streams's avatar.
     */
    static function getAvatar($id)
    {
        return new StreamAvatar($id);
    }

    /**
     * Returns an avatar object for "nobody".
     *
     * @return mixed   the streams's avatar.
     */
    static function getNobody()
    {
        return new StreamAvatar('nobody');
    }

    /**
     * Returns the URL to the courses' avatars.
     *
     * @return string     the URL to the avatars
     */
    function getAvatarDirectoryUrl()
    {
        return $GLOBALS['DYNAMIC_CONTENT_URL'] . "/blubberstream";
    }


    /**
     * Returns the file system path to the courses' avatars
     *
     * @return string      the file system path to the avatars
     */
    function getAvatarDirectoryPath()
    {
        return $GLOBALS['DYNAMIC_CONTENT_PATH'] . "/blubberstream";
    }

    /**
     * Returns the CSS class to use for this avatar image.
     *
     * @param string  one of the constants Avatar::(NORMAL|MEDIUM|SMALL)
     *
     * @return string CSS class to use for the avatar
     */
    protected function getCssClass($size) {
        return sprintf('stream-avatar-%s course-%s', $size, $this->user_id);
    }

    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    function getDefaultTitle()
    {
        require_once dirname(__file__).'/BlubberStream.class.php';
        return BlubberStream::find($this->user_id)->name;
    }
    
    /**
     * Return if avatar is visible to the current user.
     * @return boolean: true if visible
     */
    protected function checkAvatarVisibility() {
        //no special conditions for visibility of stream-avatars yet
        return true;
    }
}
