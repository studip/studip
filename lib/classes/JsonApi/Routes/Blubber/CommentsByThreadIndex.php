<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays all comments of a certain blubber thread.
 */
class CommentsByThreadIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'mentions', 'thread'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($thread = \BlubberThread::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlubberThread($this->getUser($request), $thread)) {
            throw new AuthorizationFailedException();
        }

        $comments = $thread->comments;
        list($offset, $limit) = $this->getOffsetAndLimit();
        $total = count($comments);

        return $this->getPaginatedContentResponse($comments->limit($offset, $limit), $total);
    }
}
