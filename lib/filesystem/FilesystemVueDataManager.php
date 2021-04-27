<?php

/**
 * Class FilesystemVueDataManager is a manager that provides for a file or a folder
 * the data that the vue component FilesTable needs to display the file or folder.
 */
class FilesystemVueDataManager
{
    /**
     * Provides for a file the data that the vue component FilesTable needs
     * to display that file. Data is provides as an array that can be easily
     * transferred into a JSON-object.
     * @param FileType $file : the file that should be displayed
     * @param FolderType $topFolder : The top-folder of that file
     * @return array of data
     */
    public static function getFileVueData(FileType $file, FolderType $topFolder, $last_visitdate = null)
    {
        $isDownloadable = $file->isDownloadable($GLOBALS['user']->id);
        $terms = $file->getTermsOfUse();
        $additionalColumns = [];
        foreach ((array) $topFolder->getAdditionalColumns() as $index => $name) {
            $additionalColumns[$index] = [
                'html' => (string) $file->getContentForAdditionalColumn($index),
                'order' => $file->getAdditionalColumnOrderWeigh($index)
            ];
        }
        $actionMenu = $file->getActionMenu();
        return [
            'id' => $file->getId(),
            'name' => $file->getFilename(),
            'download_url' => $isDownloadable ? $file->getDownloadURL() : null,
            'downloads' => $file->getDownloads(),
            'mime_type' => $file->getMimeType(),
            'icon' => $file->getIcon($isDownloadable ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO)->getShape(),
            'size' => $file->getSize(),
            'author_url' => $file->getUser() && $file->getUserId() !== $GLOBALS['user']->id ? URLHelper::getURL('dispatch.php/profile', ['username' => $file->getUser()->username], true) : "",
            'author_name' => $file->getUserName(),
            'author_id' => $file->getUserId(),
            'chdate' => (int) $file->getLastChangeDate(),
            'additionalColumns' => $additionalColumns,
            'details_url' => URLhelper::getURL("dispatch.php/file/details/{$file->getId()}", ['file_navigation' => '1']),
            'restrictedTermsOfUse' => $terms && !$terms->isDownloadable($topFolder->range_id, $topFolder->range_type, false),
            'actions' => $actionMenu ? (is_string($actionMenu) ? $actionMenu : $actionMenu->render()) : "",
            'new' => isset($last_visitdate) && $file->getLastChangeDate() > $last_visitdate && $file->getUserId() !== $GLOBALS['user']->id,
            'isEditable' => $file->isEditable(),
        ];
    }

    /**
     * Provides for a folder the data that the vue component FilesTable needs
     * to display that folder. Data is provides as an array that can be easily
     * transferred into a JSON-object.
     * @param FolderType $folder : the folder that should be displayed
     * @param FolderType $topFolder : The top-folder of that file
     * @return array of data
     */
    public static function getFolderVueData(FolderType $folder, FolderType $topFolder)
    {
        $actionMenu = ActionMenu::get();
        $actionMenu->addLink(
            URLHelper::getURL('dispatch.php/file/details/' . $folder->getId()),
            _('Info'),
            Icon::create('info-circle', 'clickable', ['size' => 20]),
            ['data-dialog' => '1']
        );
        if ($folder->isEditable($GLOBALS['user']->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/edit_folder/' . $folder->getId()),
                _('Ordner bearbeiten'),
                Icon::create('edit', 'clickable', ['size' => 20]),
                ['data-dialog' => '1']
            );
        }
        if ($folder->isReadable($GLOBALS['user']->id) && $GLOBALS['user']->id !== 'nobody') {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/download_folder/' . $folder->getId()),
                _('Ordner herunterladen'),
                Icon::create('download', 'clickable', ['size' => 20])
            );
        }
        if ($folder->isEditable($GLOBALS['user']->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/choose_destination/move/' . $folder->getId(), ['isfolder' => 1]),
                _('Ordner verschieben'),
                Icon::create('folder-empty+move_right', 'clickable', ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/choose_destination/copy/' . $folder->getId(), ['isfolder' => 1]),
                _('Ordner kopieren'),
                Icon::create('folder-empty+add', 'clickable', ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/delete_folder/' . $folder->getId()),
                _('Ordner löschen'),
                Icon::create('trash', 'clickable', ['size' => 20]),
                ['onclick' => "return STUDIP.Dialog.confirmAsPost('" . sprintf(_('Soll der Ordner "%s" wirklich gelöscht werden?'), htmlReady($folder->name)) . "', this.href);"]
            );
        }

        $permissions = "";
        if ($folder->isReadable($GLOBALS['user']->id)) {
            $permissions .= 'r';
        }
        if ($folder->isEditable($GLOBALS['user']->id)) {
            $permissions .= 'w';
        }
        if ($folder->isReadable($GLOBALS['user']->id)) {
            $permissions .= 'd';
        }

        $additionalColumns = [];
        foreach ($topFolder->getAdditionalColumns() as $index => $name) {
            $additionalColumns[$index] = [
                'html' => (string) $folder->getContentForAdditionalColumn($index),
                'order' => $folder->getAdditionalColumnOrderWeigh($index)
            ];
        }

        $controllerpath = 'files/index';
        if (is_numeric($topFolder->range_type)) {
            //plugin:
            $controllerpath = 'files/system/' . $topFolder->range_type;
        } elseif ($topFolder->range_type !== 'user') {
            $controllerpath = $topFolder->range_type . '/' . $controllerpath;
        }

        $vue_folder = [
            'id' => $folder->getId(),
            'icon' => $folder->getIcon(Icon::ROLE_CLICKABLE)->getShape(),
            'name' => $folder->name,
            'url' => URLhelper::getURL('dispatch.php/' . $controllerpath . '/' . $folder->getId()),
            'user_id' => $folder->user_id,
            'author_name' => $folder->owner ? $folder->owner->getFullname('no_title_rev') : '',
            'author_url' => $folder->owner && $folder->owner->id !== $GLOBALS['user']->id? URLHelper::getURL('dispatch.php/profile', ['username' => $folder->owner->username]) : '',
            'chdate' => (int) $folder->chdate,
            'actions' => $actionMenu->render(),
            'mime_type' => get_class($folder),
            'permissions' => $permissions,
            'additionalColumns' => $additionalColumns
        ];
        return $vue_folder;
    }
}
