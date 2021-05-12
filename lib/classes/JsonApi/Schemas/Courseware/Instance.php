<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Document\Link;

class Instance extends SchemaProvider
{
    const TYPE = 'courseware-instances';

    const REL_BOOKMARKS = 'bookmarks';
    const REL_ROOT = 'root';

    protected $resourceType = self::TYPE;

    /**
     * {@inheritdoc}
     */
    public function getId($resource)
    {
        $root = $resource->getRoot();

        return join('_', [$root->range_type, $root->range_id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource)
    {
        $user = $this->getDiContainer()->get('studip-current-user');

        return [
            'block-types' => array_map([$this, 'mapBlockType'], $resource->getBlockTypes()),
            'container-types' => array_map([$this, 'mapContainerType'], $resource->getContainerTypes()),
            'favorite-block-types' => $resource->getFavoriteBlockTypes($user),
            'sequential-progression' => (bool) $resource->getSequentialProgression(),
            'editing-permission-level' => $resource->getEditingPermissionLevel(),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function mapBlockType(string $typeClass): array
    {
        return [
            'type' => $typeClass::getType(),
            'title' => $typeClass::getTitle(),
            'description' => $typeClass::getDescription(),
            'categories' => $typeClass::getCategories(),
            'content_types' => $typeClass::getContentTypes(),
            'file_types' => $typeClass::getFileTypes(),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function mapContainerType(string $typeClass): array
    {
        return [
            'type' => $typeClass::getType(),
            'title' => $typeClass::getTitle(),
            'description' => $typeClass::getDescription(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $user = $this->getDiContainer()->get('studip-current-user');
        $relationships[self::REL_BOOKMARKS] = [
            self::SHOW_SELF => true,
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BOOKMARKS),
            ],
            self::DATA => $resource->getUsersBookmarks($user),
        ];

        $relationships[self::REL_ROOT] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->getRoot())
                    ->getSelfSubLink($resource->getRoot()),
            ],
            self::DATA => $resource->getRoot(),
        ];

        return $relationships;
    }
}
