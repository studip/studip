<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class SubfoldersIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['owner', 'parent', 'range', 'folders', 'file-refs'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$folder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFolder($this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        $subfolders = array_map(
            function ($subfolder) {
                return $subfolder->getTypedFolder();
            },
            $folder->subfolders->getArrayCopy()
        );
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($subfolders, $offset, $limit),
            count($subfolders)
        );
    }
}
