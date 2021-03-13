<?php
/**
 * UnknownFolderType.php
 *
 * this folder type implementation is used when a folder type entry in
 * the database is no longer available in the main system
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class UnknownFolderType implements FolderType
{
    /**
     * @var Folder
     */
    protected $folderdata;

    /**
     * StandardFolder constructor.
     * @param Folder|null $folderdata
     */
    public function __construct($folderdata)
    {
        if ($folderdata instanceof Folder) {
            $this->folderdata = $folderdata;
        } else {
            $this->folderdata = new Folder();
        }
    }

    /**
     * @return string
     */
    public static function getTypeName()
    {
        return _('Unbekannter Ordner Typ');
    }

    /**
     * @param Object|string $range_id_or_object
     * @param string $user_id
     * @return bool
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * @return Icon
     */
    public function getIcon($role = 'info')
    {
        return Icon::create('folder-broken', $role);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->folderdata->getId();
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if ($attribute === 'name') {
            return $this->folderdata['name'] . sprintf(
                _(' (unbekannter Typ: %s)'),
                $this->folderdata['folder_type']
            );
        }

        return $this->folderdata[$attribute];
    }


    /**
     * @param $user_id
     * @return bool
     */
    public function isVisible($user_id)
    {
        return true;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isReadable($user_id)
    {
        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isEditable($user_id)
    {
        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isSubfolderAllowed($user_id)
    {
        return false;
    }

    /**
     *
     */
    public function getDescriptionTemplate()
    {

    }


    /**
     *
     */
    public function getEditTemplate()
    {

    }

    /**
     * @param ArrayAccess|Request $request
     */
    public function setDataFromEditTemplate($request)
    {

    }

    /**
     * @param $uploadedfile
     * @param string $user_id
     */
    public function validateUpload(FileType $file, $user_id)
    {

    }

    public function addFile(FileType $file, $user_id = null)
    {
        return false;
    }

    /**
     * @return array
     */
    public function getSubfolders()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return [];
    }

    /**
     * @return null
     */
    public function getParent()
    {
        return $this->folderdata->parentfolder
             ? $this->folderdata->parentfolder->getTypedFolder()
             : null;
    }

    /**
     * @param string $file_ref_id
     * @return bool
     */
    public function deleteFile($file_ref_id)
    {
        return false;
    }

    /**
     * @param FolderType $folderdata
     */
    public function createSubfolder(FolderType $folderdata)
    {

    }

    /**
     * @param string $subfolder_id
     * @return bool
     */
    public function deleteSubfolder($subfolder_id)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function store()
    {
        return false;
    }

    /**
     * @param string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        return false;
    }


    /**
     * @param string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        return false;
    }

    /**
     * @param $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        return false;
    }

    public function getAdditionalColumns()
    {
        return [];
    }

    public function getContentForAdditionalColumn($column_index)
    {
        return null;
    }

    public function getAdditionalColumnOrderWeigh($column_index)
    {
        return 0;
    }

    public function getAdditionalActionButtons()
    {
        return [];
    }

    /**
     * @see FolderType::copySettings()
     */
    public function copySettings()
    {
        return ['description' => $this->description];
    }

}
