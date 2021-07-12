<?php
/**
 * This file is part of Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2020
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.6
 */


class LibraryFile extends StandardFile
{
    public $library_document = null;


    public function __construct($fileref, $file = null)
    {
        parent::__construct($fileref, $file);

        if (is_object($this->file->metadata)) {
            $this->library_document = LibraryDocument::createFromArray($this->file->metadata->getArrayCopy());
        }
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFileRef()
    {
        return $this->fileref;
    }

    public static function createFromLibraryDocument(LibraryDocument $document, $folder_id = null, $user_id = null)
    {
        $file = new File();
        $file->name = '';
        $file->size = '0';
        $file->mime_type = '';
        $file->metadata = $document->toJson();
        $file->user_id = $user_id ? $user_id : $GLOBALS['user']->id;
        $file->filetype = get_called_class();
        if ($document->csl_data['URL'] || $document->opac_link) {
            $file->metadata['url'] = $document->opac_link ?: $document->csl_data['URL'];
            $file->metadata['access_type'] = 'redirect';
        }
        $file->store();

        $file_ref = new FileRef();
        $file_ref->file_id = $file->id;
        $file_ref->folder_id = $folder_id ? $folder_id : '';
        $file_ref->name = $file->name;
        $file_ref->downloads = 0;
        $file_ref->description = $document->csl_data['description'] ? $document->csl_data['description'] : '';
        $file_ref->content_terms_of_use_id = ContentTermsOfUse::findDefault()->id;
        $file_ref->user_id = $file->user_id;
        $file_ref->store();

        return new LibraryFile($file_ref);
    }


    /**
     * Updates the LibraryFile by using a LibraryDocument instance.
     *
     * @param LibraryDocument $document The document containing the new data.
     *
     * @returns bool True, if the update was successful, false otherwise.
     */
    public function updateFromLibraryDocument(LibraryDocument $document) : bool
    {
        if ($this->file instanceof File) {
            $file_name = self::getFileNameFromDocument($document);
            $this->file->name = $file_name;
            $this->file->metadata = $document->toJson();
            if ($this->file->isDirty()) {
                if (!$this->file->store()) {
                    return false;
                }
            }
            $this->fileref->name = $this->file->name;
            if ($this->fileref->isDirty()) {
                return $this->fileref->store();
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * Extracts a file name from a LibraryDocument instance.
     * This is an internal helper method.
     *
     * @param LibraryDocument $document The document from which a file name
     *     shall be extracted.
     *
     * @returns string The extracted file name.
     */
    protected static function getFileNameFromDocument(LibraryDocument $document) : string
    {
        $file_name = $document->getTitle();
        if (!$file_name) {
            //The document hasn't got a title. We can still generate a file name
            //using the search parameters of the document.
            if ($document->search_params[LibrarySearch::TITLE]) {
                $file_name = $document->search_params[LibrarySearch::TITLE];
            } elseif ($document->search_params[LibrarySearch::AUTHOR]) {
                if ($document->search_params[LibrarySearch::YEAR]) {
                    $file_name = sprintf(
                        '%1$s (%2$s)',
                        $document->search_params[LibrarySearch::AUTHOR],
                        $document->search_params[LibrarySearch::YEAR]
                    );
                } else {
                    $file_name = $document->search_params[LibrarySearch::AUTHOR];
                }
            } elseif ($document->search_params[LibrarySearch::NUMBER]) {
                $file_name = $document->search_params[LibrarySearch::NUMBER];
            } elseif ($document->search_params[LibrarySearch::SIGNATURE]) {
                $file_name = $document->search_params[LibrarySearch::SIGNATURE];
            } else {
                $file_name = _('unbekannt');
            }
        }
        return $file_name;
    }


    public function getIcon($role)
    {
        if ($this->library_document instanceof LibraryDocument) {
            $icon = $this->library_document->getIcon();
        }
        return $icon && $icon->getShape() !== 'literature-request' ? $icon : Icon::create('literature', $role);
    }


    public function getFilename()
    {
        $file_name = '';
        if ($this->library_document instanceof LibraryDocument) {
            $file_name = $this->library_document->getTitle('long');
        }
        if (!$file_name && $this->library_document->search_params) {
            $formatted_params = $this->library_document->getSearchDescription();
            $file_name = _('Suche in der Bibliothek');
            if ($formatted_params) {
                $file_name .= ': ' . implode(', ', $formatted_params);
            }
        }
        if (!$file_name && ($this->fileref instanceof FileRef)) {
            $file_name = $this->fileref->name;
        }
        return $file_name;
    }


    public function getSize()
    {
        if ($this->fileref instanceof FileRef) {
            return $this->fileref['size'];
        }
        return null;
    }


    public function getDownloadURL()
    {
        if ($this->hasFileAttached() || $this->hasURL()) {
            if ($this->fileref instanceof FileRef) {
                return $this->fileref->getDownloadURL();
            }
        }
        return '';
    }


    public function getPath() : string
    {
        return $this->file instanceof File ? $this->file->getPath() : '';
    }


    public function getDownloads()
    {
        if ($this->fileref instanceof FileRef) {
            return $this->fileref['downloads'];
        }
        return 0;
    }


    public function getActionmenu()
    {
        $action_menu = ActionMenu::get();
        $action_menu->addLink(
            URLHelper::getURL(sprintf('dispatch.php/file/details/%s/1', $this->fileref->id)),
            _('Info'),
            Icon::create('info-circle', Icon::ROLE_CLICKABLE, ['size' => 20]),
            ['data-dialog' => ''],
            'file-display-info'
        );
        if (Config::get()->LITERATURE_ENABLE && Context::get() && $GLOBALS['perm']->have_studip_perm('tutor', Context::getId())) {
            $plugin_manager = PluginManager::getInstance();
            $library_plugins = $plugin_manager->getPlugins('LibraryPlugin');
            if (count($library_plugins)) {
                $plugin = $library_plugins[0];
                $action_menu->addLink(
                    $plugin->getRequestURL($this->getId()),
                    $plugin->getRequestTitle(),
                    $plugin->getRequestIcon(),
                    ['data-dialog' => 'size=auto;reload-on-close;id=' . $plugin->getPluginId()]
                );
            }
        }
        if (parent::isWritable($GLOBALS['user']->id)) {
            //Before the edit action can be displayed, we must make sure
            //the library document is one that is defined.
            $known_document_type = false;
            foreach ($GLOBALS['LIBRARY_DOCUMENT_TYPES'] as $defined_type) {
                if ($defined_type['name'] == $this->library_document->type) {
                    $known_document_type = true;
                    break;
                }
            }
            if ($known_document_type) {
                $action_menu->addLink(
                    URLHelper::getURL('dispatch.php/library_file/edit/' . $this->fileref->id),
                    _('Bearbeiten'),
                    Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
                    ['data-dialog' => 'size=auto']
                );
            }
            $action_menu->addButton(
                'delete',
                _('Datei löschen'),
                Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20]),
                [
                    'formaction'   => URLHelper::getURL(
                        sprintf('dispatch.php/file/delete/%s', $this->fileref->id),
                        $flat_view ? ['from_flat_view' => 1] : []
                    ),
                    'data-confirm' => sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), $this->fileref->name),
                ]
            );
        }
        return $action_menu;
    }


