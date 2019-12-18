<?php

namespace JsonApi\Schemas;

class Studip extends SchemaProvider
{
    const TYPE = 'global';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->getId();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAttributes($news)
    {
        return [];
    }
}
