<?php

namespace JsonApi\Routes\Blubber;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\BlubberThread as Schema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a new private blubber thread.
 */
class ThreadsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        if (!Authority::canCreatePrivateBlubberThread($user = $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        $contextType = self::arrayGet($json, 'data.attributes.context-type', '');
        if ('private' !== $contextType) {
            throw new BadRequestException('Only blubber threads of context-type=private can be created.');
        }

        $thread = \BlubberThread::create(
            [
                'context_type' => 'private',
                'context_id' => 'global',
                'user_id' => $user->id,
                'external_contact' => 0,
                'display_class' => null,
                'visible_in_stream' => 1,
                'commentable' => 1,
                'content' => '',
            ]
        );

        \BlubberMention::create(['thread_id' => $thread->id, 'user_id' => $user->id]);

        return $this->getCreatedResponse($thread);
    }

    protected function validateResourceDocument($json)
    {
        if (Schema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Missing or wrong type.';
        }

        if (!self::arrayHas($json, 'data.attributes.context-type')) {
            return 'Attribute \'context-type\' is required.';
        }
    }
}
