<?php

/**
 * StudipItem.interface.php - contains the StudipItem interface.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * This interface provides methods which allow an unified access
 * to basic properties of Stud.IP objects.
 * It is meant to be an extension for SimpleORMap objects.
 */
interface StudipItem
{
    /**
     * Returns a human-readable name of the object.
     *
     * @param bool $long_format If set to true, a long format
     *     that has the object type as a prefix (course, room etc.)
     *     is returned. Otherwise only the name is returned.
     *
     * @returns string A human-readable string of the object's name.
     */
    public function getItemName($long_format = true);

    /**
     * Returns an URL that points to a page describing or displaying
     * the object.
     *
     * @returns string|null Either the URL to a descriptive page for
     *     the object or null, if the object does not have such an URL.
     */
    public function getItemURL();

    /**
     * Returns the URL to the avatar image or icon of the object,
     * if applicable.
     *
     * @returns string|null Either the URL to the object's avatar
     *     or icon or null, if the object does not have an avatar.
     */
    public function getItemAvatarURL();


    /**
     * Creates a StudipLink object that links to a page with information
     * about the StudipItem object.
     *
     * @returns StudipLink A StudipLink object for the information page
     *     of the StudipItem object.
     */
    public function getLink() : StudipLink;
}
