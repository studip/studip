<?php
/**
 * OutboxFolder.class.php
 *
 * This is a FolderType implementation for file attachments of messages
 * that were sent by a user. It is a read-only folder.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class OutboxFolder extends InboxOutboxFolder
{

    /**
     * Returns a localised name of the OutboxFolder type.
     *
     * @return string The localised name of this folder type.
     */
    public static function getTypeName()
    {
        return _('Alle Anhänge gesendeter Nachrichten');
    }

    /**
     * Returns the Icon object for the OutboxFolder type.
     *
     * @return Icon An icon object with the icon for this folder type.
     */
    public function getIcon($role)
    {
        return Icon::create(
            count($this->getFiles())
            ? 'folder-inbox-full'
            : 'folder-inbox-empty',
            $role
        );
    }

    /**
     * Gets all attachments of sent messages of a specific user
     * and places the attachments inside this folder.
     *
     * @return FileRef[] Array of FileRef objects representing the message
     *     attachments.
     */
    public function getFiles()
    {
        //get all folders of the user that belongs to a received message:
        $message_folders = Folder::findBySql(
            "INNER JOIN message_user
            ON folders.range_id = message_user.message_id
            WHERE
            folders.range_type = 'message'
            AND
            message_user.user_id = :user_id
            AND
            message_user.snd_rec = 'snd'
            AND
            message_user.deleted = '0'",
            [
                'user_id' => $this->user->id
            ]
        );

        $files = [];

        foreach ($message_folders as $folder) {
            $file_refs = FileRef::findBySql(
                'folder_id = :folder_id',
                [
                    'folder_id' => $folder->id
                ]
            );
            $files = array_merge($files, $file_refs);
        }

        return $files;
    }
}
