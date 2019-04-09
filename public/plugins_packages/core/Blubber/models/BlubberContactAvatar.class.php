<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once __DIR__ . '/BlubberExternalContact.class.php';

/**
 * Avatar for external blubber-authors who are most likely anonymous accounts.
 * If there is a given email to the user, it displays a gravatar. Since the clean
 * email of the user is not sent to gravatar, this service seems secure.
 */
class BlubberContactAvatar extends Avatar {

    /**
     * Gets an BlubberContactAvatar-object for that user.
     */
    static function getAvatar($id) {
        return new BlubberContactAvatar($id);
    }
    
    /**
     * Returns the URL to the courses' avatars.
     *
     * @return string     the URL to the avatars
     */
    function getAvatarDirectoryUrl()
    {
        return $GLOBALS['DYNAMIC_CONTENT_URL'] . "/blubbercontact";
    }


    /**
     * Returns the file system path to the courses' avatars
     *
     * @return string      the file system path to the avatars
     */
    function getAvatarDirectoryPath()
    {
        return $GLOBALS['DYNAMIC_CONTENT_PATH'] . "/blubbercontact";
    }
    
    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    function getDefaultTitle()
    {
        return BlubberExternalContact::find($this->user_id)->name;
    }
    
    /**
     * Return if avatar is visible to the current user.
     * @return boolean: true if visible
     */
    protected function checkAvatarVisibility() {
        //no special conditions for visibility of blubber-contact-avatars
        return true;
    }

    /**
     * URL to given avatar, if there is a customized avatar. And for anonymous
     * authors this geturns a url to gravatar.
     * @param Avatar-sizes (constan, see there) $size
     * @param string $ext
     * @return string url
     */
    function getURL($size, $ext = 'png') {
        $this->checkAvatarVisibility();
        if ($this->is_customized()) {
            return $this->getCustomAvatarUrl($size, $ext);
        } else {
            $contact = new BlubberExternalContact($this->user_id);
            $email = $contact['mail_identifier'];
            $email_hash = md5(mb_strtolower(trim($email)));
            $width = $this->getDimension($size);
            return URLHelper::getURL(
                "http://www.gravatar.com/avatar/".$email_hash,
                [
                    's' => max([$width[0], $width[1]]),
                    'd' => $this->getNobody()->getCustomAvatarUrl($size, $ext)
                ],
                true
            );
        }
    }
    
}