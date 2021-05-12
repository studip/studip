<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\Block;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\RelationshipsController;
use Psr\Http\Message\ServerRequestInterface as Request;

class BlocksEditBlocker extends RelationshipsController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        return $this->getIdentifiersResponse($related->edit_blocker_id ? $related->edit_blocker : null);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);

        $data = self::arrayGet($json, 'data');

        if (!Authority::canUpdateEditBlocker($this->getUser($request), $related)) {
            throw new ConflictException();
        }

        if (null === $data) {
            $editorId = null;
        } else {
            $editorId = self::arrayGet($json, 'data.id');
            if (!$editor = \User::find($editorId)) {
                throw new BadRequestException('Invalid edit-blocker specified.');
            }
        }

        $related->edit_blocker_id = $editor->id;
        $related->store();

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!$related = Block::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canShowBlock($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canUpdateBlock($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\Courseware\Block::REL_EDITBLOCKER);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return null;
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at file´s top level.';
        }

        $data = self::arrayGet($json, 'data');

        if ($data === null) {
            return;
        }

        if (count($data) !== 2 || !isset($data['id']) || !isset($data['type'])) {
            return 'File´s ´data´ must be null or a resource identifier.';
        }

        if ($data['type'] !== \JsonApi\Schemas\User::TYPE) {
            return 'Wrong `data.type`.';
        }
    }
}