    public function getInfoDialogButtons(array $extra_link_params = []) : array
    {
        $buttons = [];
        if ($this->isEditable($GLOBALS['user']->id)) {
            $known_document_type = false;
            foreach ($GLOBALS['LIBRARY_DOCUMENT_TYPES'] as $defined_type) {
                if ($defined_type['name'] == $this->library_document->type) {
                    $known_document_type = true;
                    break;
                }
            }
            if ($known_document_type) {
                $buttons[] = Studip\LinkButton::create(
                    _('Bearbeiten'),
                    URLHelper::getURL('dispatch.php/library_file/edit/' . $this->fileref->id),
                    ['data-dialog' => 'size=auto']
                );
            }
        }

        if ($this->isDownloadable($GLOBALS['user']->id) && $this->getDownloadURL()) {
            $buttons[] = Studip\LinkButton::create(
                $this->hasFileAttached() ? _('Herunterladen') : _('Öffnen'),
                $this->getDownloadURL(),
                ['target' => '_blank']
            );
        }

        return $buttons;
    }


    public function isEditable($user_id = null)
    {
        if ($this->fileref instanceof FileRef) {
            return parent::isEditable($user_id);
        }
        return false;
    }


    public function getInfoTemplate(bool $include_downloadable_infos = false)
    {
        if (!$this->library_document) {
            return null;
        }
        return $this->library_document->getInfoTemplate('full');
    }


