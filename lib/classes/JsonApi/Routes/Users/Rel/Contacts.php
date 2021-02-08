<?php

namespace JsonApi\Routes\Users\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Users\Authority;
use JsonApi\Routes\RelationshipsController;

class Contacts extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $contacts = $related->contacts;
        $total = count($contacts);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse(
            $contacts->limit($offset, $limit),
            $total,
            $this->getRelationshipLinks($related)
        );
    }

    protected function addToRelationship(Request $request, $related)
    {
        $json = $this->validate($request);

        foreach ($this->validateContacts($related, $json) as $contactId) {
            if (!\Contact::countBySQL('owner_id = ? AND user_id = ?', [$related->id, $contactId])) {
                \Contact::create(['owner_id' => $related->id, 'user_id' => $contactId]);
            }
        }

        return $this->getCodeResponse(204);
    }

    protected function removeFromRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $contacts = $this->validateContacts($related, $json);
        $this->removeContacts($related, $contacts);

        return $this->getCodeResponse(204);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $contacts = $this->validateContacts($related, $json);
        $this->replaceContacts($related, $contacts);

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!$user = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $user;
    }

    protected function authorize(Request $request, $resource)
    {
        return Authority::canEditUser($this->getUser($request), $resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, 'contacts');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, 'contacts');
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
            if (self::arrayGet($item, 'type') !== \JsonApi\Schemas\User::TYPE) {
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

    private function validateContacts(\User $user, $json)
    {
        $contacts = [];

        foreach (self::arrayGet($json, 'data') as $contactResource) {
            if (!$contact = \User::find($contactResource['id'])) {
                throw new RecordNotFoundException();
            }

            if (!Authority::canShowUser($user, $contact)) {
                throw new RecordNotFoundException();
            }

            $contacts[] = $contact->id;
        }

        return $contacts;
    }

    private function addContact(\User $user, $contact_id)
    {
        if (!\Contact::countBySQL('owner_id = ? AND user_id = ?', [$user->id, $contact_id])
        ) {
            \Contact::create(['owner_id' => $user->id, 'user_id' => $contact_id]);
        }
    }

    private function removeContacts(\User $user, array $contactIds)
    {
        $user->contacts->unsetBy('user_id', $contactIds);
        $user->store();
    }

    private function replaceContacts(\User $user, array $new_ids)
    {
        $old_ids = $user->contacts->pluck('user_id');

        $this->removeContacts($user, array_diff($old_ids, $new_ids));
        $diff = array_diff($new_ids, $old_ids);
        array_walk(
            $diff,
            function ($contactId) use ($user) {
                $this->addContact($user, $contactId);
            }
        );
    }
}
