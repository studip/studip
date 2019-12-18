<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

abstract class AbstractRangeIndex extends JsonApiController
{
    use RangeHelperTrait;

    protected $allowedPagingParameters = ['offset', 'limit'];

    abstract protected function getRangeResources(\User $user, \SimpleORMap $resource);

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$this->validateResourceType($args['type'])) {
            throw new BadRequestException('Bad resource type.');
        }

        if (!$resource = $this->findResource($args['type'], $args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!$this->authorizeUser($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        $files = $this->getRangeResources($user, $resource);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($files, $offset, $limit),
            count($files)
        );
    }

    private function authorizeUser(\User $user, \SimpleORMap $resource)
    {
        if (!Authority::canShowFileArea($user, $resource)) {
            return false;
        }

        switch (get_class($resource)) {
            case 'Course':
                return Authority::canIndexCourse($user, $resource);

            case 'Institute':
                return Authority::canIndexInstitute($user, $resource);

            case 'User':
                return Authority::canIndexUser($user, $resource);
        }

        return false;
    }

    public static function getFolderRecursive(
        \FolderType $topFolder,
        \User $user
    ) {
        $userId = $user->id;
        $folders = [];
        $arrayWalker = function ($topFolder) use (&$arrayWalker, &$folders, $userId) {
            if ($topFolder->isVisible($userId)) {
                $folders[$topFolder->getId()] = $topFolder;
                if ($topFolder->isReadable($userId)) {
                    array_walk($topFolder->getSubFolders(), $arrayWalker);
                }
            }
        };

        $topFolders = [$topFolder];
        array_walk($topFolders, $arrayWalker);

        return $folders;
    }
}
