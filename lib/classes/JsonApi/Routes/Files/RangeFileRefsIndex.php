<?php

namespace JsonApi\Routes\Files;

class RangeFileRefsIndex extends AbstractRangeIndex
{
    protected $allowedIncludePaths = ['file', 'owner', 'parent', 'range', 'terms-of-use'];

    protected function getRangeResources(\User $user, \SimpleORMap $resource)
    {
        $rootFolder = \Folder::findTopFolder($resource->id)->getTypedFolder();
        $filesAndFolders = \FileManager::getFolderFilesRecursive($rootFolder, $user->id, true);

        return $filesAndFolders['files'];
    }
}
