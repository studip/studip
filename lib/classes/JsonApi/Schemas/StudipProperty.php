<?php

namespace JsonApi\Schemas;

class StudipProperty extends SchemaProvider
{
    const TYPE = 'studip-properties';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->field;
    }

    public function getAttributes($resource)
    {
        return [
            'description' => studip_utf8encode($resource->description),
            'value' => studip_utf8encode($resource->value),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks($resource)
    {
        return [];
    }
}
