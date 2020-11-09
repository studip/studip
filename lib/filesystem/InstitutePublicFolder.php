<?php

/**
 * InstitutePublicFolder.php
 *
 * The InstitutePublicFolder is a specialisation of the CoursePublicFolder class
 * for the file area of an institute.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Moritz Strohm <strohm@data-quest.de>
 * @copyright 2020
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class InstitutePublicFolder extends CoursePublicFolder
{
    public static function getTypeName()
    {
        return _('Ordner für öffentlich zugängliche Dateien');
    }


    /**
     * Determines if this folder type is available for a user and the object
     * that is either referenced by its ID or whose data is passed to this
     * method.
     *
     * @param mixed $range_id_or_object The obect to be checked.
     *
     * @param string $user_id The user for which the availability of this
     *     folder type shall be checked.
     *
     * @return bool True, if the folder type is available, false otherwise.
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        $institute = Institute::toObject($range_id_or_object);
        if ($institute instanceof Institute && !$institute->isNew()) {
            return $GLOBALS['perm']->have_studip_perm('tutor', $institute->id, $user_id);
        }
        return false;
    }


    /**
     * Determines the visibility of an InstitutePublicFolder.
     * An InstitutePublicFolder is visible for all logged in users and in
     * case ENABLE_FREE_ACCESS is set to '1' or the worldwide access option
     * is set.
     *
     * @param string $user_id The user who wishes to see the folder.
     * @return bool True, in case the user may see the folder, false otherwise.
     */
    public function isVisible($user_id)
    {
        if ($user_id === null || $user_id === 'nobody') {
            if ($this->folderdata['data_content']['worldwide_access']) {
                return true;
            }
            $range = $this->getRangeObject();
            return Config::get()->ENABLE_FREE_ACCESS && isset($range);
        }

        return true;
    }

    /**
     * Returns a description template for InstitutePublicFolder.
     *
     * @return string A string describing this folder type.
     */
    public function getDescriptionTemplate()
    {
        return _('Dateien aus diesem Ordner sind auch für Personen sichtbar, die nicht der Einrichtung zugeordnet sind.');
    }
}
