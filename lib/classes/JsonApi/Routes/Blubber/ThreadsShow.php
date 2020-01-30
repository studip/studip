<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays a certain blubber thread.
 */
class ThreadsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'comments', 'context', 'mentions'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = \BlubberThread::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlubberThread($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
