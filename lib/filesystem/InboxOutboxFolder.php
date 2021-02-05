<?php
/**
 * InboxOutboxFolder.class.php
 *
 * This is a common FolderType implementation for inbox and outbox folders.
 * It it not meant to be used directly! Instead use the InboxFolder and
 * OutboxFolder extensions of this class!
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     Moritz Strohm <strohm@data-quest.de>
 * @copyright  2016 data-quest
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category   Stud.IP
 */
class InboxOutboxFolder implements FolderType
{
    protected $user = null;
    protected $folder;

    public function __construct($folder)
    {
        if ($folder instanceof Folder) {
            $this->folder = $folder;
            $this->user   = User::find($folder->user_id);
        } else {
            $this->folder = new Folder();
        }
    }

    /**
     * Overloaded magic get method to get the attributes of the folder object.
     */
    public function __get($attribute)
    {
        return $this->folder[$attribute];
    }

    /**
     * Returns a localised name of the InboxOutboxFolder type.
     *
     * @return string The localised name of this folder type.
     */
    public static function getTypeName()
    {
        return _('InboxOutboxFolder');
    }

    /**
     * Returns the Icon object for the InboxOutboxFolder type.
     *
     * @return Icon An icon object with the icon for this folder type.
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $icon = count($this->getFiles())
            ? 'folder-full'
            : 'folder-empty';
        return Icon::create($icon, $role);
    }

    /**
     * Returns the ID of the folder object of this InboxOutboxFolder.
     */
    public function getId()
    {
        return $this->folder->id;
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * InboxOutboxFolders are only visible for the owner.
     */
    public function isVisible($user_id)
    {
        return $this->user
            && $user_id === $this->user->id;
    }

    /**
     * InboxOutboxFolders are only readable for the owner.
     */
    public function isReadable($user_id)
    {
        return $this->user
            && $user_id === $this->user->id;
    }

    /**
     * InboxOutboxFolders are not writable.
     */
    public function isWritable($user_id)
    {
        return false;
    }

    /**
     * InboxOutboxFolders are not editable.
     */
    public function isEditable($user_id)
    {
        return false;
    }

    /**
     * InboxOutboxFolders do not allow subfolders.
     */
    public function isSubfolderAllowed($user_id)
    {
        //this folder type does not allow subfolders!
        return false;
    }

    /**
     * InboxOutboxFolders don't have a description template.
     */
    public function getDescriptionTemplate()
    {
        return '';
    }

    /**
     * Returns the parent InboxOutboxFolder.
     */
    public function getParent()
    {
        if ($this->folder->parentFolder) {
            return $this->folder->parentFolder->getTypedFolder();
        }

        return null;
    }

    /**
     * InboxOutboxFolders do not allow subfolders.
     */
    public function getSubfolders()
    {
        //no subfolders allowed!
        return [];
    }

    /**
     * InboxOutboxFolders do not contain any files since the InboxOutboxFolder
     * type is not meant to be used directly.
     */
    public function getFiles()
    {
        //this folder type is not meant to be used directly, so no files
        //are returned:
        return [];
    }

    /**
     * InboxOutboxFolders do not have an edit template.
     */
    public function getEditTemplate()
    {
        return '';
    }

    /**
     * InboxOutboxFolders do not have an edit template.
     */
    public function setDataFromEditTemplate($folderdata)
    {
        return MessageBox::error(
            _('InboxOutbox-Ordner können nicht bearbeitet werden!')
        );
    }
    public function store()
    {

    }

    /**
     * InboxOutboxFolders do not allow uploads.
     */
    public function validateUpload(FileType $file, $user_id)
    {
        //no uploads allowed
        return false;
    }

    public function addFile(FileType $file, $user_id = null) {
        return false;
    }

    /**
     * InboxOutboxFolders do not allow deleting files.
     */
    public function deleteFile($file_ref_id)
    {
        return false;
    }

    /**
     * InboxOutboxFolders do not allow the creation of subfolders.
     */
    public function createSubfolder(FolderType $folderdata)
    {
        throw new UnexpectedValueException(
            _('In InboxOutbox-Ordnern können keine nutzerdefinierten Unterordner erzeugt werden!')
        );
    }

    /**
     * InboxOutboxFolders do not allow deleting subfolders.
     */
    public function deleteSubfolder($subfolder_id)
    {
        //there are no subfolders, so they can't be deleted:
        return false;
    }

    /**
     * Deletes the Folder object of an InboxOutboxFolder instance.
     *
     * @return True on success, false on failure.
     */
    public function delete()
    {
        return $this->folder->delete();
    }

    /**
     * Files are only downloadable for the owner.
     */
    public function isFileDownloadable($file_ref_id, $user_id)
    {
        return $this->user
            && $user_id === $this->user->id;
    }

    /**
     * InboxOutboxFolders do not allow editing files.
     */
    public function isFileEditable($file_ref_id, $user_id)
    {
        //files shall be unchanged in here
        return false;
    }

    /**
     * InboxOutboxFolders do not allow writing files.
     */
    public function isFileWritable($file_ref_id, $user_id)
    {
        //files shall be unchanged in here
        return false;
    }

    /**
     * Returns an associative array of additional colums with the index the id of the column
     * and their values as the localized names of the columns
     * @return array('col1' => _("Anfragestatus"))
     */
    public function getAdditionalColumns()
    {
        return [];
    }

    /**
     * Returns the content for that additional column, if it exists. You can return null a string
     * or a Flexi_Template as the content.
     * @param string $column_index
     * @return null|string|Flexi_Template
     */
    public function getContentForAdditionalColumn($column_index)
    {
        return null;
    }

    /**
     * Returns an integer or text that marks the value the content of the given column should be
     * ordered by.
     * @param string $column_index
     * @return mixed : order value
     */
    public function getAdditionalColumnOrderWeigh($column_index)
    {
        return 0;
    }

    /**
     * Returns an array of Studip\Button or Studip\LinkButton objects that get displayed
     * underneath the files-table.
     * @return array of Studip\Button or Studip\LinkButton
     */
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
