<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class Folder extends SchemaProvider
{
    const TYPE = 'folders';
    const REL_OWNER = 'owner';
    const REL_PARENT = 'parent';
    const REL_RANGE = 'range';
    const REL_FEEDBACK = 'feedback-elements';
    const REL_FILE_REFS = 'file-refs';
    const REL_FOLDERS = 'folders';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->getId();
    }

    public function getAttributes($resource)
    {
        $user = $this->getDiContainer()->get('studip-current-user');

        $attributes = [
            'folder-type' => $resource->folder_type,
            'name' => $resource->name,
            'description' => $resource->description ?: null,

            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),

            'is-visible' => (bool) $resource->isVisible($user->id),
            'is-readable' => (bool) $resource->isReadable($user->id),
            'is-writable' => (bool) $resource->isWritable($user->id),
            'is-editable' => (bool) $resource->isEditable($user->id),
            'is-subfolder-allowed' => (bool) $resource->isSubfolderAllowed($user->id),
        ];

        // TODO: sollte das wirklich zugänglich sein?
        if ($resource->isReadable($user->id)) {
            $attributes['data-content'] = json_decode($resource->data_content);
        }

        return $attributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($isPrimary) {
            $relationships = $this->getFeedbackRelationship($relationships, $resource);
            $relationships = $this->getFilesRelationship($relationships, $resource);
            $relationships = $this->getFoldersRelationship($relationships, $resource);
            $relationships = $this->getOwnerRelationship($relationships, $resource);
            $relationships = $this->getParentRelationship($relationships, $resource);
            $relationships = $this->getRangeRelationship($relationships, $resource);
        }

        return $relationships;
    }

    private function getOwnerRelationship(array $relationships, $resource)
    {
        if ($resource->user_id && $resource->owner) {
            $relationships[self::REL_OWNER] = [
                self::DATA => $resource->owner,
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->owner)
                    ->getSelfSubLink($resource->owner),
                ],
            ];
        }

        return $relationships;
    }

    private function getParentRelationship(array $relationships, $resource)
    {
        if ($resource->parent_id) {
            $parent = $resource->parentfolder->getTypedFolder();
            $relationships[self::REL_PARENT] = [
                self::DATA => $parent,
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($parent)
                    ->getSelfSubLink($parent),
                ],
            ];
        }

        return $relationships;
    }

    private function getRangeRelationship(array $relationships, $resource)
    {
        if ($resource->range_id) {
            try {
                $relationships[self::REL_RANGE] = $this->getMeaningfulRangeRelationship($resource);
            } catch (\InvalidArgumentException $exception) {
                $relationships[self::REL_RANGE] = $this->getDefaultRangeRelationship($resource);
            }
        }

        return $relationships;
    }

    private function getMeaningfulRangeRelationship($resource)
    {
        $rangeType = $resource->range_type;
        if ($range = $resource->$rangeType) {
            $schema = $this->getSchemaContainer()->getSchema($range);

            return [
                self::DATA => $range,
                self::LINKS => [
                    Link::RELATED => $schema->getSelfSubLink($range),
                ],
            ];
        }

        return $this->getDefaultRangeRelationship($resource);
    }

    private function getDefaultRangeRelationship($resource)
    {
        return [
            self::META => [
                'range_id' => $resource->range_id,
                'range_type' => $resource->range_type,
            ],
        ];
    }

    private function getFeedbackRelationship(array $relationships, $resource): array
    {
        if ($resource->range_type === 'course') {
            if (\Feedback::isActivated($resource->range_id)) {
                $relationships[self::REL_FEEDBACK] = [
                    self::LINKS => [
                        Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FEEDBACK)
                    ],
                ];
            }
        }

        return $relationships;
    }

    private function getFoldersRelationship(array $relationships, $resource)
    {
        $relationships[self::REL_FOLDERS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FOLDERS),
            ],
        ];

        return $relationships;
    }

    private function getFilesRelationship(array $relationships, $resource)
    {
        $relationships[self::REL_FILE_REFS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FILE_REFS),
            ],
        ];

        return $relationships;
    }
}
