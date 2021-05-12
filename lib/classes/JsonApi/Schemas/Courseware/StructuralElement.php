<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Document\Link;

class StructuralElement extends SchemaProvider
{
    const TYPE = 'courseware-structural-elements';

    const REL_ANCESTORS = 'ancestors';
    const REL_CHILDREN = 'children';
    const REL_CONTAINERS = 'containers';
    const REL_COURSE = 'course';
    const REL_DESCENDANTS = 'descendants';
    const REL_EDITBLOCKER = 'edit-blocker';
    const REL_EDITOR = 'editor';
    const REL_IMAGE = 'image';
    const REL_OWNER = 'owner';
    const REL_PARENT = 'parent';
    const REL_USER = 'user';

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
        $user = $this->getDiContainer()->get('studip-current-user');

        return [
            'position' => (int) $resource['position'],
            'title' => (string) $resource['title'],
            'purpose' => (string) $resource['purpose'],
            'payload' => $resource['payload']->getIterator(),
            'public' => (int) $resource['public'],
            'release-date' => $resource['release_date'] ? date('Y-m-d', (int) $resource['release_date']) : null,
            'withdraw-date' => $resource['withdraw_date'] ? date('Y-m-d', (int) $resource['withdraw_date']) : null,
            'read-approval' => $resource['read_approval']->getIterator(),
            'write-approval' => $resource['write_approval']->getIterator(),
            'copy-approval' => $resource['copy_approval']->getIterator(),
            'can-edit' => $resource->canEdit($user),
            'can-read' => $resource->canRead($user),

            'external-relations' => $resource['external_relations']->getIterator(),
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param \Courseware\Models\StructuralElement $resource
     * @param bool                                 $isPrimary
     * @param array                                $includeList
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $shouldInclude = function ($key) use ($includeList) {
            return in_array($key, $includeList);
        };

        $relationships[self::REL_CHILDREN] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_CHILDREN),
            ],
            self::DATA => $resource->children,
        ];

        $relationships[self::REL_CONTAINERS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_CONTAINERS),
            ],
            self::DATA => $resource->containers,
        ];

        if ($resource->course) {
            $relationships[self::REL_COURSE] = [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->course)
                        ->getSelfSubLink($resource->course),
                ],
                self::DATA => $resource->course,
            ];
        }

        if ($resource->user) {
            $relationships[self::REL_USER] = [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->user)
                        ->getSelfSubLink($resource->user),
                ],
                self::DATA => $resource->user,
            ];
        }

        $relationships[self::REL_OWNER] = $resource['owner_id']
            ? [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->owner)
                        ->getSelfSubLink($resource->owner),
                ],
                self::DATA => $resource->owner,
            ]
            : [self::DATA => null];

        $relationships[self::REL_EDITOR] = $resource['editor_id']
            ? [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->editor)
                        ->getSelfSubLink($resource->editor),
                ],
                self::DATA => $resource->editor,
            ]
            : [self::DATA => $resource->editor];

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

        $relationships[self::REL_PARENT] = $resource->parent_id
            ? [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($resource->parent)
                        ->getSelfSubLink($resource->parent),
                ],

                self::DATA => $resource->parent,
            ]
            : [self::DATA => null];

        $relationships = $this->addAncestorsRelationship(
            $relationships,
            $resource,
            $shouldInclude(self::REL_ANCESTORS)
        );
        $relationships = $this->addDescendantsRelationship(
            $relationships,
            $resource,
            $shouldInclude(self::REL_DESCENDANTS)
        );

        $relationships = $this->addImageRelationship($relationships, $resource, $shouldInclude(self::REL_IMAGE));

        return $relationships;
    }

    private function addAncestorsRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_ANCESTORS),
            ],
        ];

        if ($includeData) {
            $related = $resource->findAncestors();
            $relation[self::DATA] = $related;
        }

        $relationships[self::REL_ANCESTORS] = $relation;

        return $relationships;
    }

    private function addDescendantsRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_DESCENDANTS),
            ],
        ];

        if ($includeData) {
            $related = $resource->findDescendants();
            $relation[self::DATA] = $related;
        }

        $relationships[self::REL_DESCENDANTS] = $relation;

        return $relationships;
    }

    private function addImageRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::DATA => $resource->image ?: null,
        ];

        if ($resource->image) {
            $relation[self::META] = [
                'download-url' => $resource->image->getFileType()->getDownloadURL(),
            ];
        }

        $relationships[self::REL_IMAGE] = $relation;

        return $relationships;
    }
}
