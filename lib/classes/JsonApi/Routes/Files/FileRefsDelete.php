<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class FileRefsDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$fileRef = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteFileRef($this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        $folder = $fileRef->foldertype;
        if (!$folder || !$folder->deleteFile($fileRef->id)) {
            throw new InternalServerError('Could not delete file-ref.');
        }

        return $this->getCodeResponse(204);
    }
}
