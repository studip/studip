<?php
/**
 * VirtualFolderType.php
 *
 * This is a FolderType implementation for folders that dont exist in
 * the database table folders, e.g. folders from plugins
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class VirtualFolderType implements FolderType
{
    /**
     * @var array
     */
    protected $folderdata;
    /**
     * @var string
     */
    protected $plugin_id;

    /**
     * @var array
     */
    protected $files      = [];
    /**
     * @var array
     */
    protected $subfolders = [];

    /**
     * VirtualFolderType constructor.
     * @param array $folderdata
     * @param null $plugin_id
     */
    public function __construct($folderdata = [], $plugin_id = null)
    {
        $this->folderdata = $folderdata;
        $this->plugin_id  = $plugin_id;
    }

    /**
     * @return string
     */
    public static function getTypeName()
    {
        return _('Virtueller Ordner');
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
     * @param string $role
     * @return Icon
     */
    public function getIcon($role = 'info')
    {
        return Icon::create('folder-empty', $role);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->folderdata['id'];
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->folderdata[$attribute];
    }

    /**
     * @param $attribute
     * @param $value
     */
    public function __set($attribute, $value)
    {
        $this->folderdata[$attribute] = $value;
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
     * @param string $user_id
     * @return bool
     */
    public function isReadable($user_id)
    {
        return true;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return false;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isEditable($user_id)
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
     * @return null
     */
    public function getDescriptionTemplate()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getEditTemplate()
    {
        return null;
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
     * @return bool
     */
    public function validateUpload($uploadedfile, $user_id)
    {
        return false;
    }

    /**
     * @return array
     */
    public function getSubfolders()
    {
        return $this->subfolders;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return null
     */
    public function getParent()
    {
        if (!$this->folderdata['parent_id']) {
            return null;
        }

        if ($this->plugin_id) {
            return PluginManager::getInstance()->getPluginById($this->plugin_id)->getFolder($this->folderdata->parent_id);
        }

        return $this->folderdata->parent_id
             ? $this->folderdata->parentfolder->getTypedFolder()
             : null;
    }

    /**
     * @param array|ArrayAccess $filedata
     * @return FileRef
     */
    public function createFile($filedata)
    {
        if (is_array($filedata)) {
            $filedata = (object) $filedata;
        }
        $this->files[] = $filedata;
        return end($this->files);
    }

    /**
     * @param string $file_ref_id
     * @return bool
     */
    public function deleteFile($file_ref_id)
    {
        return true;
    }

    /**
     * @param FolderType $folderdata
     * @return FolderType
     */
    public function createSubfolder(FolderType $folderdata)
    {
        $this->subfolders[] = $folderdata;
        return $folderdata;
    }

    /**
     * @param string $subfolder_id
     * @return bool
     */
    public function deleteSubfolder($subfolder_id)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return true;
    }

    /**
     *
     */
    public function store()
    {
    }

    /**
     * @param string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        return true;
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
}
