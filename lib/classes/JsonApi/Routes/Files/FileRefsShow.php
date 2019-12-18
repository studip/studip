<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class FileRefsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['file', 'owner', 'parent', 'range', 'terms-of-use'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$fileRef = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFileRef($this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($fileRef);
    }
}
