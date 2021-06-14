<?php
/**
 * StandardFile.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright 2020 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class StandardFile implements FileType, ArrayAccess
{

    /**
     * @var FileRef
     */
    protected $fileref = null;
    /**
     * @var File
     */
    protected $file = null;

    /**
     * Creates a prepared file with ids that could be added to a folder
     * @param array $data : ['name': "myfilename", 'type': "mime_type", 'size': "1234", 'tmp_name': "", 'error': null]
     * @param null|string $user_id : the id of the user that should own the new file
     * @return FileType|array : FileType (of called class) on success or an array with errors if an error occurred.
     */
    static public function create($data, $user_id = null)
    {
        $errors = [];
        $user_id || $user_id = $GLOBALS['user']->id;

        $filename   = $data['name'];
        $mime_type  = $data['type'] ?: get_mime_type($data['name']);
        $filesize   = $data['size'] ?: filesize($data['tmp_name']);
        $file_path  = $data['tmp_name'];
        $error_code = $data['error'];

        if ($error_code) {
            //error handling
            if ($error_code === UPLOAD_ERR_INI_SIZE) {
                $errors[] = sprintf(_('Die maximale Dateigröße von %s wurde überschritten!'), ini_get('upload_max_filesize'));
            } elseif ($error_code > 0) {
                $errors[] = sprintf(
                    _('Ein Systemfehler ist beim Upload aufgetreten. Fehlercode: %s.'),
                    $error_code
                );
            }
            return $errors;
        }

        $file = new File();
        $file['name']      = $filename;
        $file['mime_type'] = $mime_type;
        $file['filetype']  = get_called_class();
        $file['size']      = $filesize;
        $file['user_id']   = $user_id;
        $file->store();
        $file->connectWithDataFile($file_path);

        $fileref = new FileRef();
        $fileref['name']                    = $filename;
        $fileref['description']             = $data['description'] ?: "";
        $fileref['downloads']               = 0;
        $fileref['user_id']                 = $user_id;
        $fileref['file_id']                 = $file->getId();
        $fileref['content_terms_of_use_id'] = $data['content_terms_of_use_id'] ?: ContentTermsOfUse::findDefault()->id;

        return new static($fileref, $file);
    }

    /**
     * StandardFile constructor.
     * @param $fileref
     * @param null $file : (optional) Is set if fileref and file are both new and not connected with
     *                     each other in the database.
     */
    public function __construct($fileref, $file = null)
    {
        $this->fileref = $fileref;
        $this->file = $file ?: $fileref->file;
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
     * Returns the name of the icon shape that shall be used with the FileType implementation.
     *
     * @param string $role role of icon
     * @return Icon icon for the FileType implementation.
     */
    public function getIcon($role)
    {
        $shape = FileManager::getIconNameForMimeType($this->fileref['mime_type']);
        return Icon::create($shape, $role);
    }

    public function getId()
    {
        return $this->fileref->getId();
    }

    public function getFilename()
    {
        return $this->fileref['name'];
    }

    public function getUserId()
    {
        return $this->fileref['user_id'];
    }

    public function getUserName()
    {
        return $this->fileref['author_name'];
    }


    public function getUser()
    {
        return $this->fileref->owner;
    }

    public function getSize()
    {
        return $this->fileref['size'];
    }

    public function getMimeType()
    {
        return $this->fileref['mime_type'];
    }

    public function getDownloadURL()
    {
        return $this->fileref->getDownloadURL();
    }

    public function getDownloads()
    {
        return $this->fileref['downloads'];
    }


    public function getPath() : string
    {
        return $this->file instanceof File ? $this->file->getPath() : '';
    }


    public function getLastChangeDate()
    {
        return $this->fileref['chdate'];
    }

    public function getMakeDate()
    {
        return $this->fileref['mkdate'];
    }

    public function getDescription()
    {
        return $this->fileref['description'];
    }

    public function getTermsOfUse()
    {
        return $this->fileref->terms_of_use;
    }

    public function getActionmenu()
    {
        $actionMenu = ActionMenu::get();
        $actionMenu->addLink(
            URLHelper::getURL("dispatch.php/file/details/{$this->fileref->id}/1"),
            _('Info'),
            Icon::create('info-circle', Icon::ROLE_CLICKABLE, ['size' => 20]),
            ['data-dialog' => ''],
            'file-display-info'
        );
        if ($current_action === 'flat') {
            if (Navigation::hasItem('/course/files') && Navigation::getItem('/course/files')->isActive()) {
                $actionMenu->addLink(
                    URLHelper::getURL('dispatch.php/course/files/index/' . $this->fileref->folder_id),
                    _('Ordner öffnen'),
                    Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
                );
            } elseif (Navigation::hasItem('/files_dashboard/files') && Navigation::getItem('/files_dashboard/files')->isActive()) {
                $actionMenu->addLink(
                    URLHelper::getURL('dispatch.php/files/index/' . $this->fileref->folder_id),
                    _('Ordner öffnen'),
                    Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
                );
            }
        }
        if ($this->isEditable($GLOBALS['user']->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/edit/' . $this->fileref->id),
                _('Datei bearbeiten'),
                Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => ''],
                'file-edit'
            );
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/update/' . $this->fileref->id),
                _('Datei aktualisieren'),
                Icon::create('refresh', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => ''],
                'file-update'
            );
        }
        if ($this->isWritable($GLOBALS['user']->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/choose_destination/move/' . $this->fileref->id),
                _('Datei verschieben'),
                Icon::create('file+move_right', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => 'size=auto'],
                'file-move'
            );
        }
        if ($this->isDownloadable($GLOBALS['user']->id) && $GLOBALS['user']->id !== 'nobody') {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/choose_destination/copy/' . $this->fileref->id),
                _('Datei kopieren'),
                Icon::create('file+add', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => 'size=auto'],
                'file-copy'
            );
            $actionMenu->addLink(
                $this->fileref->getDownloadURL('force_download'),
                _('Link kopieren'),
                Icon::create('group'),
                ['class' => 'copyable-link'],
                'link-to-clipboard'
            );
        }
        if ($this->isEditable($GLOBALS['user']->id) && Config::get()->OERCAMPUS_ENABLED) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/share_oer/' . $this->fileref->id),
                _('Im OER Campus veröffentlichen'),
                Icon::create('service', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => '1']
            );
        }
        if (Context::isCourse() && Feedback::isActivated()) {
            if (Feedback::hasCreatePerm(Context::getId())) {
                $actionMenu->addLink(
                    URLHelper::getURL('dispatch.php/course/feedback/create_form/'. $this->fileref->id . '/FileRef'),
                    _('Neues Feedback-Element'),
                    Icon::create('star+add', Icon::ROLE_CLICKABLE, ['size' => 20]),
                    ['data-dialog' => '1']
                );
            }
        }
        if ($this->isWritable($GLOBALS['user']->id)) {
            $actionMenu->addButton(
                'delete',
                _('Datei löschen'),
                Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20]),
                [
                    'formaction'   => URLHelper::getURL("dispatch.php/file/delete/{$this->fileref->id}", $flat_view ? ['from_flat_view' => 1] : []),
                    'data-confirm' => sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), $this->fileref->name),
                ]
            );
        }
        NotificationCenter::postNotification("FileActionMenuWillRender", $actionMenu, $this);
        return $actionMenu;
    }


    public function getInfoDialogButtons(array $extra_link_params = []) : array
    {
        $buttons = [];
        if ($this->isEditable($GLOBALS['user']->id)) {
            $buttons[] = Studip\LinkButton::create(
                _('Bearbeiten'),
                URLHelper::getURL("dispatch.php/file/edit/{$this->getId()}", $extra_link_params),
                ['data-dialog' => '']
            );
        }
        if ($this->isDownloadable($GLOBALS['user']->id)) {
            $buttons[] = Studip\LinkButton::createDownload(
                _('Herunterladen'),
                $this->fileref->getDownloadURL('force_download')
            );
        }
        return $buttons;
    }


    public function delete()
    {
        return $this->fileref->delete();
    }

    public function getFolderType()
    {
        return $this->fileref->folder->getTypedFolder();
    }


    public function isVisible($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        return $this->getFolderType()->isReadable($user_id);
    }


    /**
     * Determines if a user may download the file.
     * @param string $user_id The user who wishes to download the file.
     * @return boolean True, if the user is permitted to download the file, false otherwise.
     */
    public function isDownloadable($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        return $this->getFolderType()->isFileDownloadable(
            $this->fileref->getId(),
            $user_id
        );
    }

    /**
     * Determines if a user may edit the file.
     * @param string $user_id The user who wishes to edit the file.
     * @return boolean True, if the user is permitted to edit the file, false otherwise.
     */
    public function isEditable($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        return $this->getFolderType()->isFileEditable(
            $this->fileref->getId(),
            $user_id
        );
    }

    /**
     * Determines if a user may write to the file.
     * @param string $user_id The user who wishes to write to the file.
     * @return boolean True, if the user is permitted to write to the file, false otherwise.
     */
    public function isWritable($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        return $this->getFolderType()->isFileWritable(
            $this->fileref->getId(),
            $user_id
        );
    }

    public function convertToStandardFile()
    {
        return $this;
    }

    public function addToFolder(FolderType $standard_folder, $filename, $user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        if ($user_id !== $this->file['user_id']) {
            $file = new File();
            $file->setData($this->file->toArray());
            $file->setId($file->getNewId());
            $file['user_id'] = $user_id;
            $file['filetype'] = get_called_class();
            $file->store();
            $file->connectWithDataFile($this->file->getPath());
        } else {
            $file = $this->file;
        }
        $fileref = new FileRef();
        $fileref->setData($this->fileref->toRawArray());
        $fileref->setId($fileref->getNewId());

        if ($file->isNew()) {
            $file->store();
        }
        $fileref['file_id'] = $file->getId();
        $fileref['folder_id'] = $standard_folder->getId();
        $fileref['user_id'] = $user_id;
        $fileref['downloads'] = 0;
        $fileref['name'] = $filename;
        if (!$fileref['content_terms_of_use_id']) {
            $fileref['content_terms_of_use_id'] = ContentTermsOfUse::findDefault()->id;
        }
        $fileref->store();
        return new static($fileref);
    }

    public function getFileRef()
    {
        return $this->fileref;
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


    public function getInfoTemplate(bool $include_downloadable_infos = false)
    {
        if (!$include_downloadable_infos) {
            return null;
        }
        $mime_type = $this->getMimeType();
        $relevant_mime_type = false;
        if (FileManager::fileIsImage($this) || FileManager::fileIsAudio($this)
            || FileManager::fileIsVideo($this) ||
            in_array($mime_type, ['application/pdf', 'text/plain'])) {
            $relevant_mime_type = true;
        }
        if (!$relevant_mime_type) {
            return null;
        }

        $factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/templates/filesystem/file_types/'
        );
        $template = $factory->open('standard_file_info');
        $template->set_attribute('mime_type', $mime_type);
        $template->set_attribute('file', $this);
        return $template;
    }
}
