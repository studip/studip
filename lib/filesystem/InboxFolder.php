<?php
/**
 * InboxFolder.class.php
 *
 * This is a FolderType implementation for file attachments of messages
 * that were received by a user. It is a read-only folder.
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
class InboxFolder extends InboxOutboxFolder
{
    /**
     * Returns a localised name of the InboxFolder type.
     *
     * @return string The localised name of this folder type.
     */
    public static function getTypeName()
    {
        return _('Alle Anhänge eingegangener Nachrichten');
    }

    /**
     * Returns the Icon object for the InboxFolder type.
     *
     * @return Icon An icon object with the icon for this folder type.
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $icon = count($this->getFiles()) > 0
              ? 'folder-inbox-full'
              : 'folder-inbox-empty';
        return Icon::create($icon, $role);
    }

    /**
     * Gets all attachments of received messages of a specific user
     * and places the attachments inside this folder.
     *
     * @return FileRef[] Array of FileRef objects representing the message attachments.
     */
    public function getFiles()
    {
        $condition = "INNER JOIN message_user
                         ON folders.range_id = message_user.message_id
                      WHERE folders.range_type = 'message'
                        AND message_user.user_id = :user_id
                        AND message_user.snd_rec = 'rec'
                        AND message_user.deleted = '0'";
        $message_folders = Folder::findBySql($condition, [
            'user_id' => $this->user->id
        ]);

        $files = [];
        foreach ($message_folders as $folder) {
            $files = array_merge($files, $folder->getTypedFolder()->getFiles());
        }

        return $files;
    }


    /**
     * The magic get method is overwritten to be able to set a
     * custom value for the name attribute.
     */
    public function __get($attribute)
    {
        if ($attribute == 'name') {
            return _('Eingehende Dateianhänge');
        } else {
            return parent::__get($attribute);
        }
    }
}
