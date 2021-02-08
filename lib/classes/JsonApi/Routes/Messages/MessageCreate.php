<?php

namespace JsonApi\Routes\Messages;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessageCreate extends JsonApiController
{
    use ValidationTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!MessageAuthority::canCreateMessage($user = $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        if (!$message = $this->createMessageFromJSON($user, $this->validate($request))) {
            throw new InternalServerError('Could not create message.');
        }

        return $this->getCreatedResponse($message);
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (self::arrayGet($json, 'data.type')
            !== \JsonApi\Schemas\Message::TYPE
        ) {
            return 'Missing `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.attributes')) {
            return 'Missing `attributes` member of document´s `data`.';
        }

        // Attribute: subject
        $subject = self::arrayGet($json, 'data.attributes.subject', '');
        if (!$subject || !mb_strlen(trim($subject))) {
            return '`subject` must not be empty.';
        }

        // Attribute: message
        $message = self::arrayGet($json, 'data.attributes.message', '');
        if (!$message || !mb_strlen(trim($message))) {
            return '`message` must not be empty.';
        }

        // Attribute: tags (optional)
        if ($tags = self::arrayGet($json, 'data.attributes.tags')) {
            if (!is_array($tags)) {
                return '`tags` must be an array of strings.';
            }
            foreach ($tags as $tag) {
                if (!is_string($tag)) {
                    return '`tags` must be an array of strings.';
                }
            }
        }

        // Relation: recipients
        if (!self::arrayGet($json, 'data.relationships.recipients')) {
            return 'Relationship `recipients` must be present.';
        }

        $recipients = self::arrayGet($json, 'data.relationships.recipients.data', []);

        if (empty($recipients)) {
            return 'Relationship `recipients` must not be empty.';
        }

        foreach ($recipients as $recipient) {
            if (self::arrayGet($recipient, 'type') !== \JsonApi\Schemas\User::TYPE) {
                return 'Relationship `recipients` must only contain users.';
            }
        }

        // Relation: originator
        // TODO
    }

    protected function createMessageFromJSON(\User $user, array $json)
    {
        $subject = self::arrayGet($json, 'data.attributes.subject');
        $body = self::arrayGet($json, 'data.attributes.message');
        $tags = self::arrayGet($json, 'data.attributes.tags', []);

        $recipients = $this->getRecipientsFromJSON($json);

        // TODO:
        $originatorMessage = null;

        return $this->createMessage($user, $recipients, $subject, $body, $tags, $originatorMessage);
    }

    private function getRecipientsFromJSON($json)
    {
        return array_map(
            function ($jsonRecipient) {
                if (!$user = \User::find($jsonRecipient['id'])) {
                    throw new RecordNotFoundException('Recipient not found.');
                }

                return $user;
            },
            self::arrayGet($json, 'data.relationships.recipients.data')
        );
    }

    protected function createMessage(
        \User $sender,
        array $recipients,
        string $subject,
        string $body,
        array $tags = null,
        string $answerTo = null
    ) {
        $messageBody = \Studip\Markup::purifyHtml($body);

        $messaging = new \messaging();
        $messaging->send_as_email = 1;

        $messaging->insert_message(
            $messageBody,
            array_map(
                function (\User $recipient) {
                    return $recipient->username;
                },
                $recipients
            ),
            $sender->id,
            '',
            $messageId = md5(uniqid('message', true)),
            '',
            null,
            $subject,
            '',
            'normal',
            $tags ?: null
        );

        if ($answerTo) {
            $oldMessage = \Message::find($answerTo);
            if ($oldMessage) {
                $oldMessage->originator->answered = 1;
                $oldMessage->store();
            }
        }

        return \Message::find($messageId);
    }
}
