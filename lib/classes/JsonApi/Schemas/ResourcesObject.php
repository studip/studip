<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class ResourcesObject extends SchemaProvider
{
    const TYPE = 'resources-objects';

    const REL_ASSIGNMENTS = 'assignments';
    const REL_CATEGORY = 'category';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getPrimaryMeta($resource)
    {
        return [
            'resource-type' => $resource->category->name,
        ];
    }

    public function getAttributes($resource)
    {
        return [
            'name' => $resource->name,
            'description' => $resource->description,

            'is-room' => (bool) $resource->category->is_room,
            'multiple-assign' => (bool) $resource->multiple_assign,
            'requestable' => (bool) $resource->requestable,
            'lockable' => (bool) $resource->lockable,

            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $relationships = $this->addAssignmentsRelationship($relationships, $resource, $includeList);
        $relationships = $this->addCategoryRelationship($relationships, $resource, $includeList);

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addCategoryRelationship(array $relationships, $resource, $includeList)
    {
        if ($resource->category_id) {
            $relationships[self::REL_CATEGORY] = [
                self::DATA => $resource->category,
                self::LINKS => [
                    Link::RELATED => new Link('/resources-categories/' . $resource->category_id),
                ],
            ];
        }

        return $relationships;
    }

    private function addAssignmentsRelationship(array $relationships, $resource, $includeList)
    {
        $relationships[self::REL_ASSIGNMENTS] = [
                self::LINKS => [
                    Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_ASSIGNMENTS),
                ],
        ];

        return $relationships;
    }
}
