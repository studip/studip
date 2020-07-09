<?php

namespace JsonApi\Schemas;

use JsonApi\Routes\Files\Authority as FilesAuthority;
use Neomerx\JsonApi\Document\Link;

class File extends SchemaProvider
{
    const TYPE = 'files';

    const REL_FILE_REFS = 'file-refs';
    const REL_OWNER = 'owner';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->getId();
    }

    public function getInclusionMeta($resource)
    {
        return $this->getPrimaryMeta($resource);
    }

    public function getAttributes($resource)
    {
        $attributes = [
            'name' => $resource['name'],
            'mime-type' => $resource['mime_type'],
            'filesize' => (int) $resource['size'],

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];

        if ($resource['metadata']['url']) {
            $user = $this->getDiContainer()->get('studip-current-user');
            if (FilesAuthority::canUpdateFile($user, $resource)) {
                $attributes['url'] = $resource['metadata']['url'];
            }
        }

        return $attributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($isPrimary) {
            $relationships = $this->addOwnerRelationship($relationships, $resource);
            $relationships = $this->addFileRefsRelationship($relationships, $resource);
        }

        return $relationships;
    }

    private function addFileRefsRelationship(array $relationships, \File $resource)
    {
        $refs = $resource->refs;

        $relationships[self::REL_FILE_REFS] = [
            self::SHOW_SELF => true,
            self::DATA => $refs,
        ];

        return $relationships;
    }

    private function addOwnerRelationship(array $relationships, \File $resource)
    {
        if ($resource->user_id) {
            $relationships[self::REL_OWNER] = [
            self::LINKS => [
            Link::RELATED => $this->getSchemaContainer()
                          ->getSchema($resource->owner)->getSelfSubLink($resource->owner),
                          ],
                        ];
        }

        return $relationships;
    }
}
