<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class FoldersShow extends JsonApiController
{
    protected $allowedIncludePaths = ['owner', 'parent', 'range', 'folders', 'file-refs'];

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

        return $this->getContentResponse($folder);
    }
}
