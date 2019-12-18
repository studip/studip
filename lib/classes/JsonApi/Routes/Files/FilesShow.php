<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class FilesShow extends JsonApiController
{
    protected $allowedIncludePaths = ['file-refs', 'owner'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$file = \File::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFile($this->getUser($request), $file)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($file);
    }
}
