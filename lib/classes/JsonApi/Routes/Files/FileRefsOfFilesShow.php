<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class FileRefsOfFilesShow extends JsonApiController
{
    protected $allowedIncludePaths = ['file', 'owner', 'parent', 'range', 'terms-of-use'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$file = \File::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFile($user = $this->getUser($request), $file)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse(
            $file->refs->filter(
                function ($ref) use ($user) {
                    return Authority::canShowFileRef($user, $ref);
                }
            )
        );
    }
}
