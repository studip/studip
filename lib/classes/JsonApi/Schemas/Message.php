<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class Message extends SchemaProvider
{
    const TYPE = 'messages';
    const REL_SENDER = 'sender';
    const REL_RECIPIENTS = 'recipients';

    protected $resourceType = self::TYPE;

    public function getId($message)
    {
        return $message->id;
    }

    public function getAttributes($message)
    {
        $user = $this->getDiContainer()->get('studip-current-user');

        return [
            'subject' => $message->subject,
            'message' => $message->message,
            'mkdate' => date('c', $message->mkdate),
            'is-read' => (bool) $message->isRead($user->id),
            'priority' => $message->priority,
            'tags' => $message->getTags(),
        ];
    }

    public function getRelationships($message, $isPrimary, array $includeList)
    {
        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = [];

        if ($isPrimary) {
            $relationships = $this->getSenderRelationship($relationships, $message, $shouldInclude(self::REL_SENDER));
            $relationships = $this->getRecipientsRelationship($relationships, $message, $shouldInclude(self::REL_RECIPIENTS));
        }

        return $relationships;
    }

    private function getSenderRelationship(array $relationships, \Message $message, $includeData)
    {
        $userId = $message->getSender()->id;

        $data = null;
        if ($userId) {
            $data = $includeData ? \User::find($userId) : \User::build(['id' => $userId], false);
        }

        $relationships[self::REL_SENDER] = [
            // self::SHOW_SELF => true,
            self::LINKS => [
                Link::RELATED => new Link('/users/'.$userId),
            ],
            self::DATA => $data,
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getRecipientsRelationship(array $relationships, \Message $message, $includeData)
    {
        $relationships[self::REL_RECIPIENTS] = [
            // self::SHOW_SELF => true,
            self::LINKS => [
                // Link::RELATED => new Link('/users/'.$userId),
            ],
            self::DATA => $message->receivers->map(function ($i) { return $i->user; }),
        ];

        return $relationships;
    }
}
