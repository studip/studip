<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\Block;
use Courseware\Container;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\RelationshipsController;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContainersBlocks extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $blocks = $related->blocks;
        $total = count($blocks);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse($blocks->limit($offset, $limit), $total);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $blocks = $this->validateBlocks($this->getUser($request), $json);
        $this->replaceBlocks($related, $blocks);

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!($related = \Courseware\Container::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canIndexBlocks($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canReorderBlocks($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\Courseware\Container::REL_BLOCKS);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, \JsonApi\Schemas\Courseware\Container::REL_BLOCKS);
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
            if (self::arrayGet($item, 'type') !== \JsonApi\Schemas\Courseware\Block::TYPE) {
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

    private function validateBlocks(\User $user, $json)
    {
        $blocks = [];

        foreach (self::arrayGet($json, 'data') as $blockResource) {
            if (!($block = Block::find($blockResource['id']))) {
                throw new RecordNotFoundException();
            }

            if (!Authority::canShowBlock($user, $block)) {
                throw new RecordNotFoundException();
            }

            $blocks[] = $block->id;
        }

        return $blocks;
    }

    private function replaceBlocks(Container $container, array $newIds)
    {
        $oldIds = $container->blocks->pluck('id');
        $onlyInOld = array_diff($oldIds, $newIds);
        $onlyInNew = array_diff($newIds, $oldIds);

        // TODO: For now we are only interested in reordering the same blocks
        if ($onlyInOld || $onlyInNew) {
            throw new ConflictException();
        }

        $stmt = \DBManager::get()->prepare('UPDATE cw_blocks SET position = FIND_IN_SET(id, ?) - 1 WHERE container_id = ?');
        $stmt->execute([join(',', $newIds), $container->id]);
    }
}
