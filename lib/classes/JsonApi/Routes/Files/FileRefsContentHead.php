<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileRefsContentHead extends NonJsonApiController
{
    use EtagHelperTrait;

    public function invoke(Request $request, Response $response, $args)
    {
        if (!$fileRef = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDownloadFileRef($this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        list(, $response) = $this->handleEtag($request, $response, $fileRef, true);

        return $response;
    }
}
