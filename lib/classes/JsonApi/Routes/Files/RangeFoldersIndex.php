<?php

namespace JsonApi\Routes\Files;

class RangeFoldersIndex extends AbstractRangeIndex
{
    protected $allowedIncludePaths = ['owner', 'parent', 'range', 'folders', 'file-refs'];

    protected function getRangeResources(\User $user, \SimpleORMap $resource)
    {
        $rootFolder = \Folder::findTopFolder($resource->id)->getTypedFolder();

        return self::getFolderRecursive($rootFolder, $user);
    }
}
