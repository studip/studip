<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class CourseEvent extends SchemaProvider
{
    const TYPE = 'course-events';
    const REL_OWNER = 'owner';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        return [
            'title' => $resource->title,
            'description' => $resource->getDescription(),
            'start' => date('c', $resource->getStart()),
            'end' => date('c', $resource->getEnd()),
            'categories' => array_filter($resource->toStringCategories(true)),
            'location' => $resource->getLocation(),

            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
            'recurrence' => $resource->getRecurrence(),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($owner = $resource->course) {
            $link = $this->getSchemaContainer()->getSchema($owner)->getSelfSubLink($owner);
            $relationships = [
                self::REL_OWNER => [self::LINKS => [Link::RELATED => $link], self::DATA => $owner],
            ];
        }

        return $relationships;
    }
}