    /**
     * This is a method special to the LibraryFile class.
     * It handles the attachment of a (real) File object to this LibraryFile.
     * In that case, the library metadata must be transferred from the (virtual)
     * file that is attached to the LibraryFile to the specified file that
     * shall be attached.
     *
     * @param File $file The file to attach to this LibraryFile.
     *
     * @throws Exception In case of an error, an exception with an error message
     *     is thrown.
     */
    public function attachFile(File $file) : bool
    {
        if ($file->isNew()) {
            //The file must be stored in the database.
            if (!$file->store()) {
                throw new Exception(_('Fehler beim Speichern der Datei!'));
            }
        }
        if ($this->fileref instanceof FileRef) {
            $old_file = $this->fileref->file;
            if (!($old_file instanceof File)) {
                throw new Exception(_('Es gibt kein Dateiobjekt zum Bibliothekseintrag!'));
            }
            if ($old_file->id == $file->id) {
                //The file is already attached.
                return true;
            }
            $metadata = $old_file->metadata;
            if (!$metadata) {
                //Something went wrong.
                throw new Exception(_('Das Dateiobjekt zum Bibliothekseintrag hat keine Metadaten!'));
            }
            $file->metadata = $metadata;
            $file->filetype = get_class($this);
            if ($file->isDirty()) {
                if (!$file->store()) {
                    throw new Exception(_('Die neue Datei konnte nicht gespeichert werden!'));
                }
            }
            $this->fileref->file_id = $file->id;
            $this->fileref->name = $file->name;
            if ($this->fileref->isDirty()) {
                if ($this->fileref->store()) {
                    $old_file->delete();
                } else {
                    throw new Exception(_('Die Verknüfpung der Datei mit dem Bibliothekseintrag konnte nicht gespeichert werden!'));
                }
            }
            $this->file = $file;
        } else {
            throw new Exception(_('Der Bibliothekseintrag ist nicht mit einer virtuellen Datei verknüpft!'));
        }

        return true;
    }


    /**
     * This method checks if a real file is attached to this library file.
     *
     * @returns bool True, if a real file is attached, false otherwise.
     */
    public function hasFileAttached() : bool
    {
        return file_exists($this->file->path);
    }

    public function hasURL()
    {
        if (isset($this->file->metadata['csl_data']['URL']) && !isset($this->file->metadata['url'])) {
            $this->file->metadata['url'] = $this->file->metadata['csl_data']['URL'];
            $this->file->metadata['access_type'] = 'redirect';
            $this->file->store();
        }
        return isset($this->file->metadata['url']);
    }

    public function removeDataFile()
    {
        if ($this->hasFileAttached()) {
            $this->file->deleteDataFile();
            $this->file->mime_type = '';
            $this->file->name = '';
            $this->file->size = 0;
            $this->file->metadata['url'] = null;
            $this->file->metadata['access_type'] = null;
            $this->fileref->name = '';
            $this->fileref->store();
            return $this->file->store();
        }
    }
}
