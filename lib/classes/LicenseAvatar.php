<?php

/*
 * Copyright (C) 2020 - Rasmus Fuhse
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * This class represents the image-picture of a license.
 */
class LicenseAvatar extends Avatar
{

    /**
     * Returns an avatar object of the appropriate class.
     *
     * @param  string  the course's id
     *
     * @return mixed   the course's avatar.
     */
    public static function getAvatar($id)
    {
        return new static($id);
    }

    /**
     * Returns an avatar object for "nobody".
     *
     * @return mixed   the course's avatar.
     */
    public static function getNobody()
    {
        return new static('nobody');
    }

    /**
     * Returns the URL to the courses' avatars.
     *
     * @return string     the URL to the avatars
     */
    public function getAvatarDirectoryUrl()
    {
        return $GLOBALS['DYNAMIC_CONTENT_URL'] . "/licenses";
    }


    /**
     * Returns the file system path to the courses' avatars
     *
     * @return string      the file system path to the avatars
     */
    public function getAvatarDirectoryPath()
    {
        return $GLOBALS['DYNAMIC_CONTENT_PATH'] . "/licenses";
    }

    public function getImageTag($size = Avatar::MEDIUM, $opt = [])
    {
        if (!$this->is_customized()) {
            return "";
        } else {
            return parent::getImageTag($size, $opt);
        }
    }

    /**
     * Returns the CSS class to use for this avatar image.
     *
     * @param string  one of the constants Avatar::(NORMAL|MEDIUM|SMALL)
     *
     * @return string CSS class to use for the avatar
     */
    protected function getCssClass($size)
    {
        return sprintf('license-avatar-%s license-%s', $size, $this->user_id);
    }

    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    public function getDefaultTitle()
    {
        return License::find($this->user_id)->name;
    }

    /**
     * Return if avatar is visible to the current user.
     * @return boolean: true if visible
     */
    protected function checkAvatarVisibility()
    {
        return true;
    }

    /**
     * Return the dimension of a size
     *
     * @param    string         the dimension of a size
     * @return array            a tupel of integers [width, height]
     */
    public static function getDimension($size)
    {
        $dimensions = [
            Avatar::NORMAL => [300, 100],
            Avatar::MEDIUM => [120, 40],
            Avatar::SMALL  => [60, 20]
        ];
        return $dimensions[$size];
    }
}
