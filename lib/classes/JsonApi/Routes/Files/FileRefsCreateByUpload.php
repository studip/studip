<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileRefsCreateByUpload extends NonJsonApiController
{
    use RoutesHelperTrait;

    public function invoke(Request $request, Response $response, $args)
    {
        if (!$folder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateFileRefsInFolder($this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        $fileRef = $this->handleUpload($request, $folder);

        return $this->redirectToFileRef($response, $fileRef);
    }
}
