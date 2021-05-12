<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Document\Link;

class Container extends SchemaProvider
{
    const TYPE = 'courseware-containers';

    const REL_BLOCKS = 'blocks';
    const REL_OWNER = 'owner';
    const REL_EDITOR = 'editor';
    const REL_EDITBLOCKER = 'edit-blocker';
    const REL_STRUCTURAL_ELEMENT = 'structural-element';

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
            'position' => (int) $resource['position'],
            'site' => (int) $resource['site'],
            'container-type' => (string) $resource['container_type'],
            'title' => (string) $resource->type->getTitle(),
            'width' => (string) $resource->type->getContainerWidth(),
            'visible' => (bool) $resource['visible'],
            'payload' => $resource['payload']->getIterator(),
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

        $shouldInclude = function ($key) use ($includeList) {
            return in_array($key, $includeList);
        };

        $relationships = $this->addBlocksRelationship($relationships, $resource, $shouldInclude(self::REL_BLOCKS));

        $relationships[self::REL_OWNER] = $resource['owner_id']
            ? [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->owner)
                        ->getSelfSubLink($resource->owner),
                ],
                self::DATA => $resource->owner,
            ]
            : [self::DATA => $resource->owner];

        $relationships[self::REL_EDITOR] = $resource['editor_id']
            ? [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->editor)
                        ->getSelfSubLink($resource->editor),
                ],
                self::DATA => $resource->editor,
            ]
            : [self::DATA => null];

        $relationships[self::REL_EDITBLOCKER] = $resource['edit_blocker_id']
            ? [
                self::SHOW_SELF => true,
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->edit_blocker)
                        ->getSelfSubLink($resource->edit_blocker),
                ],
                self::DATA => $resource->edit_blocker,
            ]
            : [self::SHOW_SELF => true, self::DATA => null];

        $relationships[self::REL_STRUCTURAL_ELEMENT] = $resource['structural_element_id']
            ? [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->structural_element)
                        ->getSelfSubLink($resource->structural_element),
                ],
                self::DATA => $resource->structural_element,
            ]
            : [self::DATA => null];

        return $relationships;
    }

    private function addBlocksRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::SHOW_SELF => true,
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BLOCKS),
            ],
        ];

        $relation[self::DATA] = $resource->blocks;

        $relationships[self::REL_BLOCKS] = $relation;

        return $relationships;
    }
}
