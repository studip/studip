<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Block.
 */
class StructuralElementsUpdate extends JsonApiController
{
    use EditBlockAwareTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = StructuralElement::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateStructuralElement($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateStructuralElement($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $resource)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (StructuralElementSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayHas($json, 'data.relationships.parent')) {
            // Sonderfall: Wurzel hat kein parent und kann auch nicht verändert werden
            if ($resource->isRootNode()) {
                if (null !== self::arrayGet($json, 'data.relationships.parent.data')) {
                    return 'Cannot modify `parent` of a root node.';
                }

                // Regelfall: Es gibt die Relation, aber `parent` ist ungültig.
            } else {
                $parent = $this->getParentFromJson($json);
                if (!$parent) {
                    return 'Invalid `parent` relationship.';
                }

                // keine Schleifen
                if (
                    in_array(
                        $resource->id,
                        array_merge(
                            [$parent->id],
                            array_map(function ($ancestor) {
                                return $ancestor->id;
                            }, $parent->findAncestors())
                        )
                    )
                ) {
                    return 'Invalid `parent` relationship resulting in a cycle.';
                }
            }
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

    private function updateStructuralElement(\User $user, StructuralElement $resource, array $json): StructuralElement
    {
        return $this->updateLockedResource($user, $resource, function ($user, $resource) use ($json) {
            $attributes = [
                'copy-approval',
                'external-relations',
                'payload',
                'position',
                'public',
                'purpose',
                'read-approval',
                'release-date',
                'title',
                'withdraw-date',
                'write-approval',
            ];

            foreach ($attributes as $jsonKey) {
                $sormKey = strtr($jsonKey, '-', '_');
                if ($val = self::arrayGet($json, 'data.attributes.'.$jsonKey, '')) {
                    $resource->$sormKey = $val;
                }
            }

            // update parent
            if (self::arrayHas($json, 'data.relationships.parent')) {
                $parent = $this->getParentFromJson($json);
                $resource->parent_id = $parent->id;
            }

            $resource->editor_id = $user->id;
            $resource->store();

            return $resource;
        });
    }
}
