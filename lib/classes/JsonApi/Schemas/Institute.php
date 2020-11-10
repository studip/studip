<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class Institute extends SchemaProvider
{
    const TYPE = 'institutes';

    const REL_BLUBBER = 'blubber-threads';
    const REL_FILES = 'file-refs';
    const REL_FOLDERS = 'folders';
    const REL_STATUS_GROUPS = 'status-groups';

    protected $resourceType = self::TYPE;

    public function getId($institute)
    {
        return $institute->id;
    }

    public function getAttributes($institute)
    {
        return [
            'name' => $institute['Name'],
            'city' => $institute['Plz'],
            'street' => $institute['Strasse'],
            'phone' => $institute['telefon'],
            'fax' => $institute['fax'],
            'url' => $institute['url'],
            'mkdate' => date('c', $institute['mkdate']),
            'chdate' => date('c', $institute['chdate']),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $filesLink = $this->getRelationshipRelatedLink($resource, self::REL_FILES);
        $relationships[self::REL_FILES] = [
            self::LINKS => [
                Link::RELATED => $filesLink,
            ],
        ];

        $foldersLink = $this->getRelationshipRelatedLink($resource, self::REL_FOLDERS);
        $relationships[self::REL_FOLDERS] = [
            self::LINKS => [
                Link::RELATED => $foldersLink,
            ],
        ];

        $relationships[self::REL_BLUBBER] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BLUBBER),
            ],
        ];

        $relationships = $this->addStatusGroupsRelationship(
            $relationships,
            $resource,
            $shouldInclude(self::REL_STATUS_GROUPS)
        );

        return $relationships;
    }

    private function addStatusGroupsRelationship(
        array $relationships,
        $resource,
        $includeData
    ) {
        $relation = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_STATUS_GROUPS),
            ]
        ];
        if ($includeData) {
            $related = $resource->status_groups;
            $relation[self::DATA] = $related;
        }

        return array_merge($relationships, [self::REL_STATUS_GROUPS => $relation]);
    }
}
