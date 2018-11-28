<?php
/**
 * wysiwygrequest.php - Security and state of WYSIWYG editor requests.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
namespace Studip;

/**
 * Collection of static methods for session handling.
 */
class WysiwygRequest
{
    //// security checks //////////////////////////////////////////////////////
 
    /**
     * TODO documentation
     */
    public static function verifyWritePermission($permission)
    {
        self::verifyPostRequest();
        \CSRFProtection::verifyUnsafeRequest();
        self::verifyStudipPermission($permission);
    }

    /**
     * Throw exception if HTTP request was not send as POST.
     * @throws AccessDeniedException if request was not send as HTTP POST.
     */
    public static function verifyPostRequest()
    {
        if (!\Request::isPost()) {
            throw new \AccessDeniedException(
                \_('Die Anfrage muss als HTTP POST gestellt werden.'));
        }
    }
    
    /** 
     * Throw exception if current user hasn't required access level. 
     * 
     * @param string $permission  Minimum required access level. 
     * @throws AccessDeniedException if user does not have permission. 
     */ 
    public static function verifyStudipPermission($permission)
    {
        if (! $GLOBALS['perm']->have_studip_perm($permission, self::seminarId())) {
            throw new \AccessDeniedException(
                \sprintf(\_('Es werden mindestens %s-Zugriffsrechte benötigt.'),
                        $permission)); 
        }
    }

    //// session information //////////////////////////////////////////////////

    /**
     * Return current seminar's identifier.
     *
     * @return mixed  Seminar identifier (string) or FALSE (boolean) if no
     *                seminar is selected.
     */
    public static function seminarId() {
        if (\Request::option('cid')) {
            return \Request::option('cid');
        }
        if ($GLOBALS['SessionSeminar']) {
            \URLHelper::bindLinkParam('cid', $GLOBALS['SessionSeminar']);
            return $GLOBALS['SessionSeminar'];
        }
        return false;
    }
}
