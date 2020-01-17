<?php
/**
 * MVVFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Timo Hartge <hartge@data-quest.de>
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 * @since     4.4
 */
class MVVFolder extends StandardFolder
{

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isSubfolderAllowed($user_id)
    {
        return false;
    }

    /**
     * See method MVVFolder::isReadable
     */
    public function isFileDownloadable($file_ref_id, $user_id)
    {
        return $this->isReadable($user_id);
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isReadable($user_id)
    {
        return true;
    }

    /**
     * Returns a localised name of the PublicFolder type.
     *
     * @return string The localised name of this folder type.
     */
    static public function getTypeName()
    {
        return _('Ein Ordner für Studiengänge');
    }

    /**
     * Returns a description template for PublicFolders.
     *
     * @return string A string describing this folder type.
     */
    public function getDescriptionTemplate()
    {
        return _('Dateien aus diesem Ordner werden durch den Studiengang zum Download angeboten.');
    }

    /**
     * Retrieves the top folder for a mvv object.
     *
     * @param string $range_id The mvv object-ID of the mvv object whose top folder
     *     shall be returned
     *
     * @return MVVFolder|null The top folder of the mvv object identified by
     *     $range_id. If the folder can't be retrieved, null is returned.
     */
    public static function findTopFolder($range_id)
    {
        //try to find the top folder:
        $folder = Folder::findOneByrange_id($range_id);
        if ($folder) {
            $topfolder = $folder->getTypedFolder();
        }
        return $topfolder ?: null;
    }

    /**
     * Creates a root folder (top folder) for a mvv object referenced by its ID.
     *
     * @param string $range_id The ID of a mvv object for which a root folder
     *     shall be generated.
     *
     * @return MVVFolder A new MVVFolder as root folder for a mvv object.
     */
    public static function createTopFolder($range_id)
    {
        return new MVVFolder(
            Folder::createTopFolder(
                $range_id,
                'mvv',
                'MVVFolder'
            )
        );
    }

}
