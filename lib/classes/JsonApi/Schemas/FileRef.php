<?php

namespace JsonApi\Schemas;

use JsonApi\Providers\JsonApiConfig as C;
use Neomerx\JsonApi\Document\Link;

class FileRef extends SchemaProvider
{
    const TYPE = 'file-refs';

    const REL_FEEDBACK = 'feedback-elements';
    const REL_FILE = 'file';
    const REL_OWNER = 'owner';
    const REL_PARENT = 'parent';
    const REL_RANGE = 'range';
    const REL_TERMS = 'terms-of-use';

    const META_CONTENT = 'content';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->getId();
    }

    public function getPrimaryMeta($resource)
    {
        $link = $this->getDiContainer()->get(C::JSON_URL_PREFIX)
              .$this->getRelationshipRelatedLink($resource, self::META_CONTENT)->getSubHref();

        return [
            'download-url' => $link,
        ];
    }

    public function getAttributes($resource)
    {
        $attributes = [
            'name' => $resource['name'],
            'description' => $resource['description'],

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),

            'downloads' => (int) $resource['downloads'],

            'filesize' => (int) $resource->file->size,
            'mime-type' => $resource->file->mime_type
        ];

        $user = $this->getDiContainer()->get('studip-current-user');
        if ($folder = $resource->getFolderType()) {
            $filetype = $resource->getFileType();
            $attributes = array_merge(
                $attributes,
                [
                    'is-readable' => $folder->isReadable($user->id),
                    'is-downloadable' => $filetype->isDownloadable($user->id),
                    'is-editable' => $filetype->isEditable($user->id),
                    'is-writable' => $filetype->isWritable($user->id),
                ]
            );
        }

        return $attributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $relationships = $this->getFeedbackRelationship($relationships, $resource);
        $relationships = $this->addFileRelationship($relationships, $resource);
        $relationships = $this->addOwnerRelationship($relationships, $resource);
        $relationships = $this->addParentRelationship($relationships, $resource);
        $relationships = $this->addRangeRelationship($relationships, $resource);
        $relationships = $this->addTermsRelationship($relationships, $resource);

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getFeedbackRelationship(
        array $relationships,
        \FileRef $resource
    ) {
        if ($folder = $resource->getFolderType()) {
            if ($folder->range_id && $folder->range_type === 'course' && \Feedback::isActivated($folder->range_id)) {
                $relationships[self::REL_FEEDBACK] = [
                    self::LINKS => [
                        Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FEEDBACK)
                    ],
                ];
            }
        }

        return $relationships;
    }

    private function addFileRelationship(array $relationships, \FileRef $resource)
    {
        if ($resource->file) {
            $relationships[self::REL_FILE] = [
                self::DATA => $resource->file,
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($resource->file)
                    ->getSelfSubLink($resource->file),
                ],
            ];
        }

        return $relationships;
    }

    private function addOwnerRelationship(array $relationships, \FileRef $resource)
    {
        $relationships[self::REL_OWNER] = [
            self::META => [
                'name' => $resource->getAuthorName(),
            ],
            self::DATA => $resource->owner,
        ];

        if (isset($resource->owner)) {
            $relationships[self::REL_OWNER][self::LINKS] = [
                Link::RELATED => $this->getSchemaContainer()
                ->getSchema($resource->owner)->getSelfSubLink($resource->owner),
            ];
        }

        return $relationships;
    }

    private function addParentRelationship(array $relationships, \FileRef $resource)
    {
        if ($resource->folder_id) {
            $folder = $resource->getFolderType();
            $relationships[self::REL_PARENT] = [
                self::DATA => $folder,
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($folder)
                    ->getSelfSubLink($folder),
                ],
            ];
        }

        return $relationships;
    }

    private function addRangeRelationship(array $relationships, \FileRef $resource)
    {
        if ($folder = $resource->getFolderType()) {
            if ($folder->range_id) {
                try {
                    $rangeType = $folder->range_type;
                    if ($range = $folder->$rangeType) {
                        $schema = $this->getSchemaContainer()->getSchema($range);
                        $relationships[self::REL_RANGE] = [
                            self::DATA => $range,
                            self::LINKS => [
                                Link::RELATED => $schema->getSelfSubLink($range),
                            ],
                        ];
                    }
                } catch (\InvalidArgumentException $exception) {
                }
            }
        }

        return $relationships;
    }

    private function addTermsRelationship(array $relationships, \FileRef $resource)
    {
        $relationships[self::REL_TERMS] = [
            self::DATA => $resource->content_terms_of_use_id ? $resource->terms_of_use : null,
            self::SHOW_SELF => true,
        ];

        return $relationships;
    }
}
