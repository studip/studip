<?php
/**
 * MessageFolder.class.php
 *
 * This is a FolderType implementation for message folders.
 * A message folder contains the attachments of a Stud.IP message.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Moritz Strohm <strohm@data-quest.de>
 * @copyright 2016 data-quest
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class MessageFolder extends StandardFolder implements FolderType
{
    protected $folder;

    /**
     * @param Folder|null folder The folder object for this FolderType
     */
    public function __construct($folder = null)
    {
        if ($folder instanceof MessageFolder) {
            $this->folder = $folder->folder;
        } elseif ($folder instanceof Folder) {
            $this->folder = $folder;
        } else {
            $this->folder = Folder::build($folder);
        }
        $this->folder['folder_type'] = get_class($this);
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->folder[$attribute];
    }

    /**
     * Retrieves or creates the top folder for a message.
     *
     * Creating top folders for messages is a special task since
     * message attachments can be stored when the message wasn't sent yet.
     * This means that message attachments of an unsent message are stored
     * in a top folder with a range-ID that doesn't belong to a message
     * table entry (yet). Therefore we must create the top folder
     * manually when we can't find the top folder by the method
     * Folder::getTopFolder.
     *
     * @param string $message_id The message-ID of the message whose top folder
     *     shall be returned
     *
     * @return MessageFolder|null The top folder of the message identified by
     *     $message_id. If the folder can't be retrieved, null is returned.
     */
    public static function findTopFolder($message_id)
    {

        //try to find the top folder:
        $folder = Folder::findOneByrange_id($message_id);

        //check if that was successful:
        if ($folder) {
            return new MessageFolder($folder);
        }
    }

    /**
     * Creates a root folder (top folder) for a message referenced by its ID.
     *
     * @param string $message_id The ID of a message for which a root folder
     *     shall be generated.
     *
     * @return MessageFolder A new MessageFolder as root folder for a message.
     */
    public static function createTopFolder($message_id)
    {
        return new MessageFolder(
            Folder::createTopFolder(
                $message_id,
                'message',
                'MessageFolder'
            )
        );
    }

    /**
     * Returns the amount of attachments for a message.
     *
     * @param string $message_id The ID of a message.
     *
     * @return int The amount of attachments that have been found for the message.
     */
    public static function getNumMessageAttachments($message_id)
    {

        $message_top_folder = self::findTopFolder($message_id);
        if (!$message_top_folder) {
            return 0;
        }

        //return the amount of file references that are logically inside the
        //top folder. This is the amount of message attachments.
        $num_file_ref = FileRef::countBySql('folder_id = :folder_id', [
            'folder_id' => $message_top_folder->getId()
        ]);

        return $num_file_ref;
    }

    /**
     * This method returns always false since MessageFolder types are not
     * creatable in standard folders. They are a standalone folder type.
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * Returns a localised name of the MessageFolder type.
     */
    public static function getTypeName()
    {
        return _('Nachrichtenordner');
    }

    /**
     * Returns the Icon object for the MessageFolder type.
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create('folder-message', $role);
    }

    /**
     * Returns the ID of the folder object of this MessageFolder.
     */
    public function getId()
    {
        return $this->folder->id;
    }

    /**
     * See method MessageFolder::isReadable.
     */
    public function isVisible($user_id)
    {
        return $this->isReadable($user_id);
    }

    /**
     * This method checks if a specified user can read the MessageFolder object.
     *
     * @param string $user_id The ID of the user whose read permission
     *     shall be checked.
     *
     * @return True, if the user, specified by $user_id, can read the folder,
     *     false otherwise.
     */
    public function isReadable($user_id)
    {
        $condition = 'message_id = :message_id AND user_id = :user_id';
        return MessageUser::countBySql($condition, [
                'message_id' => $this->folder->range_id,
                'user_id'    => $user_id,
            ]) > 0;
    }

    /**
     * MessageFolders are only writable for their owners.
     */
    public function isWritable($user_id)
    {
        return $user_id === $this->folder->user_id;
    }

    /**
     * MessageFolders are never editable.
     */
    public function isEditable($user_id)
    {
        return false;
    }

    /**
     * MessageFolders will never allow subfolders.
     */
    public function isSubfolderAllowed($user_id)
    {
        return false;
    }

    /**
     * MessageFolders don't have a description template.
     */
    public function getDescriptionTemplate()
    {
        return '';
    }

    /**
     * MessageFolders don't have subfolders.
     */
    public function getSubfolders()
    {
        return [];
    }

    /**
     * MessageFolders don't have parents.
     */
    public function getParent()
    {
        return null;
    }

    /**
     * MessageFolders don't have an edit template.
     */
    public function getEditTemplate()
    {
        return '';
    }

    /**
     * MessageFolders don't have an edit template and therefore cannot
     * handle requests from such templates.
     */
    public function setDataFromEditTemplate($request)
    {
    }

    /**
     * This method handles file upload validation.
     *
     * @param array $uploaded_file The uploaded file that shall be validated.
     * @param string $user_id The user who wishes to upload a file
     *     in this MessageFolder.
     *
     * @return string|null An error message on failure, null on success.
     */
    public function validateUpload(FileType $newfile, $user_id)
    {
        $status      = $GLOBALS['perm']->get_perm($user_id);
        $upload_type = [
            'type' => $GLOBALS['UPLOAD_TYPES']['attachments']['type'],
            'file_types' => $GLOBALS['UPLOAD_TYPES']['attachments']['file_types'],
            'file_size' => $GLOBALS['UPLOAD_TYPES']['attachments']['file_sizes'][$status]
        ];
        return $this->getValidationMessages($upload_type, $newfile);
    }

    /**
     * Handles the deletion of a file inside this folder.
     *
     * @param string $file_ref_id The ID of the FileRef whose file
     *     shall be deleted.
     *
     * @return True, if the file has been deleted successfully, false otherwise.
     */
    public function deleteFile($file_ref_id)
    {
        $file_refs = $this->folderdata->file_refs;

        if ($file_refs) {
            foreach ($file_refs as $file_ref) {
                if ($file_ref->id === $file_ref_id) {
                    //we found the FileRef that shall be deleted
                    return $file_ref->delete();
                }
            }
        }

        //if no file refs are present or the file ref can't be found
        //we return false:
        return false;
    }

    /**
     * Stores the MessageFolder object.
     *
     * @return True, if the MessageFolder has been stored successfully,
     *     false otheriwse.
     */
    public function store()
    {
        return $this->folder->store();
    }

    /**
     * MessageFolders cannot have subfolders.
     */
    public function createSubfolder(FolderType $folderdata)
    {
        return null;
    }

    /**
     * MessageFolders cannot have subfolders.
     */
    public function deleteSubfolder($subfolder_id)
    {
    }

    /**
     * Deletes the MessageFolder object.
     *
     * @return True, if the MessageFolder has been deleted successfully,
     *     false otheriwse.
     */
    public function delete()
    {
        return $this->folder->delete();
    }

    /**
     * See method MessageFolder::isReadable
     */
    public function isFileDownloadable($file_ref_id, $user_id)
    {
        return $this->isReadable($user_id);
    }

    /**
     * Files inside MessageFolders are not editable.
     */
    public function isFileEditable($file_ref_id, $user_id)
    {
        //message attachments are never editable!
        return false;
    }

    /**
     * Files inside MessageFolders are not writable.
     */
    public function isFileWritable($file_ref_id, $user_id)
    {
        //message attachments are never writable!
        return false;
    }

    /**
     * @see FolderType::copySettings()
     */
    public function copySettings()
    {
        return ['description' => $this->description];
    }

}
