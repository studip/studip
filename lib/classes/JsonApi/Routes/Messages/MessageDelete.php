<?php

namespace JsonApi\Routes\Messages;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Löscht eine Nachricht.
 */
class MessageDelete extends JsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$message = \Message::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!MessageAuthority::canDeleteMessage($user = $this->getUser($request), $message)) {
            throw new AuthorizationFailedException();
        }

        if (!$this->deleteMessage($message, $user)) {
            throw new RecordNotFoundException();
        }

        return $this->getCodeResponse(204);
    }

    protected function deleteMessage(\Message $message, \User $user)
    {
        return (bool) array_reduce(
            ['snd', 'rec'],
            function ($success, $type) use ($message, $user) {
                if ($messageuser = \MessageUser::find([$user->id, $message->id, $type])) {
                    $messageuser['deleted'] = 1;
                    $success += $messageuser->store();
                }

                return $success;
            },
            0
        );
    }
}
