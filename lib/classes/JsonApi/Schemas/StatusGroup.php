<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class StatusGroup extends SchemaProvider
{
    const REL_RANGE = 'range';
    const TYPE = 'status-groups';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        $stringOrNull = function ($item) {
            return trim($item) != '' ? (string) $item : null;
        };

        $dateOrNull = function ($item) {
            return $item ? date('c', $item) : null;
        };

        return [
            'name' => (string) $resource['name'],
            'female-name' => $stringOrNull($resource['name_w']),
            'male-name' => $stringOrNull($resource['name_m']),
            'position' => (int) $resource['position'],
            'size' => (int) $resource['size'],

            'selfassign' => (bool) $resource['selfassign'],
            'selfassign-start' => $dateOrNull($resource['selfassign_start']),
            'selfassign-end' => $dateOrNull($resource['selfassign_end']),

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        // range-id
        $relationships = $this->addRangeRelationship(
            $relationships,
            $resource,
            $shouldInclude(self::REL_RANGE)
        );

        return $relationships;
    }

    private function addRangeRelationship(
        array $relationships,
        $resource,
        $includeData
    ) {
        $related = $this->findRange($resource);

        $relation = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                                      ->getSchema($related)
                                      ->getSelfSubLink($related)
            ]
        ];
        if ($includeData) {
            $relation[self::DATA] = $related;
        }

        return array_merge($relationships, [self::REL_RANGE => $relation]);
    }

    private function findRange($resource)
    {
        foreach (["parent", "course", "institute", "user"] as $sorm) {
            if ($range = $resource[$sorm]) {
                return $range;
            }
        }

        return null;
    }
}
