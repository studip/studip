<?php
/**
 * CoursePublicFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Elmar Ludwig <elmar.ludwig@uos.de>
 * @copyright 2017 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class CoursePublicFolder extends StandardFolder
{

    public static $sorter = 7;

    /**
     * Returns a localised name of the CoursePublicFolder type.
     *
     * @return string The localised name of this folder type.
     */
    static public function getTypeName()
    {
        return _('Ordner für öffentlich zugängliche Daten');
    }

    /**
     * @param Object|string $range_id_or_object
     * @param string $user_id
     * @return bool
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id);
        }
    }

    /**
     * CoursePublicFolders are visible for all logged in users.
     *
     * @param string $user_id The user who wishes to see the folder.
     *
     * @return bool True
     */
    public function isVisible($user_id)
    {
        if ($user_id === null || $user_id === 'nobody') {
            $range = $this->getRangeObject();
            return Config::get()->ENABLE_FREE_ACCESS && isset($range) && $range->lesezugriff == 0;
        }

        return true;
    }

    /**
     * CoursePublicFolders are readable for all logged in users.
     *
     * @param string $user_id The user who wishes to read the folder.
     *
     * @return bool True
     */
    public function isReadable($user_id)
    {
        return $this->isVisible($user_id);
    }

    /**
     * Returns a description template for CoursePublicFolders.
     *
     * @return string A string describing this folder type.
     */
    public function getDescriptionTemplate()
    {
        return _('Dateien aus diesem Ordner werden auf der Detailseite der Veranstaltung zum Download angeboten.');
    }

    /**
     * Files in CoursePublicFolders are downloadable for all logged in users.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to downlaod the file.
     *
     * @return bool True
     */
    public function isFileDownloadable($file_id, $user_id)
    {
        return $this->isVisible($user_id);
    }
}
