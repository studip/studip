<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a block in a container.
 */
class StructuralElementsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $parent = $this->getParentFromJson($json);
        if (!Authority::canCreateStructuralElement($user = $this->getUser($request), $parent)) {
            throw new AuthorizationFailedException();
        }
        $struct = $this->createStructuralElement($user, $json, $parent);

        return $this->getCreatedResponse($struct);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (StructuralElementSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.title')) {
            return 'Missing `title` attribute.';
        }

        if (!self::arrayHas($json, 'data.relationships.parent')) {
            return 'Missing `parent` relationship.';
        }
        if (!$this->getParentFromJson($json)) {
            return 'Invalid `parent` relationship.';
        }
    }

    private function getParentFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.parent', StructuralElementSchema::TYPE)) {
            return null;
        }
        $parentId = self::arrayGet($json, 'data.relationships.parent.data.id');

        return \Courseware\StructuralElement::find($parentId);
    }

    private function createStructuralElement(\User $user, array $json, \Courseware\StructuralElement $parent)
    {
        $struct = \Courseware\StructuralElement::build([
            'parent_id' => $parent->id,
            'range_id' => $parent->range_id,
            'range_type' => $parent->range_type,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => '',
            'title' => self::arrayGet($json, 'data.attributes.title', ''),
            'purpose' => 'CONTENT',
            'position' => $parent->countChildren()
        ]);

        $struct->store();

        return $struct;
    }
}
