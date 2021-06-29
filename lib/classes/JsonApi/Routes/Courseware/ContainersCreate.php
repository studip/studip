<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Container as ContainerSchema;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a container.
 */
class ContainersCreate extends JsonApiController
{
    use ValidationTrait;

    const REL_STRUCTURAL_ELEMENT = 'data.relationships.structural-element';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $structElem = $this->getStructElemFromJson($json);
        if (!Authority::canCreateContainer($user = $this->getUser($request), $structElem)) {
            throw new AuthorizationFailedException();
        }
        $container = $this->createContainer($user, $json, $structElem);

        return $this->getCreatedResponse($container);
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
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.container-type')) {
            return 'Missing `container-type` attribute.';
        }

        $containerType = self::arrayGet($json, 'data.attributes.container-type');
        if (!$this->validateContainerType($containerType)) {
            return 'Invalid `container-type` attribute.';
        }

        if (!self::arrayHas($json, self::REL_STRUCTURAL_ELEMENT)) {
            return 'Missing `structural-element` relationship.';
        }
        if (!$this->getStructElemFromJson($json)) {
            return 'Invalid `structural-element` relationship.';
        }
    }

    private function validateContainerType(string $containerType)
    {
        return \Courseware\ContainerTypes\ContainerType::isContainerType($containerType);
    }

    private function getStructElemFromJson($json)
    {
        if (!$this->validateResourceObject($json, self::REL_STRUCTURAL_ELEMENT, StructuralElementSchema::TYPE)) {
            return null;
        }
        $structElemId = self::arrayGet($json, self::REL_STRUCTURAL_ELEMENT . '.data.id');

        return \Courseware\StructuralElement::find($structElemId);
    }

    private function createContainer(\User $user, array $json, \Courseware\StructuralElement $structElem)
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $container = \Courseware\Container::build([
            'structural_element_id' => $structElem->id,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => '',
            'position' => $structElem->countContainers(),
            'container_type' => $get('data.attributes.container-type'),
            'payload' => '',
        ]);

        $payload = $get('data.attributes.payload');
        if ($payload) {
            if (!$container->type->validatePayload((object) $payload)) {
                throw new UnprocessableEntityException('Invalid payload for this `container-type`.');
            }
            $container->type->setPayload($payload);
        }

        $container->store();

        return $container;
    }
}
