<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class SemType extends SchemaProvider
{
    const REL_SEM_CLASS = 'sem-class';
    const TYPE = 'sem-types';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource['id'];
    }

    public function getAttributes($resource)
    {
        return [
            'name' => $resource['name'],
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        // SemClass
        $related = $resource->getClass();
        $relationships[self::REL_SEM_CLASS] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                                      ->getSchema($related)
                                      ->getSelfSubLink($related)
            ],
            self::DATA => $related,
        ];

        return $relationships;
    }
}
