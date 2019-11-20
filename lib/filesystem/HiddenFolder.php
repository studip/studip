<?php
/**
 *  HiddenFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Dominik Feldschnieders <dofeldsc@uos.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class HiddenFolder extends PermissionEnabledFolder
{

    public static $sorter = 6;


    // nobody can see, write or read in this folder except the lecturer and his tutors
    protected $permission = 0;

    public static function availableInRange($range_id_or_object, $user_id)
    {
        $range_id = is_object($range_id_or_object) ? $range_id_or_object->id : $range_id_or_object;
         return Seminar_Perm::get()->have_studip_perm('tutor', $range_id, $user_id);
    }

    /**
     * Returns the name of the HiddenFolder type.
     *
     * @return string the name of the HiddenFolder type
     */
    public static function getTypeName()
    {
        return _("Unsichtbarer Ordner");
    }

    /**
     * This function returns the suitable Icon for this folder type (HiddenFolder)
     *
     * @return Icon The icon object for this folder type
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
               ? 'folder-lock-empty+visibility-invisible'
               : 'folder-lock-full+visibility-invisible';
        return Icon::create($shape, $role);
    }

    /**
     * HiddenFolder constructor.
     */
    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);
        $this->permission = 0;
    }

    /**
     * Returns the description template for a instance of a HiddenFolder type
     *
     * @return mixed A description template for a instance of the type HiddenFolder
     */
    public function getDescriptionTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/hidden_folder/description.php');

        $template->type       = self::getTypeName();
        $template->folder     = $this;
        $template->folderdata = $this->folderdata;

        return $template;
    }

    /**
     * Returns the edit template for this folder type.
     *
     * @return Flexi_Template
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/hidden_folder/edit.php');
        $template->download_allowed = $this->download_allowed;
        return $template;
    }

    /**
     * Sets the data from a submitted edit template.
     *
     * @param array $request The data from the edit template.
     *
     * @return FolderType
     */
    public function setDataFromEditTemplate($request)
    {
        $this->download_allowed = (int)$request['hidden_folder_download_allowed'];
        return parent::setDataFromEditTemplate($request);
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if ($attribute === 'download_allowed') {
            return $this->folderdata['data_content']['download_allowed'];
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
        if ($name === 'download_allowed') {
            return $this->folderdata['data_content']['download_allowed'] = $value;
        }
        return $this->folderdata[$name] = $value;
    }

    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);

        if (is_object($fileref)) {
            if ($this->download_allowed
                && $this->getParent()->isVisible($user_id)
                && $this->getParent()->isReadable($user_id)) {
                return $fileref->terms_of_use->fileIsDownloadable($fileref, true, $user_id);
            }
        }
        return false;
    }

}
