<?php

namespace JsonApi\Routes\Blubber\Rel;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Blubber\Authority;
use JsonApi\Routes\RelationshipsController;
use JsonApi\Routes\Users\Authority as UsersAuthority;
use Psr\Http\Message\ServerRequestInterface as Request;

class Mentions extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $mentions = $related->mentions;
        $total = count($mentions);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse(
            $mentions->limit($offset, $limit)->pluck('user'),
            $total,
            $this->getRelationshipLinks($related)
        );
    }

    protected function addToRelationship(Request $request, $related)
    {
        $json = $this->validate($request);

        foreach ($this->validateMentions($this->getUser($request), $json) as $mention) {
            if (!\BlubberMention::countBySQL('thread_id = ? AND user_id = ?', [$related->id, $mention->id])) {
                \BlubberMention::create(['thread_id' => $related->id, 'user_id' => $mention->id]);
            }
        }

        return $this->getCodeResponse(204);
    }

    protected function removeFromRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $mentions = $this->validateMentions($user = $this->getUser($request), $json);

        $notMe = array_filter($mentions, function (\User $mention) use ($user) {
            return $mention->id !== $user->id;
        });

        if (count($notMe)) {
            throw new AuthorizationFailedException('Users cannot remove other mentioned users.');
        }

        $this->removeMentions($related, $mentions);

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!$thread = \BlubberThread::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $thread;
    }

    protected function authorize(Request $request, $resource)
    {
        return Authority::canCreateComment($this->getUser($request), $resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, 'mentions');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, 'mentions');
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        $data = self::arrayGet($json, 'data');

        if (!is_array($data)) {
            return 'Document´s ´data´ must be an array.';
        }

        foreach ($data as $item) {
            if (\JsonApi\Schemas\User::TYPE !== self::arrayGet($item, 'type')) {
                return 'Wrong `type` in document´s `data`.';
            }

            if (!self::arrayGet($item, 'id')) {
                return 'Missing `id` of document´s `data`.';
            }
        }

        if (self::arrayHas($json, 'data.attributes')) {
            return 'Document must not have `attributes`.';
        }
    }

    private function validateMentions(\User $user, $json)
    {
        $mentions = [];

        foreach (self::arrayGet($json, 'data') as $mentionResource) {
            if (!$mention = \User::find($mentionResource['id'])) {
                throw new RecordNotFoundException();
            }

            if (!UsersAuthority::canShowUser($user, $mention)) {
                throw new RecordNotFoundException();
            }

            $mentions[] = $mention;
        }

        return $mentions;
    }

    private function removeMentions(\BlubberThread $thread, array $users)
    {
        foreach ($users as $user) {
            \BlubberMention::deleteBySQL('thread_id = ? AND user_id = ?', [$thread->id, $user->id]);
        }
    }
}
