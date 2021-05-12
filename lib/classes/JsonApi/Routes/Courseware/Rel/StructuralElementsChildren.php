<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\StructuralElement;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\RelationshipsController;
use Psr\Http\Message\ServerRequestInterface as Request;

class StructuralElementsChildren extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $children = $related->children;
        $total = count($children);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse($children->limit($offset, $limit), $total);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $children = $this->validateChildren($this->getUser($request), $json);
        $this->replaceChildren($related, $children);

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!($related = \Courseware\StructuralElement::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canIndexStructuralElements($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canReorderStructuralElements($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\Courseware\StructuralElement::REL_CHILDREN);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, \JsonApi\Schemas\Courseware\StructuralElement::REL_CHILDREN);
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
            if (self::arrayGet($item, 'type') !== \JsonApi\Schemas\Courseware\StructuralElement::TYPE) {
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

    private function validateChildren(\User $user, $json)
    {
        $children = [];

        foreach (self::arrayGet($json, 'data') as $childResource) {
            if (!($child = StructuralElement::find($childResource['id']))) {
                throw new RecordNotFoundException();
            }

            if (!Authority::canShowStructuralElement($user, $child)) {
                throw new RecordNotFoundException();
            }

            $children[] = $child->id;
        }

        return $children;
    }

    private function replaceChildren(StructuralElement $structuralElement, array $newIds)
    {
        $oldIds = $structuralElement->children->pluck('id');
        $onlyInOld = array_diff($oldIds, $newIds);
        $onlyInNew = array_diff($newIds, $oldIds);

        // TODO: For now we are only interested in reordering the same containers
        if ($onlyInOld || $onlyInNew) {
            throw new ConflictException();
        }

        $stmt = \DBManager::get()->prepare('UPDATE cw_structural_elements SET position = FIND_IN_SET(id, ?) - 1 WHERE parent_id = ?');
        $stmt->execute([join(',', $newIds), $structuralElement->id]);
    }
}
