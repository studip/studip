<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Container as ContainerSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Container.
 */
class ContainersUpdate extends JsonApiController
{
    use EditBlockAwareTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = Container::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateContainer($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateContainer($user, $resource, $json);

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

        if (ContainerSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        // TODO: Validate everything
    }

    private function updateContainer(\User $user, Container $resource, array $json): Container
    {
        return $this->updateLockedResource($user, $resource, function ($user, $resource) use ($json) {
            if ($payload = self::arrayGet($json, 'data.attributes.payload')) {
                if (!$resource->type->validatePayload((object) $payload)) {
                    throw new UnprocessableEntityException('Invalid payload for this `container-type`.');
                }
                $resource->type->setPayload($payload);
            }

            if (self::arrayHas($json, 'data.relationships.structural-element.data.id')) {
                $resource->structural_element_id = self::arrayGet(
                    $json,
                    'data.relationships.structural-element.data.id'
                );
            }

            $resource->editor_id = $user->id;
            $resource->store();

            return $resource;
        });
    }
}
