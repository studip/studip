<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Document\Link;

class UserProgress extends SchemaProvider
{
    const TYPE = 'courseware-user-progresses';

    const REL_BLOCK = 'block';
    const REL_USER = 'user';

    protected $resourceType = self::TYPE;

    /**
     * {@inheritdoc}
     */
    public function getId($resource)
    {
        return $resource->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource)
    {
        return [
            'grade' => (float) $resource['grade'],
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $relationships[self::REL_BLOCK] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->block)
                    ->getSelfSubLink($resource->block),
            ],
            self::DATA => $resource->block,
        ];

        $relationships[self::REL_USER] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->user)
                    ->getSelfSubLink($resource->user),
            ],
            self::DATA => $resource->user,
        ];

        return $relationships;
    }
}
