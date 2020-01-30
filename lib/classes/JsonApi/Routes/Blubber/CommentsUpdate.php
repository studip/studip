<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

/**
 * Update a blubber comment.
 */
class CommentsUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        if (!($comment = \BlubberComment::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canEditComment($user = $this->getUser($request), $comment)) {
            throw new AuthorizationFailedException();
        }

        $oldContent = $comment['content'];
        $newContent = self::arrayGet($json, 'data.attributes.content');
        $comment['content'] = $newContent;

        if ($comment['user_id'] !== $user->id) {
            $this->sendMessage($user, $comment, $oldContent, $newContent);
        }

        $comment->store();

        return $this->getCodeResponse(204);
    }

    protected function validateResourceDocument($json)
    {
        if (empty(self::arrayGet($json, 'data.attributes.content'))) {
            return 'Comment should not be empty.';
        }
    }

    private function sendMessage(\User $user, \BlubberComment $comment, $oldContent, $newContent)
    {
        $messaging = new \messaging();
        $message = sprintf(
            _(
                "%s hat als Moderator gerade Ihren Beitrag in Blubber editiert.\n\nDie alte Version des Beitrags lautete:\n\n%s\n\nDie neue lautet:\n\n%s\n"
            ),
            get_fullname($user->id),
            $oldContent,
            $newContent
        );

        $message .= "\n\n";
        $message .= '[' . _('Link zu diesem Beitrag') . ']';
        $message .= $this->createLink($comment);

        $messaging->insert_message(
            $message,
            get_username($comment['user_id']),
            $user->id,
            null,
            null,
            null,
            null,
            _('Änderungen an Ihrem Blubber.')
        );
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createLink(\BlubberComment $comment)
    {
        return \URLHelper::getURL(
            "{$GLOBALS['ABSOLUTE_URI_STUDIP']}dispatch.php/blubber/index/{$comment->thread_id}",
            [],
            true
        );
    }
}
