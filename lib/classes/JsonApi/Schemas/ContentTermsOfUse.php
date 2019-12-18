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
            'name' => studip_utf8encode($resource['name']),
            'description' => mb_strlen($resource['description']) ? studip_utf8encode($resource['description']) : null,
            'icon' => studip_utf8encode($resource['icon']),
            'mkdate' => date('c', studip_utf8encode($resource['mkdate'])),
            'chdate' => date('c', studip_utf8encode($resource['chdate'])),
        ];
    }
}
