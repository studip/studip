<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class InstituteMember extends SchemaProvider
{
    const TYPE = 'institute-memberships';
    const REL_INSTITUTE = 'institute';
    const REL_USER = 'user';

    protected $resourceType = self::TYPE;

    public function getId($membership)
    {
        return $membership->id;
    }

    public function getAttributes($resource)
    {
        $defaultNull = function ($key) use ($resource) {
            return $resource->$key ?: null;
        };

        $attributes = [
            'permission' => $defaultNull('inst_perms'),
            'office-hours' => $defaultNull('sprechzeiten'),
            'location' => $defaultNull('raum'),
            'phone' => $defaultNull('telefon'),
            'fax' => $defaultNull('fax'),
            // 'externdefault' => $defaultNull('externdefault'),
            // 'priority' => $defaultNull('priority'),
            // 'visible' => (bool) $defaultNull('visible'),
        ];

        return $attributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [
            self::REL_USER => [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()->getSchema($resource->user)->getSelfSubLink($resource->user),
                ],
                self::DATA => $resource->user,
            ],

            self::REL_INSTITUTE => [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()->getSchema($resource->institute)->getSelfSubLink($resource->institute),
                ],
                self::DATA => $resource->institute,
            ],
        ];

        return $relationships;
    }
}
