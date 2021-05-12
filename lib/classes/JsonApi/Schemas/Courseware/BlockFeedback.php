<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Document\Link;

class BlockFeedback extends SchemaProvider
{
    const TYPE = 'courseware-block-feedback';

    const REL_USER = 'user';
    const REL_BLOCK = 'block';

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
            'feedback' => (string) $resource['feedback'],
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
