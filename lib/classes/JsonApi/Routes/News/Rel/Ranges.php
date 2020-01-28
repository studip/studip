<?php

namespace JsonApi\Routes\News\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\News\Authority;
use JsonApi\Routes\RelationshipsController;
use JsonApi\Schemas\StudipNews as NewsSchema;

class Ranges extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $ranges = $this->getRanges($this->getUser($request), $related);
        $total = count($ranges);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse(
            array_slice($ranges, $offset, $limit),
            $total,
            $this->getRelationshipLinks($related)
        );
    }

    protected function addToRelationship(Request $request, $related)
    {
        $json = $this->validate($request);

        $ranges = $this->validateRanges($this->getUser($request), $related, $json);
        $this->addRanges($related, $ranges);

        return $this->getCodeResponse(204);
    }

    protected function removeFromRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $ranges = $this->validateRanges($this->getUser($request), $related, $json);
        $this->removeRanges($related, $ranges);

        return $this->getCodeResponse(204);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $ranges = $this->validateRanges($this->getUser($request), $related, $json);
        $this->replaceRanges($related, $ranges);

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!$related = \StudipNews::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canShowNews($this->getUser($request), $resource);

            case 'PATCH':
            case 'POST':
            case 'DELETE':
                return Authority::canEditNews($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, 'ranges');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, 'ranges');
    }

    private function getRanges(\User $user, \StudipNews $news)
    {
        $types = NewsSchema::getRangeClasses();

        return array_filter(
            $news->news_ranges->map(function ($range) use ($types) {
                if ('global' === $range->type) {
                    return $this->container['studip-system-object'];
                } elseif (isset($types[$range->type])) {
                    $klass = $types[$range->type];

                    return $klass::build(['id' => $range->range_id], false);
                }

                return null;
            }),
            function ($range) use ($user, $news) {
                return $range
                    ? Authority::canShowNewsRange($user, $news, $range->getId())
                    : false;
            }
        );
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
            if (!in_array(self::arrayGet($item, 'type'), NewsSchema::getRangeTypes())) {
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

    private function validateRanges(\User $user, \StudipNews $news, $json)
    {
        $ranges = [];

        foreach (self::arrayGet($json, 'data') as $rangeResource) {
            if (!$range = $this->findRange($rangeResource['type'], $rangeResource['id'])) {
                throw new RecordNotFoundException();
            }

            if (!Authority::canEditNewsRange($user, $news, $range->id)) {
                throw new AuthorizationFailedException();
            }

            $ranges[] = $range;
        }

        return $ranges;
    }

    private function findRange($type, $rangeId)
    {
        switch ($type) {
            case \JsonApi\Schemas\Studip::TYPE:
                return $this->container['studip-system-object'];

            case \JsonApi\Schemas\Course::TYPE:
                return \Course::find($rangeId);

            case \JsonApi\Schemas\User::TYPE:
                return \User::find($rangeId);

            case \JsonApi\Schemas\Institute::TYPE:
                return \Institute::find($rangeId);
        }

        return null;
    }

    private function addRanges(\StudipNews $news, array $ranges)
    {
        foreach ($ranges as $range) {
            $news->addRange($range->getId());
        }
        $news->storeRanges();
    }

    private function removeRanges(\StudipNews $news, array $ranges)
    {
        foreach ($ranges as $range) {
            $news->deleteRange($range instanceof \SimpleORMap ? $range->getId() : $range);
        }
        $news->storeRanges();
    }

    private function replaceRanges(\StudipNews $news, array $ranges)
    {
        $oldRangeIds = $news->getRanges();
        $newRangeIds = array_map(
            function ($range) {
                return $range->getId();
            },
            $ranges
        );

        $this->removeRanges($news, array_diff($oldRangeIds, $newRangeIds));
        $this->addRanges($news, array_diff($newRangeIds, $oldRangeIds));

        $news->storeRanges();
    }
}
