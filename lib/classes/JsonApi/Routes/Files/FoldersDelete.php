<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class FoldersDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$folder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteFolder($this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        if (!$folder->delete()) {
            throw new InternalServerError('Could not delete folder.');
        }

        return $this->getCodeResponse(204);
    }
}
