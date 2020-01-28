<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class Institute extends SchemaProvider
{
    const TYPE = 'institutes';

    const REL_FILES = 'file-refs';
    const REL_FOLDERS = 'folders';

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

        return $relationships;
    }
}
