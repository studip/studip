<?php

namespace JsonApi\Schemas;

use JsonApi\Errors\InternalServerError;
use JsonApi\Models\Resources\ResourcesObject;
use Neomerx\JsonApi\Document\Link;

class ResourcesAssignEvent extends SchemaProvider
{
    const TYPE = 'resources-assign-events';

    const REL_OWNER = 'owner';
    const REL_RESOURCE = 'resources-object';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        return [
            'repeat-mode' => studip_utf8encode($resource->getRepeatMode()),
            'start' => date('c', $resource->getBegin()),
            'end' => date('c', $resource->getEnd()),

            'owner-free-text' => studip_utf8encode($resource->getUserFreeName()),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = [];

        $relationships = $this->addOwnerRelationship($relationships, $resource, $includeList);
        $relationships = $this->addResourceRelationship($relationships, $resource, $includeList);

        // TODO:
        // 'user_id' => $resource->user_id,
        // 'assign_id' => $resource->getAssignId(),

        return $relationships;
    }

    private function addOwnerRelationship(array $relationships, $resource, $includeList)
    {
        $getObject = function ($resource) {
            $ownerId = studip_utf8encode($resource->getAssignUserId());
            $type = get_object_type($ownerId);

            switch ($type) {
                case 'date':
                    // throw new InternalServerError('NYI');
                case 'inst':
                    return \Institute::find($ownerId);

                case 'user':
                    return \User::find($ownerId);
            }
        };

        $getLink = function ($object) {
            return $this->getSchemaContainer()->getSchema($object)->getSelfSubLink($object);
        };

        if ($data = $getObject($resource)) {
            $link = $getLink($data);

            $relationships[self::REL_OWNER] = [
                self::LINKS => [
                    Link::RELATED => $link,
                ],
                self::DATA => $data,
            ];
        }

        return $relationships;
    }

    // TODO: das ganze Objekt nur on demand als $data
    private function addResourceRelationship(array $relationships, $resource, $includeList)
    {
        if ($data = ResourcesObject::find($resource->getResourceId())) {
            $link = $this->getSchemaContainer()->getSchema($data)->getSelfSubLink($data);
            $relationships[self::REL_RESOURCE] = [
                self::LINKS => [
                    Link::RELATED => $link,
                ],
                self::DATA => $data,
            ];
        }

        return $relationships;
    }
}
