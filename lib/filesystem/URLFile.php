<?php

class URLFile extends StandardFile
{

    /**
     * @param array $data : ['name' => "myfile", 'access_type': "redirect|proxy", 'url': ""]
     * @param string|null $user_id : user-id of the user who should be owner of that file
     * @return array|FileType : the new File, which is already stored but not attached to a folder.
     */
    static public function create($data, $user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        $meta = FileManager::fetchURLMetadata($data['url']);

        $file = new File();
        $file['name'] = $data['name'] ?: ($meta['filename'] ?: 'unknown');
        $file['size'] = $meta['Content-Length'] ?: '0';
        $file['mime_type'] = $meta['Content-Type'] ? mb_strstr($meta['Content-Type'], ';', true) : get_mime_type($file['name']);
        $file['metadata'] = [
            'url' => $data['url'],
            'access_type' => $data['access_type'] ?: "redirect"
        ];
        $file['user_id'] = $user_id;
        $file['author_name'] = $data['author_name'] ?: get_fullname($file['user_id']);
        $file['filetype'] = get_called_class();
        $file->store();

        $fileref = new FileRef();
        $fileref['file_id'] = $file->getId();
        $fileref['name'] = $file['name'];
        $fileref['downloads'] = 0;
        $fileref['description'] = $data['description'] ?: "";
        $fileref['content_terms_of_use_id'] = $data['content_terms_of_use_id'] ?: ContentTermsOfUse::findDefault()->id;
        $fileref['user_id'] = $user_id;

        return new static($fileref);
    }

    /**
     * Returns the name of the icon shape that shall be used with the FileType implementation.
     *
     * @param string $role role of icon
     * @return Icon icon for the FileType implementation.
     */
    public function getIcon($role)
    {
        return Icon::create("link-extern", $role);
    }

    public function getFilename()
    {
        return $this->fileref['name'];
    }

    public function getSize()
    {
        return $this->fileref['size'] ?: null;
    }

    public function getMimeType()
    {
        return $this->fileref['mime_type'];
    }

    public function getDownloadURL()
    {
        return $this->fileref->getDownloadURL();
    }


    public function getPath() : string
    {
        return '';
    }


    public function getActionmenu()
    {
        $actionMenu = parent::getActionmenu();
        $actionMenu->addLink(
            URLHelper::getURL('dispatch.php/file/edit_urlfile/' . $this->fileref->id),
            _('Datei bearbeiten'),
            Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
            ['data-dialog' => ''],
            'file-edit'
        );
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
            $buttons[] = Studip\LinkButton::create(
                _('Öffnen'),
                $this->getDownloadURL(),
                ['target' => '_blank']
            );
        }

        return $buttons;
    }


    public function getInfoTemplate(bool $include_downloadable_infos = false)
    {
        return null;
    }
}
