<?php

namespace JsonApi\Schemas;

class ResourcesCategory extends SchemaProvider
{
    const TYPE = 'resources-categories';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        return [
            'name' => $resource->name,
            'description' => $resource->description,

            'system' => (bool) $resource->system,
            'is-room' => (bool) $resource->is_room,
            'icon' => (int) $resource->iconnr,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        // $relationships = $this->addCategoryRelationship($relationships, $resource, $includeList);

        return $relationships;
    }
}
