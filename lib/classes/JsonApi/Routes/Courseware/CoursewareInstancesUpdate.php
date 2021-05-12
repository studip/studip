<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Instance;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Instance as InstanceSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one courseware instance.
 */
class CoursewareInstancesUpdate extends JsonApiController
{
    use CoursewareInstancesHelper, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = $this->findInstance($args['id']);
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateCoursewareInstance($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateInstance($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $resource)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (InstanceSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayHas($json, 'data.attributes.favorite-block-types')) {
            $favoriteBlockTypes = self::arrayGet($json, 'data.attributes.favorite-block-types');
            if (!is_array($favoriteBlockTypes)) {
                return 'Attribute `favorite-block-types` must be an array.';
            }
            $blockTypes = array_map(function ($blockType) {
                return $blockType::getType();
            }, $resource->getBlockTypes());
            foreach ($favoriteBlockTypes as $favoriteBlockType) {
                if (!in_array($favoriteBlockType, $blockTypes)) {
                    return 'Attribute `favorite-block-types` contains an invalid block type.';
                }
            }
        } elseif (self::arrayHas($json, 'data.attributes.sequential-progression')) {
            $sequentialProgression = self::arrayGet($json, 'data.attributes.sequential-progression');
            if (!is_bool($sequentialProgression)) {
                return 'Attribute `sequential-progression` must be a bool.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.editing-permission-level')) {
            $editingPermissionLevel = self::arrayGet($json, 'data.attributes.editing-permission-level');
            if (!is_string($editingPermissionLevel)) {
                return 'Attribute `editing-permission-level` must be a string.';
            }
            if (!$resource->isValidEditingPermissionLevel($editingPermissionLevel)) {
                return 'Attribute `editing-permission-level` contains an invalid value.';
            }
        }
    }

    private function updateInstance(\User $user, Instance $instance, array $json): Instance
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $favorites = $get('data.attributes.favorite-block-types');
        $instance->setFavoriteBlockTypes($user, $favorites);

        $sequentialProgression = $get('data.attributes.sequential-progression');
        $instance->setSequentialProgression($sequentialProgression);

        $editingPermissionLevel = $get('data.attributes.editing-permission-level');
        $instance->setEditingPermissionLevel($editingPermissionLevel);

        return $instance;
    }
}
