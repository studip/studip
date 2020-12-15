<?php
/**
 * UnknownFileType.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2020 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */

class UnknownFileType implements FileType, ArrayAccess
{
    /**
     * @var FileRef
     */
    protected $fileref = null;

    public function __construct($fileref = null)
    {
        $this->fileref = $fileref;
    }

    /**
     * magic method for dynamic properties
     */
    public function __get($name)
    {
        return isset($this->fileref->$name) ? $this->fileref->$name : null;
    }

    /**
     * magic method for dynamic properties
     */
    public function __set($name, $value)
    {
        return isset($this->fileref->$name) ? $this->fileref->$name = $value : null;
    }

    /**
     * magic method for dynamic properties
     */
    public function __isset($name)
    {
        return isset($this->fileref->$name);
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess: unset the value at the given offset (not applicable)
     */
    public function offsetUnset($offset)
    {

    }
    /**
     * @inheritDoc
     */
    public function getIcon($role)
    {
        return Icon::create('file', $role);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getFilename()
    {
        return trim($this->name . ' (' . _('Unbekannter Dateityp') .  ')');
    }

    /**
     * @inheritDoc
     */
    public function getUserId()
    {
    }

    /**
     * @inheritDoc
     */
    public function getUserName()
    {
    }

    /**
     * @inheritDoc
     */
    public function getUser()
    {
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
    }

    /**
     * @inheritDoc
     */
    public function getDownloadURL()
    {
    }

    /**
     * @inheritDoc
     */
    public function getDownloads()
    {
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getLastChangeDate()
    {
    }

    /**
     * @inheritDoc
     */
    public function getMakeDate()
    {
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
    }

    /**
     * @inheritDoc
     */
    public function getMimeType()
    {
        return 'application/octet-stream';
    }

    /**
     * @inheritDoc
     */
    public function getTermsOfUse()
    {
        return new ContentTermsOfUse();
    }

    /**
     * @inheritDoc
     */
    public function getActionmenu()
    {
    }

    /**
     * @inheritDoc
     */
    public function getInfoDialogButtons(array $extra_link_params = []): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
    }

    /**
     * @inheritDoc
     */
    public function getFolderType()
    {
        return $this->folder ? $this->folder->getTypedFolder() : null;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($user_id = null)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isDownloadable($user_id = null)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isEditable($user_id = null)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isWritable($user_id = null)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function convertToStandardFile()
    {
        throw new RuntimeException('object of type ' . __CLASS__ . ' could not be converted to StandardFile');
    }

    /**
     * @inheritDoc
     */
    public function getContentForAdditionalColumn($column_index)
    {
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalColumnOrderWeigh($column_index)
    {
    }

    /**
     * @inheritDoc
     */
    public function getInfoTemplate(bool $include_downloadable_infos = false)
    {
    }
}
