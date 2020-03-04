<?php

namespace JsonApi\Schemas;

class StatusGroup extends SchemaProvider
{
    const TYPE = 'status-groups';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        return [
            'name' => (string) $resource['name'],
        ];
    }
}
