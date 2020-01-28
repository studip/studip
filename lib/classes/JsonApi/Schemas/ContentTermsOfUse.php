<?php

namespace JsonApi\Schemas;

class ContentTermsOfUse extends SchemaProvider
{
    const TYPE = 'terms-of-use';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        return [
            'name' => $resource['name'],
            'description' => mb_strlen($resource['description']) ? $resource['description'] : null,
            'icon' => $resource['icon'],
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }
}
