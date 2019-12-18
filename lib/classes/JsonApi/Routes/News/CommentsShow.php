<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Löscht eine Studip-Nachricht.
 */
class CommentsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'news'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$comment = \StudipComment::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowNews($this->getUser($request), $comment->news)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($comment);
    }
}
