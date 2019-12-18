<?php

namespace JsonApi\Routes\Messages;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// As of now: update the is-read flag
class MessageUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$message = \Message::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!MessageAuthority::canShowMessage($user = $this->getUser($request), $message)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        if (!$message = $this->updateMessageFromJSON($user, $message, $json)) {
            throw new InternalServerError('Could not update message.');
        }

        return $this->getContentResponse($message);
    }

    protected function validateResourceDocument($json)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at message´s top level.';
        }

        if (\JsonApi\Schemas\Message::TYPE
            !== self::arrayGet($json, 'data.type')
        ) {
            return 'Missing `type` member of message´s `data`.';
        }

        if (!self::arrayHas($json, 'data.attributes')) {
            return 'Missing `attributes` member of message´s `data`.';
        }

        // Attribute: is-read
        if (self::arrayHas($json, 'data.attributes.is-read')) {
            if (!is_bool(self::arrayGet($json, 'data.attributes.is-read'))) {
                return '`is-read` must be boolean.';
            }
        }
    }

    protected function updateMessageFromJSON(\User $user, \Message $message, array $json)
    {
        if (self::arrayHas($json, 'data.attributes.is-read')) {
            $isRead = (bool) self::arrayGet($json, 'data.attributes.is-read');
            $isRead ? $message->markAsRead($user->id) : $message->markAsUnread($user->id);
        }

        return $message;
    }
}
