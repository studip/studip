<?php

namespace FilesSearch;

use NotificationCenter;

/**
 * This class observes changes in file refs and folders and re-indexes
 * if applicable.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class NotificationObserver
{
    /**
     * Observe every change in files, file refs and folders.
     */
    public static function initialize()
    {
        NotificationCenter::addObserver(self::class, 'observeFileRef', 'FileRefDidCreate');
        NotificationCenter::addObserver(self::class, 'observeFileRef', 'FileRefDidDelete');
        NotificationCenter::addObserver(self::class, 'observeFileRef', 'FileRefDidUpdate');

        NotificationCenter::addObserver(self::class, 'observeFolder', 'FolderDidCreate');
        NotificationCenter::addObserver(self::class, 'observeFolder', 'FolderDidDelete');
        NotificationCenter::addObserver(self::class, 'observeFolder', 'FolderDidUpdate');
    }

    /**
     * Observe changes of FileRefs. Depending on the event either
     * create, drop or update the index.
     *
     * @param FileRef $fileRef  the observed file
     */
    public static function observeFileRef($event, \FileRef $fileRef)
    {
        switch ($event) {
            case 'FileRefDidCreate':
                FilesIndexManager::indexFile($fileRef);
                break;

            case 'FileRefDidDelete':
                FilesIndexManager::dropIndexForFile($fileRef);
                break;

            case 'FileRefDidUpdate':
                if (self::isFileRefStale($fileRef)) {
                    FilesIndexManager::dropIndexForFile($fileRef);
                    FilesIndexManager::indexFile($fileRef);
                }
                break;
        }
    }

    /**
     * Observe changes of Folders. Depending on the event either
     * create, drop or update the indexes.
     *
     * @param Folder $folder  the observed folder
     */
    public static function observeFolder($event, \Folder $folder)
    {
        switch ($event) {
            case 'FolderDidCreate':
                FilesIndexManager::indexFolder($folder);
                break;

            case 'FolderDidDelete':
                FilesIndexManager::dropIndexForFolder($folder);
                break;

            case 'FolderDidUpdate':
                if (self::isFolderStale($folder)) {
                    FilesIndexManager::dropIndexForFolder($folder);
                    FilesIndexManager::indexFolder($folder);
                }
                break;
        }
    }

    private static function isFileRefStale(\FileRef $fileRef)
    {
        return self::isStale($fileRef, ['user_id', 'mkdate', 'chdate', 'folder_id', 'name', 'description']);
    }

    private static function isFolderStale(\Folder $folder)
    {
        return self::isStale($folder, ['range_id', 'range_type', 'folder_type']);
    }

    private static function isStale(\SimpleORMap $resource, array $fields)
    {
        foreach ($fields as $field) {
            if ($resource->isFieldDirty($field)) {
                return true;
            }
        }
        return false;
    }
}
