<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays a certain blubber comment.
 */
class CommentsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'mentions', 'thread'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = \BlubberComment::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlubberThread($this->getUser($request), $resource->thread)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
