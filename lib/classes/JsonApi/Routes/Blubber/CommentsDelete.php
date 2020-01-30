<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Deletes a blubber comment.
 */
class CommentsDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($comment = \BlubberComment::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteComment($user = $this->getUser($request), $comment)) {
            throw new AuthorizationFailedException();
        }

        if ($comment['user_id'] !== $user->id) {
            $this->sendMessage($user, $comment);
        }

        $comment->delete();

        return $this->getCodeResponse(204);
    }

    private function sendMessage(\User $user, \BlubberComment $comment)
    {
        $messaging = new \messaging();
        $messaging->insert_message(
            sprintf(
            _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum GELÖSCHT.\n\nDer alte Beitrag lautete:\n\n%s\n"),
            get_fullname($user->id),
            $comment['content']
            ),
            get_username($comment['user_id']),
            $user->id,
            null,
            null,
            null,
            null,
            _('Ihr Posting wurde gelöscht.')
        );
    }
}
