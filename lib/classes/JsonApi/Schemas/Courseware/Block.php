<?php

namespace JsonApi\Schemas\Courseware;

use Courseware\UserDataField;
use Courseware\UserProgress;
use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Document\Link;

class Block extends SchemaProvider
{
    const TYPE = 'courseware-blocks';

    const REL_COMMENTS = 'comments';
    const REL_CONTAINER = 'container';
    const REL_EDITBLOCKER = 'edit-blocker';
    const REL_EDITOR = 'editor';
    const REL_FEEDBACK = 'feedback';
    const REL_OWNER = 'owner';
    const REL_USERDATAFIELD = 'user-data-field';
    const REL_USERPROGRESS = 'user-progress';
    const REL_FILES = 'file-refs';

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
            'block-type' => (string) $resource['block_type'],
            'title' => (string) $resource->type->getTitle(),
            'visible' => (bool) $resource['visible'],
            'payload' => $resource->type->getPayload(),
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

        $relationships[self::REL_COMMENTS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_COMMENTS),
            ],
            self::DATA => $resource->comments,
        ];

        $relationships[self::REL_CONTAINER] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->container)
                    ->getSelfSubLink($resource->container),
            ],
            self::DATA => $resource->container,
        ];

        $relationships[self::REL_OWNER] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->owner)
                    ->getSelfSubLink($resource->owner),
            ],
            self::DATA => $resource->owner,
        ];

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

        $relationships[self::REL_FEEDBACK] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FEEDBACK),
            ],
        ];

        $user = $this->getDiContainer()->get('studip-current-user');
        $userDataField = UserDataField::getUserDataField($user, $resource);
        $relationships[self::REL_USERDATAFIELD] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_USERDATAFIELD),
            ],
            self::DATA => $userDataField,
        ];

        $userProgress = UserProgress::getUserProgress($user, $resource);
        $relationships[self::REL_USERPROGRESS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_USERPROGRESS),
            ],
            self::DATA => $userProgress,
        ];

        if ($resource->files) {
            $filesLink = $this->getRelationshipRelatedLink($resource, self::REL_FILES);

            $relationships[self::REL_FILES] = [
                self::LINKS => [
                    Link::RELATED => $filesLink,
                ],
            ];
        }

        return $relationships;
    }
}
