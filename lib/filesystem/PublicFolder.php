<?php
/**
 * PublicFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2017 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class PublicFolder extends StandardFolder
{

    public static $sorter = 1;

    /**
     * Returns a localised name of the PublicFolder type.
     *
     * @return string The localised name of this folder type.
     */
    static public function getTypeName()
    {
        return _('Ein Ordner für öffentlich zugängliche Daten');
    }

    /**
     * @param Object|string $range_id_or_object
     * @param string $user_id
     * @return bool
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        $range_id = is_object($range_id_or_object) ? $range_id_or_object->id : $range_id_or_object;
        return $range_id === $user_id;
    }


    /**
     * @param string $role
     * @return Icon
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
               ? 'folder-public-empty'
               : 'folder-public-full';

        return Icon::create($shape, $role);
    }


    /**
     * @param $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if ($attribute === 'viewable') {
            return $this->folderdata['data_content']['viewable'];
        }
        return $this->folderdata[$attribute];
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if ($name === 'viewable') {
            return $this->folderdata['data_content']['viewable'] = $value;
        }
        return $this->folderdata[$name] = $value;
    }

    /**
     * PublicFolders are visible for the owner, or for all if viewable flag is set
     *
     * @param string $user_id The user who wishes to see the folder.
     *
     * @return bool True
     */
    public function isVisible($user_id)
    {
        return $this->viewable || $this->range_id === $user_id;
    }

    /**
     * PublicFolders are readable for the owner, or for all if viewable flag is set
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
     * Returns a description template for PublicFolders.
     *
     * @return string A string describing this folder type.
     */
    public function getDescriptionTemplate()
    {
        return $this->viewable ?
            _('Dateien aus diesem Ordner werden auf Ihrer Profilseite zum Download angeboten.')
            :
         _('Dateien aus diesem Ordner sind für alle Stud.IP Nutzer zugreifbar.');

    }

    /**
     * Files in PublicFolders are always downloadable.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to downlaod the file.
     *
     * @return bool True
     */
    public function isFileDownloadable($file_id, $user_id)
    {
        //public folder => everyone can download a file
        return true;
    }

    /**
     * Files in PublicFolders are editable for the owner only.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to edit the file.
     *
     * @return bool True, if the user is the owner of the file, false otherwise.
     */
    public function isFileEditable($file_id, $user_id)
    {
        //only the owner may edit files
        return $this->range_id === $user_id;
    }

    /**
     * Files in PublicFolders are writable for the owner only.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to write to the file.
     *
     * @return bool True, if the user is the owner of the file, false otherwise.
     */
    public function isFileWritable($file_id, $user_id)
    {
        //only the owner may delete files
        return $this->range_id === $user_id;
    }

    /**
     * Returns the edit template for this folder type.
     *
     * @return Flexi_Template
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/public_folder/edit.php');
        $template->public_folder_viewable = $this->viewable;
        return $template;
    }

    /**
     * Sets the data from a submitted edit template.
     *
     * @param array $request The data from the edit template.
     *
     * @return PublicFolder A "reference" to this PublicFolder.
     */
    public function setDataFromEditTemplate($request)
    {
        $this->viewable = (int)$request['public_folder_viewable'];
        return parent::setDataFromEditTemplate($request);
    }
}
