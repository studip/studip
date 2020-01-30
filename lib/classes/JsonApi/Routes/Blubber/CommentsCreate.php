<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use JsonApi\Routes\ValidationTrait;

/**
 * Create a new blubber comment.
 */
class CommentsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        if (!($thread = \BlubberThread::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateComment($user = $this->getUser($request), $thread)) {
            throw new AuthorizationFailedException();
        }

        $content = self::arrayGet($json, 'data.attributes.content');

        $comment = \BlubberComment::create([
            'thread_id' => $thread->id,
            'content' => $content,
            'user_id' => $user->id,
            'external_contact' => 0
        ]);

        return $this->getCreatedResponse($comment);
    }

    protected function validateResourceDocument($json)
    {
        if (empty(self::arrayGet($json, 'data.attributes.content'))) {
            return 'Comment should not be empty.';
        }
    }
}
