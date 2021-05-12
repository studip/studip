<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\Container;
use Courseware\StructuralElement;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\RelationshipsController;
use Psr\Http\Message\ServerRequestInterface as Request;

class StructuralElementsContainers extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $containers = $related->containers;
        $total = count($containers);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse($containers->limit($offset, $limit), $total);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $containers = $this->validateContainers($this->getUser($request), $json);
        $this->replaceContainers($related, $containers);

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
                return Authority::canIndexContainers($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canReorderContainers($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\Courseware\StructuralElement::REL_CONTAINERS);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, \JsonApi\Schemas\Courseware\StructuralElement::REL_CONTAINERS);
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
            if (self::arrayGet($item, 'type') !== \JsonApi\Schemas\Courseware\Container::TYPE) {
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

    private function validateContainers(\User $user, $json)
    {
        $containers = [];

        foreach (self::arrayGet($json, 'data') as $containerResource) {
            if (!($container = Container::find($containerResource['id']))) {
                throw new RecordNotFoundException();
            }

            if (!Authority::canShowContainer($user, $container)) {
                throw new RecordNotFoundException();
            }

            $containers[] = $container->id;
        }

        return $containers;
    }

    private function replaceContainers(StructuralElement $structuralElement, array $newIds)
    {
        $oldIds = $structuralElement->containers->pluck('id');
        $onlyInOld = array_diff($oldIds, $newIds);
        $onlyInNew = array_diff($newIds, $oldIds);

        // TODO: For now we are only interested in reordering the same containers
        if ($onlyInOld || $onlyInNew) {
            throw new ConflictException();
        }

        $stmt = \DBManager::get()->prepare('UPDATE cw_containers SET position = FIND_IN_SET(id, ?) - 1 WHERE structural_element_id = ?');
        $stmt->execute([join(',', $newIds), $structuralElement->id]);
    }
}
