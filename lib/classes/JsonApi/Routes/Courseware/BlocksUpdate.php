<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Block.
 */
class BlocksUpdate extends JsonApiController
{
    use EditBlockAwareTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = Block::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateBlock($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateBlock($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (BlockSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }
    }

    private function updateBlock(\User $user, Block $resource, array $json): Block
    {
        return $this->updateLockedResource($user, $resource, function ($user, $resource) use ($json) {
            $get = function ($key, $default = '') use ($json) {
                return self::arrayGet($json, $key, $default);
            };

            if ($payload = $get('data.attributes.payload')) {
                if (!$resource->type->validatePayload((object) $payload)) {
                    throw new UnprocessableEntityException('Invalid payload for this `block-type`.');
                }
                $resource->type->setPayload($payload);
            }

            if ($category = $get('data.attributes.category')) {
                $resource->category = $category;
            }

            if ($position = $get('data.attributes.position')) {
                $resource->position = $position;
            }

            if (is_bool($get('data.attributes.visible'))) {
                $resource->visible = $get('data.attributes.visible');
            }

            if ($get('data.relationships.container.data.id')) {
                $resource->container_id = $get('data.relationships.container.data.id');
            }

            $resource->editor_id = $user->id;
            $resource->store();

            return $resource;
        });
    }
}
