<?php

namespace Widgets\FilesDashboard;

require_once 'BaseWidget.php';

/**
 * This widget shows a users public files.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class MyPublicFilesWidget extends BaseWidget
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return _('Meine öffentlichen Dateien');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return _('Zeigt alle freigegebenen Dateien des eigenen Dateibereichs an.');
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return _('Meine öffentlichen Dateien');
    }

    /**
     * This is a singleton widget - use at most once per container.
     *
     * {@inheritdoc}
     */
    public function mayBeDuplicated()
    {
        return false;
    }

    /**
    * This is a singleton widget - use at most once per container.
    *
    * {@inheritdoc}
     */
    public function mayBeRemoved()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getFilesAndFolders(\Range $range, $scope)
    {
        $condition = 'range_type="user" AND range_id = ? AND folder_type = "PublicFolder"';
        $folders = \Folder::findBySQL($condition, [$range->id]);

        $publicFiles = [];
        $publicFolders = [];
        foreach ($folders as $folder) {
            $onePublicFolder = $folder->getTypedFolder();
            if ($onePublicFolder->isVisible($GLOBALS['user']->id)) {
                $allFiles = \FileManager::getFolderFilesRecursive($onePublicFolder, $GLOBALS['user']->id);
                $publicFiles = array_merge($publicFiles, $allFiles['files']);
                $publicFolders = array_merge($publicFolders, $allFiles['folders']);
            }
        }

        return ['files' => $publicFiles, 'folders' => $publicFolders];
    }
}
