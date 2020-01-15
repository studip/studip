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
class CommentsDelete extends JsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$comment = \StudipComment::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeleteComment($user = $this->getUser($request), $comment)) {
            throw new AuthorizationFailedException();
        }

        if (!$this->deleteComment($comment)) {
            throw new RecordNotFoundException();
        }

        return $this->getCodeResponse(204);
    }

    protected function deleteComment(\StudipComment $comment)
    {
        return $comment->delete();
    }
}
